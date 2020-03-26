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
 * This file contains the definition for the renderable classes for the ejsapp activity
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

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
     * @throws
     */
    public function __construct($ejsapp, $remlabinfo, $userdatafiles, $collabinfo, $personalvarsinfo) {
        global $DB, $USER, $CFG, $COURSE;

        // myFrontier is used to access this remote lab or to establish communication between users participating in a
        // collaborative session.
        if ($remlabinfo) {
            if ($remlabinfo->instance !== false || isset($collabinfo->enlargeport)) {
                $time = time();
                $min = date("i", $time);
                $seg = date("s", $time);
                mt_srand(time());
                $random = mt_rand(0, 1000000);
                if ($remlabinfo) {
                    $myFrontierkey = sha1($min . $seg . $remlabinfo->practice . fullname($USER) .
                        $USER->username . $random);
                } else {
                    $myFrontierkey = sha1($min . $seg . "EjsS Collab" . fullname($USER) . $USER->username . $random);
                }

                $newmyFrontierkey = new stdClass();
                $newmyFrontierkey->user = $USER->username;
                $newmyFrontierkey->enlargepass = $myFrontierkey;
                $newmyFrontierkey->labmanager = $remlabinfo->labmanager;
                $newmyFrontierkey->creationtime = $time;
                $newmyFrontierkey->expirationtime = $time + $remlabinfo->max_use_time;

                $DB->insert_record('block_remlab_manager_sb_keys', $newmyFrontierkey);

                if ($remlabinfo->instance !== false) {
                    $listmyFrontierips = explode(";", get_config('block_remlab_manager', 'myFrontier_IP'));
                    if (empty($myFrontierips)) {
                        $listmyFrontierips = explode(";", get_config('block_remlab_manager', 'myFrontier_IP') . ';');
                    }
                    $myFrontierip = $listmyFrontierips[$remlabinfo->instance];
                    $initpos = strpos($myFrontierip, "'");
                    $endpos = strrpos($myFrontierip, "'");
                    if (!(($initpos === false) || ($initpos === $endpos))) {
                        $myFrontierip = substr($myFrontierip, $endpos + 1);
                    }
                    if (empty($listmyFrontierports)) {
                        $listmyFrontierports = explode(";", get_config('block_remlab_manager', 'myFrontier_IP') . ';');
                    }
                } else {
                    $myFrontierip = $collabinfo->ip;
                }
            }
        }

        if (pathinfo($ejsapp->main_file, PATHINFO_EXTENSION) != 'jar') { // EjsS Javascript.
            global $PAGE;

            /**
             * If a state, recording or blockly file has been configured in the ejsapp activity, this function returns the
             * information of such file
             *
             * @param stdClass $ejsapp
             * @param string $datatype
             * @return stdClass $initialdatafile
             * @throws
             */
            function initial_data_file($ejsapp, $datatype)
            {
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
            function get_data_file($userdatafile, $initialdatafile)
            {
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

            /**
             * Call javascript code for loading state, interaction and blockly programs files as well as personalized variables
             *
             * @param string $userstatefile
             * @param string $userrecfile
             * @param string $userblkfile
             * @param stdClass $ejsapp
             * @param stdClass $collabinfo
             * @param stdClass $personalvarsinfo
             * @throws
             */
            function load_configuration($userstatefile, $userrecfile, $userblkfile, $ejsapp, $collabinfo, $personalvarsinfo)
            {
                global $PAGE;

                // Load state files.
                $initialstatefile = initial_data_file($ejsapp, 'xmlfiles');
                if ($userstatefile || (isset($initialstatefile->filename)) && $initialstatefile->filename != '.') {
                    $statefile = get_data_file($userstatefile, $initialstatefile);
                    $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'readStateFile', array($statefile));
                }

                // Load interaction recording files.
                $initialrecfile = initial_data_file($ejsapp, 'recfiles');
                if ($userrecfile || (isset($initialrecfile->filename) && $initialrecfile->filename != '.')) {
                    $endmessage = get_string('end_message', 'ejsapp');
                    $recfile = get_data_file($userrecfile, $initialrecfile);
                    $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'playRecFile',
                        array($recfile, $endmessage));
                }

                // Load blockly program files.
                $initialblkfile = initial_data_file($ejsapp, 'blkfiles');
                if ($userblkfile || (isset($initialblkfile->filename) && $initialblkfile->filename != '.')) {
                    $blocklyconf = json_decode($ejsapp->blockly_conf);
                    if ($blocklyconf[0] == 1) {
                        $blkfile = get_data_file($userblkfile, $initialblkfile);
                        $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'readBlocklyFile',
                            array($blkfile));
                    }
                }

                // Load personalized variables.
                if (!$collabinfo && isset($personalvarsinfo->name) && isset($personalvarsinfo->value)
                    && isset($personalvarsinfo->type)) {
                    $personalizevars = "'{";
                    for ($i = 0; $i < count($personalvarsinfo->name); $i++) {
                        $personalizevars .= '"' . $personalvarsinfo->name[$i] . '":' . $personalvarsinfo->value[$i];
                        if ($i < count($personalvarsinfo->name) - 1) {
                            $personalizevars .= ",";
                        }
                    }
                    $personalizevars .= "}'";
                    $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'personalizeVariables',
                        array($personalizevars));
                }
            }

            // For remote labs and collaborative sessions only
            if (($ejsapp->is_rem_lab || $collabinfo) || $remlabinfo) {
                if ($remlabinfo) {
                    if ($remlabinfo->instance !== false) {
                        // For remote labs accessed through myFrontier, pass authentication params to the app.
                        $practice = explode("@", $remlabinfo->practice, 2);
                        // TODO: Replace $CFG->wwwroot by get_config('mod_ejsapp', 'server_id')?
                        $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'myFrontierCredentials',
                            array($USER->username . "@" . $CFG->wwwroot, $myFrontierkey));
                        $myGatewayExperiences = get_experiences_mygateway("", 0, true);
                        $pos = strpos($myGatewayExperiences, $remlabinfo->practice);
                        if ($pos !== false) { // myGateway experience
                            $end = substr($myGatewayExperiences, 0, $pos-1);
                            $length = $pos - strrpos($end, ';') -2;
                            $myGatewayip = substr($end, -$length);
                            $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'myFrontierRun',
                                array($myGatewayip, strtolower($practice[1]), $practice[0],
                                    $CFG->wwwroot . '/course/view.php?id=' . $COURSE->id));
                        } else { // myFrontier experience
                            $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'myFrontierRun',
                                array($myFrontierip, 'SARLABV8.0', $practice[0],
                                    $CFG->wwwroot . '/course/view.php?id=' . $COURSE->id));
                        }
                    }
                }
                // Make sure the Javascript application doesn't stop when losing focus and set SSE info for collab.
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

            if (!is_null($userdatafiles)) {
                $userstatefile = $userdatafiles[0];
                $userrecfile = $userdatafiles[1];
                $userblkfile = $userdatafiles[2];
            } else {
                $userstatefile = null;
                $userrecfile = null;
                $userblkfile = null;
            }

            load_configuration($userstatefile, $userrecfile, $userblkfile, $ejsapp, $collabinfo, $personalvarsinfo);
        } else if (isset($remlabinfo)) { // EjsS Java.
            if ($remlabinfo->instance !== false) {
                global $PAGE;
                $practice = explode("@", $remlabinfo->practice, 2);
                $filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'compressed',
                    'itemid' => $ejsapp->id, 'filename' => $ejsapp->main_file), 'filesize DESC');
                $filerecord = reset($filerecords);
                $fs = get_file_storage();
                $file = $fs->get_file_by_id($filerecord->id);
                $pathfile = $CFG->wwwroot . "/pluginfile.php/" . $file->get_contextid() . "/" . $file->get_component() . "/" . $file->get_filearea() .
                    "/" . $file->get_itemid() . "/" . $file->get_filename();

                $PAGE->requires->js_call_amd('mod_ejsapp/enlarge_websocket', 'enlargeWebSocket',
                    array('execjar', $myFrontierip, 443, $practice, $remlabinfo->max_use_time / 60,
                        $USER->username . "@" . $CFG->wwwroot, $myFrontierkey, $pathfile));
            }
        }
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