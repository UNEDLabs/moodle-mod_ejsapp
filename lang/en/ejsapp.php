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
 * English strings for ejsapp
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'EJSApp';
$string['modulenameplural'] = 'EJSApps';
$string['modulename_help'] = 'The EJSApp activity module enables teachers to add a java applets created with Easy Java Simulations (EJS) into their Moodle courses.

EJS applets will be embedded into Moodle courses. The teacher can select to keep the orginal applet size or let Moodle to resize it according to the available space. If the EJS applet was compiled using the "Add language facilities" option in EJS, the applet embedded into Moodle with the EJSApp activity will automatically set its language to the one selected by the user in Moodle, if possible. This activity supports configuring conditional access restrictions.

When used along with the EJSApp File Browser block, students can save the state of the EJS applet, when it is running, by just right-clicking on it and selecting the proper option in the menu. The information of these states is saved into an .xml file which is stored in the private files area (EJSApp File Browser). These states can be recovered by an EJS applet in two different ways: clicking on the .xml files in the EJSApp File Browser block or right-clicking on the EJS applet and selecting the proper option in the menu. If the EJS applet is prepared to do so, it can also save text or image files and store them in the private files area.

When used along with the EJSApp Collab Sessions block, Moodle users can work with the same EJS applet in a synchronous way, meaning the applet will show the same state for all the users in the collaborative session. Thanks to this block, users can create sessions, invite other users and work together with the same EJSApp activity.';
$string['ejsappfieldset'] = 'Custom example fieldset';
$string['ejsappname'] = 'Lab name';
$string['ejsappname_help'] = 'Name that will appear in the course for this laboratory';
$string['ejsapp'] = 'EJSApp';
$string['pluginadministration'] = 'EJSApp administration';
$string['pluginname'] = 'EJSApp';

$string['state_load_msg'] = 'The lab state is going to be updated';
$string['state_fail_msg'] = 'Error while trying to load the state';

$string['more_text'] = 'Optional text after the applet';

$string['jar_file'] = '.jar file that encapsulates the  EJS lab';

$string['appletfile'] = 'Easy Java Simulation';
$string['appletfile_required'] = 'A .jar file must be selected';
$string['appletfile_help'] = 'Select the .jar file that encapsulates the Easy Java Simulation (EJS) application. The official website of EJS is http://fem.um.es/Ejs/';

$string['applet_size_conf'] = 'Size the applet';
$string['applet_size_conf_help'] = 'Three options: 1) "Preserve original size" will preserve the original size of the EJS applet, 2) "Let Moodle set the size" will resize the applet to take up all the possible space while mantaining the original aspect ratio, 3) "Let the user set the size" will let the user to set the size of the applet and select whether to preserve its original aspect ratio or not.';
$string['preserve_applet_size'] = 'Preserve original size';
$string['moodle_resize'] = 'Let Moodle set the size';
$string['user_resize'] = 'Let the user set the size';

$string['preserve_aspect_ratio'] = 'Preserve aspect ratio';
$string['preserve_aspect_ratio_help'] = 'If this option is selected, the original aspect ratio of the applet will be respected. In this case, the user will be able to modify the width of the applet and the system will automatically adjust its height. If this option is set to "no", the user will be able to set both the width and the height of the applet.';

$string['custom_width'] = 'Applet width (px)';
$string['custom_width_required'] = 'WARNING: Applet width was not set. You must provide a different value.';

$string['custom_height'] = 'Applet height (px)';
$string['custom_height_required'] = 'WARNING: Applet height was not set. You must provide a different value.';

$string['appwording'] = 'Wording';

$string['state_file'] = '.xml file with the state to be read when this EJS lab loads';

$string['statefile'] = 'Easy Java Simulation State';
$string['statefile_help'] = 'Select the .xml file with the state the Easy Java Simulation (EJS) application should load'.

$string['rem_lab_conf'] = 'Remote Lab Configuration';

$string['is_rem_lab'] = 'Remote experimental system?';
$string['is_rem_lab_help'] = 'If this EJSApp connects to real remote resources AND you want the EJSApp Booking System to manage their access, select "yes". Otherwise, select "no".';

$string['sarlab'] = "Using Sarlab?";
$string['sarlab_help'] = "Only select yes if you are using Sarlab; a system that manages connections to remote laboratories resources";

$string['sarlab_instance'] = "Sarlab server for this lab";
$string['sarlab_instance_help'] = "The order corresponds to the one used for the values in the sarlab_IP and sarlab_port variables configured at the ejsapp settings page";

$string['ip_lab'] = 'IP direction';
$string['ip_lab_help'] = 'Esperimental system IP direction. If you are using Sarlab, you dont have to worry about this parameter.';
$string['ip_lab_required'] = 'WARNING: You need to provide a valid IP direction.';
$string['port'] = 'Port';
$string['port_help'] = 'The port used to establish the communication. If you are using Sarlab, you dont have to worry about this parameter.';
$string['port_required'] = 'WARNING: You need to provide a valid port.';
$string['practiceintro'] = 'Practice identifier in Sarlab';
$string['practiceintro_help'] = 'Practices (separated by semicolons) configured in Sarlab for this experimental system.';
$string['practiceintro_required'] = 'WARNING: You need to specify at least one practice.';
$string['totalslots'] = 'Total hours of work';
$string['totalslots_help'] = 'Total amount of maximum hours each student will be allowed to work with this lab.';
$string['weeklyslots'] = 'Weekly hours of work';
$string['weeklyslots_help'] = 'Weekly amount of maximum hours each student will be allowed to work with this lab.';
$string['dailyslots'] = 'Daily hours of work';
$string['dailyslots_help'] = 'Daily amount of maximum hours each student will be allowed to work with this lab.';

$string['file_error'] = "Can't open file from the server";
$string['manifest_error'] = " > Can't find or open manifest .mf. Check the file you uploaded.";

$string['no_booking'] = 'You do not have an active booking for this lab.';
$string['check_bookings'] = 'Check your active bookings with the booking system.';

$string['ejsapp_error'] = 'The EJSApp activity you are trying to access does not exist.';

//Settings
$string['default_display_set'] = "Default display settings";
$string['default_communication_set'] = "Default communication settings";
$string['columns_width'] = "Columns width";
$string['columns_width_description'] = "Total width (px) occupied by the columns in your Moodle visual theme";
//$string['sarlab_description'] = "Only select yes if you are using Sarlab; a system that manages connections to remote laboratories resources";
$string['collaborative_port'] = "Port for collaborative sessions";
$string['collaborative_port_description'] = "Port used to establish communication for the collaborative sessions (requires the EJSApp collab sessions block)";
$string['sarlab_IP'] = "IP direction of the Sarlab server";
$string['sarlab_IP_description'] = "If you are using Sarlab (a system that manages connections to remote laboratories resources), you need to provide the IP direction of the server that runs the Sarlab system you want to use. Otherwise, this value will not be used, so you can leave the default value. If you have more than one sarlab server (for example, one at 127.0.0.1 and a second one at 127.0.0.2), insert the IPs direction separated by semicolons: 127.0.0.1;127.0.0.2";
$string['sarlab_port'] = "Sarlab communication port";
$string['sarlab_port_description'] = "If you are using Sarlab (a system that manages connections to remote laboratories resources), you need to provide a valid port for establishing the communications with the Sarlab server. Otherwise, this value will not be used, so you can leave the default value. If you have more than one sarlab server (for example, one using port 443 and a second one also using port 443), insert the values separated by semicolons: 443;443";