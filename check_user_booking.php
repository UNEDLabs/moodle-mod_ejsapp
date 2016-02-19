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
 * Checks if the specified user has an active booking for the specified remote ejsapp lab
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $DB;

$username = required_param('username', PARAM_TEXT);
$ejsappid = required_param('ejsappid', PARAM_TEXT);

if ($DB->record_exists('ejsappbooking_remlab_access', array('username' => $username, 'ejsappid' => $ejsappid, 'valid' => 1))) {
    $bookings = $DB->get_records('ejsappbooking_remlab_access', array('username' => $username, 'ejsappid' => $ejsappid, 'valid' => 1));
    foreach ($bookings as $booking) { // If the user has an active booking, check the time
        if (date('Y-m-d H:i:s') >= $booking->starttime && date('Y-m-d H:i:s') < $booking->endtime) {
            echo "access=true\n";
            break;
        }
    }
}