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


class restore_ejsapp_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {
        $paths = array();
        $paths[] = new restore_path_element('ejsapp', '/activity/ejsapp');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_ejsapp($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // insert the ejsapp record
        $newitemid = $DB->insert_record('ejsapp', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
    
    	global $CFG,$DB;

      // Add ejsapp related files, no need to match by itemname (just internally handled context)
      $this->add_related_files('mod_ejsapp', 'content', null);

    	// restore ejsapp files:
      
		  $sql = "select * from {$CFG->prefix}ejsapp";
    	$ejsapp_records = $DB->get_records_sql($sql);
    	
		  foreach ($ejsapp_records as $ejsapp_record) {
			  // copy files
			  $sql = "select * from {$CFG->prefix}files where component = 'mod_ejsapp'	and filename = '{$ejsapp_record->applet_name}.jar'";
			  $file_records = $DB->get_records_sql($sql);
			  if ($file_records) {
			    foreach ($file_records as $file_record) {
			      $fs = get_file_storage();
			      $fileinfo = array(
					  'contextid' => $file_record->contextid,  // ID of context
					  'component' => 'mod_ejsapp',     	// usually = table name
					  'filearea' => 'content',          // usually = table name
					  'itemid' => 0,  // usually = ID of row in table
					  'filepath' => '/',                   // any path beginning and ending in /
					  'filename' => $file_record->filename);   // any filename
			      $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'],
  					$fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'],
	  				$fileinfo['filename']);
		  	    if ($file) {
              // create directories
				      if (!file_exists($CFG->dirroot . '/mod/ejsapp/jarfile/')) {
  				  	  mkdir($CFG->dirroot . '/mod/ejsapp/jarfile/', 0777);
  				    }   
  				    if (!file_exists($CFG->dirroot . '/mod/ejsapp/jarfile/' . $ejsapp_record->course)) {
					      mkdir($CFG->dirroot . '/mod/ejsapp/jarfile/' . $ejsapp_record->course, 0777);
  				    }
  				    if (!file_exists($CFG->dirroot . '/mod/ejsapp/jarfile/' . $ejsapp_record->course . '/' . $ejsapp_record->id)) {
	   				    mkdir($CFG->dirroot . '/mod/ejsapp/jarfile/' . $ejsapp_record->course . '/' . $ejsapp_record->id, 0777);
  		  		  }

	  				  // copy file
		  		    $file_content = $file->get_content();
			  	    $fh = fopen($CFG->dirroot . '/mod/ejsapp/jarfile/' . $ejsapp_record->course . '/' . $ejsapp_record->id . '/' .  $file_record->filename, 'w+') or die("can't open file");
  		    	  fwrite($fh, $file_content);
  				    fclose($fh);

	   			    // <update ejsapp table>
  				    mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die(mysql_error());
	   			    mysql_select_db($CFG->dbname) or die(mysql_error());
		  		    $codebase = '';
			  	    preg_match('/http:\/\/.+?\/(.+)/', $CFG->wwwroot, $match_result);
					    if (!empty($match_result) and $match_result[1]) {
				 		    $codebase .= '/' . $match_result[1];
				      }
				      $codebase .= '/mod/ejsapp/jarfile/' . $ejsapp_record->course . '/' . $ejsapp_record->id . '/';
  				    $sql = "update {$CFG->prefix}ejsapp set codebase='$codebase' where id='{$ejsapp_record->id}'";
  				    mysql_query($sql) or die(mysql_error());
				    } //if ($file)
				  } //foreach
			  } //if ($file_records)
		  } //foreach

    } //after_execute
    
} //class