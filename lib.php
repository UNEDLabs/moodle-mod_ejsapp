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
 * Library of interface functions and constants for module ejsapp
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle are here.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('locallib.php');

/**
 *
 * Supported features by EJSApp
 *
 * @param constant $feature feature to be supported
 * @return boolean true if EJSApp supports the feature, false elsewhere
 *
 */
function ejsapp_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_CONTROLS_GRADE_VISIBILITY:
            return true;

        default:
            return null;
    }
}

/**
 *
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param string $data the data submitted from the reset course.
 * @return array status array
 *
 */
function ejsapp_reset_userdata($data) {
    return array();
}

/**
 *
 * List of view style log actions
 * @return array
 *
 */
function ejsapp_get_view_actions() {
    return array('view', 'view all');
}

/**
 *
 * List of update style log actions
 * @return array
 *
 */
function ejsapp_get_post_actions() {
    return array('update', 'add');
}

/**
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $ejsapp An object from the form in mod_form.php
 * @param object $mform
 * @return int The id of the newly inserted ejsapp record
 *
 */

function ejsapp_add_instance($ejsapp, $mform = null) {
    global $DB;

    $ejsapp->timecreated = time();
    $ejsapp->id = $DB->insert_record('ejsapp', $ejsapp);

    if ($mform) {
        $ejsapp->appwording = $ejsapp->ejsappwording['text'];
        $ejsapp->appwordingformat = $ejsapp->ejsappwording['format'];
    }

    $cmid = $ejsapp->coursemodule;
    $context = context_module::instance($cmid);
    $ejs_ok = update_ejsapp_files_and_tables($ejsapp, $context);

    if ($ejs_ok) {
        ejsapp_grade_item_update($ejsapp);
        if ($ejsapp->is_rem_lab == 1) { // Remote lab
            if ($ejsapp->remlab_manager) {
                $complete_pract_list = explode(';', $ejsapp->list_practices);
                $remlab_info = $DB->get_record('block_remlab_manager_conf', array('practiceintro' => $complete_pract_list[$ejsapp->practiceintro]));
                if ($remlab_info == null) {
                    $remlab_info = default_rem_lab_conf($ejsapp);
                    $DB->insert_record('block_remlab_manager_conf', $remlab_info);
                }
            }
            ejsapp_expsyst2pract($ejsapp);
            update_booking_table($ejsapp);
        }
    }

    return $ejsapp->id;
}

/**
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $ejsapp An object from the form in mod_form.php
 * @param object $mform
 * @return boolean Success/Fail
 *
 */
function ejsapp_update_instance($ejsapp, $mform=null) {
    global $DB;

    $ejsapp->timemodified = time();
    $ejsapp->id = $ejsapp->instance;

    if ($mform) {
        $ejsapp->appwording = $ejsapp->ejsappwording['text'];
        $ejsapp->appwordingformat = $ejsapp->ejsappwording['format'];
    }

    $cmid = $DB->get_field('course_modules', 'id', array('course'=>$ejsapp->course, 'instance'=>$ejsapp->id));
    $context = context_module::instance($cmid);

    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'jarfiles', $ejsapp->id);
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'jarfiles', $ejsapp->id);
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'tmp_jarfiles', $ejsapp->id);
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'tmp_jarfiles', $ejsapp->id);
    $ejs_ok = update_ejsapp_files_and_tables($ejsapp, $context);
    if ($ejs_ok) {
        ejsapp_grade_item_update($ejsapp);
        $is_ejsappbooking_installed = $DB->get_records('modules',array('name'=>'ejsappbooking'));
        $is_ejsappbooking_installed = !empty($is_ejsappbooking_installed);
        if ($ejsapp->is_rem_lab == 1) { // Remote lab
            if ($ejsapp->remlab_manager) {
                $complete_pract_list = explode(';', $ejsapp->list_practices);
                $remlab_info = $DB->get_record('block_remlab_manager_conf', array('practiceintro' => $complete_pract_list[$ejsapp->practiceintro]));
                if ($remlab_info == null) {
                    $remlab_info = default_rem_lab_conf($ejsapp);
                    $DB->insert_record('block_remlab_manager_conf', $remlab_info);
                }
                $DB->delete_records('block_remlab_manager_exp2prc', array('ejsappid' => $ejsapp->id));
                ejsapp_expsyst2pract($ejsapp);
            }
            if ($is_ejsappbooking_installed) update_booking_table($ejsapp);
        } else {
            if ($ejsapp->remlab_manager) $DB->delete_records('block_remlab_manager_exp2prc', array('ejsappid' => $ejsapp->id));
            if ($is_ejsappbooking_installed) {
                if ($DB->record_exists('ejsappbooking', array('course' => $ejsapp->course))) {
                    $DB->delete_records('ejsappbooking_usersaccess', array('ejsappid' => $ejsapp->id));
                    $DB->delete_records('ejsappbooking_remlab_access', array('ejsappid' => $ejsapp->id));
                }
            }
        }
    }

    return $ejsapp->id;
}

/**
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 *
 */
function ejsapp_delete_instance($id) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/filelib.php');

    if (!$ejsapp = $DB->get_record('ejsapp', array('id' => $id))) {
        return false;
    }

    $cmid = $DB->get_field('course_modules', 'id', array('course'=>$ejsapp->course, 'instance'=>$id));
    $context = context_module::instance($cmid);

    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'jarfiles', $id);
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'jarfiles', $id);
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'tmp_jarfiles', $id);
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'tmp_jarfiles', $id);

    $DB->delete_records('ejsapp', array('id' => $id));
    if ($ejsapp->is_rem_lab == 1) {
        $DB->delete_records('block_remlab_manager_exp2prc', array('ejsappid' => $id));
        // EJSApp booking system
        if($DB->record_exists('ejsappbooking', array('course'=>$ejsapp->course))) {
          $DB->delete_records('ejsappbooking_usersaccess', array('ejsappid' => $id));
          $DB->delete_records('ejsappbooking_remlab_access', array('ejsappid' => $id));
        }
    }

    if ($ejsapp->personalvars == 1) {
        $DB->delete_records('ejsapp_personal_vars', array('ejsappid' => $id));
    }

    ejsapp_grade_item_delete($ejsapp);

    // Delete recursively
    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $id;
    delete_recursively($path);
    return true;
}

/**
 * Create grade item for given $ejsapp
 *
 * @category grade
 * @param object $ejsapp object
 * @param mixed array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function ejsapp_grade_item_update($ejsapp, $grades = null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $params = array('itemname' => $ejsapp->name);

    if ($ejsapp->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $ejsapp->grade;
        $params['grademin']  = 0;

    } else if ($ejsapp->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$ejsapp->grade;

    } else {
        $params['gradetype'] = GRADE_TYPE_TEXT; // Allow text comments only.
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/ejsapp', $ejsapp->course, 'mod', 'ejsapp', $ejsapp->id, 0, $grades, $params);
}

/**
 * Delete grade item for given ejsapp
 *
 * @category grade
 * @param object $ejsapp object
 * @return int
 */
function ejsapp_grade_item_delete($ejsapp) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/ejsapp', $ejsapp->course, 'mod', '$ejsapp', $ejsapp->id, 0, null, array('deleted' => 1));
}

/**
 *
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $ejsapp
 *
 * @return object $result
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 */
function ejsapp_user_outline($course, $user, $mod, $ejsapp) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'ejsapp',
        'info'=>$ejsapp->name), 'time ASC')) {

        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new stdClass();
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    }
    return NULL;
}

/**
 *
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $ejsapp
 *
 * @return boolean
 *
 */
function ejsapp_user_complete($course, $user, $mod, $ejsapp) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'ejsapp',
        'info'=>$ejsapp->name), 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);

    } else {
        print_string('neverseen', 'ejsapp');
    }
}

/**
 *
 * Given a course and a time, this module should find recent activity
 * that has occurred in ejsapp activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @param object $course
 * @param $viewfullnames
 * @param $timestart
 * @return boolean false
 *
 */
function ejsapp_print_recent_activity($course, $viewfullnames, $timestart) {
    return false; //  True if anything was printed, otherwise false
}

/**
 *
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link newmodule_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 *
 */
function ejsapp_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
}

/**
 *
 * Prints single activity item prepared by {@see newmodule_get_recent_mod_activity()}
 *
 * @param $activity
 * @param int $courseid
 * @param $detail
 * @param $modnames
 * @param $viewfullnames
 * @return void
 *
 */
function ejsapp_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 *
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 *
 **/
function ejsapp_cron() {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/filter/multilang/filter.php');

    //Delete stored Sarlab keys that are one day old or more:
    $time = array(strtotime(date('Y-m-d H:i:s'))-86400);
    $DB->delete_records_select('block_remlab_manager_sb_keys', "creationtime < ?", $time);

    //Checking whether remote labs are operative or not (once per day):
    if (date('H') >= 8) {
        $ejsapp_remlabs_conf = $DB->get_records('block_remlab_manager_conf');
        foreach ($ejsapp_remlabs_conf as $ejsapp_remlab_conf) {
            $practiceintro = null;
            if ($ejsapp_remlab_conf->usingsarlab) {
                $practiceintro = $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro', array('ejsappid' => $ejsapp_remlab_conf->ejsappid));
            }
            $devices_info = new stdClass();
            $lab_state = ping($ejsapp_remlab_conf->ip, $ejsapp_remlab_conf->port, $ejsapp_remlab_conf->usingsarlab, $practiceintro);
            // Send e-mail to teachers if the remote lab state is not checkable or if it has passed from active to inactive:
            $rem_lab = $DB->get_record('ejsapp', array('id' => $ejsapp_remlab_conf->ejsappid));
            $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
            // TODO: Allow configuring which roles will receive the e-mails? (managers, non-editing teacher...) Use Moodle capabilities
            $context = context_course::instance($rem_lab->course);
            $multilang = new filter_multilang($context, array('filter_multilang_force_old' => 0));
            $send_mail = false;
            // Prepare e-mails' content and update lab state when checkable:
            $subject = '';
            $messagebody = '';
            if ($lab_state == 2) {  // Not checkable:
                $subject = get_string('mail_subject_lab_not_checkable', 'ejsapp');
                $messagebody = get_string('mail_content1_lab_not_checkable', 'ejsapp') . $multilang->filter($rem_lab->name) .
                    get_string('mail_content2_lab_not_checkable', 'ejsapp') . $ejsapp_remlab_conf->ip .
                    get_string('mail_content3_lab_not_checkable', 'ejsapp');
                $send_mail = true;
            } else {                // Active or inactive:
                if ($ejsapp_remlab_conf->active == 1 && $lab_state == 0) {  // Lab has passed from active to inactive
                    $subject = get_string('mail_subject_lab_down', 'ejsapp');
                    $messagebody = get_string('mail_content1_lab_down', 'ejsapp') . $multilang->filter($rem_lab->name) .
                        get_string('mail_content2_lab_down', 'ejsapp') . $ejsapp_remlab_conf->ip .
                        get_string('mail_content3_lab_down', 'ejsapp') . get_string('mail_content4_lab_down', 'ejsapp');
                    foreach ($devices_info as $device_info) {
                        if (!$device_info->alive) $messagebody .= $device_info->name . ', ' . $device_info->ip . "\r\n";
                    }
                    $send_mail = true;
                } else if ($ejsapp_remlab_conf->active == 0 && $lab_state == 1) { // Lab has passed from inactive to active
                    $subject = get_string('mail_subject_lab_up', 'ejsapp');
                    $messagebody = get_string('mail_content1_lab_up', 'ejsapp') . $multilang->filter($rem_lab->name) .
                        get_string('mail_content2_lab_up', 'ejsapp') . $ejsapp_remlab_conf->ip .
                        get_string('mail_content3_lab_up', 'ejsapp');
                    $send_mail = true;
                }
                $ejsapp_remlab_conf->active = $lab_state;
                $DB->update_record('block_remlab_manager_conf', $ejsapp_remlab_conf);
            }
            // Send e-mails:
            if ($send_mail) {
                $teachers = get_role_users($role->id, $context);
                foreach ($teachers as $teacher) {
                    email_to_user($teacher, $teacher, $subject, $messagebody);
                }
            }
        }
    }

    return true;
}

/**
 *
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 *
 */
function ejsapp_get_extra_capabilities() {
    return array('moodle/role:assign', 'moodle/site:accessallgroups', 'moodle/course:viewhiddenuserfields',
                 'moodle/site:viewparticipants', 'moodle/course:managegroups', 'moodle/course:enrolreview',
                 'moodle/user:viewdetails');
}

/**
 *
 * Must return an array of users who are participants for a given instance
 * of ejsapp. Must include every user involved in the instance,
 * independent of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $ejsappid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 *
 */
function ejsapp_get_participants($ejsappid) {
    return false;
}



////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////
/**
 *
 * This function extends the settings navigation block for the site.
 *
 * It is safe to rely on PAGE here as we will only ever be within the module
 * context when this is called
 *
 * @param settings_navigation $settings
 * @param navigation_node $ejsappnode
 * @return void
 *
 */
function ejsapp_extend_settings_navigation($settings, $ejsappnode) {
    global $PAGE;

    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $ejsappnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (has_capability('mod/ejsapp:addinstance', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/ejsapp/personalized_vars_values.php', array('id'=>$PAGE->cm->id, 'courseid'=>$PAGE->course->id));
        $node = navigation_node::create(get_string('personal_vars_button', 'ejsapp'),
            $url, navigation_node::TYPE_SETTING, null, 'mod_ejsapp_personal_vars');
        $ejsappnode->add_node($node, $beforekey);
    }
}



////////////////////////////////////////////////////////////////////////////////
// File API //
////////////////////////////////////////////////////////////////////////////////

/**
 *
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 *
 */
function ejsapp_get_file_areas($course, $cm, $context) {
    return array('jarfiles' => 'Applets and Javascript files with the virtual or remote labs',
                 'xmlfile'  => 'Text files containing all the information to define the state of a lab',
                 'cntfiles' => 'Text files containing a code (typically, a controller)',
                 'recfiles' => 'Text files containing a script recording the interaction of a user with a lab',
                 'blkfiles' => 'Text files containing a blockly program or a configuration of blocks');
}

/**
 *
 * File browsing support for ejsapp file areas
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 *
 */
function ejsapp_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 *
 * Serves the files from the ejsapp file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the ejsapp's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return null
 *
 */
function ejsapp_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, $options=null) {
    global $DB;

    if ($context->contextlevel != CONTEXT_MODULE && $context->contextlevel != CONTEXT_USER) {
        return false;
    }

    require_login($course, true, $cm);

    if ($filearea !== 'private' && $filearea !== 'jarfiles' && $filearea !== 'xmlfiles' && $filearea !== 'cntfiles' && $filearea !== 'recfiles' && $filearea !== 'blkfiles') {
        return false;
    }

    $fileid = (int)array_shift($args);

    if (!$submissions = $DB->get_records('files', array('contextid' => $context->id, 'itemid' => $fileid, 'filearea' => $filearea))) {
        return false;
    }

    $relativepath = implode('/', $args);

    if ($filearea == 'private') {
        $fullpath = '/' . $context->id . '/user/' . $filearea . '/' . $fileid . '/' . $relativepath;
    } else {
        $fullpath = '/' . $context->id . '/mod_ejsapp/' . $filearea . '/' . $fileid . '/' . $relativepath;
    }

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    return send_stored_file($file, 604800, 0, $forcedownload, $options);
}



////////////////////////////////////////////////////////////////////////////////
// External API //
////////////////////////////////////////////////////////////////////////////////

/**
 * EJSApp Interface for external applications (such as IPAL: http://www.compadre.org/ipal/)
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function get_ejsapp_instances($course_id=null) {
    global $DB, $USER;

    $courses = array();
    if (!is_null($course_id)) {
        $courses[] = $course_id;
    } else {
        $course_records = $DB->get_records('course', array() );
        foreach ($course_records as $course_record) {
            $courses[] = $course_record->id;
        }
    }

    $ejsapp_instances = array();
    foreach ($courses as $course) {
        $context = context_course::instance($course);
        if (has_capability('mod/ejsapp:requestinformation', $context, $USER->id, TRUE)) {
            $ejsapp_instances = array_merge($ejsapp_instances, $DB->get_records('ejsapp', array('course'=>$course)));
        }
    }

    $result = array_values($ejsapp_instances);
    return $result;
}//get_ejsapp_instances

function get_ejsapp_states($ejsapp_id) {
    global $DB,$USER;

    // get private state files
    $all_state_files = $DB->get_records('files',
        array('userid' => $USER->id,
            'mimetype' => 'application/xml',
            'filearea' => 'private',
            'component' => 'mod_ejsapp'
        )
    );
    // get initial state files
    $all_state_files = array_merge($all_state_files,$DB->get_records('files',
        array('mimetype' => 'application/xml',
            'filearea' => 'xmlfiles',
            'component' => 'mod_ejsapp'
        )
    ));

    // filter state files by ejsappid
    $source = 'ejsappid='.$ejsapp_id;
    $state_files = array();
    foreach ($all_state_files as $key=>$value) {
        if (($value->itemid == $ejsapp_id) ||
            ($value->source == $source)) {
            $state_object = new stdClass();
            $state_object->state_name=$value->filename;
            $state_object->state_id =
                $value->contextid . "/" . $value->component . "/" .
                $value->filearea . "/" . $value->itemid . "/" .
                $value->filename;

            $state_files[] = $state_object;
        }
    }

    return $state_files;

}//get_ejsapp_states

//TODO: get .rec files (get_ejsapp_recordings) and .blk files (get_ejsapp_blockly_programs)

function draw_ejsapp_instance($ejsapp_id, $data_files=null) {
    global $DB, $CFG;

    if ($DB->record_exists('ejsapp', array('id' => $ejsapp_id))) {
        $ejsapp = $DB->get_record('ejsapp', array('id' => $ejsapp_id));
        require_once($CFG->dirroot . '/mod/ejsapp/generate_embedding_code.php');
        $code = generate_embedding_code($ejsapp, null, $data_files, null, null);
    }
    else {
        $code = get_string('ejsapp_error', 'ejsapp');
    }

    return $code;
} //draw_ejsapp_instance