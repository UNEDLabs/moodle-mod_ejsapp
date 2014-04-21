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
//certificate_alia,     The alias of your trust certificate.
//sarlab_IP,            If one or more SARLAB systems are used for accessing the remote laboratories, the list of their IPs directions must be written here.
//sarlab_port,          If one or more SARLAB systems are used for accessing the remote laboratories, the list of the ports used to connect with them must be written here.
//sarlab_enc_key        If one or more SARLAB systems are used for accessing the remote laboratories, the list of their encoding keys must be written here.

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'ejsapp/certificatesettings',
        get_string('default_certificate_set', 'ejsapp'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'ejsapp/certificate_path',
        get_string('certificate_path', 'ejsapp'),
        get_string('certificate_path_description', 'ejsapp'),
        dirname(__FILE__) . '/firmadia.pfx',
        PARAM_TEXT,
        '20'
    ));

    $settings->add(new admin_setting_configtext(
        'ejsapp/certificate_password',
        get_string('certificate_password', 'ejsapp'),
        get_string('certificate_password_description', 'ejsapp'),
        '',
        PARAM_TEXT,
        '20'
    ));

    $settings->add(new admin_setting_configtext(
        'ejsapp/certificate_alias',
        get_string('certificate_alias', 'ejsapp'),
        get_string('certificate_alias_description', 'ejsapp'),
        '',
        PARAM_TEXT,
        '40'
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
        '4'
    ));

    $settings->add(new admin_setting_configtext(
        'sarlab_enc_key',
        get_string('sarlab_enc_key', 'ejsapp'),
        get_string('sarlab_enc_key_description', 'ejsapp'),
        '1234567890123456',
        PARAM_TEXT,
        '30'
    ));
}