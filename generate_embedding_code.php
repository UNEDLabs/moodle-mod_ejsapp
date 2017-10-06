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
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
//
// EJSApp has been developed by:
// - Luis de la Torre: ldelatorre@dia.uned.es
// - Ruben Heradio: rheradio@issi.uned.es
//
// at the Computer Science and Automatic Control, Spanish Open University
// (UNED), Madrid, Spain.

/**
 *
 * This file generates the code that embeds the EJS application into Moodle.
 *
 * It is used for three different cases: 1) when only the EJSApp activity is
 * being used, 2) when the EJSApp File Browser is used to load a state or rec
 * file, and 3) when the EJSApp Collab Session is used.
 *
 * @package    mod_ejsapp
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
 *      2) when the EJSApp File Browser is used to load a state, a controller or a recording file
 *      3) when the EJSApp Collab Session is used
 *      4) when third party plugins want to display EJS applets in their own activities by means of the EJSApp external interface
 *
 * @param stdClass $ejsapp record from table ejsapp
 * @param stdClass|null $sarlabinfo
 *                                  $sarlabinfo->instance: int sarlab id,
 *                                  $sarlabinfo->practice: int practice id,
 *                                  $sarlabinfo->collab: int collab whether sarlab offers collab access to this remote
 *                                      lab (1) or not (0),
 *                                  $sarlabinfo->labmanager: int laboratory manager (1) or student (0)
 *                                  $sarlabinfo->max_use_time: int maximum time the remote lab can be connected (in seconds)
 *                                      Null if sarlab is not used
 * @param array|null $userdatafiles
 *                                  $userdatafiles[0]: user_state_file, if generate_embedding_code is called from
 *                                      block ejsapp_file_browser, this is the name of the .xml or .json file that stores
 *                                      the state of an EJS applet, elsewhere it is null
 *                                  $userdatafiles[1]: user_cnt_file, if generate_embedding_code is called from
 *                                      block ejsapp_file_browser, this is the name of the .cnt file that stores the code
 *                                      of the controller used within an EJS applet, elsewhere it is null
 *                                  $userdatafiles[2]: user_rec_file, if generate_embedding_code is called from
 *                                      block ejsapp_file_browser, this is the name of the .rec file that stores the script
 *                                      with the recording of the interaction with an EJS applet, elsewhere it is null
 *                                  $userdatafiles[3]: user_blk_file, if generate_embedding_code is called from block
 *                                      ejsapp_file_browser, this is the name of the .blk file that stores a blockly
 *                                      program, elsewhere it is null
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
function generate_embedding_code($ejsapp, $sarlabinfo, $userdatafiles, $collabinfo, $personalvarsinfo) {
    global $DB, $USER, $CFG;

    /**
     * If a state, controller or recording file has been configured in the ejsapp activity, this function returns the
     * information of such file
     *
     * @param stdClass $ejsapp
     * @param string $datatype
     * @return stdClass $initialdatafile
     */
    function initial_data_file($ejsapp, $datatype) {
        global $DB;
        $filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => $datatype,
            'itemid' => ($ejsapp->id)));
        $initialdatafile = new stdClass();
        if (!empty($filerecords)) {
            foreach ($filerecords as $initialdatafile) {
                if ($initialdatafile->filename != '.') {
                    break;
                }
            }
        }
        return $initialdatafile;
    }

    /**
     * Return either the initial or the user-saved data file (state, controller or interaction recording)
     *
     * @param string $userdatafile
     * @param stdClass $initialdatafile
     * @return string $datafile
     */
    function get_data_file($userdatafile, $initialdatafile) {
        global $CFG;
        if ($userdatafile) {
            $datafile = $CFG->wwwroot . "/pluginfile.php/" . $userdatafile;
        } else {
            $datafile = $CFG->wwwroot . "/pluginfile.php/" . $initialdatafile->contextid .
                "/" . $initialdatafile->component . "/" . $initialdatafile->filearea .
                "/" . $initialdatafile->itemid . "/" . $initialdatafile->filename;
        }
        return $datafile;
    }

    if (!is_null($userdatafiles)) {
        $userstatefile = $userdatafiles[0];
        $usercntfile = $userdatafiles[1];
        $userrecfile = $userdatafiles[2];
        $userblkfile = $userdatafiles[3];
    } else {
        $userstatefile = null;
        $usercntfile = null;
        $userrecfile = null;
        $userblkfile = null;
    }

    // Sarlab is used to access this remote lab or to establish communication between users participating in a
    // collaborative session.
    if ($sarlabinfo || isset($collabinfo->sarlabport)) {
        $time = time();
        $year = date("Y", $time);
        $month = date("n", $time);
        $day = date("j", $time);
        $hour = date("G", $time);
        $min = date("i", $time);
        $seg = date("s", $time);
        mt_srand(time());
        $random = mt_rand(0, 1000000);
        if ($sarlabinfo) {
            $sarlabkey = sha1($year . $month . $day . $hour . $min . $seg . $sarlabinfo->practice .
                fullname($USER) . $USER->username . $random);
        } else {
            $sarlabkey = sha1($year . $month . $day . $hour . $min . $seg . "EJS Collab" . fullname($USER) .
                $USER->username . $random);
        }

        $newsarlabkey = new stdClass();
        $newsarlabkey->user = $USER->username;
        $newsarlabkey->sarlabpass = $sarlabkey;
        $newsarlabkey->labmanager = $sarlabinfo->labmanager;
        $newsarlabkey->creationtime = $time;
        $newsarlabkey->expirationtime = $time + $sarlabinfo->max_use_time;

        $DB->insert_record('block_remlab_manager_sb_keys', $newsarlabkey);

        if ($sarlabinfo) {
            $listsarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
            $sarlabip = $listsarlabips[$sarlabinfo->instance];
            $initpos = strpos($sarlabip, "'");
            $endpos = strrpos($sarlabip, "'");
            if (!(($initpos === false) || ($initpos === $endpos)) ) {
                $sarlabip = substr($sarlabip, $endpos + 1);
            }
            $listsarlabports = explode(";", get_config('block_remlab_manager', 'sarlab_port'));
            $sarlabport = $listsarlabports[$sarlabinfo->instance];
        } else {
            $sarlabip = $collabinfo->ip;
            $sarlabport = $collabinfo->sarlabport;
        }

        $commandsarlab = 'sarlab';
        $jarpath = '';
    }

    $context = context_user::instance($USER->id);
    $language = current_language();

    if ($ejsapp->class_file == '') { // EjsS Javascript.
        global $PAGE;

        if (count(explode('/', $CFG->wwwroot)) <= 3) {
            $path = $CFG->wwwroot . $ejsapp->codebase;
        } else {
            $path = substr($CFG->wwwroot, 0, strrpos( $CFG->wwwroot, '/') ) . $ejsapp->codebase;
        }

        $filename = substr($ejsapp->applet_name, 0, strpos($ejsapp->applet_name, '.'));
        $extension = substr($ejsapp->applet_name, strpos($ejsapp->applet_name, ".") + 1);

        $jsheaders = @get_headers($path . $filename . '.js');
        $separatedjs = false;
        if (($jsheaders[0] == 'HTTP/1.1 404 Not Found')) { // Javascript code included in html.
            $fileheaders = @get_headers($path . $filename . '_' . $language . '.' . $extension);
            if ($fileheaders[0] == 'HTTP/1.1 404 Not Found') {
                $code = file_get_contents($path . $ejsapp->applet_name);
            } else {
                $code = file_get_contents($path . $filename . '_' . $language . '.' . $extension);
            }
        } else { // Javascript code in a separated .js file.
            $separatedjs = true;
            $jsheaderslang = @get_headers($path . $filename . '_' . $language . '.js');
            if ($jsheaderslang[0] == 'HTTP/1.1 404 Not Found') {
                $code = file_get_contents($path . $filename . '.js');
            } else {
                $code = file_get_contents($path . $filename . '_' . $language . '.js');
            }
            $htmlcode = file_get_contents($path . $ejsapp->applet_name);
        }

        // For remote labs and collaborative sessions only
        if ($ejsapp->is_rem_lab || $collabinfo) {
            if ($sarlabinfo) { // For remote labs accessed through Sarlab, pass authentication params to the app.
                $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'sarlabCredentials',
                    array($USER->username, $sarlabkey));
            }
            // Make sure the Javascript application doesn't stop when losing focus.
            $sseuri = '';
            $port = '';
            if ($collabinfo && !isset($collabinfo->director)) {
                // Collaborative session with an invited user.
                $f = @fopen("actions.log", "rb");
                $collsessid = 1;
                $_SESSION["file_actions_session_$collsessid"] = $f;
                // $sseuri = $CFG->wwwroot . "/blocks/ejsapp_collab_session/ws/sse.php?id=$collsessid";.
                $sseuri = $CFG->wwwroot . "/blocks/ejsapp_collab_session/ws/sse.php?";

            } else if ($collabinfo && isset($collabinfo->director)) {
                // Collaborative session with the director of the session.
                $port = 8000;
            }
            $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'addToInitialization',
                array($sseuri, $port));
        }

        // Init of loading state, controller, interaction and blockly programs files as well as personalized variables.
        // Init of loading state files.
        $initialstatefile = initial_data_file($ejsapp, 'xmlfiles');
        if ($userstatefile || (isset($initialstatefile->filename)) && $initialstatefile->filename != '.') {
            $statefile = get_data_file($userstatefile, $initialstatefile);
            $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'readStateFile', $statefile);
        }
        // End of loading state files.

        // Init of loading controller files.
        $initialcntfile = initial_data_file($ejsapp, 'cntfiles');
        if ($usercntfile || (isset($initialcntfile->filename) && $initialcntfile->filename != '.')) {
            $cntfile = get_data_file($usercntfile, $initialcntfile);
            // TODO.
        }
        // End of loading controller files.

        // Init of loading interaction recording files.
        $initialrecfile = initial_data_file($ejsapp, 'recfiles');
        if ($userrecfile || (isset($initialrecfile->filename) && $initialrecfile->filename != '.')) {
            $endmessage = get_string('end_message', 'ejsapp');
            $recfile = get_data_file($userrecfile, $initialrecfile);
            $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'playRecFile',
                array($recfile, $endmessage));
        }
        // End of loading interaction recording files.

        // Init of loading blockly program files.
        $initialblkfile = initial_data_file($ejsapp, 'blkfiles');
        if ($userblkfile || (isset($initialblkfile->filename) && $initialblkfile->filename != '.')) {
            $blocklyconf = json_decode($ejsapp->blockly_conf);
            if ($blocklyconf[0] == 1) {
                $blkfile = get_data_file($userblkfile, $initialblkfile);
                $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'readBlocklyFile',
                    array($blkfile));
            }
        }
        // End of loading blockly program files.

        // Init of loading personalized variables.
        $search = ',"webUserInput"';
        if (!$collabinfo && isset($personalvarsinfo->name) && isset($personalvarsinfo->value) && isset($personalvarsinfo->type)) {
            $personalizevarscode = "'{";
            for ($i = 0; $i < count($personalvarsinfo->name); $i++) {
                $personalizevarscode .= '"' . $personalvarsinfo->name[$i] . '":' . $personalvarsinfo->value[$i];
                if ($i < count($personalvarsinfo->name) - 1) {
                    $personalizevarscode .= ",";
                }
            }
            $personalizevarscode .= "}'";
            $replace = "," . '"' . bin2hex(base64_encode($personalizevarscode)) . '"';
        } else {
            $replace = "";
        }
        $code = str_replace($search, $replace, $code);
        // End of loading personalized variables.
        // End of loading state, controller, interaction and blockly programs files as well as personalized variables.

        // End message when the recording of the user interaction stops.
        $endmessage = get_string('end_message', 'ejsapp');
        $search = "window.alert(end_reproduction_message);";
        $replace = "window.alert(\"$endmessage\");";
        $code = str_replace($search, $replace, $code);

        // Embedding the js code in the html file in case there is a separated js file.
        if ($separatedjs) {
            $code = '<script type="text/javascript"><!--//--><![CDATA[//><!--' . "\n" . $code . '//--><!]]></script>';
            $code = substr($htmlcode, 0, -strlen($htmlcode) +
                    strpos($htmlcode, '<div id="_topFrame" style="text-align:center"')) .
                '<div id="_topFrame" style="text-align:center"></div>' . $code . '</div>';
        }

    } else { // EjsS Java.

        $dirpath = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
        $ejsappname = $ejsapp->applet_name;
        //if (!$sarlabinfo) { // Without Sarlab, launch the Java file as a Web Start Application with the JNLP.
            if (pathinfo($ejsappname, PATHINFO_EXTENSION) == 'jar') {
                $ejsappname = substr($ejsapp->applet_name, 0, -4);
            }

            if (count(explode('/', $CFG->wwwroot)) <= 3) {
                $wwwpath = $CFG->wwwroot . $ejsapp->codebase;
            } else {
                $wwwpath = substr($CFG->wwwroot, 0, strrpos($CFG->wwwroot, '/')) . $ejsapp->codebase;
            }
            /*$records = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'jarfiles',
                'itemid' => ($ejsapp->id), 'filename' => ($ejsapp->applet_name)));
            $record = reset($records);
            $wwwpath = $CFG->wwwroot . '/pluginfile.php/' . $record->contextid . '/mod_ejsapp/jarfiles/' . $record->itemid . '/';*/

            $mainclass = substr($ejsapp->class_file, 0, -12);

            // Create the JNLP file.
            $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                        <jnlp spec=\"1.0+\"
                            codebase=\"$wwwpath\"
                            href=\"$ejsappname.jnlp\">
                            <information>
                                <title>$ejsappname</title>
                                <vendor>Easy Java Simulations</vendor>
                            </information>
                            <resources>
                                <j2se version=\"1.7+\"
                                    href=\"http://java.sun.com/products/autodl/j2se\"/>
                                <jar href=\"$ejsappname.jar\" main=\"true\"/>
                            </resources>
                            <application-desc
                                main-class=\"$mainclass\">
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
                $content .= "<argument>-ipserver</argument>
                                <argument>$sarlabip</argument>
                                <argument>-portserver</argument>
                                <argument>$sarlabport</argument>
                                <argument>-idExp</argument>
                                <argument>$sarlabinfo->practice</argument>
                                <argument>-user</argument>
                                <argument>{$USER->username}@{$CFG->wwwroot}</argument>
                                <argument>-passwd</argument>
                                <argument>$sarlabkey</argument>
                                <argument>-max_time</argument>
                                <argument>$sarlabinfo->max_use_time</argument>";
            }
            $content .= "</application-desc>
                            <security>
                                <all-permissions/>
                            </security>
                            <update check=\"background\"/>
                        </jnlp>";

            $jnlp = fopen($dirpath . $ejsappname . '.jnlp', 'w');
            fwrite($jnlp, $content);
            fclose($jnlp);

            // Run or download JNLP.
            $code = "<iframe id=\"EJsS\" style=\"display:none;\"></iframe>
            <script src=\"https://www.java.com/js/deployJava.js\"></script>
            <script>
                var url = '$wwwpath$ejsappname.jnlp';
                var is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
                if (is_chrome) document.getElementById('EJsS').src = url;
                else deployJava.launchWebStartApplication(url);
            </script>";
        /*} else {
            $commandsarlab = 'execjar';
            $jarpath = $dirpath . $ejsappname . ".jar";
        }*/

    }

    // Launching the websocket service for Sarlab.
    /*if ($sarlabinfo) {
        global $PAGE;
        $username = $USER->username . "@" . $CFG->wwwroot;
        $PAGE->requires->js_call_amd('mod_ejsapp/sarlab_websocket', 'SarlabWebSocket',
            array($commandsarlab, $sarlabip, $sarlabport, $sarlabinfo->practice,
                $sarlabinfo->max_use_time/60, $username, $sarlabkey, $jarpath));
        $PAGE->requires->js_call_amd('mod_ejsapp/sarlab_websocket', 'stopExperienceOnLeave');
    }*/

    return $code;

} // End of generate_embedding_code.