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
 * Sends to an EjsS application the list of state files, *.exp files or text plain files saved by that application.
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Some security issues when receiving data from users.
require_once('../../config.php');
require_login();

global $DB, $USER, $CFG;

require_once($CFG->libdir . '/moodlelib.php');

$ejsappid = required_param('ejsapp_id', PARAM_INT);
$type = optional_param('type', '.xml', PARAM_TEXT);

$filename = array();
$filepath = array();
$filenames = array();
$filepaths = array();
if ($type == '.cnt') {
    $sourceinfo = 'controller';
} else {
    $sourceinfo = 'ejsappid='.$ejsappid;
}
$records = $DB->get_records_select('files', "component='user' AND filearea='private' AND " .
    "userid='$USER->id' AND source='$sourceinfo'");

foreach ($records as $record) {
    $extension = pathinfo($record->filename, PATHINFO_EXTENSION);
    if ( ($type == '.xml' && $extension == 'xml') || ($type == 'text' && $extension == 'txt') ||
        ($type == '.cnt' && $extension == 'cnt') || ($type == '.rec' && $extension == 'rec') ||
        ($type == '.blk' && $extension == 'blk') || ($type == '.json' && $extension == 'json')) {
        $ejsappfilepath = $CFG->wwwroot . '/pluginfile.php/' . $record->contextid . '/mod_ejsapp/private/' .
            $record->itemid . '/';
        $filename["file_name"] = $record->filename;
        $filenames[] = $filename;
        $filepath["file_path"] = $ejsappfilepath . $record->filename;
        $filepaths[] = $filepath;
    }
}

$obj = array('file_names' => $filenames, 'file_paths' => $filepaths);
echo json_encode($obj);