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
        // Adding the rest of ejsapp settings by adding more fieldsets
        $mform->addElement('header', 'conf_parameters', get_string('jar_file', 'ejsapp'));

        $mform->addElement('hidden', 'class_file', null);
        $mform->setType('class_file', PARAM_TEXT);
        $mform->setDefault('class_file', 'null');

        $mform->addElement('hidden', 'codebase', null);
        $mform->setType('codebase', PARAM_TEXT);
        $mform->setDefault('codebase', 'null');

        $mform->addElement('hidden', 'mainframe', null);
        $mform->setType('mainframe', PARAM_TEXT);
        $mform->setDefault('mainframe', 'null');

        $mform->addElement('hidden', 'is_collaborative', null);
        $mform->setType('is_collaborative', PARAM_TEXT);
        $mform->setDefault('is_collaborative', 0);

        $maxbytes = get_max_upload_file_size($CFG->maxbytes);
		    $mform->addElement('filepicker', 'appletfile', get_string('file'), null, array('maxbytes' => $maxbytes, 'accepted_types' => 'application/jar'));
		    $mform->addRule('appletfile', get_string('appletfile_required', 'ejsapp'), 'required');
        $mform->addHelpButton('appletfile', 'appletfile', 'ejsapp');

        $select = &$mform->addElement('select', 'applet_size_conf', get_string('applet_size_conf','ejsapp'), array(get_string('preserve_applet_size','ejsapp'), get_string('moodle_resize','ejsapp'), get_string('user_resize','ejsapp')));
        $mform->addHelpButton('applet_size_conf', 'applet_size_conf', 'ejsapp');
        
        $mform->addElement('selectyesno', 'preserve_aspect_ratio', get_string('preserve_aspect_ratio', 'ejsapp'));
    	  $mform->addHelpButton('preserve_aspect_ratio', 'preserve_aspect_ratio', 'ejsapp');
    	  $mform->disabledIf('preserve_aspect_ratio', 'applet_size_conf', 'eq', 0);
    	  $mform->disabledIf('preserve_aspect_ratio', 'applet_size_conf', 'eq', 1);
        
        $mform->addElement('text', 'custom_width', get_string('custom_width', 'ejsapp'),array('size'=>'3'));
        $mform->setType('custom_width', PARAM_INT);
        $mform->disabledIf('custom_width', 'applet_size_conf', 'eq', 0);
        //$mform->disabledIf('custom_width', 'applet_size_conf', 'eq', 1);
        
        $mform->addElement('text', 'custom_height', get_string('custom_height', 'ejsapp'),array('size'=>'3'));
        $mform->setType('custom_height', PARAM_INT);
    	  $mform->disabledIf('custom_height', 'applet_size_conf', 'eq', 0);
    	  $mform->disabledIf('custom_height', 'applet_size_conf', 'eq', 1);
    	  $mform->disabledIf('custom_height', 'preserve_aspect_ratio', 'eq', 1);
    	  
    	  $mform->addElement('editor', 'ejsapp', get_string('appwording', 'ejsapp'), null, array('subdirs'=>1, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'changeformat'=>1, 'context'=>$this->context, 'noclean'=>1, 'trusttext'=>0));
        $mform->setType('ejsapp', PARAM_RAW);
		    // -------------------------------------------------------------------------------
		    // Adding elements to configure the remote lab, if that's the case 
		    $mform->addElement('header', 'rem_lab', get_string('rem_lab_conf', 'ejsapp'));
		    
		    $mform->addElement('selectyesno', 'is_rem_lab', get_string('is_rem_lab', 'ejsapp'));
    	  $mform->addHelpButton('is_rem_lab', 'is_rem_lab', 'ejsapp'); 
		    
		    $mform->addElement('text', 'ip_lab', get_string('ip_lab', 'ejsapp'),array('size'=>'12'));
        $mform->setType('ip_lab', PARAM_TEXT);
        $mform->addRule('ip_lab', get_string('maximumchars', '', 15), 'maxlength', 15, 'client');
        $mform->setDefault('ip_lab', '127.0.0.1');
        $mform->addHelpButton('ip_lab', 'ip_lab', 'ejsapp');
        $mform->disabledIf('ip_lab', 'is_rem_lab', 'eq', 0);
		    
        $mform->addElement('text', 'totalslots', get_string('totalslots', 'ejsapp'),array('size'=>'2'));
        $mform->setType('totalslots', PARAM_INT);
        $mform->setDefault('totalslots', '10');
        $mform->addRule('totalslots', get_string('maximumchars', '', 5), 'maxlength', 5, 'client');
        $mform->addHelpButton('totalslots', 'totalslots', 'ejsapp');
        $mform->disabledIf('totalslots', 'is_rem_lab', 'eq', 0);
        
        $mform->addElement('text', 'weeklyslots', get_string('weeklyslots', 'ejsapp'),array('size'=>'2'));
        $mform->setType('weeklyslots', PARAM_INT);
        $mform->setDefault('weeklyslots', '8');
        $mform->addRule('weeklyslots', get_string('maximumchars', '', 3), 'maxlength', 3, 'client');
        $mform->addHelpButton('weeklyslots', 'weeklyslots', 'ejsapp');
        $mform->disabledIf('weeklyslots', 'is_rem_lab', 'eq', 0);
        
        $mform->addElement('text', 'dailyslots', get_string('dailyslots', 'ejsapp'),array('size'=>'2'));
        $mform->setType('dailyslots', PARAM_INT);
        $mform->setDefault('dailyslots', '4');
        $mform->addRule('dailyslots', get_string('maximumchars', '', 2), 'maxlength', 2, 'client');
        $mform->addHelpButton('dailyslots', 'dailyslots', 'ejsapp');
        $mform->disabledIf('dailyslots', 'is_rem_lab', 'eq', 0);
        
        $mform->setAdvanced('rem_lab');
		    // -------------------------------------------------------------------------------
        // Add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        // -------------------------------------------------------------------------------
        // Add standard buttons, common to all modules
        $this->add_action_buttons(); 
    } //function definition


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
        //$this->save_file('appletfile', $path, true);
        //$mform->save_stored_file('appletfile', $newcontextid, $newcomponent, $newfilearea, $newitemid, $newfilepath='/', $newfilename=null, $overwrite=false, $newuserid=null);
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
           
        //Check the data provided by the user
        if ($form_data->applet_size_conf == 2) {
          //$mform->addRule('custom_width', get_string('custom_width_required', 'ejsapp'), 'required');
          if ($form_data->custom_width == null) {
            //$form_data->applet_size_conf = 0;
            //$form_data->custom_width = 600;
            echo get_string('custom_width_required', 'ejsapp');
          }
          if ($form_data->preserve_aspect_ratio == 0) {  
            //$mform->addRule('custom_height', get_string('custom_height_required', 'ejsapp'), 'required');
            if ($form_data->custom_width == null) {
              //$form_data->applet_size_conf = 0;
              //$form_data->custom_height = 400;
              echo get_string('custom_height_required', 'ejsapp');
            }
          }
        }
        if ($form_data->is_rem_lab == 1) {
          //$mform->addRule('ip_lab', get_string('ip_lab_required', 'ejsapp'), 'required');
          if ($form_data->ip_lab == null) {
            //$form_data->ip_lab = "127.0.0.1"; 
            echo get_string('ip_lab_required', 'ejsapp');
          }
        }  
      } //if ($content)
      
      if ($this->current->instance) {
        $draftitemid = file_get_submitted_draft_itemid('ejsapp');
        $default_values['ejsapp']['format'] = $default_values['appwordingformat'];
        $default_values['ejsapp']['text'] = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_ejsapp', 'appwording', 0, array('subdirs'=>1, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'changeformat'=>1, 'context'=>$this->context, 'noclean'=>1, 'trusttext'=>0), $default_values['appwording']);
        $default_values['ejsapp']['itemid'] = $draftitemid;
      }           
    } //data_preprocessing
    
    
    /*function validation($data, $files) {     
      
    }*/

} //class mod_ejsapp_mod_form