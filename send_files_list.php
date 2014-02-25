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
 * This file is used to send to an EJS applet the list of .xml state files,
 * .exp file or text plain files saved by that applet.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//Some security issues when receiving data from users:
require_once('../../config.php');
require_login();

global $DB, $USER, $CFG;

require_once($CFG->libdir . '/moodlelib.php');

$ejsapp_id = required_param('ejsapp_id', PARAM_INT);
$type = optional_param('type', '.xml', PARAM_TEXT);

$info = '';
$records = $DB->get_records('files',array('component'=>'user', 'filearea'=>'private', 'userid'=>$USER->id, 'source'=>'ejsappid='.$ejsapp_id));

foreach ($records as $record) {
    $file_extension = pathinfo($record->filename, PATHINFO_EXTENSION);
    if ( ($type == '.xml' && $file_extension == 'xml') || ($type == 'text' && $file_extension == 'txt') || ($type == '.exp' && $file_extension == 'exp') ) {
        $ejsapp_file_path = $CFG->wwwroot . '/pluginfile.php/' . $record->contextid . '/mod_ejsapp/private/' . $record->itemid . '/';
        $info .= $record->filename . ';' . $ejsapp_file_path . $record->filename . ';';
    }
}

echo $info;