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
require_once('generate_embedding_code.php');

global $USER, $DB, $CFG, $PAGE, $OUTPUT;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$state_file = optional_param('state_file', null, PARAM_TEXT);
$cnt_file = optional_param('cnt_file', null, PARAM_TEXT);
$rec_file = optional_param('rec_file', null, PARAM_TEXT);
$session_id = optional_param('colsession', null, PARAM_INT);

$data_files = array($state_file, $cnt_file, $rec_file);

if (!is_null($session_id)) {
    $collab_session = $DB->get_record('ejsapp_collab_sessions',array('id'=>$session_id));
    if (isset($collab_session->localport)) {
        require_once(dirname(__FILE__) . '/../../blocks/ejsapp_collab_session/manage_collab_db.php');

        $n = $collab_session->ejsapp;

        $collabinfo = new stdClass();
        $collabinfo->session = $session_id;
        $collabinfo->ip = $collab_session->ip;
        $collabinfo->localport = $collab_session->localport;
        if ($collab_session->sarlabport != 0) $collabinfo->sarlabport = $collab_session->sarlabport;

        if (am_i_master_user()) {
            $collabinfo->director = $collab_session->id;
        }
        elseif (!$DB->record_exists('ejsapp_collab_acceptances', array('accepted_user'=>$USER->id))) {
            $collab_record = new stdClass();
            $collab_record->accepted_user = $USER->id;
            $collab_record->collaborative_session = $session_id;
            $DB->insert_record('ejsapp_collab_acceptances', $collab_record);
        }
    } else print_error('cantJoinSessionErr2', 'block_ejsapp_collab_session');
} else {
    $n = optional_param('n', null, PARAM_INT);
    $collabinfo = null;
}

if ($id) {
    $cm = get_coursemodule_from_id('ejsapp', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $ejsapp = $DB->get_record('ejsapp', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif (isset($n)) {
    $ejsapp = $DB->get_record('ejsapp', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $ejsapp->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('ejsapp', $ejsapp->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);

// Print the page header
$PAGE->set_url('/mod/ejsapp/view.php', array('id' => $cm->id));
$PAGE->set_title($ejsapp->name);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'ejsapp'));

// Set CSS style for javascript ejsapps
//$original_css_filename = $ejsapp->codebase.'_ejs_library/css/ejsSimulation.css';
$custom_css_filename = $ejsapp->codebase.'_ejs_library/css/ejsapp.css';
/*if (file_exists($CFG->dirroot.$original_css_filename)) {
    $PAGE->requires->css($original_css_filename);
}*/
if (file_exists($CFG->dirroot.$custom_css_filename)) {
    $PAGE->requires->css($custom_css_filename);
}

// Output starts here
echo $OUTPUT->header();
if ($ejsapp->intro) { // If some text was written, show the intro
    echo $OUTPUT->box(format_module_intro('ejsapp', $ejsapp, $cm->id), 'generalbox mod_introbox', 'ejsappintro');
}

// Check if there are variables configured to be personalized in this EJSApp
$personalvarsinfo = personalize_vars($ejsapp, $USER);

// For logging purposes:
$action = 'view';
$check_activity = 30;   //register whether the user is still in the activity or not every 30 seconds
$accessed = false;

// <Check the access conditions, depending on whether sarlab and/or the ejsapp booking system are being used or not and whether the ejsapp instance is a remote lab or not>
$sarlabinfo = null;
if (($ejsapp->is_rem_lab == 0)) { //Virtual lab
    $accessed = true;
    $max_use_time = 604800; // High enough number... although it is never used in the case of a virtual lab
    prepare_ejs_file($ejsapp);
    echo $OUTPUT->box(generate_embedding_code($ejsapp, $sarlabinfo, $data_files, $collabinfo, $personalvarsinfo, null));
} else { //<Remote lab>
    $practice = $DB->get_field('remlab_manager_expsyst2pract', 'practiceintro', array('ejsappid'=>$ejsapp->id));
    $idle_time = $DB->get_field('remlab_manager_conf', 'reboottime', array('practiceintro'=>$practice)); //minimum elapsed time between someone stops using a remote lab and somebody else can start using it

    //<Some stuff we will need later>
    $lab_status = '';
    $time_information = '';
    //</Some stuff we will need later>

    //<Check if the remote lab is operative>
    $allow_access = true;
    $ejsapp_lab_active = $DB->get_field('remlab_manager_conf', 'active', array('practiceintro'=>$practice));
    if ($ejsapp_lab_active == 0) {
        $allow_access = false;
    }
    //</Check if the remote lab is operative>

    //<Check if we should grant free access to the user for this remote lab>
    $allow_free_access = true;
    $labmanager = has_capability('mod/ejsapp:accessremotelabs', $coursecontext, $USER->id, true);
    $remlab_conf = $DB->get_record('remlab_manager_conf', array('practiceintro'=>$practice));
    $repeated_ejsapp_labs = get_repeated_remlabs($remlab_conf);
    $booking_info = check_active_booking($repeated_ejsapp_labs, $course->id);
    $booking_system_in_use = check_booking_system($ejsapp);
    if (!$labmanager) { // The user does not have special privileges and...
        if (($remlab_conf->free_access != 1) && $booking_system_in_use) { //Not free access and the booking system is in use
            $allow_free_access = false;
        } else if (($remlab_conf->free_access == 1) && $booking_info['active_booking']) { //Free access and there is an active booking for this remote lab made by anyone in a different course
            $allow_free_access = false;
        } else if (($remlab_conf->free_access != 1) && !$booking_system_in_use && $booking_info['active_booking']) { //Not free access, the booking system is not in use and there is an active booking for this remote lab made by anyone in a different course
            $allow_free_access = false;
        }
    }
    //</Check if we should grant free access to the user for this remote lab>

    $usingsarlab = $remlab_conf->usingsarlab;
    if ($allow_free_access && $allow_access) { //Admins and teachers, not using ejsappbooking or free access remote lab, AND the remote lab is operative
        //<Getting the maximum time the user is allowed to use the remote lab>
        $maxslots = $remlab_conf->dailyslots;
        $slotsduration_conf = $remlab_conf->slotsduration;
        if ($slotsduration_conf > 4) $slotsduration_conf = 4;
        $slotsduration = array(2, 5, 15, 30, 60);
        $max_use_time = $maxslots*60*$slotsduration[$slotsduration_conf]; //in seconds
        //</Getting the maximum time the user is allowed to use the remote lab>
        //Search past accesses to this ejsapp lab or to the same remote lab added as a different ejsapp activity in this or any other course
        $time_information = get_occupied_ejsapp_time_information($repeated_ejsapp_labs, $slotsduration, time());
        //Get the lab use status
        $lab_status = get_lab_status($time_information, $idle_time, $check_activity);
        if ($lab_status == 'available') {
            if ($usingsarlab == 1) {
                //Check if there is a booking done by this user and obtain the needed information for Sarlab in case it is used:
                $sarlabinfo = check_users_booking($DB, $USER, $ejsapp, date('Y-m-d H:i:s'), $remlab_conf->sarlabinstance, $labmanager, $max_use_time);
                if (is_null($sarlabinfo)) {
                    $expsyst2pract = $DB->get_record('remlab_manager_expsyst2pract', array('ejsappid' => $ejsapp->id, 'practiceid' => 1));
                    $sarlabinfo = define_sarlab($remlab_conf->sarlabinstance, 0, $expsyst2pract->practiceintro, $labmanager, $max_use_time);
                }
            }
            $accessed = true;
            prepare_ejs_file($ejsapp);
            echo $OUTPUT->box(generate_embedding_code($ejsapp, $sarlabinfo, $data_files, $collabinfo, $personalvarsinfo, null));
        } else {
            echo $OUTPUT->box(get_string('lab_in_use', 'ejsapp'));
            if (($usingsarlab == 1 && $remlab_conf->sarlabcollab == 1)) { //Teacher can still access in collaborative mode
                echo $OUTPUT->box(get_string('collab_access', 'ejsapp'));
                $sarlabinfo = define_sarlab($remlab_conf->sarlabinstance, $remlab_conf->sarlabcollab, 'NULL', $labmanager, $max_use_time);
                prepare_ejs_file($ejsapp);
                echo $OUTPUT->box(generate_embedding_code($ejsapp, $sarlabinfo, $data_files, $collabinfo, $personalvarsinfo, null));
                $action = 'collab_view';
            } else {
                $action = 'need_to_wait';
            }
        }
    } else { //Students trying to access a remote lab with restricted access OR remote lab not operative
        if (!$allow_access) { //Remote lab not operative
            echo $OUTPUT->box(get_string('inactive_lab', 'ejsapp'));
            $action = 'inactive_lab';
        } else {    //Students trying to access a remote lab with restricted access
            if ($booking_info['active_booking']) { //Remote lab freely accessible from one course but with an active booking made by anyone in a different course
                echo $OUTPUT->box(get_string('booked_lab', 'ejsapp'));
                $action = 'booked_lab';
            } else { //Other cases
                //<Getting the maximum time the user is allowed to use the remote lab>
                $booking_end_time = check_last_valid_booking($DB, $USER->username, $ejsapp->id);
                $booking_end_time_UNIX = strtotime($booking_end_time);
                $currenttime = time();
                $max_use_time = $booking_end_time_UNIX - $currenttime; //in seconds
                //</Getting the maximum time the user is allowed to use the remote lab>
                //Check if there is a booking done by this user and obtain the needed information for Sarlab in case it is used:
                $sarlabinfo = check_users_booking($DB, $USER, $ejsapp, date('Y-m-d H:i:s'), $remlab_conf->sarlabinstance, $labmanager, $max_use_time);
                if (!is_null($sarlabinfo)) { //The user has an active booking -> he can access the lab
                    $accessed = true;
                    prepare_ejs_file($ejsapp);
                    echo $OUTPUT->box(generate_embedding_code($ejsapp, $sarlabinfo, $data_files, $collabinfo, $personalvarsinfo, null));
                } else { //No active booking
                    echo $OUTPUT->box(get_string('no_booking', 'ejsapp'));
                    if (($usingsarlab == 1 && $remlab_conf->sarlabcollab == 1)) { //Student can still access in collaborative mode
                        echo $OUTPUT->box(get_string('collab_access', 'ejsapp'));
                        $sarlabinfo = define_sarlab($remlab_conf->sarlabinstance, $remlab_conf->sarlabcollab, 'NULL', $labmanager, $max_use_time);
                        prepare_ejs_file($ejsapp);
                        echo $OUTPUT->box(generate_embedding_code($ejsapp, $sarlabinfo, $data_files, $collabinfo, $personalvarsinfo, null));
                        $action = 'collab_view';
                    } else { //No access
                        echo $OUTPUT->box(get_string('check_bookings', 'ejsapp'));
                        $action = 'need_to_book';
                    }
                }
            }
        }
    } //</Remote lab>
} //if(($ejsapp->is_rem_lab == 0)... else
// </Check the access conditions, depending on whether sarlab and/or the ejsapp booking system are being used or not and whether the ejsapp instance is a remote lab or not>

// <Add the access to the log, taking into account the action; i.e. whether the user could access (view) the lab or not>
switch ($action) {
    case 'view':
        $event = \mod_ejsapp\event\course_module_viewed::create(array(
            'objectid' => $ejsapp->id,
            'context' => $modulecontext,
            'other' => $ejsapp->name,
        ));
        $record = new stdClass();
        $record->id = $cm->id;
        $record->time = time();
        $record->userid = $USER->id;
        $record->action = 'viewed';
        $record->info = $ejsapp->name;
        $event->add_record_snapshot('ejsapp_log', $record);
        $DB->insert_record('ejsapp_log', $record);
        break;
    case 'need_to_wait':
        $event = \mod_ejsapp\event\course_module_wait::create(array(
            'objectid' => $ejsapp->id,
            'context' => $modulecontext,
            'other' => $ejsapp->name,
        ));
        break;
    case 'need_to_book':
        $event = \mod_ejsapp\event\course_module_book::create(array(
            'objectid' => $ejsapp->id,
            'context' => $modulecontext,
            'other' => $ejsapp->name,
        ));
        break;
    case 'collab_view':
        $event = \mod_ejsapp\event\course_module_collab::create(array(
            'objectid' => $ejsapp->id,
            'context' => $modulecontext,
            'other' => $ejsapp->name,
        ));
        break;
    case 'inactive_lab':
        $event = \mod_ejsapp\event\course_module_inactive::create(array(
            'objectid' => $ejsapp->id,
            'context' => $modulecontext,
            'other' => $ejsapp->name,
        ));
        break;
    case 'booked_lab':
        $event = \mod_ejsapp\event\course_module_booked::create(array(
            'objectid' => $ejsapp->id,
            'context' => $modulecontext,
            'other' => $ejsapp->name,
        ));
        break;
}
/*$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('ejsapp', $ejsapp);*/
$event->trigger();
// </Add the access to the log, taking into account the action; i.e. whether the user could access (view) the lab or not>

// If some text was written, show it
if ($ejsapp->appwording) {
    $formatoptions = new stdClass;
    $formatoptions->noclean = true;
    $formatoptions->overflowdiv = true;
    $formatoptions->context = $modulecontext;
    $content = format_text($ejsapp->appwording, $ejsapp->appwordingformat, $formatoptions);
    echo $OUTPUT->box($content, 'generalbox center clearfix');
}

// <Javascript features>
if ($accessed) {
    // Monitoring for how long the user works with the lab and checking she does not exceed the maximum time allowed to work with the remote lab
    $ejsappname = urlencode($ejsapp->name);
    $url_log = $CFG->wwwroot . '/mod/ejsapp/add_to_log.php?courseid='.$course->id.'&activityid='.$cm->id.'&ejsappname='.$ejsappname.'&userid='.$USER->id;
    $htmlid = "EJsS";
    $url_view = $CFG->wwwroot . '/mod/ejsapp/kick_out.php';
    $PAGE->requires->js_init_call('M.mod_ejsapp.init_add_log', array($url_log, $url_view, $ejsapp->is_rem_lab, $htmlid, $check_activity, $max_use_time));
} else if ($action == 'booked_lab' || $action == 'need_to_wait') {
    $remaining_time = get_remaining_time($booking_info, $lab_status, $time_information, $idle_time, $check_activity);
    $url = $CFG->wwwroot . '/mod/ejsapp/countdown.php?ejsappid='.$ejsapp->id.'&courseid='.$course->id.'&check_activity='.$check_activity;
    $htmlid = "timecountdown";
    echo $OUTPUT->box(html_writer::div('', '', array('id'=>$htmlid)));
    $PAGE->requires->js_init_call('M.mod_ejsapp.init_countdown', array($url, $htmlid, $remaining_time, $check_activity, ' ' . get_string('seconds', 'ejsapp'), get_string('refresh', 'ejsapp')));
}
// </Javascript features>

// <Buttons to close or leave collab sessions>
if (isset($collab_session)) {
    $url = $CFG->wwwroot . "/blocks/ejsapp_collab_session/close_collab_session.php?session=$session_id&courseid={$course->id}";
    if ($USER->id == $collab_session->master_user) {
        $text = get_string('closeMasSessBut', 'block_ejsapp_collab_session');
    } else {
        $text = get_string('closeStudSessBut', 'block_ejsapp_collab_session');
    }
    $button = html_writer::empty_tag('input', array('type'=>'button', 'name'=>'close_session', 'value'=>$text, 'onClick'=>"window.location.href = '$url'"));
    echo $button;
}
// </Buttons to close or leave collab sessions>

// Finish the page
echo $OUTPUT->footer();