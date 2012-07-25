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
 * Common operations when adding and updating an EJSApp activity instance. 
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once('locallib.php');

function update_db($ejsapp, $contextid)
{

    global $CFG, $DB, $USER;

    $path = $CFG->dirroot . '/mod/ejsapp/jarfile/' . $ejsapp->course . '/' .
    delete_non_alphanumeric_symbols($ejsapp->name) . '/';
    $new_path = $CFG->dirroot . '/mod/ejsapp/jarfile/' . $ejsapp->course . '/' . $ejsapp->id . '/';

    if (file_exists($new_path)) { // the ejsapp has been renamed or  the applet has been updated
        delete_recursively($new_path);
    }

    rename($path, $new_path);

  	$applet_name=$ejsapp->applet_name;
  	
    // Open manifest
    $manifest_filename = $new_path . 'temp/' . 'META-INF/MANIFEST.MF';
    $fh = fopen($manifest_filename, 'r') or die(get_string('manifest_error', 'ejsapp'));
    $manifest = fread($fh, filesize($manifest_filename));
    fclose($fh);
    
    // Get params
    
    // class_file
 		$pattern = '/Main-Class\s*:\s*(.+)\s*/';
 		preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
 		$sub_str = $matches[1][0];
 		if (strlen($matches[1][0]) == 59) { 
 		  $pattern = '/^\s(.+)\s*/m';
 		  if ((preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE) > 0)) {   
 		    $sub_str = $sub_str . $matches[1][0];
      }
 	  }
 		$class_file = $sub_str . 'Applet.class';
 		$class_file = preg_replace('/\s+/', "", $class_file); // delete all white-spaces and the first newline 
 		
    // codebase
    $codebase = '';
	  preg_match('/http:\/\/.+?\/(.+)/', $CFG->wwwroot, $match_result);
	  if (!empty($match_result) and $match_result[1]) {
   		$codebase .= '/' . $match_result[1];
    }
	  $codebase .= '/mod/ejsapp/jarfile/' . $ejsapp->course . '/' . $ejsapp->id . '/';
	  
	  // mainframe
    $pattern = '/Main-Frame\s*:\s*(.+)\s*/';
    preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
    if (count($matches)==0) {
      $mainframe = '';
    } else {
      $mainframe = $matches[1][0];
    	$mainframe = preg_replace('/\s+/', "", $mainframe); // delete all white-spaces
    }
    
    // is_collaborative
    $pattern = '/Is-Collaborative\s*:\s*(\w+)/';
    preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
    if (count($matches)==0) {
      $is_collaborative = 0;
    }else {
      $is_collaborative = trim($matches[1][0]);
      if ($is_collaborative == 'true') {
        $is_collaborative = 1;
      } else {
        $is_collaborative = 0;
      }
    }
    
	  // height
	  $pattern = '/Applet-Height\s*:\s*(\w+)/';
	  preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
	  if (count($matches)==0) {
  		$height = 0;
  	} else {
  		$height = $matches[1][0];
  		$height = preg_replace('/\s+/', "", $height); // delete all white-spaces
  	}
  	
  	// width
  	$pattern = '/Applet-Width\s*:\s*(\w+)/';
  	preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
  	if (count($matches)==0) {
  		$width = 0;
  	} else {
  		$width = $matches[1][0];
  		$width = preg_replace('/\s+/', "", $width); // delete all white-spaces
  	}
  	
  	// <update files table>       
    $fs = get_file_storage();
    // Prepare file record object
    $fileinfo = array(
        'contextid' => $contextid, // ID of context
        'component' => 'mod_ejsapp', // usually = table name
        'filearea' => 'jarfile', // usually = table name
        'itemid' => 0, // usually = ID of row in table
        'filepath' => '/' . $ejsapp->course . '/' . $ejsapp->id . '/', // any path beginning and ending in /
        'filename' => $applet_name . '.jar'); // any filename
    // Create the stored file
    $uploaded_file = $new_path . $applet_name . '.jar';
    $fs->create_file_from_pathname($fileinfo, $uploaded_file);
    // </update files table>
    
    $ejsapp->class_file = $class_file;
    $ejsapp->codebase = $codebase;
    $ejsapp->mainframe = $mainframe;
    $ejsapp->is_collaborative = $is_collaborative;
    $ejsapp->height = $height;
    $ejsapp->width = $width;
    $DB->update_record('ejsapp', $ejsapp);
    
} //update_db

?>