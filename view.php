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
        if ($collabsession->enlargeport != 0) {
            $collabinfo->enlargeport = $collabsession->enlargeport;
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
    $cm = get_coursemodule_from_instance('ejsapp', $ejsapp->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

$PAGE->set_cm($cm, $course, $ejsapp); // Set's up global $COURSE.
$modulecontext = context_module::instance($cm->id);
$PAGE->set_context($modulecontext);

require_login($course, true, $cm);
require_capability('mod/ejsapp:view', $modulecontext);

// Completion on view.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Print the page header.
$PAGE->set_url('/mod/ejsapp/view.php', array('id' => $cm->id));
$PAGE->set_title($ejsapp->name);
$PAGE->set_title($ejsapp->name);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
if ($CFG->version < 2016090100) {
    $PAGE->set_button($OUTPUT->update_module_button($cm->id, 'ejsapp'));
}

// For logging purposes.
$action = 'view';
$accessed = false;
$checkactivity = intval(get_config('mod_ejsapp', 'check_activity'));

// Other data.
$remlabinfo = null;
$message1 = '';
$message2 = '';
$personalvarsinfo = '';

// Check the access conditions, depending on whether myFrontier and/or the ejsapp booking system are being used or not and
// whether the ejsapp instance is a remote lab or not.
if (($ejsapp->is_rem_lab == 0)) { // Virtual lab.
    $accessed = true;
} else {
    $remlabaccess = remote_lab_access_info($ejsapp, $course);
    $remlabconf = $remlabaccess->remlab_conf;
    $repeatedlabs = $remlabaccess->repeated_ejsapp_labs;
    $myFrontierinstance = is_practice_in_enlarge($remlabconf->practiceintro);
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
            if ($myFrontierinstance !== false) {
                // Check if there is a booking from this user and obtain the information for myFrontier in case it is used.
                $remlabinfo = check_users_booking($DB, $USER, $ejsapp, date('Y-m-d H:i:s'), $myFrontierinstance,
                    $remlabaccess->labmanager, $maxusetime);
                if (is_null($remlabinfo)) {
                    $expsyst2pract = $DB->get_record('block_remlab_manager_exp2prc', array('ejsappid' => $ejsapp->id,
                        'practiceid' => 1));
                    $remlabinfo = define_remlab($myFrontierinstance, 0, $expsyst2pract->practiceintro,
                        $remlabaccess->labmanager, $maxusetime);
                }
            }
            $accessed = true;
        } else { // Lab is in use or rebooting.
            if (false) { // TODO: Check if the lab and course support collaborative access.
                // Teachers can still access in collaborative mode.
                $message1 = $OUTPUT->box(get_string('collab_access', 'ejsapp'));
                if ($myFrontierinstance !== false) {
                    $remlabinfo = define_remlab($myFrontierinstance, true, 'NULL',
                        $remlabaccess->labmanager, $maxusetime);
                }
                $action = 'collab_view';
            } else { // No access.
                $userwithbooking = check_anyones_booking($DB, $ejsapp);
                if ($userwithbooking !== '') {
                    $endtime = check_last_valid_booking($DB, $userwithbooking, $ejsapp->id);
                    $remlabtime->max_use_time = strtotime($endtime) - time();
                }
                $message1 = $OUTPUT->box(get_string('lab_in_use', 'ejsapp'));
                $action = 'need_to_wait';
            }
        }
    } else { // Students trying to access a remote lab with restricted access OR remote lab not operative.
        if (!$remlabaccess->operative) { // Remote lab not operative.
            $message1 = $OUTPUT->box(get_string('inactive_lab', 'ejsapp'));
            $action = 'inactive_lab';
        } else {    // Students trying to access a remote lab with restricted access.
            if (!substr($USER->username, 0, 20) === "enrol_lti_enrol_lti_") { // Prevent double-LTI access!
                if ($remlabaccess->booking_info['active_booking']) {
                    // Remote lab freely accessible from the current course but with an active booking made by someone in a different course.
                    $message1 = $OUTPUT->box(get_string('booked_lab', 'ejsapp'));
                    $action = 'booked_lab';
                } else { // Other cases.
                    // Getting the maximum time the user is allowed to use the remote lab.
                    $bookingendtime = check_last_valid_booking($DB, $USER->username, $ejsapp->id);
                    $bookingendtimeunix = strtotime($bookingendtime);
                    $maxusetime = $bookingendtimeunix - time(); // In seconds.
                    // Check if there is a booking done by this user and obtain the needed information for myFrontier in case it is used.
                    $remlabinfo = check_users_booking($DB, $USER, $ejsapp, date('Y-m-d H:i:s'), $myFrontierinstance,
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
                        } else {
                            $message = $OUTPUT->box(get_string('lab_in_use', 'ejsapp'));
                            $action = 'need_to_wait';
                        }
                    } else { // No active booking.
                        $message1 = $OUTPUT->box(get_string('no_booking', 'ejsapp'));
                        if (false) { // TODO: Check if the lab and course support collaborative access.
                            // Students can still access in collaborative mode.
                            $message2 = $OUTPUT->box(get_string('collab_access', 'ejsapp'));
                            $remlabinfo = define_remlab($myFrontierinstance, true, 'NULL',
                                $remlabaccess->labmanager, $maxusetime);
                            $action = 'collab_view';
                        } else { // No access.
                            $message2 = $OUTPUT->box(get_string('check_bookings', 'ejsapp'));
                            $action = 'need_to_book';
                        }
                    }
                }
            } else {
                $message2 = $OUTPUT->box(get_string('forbid_lti', 'ejsapp'));
                $action = 'need_to_book'; //TODO: Replace with event for double-lti access
            }
        }
    }
}

$renderer = $PAGE->get_renderer('mod_ejsapp');

// Javascript files and html injection for Blockly
$chartsdiv = '';
$experiments = '';
if (pathinfo($ejsapp->main_file,PATHINFO_EXTENSION) != 'jar' && $accessed) { // Javascript
    $fs = get_file_storage();
    $filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'content',
        'itemid' => $ejsapp->id, 'filename' => 'ejss.css'), 'filesize DESC');
    if (!empty($filerecords)) {
        $filerecord = reset($filerecords);
        $file = $fs->get_file_by_id($filerecord->id);
        if ($file) {
            $originalcss = "/pluginfile.php/" . $file->get_contextid() . "/" . $file->get_component() . "/content/" .
                $file->get_itemid() . "/_ejs_library/css/" . $file->get_filename();
        }
    } else {
        $filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'content',
            'itemid' => $ejsapp->id, 'filename' => 'ejsSimulation.css'), 'filesize DESC');
        if (!empty($filerecords)) {
            $filerecord = reset($filerecords);
            $file = $fs->get_file_by_id($filerecord->id);
            if ($file) {
                $originalcss = "/pluginfile.php/" . $file->get_contextid() . "/" . $file->get_component() . "/content/" .
                    $file->get_itemid() . "/_ejs_library/css/" . $file->get_filename();
            }
        }
    }
    if ($file) {
        $PAGE->requires->css($originalcss);
    }
    $filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'content',
        'itemid' => $ejsapp->id, 'filename' => 'ejsapp.css'), 'filesize DESC');
    if (!empty($filerecords)) {
        $filerecord = reset($filerecords);
        $file = $fs->get_file_by_id($filerecord->id);
        if ($file) {
            $customcss = "/pluginfile.php/" . $file->get_contextid() . "/" . $file->get_component() . "/content/" .
                $file->get_itemid() . "/_ejs_library/css/" . $file->get_filename();
            $PAGE->requires->css($customcss);
        }
    }

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
    $PAGE->requires->js_call_amd('mod_ejsapp/activity_interactions', 'fullScreen');

    $blocklyconf = json_decode($ejsapp->blockly_conf);
    if ($blocklyconf[0] == 1) {
        $PAGE->requires->js_call_amd('mod_ejsapp/blockly_conf', 'configureBlockly',
            array($ejsapp->is_rem_lab, $blocklyconf[1], $blocklyconf[2], $blocklyconf[3]));
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
        $controldiv = $renderer->ejsapp_controlbar($blocklyconf);
        $blocklydiv = $renderer->ejsapp_blockly();
        $logdiv = $renderer->ejsapp_log();

        // Join HTML divs for placing blockly related elements in a single one.
        $experiments = $controldiv . $blocklydiv . $logdiv;
    }

    // Include the three required javascript files for EjsS.
    try {
        $filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'content',
            'itemid' => $ejsapp->id, 'filename' => 'ejsS.v1.min.js'), 'filesize DESC');
        $filerecord = reset($filerecords);
        $file = $fs->get_file_by_id($filerecord->id);
        $pathfile = "/pluginfile.php/" . $file->get_contextid() . "/" . $file->get_component() . "/content/" .
            $file->get_itemid() . "/_ejs_library/" . $file->get_filename();
        $PAGE->requires->js(new moodle_url($pathfile));
    } catch (dml_exception $e) {
    }

    try {
        $filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'content',
            'itemid' => $ejsapp->id, 'filename' => 'textresizedetector.js'), 'filesize DESC');
        $filerecord = reset($filerecords);
        $file = $fs->get_file_by_id($filerecord->id);
        $pathfile = "/pluginfile.php/" . $file->get_contextid() . "/" . $file->get_component() . "/content/" .
            $file->get_itemid() . "/_ejs_library/scripts/" . $file->get_filename();
        $PAGE->requires->js(new moodle_url($pathfile));
    } catch (dml_exception $e) {
    }

    try {
        $filerecords = $DB->get_records('files', array('component' => 'mod_ejsapp', 'filearea' => 'content',
            'itemid' => $ejsapp->id, 'filename' => $ejsapp->main_file . '.js'), 'filesize DESC');
        $filerecord = reset($filerecords);
        $file = $fs->get_file_by_id($filerecord->id);
        $pathfile = "/pluginfile.php/" . $file->get_contextid() . "/" . $file->get_component() . "/content/" .
            $file->get_itemid() . "/" . $file->get_filename();
        $PAGE->requires->js(new moodle_url($pathfile));
    } catch (dml_exception $e) {
    }

    // Check if there are variables configured to be personalized in this EJSApp.
    $personalvarsinfo = personalize_vars($ejsapp, $USER, false);
}

// Output starts here.
echo $OUTPUT->header();

if ($accessed) {
    if ($ejsapp->intro) {
        echo $OUTPUT->box(format_module_intro('ejsapp', $ejsapp, $cm->id), 'generalbox mod_introbox', 'ejsappintro');
    }

    echo html_writer::div($renderer->ejsapp_lab($ejsapp, $remlabinfo, $datafiles, $collabinfo, $personalvarsinfo) .
        $chartsdiv, 'labchart');
    echo $experiments;

    if ($ejsapp->appwording) {
        $formatoptions = new stdClass;
        $formatoptions->noclean = true;
        $formatoptions->overflowdiv = true;
        $formatoptions->context = $modulecontext;
        $content = format_text($ejsapp->appwording, $ejsapp->appwordingformat, $formatoptions);
        echo $OUTPUT->box($content, 'generalbox center clearfix');
    }

    // Buttons to close or leave collab sessions.
    if (isset($collabsession)) {
        $url = $CFG->wwwroot . "/blocks/ejsapp_collab_session/close_collab_session.php?session=$colsessionid
        &courseid={$course->id}";
        if ($USER->id == $collabsession->master_user) {
            $text = get_string('closeMasSessBut', 'block_ejsapp_collab_session');
        } else {
            $text = get_string('closeStudSessBut', 'block_ejsapp_collab_session');
        }
        $button = html_writer::empty_tag('input', array('type' => 'button', 'name' => 'close_session',
            'value' => $text, 'onClick' => "window.location.href = '$url'"));
        echo $button;
    }
} else {
    echo $message1;
    echo $message2;
}

// Add the access to the log, taking into account the action; i.e. whether the user could access (view) the lab or not
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