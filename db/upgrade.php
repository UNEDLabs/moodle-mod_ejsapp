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

defined('MOODLE_INTERNAL') || die();

function xmldb_ejsapp_upgrade($oldversion) {

    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2012060602) {
    	/// Define field course to be added to ejsapp
    	$table = new xmldb_table('ejsapp');

    	$field = new xmldb_field('preserve_applet_size', XMLDB_TYPE_TEXT);
    	/// Conditionally launch add field preserve_applet_size
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}

    	$field = new xmldb_field('height', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'preserve_applet_size');
    	// Conditionally launch add field height
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}

    	$field = new xmldb_field('width', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'height');

    	// Conditionally launch add field width
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    }

    return true;
    
}