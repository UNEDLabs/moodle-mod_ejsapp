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
 * This page is used by Moodle when listing all the instances of your module that are in a
 * particular course with the course id being passed to this script
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

global $DB, $PAGE, $OUTPUT;

$id = required_param('id', PARAM_INT); // course

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('Course ID is incorrect');
}

require_course_login($course);

if ($CFG->version < 2013111899) { //Moodle 2.6 or inferior
    add_to_log($course->id, 'ejsapp', 'view all', "index.php?id=$course->id", '');
} else {
    $params = array(
        'context' => context_course::instance($course->id)
    );
    $event = \mod_ejsapp\event\course_module_instance_list_viewed::create($params);
    $event->add_record_snapshot('course', $course);
    $event->trigger();
}

/// Print the header

$PAGE->set_url('/mod/ejsapp/view.php', array('id' => $id));
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

/// Get all the appropriate data

if (!$ejsapps = get_all_instances_in_course('ejsapp', $course)) {
    echo $OUTPUT->heading(get_string('noejsapps', 'ejsapp'), 2);
    echo $OUTPUT->continue_button("view.php?id=$course->id");
    echo $OUTPUT->footer();
    die();
}

/// Print the list of instances

$timenow = time();
$strname = get_string('name');
$strweek = get_string('week');
$strtopic = get_string('topic');
$table = new html_table();
$table->attributes['class'] = 'generaltable mod_ejsapp';

if ($course->format == 'weeks') {
    $table->head = array($strweek, $strname);
    $table->align = array('center', 'left');
} else if ($course->format == 'topics') {
    $table->head = array($strtopic, $strname);
    $table->align = array('center', 'left', 'left', 'left');
} else {
    $table->head = array($strname);
    $table->align = array('left', 'left', 'left');
}

foreach ($ejsapps as $ejsapp) {
    if (!$ejsapp->visible) {
        //Show dimmed if the mod is hidden
        $link = '<a class="dimmed" href="view.php?id=' . $ejsapp->coursemodule . '">' . format_string($ejsapp->name) . '</a>';
    } else {
        //Show normal if the mod is visible
        $link = '<a href="view.php?id=' . $ejsapp->coursemodule . '">' . format_string($ejsapp->name) . '</a>';
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array($ejsapp->section, $link);
    } else {
        $table->data[] = array($link);
    }
}

echo $OUTPUT->heading(get_string('modulenameplural', 'ejsapp'), 2);
echo html_writer::table($table);

/// Finish the page

echo $OUTPUT->footer();