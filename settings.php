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
 * Use this file to configure the EJSApp module AND (when installed) the EJSApp
 * Collab Sessions block.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

//columns_width,      Total width occupied by the column(s) (in pixels) in your Moodle visual theme
//sarlab_IP,          If one or more SARLAB systems are used for accessing the remote laboratories, the list of their IPs directions must be written here.
//sarlab_port,       If one or more SARLAB systems are used for accessing the remote laboratories, the list of the ports used to connect with them must be written here.  

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'ejsapp/displaysettings',
        get_string('default_display_set', 'ejsapp'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'columns_width',
        get_string('columns_width', 'ejsapp'),
        get_string('columns_width_description', 'ejsapp'),
        480,
        PARAM_INT,
        '2'
    ));

    $settings->add(new admin_setting_heading(
        'ejsapp/communicationsettings',
        get_string('default_communication_set', 'ejsapp'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'sarlab_IP',
        get_string('sarlab_IP', 'ejsapp'),
        get_string('sarlab_IP_description', 'ejsapp'),
        '127.0.0.1',
        PARAM_TEXT,
        '13'
    ));

    $settings->add(new admin_setting_configtext(
        'sarlab_port',
        get_string('sarlab_port', 'ejsapp'),
        get_string('sarlab_port_description', 'ejsapp'),
        443,
        PARAM_TEXT,
        '2'
    ));
}
?>