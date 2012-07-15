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
 * This file is used to manage the .xml state files saved by the EJS applets and
 * that can be loaded by them. 
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once ('../../config.php');
require_once($CFG->libdir.'/filelib.php');
require_login();

function store_tmp_state_file($state_file_id){
	global $CFG;
	$fs = get_file_storage();
	$file = $fs->get_file_by_id($state_file_id);

	if ($file) {
		$tmp_state_files_path = $CFG->dirroot . '/mod/ejsapp/tmp_state_files/';
		if (!file_exists($tmp_state_files_path)) {
			mkdir($tmp_state_files_path, 0777);
		}
		$state_file_tmp_name =  $tmp_state_files_path . $state_file_id . '.xml';
		$tmp_file = fopen($state_file_tmp_name, 'w+');
		fwrite($tmp_file, $file->get_content());
		fclose($tmp_file);
	}

} //store_tmp_state_file

?>