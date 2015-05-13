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
 * Ajax update for the EJSApp view.php when a user needs to wait for a remote lab to be available
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once('locallib.php');

require_login(0, false);

$ejsappid = required_param('ejsappid', 'int');
$check_activity = required_param('check_activity', 'int');
$remaining_time = required_param('remaining_time', 'int');
$skip = required_param('skip', 'int');

global $PAGE, $DB;

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/ejsapp/countdown.php');

if ($remaining_time > 0) {
    if ($skip == 0) { // check whether there has been any change in the state of the remote lab (i.e. it is not used anymore)
        $slotsduration = array(60, 30, 15, 5, 2);
        $currenttime = time();
        $ejsapp = $DB->get_record('ejsapp', array('id' => $ejsappid));
        $remlab_conf = $DB->get_record('ejsapp_remlab_conf', array('ejsappid' => $ejsappid));
        $idle_time = $remlab_conf->reboottime;
        $repeated_ejsapp_labs = get_repeated_remlabs($remlab_conf, $ejsapp);
        $time_information = get_occupied_ejsapp_time_information($repeated_ejsapp_labs, $slotsduration, $currenttime);
        $time_first_access = $time_information['time_first_access'];
        $time_last_access = $time_information['time_last_access'];
        $occupied_ejsapp_max_use_time = $time_information['occupied_ejsapp_max_use_time'];
        $lab_rebooting = false;
        if (($currenttime - $time_last_access - $check_activity > 0) && ($currenttime - $time_last_access - 60 * $idle_time - $check_activity < 0)) {
            $lab_rebooting = true;
        }
        if ($lab_rebooting) {
            $remaining_time = 60 * $idle_time + $check_activity - ($currenttime - $time_last_access);
        } else {
            if ($time_first_access == INF) $time_first_access = time();
            $remaining_time = 60 * $idle_time + $occupied_ejsapp_max_use_time - ($currenttime - $time_first_access);
        }
    }
    echo $remaining_time . ' ' . get_string('seconds', 'ejsapp');
} else {
    echo get_string('refresh', 'ejsapp');
}