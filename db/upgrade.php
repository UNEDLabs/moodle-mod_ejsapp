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
 * Upgrade file for the ejsapp module
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function for the ejsapp module
 *
 * @param string $oldversion
 */
function xmldb_ejsapp_upgrade($oldversion)
{
    global $DB;
    
    if ($oldversion <= '2012112900') {
      // Rename sarlab_keys database table to ejsapp_sarlab_keys
      $dbman = $DB->get_manager();
      $table = new xmldb_table('sarlab_keys');
      $dbman->rename_table($table, 'ejsapp_sarlab_keys');
    }
    
    if ($oldversion < '2012121300') {
      // Create "active" field in ejsapp_remlab_conf table
      $dbman = $DB->get_manager();
      $table = new xmldb_table('ejsapp_remlab_conf');
      $field = new xmldb_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'dailyslots');
      $dbman->add_field($table, $field);
    }
    
    return true;
}