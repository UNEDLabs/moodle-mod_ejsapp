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
 * Definition of event observers and handlers for ejsapp
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(

    array(
        'eventname' => '\mod_ejsapp\event\ejsapp_book',
        'callback' => 'mod_ejsapp\observers::ejsapp_book',
    ),

    array(
        'eventname' => '\mod_ejsapp\event\ejsapp_booked',
        'callback' => 'mod_ejsapp\observers::ejsapp_booked',
    ),

    array(
        'eventname' => '\mod_ejsapp\event\ejsapp_collab',
        'callback' => 'mod_ejsapp\observers::ejsapp_collab',
    ),

    array(
        'eventname' => '\mod_ejsapp\event\ejsapp_inactive',
        'callback' => 'mod_ejsapp\observers::ejsapp_inactive',
    ),

    array(
        'eventname' => '\mod_ejsapp\event\ejsapp_viewed',
        'callback' => 'mod_ejsapp\observers::ejsapp_viewed',
    ),

    array(
        'eventname' => '\mod_ejsapp\event\ejsapp_wait',
        'callback' => 'mod_ejsapp\observers::ejsapp_wait',
    ),

    array(
        'eventname' => '\mod_ejsapp\event\ejsapp_working',
        'callback' => 'mod_ejsapp\observers::ejsapp_working',
    ),

    array(
        'eventname' => '\mod_ejsapp\event\ejsapp_left',
        'callback' => 'mod_ejsapp\observers::ejsapp_left',
    ),

);