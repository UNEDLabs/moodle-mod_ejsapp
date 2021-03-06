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
 * EJSApp settings form.
 *
 * @package    mod_ejsapp
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
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_ejsapp_mod_form extends moodleform_mod {
    
    /**
     * Called from Moodle to define this form
     *
     * @return void
     * @throws
     */
    public function definition() {
        global $CFG, $DB, $USER;
        $mform = & $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));
        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('ejsappname', 'ejsapp'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_NOTAGS);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength',
            255, 'client');
        $mform->addHelpButton('name', 'ejsappname', 'ejsapp');
        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->version < 2015051100) {
            $this->add_intro_editor();
        } else {
            $this->standard_intro_elements();
        }

        // Adding other ejsapp settings by adding hidden fieldsets.
        $mform->addElement('header', 'conf_parameters', get_string('jar_file', 'ejsapp'));

        $mform->addElement('hidden', 'manifest', null);
        $mform->setType('manifest', PARAM_TEXT);
        $mform->setDefault('manifest', '');

        $mform->addElement('hidden', 'main_file', null);
        $mform->setType('main_file', PARAM_TEXT);
        $mform->setDefault('main_file', '');

        $mform->addElement('hidden', 'remlab_manager', null);
        $mform->setType('remlab_manager', PARAM_INT);

        // File picker.
        $maxbytes = get_max_upload_file_size($CFG->maxbytes);
        $mform->addElement('filemanager', 'appletfile', get_string('file'), null, array('subdirs' => 0,
            'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => array('application/java-archive', 'application/zip')));
        $mform->addRule('appletfile', get_string('appletfile_required', 'ejsapp'), 'required');
        $mform->addHelpButton('appletfile', 'appletfile', 'ejsapp');

        // More optional text to be shown after the lab.
        $mform->addElement('header', 'more_text', get_string('more_text', 'ejsapp'));
        $mform->addElement('editor', 'ejsappwording', get_string('appwording', 'ejsapp'), null,
            array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1,
                'context' => $this->context, 'noclean' => 1, 'trusttext' => 0));
        $mform->setType('appwording', PARAM_RAW);

        // Optional Javascript CSS styles.
        $mform->addElement('header', 'css_style', get_string('css_style', 'ejsapp'));
        $mform->addElement('textarea', 'css', get_string('css_rules', 'ejsapp'),
            'wrap="virtual" rows="8" cols="50"');
        $mform->addHelpButton('css', 'css_rules', 'ejsapp');

        // Adding an optional state file to be read when the lab loads.
        $mform->addElement('header', 'state_file', get_string('state_file', 'ejsapp'));
        $mform->addElement('filemanager', 'statefile', get_string('file'), null, array('subdirs' => 0,
            'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => '.json'));
        $mform->addHelpButton('statefile', 'statefile', 'ejsapp');

        // Adding an optional text file with a recording to automatically run it when the lab loads.
        $mform->addElement('header', 'recording_file', get_string('recording_file', 'ejsapp'));
        $mform->addElement('filemanager', 'recordingfile', get_string('file'), null, array('subdirs' => 0,
            'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => '.rec'));
        $mform->addHelpButton('recordingfile', 'recordingfile', 'ejsapp');

        // Personalize variables from the EJS application.
        $mform->addElement('header', 'personalize_vars', get_string('personalize_vars', 'ejsapp'));
        $mform->addElement('selectyesno', 'personalvars', get_string('use_personalized_vars', 'ejsapp'));
        $mform->addHelpButton('personalvars', 'use_personalized_vars', 'ejsapp');

        $varsarray = array();
        $varsarray[] = $mform->createElement('text', 'var_name', get_string('var_name', 'ejsapp'));
        $varsarray[] = $mform->createElement('select', 'var_type', get_string('var_type', 'ejsapp'),
            array('Boolean', 'Integer', 'Double'));
        $varsarray[] = $mform->createElement('text', 'min_value', get_string('min_value', 'ejsapp'),
            array('size' => '8'));
        $varsarray[] = $mform->createElement('text', 'max_value', get_string('max_value', 'ejsapp'),
            array('size' => '8'));

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
            if ($personalvars = $DB->get_records('ejsapp_personal_vars', array('ejsappid' => $this->current->instance))) {
                $no = count($personalvars);
            }
        }

        $this->repeat_elements($varsarray, $no, $repeateloptions, 'option_repeats',
            'option_add_vars', 2, null, true);

        $experiencelist = '';
        if ($DB->record_exists('block', array('name' => 'remlab_manager'))) {
            // Elements to configure the remote lab, if that's the case (only if remlab_manager block is installed).
            $mform->addElement('header', 'rem_lab', get_string('rem_lab_conf', 'ejsapp'));

            $mform->addElement('selectyesno', 'is_rem_lab', get_string('is_rem_lab', 'ejsapp'));
            $mform->addHelpButton('is_rem_lab', 'is_rem_lab', 'ejsapp');
            $remlabmanagerinstalled = $DB->get_records('block', array('name' => 'remlab_manager'));
            $remlabmanagerinstalled = !empty($remlabmanagerinstalled);
            $mform->setDefault('remlab_manager', $remlabmanagerinstalled ? 1 : 0);
            $mform->setDefault('is_rem_lab', 0);
            $mform->disabledIf('is_rem_lab', 'remlab_manager', 'eq', 0);

            if ($remlabmanagerinstalled) {
                $showableexperiences = get_showable_experiences($USER->username, 1);
            } else {
                $showableexperiences = array();
            }
            $mform->addElement('select', 'practiceintro', get_string('practiceintro', 'ejsapp'),
                $showableexperiences);
            $mform->addHelpButton('practiceintro', 'practiceintro', 'ejsapp');
            $mform->disabledIf('practiceintro', 'is_rem_lab', 'eq', 0);
            if ($this->current->instance && $remlabmanagerinstalled) {
                $practiceintro = $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro',
                    array('ejsappid' => $this->current->instance));
                if ($practiceintro) {
                    $i = 0;
                    $selectedpracticeindex = $i;
                    foreach ($showableexperiences as $myFrontierexp) {
                        if ($practiceintro == $myFrontierexp) {
                            $selectedpracticeindex = $i;
                            break;
                        }
                        $i++;
                    }
                    $mform->setDefault('practiceintro', $selectedpracticeindex);
                } else {
                    $mform->setDefault('practiceintro', '');
                }
            }
            foreach ($showableexperiences as $experience) {
                $experiencelist .= $experience . ';';
            }

        } else {
            $mform->addElement('hidden', 'is_rem_lab');
            $mform->setType('is_rem_lab', PARAM_INT);
            $mform->setDefault('is_rem_lab', 0);
        }
        $mform->addElement('hidden', 'list_practices', null);
        $mform->setType('list_practices', PARAM_TEXT);
        $mform->setDefault('list_practices', $experiencelist);



        // Use and configuration of Blockly and ACE code editor.
        $mform->addElement('header', 'programming_config', get_string('programming_config', 'ejsapp'));

        $mform->addElement('selectyesno', 'use_blockly', get_string('use_blockly', 'ejsapp'));
        $mform->addHelpButton('use_blockly', 'use_blockly', 'ejsapp');
        $mform->setDefault('use_blockly', 0);

        $mform->addElement('selectyesno', 'charts_blockly', get_string('charts_blockly', 'ejsapp'));
        $mform->disabledIf('charts_blockly', 'use_blockly', 'eq', 0);
        $mform->setDefault('charts_blockly', 0);

        $mform->addElement('selectyesno', 'events_blockly', get_string('events_blockly', 'ejsapp'));
        $mform->disabledIf('events_blockly', 'use_blockly', 'eq', 0);
        $mform->setDefault('events_blockly', 0);

        $mform->addElement('selectyesno', 'functions', get_string('functions', 'ejsapp'));
        $mform->disabledIf('functions', 'use_blockly', 'eq', 0);
        $mform->setDefault('functions', 0);

        $available_languages = array(
            "blockly" => "Blockly",
            "javascript" => "JavaScript",
            "c_cpp" => "C and C++",
            "csharp" => "C#",
            "cobol" => "Cobol",
            "java" => "Java",
            "matlab" => "Matlab",
            "pascal" => "Pascal",
            "php" => "Php",
            "python" => "Python",
            "r" => "R",
            "ruby" => "Ruby",
            "rust" => "Rust",
            "vhdl" => "VHDL",
        );
        $mform->addElement('select', 'func_language', get_string('func_language', 'ejsapp'), $available_languages);
        $mform->addHelpButton('func_language', 'func_language', 'ejsapp');
        $mform->disabledIf('func_language', 'functions', 'eq', 0);
        $mform->setDefault('func_language', 'javascript');

        $varsarray = array();
        $varsarray[] = $mform->createElement('text', 'func_name', get_string('func_name', 'ejsapp'));
        if ($DB->record_exists('block', array('name' => 'remlab_manager'))) {
            $varsarray[] = $mform->createElement('selectyesno', 'remote_function', get_string('remote_function', 'ejsapp'));
            $mform->disabledIf('remote_function', 'functions', 'eq', 0);
        } else {
            $mform->addElement('hidden', 'remote_function', null);
            $mform->setType('remote_function', PARAM_INT);
            $mform->setDefault('remote_function', 0);
        }

        $repeatfuncoptions = array();
        $repeatfuncoptions['func_name']['disabledif'] = array('functions', 'eq', 0);
        $repeatfuncoptions['func_name']['type'] = PARAM_TEXT;
        $repeatfuncoptions['func_name']['helpbutton'] = array('func_name', 'ejsapp');
        if ($DB->record_exists('block', array('name' => 'remlab_manager'))) {
            $repeatfuncoptions['remote_function']['disabledif'] = array('functions', 'eq', 0);
            $repeatfuncoptions['remote_function']['disabledif'] = array('is_rem_lab', 'eq', 0);
        }

        $no = 1;
        if ($this->current->instance) {
            if ($blocklyconf = $DB->get_field('ejsapp', 'blockly_conf', array('id' => $this->current->instance))) {
                $blocklyconf = json_decode($blocklyconf);
                $functions = $blocklyconf[5];
                $no = count($functions);
            }
        }

        $this->repeat_elements($varsarray, $no, $repeatfuncoptions, 'funcoption_repeats',
            'option_add_funcs', 1, null, true);

        // Adding an optional text file with a blockly program.
        $mform->addElement('filemanager', 'blocklyfile', get_string('blocklyfile', 'ejsapp'),
            null, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => '.blk'));
        $mform->addHelpButton('blocklyfile', 'blocklyfile', 'ejsapp');
        $mform->disabledIf('blocklyfile', 'use_blockly', 'eq', 0);

        /* TODO: Adding an optional text file with a textual program (if any programming language rather than blockly is selected).
        $mform->addElement('filemanager', 'blocklyfile', get_string('blocklyfile', 'ejsapp'),
            null, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => '.blk'));
        $mform->addHelpButton('blocklyfile', 'blocklyfile', 'ejsapp');
        $mform->disabledIf('blocklyfile', 'use_blockly', 'eq', 0); */



        // Select the users interaction recording options.
        $mform->addElement('header', 'record_interactions_title', get_string('record_interactions', 'ejsapp'));
        $mform->addElement('selectyesno', 'record', get_string('record_interactions', 'ejsapp'));
        $mform->addHelpButton('record', 'record_interactions', 'ejsapp');
        $mform->setDefault('record', 0);
        $mform->addElement('selectyesno', 'mouseevents', get_string('record_mouse_events', 'ejsapp'));
        $mform->addHelpButton('mouseevents', 'record_mouse_events', 'ejsapp');
        $mform->disabledIf('mouseevents', 'record_interactions', 'eq', 0);
        $mform->setDefault('mouseevents', 0);

        // Adding standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Adding standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Adding standard buttons, common to all modules.
        $this->add_action_buttons();
    } // End of function definition().


    /**
     * Any data processing needed before the form is displayed (needed to set up draft areas for editor and filemanager
     * elements).
     *
     * @param array $defaultvalues
     * @throws
     */
    public function data_preprocessing(&$defaultvalues) {
        global $CFG, $DB;

        $maxbytes = get_max_upload_file_size($CFG->maxbytes);

        // Fill the form elements with previous submitted files/data.
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('appletfile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_ejsapp', 'compressed',
                $this->current->instance, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1,
                    'accepted_types' => array('application/java-archive', 'application/zip')));
            $defaultvalues['appletfile'] = $draftitemid;

            $draftitemidwording = file_get_submitted_draft_itemid('appwording');
            $defaultvalues['ejsappwording']['format'] = $defaultvalues['appwordingformat'];
            $defaultvalues['ejsappwording']['text'] = file_prepare_draft_area($draftitemidwording, $this->context->id,
                'mod_ejsapp', 'appwording', 0, array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes,
                    'changeformat' => 1, 'context' => $this->context, 'noclean' => 1, 'trusttext' => 0),
                $defaultvalues['appwording']);
            $defaultvalues['ejsappwording']['itemid'] = $draftitemidwording;
            
            $draftitemidstate = file_get_submitted_draft_itemid('statefile');
            file_prepare_draft_area($draftitemidstate, $this->context->id, 'mod_ejsapp', 'xmlfiles',
                $this->current->instance, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));
            $defaultvalues['statefile'] = $draftitemidstate;

            $draftitemidrecording = file_get_submitted_draft_itemid('recordingfile');
            file_prepare_draft_area($draftitemidrecording, $this->context->id, 'mod_ejsapp', 'recfiles',
                $this->current->instance, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));
            $defaultvalues['recordingfile'] = $draftitemidrecording;

            $draftitemidblockly = file_get_submitted_draft_itemid('blocklyfile');
            file_prepare_draft_area($draftitemidblockly, $this->context->id, 'mod_ejsapp', 'blkfiles',
                $this->current->instance, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));
            $defaultvalues['blocklyfile'] = $draftitemidblockly;

            $personalvars = $DB->get_records('ejsapp_personal_vars', array('ejsappid' => $this->current->instance));
            $key = 0;
            foreach ($personalvars as $personalvar) {
                $defaultvalues['var_name['.$key.']'] = $personalvar->name;
                $vartype = '0';
                if ($personalvar->type == 'Integer') {
                    $vartype = '1';
                } else if ($personalvar->type == 'Double') {
                    $vartype = '2';
                }
                $defaultvalues['var_type['.$key.']'] = $vartype;
                if ($vartype != 0) {
                    $defaultvalues['min_value['.$key.']'] = $personalvar->minval;
                    $defaultvalues['max_value['.$key.']'] = $personalvar->maxval;
                }
                $key ++;
            }

            $jsonblocklyconf = $DB->get_field('ejsapp', 'blockly_conf',
                array('id' => $this->current->instance));
            $blocklyconf = json_decode($jsonblocklyconf);
            if (is_array($blocklyconf)) {
                $defaultvalues['use_blockly'] = $blocklyconf[0];
                $defaultvalues['charts_blockly'] = $blocklyconf[1];
                $defaultvalues['events_blockly'] = $blocklyconf[2];
                $defaultvalues['functions'] = $blocklyconf[3];
                $defaultvalues['func_language'] = $blocklyconf[4];
                $functions = $blocklyconf[5];
                $key = 0;
                foreach ($functions as $function) {
                    $defaultvalues['func_name['.$key.']'] = $function[0];
                    $defaultvalues['remote_function['.$key.']'] = $function[1];
                    $key ++;
                }
            }
        }
    } // End of funtion data_preprocessing().


    /**
     * Performs minimal validation on the settings form
     *
     * @param array $data
     * @param array $files
     * @return array $errors
     * @throws
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['personalvars'] == 1) {
            if (empty($data['var_name'])) {
                $errors['var_name[0]'] = get_string('vars_required', 'ejsapp');
            }
            $i = 0;
            foreach ($data['var_type'] as $vartype) {
                $minvalues = $data['min_value'];
                $maxvalues = $data['max_value'];
                if ($vartype == 1 && (!(floor($minvalues[$i]) == $minvalues[$i]) ||
                        !(floor($maxvalues[$i]) == $maxvalues[$i]))) {
                    $errors['var_type['.$i.']'] = get_string('vars_incorrect_type', 'ejsapp');
                } else if ($vartype == 2 && (!is_float($minvalues[$i]) || !is_float($maxvalues[$i]))) {
                    $errors['var_type['.$i.']'] = get_string('vars_incorrect_type', 'ejsapp');
                }
                $i++;
            }
        }

        if ($data['is_rem_lab'] == 1) {
            if ($data['practiceintro'] == '') {
                $errors['practiceintro'] = get_string('practiceintro_required', 'ejsapp');
            }
        }

        return $errors;
    } // End of function validation().

    /*public function add_completion_rules() {

    }*/

    /*public function completion_rule_enabled($data) {
        $status = !empty($data['completionstatusrequired']);
        $score = empty($data['completionscoredisabled']) && strlen($data['completionscorerequired']);

        return $status || $score;
    }*/

}