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
//  (UNED), Madrid, Spain


/**
 * This file is used to receive any .xml, .rec, .blk, .cnt, text or image file saved by
 * an EJS applet or an EjsS javascript application.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php'); // getting $CFG
require_login();

global $CFG;

require_once($CFG->libdir."/formslib.php");
require_once($CFG->libdir."/dml/moodle_database.php");
require_once($CFG->libdir."/blocklib.php");

// Distinguish between a file sent by EJS and EjsS
$original_file_name = null;
if ($_FILES['user_file']['name'] != null) { //receiving from EJS (java client)
    $method = true;
    $original_file_name = $_FILES['user_file']['name'];
    $file_name = replace_characters($original_file_name);
} else { //receiving from EjsS (javascript client)
    $method = false;
    $original_file_name = $_POST['user_file'];
    $file_name = replace_characters($original_file_name);
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    if (!$extension) {
        if ($_POST['type'] == 'json') $extension = '.json';
        if ($_POST['type'] == 'txt') $extension = '.txt';
        if ($_POST['type'] == 'png') $extension = '.png';
        $file_name = $file_name . $extension;
    }
}

$context_id = $_POST['context_id'];
$user_id = $_POST['user_id'];
$ejsapp_id = $_POST['ejsapp_id'];

// <upload the file to a temporal folder>
if ($method) { // from EJS
    $upload_dir = $CFG->dataroot . "/tmp/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0700);
    }

    $path = $upload_dir . $file_name;

    if ($original_file_name != null) { // as long as a file was selected...
        if (copy($_FILES['user_file']['tmp_name'], $path)) {
            // if the file has been successfully copied do nothing
        } else {
            // print and error message
            echo "File $original_file_name could not be uploaded";
        }
    }
}
// </upload the file to a temporal folder>

// <store the file in the user repository>
// <prepare the file info data>
$fs = get_file_storage();
// Prepare file record object
if ($extension == 'cnt') $source_info = 'controller';
else $source_info = 'ejsappid='.$ejsapp_id;
$fileinfo = array(
    'contextid' => $context_id, // ID of context
    'component' => 'user', // usually = table name
    'filearea' => 'private', // usually = table name
    'itemid' => 0, // usually = ID of row in table
    'source' => $source_info,
    'userid' => $user_id,
    'filepath' => '/',
    'filename' => $file_name);
// </prepare the file info data>

// <if there is an old file in the user repository with the same name, then delete it>
$old_file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
if ($old_file) $old_file->delete();
// </if there is an old file in the user repository with the same name, then delete it>

if ($method) { // from EJS
    $fs->create_file_from_pathname($fileinfo, $_FILES['user_file']['tmp_name']);
    // remove the temporal file from the temporal folder
    unlink("$path");
} else { // from EjsS
    if ($_POST['type'] != 'png') $fs->create_file_from_string($fileinfo, rawurldecode($_POST['file']));
    else {
        $data = rawurldecode($_POST['file']);
        list($type,$data) = explode(';', $data);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);
        $fs->create_file_from_string($fileinfo, $data);
    }
}
// </store the file in the user repository>

// To avoid problems with the file names
function replace_characters($original_file_name) {
    $aux_array = explode('/', $original_file_name);
    $safe_file = $aux_array[count($aux_array) - 1];
    $safe_file = str_replace(" ", "_", $safe_file);
    $safe_file = str_replace("#", "", $safe_file);
    $safe_file = str_replace("$", "Dollar", $safe_file);
    $safe_file = str_replace("%", "Percent", $safe_file);
    $safe_file = str_replace("^", "", $safe_file);
    $safe_file = str_replace("&", "", $safe_file);
    $safe_file = str_replace("*", "", $safe_file);
    $safe_file = str_replace("?", "", $safe_file);

    return $safe_file;
}