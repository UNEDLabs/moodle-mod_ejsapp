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
 * Prints a particular instance of ejsapp
 *
 * @package    mod
 * @subpackage ejsappbooking
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_login();
require_once(dirname(__FILE__).'/lib.php');
require_once('generate_applet_embedding_code.php');
require_once('locallib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ejsapp instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('ejsapp', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $ejsapp  = $DB->get_record('ejsapp', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $ejsapp  = $DB->get_record('ejsapp', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $ejsapp->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('ejsapp', $ejsapp->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'ejsapp', 'view', "view.php?id=$cm->id", $ejsapp->name, $cm->id);

// Print the page header
$PAGE->set_url('/mod/ejsapp/view.php', array('id' => $cm->id));
$PAGE->set_title($ejsapp->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_context($context);
$PAGE->set_button(update_module_button($cm->id, $course->id, get_string('modulename', 'ejsapp')));

// Output starts here
echo $OUTPUT->header();

echo $OUTPUT->heading(generate_applet_embedding_code($ejsapp, null, null, null));

// Finish the page
echo $OUTPUT->footer();