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
 * Steps file to perform the EJSApp backup
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


/**
 * Define the complete EJSApp structure for backup, with file and id annotations
 */
class backup_ejsapp_activity_structure_step extends backup_activity_structure_step
{

    /**
     * Define the complete EJSApp structure for backup, with file and id annotations
     */
    protected function define_structure()
    {
        global $DB;

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated

        // 'course' is needed in $ejsapp to get ejsappbooking
        $ejsapp = new backup_nested_element('ejsapp', array('id'), array(
            'course', 'name', 'intro', 'introformat', 'appwording', 'appwordingformat', 'timecreated',
            'timemodified', 'applet_name', 'class_file', 'codebase',
            'mainframe', 'is_collaborative', 'applet_size_conf', 'preserve_aspect_ratio',
            'custom_width', 'custom_height', 'is_rem_lab', 'height', 'width', 'personalvars'));

        $ejsapp_personal_vars = new backup_nested_element('ejsapp_personal_vars', array('id'),
            array('name', 'type', 'minval', 'maxval'));

        $ejsapp_log = new backup_nested_element('ejsapp_log', array('id'),
            array('time', 'userid', 'action', 'info'));

        // Remote labs configuration
        /*$remlab_manager_exptsyst2pract = new backup_nested_element('remlab_manager_exptsyst2pract',
            array('id'), array('ejsappid', 'practiceid', 'practiceintro'));*/

        // Booking
        $ejsappbooking = new backup_nested_element('ejsappbooking', array('id'),
            array('course', 'name', 'intro', 'introformat', 'timecreated', 'timemodified'));

        $ejsappbooking_remlab_accesses = new backup_nested_element('ejsappbooking_remlab_accesses');
        $ejsappbooking_remlab_access = new backup_nested_element('ejsappbooking_remlab_access',
            array('id'), array('username', 'practiceid', 'starttime', 'endtime', 'valid'));

        $ejsappbooking_usersaccesses = new backup_nested_element('ejsappbooking_usersaccesses');
        $ejsappbooking_usersaccess = new backup_nested_element('ejsappbooking_usersaccess',
            array('id'), array('bookingid', 'userid', 'allowremaccess'));

        // Build the tree
        $ejsapp->add_child($ejsapp_personal_vars);
        $ejsapp->add_child($ejsapp_log);
        //$ejsapp->add_child($remlab_manager_exptsyst2pract);
        $ejsapp->add_child($ejsappbooking);
        $ejsapp->add_child($ejsappbooking_remlab_accesses);
        $ejsapp->add_child($ejsappbooking_usersaccesses);
        $ejsappbooking_remlab_accesses->add_child($ejsappbooking_remlab_access);
        $ejsappbooking_usersaccesses->add_child($ejsappbooking_usersaccess);

        // Define sources
        $ejsapp->set_source_table('ejsapp', array('id' => backup::VAR_ACTIVITYID));
        $ejsapp_personal_vars->set_source_table('ejsapp_personal_vars', array('ejsappid'  => '../id'));

        // Logging:
        //if ($userinfo) $ejsapp_log->set_source_table('ejsapp_log', array('ejsappid'  => '../id'));

        // Remote labs
        /*$is_remlab_manager_installed = $DB->get_records('block',array('name'=>'remlab_manager'));
        $is_remlab_manager_installed = !empty($is_remlab_manager_installed);
        if ($is_remlab_manager_installed) {
            $remlab_manager_exptsyst2pract->set_source_table('remlab_manager_exptsyst2pract', array('ejsappid'  => '../id'));
        }*/

        // Booking
        $is_ejsappbooking_installed = $DB->get_records('modules',array('name'=>'ejsappbooking'));
        $is_ejsappbooking_installed = !empty($is_ejsappbooking_installed);
        if ($is_ejsappbooking_installed && $userinfo) {
            $ejsappbooking->set_source_table('ejsappbooking', array('course'  => '../course'));
            $ejsappbooking_usersaccess->set_source_table('ejsappbooking_usersaccess', array('ejsappid'  => '../../id'));
            $ejsappbooking_remlab_access->set_source_table('ejsappbooking_remlab_access', array('ejsappid'  => '../../id'));
            // Define id annotations
            $ejsappbooking_usersaccess->annotate_ids('user', 'userid');
            $ejsappbooking_remlab_access->annotate_ids('user', 'username');
        }

        // Define file annotations
        $ejsapp->annotate_files('mod_ejsapp', 'jarfiles', null);
        $ejsapp->annotate_files('mod_ejsapp', 'tmp_jarfiles', null);
        $ejsapp->annotate_files('mod_ejsapp', 'xmlfiles', null);
        $ejsapp->annotate_files('mod_ejsapp', 'cntfiles', null);
        $ejsapp->annotate_files('mod_ejsapp', 'recfiles', null);

        // Return the root element (ejsapp), wrapped into standard activity structure
        return $this->prepare_activity_structure($ejsapp);

    }

}