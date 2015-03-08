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
echo "key=$key\n";

if ($DB->record_exists('ejsapp_sarlab_keys', array('sarlabpass' => $key))) {
    $permissions = "labmanager=false\n";
    if($DB->get_field('ejsapp_sarlab_keys', 'labmanager', array('sarlabpass'=>$key)) == 1) $permissions = "labmanager=true\n";
    echo "access=true\n".$permissions;
} else echo "access=false\n";