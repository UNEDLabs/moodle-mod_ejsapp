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
    /** @var string $class_file is either the name of the EjsS app main class (if Java) or an empty string (if Javascript) */
    public $class_file = '';
    /** @var stdClass $remlabinfo contains the information related to the remote laboratory */
    public $remlabinfo = null;
    /** @var string $ejsapp name is the name of the EjsS file uploaded to the EJSApp activity */
    public $ejsappname = '';
    /** @var string $sarlabkey is the auth key to access the sarlab experience */
    public $sarlabkey = '';
    /** @var string $sarlabip is the IP address of the sarlab server */
    public $sarlabip = '';
    /** @var string $practice is the name of the sarlab experience */
    public $practice = '';
    /** @var string $wwwpath is the path to the parent directory where the jar file is */
    public $wwwpath = '';
    /** @var string $jarpath is the path to the jar file */
    public $jarpath = '';

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

        $this->class_file = $ejsapp->class_file;
        $this->remlabinfo = $remlabinfo;

        // Sarlab is used to access this remote lab or to establish communication between users participating in a
        // collaborative session.
        if ($remlabinfo) {
            if ($remlabinfo->instance !== false || isset($collabinfo->sarlabport)) {
                $time = time();
                $min = date("i", $time);
                $seg = date("s", $time);
                mt_srand(time());
                $random = mt_rand(0, 1000000);
                if ($remlabinfo) {
                    $sarlabkey = sha1($min . $seg . $remlabinfo->practice . fullname($USER) .
                        $USER->username . $random);
                } else {
                    $sarlabkey = sha1($min . $seg . "EjsS Collab" . fullname($USER) . $USER->username . $random);
                }

                $newsarlabkey = new stdClass();
                $newsarlabkey->user = $USER->username;
                $newsarlabkey->sarlabpass = $sarlabkey;
                $newsarlabkey->labmanager = $remlabinfo->labmanager;
                $newsarlabkey->creationtime = $time;
                $newsarlabkey->expirationtime = $time + $remlabinfo->max_use_time;

                $DB->insert_record('block_remlab_manager_sb_keys', $newsarlabkey);

                if ($remlabinfo->instance !== false) {
                    $listsarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
                    if (empty($sarlabips)) {
                        $listsarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP') . ';');
                    }
                    $sarlabip = $listsarlabips[$remlabinfo->instance];
                    $initpos = strpos($sarlabip, "'");
                    $endpos = strrpos($sarlabip, "'");
                    if (!(($initpos === false) || ($initpos === $endpos))) {
                        $sarlabip = substr($sarlabip, $endpos + 1);
                    }
                    $listsarlabports = explode(";", get_config('block_remlab_manager', 'sarlab_port'));
                    if (empty($listsarlabports)) {
                        $listsarlabports = explode(";", get_config('block_remlab_manager', 'sarlab_IP') . ';');
                    }
                    $sarlabport = $listsarlabports[$remlabinfo->instance];
                } else {
                    $sarlabip = $collabinfo->ip;
                    $sarlabport = $collabinfo->sarlabport;
                }
            }
        }

        if ($ejsapp->class_file == '') { // EjsS Javascript.
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
                if (!$collabinfo && isset($personalvarsinfo->name) && isset($params->personalvarsinfo->value)
                    && isset($params->personalvarsinfo->type)) {
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
            if (($ejsapp->is_rem_lab || $collabinfo) && $remlabinfo) {
                if ($remlabinfo->instance !== false) {
                    // For remote labs accessed through Sarlab, pass authentication params to the app.
                    $practice = explode("@", $remlabinfo->practice, 2);
                    // TODO: Replace $CFG->wwwroot by get_config('mod_ejsapp', 'server_id')?
                    $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'sarlabCredentials',
                        array($USER->username . "@" . $CFG->wwwroot, $sarlabkey));
                    $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'sarlabRun',
                        array($sarlabport == 443, $sarlabip, 'SARLABV8.0', $sarlabport, $practice[0], $CFG->wwwroot .
                            '/course/view.php?id=' . $COURSE->id));
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
        } else {
            $dirpath = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
            $this->ejsappname = $ejsapp->applet_name;
            if (!$remlabinfo || !$remlabinfo->instance === false) {
                // Without Sarlab, launch the Java file as a Web Start Application with the JNLP.
                if (pathinfo($this->ejsappname, PATHINFO_EXTENSION) == 'jar') {
                    $this->ejsappname = substr($ejsapp->applet_name, 0, -4);
                }

                $this->wwwpath = new moodle_url($ejsapp->codebase);
                $mainclass = substr($ejsapp->class_file, 0, -12);
                $context = context_user::instance($USER->id);
                $language = current_language();

                // Create the JNLP file.
                $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                    <jnlp spec=\"1.0+\"
                        codebase=\"$this->wwwpath\"
                        href=\"$this->ejsappname.jnlp\">
                        <information>
                            <title>$this->ejsappname</title>
                            <vendor>Easy Java Simulations</vendor>
                        </information>
                        <resources>
                            <j2se version=\"1.7+\"
                                href=\"http://java.sun.com/products/autodl/j2se\"/>
                            <jar href=\"$this->ejsappname.jar\" main=\"true\"/>
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
                            <argument>{$CFG->wwwroot}/mod/ejsapp/upload_file</argument>
                        </application-desc>
                        <security>
                            <all-permissions/>
                        </security>
                        <update check=\"background\"/>
                    </jnlp>";

                $jnlp = fopen($dirpath . $this->ejsappname . '.jnlp', 'w');
                fwrite($jnlp, $content);
                fclose($jnlp);
            } else {
                $this->jarpath = $CFG->wwwroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' .
                    $ejsapp->id . '/' . $this->ejsappname;
                $practice = explode("@", $remlabinfo->practice, 2);
            }
        }

        if (isset($sarlabip)) $this->sarlabip = $sarlabip;
        if (isset($sarlabkey)) $this->sarlabkey = $sarlabkey;
        if (isset($practice)) $this->practice = $practice[0];
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