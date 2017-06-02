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
 * Steps file to perform the EJSApp backup
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete EJSApp structure for backup, with file and id annotations
 *
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_ejsapp_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define the complete EJSApp structure for backup, with file and id annotations
     */
    protected function define_structure() {
        global $DB;

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separately.

        // Parameter 'course' is needed in $ejsapp to get ejsappbooking.
        $ejsapp = new backup_nested_element('ejsapp', array('id'), array(
            'course', 'name', 'intro', 'introformat', 'appwording', 'appwordingformat', 'css', 'timecreated',
            'timemodified', 'applet_name', 'class_file', 'codebase',
            'mainframe', 'is_collaborative', 'applet_size_conf', 'preserve_aspect_ratio',
            'custom_width', 'custom_height', 'is_rem_lab', 'height', 'width', 'personalvars', 'blockly_conf'));

        $personalvars = new backup_nested_element('ejsapp_personal_vars', array('id'),
            array('name', 'type', 'minval', 'maxval'));

        $log = new backup_nested_element('ejsapp_log', array('id'),
            array('time', 'userid', 'action', 'info'));

        // Remote labs configuration.
        $exp2practs = new backup_nested_element('remlab_manager_exp2practs');
        $exp2prc = new backup_nested_element('block_remlab_manager_exp2prc',
            array('id'), array('ejsappid', 'practiceid', 'practiceintro'));

        // Booking.
        $ejsappbookings = new backup_nested_element('ejsappbookings');
        $ejsappbooking = new backup_nested_element('ejsappbooking', array('id'),
            array('course', 'name', 'intro', 'introformat', 'timecreated', 'timemodified'));

        $remlabaccesses = new backup_nested_element('ejsappbooking_remlab_accesses');
        $remlabaccess = new backup_nested_element('ejsappbooking_remlab_access',
            array('id'), array('username', 'practiceid', 'starttime', 'endtime', 'valid'));

        $usersaccesses = new backup_nested_element('ejsappbooking_usersaccesses');
        $usersaccess = new backup_nested_element('ejsappbooking_usersaccess',
            array('id'), array('bookingid', 'userid', 'allowremaccess'));

        // Build the tree.
        $ejsapp->add_child($personalvars);
        $ejsapp->add_child($log);
        $ejsapp->add_child($exp2practs);
        $ejsapp->add_child($ejsappbookings);
        $ejsapp->add_child($remlabaccesses);
        $ejsapp->add_child($usersaccesses);
        $exp2practs->add_child($exp2prc);
        $ejsappbookings->add_child($ejsappbooking);
        $remlabaccesses->add_child($remlabaccess);
        $usersaccesses->add_child($usersaccess);

        // Define sources.
        $ejsapp->set_source_table('ejsapp', array('id' => backup::VAR_ACTIVITYID));
        $personalvars->set_source_table('ejsapp_personal_vars', array('ejsappid'  => '../id'));

        // Logging.
        // if ($userinfo) $log->set_source_table('ejsapp_log', array('ejsappid'  => '../id'));.

        // Remote labs.
        $remlabmanagaer = $DB->get_records('block', array('name' => 'remlab_manager'));
        $remlabmanagaer = !empty($remlabmanagaer);
        if ($remlabmanagaer) {
            $exp2prc->set_source_table('block_remlab_manager_exp2prc', array('ejsappid'  => '../../id'));
        }

        // Booking.
        if ($userinfo) {
            $ejsappbooking = $DB->get_records('modules', array('name' => 'ejsappbooking'));
            $ejsappbooking = !empty($ejsappbooking);
            if ($ejsappbooking) {
                $ejsappbooking->set_source_table('ejsappbooking', array('course' => '../../course'));
                $usersaccess->set_source_table('ejsappbooking_usersaccess', array('ejsappid' => '../../id'));
                $remlabaccess->set_source_table('ejsappbooking_remlab_access', array('ejsappid' => '../../id'));
                // Define id annotations.
                $usersaccess->annotate_ids('user', 'userid');
                $remlabaccess->annotate_ids('user', 'username');
            }
        }

        // Define file annotations.
        $ejsapp->annotate_files('mod_ejsapp', 'jarfiles', null);
        $ejsapp->annotate_files('mod_ejsapp', 'tmp_jarfiles', null);
        $ejsapp->annotate_files('mod_ejsapp', 'xmlfiles', null);
        $ejsapp->annotate_files('mod_ejsapp', 'cntfiles', null);
        $ejsapp->annotate_files('mod_ejsapp', 'recfiles', null);

        // Return the root element (ejsapp), wrapped into standard activity structure.
        return $this->prepare_activity_structure($ejsapp);
    }

}