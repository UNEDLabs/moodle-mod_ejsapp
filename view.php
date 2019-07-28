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
require_once('locallib.php');

global $USER, $DB, $CFG, $PAGE, $OUTPUT;
$CFG->cachejs = false;
$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
$colsessionid = optional_param('colsession', null, PARAM_INT);
$statefile = optional_param('state_file', null, PARAM_TEXT);
$recfile = optional_param('rec_file', null, PARAM_TEXT);
$blkfile = optional_param('blk_file', null, PARAM_TEXT);

$datafiles = array($statefile, $recfile, $blkfile);

if (!is_null($colsessionid)) {
    $collabsession = $DB->get_record('ejsapp_collab_sessions', array('id' => $colsessionid));
    if (isset($collabsession->localport)) {
        require_once(dirname(dirname(dirname(__FILE__))) . '/blocks/ejsapp_collab_session/manage_collab_db.php');

        $n = $collabsession->ejsapp;

        $collabinfo = new stdClass();
        $collabinfo->session = $colsessionid;
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
            $collabrecord->collaborative_session = $colsessionid;
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
if (file_exists($CFG->dirroot . $originalcss)) {
    $PAGE->requires->css($originalcss);
}
$customcss = $ejsapp->codebase . '_ejs_library/css/ejsapp.css';
if (file_exists($CFG->dirroot . $customcss)) {
    $PAGE->requires->css($customcss);
}

$renderer = $PAGE->get_renderer('mod_ejsapp');

// Javascript files and html injection for Blockly
$chartsdiv = '';
$experiments = '';
if ($ejsapp->class_file == '') {
    $context = context_user::instance($USER->id);
    // Pass the needed common parameters to the javascript application.
    $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'setCommonParameters',
        array($context->id, $USER->id, $ejsapp->id, $CFG->wwwroot . '/mod/ejsapp/upload_file.php', $CFG->wwwroot .
            '/mod/ejsapp/send_files_list.php', 'refreshEJSAppFBBut'));
    // If enabled, start recording of users interactions.
    if ($ejsapp->record == 1) {
        if ($DB->get_field('ejsapp', 'mouseevents', array('id' => $ejsapp->id)) == 1) {
            $mouseevents = true;
        } else {
            $mouseevents = false;
        }
        $PAGE->requires->js_call_amd('mod_ejsapp/ejss_interactions', 'recording', array($mouseevents));
    }
    // Full screen features
    $PAGE->requires->js_call_amd('mod_ejsapp/screenfull', 'init');
    $PAGE->requires->js_call_amd('mod_ejsapp/activity_interactions', 'fullScreen');

    $blocklyconf = json_decode($ejsapp->blockly_conf);
    if ($blocklyconf[0] == 1) {
        // Required libraries for blockly
        $PAGE->requires->js('/mod/ejsapp/vendor/blockly/blockly_compressed.js', true);
        $PAGE->requires->js('/mod/ejsapp/vendor/blockly/blocks_compressed.js', true);
        $PAGE->requires->js('/mod/ejsapp/vendor/blockly/javascript_compressed.js', true);
        if (file_exists('/mod/ejsapp/vendor/blockly/msg/js/' . current_language() . '.js')) {
            $PAGE->requires->js('/mod/ejsapp/vendor/blockly/msg/js/' . current_language() . '.js', true);
        } else {
            $PAGE->requires->js('/mod/ejsapp/vendor/blockly/msg/js/en.js', true);
        }
        $PAGE->requires->js('/mod/ejsapp/vendor/js-interpreter/acorn.js', true);
        $PAGE->requires->js('/mod/ejsapp/vendor/js-interpreter/interpreter.js', true);
        $PAGE->requires->js('/mod/ejsapp/vendor/ace/ace.js', true);
        $PAGE->requires->js('/lib/amd/src/chartjs-lazy.js', true);
        $PAGE->requires->js('/mod/ejsapp/addon/configuration.js');
        $PAGE->requires->js('/mod/ejsapp/addon/charts.js');
        $PAGE->requires->js('/mod/ejsapp/addon/blockly.js');
        $PAGE->requires->js('/mod/ejsapp/addon/blocks.js');
        $PAGE->requires->js('/mod/ejsapp/addon/javascript.js');
        $PAGE->requires->js('/mod/ejsapp/addon/execution.js');
        $PAGE->requires->js('/mod/ejsapp/addon/toolbox.js');
        if (file_exists('/mod/ejsapp/addon/lang/' . current_language() . '.js')) {
            $PAGE->requires->js('/mod/ejsapp/addon/lang/' . current_language() . '.js');
        } else {
            $PAGE->requires->js('/mod/ejsapp/addon/lang/en.js');
        }

        $chartsdiv = $renderer->ejsapp_charts();
        $controldiv = $renderer->ejsapp_controlbar();
        $blocklydiv = $renderer->ejsapp_blockly();
        $logdiv = $renderer->ejsapp_log();

        // Join HTML divs for placing blockly related elements in a single one.
        $experiments = $controldiv . $blocklydiv . $logdiv;

        // If required, create the javascript file with the configuration for using blockly.
        //create_blockly_configuration($ejsapp);
    }

    // Check if there are variables configured to be personalized in this EJSApp.
    $personalvarsinfo = personalize_vars($ejsapp, $USER, false);
}

// Output starts here.
echo $OUTPUT->header();
if ($ejsapp->intro) { // If some text was written, show the intro.
    echo $OUTPUT->box(format_module_intro('ejsapp', $ejsapp, $cm->id), 'generalbox mod_introbox', 'ejsappintro');
}

// For logging purposes.
$action = 'view';
$accessed = false;
$checkactivity = intval(get_config('mod_ejsapp', 'check_activity'));

// Check the access conditions, depending on whether sarlab and/or the ejsapp booking system are being used or not and
// whether the ejsapp instance is a remote lab or not.
$remlabinfo = null;
if (($ejsapp->is_rem_lab == 0)) { // Virtual lab.
    $accessed = true;
    echo html_writer::div($renderer->ejsapp_lab($ejsapp, $remlabinfo, $datafiles, $collabinfo, $personalvarsinfo) .
        $chartsdiv, 'labchart');
    echo $experiments;
} else { // Remote lab.
    $remlabaccess = remote_lab_access_info($ejsapp, $course);
    $remlabconf = $remlabaccess->remlab_conf;
    $repeatedlabs = $remlabaccess->repeated_ejsapp_labs;
    $sarlabinstance = is_practice_in_sarlab($remlabconf->practiceintro);
    if ($remlabaccess->allow_free_access && $remlabaccess->operative) {
        // Admins and teachers, not using ejsappbooking or free access remote lab, AND the remote lab is operative.
        $remlabtime = remote_lab_use_time_info($repeatedlabs, $ejsapp);
        $maxusetime = $remlabtime->max_use_time;
        // Get the lab use status.
        $labstatus = $remlabconf->usestate;
        // Determine the waiting time.
        $waittime = get_wait_time($remlabconf, $remlabtime->time_first_access, $remlabtime->time_last_access,
            $remlabtime->max_use_time, $remlabtime->reboottime, $checkactivity);
        if ($labstatus == 'available' || ($labstatus == 'rebooting' && $waittime <= 0)) { // Lab is available.
            if ($sarlabinstance !== false) {
                // Check if there is a booking done by this user and obtain the needed information for Sarlab in case it is used.
                $remlabinfo = check_users_booking($DB, $USER, $ejsapp, date('Y-m-d H:i:s'), $sarlabinstance,
                    $remlabaccess->labmanager, $maxusetime);
                if (is_null($remlabinfo)) {
                    $expsyst2pract = $DB->get_record('block_remlab_manager_exp2prc', array('ejsappid' => $ejsapp->id,
                        'practiceid' => 1));
                    $remlabinfo = define_remlab($sarlabinstance, 0, $expsyst2pract->practiceintro,
                        $remlabaccess->labmanager, $maxusetime);
                }
            }
            $accessed = true;
            echo html_writer::div($renderer->ejsapp_lab($ejsapp, $remlabinfo, $datafiles, $collabinfo, $personalvarsinfo) .
                $chartsdiv, 'labchart');
            echo $experiments;
        } else { // Lab is in use or rebooting.
            if (false) { // TODO: Check if the lab supports collaborative access.
                // Teacher can still access in collaborative mode.
                echo $OUTPUT->box(get_string('collab_access', 'ejsapp'));
                if ($sarlabinstance !== false) {
                    $remlabinfo = define_remlab($sarlabinstance, true, 'NULL',
                        $remlabaccess->labmanager, $maxusetime);
                }
                echo html_writer::div($renderer->ejsapp_lab($ejsapp, $remlabinfo, $datafiles, $collabinfo, $personalvarsinfo) .
                    $chartsdiv, 'labchart');
                echo $experiments;
                $action = 'collab_view';
            } else { // No access.
                $userwithbooking = check_anyones_booking($DB, $ejsapp);
                if ($userwithbooking !== '') {
                    $endtime = check_last_valid_booking($DB, $userwithbooking, $ejsapp->id);
                    $remlabtime->max_use_time = strtotime($endtime) - time();
                }
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
                // Remote lab freely accessible from the current course but with an active booking made by someone in a different course.
                echo $OUTPUT->box(get_string('booked_lab', 'ejsapp'));
                $action = 'booked_lab';
            } else { // Other cases.
                // Getting the maximum time the user is allowed to use the remote lab.
                $bookingendtime = check_last_valid_booking($DB, $USER->username, $ejsapp->id);
                $bookingendtimeunix = strtotime($bookingendtime);
                $maxusetime = $bookingendtimeunix - time(); // In seconds.
                // Check if there is a booking done by this user and obtain the needed information for Sarlab in case it is used.
                $remlabinfo = check_users_booking($DB, $USER, $ejsapp, date('Y-m-d H:i:s'), $sarlabinstance,
                    $remlabaccess->labmanager, $maxusetime);
                if (!is_null($remlabinfo)) { // The user has an active booking -> he can access the lab.
                    $remlabtime = remote_lab_use_time_info($repeatedlabs, $ejsapp);
                    $maxusetime = $remlabtime->max_use_time;
                    // Get the lab use status.
                    $labstatus = $remlabconf->usestate;
                    // Determine the waiting time.
                    $waittime = get_wait_time($remlabconf, $remlabtime->time_first_access, $remlabtime->time_last_access,
                        $remlabtime->max_use_time, $remlabtime->reboottime, $checkactivity);
                    if ($labstatus == 'available' || ($labstatus == 'rebooting' && $waittime <= 0)) { // Lab is available.
                        $accessed = true;
                        echo html_writer::div($renderer->ejsapp_lab($ejsapp, $remlabinfo, $datafiles, $collabinfo,
                                $personalvarsinfo) . $chartsdiv, 'labchart');
                        echo $experiments;
                    } else {
                        echo $OUTPUT->box(get_string('lab_in_use', 'ejsapp'));
                        $action = 'need_to_wait';
                    }
                } else { // No active booking.
                    echo $OUTPUT->box(get_string('no_booking', 'ejsapp'));
                    if (false) {    // TODO: Check if the lab supports collaborative access.
                        // Student can still access in collaborative mode.
                        echo $OUTPUT->box(get_string('collab_access', 'ejsapp'));
                        $remlabinfo = define_remlab($sarlabinstance, true, 'NULL',
                            $remlabaccess->labmanager, $maxusetime);
                        echo html_writer::div($renderer->ejsapp_lab($ejsapp, $remlabinfo, $datafiles, $collabinfo,
                                $personalvarsinfo) . $chartsdiv, 'labchart');
                        echo $experiments;
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
        if ($DB->record_exists('block', array('name' => 'remlab_manager')) && isset($remlabconf)) {
            $remlabconf->usestate = 'in use';
            $result = $DB->update_record('block_remlab_manager_conf', $remlabconf);
        }
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

// Buttons to close or leave collab sessions.
if (isset($collabsession)) {
    $url = $CFG->wwwroot . "/blocks/ejsapp_collab_session/close_collab_session.php?session=$colsessionid&courseid={$course->id}";
    if ($USER->id == $collabsession->master_user) {
        $text = get_string('closeMasSessBut', 'block_ejsapp_collab_session');
    } else {
        $text = get_string('closeStudSessBut', 'block_ejsapp_collab_session');
    }
    $button = html_writer::empty_tag('input', array('type' => 'button', 'name' => 'close_session', 'value' => $text,
        'onClick' => "window.location.href = '$url'"));
    echo $button;
}

// If some text was written, show it.
if ($ejsapp->appwording) {
    $formatoptions = new stdClass;
    $formatoptions->noclean = true;
    $formatoptions->overflowdiv = true;
    $formatoptions->context = $modulecontext;
    $content = format_text($ejsapp->appwording, $ejsapp->appwordingformat, $formatoptions);
    echo $OUTPUT->box($content, 'generalbox center clearfix');
}

// Monitor the time spent by a user in the activity.
if ($accessed) {
    // Monitoring for how long the user works with the lab and checking she does not exceed the maximum time allowed to
    // work with the remote lab.
    $ejsappname = urlencode($ejsapp->name);
    $params = '?courseid=' . $course->id . '&activityid=' . $ejsapp->id .
        '&cmid=' . $cm->id . '&ejsappname=' . urlencode($ejsappname) . '&userid=' . $USER->id;
    $urllog = $CFG->wwwroot . '/mod/ejsapp/add_to_log.php' . $params;
    $urlleave = $CFG->wwwroot . '/mod/ejsapp/leave_or_kick_out.php' . $params;
    if ($ejsapp->is_rem_lab == 0) {
        $PAGE->requires->js_call_amd('mod_ejsapp/activity_interactions', 'addLog', array($urllog, $urlleave,
            intval($ejsapp->is_rem_lab), 'EJsS', $checkactivity));
    } else {
        $PAGE->requires->js_call_amd('mod_ejsapp/activity_interactions', 'addLog', array($urllog, $urlleave,
            intval($ejsapp->is_rem_lab), 'EJsS', $checkactivity, $maxusetime));
    }
    $PAGE->requires->js_call_amd('mod_ejsapp/onclose', 'onclose', array($urlleave));
} else if ($action == 'booked_lab' || $action == 'need_to_wait') {
    // Remote lab not accessible by the user at the present moment.
    $url = $CFG->wwwroot . '/mod/ejsapp/countdown.php?ejsappid=' . $ejsapp->id . '&check_activity=' . $checkactivity;
    $htmlid = "timecountdown";
    echo $OUTPUT->box(html_writer::div('', '', array('id' => $htmlid)));
    $PAGE->requires->js_call_amd('mod_ejsapp/activity_interactions', 'countdown', array($url, $htmlid,
        $waittime, $checkactivity, ' ' . get_string('seconds', 'ejsapp'),
        get_string('refresh', 'ejsapp')));
}

// Finish the page.
echo $OUTPUT->footer();