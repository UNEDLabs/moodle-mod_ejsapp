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
 * Prints a particular instance of EJSApp
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once('generate_applet_embedding_code.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n = optional_param('n', 0, PARAM_INT); // ejsapp instance ID - it should be named as the first character of the module
$state_file = optional_param('state_file', null, PARAM_TEXT);
$session_id = optional_param('colsession', null, PARAM_INT);
$session_director = optional_param('sessiondirector', null, PARAM_INT);
$session_ip = optional_param('colip', null, PARAM_TEXT);
$session_port = optional_param('colport', null, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('ejsapp', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $ejsapp = $DB->get_record('ejsapp', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $ejsapp = $DB->get_record('ejsapp', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $ejsapp->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('ejsapp', $ejsapp->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'ejsapp', 'view', "view.php?id=$cm->id", $ejsapp->name, $cm->id);

// Print the page header
$PAGE->set_url('/mod/ejsapp/view.php', array('id' => $cm->id));
$PAGE->set_title($ejsapp->name);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_button(update_module_button($cm->id, $course->id, get_string('modulename', 'ejsapp')));

// Output starts here
echo $OUTPUT->header();
if ($ejsapp->intro) { // If some text was written, show the intro
    echo $OUTPUT->box(format_module_intro('ejsapp', $ejsapp, $cm->id), 'generalbox mod_introbox', 'ejsappintro');
}

$practiceintro = null;
$sarlabinstance = null;
//Check the access conditions, depending on whether sarlab is beeing used or not, whether the ejsapp booking system is beeing used or not and whether the ejsapp instance is a remote lab or not.
if (($ejsapp->is_rem_lab == 0) || (!$DB->record_exists('ejsappbooking', array('course' => $ejsapp->course)))) { //Virtual lab or not using ejsappbooking
    $practiceintro = null;
    echo $OUTPUT->heading(generate_applet_embedding_code($ejsapp, $sarlabinstance, $practiceintro, $state_file, $session_id, $session_ip, $session_port, $session_director));
} else { //Remote lab and using ejsappbooking 
    $remlab_conf = $DB->get_record('ejsapp_remlab_conf', array('ejsappid' => $ejsapp->id));
    $usingsarlab = $remlab_conf->usingsarlab;
    if (has_capability('moodle/course:viewhiddensections', $context, $USER->id, true)) { //Admins and teachers
        if ($usingsarlab == 1) {
            $sarlabinstance = $remlab_conf->sarlabinstance;
            $bookings = $DB->get_records('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id));
            if ($bookings) { // If the admin or teacher has a booking use that info
                foreach ($bookings as $booking) {
                    if ($booking->starttime >= $currenttime) {
                        break;
                    }
                }
                $practiceid = $booking->practiceid;
                $expsyst2pract = $DB->get_record('ejsapp_expsyst2pract', array('ejsappid' => $ejsapp->id, 'practiceid' => $practiceid));
                $pracriceintro = $expsyst2pract->practiceintro;
            } else { // If there is no booking, use any info
                $expsyst2pract = $DB->get_record('ejsapp_expsyst2pract', array('ejsappid' => $ejsapp->id, 'practiceid' => '1'));
                $practiceintro = $expsyst2pract->practiceintro;
            }
        }
        echo $OUTPUT->heading(generate_applet_embedding_code($ejsapp, $sarlabinstance, $practiceintro, $state_file, $session_id, $session_ip, $session_port, $session_director));
    } else { //Students
        if ($DB->record_exists('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id))) {
            $currenttime = date('Y-m-d H:00:00');
            $bookings = $DB->get_records('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id));
            foreach ($bookings as $booking) {
                if ($booking->starttime >= $currenttime) {
                    break;
                }
            }
            $endtime = $booking->endtime;
            $valid = $booking->valid;
            if ($booking->starttime == $currenttime || has_capability('moodle/course:viewhiddensections', $context, $USER->id, true)) { //Check booking date and hour
                if ($usingsarlab == 1) {
                    $sarlabinstance = $remlab_conf->sarlabinstance;
                    $practiceid = $booking->practiceid;
                    $expsyst2pract = $DB->get_record('ejsapp_expsyst2pract', array('ejsappid' => $ejsapp->id, 'practiceid' => $practiceid));
                    $pracriceintro = $expsyst2pract->practiceintro;
                }
                echo $OUTPUT->heading(generate_applet_embedding_code($ejsapp, $sarlabinstance, $practiceintro, $state_file, $session_id, $session_ip, $session_port, $session_director));
            } else {
                echo $OUTPUT->heading(get_string('no_booking', 'ejsapp'));
                echo $OUTPUT->heading(get_string('check_bookings', 'ejsapp'));
            }
        } else {
            echo $OUTPUT->heading(get_string('no_booking', 'ejsapp'));
            echo $OUTPUT->heading(get_string('check_bookings', 'ejsapp'));
        }
    }
} //if(($ejsapp->is_rem_lab == 0)... else

if ($ejsapp->appwording) { // If some text was written, show it
    $formatoptions = new stdClass;
    $formatoptions->noclean = true;
    $formatoptions->overflowdiv = true;
    $formatoptions->context = $context;
    $content = format_text($ejsapp->appwording, $ejsapp->appwordingformat, $formatoptions);
    echo $OUTPUT->box($content, 'generalbox center clearfix');
}

//Buttons to close or leave collab sessions:
if ($session_id) {
    $close_url = $CFG->wwwroot .
        "/blocks/ejsapp_collab_session/close_collaborative_session.php?session=" .
        $session_id . "&courseid=" . $course->id;
    // . "&cmid=" . $cm->id;

    if ($session_director) {
        $close_button = get_string('closeMasSessBut', 'block_ejsapp_collab_session');
    } else {
        $close_button = get_string('closeStudSessBut', 'block_ejsapp_collab_session');
    }
    $button = <<<EOD
<center>
<form>
<input type="button" value="$close_button" onClick="window.location.href = '  $close_url'">
</form>
</center>
EOD;
    echo $button;
}

// Finish the page
echo $OUTPUT->footer();