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
 * This file generates the code that embeds the EJS application into Moodle.
 * It is used for three different cases: 1) when only the EJSApp activity is
 * being used, 2) when the EJSApp File Browser is used to load a state or rec
 * file, and 3) when the EJSApp Collab Session is used.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the code that embeds an EJS applet into Moodle
 *
 * This function returns the HTML and JavaScript code that embeds an EJS applet into Moodle
 * It is used for four different cases:
 *      1) when only the EJSApp activity is being used
 *      2) when the EJSApp File Browser is used to load a state file
 *      3) when the EJSApp Collab Session is used
 *      4) when third party plugins want to display EJS applets in their own activities by means of the EJSApp external interface
 *
 * @param stdClass $ejsapp record from table ejsapp
 * @param stdClass|null $sarlabinfo 
 *                                  $sarlabinfo->instance: int sarlab id, 
 *                                  $sarlabinfo->practice: int practice id, 
 *                                  $sarlabinfo->collab:   int collab whether sarlab offers collab access to this remote lab (1) or not (0), 
 *                                  Null if sarlab is not used
 *                                  $sarlabinfo->labmanager: int laboratory manager (1) or student (0)
 * @param array|null $data_files
 *                                  $data_files[0]: state_file, if generate_applet_embedding_code is called from block ejsapp_file_browser, this is the name of the .xml file that stores the state of an EJS applet, elsewhere it is null
 *                                  $data_files[1]: cnt_file, if generate_applet_embedding_code is called from block ejsapp_file_browser, this is the name of the .cnt file that stores the code of the controller used within an EJS applet, elsewhere it is null
 *                                  $data_files[2]: rec_file, if generate_applet_embedding_code is called from block ejsapp_file_browser, this is the name of the .rec file that stores the script with the recording of the interaction with an EJS applet, elsewhere it is null
 * @param stdClass|null $collabinfo 
 *                                  $collabinfo->session: int collaborative session id, 
 *                                  $collabinfo->ip: string collaborative session ip, 
 *                                  $collabinfo->localport: int collaborative session local port,
 *                                  $collabinfo->sarlabport: int|null sarlab port,
 *                                  $collabinfo->director: int|null id of the collaborative session master user, `
 *                                  Null if generate_applet_embedding_code is not called from block ejsapp_collab_session
 * @param stdClass|null $personalvarsinfo
 *                                  $personalvarsinfo->name: string[] name(s) of the EJS variable(s),
 *                                  $personalvarsinfo->value: double[] value(s) of the EJS variable(s),
 *                                  $personalvarsinfo->type: string[] type(s) of the EJS variable(s),
 *                                  Null if no personal variables were defined for this EJSApp
 * @param stdClass|null $external_size
 *                                  $external_size->width: int value (in pixels) for the width of the applet to be drawn
 *                                  $external_size->height: int value (in pixels) for the height of the applet to be drawn  
 *                                  Null if generate_applet_embedding_code is not called from the external interface (draw_ejsapp_instance() function)

 * @return string code that embeds an EJS applet into Moodle
 */
function generate_applet_embedding_code($ejsapp, $sarlabinfo, $data_files, $collabinfo, $personalvarsinfo, $external_size)
{
    global $DB, $USER, $CFG;

    if (!is_null($data_files)) {
        $state_file = $data_files[0];
        $cnt_file = $data_files[1];
        $rec_file = $data_files[2];
    }

    if ($sarlabinfo || isset($collabinfo->sarlabport)) {    // Sarlab is used to access this remote lab or to establish communication between users
        $time = time();                                     // participating in a collaborative session
        $year = date("Y", $time);
        $month = date("n", $time);
        $day = date("j", $time);
        $hour = date("G", $time);
        $min = date("i", $time);
        $seg = date("s", $time);
        $time = mktime($hour, $min, $seg, $month, $day, $year);
        $DB->delete_records('ejsapp_sarlab_keys', array('user' => $USER->username)); //WARNING: This also deletes keys for collab sessions with Sarlab!!
        mt_srand(time());
        $random = mt_rand(0, 1000000);
        if ($sarlabinfo) $sarlab_key = sha1($year . $month . $day . $hour . $min . $seg . $sarlabinfo->practice . fullname($USER) . $USER->username . $random);
        else $sarlab_key = sha1($year . $month . $day . $hour . $min . $seg . "EJS Collab" . fullname($USER) . $USER->username . $random);

        $new_sarlab_key = new stdClass();
        $new_sarlab_key->user = $USER->username;
        $new_sarlab_key->sarlabpass = $sarlab_key;
        $new_sarlab_key->labmanager = $sarlabinfo->labmanager;
        $new_sarlab_key->creationtime = $time;

        $DB->insert_record('ejsapp_sarlab_keys', $new_sarlab_key);

        if ($sarlabinfo) {
            $list_sarlab_IPs = explode(";", $CFG->sarlab_IP);
            $sarlab_IP = $list_sarlab_IPs[$sarlabinfo->instance];
            $init_pos = strpos($sarlab_IP, "'");
            $end_pos = strrpos($sarlab_IP, "'");
            if( !(($init_pos === false) || ($init_pos === $end_pos)) ) {
                $sarlab_IP = substr($sarlab_IP,$end_pos+1);
            }
            $list_sarlab_ports = explode(";", $CFG->sarlab_port);
            $sarlab_port = $list_sarlab_ports[$sarlabinfo->instance];
        } else {
            $sarlab_IP = $collabinfo->ip;
            $sarlab_port =  $collabinfo->sarlabport;
        }
    }

    if ($ejsapp->class_file == '') { //EJS Javascript

        $path = $CFG->wwwroot . $ejsapp->codebase;
        $code = file_get_contents($path . $ejsapp->applet_name);

    } else { //EJS Applet

        $code = '<div id = "EJsS"><script type="text/javascript">';

        // <set the applet size on the screen>
        if (isset($external_size->width)) {
          $code .= " var w = $external_size->width, h = $external_size->height;";
        } else {
          switch ($ejsapp->applet_size_conf) {
              case 0:
                  $code .= " var w = {$ejsapp->width}, h = {$ejsapp->height};";
                  break;
              case 1:
                  $code .= " h_max = 460;
                    if (window.innerWidth && window.innerHeight) {
                        h_max = window.innerHeight;
                    } else if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth && document.documentElement.offsetHeight) {
                        h_max = document.documentElement.offsetHeight;
                    } else if (document.documentElement && document.documentElement.clientWidth && document.documentElement.clientHeight) {
                        h_max = document.documentElement.clientHeight;
                    }
                    w = document.getElementById('EJsS').offsetWidth
                    h = w*{$ejsapp->height}/{$ejsapp->width};
                    if (h > h_max) {
                        h = 0.93*h_max;
                        w = h*{$ejsapp->width}/{$ejsapp->height};
                    }";
                  break;
              case 2:
                  if ($ejsapp->preserve_aspect_ratio == 0) {
                      $code .= " var w = {$ejsapp->custom_width}, h = {$ejsapp->custom_height};";
                  } else {
                      $code .= " var w = {$ejsapp->custom_width}, h = w*{$ejsapp->height}/{$ejsapp->width};";
                  }
                  break;
          }
        }
        // <\set the applet size on the screen>

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /*$file_record = $DB->get_record('files', array('filename' => $ejsapp->applet_name.'.jar', 'component' => 'mod_ejsapp', 'filearea' => 'jarfiles', 'itemid' => $ejsapp->id));
        $app_codebase = $CFG->wwwroot . "/pluginfile.php/" . $file_record->contextid . "/" . $file_record->component . "/" . $file_record->filearea . "/" . $file_record->itemid . $file_record->filepath;
        $ejsapp->codebase = $app_codebase;// . $file_record->filename;*/
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        if ($collabinfo && !isset($collabinfo->director)) { // Invited users to collaborative sessions
            $class_file = $ejsapp->class_file;
            $class_file = str_replace(".class", "Student.class", $class_file);
        } else { // Rest of cases
            $class_file = $ejsapp->class_file;
        }
        $code .= "document.write('<applet code=\"{$class_file}\"');";

        $context = context_user::instance($USER->id);
        $language = current_language();
        $username = fullname($USER); //For collab

        $permissions = 'sandbox';
        if ($ejsapp->is_rem_lab == 1) $permissions = 'all-permissions';
        $ext = '.jar';
        if (pathinfo($ejsapp->applet_name, PATHINFO_EXTENSION) == 'jar') $ext = '';
        $code .= "document.write(' codebase=\"{$ejsapp->codebase}\"');
                  document.write(' id=\"{$ejsapp->applet_name}\"');
                  document.write(' width=\"'+w+'\"');
                  document.write(' height=\"'+h+'\">');
                  document.write('<param name=\"cache_archive\" value=\"{$ejsapp->applet_name}$ext\">');
                  document.write('<param name=\"permissions\" value=\"{$permissions}\"/>');
                  document.write('<param name=\"codebase_lookup\" value=\"false\"/>');
                  document.write('<param name=\"context_id\" value=\"{$context->id}\"/>');
                  document.write('<param name=\"user_id\" value=\"{$USER->id}\"/>');
                  document.write('<param name=\"ejsapp_id\" value=\"{$ejsapp->id}\"/>');
                  document.write('<param name=\"language\" value=\"$language\"/>');
                  document.write('<param name=\"username\" value=\"$username\"/>');
                  document.write('<param name=\"user_moodle\" value=\"{$USER->username}\"/>');
                  document.write('<param name=\"password_moodle\" value=\"DEPRECATED\"/>');
                  document.write('<param name=\"moodle_upload_file\" value=\"{$CFG->wwwroot}/mod/ejsapp/upload_file.php\"/>');
                  document.write('<param name=\"lookandfeel\" value=\"NIMBUS\"/>');";

        if ($collabinfo) {
                $code .= "document.write('<param name=\"is_collaborative\" value=\"true\"/>');";
                if (isset($collabinfo->director)) {
                    $code .= "document.write('<param name=\"directorname\" value=\"$username\"/>');
                              document.write('<param name=\"Port_Teacher\" value=\"" . get_config('ejsapp_collab_session', 'collaborative_port') . "\"/>');";
                } else {
                    insert_collaborative_user($USER->id, null, $collabinfo->session);
                    $code .= "document.write('<param name=\"IP_Teacher\" value=\"{$collabinfo->ip}\"/>');//127.0.0.1\"/>');
                              document.write('<param name=\"MainFrame_Teacher\" value=\"{$ejsapp->mainframe}\"/>');
                              document.write('<param name=\"Port_Teacher\" value=\"{$collabinfo->localport}\"/>');";
                }
                if (isset($collabinfo->sarlabport)) {
                    $code .= "document.write('<param name=\"ipserver\" value=\"{$collabinfo->ip}\"/>');
                              document.write('<param name=\"portserver\" value=\"{$collabinfo->sarlabport}\"/>');
                              document.write('<param name=\"idExp\" value=\"EJS Collab\"/>');
                              document.write('<param name=\"user\" value=\"{$USER->username}@{$CFG->wwwroot}\"/>');
                              document.write('<param name=\"passwd\" value=\"$sarlab_key\"/>');";
                }
        } else {
            $code .= "document.write('<param name=\"is_collaborative\" value=\"false\"/>');";
        } // collabinfo for collaborative sessions

        if ($sarlabinfo){
            $code .= "document.write('<param name=\"ipserver\" value=\"{$sarlab_IP}\"/>');
                      document.write('<param name=\"portserver\" value=\"{$sarlab_port}\"/>');
                      document.write('<param name=\"idExp\" value=\"$sarlabinfo->practice\"/>');";
            if ($sarlabinfo->collab == 0) {
                  $code .= "document.write('<param name=\"user\" value=\"{$USER->username}@{$CFG->wwwroot}\"/>');
                            document.write('<param name=\"passwd\" value=\"$sarlab_key\"/>');";
            }
        } // sarlabinfo for remote laboratories

        $code .= "document.write('</applet>');";

        // <Loading state, controller and interaction files as well as personalized variables>
        // <Loading state files>
        $file_records = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'xmlfiles', 'itemid' => ($ejsapp->id)));
        $initial_state_file = new stdClass();
        if (!empty($file_records)) {
            foreach($file_records as $initial_state_file){
                if ($initial_state_file->filename != '.') {
                    break;
                }
            }
        }

        if ($state_file || (isset($initial_state_file->filename)) && $initial_state_file->filename != '.') {
            //<to read the applet state, javascript must wait until the applet has been totally downloaded>
            if ($state_file) {
              $state_file = $CFG->wwwroot . "/pluginfile.php/" . $state_file;
            } else {
                $state_file = $CFG->wwwroot . "/pluginfile.php/" . $initial_state_file->contextid .
                    "/" . $initial_state_file->component . "/" . $initial_state_file->filearea .
                    "/" . $initial_state_file->itemid . "/" . $initial_state_file->filename;
            }
            $state_fail_msg = get_string('state_fail_msg', 'ejsapp');
            $load_state_code = "
              function loadState(count) {
                if (!{$ejsapp->applet_name}._simulation && count > 0) {
                    window.setTimeout( function() { loadState( --count ); }, 1000 );
                }
                else if ({$ejsapp->applet_name}._simulation) {
                  window.setTimeout( function() { {$ejsapp->applet_name}._readState('url:$state_file'); }, 100 );
                  {$ejsapp->applet_name}._view.resetTraces();
                  //{$ejsapp->applet_name}._view.clearData();
                  //{$ejsapp->applet_name}._view.clearElements();
                  //{$ejsapp->applet_name}._view.resetElements();
                }
                else {
                  alert('$state_fail_msg');
                }
              }
              loadState(10);";
            //<\to read the applet state, javascript must wait until the applet has been totally downloaded>
            $code .= $load_state_code;
        } //end of if ($state_file)
        // <\Loading state files>

        // <Loading controller files>
        $file_records2 = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'cntfiles', 'itemid' => ($ejsapp->id)));
        $initial_cnt_file = new stdClass();
        if (!empty($file_records2)) {
            foreach($file_records2 as $initial_cnt_file){
                if ($initial_cnt_file->filename != '.') {
                    break;
                }
            }
        }

        if ($cnt_file || (isset($initial_cnt_file->filename) && $initial_cnt_file->filename != '.')) {
            //<to allow the applet loading the controller, javascript must wait until the applet has been totally downloaded>
            if ($cnt_file) {
                $cnt_file = $CFG->wwwroot . "/pluginfile.php/" . $cnt_file;
            } else {
                $cnt_file = $CFG->wwwroot . "/pluginfile.php/" . $initial_cnt_file->contextid .
                    "/" . $initial_cnt_file->component . "/" . $initial_cnt_file->filearea .
                    "/" . $initial_cnt_file->itemid . "/" . $initial_cnt_file->filename;
            }
            $cnt_fail_msg = get_string('controller_fail_msg', 'ejsapp');
            $load_cnt_code = "
              function loadController(count) {
                if (!{$ejsapp->applet_name}._model && count > 0) {
                    window.setTimeout( function() { loadController( --count ); }, 1000 );
                }
                else if ({$ejsapp->applet_name}._model) {
                  window.setTimeout( function() {
                  var element = {$ejsapp->applet_name}._model.getUserData('_codeController');
                  element.setController({$ejsapp->applet_name}._readText('url:$cnt_file')); }, 100 );
                  //{$ejsapp->applet_name}._model.codeEvaluator.setController(applet._readText('url:$cnt_file')); }, 100 );
                }
                else {
                  alert('$cnt_fail_msg');
                }
              }
              loadController(10);";
            //<\to allow the applet loading the controller, javascript must wait until the applet has been totally downloaded>
            $code .= $load_cnt_code;
        } //end of if ($cnt_file)
        // <\Loading controller files>

        // <Loading interaction recording files>
        $file_records3 = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'recfiles', 'itemid' => ($ejsapp->id)));
        $initial_rec_file = new stdClass();
        if (!empty($file_records3)) {
            foreach($file_records3 as $initial_rec_file){
                if ($initial_rec_file->filename != '.') {
                    break;
                }
            }
        }

        // <Loading personalized variables>
        if (!$collabinfo && isset($personalvarsinfo->name) && isset($personalvarsinfo->value) && isset($personalvarsinfo->type)) {
            $js_vars_names = json_encode($personalvarsinfo->name);
            $js_vars_values = json_encode($personalvarsinfo->value);
            $js_vars_types = json_encode($personalvarsinfo->type);
            $personalize_vars_code = "
              var js_vars_names = ". $js_vars_names . ";
              var js_vars_values = ". $js_vars_values . ";
              var js_vars_types = ". $js_vars_types . ";
              function personalizeVars(count) {
                if (!{$ejsapp->applet_name}._simulation && count > 0) {
                    window.setTimeout( function() { personalizeVars( --count ); }, 1000 );
                }
                else if ({$ejsapp->applet_name}._simulation) {
                    for (var i=0; i<js_vars_names.length; i++) {
                        if (js_vars_types[i] != \"Boolean\") {
                            {$ejsapp->applet_name}._simulation.setVariable(js_vars_names[i],js_vars_values[i].toString());
                        } else {
                            var bool = (js_vars_values[i] == 1);
                            {$ejsapp->applet_name}._simulation.setVariable(js_vars_names[i],bool);
                        }
                    }
                    {$ejsapp->applet_name}._simulation.update();
                    //{$ejsapp->applet_name}._initialize();
                }
              }
              personalizeVars(10);";
            $code .= $personalize_vars_code;
        }
        // <\Loading personalized variables>

        if ($rec_file || (isset($initial_rec_file->filename) && $initial_rec_file->filename != '.')) {
            //<to allow the applet running the recording file, javascript must wait until the applet has been totally downloaded>
            if ($rec_file) {
                $rec_file = $CFG->wwwroot . "/pluginfile.php/" . $rec_file;
            } else {
                $rec_file = $CFG->wwwroot . "/pluginfile.php/" . $initial_rec_file->contextid .
                    "/" . $initial_rec_file->component . "/" . $initial_rec_file->filearea .
                    "/" . $initial_rec_file->itemid . "/" . $initial_rec_file->filename;
            }
            $rec_fail_msg = get_string('recording_fail_msg', 'ejsapp');
            $load_rec_code = "
              function loadExperiment(count) {
                if (!{$ejsapp->applet_name}._simulation && count > 0) {
                    window.setTimeout( function() { loadExperiment( --count ); }, 1000 );
                }
                else if ({$ejsapp->applet_name}._simulation) {
                  window.setTimeout( function() { {$ejsapp->applet_name}._simulation.runLoadExperiment('url:$rec_file'); }, 100 );
                }
                else {
                  alert('$rec_fail_msg');
                }
              }
              loadExperiment(10);";
            //<\to allow the applet running the recording file, javascript must wait until the applet has been totally downloaded>
            $code .= $load_rec_code;
        } //end of if ($rec_file)
        // <\Loading interaction recording files>
        // <\Loading state, controller and interaction files as well as personalized variables>

        $code .= '</script></div>';

    }

    return $code;

} //end of generate_applet_embedding_code