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
// The GNU General Public License is available on <http://www.gnu.org/licenses/>
//
// EJSApp has been developed by:
//  - Luis de la Torre: ldelatorre@dia.uned.es
//	- Ruben Heradio: rheradio@issi.uned.es
//
//  at the Computer Science and Automatic Control, Spanish Open University
//  (UNED), Madrid, Spain

/**
 * Tasks file to perform the EJSApp restore
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/ejsapp/backup/moodle2/restore_ejsapp_stepslib.php'); // Because it exists (must)

/**
 * EJSApp restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_ejsapp_activity_task extends restore_activity_task
{

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings()
    {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps()
    {
        // ejsapp only has one structure step
        $this->add_step(new restore_ejsapp_activity_structure_step('ejsapp_structure', 'ejsapp.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents()
    {
        $contents = array();

        $contents[] = new restore_decode_content('ejsapp', array('intro'), 'ejsapp');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules()
    {
        $rules = array();

        $rules[] = new restore_decode_rule('EJSAPPINDEX', '/mod/ejsapp/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('EJSAPPVIEWBYID', '/mod/ejsapp/view.php?id=$1', 'course_module');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * ejsapp logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules()
    {
        $rules = array();

        $rules[] = new restore_log_rule('ejsapp', 'add', 'view.php?id={course_module}', '{ejsapp}');
        $rules[] = new restore_log_rule('ejsapp', 'update', 'view.php?id={course_module}', '{ejsapp}');
        $rules[] = new restore_log_rule('ejsapp', 'view', 'view.php?id={course_module}', '{ejsapp}');

        $rules[] = new restore_log_rule('ejsapp', 'choose', 'view.php?id={course_module}', '{ejsapp}');
        $rules[] = new restore_log_rule('ejsapp', 'choose again', 'view.php?id={course_module}', '{ejsapp}');
        $rules[] = new restore_log_rule('ejsapp', 'report', 'report.php?id={course_module}', '{ejsapp}');


        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course()
    {
        $rules = array();

        // Fix old wrong uses (missing extension)
        $rules[] = new restore_log_rule('ejsapp', 'view all', 'index?id={course}', null,
            null, null, 'index.php?id={course}');

        $rules[] = new restore_log_rule('ejsapp', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}