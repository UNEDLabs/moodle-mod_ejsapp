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
 * All the ejsappbooking specific functions, needed to implement the module
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
 */
function update_db($ejsapp, $contextid)
{
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

    $ext = '.zip';
    $class_file = '';
    $mainframe = '';
    $is_collaborative = 0;
    $height = 0;
    $width = 0;

    // Get params

    // codebase
    $codebase = '';
    preg_match('/http:\/\/.+?\/(.+)/', $CFG->wwwroot, $match_result);
    if (!empty($match_result) and $match_result[1]) {
        $codebase .= '/' . $match_result[1];
    }
    $codebase .= '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';

    if ($manifest != 'EJsS') { //Java Applet
        $ext = '.jar';

        // class_file
        $pattern = '/Main-Class\s*:\s*(.+)\s*/';
        preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE);
        $sub_str = $matches[1][0];
        if (strlen($matches[1][0]) == 59) { //TODO
            $pattern = '/^\s(.+)\s*/m';     //PROBLEM WITH THOSE THAT ARE EXACTLY 59 AND NOT MORE!!
            if ((preg_match($pattern, $manifest, $matches, PREG_OFFSET_CAPTURE) > 0)) {
                $sub_str = $sub_str . $matches[1][0];
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
    } else { //Javascript
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
    }

    // <update files table>
    $fs = get_file_storage();
    // Prepare file record object
    $fileinfo = array(
        'contextid' => $contextid, // ID of context
        'component' => 'mod_ejsapp', // usually = table name
        'filearea' => 'jarfiles', // usually = table name
        'itemid' => $ejsapp->id, // usually = ID of row in table
        'filepath' => '/',
        'filename' => $applet_name . $ext); // any filename
    // Create the stored file
    $uploaded_file = $new_path . $applet_name . $ext;
    $fs->create_file_from_pathname($fileinfo, $uploaded_file);

    if($manifest == 'EJsS') {
        if (file_exists($new_path . $ejsapp->applet_name)) $code = file_get_contents($new_path . $ejsapp->applet_name);
        $path = $CFG->wwwroot . $codebase;
        $search = "window.addEventListener('load', function () {  new " . substr($ejsapp->applet_name,0,-16) . '("_topFrame","_ejs_library/",null);';
        $replace = "window.addEventListener('load', function () {  new " . substr($ejsapp->applet_name,0,-16) . '("_topFrame","' . $path . '/_ejs_library/","' . $path . '");';
        $code = str_replace($search,$replace,$code);
        file_put_contents($new_path . $ejsapp->applet_name, $code);
        /*$fileinfo['filename'] = $ejsapp->applet_name;
        $fs = get_file_storage();
        $fs->create_file_from_pathname($fileinfo, $new_path . $ejsapp->applet_name);*/
        unlink($new_path . $applet_name . '.zip');
    }
    // </update files table>

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
                $personal_vars->minval = $min_value;
                $personal_vars->maxval = $max_value;
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
 * @param file $dir directory to delete
 * @return bool TRUE on success or FALSE on failure
 */
function delete_recursively($dir)
{
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
}//delete_recursively

/**
 * Removes non alphanumeric_symbols from a string
 *
 * @param $str string
 * @return string
 *
 */
function delete_non_alphanumeric_symbols($str)
{
    return preg_replace('/[^a-zA-Z0-9]/', '', $str);
}