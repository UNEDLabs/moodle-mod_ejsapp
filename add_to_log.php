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
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
//
// EJSApp has been developed by:
// - Luis de la Torre: ldelatorre@dia.uned.es
// - Ruben Heradio: rheradio@issi.uned.es
//
// at the Computer Science and Automatic Control, Spanish Open University
// (UNED), Madrid, Spain.

/**
 * Ajax update of the log table for ejsapp
 *
 * @package    mod_ejsapp
 * @copyright  2013 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_login(0, false);

global $PAGE;

$courseid = required_param('courseid', PARAM_INT);
$cmid = required_param('activityid', PARAM_INT);
$ejsappname = required_param('ejsappname', PARAM_TEXT);
$userid = required_param('userid', PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/ejsapp/add_to_log.php');

$ejsappname = urldecode($ejsappname);
$modulecontext = context_module::instance($cmid);
$event = \mod_ejsapp\event\course_module_working::create(array(
    'objectid' => $cmid,
    'courseid' => $courseid,
    'userid' => $userid,
    'context' => $modulecontext,
    'other' => $ejsappname,
));
/*$ejsapp = $DB->get_record('ejsapp', array('id' => $cm->instance), '*', MUST_EXIST);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('ejsapp', $ejsapp);
$record = new stdClass();
$record->id = $cmid;
$record->time = time();
$record->userid = $userid;
$record->action = 'working';
$record->info = $ejsappname;
$event->add_record_snapshot('ejsapp_log', $record);*/
$event->trigger();