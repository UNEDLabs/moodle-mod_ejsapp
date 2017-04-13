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
 *
 * Check conditions in order to allow or restrict the access to the remote lab applet when using Sarlab
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ('../../config.php');

global $DB;

$key = required_param('key', PARAM_TEXT);
$obj = "key=$key\n";
//$obj->key = $key;

$time = array(strtotime(date('Y-m-d H:i:s')) + 180); //at least three minutes margin for working with the lab

if ($record = $DB->get_records_select('block_remlab_manager_sb_keys', 'sarlabpass = ? AND expirationtime > ?', array($key, $time))) {
    //Delete expired Sarlab keys:
    $DB->delete_records_select('block_remlab_manager_sb_keys', "expirationtime < ?", $time);
    //Check permissions, expiration time, and grant access:
    $permissions = "labmanager=false\n";
    if(reset($record)->labmanager == 1) $permissions = "labmanager=true\n";
    $expirationtime = "expiration_time=" . reset($record)->expirationtime . "\n";
    $obj .= "access=true\n".$permissions.$expirationtime;
    /*$obj->access = "true";
    $obj->lab_manager = $permissions;
    $obj->expiration_time = $expiration_time;*/
} else $obj .= "access=false\n"; //$obj->access = "false";

echo $obj;
//echo json_encode($obj);