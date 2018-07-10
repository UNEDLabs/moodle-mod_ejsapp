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
 * Library of interface functions and constants for module ejsapp.
 *
 * All the core Moodle functions, neeeded to allow the module to work integrated in Moodle are here.
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('locallib.php');

/**
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
            return true;
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
 * Given an object containing all the necessary data, (defined by the form in mod_form.php) this function will create a
 * new instance and return the id number of the new instance.
 *
 * @param object $ejsapp An object from the form in mod_form.php
 * @param object $mform
 * @return int The id of the newly inserted ejsapp record
 * @throws
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
    $ejsok = update_ejsapp_files_and_tables($ejsapp, $context);

    if ($ejsok) {
        ejsapp_grade_item_update($ejsapp);
        if ($ejsapp->is_rem_lab == 1) { // Remote lab.
            if ($ejsapp->remlab_manager) {
                $practiceslist = explode(';', $ejsapp->list_practices);
                check_create_remlab_conf($practiceslist[$ejsapp->practiceintro]);
            }
            ejsapp_expsyst2pract($ejsapp);
            update_booking_table($ejsapp);
        }
    }

    return $ejsapp->id;
}

/**
 * Given an object containing all the necessary data, (defined by the form in mod_form.php) this function will update
 * an existing instance with new data.
 *
 * @param object $ejsapp An object from the form in mod_form.php
 * @param object $mform
 * @return boolean Success/Fail
 * @throws
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

    $moduleid = $DB->get_field('modules', 'id', array('name' => 'ejsapp'));
    $cmid = $DB->get_field('course_modules', 'id', array('course' => $ejsapp->course,
        'module' => $moduleid, 'instance' => $ejsapp->id));
    $context = context_module::instance($cmid);

    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'jarfiles', $ejsapp->id);
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'tmp_jarfiles', $ejsapp->id);
    $ejsok = update_ejsapp_files_and_tables($ejsapp, $context);
    if ($ejsok) {
        ejsapp_grade_item_update($ejsapp);
        $bookinginstalled = $DB->get_records('modules', array('name' => 'ejsappbooking'));
        $bookinginstalled = !empty($bookinginstalled);
        if ($ejsapp->is_rem_lab == 1) { // Remote lab.
            if ($ejsapp->remlab_manager) {
                $practiceslist = explode(';', $ejsapp->list_practices);
                check_create_remlab_conf($practiceslist[$ejsapp->practiceintro]);
                $DB->delete_records('block_remlab_manager_exp2prc', array('ejsappid' => $ejsapp->id));
                ejsapp_expsyst2pract($ejsapp);
            }
            if ($bookinginstalled) {
                update_booking_table($ejsapp);
            }
        } else {
            if ($ejsapp->remlab_manager) {
                $DB->delete_records('block_remlab_manager_exp2prc', array('ejsappid' => $ejsapp->id));
            }
            if ($bookinginstalled) {
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
 * Given an ID of an instance of this module, this function will permanently delete the instance and any data that
 * depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 * @throws
 *
 */
function ejsapp_delete_instance($id) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/filelib.php');

    if (!$ejsapp = $DB->get_record('ejsapp', array('id' => $id))) {
        return false;
    }

    $moduleid = $DB->get_field('modules', 'id', array('name' => 'ejsapp'));
    $cmid = $DB->get_field('course_modules', 'id', array('course' => $ejsapp->course, 'instance' => $id,
        'module' => $moduleid));
    $context = context_module::instance($cmid);

    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'jarfiles', $id);
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'tmp_jarfiles', $id);
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'xmlfiles', $id);
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'recfiles', $id);
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'blkfiles', $id);

    $DB->delete_records('ejsapp', array('id' => $id));
    if ($ejsapp->is_rem_lab == 1) {
        $DB->delete_records('block_remlab_manager_exp2prc', array('ejsappid' => $id));
        // EJSApp booking system.
        if ($DB->record_exists('ejsappbooking', array('course' => $ejsapp->course))) {
            $DB->delete_records('ejsappbooking_usersaccess', array('ejsappid' => $id));
            $DB->delete_records('ejsappbooking_remlab_access', array('ejsappid' => $id));
        }
    }

    if ($ejsapp->personalvars == 1) {
        $DB->delete_records('ejsapp_personal_vars', array('ejsappid' => $id));
    }

    ejsapp_grade_item_delete($ejsapp);

    // Delete recursively.
    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $id;
    delete_recursively($path);
    return true;
}

/**
 * Create grade item for given $ejsapp
 *
 * @param object $ejsapp
 * @param array|object $grades 'reset' means reset grades in gradebook
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

    return grade_update('mod/ejsapp', $ejsapp->course, 'mod', '$ejsapp', $ejsapp->id,
        0, null, array('deleted' => 1));
}

/**
 * Return a small object with summary information about what a user has done with a given particular instance of this
 * module. Used for user activity reports.
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
 * @throws
 *
 */
function ejsapp_user_outline($course, $user, $mod, $ejsapp) {
    global $DB;

    if ($logs = $DB->get_records('logstore_standard_log', array('userid' => $user->id,
        'component' => 'mod_ejsapp', 'objectid' => $ejsapp->id), 'time ASC')) {
        // Standard logstore
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new stdClass();
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    } else if ($logs = $DB->get_records('log', array('userid' => $user->id, 'module' => 'ejsapp',
        'info' => $ejsapp->name), 'time ASC')) {
        // Legacy log
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new stdClass();
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    }

    return null;
}

/**
 * Print a detailed representation of what a user has done with a given particular instance of this module, for user
 * activity reports.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $ejsapp
 *
 * @throws
 *
 */
function ejsapp_user_complete($course, $user, $mod, $ejsapp) {
    global $DB;

    if ($logs = $DB->get_records('logstore_standard_log', array('userid' => $user->id,
        'component' => 'mod_ejsapp', 'objectid' => $ejsapp->id), 'time ASC')) {
        // Standard logstore
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);
    } else if ($logs = $DB->get_records('log', array('userid' => $user->id, 'module' => 'ejsapp',
        'info' => $ejsapp->name), 'time ASC')) {
        // Legacy log
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
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param stdClass $data the data submitted from the reset course.
 * @return array status array
 * @throws
 *
 */
function ejsapp_reset_userdata($data) {
    global $DB;

    $status = array();
    $componentstr = get_string('modulenameplural', 'ejsapp');

    $dbman = $DB->get_manager();
    $moodlelog = $dbman->table_exists('logstore_standard_log');
    if ($moodlelog) {
        $DB->delete_records('logstore_standard_log', array('component' => 'mod_ejsapp', 'courseid' => $data->courseid));
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deletedlogs', 'ejsapp'), 'error' => false);
    } else {
        $DB->delete_records('log', array('module' => 'mod_ejsapp', 'course' => $data->courseid));
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deletedlegacylogs', 'ejsapp'), 'error' => false);
    }

    $select = "course = ?";
    $ejsapps = $DB->get_records_select('ejsapp', $select, array($data->courseid));
    foreach ($ejsapps as $ejsapp) {
        $DB->delete_records('ejsapp_personal_vars', array('ejsappid' => $ejsapp->id));
        $DB->delete_records('ejsapp_records', array('ejsappid' => $ejsapp->id));
        ejsapp_grade_item_delete($ejsapp);
    }

    $status[] = array('component'=>$componentstr, 'item'=>get_string('deletedrecords', 'ejsapp'), 'error' => false);
    $status[] = array('component'=>$componentstr, 'item'=>get_string('deletedpersonalvars', 'ejsapp'), 'error' => false);
    $status[] = array('component'=>$componentstr, 'item'=>get_string('deletedgrades', 'ejsapp'), 'error' => false);
    return $status;
}

/**
 * List of view style log actions.
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
 * Given a course and a time, this module should find recent activity that has occurred in ejsapp activities and print
 * it out. Return true if there was output, or false is there was none.
 *
 * @param object $course
 * @param bool $viewfullnames
 * @param int $timestart
 * @return boolean false
 *
 */
function ejsapp_print_recent_activity($course, $viewfullnames, $timestart) {
    return false; // True if anything was printed, otherwise false.
}

/**
 * Prepares the recent activity data.
 *
 * This callback function is supposed to populate the passed array with custom activity records. These records are then
 * rendered into HTML via ejsapp_print_recent_mod_activity().
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
 * Prints single activity item prepared by ejsapp_get_recent_mod_activity().
 *
 * @param object $activity the activity object the forum resides in
 * @param int $courseid the id of the course the forum resides in
 * @param bool $detail not used, but required for compatibilty with other modules
 * @param int $modnames not used, but required for compatibilty with other modules
 * @param bool $viewfullnames not used, but required for compatibilty with other modules
 * @return void
 *
 */
function ejsapp_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron This function searches for things that need to be done,
 * such as sending out mail, toggling flags etc.
 *
 * @return boolean
 * @throws
 *
 **/
function ejsapp_cron() {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/filter/multilang/filter.php');

    // Delete stored Sarlab keys that are one day old or more.
    $time = array(strtotime(date('Y-m-d H:i:s')) - 86400);
    $DB->delete_records_select('block_remlab_manager_sb_keys', "creationtime < ?", $time);

    // Checking whether remote labs are operative or not (once per day).
    if (date('H') >= 8 && date('H') < 10) {
        $remlabsconf = $DB->get_records('block_remlab_manager_conf');
        foreach ($remlabsconf as $remlabconf) {
            $sarlabinstance = is_practice_in_sarlab($remlabconf->practiceintro);
            $devicesinfo = new stdClass();
            // TODO: Do the ping with Sarlab in between too!
            $labstate = ping($remlabconf->ip, $remlabconf->port, $sarlabinstance, $remlabconf->practiceintro);
            $remlabs = get_repeated_remlabs($remlabconf->practiceintro);
            foreach ($remlabs as $remlab) {
                $context = context_course::instance($remlab->course);
                $multilang = new filter_multilang($context, array('filter_multilang_force_old' => 0));
                $sendmail = false;
                // Prepare e-mails' content and update lab state when checkable.
                $subject = '';
                $messagebody = '';
                // E-mails are sent only if the remote lab state is not checkable or if it has passed from active to inactive
                if ($labstate == 2) {  // Not checkable.
                    $subject = get_string('mail_subject_lab_not_checkable', 'ejsapp');
                    $messagebody = get_string('mail_content1_lab_not_checkable', 'ejsapp') .
                        $multilang->filter($remlab->name) .
                        get_string('mail_content2_lab_not_checkable', 'ejsapp') . $remlabconf->ip .
                        get_string('mail_content3_lab_not_checkable', 'ejsapp');
                    $sendmail = true;
                } else {               // Active or inactive.
                    if ($remlabconf->active == 1 && $labstate == 0) {  // Lab has passed from active to inactive.
                        $subject = get_string('mail_subject_lab_down', 'ejsapp');
                        $messagebody = get_string('mail_content1_lab_down', 'ejsapp') .
                            $multilang->filter($remlab->name) .
                            get_string('mail_content2_lab_down', 'ejsapp') . $remlabconf->ip .
                            get_string('mail_content3_lab_down', 'ejsapp') .
                            get_string('mail_content4_lab_down', 'ejsapp');
                        foreach ($devicesinfo as $deviceinfo) {
                            if (!$deviceinfo->alive) {
                                $messagebody .= $deviceinfo->name . ', ' . $deviceinfo->ip . "\r\n";
                            }
                        }
                        $sendmail = true;
                    } else if ($remlabconf->active == 0 && $labstate == 1) { // Lab has passed from inactive to active.
                        $subject = get_string('mail_subject_lab_up', 'ejsapp');
                        $messagebody = get_string('mail_content1_lab_up', 'ejsapp') .
                            $multilang->filter($remlab->name) .
                            get_string('mail_content2_lab_up', 'ejsapp') . $remlabconf->ip .
                            get_string('mail_content3_lab_up', 'ejsapp');
                        $sendmail = true;
                    }
                    $remlabconf->active = $labstate;
                    $DB->update_record('block_remlab_manager_conf', $remlabconf);
                }
                // Send e-mails to teachers if conditions are met.
                $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
                // TODO: Allow configuring which roles receive the e-mails? (managers, non-editing teacher...) Use Moodle capabilities.
                if ($sendmail) {
                    $teachers = get_role_users($role->id, $context);
                    foreach ($teachers as $teacher) {
                        email_to_user($teacher, $teacher, $subject, $messagebody);
                    }
                }
            }
        }
    }

    // TODO: Get 'working' records from logstore_standard_log and transform them into one single 'exited' record per session

    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @return array
 *
 */
function ejsapp_get_extra_capabilities() {
    return array('moodle/role:assign', 'moodle/site:accessallgroups', 'moodle/course:viewhiddenuserfields',
                 'moodle/site:viewparticipants', 'moodle/course:managegroups', 'moodle/course:enrolreview',
                 'moodle/user:viewdetails');
}

/**
 * Must return an array of users who are participants for a given instance of ejsapp. Must include every user involved
 * in the instance, independent of his role (student, teacher, admin...). The returned objects must contain at least
 * id property. See other modules as example.
 *
 * @param int $ejsappid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 *
 */
function ejsapp_get_participants($ejsappid) {
    return false;
}



// Navigation API.

/**
 * This function extends the settings navigation block for the site.
 *
 * It is safe to rely on PAGE here as we will only ever be within the module context when this is called.
 *
 * @param settings_navigation $settings
 * @param navigation_node $ejsappnode
 * @return void
 * @throws
 *
 */
function ejsapp_extend_settings_navigation($settings, $ejsappnode) {
    global $PAGE;

    // We want to add these new nodes after the Edit settings node, and before the locally assigned roles node.
    // Of course, both of those are controlled by capabilities.
    $keys = $ejsappnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (has_capability('mod/ejsapp:addinstance', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/ejsapp/personalized_vars_values.php',
            array('id' => $PAGE->cm->id, 'courseid' => $PAGE->course->id));
        $node = navigation_node::create(get_string('personal_vars_button', 'ejsapp'),
            $url, navigation_node::TYPE_SETTING, null, 'mod_ejsapp_personal_vars');
        $ejsappnode->add_node($node, $beforekey);
    }
}



// File API.

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically by
 * file_browser::get_file_info_context_module()
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 *
 */
function ejsapp_get_file_areas($course, $cm, $context) {
    return array('jarfiles' => 'Applets and Javascript files with the virtual or remote labs',
                 'xmlfiles' => 'Text files containing all the information to define the state of a lab',
                 'recfiles' => 'Text files containing a script recording the interaction of a user with a lab',
                 'blkfiles' => 'Text files containing a blockly program or a configuration of blocks');
}

/**
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
 * @throws
 *
 */
function ejsapp_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, $options=null) {
    global $DB;

    if ($context->contextlevel != CONTEXT_MODULE && $context->contextlevel != CONTEXT_USER) {
        return false;
    }

    require_login($course, true, $cm);

    if ($filearea !== 'private' && $filearea !== 'jarfiles' && $filearea !== 'xmlfiles' &&
        $filearea !== 'recfiles' && $filearea !== 'blkfiles') {
        return false;
    }

    $fileid = (int)array_shift($args);

    if (!$submissions = $DB->get_records('files', array('contextid' => $context->id, 'itemid' => $fileid,
        'filearea' => $filearea))) {
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



// External API.

/**
 * Returns a list of ejsapp instances in a course.
 *
 * @param int $courseid
 * @return array $result
 * @throws
 */
function get_ejsapp_instances($courseid=null) {
    global $DB, $USER;

    $courses = array();
    if (!is_null($courseid)) {
        $courses[] = $courseid;
    } else {
        $courserecords = $DB->get_records('course', array() );
        foreach ($courserecords as $courserecord) {
            $courses[] = $courserecord->id;
        }
    }

    $ejsappinstances = array();
    foreach ($courses as $course) {
        $context = context_course::instance($course);
        if (has_capability('mod/ejsapp:requestinformation', $context, $USER->id, true)) {
            $ejsappinstances = array_merge($ejsappinstances, $DB->get_records('ejsapp', array('course' => $course)));
        }
    }

    $result = array_values($ejsappinstances);
    return $result;
}

/**
 * Returns a list of .xml and .json files saved in a particular ejsapp activity for the current user.
 *
 * @param int $ejsappid
 * @return array $statefiles
 * @throws
 */
function get_ejsapp_states($ejsappid) {
    global $DB, $USER;

    // Get private xml state files.
    $allstatefiles = $DB->get_records('files', array('userid' => $USER->id, 'mimetype' => 'application/xml',
            'filearea' => 'private', 'component' => 'mod_ejsapp'));
    $allstatefiles = array_merge($allstatefiles, $DB->get_records('files', array('userid' => $USER->id,
        'mimetype' => 'text/xml', 'filearea' => 'private', 'component' => 'mod_ejsapp')));

    // Get initial xml state files.
    $allstatefiles = array_merge($allstatefiles, $DB->get_records('files', array('mimetype' => 'application/xml',
            'filearea' => 'xmlfiles', 'component' => 'mod_ejsapp')));
    $allstatefiles = array_merge($allstatefiles, $DB->get_records('files', array('mimetype' => 'text/xml',
        'filearea' => 'xmlfiles', 'component' => 'mod_ejsapp')));

    // Get private json state files.
    $allstatefiles = array_merge($allstatefiles, $DB->get_records('files', array('userid' => $USER->id,
        'mimetype' => 'application/json', 'filearea' => 'private', 'component' => 'mod_ejsapp')));

    // Get initial json state files.
    $allstatefiles = array_merge($allstatefiles, $DB->get_records('files', array('mimetype' => 'application/json',
        'filearea' => 'xmlfiles', 'component' => 'mod_ejsapp')));

    // Filter state files by ejsappid.
    $source = 'ejsappid='.$ejsappid;
    $statefiles = array();
    foreach ($allstatefiles as $key => $value) {
        if (($value->itemid == $ejsappid) || ($value->source == $source)) {
            $stateobject = new stdClass();
            $stateobject->state_name = $value->filename;
            $stateobject->state_id = $value->contextid . "/" . $value->component . "/" . $value->filearea . "/" .
                $value->itemid . "/" . $value->filename;

            $statefiles[] = $stateobject;
        }
    }

    return $statefiles;
}

/**
 * Returns a list of .rec files saved in a particular ejsapp activity for the current user.
 *
 * @param int $ejsappid
 * @return array $recordingfiles
 * @throws
 */
function get_ejsapp_recordings($ejsappid) {
    global $DB, $USER;

    $recordingfiles = array();
    // TODO
    return $recordingfiles;
}

/**
 * Returns a list of .blk files saved in a particular ejsapp activity for the current user.
 *
 * @param int $ejsappid
 * @return array $experimentfiles
 * @throws
 */
function get_ejsapp_blockly_programs($ejsappid) {
    global $DB, $USER;

    $experimentfiles = array();
    // TODO
    return $experimentfiles;
}

/**
 * Returns the embedding code for an virtual lab EjsS application.
 *
 * @param int $ejsappid the course object
 * @param array|null $datafiles
 * @return string $code
 * @throws
 */
function draw_ejsapp_instance($ejsappid, $datafiles = null) {
    global $DB, $CFG;

    if ($DB->record_exists('ejsapp', array('id' => $ejsappid))) {
        $ejsapp = $DB->get_record('ejsapp', array('id' => $ejsappid));
        require_once($CFG->dirroot . '/mod/ejsapp/generate_embedding_code.php');
        $code = generate_embedding_code($ejsapp, null, $datafiles, null, null);
    } else {
        $code = get_string('ejsapp_error', 'ejsapp');
    }

    return $code;
}