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
                'accepted_types' => 'application/xml'));
    }

    // Creating the controller file in dataroot and updating the files table in the database.
    if ($ejsapp->controllerfile) {
        file_save_draft_area_files($ejsapp->controllerfile, $context->id, 'mod_ejsapp', 'cntfiles',
            $ejsapp->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));
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
    // I think we should use $file->sync_external_file(), but it doesn't do what I'd expect for non-image files...
    // We need a workaround.
    if (class_exists('repository_filesystem')) {
        if (!is_null($filerecord->referencefileid)) {
            $repositoryid = $DB->get_field('files_reference', 'repositoryid',
                array('id' => $filerecord->referencefileid));
            $repositorytypeid = $DB->get_field('repository_instances', 'typeid',
                array('id' => $repositoryid));
            if ($DB->get_field('repository', 'type', array('id' => $repositorytypeid)) == 'filesystem') {
                $repository = repository_filesystem::get_instance($repositoryid);
                $filepath = $repository->get_rootpath() . ltrim($file->get_reference(), '/');
                $contenthash = sha1_file($filepath);
                if ($file->get_contenthash() == $contenthash) {
                    // File did not change since the last synchronisation.
                    $filesize = filesize($filepath);
                } else {
                    // Copy file into moodle filepool (used to generate an image thumbnail).
                    list($contenthash, $filesize, $newfile) = $fs->add_file_to_pool($filepath);
                }
                $file->set_synchronized($contenthash, $filesize);
            }
        }
    }

    // Create folders to store the .jar or .zip file.
    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/';
    if (!file_exists($path)) {
        mkdir($path, 0755);
    }
    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course;
    if (!file_exists($path)) {
        mkdir($path, 0755);
    }
    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
    if (file_exists($path)) { // Updating, not creating, the ejsapp activity.
        delete_recursively($path);
    }
    mkdir($path, 0755);

    // Copy the jar/zip file to its destination folder in jarfiles.
    $filepath = $path . $filerecord->filename;
    $file->copy_content_to($filepath);

    // Codebase.
    $codebase = '';
    preg_match('/http:\/\/.+?\/(.+)/', $CFG->wwwroot, $matchresult);
    if (!empty($matchresult) and $matchresult[1]) {
        $codebase .= '/' . $matchresult[1];
    }
    $codebase .= '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';

    // Initialize the mod_form elements.
    $ejsapp->class_file = '';
    $ejsapp->codebase = $codebase;
    $ejsapp->mainframe = '';
    $ejsapp->is_collaborative = 0;
    $ejsapp->manifest = 'EJsS';

    $ext = pathinfo($filepath, PATHINFO_EXTENSION);
    // Get params and set their corresponding values in the mod_form elements and update the ejsapp table.
    if ($ext == 'jar') { // Java.
        $ejsok = modifications_for_java($filepath, $ejsapp, $file, $filerecord, false);
    } else { // Javascript.
        $codebase = '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
        $ejsok = modifications_for_javascript($filepath, $ejsapp, $path, $codebase);
    }

    // Configuration of blockly.
    $blocklyconf = array();
    array_push($blocklyconf, $ejsapp->use_blockly);
    array_push($blocklyconf, $ejsapp->display_logic);
    array_push($blocklyconf, $ejsapp->display_loops);
    array_push($blocklyconf, $ejsapp->display_math);
    array_push($blocklyconf, $ejsapp->display_text);
    array_push($blocklyconf, $ejsapp->display_lists);
    array_push($blocklyconf, $ejsapp->display_variables);
    array_push($blocklyconf, $ejsapp->display_functions);
    array_push($blocklyconf, $ejsapp->display_lab);
    array_push($blocklyconf, $ejsapp->display_lab_variables);
    array_push($blocklyconf, $ejsapp->display_lab_functions);
    array_push($blocklyconf, $ejsapp->display_lab_control);
    $ejsapp->blockly_conf = json_encode($blocklyconf);

    $DB->update_record('ejsapp', $ejsapp);

    // We add an entry in Moodle's file table for the .zip or .jar file in the jarfiles directory.
    $fileinfo = array(                          // Prepare file record object.
        'contextid' => $context->id,            // ID of context.
        'component' => 'mod_ejsapp',            // usually = table name.
        'filearea' => 'tmp_jarfiles',           // usually = table name.
        'itemid' => $ejsapp->id,                // usually = ID of row in table.
        'filepath' => '/',                      // any path beginning and ending in /.
        'filename' => $filerecord->filename);  // any filename.
    $fs->create_file_from_pathname($fileinfo, $filepath);

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
 * Deletes a directory from the server
 *
 * @param string $dir directory to delete
 * @return bool TRUE on success or FALSE on failure
 *
 */
function delete_recursively($dir) {
    if (file_exists($dir)) {
        $it = new RecursiveDirectoryIterator($dir);
        $files = new RecursiveIteratorIterator($it,
            RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        return @rmdir($dir);
    }
    return false;
}


/**
 * Creates the list of all Sarlab experiences accessible by a particular user.
 *
 * @param string $username
 * @param array $sarlabips
 * @return string $listexperiences
 *
 */
function get_experiences_sarlab($username, $sarlabips) {
    $listexperiences = '';

    foreach ($sarlabips as $sarlabip) {
        $lastquotemark = strrpos($sarlabip, "'");
        if ($lastquotemark != 0) {
            $lastquotemark++;
        }
        $ip = substr($sarlabip, $lastquotemark);
        if ($ip != '127.0.0.1' && $ip != '') {
            if ($fp = fsockopen($ip, '80', $errorcode, $errorstring, 1)) { // IP is alive.
                fclose($fp);
                $uri = 'http://' . $ip . '/';
                $headers = get_headers($uri);
                if (substr($headers[0], 9, 3) == 200) { // Valid file.
                    if ($xml = simplexml_load_file($uri)) {
                        $listexperiences = $xml->Experience; // Get list of experiences.
                        foreach ($listexperiences as $experience) {
                            // Get list of users who can access the experience.
                            $ownerusers = $experience->owneUser;
                            foreach ($ownerusers as $owneruser) {
                                // Check whether the required user has access to the experience.
                                if ($username == $owneruser || is_siteadmin()) {
                                    $expideriences = $experience->idExperience;
                                    foreach ($expideriences as $expiderience) {
                                        // Add the experience to the user's list of accessible experiences.
                                        $listexperiences .= $expiderience . ';';
                                    }
                                    break;
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
 * Gets the experiences defined without sarlab and combines them with those in Sarlab in a unique, ordered list.
 *
 * @param array $sarlabexperiences
 * @return array $combinedexperiences
 *
 */
function combine_experiences($sarlabexperiences) {
    global $DB;
    $remlabmanager = $DB->get_records('block', array('name' => 'remlab_manager'));
    $remlabmanager = !empty($remlabmanager);

    if ($remlabmanager) {
        $listexperienceswithoutsarlab = $DB->get_records('block_remlab_manager_conf',
            array('usingsarlab' => '0'));
        $combinedexperiences = array();
        if ($sarlabexperiences[0] != '') {
            $combinedexperiences = $sarlabexperiences;
        }
        foreach ($listexperienceswithoutsarlab as $listexperienceswithoutsarlab) {
            if (!in_array($listexperienceswithoutsarlab->practiceintro, $sarlabexperiences)) {
                $combinedexperiences[] = $listexperienceswithoutsarlab->practiceintro;
            }
        }
    } else {
        $combinedexperiences = $sarlabexperiences;
    }

    // Order the list alphabetically.
    sort($combinedexperiences);

    return $combinedexperiences;
}


/**
 * Gets the experiences defined without sarlab and combines them with those in Sarlab in a unique, ordered list.
 *
 * @return array $showableexperiences
 *
 */
function get_showable_experiences() {
    global $USER;
    $sarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
    // Get experiences from Sarlab.
    $listexperiences = get_experiences_sarlab($USER->username, $sarlabips);
    $sarlabexperiences = explode(";", $listexperiences);
    // Also get experiences NOT in Sarlab and add them to the list.
    $showableexperiences = combine_experiences($sarlabexperiences);

    return $showableexperiences;
}


/**
 * Modifies links to libraries and images used by the EjsS javascript applications.
 *
 * @param string $codebase
 * @param stdClass $ejsapp
 * @param string $code
 * @param boolean $usecss
 * @return string $code
 *
 */
function update_links($codebase, $ejsapp, $code, $usecss) {
    global $CFG;

    $path = $CFG->wwwroot . $codebase;
    $explodedname = explode("_Simulation", $ejsapp->applet_name);

    // In case it exists, change the content of the separated .js file to make it work.
    // TODO: Do it for all languages .js files.
    $filename = substr($ejsapp->applet_name, 0, strpos($ejsapp->applet_name, '.'));
    $filepath = $CFG->dirroot . $codebase . $filename . '.js';
    if (file_exists($filepath)) { // Javascript code included in a separated .js file.
        // Replace links for images and stuff and insert a placeholder for future purposes.
        $jscode = file_get_contents($filepath);
        $search = '("_topFrame","_ejs_library/",null);';
        $replace = '("_topFrame","' . $path . '_ejs_library/","' . $path . '","webUserInput");';
        $jscode = str_replace($search, $replace, $jscode);
        file_put_contents($filepath, $jscode);
    } else { // If the .js file does not exists, then this part is inside the $code variable.
        // Replace links for images and stuff and insert a placeholder for future purposes.
        $search = '("_topFrame","_ejs_library/",null);';
        $replace = '("_topFrame","' . $path . '_ejs_library/","' . $path . '","webUserInput");';
        $code = str_replace($search, $replace, $code);
    }

    // Replace link for css.
    $ejsscss = '_ejs_library/css/ejss.css';
    if (!file_exists($CFG->dirroot . $codebase . $ejsscss)) {
        $ejsscss = '_ejs_library/css/ejsSimulation.css';
    }
    $search = '<link rel="stylesheet"  type="text/css" href="' . $ejsscss . '" />';
    if ($usecss) {
        $replace = '<link rel="stylesheet"  type="text/css" href="' . $path . $ejsscss . '" />';
    } else {
        $replace = '';
    }
    $code = str_replace($search, $replace, $code);

    $search = '<link rel="stylesheet"  type="text/css" href="css/style.css" />';
    $replace = '';
    $code = str_replace($search, $replace, $code);

    // Replace link for common_script.js and textsizedetector.js.
    $search = '<script src="_ejs_library/scripts/common_script.js"></script>';
    $replace = '<script src="' . $path .'_ejs_library/scripts/common_script.js"></script>';
    $code = str_replace($search, $replace, $code);
    $search = '<script src="_ejs_library/scripts/textresizedetector.js"></script>';
    $replace = '<script src="' . $path .'_ejs_library/scripts/textresizedetector.js"></script>';
    $code = str_replace($search, $replace, $code);
    $search = '<script src="_ejs_library/ejsS.v1.min.js"></script>';
    $replace = '<script src="' . $path .'_ejs_library/ejsS.v1.min.js"></script>';
    $code = str_replace($search, $replace, $code);
    $search = '<script src="_ejs_library/ejsS.v1.max.js"></script>';
    $replace = '<script src="' . $path .'_ejs_library/ejsS.v1.max.js"></script>';
    $code = str_replace($search, $replace, $code);

    // Replace call for main function so we can later pass parameters to it.
    // TODO: Not needed in newest versions of EjsS.
    $search = "window.addEventListener('load', function () {  new " . $explodedname[0];
    $replace = "window.addEventListener('load', function () {  var _model = new " . $explodedname[0];
    $code = str_replace($search, $replace, $code);

    return $code;
}

/**
 * Generates the values of the personalized variables in a particular EJS application for a given user.
 *
 * @param stdClass $ejsapp
 * @param stdClass $user
 * @param boolean $shuffle
 * @return stdClass $personalvarsinfo
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
 * @param string $filepath
 * @param stdClass $ejsapp
 * @param stored_file $file
 * @param stdClass $filerecord
 * @param boolean $alert
 * @return boolean $ejsok
 *
 */
function modifications_for_java($filepath, $ejsapp, $file, $filerecord, $alert) {
    global $CFG;

    $ejsok = false;

    $ejsapp->applet_name = $filerecord->filename;

    if (file_exists($filepath)) {
        // Extract the manifest.mf file from the .jar.
        $manifest = file_get_contents('zip://' . $filepath . '#' . 'META-INF/MANIFEST.MF');

        // Class_file.
        $ejsapp->class_file = get_class_for_java($manifest);

        // Mainframe.
        $pattern = '/Main-Frame\s*:\s*(.+)\s*/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) == 0) {
            $mainframe = '';
        } else {
            $mainframe = $matches[1][0];
            $mainframe = preg_replace('/\s+/', "", $mainframe); // Delete all white-spaces.
        }
        $ejsapp->mainframe = $mainframe;

        // Is_collaborative.
        $pattern = '/Is-Collaborative\s*:\s*(\w+)/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) == 0) {
            $iscollaborative = 0;
        } else {
            $iscollaborative = trim($matches[1][0]);
            if ($iscollaborative == 'true') {
                $iscollaborative = 1;
            } else {
                $iscollaborative = 0;
            }
        }
        $ejsapp->is_collaborative = $iscollaborative;

        // Check whether the EjsS version to build this applet is supported.
        $pattern = '/Applet-Height\s*:\s*(\w+)/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) == 0) {
            // If this field doesn't exist in the manifest, then the EjsS version used to compile the jar does not support Moodle.
            if ($alert) {
                $message = get_string('EJS_version', 'ejsapp');
                $alert = "<script type=\"text/javascript\">
                      window.alert(\"$message\")
                      </script>";
                echo $alert;
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
                        $filepath . " " . get_config('mod_ejsapp', 'certificate_alias'));
                    // We replace the file stored in Moodle's filesystem and its table with the signed version.
                    $file->delete();
                    $fs = get_file_storage();
                    $fs->create_file_from_pathname($filerecord, $filepath);
                }
            } else if ($alert) { // Files without the codebase parameter set to the Moodle server direction are not signed.
                $message = get_string('EJS_codebase', 'ejsapp');
                $alert = "<script type=\"text/javascript\">
                      window.alert(\"$message\")
                      </script>";
                echo $alert;
            }
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
 * For EjsS javascript applications.
 *
 * @param string $filepath
 * @param stdClass $ejsapp
 * @param string $folderpath
 * @param string $codebase
 * @return boolean $ejsok
 *
 */
function modifications_for_javascript($filepath, $ejsapp, $folderpath, $codebase) {
    global $CFG;

    $ejsapp->is_collaborative = 1;

    $zip = new ZipArchive;
    if ($zip->open($filepath)) {
        $zip->extractTo($folderpath);
        $zip->close();
        $metadata = file_get_contents($folderpath . '_metadata.txt');
        $ejsok = true;

        // Search in _metadata for the name of the main Javascript file.
        $pattern = '/main-simulation\s*:\s*(.+)\s*/';
        preg_match($pattern, $metadata, $matches, PREG_OFFSET_CAPTURE);
        $substr = $matches[1][0];
        if (strlen($matches[1][0]) == 59) {
            $pattern = '/^\s(.+)\s*/m';
            if ((preg_match($pattern, $metadata, $matches, PREG_OFFSET_CAPTURE) > 0)) {
                $substr = $substr . $matches[1][0];
            }
        }
        $ejsapp->applet_name = rtrim($substr);

        // Create/delete/modify the css file to change the visual aspect of the javascript application.
        // Custom css.
        $useoriginalcss = false;
        $cssfilepath = $folderpath . '_ejs_library/css/ejsapp.css';
        if ($ejsapp->css == '' && file_exists($cssfilepath)) {
            unlink($cssfilepath);
        }
        $cssfilecontent = "";
        if ($ejsapp->css != '') { // Custom css.
            $lines = explode(PHP_EOL, $ejsapp->css);
            foreach ($lines as $line) {
                if (strpos($line, '{')) {
                    $cssfilecontent .= 'div#EJsS ' . $line;
                } else {
                    $cssfilecontent .= $line;
                }
            }
            $file = fopen($cssfilepath, "w");
            fwrite($file, $cssfilecontent);
            fclose($file);
        } else { // Original css.
            $useoriginalcss = true;
            $ejsscss = '_ejs_library/css/ejss.css';
            if (!file_exists($CFG->dirroot . $codebase . $ejsscss)) {
                $ejsscss = '_ejs_library/css/ejsSimulation.css';
            }
            $cssfilepath = $folderpath . $ejsscss;
            $handle = fopen($cssfilepath, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, '{')) {
                        $cssfilecontent .= 'div#EJsS ' . $line;
                    } else {
                        $cssfilecontent .= $line;
                    }
                }
                fclose($handle);
                file_put_contents($cssfilepath, $cssfilecontent);
            }
        }

        // Languages.
        $languages = array('default');
        $pattern = '/available-languages\s*:\s*(.+)\s*/';
        if (preg_match($pattern, $metadata, $matches, PREG_OFFSET_CAPTURE)) {
            $substr = $matches[1][0];
            if (strpos($substr, ',')) {
                $languages = explode(',', $substr);
                array_push($languages, 'default');
            } else if ($substr !== '') {
                array_push($languages, $substr);
            }
        }

        // Change content of the html file to make it work.
        foreach ($languages as $language) {
            if ($language == 'default') {
                $filepath = $folderpath . $ejsapp->applet_name;
            } else {
                $filename = substr($ejsapp->applet_name, 0, strpos($ejsapp->applet_name, '.'));
                $extension = substr($ejsapp->applet_name, strpos($ejsapp->applet_name, ".") + 1);
                $filepath = $folderpath . $filename . '_' . $language . '.' . $extension;
            }
            if (file_exists($filepath)) {
                $code = file_get_contents($filepath);
                // Get the whole code from </title> (not included) onwards.
                $code = explode('</title>', $code);
                $code = '<div id="EJsS">' . $code[1];

                // Variable $code1 is $code till </head> (not included) and with the missing standard part.
                $code1 = substr($code, 0, -strlen($code) + strpos($code, '</head>')) .
                    '<div id="_topFrame" style="text-align:center"></div>';

                if (strpos($code, '<script type')) { // EjsS with Javascript embedded in the html page.
                    // Variable $code2 is $code from </head> to </body> tags, none of them included.
                    $code2 = substr($code, strpos($code, '</head>'));
                    $code2 = explode('</body>', $code2);
                    $code2 = $code2[0] . '</div>';
                    $code2 = substr($code2, strpos($code2, '<script type'));
                } else { // EjsS with an external .js file for the Javascript.
                    $explodedfilename = explode(".", $ejsapp->applet_name);
                    $code2 = '<script src="' . $CFG->wwwroot . $codebase . $explodedfilename[0] . '.js"></script></div>';
                }

                $code = $code1 . $code2;
                $code = update_links($codebase, $ejsapp, $code, $useoriginalcss);
                file_put_contents($filepath, $code);
            }
        }
    } else {
        $ejsok = false;
    }

    chmod_r($folderpath);

    return $ejsok;
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
 * @param int $usingsarlab
 * @param string $expid
 * @param int $timeout
 * @return int 0, not alive; 1, alive; 2, not checkable
 *
 */
function ping($host, $port=80, $usingsarlab, $expid=null, $timeout=3) {
    global $infodevices;

    $alive = fsockopen($host, $port, $errno, $errstr, $timeout);
    $notcheckable = false;
    if ($alive && $usingsarlab) {
        // Obtain the xml filename from idExp.
        $uri = 'http://' . $host . '/';
        $headers = @get_headers($uri);
        if (substr($headers[0], 9, 3) == 200) { // Valid file.
            $dom = new DOMDocument;
            $dom->validateOnParse = true;
            if ($dom->load($uri)) {
                $listexperiences = $dom->getElementsByTagName('Experience'); // Get list of experiences.
                $xmlfilename = 'null';
                foreach ($listexperiences as $experience) {
                    $expideriences = $experience->getElementsByTagName('idExperience'); // Get the name of the experience.
                    foreach ($expideriences as $expiderience) {
                        if ($expiderience->nodeValue == $expid) {
                            $fileexperiences = $experience->getElementsByTagName('fileName'); // Get the name of the xml file.
                            foreach ($fileexperiences as $fileexperience) {
                                $xmlfilename = $fileexperience->nodeValue;
                            }
                            break 2;
                        }
                    }
                }
                $url = $uri . 'isAliveExp?' . $xmlfilename;
                if ($info = file_get_contents($url)) {
                    $info = explode("=", $info);
                    $alive = (mb_strtoupper(trim($info[1])) === mb_strtoupper ("true")) ? true : false;
                    if (!$alive) {
                        // Get list of devices in the experience that are not alive and see which ones are down.
                        $url = $uri . 'pingExp' . $xmlfilename;
                        if ($info = file_get_contents($url)) {
                            $devices = explode("Ping to ", $info);

                            /**
                             * Gets a string between an initial and a final string.
                             *
                             * @param string $string
                             * @param string $start
                             * @param string $end
                             * @return string
                             *
                             */
                            function get_string_between($string, $start, $end) {
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
                                $infodevices[]->name = get_string_between($device, ": ", "ping ");
                                $ip = get_string_between($device, "ping ", "Reply from ");
                                $infodevices[]->ip = $ip;
                                $url = $uri . 'isAlive?' . $ip;
                                if ($info = file_get_contents($url)) {
                                    $infodevices[]->alive = (mb_strtoupper(trim($info[1])) === mb_strtoupper("true")) ? true : false;
                                }
                            }
                        }
                    }
                } else {
                    $notcheckable = true;
                }
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
        return 0;
    }
}

/**
 * Creates a default record for the block_remlab_manager_conf table
 *
 * @param stdClass $ejsapp
 * @return stdClass $defaulconf
 *
 */
function default_rem_lab_conf($ejsapp) {
    global $USER;

    $defaulconf = new stdClass();
    // Get experiences from Sarlab and check whether this practice is in a Sarlab server or not.
    $sarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
    $listexperiences = get_experiences_sarlab($USER->username, $sarlabips);
    $sarlabexperiences = explode(";", $listexperiences);
    $completelistpract = explode(';', $ejsapp->list_practices);
    $defaulconf->practiceintro = $completelistpract[$ejsapp->practiceintro];
    $defaulconf->usingsarlab = 0;
    if (in_array($ejsapp->practiceintro, $sarlabexperiences)) {
        $defaulconf->usingsarlab = 1;
    }
    if ($defaulconf->usingsarlab == 1) {
        $sarlabinstance = 0;
        $defaulconf->sarlabinstance = $sarlabinstance;
        $defaulconf->sarlabcollab = 0;
        $sarlabips = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
        $sarlabports = explode(";", get_config('block_remlab_manager', 'sarlab_port'));
        $initchar = strrpos($sarlabips[intval($sarlabinstance)], "'");
        if ($initchar != 0) {
            $initchar++;
        }
        $ip = substr($sarlabips[intval($sarlabinstance)], $initchar);
        $defaulconf->ip = $ip;
        $defaulconf->port = $sarlabports[intval($sarlabinstance)];
    } else {
        $defaulconf->sarlabinstance = 0;
        $defaulconf->sarlabcollab = 0;
        $defaulconf->ip = '127.0.0.1';
        $defaulconf->port = 443;
    }
    $defaulconf->slotsduration = 1;
    $defaulconf->totalslots = 18;
    $defaulconf->weeklyslots = 9;
    $defaulconf->dailyslots = 3;
    $defaulconf->reboottime = 2;
    $defaulconf->active = 1;
    $defaulconf->free_access = 0;

    return $defaulconf;
}

/**
 * Creates the record for the block_remlab_manager_exp2prc table
 *
 * @param stdClass $ejsapp
 * @return void
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
 * Updates the ejsappbooking_usersaccess table
 *
 * @param stdClass $ejsapp
 * @return void
 *
 */
function update_booking_table($ejsapp) {
    global $DB;

    /**
     * Updates a record in the the ejsappbooking_usersaccess table.
     *
     * @param int $ejsappbookingid
     * @param object $usersaccess
     * @param int $ejsappid
     *
     */
    function update_or_insert_record($ejsappbookingid, $usersaccess, $ejsappid) {
        global $DB;
        if (!$DB->record_exists('ejsappbooking_usersaccess', array('bookingid' => $ejsappbookingid,
            'userid' => $usersaccess->userid, 'ejsappid' => $ejsappid))) {
            $DB->insert_record('ejsappbooking_usersaccess', $usersaccess);
        } else {
            $record = $DB->get_record('ejsappbooking_usersaccess', array('bookingid' => $ejsappbookingid,
                'userid' => $usersaccess->userid, 'ejsappid' => $ejsappid));
            $usersaccess->id = $record->id;
            $DB->update_record('ejsappbooking_usersaccess', $usersaccess);
        }
    }

    if ($DB->record_exists('ejsappbooking', array('course' => $ejsapp->course))) {
        $coursecontext = context_course::instance($ejsapp->course);
        $users = get_enrolled_users($coursecontext);
        $ejsappbooking = $DB->get_record('ejsappbooking', array('course' => $ejsapp->course));
        // For ejsappbooking_usersaccess table.
        $usersaccess = new stdClass();
        $usersaccess->bookingid = $ejsappbooking->id;
        $usersaccess->ejsappid = $ejsapp->id;
        // Grant remote access to admin user.
        $usersaccess->userid = 2;
        $usersaccess->allowremaccess = 1;
        update_or_insert_record($ejsappbooking->id, $usersaccess, $ejsapp->id);
        // Consider other enrolled users.
        foreach ($users as $user) {
            $usersaccess->userid = $user->id;
            if (!has_capability('mod/ejsapp:addinstance', $coursecontext, $user->id, true)) {
                $usersaccess->allowremaccess = 0;
            } else {
                $usersaccess->allowremaccess = 1;
            }
            update_or_insert_record($ejsappbooking->id, $usersaccess, $ejsapp->id);
        }
    }
}

/**
 * Checks whether a the booking system is being used in the course of a particular ejsapp activity or not.
 *
 * @param stdClass $ejsapp
 * @return bool $usingbookings
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
 * Checks if there is an active booking made by the current user and gets the information needed by sarlab
 *
 * @param object $DB
 * @param object $USER
 * @param stdClass $ejsapp
 * @param string $currenttime
 * @param int $sarlabinstance
 * @param int $labmanager
 * @param int $maxusetime
 * param int $maxusetime
 * @return stdClass $sarlabinfo
 *
 */
function check_users_booking($DB, $USER, $ejsapp, $currenttime, $sarlabinstance, $labmanager, $maxusetime) {
    $sarlabinfo = null;

    if ($DB->record_exists('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id,
        'valid' => 1))) {
        $bookings = $DB->get_records('ejsappbooking_remlab_access', array('username' => $USER->username,
            'ejsappid' => $ejsapp->id, 'valid' => 1));
        foreach ($bookings as $booking) { // If the user has an active booking, use that info.
            if ($currenttime >= $booking->starttime && $currenttime < $booking->endtime) {
                $expsyst2pract = $DB->get_record('block_remlab_manager_exp2prc', array('ejsappid' => $ejsapp->id,
                    'practiceid' => $booking->practiceid));
                $practice = $expsyst2pract->practiceintro;
                $sarlabinfo = define_sarlab($sarlabinstance, 0, $practice, $labmanager, $maxusetime);
                break;
            }
        }
    }

    return $sarlabinfo;
}

/**
 * Checks if there is an active booking made by the current user and if there is, it gets the ending time of the farest
 * consecutive booking.
 *
 * @param object $DB
 * @param string $username
 * @param int $ejsappid
 * @return boolean $active_booking
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
        function cmp($a, $b) {
            return strtotime($a->starttime) - strtotime($b->starttime);
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
 * @param string $currenttime
 * @return string $username
 *
 */
function check_anyones_booking($DB, $ejsapp, $currenttime) {
    $username = '';

    if ($DB->record_exists('ejsappbooking_remlab_access', array('ejsappid' => $ejsapp->id, 'valid' => 1))) {
        $bookings = $DB->get_records('ejsappbooking_remlab_access', array('ejsappid' => $ejsapp->id, 'valid' => 1));
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
 * @param array $slotsduration
 * @param int $currenttime
 * @return array $timeinfo
 *
 */
function get_occupied_ejsapp_time_information($repeatedlabs, $slotsduration, $currenttime) {
    global $DB, $USER;

    $timefirstaccess = 0;
    // TODO: Make $timefirstaccess INF when we stop resetting time when a user in a remote lab refreshes the page.
    $timelastaccess = 0;
    $maxusetime = 3600;
    foreach ($repeatedlabs as $repeatedlab) {
        if (isset($repeatedlab->name)) {
            // Retrieve information from ejsapp's logging table.
            $workinglogs = $DB->get_records('ejsapp_log', array('info' => $repeatedlab->name, 'action' => 'working'));
            $viewedlogs = $DB->get_records('ejsapp_log', array('info' => $repeatedlab->name, 'action' => 'viewed'));
            $userid = $USER->id;
            foreach ($workinglogs as $workinglog) {
                if ($workinglog->userid != $USER->id) {
                    if ($workinglog->time > $timelastaccess) {
                        $timelastaccess = $workinglog->time;
                        $userid = $workinglog->userid;
                    }
                }
            }
            $practiceintro = $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro',
                array('ejsappid' => $repeatedlab->id));
            foreach ($viewedlogs as $viewedlog) {
                if ($viewedlog->userid != $USER->id) {
                    if ($viewedlog->userid == $userid) { // Accesses of the user that is currently working with the rem lab.
                        $slotsdurationconf = $DB->get_field('block_remlab_manager_conf',
                            'slotsduration', array('practiceintro' => $practiceintro));
                        if ($slotsdurationconf > 4) {
                            $slotsdurationconf = 0;
                        }
                        $maxslots = $DB->get_field('block_remlab_manager_conf',
                            'dailyslots', array('practiceintro' => $practiceintro));
                        $maxusetime = $maxslots * 60 * $slotsduration[$slotsdurationconf];
                        if ($viewedlog->time > $currenttime - $maxusetime) {
                            $timefirstaccess = max($timefirstaccess, $viewedlog->time);
                            // TODO: Change to min when we stop resetting time when a user in a remote lab refreshes the page.
                        }
                    }
                }
            }
        }
    }
    if ($timefirstaccess == 0) {
        $timefirstaccess = INF;
    }
    $timeinfo['time_first_access'] = $timefirstaccess;
    $timeinfo['time_last_access'] = $timelastaccess;
    $timeinfo['occupied_ejsapp_max_use_time'] = $maxusetime;

    return $timeinfo;
}

/**
 * Checks whether a remote lab is available, in use or rebooting.
 *
 * @param array $timeinfo
 * @param int $idletime
 * @param int $checkactivity
 * @return string $status
 *
 */
function get_lab_status($timeinfo, $idletime, $checkactivity) {
    $status = 'in_use';
    $currenttime = time();
    if ($currenttime - $timeinfo['time_last_access'] - 60 * $idletime - $checkactivity > 0) {
        // We need -$checkactivity because the last 'working' log doesn't get recorded.
        $status = 'available';
    } else if ($currenttime - $timeinfo['time_last_access'] - $checkactivity > 0) {
        // We need -$checkactivity because the last 'working' log doesn't get recorded.
        $status = 'rebooting';
    }
    return $status;
}

/**
 * Gets the remaining time till the lab is available again.
 *
 * @param array $bookinginfo
 * @param array $status
 * @param array $timeinfo
 * @param int $idletime
 * @param int $checkactivity
 * @return string $remainingtime
 *
 */
function get_remaining_time($bookinginfo, $status, $timeinfo, $idletime, $checkactivity) {
    global $DB;

    $currenttime = time();
    if ($bookinginfo["active_booking"]) {
        if (array_key_exists('username_with_booking', $bookinginfo) && array_key_exists('ejsappid', $bookinginfo)) {
            $endingtime = check_last_valid_booking($DB, $bookinginfo['username_with_booking'], $bookinginfo['ejsappid']);
            $endingtime = strtotime($endingtime);
            $remainingtime = 60 * $idletime + $endingtime - $currenttime;
        } else { // In use.
            if ($timeinfo['time_first_access'] == INF) {
                $timeinfo['time_first_access'] = time();
            }
            $remainingtime = 60 * $idletime + $timeinfo['occupied_ejsapp_max_use_time']
                - ($currenttime - $timeinfo['time_first_access']);
        }
    } else {
        if ($status == 'available') {
            $remainingtime = 0;
        } else if ($status == 'rebooting') {
            $remainingtime = 60 * $idletime + $checkactivity - ($currenttime - $timeinfo['time_last_access']);
        } else { // In use.
            if ($timeinfo['time_first_access'] == INF) {
                $timeinfo['time_first_access'] = time();
            }
            $remainingtime = 60 * $idletime + $timeinfo['occupied_ejsapp_max_use_time']
                - ($currenttime - $timeinfo['time_first_access']);
        }
    }

    return $remainingtime;
}

/**
 * Checks whether a particular remote lab is also present in other courses or not and gives the list of repeated labs.
 *
 * @param stdClass $remlabconf
 * @return array $repeatedlabs
 *
 */
function get_repeated_remlabs($remlabconf) {
    global $DB;

    $repeatedpractices = $DB->get_records('block_remlab_manager_exp2prc',
        array('practiceintro' => $remlabconf->practiceintro));
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
function check_active_booking($repeatedlabs, $courseid) {
    global $DB;

    $bookinfo = array();
    $bookinfo['active_booking'] = false;
    if (count($repeatedlabs) > 1) {
        $repeatedlabswithbs = get_repeated_remlabs_with_bs($repeatedlabs);
        if (count($repeatedlabswithbs) > 0) {
            foreach ($repeatedlabswithbs as $repeatedlabwithbs) {
                if ($repeatedlabwithbs->course != $courseid) {
                    $bookinfo['username_with_booking'] = check_anyones_booking($DB, $repeatedlabwithbs, date('Y-m-d H:i:s'));
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
 *
 */
function remote_lab_access_info($ejsapp, $course) {
    global $DB, $USER;

    $coursecontext = context_course::instance($course->id);
    $remlabaccess = new stdClass;

    $practice = $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro', array('ejsappid' => $ejsapp->id));
    $remlabaccess->remlab_conf = $DB->get_record('block_remlab_manager_conf', array('practiceintro' => $practice));

    // Check if the remote lab is operative.
    $remlabaccess->operative = true;
    $labactive = $DB->get_field('block_remlab_manager_conf', 'active', array('practiceintro' => $practice));
    if ($labactive == 0) {
        $remlabaccess->operative = false;
    }

    // Check if we should grant free access to the user for this remote lab.
    $remlabaccess->allow_free_access = true;
    $remlabaccess->labmanager = has_capability('mod/ejsapp:accessremotelabs', $coursecontext, $USER->id, true);
    $remlabaccess->repeated_ejsapp_labs = get_repeated_remlabs($remlabaccess->remlab_conf);
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
 * Returns some the time use information regarding a particular remote lab.
 *
 * @param stdClass $remlabconf
 * @param stdClass $repeatedlabs
 * @return stdClass $remlabtimeinfo
 *
 */
function remote_lab_use_time_info($remlabconf, $repeatedlabs) {
    $remlabtimeinfo = new stdClass;

    // Getting the maximum time the user is allowed to use the remote lab.
    $maxslots = $remlabconf->dailyslots;
    $slotsdurationconf = $remlabconf->slotsduration;
    if ($slotsdurationconf > 4) {
        $slotsdurationconf = 4;
    }
    $slotsduration = array(60, 30, 15, 5, 2);
    $remlabtimeinfo->max_use_time = $maxslots * 60 * $slotsduration[$slotsdurationconf]; // In seconds.

    // Search past accesses to this ejsapp lab or to the same remote lab added as a different ejsapp activity in this or
    // any other course.
    $remlabtimeinfo->time_information = get_occupied_ejsapp_time_information($repeatedlabs, $slotsduration, time());

    return $remlabtimeinfo;
}

/**
 * Defines a new sarlab object with all the needed information
 *
 * @param int $instance sarlab instance
 * @param int $collab 0 if not a collab session, 1 if collaborative
 * @param string $practice the practice identifier in sarlab
 * @param int $labmanager whether the user is a laboratory manager or not
 * @param int $maxusetime maximum time for using the remote lab
 * @return stdClass $sarlabinfo
 *
 */
function define_sarlab($instance, $collab, $practice, $labmanager, $maxusetime) {
    $sarlabinfo = new stdClass();
    $sarlabinfo->instance = $instance;
    $sarlabinfo->collab = $collab;
    $sarlabinfo->practice = $practice;
    $sarlabinfo->labmanager = $labmanager;
    $sarlabinfo->max_use_time = $maxusetime;

    return $sarlabinfo;
}

/**
 * Gets the required EJS .jar or .zip file for this activity from Moodle's File System and places it in the required
 * directory (inside jarfiles) when the file there doesn't exist or it is not synchronized with the file in Moodle's
 * File System (whether because its an alias to a file that has been modified or because the activity has been edited
 * and the original .jar or .zip file has been replaced by a new one).
 *
 * @param stdClass $ejsapp
 * @return void
 *
 */
function prepare_ejs_file($ejsapp) {
    global $DB, $CFG;

    /**
     * Deletes files inside a directory in jarfiles.
     *
     * @param stored_file $storedfile
     * @param object $tempfile
     * @param string $folderpath
     * @return int|false
     *
     */
    function delete_outdated_file($storedfile, $tempfile, $folderpath) {
        // We compare the content of the linked file with the content of the file in the jarfiles folder.
        if ($storedfile->get_contenthash() != $tempfile->get_contenthash()) { // If they are not the same...
            // Delete the files in jarfiles directory in order to replace them with the content of $storedfile.
            delete_recursively($folderpath);
            if (!file_exists($folderpath)) {
                mkdir($folderpath, 0700);
            }
            // Delete $tempfile from Moodle filesystem.
            $tempfile->delete();
            return true;
        } else { // If the file exists and matches the one configured in the ejsapp activity, do nothing.
            return false;
        }
    }

    /**
     * Creates a temp file in Moodle files system.
     *
     * @param int $contextid
     * @param int $ejsappid
     * @param string $filename
     * @param file_storage $fs
     * @param string $tempfilepath
     * @return stored_file $tempfile
     *
     */
    function create_temp_file($contextid, $ejsappid, $filename, $fs, $tempfilepath) {
        $fileinfo = array(
            'contextid' => $contextid,
            'component' => 'mod_ejsapp',
            'filearea' => 'tmp_jarfiles',
            'itemid' => $ejsappid,
            'filepath' => '/',
            'filename' => $filename);
        return $tempfile = $fs->create_file_from_pathname($fileinfo, $tempfilepath);
    }

    // We first get the jar/zip file configured in the ejsapp activity and stored in the filesystem.
    $filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'jarfiles',
        'itemid' => $ejsapp->id), 'filesize DESC');
    $filerecord = reset($filerecords);
    if ($filerecord) {
        $fs = get_file_storage();
        $storedfile = $fs->get_file_by_id($filerecord->id);

        // In case it is an alias to an external repository.
        // We should use $storedfile->sync_external_file(), but it doesn't do what I'd expect for non-image files...
        // We need a workaround.
        if (class_exists('repository_filesystem')) {
            if (!is_null($filerecord->referencefileid)) {
                $repositoryid = $DB->get_field('files_reference', 'repositoryid',
                    array('id' => $filerecord->referencefileid));
                $repositorytypeid = $DB->get_field('repository_instances', 'typeid',
                    array('id' => $repositoryid));
                if ($DB->get_field('repository', 'type', array('id' => $repositorytypeid)) == 'filesystem') {
                    $repository = repository_filesystem::get_instance($repositoryid);
                    $filepath = $repository->get_rootpath() . ltrim($storedfile->get_reference(), '/');
                    $contenthash = sha1_file($filepath);
                    if ($storedfile->get_contenthash() == $contenthash) {
                        // File did not change since the last synchronisation.
                        $filesize = filesize($filepath);
                    } else {
                        // Copy file into moodle filepool (used to generate an image thumbnail).
                        list($contenthash, $filesize, $newfile) = $fs->add_file_to_pool($filepath);
                    }
                    $storedfile->set_synchronized($contenthash, $filesize);
                }
            }
        }

        $codebase = '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
        $folderpath = $CFG->dirroot . $codebase;
        $ext = pathinfo($filerecord->filename, PATHINFO_EXTENSION);
        $filepath = $folderpath . $filerecord->filename;
        // Get the file stored in Moodle filesystem for the file in jarfiles, compare it and delete it if it is outdated.
        $tmpfilerecords = $DB->get_records('files', array('component' => 'mod_ejsapp',
            'filearea' => 'tmp_jarfiles', 'itemid' => $ejsapp->id), 'filesize DESC');
        $tmpfilerecord = reset($tmpfilerecords);
        if (file_exists($filepath)) { // If file in jarfiles exists...
            if ($tmpfilerecord) { // The file exists in jarfiles and in Moodle filesystem.
                $tempfile = $fs->get_file_by_id($tmpfilerecord->id);
            } else {
                // The file exists in jarfiles but not in Moodle filesystem (can happen with older versions of ejsapp
                // plugins that have been updated recently or after duplicating or restoring an ejsapp activity).
                $tempfile = create_temp_file($filerecord->contextid, $ejsapp->id, $filerecord->filename, $fs, $filepath);
            }
            $delete = delete_outdated_file($storedfile, $tempfile, $folderpath);
            if (!$delete) { // If files are the same, we have finished.
                return;
            }
        } else { // If file in jarfiles doesn't exists... (this should never happen actually, but just in case...).
            // We create the directories in jarfiles to put inside $storedfile.
            $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/';
            if (!file_exists($path)) {
                mkdir($path, 0700);
            }
            $path .= $ejsapp->course . '/';
            if (!file_exists($path)) {
                mkdir($path, 0700);
            }
            if (!file_exists($folderpath)) {
                mkdir($folderpath, 0700);
            }
            if ($tmpfilerecord) { // The file does not exist in jarfiles but it does in Moodle filesystem.
                $tempfile = $fs->get_file_by_id($tmpfilerecord->id);
                $tempfile->delete();
            }
        }

        // We copy the content of storedfile to jarfiles and add it to the file storage.
        $storedfile->copy_content_to($filepath);
        create_temp_file($filerecord->contextid, $ejsapp->id, $ejsapp->applet_name, $fs, $filepath);

        // We need to do a few more things depending on whether it is a Java or a Javascript application.
        if ($ext == 'jar') {
            modifications_for_java($filepath, $ejsapp, $storedfile, $filerecord, false);
        } else {
            modifications_for_javascript($filepath, $ejsapp, $folderpath, $codebase);
        }
        $DB->update_record('ejsapp', $ejsapp);
    }
}

/**
 * Creates a javascript file with all the required configuration to start using Blockly when an ejsapp activity is
 * configure to use it.
 *
 * @param stdClass $ejsapp
 * @return void
 *
 */
function create_blockly_configuration($ejsapp) {
    global $CFG;

    $blocklyconf = json_decode($ejsapp->blockly_conf);
    if ($blocklyconf[0] == 1) {
        $filepath = $CFG->dirroot . $ejsapp->codebase . 'configuration.js';
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // First, define all categories.
        $logic = "'<category name=\"" . get_string('xml_logic', 'ejsapp') .
            "\" colour=\"210\"><block type=\"controls_if\"></block><block type=\"logic_compare\"></block>" .
            "<block type=\"logic_operation\"></block><block type=\"logic_negate\"></block>" .
            "<block type=\"logic_boolean\"></block><block type=\"logic_null\"></block>" .
            "<block type=\"logic_ternary\"></block></category>'";
        $loops = "'<category name=\"" . get_string('xml_loops', 'ejsapp') .
            "\" colour=\"120\"><block type=\"controls_repeat_ext\"><value name=\"TIMES\">" .
            "<shadow type=\"math_number\"><field name=\"NUM\">10</field></shadow></value></block>" .
            "<block type=\"controls_whileUntil\"></block> <block type=\"controls_for\">" .
            "<value name=\"FROM\"><shadow type=\"math_number\"><field name=\"NUM\">1</field></shadow></value>" .
            "<value name=\"TO\"><shadow type=\"math_number\"><field name=\"NUM\">10</field></shadow></value>" .
            "<value name=\"BY\"><shadow type=\"math_number\"><field name=\"NUM\">1</field></shadow></value></block>" .
            "<block type=\"controls_forEach\"></block><block type=\"controls_flow_statements\"></block></category>'";
        $math = "'<category name=\"" . get_string('xml_maths', 'ejsapp') .
            "\" colour=\"230\"><block type=\"math_number\"></block><block type=\"math_arithmetic\">" .
            "<value name=\"A\"><shadow type=\"math_number\"><field name=\"NUM\">1</field></shadow></value>" .
            "<value name=\"B\"><shadow type=\"math_number\"><field name=\"NUM\">1</field></shadow></value></block>" .
            "<block type=\"math_single\"><value name=\"NUM\"><shadow type=\"math_number\">" .
            "<field name=\"NUM\">9</field></shadow></value></block>" .
            "<block type=\"math_trig\"><value name=\"NUM\"><shadow type=\"math_number\"><field name=\"NUM\">45</field>" .
            "</shadow></value></block><block type=\"math_constant\"></block>" .
            "<block type=\"math_number_property\"><value name=\"NUMBER_TO_CHECK\"><shadow type=\"math_number\">" .
            "<field name=\"NUM\">0</field></shadow></value></block><block type=\"math_round\">" .
            "<value name=\"NUM\"><shadow type=\"math_number\"><field name=\"NUM\">3.1</field></shadow></value></block>" .
            "<block type=\"math_on_list\"></block><block type=\"math_modulo\">" .
            "<value name=\"DIVIDEND\"><shadow type=\"math_number\"><field name=\"NUM\">64</field></shadow></value>" .
            "<value name=\"DIVISOR\"><shadow type=\"math_number\"><field name=\"NUM\">10</field></shadow></value></block>" .
            "<block type=\"math_constrain\"><value name=\"VALUE\"><shadow type=\"math_number\">" .
            "<field name=\"NUM\">50</field></shadow></value><value name=\"LOW\"><shadow type=\"math_number\">" .
            "<field name=\"NUM\">1</field></shadow></value><value name=\"HIGH\"><shadow type=\"math_number\">" .
            "<field name=\"NUM\">100</field></shadow></value></block><block type=\"math_random_int\">" .
            "<value name=\"FROM\"><shadow type=\"math_number\"><field name=\"NUM\">1</field></shadow></value>" .
            "<value name=\"TO\"><shadow type=\"math_number\"><field name=\"NUM\">100</field></shadow>" .
            "</value></block><block type=\"math_random_float\"></block></category>'";
        $text = "'<category name=\"" . get_string('xml_text', 'ejsapp') .
            "\" colour=\"160\"><block type=\"text\"></block><block type=\"text_join\"></block>" .
            "<block type=\"text_append\"><value name=\"TEXT\"><shadow type=\"text\"></shadow></value></block>" .
            "<block type=\"text_length\"><value name=\"VALUE\"><shadow type=\"text\">" .
            "<field name=\"TEXT\">abc</field></shadow></value></block>" .
            "<block type=\"text_isEmpty\"><value name=\"VALUE\"><shadow type=\"text\">" .
            "<field name=\"TEXT\"></field></shadow></value></block><block type=\"text_indexOf\">" .
            "<value name=\"VALUE\"><block type=\"variables_get\">" .
            "<field name=\"VAR\">{textVariable}</field></block></value><value name=\"FIND\">" .
            "<shadow type=\"text\"><field name=\"TEXT\">abc</field></shadow></value></block>" .
            "<block type=\"text_charAt\"><value name=\"VALUE\"><block type=\"variables_get\">" .
            "<field name=\"VAR\">{textVariable}</field></block></value></block><block type=\"text_getSubstring\">" .
            "<value name=\"STRING\"><block type=\"variables_get\">" .
            "<field name=\"VAR\">{textVariable}</field></block></value></block><block type=\"text_changeCase\">" .
            "<value name=\"TEXT\"><shadow type=\"text\"><field name=\"TEXT\">abc</field></shadow></value></block>" .
            "<block type=\"text_trim\"><value name=\"TEXT\"><shadow type=\"text\">" .
            "<field name=\"TEXT\">abc</field></shadow></value></block><block type=\"text_print\">" .
            "<value name=\"TEXT\"><shadow type=\"text\"><field name=\"TEXT\">abc</field></shadow></value></block>" .
            "<block type=\"text_prompt_ext\"><value name=\"TEXT\"><shadow type=\"text\">" .
            "<field name=\"TEXT\">abc</field></shadow></value></block></category>'";
        $lists = "'<category name=\"" . get_string('xml_lists', 'ejsapp') .
            "\" colour=\"260\"><block type=\"lists_create_with\"><mutation items=\"0\"></mutation></block>" .
            "<block type=\"lists_create_with\"></block><block type=\"lists_repeat\"><value name=\"NUM\">" .
            "<shadow type=\"math_number\"><field name=\"NUM\">5</field></shadow></value></block>" .
            "<block type=\"lists_length\"></block><block type=\"lists_isEmpty\"></block>" .
            "<block type=\"lists_indexOf\"><value name=\"VALUE\"><block type=\"variables_get\">" .
            "<field name=\"VAR\">{listVariable}</field></block></value></block>" .
            "<block type=\"lists_getIndex\"><value name=\"VALUE\"><block type=\"variables_get\">" .
            "<field name=\"VAR\">{listVariable}</field></block></value></block>" .
            "<block type=\"lists_setIndex\"><value name=\"LIST\"><block type=\"variables_get\">" .
            "<field name=\"VAR\">{listVariable}</field></block></value></block>" .
            "<block type=\"lists_getSublist\"><value name=\"LIST\"><block type=\"variables_get\">" .
            "<field name=\"VAR\">{listVariable}</field></block></value></block>" .
            "<block type=\"lists_split\"><value name=\"DELIM\"><shadow type=\"text\">" .
            "<field name=\"TEXT\">,</field></shadow></value></block><block type=\"lists_sort\"></block></category>'";
        $variables = "'<category name=\"" . get_string('xml_variables', 'ejsapp') .
            "\" colour=\"330\" custom=\"VARIABLE\"></category>'";
        $functions = "'<category name=\"" . get_string('xml_functions', 'ejsapp') .
            "\" colour=\"290\" custom=\"PROCEDURE\"></category>'";
        $lab = "'<category name=\"" . get_string('xml_lab', 'ejsapp') . "\" colour=\"44\">'";
        $labvariables = "'<category name=\"" . get_string('xml_lab_variables', 'ejsapp') .
            "\"><block type=\"get_model_variable\"></block><block type=\"set_model_variable\"></block>" .
            "<category name=\"" . get_string('xml_lab_var_boolean', 'ejsapp') . "\">" .
            "<block type=\"set_model_variable_boolean\"></block>" .
            "<block type=\"get_model_variable_boolean\"></block></category><category name=\"" .
            get_string('xml_lab_var_string', 'ejsapp') . "\">" .
            "<block type=\"set_model_variable_string\"></block>" .
            "<block type=\"get_model_variable_string\"></block></category><category name=\"" .
            get_string('xml_lab_var_number', 'ejsapp') . "\">" .
            "<block type=\"set_model_variable_number\"></block>" .
            "<block type=\"get_model_variable_number\"></block></category><category name=\"" .
            get_string('xml_functions', 'ejsapp') . "\">" .
            "<block type=\"set_model_variable_funs\"></block>" .
            "<block type=\"get_model_variable_funs\"></block></category><category name=\"" .
            get_string('xml_lab_var_others', 'ejsapp') . "\">" .
            "<block type=\"set_model_variable_others\"></block>" .
            "<block type=\"get_model_variable_others\"></block></category></category>'";
        $labfunctions = "'<category name=\"" . get_string('xml_lab_functions', 'ejsapp') .
            "\"><block type=\"play_lab\"></block><block type=\"pause_lab\"></block>" .
            "<block type=\"initialize_lab\"></block><block type=\"reset_lab\"></block></category>'";
        $labcontrol = "'<category name=\"" . get_string('xml_lab_control', 'ejsapp') .
            "\"><block type=\"event\"></block><block type=\"fixedRelation\"></block>" .
            "<block type=\"wait\"></block></category>'";

        // Now, create the configuration by adding the categories selected in the ejsapp activity configuration.
        // Categories.
        $jsconfcode = "
        var time_step = 10; // INTERVAL BETWEEN ACTIONS
        var buttonFunction = playCode;
        
        var playCode = function playCode() {
            parseCode();
            inter=setInterval(stepCode, time_step);
        };";

        $jsconfcode .= "\n" . "var toolbox = '<xml>';";
        if ($blocklyconf[1] == 1) {
            $jsconfcode .= "\n" . 'toolbox += ' . $logic . ';';
        }
        if ($blocklyconf[2] == 1) {
            $jsconfcode .= "\n" . 'toolbox += ' . $loops . ';';
        }
        if ($blocklyconf[3] == 1) {
            $jsconfcode .= "\n" . 'toolbox += ' . $math . ';';
        }
        if ($blocklyconf[4] == 1) {
            $jsconfcode .= "\n" . 'toolbox += ' . $text . ';';
        }
        if ($blocklyconf[5] == 1) {
            $jsconfcode .= "\n" . 'toolbox += ' . $lists . ';';
        }
        if ($blocklyconf[6] == 1) {
            $jsconfcode .= "\n" . 'toolbox += ' . $variables . ';';
        }
        if ($blocklyconf[7] == 1) {
            $jsconfcode .= "\n" . 'toolbox += ' . $functions . ';';
        }
        if ($blocklyconf[8] == 1) {
            $jsconfcode .= "\n" . 'toolbox += ' . $lab . ';'; // Starts lab category in the xml structure.
            if ($blocklyconf[9] == 1) {
                $jsconfcode .= "\n" . 'toolbox += ' . $labvariables . ';';
            }
            if ($blocklyconf[10] == 1) {
                $jsconfcode .= "\n" . 'toolbox += ' . $labfunctions . ';';
            }
            if ($blocklyconf[11] == 1) {
                $jsconfcode .= "\n" . 'toolbox += ' . $labcontrol . ';';
            }
            $jsconfcode .= "\n" . "toolbox += '</category>'"; // Closes the lab category if created.
        }
        $jsconfcode .= "\n" . "toolbox += '</xml>';";

        // Finally, create the javascript file.
        file_put_contents($filepath, $jsconfcode);
    }
}