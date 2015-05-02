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
 * Updates the ejsapp, ejsapp_personal_vars, and files tables according to the .jar/.zip information
 *
 * @param stdClass $ejsapp record from table ejsapp
 * @param object $context context module
 *
 * @return boolean ejs_ok
 */
function update_ejsapp_and_files_tables($ejsapp, $context) {
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
        mkdir($path, 0700);
    }
    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course;
    if (!file_exists($path)) {
        mkdir($path, 0700);
    }
    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
    if (file_exists($path)) { // updating, not creating, the ejsapp activity
        delete_recursively($path);
    }
    mkdir($path, 0700);

    // Copy the jar/zip file to its destination folder in jarfiles
    $filepath = $path . $file_record->filename;
    $file->copy_content_to($filepath);

    // codebase
    $codebase = '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';

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
    // </update ejsapp_personal_vars table> // TODO: Only update personal variables that were set new when updating an ejsapp activity

    return $ejs_ok;
 } //update_db


/**
 * Deletes a directory from the server
 *
 * @param string $dir directory to delete
 * @return bool TRUE on success or FALSE on failure
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
} //delete_recursively


/**
 *
 * Creates the list of all Sarlab experiences accessible by a particular user.
 *
 * @param string $username
 * @param array $list_sarlab_IPs
 * @return array $listExperiences
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
        if($fp = @fsockopen($ip, '80', $errCode, $errStr, 1)) { //IP is alive
            $URI = 'http://' . $ip .'/';
            $file_headers = @get_headers($URI);
            if (substr($file_headers[0], 9, 3) == 200) { //Valid file
                if ($dom->load($URI)) {
                    $experiences = $dom->getElementsByTagName('Experience'); //Get list of experiences
                    foreach ($experiences as $experience) {
                        $owneUsers = $experience->getElementsByTagName('owneUser'); //Get list of users who can access the experience
                        foreach ($owneUsers as $owneUser) {
                            if ($username == $owneUser->nodeValue || $username == 'admin') { //Check whether the required user has access to the experience
                                $idExperiences = $experience->getElementsByTagName('idExperience');
                                foreach ($idExperiences as $idExperience) {
                                    $listExperiences .= $idExperience->nodeValue . ';' ; //Add the experience to the user's list of accessible experiences
                                }
                                break;
                            }
                        }
                    }
                }
            }
            fclose($fp);
        }
    }

    $listExperiences = substr($listExperiences,0,-1);

    return $listExperiences;
} //get_experiences_sarlab


/**
 *
 * Modifies links to libraries and images used by the EJsS javascript applications.
 *
 * @param string $codebase
 * @param stdClass $ejsapp
 * @param string $code
 * @param string $method
 * @param boolean $use_css
 * @return string $code
 *
 */
function update_links($codebase, $ejsapp, $code, $method, $use_css) {
    global $CFG;

    $path = $CFG->wwwroot . $codebase;

    // Replace links for images
    if ($method == 'old') {
        $exploded_name = explode("_Simulation",$ejsapp->applet_name);
        $search = "window.addEventListener('load', function () {  new " . $exploded_name[0] . '("_topFrame","_ejs_library/",null);';
        $replace = "window.addEventListener('load', function () {  new " . $exploded_name[0] . '("_topFrame","' . $path . '_ejs_library/","' . $path . '");';
    } else {
        $search = '("_topFrame","_ejs_library/",null);';
        $replace = '("_topFrame","' . $path . '_ejs_library/","' . $path . '");';
    }
    $code = str_replace($search,$replace,$code);

    // Replace link for css
    $search = '<link rel="stylesheet"  type="text/css" href="_ejs_library/css/ejsSimulation.css" />';
    if ($use_css) {
        $replace = '<link rel="stylesheet"  type="text/css" href="' . $path . '_ejs_library/css/ejsSimulation.css" />';
    } else {
        $replace = '';
    }
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
 */
function modifications_for_java($filepath, $ejsapp, $file, $file_record, $alert) {
    global $CFG;

    $ejs_ok = false;

    $ejsapp->applet_name = $file_record->filename;

    if (file_exists($filepath)) {
        // Extract the manifest.mf file from the .jar
        $manifest = file_get_contents('zip://' . $filepath . '#' . 'META-INF/MANIFEST.MF');

        // class_file
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
        $pattern = '/Applet-Width\s*:\s*(\w+)/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) == 0) {
            $width = 0;
        } else {
            $width = $matches[1][0];
            $width = preg_replace('/\s+/', "", $width); // delete all white-spaces
        }
        $ejsapp->width = $width;

        // Sign the applet
        // Check whether a certificate is installed and in use
        //if (file_exists(get_config('ejsapp', 'certificate_path')) && get_config('ejsapp', 'certificate_password') != '' && get_config('ejsapp', 'certificate_alias') != '') {
        // Check whether the applet has the codebase parameter in manifest.mf set to $CFG->wwwroot
        $pattern = '/\s*\nCodebase\s*:\s*(.+)\s*/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        $host = explode("://", $CFG->wwwroot);
        if (substr($matches[1][0], 0, -1) == $host[1]) {
            if (is_null($file->get_referencefileid())) { // linked files must be already signed!
                // Sign the applet
                shell_exec(dirname(__FILE__) . DIRECTORY_SEPARATOR . './sign.sh ' .
                    $filepath . ' ' .                                       // parameter 1
                    get_config('ejsapp', 'certificate_path') . ' ' .        // parameter 2
                    get_config('ejsapp', 'certificate_password') . ' ' .    // parameter 3
                    get_config('ejsapp', 'certificate_alias')               // parameter 4
                );
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
 * For EjsS javascript applications.
 *
 * @param string $folderpath
  * @param stdClass $ejsapp
 * @param string $filepath
 * @param string $codebase
 * @return boolean $ejs_ok
 */
function modifications_for_javascript($filepath, $ejsapp, $folderpath, $codebase) {
    global $CFG;

    $zip = new ZipArchive;
    if ($zip->open($filepath) === TRUE) {
        $zip->extractTo($folderpath);
        $zip->close();
        $metadata = file_get_contents($folderpath . '_metadata.txt');
        $ejs_ok = true;
    } else {
        $ejs_ok = false;
    }

    if ($ejs_ok) {
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
        $ejsapp->applet_name = $sub_str;

        //Create/delete the css file to modify the visual aspect of the javascript application
        $css_file_location = $CFG->dirroot . $ejsapp->codebase . '_ejs_library/css/ejsapp.css';
        if ($ejsapp->css == '' && file_exists($css_file_location)) {
            unlink($css_file_location);
        }
        if ($ejsapp->css != '') {
            $css_file_content = '#EJsS{' . $ejsapp->css . '}';
            $file = fopen($css_file_location, "w");
            fwrite($file, $css_file_content);
            fclose($file);
        }

        // Change content of the html/js file to make them work
        if (file_exists($folderpath . $ejsapp->applet_name)) {
            $code = file_get_contents($folderpath . $ejsapp->applet_name);
            //<get the whole code from </title> (not included) onwards>
            $code = explode('</title>', $code);
            $code = '<div id="EJsS">' . $code[1];
            //</get the whole code from </title> (not included) onwards>
            //<$code1 is $code till </head> (not included) and with the missing standard part>
            $code1 = substr($code, 0, -strlen($code) + strpos($code, '</head>')) . '<div id="_topFrame" style="text-align:center"></div>';
            //</$code1 is $code till </head> (not included) and with the missing standard part>
            //<$code2 is $code from </head> to </body> tags, none of them included>
            $code2 = substr($code, strpos($code, '</head>'));
            $code2 = explode('</body>', $code2);
            $code2 = $code2[0] . '</div>';
            //</$code2 is $code from </head> to </body> tags, none of them included>
            if (strpos($code, '<script type')) { //Old EJS version with Javascript embedded into the html page
                $code2 = substr($code2, strpos($code2, '<script type'));
                $code = $code1 . $code2;
                $code = update_links($codebase, $ejsapp, $code, 'old', false);
            } else { //New EJS version with an external .js file for the Javascript
                $exploded_file_name = explode(".", $ejsapp->applet_name);
                $code2 = '<script src="' . $CFG->wwwroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/' . $exploded_file_name[0] . '.js"></script></body></html>';
                $code = $code1 . $code2;
                $codeJS = file_get_contents($folderpath . $exploded_file_name[0] . '.js');
                $codeJS = update_links($codebase, $ejsapp, $codeJS, 'new', false);
                file_put_contents($folderpath . $exploded_file_name[0] . '.js', $codeJS);
            }
            file_put_contents($folderpath . $ejsapp->applet_name, $code);
        }
    }

    return $ejs_ok;
} // modifications_for_javascript


/**
 *
 * Checks if a remote lab equipment is alive or not, either directly when it has a public IP or by asking Sarlab.
 *
 * @param string $host
 * @param string $port
 * @param stdClass $usignsarlab
 * @param string $idExp
 * @param string $timeout
 * @return int 0, not alive; 1, alive; 2, not checkable
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
 * Creates the record for the ejsapp_rem_lab table
 *
 * @param stdClass $ejsapp
 * @return stdClass $ejsapp_rem_lab
 */
function ejsapp_rem_lab_conf($ejsapp) {
    global $CFG;

    $ejsapp_rem_lab = new stdClass();
    $ejsapp_rem_lab->ejsappid = $ejsapp->id;
    $ejsapp_rem_lab->usingsarlab = $ejsapp->sarlab;
    $ejsapp_rem_lab->active = $ejsapp->active;
    if ($ejsapp_rem_lab->usingsarlab == 1) {
        $sarlabinstance = $ejsapp->sarlab_instance;
        $ejsapp_rem_lab->sarlabinstance = $sarlabinstance;
        $ejsapp_rem_lab->sarlabcollab = $ejsapp->sarlab_collab;
        $list_sarlab_IPs = explode(";", $CFG->sarlab_IP);
        $list_sarlab_ports = explode(";", $CFG->sarlab_port);
        $init_char = strrpos($list_sarlab_IPs[intval($sarlabinstance)], "'");
        if ($init_char != 0) $init_char++;
        $ip = substr($list_sarlab_IPs[intval($sarlabinstance)], $init_char);
        $ejsapp_rem_lab->ip = $ip;
        $ejsapp_rem_lab->port = $list_sarlab_ports[intval($sarlabinstance)];
    } else {
        $ejsapp_rem_lab->sarlabinstance = '0';
        $ejsapp_rem_lab->sarlabcollab = '0';
        $ejsapp_rem_lab->ip = $ejsapp->ip_lab;
        $ejsapp_rem_lab->port = $ejsapp->port;
    }
    $ejsapp_rem_lab->slotsduration = $ejsapp->slotsduration;
    $ejsapp_rem_lab->totalslots = $ejsapp->totalslots;
    $ejsapp_rem_lab->weeklyslots = $ejsapp->weeklyslots;
    $ejsapp_rem_lab->dailyslots = $ejsapp->dailyslots;
    $ejsapp_rem_lab->reboottime = $ejsapp->reboottime;

    return $ejsapp_rem_lab;
} // ejsapp_rem_lab_conf


/**
 *
 * Creates the record for the ejsapp_expsyst2pract table
 *
 * @param stdClass $ejsapp
 * @return void
 */
function ejsapp_expsyst2pract($ejsapp) {
    global $DB;

    $ejsapp_expsyst2pract = new stdClass();
    $ejsapp_expsyst2pract->ejsappid = $ejsapp->id;
    if ($ejsapp->sarlab == 1) {
        $expsyst2pract_list = $ejsapp->list_practices;
        $expsyst2pract_list = explode(";", $expsyst2pract_list);
        $selected_practices = $ejsapp->practiceintro;
        for ($i = 0; $i < count($selected_practices); $i++) {
            $ejsapp_expsyst2pract->practiceid = $i + 1;
            $ejsapp_expsyst2pract->practiceintro = $expsyst2pract_list[$selected_practices[$i]];
            $DB->insert_record('ejsapp_expsyst2pract', $ejsapp_expsyst2pract);
        }
    } else {
        $ejsapp_expsyst2pract->practiceid = 1;
        $ejsapp_expsyst2pract->practiceintro = $ejsapp->name;
        $DB->insert_record('ejsapp_expsyst2pract', $ejsapp_expsyst2pract);
    }

} // ejsapp_expsyst2pract


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
 * @param stdClass $remlab_conf
 * @param int $labmanager
 * param int $max_use_time
 * @return stdClass $sarlabinfo
 */
function check_users_booking($DB, $USER, $ejsapp, $currenttime, $remlab_conf, $labmanager, $max_use_time) {
    $sarlabinfo = null;

    if ($DB->record_exists('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id, 'valid' => 1))) {
        $bookings = $DB->get_records('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id, 'valid' => 1));
        foreach ($bookings as $booking) { // If the user has an active booking, use that info
            if ($currenttime >= $booking->starttime && $currenttime < $booking->endtime) {
                $expsyst2pract = $DB->get_record('ejsapp_expsyst2pract', array('ejsappid' => $ejsapp->id, 'practiceid' => $booking->practiceid));
                $practice = $expsyst2pract->practiceintro;
                $sarlabinfo = define_sarlab($remlab_conf->sarlabinstance, 0, $practice, $labmanager, $max_use_time);
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

    $time_first_access = 0; //TODO: Change to INF when we stop reseting time when a user connected to a remote lab refreshes the page
    $time_last_access = 0;
    $occupied_ejsapp_max_use_time = 3600;
    foreach($repeated_ejsapp_labs as $repeated_ejsapp_lab) {
        if (isset($repeated_ejsapp_lab->ejsappid)) {
            $repeated_ejsapp = $DB->get_record('ejsapp', array('id'=>$repeated_ejsapp_lab->ejsappid));
            if (isset($repeated_ejsapp->name)) {
                // Retrieve information from ejsapp's logging table
                $working_log_records = $DB->get_records('ejsapp_log', array('info'=>$repeated_ejsapp->name, 'action'=>'working'));
                $viewed_log_records = $DB->get_records('ejsapp_log', array('info'=>$repeated_ejsapp->name, 'action'=>'viewed'));
                $user_occupying_lab_id = $USER->id;
                foreach ($working_log_records as $working_log_record) {
                    if ($working_log_record->userid != $USER->id) {
                        if ($working_log_record->time > $time_last_access) {
                            $time_last_access = $working_log_record->time;
                            $user_occupying_lab_id = $working_log_record->userid;
                        }
                    }
                }
                foreach ($viewed_log_records as $viewed_log_record) {
                    if ($viewed_log_record->userid != $USER->id) {
                        if ($viewed_log_record->userid == $user_occupying_lab_id) { // accesses of the user that is currently working with the rem lab
                            $occupied_ejsapp_slotsduration_conf = $DB->get_field('ejsapp_remlab_conf', 'slotsduration', array('ejsappid' => $repeated_ejsapp->id));
                            $occupied_ejsapp_maxslots = $DB->get_field('ejsapp_remlab_conf', 'dailyslots', array('ejsappid' => $repeated_ejsapp->id));
                            $occupied_ejsapp_max_use_time = $occupied_ejsapp_maxslots * 60 * $slotsduration[$occupied_ejsapp_slotsduration_conf];
                            if ($viewed_log_record->time > $currenttime - $occupied_ejsapp_max_use_time) {
                                $time_first_access = max($time_first_access, $viewed_log_record->time); // TODO: Change to min when we stop reseting time when a user connected to a remote lab refreshes the page
                            }
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
 * Checks whether a particular remote lab is also present in other courses or not and gives the list of repeated labs.
 *
 * @param stdClass $remlab_conf
 * @param stdClass $ejsapp
 * @return array $repeated_ejsapp_labs
 *
 */
function get_repeated_remlabs($remlab_conf, $ejsapp) {
    global $DB;

    if ($remlab_conf->usingsarlab == 0) {
        $ejsapp_lab_ip = $DB->get_field('ejsapp_remlab_conf', 'ip', array('ejsappid'=>$ejsapp->id));
        $ejsapp_lab_port = $DB->get_field('ejsapp_remlab_conf', 'port', array('ejsappid'=>$ejsapp->id));
        $repeated_ejsapp_labs = $DB->get_records('ejsapp_remlab_conf', array('ip'=>$ejsapp_lab_ip, 'port'=>$ejsapp_lab_port));
    } else {
        $ejsapp_lab_conf = $DB->get_field('ejsapp_expsyst2pract', 'practiceintro', array('ejsappid'=>$ejsapp->id));
        $repeated_practices = $DB->get_records('ejsapp_expsyst2pract', array('practiceintro'=>$ejsapp_lab_conf));
        $ejsappids = array();
        foreach ($repeated_practices as $repeated_practice) {
            array_push($ejsappids, $repeated_practice->ejsappid);
        }
        $repeated_practices = $DB->get_records_list('ejsapp_remlab_conf', 'ejsappid', $ejsappids);
        //Previous queries may identify two different remote labs in two different SARLAB systems as only one, so we need to do something more:
        $sarlab_instance = $DB->get_field('ejsapp_remlab_conf', 'sarlabinstance', array('ejsappid'=>$ejsapp->id));
        $repeated_ejsapp_labs = array();
        foreach ($repeated_practices as $repeated_practice) { //check whether the remote lab is in the same SARLAB instance or not
            if ($repeated_practice->usingsarlab == 1 && $repeated_practice->sarlabinstance == $sarlab_instance){
                array_push($repeated_ejsapp_labs, $repeated_practice);
            }
        }
    }

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
    global $DB;

    $repeated_ejsapp_labs_with_bs = array();
    foreach ($repeated_ejsapp_labs as $repeated_ejsapp_lab) {
        $ejsappid = $DB->get_field('ejsapp_remlab_conf', 'ejsappid', array('id'=>$repeated_ejsapp_lab->id));
        $ejsapp = $DB->get_record('ejsapp', array('id'=>$ejsappid));
        if (check_booking_system($ejsapp)) array_push($repeated_ejsapp_labs_with_bs, $ejsapp);
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
 * Defines a new sarlab object with all the needed information
 *
 * @param int $instance sarlab instance
 * @param int $collab 0 if not a collab session, 1 if collaborative
 * @param string $practice the practice identifier in sarlab
 * @param int $labmanager whether the user is a laboratory manager or not
 * @param int $max_use_time maximum time for using the remote lab
 * @return stdClass $sarlabinfo
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