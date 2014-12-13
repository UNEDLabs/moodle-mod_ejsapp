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
 * Ajax update of the log table for ejsapp
 *  
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2013 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_login(0, false);

global $PAGE;

$course_id = required_param('courseid', PARAM_INT);
$cm_id = required_param('activityid', PARAM_INT);
$ejsapp_name = required_param('ejsappname', PARAM_TEXT);
$method = required_param('method', PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/ejsapp/add_to_log.php');

if ($method == 0) { //Moodle 2.6 or inferior
    add_to_log($course_id, 'ejsapp', 'working', "view.php?id=$cm_id", $ejsapp_name, $cm_id);
} else {
    $user_id = required_param('userid', PARAM_INT);
    $modulecontext = context_module::instance($cm_id);
    //$ejsapp = $DB->get_record('ejsapp', array('id' => $cm->instance), '*', MUST_EXIST);
    $event = \mod_ejsapp\event\course_module_working::create(array(
        'objectid' => $cm_id,
        'context' => $modulecontext,
        'other' => $ejsapp_name,
    ));
    /*$event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('ejsapp', $ejsapp);*/
    $record = new stdClass();
    $record->id = $cm_id;
    $record->time = strtotime(date('Y-m-d H:i:s'));
    $record->userid = $user_id;
    $record->action = 'working';
    $record->info = $ejsapp_name;
    $event->add_record_snapshot('record', $record);
    $event->trigger();
}