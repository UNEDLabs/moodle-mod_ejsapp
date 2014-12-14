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
 * Updates the EJSApp tables according to the .jar information
 *
 * @param stdClass $ejsapp record from table ejsapp
 * @param int $contextid
 *
 * @return boolean ejs_ok
 */
function update_db($ejsapp, $contextid) {
    global $CFG, $DB;

    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . delete_non_alphanumeric_symbols($ejsapp->name) . '/';
    $new_path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';

    if (file_exists($new_path)) { // the ejsapp activity has been renamed or updated
        delete_recursively($new_path);
    }

    rename($path, $new_path);

    $applet_name = $ejsapp->applet_name;
    $manifest = $ejsapp->manifest;
    $metadata = $ejsapp->metadata;

    $class_file = '';
    $mainframe = '';
    $is_collaborative = 0;
    $height = 0;
    $width = 0;

    // Get params

    // codebase
    $codebase = '';
    if ($manifest != 'EJsS') {
        preg_match('/http:\/\/.+?\/(.+)/', $CFG->wwwroot, $match_result);
        if (!empty($match_result) and $match_result[1]) {
            $codebase .= '/' . $match_result[1];
        }
    }
    $codebase .= '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';

    if ($manifest != 'EJsS') { //Java Applet
        $ext = '.jar';
        $uploaded_file = $new_path . $applet_name . $ext;

        // class_file
        $pattern = '/Main-Class\s*:\s*(.+)\s*/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        $sub_str = $matches[1][0];
        if (strlen($matches[1][0]) == 59) {
            $pattern = '/^\s(.+)\s*/m';
            if (preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE) > 0) {
                if (preg_match('/\s*:\s*/', $matches[1][0], $matches2, PREG_OFFSET_CAPTURE) == 0){
                    $sub_str = $sub_str . $matches[1][0];
                }
            }
        }
        $class_file = $sub_str . 'Applet.class';
        $class_file = preg_replace('/\s+/', "", $class_file); // delete all white-spaces and the first newline

        // mainframe
        $pattern = '/Main-Frame\s*:\s*(.+)\s*/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) == 0) {
            $mainframe = '';
        } else {
            $mainframe = $matches[1][0];
            $mainframe = preg_replace('/\s+/', "", $mainframe); // delete all white-spaces
        }

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

        // height
        $pattern = '/Applet-Height\s*:\s*(\w+)/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        $ejs_ok = false;
        if (count($matches) == 0) {
            $height = 0;
            // If this field does not exist in the manifest, it means the version of
            // EJS used to compile the jar does not support Moodle.
            $message = get_string('EJS_version', 'ejsapp');
            $code = "<script type=\"text/javascript\">
            window.alert(\"$message\")
            </script>";
            echo $code;
        } else {
            $ejs_ok = true;
            $height = $matches[1][0];
            $height = preg_replace('/\s+/', "", $height); // delete all white-spaces
        }

        // width
        $pattern = '/Applet-Width\s*:\s*(\w+)/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) == 0) {
            $width = 0;
        } else {
            $width = $matches[1][0];
            $width = preg_replace('/\s+/', "", $width); // delete all white-spaces
        }

        //Sign the applet
        // Check whether a certificate is installed and in use
        if (file_exists(get_config('ejsapp', 'certificate_path')) && get_config('ejsapp', 'certificate_password') != '' && get_config('ejsapp', 'certificate_alias') != '') {
            // Check whether the applet has the codebase parameter in manifest.mf set to $CFG->wwwroot
            $pattern = '/\s*\nCodebase\s*:\s*(.+)\s*/';
            preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
            if (substr($matches[1][0], 0, -1) == substr($CFG->wwwroot, 7)) {
                // Sign the applet
                shell_exec('sh ' . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sign.sh ' .
                    $uploaded_file . ' ' .                                  // parameter 1
                    get_config('ejsapp', 'certificate_path') . ' ' .        // parameter 2
                    get_config('ejsapp', 'certificate_password') . ' ' .    // parameter 3
                    get_config('ejsapp', 'certificate_alias')               // parameter 4
                );
            }
        }
    } else { //Javascript
        $ext = '.zip';
        $uploaded_file = $new_path . $applet_name . $ext;

        $zip = new ZipArchive;
        if ($zip->open($new_path . $applet_name . '.zip') === TRUE) {
            $zip->extractTo($new_path);
            $zip->close();
            $ejs_ok = true;
        } else {
            $ejs_ok = false;
        }

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
            $file = fopen($css_file_location,"w");
            fwrite($file,$css_file_content);
            fclose($file);
        }
    }

    // <update files table>
    $fs = get_file_storage();
    // Prepare file record object
    $fileinfo = array(
        'contextid' => $contextid,          // ID of context
        'component' => 'mod_ejsapp',        // usually = table name
        'filearea' => 'jarfiles',           // usually = table name
        'itemid' => $ejsapp->id,            // usually = ID of row in table
        'filepath' => '/',                  // any path beginning and ending in /
        'filename' => $applet_name . $ext); // any filename
    // Create the stored file
    $fs->create_file_from_pathname($fileinfo, $uploaded_file);

    if($manifest == 'EJsS') {  //TODO: Watch out with backups in this case!!!!!!
        if (file_exists($new_path . $ejsapp->applet_name)) {
            $code = file_get_contents($new_path . $ejsapp->applet_name);
            //<get the whole code from </title> (not included) onwards>
            $code = explode('</title>',$code);
            $code = '<div id="EJsS">' . $code[1];
            //</get the whole code from </title> (not included) onwards>
            //<$code1 is $code till </head> (not included) and with the missing standard part>
            $code1 = substr($code, 0, -strlen($code)+strpos($code, '</head>')) . '<div id="_topFrame" style="text-align:center"></div>';
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
                $codeJS = file_get_contents($new_path . $exploded_file_name[0] .'.js');
                $codeJS = update_links($codebase, $ejsapp, $codeJS, 'new', false);
                file_put_contents($new_path . $exploded_file_name[0] .'.js', $codeJS);
            }
            file_put_contents($new_path . $ejsapp->applet_name, $code);
            //TODO: Use Moodle files system
            /*$fileinfo['filename'] = $ejsapp->applet_name;
            $fs = get_file_storage();
            $fs->create_file_from_pathname($fileinfo, $new_path . $ejsapp->applet_name);*/
            unlink($new_path . $applet_name . '.zip');
        }
    }
    // </update files table>

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

    $ejsapp->class_file = $class_file;
    $ejsapp->codebase = $codebase;
    $ejsapp->mainframe = $mainframe;
    $ejsapp->is_collaborative = $is_collaborative;
    $ejsapp->height = $height;
    $ejsapp->width = $width;
    $DB->update_record('ejsapp', $ejsapp);

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
 * Removes non alphanumeric_symbols from a string
 *
 * @param string $str
 * @return string
 *
 */
function delete_non_alphanumeric_symbols($str) {
    return preg_replace('/[^a-zA-Z0-9]/', '', $str);
}


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
            $uniqueval = filter_var(md5($user->firstname . $i . $user->username . $user->lastname . $user->id . $personalvar->id . $personalvar->name . $personalvar->type . $user->email . $personalvar->minval . $personalvar->maxval), FILTER_SANITIZE_NUMBER_INT);
            mt_srand($uniqueval/(pow(10,strlen($user->username))));
            $personalvarsinfo->name[$i] = $personalvar->name;
            $factor = 1;
            if ($personalvar->type == 'Double')  $factor = 1000;
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