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
$courseid = required_param('courseid', 'int');
$check = required_param('check_activity', 'int');
$remainingtime = required_param('remaining_time', 'int');

global $PAGE, $DB;

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/ejsapp/countdown.php');

if ($remainingtime > 0) {
    $currenttime = time();
    $ejsapp = $DB->get_record('ejsapp', array('id' => $ejsappid));
    $practiceintro = $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro', array('ejsappid' => $ejsappid));
    $remlabconf = $DB->get_record('block_remlab_manager_conf', array('practiceintro' => $practiceintro));
    $idletime = $remlabconf->reboottime;
    $repeatedlabs = get_repeated_remlabs($remlabconf);
    $timeinfo = remote_lab_use_time_info($repeatedlabs);
    $labstatus = get_lab_status($timeinfo, $idletime, $check);
    $bookinginfo = check_active_booking($repeatedlabs, $courseid);
    $remainingtime = get_remaining_time($bookinginfo, $labstatus, $timeinfo, $idletime, $check);
    echo $remainingtime . ' ' . get_string('seconds', 'ejsapp');
} else {
    echo get_string('refresh', 'ejsapp');
}