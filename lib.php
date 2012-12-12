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
 * Supported features by EJSApp
 *
 * @param constant $feature feature to be supported
 * @return boolean true if EJSApp supports the feature, false elsewhere
 */
function ejsapp_supports($feature)
{
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $ejsapp An object from the form in mod_form.php
 * @param $mform
 * @return int The id of the newly inserted ejsapp record
 */
function ejsapp_add_instance($ejsapp, $mform = null)
{
    global $DB, $CFG;

    $ejsapp->timecreated = time();
    $ejsapp->id = $DB->insert_record('ejsapp', $ejsapp);

    if ($mform) {
        $ejsapp->appwording = $ejsapp->ejsappwording['text'];
        $ejsapp->appwordingformat = $ejsapp->ejsappwording['format'];
    }

    $cmid = $ejsapp->coursemodule;
    $context = get_context_instance(CONTEXT_MODULE, $cmid);
    update_db($ejsapp, $context->id);

    // Remote labs
    if ($ejsapp->is_rem_lab == 1) {
        $ejsapp_rem_lab = new stdClass();
        $ejsapp_rem_lab->ejsappid = $ejsapp->id;
        $ejsapp_rem_lab->usingsarlab = $ejsapp->sarlab;
        if ($ejsapp_rem_lab->usingsarlab == 1) {
            $sarlabinstance = $ejsapp->sarlab_instance;
            $ejsapp_rem_lab->sarlabinstance = $sarlabinstance;
            $list_sarlab_IPs = explode(";", $CFG->sarlab_IP);
            $list_sarlab_ports = explode(";", $CFG->sarlab_port);
            $ejsapp_rem_lab->ip = $list_sarlab_IPs[intval($sarlabinstance)];
            $ejsapp_rem_lab->port = $list_sarlab_ports[intval($sarlabinstance)];
        } else {
            $ejsapp_rem_lab->sarlabinstance = '0';
            $ejsapp_rem_lab->ip = $ejsapp->ip_lab;
            $ejsapp_rem_lab->port = $ejsapp->port;
        }
        $ejsapp_rem_lab->totalslots = $ejsapp->totalslots;
        $ejsapp_rem_lab->weeklyslots = $ejsapp->weeklyslots;
        $ejsapp_rem_lab->dailyslots = $ejsapp->dailyslots;
        $DB->insert_record('ejsapp_remlab_conf', $ejsapp_rem_lab);

        $ejsapp_expsyst2pract = new stdClass();
        $ejsapp_expsyst2pract->ejsappid = $ejsapp->id;
        //Receive parameters from Sarlab's config tool (if it is used)... TODO
        if ($ejsapp_rem_lab->usingsarlab == 1) {
            $expsyst2pract_list = explode(';', $ejsapp->practiceintro);
            for ($i = 0; $i < count($expsyst2pract_list); $i++) {
                $ejsapp_expsyst2pract->practiceid = $i + 1;
                $ejsapp_expsyst2pract->practiceintro = $expsyst2pract_list[$i];
                $DB->insert_record('ejsapp_expsyst2pract', $ejsapp_expsyst2pract);
            }
        } else {
            $ejsapp_expsyst2pract->practiceid = 1;
            $ejsapp_expsyst2pract->practiceintro = $ejsapp->name;
            $DB->insert_record('ejsapp_expsyst2pract', $ejsapp_expsyst2pract);
        }
        // EJSApp booking system
        if($DB->record_exists('ejsappbooking', array('course'=>$ejsapp->course))) {
            $context = get_context_instance(CONTEXT_COURSE, $ejsapp->course);
            $users = get_enrolled_users($context);
            $ejsappbooking = $DB->get_record('ejsappbooking', array('course'=>$ejsapp->course));
            //ejsappbooking_usersaccess table:
            $ejsappbooking_usersaccess = new stdClass();
            $ejsappbooking_usersaccess->bookingid = $ejsappbooking->id;
            $ejsappbooking_usersaccess->ejsappid = $ejsapp->id;
            //Grant remote access to admin user:
            $ejsappbooking_usersaccess->userid = 2;
            $ejsappbooking_usersaccess->allowremaccess = 1;   
            $DB->insert_record('ejsappbooking_usersaccess', $ejsappbooking_usersaccess);
            //Consider other enrolled users:
            foreach ($users as $user) {
              $ejsappbooking_usersaccess->userid = $user->id;
              if (!has_capability('moodle/course:viewhiddensections', $context, $user->id, false)) {
                $ejsappbooking_usersaccess->allowremaccess = 0;
              } else {
                $ejsappbooking_usersaccess->allowremaccess = 1;
              }
              $DB->insert_record('ejsappbooking_usersaccess', $ejsappbooking_usersaccess);
            }
        }
    }

    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
    delete_recursively($path . 'temp');

    if ($mform and !empty($ejsapp->ejsappwording['itemid'])) {
        $draftitemid = $ejsapp->ejsappwording['itemid'];
        $ejsapp->appwording = file_save_draft_area_files($draftitemid, $context->id, 'mod_ejsapp', 'appwording', 0, array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1, 'context' => $context, 'noclean' => 1, 'trusttext' => 0), $ejsapp->appwording);
        $DB->update_record('ejsapp', $ejsapp);
    }
    
    // Creating the state file in dataroot and updating the files table in the database
    $context = get_context_instance(CONTEXT_MODULE, $cmid);
    $draftitemid = $ejsapp->statefile;
    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_ejsapp', 'xmlfiles', $ejsapp->id, array('subdirs' => true));
    }

    return $ejsapp->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $ejsapp An object from the form in mod_form.php
 * @param $mform
 * @return boolean Success/Fail
 */
function ejsapp_update_instance($ejsapp, $mform)
{
    global $DB, $CFG;

    $ejsapp->timemodified = time();
    $ejsapp->id = $ejsapp->instance;

    $ejsapp->appwording = $ejsapp->ejsappwording['text'];
    $ejsapp->appwordingformat = $ejsapp->ejsappwording['format'];

    $cmid = $ejsapp->coursemodule;
    $context = get_context_instance(CONTEXT_MODULE, $cmid);

    // If the file attached to the updated EJSApp instance has been updated, delete older file and create the new one (TODO)
    require_once($CFG->libdir . '/filelib.php');
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'jarfiles', $ejsapp->id);
    update_db($ejsapp, $context->id);

    // Remote labs
    if ($ejsapp->is_rem_lab == 1) {
        $ejsapp_rem_lab = new stdClass();
        $ejsapp_rem_lab->ejsappid = $ejsapp->id;
        $ejsapp_rem_lab->usingsarlab = $ejsapp->sarlab;
        if ($ejsapp_rem_lab->usingsarlab == 1) {
            $sarlabinstance = $ejsapp->sarlab_instance;
            $ejsapp_rem_lab->sarlabinstance = $sarlabinstance;
            $list_sarlab_IPs = explode(";", $CFG->sarlab_IP);
            $list_sarlab_ports = explode(";", $CFG->sarlab_port);
            $ejsapp_rem_lab->ip = $list_sarlab_IPs[intval($sarlabinstance)];
            $ejsapp_rem_lab->port = $list_sarlab_ports[intval($sarlabinstance)];
        } else {
            $ejsapp_rem_lab->sarlabinstance = '0';
            $ejsapp_rem_lab->ip = $ejsapp->ip_lab;
            $ejsapp_rem_lab->port = $ejsapp->port;
        }
        $ejsapp_rem_lab->totalslots = $ejsapp->totalslots;
        $ejsapp_rem_lab->weeklyslots = $ejsapp->weeklyslots;
        $ejsapp_rem_lab->dailyslots = $ejsapp->dailyslots;

        $rem_lab = $DB->get_record('ejsapp_remlab_conf', array('ejsappid' => $ejsapp->id));
        if ($rem_lab != null) {
            $ejsapp_rem_lab->id = $rem_lab->id;
            $DB->update_record('ejsapp_remlab_conf', $ejsapp_rem_lab);
        } else {
            $DB->insert_record('ejsapp_remlab_conf', $ejsapp_rem_lab);
        }

        $DB->delete_records('ejsapp_expsyst2pract', array('ejsappid' => $ejsapp->id));
        $ejsapp_expsyst2pract = new stdClass();
        $ejsapp_expsyst2pract->ejsappid = $ejsapp->id;
        //Receive parameters from Sarlab's config tool (if it is used)... TODO
        if ($ejsapp->sarlab == 1) {
            $expsyst2pract_list = explode(';', $ejsapp->practiceintro);
            for ($i = 0; $i < count($expsyst2pract_list); $i++) {
                $ejsapp_expsyst2pract->practiceid = $i + 1;
                $ejsapp_expsyst2pract->practiceintro = $expsyst2pract_list[$i];
                $DB->insert_record('ejsapp_expsyst2pract', $ejsapp_expsyst2pract);
            }
        } else {
            $ejsapp_expsyst2pract->practiceid = 1;
            $ejsapp_expsyst2pract->practiceintro = $ejsapp->name;
            $DB->insert_record('ejsapp_expsyst2pract', $ejsapp_expsyst2pract);
        }
        // EJSApp booking system
        if($DB->record_exists('ejsappbooking', array('course'=>$ejsapp->course))) {
            $context = get_context_instance(CONTEXT_COURSE, $ejsapp->course);
            $users = get_enrolled_users($context);
            $ejsappbooking = $DB->get_record('ejsappbooking', array('course'=>$ejsapp->course));
            //ejsappbooking_usersaccess table:
            $ejsappbooking_usersaccess = new stdClass();
            $ejsappbooking_usersaccess->bookingid = $ejsappbooking->id;
            $ejsappbooking_usersaccess->ejsappid = $ejsapp->id;
            //Grant remote access to admin user:
            $ejsappbooking_usersaccess->userid = 2;
            $ejsappbooking_usersaccess->allowremaccess = 1;   
            $DB->insert_record('ejsappbooking_usersaccess', $ejsappbooking_usersaccess);
            //Consider other enrolled users:
            foreach ($users as $user) {
              $ejsappbooking_usersaccess->userid = $user->id;
              if (!has_capability('moodle/course:viewhiddensections', $context, $user->id, false)) {
                $ejsappbooking_usersaccess->allowremaccess = 0;
              } else {
                $ejsappbooking_usersaccess->allowremaccess = 1;
              }
              $DB->insert_record('ejsappbooking_usersaccess', $ejsappbooking_usersaccess);
            }
        }
    } elseif ($rem_labs = $DB->get_records('ejsapp_remlab_conf', array('ejsappid' => $ejsapp->id)) != null) {
        $DB->delete_records('ejsapp_remlab_conf', array('ejsappid' => $ejsapp->id));
        $DB->delete_records('ejsapp_expsyst2pract', array('ejsappid' => $ejsapp->id));
        // EJSApp booking system
        if($DB->record_exists('ejsappbooking', array('course'=>$ejsapp->course))) {
          $DB->delete_records('ejsappbooking_usersaccess', array('ejsappid' => $ejsapp->id));
          $DB->delete_records('ejsappbooking_remlab_access', array('ejsappid' => $ejsapp->id));
        }
    }

    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $ejsapp->id . '/';
    delete_recursively($path . 'temp');

    $draftitemid = $ejsapp->ejsappwording['itemid'];
    if ($draftitemid) {
        $ejsapp->appwording = file_save_draft_area_files($draftitemid, $context->id, 'mod_ejsapp', 'appwording', 0, array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1, 'context' => $context, 'noclean' => 1, 'trusttext' => 0), $ejsapp->appwording);
        $DB->update_record('ejsapp', $ejsapp);
    }

    // Creating the state file in dataroot and updating the files table in the database
    $context = get_context_instance(CONTEXT_MODULE, $cmid);
    $draftitemid = $ejsapp->statefile;
    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_ejsapp', 'xmlfiles', $ejsapp->id, array('subdirs' => true));
    }

    return $ejsapp->id;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function ejsapp_delete_instance($id)
{
    global $DB, $CFG;
    require_once($CFG->libdir . '/filelib.php');

    if (!$ejsapp = $DB->get_record('ejsapp', array('id' => $id))) {
        return false;
    }

    $context = get_system_context();

    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_ejsapp', 'jarfiles', $ejsapp->id);

    $DB->delete_records('ejsapp', array('id' => $ejsapp->id));
    if ($ejsapp->is_rem_lab == 1) {
        $DB->delete_records('ejsapp_remlab_conf', array('ejsappid' => $ejsapp->id));
        $DB->delete_records('ejsapp_expsyst2pract', array('ejsappid' => $ejsapp->id));
        // EJSApp booking system
        if($DB->record_exists('ejsappbooking', array('course'=>$ejsapp->course))) {
          $DB->delete_records('ejsappbooking_usersaccess', array('ejsappid' => $ejsapp->id));
          $DB->delete_records('ejsappbooking_remlab_access', array('ejsappid' => $ejsapp->id));
        }
    }

    $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $ejsapp->course . '/' . $id;
    delete_recursively($path);
    return true;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * @param $course
 * @param $user
 * @param $mod
 * @param $ejsapp
 *
 * @return
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 */
function ejsapp_user_outline($course, $user, $mod, $ejsapp)
{
    $return = new stdClass;
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param $course
 * @param $user
 * @param $mod
 * @param $ejsapp
 *
 * @return boolean
 */
function ejsapp_user_complete($course, $user, $mod, $ejsapp)
{
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in ejsapp activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @param $course
 * @param $viewfullnames
 * @param $timestart
 * @return boolean false
 */
function ejsapp_print_recent_activity($course, $viewfullnames, $timestart)
{
    return false; //  True if anything was printed, otherwise false
}

/**
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
 */
function ejsapp_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0)
{
}

/**
 * Prints single activity item prepared by {@see newmodule_get_recent_mod_activity()}
 * @param $activity
 * @param $courseid
 * @param $detail
 * @param $modnames
 * @param $viewfullnames
 * @return void
 */
function ejsapp_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames)
{
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 **/
function ejsapp_cron()
{
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function newmodule_get_extra_capabilities()
{
    return array();
}

/**
 * Must return an array of users who are participants for a given instance
 * of ejsapp. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $ejsappid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function ejsapp_get_participants($ejsappid)
{
    return false;
}



////////////////////////////////////////////////////////////////////////////////
// File API //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function ejsapp_get_file_areas($course, $cm, $context)
{
    return array();
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
 */
function ejsapp_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename)
{
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
 * @return file served file
 */
function ejsapp_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, $options=null)
{
    global $DB;

    if ($context->contextlevel != CONTEXT_MODULE && $context->contextlevel != CONTEXT_USER) {
        return false;
    }

    require_login($course, true, $cm);

    if ($filearea !== 'private' && $filearea !== 'jarfiles' && $filearea !== 'xmlfiles') {
        return false;
    }

    $fileid = (int)array_shift($args);

    if (!$submissions = $DB->get_records('files', array('contextid' => $context->id, 'itemid' => $fileid, 'filearea' => $filearea))) {
        return false;
    }

    $relativepath = implode('/', $args);
    //The previous line of code works for the saved xml state files but not with the embedded EJS jar files that use DefaultState.out
    /*$extension = substr($relativepath,-4);
    if (strcmp($extension,'.xml') != 0 && strcmp($extension,'.gif') != 0 && strcmp($extension,'.jpg') != 0 && strcmp($extension,'.bmp') != 0) { //not an .xml state file or an image
      if (count($submissions) == 2) { //EJS jar files have two registers in the files table
        foreach ($submissions as $submission) {
          if ($submission->source == null && strcmp($submission->filename,'.') != 0) { //jar file
            //$relativepath = $submission->filename;
            //return true;
          }
        }
      }
    }*/

    $fullpath = '/' . $context->id . '/mod_ejsapp/' . $filearea . '/' . $fileid . '/' . $relativepath;

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    return send_stored_file($file, 0, 0, true);
    //return send_stored_file($file, 604800, 0, true); // download MUST be forced - security! I CAN ONLY SET CACHE != 0 if WE USE DIFFERENT APPLETS FOR COLLAB THAN FOR INDIVIDUAL SESSIONS

}