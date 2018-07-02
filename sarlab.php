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
 * Authentication and privileges verification between Moodle and Sarlab for remote lab applications
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
//require_login();

global $DB;

$key = required_param('key', PARAM_TEXT);
$version = optional_param('version', 0, PARAM_FLOAT);

if ($version < 8) {
    $obj = "key=$key\n";
} else {
    $obj = new stdClass;
    $obj->key = $key;
}

$time = array(strtotime(date('Y-m-d H:i:s')) + 120); // At least two minutes margin for working with the lab.

if ($record = $DB->get_records_select('block_remlab_manager_sb_keys',
    'sarlabpass = ? AND expirationtime > ?', array($key, $time))) {
    // Delete the key so it can't be used later again.
    $DB->delete_records('block_remlab_manager_sb_keys', array('sarlabpass' => $key));
    // Check permissions, expiration time, and grant access.
    $permissions = "false";
    if (reset($record)->labmanager == 1) {
        $permissions = "true";
    }
    $expirationtime = floor((reset($record)->expirationtime - time()) / 60); // This reduces available lab time in up to 59 seconds.
    if ($version < 8) {
        $obj .= "access=true\n" . "labmanager=$permissions\n" . "expiration_time=$expirationtime\n";
    } else {
        $obj->access = true;
        $obj->lab_manager = ($permissions === "true");
        $obj->expiration_time = $expirationtime;
    }
} else {
    if ($version < 8) {
        $obj .= "access=false\n";
    } else {
        $obj->access = false;
    }
}

if ($version < 8) {
    echo $obj;
} else {
    echo json_encode($obj);
}