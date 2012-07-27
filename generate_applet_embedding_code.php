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
 *
 * This file is generates the code that embeds the EJS applet into Moodle. It is
 * used for three different cases: 1) when only the EJSApp activity is being
 * used, 2) when the EJSApp File Browser is used to load a state file, and
 * 3) when the EJSApp Collab Session is used.
 *  
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ('../../config.php');
require_login();
require_once('manage_tmp_state_files.php');


if (is_block_installed('ejsapp_collab_session')) {
	require_once($CFG->dirroot . '/blocks/ejsapp_collab_session/manage_collaborative_db.php' );
}

global $DB, $USER, $CFG, $COURSE;

function is_block_installed($block_name){
		global $DB, $CFG;
		$sql = "select * from {$CFG->prefix}block where name='{$block_name}'";
		$records = $DB->get_records_sql($sql);
		return (count($records) > 0);
}


function generate_applet_embedding_code($ejsapp,
	$state_file, // caller = ejsapp_file_browser
	$master_user, $col_session // caller = ejsapp_collab_session
	)
{

  global $USER, $CFG, $DB;

  $code = '';
  $code .= '<script "text/javascript">';

	// <set the applet size on the screen>
	switch ($ejsapp->applet_size_conf) {
	  case 0:
		  $code .= "var w = {$ejsapp->width}, h = {$ejsapp->height};";
		  break;
	  case 1:
		  $code .= "var w = 630, h = 460;
		  if (window.innerWidth)
      w = window.innerWidth;
      else if (document.body && document.body.offsetWidth)
      w = document.body.offsetWidth;
      if (document.body && document.body.clientWidth)
      w= document.body.clientWidth;
      else if (document.compatMode=='CSS1Compat' &&
      document.documentElement &&
      document.documentElement.offsetWidth )
      w = document.documentElement.offsetWidth;
      else if (document.documentElement &&
      document.documentElement.clientWidth)
      w = document.documentElement.clientWidth;
      w = w - $CFG->columns_width;
      h = w*{$ejsapp->height}/{$ejsapp->width};";
      //h = screen.availHeight*(w/screen.availWidth);";
      break;
    case 2:
      if($ejsapp->preserve_aspect_ratio == 0) {
        $code .= "var w = {$ejsapp->custom_width}, h = {$ejsapp->custom_height};";
      } else {
        $code .= "var w = {$ejsapp->custom_width}, h = w*{$ejsapp->height}/{$ejsapp->width};";
      }
      break;
	}
	// <\set the applet size on the screen>

  if ($col_session && !am_i_master_user()) {
   	$class_file = $ejsapp->class_file;
   	$class_file = str_replace(".class", "Student.class", $class_file);
   	$code .= "document.write('<applet code=\"$class_file\"');";
  } else {
   	$code .= "document.write('<applet code=\"{$ejsapp->class_file}\"');";
  }
  $context = get_context_instance(CONTEXT_USER, $USER->id);
  $language = current_language();
  $user_name = fullname($USER);            //For collab
  /*$username = new stdClass();
  username = $USER->name;                  //For checking Moodle connection*/
	$code .= "document.write('codebase=\"{$ejsapp->codebase}\"');
  document.write('archive=\"{$ejsapp->applet_name}.jar\"');
  document.write('name=\"{$ejsapp->applet_name}\"');
  document.write('id=\"{$ejsapp->applet_name}\"');
  document.write('width=\"'+w+'\"');
  document.write('height=\"'+h+'\">');
  document.write('<param name=\"context_id\" value=\"{$context->id}\"/>');
	document.write('<param name=\"user_id\" value=\"{$USER->id}\"/>');
	document.write('<param name=\"ejsapp_id\" value=\"{$ejsapp->id}\"/>');
	document.write('<param name=\"language\" value=\"$language\"/>');
	document.write('<param name=\"username\" value=\"$user_name\"/>');
	document.write('<param name=\"password\" value=\"{$USER->password}\"/>');
	document.write('<param name=\"moodle_upload_file\" value=\"{$CFG->wwwroot}/mod/ejsapp/upload_file.php\"/>');";
	
  if ($col_session) {
  	$port = get_port($master_user->collaborative_session_where_user_participates);
		$code .= "document.write('<param name=\"is_collaborative\" value=\"true\"/>');
		document.write('<param name=\"Port_Teacher\" value=\"$port\"/>');";
		if (am_i_master_user()) {
			$code .= "document.write('<param name=\"directorname\" value=\"$user_name\"/>');";
		}else{
			insert_collaborative_user($USER->id, null, $col_session);
			$code .= "document.write('<param name=\"IP_Teacher\" value=\"{$master_user->ip}\"/>');
			document.write('<param name=\"MainFrame_Teacher\" value=\"{$ejsapp->mainframe}\"/>');";
		}
	} else {
		$code .= "document.write('<param name=\"is_collaborative\" value=\"false\"/>');";
	} //col_session
	
	$code .= "document.write('</applet>');";

  if ($state_file) {
    //<to read the applet state, javascript must wait until the applet has been totally downloaded>
    $state_load_msg = get_string('state_load_msg', 'ejsapp');
    $state_fail_msg = get_string('state_fail_msg', 'ejsapp');
    $load_state_code = <<<EOC
    var applet = document.getElementById('{$ejsapp->applet_name}');

    function performAppletCode(count) {
	    //if (count == 10) {alert('$state_load_msg')};
	    if (!applet._readState && count > 0) {
	      window.setTimeout( function() { performAppletCode( --count ); }, 2000 );
      }
      else if (applet._readState) {
        applet._readState('url:$state_file');
        //applet._view.resetTraces();
        //applet._view.clearData();
        //applet._view.clearElements();
        //applet._view.resetElements();
      }
      else {
      alert('$state_fail_msg');
      }
    }

    performAppletCode(10);
EOC;
    //<\to read the applet state, javascript must wait until the applet has been totally downloaded>
		$code .= $load_state_code;
    } //end of if ($state_file)

    $code .= '</script>';

    return $code;  
    
} //end of generate_applet_embedding_code
     
     
if (!array_key_exists('caller', $_GET)) {
	$caller = 'ejsapp';
} else {
	$caller = $_GET['caller'];
}


switch ($caller) {

	case 'ejsapp':
		// do nothing
		break;

	case 'ejsapp_file_browser':
	  $state_file_id = required_param('state_file_id', PARAM_INT);
	  $ejsapp_id = required_param('ejsapp_id', PARAM_INT);
		$ejsapp = $DB->get_record('ejsapp', array('id' => $_GET['ejsapp_id']));

	  $course = $DB->get_record('course', array('id' => $ejsapp->course), '*', MUST_EXIST);
	  $cm     = get_coursemodule_from_instance('ejsapp', $ejsapp->id, $course->id, false, MUST_EXIST);
	  require_login($course, true, $cm);
	  add_to_log($course->id, 'ejsapp', 'view', "view.php?id=$cm->id", $ejsapp->name, $cm->id); 
	  $PAGE->set_title($ejsapp->name);
	  $PAGE->set_heading($course->fullname);

		// get state_file and store it into the folder '/mod/ejsapp/tmp_state_files/'
		store_tmp_state_file($state_file_id);

		$context = get_context_instance(CONTEXT_COURSE, $ejsapp->course);
		$PAGE->set_url('/mod/ejsapp/generate_applet_embedding_code.php', array('caller' => $caller, 'ejsapp_id' => $ejsapp->id, 'context_id' => $context->id, 'state_file_id' => $state_file_id));
    $PAGE->set_button(update_module_button($cm->id, $course->id, get_string('modulename', 'ejsapp')));
		$PAGE->set_pagelayout('incourse'); 
		echo $OUTPUT->header();
		
		$tmp_state_files_path = $CFG->wwwroot . '/mod/ejsapp/tmp_state_files/';
		$state_file_tmp_name = $tmp_state_files_path . $state_file_id . '.xml';
		echo $OUTPUT->heading(generate_applet_embedding_code($ejsapp, $state_file_tmp_name, null, null));
		
		echo $OUTPUT->footer();
		break;

	case 'ejsapp_collab_session':
		require("{$CFG->dirroot}/blocks/ejsapp_collab_session/init_page.php");
		$session = required_param('session', PARAM_INT);
		$courseid = required_param('courseid', PARAM_INT);
		$contextid = required_param('contextid', PARAM_INT);
		$PAGE->set_url('/mod/ejsapp/generate_applet_embedding_code.php', array('caller' => $caller, 'session' => $session, 'courseid' => $courseid, 'contextid' => $contextid));

		$ejsapp = get_ejsapp_object($session);

	  $course = $DB->get_record('course', array('id' => $ejsapp->course), '*', MUST_EXIST);
	  $cm     = get_coursemodule_from_instance('ejsapp', $ejsapp->id, $course->id, false, MUST_EXIST);
	  require_login($course, true, $cm);
	  add_to_log($course->id, 'ejsapp', 'view', "view.php?id=$cm->id", $ejsapp->name, $cm->id);
	  $PAGE->set_title($ejsapp->name);
	  $PAGE->set_heading($course->fullname); 
		//$PAGE->set_context($contextid);
    //$PAGE->set_button(update_module_button($cm->id, $course->id, get_string('modulename', 'ejsapp')));
		//$PAGE->set_pagelayout('incourse'); 
    //$PAGE->navbar->add($ejsapp->name);
		//echo $OUTPUT->header();

		$master_user = get_master_user_object($session);

		$page_caller = $ejsapp->name;

		echo $OUTPUT->heading(generate_applet_embedding_code($ejsapp,null,$master_user, $session));

		$close_url = $CFG->wwwroot .
			"/blocks/ejsapp_collab_session/close_collaborative_session.php?session=$session&courseid=$courseid&contextid=$contextid";

		if (am_i_master_user()) {
			$close_button = get_string('closeMasSessBut', 'block_ejsapp_collab_session');
		} else {
			$close_button = get_string('closeStudSessBut', 'block_ejsapp_collab_session');
		}

    $button = <<<EOD
    <center>
    <form>
    <input type="button" value="$close_button" onClick="window.location.href = '  $close_url'">
    </form>
    </center>
EOD;

		echo $button;

		echo $OUTPUT->footer();
		break;
		
} // end of switch   