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
        $context = get_context_instance(CONTEXT_COURSE,$course);
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
                'component' => 'user'
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

function draw_ejsapp_instance($ejsapp_id, $state_file=null, $width=null, $height=null) {
    global $DB, $USER, $CFG;
    
    if ($DB->record_exists('ejsapp', array('id' => $ejsapp_id))) {
      $ejsapp = $DB->get_record('ejsapp', array('id' => $ejsapp_id));
      if ($width && $height) {
        $external_size = new stdClass();
        $external_size->width = $width;
        $external_size->height = $height;
      } else {
        $external_size = null;
      }
      require_once($CFG->dirroot . '/mod/ejsapp/generate_applet_embedding_code.php');
      $code = generate_applet_embedding_code($ejsapp, null, $state_file, null, $external_size);
    }
    else {
      $code = get_string('ejsapp_error', 'ejsapp');
    }
    
    return $code;
} //draw_ejsapp_instance