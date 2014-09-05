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
 * EJSApp settings form.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/filestorage/zip_packer.php');
require_once('locallib.php');


/**
 * Class that defines the EJSApp settings form.
 */
class mod_ejsapp_mod_form extends moodleform_mod
{


    /**
     * Called from Moodle to define this form
     *
     * @return void
     */
    function definition()
    {
        global $CFG, $DB, $USER;
        $mform = & $this->_form;
        // -------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('ejsappname', 'ejsapp'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_NOTAGS);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'ejsappname', 'ejsapp');
        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor();
        // -------------------------------------------------------------------------------
        // Adding other ejsapp settings by adding more fieldsets
        $mform->addElement('header', 'conf_parameters', get_string('jar_file', 'ejsapp'));

        $mform->addElement('hidden', 'class_file', null);
        $mform->setType('class_file', PARAM_TEXT);
        $mform->setDefault('class_file', 'null');

        $mform->addElement('hidden', 'codebase', null);
        $mform->setType('codebase', PARAM_TEXT);
        $mform->setDefault('codebase', 'null');

        $mform->addElement('hidden', 'mainframe', null);
        $mform->setType('mainframe', PARAM_TEXT);
        $mform->setDefault('mainframe', 'null');

        $mform->addElement('hidden', 'is_collaborative', null);
        $mform->setType('is_collaborative', PARAM_TEXT);
        $mform->setDefault('is_collaborative', 0);

        $maxbytes = get_max_upload_file_size($CFG->maxbytes);
        $mform->addElement('filepicker', 'appletfile', get_string('file'), null, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => array('application/java-archive', 'application/zip')));
        $mform->addRule('appletfile', get_string('appletfile_required', 'ejsapp'), 'required');
        $mform->addHelpButton('appletfile', 'appletfile', 'ejsapp');

        $mform->addElement('select', 'applet_size_conf', get_string('applet_size_conf','ejsapp'), array(get_string('preserve_applet_size','ejsapp'), get_string('moodle_resize','ejsapp'), get_string('user_resize','ejsapp')));
        $mform->addHelpButton('applet_size_conf', 'applet_size_conf', 'ejsapp');

        $mform->addElement('selectyesno', 'preserve_aspect_ratio', get_string('preserve_aspect_ratio', 'ejsapp'));
        $mform->addHelpButton('preserve_aspect_ratio', 'preserve_aspect_ratio', 'ejsapp');
        $mform->disabledIf('preserve_aspect_ratio', 'applet_size_conf', 'neq', 2);

        $mform->addElement('text', 'custom_width', get_string('custom_width', 'ejsapp'), array('size' => '3'));
        $mform->setType('custom_width', PARAM_INT);
        $mform->disabledIf('custom_width', 'applet_size_conf', 'neq', 2);

        $mform->addElement('text', 'custom_height', get_string('custom_height', 'ejsapp'), array('size' => '3'));
        $mform->setType('custom_height', PARAM_INT);
        $mform->disabledIf('custom_height', 'applet_size_conf', 'neq', 2);
        $mform->disabledIf('custom_height', 'preserve_aspect_ratio', 'eq', 1);
        // -------------------------------------------------------------------------------
        // More optional text to be shown after the applet
        $mform->addElement('header', 'more_text', get_string('more_text', 'ejsapp'));

        $mform->addElement('editor', 'ejsappwording', get_string('appwording', 'ejsapp'), null, array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1, 'context' => $this->context, 'noclean' => 1, 'trusttext' => 0));
        $mform->setType('appwording', PARAM_RAW);
        // -------------------------------------------------------------------------------
        // Optional CSS styles
        $mform->addElement('header', 'css_style', get_string('css_style', 'ejsapp'));

        $mform->addElement('textarea', 'css', get_string('css_rules', 'ejsapp'), 'wrap="virtual" rows="8" cols="50"');
        // -------------------------------------------------------------------------------
        // Adding an optional state file to be read when the applet loads
        $mform->addElement('header', 'state_file', get_string('state_file', 'ejsapp'));

        $mform->addElement('filemanager', 'statefile', get_string('file'), null, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => 'application/xml'));
        $mform->addHelpButton('statefile', 'statefile', 'ejsapp');
        // -------------------------------------------------------------------------------
        // Personalize variables from the EJS application
        $mform->addElement('header', 'personalize_vars', get_string('personalize_vars', 'ejsapp'));

        $mform->addElement('selectyesno', 'personalvars', get_string('use_personalized_vars', 'ejsapp'));
        $mform->addHelpButton('personalvars', 'use_personalized_vars', 'ejsapp');

        $varsarray = array();
        $varsarray[] = $mform->createElement('text', 'var_name', get_string('var_name', 'ejsapp'));
        $varsarray[] = $mform->createElement('select', 'var_type', get_string('var_type', 'ejsapp'),  array('Boolean', 'Integer', 'Double'));
        $varsarray[] = $mform->createElement('text', 'min_value', get_string('min_value', 'ejsapp'), array('size' => '8'));
        $varsarray[] = $mform->createElement('text', 'max_value', get_string('max_value', 'ejsapp'), array('size' => '8'));

        $repeateloptions = array();
        $repeateloptions['var_name']['disabledif'] = array('personalvars', 'eq', 0);
        $repeateloptions['var_name']['type'] = PARAM_TEXT;
        $repeateloptions['var_name']['helpbutton'] = array('var_name', 'ejsapp');
        $repeateloptions['var_type']['disabledif'] = array('personalvars', 'eq', 0);
        $repeateloptions['var_type']['type'] = PARAM_TEXT;
        $repeateloptions['var_type']['helpbutton'] = array('var_type', 'ejsapp');
        $repeateloptions['min_value']['disabledif'] = array('personalvars', 'eq', 0);
        $repeateloptions['min_value']['disabledif'] = array('var_type', 'eq', 0);
        $repeateloptions['min_value']['type'] = PARAM_FLOAT;
        $repeateloptions['min_value']['helpbutton'] = array('min_value', 'ejsapp');
        $repeateloptions['max_value']['disabledif'] = array('personalvars', 'eq', 0);
        $repeateloptions['max_value']['disabledif'] = array('var_type', 'eq', 0);
        $repeateloptions['max_value']['type'] = PARAM_FLOAT;
        $repeateloptions['max_value']['helpbutton'] = array('max_value', 'ejsapp');

        $no = 2;
        if ($this->current->instance) {
            if ($personal_vars = $DB->get_records('ejsapp_personal_vars', array('ejsappid' => $this->current->instance))) {
                $no = count($personal_vars);
            }
        }

        $this->repeat_elements($varsarray, $no, $repeateloptions, 'option_repeats', 'option_add_vars', 2, null, true);
        // -------------------------------------------------------------------------------
        // Adding an optional text file with an experiment to automatically run it when the applet loads
        $mform->addElement('header', 'experiment_file', get_string('experiment_file', 'ejsapp'));

        $mform->addElement('filemanager', 'expfile', get_string('file'), null, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => '.exp'));
        $mform->addHelpButton('expfile', 'expfile', 'ejsapp');
        // -------------------------------------------------------------------------------
        // Adding elements to configure the remote lab, if that's the case
        $mform->addElement('header', 'rem_lab', get_string('rem_lab_conf', 'ejsapp'));

        $mform->addElement('selectyesno', 'is_rem_lab', get_string('is_rem_lab', 'ejsapp'));
        $mform->addHelpButton('is_rem_lab', 'is_rem_lab', 'ejsapp');

        $mform->addElement('selectyesno', 'sarlab', get_string('sarlab', 'ejsapp'));
        $mform->addHelpButton('sarlab', 'sarlab', 'ejsapp');
        $mform->disabledIf('sarlab', 'is_rem_lab', 'eq', 0);
        if ($this->current->instance) {
            $rem_lab_data = $DB->get_record('ejsapp_remlab_conf', array('ejsappid' => $this->current->instance));
            if ($rem_lab_data) {
                $mform->setDefault('sarlab', $rem_lab_data->usingsarlab);
            }
        }

        $list_sarlab_IPs = explode(";", $CFG->sarlab_IP);
        if(is_array($list_sarlab_IPs)) $sarlab_IP = $list_sarlab_IPs[0];
        else  $sarlab_IP = $CFG->sarlab_IP;
        $init_pos = strpos($sarlab_IP, "'");
        $end_pos = strrpos($sarlab_IP, "'");
        if(($init_pos === false) || ($init_pos === $end_pos)) {
            $sarlab_instance_options = array('Sarlab server 1');
        } else {
            $sarlab_instance_options = array(substr($sarlab_IP,$init_pos+1,$end_pos-$init_pos-1));
        }
        for ($i = 1; $i < count($list_sarlab_IPs); $i++) {
            $sarlab_instance_options_temp = $list_sarlab_IPs[$i];
            $init_pos = strpos($sarlab_instance_options_temp, "'");
            $end_pos = strrpos($sarlab_instance_options_temp, "'");
            if(($init_pos === false) || ($init_pos === $end_pos)) {
                array_push($sarlab_instance_options, 'Sarlab server ' . ($i+1));
            } else {
                array_push($sarlab_instance_options, substr($sarlab_instance_options_temp,$init_pos+1,$end_pos-$init_pos-1));
            }
        }

        $mform->addElement('select', 'sarlab_instance', get_string('sarlab_instance', 'ejsapp'), $sarlab_instance_options);
        $mform->addHelpButton('sarlab_instance', 'sarlab_instance', 'ejsapp');
        $mform->disabledIf('sarlab_instance', 'is_rem_lab', 'eq', 0);
        $mform->disabledIf('sarlab_instance', 'sarlab', 'eq', 0);
        if ($this->current->instance) {
            if ($rem_lab_data) {
                $mform->setDefault('sarlab_instance', $rem_lab_data->sarlabinstance);
            }
        }
        
        $mform->addElement('selectyesno', 'sarlab_collab', get_string('sarlab_collab', 'ejsapp'));
        $mform->addHelpButton('sarlab_collab', 'sarlab_collab', 'ejsapp');
        $mform->disabledIf('sarlab_instance', 'is_rem_lab', 'eq', 0);
        $mform->disabledIf('sarlab_collab', 'sarlab', 'eq', 0);
        if ($this->current->instance) {
            $rem_lab_data = $DB->get_record('ejsapp_remlab_conf', array('ejsappid' => $this->current->instance));
            if ($rem_lab_data) {
                $mform->setDefault('sarlab_collab', $rem_lab_data->sarlabcollab);
            }
        }

        // Obtain the list of Sarlab experiences the current user can configure and add them to the form
        $listExperiences = get_experiences_sarlab($USER->username, $list_sarlab_IPs);
        $list_sarlab_experiences = explode(";", $listExperiences);
        $select_practice = $mform->addElement('select', 'practiceintro', get_string('practiceintro', 'ejsapp'), $list_sarlab_experiences);
        $mform->addHelpButton('practiceintro', 'practiceintro', 'ejsapp');
        $mform->disabledIf('practiceintro', 'is_rem_lab', 'eq', 0);
        $mform->disabledIf('practiceintro', 'sarlab', 'eq', 0);
        $select_practice->setMultiple(true);
        if ($this->current->instance) {
            $practices_data = $DB->get_records('ejsapp_expsyst2pract', array('ejsappid' => $this->current->instance));
            if ($practices_data) {
                $selected_practice_index = array();
                foreach ($practices_data as $practice_data) {
                    $i = 0;
                    foreach ($list_sarlab_experiences as $sarlab_experience) {
                        if ($practice_data->practiceintro == $sarlab_experience) {
                            array_push($selected_practice_index, $i);
                            break;
                        }
                        $i++;
                    }
                }
                $mform->setDefault('practiceintro', $selected_practice_index);
            }
        }
        $mform->addElement('hidden', 'list_practices', null);
        $mform->setType('list_practices', PARAM_TEXT);
        $mform->setDefault('list_practices', $listExperiences);

        $mform->addElement('text', 'ip_lab', get_string('ip_lab', 'ejsapp'), array('size' => '12'));
        $mform->setType('ip_lab', PARAM_TEXT);
        $mform->addRule('ip_lab', get_string('maximumchars', '', 15), 'maxlength', 15, 'client');
        $mform->addHelpButton('ip_lab', 'ip_lab', 'ejsapp');
        $mform->disabledIf('ip_lab', 'is_rem_lab', 'eq', 0);
        $mform->disabledIf('ip_lab', 'sarlab', 'eq', 1);
        if ($this->current->instance) {
            if ($rem_lab_data) {
                $mform->setDefault('ip_lab', $rem_lab_data->ip);
            }
        }

        $mform->addElement('text', 'port', get_string('port', 'ejsapp'), array('size' => '2'));
        $mform->setType('port', PARAM_INT);
        $mform->addRule('port', get_string('maximumchars', '', 6), 'maxlength', 6, 'client');
        $mform->addHelpButton('port', 'port', 'ejsapp');
        $mform->disabledIf('port', 'is_rem_lab', 'eq', 0);
        $mform->disabledIf('port', 'sarlab', 'eq', 1);
        if ($this->current->instance) {
            if ($rem_lab_data) {
                $mform->setDefault('port', $rem_lab_data->port);
            }
        }

        $mform->addElement('selectyesno', 'active', get_string('active', 'ejsapp'));
        $mform->addHelpButton('active', 'active', 'ejsapp');
        $mform->disabledIf('active', 'is_rem_lab', 'eq', 0);
        if ($this->current->instance) {
            if ($rem_lab_data) {
                $mform->setDefault('active', $rem_lab_data->active);
            } else $mform->setDefault('active', '1');
        } else $mform->setDefault('active', '1');

        $mform->addElement('selectyesno', 'free_access', get_string('free_access', 'ejsapp'));
        $mform->addHelpButton('free_access', 'free_access', 'ejsapp');
        $mform->disabledIf('free_access', 'is_rem_lab', 'eq', 0);

        $mform->addElement('text', 'totalslots', get_string('totalslots', 'ejsapp'), array('size' => '2'));
        $mform->setType('totalslots', PARAM_INT);
        $mform->addRule('totalslots', get_string('maximumchars', '', 5), 'maxlength', 5, 'client');
        $mform->addHelpButton('totalslots', 'totalslots', 'ejsapp');
        $mform->disabledIf('totalslots', 'is_rem_lab', 'eq', 0);
        $mform->disabledIf('totalslots', 'free_access', 'eq', 1);
        if ($this->current->instance && $rem_lab_data) {
            $mform->setDefault('totalslots', $rem_lab_data->totalslots);
        } else {
            $mform->setDefault('totalslots', 18);
        }

        $mform->addElement('text', 'weeklyslots', get_string('weeklyslots', 'ejsapp'), array('size' => '2'));
        $mform->setType('weeklyslots', PARAM_INT);
        $mform->addRule('weeklyslots', get_string('maximumchars', '', 3), 'maxlength', 3, 'client');
        $mform->addHelpButton('weeklyslots', 'weeklyslots', 'ejsapp');
        $mform->disabledIf('weeklyslots', 'is_rem_lab', 'eq', 0);
        $mform->disabledIf('weeklyslots', 'free_access', 'eq', 1);
        if ($this->current->instance && $rem_lab_data) {
            $mform->setDefault('weeklyslots', $rem_lab_data->weeklyslots);
        } else {
            $mform->setDefault('weeklyslots', 9);
        }

        $mform->addElement('text', 'dailyslots', get_string('dailyslots', 'ejsapp'), array('size' => '2'));
        $mform->setType('dailyslots', PARAM_INT);
        $mform->addRule('dailyslots', get_string('maximumchars', '', 2), 'maxlength', 2, 'client');
        $mform->addHelpButton('dailyslots', 'dailyslots', 'ejsapp');
        $mform->disabledIf('dailyslots', 'is_rem_lab', 'eq', 0);
        $mform->disabledIf('dailyslots', 'free_access', 'eq', 1);
        if ($this->current->instance && $rem_lab_data) {
            $mform->setDefault('dailyslots', $rem_lab_data->dailyslots);
        } else {
            $mform->setDefault('dailyslots', 3);
        }

        $mform->setAdvanced('rem_lab');
        // -------------------------------------------------------------------------------
        // Add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        // -------------------------------------------------------------------------------
        // Add standard buttons, common to all modules
        $this->add_action_buttons();
    } // definition


    /**
     * Any data processing needed before the form is displayed
     * (needed to set up draft areas for editor and filemanager elements)
     * @param array &$default_values
     */
    function data_preprocessing(&$default_values)
    {
        global $CFG, $DB;
        $mform = $this->_form;

        $maxbytes = get_max_upload_file_size($CFG->maxbytes);

        // Fill the form elements with previous submitted files/data
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('appletfile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_ejsapp', 'jarfiles', $this->current->instance, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => array('application/java-archive', 'application/zip')));
            $default_values['appletfile'] = $draftitemid;

            $draftitemid = file_get_submitted_draft_itemid('appwording');
            $default_values['ejsappwording']['format'] = $default_values['appwordingformat'];
            $default_values['ejsappwording']['text'] = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_ejsapp', 'appwording', 0, array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => 1, 'changeformat' => 1, 'context' => $this->context, 'noclean' => 1, 'trusttext' => 0), $default_values['appwording']);
            $default_values['ejsappwording']['itemid'] = $draftitemid;
            
            $draftitemid = file_get_submitted_draft_itemid('statefile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_ejsapp', 'xmlfiles', $this->current->instance, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => 'application/xml'));
            $default_values['statefile'] = $draftitemid;

            $draftitemid = file_get_submitted_draft_itemid('expfile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_ejsapp', 'expfiles', $this->current->instance, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));
            $default_values['expfile'] = $draftitemid;

            $personal_vars = $DB->get_records('ejsapp_personal_vars', array('ejsappid' => $this->current->instance));
            $key = 0;
            foreach ($personal_vars as $personal_var) {
                $default_values['var_name['.$key.']'] = $personal_var->name;
                $vartype = '0';
                if ($personal_var->type == 'Integer') $vartype = '1';
                elseif ($personal_var->type == 'Double') $vartype = '2';
                $default_values['var_type['.$key.']'] = $vartype;
                if ($vartype != 0) {
                    $default_values['min_value['.$key.']'] = $personal_var->minval;
                    $default_values['max_value['.$key.']'] = $personal_var->maxval;
                }
                $key ++;
            }
        }

        $content = $this->get_file_content('appletfile');
        if ($content) {
            $form_data = $this->get_data();

            // Create folders to store the .jar file
            if (!file_exists($CFG->dirroot . '/mod/ejsapp/jarfiles/')) {
                mkdir($CFG->dirroot . '/mod/ejsapp/jarfiles/', 0777);
            }
            if (!file_exists($CFG->dirroot . '/mod/ejsapp/jarfiles/' . $form_data->course)) {
                mkdir($CFG->dirroot . '/mod/ejsapp/jarfiles/' . $form_data->course, 0777);
            }
            $name = delete_non_alphanumeric_symbols($form_data->name);
            if (!file_exists($CFG->dirroot . '/mod/ejsapp/jarfiles/' . $form_data->course . '/' . $name)) {
                mkdir($CFG->dirroot . '/mod/ejsapp/jarfiles/' . $form_data->course . '/' . $name, 0777);
            }

            $applet_name = $this->get_new_filename('appletfile');

            // Store the .jar file in the proper folder
            $path = $CFG->dirroot . '/mod/ejsapp/jarfiles/' . $form_data->course . '/' . $name . '/';
            $filename = $path . $applet_name;
            $this->save_file('appletfile', $filename, true);

            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $manifest = 'EJsS';
            $metadata = '';
            if ($ext == 'jar') {
                // Extract the manifest.mf file from the .jar
                $manifest = file_get_contents('zip://' . $filename . '#' . 'META-INF/MANIFEST.MF');
            } else {
                // Extract the _metadata.txt file from the .jar
                $metadata = file_get_contents('zip://' . $filename . '#' . '_metadata.txt');
            }
            $mform->addElement('hidden', 'manifest', null);
            $mform->setType('manifest', PARAM_TEXT);
            $mform->setDefault('manifest', $manifest);
            $mform->addElement('hidden', 'metadata', null);
            $mform->setType('metadata', PARAM_TEXT);
            $mform->setDefault('metadata', $metadata);

            // Set the mod_form element
            if ($ext == 'jar') {
                $pattern = '/(\w+)[.]jar/';
            } else {
                $pattern = '/(\w+)[.]zip/';
            }
            preg_match($pattern, $applet_name, $matches, PREG_OFFSET_CAPTURE);
            $applet_name = $matches[1][0];
            $mform->addElement('hidden', 'applet_name', null);
            $mform->setType('applet_name', PARAM_TEXT);
            $mform->setDefault('applet_name', $applet_name);
            $default_values['applet_name'] = $applet_name;
        } //if ($content)
    } // data_preprocessing


    /**
     * Performs minimal validation on the settings form
     * @param array $data
     * @param array $files
     * @return array $errors
     */
    function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        if ($data['applet_size_conf'] == 2) {
            if (empty($data['custom_width'])) {
                $errors['custom_width'] = get_string('custom_width_required', 'ejsapp');
            }
            if ($data['preserve_aspect_ratio'] == 0) {
                if (empty($data['custom_height'])) {
                    $errors['custom_height'] = get_string('custom_height_required', 'ejsapp');
                }
            }
        }

        if ($data['is_rem_lab'] == 1 and $data['sarlab'] == 0) {
            if (empty($data['ip_lab'])) {
                $errors['ip_lab'] = get_string('ip_lab_required', 'ejsapp');
            }
            if (empty($data['port'])) {
                $errors['port'] = get_string('port_required', 'ejsapp');
            }
        }

        if ($data['is_rem_lab'] == 1 and $data['sarlab'] == 1) {
            if (empty($data['practiceintro'])) {
                $errors['practiceintro'] = get_string('practiceintro_required', 'ejsapp');
            }
        }

        if ($data['personalvars'] == 1) {
            if (empty($data['var_name'])) {
                $errors['var_name[0]'] = get_string('vars_required', 'ejsapp');
            }
            $i = 0;
            foreach ($data['var_type'] as $this_var_type) {
                $min_values = $data['min_value'];
                $max_values = $data['max_value'];
                if ($this_var_type == 1 && (!(floor($min_values[$i]) == $min_values[$i]) || !(floor($max_values[$i]) == $max_values[$i]))) {
                    $errors['var_type['.$i.']'] = get_string('vars_incorrect_type', 'ejsapp');
                } elseif ($this_var_type == 2 && (!is_float($min_values[$i]) || !is_float($max_values[$i]))) {
                    $errors['var_type['.$i.']'] = get_string('vars_incorrect_type', 'ejsapp');
                }
                $i++;
            }
        }

        // Check whether the manifest/metadata file has the necessary information
        /*if (!empty($data['manifest'])) {
            if ($data['manifest'] != 'EJsS') { //java
                $pattern = '/Applet-Height\s*:\s*(\w+)/';
                preg_match($pattern, $data['manifest'], $matches, PREG_OFFSET_CAPTURE);
                if (count($matches) == 0) {
                    $errors['appletfile'] = get_string('EJS_version', 'ejsapp');
                }
            } else { //javascript
                $pattern = '/main-simulation\s*:\s*(\w+)/';
                preg_match($pattern, $data['metadata'], $matches, PREG_OFFSET_CAPTURE);
                if (count($matches) == 0) {
                    $errors['appletfile'] = get_string('EJS_version', 'ejsapp');
                }
            }
        }*/


        return $errors;
    } // validation


} // class mod_ejsapp_mod_form