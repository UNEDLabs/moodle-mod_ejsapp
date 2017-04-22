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
 *
 * Returns the code that embeds an EJS applet into Moodle
 *
 * This function returns the HTML and JavaScript code that embeds an EJS applet into Moodle
 * It is used for four different cases:
 *      1) when only the EJSApp activity is being used
 *      2) when the EJSApp File Browser is used to load a state, a controller or a recording file
 *      3) when the EJSApp Collab Session is used
 *      4) when third party plugins want to display EJS applets in their own activities by means of the EJSApp external interface
 *
 * @param stdClass $ejsapp record from table ejsapp
 * @param stdClass|null $sarlabinfo 
 *                                  $sarlabinfo->instance: int sarlab id, 
 *                                  $sarlabinfo->practice: int practice id, 
 *                                  $sarlabinfo->collab:   int collab whether sarlab offers collab access to this remote lab (1) or not (0), 
 *                                  $sarlabinfo->labmanager: int laboratory manager (1) or student (0)
 *                                  $sarlabinfo->max_use_time: int maximum time the remote lab can be connected (in seconds)
 *                                  Null if sarlab is not used
 * @param array|null $user_data_files
 *                                  $user_data_files[0]: user_state_file, if generate_embedding_code is called from block ejsapp_file_browser, this is the name of the .xml or .json file that stores the state of an EJS applet, elsewhere it is null
 *                                  $user_data_files[1]: user_cnt_file, if generate_embedding_code is called from block ejsapp_file_browser, this is the name of the .cnt file that stores the code of the controller used within an EJS applet, elsewhere it is null
 *                                  $user_data_files[2]: user_rec_file, if generate_embedding_code is called from block ejsapp_file_browser, this is the name of the .rec file that stores the script with the recording of the interaction with an EJS applet, elsewhere it is null
 *                                  $user_data_files[3]: user_blk_file, if generate_embedding_code is called from block ejsapp_file_browser, this is the name of the .blk file that stores a blockly program, elsewhere it is null
 * @param stdClass|null $collabinfo 
 *                                  $collabinfo->session: int collaborative session id, 
 *                                  $collabinfo->ip: string collaborative session ip, 
 *                                  $collabinfo->localport: int collaborative session local port,
 *                                  $collabinfo->sarlabport: int|null sarlab port,
 *                                  $collabinfo->director: int|null id of the collaborative session master user, `
 *                                  Null if generate_embedding_code is not called from block ejsapp_collab_session
 * @param stdClass|null $personalvarsinfo
 *                                  $personalvarsinfo->name: string[] name(s) of the EJS variable(s),
 *                                  $personalvarsinfo->value: double[] value(s) of the EJS variable(s),
 *                                  $personalvarsinfo->type: string[] type(s) of the EJS variable(s),
 *                                  Null if no personal variables were defined for this EJSApp

 * @return string code that embeds an EjsS application in Moodle
 *
 */
function generate_embedding_code($ejsapp, $sarlabinfo, $user_data_files, $collabinfo, $personalvarsinfo) {
    global $DB, $USER, $CFG;

    /**
     * If a state, controller or recording file has been configured in the ejsapp activity, this function returns the information of such file
     *
     * @param stdClass $ejsapp
     * @param string $data_type
     * @return stdClass $initial_data_file
     */
    function initial_data_file($ejsapp, $data_type) {
        global $DB;
        $file_records = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => $data_type, 'itemid' => ($ejsapp->id)));
        $initial_data_file = new stdClass();
        if (!empty($file_records)) {
            foreach ($file_records as $initial_data_file) {
                if ($initial_data_file->filename != '.') {
                    break;
                }
            }
        }
        return $initial_data_file;
    }

    /**
     * Return either the initial or the user-saved data file (state, controller or interaction recording)
     *
     * @param string $user_data_file
     * @param stdClass $initial_data_file
     * @return string $data_file
     */
    function get_data_file($user_data_file, $initial_data_file) {
        global $CFG;
        if ($user_data_file) {
            $data_file = $CFG->wwwroot . "/pluginfile.php/" . $user_data_file;
        } else {
            $data_file = $CFG->wwwroot . "/pluginfile.php/" . $initial_data_file->contextid .
                "/" . $initial_data_file->component . "/" . $initial_data_file->filearea .
                "/" . $initial_data_file->itemid . "/" . $initial_data_file->filename;
        }
        return $data_file;
    }

    if (!is_null($user_data_files)) {
        $user_state_file = $user_data_files[0];
        $user_cnt_file = $user_data_files[1];
        $user_rec_file = $user_data_files[2];
        $user_blk_file = $user_data_files[3];
    } else {
        $user_state_file = null;
        $user_cnt_file = null;
        $user_rec_file = null;
        $user_blk_file = null;
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
        $DB->delete_records('block_remlab_manager_sb_keys', array('user' => $USER->username)); //WARNING: This also deletes keys for collab sessions with Sarlab!!
        mt_srand(time());
        $random = mt_rand(0, 1000000);
        if ($sarlabinfo) $sarlab_key = sha1($year . $month . $day . $hour . $min . $seg . $sarlabinfo->practice . fullname($USER) . $USER->username . $random);
        else $sarlab_key = sha1($year . $month . $day . $hour . $min . $seg . "EJS Collab" . fullname($USER) . $USER->username . $random);

        $new_sarlab_key = new stdClass();
        $new_sarlab_key->user = $USER->username;
        $new_sarlab_key->sarlabpass = $sarlab_key;
        $new_sarlab_key->labmanager = $sarlabinfo->labmanager;
        $new_sarlab_key->creationtime = $time;
        $new_sarlab_key->expirationtime = $time + $sarlabinfo->max_use_time;

        $DB->insert_record('block_remlab_manager_sb_keys', $new_sarlab_key);

        if ($sarlabinfo) {
            $list_sarlab_IPs = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
            $sarlab_IP = $list_sarlab_IPs[$sarlabinfo->instance];
            $init_pos = strpos($sarlab_IP, "'");
            $end_pos = strrpos($sarlab_IP, "'");
            if( !(($init_pos === false) || ($init_pos === $end_pos)) ) {
                $sarlab_IP = substr($sarlab_IP,$end_pos+1);
            }
            $list_sarlab_ports = explode(";", get_config('block_remlab_manager', 'sarlab_port'));
            $sarlab_port = $list_sarlab_ports[$sarlabinfo->instance];
        } else {
            $sarlab_IP = $collabinfo->ip;
            $sarlab_port =  $collabinfo->sarlabport;
        }
    }

    $context = context_user::instance($USER->id);
    $language = current_language();

    if ($ejsapp->class_file == '') { //EjsS Javascript

        if (count(explode('/', $CFG->wwwroot)) <= 3) $path = $CFG->wwwroot . $ejsapp->codebase;
        else $path = substr($CFG->wwwroot, 0, strrpos( $CFG->wwwroot, '/') ) . $ejsapp->codebase;

        $filename = substr($ejsapp->applet_name, 0, strpos($ejsapp->applet_name, '.'));
        $extension = substr($ejsapp->applet_name, strpos($ejsapp->applet_name, ".") + 1);

        $js_file_headers = @get_headers($path . $filename . '.js');
        $separated_js_file = false;
        if (($js_file_headers[0] == 'HTTP/1.1 404 Not Found')) { // Javascript code included in html
            $html_file_headers = @get_headers($path . $filename . '_' . $language . '.' . $extension);
            if ($html_file_headers[0] == 'HTTP/1.1 404 Not Found') $code = file_get_contents($path . $ejsapp->applet_name);
            else $code = file_get_contents($path . $filename . '_' . $language . '.' . $extension);
        } else { // Javascript code in a separated .js file
            $separated_js_file = true;
            $js_file_headers_lang = @get_headers($path . $filename . '_' . $language . '.js');
            if ($js_file_headers_lang[0] == 'HTTP/1.1 404 Not Found') $code = file_get_contents($path . $filename . '.js');
            else $code = file_get_contents($path . $filename . '_' . $language . '.js');
            $html_code = file_get_contents($path . $ejsapp->applet_name);
        }

        // Pass the needed parameters to the javascript application
        $search = "}, false);";
        $replace = '_model.setStatusParams("'.$context->id.'", "'.$USER->id.'", "'.$ejsapp->id.'", "'.$CFG->wwwroot.'/mod/ejsapp/upload_file.php", "'.$CFG->wwwroot.'/mod/ejsapp/send_files_list.php", function(){document.getElementById("refreshEJSAppFBBut").click();});
        }, false);';
        $pos = strpos($code, $search);
        if ($pos !== false) {
            $code = substr_replace($code, $replace, $pos, strlen($search));
        }

        // For remote labs and collaborative sessions, make sure the application keeps running even when the focus is not in the browser window
        if ($ejsapp->is_rem_lab || $collabinfo) {
            $search = "_model.addToInitialization(function() {";
            if ($collabinfo && !isset($collabinfo->director)) { // collaborative session with an invited user
                $f = @fopen("actions.log", "rb");
                $id_coll_sess = 1;
                $_SESSION["file_actions_session_$id_coll_sess"] = $f;
                //$sse_uri = $CFG->wwwroot . "/blocks/ejsapp_collab_session/ws/sse.php?id=$id_coll_sess";
                $sse_uri = $CFG->wwwroot . "/blocks/ejsapp_collab_session/ws/sse.php?";
                $replace = "_model.addToInitialization(function() { _model.setRunAlways(true); _model.playCaptureStream('$sse_uri');";
            } else if ($collabinfo && isset($collabinfo->director)) { // collaborative session with the director of the session
                $replace = "_model.addToInitialization(function() { _model.setRunAlways(true); _model.startCaptureStream(8000);";
            } else { // remote lab
                $replace = "_model.addToInitialization(function() { _model.setRunAlways(true);";
            }
            $code = str_replace($search, $replace, $code);
        }

        // <Loading state, controller, interaction and blockly programs files as well as personalized variables>
        // <Loading state files>
        $initial_state_file = initial_data_file($ejsapp, 'xmlfiles');
        if ($user_state_file || (isset($initial_state_file->filename)) && $initial_state_file->filename != '.') {
            $state_file = get_data_file($user_state_file, $initial_state_file);
            $search = "}, false);";
            $replace = "_model.readState('$state_file','.json');
            }, false);";
            $pos = strpos($code, $search);
            if ($pos !== false) {
                $code = substr_replace($code, $replace, $pos, strlen($search));
            }
        }
        // <\Loading state files>

        // <Loading controller files>
        $initial_cnt_file = initial_data_file($ejsapp, 'cntfiles');
        if ($user_cnt_file || (isset($initial_cnt_file->filename) && $initial_cnt_file->filename != '.')) {
            $cnt_file = get_data_file($user_cnt_file, $initial_cnt_file);
            //TODO
        }
        // <\Loading controller files>

        // <Loading interaction recording files>
        $initial_rec_file = initial_data_file($ejsapp, 'recfiles');
        if ($user_rec_file || (isset($initial_rec_file->filename) && $initial_rec_file->filename != '.')) {
            $end_message = get_string('end_message','ejsapp');
            $rec_file = get_data_file($user_rec_file, $initial_rec_file);
            $search = "}, false);";
            $replace = "_model.readText('$rec_file','.rec',function(content){_model.playCapture(JSON.parse(content),function(){alert('$end_message')})});
            }, false);";
            $pos = strpos($code, $search);
            if ($pos !== false) {
                $code = substr_replace($code, $replace, $pos, strlen($search));
            }
        }
        // <\Loading interaction recording files>

        // <Loading blockly program files>
        $initial_blk_file = initial_data_file($ejsapp, 'blkfiles');
        if ($user_blk_file || (isset($initial_blk_file->filename) && $initial_blk_file->filename != '.')) {
            $blockly_conf = json_decode($ejsapp->blockly_conf);
            if ($blockly_conf[0] == 1) {
                $blk_file = get_data_file($user_blk_file, $initial_blk_file);
                $search = "}, false);";
                $replace = "_model.readText('$blk_file','.blk',function(xmlText){if (xmlText){workspace.clear();xmlDom = Blockly.Xml.textToDom(xmlText);Blockly.Xml.domToWorkspace(xmlDom, workspace);}});
                }, false);";
                $pos = strpos($code, $search);
                if ($pos !== false) {
                    $code = substr_replace($code, $replace, $pos, strlen($search));
                }
            }
        }
        // <\Loading blockly program files>

        // <Loading personalized variables>
        $search = ',"webUserInput"';
        if (!$collabinfo && isset($personalvarsinfo->name) && isset($personalvarsinfo->value) && isset($personalvarsinfo->type)) {
            $personalize_vars_code = "'{";
            for ($i = 0; $i < count($personalvarsinfo->name); $i++) {
                $personalize_vars_code .= '"' . $personalvarsinfo->name[$i] . '":' . $personalvarsinfo->value[$i];
                if ($i < count($personalvarsinfo->name) - 1) $personalize_vars_code .= ",";
            }
            $personalize_vars_code .= "}'";
            $replace = "," . '"' . bin2hex(base64_encode($personalize_vars_code)) . '"';
        } else {
            $replace = "";
        }
        $code = str_replace($search, $replace, $code);
        // <\Loading personalized variables>
        // <\Loading state, controller, interaction and blockly program files as well as personalized variables>

        // <End message when the recording of the user interaction stops>
        $end_message = get_string('end_message','ejsapp');
        $search = "window.alert(end_reproduction_message);";
        $replace = "window.alert(\"$end_message\");";
        $code = str_replace($search, $replace, $code);
        // <\End message when the recording of the user interaction stops>

        // <Embedding the js code in the html file in case there is a separated js file>
        if ($separated_js_file) {
            $code = '<script type="text/javascript"><!--//--><![CDATA[//><!--' . "\n" . $code . '//--><!]]></script>';
            $code = substr($html_code, 0, -strlen($html_code) + strpos($html_code, '<div id="_topFrame" style="text-align:center"')) . '<div id="_topFrame" style="text-align:center"></div>' . $code . '</div>';
        }
        // </Embedding the js code in the html file in case there is a separate js file>

        // <Launching the websocket service for Sarlab>
        if ($sarlabinfo) {
            global $PAGE;
            $username = $USER->username . "@" . $CFG->wwwroot;
            $PAGE->requires->js_call_amd('mod_ejsapp/sarlab_websocket', 'SarlabWebSocket', array($CFG->wwwroot, $sarlab_IP, $sarlab_port, $sarlabinfo->practice, $new_sarlab_key->expirationtime, $username, $sarlab_key));
            $PAGE->requires->js_call_amd('mod_ejsapp/sarlab_websocket', 'stopExperienceOnLeave');
        }
        // </Launching the websocket service for Sarlab>

    } else { //EjsS Java (Web Start Application)

        $ejsapp_name = $ejsapp->applet_name;
        if (pathinfo($ejsapp_name, PATHINFO_EXTENSION) == 'jar') $ejsapp_name = substr($ejsapp->applet_name, 0, -4);

        if (count(explode('/', $CFG->wwwroot)) <= 3) $wwwpath = $CFG->wwwroot . $ejsapp->codebase;
        else $wwwpath = substr($CFG->wwwroot, 0, strrpos($CFG->wwwroot, '/')) . $ejsapp->codebase;
        /*$records = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'jarfiles', 'itemid' => ($ejsapp->id), 'filename' => ($ejsapp->applet_name)));
        $record = reset($records);
        $wwwpath = $CFG->wwwroot . '/pluginfile.php/' . $record->contextid . '/mod_ejsapp/jarfiles/' . $record->itemid . '/';*/

        $main_class = substr($ejsapp->class_file, 0, -12);

        // Create the JNLP file:
        $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                        <jnlp spec=\"1.0+\"
                            codebase=\"$wwwpath\"
                            href=\"$ejsapp_name.jnlp\">
                            <information>
                                <title>$ejsapp_name</title>
                                <vendor>Easy Java Simulations</vendor>
                            </information>
                            <resources>
                                <!-- Application Resources -->
                                <j2se version=\"1.7+\"
                                    href=\"http://java.sun.com/products/autodl/j2se\"/>
                                <jar href=\"$ejsapp_name.jar\" main=\"true\"/>
                            </resources>
                            <application-desc
                                main-class=\"$main_class\">
                                <argument>-language</argument>
                                <argument>$language</argument>
                                <argument>-lookandfeel</argument>
                                <argument>NIMBUS</argument>
                                <argument>-context_id</argument>
                                <argument>{$context->id}</argument>
                                <argument>-user_id</argument>
                                <argument>{$USER->id}</argument>
                                <argument>-ejsapp_id</argument>
                                <argument>{$ejsapp->id}</argument>
                                <argument>-user_moodle</argument>
                                <argument>user</argument>
                                <argument>-password_moodle</argument>
                                <argument>password</argument>
                                <argument>-moodle_upload_file</argument>
                                <argument>{$CFG->wwwroot}/mod/ejsapp/upload_file</argument>";
        if ($sarlabinfo) {
            $content .=         "<argument>-ipserver</argument>
                                <argument>$sarlab_IP</argument>
                                <argument>-portserver</argument>
                                <argument>$sarlab_port</argument>
                                <argument>-idExp</argument>
                                <argument>$sarlabinfo->practice</argument>
                                <argument>-user</argument>
                                <argument>{$USER->username}@{$CFG->wwwroot}</argument>
                                <argument>-passwd</argument>
                                <argument>$sarlab_key</argument>
                                <argument>-max_time</argument>
                                <argument>$sarlabinfo->max_use_time</argument>";
        }
        $content .=         "</application-desc>
                            <security>
                                <all-permissions/>
                            </security>
                            <update check=\"background\"/>
                        </jnlp>";

        $dirpath = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
        $jnlp = fopen($dirpath.$ejsapp_name.'.jnlp', 'w');
        fwrite($jnlp, $content);
        fclose($jnlp);

        // Run or download JNLP:
        $code = "<iframe id=\"EJsS\" style=\"display:none;\"></iframe>
            <script src=\"https://www.java.com/js/deployJava.js\"></script>
            <script>
                var url = '$wwwpath$ejsapp_name.jnlp';
                var is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
                if (is_chrome) document.getElementById('EJsS').src = url;
                else deployJava.launchWebStartApplication(url);
            </script>";

    }

    return $code;

} //end of generate_embedding_code