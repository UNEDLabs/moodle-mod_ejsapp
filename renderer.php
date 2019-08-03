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
 * Prints a particular instance of EJSApp
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Prints the required html in the ejsapp activity view
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_ejsapp_renderer extends plugin_renderer_base {

    /**
     * Prints ejsapp EJsS div for the lab
     * This function returns the HTML and JavaScript code that embeds an EjsS application into Moodle
     * It is used for four different cases:
     *      1) when only the EJSApp activity is being used
     *      2) when the EJSApp File Browser is used to load a state, recording or blockly file
     *      3) when the EJSApp Collab Session is used
     *      4) when third party plugins want to display EjsS labs in their own activities by means of the EJSApp external interface
     *
     * @param stdClass $ejsapp record from table ejsapp
     * @param stdClass|null $remlabinfo
     *                                  $remlabinfo->instance: false|int sarlab id,
     *                                  $remlabinfo->practice: int practice id,
     *                                  $remlabinfo->collab: int collab whether sarlab offers collab access to this remote
     *                                      lab (1) or not (0),
     *                                  $remlabinfo->labmanager: int laboratory manager (1) or student (0)
     *                                  $remlabinfo->max_use_time: int maximum time the remote lab can be connected (in seconds)
     *                                  Null if virtual lab
     * @param array|null $userdatafiles
     *                                  $userdatafiles[0]: user_state_file, if generate_embedding_code is called from
     *                                      block ejsapp_file_browser, this is the name of the .json file that stores
     *                                      the state of an EjsS lab, elsewhere it is null
     *                                  $userdatafiles[1]: user_rec_file, if generate_embedding_code is called from
     *                                      block ejsapp_file_browser, this is the name of the .rec file that stores the script
     *                                      with the recording of the interaction with an EJS applet, elsewhere it is null
     *                                  $userdatafiles[2]: user_blk_file, if generate_embedding_code is called from block
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
     * @return string Html code that prints the general html view layout
     */
    public function ejsapp_lab($ejsapp, $remlabinfo, $userdatafiles, $collabinfo, $personalvarsinfo) {
        return $this->render(new ejsapp_lab($ejsapp, $remlabinfo, $userdatafiles, $collabinfo, $personalvarsinfo));
    }

    /**
     * Prints ejsapp chart div
     * @return string Html code that prints the chart div
     */
    public function ejsapp_charts() {
        return $this->render(new ejsapp_charts());
    }

    /**
     * Prints ejsapp controlbar div
     * @param array $blocklyconf
     * @return string Html code that prints the control bar div
     */
    public function ejsapp_controlbar($blocklyconf) {
        return $this->render(new ejsapp_controlbar($blocklyconf));
    }

    /**
     * Prints ejsapp blockly div
     * @return string Html code that prints the blockly div
     */
    public function ejsapp_blockly() {
        return $this->render(new ejsapp_blockly());
    }

    /**
     * Prints ejsapp log div
     * @return string Html code that prints the log div
     */
    public function ejsapp_log() {
        return $this->render(new ejsapp_log());
    }

    /**
     * Returns the code that embeds an EjsS application into Moodle
     *
     * @param ejsapp_lab $params
     * @return string Html code that embeds an EjsS application in Moodle
     * @throws
     *
     */
    public function render_ejsapp_lab(ejsapp_lab $params) {
        global $DB, $USER, $CFG, $COURSE;

        /**
         * If a state, recording or blockly file has been configured in the ejsapp activity, this function returns the
         * information of such file
         *
         * @param stdClass $ejsapp
         * @param string $datatype
         * @return stdClass $initialdatafile
         * @throws
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
         * Return either the initial or the user-saved data file (state, interaction recording or blockly)
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

        prepare_ejs_file($params->ejsapp);

        if (!is_null($params->userdatafiles)) {
            $userstatefile = $params->userdatafiles[0];
            $userrecfile = $params->userdatafiles[1];
            $userblkfile = $params->userdatafiles[2];
        } else {
            $userstatefile = null;
            $userrecfile = null;
            $userblkfile = null;
        }

        // Sarlab is used to access this remote lab or to establish communication between users participating in a
        // collaborative session.
        if ($params->remlabinfo) {
            if ($params->remlabinfo->instance !== false || isset($collabinfo->sarlabport)) {
                $time = time();
                $min = date("i", $time);
                $seg = date("s", $time);
                mt_srand(time());
                $random = mt_rand(0, 1000000);
                if ($params->remlabinfo) {
                    $sarlabkey = sha1($min . $seg . $params->remlabinfo->practice . fullname($USER) . $USER->username . $random);
                } else {
                    $sarlabkey = sha1($min . $seg . "EjsS Collab" . fullname($USER) . $USER->username . $random);
                }

                $newsarlabkey = new stdClass();
                $newsarlabkey->user = $USER->username;
                $newsarlabkey->sarlabpass = $sarlabkey;
                $newsarlabkey->labmanager = $params->remlabinfo->labmanager;
                $newsarlabkey->creationtime = $time;
                $newsarlabkey->expirationtime = $time + $params->remlabinfo->max_use_time;

                $DB->insert_record('block_remlab_manager_sb_keys', $newsarlabkey);

                if ($params->remlabinfo->instance !== false) {
                    $listsarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
                    if (empty($sarlabips)) {
                        $listsarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP') . ';');
                    }
                    $sarlabip = $listsarlabips[$params->remlabinfo->instance];
                    $initpos = strpos($sarlabip, "'");
                    $endpos = strrpos($sarlabip, "'");
                    if (!(($initpos === false) || ($initpos === $endpos))) {
                        $sarlabip = substr($sarlabip, $endpos + 1);
                    }
                    $listsarlabports = explode(";", get_config('block_remlab_manager', 'sarlab_port'));
                    if (empty($listsarlabports)) {
                        $listsarlabports = explode(";", get_config('block_remlab_manager', 'sarlab_IP') . ';');
                    }
                    $sarlabport = $listsarlabports[$params->remlabinfo->instance];
                } else {
                    $sarlabip = $params->collabinfo->ip;
                    $sarlabport = $params->collabinfo->sarlabport;
                }
            }
        }

        $context = context_user::instance($USER->id);
        $language = current_language();

        if ($params->ejsapp->class_file == '') { // EjsS Javascript.
            global $PAGE;

            $path = new moodle_url($params->ejsapp->codebase);

            $filename = substr($params->ejsapp->applet_name, 0, strpos($params->ejsapp->applet_name, '.'));
            $extension = substr($params->ejsapp->applet_name, strpos($params->ejsapp->applet_name, ".") + 1);

            $jsheaders = @get_headers($path . $filename . '.js');
            $separatedjs = false;
            if (($jsheaders[0] == 'HTTP/1.1 404 Not Found')) { // Javascript code included in html.
                $fileheaders = @get_headers($path . $filename . '_' . $language . '.' . $extension);
                if ($fileheaders[0] == 'HTTP/1.1 404 Not Found') {
                    $code = file_get_contents($path . $params->ejsapp->applet_name);
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
                $htmlcode = file_get_contents($path . $params->ejsapp->applet_name);
            }

            // For remote labs and collaborative sessions only
            if (($params->ejsapp->is_rem_lab || $params->collabinfo) && $params->remlabinfo) {
                if ($params->remlabinfo->instance !== false ) { // For remote labs accessed through Sarlab, pass authentication params to the app.
                    $practice = explode("@", $params->remlabinfo->practice, 2);
                    // TODO: Replace $CFG->wwwroot by get_config('mod_ejsapp', 'server_id')?
                    $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'sarlabCredentials',
                        array($USER->username . "@" . $CFG->wwwroot, $sarlabkey));
                    $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'sarlabRun',
                        array($sarlabport == 443, $sarlabip, 'SARLABV8.0', $sarlabport, $practice[0], $CFG->wwwroot . '/course/view.php?id=' . $COURSE->id));
                }
                // Make sure the Javascript application doesn't stop when losing focus and set SSE info for collab.
                $sseuri = '';
                $port = '';
                if ($params->collabinfo && !isset($collabinfo->director)) {
                    // Collaborative session with an invited user.
                    $f = @fopen("actions.log", "rb");
                    $collsessid = 1;
                    $_SESSION["file_actions_session_$collsessid"] = $f;
                    // $sseuri = $CFG->wwwroot . "/blocks/ejsapp_collab_session/ws/sse.php?id=$collsessid";.
                    $sseuri = $CFG->wwwroot . "/blocks/ejsapp_collab_session/ws/sse.php?";
                } else if ($params->collabinfo && isset($collabinfo->director)) {
                    // Collaborative session with the director of the session.
                    $port = 8000;
                }
                $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'addToInitialization',
                    array($sseuri, $port));
            }

            // Init of loading state, interaction and blockly programs files as well as personalized variables.
            // Init of loading state files.
            $initialstatefile = initial_data_file($params->ejsapp, 'xmlfiles');
            if ($userstatefile || (isset($initialstatefile->filename)) && $initialstatefile->filename != '.') {
                $statefile = get_data_file($userstatefile, $initialstatefile);
                $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'readStateFile', array($statefile));
            }
            // End of loading state files.

            // Init of loading interaction recording files.
            $initialrecfile = initial_data_file($params->ejsapp, 'recfiles');
            if ($userrecfile || (isset($initialrecfile->filename) && $initialrecfile->filename != '.')) {
                $endmessage = get_string('end_message', 'ejsapp');
                $recfile = get_data_file($userrecfile, $initialrecfile);
                $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'playRecFile',
                    array($recfile, $endmessage));
            }
            // End of loading interaction recording files.

            // Init of loading blockly program files.
            $initialblkfile = initial_data_file($params->ejsapp, 'blkfiles');
            if ($userblkfile || (isset($initialblkfile->filename) && $initialblkfile->filename != '.')) {
                $blocklyconf = json_decode($params->ejsapp->blockly_conf);
                if ($blocklyconf[0] == 1) {
                    $blkfile = get_data_file($userblkfile, $initialblkfile);
                    $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'readBlocklyFile',
                        array($blkfile));
                }
            }
            // End of loading blockly program files.

            // Init of loading personalized variables.
            $search = ',"webUserInput"';
            if (!$params->collabinfo && isset($params->personalvarsinfo->name) && isset($params->personalvarsinfo->value) && isset($params->personalvarsinfo->type)) {
                $personalizevarscode = "'{";
                for ($i = 0; $i < count($params->personalvarsinfo->name); $i++) {
                    $personalizevarscode .= '"' . $params->personalvarsinfo->name[$i] . '":' . $params->personalvarsinfo->value[$i];
                    if ($i < count($params->personalvarsinfo->name) - 1) {
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
            // End of loading state, interaction and blockly programs files as well as personalized variables.

            // Embedding the js code in the html file in case there is a separated js file.
            if ($separatedjs) {
                $code = '<script type="text/javascript"><!--//--><![CDATA[//><!--' . "\n" . $code . '//--><!]]></script>';
                $code = substr($htmlcode, 0, -strlen($htmlcode) +
                        strpos($htmlcode, '<div id="_topFrame" style="text-align:center"')) .
                    '<div id="_topFrame" style="text-align:center"></div>' . $code . '</div>';
            }

        } else { // EjsS Java.

            $dirpath = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $params->ejsapp->course . '/' . $params->ejsapp->id . '/';
            $ejsappname = $params->ejsapp->applet_name;
            if (!$params->remlabinfo || !$params->remlabinfo->instance === false) {
                // Without Sarlab, launch the Java file as a Web Start Application with the JNLP.
                if (pathinfo($ejsappname, PATHINFO_EXTENSION) == 'jar') {
                    $ejsappname = substr($params->ejsapp->applet_name, 0, -4);
                }

                $wwwpath = new moodle_url($params->ejsapp->codebase);
                $mainclass = substr($params->ejsapp->class_file, 0, -12);

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
                            <argument>{$params->ejsapp->id}</argument>
                            <argument>-user_moodle</argument>
                            <argument>user</argument>
                            <argument>-password_moodle</argument>
                            <argument>password</argument>
                            <argument>-moodle_upload_file</argument>
                            <argument>{$CFG->wwwroot}/mod/ejsapp/upload_file</argument>
                        </application-desc>
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
            } else {
                $code = '';
                $commandsarlab = 'execjar';
                $jarpath = $CFG->wwwroot . '/mod/ejsapp/jarfiles/' . $params->ejsapp->course . '/' . $params->ejsapp->id . '/' . $ejsappname;
                // Launching the websocket service for Sarlab.
                global $PAGE;
                $username = $USER->username . "@" . $CFG->wwwroot;
                $practice = explode("@", $params->remlabinfo->practice, 2);
                $PAGE->requires->js_call_amd('mod_ejsapp/sarlab_websocket', 'SarlabWebSocket',
                    array($commandsarlab, $sarlabip, 443, $practice[0],
                        $params->remlabinfo->max_use_time/60, $username, $sarlabkey, $jarpath));
                $PAGE->requires->js_call_amd('mod_ejsapp/sarlab_websocket', 'stopExperienceOnLeave');
            }

        }

        return $code;
    }

    /**
     * Prints ejsapp charts div
     * @return string Html code that prints the html for the charts
     */
    public function render_ejsapp_charts() {
        global $CFG;

        $chartsdiv =
            html_writer::start_div("", array("id" => "ChartBox", "style" => "display:none;")) .
                html_writer::start_div("topnav-right align-self-end") .
                    /*html_writer::tag("i", "", array("id" => "minslideshow", "onclick" => "minimize('ChartBox')",
                        "class" => "fa fa-window-minimize", "aria-hidden" => "true")) .
                    html_writer::tag("i", "", array("id" => "maxslideshow", "onclick" => "maximize('ChartBox')",
                        "class" => "fa fa-window-maximize", "aria-hidden" => "true")) .*/
                    html_writer::tag("i", "", array("id" => "save_chart_img", "onclick" =>
                        "saveImg('" . $CFG->wwwroot . "/mod/ejsapp/upload_file.php" . "')",
                        "class" => "fa fa-area-chart fa-2x")) .
                    html_writer::tag("i", "", array("id" => "save_chart_data", "onclick" =>
                        "saveCSV(0)", "class" => "fa fa-file-text-o fa-2x")) .
                    html_writer::tag("i", "", array("id" => "full_screen_chart", "class" =>
                        "fa fa-expand fa-2x", "aria-hidden" => "true")) .
                    /*html_writer::tag("i", "", array("id" => "ChartBoxHeader", "class" => "fa fa-arrows-alt",
                        "aria-hidden" => "true")) .
                    html_writer::tag("i", "", array("id" => "return_ChartBox", "style" => "display:none;",
                        "onclick" => "returning('ChartBox')", "class" => "fa fa-window-restore", "aria-hidden" => "true")) .*/
                html_writer::end_div() .
                html_writer::start_div("d-flex flex-column", array("id" => "slideshow-wrapper")) .
                    html_writer::start_div("d-flex justify-content-center", array("id" => "control_chart")) .
                        html_writer::tag("i", "", array("id" => "prev_chart", "onclick" => "prevChart()",
                            "class" => "fa fa-angle-double-left fa-2x", "style" => "display:none;")) .
                        html_writer::tag("i", "", array("id" => "clean_chart", "onclick" => "cleanCharts()",
                            "class" => "fa fa-times fa-2x", "style" => "display:none;")) .
                        html_writer::tag("i", "", array("id" => "next_chart", "onclick" => "nextChart()",
                            "class" => "fa fa-angle-double-right fa-2x", "style" => "display:none;")) .
                    html_writer::end_div() .
                html_writer::end_div() .
            html_writer::end_div();

        return $chartsdiv;
    }

    /**
     * Prints ejsapp control bar div
     * @param ejsapp_controlbar $params
     * @return string Html code that prints the html for the control bar
     */
    public function render_ejsapp_controlbar($params) {
        $navbar =
            html_writer::start_div("navbar", array('id' => 'blockly_navbar')) .
                html_writer::start_div("dropdown", array("id" => "experimentsDropdown")) .
                    html_writer::tag("button", "Experiments", array("class" =>
                        "btn btn-secondary dropdown-toggle", "type" => "button", "id" => "dropdownMenuButton",
                        "data-toggle" => "dropdown", "aria-haspopup" => "true", "aria-expanded" => "false")) .
                    html_writer::start_div("dropdown-menu", array("id" => "experimentsScripts",
                        "aria-labelledby" => "dropdownMenuButton")) .
                        html_writer::start_tag("a", array("class" => "dropdown-item",
                            "onclick" => "newScript(1)")) .
                            html_writer::tag("i", "", array("class" => "fa fa-plus",
                                "aria-hidden" => "true")) . " New experiment script" .
                        html_writer::end_tag("a") .
                    html_writer::end_div() .
                html_writer::end_div() .
                html_writer::start_div("dropdown", array("id" => "chartsDropdown")) .
                    html_writer::tag("button", "Data & Charts", array("class" => "btn btn-secondary dropdown-toggle",
                        "type" => "button", "id" => "dropdownMenuButton", "data-toggle" => "dropdown",
                        "aria-haspopup" => "true", "aria-expanded" => "false")) .
                    html_writer::start_div("dropdown-menu", array("id" => "chartsScripts", "aria-labelledby" =>
                        "dropdownMenuButton")) .
                        html_writer::tag("a", html_writer::tag("i", "", array("class" =>
                                "fa fa-plus", "aria-hidden" => "true")) . " New chart script", array("class" =>
                            "dropdown-item", "onclick" => "newScript(2)")) .
                    html_writer::end_div() .
                html_writer::end_div() .
                html_writer::start_div("dropdown", array("id" => "eventsDropdown")) .
                    html_writer::tag("button", "Events", array("class" => "btn btn-secondary dropdown-toggle",
                        "type" => "button", "id" => "dropdownMenuButton", "data-toggle" => "dropdown",
                        "aria-haspopup" => "true", "aria-expanded" => "false")) .
                    html_writer::start_div("dropdown-menu", array("id" => "eventsScripts", "aria-labelledby" =>
                        "dropdownMenuButton")) .
                        html_writer::tag("a", html_writer::tag("i", "", array("class" =>
                                "fa fa-plus", "aria-hidden" => "true")) . " New event script", array("class" =>
                            "dropdown-item", "onclick" => "newScript(3)")) .
                    html_writer::end_div() .
                html_writer::end_div() .
                html_writer::start_div("dropdown", array("id" => "controllersDropdown")) .
                    html_writer::tag("button", "Controllers", array("class" => "btn btn-secondary dropdown-toggle",
                        "type" => "button", "id" => "dropdownMenuButton", "data-toggle" => "dropdown",
                        "aria-haspopup" => "true", "aria-expanded" => "false")) .
                    html_writer::start_div("dropdown-menu", array("id" => "controllersScripts", "aria-labelledby" =>
                        "dropdownMenuButton")) .
                        html_writer::tag("a", html_writer::tag("i", "", array("class" =>
                                "fa fa-plus", "aria-hidden" => "true")) . " New controller script", array("class" =>
                            "dropdown-item", "onclick" => "newScript(4)")) .
                    html_writer::end_div() .
                html_writer::end_div() .
                html_writer::start_div("topnav-right", array("id" => "logs")) .
                    html_writer::start_tag("a", array("id" => "show_log", "onclick" => "showLog()")) .
                        html_writer::tag("i", " Logs", array("class" => "fa fa-bug", "aria-hidden" => "true")) .
                    html_writer::end_tag("a") .
                html_writer::end_div() .
            html_writer::end_div();

        $textExecution =
            html_writer::start_div("d-flex justify-content-center flex-wrap", array("id" => "textExecution")) .
                html_writer::start_div("d-flex justify-content-center flex-wrap", array("id" => "prepareSelectionsMenu")) .
                    html_writer::tag("select", html_writer::tag("option", "Select experiment",
                        array("data-display" => "Experiment")), array("id" => "experimentSelection",
                        "class" => "textExecutionElement nice-select green")) .
                    html_writer::tag("select", html_writer::tag("option", "Select chart",
                        array("data-display" => "Chart")), array("id" => "chartSelection",
                        "class" => "textExecutionElement nice-select blue")) .
                    html_writer::tag("select", html_writer::tag("option", "Select event",
                        array("data-display" => "Event")), array("id" => "eventSelection",
                        "class" => "textExecutionElement nice-select red")) .
                    html_writer::tag("select", html_writer::tag("option", "Select controller",
                        array("data-display" => "Controller")), array("id" => "controllerSelection",
                        "class" => "textExecutionElement nice-select peru")) .
                html_writer::end_div() .
                html_writer::start_div("d-flex justify-content-center") .
                    html_writer::tag("button", "Run", array("class" => "play-code textExecutionElement",
                        "onclick" => "playCode(" . $params->blocklyconf[1] . "," . $params->blocklyconf[2] . "," .
                            $params->blocklyconf[3] . "," .  ")")) .
                html_writer::end_div() .
            html_writer::end_div();

        return $navbar . $textExecution;
    }

    /**
     * Prints ejsapp blockly div
     * @return string Html code that prints the html for the blockly div
     */
    public function render_ejsapp_blockly() {
        $blocklydiv =
            html_writer::start_div("blockly", array('id' => 'blocklyDiv')) .
                html_writer::start_div("box", array("id" => "ScriptBox", "style" => "display:none;")) .
                    html_writer::start_div("d-flex justify-content-between") .
                        html_writer::tag("h3", "", array("id" => "titleScriptBox")) .
                        html_writer::tag("i", "", array("class" => "fa fa-expand fa-2x", "id" =>
                            "full_screen_blockly", "aria-hidden" => "true")) .
                    html_writer::end_div() .
                    html_writer::start_div("", array("id" => "whereScriptsAre")) .
                        html_writer::div("", "", array("id" => "blocklyDivExperiments",
                            "style" => "display:none;")) .
                        html_writer::div("", "", array("id" => "blocklyDivCharts",
                            "style" => "display:none;")) .
                        html_writer::div("", "", array("id" => "blocklyDivEvents",
                            "style" => "display:none;")) .
                        html_writer::start_div("", array("id" => "ControllerDiv")) .
                            html_writer::div("", "", array("id" => "blocklyDivController",
                                "style" => "display:none;")) .
                        html_writer::end_div() .
                    html_writer::end_div() .
                html_writer::end_div() .
                html_writer::start_div("modal", array("id" => "myModal")) .
                    html_writer::start_div("modal-content") .
                        html_writer::tag("span", "&times;", array("class" => "close")) .
                        html_writer::start_div("box", array("id" => "ScriptBox")) .
                            html_writer::tag("h3", "Javascript code") .
                            html_writer::div("", "", array("id" => "_javaScriptFrame")) .
                        html_writer::end_div() .
                    html_writer::end_div() .
                html_writer::end_div() .
            html_writer::end_div();

        return $blocklydiv;
    }

    /**
     * Prints ejsapp log div
     * @return string Html code that prints the html for the log div
     */
    public function render_ejsapp_log() {
        $logdiv =
            html_writer::start_div("", array("id" => "footer", "style" => "display:none;")) .
                html_writer::start_div("", array("id" => "executionLogGen", "style" => "display:none")) .
                    html_writer::tag("hr", "") .
                    html_writer::tag("h5", "Previous executions:") .
                    html_writer::div("", "", array("id" => "executionLog")) .
                html_writer::end_div() .
                html_writer::tag("hr", "") .
                html_writer::tag("textarea", "Errors:", array("id" => "errorArea", "readonly" => "true")) .
            html_writer::end_div();

        return $logdiv;
    }
}

/**
 * Auxiliary class to print the EJsS div
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ejsapp_lab implements renderable {
    /**
     * __construct
     * @param stdClass $ejsapp
     * @param stdClass $remlabinfo
     * @param array $userdatafiles
     * @param stdClass $collabinfo
     * @param stdClass $personalvarsinfo
     */
    public function __construct($ejsapp, $remlabinfo, $userdatafiles, $collabinfo, $personalvarsinfo) {
        $this->ejsapp = $ejsapp;
        $this->remlabinfo = $remlabinfo;
        $this->userdatafiles = $userdatafiles;
        $this->collabinfo = $collabinfo;
        $this->personalvarsinfo = $personalvarsinfo;
    }
}

/**
 * Auxiliary class to print the charts div
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ejsapp_charts implements renderable {
    /**
     * __construct
     */
    public function __construct() {
    }
}

/**
 * Auxiliary class to print the controlbar div
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ejsapp_controlbar implements renderable {
    /**
     * __construct
     * @param array $blocklyconf
     */
    public function __construct($blocklyconf) {
        $this->blocklyconf = $blocklyconf;
    }
}

/**
 * Auxiliary class to print the blockly div
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ejsapp_blockly implements renderable {
    /**
     * __construct
     */
    public function __construct() {
    }
}

/**
 * Auxiliary class to print the log div
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ejsapp_log implements renderable {
    /**
     * __construct
     */
    public function __construct() {
    }
}