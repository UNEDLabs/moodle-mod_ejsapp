<?php
// This file is part of the Moodle module "EJSApp"
//
// EJSApp is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// EJSApp is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
//
// EJSApp has been developed by:
// - Luis de la Torre: ldelatorre@dia.uned.es
// - Ruben Heradio: rheradio@issi.uned.es
//
// at the Computer Science and Automatic Control, Spanish Open University
// (UNED), Madrid, Spain.


/**
 * Event observers used in ejsapp
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ejsapp;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observers used in ejsapp
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observers {

    /**
     * A user tried to access an EJSApp remote lab but needs to book first.
     *
     * @param \core\event\base $event The event.
     * @return void
     * @throws
     */
    public static function ejsapp_book($event) {
        global $DB;
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('logstore_standard_log')) {
            // Write info in custom table.
            $record = $event->get_record_snapshot('ejsapp_log', $event->objectid);
            $DB->insert_record('ejsapp_log', $record);
        }
    }

    /**
     * A user accessed an EJSApp remote lab but it was booked by another user.
     *
     * @param \core\event\base $event The event.
     * @return void
     * @throws
     */
    public static function ejsapp_booked($event) {
        global $DB;
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('logstore_standard_log')) {
            // Write info in custom table.
            $record = $event->get_record_snapshot('ejsapp_log', $event->objectid);
            $DB->insert_record('ejsapp_log', $record);
        }
    }

    /**
     * A user accessed an EJSApp virtual or remote lab in collaborative mode.
     *
     * @param \core\event\base $event The event.
     * @return void
     * @throws
     */
    public static function ejsapp_collab($event) {
        global $DB;
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('logstore_standard_log')) {
            // Write info in custom table.
            $record = $event->get_record_snapshot('ejsapp_log', $event->objectid);
            $DB->insert_record('ejsapp_log', $record);
        }
    }

    /**
     * A user accessed an EJSApp remote lab nut it was inactive.
     *
     * @param \core\event\base $event The event.
     * @return void
     * @throws
     */
    public static function ejsapp_inactive($event) {
        global $DB;
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('logstore_standard_log')) {
            // Write info in custom table.
            $record = $event->get_record_snapshot('ejsapp_log', $event->objectid);
            $DB->insert_record('ejsapp_log', $record);
        }
    }

    /**
     * A user accessed an EJSApp virtual or remote lab.
     *
     * @param \core\event\base $event The event.
     * @return void
     * @throws
     */
    public static function ejsapp_viewed($event) {
        global $DB;
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('logstore_standard_log')) {
            // Write info in custom table.
            $record = $event->get_record_snapshot('ejsapp_log', $event->objectid);
            $DB->insert_record('ejsapp_log', $record);
        }
    }

    /**
     * A user tried to access an EJSApp remote lab but he needed to wait.
     *
     * @param \core\event\base $event The event.
     * @return void
     * @throws
     */
    public static function ejsapp_wait($event) {
        global $DB;
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('logstore_standard_log')) {
            // Write info in custom table.
            $record = $event->get_record_snapshot('ejsapp_log', $event->objectid);
            $DB->insert_record('ejsapp_log', $record);
        }
    }

    /**
     * A user is working with an EJSApp virtual or remote lab.
     *
     * @param \core\event\base $event The event.
     * @return void
     * @throws
     */
    public static function ejsapp_working($event) {
        global $DB;
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('logstore_standard_log')) {
            // Write info in custom table.
            $record = $event->get_record_snapshot('ejsapp_log', $event->objectid);
            $DB->insert_record('ejsapp_log', $record);
        }
    }

}
