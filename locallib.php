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
 * Internal library of functions for module ejsapp
 *
 * All the ejsapp specific functions, needed to implement the module logic, are here.
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Updates the ejsapp, ejsapp_personal_vars, tables and files according to the .jar/.zip information
 *
 * @param stdClass $ejsapp record from table ejsapp
 * @param object $context context module
 * @return boolean ejs_ok
 * @throws
 *
 */
function update_ejsapp_files_and_tables($ejsapp, $context) {
    global $CFG, $DB;

    $maxbytes = get_max_upload_file_size($CFG->maxbytes);

    // Creating the .jar or .zip file in dataroot and updating the files table in the database.
    if ($ejsapp->appletfile) {
        file_save_draft_area_files($ejsapp->appletfile, $context->id, 'mod_ejsapp', 'jarfiles',
            $ejsapp->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1,
                'accepted_types' => array('application/java-archive', 'application/zip')));
    }

    // Creating the state file in dataroot and updating the files table in the database.
    if ($ejsapp->statefile) {
        file_save_draft_area_files($ejsapp->statefile, $context->id, 'mod_ejsapp', 'xmlfiles',
            $ejsapp->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1,
                'accepted_types' => array('text/xml', 'application/xml', 'application/json')));
    }

    // Creating the recording file in dataroot and updating the files table in the database.
    if ($ejsapp->recordingfile) {
        file_save_draft_area_files($ejsapp->recordingfile, $context->id, 'mod_ejsapp', 'recfiles',
            $ejsapp->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));
    }

    // Creating the recording file in dataroot and updating the files table in the database.
    if ($ejsapp->use_blockly == 1) {
        if ($ejsapp->blocklyfile) {
            file_save_draft_area_files($ejsapp->blocklyfile, $context->id, 'mod_ejsapp', 'blkfiles',
                $ejsapp->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));
        }
    }

    // Same with the content of the wording element.
    if ($ejsapp->ejsappwording['itemid']) {
        $ejsapp->appwording = file_save_draft_area_files($ejsapp->ejsappwording['itemid'], $context->id, 'mod_ejsapp',
            'appwording', 0, array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'changeformat' => 1,
                'context' => $context, 'noclean' => 1, 'trusttext' => 0), $ejsapp->appwording);
    }

    // Obtain the uploaded .zip or .jar file from moodledata using the information in the files table.
    $filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'jarfiles',
        'itemid' => $ejsapp->id), 'filesize DESC');
    $filerecord = reset($filerecords);
    $fs = get_file_storage();
    $file = $fs->get_file_by_id($filerecord->id);

    // In case it is an alias to an external repository.
    if (class_exists('repository_filesystem')) {
        if (!is_null($file->get_referencefileid())) {
            $repositoryid = $file->get_repository_id();
            $repositorytypeid = $DB->get_field('repository_instances', 'typeid',
                array('id' => $repositoryid));
            if ($DB->get_field('repository', 'type', array('id' => $repositorytypeid)) == 'filesystem') {
                $repository = repository_filesystem::get_instance($repositoryid);
                $repository->sync_reference($file);
            }
        }
    }

    // Initialize the mod_form elements.
    $ejsapp->class_file = '';
    $ejsapp->manifest = 'EJsS';

    $ext = pathinfo($file->get_filename(), PATHINFO_EXTENSION);
    // Get params and set their corresponding values in the mod_form elements and update the ejsapp table.
    if ($ext == 'jar') { // Java.
        $ejsok = modifications_for_java($context, $ejsapp, $file, false);
    } else { // Javascript.
        $ejsok = modifications_for_javascript($context, $ejsapp, $file);
    }

    // Configuration of blockly.
    $blocklyconf = array();
    array_push($blocklyconf, $ejsapp->use_blockly);
    array_push($blocklyconf, $ejsapp->charts_blockly);
    array_push($blocklyconf, $ejsapp->events_blockly);
    array_push($blocklyconf, $ejsapp->controller_blockly);
    $ejsapp->blockly_conf = json_encode($blocklyconf);

    $DB->update_record('ejsapp', $ejsapp);

    // Update ejsapp_personal_vars table.
    // Personalizing EJS variables: update ejsapp_personal_vars table.
    $oldejsapp = $DB->get_records('ejsapp_personal_vars', array('ejsappid' => $ejsapp->id));
    if (isset($oldejsapp)) {  // We clean all the personalized variables configuration and start over again.
        $DB->delete_records('ejsapp_personal_vars', array('ejsappid' => $ejsapp->id));
    }
    if ($ejsapp->personalvars == 1) {
        $personalvars = new stdClass();
        $personalvars->ejsappid = $ejsapp->id;
        for ($i = 0; $i < count($ejsapp->var_name); $i++) {
            if (strcmp($ejsapp->var_name[$i], '') != 0) { // Variables without name are ignored.
                $personalvars->name = $ejsapp->var_name[$i];
                $typeinfo = 'Boolean';
                $minvalue = 0;
                $maxvalue = 1;
                if ($ejsapp->var_type[$i] == 1) {
                    $typeinfo = 'Integer';
                    $minvalue = $ejsapp->min_value[$i];
                    $maxvalue = $ejsapp->max_value[$i];
                } else if ($ejsapp->var_type[$i] == 2) {
                    $typeinfo = 'Double';
                    $minvalue = $ejsapp->min_value[$i];
                    $maxvalue = $ejsapp->max_value[$i];
                }
                $personalvars->type = $typeinfo;
                $personalvars->minval = $minvalue + 0;
                $personalvars->maxval = $maxvalue + 0;
                $DB->insert_record('ejsapp_personal_vars', $personalvars);
            }
        }
    }

    return $ejsok;
} // End of function update_ejsapp_files_and_tables.

/**
 * Generates the values of the personalized variables in a particular EJS application for a given user.
 *
 * @param stdClass $ejsapp
 * @param stdClass $user
 * @param boolean $shuffle
 * @return stdClass $personalvarsinfo
 * @throws
 *
 */
function personalize_vars($ejsapp, $user, $shuffle) {
    global $DB;

    $personalvarsinfo = null;
    if ($ejsapp->personalvars == 1) {
        $personalvarsinfo = new stdClass();
        $personalvars = $DB->get_records('ejsapp_personal_vars', array('ejsappid' => $ejsapp->id));
        $i = 0;
        foreach ($personalvars as $personalvar) {
            $personalvarsinfo->name[$i] = $personalvar->name;
            $factor = 1;
            if ($personalvar->type == 'Double') {
                $factor = 100;
            }
            $seed1 = array($user->firstname, $user->lastname, $user->id, $personalvar->name);
            $seed2 = array($user->email, $personalvar->minval, $user->username, $personalvar->maxval);
            if ($shuffle) {
                shuffle($seed1);
                shuffle($seed2);
            }
            $seedvalue1 = filter_var(md5($seed1[0] . $seed1[1] . $seed1[2] . $seed1[3]), FILTER_SANITIZE_NUMBER_INT);
            $seedvalue2 = filter_var(md5($seed2[0] . $seed2[1] . $seed2[2] . $seed1[3]), FILTER_SANITIZE_NUMBER_INT);
            $seedval = $seedvalue1 + $seedvalue2;
            mt_srand(intval($seedval));
            $personalvarsinfo->value[$i] = mt_rand($factor * $personalvar->minval, $factor * $personalvar->maxval) / $factor;
            $personalvarsinfo->type[$i] = $personalvar->type;
            $i++;
        }
    }

    return $personalvarsinfo;
}

/**
 * Generates the values of the personalized variables in a particular EJS application for all the users in the course
 * that ejsapp activity is.
 *
 * @param stdClass $ejsapp
 * @return array $userspersonalvarsinfo
 * @throws
 *
 */
function users_personalized_vars($ejsapp) {
    global $DB;

    $enrolids = $DB->get_fieldset_select('enrol', 'id', 'courseid = :courseid',
        array('courseid' => $ejsapp->course));
    $usersids = $DB->get_fieldset_sql('SELECT userid FROM {user_enrolments} WHERE enrolid IN (' .
        implode(',', $enrolids) . ')');
    $users = $DB->get_records_sql('SELECT * FROM {user} WHERE id IN (' . implode(',', $usersids) . ')');
    $userspersonalvarsinfo = array();
    foreach ($users as $user) {
        $userspersonalvarsinfo[$user->id.''] = personalize_vars($ejsapp, $user, false);
    }

    return $userspersonalvarsinfo;
}

/**
 * For EjsS java applications.
 *
 * @param stdClass $context
 * @param stdClass $ejsapp
 * @param stored_file $file
 * @param boolean $alert
 * @return boolean $ejsok
 * @throws
 *
 */
function modifications_for_java($context, $ejsapp, $file, $alert) {
    global $DB, $CFG;

    $ejsok = false;
    $packer = get_file_packer('application/zip');
    if ($file->extract_to_storage($packer, $context->id, 'mod_ejsapp', 'content', $ejsapp->id, '/')) {
        $ejsapp->applet_name = $file->get_filename();

        // Extract the manifest.mf file from the .jar.
        $filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'content',
            'itemid' => $ejsapp->id, 'filename' => 'MANIFEST.MF'), 'filesize DESC');
        $filerecord = reset($filerecords);
        $fs = get_file_storage();
        $manifest = $fs->get_file_by_id($filerecord->id);
        $manifest = $file->get_content();

        // Class_file.
        $ejsapp->class_file = get_class_for_java($manifest);

        // Check whether the EjsS version to build this applet is supported.
        $pattern = '/Applet-Height\s*:\s*(\w+)/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) == 0) {
            // If this field doesn't exist in the manifest, then the EjsS version used to compile the jar does not support Moodle.
            if ($alert) {
                $message = get_string('EJS_version', 'ejsapp');
                echo html_writer::tag("script","window.alert(\"$message\")",
                    array("type" => "text/javascript"));
            }
        } else {
            $ejsok = true;
        }

        // Sign the applet.
        // Check whether a certificate is installed and in use.
        if (file_exists(get_config('mod_ejsapp', 'certificate_path')) &&
            get_config('mod_ejsapp', 'certificate_password') != '' &&
            get_config('mod_ejsapp', 'certificate_alias') != '') {
            // Check whether the applet has the codebase parameter in manifest.mf set to $CFG->wwwroot.
            $pattern = '/\s*\nCodebase\s*:\s*(.+)\s*/';
            preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
            $host = explode("://", $CFG->wwwroot);
            if (substr($matches[1][0], 0, -1) == $host[1]) {
                if (is_null($file->get_referencefileid())) { // Linked files won't get signed.
                    // Sign the applet.
                    shell_exec("jarsigner -storetype pkcs12 -keystore " . get_config('mod_ejsapp',
                            'certificate_path') . " -storepass " .
                        get_config('mod_ejsapp', 'certificate_password') .
                        " -tsa http://timestamp.comodoca.com/rfc3161 " .
                        $file . " " . get_config('mod_ejsapp', 'certificate_alias'));
                    // We replace the file stored in Moodle's filesystem and its table with the signed version.
                    /*$file->delete();
                    $fs = get_file_storage();
                    $fs->create_file_from_pathname($filerecord, $filepath);*/
                }
            } else if ($alert) { // Files without the codebase parameter set to the Moodle server direction are not signed.
                $message = get_string('EJS_codebase', 'ejsapp');
                echo html_writer::tag("script","window.alert(\"$message\")",
                    array("type" => "text/javascript"));
            }
        }
    }

    return $ejsok;
}

/**
 * For EjsS javascript applications.
 *
 * @param stdClass $context
 * @param stdClass $ejsapp
 * @param stored_file $file
 * @return boolean $ejsok
 * @throws
 *
 */
function modifications_for_javascript($context, $ejsapp, $file) {
    global $DB;

    $ejsok = false;
    $packer = get_file_packer('application/zip');
    if ($file->extract_to_storage($packer, $context->id, 'mod_ejsapp', 'content', $ejsapp->id, '/')) {
        // Search in _metadata for the name of the main Javascript file and save it.
        $filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'content',
            'itemid' => $ejsapp->id, 'filename' => '_metadata.txt'), 'filesize DESC');
        $filerecord = reset($filerecords);
        $fs = get_file_storage();
        $file = $fs->get_file_by_id($filerecord->id);
        $metadata = $file->get_content();
        $pattern = '/main-simulation\s*:\s*(.+)\s*/';
        preg_match($pattern, $metadata, $matches, PREG_OFFSET_CAPTURE);
        $substr = $matches[1][0];
        if (strlen($matches[1][0]) == 59) {
            $pattern = '/^\s(.+)\s*/m';
            if ((preg_match($pattern, $metadata, $matches, PREG_OFFSET_CAPTURE) > 0)) {
                $substr = $substr . $matches[1][0];
            }
        }
        $ejsapp->applet_name = pathinfo(rtrim($substr), PATHINFO_FILENAME);

        if (!empty($filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'content',
            'itemid' => $ejsapp->id, 'filename' => $ejsapp->applet_name . '.js'), 'filesize DESC'))) {
            $mainfile = 'javascript';
        } else if (!empty($filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'content',
            'itemid' => $ejsapp->id, 'filename' => $ejsapp->applet_name . '.xhtml'), 'filesize DESC'))) {
            $mainfile = 'xhtml';
        }
        if (!empty($filerecords)) {
            // Edit file content to replace context-dependent content (i.e. the EjsS' _model variable declaration)
            $filerecord = reset($filerecords);
            $fs = get_file_storage();
            $originalfile = $fs->get_file_by_id($filerecord->id);
            $originalfilecontent = $originalfile->get_content();
            $pathfiles = new moodle_url("/pluginfile.php/" . $file->get_contextid() . "/" . $file->get_component() .
                "/content/" . $file->get_itemid());
            $ejsslibpathfiles = $pathfiles . "/_ejs_library/";
            $newfilecontent = str_replace("(\"_topFrame\",\"_ejs_library/\",null);",
                "(\"_topFrame\",\"$ejsslibpathfiles\",\"$pathfiles\");",
                $originalfilecontent);
            $originalfile->delete();
            if ($mainfile == "xhtml") {
                // Extract javascript code from the .xhtml file into a new .js file
                $newfilecontent = strstr($newfilecontent, 'function ' .
                    strstr($ejsapp->applet_name, '_Simulation', true));
                $newfilecontent1 = strstr($newfilecontent, '//--><!]]></script>', true);
                $newfilecontent2 = strstr($newfilecontent, 'var _model;');
                $newfilecontent2 = strstr($newfilecontent2, '//--><!]]></script>', true);
                $newfilecontent = $newfilecontent1 . $newfilecontent2;
                $filerecord->filename = $ejsapp->applet_name . '.js';
            }
            $fs->create_file_from_string($filerecord, $newfilecontent);
            $ejsok = true;
        }
    }

    return $ejsok;
}

/**
 * Gets the java .class from the manifest
 *
 * @param string $manifest
 * @return string $classfile
 *
 */
function get_class_for_java($manifest) {
    $pattern = '/Main-Class\s*:\s*(.+)\s*/';
    preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
    $substr = $matches[1][0];
    if (strlen($matches[1][0]) == 59) {
        // Delete all white-spaces and the first newline.
        if (preg_match('/^\s(.+)\s*/m', $manifest, $matches, PREG_OFFSET_CAPTURE) > 0) {
            if (preg_match('/\s*:\s*/', $matches[1][0], $matches2, PREG_OFFSET_CAPTURE) == 0) {
                $substr = $substr . $matches[1][0];
            }
        }
    }
    $classfile = $substr . 'Applet.class';

    return $classfile;
}

/**
 * Recursively change permissions for the jarfiles directory and subdirectories
 *
 * @param string $path
 *
 */
function chmod_r($path) {
    $dir = new DirectoryIterator($path);
    foreach ($dir as $item) {
        if (!is_dir($item->getPathname())) {
            chmod($item->getPathname(), 0644);
        } else {
            chmod($item->getPathname(), 0755);
        }
        if ($item->isDir() && !$item->isDot()) {
            chmod_r($item->getPathname());
        }
    }
}

/**
 * Checks if a remote lab equipment is alive or not, either directly when it has a public IP or by asking Sarlab.
 *
 * @param string $host
 * @param int $port
 * @param boolean|string|int $sarlabinstance
 * @param string $expid
 * @param int $timeout
 * @return int 0, not alive; 1, alive; 2, not checkable
 *
 */
function ping($host, $port, $sarlabinstance, $expid=null, $timeout=3) {
    global $devicesinfo;

    $alive = fsockopen($host, $port, $errno, $errstr, $timeout);
    $notcheckable = false;
    if ($alive && $sarlabinstance !== false) {
        $uri = 'http://' . $host . '/SARLABV8.0/';
        $headers = @get_headers($uri);
        if (substr($headers[0], 9, 3) == 200) { // Valid url.
            if ($expid != null) {
                $expid = explode('@', $expid);
                $expid = urlencode($expid[0]);
            }
            $url = $uri . 'webresources/net/isLive?idExp=' . $expid;
            if ($response = file_get_contents($url)) {
                $alive = ($response === 'true');
            } else {
                $notcheckable = true;
            }
        } else {
            $notcheckable = true;
        }
    }
    if ($notcheckable) {
        return 2;
    }
    if ($alive) {
        return 1;
    } else {
        // Get list of devices in the experience that are not alive and see which ones are down.
        //$devices = explode("Ping to ", $response);

        /**
         * Gets a string between an initial and a final string.
         *
         * @param string $string
         * @param string $start
         * @param string $end
         * @return string
         *
         */
        /*function get_string_between($string, $start, $end) {
            $string = " ".$string;
            $ini = strpos($string, $start);
            if ($ini == 0) {
                return "";
            }
            $ini += strlen($start);
            $len = strpos($string, $end, $ini) - $ini;
            return substr($string, $ini, $len);
        }

        foreach ($devices as $device) {
            $devicesinfo[]->name = get_string_between($device, ": ", "ping ");
            $ip = get_string_between($device, "ping ", "Reply from ");
            $devicesinfo[]->ip = $ip;
            $url = $uri . 'isAlive?' . $ip;
            if ($info = file_get_contents($url)) {
                $devicesinfo[]->alive = (mb_strtoupper(trim($info[1])) === mb_strtoupper("true")) ? true : false;
            }
        }*/
        return 0;
    }
}

/**
 * Creates the list of all Sarlab experiences. If a username is provided, it only returns those practices accessible by
 * this particular user.
 *
 * @param array $sarlabips
 * @param string $username
 * @param int $ejsappcontext 0 if block remlab_manager, 1 if mod ejsapp
 * @return string $listexperiences
 * @throws
 *
 */
function get_experiences_sarlab($sarlabips, $username = "", $ejsappcontext = 0) {
    global $USER;
    $listexperiences = '';

    $context = context_system::instance();
    if ($username != "" && !has_capability('ltisource/sarlab:editexp', $context, $USER->id, false)){
        return $listexperiences;
    }

    foreach ($sarlabips as $sarlabip) {
        $lastquotemark = strrpos($sarlabip, "'");
        if ($lastquotemark != 0) {
            $lastquotemark++;
        }
        $ip = substr($sarlabip, $lastquotemark);
        $firstquotemark = strpos($sarlabip, "'");
        if ($firstquotemark !== false) {
            $firstquotemark++;
            $name = substr($sarlabip, $firstquotemark, $lastquotemark - 1 - $firstquotemark);
        } else {
            $name = $ip;
        }
        if ($ip != '127.0.0.1' && $ip != '') {
            if ($fp = fsockopen($ip, '80', $errorcode, $errorstring, 3)) { // IP is alive.
                fclose($fp);
                $uri = 'http://' . $ip . '/SARLABV8.0/gexlab';
                $headers = get_headers($uri);
                if (substr($headers[0], 9, 3) == 200) { // Valid file.
                    if ($xml = simplexml_load_file($uri)) {
                        $map = $xml->MapExperience;
                        $listsarlabexperiences = $map->Experience; // Get list of experiences.
                        foreach ($listsarlabexperiences as $experience) {
                            $experimentsettings = $experience->ExperimentSettings;
                            // Get list of Moodle servers and users who can access the experience.
                            $listownermoodleusers = $experimentsettings->listOfOwnersExperience;
                            // Get list of users in this Moodle server who can access the experience
                            $moodleservers = $listownermoodleusers->ServerMoodle;
                            if ($moodleservers != null) {
                                foreach ($moodleservers as $moodleserver) {
                                    // Check whether this Moodle server is registered in the experience.
                                    if ($moodleserver['IdMoodle'] == get_config('mod_ejsapp', 'server_id')) {
                                        if ($username != "") {
                                            // If username is provided, check users permissions both in Moodle and Sarlab.
                                            $ownerusers = $moodleserver->Owner;
                                            if ($ejsappcontext == 0) {
                                                $cap = get_capability_info('ltisource/sarlab:editexp');
                                            } else {
                                                $cap = get_capability_info('ltisource/sarlab:useexp');
                                            }
                                            if ($cap) {
                                                foreach ($ownerusers as $owneruser) {
                                                    // Check whether the required user has access to the experience.
                                                    if (strcasecmp($username, $owneruser) == 0) {
                                                        $listexperiences .= $experience['IdExp'] . '@' . $name . ';';
                                                        break;
                                                    }
                                                }
                                            }
                                        } else {
                                            // If not, the whole list of Sarlab experiences must be returned, so add it.
                                            $listexperiences .= $experience['IdExp'] . '@' . $name . ';';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    $listexperiences = substr($listexperiences, 0, -1);

    return $listexperiences;
}

/**
 * Gets the local experiences (defined without sarlab) and combines them with those in Sarlab in a unique, ordered list.
 *
 * @param array $usersarlabexperiences
 * @param array $allsarlabexperiences
 * @return array $combinedexperiences
 * @throws
 *
 */
function combine_experiences($usersarlabexperiences, $allsarlabexperiences) {
    global $DB;
    $remlabmanager = $DB->get_records('block', array('name' => 'remlab_manager'));
    $remlabmanager = !empty($remlabmanager);

    if ($remlabmanager) {
        $localexperiences = $DB->get_records('block_remlab_manager_conf');
        $combinedexperiences = array();
        if ($usersarlabexperiences[0] != '') {
            $combinedexperiences = $usersarlabexperiences;
        }
        foreach ($localexperiences as $localexperience) {
            if (!in_array($localexperience->practiceintro, $allsarlabexperiences)) {
                $combinedexperiences[] = $localexperience->practiceintro;
            }
        }
    } else {
        $combinedexperiences = $usersarlabexperiences;
    }

    // Order the list alphabetically.
    sort($combinedexperiences);

    return $combinedexperiences;
}

/**
 * Gets the experiences defined without sarlab and combines them with those in Sarlab in a unique, ordered list.
 *
 * @param string $username
 * @param int $ejsappcontext 0 if block remlab_manager, 1 if mod ejsapp
 * @return array $showableexperiences
 * @throws
 *
 */
function get_showable_experiences($username = "", $ejsappcontext = 0) {
    $sarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
    if (empty($sarlabips)) {
        $sarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP') . ';');
    }
    // Get experiences from Sarlab.
    $userlistexperiences = ($username != "") ? get_experiences_sarlab($sarlabips, $username, $ejsappcontext) : "";
    $alllistexperiences = get_experiences_sarlab($sarlabips, "");
    $usersarlabexperiences = ($username != "") ? explode(";", $userlistexperiences) : array("");
    $allsarlabexperiences = explode(";", $alllistexperiences);
    // Also get experiences NOT in Sarlab and add them to the list.
    $showableexperiences = combine_experiences($usersarlabexperiences, $allsarlabexperiences);

    return $showableexperiences;
}

/**
 * Creates a default practice record for the block_remlab_manager_conf table
 *
 * @param string $practice
 * @param string $username
 * @return stdClass $defaultconf
 * @throws
 *
 */
function default_rem_lab_conf($practice, $username = "") {
    // Get experiences from Sarlab and check whether this practice is in a Sarlab server or not.
    $sarlabinstance = is_practice_in_sarlab($practice, $username, 1);
    $defaultconf = new stdClass();
    if ($sarlabinstance !== false) { // Practice is defined in a Sarlab server
        $sarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
        if (empty($sarlabips)) {
            $sarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP') . ';');
        }
        $sarlabports = explode(";", get_config('block_remlab_manager', 'sarlab_port'));
        if (empty($sarlabports)) {
            $sarlabports = explode(";", get_config('block_remlab_manager', 'sarlab_IP') . ';');
        }
        $initchar = strrpos($sarlabips[intval($sarlabinstance)], "'");
        if ($initchar != 0) {
            $initchar++;
        }
        $ip = substr($sarlabips[intval($sarlabinstance)], $initchar);
        $defaultconf->ip = $ip;
        $defaultconf->port = $sarlabports[intval($sarlabinstance)];
    } else {
        $arr = explode("@", $practice, 2);
        $practice = $arr[0];
        $defaultconf->ip = '127.0.0.1';
        $defaultconf->port = 443;
    }
    $defaultconf->practiceintro = $practice;
    $defaultconf->slotsduration = 1;
    $defaultconf->totalslots = 18;
    $defaultconf->weeklyslots = 9;
    $defaultconf->dailyslots = 3;
    $defaultconf->reboottime = 2;
    $defaultconf->active = 1;
    $defaultconf->free_access = 0;

    return $defaultconf;
}

/**
 * Checks whether a user-accessible practice is defined in an operative Sarlab server and returns the Sarlab instance if
 * it is, or false if it is not.
 *
 * @param string $practice
 * @param string $username
 * @param int $ejsappcontext 0 if block remlab_manager, 1 if mod ejsapp
 * @return false|int $sarlabinstance
 * @throws
 *
 */
function is_practice_in_sarlab($practice, $username = "", $ejsappcontext = 0) {
    $sarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
    if (empty($sarlabips)) {
        $sarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP') . ';');
    }
    $sarlabinstance = false;
    $instance = 0;
    foreach ($sarlabips as $sarlabip) {
        $sarlabiparray = array($sarlabip);
        $listexperiences = get_experiences_sarlab($sarlabiparray, $username, $ejsappcontext);
        $sarlabexperiences = explode(";", $listexperiences);
        if (in_array($practice, $sarlabexperiences)) {
            $sarlabinstance = $instance;
            break;
        }
        $instance++;
    }
    return $sarlabinstance;
}

/**
 * Checks whether the lab experience has a local configuration defined, creates a default configuration if not, and
 * returns the data.
 *
 * @param string $practice
 * @return stdClass $remlab_conf
 * @throws
 *
 */
function check_create_remlab_conf($practice) {
    global $DB;

    if ($DB->record_exists('block_remlab_manager_conf', array('practiceintro' => $practice))) {
        $remlab_conf = $DB->get_record('block_remlab_manager_conf', array('practiceintro' => $practice));
    } else {
        if ($practice != '') {
            $remlab_conf = default_rem_lab_conf($practice);
            $DB->insert_record('block_remlab_manager_conf', $remlab_conf);
        } else {
            $remlab_conf = null;
        }
    }

    return $remlab_conf;
}

/**
 * Creates the record for the block_remlab_manager_exp2prc table
 *
 * @param stdClass $ejsapp
 * @return void
 * @throws
 *
 */
function ejsapp_expsyst2pract($ejsapp) {
    global $DB;

    $exp2pract = new stdClass();
    $exp2pract->ejsappid = $ejsapp->id;
    $exp2pract->practiceid = 1;
    $exp2practlist = $ejsapp->list_practices;
    $exp2practlist = explode(';', $exp2practlist);
    $exp2pract->practiceintro = $exp2practlist[$ejsapp->practiceintro];
    $DB->insert_record('block_remlab_manager_exp2prc', $exp2pract);
}

/**
 * Checks whether a the booking system is being used in the course of a particular ejsapp activity or not.
 *
 * @param stdClass $ejsapp
 * @return bool $usingbookings
 * @throws
 *
 */
function check_booking_system($ejsapp) {
    global $DB;

    $usingbookings = false;
    // Check whether EJSApp Booking System plugins is installed or not.
    if ($DB->record_exists('modules', array('name' => 'ejsappbooking'))) {
        $module = $DB->get_record('modules', array('name' => 'ejsappbooking'));
        // Check whether there is an ejsappbooking instance in the course or not.
        if ($DB->record_exists('course_modules', array('course' => $ejsapp->course, 'module' => $module->id))) {
            // Check whether the booking system resource is visible (in use) or not.
            if ($DB->get_field('course_modules', 'visible',  array('course' => $ejsapp->course,
                'module' => $module->id))) {
                $usingbookings = true;
            }
        }
    }

    return $usingbookings;
}

/**
 * Checks if there is an active booking made by the current user for the remote lab and gets information needed by sarlab
 *
 * @param object $DB
 * @param object $USER
 * @param stdClass $ejsapp
 * @param string $currenttime
 * @param boolean|string|int $sarlabinstance
 * @param int $labmanager
 * @param int $maxusetime
 * param int $maxusetime
 * @return stdClass $remlabinfo
 *
 */
function check_users_booking($DB, $USER, $ejsapp, $currenttime, $sarlabinstance, $labmanager, $maxusetime) {
    $remlabinfo = null;

    if ($DB->record_exists('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id,
        'valid' => 1))) {
        $bookings = $DB->get_records('ejsappbooking_remlab_access', array('username' => $USER->username,
            'ejsappid' => $ejsapp->id, 'valid' => 1));
        $currenttime = strtotime($currenttime) + 60; // User must still have at least one minute of lab time ahead.
        $currenttime = date('Y-m-d H:i:s', $currenttime);
        foreach ($bookings as $booking) { // If the user has an active booking, use that info.
            if ($currenttime >= $booking->starttime && $currenttime < $booking->endtime) {
                $expsyst2pract = $DB->get_record('block_remlab_manager_exp2prc', array('ejsappid' => $ejsapp->id,
                    'practiceid' => $booking->practiceid));
                $practice = $expsyst2pract->practiceintro;
                $remlabinfo = define_remlab($sarlabinstance, 0, $practice, $labmanager, $maxusetime);
                break;
            }
        }
    }

    return $remlabinfo;
}

/**
 * Checks if there is an active booking made by the current user and if there is, it gets the ending time of the farest
 * consecutive booking.
 *
 * @param object $DB
 * @param string $username
 * @param int $ejsappid
 * @return string $endtime
 *
 */
function check_last_valid_booking($DB, $username, $ejsappid) {
    $endtime = 0;
    $currenttime = date('Y-m-d H:i:s');

    if ($DB->record_exists('ejsappbooking_remlab_access', array('username' => $username, 'ejsappid' => $ejsappid,
        'valid' => 1))) {
        $bookings = $DB->get_records('ejsappbooking_remlab_access', array('username' => $username,
            'ejsappid' => $ejsappid, 'valid' => 1));
        /**
         * Substracts a time from another.
         *
         * @param object $a
         * @param object $b
         * @return int|false
         *
         */
        if (!function_exists('cmp')) {
            function cmp($a, $b) {
                return strtotime($a->starttime) - strtotime($b->starttime);
            }
        }
        usort($bookings, "cmp");
        foreach ($bookings as $booking) { // If the user has an active booking, check the time.
            if ($currenttime >= $booking->starttime && $currenttime < $booking->endtime) {
                $endtime = $booking->endtime;
                $currenttime = strtotime($endtime) + 1;
                $currenttime = date("Y-m-d H:i:s", $currenttime);
            }
        }
    }

    return $endtime;
}

/**
 * Checks if there is an active booking made by any user and if it is, it returns the username.
 *
 * @param object $DB
 * @param stdClass $ejsapp
 * @return string $username
 *
 */
function check_anyones_booking($DB, $ejsapp) {
    $username = '';

    if ($DB->record_exists('ejsappbooking_remlab_access', array('ejsappid' => $ejsapp->id, 'valid' => 1))) {
        $bookings = $DB->get_records('ejsappbooking_remlab_access', array('ejsappid' => $ejsapp->id, 'valid' => 1));
        $currenttime = date('Y-m-d H:i:s');
        foreach ($bookings as $booking) { // If the user has an active booking, use that info.
            if ($currenttime >= $booking->starttime && $currenttime < $booking->endtime) {
                $username = $booking->username;
            }
        }
    }

    return $username;
}

/**
 * Gets information about the first and last access a user made to the remote lab and the allowed maximum time of use.
 *
 * @param array $repeatedlabs
 * @param stdClass $ejsapp
 * @return stdClass $timeinfo
 * @throws
 *
 */
function remote_lab_use_time_info($repeatedlabs, $ejsapp) {
    global $DB;

    $userwithbooking = check_anyones_booking($DB, $ejsapp);
    $repeatedlab = reset($repeatedlabs);
    $practiceintro = $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro',
        array('ejsappid' => $repeatedlab->id));
    $currenttime = time();
    if ($userwithbooking !== '') {
        $maxusetime = strtotime(check_last_valid_booking($DB, $userwithbooking, $ejsapp->id)) - $currenttime;
    } else {
        $slotsdurationconf = $DB->get_field('block_remlab_manager_conf',
            'slotsduration', array('practiceintro' => $practiceintro));
        if ($slotsdurationconf > 4) {
            $slotsdurationconf = 0;
        }
        $maxslots = $DB->get_field('block_remlab_manager_conf',
            'dailyslots', array('practiceintro' => $practiceintro));
        $slotsduration = array(60, 30, 15, 5, 2);
        $maxusetime = $maxslots * 60 * $slotsduration[$slotsdurationconf];
    }

    $ids = array();
    $names = array();
    foreach ($repeatedlabs as $repeatedlab) {
        $ids[] = $repeatedlab->id;
        $names[] = $repeatedlab->name;
    }

    $dbman = $DB->get_manager();
    $standardlog = $dbman->table_exists('logstore_standard_log');

    // Retrieve information from Moodle's or ejsapp's logging table.
    // TODO: Change queries when we stop resetting time when a user in a remote lab refreshes the page.
    if ($standardlog) {
        $select = 'component = :component AND action = :action AND timecreated > :timecreated AND objectid ';
        list($sql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
        $select .= $sql;
        $queryparams = ['component' => 'mod_ejsapp', 'action' => 'working', 'timecreated' => $currenttime - $maxusetime];
        $queryparams += $params;
        $timelastaccess = $DB->get_field_select('logstore_standard_log', 'MAX(timecreated)', $select, $queryparams);
        $queryparams = ['component' => 'mod_ejsapp', 'action' => 'viewed', 'timecreated' => $currenttime - $maxusetime];
        $queryparams += $params;
        $timefirstaccess = $DB->get_field_select('logstore_standard_log', 'MAX(timecreated)', $select, $queryparams);
        // Get last user:
        /*$select = 'component = :component AND action = :action AND timecreated = :timecreated AND objectid ';
        list($sql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
        $select .= $sql;
        $queryparams = ['component' => 'mod_ejsapp', 'action' => 'working', 'timecreated' => $timelastaccess];
        $queryparams += $params;
        $lastuserid = $DB->get_field_select('logstore_standard_log', 'userid', $select, $queryparams);*/
    } else {
        $select = 'action = :action AND time > :time AND info ';
        list($sql, $params) = $DB->get_in_or_equal($names, SQL_PARAMS_NAMED);
        $select .= $sql;
        $queryparams = ['action' => 'working', 'time' => $currenttime - $maxusetime];
        $queryparams += $params;
        $timelastaccess = $DB->get_field_select('log', 'MAX(time)', $select, $queryparams);
        $queryparams = ['action' => 'viewed', 'time' => $currenttime - $maxusetime];
        $queryparams += $params;
        $timefirstaccess = $DB->get_field_select('log', 'MAX(time)', $select, $queryparams);
        // Get last user:
        /*$select = 'action = :action AND time = :time AND info ';
        list($sql, $params) = $DB->get_in_or_equal($names, SQL_PARAMS_NAMED);
        $select .= $sql;
        $queryparams = ['action' => 'working', 'time' => $timelastaccess];
        $queryparams += $params;
        $lastuserid = $DB->get_field_select('log', 'userid', $select, $queryparams);*/
    }

    $timeinfo = new stdClass;
    if ($timelastaccess) {
        $timeinfo->time_last_access = $timelastaccess;
    } else {
        $timeinfo->time_last_access = 0;
    }
    if ($timefirstaccess) {
        $timeinfo->time_first_access = $timefirstaccess;
    } else {
        $timeinfo->time_first_access = 0;
    }
    //$timeinfo->last_user_id = $lastuserid;
    $timeinfo->max_use_time = $maxusetime;
    $timeinfo->reboottime = $DB->get_field('block_remlab_manager_conf',
        'reboottime', array('practiceintro' => $practiceintro));

    return $timeinfo;
}

/**
 * Get remaining time till the lab is again available.
 *
 * @param stdClass $remlabconf
 * @param int $timefirstaccess
 * @param int $timelastaccess
 * @param int $maxusetime
 * @param int $reboottime
 * @param int $checkactivity
 * @return int $remainingtime
 * @throws
 *
 */
function get_wait_time($remlabconf, $timefirstaccess, $timelastaccess, $maxusetime, $reboottime, $checkactivity) {
    if ($remlabconf->usestate == 'in use') {
        $remainingtime = $timefirstaccess + $maxusetime + 60 * $reboottime + $checkactivity - time();
    } else if ($remlabconf->usestate == 'rebooting') {
        if ($reboottime == 0) {
            $remainingtime = 0;
        } else {
            $remainingtime = $timelastaccess + 60 * $reboottime + $checkactivity - time();
        }
    } else {
        $remainingtime = 0;
    }
    make_lab_available($remainingtime, $remlabconf);

    return $remainingtime;
}

/**
 * Makes a lab available again if conditions are met.
 *
 * @param int $waittime
 * @param stdClass $remlabconf
 * @throws
 *
 */
function make_lab_available($waittime, $remlabconf) {
    global $DB;

    if ($waittime <= 0) {
        // In this case, wait should be over and we are only waiting for cron to be run to update the lab state
        // We make the update ourselves:
        $remlabconf->usestate = 'available';
        $DB->update_record('block_remlab_manager_conf', $remlabconf);
    }
}

/**
 * Checks whether a particular remote lab is also present in other ejsapp activities or not and returns the list.
 *
 * @param string $practiceintro
 * @return array $repeatedlabs
 * @throws
 *
 */
function get_repeated_remlabs($practiceintro) {
    global $DB;

    $repeatedpractices = $DB->get_records('block_remlab_manager_exp2prc',
        array('practiceintro' => $practiceintro));
    $ejsappids = array();
    foreach ($repeatedpractices as $repeatedpractice) {
        array_push($ejsappids, $repeatedpractice->ejsappid);
    }
    $repeatedlabs = $DB->get_records_list('ejsapp', 'id', $ejsappids);

    return $repeatedlabs;
}

/**
 * Gives the list of repeated remote labs in courses with a booking system.
 *
 * @param array $repeatedlabs
 * @return array $repeatedlabswithbs
 *
 */
function get_repeated_remlabs_with_bs($repeatedlabs) {

    $repeatedlabswithbs = array();
    foreach ($repeatedlabs as $repeatedlab) {
        if (check_booking_system($repeatedlab)) {
            array_push($repeatedlabswithbs, $repeatedlab);
        }
    }

    return $repeatedlabswithbs;
}

/**
 * Tells if there is at least one different course in which the same remote lab has been booked for this hour and if it
 * is, it returns an array with the name of the user with the booking and the id of that ejsapp activity.
 *
 * @param array $repeatedlabs
 * @param int $courseid
 * @return array $bookinfo
 *
 */
function check_active_booking($repeatedlabs, $courseid = null) {
    global $DB;

    $bookinfo = array();
    $bookinfo['active_booking'] = false;
    if (count($repeatedlabs) > 0) {
        $repeatedlabswithbs = get_repeated_remlabs_with_bs($repeatedlabs);
        if (count($repeatedlabswithbs) > 0) {
            foreach ($repeatedlabswithbs as $repeatedlabwithbs) {
                if ($repeatedlabwithbs->course != $courseid) {
                    $bookinfo['username_with_booking'] = check_anyones_booking($DB, $repeatedlabwithbs);
                    if (!empty($bookinfo['username_with_booking'])) {
                        $bookinfo['active_booking'] = true;
                        $bookinfo['ejsappid'] = $repeatedlabwithbs->id;
                        break;
                    }
                }
            }
        }
    }

    return $bookinfo;
}

/**
 * Returns some info about or related to the access conditions to the remote lab: required idle time, whether the user
 * is a manager of that lab or not, whether the lab is available or not, whether the user can access the lab freely or
 * not and whether the lab uses sarlab or not.
 *
 * @param stdClass $ejsapp
 * @param stdClass $course
 * @return stdClass $remote_lab_info
 * @throws
 *
 */
function remote_lab_access_info($ejsapp, $course) {
    global $DB, $USER;

    $coursecontext = context_course::instance($course->id);
    $remlabaccess = new stdClass;

    $practice = $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro', array('ejsappid' => $ejsapp->id));
    $remlabaccess->remlab_conf = check_create_remlab_conf($practice);

    // Check if the remote lab is operative.
    $remlabaccess->operative = true;
    $labactive = $DB->get_field('block_remlab_manager_conf', 'active', array('practiceintro' => $practice));
    if ($labactive == 0) {
        $remlabaccess->operative = false;
    }

    // Check if we should grant free access to the user for this remote lab.
    $remlabaccess->allow_free_access = true;
    $remlabaccess->labmanager = has_capability('mod/ejsapp:accessremotelabs', $coursecontext, $USER->id, true);
    $remlabaccess->repeated_ejsapp_labs = get_repeated_remlabs($practice);
    $remlabaccess->booking_info = check_active_booking($remlabaccess->repeated_ejsapp_labs, $course->id);
    $usingbookingsystem = check_booking_system($ejsapp);
    if (!$remlabaccess->labmanager) { // The user does not have special privileges and...
        if (($remlabaccess->remlab_conf->free_access != 1) && $usingbookingsystem) {
            // Not free access and the booking system is in use.
            $remlabaccess->allow_free_access = false;
        } else if (($remlabaccess->remlab_conf->free_access == 1) && $remlabaccess->booking_info['active_booking']) {
            // Free access and there is an active booking for this remote lab made by anyone in a different course.
            $remlabaccess->allow_free_access = false;
        } else if (($remlabaccess->remlab_conf->free_access != 1) && !$usingbookingsystem
            && $remlabaccess->booking_info['active_booking']) {
            // Not free access, the booking system is not in use and there is an active booking for this remote lab made
            // by anyone in a different course.
            $remlabaccess->allow_free_access = false;
        }
    }

    return $remlabaccess;
}

/**
 * Defines a new remlab object with all the needed information
 *
 * @param false|int $instance sarlab instance
 * @param boolean $collab false if not a collab session, true if collaborative
 * @param string $practice the practice identifier in sarlab
 * @param int $labmanager whether the user is a laboratory manager or not
 * @param int $maxusetime maximum time for using the remote lab
 * @return stdClass $remlabinfo
 *
 */
function define_remlab($instance, $collab, $practice, $labmanager, $maxusetime) {
    $remlabinfo = new stdClass();
    $remlabinfo->instance = $instance;
    $remlabinfo->collab = $collab;
    $remlabinfo->practice = $practice;
    $remlabinfo->labmanager = $labmanager;
    $remlabinfo->max_use_time = $maxusetime;

    return $remlabinfo;
}