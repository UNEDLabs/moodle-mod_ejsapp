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
            'custom_width', 'custom_height', 'is_rem_lab', 'height', 'width', 'personalvars', 'free_access'));

        $ejsapp_expsyst2practs = new backup_nested_element('ejsapp_expsyst2practs');
        $ejsapp_expsyst2pract = new backup_nested_element('ejsapp_expsyst2pract', array('id'),
            array('practiceid', 'practiceintro'));

        $ejsapp_personal_vars = new backup_nested_element('ejsapp_personal_vars', array('id'),
            array('name', 'type', 'minval', 'maxval'));

        $ejsapp_remlab_conf = new backup_nested_element('ejsapp_remlab_conf', array('id'),
            array('usingsarlab', 'sarlabinstance', 'sarlabcollab', 'ip', 'port', 'totalslots',
                  'weeklyslots', 'dailyslots', 'active'));

        $ejsapp_log = new backup_nested_element('ejsapp_log', array('id'),
            array('time', 'userid', 'action', 'info'));

        $ejsapp_sarlab_keys = new backup_nested_element('ejsapp_sarlab_keys', array('id'),
            array('user', 'sarlabpass', 'labmanager', 'creationtime'));

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
        $ejsapp->add_child($ejsapp_expsyst2practs);
        $ejsapp->add_child($ejsapp_personal_vars);
        $ejsapp->add_child($ejsapp_remlab_conf);
        $ejsapp->add_child($ejsapp_log);
        $ejsapp->add_child($ejsapp_sarlab_keys);
        $ejsapp->add_child($ejsappbooking);
        $ejsapp->add_child($ejsappbooking_usersaccesses);
        $ejsapp->add_child($ejsappbooking_remlab_accesses);
        $ejsapp_expsyst2practs->add_child($ejsapp_expsyst2pract);
        $ejsappbooking_remlab_accesses->add_child($ejsappbooking_remlab_access);
        $ejsappbooking_usersaccesses->add_child($ejsappbooking_usersaccess);

        // Define sources
        $ejsapp->set_source_table('ejsapp', array('id' => backup::VAR_ACTIVITYID));
        $ejsapp_expsyst2pract->set_source_table('ejsapp_expsyst2pract', array('ejsappid'  => '../../id'));
        $ejsapp_personal_vars->set_source_table('ejsapp_personal_vars', array('ejsappid'  => '../id'));
        $ejsapp_remlab_conf->set_source_table('ejsapp_remlab_conf', array('ejsappid'  => '../id'));

        // Booking
        $is_ejsappbooking_installed = $DB->get_records('modules',array('name'=>'ejsappbooking'));
        $is_ejsappbooking_installed = !empty($is_ejsappbooking_installed);
        if ($is_ejsappbooking_installed && $userinfo) {
            $ejsappbooking->set_source_table('ejsappbooking', array('course'  => '../course'));
            $ejsappbooking_usersaccess->set_source_table('ejsappbooking_usersaccess', array('ejsappid'  => '../../id'));
            $ejsappbooking_remlab_access->set_source_table('ejsappbooking_remlab_access', array('ejsappid'  => '../../id'));
        }

        // Define id annotations
        if ($is_ejsappbooking_installed && $userinfo) {
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