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

require_once('update_db.php');
require_once('locallib.php');

function ejsapp_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_BACKUP_MOODLE2:          return false;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $ejsapp An object from the form in mod_form.php
 * @return int The id of the newly inserted ejsapp record
 */
function ejsapp_add_instance($ejsapp, $mform = null) {
    global $DB, $CFG;
        
    $ejsapp->timecreated = time();
    $ejsapp->id = $DB->insert_record('ejsapp', $ejsapp);
    
    if ($mform) {
        $ejsapp->appwording       = $ejsapp->ejsapp['text'];
        $ejsapp->appwordingformat = $ejsapp->ejsapp['format'];
    }
    
    $cmid = $ejsapp->coursemodule;
    $context = get_context_instance(CONTEXT_MODULE, $cmid);
    update_db($ejsapp, $context->id);
    
    // Remote labs
    if ($ejsapp->is_rem_lab == 1) {
      $ejsapp_rem_lab = new stdClass();
      $ejsapp_rem_lab->ejsappid = $ejsapp->id;
      $ejsapp_rem_lab->port = '0';
      $ejsapp_rem_lab->ip = $ejsapp->ip_lab;
      $ejsapp_rem_lab->totalslots = $ejsapp->totalslots;
      $ejsapp_rem_lab->weeklyslots = $ejsapp->weeklyslots;
      $ejsapp_rem_lab->dailyslots = $ejsapp->dailyslots;
      $DB->insert_record('ejsapp_remlab_conf', $ejsapp_rem_lab);
    }
    
    $path = $CFG->dirroot . '/mod/ejsapp/jarfile/' . $ejsapp->course . '/' . $ejsapp->id . '/';
    delete_recursively($path . 'temp');
    
    if ($mform and !empty($ejsapp->ejsapp['itemid'])) {
      $draftitemid = $ejsapp->ejsapp['itemid'];
      $ejsapp->appwording = file_save_draft_area_files($draftitemid, $context->id, 'mod_ejsapp', 'appwording', 0, array('subdirs'=>1, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'changeformat'=>1, 'context'=>$context, 'noclean'=>1, 'trusttext'=>0), $ejsapp->appwording);
      $DB->update_record('ejsapp', $ejsapp);
    }
    
    return $ejsapp->id;
}
                                
/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $ejsapp An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function ejsapp_update_instance($ejsapp, $mform) {
    global $DB, $CFG;
    
    $ejsapp->timemodified = time();
    $ejsapp->id = $ejsapp->instance;
    
    $ejsapp->appwording       = $ejsapp->ejsapp['text'];
    $ejsapp->appwordingformat = $ejsapp->ejsapp['format'];
      
    $cmid = $ejsapp->coursemodule;
    $context = get_context_instance(CONTEXT_MODULE, $cmid); 
    $DB->delete_records('files', array('contextid' => $context->id, 'component' => 'mod_ejsapp', 'filearea' => 'jarfile', 'itemid' => '0'));  
    update_db($ejsapp, $context->id);
    
    // Remote labs
    if ($ejsapp->is_rem_lab == 1) {
      $ejsapp_rem_lab = new stdClass();
      $ejsapp_rem_lab->ejsappid = $ejsapp->id;
      $ejsapp_rem_lab->port = '0';
      $ejsapp_rem_lab->ip = $ejsapp->ip_lab;
      $ejsapp_rem_lab->totalslots = $ejsapp->totalslots;
      $ejsapp_rem_lab->weeklyslots = $ejsapp->weeklyslots;
      $ejsapp_rem_lab->dailyslots = $ejsapp->dailyslots;      
      $rem_labs = $DB->get_records('ejsapp_remlab_conf', array('ejsappid'=>$ejsapp->id));
      if ($rem_labs != null) {
        foreach ($rem_labs as $rem_lab) {
          $ejsapp_rem_lab->id = $rem_lab->id;
          $DB->update_record('ejsapp_remlab_conf', $ejsapp_rem_lab);
        }
      } else {
        $DB->insert_record('ejsapp_remlab_conf', $ejsapp_rem_lab);
      }        
    } elseif ($rem_labs = $DB->get_records('ejsapp_remlab_conf', array('ejsappid'=>$ejsapp->id)) != null) {
      $DB->delete_records('ejsapp_remlab_conf', array('ejsappid' => $ejsapp->id)); 
    }
                  
    $path = $CFG->dirroot . '/mod/ejsapp/jarfile/' . $ejsapp->course . '/' . $ejsapp->id . '/';
    delete_recursively($path . 'temp');
    
    $draftitemid = $ejsapp->ejsapp['itemid'];
    if ($draftitemid) {
      $ejsapp->appwording = file_save_draft_area_files($draftitemid, $context->id, 'mod_ejsapp', 'appwording', 0, array('subdirs'=>1, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'changeformat'=>1, 'context'=>$context, 'noclean'=>1, 'trusttext'=>0), $ejsapp->appwording);
      $DB->update_record('ejsapp', $ejsapp);
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
function ejsapp_delete_instance($id) {
    global $DB, $CFG;

    if (! $ejsapp = $DB->get_record('ejsapp', array('id' => $id))) {
        return false;
    }

    $cmid = $ejsapp->coursemodule;
    $context = get_context_instance(CONTEXT_MODULE, $cmid); 
	  
	  $DB->delete_records('files', array('contextid' => $context->id, 'component' => 'mod_ejsapp', 'filearea' => 'jarfile', 'itemid' => '0'));
    $DB->delete_records('ejsapp', array('id' => $ejsapp->id));
    
    $path = $CFG->dirroot . '/mod/ejsapp/jarfile/' . $ejsapp->course . '/' . $id;
    delete_recursively($path);
    keep_me_clean();

    return true;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
function ejsapp_user_outline($course, $user, $mod, $ejsapp) {
    $return = new stdClass;
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function ejsapp_user_complete($course, $user, $mod, $ejsapp) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in ejsapp activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function ejsapp_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function ejsapp_cron () {
    return true;
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
function ejsapp_get_participants($ejsappid) {
    return false;
}

/**
 * This function returns if a scale is being used by one ejsapp
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $ejsappid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 */
function ejsapp_scale_used($ejsappid, $scaleid) {
    global $DB;

    $return = false;

    //$rec = $DB->get_record("ejsapp", array("id" => "$ejsappid", "scale" => "-$scaleid"));
    //
    //if (!empty($rec) && !empty($scaleid)) {
    //    $return = true;
    //}

    return $return;
}

/**
 * Checks if scale is being used by any instance of ejsapp.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any ejsapp
 */
function ejsapp_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('ejsapp', 'grade', -$scaleid)) {
        return true;
    } else {
        return false;
    }
}