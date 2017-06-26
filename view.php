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
 * Prints a particular instance of EJSApp
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('generate_embedding_code.php');

global $USER, $DB, $CFG, $PAGE, $OUTPUT;

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
$statefile = optional_param('state_file', null, PARAM_TEXT);
$cntfile = optional_param('cnt_file', null, PARAM_TEXT);
$recfile = optional_param('rec_file', null, PARAM_TEXT);
$blkfile = optional_param('blk_file', null, PARAM_TEXT);
$sessionid = optional_param('colsession', null, PARAM_INT);

$datafiles = array($statefile, $cntfile, $recfile, $blkfile);

if (!is_null($sessionid)) {
    $collabsession = $DB->get_record('ejsapp_collab_sessions', array('id' => $sessionid));
    if (isset($collabsession->localport)) {
        require_once(dirname(dirname(dirname(__FILE__))) . '/blocks/ejsapp_collab_session/manage_collab_db.php');

        $n = $collabsession->ejsapp;

        $collabinfo = new stdClass();
        $collabinfo->session = $sessionid;
        $collabinfo->ip = $collabsession->ip;
        $collabinfo->localport = $collabsession->localport;
        if ($collabsession->sarlabport != 0) {
            $collabinfo->sarlabport = $collabsession->sarlabport;
        }
        if (am_i_master_user()) {
            $collabinfo->director = $collabsession->id;
        } else if (!$DB->record_exists('ejsapp_collab_acceptances', array('accepted_user' => $USER->id))) {
            $collabrecord = new stdClass();
            $collabrecord->accepted_user = $USER->id;
            $collabrecord->collaborative_session = $sessionid;
            $DB->insert_record('ejsapp_collab_acceptances', $collabrecord);
        }
    } else {
        print_error('cantJoinSessionErr2', 'block_ejsapp_collab_session');
    }
} else {
    $n = optional_param('n', null, PARAM_INT);
    $collabinfo = null;
}

if ($id) {
    $cm = get_coursemodule_from_id('ejsapp', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $ejsapp = $DB->get_record('ejsapp', array('id' => $cm->instance), '*', MUST_EXIST);
} else if (isset($n)) {
    $ejsapp = $DB->get_record('ejsapp', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $ejsapp->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('ejsapp', $ejsapp->id, $course->id, false,
        MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

// Completion on view.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Print the page header.
$PAGE->set_cm($cm, $course, $ejsapp);
$PAGE->set_context($modulecontext);
$PAGE->set_url('/mod/ejsapp/view.php', array('id' => $cm->id));
$PAGE->set_title($ejsapp->name);
$PAGE->set_title($ejsapp->name);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
if ($CFG->version < 2016090100) {
    $PAGE->set_button($OUTPUT->update_module_button($cm->id, 'ejsapp'));
}

// Set CSS style for javascript ejsapps.
$originalcss = $ejsapp->codebase . '_ejs_library/css/ejss.css';
if (!file_exists($CFG->dirroot . $originalcss)) {
    $originalcss = $ejsapp->codebase . '_ejs_library/css/ejsSimulation.css';
}
$customcss = $ejsapp->codebase . '_ejs_library/css/ejsapp.css';
if (file_exists($CFG->dirroot . $customcss)) {
    $PAGE->requires->css($customcss);
} else if (file_exists($CFG->dirroot . $originalcss)) {
    $PAGE->requires->css($originalcss);
}

// Output starts here.
echo $OUTPUT->header();
if ($ejsapp->intro) { // If some text was written, show the intro.
    echo $OUTPUT->box(format_module_intro('ejsapp', $ejsapp, $cm->id), 'generalbox mod_introbox', 'ejsappintro');
}

// Check if there are variables configured to be personalized in this EJSApp.
$personalvarsinfo = personalize_vars($ejsapp, $USER, false);

// If required, create the javascript file with the configuration for using blockly.
create_blockly_configuration($ejsapp);

// For logging purposes.
$action = 'view';
$accessed = false;

// Define the div section for charts.
$containerdiv = html_writer::div(html_writer::tag('ul',''), 'charts', array('id' => 'container-1'));
$chartsdiv = html_writer::div($containerdiv, 'charts', array('id' => 'chart_div'));

// Check the access conditions, depending on whether sarlab and/or the ejsapp booking system are being used or not and
// whether the ejsapp instance is a remote lab or not.
$sarlabinfo = null;
if (($ejsapp->is_rem_lab == 0)) { // Virtual lab.
    $accessed = true;
    $maxusetime = 604800; // High enough number... although it is never used in the case of a virtual lab.
    prepare_ejs_file($ejsapp);
    echo html_writer::div(generate_embedding_code($ejsapp, $sarlabinfo, $datafiles, $collabinfo, $personalvarsinfo) .
        $chartsdiv, 'labchart');
} else { // Remote lab.
    $remlabaccess = remote_lab_access_info($ejsapp, $course);
    $remlabconf = $remlabaccess->remlab_conf;
    $repeatedlabs = $remlabaccess->repeated_ejsapp_labs;
    if ($remlabaccess->allow_free_access && $remlabaccess->operative) {
        // Admins and teachers, not using ejsappbooking or free access remote lab, AND the remote lab is operative.
        $remlabtime = remote_lab_use_time_info($remlabconf, $repeatedlabs);
        $maxusetime = $remlabtime->max_use_time;
        // Get the lab use status.
        $labstatus = get_lab_status($remlabtime->time_information, $remlabconf->reboottime,
            get_config('mod_ejsapp', 'check_activity'));
        if ($labstatus == 'available') {
            if ($remlabconf->usingsarlab == 1) {
                // Check if there is a booking done by this user and obtain the needed information for Sarlab in case it is used.
                $sarlabinfo = check_users_booking($DB, $USER, $ejsapp, date('Y-m-d H:i:s'), $remlabconf->sarlabinstance,
                    $remlabaccess->labmanager, $maxusetime);
                if (is_null($sarlabinfo)) {
                    $expsyst2pract = $DB->get_record('block_remlab_manager_exp2prc', array('ejsappid' => $ejsapp->id,
                        'practiceid' => 1));
                    $sarlabinfo = define_sarlab($remlabconf->sarlabinstance, 0, $expsyst2pract->practiceintro,
                        $remlabaccess->labmanager, $maxusetime);
                }
            }
            $accessed = true;
            prepare_ejs_file($ejsapp);
            echo html_writer::div(generate_embedding_code($ejsapp, $sarlabinfo, $datafiles, $collabinfo, $personalvarsinfo) .
                $chartsdiv, 'labchart');
        } else {
            if (($remlabconf->usingsarlab == 1 && $remlabconf->sarlabcollab == 1)) {
                // Teacher can still access in collaborative mode.
                echo $OUTPUT->box(get_string('collab_access', 'ejsapp'));
                $sarlabinfo = define_sarlab($remlabconf->sarlabinstance, $remlabconf->sarlabcollab, 'NULL',
                    $remlabaccess->labmanager, $maxusetime);
                prepare_ejs_file($ejsapp);
                echo html_writer::div(generate_embedding_code($ejsapp, $sarlabinfo, $datafiles, $collabinfo, $personalvarsinfo) .
                    $chartsdiv, 'labchart');
                $action = 'collab_view';
            } else { // No access.
                echo $OUTPUT->box(get_string('lab_in_use', 'ejsapp'));
                $action = 'need_to_wait';
            }
        }
    } else { // Students trying to access a remote lab with restricted access OR remote lab not operative.
        if (!$remlabaccess->operative) { // Remote lab not operative.
            echo $OUTPUT->box(get_string('inactive_lab', 'ejsapp'));
            $action = 'inactive_lab';
        } else {    // Students trying to access a remote lab with restricted access.
            if ($remlabaccess->booking_info['active_booking']) {
                // Remote lab freely accessible from one course but with an active booking made by anyone in a different course.
                echo $OUTPUT->box(get_string('booked_lab', 'ejsapp'));
                $action = 'booked_lab';
            } else { // Other cases.
                // Getting the maximum time the user is allowed to use the remote lab.
                $bookingendtime = check_last_valid_booking($DB, $USER->username, $ejsapp->id);
                $bookingendtimeunix = strtotime($bookingendtime);
                $currenttime = time();
                $maxusetime = $bookingendtimeunix - $currenttime; // In seconds.
                // Check if there is a booking done by this user and obtain the needed information for Sarlab in case it is used.
                $sarlabinfo = check_users_booking($DB, $USER, $ejsapp, date('Y-m-d H:i:s'), $remlabconf->sarlabinstance,
                    $remlabaccess->labmanager, $maxusetime);
                if (!is_null($sarlabinfo)) { // The user has an active booking -> he can access the lab.
                    $accessed = true;
                    prepare_ejs_file($ejsapp);
                    echo html_writer::div(generate_embedding_code($ejsapp, $sarlabinfo, $datafiles, $collabinfo, $personalvarsinfo) .
                        $chartsdiv, 'labchart');
                } else { // No active booking.
                    echo $OUTPUT->box(get_string('no_booking', 'ejsapp'));
                    if (($remlabconf->usingsarlab == 1 && $remlabconf->sarlabcollab == 1)) {
                        // Student can still access in collaborative mode.
                        echo $OUTPUT->box(get_string('collab_access', 'ejsapp'));
                        $sarlabinfo = define_sarlab($remlabconf->sarlabinstance, $remlabconf->sarlabcollab, 'NULL',
                            $remlabaccess->labmanager, $maxusetime);
                        prepare_ejs_file($ejsapp);
                        echo html_writer::div(generate_embedding_code($ejsapp, $sarlabinfo, $datafiles, $collabinfo,
                                $personalvarsinfo) . $chartsdiv, 'labchart');
                        $action = 'collab_view';
                    } else { // No access.
                        echo $OUTPUT->box(get_string('check_bookings', 'ejsapp'));
                        $action = 'need_to_book';
                    }
                }
            }
        }
    }
}

// Add the access to the log, taking into account the action; i.e. whether the user could access (view) the lab or not.
switch ($action) {
    case 'view':
        $event = \mod_ejsapp\event\ejsapp_viewed::create(array(
            'objectid' => $ejsapp->id,
            'courseid' => $course->id,
            'userid' => $USER->id,
            'context' => $modulecontext,
            'other' => $ejsapp->name,
        ));
        break;
    case 'need_to_wait':
        $event = \mod_ejsapp\event\ejsapp_wait::create(array(
            'objectid' => $ejsapp->id,
            'courseid' => $course->id,
            'userid' => $USER->id,
            'context' => $modulecontext,
            'other' => $ejsapp->name,
        ));
        break;
    case 'need_to_book':
        $event = \mod_ejsapp\event\ejsapp_book::create(array(
            'objectid' => $ejsapp->id,
            'courseid' => $course->id,
            'userid' => $USER->id,
            'context' => $modulecontext,
            'other' => $ejsapp->name,
        ));
        break;
    case 'collab_view':
        $event = \mod_ejsapp\event\ejsapp_collab::create(array(
            'objectid' => $ejsapp->id,
            'courseid' => $course->id,
            'userid' => $USER->id,
            'context' => $modulecontext,
            'other' => $ejsapp->name,
        ));
        break;
    case 'inactive_lab':
        $event = \mod_ejsapp\event\ejsapp_inactive::create(array(
            'objectid' => $ejsapp->id,
            'courseid' => $course->id,
            'userid' => $USER->id,
            'context' => $modulecontext,
            'other' => $ejsapp->name,
        ));
        break;
    case 'booked_lab':
        $event = \mod_ejsapp\event\ejsapp_booked::create(array(
            'objectid' => $ejsapp->id,
            'courseid' => $course->id,
            'userid' => $USER->id,
            'context' => $modulecontext,
            'other' => $ejsapp->name,
        ));
        break;
}
$event->trigger();
// Add the access to the log, taking into account the action; i.e. whether the user could access (view) the lab or not.

// If some text was written, show it.
if ($ejsapp->appwording) {
    $formatoptions = new stdClass;
    $formatoptions->noclean = true;
    $formatoptions->overflowdiv = true;
    $formatoptions->context = $modulecontext;
    $content = format_text($ejsapp->appwording, $ejsapp->appwordingformat, $formatoptions);
    echo $OUTPUT->box($content, 'generalbox center clearfix');
}

// Blockly programming space for Javascript labs.
if ($accessed && $ejsapp->class_file == '') {
    $blocklyconf = json_decode($ejsapp->blockly_conf);
    if ($blocklyconf[0] == 1) {
        $PAGE->requires->js_call_amd('mod_ejsapp/jqueryui', 'init');
        $includejslibraries = html_writer::tag('script', '',
                array('src' => $ejsapp->codebase . 'configuration.js')) .
            html_writer::tag('script', '', array('src' => 'blockly/acorn_interpreter.js')) .
            html_writer::tag('script', '', array('src' => 'blockly/blockly_compressed.js')) .
            html_writer::tag('script', '', array('src' => 'blockly/blocks_compressed.js')) .
            html_writer::tag('script', '', array('src' => 'blockly/javascript_compressed.js')) .
            html_writer::tag('script', '', array('src' => 'blockly/blockly_addon.js')) .
            html_writer::tag('script', '', array('src' => 'charts/Chart.bundle.js')) .
            html_writer::tag('script', '', array('src' => 'charts/jquery.js')) .
            html_writer::tag('script', '', array('src' => 'charts/jquery-ui-1.9.2.custom.min.js')) .
            html_writer::tag('script', '', array('src' => 'blockly/GUI_functions.js')) .
            html_writer::tag('script', '', array('src' => 'charts/charts_functions.js')) .
            html_writer::tag('script', '', array('src' => 'blockly/API_functions.js'));
        if (strpos(current_language(), 'es') !== false) {
            $includejslibraries .= html_writer::tag('script', '', array('src' => 'blockly/es.js'));
        } else {
            $includejslibraries .= html_writer::tag('script', '', array('src' => 'blockly/en.js'));
        }
        echo html_writer::div($includejslibraries, 'blockly', array('id' => 'blocklyDiv'));
    }
}

// Buttons to close or leave collab sessions.
if (isset($collabsession)) {
    $url = $CFG->wwwroot . "/blocks/ejsapp_collab_session/close_collab_session.php?session=$sessionid&courseid={$course->id}";
    if ($USER->id == $collabsession->master_user) {
        $text = get_string('closeMasSessBut', 'block_ejsapp_collab_session');
    } else {
        $text = get_string('closeStudSessBut', 'block_ejsapp_collab_session');
    }
    $button = html_writer::empty_tag('input', array('type' => 'button', 'name' => 'close_session', 'value' => $text,
        'onClick' => "window.location.href = '$url'"));
    echo $button;
}

// Javascript features for monitoring the time spent by a user in the activity.
if ($accessed) {
    // Monitoring for how long the user works with the lab and checking she does not exceed the maximum time allowed to
    // work with the remote lab.
    $ejsappname = urlencode($ejsapp->name);
    $urllog = $CFG->wwwroot . '/mod/ejsapp/add_to_log.php?courseid=' . $course->id . '&activityid=' . $cm->id .
        '&ejsappname=' . $ejsappname . '&userid=' . $USER->id;
    $htmlid = "EJsS";
    $urlview = $CFG->wwwroot . '/mod/ejsapp/kick_out.php';
    $PAGE->requires->js_init_call('M.mod_ejsapp.init_add_log', array($urllog, $urlview, $ejsapp->is_rem_lab, $htmlid,
        get_config('mod_ejsapp', 'check_activity'), $maxusetime));
} else if ($action == 'booked_lab' || $action == 'need_to_wait') {
    // Remote lab not accessible by the user at the present moment.
    $remainingtime = get_remaining_time($remlabaccess->booking_info, $labstatus, $remlabtime->time_information,
        $remlabconf->reboottime, get_config('mod_ejsapp', 'check_activity'));
    $url = $CFG->wwwroot . '/mod/ejsapp/countdown.php?ejsappid=' . $ejsapp->id . '&courseid=' . $course->id .
        '&check_activity=' . get_config('mod_ejsapp', 'check_activity');
    $htmlid = "timecountdown";
    echo $OUTPUT->box(html_writer::div('', '', array('id' => $htmlid)));
    $PAGE->requires->js_init_call('M.mod_ejsapp.init_countdown', array($url, $htmlid, $remainingtime,
        get_config('mod_ejsapp', 'check_activity'), ' ' .
        get_string('seconds', 'ejsapp'), get_string('refresh', 'ejsapp')));
}

// Finish the page.
echo $OUTPUT->footer();