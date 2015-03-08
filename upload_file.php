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
 * This file is used to receive any .xml, .exp, text or image file saved by an EJS
 * applet.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php'); // getting $CFG
require_login();

global $CFG;

require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/dml/moodle_database.php");
require_once("$CFG->libdir/blocklib.php");

// user_file has the following format:
// filename_context_id_879_ejsapp_id_87.extension

// To avoid problems with the file names
$safe_file = $_FILES['user_file']['name'];
$aux_array = explode('\\', $safe_file);
$aux_array = explode('/', $safe_file);
$safe_file = $aux_array[count($aux_array) - 1];
$safe_file = str_replace(" ", "_", $safe_file);
$safe_file = str_replace("#", "", $safe_file);
$safe_file = str_replace("$", "Dollar", $safe_file);
$safe_file = str_replace("%", "Percent", $safe_file);
$safe_file = str_replace("^", "", $safe_file);
$safe_file = str_replace("&", "", $safe_file);
$safe_file = str_replace("*", "", $safe_file);
$safe_file = str_replace("?", "", $safe_file);

// Get file_name, context_id,  ejsapp_id and file_extension
preg_match('/(.+)_context_id_/', $safe_file, $match);
$file_name = $match[1];
preg_match('/_context_id_(\d+)/', $safe_file, $match);
$context_id = $match[1];
preg_match('/_user_id_(\d+)/', $safe_file, $match);
$user_id = $match[1];
preg_match('/_ejsapp_id_(\d+)/', $safe_file, $match);
$ejsapp_id = $match[1];
$file_extension = pathinfo($safe_file, PATHINFO_EXTENSION);

// <upload the file to a temporal folder>
$upload_dir = $CFG->dataroot . "/tmp/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0700);
}

$path = $upload_dir . $file_name . '.' . $file_extension;

if ($_FILES['user_file'] != null) { // as long as a file was selected...

    if (copy($_FILES['user_file']['tmp_name'], $path)) {
        // if the file has been successfully copied do nothing
    } else {
        // print and error message
        $theFileName = $_FILES['user_file']['name'];
        echo "File $theFileName could not be uploaded";
    }

}
// </upload the file to a temporal folder>

// <store the file in the user repository>
// <prepare the file info data>
$fs = get_file_storage();
// Prepare file record object
if ($file_extension == 'cnt') $source_info = 'controller';
else $source_info = 'ejsappid='.$ejsapp_id;
$fileinfo = array(
    'contextid' => $context_id, // ID of context
    'component' => 'user', // usually = table name
    'filearea' => 'private', // usually = table name
    'itemid' => 0, // usually = ID of row in table
    'source' => $source_info,
    'userid' => $user_id,
    'filepath' => '/',
    'filename' => $file_name . '.' . $file_extension);
// </prepare the file info data>

// <if there is an old file in the user repository with the same name, then delete it>
$old_file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
if ($old_file) {
    $old_file->delete();
}
// </if there is an old file in the user repository with the same name, then delete it>

$fs->create_file_from_pathname($fileinfo, $_FILES['user_file']['tmp_name']);
// </store the file in the user repository>

// remove the temporal file from the temporal folder
unlink("$path");