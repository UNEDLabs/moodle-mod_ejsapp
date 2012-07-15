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

// EJS manifest pareameters:
// 	- Applet-Width
// 	- Applet-Height
//  - Main-Frame
//  - Main-Class 
//  - Is-Collaborative


/**
 * The main ejsapp configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once ($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/filestorage/zip_packer.php');
require_once('locallib.php');

class mod_ejsapp_mod_form extends moodleform_mod {

    function definition()
    {
        global $COURSE, $CFG;
        $mform = &$this->_form;
        // -------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('ejsappname', 'ejsapp'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'ejsappname', 'ejsapp');
        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor();
        // -------------------------------------------------------------------------------   
        // Adding the rest of ejsapp settings, adding more fieldsets
        $mform->addElement('header', 'conf_parameters', get_string('jar_file', 'ejsapp'));

        $mform->addElement('hidden', 'class_file', null); // automatic sesskey protection
        $mform->setType('class_file', PARAM_TEXT);
        $mform->setDefault('class_file', 'null');

        $mform->addElement('hidden', 'codebase', null); // automatic sesskey protection
        $mform->setType('codebase', PARAM_TEXT);
        $mform->setDefault('codebase', 'null');

        $mform->addElement('hidden', 'mainframe', null); // automatic sesskey protection
        $mform->setType('mainframe', PARAM_TEXT);
        $mform->setDefault('mainframe', 'null');

        $mform->addElement('hidden', 'is_collaborative', null); // automatic sesskey protection
        $mform->setType('is_collaborative', PARAM_TEXT);
        $mform->setDefault('is_collaborative', 0);

        $maxbytes = get_max_upload_file_size($CFG->maxbytes);
		    $mform->addElement('filepicker', 'appletfile', get_string('file'), null, array('maxbytes' => $maxbytes, 'accepted_types' => 'application/jar'));
		    $mform->addRule('appletfile', get_string('appletfile_required', 'ejsapp'), 'required');
        $mform->addHelpButton('appletfile', 'appletfile', 'ejsapp');

    	  $mform->addElement('selectyesno', 'preserve_applet_size', get_string('preserve_applet_size', 'ejsapp'));
    	  $mform->addHelpButton('preserve_applet_size', 'preserve_applet_size', 'ejsapp');      
    	  
    	  $mform->addElement('selectyesno', 'is_rem_lab', get_string('is_rem_lab', 'ejsapp'));
    	  $mform->addHelpButton('is_rem_lab', 'is_rem_lab', 'ejsapp');  

		// -------------------------------------------------------------------------------
        // Add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        // -------------------------------------------------------------------------------
        // Add standard buttons, common to all modules
        $this->add_action_buttons();
    }


    function data_preprocessing(&$default_values)
    {
      global $DB, $CFG;
      $mform = $this->_form;

    	// Fill the file picker element with a previous submitted file
    	if ($this->current->instance) {
    		$draftitemid = file_get_submitted_draft_itemid('appletfile');
    		file_prepare_draft_area($draftitemid, $this->context->id, 'mod_ejsapp', 'jarfile', 0, array('subdirs'=>true));
    		$default_values['appletfile'] = $draftitemid;
    	}

      $content = $this->get_file_content('appletfile');
      
      if ($content) {
        $form_data = $this->get_data();
        
        // Create folders to store the .jar file
        if (!file_exists($CFG->dirroot . '/mod/ejsapp/jarfile/')) {
          mkdir($CFG->dirroot . '/mod/ejsapp/jarfile/', 0777);
        }
        if (!file_exists($CFG->dirroot . '/mod/ejsapp/jarfile/' . $form_data->course)) {
          mkdir($CFG->dirroot . '/mod/ejsapp/jarfile/' . $form_data->course, 0777);
        }
        $name = delete_non_alphanumeric_symbols($form_data->name);
        if (!file_exists($CFG->dirroot . '/mod/ejsapp/jarfile/' . $form_data->course . '/' . $name)) {
          mkdir($CFG->dirroot . '/mod/ejsapp/jarfile/' . $form_data->course . '/' . $name, 0777);
        }
        
        $applet_name = $this->get_new_filename('appletfile');
        
        // Store the .jar file in the proper folder
        $path = $CFG->dirroot . '/mod/ejsapp/jarfile/' . $form_data->course . '/' . $name . '/';
        $filename = $path . $applet_name;
        //$mform->save_file('appletfile', $path, true); 
        $fh = fopen($filename, 'w+') or die(get_string('file_error', 'ejsapp'));
        fwrite($fh, $content);
        fclose($fh);
        
        // Extract the manifest.mf file from the .jar
        //$fp = new zip_archive();
        //$success = $fp->open($filename, 1);
        $fp = get_file_packer();
        $success = $fp->extract_to_pathname($filename, $path . '/temp/');
        
        // Set the mod_form element
        $pattern = '/(\w+)[.]jar/';
        preg_match($pattern, $applet_name, $matches, PREG_OFFSET_CAPTURE);
        $applet_name = $matches[1][0];
        $mform->addElement('hidden', 'applet_name', null);
        $mform->setType('applet_name', PARAM_TEXT);
        $mform->setDefault('applet_name', $applet_name);
        $default_values['applet_name'] = $applet_name;
      } //if ($content)
    } //data_preprocessing
    
    
    //function definition

} //class mod_ejsapp_mod_form