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
 * Receives any .rec, .blk, .json, text or image file saved by an EjsS javascript lab
 * application and decides what to do with it:
 *  1) store it in the users' private files
 *  2) store it in the database (for users interactions only).
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_login();

require_once($CFG->libdir."/formslib.php");
require_once($CFG->libdir."/dml/moodle_database.php");
require_once($CFG->libdir."/blocklib.php");

if ($_POST['type'] == 'actions') { // Users interactions: store in the database for future possible analysis
    global $DB;
    $data = new stdClass();
    $data->time = time();
    $data->userid = $_POST['user_id'];
    $data->ejsappid = $_POST['ejsapp_id'];
    $data->sessionid = 0;
    $data->actions = $_POST['file'];
    $DB->insert_record('ejsapp_records', $data);
} else { // Store the file in the user private repository.
    $originalname = $_POST['user_file'];
    $filename = replace_characters($originalname);
    $ejssextension = $_POST['type'];
    $userextension = pathinfo($filename, PATHINFO_EXTENSION);
    if ($ejssextension != null && $ejssextension != '') {
        if ($userextension) {
            $filename = substr($filename, 0, strrpos( $filename, '.')) . $ejssextension;
        } else if (substr($ejssextension, 0, 1) == '.') {
            $filename .= $ejssextension;
        } else {
            $filename .= '.' . $ejssextension;
        }
    } else {
        if (!$userextension) $filename = $filename . '.txt';
    }

    $contextid = $_POST['context_id'];
    $userid = $_POST['user_id'];
    $ejsappid = $_POST['ejsapp_id'];

    // Prepare the file info data.
    $fs = get_file_storage();
    // Prepare file record object.
    $sourceinfo = 'ejsappid=' . $ejsappid;
    $fileinfo = array(
        'contextid' => $contextid, // ID of context.
        'component' => 'user', // Usually = table name.
        'filearea' => 'private', // Usually = table name.
        'itemid' => 0, // Usually = ID of row in table.
        'source' => $sourceinfo,
        'userid' => $userid,
        'filepath' => '/',
        'filename' => $filename);

    // If there is an old file in the user repository with the same name, then delete it.
    $oldfile = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'],
        $fileinfo['filepath'], $fileinfo['filename']);
    if ($oldfile) {
        $oldfile->delete();
    }

    if ($_POST['type'] != 'png') {
        $fs->create_file_from_string($fileinfo, rawurldecode($_POST['file']));
    } else {
        $data = rawurldecode($_POST['file']);
        list($type, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);
        $fs->create_file_from_string($fileinfo, $data);
    }
}

/**
 * Avoid problems with the file names.
 *
 * @param String $originalname
 * @return array $safefile
 */
function replace_characters($originalname) {
    $auxarray = explode('/', $originalname);
    $safefile = $auxarray[count($auxarray) - 1];
    $safefile = str_replace(" ", "_", $safefile);
    $safefile = str_replace("#", "", $safefile);
    $safefile = str_replace("$", "Dollar", $safefile);
    $safefile = str_replace("%", "Percent", $safefile);
    $safefile = str_replace("^", "", $safefile);
    $safefile = str_replace("&", "", $safefile);
    $safefile = str_replace("*", "", $safefile);
    $safefile = str_replace("?", "", $safefile);

    return $safefile;
}