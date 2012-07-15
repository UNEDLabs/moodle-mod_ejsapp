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
//  (UNED), Madrid, Spain/


/**
 * Internal library of functions for module ejsapp
 *
 * All the ejsappbooking specific functions, needed to implement the module
 * logic, are here.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


function delete_recursively($dir){
	if(is_file($dir)){
		return @unlink($dir);
	}
	elseif(is_dir($dir)){
		$scan = glob(rtrim($dir,'/').'/*');
		foreach($scan as $index=>$path){
			delete_recursively($path);
		}
		return @rmdir($dir);
	}
}


function keep_me_clean(){
	global $CFG, $DB;
	$path = $CFG->dirroot . '/mod/ejsapp/jarfile/';
	$course_dirs = scandir($path);
	$table = $CFG->prefix . 'ejsapp';
	foreach ($course_dirs as $course_dir) {
		if ($course_dir != '.' and $course_dir != '..') {
			$sql = "select * from $table where course = '$course_dir'";
			$records = $DB->get_records_sql($sql);
			if (count($records) == 0) {
				delete_recursively($path . $course_dir);
			}
			else {
				$ejsapp_dirs = scandir($path . $course_dir);
				foreach ($ejsapp_dirs as $ejsapp_dir){
					if ($ejsapp_dir != '.' and $ejsapp_dir != '..') {
						$sql = "select  *
							from $table
							where course = '$course_dir' and id = '$ejsapp_dir'
						";
						$records = $DB->get_records_sql($sql);
						if (count($records) == 0) {
							delete_recursively($path . $course_dir . '/' . $ejsapp_dir);
						}
					} //if
				} //foreach
			}//else
		} //if
	} //foreach
} //keep_me_clean


function delete_non_alphanumeric_symbols($str){
	return preg_replace('/[^a-zA-Z0-9]/', '', $str);
}