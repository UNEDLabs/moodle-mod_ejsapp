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
 * Tasks file to perform the EJSApp backup
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one EJSApp activity
 */
class restore_ejsapp_activity_structure_step extends restore_activity_structure_step
{
    /**
     * Define structure
     */
    protected function define_structure()
    {
        $paths = array();
        $paths[] = new restore_path_element('ejsapp', '/activity/ejsapp');
        $paths[] = new restore_path_element('ejsapp_expsyst2pract', '/activity/ejsapp/ejsapp_expsyst2practs/ejsapp_expsyst2pract');
        $paths[] = new restore_path_element('ejsapp_personal_vars', '/activity/ejsapp/ejsapp_personal_vars/personal_vars');
        $paths[] = new restore_path_element('ejsapp_remlab_conf', '/activity/ejsapp/ejsapp_remlab_conf');

        $userinfo = $this->get_setting_value('userinfo');
        if ($userinfo){
            $paths[] = new restore_path_element('ejsappbooking_usersaccess', '/activity/ejsapp/ejsappbooking_usersaccesses/ejsappbooking_usersaccess');
            $paths[] = new restore_path_element('ejsappbooking_remlab_access', '/activity/ejsapp/ejsappbooking_remlab_accesses/ejsappbooking_remlab_access');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process table ejsapp
     * @param stdClass $data
     */
    protected function process_ejsapp($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->course = $this->get_courseid();

        // insert the ejsapp record
        $newitemid = $DB->insert_record('ejsapp', $data);

        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);

        // mapping old_ejsapp_id->new_old_ejsapp_id for xml state_files
        // (see after_execute)
        $this->set_mapping('ejsapp', $oldid, $newitemid, true);
    }//process_ejsapp

    /**
     * Process table ejsapp_expsyst2pract
     * @param stdClass $data
     */
    protected function process_ejsapp_expsyst2pract($data)
    {
        global $DB;

        $data = (object)$data;

        $data->ejsappid = $this->get_new_parentid('ejsapp');

        // insert the ejsapp record
        $DB->insert_record('ejsapp_expsyst2pract', $data);
    }//process_ejsapp_expsyst2pract

    /**
     * Process table ejsapp_personal_vars
     * @param stdClass $data
     */
    protected function process_ejsapp_personal_vars($data)
    {
        global $DB;

        $data = (object)$data;

        $data->ejsappid = $this->get_new_parentid('ejsapp');

        // insert the ejsapp record
        $DB->insert_record('ejsapp_personal_vars', $data);
    }//process_ejsapp_personal_vars

    /**
     * Process table ejsapp_remlab_conf
     * @param stdClass $data
     */
    protected function process_ejsapp_remlab_conf($data)
    {
        global $DB;

        $data = (object)$data;

        $data->ejsappid = $this->get_new_parentid('ejsapp');

        // insert the ejsapp record
        $DB->insert_record('ejsapp_remlab_conf', $data);
    }//process_ejsapp_remlab_conf

    /**
     * Process table ejsappbooking
     * @param stdClass $data
     */
    protected function process_ejsappbooking($data)
    {
        global $DB;

        $data = (object)$data;

        $data->course = $this->get_courseid();

        // insert the ejsapp record
        $is_ejsappbooking_restored = $DB->get_records('ejsappbooking',array('course'=>$data->course));
        $is_ejsappbooking_restored = !empty($is_ejsappbooking_restored);
        if (!$is_ejsappbooking_restored) {
            $DB->insert_record('ejsappbooking', $data);
        }
    }//process_ejsappbooking

    /**
     * Process table process_ejsappbooking_usersaccess
     * @param stdClass $data
     */
    protected function process_ejsappbooking_usersaccess($data)
    {
        global $DB;

        $data = (object)$data;

        $data->ejsappid = $this->get_new_parentid('ejsapp');
        $data->bookingid = $this->get_mappingid('ejsappbooking', $data->bookingid);
        $data->userid = $this->get_mappingid('user', $data->userid);


        // insert the ejsapp record
        $DB->insert_record('ejsappbooking_usersaccess', $data);
    }//process_ejsappbooking_usersaccess

    /**
     * Process table process_ejsappbooking_remlab_access
     * @param stdClass $data
     */
    protected function process_ejsappbooking_remlab_access($data)
    {
        global $DB;

        $data = (object)$data;

        // There is no necessity of mapping for "practiceid" nor "username"
        $data->ejsappid = $this->get_new_parentid('ejsapp');

        // insert the ejsapp record
        $DB->insert_record('ejsappbooking_remlab_access', $data);
    }//process_ejsappbooking_remlab_access

    /**
     * Extract jarfiles to the ejsapp jar folder
     */
    protected function after_execute()
    {

        global $CFG, $DB;

        // Add ejsapp related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_ejsapp', 'jarfiles', 'ejsapp');
        $this->add_related_files('mod_ejsapp', 'xmlfiles', 'ejsapp');
        $this->add_related_files('mod_ejsapp', 'expfiles', 'ejsapp');

        // restore ejsapp files:
        $sql = "select * from {$CFG->prefix}ejsapp";
        $ejsapp_records = $DB->get_records_sql($sql);

        foreach ($ejsapp_records as $ejsapp_record) {
            // copy files
            $sql = "select * from {$CFG->prefix}files where component = 'mod_ejsapp' and filename = '{$ejsapp_record->applet_name}.jar'";
            $file_records = $DB->get_records_sql($sql);
            if ($file_records) {
                foreach ($file_records as $file_record) {
                    $fs = get_file_storage();
                    $fileinfo = array(
                        'contextid' => $file_record->contextid, // ID of context
                        'component' => 'mod_ejsapp', // usually = table name
                        'filearea' => 'jarfiles', // usually = table name
                        'itemid' => $file_record->itemid, // usually = ID of row in table
                        'filepath' => '/', // any path beginning and ending in /
                        'filename' => $file_record->filename); // any filename
                    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'],
                        $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'],
                        $fileinfo['filename']);
                    if ($file) {
                        // create directories
                        if (!file_exists($CFG->dirroot . '/mod/ejsapp/jarfiles/')) {
                            mkdir($CFG->dirroot . '/mod/ejsapp/jarfiles/', 0777);
                        }
                        if (!file_exists($CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp_record->course)) {
                            mkdir($CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp_record->course, 0777);
                        }
                        if (!file_exists($CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp_record->course . '/' . $ejsapp_record->id)) {
                            mkdir($CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp_record->course . '/' . $ejsapp_record->id, 0777);
                        }

                        // copy file
                        $file_content = $file->get_content();
                        $fh = fopen($CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp_record->course . '/' . $ejsapp_record->id . '/' . $file_record->filename, 'w+') or die("can't open file");
                        fwrite($fh, $file_content);
                        fclose($fh);

                        // <update ejsapp table>
                        $codebase = '';
                        preg_match('/http:\/\/.+?\/(.+)/', $CFG->wwwroot, $match_result);
                        if (!empty($match_result) and $match_result[1]) {
                            $codebase .= '/' . $match_result[1];
                        }
                        $codebase .= '/mod/ejsapp/jarfiles/' . $ejsapp_record->course . '/' . $ejsapp_record->id . '/';
                        $record = new stdClass();
                        $record->id = $ejsapp_record->id;
                        $record->codebase = $codebase;
                        $DB->update_record('ejsapp', $record);
                    } //if ($file)
                } //foreach
            } //if ($file_records)
        } //foreach

    } //after_execute

} //class