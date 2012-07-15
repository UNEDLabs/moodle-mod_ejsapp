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
    
    if ($oldversion < 2012071507) {
    /// Define field course to be added to ejsapp
    $table = new xmldb_table('ejsapp');
    
    $field = new xmldb_field('is_rem_lab', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, true, 0, null, 'preserve_applet_size');
    	/// Conditionally launch add field is_rem_lab
    	if (!$dbman->field_exists($table, $field)) {
    	  $dbman->add_field($table, $field, 0);  
    	}
    }

    return true;
    
}