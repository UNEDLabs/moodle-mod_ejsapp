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
 *
 * Ajax update for the EJSApp view.php when a user needs to wait for a remote lab to be available
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once('locallib.php');

require_login(0, false);

$ejsappid = required_param('ejsappid', 'int');
$check = required_param('check_activity', 'int');
$remainingtime = required_param('remaining_time', 'int');

global $PAGE, $DB;

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/ejsapp/countdown.php');

$ejsapp = $DB->get_record('ejsapp', array('id' => $ejsappid));
$practiceintro = $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro', array('ejsappid' => $ejsappid));
$remlabconf = $DB->get_record('block_remlab_manager_conf', array('practiceintro' => $practiceintro));

if ($remainingtime > 0) {
    $checkactivity = get_config('mod_ejsapp', 'check_activity');
    $repeatedlabs = get_repeated_remlabs($practiceintro);
    $timeinfo = remote_lab_use_time_info($repeatedlabs, $ejsapp);
    $waittime = get_wait_time($remlabconf, $timeinfo->time_first_access, $timeinfo->time_last_access,
        $timeinfo->max_use_time, $timeinfo->reboottime, $checkactivity);
    echo $waittime . ' ' . get_string('seconds', 'ejsapp');
} else {
    make_lab_available($remainingtime, $remlabconf);
    echo get_string('refresh', 'ejsapp');
}