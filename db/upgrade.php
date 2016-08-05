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
 * @return true
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

    if ($oldversion < '2015040406') {
        // Change max length for username in ejsapp_sarlab_keys table
        $dbman = $DB->get_manager();
        $table = new xmldb_table('ejsapp_sarlab_keys');
        $old_field = new xmldb_field('user', XMLDB_TYPE_CHAR, '20', true, true, false, null, 'id', 'sarlabpass');
        $new_field = new xmldb_field('user', XMLDB_TYPE_CHAR, '100', true, true, false, null, 'id', 'sarlabpass');
        $index = new xmldb_index('user', XMLDB_INDEX_NOTUNIQUE, array('user'));
        $dbman->drop_index($table, $index);
        $dbman->drop_field($table, $old_field);
        if (!$dbman->field_exists($table, $new_field)) {
            $dbman->add_field($table, $new_field);
        }
        $dbman->add_index($table, $index);
    }

    if  ($oldversion < '2015041903') {
        // Create "slotsduration" and "reboottime" fields in ejsapp_remlab_conf table
        $dbman = $DB->get_manager();
        $table = new xmldb_table('ejsapp_remlab_conf');
        $slotsduration = new xmldb_field('slotsduration', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '60', 'port', 'totalslots');
        $reboottime= new xmldb_field('reboottime', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '2', 'dailyslots', 'active');
        $dbman->add_field($table, $slotsduration);
        $dbman->add_field($table, $reboottime);
    }

    if  ($oldversion < '2015061901') {
        // Create "free_access" field in ejsapp_remlab_conf table
        $dbman = $DB->get_manager();
        $table = new xmldb_table('ejsapp_remlab_conf');
        $field = new xmldb_field('free_access', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'active');
        $dbman->add_field($table, $field);
        // Delete the "free_access" field from the ejsapp table
        $table = new xmldb_table('ejsapp');
        $field = new xmldb_field('free_access', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'personalvars');
        $dbman->drop_field($table, $field, $continue=true, $feedback=true);
    }

    if  ($oldversion < '2015062600') {
        $dbman = $DB->get_manager();
        $table_conf_old = new xmldb_table('ejsapp_remlab_conf');
        $table_sarlab_old = new xmldb_table('ejsapp_sarlab_keys');
        $table_expsyst_old = new xmldb_table('ejsapp_expsyst2pract');
        // Rename tables ejsapp_remlab_conf, ejsapp_sarlab_keys and ejsapp_expsyst2pract as block_remlab_manager_conf,
        // block_remlab_manager_sb_keys and block_remlab_manager_exp2prc
        $dbman->rename_table($table_conf_old, 'block_remlab_manager_conf', $continue=true, $feedback=true);
        $dbman->rename_table($table_sarlab_old, 'block_remlab_manager_sb_keys', $continue=true, $feedback=true);
        $dbman->rename_table($table_expsyst_old, 'block_remlab_manager_exp2prc', $continue=true, $feedback=true);
    }

    if ($oldversion < '2015062800') {
        $dbman = $DB->get_manager();
        $table_conf = new xmldb_table('block_remlab_manager_conf');
        //Getting ejsappid fields in ejsapp_remlab_conf:
        $ejsapp_ids = $DB->get_records('block_remlab_manager_conf', array(), '', 'ejsappid');
        // Delete index with dependency
        $index = new xmldb_index('ejsappid', XMLDB_INDEX_NOTUNIQUE, array('ejsappid'));
        if ($dbman->index_exists($table_conf, $index)) $dbman->drop_index($table_conf, $index, $continue=true, $feedback=true);
        // Replacing ejsappid field configuration in block_remlab_manager_conf by practiceintro field configurattion: in block_remlab_manager_exp2prc
        $field = new xmldb_field('ejsappid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
        $dbman->rename_field($table_conf, $field, 'practiceintro');
        $field = new xmldb_field('practiceintro', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'id');
        $dbman->change_field_type($table_conf, $field);
        // Replacing ejsappid fields content in block_remlab_manager_conf by practiceintro fields content in block_remlab_manager_exp2prc
        foreach ($ejsapp_ids as $ejsapp_id) {
            $practiceintros = $DB->get_records('block_remlab_manager_exp2prc', array('ejsappid' => $ejsapp_id->ejsappid));
            $counter = 1;
            foreach ($practiceintros as $practiceintro) {
                $record = $DB->get_record('block_remlab_manager_conf', array('practiceintro' => $ejsapp_id->ejsappid));
                $record->practiceintro = $practiceintro->practiceintro;
                if ($counter == 1) $DB->update_record('block_remlab_manager_conf', $record);
                else {
                    unset($record->id);
                    $DB->insert_record('block_remlab_manager_conf', $record);
                }
                $counter++;
            }
        }
    }

    if ($oldversion < '2015070401') {
        // Deleting records in block_remlab_manager_conf with repeated practiceintro
        $practiceintros = $DB->get_records('block_remlab_manager_conf', array(), '', 'practiceintro');
        foreach ($practiceintros as $practiceintro) {
            $repeated_practiceintros = $DB->get_records('block_remlab_manager_conf', array('practiceintro' => $practiceintro->practiceintro));
            if (count($repeated_practiceintros) > 1) {
                $counter = 1;
                foreach ($repeated_practiceintros as $repeated_practiceintro) {
                    if ($counter > 1) $DB->delete_records('block_remlab_manager_conf', array('id' => $repeated_practiceintro->id));
                    $counter++;
                }
            }
        }
    }

    return true;
}