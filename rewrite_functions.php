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
//
// at the Computer Science and Automatic Control, Spanish Open University
// (UNED), Madrid, Spain.

/**
 * Page for selecting the EjsS lab functions the teacher wants to make available for students to rewrite them
 *
 * @package    mod_ejsapp
 * @copyright  2020 Luis de la Torre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot . '/filter/multilang/filter.php');
require_once('locallib.php');

define('USER_SMALL_CLASS', 20);   // Below this is considered small.
define('USER_LARGE_CLASS', 200);  // Above this is considered large.
define('DEFAULT_PAGE_SIZE', 20);
define('SHOW_ALL_PAGE_SIZE', 5000);

$id = required_param('id', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$cm = get_coursemodule_from_id('ejsapp', $id, 0, false, MUST_EXIST);

$ejsappid = optional_param('ejsappid', 0, PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course, true, $cm);
$contextmod = context_module::instance($cm->id);
$context = context_course::instance($courseid);

$PAGE->set_url('/mod/ejsapp/rewrite_functions.php', array('id' => $id, 'courseid' => $courseid));
$PAGE->set_context($contextmod);
$title = get_string('rewriteFuncs_pageTitle', 'ejsapp');
$PAGE->set_title($title);
echo $OUTPUT->header();
$PAGE->set_heading($title);

$systemcontext = context_system::instance();
$isfrontpage = ($course->id == SITEID);

$frontpagectx = context_course::instance(SITEID);

if ($isfrontpage) {
    $PAGE->set_pagelayout('admin');
} else {
    $PAGE->set_pagelayout('incourse');
}

// TODO

echo $OUTPUT->footer();