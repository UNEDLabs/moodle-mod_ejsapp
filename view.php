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
$cnt_file = optional_param('cnt_file', null, PARAM_TEXT);
$rec_file = optional_param('rec_file', null, PARAM_TEXT);
$session_id = optional_param('colsession', null, PARAM_INT);

$data_files = array($state_file, $cnt_file, $rec_file);

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
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'ejsapp'));

//Set CSS style
$cssfilename = $ejsapp->codebase.'_ejs_library/css/ejsapp.css';
if (file_exists($CFG->dirroot.$cssfilename)) {
    $PAGE->requires->css($cssfilename);
}

// Output starts here
echo $OUTPUT->header();
if ($ejsapp->intro) { // If some text was written, show the intro
    echo $OUTPUT->box(format_module_intro('ejsapp', $ejsapp, $cm->id), 'generalbox mod_introbox', 'ejsappintro');
}

//Check if there are variables configured to be personalized in this EJSApp
$personalvarsinfo = personalize_vars($ejsapp, $USER);

//For logging purposes:
$action = 'view';
$check_activity = 300;   //register whether the user is still in the activity or not every 5 min
$accessed = false;

//Check the access conditions, depending on whether sarlab and/or the ejsapp booking system are being used or not and whether the ejsapp instance is a remote lab or not.
$sarlabinfo = null;
if (($ejsapp->is_rem_lab == 0)) { //Virtual lab
    $accessed = true;
    prepare_ejs_file($ejsapp);
    echo $OUTPUT->box(generate_applet_embedding_code($ejsapp, $sarlabinfo, $data_files, $collabinfo, $personalvarsinfo, null));
} else { //<Remote lab>
    //<Check if the remote lab is operative>
    $allow_access = true;
    $ejsapp_lab_active = $DB->get_field('ejsapp_remlab_conf', 'active', array('ejsappid'=>$ejsapp->id));
    if ($ejsapp_lab_active == 0) {
        $allow_access = false;
    }
    //</Check if the remote lab is operative>

    //<Check if we should grant free access to the user for this remote lab>
    $allow_free_access = true;
    $labmanager = has_capability('mod/ejsapp:accessremotelabs', $coursecontext, $USER->id, true);
    $remlab_conf = $DB->get_record('ejsapp_remlab_conf', array('ejsappid'=>$ejsapp->id));
    $repeated_ejsapp_labs = get_repeated_remlabs($remlab_conf, $ejsapp);
    $anyones_active_booking = check_active_booking($repeated_ejsapp_labs, $course->id);
    if ( (($ejsapp->free_access != 1) && (!$labmanager)) && check_booking_system($ejsapp) ){ //Not free access and the user does not have special privileges and the booking system is in use
        $allow_free_access = false;
    } else if ( (($ejsapp->free_access == 1) && !$labmanager && $anyones_active_booking) ) { //Free access, the user does not have special privileges and there is an active booking for this remote lab made by anyone in a different course
        $allow_free_access = false;
    }
    //</Check if we should grant free access to the user for this remote lab>

    $usingsarlab = $remlab_conf->usingsarlab;
    if ($allow_free_access && $allow_access) { //Admins and teachers, not using ejsappbooking or free access remote lab, AND the remote lab is operative
        //<Search past accesses to this ejsapp lab or to the same remote lab added as a different ejsapp activity in this or any other course>
        $time_last_access = 0;
        foreach($repeated_ejsapp_labs as $repeated_ejsapp_lab) {
            if (isset($repeated_ejsapp_lab->ejsappid)) {
                $repeated_ejsapp = $DB->get_record('ejsapp', array('id'=>$repeated_ejsapp_lab->ejsappid));
                if (isset($repeated_ejsapp->name)) {
                    if ($CFG->version < 2013111899) { //Moodle 2.6 or inferior
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
                //Check if there is a booking done by this user and obtain the needed information for Sarlab in case it is used:
                $sarlabinfo = check_users_booking($DB, $USER, $ejsapp, date('Y-m-d H:i:s'), $remlab_conf, $labmanager);
                if (is_null($sarlabinfo)) { //If there is no active booking, the user can still enter to the first experience defined for this remote lab... TODO: Let choosing the experience
                    $expsyst2pract = $DB->get_record('ejsapp_expsyst2pract', array('ejsappid' => $ejsapp->id, 'practiceid' => 1));
                    $sarlabinfo = define_sarlab($remlab_conf->sarlabinstance, 0, $expsyst2pract->practiceintro, $labmanager);
                }
            }
            $accessed = true;
            prepare_ejs_file($ejsapp);
            echo $OUTPUT->box(generate_applet_embedding_code($ejsapp, $sarlabinfo, $data_files, $collabinfo, $personalvarsinfo, null));
        } else {
            echo $OUTPUT->box(get_string('lab_in_use', 'ejsapp')); //TODO: Add countdown with the time remaining till the lab becomes available
            $action = 'need_to_wait';
            /*$url = $CFG->wwwroot . '/mod/ejsapp/countdown.php';
            $PAGE->requires->js_init_call('M.mod_ejsapp.countdown', array($url, $CFG->version));*/
        }
    } else { //Students trying to access a remote lab with restricted access OR remote lab not operative
        if (!$allow_access) { //Remote lab not operative
            echo $OUTPUT->box(get_string('inactive_lab', 'ejsapp'));
            $action = 'inactive_lab';
        } else {    //Students trying to access a remote lab with restricted access
            if ($anyones_active_booking) { //Remote lab freely accessible from one course but with an active booking made by anyone in a different course
                echo $OUTPUT->box(get_string('booked_lab', 'ejsapp'));
                $action = 'booked_lab';
            } else { //Other cases
                //Check if there is a booking done by this user and obtain the needed information for Sarlab in case it is used:
                $sarlabinfo = check_users_booking($DB, $USER, $ejsapp, date('Y-m-d H:i:s'), $remlab_conf, $labmanager);
                if (!is_null($sarlabinfo)) { //The user has an active booking -> he can access the lab
                    $accessed = true;
                    prepare_ejs_file($ejsapp);
                    echo $OUTPUT->box(generate_applet_embedding_code($ejsapp, $sarlabinfo, $data_files, $collabinfo, $personalvarsinfo, null));
                } else { //No active booking
                    echo $OUTPUT->box(get_string('no_booking', 'ejsapp'));
                    if (($usingsarlab == 1 && $remlab_conf->sarlabcollab == 1)) { //Student can still access in collaborative mode
                        echo $OUTPUT->box(get_string('collab_access', 'ejsapp'));
                        $sarlabinfo = define_sarlab($remlab_conf->sarlabinstance, $remlab_conf->sarlabcollab, 'NULL', $labmanager);
                        prepare_ejs_file($ejsapp);
                        echo $OUTPUT->box(generate_applet_embedding_code($ejsapp, $sarlabinfo, $data_files, $collabinfo, $personalvarsinfo, null));
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

// Add the access to the log, taking into account the action; i.e. whether the user could access (view) the lab or not:
if ($CFG->version < 2013111899) { //Moodle 2.6 or inferior
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
        case 'booked_lab':
            $event = \mod_ejsapp\event\course_module_booked::create(array(
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
    if ($CFG->version < 2013111899) { //Moodle 2.6 or inferior
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
    /*if (isset($collab_session->master_user)) {
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
    }*/

    $form = new html_form();
    $form->url = new moodle_url($CFG->wwwroot . "/blocks/ejsapp_collab_session/close_collaborative_session.php",
                                array('session' => $session_id, 'courseid' => $course->id));
    if ($USER->id == $collab_session->master_user) {
        $form->button->text = get_string('closeMasSessBut', 'block_ejsapp_collab_session');
    } else {
        $form->button->text = get_string('closeStudSessBut', 'block_ejsapp_collab_session');
    }
    echo $OUTPUT->button($form);
}

// Finish the page
echo $OUTPUT->footer();


/**
 *
 * Checks whether a the booking system is being used in the course of a particular ejsapp activity or not.
 *
 * @param stdClass $ejsapp
 * @return bool $using_bs
 *
 */
function check_booking_system($ejsapp) {
    global $DB;

    $using_bs = false;
    if ($DB->record_exists('modules', array('name' => 'ejsappbooking'))) { //Is EJSApp Booking System plugins installed?
        $module = $DB->get_record('modules', array('name' => 'ejsappbooking'));
        if ($DB->record_exists('course_modules', array('course' => $ejsapp->course, 'module' => $module->id))) { //Is there an ejsappbooking instance in the course?
            if ($DB->get_field('course_modules', 'visible',  array('course' => $ejsapp->course, 'module' => $module->id))) { //Is it visible?
                $using_bs = true;
            }
        }
    }

    return $using_bs;
}


/**
 * Checks if there is an active booking made by the current user and gets the information needed by sarlab
 *
 * @param object $DB
 * @param object $USER
 * @param stdClass $ejsapp
 * @param string $currenttime
 * @param stdClass $remlab_conf
 * @param int $labmanager
 * @return stdClass $sarlabinfo
 */
function check_users_booking($DB, $USER, $ejsapp, $currenttime, $remlab_conf, $labmanager) {
    $sarlabinfo = null;

    if ($DB->record_exists('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id, 'valid' => 1))) {
        $bookings = $DB->get_records('ejsappbooking_remlab_access', array('username' => $USER->username, 'ejsappid' => $ejsapp->id, 'valid' => 1));
        foreach ($bookings as $booking) { // If the user has an active booking, use that info
            if ($currenttime >= $booking->starttime && $currenttime < $booking->endtime) {
                $expsyst2pract = $DB->get_record('ejsapp_expsyst2pract', array('ejsappid' => $ejsapp->id, 'practiceid' => $booking->practiceid));
                $practice = $expsyst2pract->practiceintro;
                $sarlabinfo = define_sarlab($remlab_conf->sarlabinstance, 0, $practice, $labmanager);
                break;
            }
        }
     }

    return $sarlabinfo;
}


/**
 * Checks if there is an active booking made by any user
 *
 * @param object $DB
 * @param stdClass $ejsapp
 * @param string $currenttime
 * @return boolean $active_booking
 */
function check_anyones_booking($DB, $ejsapp, $currenttime) {
    $active_booking = false;

    if ($DB->record_exists('ejsappbooking_remlab_access', array('ejsappid' => $ejsapp->id, 'valid' => 1))) {
        $bookings = $DB->get_records('ejsappbooking_remlab_access', array('ejsappid' => $ejsapp->id, 'valid' => 1));
        foreach ($bookings as $booking) { // If the user has an active booking, use that info
            if ($currenttime >= $booking->starttime && $currenttime < $booking->endtime) {
                $active_booking = true;
            }
        }
    }

    return $active_booking;
}


/**
 *
 * Checks whether a particular remote lab is also present in other courses or not and gives the list of repeated labs.
 *
 * @param stdClass $remlab_conf
 * @param stdClass $ejsapp
 * @return array $repeated_ejsapp_labs
 *
 */
function get_repeated_remlabs($remlab_conf, $ejsapp) {
    global $DB;

    if ($remlab_conf->usingsarlab == 0) {
        $ejsapp_lab_ip = $DB->get_field('ejsapp_remlab_conf', 'ip', array('ejsappid'=>$ejsapp->id));
        $ejsapp_lab_port = $DB->get_field('ejsapp_remlab_conf', 'port', array('ejsappid'=>$ejsapp->id));
        $repeated_ejsapp_labs = $DB->get_records('ejsapp_remlab_conf', array('ip'=>$ejsapp_lab_ip, 'port'=>$ejsapp_lab_port));
    } else {
        $ejsapp_lab_conf = $DB->get_field('ejsapp_expsyst2pract', 'practiceintro', array('ejsappid'=>$ejsapp->id));
        $repeated_practices = $DB->get_records('ejsapp_expsyst2pract', array('practiceintro'=>$ejsapp_lab_conf));
        $ejsappids = array();
        foreach ($repeated_practices as $repeated_practice) {
            array_push($ejsappids, $repeated_practice->ejsappid);
        }
        $repeated_practices = $DB->get_records_list('ejsapp_remlab_conf', 'ejsappid', $ejsappids);
        //Previous queries may identify two different remote labs in two different SARLAB systems as only one, so we need to do something more:
        $sarlab_instance = $DB->get_field('ejsapp_remlab_conf', 'sarlabinstance', array('ejsappid'=>$ejsapp->id));
        $repeated_ejsapp_labs = array();
        foreach ($repeated_practices as $repeated_practice) { //check whether the remote lab is in the same SARLAB instance or not
            if ($repeated_practice->usingsarlab == 1 && $repeated_practice->sarlabinstance == $sarlab_instance){
                array_push($repeated_ejsapp_labs, $repeated_practice);
            }
        }
    }

    return $repeated_ejsapp_labs;
}


/**
 *
 * Gives the list of repeated remote labs in courses with a booking system.
 *
 * @param array $repeated_ejsapp_labs
 * @return array $repeated_ejsapp_labs_with_bs
 *
 */
function get_repeated_remlabs_with_bs($repeated_ejsapp_labs) {
    global $DB;

    $repeated_ejsapp_labs_with_bs = array();
    foreach ($repeated_ejsapp_labs as $repeated_ejsapp_lab) {
        $ejsappid = $DB->get_field('ejsapp_remlab_conf', 'ejsappid', array('id'=>$repeated_ejsapp_lab->id));
        $ejsapp = $DB->get_record('ejsapp', array('id'=>$ejsappid));
        if (check_booking_system($ejsapp)) array_push($repeated_ejsapp_labs_with_bs, $ejsapp);
    }

    return $repeated_ejsapp_labs_with_bs;
}


/**
 *
 * Tells if there is at least one different course in which the same remote lab has been booked for this hour.
 *
 * @param array $repeated_ejsapp_labs
 * @param int $courseid
 * @return boolean $active_booking
 *
 */
function check_active_booking($repeated_ejsapp_labs, $courseid) {
    global $DB;

    $active_booking = false;
    if (count($repeated_ejsapp_labs) > 1) {
        $repeated_ejsapp_labs_with_bs = get_repeated_remlabs_with_bs($repeated_ejsapp_labs);
        if (count($repeated_ejsapp_labs_with_bs) > 0) {
            foreach ($repeated_ejsapp_labs_with_bs as $repeated_ejsapp_lab_with_bs) {
                if ($repeated_ejsapp_lab_with_bs->course != $courseid) {
                    if ($active_booking = check_anyones_booking($DB, $repeated_ejsapp_lab_with_bs, date('Y-m-d H:i:s'))) {
                        break;
                    }
                }
            }
        }
    }

    return $active_booking;
}


/**
 * Defines a new sarlab object with all the needed information
 *
 * @param int $instance sarlab instance
 * @param int $collab 0 if not a collab session, 1 if collaborative
 * @param string $practice the practice identifier in sarlab
 * @param int $labmanager whether the user is a laboratory manager or not
 * @return stdClass $sarlabinfo
 */
function define_sarlab($instance, $collab, $practice, $labmanager) {
    $sarlabinfo = new stdClass();
    $sarlabinfo->instance = $instance;
    $sarlabinfo->collab = $collab;
    $sarlabinfo->practice = $practice;
    $sarlabinfo->labmanager = $labmanager;

    return $sarlabinfo;
}


/**
 * Gets the required EJS .jar or .zip file for this activity from Moodle's File System and places it
 * in the required directory (inside jarfiles) when the file there doesn't exist or it is not synchronized with
 * the file in Moodle's File System (whether because its an alias to a file that has been modified or because
 * the activity has been edited and the original .jar or .zip file has been replaced by a new one).
 *
 * @param stdClass $ejsapp
 * @return void
 */
function prepare_ejs_file($ejsapp) {
    global $DB,$CFG;

    function delete_outdated_file($storedfile, $temp_file, $folderpath) {
        // We compare the content of the linked file with the content of the file in the jarfiles folder:
        if ($storedfile->get_contenthash() != $temp_file->get_contenthash()) { //if they are not the same...
            // Delete the files in jarfiles directory in order to replace them with the content of $storedfile
            delete_recursively($folderpath);
            if (!file_exists($folderpath)) mkdir($folderpath, 0700);
            // Delete $temp_file from Moodle filesystem
            $temp_file->delete();
            return true;
        } else { // If the file exists and matches the one configured in the ejsapp activity, do nothing
            return false;
        }
    }

    function create_temp_file($contextid, $ejsappid, $filename, $fs, $temp_filepath) {
        $fileinfo = array(
            'contextid' => $contextid,
            'component' => 'mod_ejsapp',
            'filearea' => 'tmp_jarfiles',
            'itemid' => $ejsappid,
            'filepath' => '/',
            'filename' => $filename);
        return $temp_file = $fs->create_file_from_pathname($fileinfo, $temp_filepath);
    }

    // We first get the jar/zip file configured in the ejsapp activity and stored in the filesystem
    $file_records = $DB->get_records('files', array('component'=>'mod_ejsapp', 'filearea'=>'jarfiles', 'itemid'=>$ejsapp->id), 'filesize DESC');
    $file_record = reset($file_records);
    if ($file_record) {
        $fs = get_file_storage();
        $storedfile = $fs->get_file_by_id($file_record->id);

        // <In case it is an alias to an external repository>
        //$storedfile->sync_external_file(); // Not doing what we expect for non-image files... we need a workaround
        if (class_exists('repository_filesystem')) {
            if (!is_null($file_record->referencefileid)) {
                $repository_instance_id = $DB->get_field('files_reference', 'repositoryid', array('id' => $file_record->referencefileid));
                $repository_type_id = $DB->get_field('repository_instances', 'typeid', array('id' => $repository_instance_id));
                if ($DB->get_field('repository', 'type', array('id' => $repository_type_id)) == 'filesystem') {
                    $repository = repository_filesystem::get_instance($repository_instance_id);
                    $filepath = $repository->get_rootpath() . ltrim($storedfile->get_reference(), '/');
                    $contenthash = sha1_file($filepath);
                    if ($storedfile->get_contenthash() == $contenthash) {
                        // File did not change since the last synchronisation.
                        $filesize = filesize($filepath);
                    } else {
                        // Copy file into moodle filepool (used to generate an image thumbnail).
                        list($contenthash, $filesize, $newfile) = $fs->add_file_to_pool($filepath);
                    }
                    $storedfile->set_synchronized($contenthash, $filesize);
                }
            }
        }
        // </In case it is an alias to an external repository>

        $codebase = '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
        $folderpath = $CFG->dirroot . $codebase;
        $ext = pathinfo($file_record->filename, PATHINFO_EXTENSION);
        $filepath = $folderpath . $file_record->filename;
        if (file_exists($filepath)) { // if file in jarfiles exists...
            // We get the file stored in Moodle filesystem for the file in jarfiles, compare it and delete it if it is outdated
            $tmp_file_record = $DB->get_record('files', array('filename' => $file_record->filename, 'component' => 'mod_ejsapp', 'filearea' => 'tmp_jarfiles', 'itemid' => $ejsapp->id));
            if ($tmp_file_record) { // the file exists in jarfiles and in Moodle filesystem
                $temp_file = $fs->get_file_by_id($tmp_file_record->id);
            } else { // the file exists in jarfiles but not in Moodle filesystem (can happen with older versions of ejsapp plugins that have been updated recently or after duplicating or restoring an ejsapp activity)
                $temp_file = create_temp_file($file_record->contextid, $ejsapp->id, $file_record->filename, $fs, $filepath);
            }
            $delete = delete_outdated_file($storedfile, $temp_file, $folderpath);
            if (!$delete) return; //If files are the same, we have finished
        } else { // if file in jarfiles doesn't exists... (this should never happen actually, but just in case...)
            // We create the directories in jarfiles to put inside $storedfile
            $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/';
            if (!file_exists($path)) mkdir($path, 0700);
            $path .= $ejsapp->course . '/';
            if (!file_exists($path)) mkdir($path, 0700);
            if (!file_exists($folderpath)) mkdir($folderpath, 0700);
        }

        // We copy the content of storedfile to jarfiles and add it to the file storage
        $storedfile->copy_content_to($filepath);
        create_temp_file($file_record->contextid, $ejsapp->id, $ejsapp->applet_name, $fs, $filepath);

        // We need to do a few more things depending on whether it is a Java applet or Javascript:
        if ($ext == 'jar') {
            modifications_for_java($filepath, $ejsapp, $storedfile, $file_record, false);
        } else {
            modifications_for_javascript($filepath, $ejsapp, $folderpath, $codebase);
        }
        $DB->update_record('ejsapp', $ejsapp);
    }
}