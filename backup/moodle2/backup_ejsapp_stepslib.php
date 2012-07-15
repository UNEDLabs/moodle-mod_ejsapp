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


defined('MOODLE_INTERNAL') || die;

class backup_ejsapp_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // Define each element separated
        $ejsapp = new backup_nested_element('ejsapp', array('id'), array(
            'course','name', 'intro', 'introformat', 'timecreated',
            'timemodified', 'applet_name', 'class_file', 'codebase',
            'mainframe', 'is_collaborative', 'preserve_applet_size',
            'height', 'width'));

        // Define sources
        $ejsapp->set_source_table('ejsapp', array('id' => backup::VAR_ACTIVITYID));

		    // Define id annotations
        // module has no id annotations

        // Define file annotations
    	  $ejsapp->annotate_files('mod_ejsapp', 'content', null);

        // Return the root element (ejsapp), wrapped into standard activity structure
        return $this->prepare_activity_structure($ejsapp);

    }
    
}