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
 * This file generates the code that embeds the EJS applet into Moodle. It is
 * used for three different cases: 1) when only the EJSApp activity is being
 * used, 2) when the EJSApp File Browser is used to load a state file, and
 * 3) when the EJSApp Collab Session is used.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($DB->record_exists('block', array('name' => 'ejsapp_collab_session'))) {
    require_once($CFG->dirroot . '/blocks/ejsapp_collab_session/manage_collaborative_db.php');
}

/**
 * Returns the code that embeds an EJS applet into Moodle
 *
 * This function returns the HTML and JavaScript code that embeds an EJS applet into Moodle
 * It is used for three different cases:
 *      1) when only the EJSApp activity is being used
 *      2) when the EJSApp File Browser is used to load a state file
 *      3) when the EJSApp Collab Session is used
 *
 * @param stdClass $ejsapp record from table ejsapp
 * @param int|null $sarlabinstance sarlab id (null if sarlab is not used)
 * @param int|null $practiceid practice id (null if sarlab is not used)
 * @param string|null $state_file if generate_applet_embedding_code is called from block ejsapp_file_browser it is the name of the xml file that stores the state of an EJS applet, elsewhere it is null
 * @param int|null $col_session if generate_applet_embedding_code is called from block ejsapp_collab_sessioncollaborative it is the session id, elsewhere it is null
 * @param string|null $col_ip if generate_applet_embedding_code is called from block ejsapp_collab_sessioncollaborative it is the IP of the collaborative session master user, elsewhere it is null
 * @param int|null $col_port if generate_applet_embedding_code is called from block ejsapp_collab_sessioncollaborative it is the port of the collaborative session master user, elsewhere it is null
 * @param int|null $session_director if generate_applet_embedding_code is called from block ejsapp_collab_sessioncollaborative it is the id of the collaborative session master user, elsewhere it is null
 * @return string code that embeds an EJS applet into Moodle
 */
function generate_applet_embedding_code($ejsapp, $sarlabinstance, $practiceid, $state_file, $col_session, $col_ip, $col_port, $session_director)
{
    global $DB, $USER, $CFG;

    if ($practiceid) { // Sarlab is used to access this remote lab
        $time = time();
        $year = date("Y", $time);
        $month = date("n", $time);
        $day = date("j", $time);
        $hour = date("G", $time);
        $min = date("i", $time);
        $seg = date("s", $time);
        $time = mktime($hour, $min, $seg, $month, $day, $year);
        $DB->delete_records('ejsapp_sarlab_keys', array('user' => $USER->username, 'creationtime' => $time - 5));
        mt_srand(time());
        $random = mt_rand(0, 1000000);
        $sarlab_key = sha1($year . $month . $day . $hour . $min . $seg . $practiceid . fullname($USER) . $USER->username . $random);

        $new_sarlab_key = new stdClass();
        $new_sarlab_key->user = $USER->username;
        $new_sarlab_key->sarlabpass = $sarlab_key;
        $new_sarlab_key->creationtime = $time;
        $DB->insert_record('ejsapp_sarlab_keys', $new_sarlab_key);

        $list_sarlab_IPs = explode(";", $CFG->sarlab_IP);
        $list_sarlab_ports = explode(";", $CFG->sarlab_port);
        $sarlab_IP = $list_sarlab_IPs[$sarlabinstance];
        $sarlab_port = $list_sarlab_ports[$sarlabinstance];
    }

    $code = '<script "text/javascript">';

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
            break;
        case 2:
            if ($ejsapp->preserve_aspect_ratio == 0) {
                $code .= "var w = {$ejsapp->custom_width}, h = {$ejsapp->custom_height};";
            } else {
                $code .= "var w = {$ejsapp->custom_width}, h = w*{$ejsapp->height}/{$ejsapp->width};";
            }
            break;
    }
    // <\set the applet size on the screen>

    if ($col_session && !$session_director) {
        $class_file = $ejsapp->class_file;
        $class_file = str_replace(".class", "Student.class", $class_file);
        $code .= "document.write('<applet code=\"$class_file\"');";
    } else {
        $code .= "document.write('<applet code=\"{$ejsapp->class_file}\"');";
    }

    $context = get_context_instance(CONTEXT_USER, $USER->id);
    $language = current_language();
    $username = fullname($USER); //For collab
    $user_name = $USER->username; //For checking Moodle connection*/
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
	document.write('<param name=\"username\" value=\"$username\"/>');
	document.write('<param name=\"user_name\" value=\"$user_name\"/>');
	document.write('<param name=\"password\" value=\"{$USER->password}\"/>');
	document.write('<param name=\"moodle_upload_file\" value=\"{$CFG->wwwroot}/mod/ejsapp/upload_file.php\"/>');";

    if ($col_session) {
        $code .= "document.write('<param name=\"is_collaborative\" value=\"true\"/>');
		document.write('<param name=\"Port_Teacher\" value=\"$col_port\"/>');";
        if ($session_director) {
            $code .= "document.write('<param name=\"directorname\" value=\"$username\"/>');";
        } else {
            insert_collaborative_user($USER->id, null, $col_session);
            $code .= "document.write('<param name=\"IP_Teacher\" value=\"$col_ip\"/>');
			document.write('<param name=\"MainFrame_Teacher\" value=\"{$ejsapp->mainframe}\"/>');";
        }
    } else {
        $code .= "document.write('<param name=\"is_collaborative\" value=\"false\"/>');";
    } //col_session

    if ($practiceid) {
        $code .= "document.write('<param name=\"ipserver\" value=\"{$sarlab_IP}\"/>');
	  document.write('<param name=\"portserver\" value=\"{$sarlab_port}\"/>');
	  document.write('<param name=\"idExp\" value=\"$practiceid\"/>');
	  document.write('<param name=\"user\" value=\"EJSApp\"/>');
	  document.write('<param name=\"passwd\" value=\"$sarlab_key\"/>');";
    }

    $code .= "document.write('</applet>');";

    $file_records = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'xmlfiles', 'itemid' => ($ejsapp->id)));
    foreach($file_records as $file_record){
      if ($file_record->filename != '.') {
        break;
      }  
    }
    $original_state_file = $CFG->wwwroot . "/pluginfile.php/" . $file_record->contextid . 
                          "/" . $file_record->component . "/" . $file_record->filearea . 
                          "/" . $file_record->itemid . "/" . $file_record->filename;
    if ($state_file || $file_record) {
        //<to read the applet state, javascript must wait until the applet has been totally downloaded>
        if ($state_file) {
          $state_file = $CFG->wwwroot . "/pluginfile.php/" . $state_file;
        } else {
          $state_file = $original_state_file;
        }
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