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
require_once('generate_applet_embedding_code.php');

global $USER, $DB, $CFG, $PAGE, $OUTPUT;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$state_file = optional_param('state_file', null, PARAM_TEXT);
$exp_file = optional_param('exp_file', null, PARAM_TEXT);
$session_id = optional_param('colsession', null, PARAM_INT);

if (!is_null($session_id)) {
    $collab_session = $DB->get_record('ejsapp_collab_sessions',array('id'=>$session_id));
    if (isset($collab_session->localport)) {
        require_once(dirname(__FILE__) . '/../../blocks/ejsapp_collab_session/manage_collaborative_db.php');

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
    } else print_error(get_string('cantJoinSessionErr2', 'block_ejsapp_collab_session'));
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
$PAGE->set_button($OUTPUT->update_module_button($cm->id, get_string('modulename', 'ejsapp')));

//Set CSS style
/*$cssfilename = '/mod/ejsapp/styles.css';
if (file_exists($CFG->dirroot.$cssfilename)) {
    $PAGE->requires->css($cssfilename);
}*/

// Output starts here
echo $OUTPUT->header();
if ($ejsapp->intro) { // If some text was written, show the intro
    echo $OUTPUT->box(format_module_intro('ejsapp', $ejsapp, $cm->id), 'generalbox mod_introbox', 'ejsappintro');
}

//Check if there are variables configured to be personalized in this EJSApp
$personalvarsinfo = null;
if ($ejsapp->personalvars == 1) {
    $personalvarsinfo = new stdClass();
    $personalvars = $DB->get_records('ejsapp_personal_vars', array('ejsappid' => $ejsapp->id));
    $i = 0;
    foreach ($personalvars as $personalvar) {
        $uniqueval = filter_var(md5($USER->firstname . $i . $USER->username . $USER->lastname . $USER->id . $personalvar->id . $personalvar->name . $personalvar->type . $USER->email . $personalvar->minval . $personalvar->maxval), FILTER_SANITIZE_NUMBER_INT);
        mt_srand($uniqueval/(pow(10,strlen($USER->username))));
        $personalvarsinfo->name[$i] = $personalvar->name;
        $factor = 1;
        if ($personalvar->type == 'Double')  $factor = 1000;
        $personalvarsinfo->value[$i] = mt_rand($factor*$personalvar->minval, $factor*$personalvar->maxval)/$factor;
        $personalvarsinfo->type[$i] = $personalvar->type;
        $i++;
    }
}

//For logging purposes:
$action = 'view';
$check_activity = 300;   //register whether the user is still in the activity or not every 5 min
$accessed = false;

//Check the access conditions, depending on whether sarlab and/or the ejsapp booking system are being used or not and whether the ejsapp instance is a remote lab or not.
$sarlabinfo = null;
if (($ejsapp->is_rem_lab == 0)) { //Virtual lab
    $accessed = true;
    echo $OUTPUT->heading(generate_applet_embedding_code($ejsapp, $sarlabinfo, $state_file, $collabinfo, $personalvarsinfo, $exp_file, null));
} else { //<Remote lab>
    //<Check if the remote lab is operative>
    $allow_access = true;
    $ejsapp_lab_active = $DB->get_field('ejsapp_remlab_conf', 'active', array('ejsappid'=>$ejsapp->id));
    if ($ejsapp_lab_active == 0) {
        $allow_access = false;
    }
    //</Check if the remote lab is operative>

    //<Check if we should grant free access to the user for this remote lab>
    $module = new stdClass();
    $allow_free_access = true;
    $using_bookings = false;
    if ( ($ejsapp->free_access != 1) && (!has_capability('mod/ejsapp:addinstance', $coursecontext, $USER->id, true)) ) {     //Not free access and the user does not have special privileges
        if ($DB->record_exists('modules', array('name' => 'ejsappbooking'))) { //Is EJSApp Booking System plugins installed?
            $module = $DB->get_record('modules', array('name' => 'ejsappbooking'));
            if ($DB->record_exists('course_modules', array('course' => $ejsapp->course, 'module' => $module->id))) { //Is there an ejsappbooking instance in the course?
                if ($DB->get_field('course_modules', 'visible',  array('course' => $ejsapp->course, 'module' => $module->id))) { //Is it visible?
                    $using_bookings = true;
                    $allow_free_access = false;
                }
            }
        }
    } //TODO: Labs with free access in a course but with Booking System in a different course.
    //</Check if we should grant free access to the user for this remote lab>

    $remlab_conf = $DB->get_record('ejsapp_remlab_conf', array('ejsappid'=>$ejsapp->id));
    $usingsarlab = $remlab_conf->usingsarlab;
    if ($allow_free_access && $allow_access) { //Admins and teachers, not using ejsappbooking or free access remote lab, AND the remote lab is operative
        //<Search past accesses to this ejsapp lab or to the same remote lab added as a different ejsapp activity in this or any other course>
        if ($remlab_conf->usingsarlab == 0) {
            $ejsapp_lab_ip = $DB->get_field('ejsapp_remlab_conf', 'ip', array('ejsappid'=>$ejsapp->id));
            $ejsapp_lab_port = $DB->get_field('ejsapp_remlab_conf', 'port', array('ejsappid'=>$ejsapp->id));
            $repeated_ejsapp_labs = $DB->get_records('ejsapp_remlab_conf', array('ip'=>$ejsapp_lab_ip, 'port'=>$ejsapp_lab_port));
        } else {
            $ejsapp_lab_conf = $DB->get_field('ejsapp_expsyst2pract', 'practiceintro', array('ejsappid'=>$ejsapp->id));
            $repeated_practices = $DB->get_records('ejsapp_expsyst2pract', array('practiceintro'=>$ejsapp_lab_conf));
            $ejsappids = array(0);
            foreach ($repeated_practices as $repeated_practice){
                array_push($ejsappids, $repeated_practice->ejsappid);
            }
            $repeated_practices = $DB->get_records_list('ejsapp_remlab_conf', 'ejsappid', $ejsappids);
            //The previous queries may identify two different remote labs in two different SARLAB systems as only one, so we need to do something more
            $sarlab_instance = $DB->get_field('ejsapp_remlab_conf', 'sarlabinstance', array('ejsappid'=>$ejsapp->id));
            $repeated_ejsapp_labs = array(0);
            foreach ($repeated_practices as $repeated_practice) { //check whether the remote lab is in the same SARLAB instance or not
                if ($repeated_practice->usingsarlab == 1 && $repeated_practice->sarlabinstance == $sarlab_instance){
                    array_push($repeated_ejsapp_labs, $repeated_practice);
                }
            }
        }
        $time_last_access = 0;
        foreach($repeated_ejsapp_labs as $repeated_ejsapp_lab) {
            if (isset($repeated_ejsapp_lab->ejsappid)) {
                $repeated_ejsapp = $DB->get_record('ejsapp', array('id'=>$repeated_ejsapp_lab->ejsappid));
                if (isset($repeated_ejsapp->name)) {
                    if ($CFG->version < 2013111800) { //Moodle 2.5 or inferior
                        $log_records = $DB->get_records('log', array('module'=>'ejsapp', 'info'=>$repeated_ejsapp->name, 'action'=>'working'));
                    } else {
                        // Retrieve information from ejsapp's logging table
                        $log_records = $DB->get_records('ejsapp_log', array('info'=>$repeated_ejsapp->name, 'action'=>'working'));
                    }
                    foreach ($log_records as $log_record) {
                        if ($log_record->userid != $USER->id) {
                            $time_last_access = max($time_last_access, $log_record->time);
                        }
                    }
                }
            }
        }
        //</Search past accesses to this ejsapp lab or to the same remote lab added as a different ejsapp activity in this or any other course>
        $currenttime = date('Y-m-d H:i:s');
        $currenttime_UNIX = strtotime($currenttime);
        $lab_in_use = true;
        if ($currenttime_UNIX - $time_last_access > $check_activity+10) $lab_in_use = false;
        if (!$lab_in_use) {
            if ($usingsarlab == 1) {
                if ($using_bookings) {
                    //Check if there is a booking and obtain the needed information for Sarlab in case there is:
                    $sarlabinfo = check_booking($DB, $USER, $ejsapp, $currenttime, $remlab_conf);
                } else { //If there is no active booking, the user can still enter to the first experience defined for this remote lab... TODO: Let choosing the experience
                    $expsyst2pract = $DB->get_record('ejsapp_expsyst2pract', array('ejsappid' => $ejsapp->id, 'practiceid' => 1));
                    $sarlabinfo = define_sarlab($remlab_conf->sarlabinstance, 0, $expsyst2pract->practiceintro);
                }
            }
            $accessed = true;
            echo $OUTPUT->heading(generate_applet_embedding_code($ejsapp, $sarlabinfo, $state_file, $collabinfo, $personalvarsinfo, $exp_file, null));
        } else {
            echo $OUTPUT->heading(get_string('lab_in_use', 'ejsapp')); //TODO: Add countdown with the time remaining till the lab becomes available
            $action = 'need_to_wait';
            /*$url = $CFG->wwwroot . '/mod/ejsapp/countdown.php';
            $PAGE->requires->js_init_call('M.mod_ejsapp.countdown', array($url, $CFG->version));*/
        }
    } else { //Students trying to access a remote lab with restricted access OR remote lab not operative
        if (!$allow_access) { //Remote lab not operative
            echo $OUTPUT->heading(get_string('inactive_lab', 'ejsapp'));
            $action = 'inactive_lab';
        } else {    //Students trying to access a remote lab with restricted access
            //Check if there is a booking and obtain the needed information for Sarlab in case it is used:
            $sarlabinfo = check_booking($DB, $USER, $ejsapp, date('Y-m-d H:i:s'), $remlab_conf);
            if (!is_null($sarlabinfo)) {
                $accessed = true;
                echo $OUTPUT->heading(generate_applet_embedding_code($ejsapp, $sarlabinfo, $state_file, $collabinfo, $personalvarsinfo, $exp_file, null));
            } else { //No active booking
                echo $OUTPUT->heading(get_string('no_booking', 'ejsapp'));
                if (($usingsarlab == 1 && $remlab_conf->sarlabcollab == 1)) {
                    echo $OUTPUT->heading(get_string('collab_access', 'ejsapp'));
                    $sarlabinfo = define_sarlab($remlab_conf->sarlabinstance, $remlab_conf->sarlabcollab, 'NULL');
                    echo $OUTPUT->heading(generate_applet_embedding_code($ejsapp, $sarlabinfo, $state_file, $collabinfo, $personalvarsinfo, $exp_file, null));
                    $action = 'collab_view';
                } else {
                    echo $OUTPUT->heading(get_string('check_bookings', 'ejsapp'));
                    $action = 'need_to_book';
                }
            }
        }
    } //</Remote lab>
} //if(($ejsapp->is_rem_lab == 0)... else

// Add the access to the log, taking into account the action; i.e. whether the user could access (view) the lab or not:
if ($CFG->version < 2013111800) { //Moodle 2.5 or inferior
    add_to_log($course->id, 'ejsapp', $action, "view.php?id=$cm->id", $ejsapp->name, $cm->id);
} else {
    switch ($action) {
        case 'view':
            $event = \mod_ejsapp\event\course_module_viewed::create(array(
                'objectid' => $ejsapp->id,
                'context' => $modulecontext
            ));
            break;
        case 'need_to_wait':
            $event = \mod_ejsapp\event\course_module_wait::create(array(
                'objectid' => $ejsapp->id,
                'context' => $modulecontext
            ));
            break;
        case 'need_to_book':
            $event = \mod_ejsapp\event\course_module_book::create(array(
                'objectid' => $ejsapp->id,
                'context' => $modulecontext
            ));
            break;
        case 'collab_view':
            $event = \mod_ejsapp\event\course_module_collab::create(array(
                'objectid' => $ejsapp->id,
                'context' => $modulecontext
            ));
            break;
        case 'inactive_lab':
            $event = \mod_ejsapp\event\course_module_inactive::create(array(
                'objectid' => $ejsapp->id,
                'context' => $modulecontext,
            ));
            break;
    }
    /*$event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('ejsapp', $ejsapp);*/
    $event->trigger();
}
// Monitoring for how long the user works with the lab:
if ($accessed) {
    if ($CFG->version < 2013111800) { //Moodle 2.5 or inferior
        $url = $CFG->wwwroot . '/mod/ejsapp/add_to_log.php?courseid='.$course->id.'&activityid='.$cm->id.'&ejsappname='.$ejsapp->name.'&method=0';
    } else {
        $url = $CFG->wwwroot . '/mod/ejsapp/add_to_log.php?courseid='.$course->id.'&activityid='.$cm->id.'&ejsappname='.$ejsapp->name.'&method=1&userid='.$USER->id;
    }
    $PAGE->requires->js_init_call('M.mod_ejsapp.init_add_log', array($url, $CFG->version, $check_activity));
}

// If some text was written, show it
if ($ejsapp->appwording) {
    $formatoptions = new stdClass;
    $formatoptions->noclean = true;
    $formatoptions->overflowdiv = true;
    $formatoptions->context = $modulecontext;
    $content = format_text($ejsapp->appwording, $ejsapp->appwordingformat, $formatoptions);
    echo $OUTPUT->box($content, 'generalbox center clearfix');
}

// Buttons to close or leave collab sessions:
if (isset($collab_session)) {
    if (isset($collab_session->master_user)) {
        $close_url = $CFG->wwwroot .
            "/blocks/ejsapp_collab_session/close_collaborative_session.php?session=" .
            $session_id . "&courseid=" . $course->id;
        if ($USER->id == $collab_session->master_user) {
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
}

// Finish the page
echo $OUTPUT->footer();


/**
 * Checks if there is an active booking and gets the information needed by sarlab
 *
 * @param object $DB
 * @param object $USER
 * @param stdClass $ejsapp
 * @param string $currenttime
 * @param stdClass $remlab_conf
 * @return stdClass $sarlabinfo
 */
function check_booking($DB, $USER, $ejsapp, $currenttime, $remlab_conf) {
    $sarlabinfo = null;

    if ($DB->record_exists('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id, 'valid' => 1))) {
        $bookings = $DB->get_records('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id, 'valid' => 1));
	 foreach ($bookings as $booking) { // If the user has an active booking, use that info
            if ($currenttime >= $booking->starttime && $currenttime < $booking->endtime) {
                $expsyst2pract = $DB->get_record('ejsapp_expsyst2pract', array('ejsappid' => $ejsapp->id, 'practiceid' => $booking->practiceid));
                $practice = $expsyst2pract->practiceintro;
                $sarlabinfo = define_sarlab($remlab_conf->sarlabinstance, 0, $practice);
                break;
            }
        }
    }

    return $sarlabinfo;
}


/**
 * Defines a new sarlab object with all the needed information
 *
 * @param int $instance sarlab instance
 * @param int $collab 0 if not a collab session, 1 if collaborative
 * @param string $practice the practice identifier in sarlab
 * @return stdClass $sarlabinfo
 */
function define_sarlab($instance, $collab, $practice) {
    $sarlabinfo = new stdClass();
    $sarlabinfo->instance = $instance;
    $sarlabinfo->collab = $collab;
    $sarlabinfo->practice = $practice;

    return $sarlabinfo;
}