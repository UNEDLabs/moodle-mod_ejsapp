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
 * Defines the version of ejsapp
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre, Ruben Heradio and Javier Pavón
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$module->version  = 2012072501;  // The current module version (Date: YYYYMMDDXX)
$module->requires = 2010112400;
$module->cron     = 0;           // Period for cron to check this module (secs)
$module->component = 'mod_ejsapp'; // To check on upgrade, that module sits in correct place
$module->maturity = MATURITY_STABLE;
$module->release = '1.1 (Build: 2012072501)';