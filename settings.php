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

//certificate_path,     The path to your trust certificate for signing the java applets.
//certificate_password, The password for using your trust certificate.
//certificate_alias,    The alias of your trust certificate.

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'mod_ejsapp/generalsettings',
        get_string('default_general_set', 'ejsapp'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'mod_ejsapp/check_activity',
        get_string('check_activity', 'ejsapp'),
        get_string('check_activity_description', 'ejsapp'),
        60,
        PARAM_INT,
        '8'
    ));

    $settings->add(new admin_setting_heading(
        'mod_ejsapp/certificatesettings',
        get_string('default_certificate_set', 'ejsapp'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'mod_ejsapp/certificate_path',
        get_string('certificate_path', 'ejsapp'),
        get_string('certificate_path_description', 'ejsapp'),
        $CFG->dataroot . '/firmadia.pfx',
        PARAM_TEXT,
        '20'
    ));

    $settings->add(new admin_setting_configtext(
        'mod_ejsapp/certificate_password',
        get_string('certificate_password', 'ejsapp'),
        get_string('certificate_password_description', 'ejsapp'),
        '',
        PARAM_TEXT,
        '20'
    ));

    $settings->add(new admin_setting_configtext(
        'mod_ejsapp/certificate_alias',
        get_string('certificate_alias', 'ejsapp'),
        get_string('certificate_alias_description', 'ejsapp'),
        '',
        PARAM_TEXT,
        '40'
    ));
}