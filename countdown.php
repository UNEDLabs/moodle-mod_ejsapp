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
 *
 * Ajax update for the EJSApp view.php when a user needs to wait for a remote lab to be available
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once('locallib.php');

require_login(0, false);

$ejsappid = required_param('ejsappid', 'int');
$courseid = required_param('courseid', 'int');
$check_activity = required_param('check_activity', 'int');
$remaining_time = required_param('remaining_time', 'int');

global $PAGE, $DB;

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/ejsapp/countdown.php');

if ($remaining_time > 0) {
    $slotsduration = array(60, 30,15, 5, 2);
    $currenttime = time();
    $ejsapp = $DB->get_record('ejsapp', array('id' => $ejsappid));
    $practiceintro = $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro', array('ejsappid' => $ejsappid));
    $remlab_conf = $DB->get_record('block_remlab_manager_conf', array('practiceintro' => $practiceintro));
    $idle_time = $remlab_conf->reboottime;
    $repeated_ejsapp_labs = get_repeated_remlabs($remlab_conf);
    $time_information = get_occupied_ejsapp_time_information($repeated_ejsapp_labs, $slotsduration, $currenttime);
    $lab_status = get_lab_status($time_information, $idle_time, $check_activity);
    $repeated_ejsapp_labs = get_repeated_remlabs($remlab_conf);
    $booking_info = check_active_booking($repeated_ejsapp_labs, $courseid);
    $remaining_time = get_remaining_time($booking_info, $lab_status, $time_information, $idle_time, $check_activity);
    echo $remaining_time . ' ' . get_string('seconds', 'ejsapp');
} else {
    echo get_string('refresh', 'ejsapp');
}