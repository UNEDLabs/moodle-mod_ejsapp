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
    
    if ($oldversion < '2013031800') {
        // Create "sarlabcollab" field in ejsapp_remlab_conf table
        $dbman = $DB->get_manager();
        $table = new xmldb_table('ejsapp_remlab_conf');
        $field = new xmldb_field('sarlabcollab', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'sarlabinstance', 'ip');
        $dbman->add_field($table, $field);
    }

    if  ($oldversion < '2013060101') {
        // Create "personalvars" field in ejsapp table
        $dbman = $DB->get_manager();
        $table = new xmldb_table('ejsapp');
        $field = new xmldb_field('personalvars', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'width');
        $dbman->add_field($table, $field);

        // Create "ejsapp_personal_vars" table
        $table = new xmldb_table('ejsapp_personal_vars');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, true, null);
        $table->add_field('ejsappid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_TEXT, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('type', XMLDB_TYPE_TEXT, '8', null, XMLDB_NOTNULL, null, null);
        $min_value = new xmldb_field('minval', XMLDB_TYPE_FLOAT, '10', null, null, null, null);
        $min_value->setDecimals('6');
        $table->addField($min_value);
        $max_value = new xmldb_field('maxval', XMLDB_TYPE_FLOAT, '10', null, null, null, null);
        $max_value->setDecimals('6');
        $table->addField($max_value);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('ejsappid', XMLDB_INDEX_NOTUNIQUE, array('ejsappid'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if  ($oldversion < '2013071600') {
        // Create "freeaccess" field in ejsapp table
        $dbman = $DB->get_manager();
        $table = new xmldb_table('ejsapp');
        $field = new xmldb_field('free_access', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'personalvars');
        $dbman->add_field($table, $field);
    }

    if  ($oldversion < '2013110301') {
        // Modify "ejsapp_personal_vars" table
        $dbman = $DB->get_manager();
        $table = new xmldb_table('ejsapp_personal_vars');
        $min_value = new xmldb_field('minval', XMLDB_TYPE_FLOAT, '10', null, null, null, null, 'type');
        $min_value->setDecimals('6');
        $max_value = new xmldb_field('maxval', XMLDB_TYPE_FLOAT, '10', null, null, null, null, 'minval');
        $max_value->setDecimals('6');
        $dbman->change_field_type($table, $min_value);
        $dbman->change_field_type($table, $max_value);
        $table->deleteField('maxval');
        $table->deleteField('minval');
    }

    if  ($oldversion < '2014072107') {
        // Create "ejsapp_log" table
        $dbman = $DB->get_manager();
        $table = new xmldb_table('ejsapp_log');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, true, null);
        $table->add_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('action', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->add_field('info', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if  ($oldversion < '2014083100') {
        // Create "labmanager" field in ejsapp_sarlab_keys table
        $dbman = $DB->get_manager();
        $table = new xmldb_table('ejsapp_sarlab_keys');
        $field = new xmldb_field('labmanager', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'sarlabpass', 'creationtime');
        $dbman->add_field($table, $field);
    }

    if  ($oldversion < '2014090205') {
        // Create "css" field in ejsapp table
        $dbman = $DB->get_manager();
        $table = new xmldb_table('ejsapp');
        $field = new xmldb_field('css', XMLDB_TYPE_TEXT, '1024', null, null, null, null, 'appwordingformat', 'timecreated');
        $dbman->add_field($table, $field);
    }

    return true;
}