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
 * Capability definitions for the ejsappbooking module
 *
 * The variable name for the capability definitions array is $capabilities
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_ejsapp_upgrade($oldversion) {

    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    
    /*if ($oldversion < 2012071507) {
      /// Define field course to be added to ejsapp
      $table = new xmldb_table('ejsapp');
      $field = new xmldb_field('is_rem_lab', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, false, null, 0, 'preserve_applet_size');
      
    	/// Conditionally launch add field is_rem_lab
   	  $dbman->add_field($table, $field, 0);  
    }
    
    if ($oldversion < 2012071510) {
      /// Set the table name to be added  (ejsapp_remlab_conf) and add its fields
      $table = new xmldb_table('ejsapp_remlab_conf');
      
      $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
      $table->add_field('ejsappid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);                 
      $table->add_field('ip', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
      $table->add_field('port', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
      $table->add_field('totalslots', XMLDB_TYPE_INTEGER, '5', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
      $table->add_field('weeklyslots', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null); 
      $table->add_field('dailyslots', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);  

      /// Set the table's key and index
      $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);
      $table->add_index('ejsappid', XMLDB_INDEX_NOTUNIQUE, array('ejsappid'));
    
    	/// Create the new table
    	$dbman->create_table($table);  
    }
    
    if ($oldversion < 2012072301) {
      $table = new xmldb_table('ejsapp');
      
      $field1 = new xmldb_field('appwording', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'introformat');
      $field2 = new xmldb_field('appwordingformat', XMLDB_TYPE_INT, '4', XMLDB_UNSIGNED, false, null, 0, 'appwording');
      
    	/// Conditionally launch add field
   	  $dbman->add_field($table, $field1, 0);  
   	  $dbman->add_field($table, $field2, 0);  
    }*/

    return true;
    
}