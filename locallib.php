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
//  (UNED), Madrid, Spain/


/**
 * Internal library of functions for module ejsapp
 *
 * All the ejsapp specific functions, needed to implement the module
 * logic, are here.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 *
 * Updates the ejsapp, ejsapp_personal_vars, tables and files according to the .jar/.zip information
 *
 * @param stdClass $ejsapp record from table ejsapp
 * @param object $context context module
 *
 * @return boolean ejs_ok
 *
 */
function update_ejsapp_files_and_tables($ejsapp, $context) {
    global $CFG, $DB;

    $maxbytes = get_max_upload_file_size($CFG->maxbytes);

    // Creating the .jar or .zip file in dataroot and updating the files table in the database
    $draftitemid_applet = $ejsapp->appletfile;
    if ($draftitemid_applet) {
        file_save_draft_area_files($draftitemid_applet, $context->id, 'mod_ejsapp', 'jarfiles', $ejsapp->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => array('application/java-archive', 'application/zip')));
    }

    // Creating the state file in dataroot and updating the files table in the database
    $draftitemid_state = $ejsapp->statefile;
    if ($draftitemid_state) {
        file_save_draft_area_files($draftitemid_state, $context->id, 'mod_ejsapp', 'xmlfiles', $ejsapp->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => 'application/xml'));
    }

    // Creating the controller file in dataroot and updating the files table in the database
    $draftitemid_controller = $ejsapp->controllerfile;
    if ($draftitemid_controller) {
        file_save_draft_area_files($draftitemid_controller, $context->id, 'mod_ejsapp', 'cntfiles', $ejsapp->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));
    }

    // Creating the recording file in dataroot and updating the files table in the database
    $draftitemid_recording = $ejsapp->recordingfile;
    if ($draftitemid_recording) {
        file_save_draft_area_files($draftitemid_recording, $context->id, 'mod_ejsapp', 'recfiles', $ejsapp->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));
    }

    // Same with the content of the wording element
    $draftitemid_wording = $ejsapp->ejsappwording['itemid'];
    if ($draftitemid_wording) {
        $ejsapp->appwording = file_save_draft_area_files($draftitemid_wording, $context->id, 'mod_ejsapp', 'appwording', 0, array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'changeformat' => 1, 'context' => $context, 'noclean' => 1, 'trusttext' => 0), $ejsapp->appwording);
    }

    // Obtain the uploaded .zip or .jar file from moodledata using the information in the files table
    //$file_records = $DB->get_records('files', array('component'=>'user', 'filearea'=>'draft', 'itemid'=>$draftitemid_applet), 'filesize DESC');
    $file_records = $DB->get_records('files', array('contextid'=>$context->id, 'component'=>'mod_ejsapp', 'filearea'=>'jarfiles', 'itemid'=>$ejsapp->id), 'filesize DESC');
    $file_record = reset($file_records);
    $fs = get_file_storage();
    $file = $fs->get_file_by_id($file_record->id);

    // <In case it is an alias to an external repository>
    //$file->sync_external_file(); // Not doing what we expect for non-image files... we need a workaround
    if (class_exists('repository_filesystem')) {
        if (!is_null($file_record->referencefileid)) {
            $repository_instance_id = $DB->get_field('files_reference', 'repositoryid', array('id' => $file_record->referencefileid));
            $repository_type_id = $DB->get_field('repository_instances', 'typeid', array('id' => $repository_instance_id));
            if ($DB->get_field('repository', 'type', array('id' => $repository_type_id)) == 'filesystem') {
                $repository = repository_filesystem::get_instance($repository_instance_id);
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
    // </In case it is an alias to an external repository>

    // Create folders to store the .jar or .zip file
    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/';
    if (!file_exists($path)) {
        mkdir($path, 0755);
    }
    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course;
    if (!file_exists($path)) {
        mkdir($path, 0755);
    }
    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
    if (file_exists($path)) { // updating, not creating, the ejsapp activity
        delete_recursively($path);
    }
    mkdir($path, 0770);

    // Copy the jar/zip file to its destination folder in jarfiles
    $filepath = $path . $file_record->filename;
    $file->copy_content_to($filepath);

    // codebase
    $codebase = '';
    preg_match('/http:\/\/.+?\/(.+)/', $CFG->wwwroot, $match_result);
    if (!empty($match_result) and $match_result[1]) {
        $codebase .= '/' . $match_result[1];
    }
    $codebase .= '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';

    // <Initialize the mod_form elements>
    $ejsapp->class_file = '';
    $ejsapp->codebase = $codebase;
    $ejsapp->mainframe = '';
    $ejsapp->is_collaborative = 0;
    $ejsapp->manifest = 'EJsS';
    $ejsapp->height = 0;
    $ejsapp->width = 0;

    $ext = pathinfo($filepath, PATHINFO_EXTENSION);
    // Get params and set their corresponding values in the mod_form elements and update the ejsapp table
    if ($ext == 'jar') { //Java Applet
        $ejs_ok = modifications_for_java($filepath, $ejsapp, $file, $file_record, false);
    } else { //Javascript
        $codebase = '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
        $ejs_ok = modifications_for_javascript($filepath, $ejsapp, $path, $codebase);
    }

    $DB->update_record('ejsapp', $ejsapp);

    // We add an entry in Moodle's file table for the .zip or .jar file in the jarfiles directory
    $fileinfo = array(                          // Prepare file record object
        'contextid' => $context->id,            // ID of context
        'component' => 'mod_ejsapp',            // usually = table name
        'filearea' => 'tmp_jarfiles',           // usually = table name
        'itemid' => $ejsapp->id,                // usually = ID of row in table
        'filepath' => '/',                      // any path beginning and ending in /
        'filename' => $file_record->filename);  // any filename
    $fs->create_file_from_pathname($fileinfo, $filepath);

    // <update ejsapp_personal_vars table>
    //Personalizing EJS variables <update ejsapp_personal_vars table>
    $old_ejsapp = $DB->get_records('ejsapp_personal_vars', array('ejsappid' => $ejsapp->id));
    if (isset($old_ejsapp)) {  // We clean all the personalized variables configuration and start over again
        $DB->delete_records('ejsapp_personal_vars', array('ejsappid' => $ejsapp->id));
    }
    if($ejsapp->personalvars == 1) {
        $personal_vars = new stdClass();
        $personal_vars->ejsappid = $ejsapp->id;
        for ($i=0; $i < count($ejsapp->var_name); $i++) {
            if (strcmp($ejsapp->var_name[$i],'') != 0) { // Variables without name are ignored
                $personal_vars->name = $ejsapp->var_name[$i];
                $type_info = 'Boolean';
                $min_value = 0;
                $max_value = 1;
                if ($ejsapp->var_type[$i] == 1) {
                    $type_info = 'Integer';
                    $min_value = $ejsapp->min_value[$i];
                    $max_value = $ejsapp->max_value[$i];
                } elseif ($ejsapp->var_type[$i]== 2) {
                    $type_info = 'Double';
                    $min_value = $ejsapp->min_value[$i];
                    $max_value = $ejsapp->max_value[$i];
                }
                $personal_vars->type = $type_info;
                $personal_vars->minval = $min_value + 0;
                $personal_vars->maxval = $max_value + 0;
                $DB->insert_record('ejsapp_personal_vars', $personal_vars);
            }
        }
    }
    // </update ejsapp_personal_vars table>

    return $ejs_ok;
 } //update_db


/**
 *
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
        foreach($files as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        return @rmdir($dir);
    }
    return false;
} //delete_recursively


/**
 *
 * Creates the list of all Sarlab experiences accessible by a particular user.
 *
 * @param string $username
 * @param array $list_sarlab_IPs
 * @return string $listExperiences
 *
 */
function get_experiences_sarlab($username, $list_sarlab_IPs) {
    $listExperiences = '';

    $dom = new DOMDocument;
    $dom->validateOnParse = true;
    foreach ($list_sarlab_IPs as $sarlab_IP) {
        $last_quote_mark = strrpos($sarlab_IP, "'");
        if ($last_quote_mark != 0) $last_quote_mark++;
        $ip = substr($sarlab_IP, $last_quote_mark);
        if ($ip != '127.0.0.1' && $ip != '') {
            if ($fp = @fsockopen($ip, '80', $errCode, $errStr, 1)) { //IP is alive
                fclose($fp);
                $URI = 'http://' . $ip . '/';
                $file_headers = @get_headers($URI);
                if (substr($file_headers[0], 9, 3) == 200) { //Valid file
                    if ($dom->load($URI)) {
                        $experiences = $dom->getElementsByTagName('Experience'); //Get list of experiences
                        foreach ($experiences as $experience) {
                            $ownerUsers = $experience->getElementsByTagName('owneUser'); //Get list of users who can access the experience
                            foreach ($ownerUsers as $ownerUser) {
                                if ($username == $ownerUser->nodeValue || is_siteadmin()) { //Check whether the required user has access to the experience
                                    $idExperiences = $experience->getElementsByTagName('idExperience');
                                    foreach ($idExperiences as $idExperience) {
                                        $listExperiences .= $idExperience->nodeValue . ';'; //Add the experience to the user's list of accessible experiences
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

    $listExperiences = substr($listExperiences,0,-1);

    return $listExperiences;
} //get_experiences_sarlab


/**
 *
 * Gets the experiences defined without sarlab and combines them with those in Sarlab in a unique, ordered list.
 *
 * @param array $list_sarlab_experiences
 * @return array $list_combined_experiences
 *
 */
function combine_experiences($list_sarlab_experiences) {
    global $DB;
    $is_remlab_manager_installed = $DB->get_records('block',array('name'=>'remlab_manager'));
    $is_remlab_manager_installed = !empty($is_remlab_manager_installed);
    if ($is_remlab_manager_installed) {
        $list_remlab_experiences_without_sarlab = $DB->get_records('block_remlab_manager_conf', array('usingsarlab' => '0'));
        $list_combined_experiences = array();
        if ($list_sarlab_experiences[0] != '') $list_combined_experiences = $list_sarlab_experiences;
        foreach ($list_remlab_experiences_without_sarlab as $remlab_experiences_without_sarlab) {
            if (!in_array($remlab_experiences_without_sarlab->practiceintro, $list_sarlab_experiences)) {
                $list_combined_experiences[] = $remlab_experiences_without_sarlab->practiceintro;
            }
        }
    } else {
        $list_combined_experiences = $list_sarlab_experiences;
    }
    //Order the list alphabetically
    sort($list_combined_experiences);

    return $list_combined_experiences;
} //combine_experiences


/**
 *
 * Gets the experiences defined without sarlab and combines them with those in Sarlab in a unique, ordered list.
 *
 * @return array $list_showable_experiences
 *
 */
function get_showable_experiences() {
    global $USER;
    $list_sarlab_IPs = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
    //Get experiences from Sarlab
    $listExperiences = get_experiences_sarlab($USER->username, $list_sarlab_IPs);
    $list_sarlab_experiences = explode(";", $listExperiences);
    //Also get experiences NOT in Sarlab and add them to the list
    $list_showable_experiences = combine_experiences($list_sarlab_experiences);

    return $list_showable_experiences;
} //get_showable_experiences


/**
 *
 * Modifies links to libraries and images used by the EJsS javascript applications.
 *
 * @param string $codebase
 * @param stdClass $ejsapp
 * @param string $code
 * @param boolean $use_css
 * @return string $code
 *
 */
function update_links($codebase, $ejsapp, $code, $use_css) {
    global $CFG;

    $path = $CFG->wwwroot . $codebase;
    $exploded_name = explode("_Simulation",$ejsapp->applet_name);

    // Replace links for images and stuff
    $search = '("_topFrame","_ejs_library/",null);';
    $replace = '("_topFrame","' . $path . '_ejs_library/","' . $path . '");';
    $code = str_replace($search,$replace,$code);

    // Replace link for css
    $ejss_css = '_ejs_library/css/ejss.css';
    if (!file_exists($CFG->dirroot . $codebase . $ejss_css)) {
        $ejss_css = '_ejs_library/css/ejsSimulation.css';
    }
    $search = '<link rel="stylesheet"  type="text/css" href="' . $ejss_css;
    if ($use_css) {
        $replace = '<link rel="stylesheet"  type="text/css" href="' . $path . $ejss_css;
    } else {
        $replace = '';
    }
    $code = str_replace($search,$replace,$code);

    // Replace link for common_script.js and textsizedetector.js
    $search = '<script src="_ejs_library/scripts/common_script.js"></script>';
    $replace = '<script src="' . $path .'_ejs_library/scripts/common_script.js"></script>';
    $code = str_replace($search,$replace,$code);
    $search = '<script src="_ejs_library/scripts/textresizedetector.js"></script>';
    $replace = '<script src="' . $path .'_ejs_library/scripts/textresizedetector.js"></script>';
    $code = str_replace($search,$replace,$code);

    // Replace call for main function so we can later pass parameters to it
    $search = "window.addEventListener('load', function () {  new " . $exploded_name[0];
    $replace = "window.addEventListener('load', function () {  var _model = new " . $exploded_name[0];
    $code = str_replace($search,$replace,$code);

    return $code;
} //update_links


/**
 *
 * Generates the values of the personalized variables in a particular EJS application for a given user.
 *
 * @param stdClass $ejsapp
 * @param stdClass $user
 * @return stdClass $personalvarsinfo
 *
 */
function personalize_vars($ejsapp, $user) {
    global $DB;

    $personalvarsinfo = null;
    if ($ejsapp->personalvars == 1) {
        $personalvarsinfo = new stdClass();
        $personalvars = $DB->get_records('ejsapp_personal_vars', array('ejsappid' => $ejsapp->id));
        $i = 0;
        foreach ($personalvars as $personalvar) {
            $uniqueval = filter_var(md5($user->firstname . $i . $user->username . $user->lastname . $user->id .
                                    $personalvar->id . $personalvar->name . $personalvar->type . $user->email .
                                    $personalvar->minval . $personalvar->maxval), FILTER_SANITIZE_NUMBER_INT);
            $uniqueval = round($uniqueval);
            mt_srand($uniqueval/(pow(10,strlen($user->username))));
            $personalvarsinfo->name[$i] = $personalvar->name;
            $factor = 1;
            if ($personalvar->type == 'Double') $factor = 1000;
            $personalvarsinfo->value[$i] = mt_rand($factor*$personalvar->minval, $factor*$personalvar->maxval)/$factor;
            $personalvarsinfo->type[$i] = $personalvar->type;
            $i++;
        }
    }

    return $personalvarsinfo;
} //personalize_vars


/**
 *
 * Generates the values of the personalized variables in a particular EJS application for all the users in the course that ejsapp activity is.
 *
 * @param stdClass $ejsapp
 * @return array $userspersonalvarsinfo
 *
 */
function users_personalized_vars($ejsapp) {
    global $DB;

    $courseid = $ejsapp->course;
    $enrolids = $DB->get_fieldset_select('enrol', 'id', 'courseid = :courseid', array('courseid'=>$courseid));
    $usersids = $DB->get_fieldset_sql('SELECT userid FROM {user_enrolments} WHERE enrolid IN (' . implode(',',$enrolids) . ')');
    $users = $DB->get_records_sql('SELECT * FROM {user} WHERE id IN (' . implode(',',$usersids) . ')');
    $userspersonalvarsinfo = array();
    foreach ($users as $user) {
        $userspersonalvarsinfo[$user->id.''] = personalize_vars($ejsapp, $user);
    }

    return $userspersonalvarsinfo;
} //users_personalized_vars


/**
 *
 * For EjsS java applications.
 *
 * @param string $filepath
 * @param stdClass $ejsapp
 * @param stored_file $file
 * @param stdClass $file_record
 * @param boolean $alert
 * @return boolean $ejs_ok
 *
 */
function modifications_for_java($filepath, $ejsapp, $file, $file_record, $alert) {
    global $CFG;

    $ejs_ok = false;

    $ejsapp->applet_name = $file_record->filename;

    if (file_exists($filepath)) {
        // Extract the manifest.mf file from the .jar
        $manifest = file_get_contents('zip://' . $filepath . '#' . 'META-INF/MANIFEST.MF');

        // class_file
        $class_file = get_class_for_java($manifest);
        $ejsapp->class_file = preg_replace('/\s+/', "", $class_file); // delete all white-spaces and the first newline

        // mainframe
        $pattern = '/Main-Frame\s*:\s*(.+)\s*/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) == 0) {
            $mainframe = '';
        } else {
            $mainframe = $matches[1][0];
            $mainframe = preg_replace('/\s+/', "", $mainframe); // delete all white-spaces
        }
        $ejsapp->mainframe = $mainframe;

        // is_collaborative
        $pattern = '/Is-Collaborative\s*:\s*(\w+)/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) == 0) {
            $is_collaborative = 0;
        } else {
            $is_collaborative = trim($matches[1][0]);
            if ($is_collaborative == 'true') {
                $is_collaborative = 1;
            } else {
                $is_collaborative = 0;
            }
        }
        $ejsapp->is_collaborative = $is_collaborative;

        // height
        $pattern = '/Applet-Height\s*:\s*(\w+)/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) == 0) {
            $height = 0;
            // If this field does not exist in the manifest, it means the version of EJS used to compile the jar does not support Moodle.
            if ($alert) {
                $message = get_string('EJS_version', 'ejsapp');
                $alert = "<script type=\"text/javascript\">
                      window.alert(\"$message\")
                      </script>";
                echo $alert;
            }
        } else {
            $ejs_ok = true;
            $height = $matches[1][0];
            $height = preg_replace('/\s+/', "", $height); // delete all white-spaces
        }
        $ejsapp->height = $height;

        // width
        $width = get_width_for_java($manifest);
        $ejsapp->width = $width;

        // Sign the applet
        // Check whether a certificate is installed and in use
        //if (file_exists(get_config('mod_ejsapp', 'certificate_path')) && get_config('mod_ejsapp', 'certificate_password') != '' && get_config('mod_ejsapp', 'certificate_alias') != '') {
        // Check whether the applet has the codebase parameter in manifest.mf set to $CFG->wwwroot
        $pattern = '/\s*\nCodebase\s*:\s*(.+)\s*/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        $host = explode("://", $CFG->wwwroot);
        if (substr($matches[1][0], 0, -1) == $host[1]) {
            if (is_null($file->get_referencefileid())) { // linked files won't get signed!
                // Sign the applet
                shell_exec("jarsigner -storetype pkcs12 -keystore " . get_config('mod_ejsapp', 'certificate_path') . " -storepass " .
                            get_config('mod_ejsapp', 'certificate_password') . " -tsa http://timestamp.comodoca.com/rfc3161 " .
                            $filepath . " " . get_config('mod_ejsapp', 'certificate_alias'));
                // We replace the file stored in Moodle's filesystem and its table with the signed version:
                $file->delete();
                $fs = get_file_storage();
                $fs->create_file_from_pathname($file_record, $filepath);
            }
        } else if ($alert) { // Files which do not include the codebase parameter with the Moodle server direction are not signed
            $message = get_string('EJS_codebase', 'ejsapp');
            $alert = "<script type=\"text/javascript\">
                      window.alert(\"$message\")
                      </script>";
            echo $alert;
        }
    }

    return $ejs_ok;
} // modifications_for_java

/**
 *
 * Gets the java .class from the manifest
 *
 * @param string $manifest
 * @return string $class_file
 *
 */
function get_class_for_java($manifest){
    $pattern = '/Main-Class\s*:\s*(.+)\s*/';
    preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
    $sub_str = $matches[1][0];
    if (strlen($matches[1][0]) == 59) {
        $pattern = '/^\s(.+)\s*/m';
        if (preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE) > 0) {
            if (preg_match('/\s*:\s*/', $matches[1][0], $matches2, PREG_OFFSET_CAPTURE) == 0) {
                $sub_str = $sub_str . $matches[1][0];
            }
        }
    }
    $class_file = $sub_str . 'Applet.class';

    return $class_file;
}

/**
 *
 * Gets the java applet height from the manifest. If the form has the height param, it will be prioritary.
 *
 * @param string $manifest
 * @return string $class_file
 *
 */
function get_height_for_java($manifest){
    $height = 0;

    $pattern = '/Applet-Height\s*:\s*(\w+)/';
    preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
    if (count($matches) > 0) {
        $height = $matches[1][0];
        $height = preg_replace('/\s+/', "", $height); // delete all white-spaces
    }


    return $height;
}

/**
 *
 * Gets the java applet height from the manifest. If the form has the height param, it will be prioritary.
 *
 * @param string $manifest
 * @return string $class_file
 *
 */
function get_width_for_java($manifest){
    $width = 0;

    $pattern = '/Applet-Width\s*:\s*(\w+)/';
    preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
    if (count($matches) > 0) {
        $width = $matches[1][0];
        $width = preg_replace('/\s+/', "", $width); // delete all white-spaces
    }


    return $width;
}


/**
 *
 * For EjsS javascript applications.
 *
 * @param string $filepath
 * @param stdClass $ejsapp
 * @param string $folderpath
 * @param string $codebase
 * @return boolean $ejs_ok
 *
 */
function modifications_for_javascript($filepath, $ejsapp, $folderpath, $codebase) {
    global $CFG;

    $ejsapp->is_collaborative = 1;

    $zip = new ZipArchive;
    if ($zip->open($filepath) === TRUE) {
        $zip->extractTo($folderpath);
        $zip->close();
        $metadata = file_get_contents($folderpath . '_metadata.txt');
        $ejs_ok = true;

        // Search in _metadata for the name of the main Javascript file
        $pattern = '/main-simulation\s*:\s*(.+)\s*/';
        preg_match($pattern, $metadata, $matches, PREG_OFFSET_CAPTURE);
        $sub_str = $matches[1][0];
        if (strlen($matches[1][0]) == 59) {
            $pattern = '/^\s(.+)\s*/m';
            if ((preg_match($pattern, $metadata, $matches, PREG_OFFSET_CAPTURE) > 0)) {
                $sub_str = $sub_str . $matches[1][0];
            }
        }
        $ejsapp->applet_name = rtrim($sub_str);

        // Create/delete/modify the css file to modify the visual aspect of the javascript application
        // Custom css
        $use_original_css = false;
        $css_file_location = $folderpath . '_ejs_library/css/ejsapp.css';
        if ($ejsapp->css == '' && file_exists($css_file_location)) {
            unlink($css_file_location);
        }
        $css_file_content = "";
        if ($ejsapp->css != '') { // Custom css
            $lines = explode(PHP_EOL, $ejsapp->css);
            foreach ($lines as $line) {
                if (strpos($line, '{')) $css_file_content .= 'div#EJsS ' . $line;
                else $css_file_content .= $line;
            }
            $file = fopen($css_file_location, "w");
            fwrite($file, $css_file_content);
            fclose($file);
        } else { // Original css
            $use_original_css = true;
            $ejss_css = '_ejs_library/css/ejss.css';
            if (!file_exists($CFG->dirroot . $codebase . $ejss_css)) {
                $ejss_css = '_ejs_library/css/ejsSimulation.css';
            }
            $css_file_location = $folderpath . $ejss_css;
            $handle = fopen($css_file_location, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, '{')) $css_file_content .= 'div#EJsS ' . $line;
                    else $css_file_content .= $line;
                }
                fclose($handle);
                file_put_contents($css_file_location, $css_file_content);
            }
        }


        // Languages
        $languages = array('');
        $pattern = '/available-languages\s*:\s*(.+)\s*/';
        if (preg_match($pattern, $metadata, $matches, PREG_OFFSET_CAPTURE)) {
            $sub_str = $matches[1][0];
            if (strpos($sub_str, ',')) {
                $languages = explode(',', $sub_str);
                array_push($languages, '');
            }
        }

        // Change content of the html/js file to make it work
        foreach ($languages as $language) {
            if ($language == '') $filepath = $folderpath . $ejsapp->applet_name;
            else {
                $filename = substr($ejsapp->applet_name, 0, strpos($ejsapp->applet_name, '.'));
                $extension = substr($ejsapp->applet_name, strpos($ejsapp->applet_name, ".") + 1);
                $filepath = $folderpath . $filename . '_' . $language . '.' . $extension;
            }
            if (file_exists($filepath)) {
                $code = file_get_contents($filepath);
                //<get the whole code from </title> (not included) onwards>
                $code = explode('</title>', $code);
                $code = '<div id="EJsS">' . $code[1];
                //</get the whole code from </title> (not included) onwards>
                //<$code1 is $code till </head> (not included) and with the missing standard part>
                $code1 = substr($code, 0, -strlen($code) + strpos($code, '</head>')) . '<div id="_topFrame" style="text-align:center"></div>';
                //</$code1 is $code till </head> (not included) and with the missing standard part>
                if (strpos($code, '<script type')) { //EjsS version with Javascript embedded into the html page
                    //<$code2 is $code from </head> to </body> tags, none of them included>
                    $code2 = substr($code, strpos($code, '</head>'));
                    $code2 = explode('</body>', $code2);
                    $code2 = $code2[0] . '</div>';
                    //</$code2 is $code from </head> to </body> tags, none of them included>
                    $code2 = substr($code2, strpos($code2, '<script type'));
                } else { //EjsS version with an external .js file for the Javascript
                    $exploded_file_name = explode(".", $ejsapp->applet_name);
                    $code2 = '<script src="' . $CFG->wwwroot . $codebase . $exploded_file_name[0] . '.js"></script></div>';
                }
                $code = $code1 . $code2;
                $code = update_links($codebase, $ejsapp, $code, $use_original_css);
                file_put_contents($filepath, $code);
            }
        }
    } else {
        $ejs_ok = false;
    }

    chmod_r($folderpath);

    return $ejs_ok;
} // modifications_for_javascript


/**
 *
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
 *
 * Checks if a remote lab equipment is alive or not, either directly when it has a public IP or by asking Sarlab.
 *
 * @param string $host
 * @param int $port
 * @param int $usingsarlab
 * @param string $idExp
 * @param int $timeout
 * @return int 0, not alive; 1, alive; 2, not checkable
 *
 */
function ping($host, $port=80, $usingsarlab, $idExp=null, $timeout=3) {
    global $devices_info;

    $alive = fsockopen($host, $port, $errno, $errstr, $timeout);
    $not_checkable = false;
    if ($alive && $usingsarlab) {
        //Obtain the xml filename from idExp
        $URI = 'http://' . $host . '/';
        $file_headers = @get_headers($URI);
        if (substr($file_headers[0], 9, 3) == 200) { // Valid file
            $dom = new DOMDocument;
            $dom->validateOnParse = true;
            if ($dom->load($URI)) {
                $experiences = $dom->getElementsByTagName('Experience'); //Get list of experiences
                $xmlfilename = 'null';
                foreach ($experiences as $experience) {
                    $idExperiences = $experience->getElementsByTagName('idExperience'); //Get the name of the experience
                    foreach ($idExperiences as $idExperience) {
                        if ($idExperience->nodeValue == $idExp) {
                            $file_experiences = $experience->getElementsByTagName('fileName'); //Get the name of the xml file
                            foreach ($file_experiences as $file_experience) {
                                $xmlfilename = $file_experience->nodeValue;
                            }
                            break 2;
                        }
                    }
                }
                $URL = $URI . 'isAliveExp?' . $xmlfilename;
                if ($info = file_get_contents($URL)) {
                    $info = explode("=", $info);
                    $alive = (mb_strtoupper(trim($info[1])) === mb_strtoupper ("true")) ? TRUE : FALSE;
                    if (!$alive) {
                        // Get list of devices in the experience that are not alive and see which ones are down
                        $URL = $URI . 'pingExp' . $xmlfilename;
                        if ($info = file_get_contents($URL)) {
                            $devices = explode("Ping to ", $info);

                            function get_string_between($string, $start, $end){
                                $string = " ".$string;
                                $ini = strpos($string,$start);
                                if ($ini == 0) return "";
                                $ini += strlen($start);
                                $len = strpos($string,$end,$ini) - $ini;
                                return substr($string,$ini,$len);
                            }

                            foreach ($devices as $device) {
                                $devices_info[]->name = get_string_between($device, ": ", "ping ");
                                $ip = get_string_between($device, "ping ", "Reply from ");
                                $devices_info[]->ip = $ip;
                                $URL = $URI . 'isAlive?' . $ip;
                                if ($info = file_get_contents($URL)) {
                                    $devices_info[]->alive = (mb_strtoupper(trim($info[1])) === mb_strtoupper("true")) ? TRUE : FALSE;
                                }
                            }
                        }
                    }
                } else $not_checkable = true;
            } else $not_checkable = true;
        } else $not_checkable = true;
    }
    if ($not_checkable) return 2;
    if ($alive) return 1;
    else return 0;
} // ping


/**
 *
 * Creates a default record for the block_remlab_manager_conf table
 *
 * @param stdClass $ejsapp
 * @return stdClass $default_rem_lab_conf
 *
 */
function default_rem_lab_conf($ejsapp) {
    global $USER, $CFG;

    $default_rem_lab_conf = new stdClass();
    //Get experiences from Sarlab and check whether this practice is in a Sarlab server or not
    $list_sarlab_IPs = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
    $listExperiences = get_experiences_sarlab($USER->username, $list_sarlab_IPs);
    $list_sarlab_experiences = explode(";", $listExperiences);
    $complete_pract_list = explode(';', $ejsapp->list_practices);
    $default_rem_lab_conf->practiceintro = $complete_pract_list[$ejsapp->practiceintro];
    $default_rem_lab_conf->usingsarlab = 0;
    if(in_array($ejsapp->practiceintro, $list_sarlab_experiences)) $default_rem_lab_conf->usingsarlab = 1;
    if ($default_rem_lab_conf->usingsarlab == 1) {
        $sarlabinstance = 0;
        $default_rem_lab_conf->sarlabinstance = $sarlabinstance;
        $default_rem_lab_conf->sarlabcollab = 0;
        $list_sarlab_IPs = explode(";", get_config('block_remlab_manager', 'sarlab_IP'));
        $list_sarlab_ports = explode(";", get_config('block_remlab_manager', 'sarlab_port'));
        $init_char = strrpos($list_sarlab_IPs[intval($sarlabinstance)], "'");
        if ($init_char != 0) $init_char++;
        $ip = substr($list_sarlab_IPs[intval($sarlabinstance)], $init_char);
        $default_rem_lab_conf->ip = $ip;
        $default_rem_lab_conf->port = $list_sarlab_ports[intval($sarlabinstance)];
    } else {
        $default_rem_lab_conf->sarlabinstance = 0;
        $default_rem_lab_conf->sarlabcollab = 0;
        $default_rem_lab_conf->ip = '127.0.0.1';
        $default_rem_lab_conf->port = 443;
    }
    $default_rem_lab_conf->slotsduration = 1;
    $default_rem_lab_conf->totalslots = 18;
    $default_rem_lab_conf->weeklyslots = 9;
    $default_rem_lab_conf->dailyslots = 3;
    $default_rem_lab_conf->reboottime = 2;
    $default_rem_lab_conf->active = 1;
    $default_rem_lab_conf->free_access = 0;

    return $default_rem_lab_conf;
} // default_rem_lab_conf


/**
 *
 * Creates the record for the block_remlab_manager_exp2prc table
 *
 * @param stdClass $ejsapp
 * @return void
 *
 */
function ejsapp_expsyst2pract($ejsapp) {
    global $DB;

    $ejsapp_expsyst2pract = new stdClass();
    $ejsapp_expsyst2pract->ejsappid = $ejsapp->id;
    $ejsapp_expsyst2pract->practiceid = 1;
    $expsyst2pract_list = $ejsapp->list_practices;
    $expsyst2pract_list = explode(';', $expsyst2pract_list);
    $ejsapp_expsyst2pract->practiceintro = $expsyst2pract_list[$ejsapp->practiceintro];
    $DB->insert_record('block_remlab_manager_exp2prc', $ejsapp_expsyst2pract);
} // ejsapp_expsyst2pract


/**
 *
 * Updates the ejsappbooking_usersaccess table
 *
 * @param stdClass $ejsapp
 * @return void
 *
 */
function update_booking_table($ejsapp) {
    global $DB;

    function update_or_insert_record($ejsappbookingid, $ejsappbooking_usersaccess, $ejsappid) {
        global $DB;
        if (!$DB->record_exists('ejsappbooking_usersaccess', array('bookingid'=>$ejsappbookingid, 'userid'=>$ejsappbooking_usersaccess->userid, 'ejsappid'=>$ejsappid))) {
            $DB->insert_record('ejsappbooking_usersaccess', $ejsappbooking_usersaccess);
        } else {
            $record = $DB->get_record('ejsappbooking_usersaccess', array('bookingid'=>$ejsappbookingid, 'userid'=>$ejsappbooking_usersaccess->userid, 'ejsappid'=>$ejsappid));
            $ejsappbooking_usersaccess->id = $record->id;
            $DB->update_record('ejsappbooking_usersaccess', $ejsappbooking_usersaccess);
        }
    }

    if ($DB->record_exists('ejsappbooking', array('course' => $ejsapp->course))) {
        $course_context = context_course::instance($ejsapp->course);
        $users = get_enrolled_users($course_context);
        $ejsappbooking = $DB->get_record('ejsappbooking', array('course'=>$ejsapp->course));
        //ejsappbooking_usersaccess table:
        $ejsappbooking_usersaccess = new stdClass();
        $ejsappbooking_usersaccess->bookingid = $ejsappbooking->id;
        $ejsappbooking_usersaccess->ejsappid = $ejsapp->id;
        //Grant remote access to admin user:
        $ejsappbooking_usersaccess->userid = 2;
        $ejsappbooking_usersaccess->allowremaccess = 1;
        update_or_insert_record($ejsappbooking->id, $ejsappbooking_usersaccess, $ejsapp->id);
        //Consider other enrolled users:
        foreach ($users as $user) {
            $ejsappbooking_usersaccess->userid = $user->id;
            if (!has_capability('mod/ejsapp:addinstance', $course_context, $user->id, true)) {
                $ejsappbooking_usersaccess->allowremaccess = 0;
            } else {
                $ejsappbooking_usersaccess->allowremaccess = 1;
            }
            update_or_insert_record($ejsappbooking->id, $ejsappbooking_usersaccess, $ejsapp->id);
        }
    }
}


/**
 *
 * Checks whether a the booking system is being used in the course of a particular ejsapp activity or not.
 *
 * @param stdClass $ejsapp
 * @return bool $using_bs
 *
 */
function check_booking_system($ejsapp) {
    global $DB;

    $using_bs = false;
    if ($DB->record_exists('modules', array('name' => 'ejsappbooking'))) { //Is EJSApp Booking System plugins installed?
        $module = $DB->get_record('modules', array('name' => 'ejsappbooking'));
        if ($DB->record_exists('course_modules', array('course' => $ejsapp->course, 'module' => $module->id))) { //Is there an ejsappbooking instance in the course?
            if ($DB->get_field('course_modules', 'visible',  array('course' => $ejsapp->course, 'module' => $module->id))) { //Is it visible?
                $using_bs = true;
            }
        }
    }

    return $using_bs;
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
 * @param int $max_use_time
 * param int $max_use_time
 * @return stdClass $sarlabinfo
 *
 */
function check_users_booking($DB, $USER, $ejsapp, $currenttime, $sarlabinstance, $labmanager, $max_use_time) {
    $sarlabinfo = null;

    if ($DB->record_exists('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id, 'valid' => 1))) {
        $bookings = $DB->get_records('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id, 'valid' => 1));
        foreach ($bookings as $booking) { // If the user has an active booking, use that info
            if ($currenttime >= $booking->starttime && $currenttime < $booking->endtime) {
                $expsyst2pract = $DB->get_record('block_remlab_manager_exp2prc', array('ejsappid' => $ejsapp->id, 'practiceid' => $booking->practiceid));
                $practice = $expsyst2pract->practiceintro;
                $sarlabinfo = define_sarlab($sarlabinstance, 0, $practice, $labmanager, $max_use_time);
                break;
            }
        }
    }

    return $sarlabinfo;
}


/**
 * Checks if there is an active booking made by the current user and if there is, it gets the ending time of the farest consecutive booking
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

    if ($DB->record_exists('ejsappbooking_remlab_access', array('username' => $username, 'ejsappid' => $ejsappid, 'valid' => 1))) {
        $bookings = $DB->get_records('ejsappbooking_remlab_access', array('username' => $username, 'ejsappid' => $ejsappid, 'valid' => 1));
        function cmp($a, $b) {
            return strtotime($a->starttime) - strtotime($b->starttime);
        }
        usort($bookings, "cmp");
        foreach ($bookings as $booking) { // If the user has an active booking, check the time
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
 * @return string $username_with_active_booking
 *
 */
function check_anyones_booking($DB, $ejsapp, $currenttime) {
    $username_with_active_booking = '';

    if ($DB->record_exists('ejsappbooking_remlab_access', array('ejsappid' => $ejsapp->id, 'valid' => 1))) {
        $bookings = $DB->get_records('ejsappbooking_remlab_access', array('ejsappid' => $ejsapp->id, 'valid' => 1));
        foreach ($bookings as $booking) { // If the user has an active booking, use that info
            if ($currenttime >= $booking->starttime && $currenttime < $booking->endtime) {
                $username_with_active_booking = $booking->username;
            }
        }
    }

    return $username_with_active_booking;
}


/**
 *
 * Gets information about the first and last access a user made to the remote lab. It also retrieves the maximum time of use allowed for that lab.
 *
 * @param array stdClass $repeated_ejsapp_labs
 * @param array int $slotsduration
 * @param int $currenttime
 * @return array int $time_information
 *
 */
function get_occupied_ejsapp_time_information($repeated_ejsapp_labs, $slotsduration, $currenttime) {
    global $DB, $USER;

    $time_first_access = 0; //TODO: Change to INF when we stop resetting time when a user connected to a remote lab refreshes the page
    $time_last_access = 0;
    $occupied_ejsapp_max_use_time = 3600;
    foreach($repeated_ejsapp_labs as $repeated_ejsapp_lab) {
        if (isset($repeated_ejsapp_lab->name)) {
            // Retrieve information from ejsapp's logging table
            $working_log_records = $DB->get_records('ejsapp_log', array('info'=>$repeated_ejsapp_lab->name, 'action'=>'working'));
            $viewed_log_records = $DB->get_records('ejsapp_log', array('info'=>$repeated_ejsapp_lab->name, 'action'=>'viewed'));
            $user_occupying_lab_id = $USER->id;
            foreach ($working_log_records as $working_log_record) {
                if ($working_log_record->userid != $USER->id) {
                    if ($working_log_record->time > $time_last_access) {
                        $time_last_access = $working_log_record->time;
                        $user_occupying_lab_id = $working_log_record->userid;
                    }
                }
            }
            $practiceintro = $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro', array('ejsappid' => $repeated_ejsapp_lab->id));
            foreach ($viewed_log_records as $viewed_log_record) {
                if ($viewed_log_record->userid != $USER->id) {
                    if ($viewed_log_record->userid == $user_occupying_lab_id) { // accesses of the user that is currently working with the rem lab
                        $occupied_ejsapp_slotsduration_conf = $DB->get_field('block_remlab_manager_conf', 'slotsduration', array('practiceintro' => $practiceintro));
                        if ($occupied_ejsapp_slotsduration_conf > 4) $occupied_ejsapp_slotsduration_conf = 0;
                        $occupied_ejsapp_maxslots = $DB->get_field('block_remlab_manager_conf', 'dailyslots', array('practiceintro' => $practiceintro));
                        $occupied_ejsapp_max_use_time = $occupied_ejsapp_maxslots * 60 * $slotsduration[$occupied_ejsapp_slotsduration_conf];
                        if ($viewed_log_record->time > $currenttime - $occupied_ejsapp_max_use_time) {
                            $time_first_access = max($time_first_access, $viewed_log_record->time); // TODO: Change to min when we stop resetting time when a user connected to a remote lab refreshes the page
                        }
                    }
                }
            }
        }
    }
    if ($time_first_access == 0) $time_first_access = INF;
    $time_information['time_first_access'] = $time_first_access;
    $time_information['time_last_access'] = $time_last_access;
    $time_information['occupied_ejsapp_max_use_time'] = $occupied_ejsapp_max_use_time;

    return $time_information;
}


/**
 *
 * Checks whether a remote lab is available, in use or rebooting.
 *
 * @param array $time_information
 * @param int $idle_time
 * @param int $check_activity
 * @return string $status
 *
 */
function get_lab_status($time_information, $idle_time, $check_activity) {
    $status = 'in_use';
    $currenttime = time();
    if ($currenttime - $time_information['time_last_access'] - 60*$idle_time - $check_activity > 0) { //-$check_activity because the last 'working' log doesn't get recorded
        $status = 'available';
    } else if ($currenttime - $time_information['time_last_access'] - $check_activity > 0) { //-$check_activity because the last 'working' log doesn't get recorded
        $status = 'rebooting';
    }
    return $status;
}


/**
 *
 * Gets the remaining time till the lab is available again.
 *
 * @param array $booking_info
 * @param array $status
 * @param array $time_information
 * @param int $idle_time
 * @param int $check_activity
 * @return string $remaining_time
 *
 */
function get_remaining_time($booking_info, $status, $time_information, $idle_time, $check_activity) {
    global $DB;
    $currenttime = time();
    if ($booking_info["active_booking"]) {
        if (array_key_exists('username_with_booking', $booking_info) && array_key_exists('ejsappid', $booking_info)) {
            $ending_time = check_last_valid_booking($DB, $booking_info['username_with_booking'], $booking_info['ejsappid']);
            $ending_time = strtotime($ending_time);
            $remaining_time = 60 * $idle_time + $ending_time - $currenttime;
        } else { // in_use
            if ($time_information['time_first_access'] == INF) $time_information['time_first_access'] = time();
            $remaining_time = 60 * $idle_time + $time_information['occupied_ejsapp_max_use_time'] - ($currenttime - $time_information['time_first_access']);
        }

    } else {
        if ($status == 'available') {
            $remaining_time = 0;
        } else if ($status == 'rebooting') {
            $remaining_time = 60 * $idle_time + $check_activity - ($currenttime - $time_information['time_last_access']);
        } else { // in_use
            if ($time_information['time_first_access'] == INF) $time_information['time_first_access'] = time();
            $remaining_time = 60 * $idle_time + $time_information['occupied_ejsapp_max_use_time'] - ($currenttime - $time_information['time_first_access']);
        }
    }
    return $remaining_time;
}


/**
 *
 * Checks whether a particular remote lab is also present in other courses or not and gives the list of repeated labs.
 *
 * @param stdClass $remlab_conf
 * @return array $repeated_ejsapp_labs
 *
 */
function get_repeated_remlabs($remlab_conf) {
    global $DB;

    $repeated_practices = $DB->get_records('block_remlab_manager_exp2prc', array('practiceintro'=>$remlab_conf->practiceintro));
    $ejsappids = array();
    foreach ($repeated_practices as $repeated_practice) {
        array_push($ejsappids, $repeated_practice->ejsappid);
    }
    $repeated_ejsapp_labs = $DB->get_records_list('ejsapp', 'id', $ejsappids);

    return $repeated_ejsapp_labs;
}


/**
 *
 * Gives the list of repeated remote labs in courses with a booking system.
 *
 * @param array $repeated_ejsapp_labs
 * @return array $repeated_ejsapp_labs_with_bs
 *
 */
function get_repeated_remlabs_with_bs($repeated_ejsapp_labs) {

    $repeated_ejsapp_labs_with_bs = array();
    foreach ($repeated_ejsapp_labs as $repeated_ejsapp_lab) {
        if (check_booking_system($repeated_ejsapp_lab)) array_push($repeated_ejsapp_labs_with_bs, $repeated_ejsapp_lab);
    }

    return $repeated_ejsapp_labs_with_bs;
}


/**
 *
 * Tells if there is at least one different course in which the same remote lab has been booked for this hour and if it is,
 * it returns an array with the name of the user with the booking and the id of that ejsapp activity.
 *
 * @param array $repeated_ejsapp_labs
 * @param int $courseid
 * @return array $book_info
 *
 */
function check_active_booking($repeated_ejsapp_labs, $courseid) {
    global $DB;

    $book_info = array();
    $book_info['active_booking'] = false;
    if (count($repeated_ejsapp_labs) > 1) {
        $repeated_ejsapp_labs_with_bs = get_repeated_remlabs_with_bs($repeated_ejsapp_labs);
        if (count($repeated_ejsapp_labs_with_bs) > 0) {
            foreach ($repeated_ejsapp_labs_with_bs as $repeated_ejsapp_lab_with_bs) {
                if ($repeated_ejsapp_lab_with_bs->course != $courseid) {
                    $book_info['username_with_booking'] = check_anyones_booking($DB, $repeated_ejsapp_lab_with_bs, date('Y-m-d H:i:s'));
                    if (!empty($book_info['username_with_booking'])) {
                        $book_info['active_booking'] = true;
                        $book_info['ejsappid'] = $repeated_ejsapp_lab_with_bs->id;
                        break;
                    }
                }
            }
        }
    }

    return $book_info;
}


/**
 *
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
    $remote_lab_access = new stdClass;

    $practice = $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro', array('ejsappid' => $ejsapp->id));
    $remote_lab_access->remlab_conf = $DB->get_record('block_remlab_manager_conf', array('practiceintro' => $practice));

    //<Check if the remote lab is operative>
    $remote_lab_access->operative = true;
    $ejsapp_lab_active = $DB->get_field('block_remlab_manager_conf', 'active', array('practiceintro' => $practice));
    if ($ejsapp_lab_active == 0) {
        $remote_lab_access->operative = false;
    }
    //</Check if the remote lab is operative>

    //<Check if we should grant free access to the user for this remote lab>
    $remote_lab_access->allow_free_access = true;
    $remote_lab_access->labmanager = has_capability('mod/ejsapp:accessremotelabs', $coursecontext, $USER->id, true);
    $remote_lab_access->repeated_ejsapp_labs = get_repeated_remlabs($remote_lab_access->remlab_conf);
    $remote_lab_access->booking_info = check_active_booking($remote_lab_access->repeated_ejsapp_labs, $course->id);
    $booking_system_in_use = check_booking_system($ejsapp);
    if (!$remote_lab_access->labmanager) { // The user does not have special privileges and...
        if (($remote_lab_access->remlab_conf->free_access != 1) && $booking_system_in_use) { //Not free access and the booking system is in use
            $remote_lab_access->allow_free_access = false;
        } else if (($remote_lab_access->remlab_conf->free_access == 1) && $remote_lab_access->booking_info['active_booking']) { //Free access and there is an active booking for this remote lab made by anyone in a different course
            $remote_lab_access->allow_free_access = false;
        } else if (($remote_lab_access->remlab_conf->free_access != 1) && !$booking_system_in_use && $remote_lab_access->booking_info['active_booking']) { //Not free access, the booking system is not in use and there is an active booking for this remote lab made by anyone in a different course
            $remote_lab_access->allow_free_access = false;
        }
    }
    //</Check if we should grant free access to the user for this remote lab>

    return $remote_lab_access;
}


/**
 *
 * Returns some the time use information regarding a particular remote lab.
 *
 * @param stdClass $remlab_conf
 * @param stdClass $repeated_ejsapp_labs
 * @return stdClass $remote_lab_info
 *
 */
function remote_lab_use_time_info($remlab_conf, $repeated_ejsapp_labs) {
    $remote_lab_time = new stdClass;

    //<Getting the maximum time the user is allowed to use the remote lab>
    $maxslots = $remlab_conf->dailyslots;
    $slotsduration_conf = $remlab_conf->slotsduration;
    if ($slotsduration_conf > 4) $slotsduration_conf = 4;
    $slotsduration = array(60, 30,15, 5, 2);
    $remote_lab_time->max_use_time = $maxslots * 60 * $slotsduration[$slotsduration_conf]; //in seconds
    //</Getting the maximum time the user is allowed to use the remote lab>

    //Search past accesses to this ejsapp lab or to the same remote lab added as a different ejsapp activity in this or any other course
    $remote_lab_time->time_information = get_occupied_ejsapp_time_information($repeated_ejsapp_labs, $slotsduration, time());

    return $remote_lab_time;
}


/**
 * Defines a new sarlab object with all the needed information
 *
 * @param int $instance sarlab instance
 * @param int $collab 0 if not a collab session, 1 if collaborative
 * @param string $practice the practice identifier in sarlab
 * @param int $labmanager whether the user is a laboratory manager or not
 * @param int $max_use_time maximum time for using the remote lab
 * @return stdClass $sarlabinfo
 *
 */
function define_sarlab($instance, $collab, $practice, $labmanager, $max_use_time) {
    $sarlabinfo = new stdClass();
    $sarlabinfo->instance = $instance;
    $sarlabinfo->collab = $collab;
    $sarlabinfo->practice = $practice;
    $sarlabinfo->labmanager = $labmanager;
    $sarlabinfo->max_use_time = $max_use_time;

    return $sarlabinfo;
}


/**
 * Gets the required EJS .jar or .zip file for this activity from Moodle's File System and places it
 * in the required directory (inside jarfiles) when the file there doesn't exist or it is not synchronized with
 * the file in Moodle's File System (whether because its an alias to a file that has been modified or because
 * the activity has been edited and the original .jar or .zip file has been replaced by a new one).
 *
 * @param stdClass $ejsapp
 * @return void
 *
 */
function prepare_ejs_file($ejsapp) {
    global $DB, $CFG;

    function delete_outdated_file($storedfile, $temp_file, $folderpath) {
        // We compare the content of the linked file with the content of the file in the jarfiles folder:
        if ($storedfile->get_contenthash() != $temp_file->get_contenthash()) { //if they are not the same...
            // Delete the files in jarfiles directory in order to replace them with the content of $storedfile
            delete_recursively($folderpath);
            if (!file_exists($folderpath)) mkdir($folderpath, 0700);
            // Delete $temp_file from Moodle filesystem
            $temp_file->delete();
            return true;
        } else { // If the file exists and matches the one configured in the ejsapp activity, do nothing
            return false;
        }
    }

    function create_temp_file($contextid, $ejsappid, $filename, $fs, $temp_filepath) {
        $fileinfo = array(
            'contextid' => $contextid,
            'component' => 'mod_ejsapp',
            'filearea' => 'tmp_jarfiles',
            'itemid' => $ejsappid,
            'filepath' => '/',
            'filename' => $filename);
        return $temp_file = $fs->create_file_from_pathname($fileinfo, $temp_filepath);
    }

    // We first get the jar/zip file configured in the ejsapp activity and stored in the filesystem
    $file_records = $DB->get_records('files', array('component'=>'mod_ejsapp', 'filearea'=>'jarfiles', 'itemid'=>$ejsapp->id), 'filesize DESC');
    $file_record = reset($file_records);
    if ($file_record) {
        $fs = get_file_storage();
        $storedfile = $fs->get_file_by_id($file_record->id);

        // <In case it is an alias to an external repository>
        //$storedfile->sync_external_file(); // Not doing what we expect for non-image files... we need a workaround
        if (class_exists('repository_filesystem')) {
            if (!is_null($file_record->referencefileid)) {
                $repository_instance_id = $DB->get_field('files_reference', 'repositoryid', array('id' => $file_record->referencefileid));
                $repository_type_id = $DB->get_field('repository_instances', 'typeid', array('id' => $repository_instance_id));
                if ($DB->get_field('repository', 'type', array('id' => $repository_type_id)) == 'filesystem') {
                    $repository = repository_filesystem::get_instance($repository_instance_id);
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
        // </In case it is an alias to an external repository>

        $codebase = '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
        $folderpath = $CFG->dirroot . $codebase;
        $ext = pathinfo($file_record->filename, PATHINFO_EXTENSION);
        $filepath = $folderpath . $file_record->filename;
        // We get the file stored in Moodle filesystem for the file in jarfiles, compare it and delete it if it is outdated
        $tmp_file_records = $DB->get_records('files', array('contextid' => $file_record->contextid, 'component' => 'mod_ejsapp', 'filearea' => 'tmp_jarfiles', 'itemid' => $ejsapp->id), 'filesize DESC');
        $tmp_file_record = reset($tmp_file_records);
        if (file_exists($filepath)) { // if file in jarfiles exists...
            if ($tmp_file_record) { // the file exists in jarfiles and in Moodle filesystem
                $temp_file = $fs->get_file_by_id($tmp_file_record->id);
            } else { // the file exists in jarfiles but not in Moodle filesystem (can happen with older versions of ejsapp plugins that have been updated recently or after duplicating or restoring an ejsapp activity)
                $temp_file = create_temp_file($file_record->contextid, $ejsapp->id, $file_record->filename, $fs, $filepath);
            }
            $delete = delete_outdated_file($storedfile, $temp_file, $folderpath);
            if (!$delete) return; //If files are the same, we have finished
        } else { // if file in jarfiles doesn't exists... (this should never happen actually, but just in case...)
            // We create the directories in jarfiles to put inside $storedfile
            $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/';
            if (!file_exists($path)) mkdir($path, 0700);
            $path .= $ejsapp->course . '/';
            if (!file_exists($path)) mkdir($path, 0700);
            if (!file_exists($folderpath)) mkdir($folderpath, 0700);
            if ($tmp_file_record) { // the file does not exist in jarfiles but it does in Moodle filesystem
                $temp_file = $fs->get_file_by_id($tmp_file_record->id);
                $temp_file->delete();
            }
        }

        // We copy the content of storedfile to jarfiles and add it to the file storage
        $storedfile->copy_content_to($filepath);
        create_temp_file($file_record->contextid, $ejsapp->id, $ejsapp->applet_name, $fs, $filepath);

        // We need to do a few more things depending on whether it is a Java applet or Javascript:
        if ($ext == 'jar') {
            modifications_for_java($filepath, $ejsapp, $storedfile, $file_record, false);
        } else {
            modifications_for_javascript($filepath, $ejsapp, $folderpath, $codebase);
        }
        $DB->update_record('ejsapp', $ejsapp);
    }
}