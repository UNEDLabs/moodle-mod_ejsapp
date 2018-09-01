<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * EJSApp plugin privacy api.
 *
 * @package    mod_ejsapp
 * @copyright  2018 Luis de la Torre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_ejsapp\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Class to indicate what data stores the plugin, how to export it and how to delete it.
 *
 * @package    mod_ejsapp
 * @copyright  2018 Luis de la Torre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider {
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table(
            'ejsapp_records',
            [
                'time' => 'privacy:metadata:ejsapp_records:time',
                'userid' => 'privacy:metadata:ejsapp_records:userid',
                'ejsappid' => 'privacy:metadata:ejsapp_records:ejsappid',
                'sessionsid' => 'privacy:metadata:ejsapp_records:sessionsid',
                'actions' => 'privacy:metadata:ejsapp_records:actions',

            ],
            'privacy:metadata:ejsapp_records'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT c.id
                 FROM {context} c
           INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
           INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
           INNER JOIN {ejsapp} f ON e.id = cm.instance
            LEFT JOIN {ejsapp_records} r ON r.ejsappid = e.id
                WHERE (
                r.userid        = :drecorduserid
                )
        ";

        $params = [
            'modname'       => 'ejsapp',
            'contextlevel'  => CONTEXT_MODULE,
            'drecorduserid' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export personal data for the given approved_contextlist related to EJSApp records.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     * @throws
     */
    protected static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        // Filter out any contexts that are not related to modules.
        $cmids = array_reduce($contextlist->get_contexts(), function($carry, $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                $carry[] = $context->instanceid;
            }
            return $carry;
        }, []);

        if (empty($cmids)) {
            return;
        }

        $user = $contextlist->get_user();

        // Get all the EJSApp activities associated with the above course modules.
        $ejsappidstocmids = self::get_ejsapp_ids_to_cmids_from_cmids($cmids);
        $ejsappids = array_keys($ejsappidstocmids);

        list($insql, $inparams) = $DB->get_in_or_equal($ejsappids, SQL_PARAMS_NAMED);
        $params = array_merge($inparams, ['userid' => $user->id]);
        $recordset = $DB->get_recordset_select('ejsapp_records', "ejsappid $insql AND userid = :userid", $params, 'time, id');
        self::recordset_loop_and_export($recordset, 'ejsappid', [], function($carry, $record) use ($user, $ejsappidstocmids) {
            $carry[] = [
                'gradepercent' => $record->gradepercent,
                'originalgrade' => $record->originalgrade,
                'datesubmitted' => transform::datetime($record->datesubmitted),
                'dateupdated' => transform::datetime($record->dateupdated)
            ];
            return $carry;
        }, function($ejsappid, $data) use ($user, $ejsappidstocmids) {
            $context = \context_module::instance($ejsappidstocmids[$ejsappid]);
            $contextdata = helper::get_context_data($context, $user);
            $finaldata = (object) array_merge((array) $contextdata, ['records' => $data]);
            helper::export_context_files($context, $user);
            writer::with_context($context)->export_data([], $finaldata);
        });
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     * @throws
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        if ($cm = get_coursemodule_from_id('ejsapp', $context->instanceid)) {
            $DB->delete_records('ejsapp_records', ['ejsappid' => $cm->instance]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     * @throws
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
            $DB->delete_records('ejsapp_records', ['ejsappid' => $instanceid, 'userid' => $userid]);
        }
    }

    /**
     * Return a dict of EJSApp IDs mapped to their course module ID.
     *
     * @param array $cmids The course module IDs.
     * @return array In the form of [$ejsappid => $cmid].
     * @throws
     */
    protected static function get_ejsapp_ids_to_cmids_from_cmids(array $cmids) {
        global $DB;

        list($insql, $inparams) = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED);
        $sql = "SELECT ejsapp.id, cm.id AS cmid
                 FROM {ejsapp} ejsapp
                 JOIN {modules} m
                   ON m.name = :ejsapp
                 JOIN {course_modules} cm
                   ON cm.instance = ejsapp.id
                  AND cm.module = m.id
                WHERE cm.id $insql";
        $params = array_merge($inparams, ['ejsapp' => 'ejsapp']);

        return $DB->get_records_sql_menu($sql, $params);
    }

    /**
     * Loop and export from a recordset.
     *
     * @param \moodle_recordset $recordset The recordset.
     * @param string $splitkey The record key to determine when to export.
     * @param mixed $initial The initial data to reduce from.
     * @param callable $reducer The function to return the dataset, receives current dataset, and the current record.
     * @param callable $export The function to export the dataset, receives the last value from $splitkey and the dataset.
     * @return void
     */
    protected static function recordset_loop_and_export(\moodle_recordset $recordset, $splitkey, $initial,
                                                        callable $reducer, callable $export) {
        $data = $initial;
        $lastid = null;

        foreach ($recordset as $record) {
            if ($lastid && $record->{$splitkey} != $lastid) {
                $export($lastid, $data);
                $data = $initial;
            }
            $data = $reducer($data, $record);
            $lastid = $record->{$splitkey};
        }
        $recordset->close();

        if (!empty($lastid)) {
            $export($lastid, $data);
        }
    }
}