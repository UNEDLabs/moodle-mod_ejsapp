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
 * Tasks file to perform the EJSApp backup
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once(dirname(dirname(dirname(__FILE__))) . '/locallib.php');

/**
 * Structure step to restore one EJSApp activity
 *
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_ejsapp_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define structure
     */
    protected function define_structure() {
        $paths = array();
        $paths[] = new restore_path_element('ejsapp', '/activity/ejsapp');
        $paths[] = new restore_path_element('ejsapp_personal_vars',
            '/activity/ejsapp/ejsapp_personal_vars');
        $paths[] = new restore_path_element('remlab_manager_exp2prc',
            '/activity/ejsapp/remlab_manager_exp2practs/block_remlab_manager_exp2prc');
        $paths[] = new restore_path_element('ejsappbooking', '/activity/ejsapp/ejsappbookings/ejsappbooking');

        $userinfo = $this->get_setting_value('userinfo');
        if ($userinfo) {
            $paths[] = new restore_path_element('ejsappbooking_remlab_access',
                '/activity/ejsapp/ejsappbooking_remlab_accesses/ejsappbooking_remlab_access');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process table ejsapp
     * @param stdClass $data
     * @throws
     */
    protected function process_ejsapp($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->course = $this->get_courseid();

        // Insert the ejsapp record.
        $newitemid = $DB->insert_record('ejsapp', $data);
        $this->apply_activity_instance($newitemid);

        // Mapping old_ejsapp_id->new_old_ejsapp_id for associated files (see after_execute).
        $this->set_mapping('ejsapp', $oldid, $newitemid, true);
    }

    /**
     * Process table ejsapp_personal_vars
     * @param stdClass $data
     * @throws
     */
    protected function process_ejsapp_personal_vars($data) {
        global $DB;

        $data = (object)$data;
        $data->ejsappid = $this->get_new_parentid('ejsapp');
        $DB->insert_record('ejsapp_personal_vars', $data);
    }

    /**
     * Process table process_remlab_manager_exp2prc
     * @param stdClass $data
     * @throws
     */
    protected function process_remlab_manager_exp2prc($data) {
        global $DB;

        $data = (object)$data;
        $data->ejsappid = $this->get_new_parentid('ejsapp');
        $DB->insert_record('block_remlab_manager_exp2prc', $data);
    }

    /**
     * Process table ejsappbooking
     * @param stdClass $data
     * @throws
     */
    protected function process_ejsappbooking($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $ejsappbooking = $DB->get_records('ejsappbooking', array('course' => $data->course));
        $ejsappbooking = !empty($ejsappbooking);
        if (!$ejsappbooking) {
            $DB->insert_record('ejsappbooking', $data);
        }
    }

    /**
     * Process table process_ejsappbooking_remlab_access
     * @param stdClass $data
     * @throws
     */
    protected function process_ejsappbooking_remlab_access($data) {
        global $DB;

        $data = (object)$data;
        // There is no necessity of mapping for "practiceid" nor "username".
        $data->ejsappid = $this->get_new_parentid('ejsapp');
        $DB->insert_record('ejsappbooking_remlab_access', $data);
    }

    protected function after_execute() {
        // Add ejsapp related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_ejsapp', 'compressed', 'ejsapp');
        $this->add_related_files('mod_ejsapp', 'content', 'ejsapp');
        $this->add_related_files('mod_ejsapp', 'xmlfiles', 'ejsapp');
        $this->add_related_files('mod_ejsapp', 'recfiles', 'ejsapp');
        $this->add_related_files('mod_ejsapp', 'blkfiles', 'ejsapp');
    }

}