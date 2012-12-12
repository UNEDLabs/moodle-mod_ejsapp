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
 * EJSApp Interface for IPAL (see http://www.compadre.org/ipal/)
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

function get_ejsapp_instances($course_id=null) {
    global $DB;
    if (is_null($course_id)) {
        $ejsapp_instances = $file_records = $DB->get_records('ejsapp', array());
    } else {
        $ejsapp_instances = $file_records = $DB->get_records('ejsapp', array('course'=>$course_id));
    }
    $result = array_values($ejsapp_instances);
    return $result;
}//get_ejsapp_instances

function get_ejsapp_states($ejsapp_id, $all_users=false) {
    global $DB,$USER;

    // get private state files
    if ($all_users) { //all users
        $all_state_files = $DB->get_records('files',
            array('mimetype' => 'application/xml',
                'filearea' => 'private',
                'component' => 'mod_ejsapp'
            )
        );
    } else { //just me
        $all_state_files = $DB->get_records('files',
            array('userid' => $USER->id,
                'mimetype' => 'application/xml',
                'filearea' => 'private',
                'component' => 'mod_ejsapp'
            )
        );
    }
    // get initial state files
    $all_state_files = array_merge($all_state_files,$DB->get_records('files',
        array('mimetype' => 'application/xml',
            'filearea' => 'xmlfiles',
            'component' => 'mod_ejsapp'
        )
    ));

    // filter state files by ejsappid
    $source = 'ejsappid='.$ejsapp_id;
    $state_files = array();
    foreach ($all_state_files as $key=>$value) {
        if (($value->itemid == $ejsapp_id) ||
            ($value->source == $source)) {
            $state_object = new stdClass();
            $state_object->state_name=$value->filename;
            $state_object->state_id =
                $value->contextid . "/" . $value->component . "/" .
                $value->filearea . "/" . $value->itemid . "/" .
                $value->filename;

            $state_files[] = $state_object;
        }
    }
    
    return $state_files;

}//get_ejsapp_states

function draw_ejsapp_instance($ejsapp_id, $state_file=null, $width=null, $height=null) {
    global $DB, $USER, $CFG;
    $ejsapp = $DB->get_record('ejsapp', array('id' => $ejsapp_id), '*', MUST_EXIST);

    $code = '<script "text/javascript">';

    // <set the applet size on the screen>
    if ($width && $height) {
        $code .= "var w = $width, h = $height;";
    } else {
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
    }
    // <\set the applet size on the screen>

    $code .= "document.write('<applet code=\"{$ejsapp->class_file}\"');";

    $context = get_context_instance(CONTEXT_USER, $USER->id);
    $language = current_language();
    $username = fullname($USER);
    $user_name = $USER->username;
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

    $code .= "document.write('</applet>');";

    $file_records = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'xmlfiles', 'itemid' => ($ejsapp->id)));
    if (empty($file_records)) {
        $initial_state_file = false;
    } else {
        foreach($file_records as $initial_state_file){
            if ($initial_state_file->filename != '.') {
                break;
            }
        }
    }
    if ($state_file || $initial_state_file) {
        //<to read the applet state, javascript must wait until the applet has been totally downloaded>
        if ($state_file) {
            $state_file = $CFG->wwwroot . "/pluginfile.php/" . $state_file;
        } else {
            $state_file = $CFG->wwwroot . "/pluginfile.php/" . $initial_state_file->contextid .
                "/" . $initial_state_file->component . "/" . $initial_state_file->filearea .
                "/" . $initial_state_file->itemid . "/" . $initial_state_file->filename;
        }
        $state_load_msg = get_string('state_load_msg', 'ejsapp');
        $state_fail_msg = get_string('state_fail_msg', 'ejsapp');
        $load_state_code = <<<EOC
    var applet = document.getElementById('{$ejsapp->applet_name}');

    function performAppletCode(count) {
	    if (!applet._readState && count > 0) {
	      window.setTimeout( function() { performAppletCode( --count ); }, 2000 );
      }
      else if (applet._readState) {
        applet._readState('url:$state_file');
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

} //draw_ejsapp_instance

// example of use
xdebug_var_dump(get_ejsapp_instances());

xdebug_var_dump(get_ejsapp_states(85, true));
echo draw_ejsapp_instance(85);
echo "\n";
echo draw_ejsapp_instance(85,'77/mod_ejsapp/private/0/Gyroscope_ruben.xml', 100, 200);

