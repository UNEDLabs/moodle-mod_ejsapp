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
 * Test of the EJSApp External Interface
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->dirroot.'/mod/ejsapp/external_interface/ejsapp_external_interface.php');

/**
 * Retrieving information from EJSApp
 */

// Get the ejsapp instances in the course with id 2
$courseid = 2;
$ejsapp_instances = get_ejsapp_instances($courseid);
var_dump($ejsapp_instances);

echo '<br/>'; // write an end of line
echo '<br/>'; // write an end of line

// Get all ejsapp instances for the whole moodle site
$ejsapp_instances = get_ejsapp_instances();
var_dump($ejsapp_instances);

echo '<br/>'; // write an end of line
echo '<br/>'; // write an end of line

// Get the size of ejsapp with id=1
$ejsapp_id = 1;
$size = get_ejsapp_size($ejsapp_id);
var_dump($size);

echo '<br/>'; // write an end of line
echo '<br/>'; // write an end of line

// Get all state files for ejsapp with id=1
$ejsapp_id = 1;
$state_files = get_ejsapp_states($ejsapp_id);
var_dump($state_files);

echo '<br/>'; // write an end of line
echo '<br/>'; // write an end of line

// Draw ejsapp with id=1 with state $state_files[0]->state_id, width=500 and height=300
$code_1 = draw_ejsapp_instance($ejsapp_id, $state_files[0]->state_id, 500, 300);
echo $code_1;

echo '<br/>'; // write an end of line
echo '<br/>'; // write an end of line

// Draw ejsapp with id=1 keeping its form configuration
$code_2 = draw_ejsapp_instance($ejsapp_id);
echo $code_2;