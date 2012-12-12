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

// Get the ejsapp instances in the course with id 30
$courseid = 30;
$ejsapp_instances = get_ejsapp_instances($courseid);
var_dump($ejsapp_instances);

// Get all ejsapp instances for the whole moodle site
$ejsapp_instances = get_ejsapp_instances();
var_dump($ejsapp_instances);

// Get all state files for ejsapp with id=85
$ejsapp_id = 85;
$state_files = get_ejsapp_states(85);
var_dump($state_files);


// Draw ejsapp with id=85 following its form configuration
$code_1 = draw_ejsapp_instance(85);
echo $code_1;

echo '<br/>'; // write an end of line

// Draw ejsapp with id=85 with state $state_files[0]->state_id, width=500 and height=300
$code_2 = draw_ejsapp_instance(85, $state_files[0]->state_id, 500, 300);
echo $code_2;


//echo "\n";
//echo draw_ejsapp_instance(85,'77/mod_ejsapp/private/0/Gyroscope_ruben.xml', 100, 200);