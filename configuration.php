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

$COLUMNS_WIDTH = 480;       //Total width occupied by your columns (in pixels)
                            //in your Moodle visual theme
$APPLET_WIDTH = 800-$COLUMNS_WIDTH; //Minimum width (in pixels) used by the main
                                    //frame (where the applet is embeded) in
                                    //your Moodle theme
$COLLABORATIVE_PORT = 50000; //Port used for establishing TCP connections in the
                             //collaborative sessions (requires the EJSApp 
                             //Collab Sessions block)

?>