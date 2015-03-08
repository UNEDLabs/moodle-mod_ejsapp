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
 * Tasks file to perform the EJSApp backup
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/ejsapp/backup/moodle2/backup_ejsapp_stepslib.php');
require_once($CFG->dirroot . '/mod/ejsapp/backup/moodle2/backup_ejsapp_settingslib.php');

/**
 * EJSApp backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_ejsapp_activity_task extends backup_activity_task
{
    /**
     * Define particular settings for this activity
     */
    protected function define_my_settings()
    {
        // No particular settings for this activity
    }

    /**
     * Caller to define_structure->define_structure
     */
    protected function define_my_steps()
    {
        $this->add_step(new backup_ejsapp_activity_structure_step('ejsapp_structure', 'ejsapp.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     *
     * @param string $content
     * @return string $content
     */
    static public function encode_content_links($content)
    {
        global $CFG;

        $base = preg_quote($CFG->wwwroot . '/mod/ejsapp', '#');

        //Access a list of all links in a course
        $pattern = '#(' . $base . '/index\.php\?id=)([0-9]+)#';
        $replacement = '$@EJSAPPINDEX*$2@$';
        $content = preg_replace($pattern, $replacement, $content);

        //Access the link supplying a course module id
        $pattern = '#(' . $base . '/view\.php\?id=)([0-9]+)#';
        $replacement = '$@EJSAPPVIEWBYID*$2@$';
        $content = preg_replace($pattern, $replacement, $content);

        return $content;
    }
}