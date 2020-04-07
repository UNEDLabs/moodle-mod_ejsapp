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
 * Prints a particular instance of EJSApp
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('renderable.php');


/**
 * Prints the required html in the ejsapp activity view
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_ejsapp_renderer extends plugin_renderer_base {

    /**
     * Prints ejsapp EJsS div for the lab
     * This function returns the HTML and JavaScript code that embeds an EjsS application into Moodle
     * It is used for four different cases:
     *      1) when only the EJSApp activity is being used
     *      2) when the EJSApp File Browser is used to load a state, recording or blockly file
     *      3) when the EJSApp Collab Session is used
     *      4) when third party plugins want to display EjsS labs in their own activities by means of the EJSApp external interface
     *
     * @param stdClass $ejsapp record from table ejsapp
     * @param stdClass|null $remlabinfo
     *                                  $remlabinfo->instance: false|int myFrontier id,
     *                                  $remlabinfo->practice: int practice id,
     *                                  $remlabinfo->collab: int collab whether myFrontier offers collab access to this remote
     *                                      lab (1) or not (0),
     *                                  $remlabinfo->labmanager: int laboratory manager (1) or student (0)
     *                                  $remlabinfo->max_use_time: int maximum time the remote lab can be connected (in seconds)
     *                                  Null if virtual lab
     * @param array|null $userdatafiles
     *                                  $userdatafiles[0]: user_state_file, if generate_embedding_code is called from
     *                                      block ejsapp_file_browser, this is the name of the .json file that stores
     *                                      the state of an EjsS lab, elsewhere it is null
     *                                  $userdatafiles[1]: user_rec_file, if generate_embedding_code is called from
     *                                      block ejsapp_file_browser, this is the name of the .rec file that stores the script
     *                                      with the recording of the interaction with an EJS applet, elsewhere it is null
     *                                  $userdatafiles[2]: user_blk_file, if generate_embedding_code is called from block
     *                                      ejsapp_file_browser, this is the name of the .blk file that stores a blockly
     *                                      program, elsewhere it is null
     * @param stdClass|null $collabinfo
     *                                  $collabinfo->session: int collaborative session id,
     *                                  $collabinfo->ip: string collaborative session ip,
     *                                  $collabinfo->localport: int collaborative session local port,
     *                                  $collabinfo->enlargeport: int|null ENLARGE port,
     *                                  $collabinfo->director: int|null id of the collaborative session master user, `
     *                                  Null if generate_embedding_code is not called from block ejsapp_collab_session
     * @param stdClass|null $personalvarsinfo
     *                                  $personalvarsinfo->name: string[] name(s) of the EJS variable(s),
     *                                  $personalvarsinfo->value: double[] value(s) of the EJS variable(s),
     *                                  $personalvarsinfo->type: string[] type(s) of the EJS variable(s),
     *                                  Null if no personal variables were defined for this EJSApp
     * @return string Html code that prints the general html view layout
     */
    public function ejsapp_lab($ejsapp, $remlabinfo, $userdatafiles, $collabinfo, $personalvarsinfo) {
        return $this->render(new ejsapp_lab($ejsapp, $remlabinfo, $userdatafiles, $collabinfo, $personalvarsinfo));
    }

    /**
     * Prints ejsapp chart div
     * @return string Html code that prints the chart div
     */
    public function ejsapp_charts() {
        return $this->render(new ejsapp_charts());
    }

    /**
     * Prints ejsapp controlbar div
     * @param array $blocklyconf
     * @return string Html code that prints the control bar div
     */
    public function ejsapp_controlbar($blocklyconf) {
        return $this->render(new ejsapp_controlbar($blocklyconf));
    }

    /**
     * Prints ejsapp blockly div
     * @return string Html code that prints the blockly div
     */
    public function ejsapp_blockly() {
        return $this->render(new ejsapp_blockly());
    }

    /**
     * Prints ejsapp log div
     * @return string Html code that prints the log div
     */
    public function ejsapp_log() {
        return $this->render(new ejsapp_log());
    }

    /**
     * Returns the code that embeds an EjsS application into Moodle
     *
     * @return string Html code that embeds an EjsS application in Moodle
     * @throws
     *
     */
    public function render_ejsapp_lab() {
        $code =
            html_writer::start_div("", array("id" => "prevDrag")) .
            html_writer::end_div() .
            html_writer::start_div("", array("id" => "EJsS")) .
                html_writer::start_div("topnav-right") .
                    /*html_writer::tag("i", "", array("id" => "#EJsSheader", "class" => "fa fa-arrows-alt fa-2x",
                        "aria-hidden" => "true", "onmousedown"=>"copyToDragDiv('#EJsS');")) .*/
                html_writer::end_div() .
                html_writer::div("", "", array("id" => "_topFrame")) .
            html_writer::end_div();

        return $code;
    }

    /**
     * Prints ejsapp charts div
     * @return string Html code that prints the html for the charts
     */
    public function render_ejsapp_charts() {
        global $CFG;

        $chartsdiv =
            html_writer::start_div("", array("id" => "ChartBox", "style" => "display:none;")) .
               html_writer::start_div("topnav-right align-self-end") .
                    html_writer::tag("i", "", array("id" => "save_chart_img", "onclick" =>
                        "saveImg('" . $CFG->wwwroot . "/mod/ejsapp/upload_file.php" . "')",
                        "class" => "fa fa-area-chart fa-2x")) .
                    html_writer::tag("i", "", array("id" => "save_chart_data", "onclick" =>
                        "saveCSV(0)", "class" => "fa fa-file-text-o fa-2x")) .
                    html_writer::tag("i", "", array("id" => "full_screen_chart", "class" =>
                        "fa fa-expand fa-2x", "aria-hidden" => "true")) .
                    /*html_writer::tag("i", "", array("id" => "#ChartBoxheader", "class" => "fa fa-arrows-alt fa-2x",
                        "aria-hidden" => "true", "style"=>"cursor:move; margin-left:1rem", "onmousedown"=>"copyToDragDiv('#ChartBox');")) .*/
                html_writer::end_div() .
                html_writer::start_div("d-flex flex-column", array("id" => "slideshow-wrapper")) .
                    html_writer::start_div("d-flex justify-content-center", array("id" => "control_chart")) .
                        html_writer::tag("i", "", array("id" => "prev_chart", "onclick" => "prevChart()",
                            "class" => "fa fa-angle-double-left fa-2x", "style" => "display:none;")) .
                        html_writer::tag("i", "", array("id" => "clean_chart", "onclick" => "cleanCharts()",
                            "class" => "fa fa-times fa-2x", "style" => "display:none;")) .
                        html_writer::tag("i", "", array("id" => "next_chart", "onclick" => "nextChart()",
                            "class" => "fa fa-angle-double-right fa-2x", "style" => "display:none;")) .
                    html_writer::end_div() .
                html_writer::end_div() .
            html_writer::end_div();

        return $chartsdiv;
    }

    /**
     * Prints ejsapp control bar div
     * @param ejsapp_controlbar $params
     * @return string Html code that prints the html for the control bar
     * @throws coding_exception
     */
    public function render_ejsapp_controlbar($params) {
        $navbar1 =
            html_writer::start_div("navbar", array('id' => 'blockly_navbar')) .
                html_writer::start_div("dropdown", array("id" => "experimentsDropdown")) .
                    html_writer::tag("button ", get_string('experiment_blockly', 'ejsapp'), array("class" =>
                        " btn btn-secondary dropdown-toggle mod-ejsapp-green", "type" => "button ", "id" => "dropdownMenuButton",
                        "data-toggle" => "dropdown", "aria-haspopup" => "true", "aria-expanded" => "false")) .
                    html_writer::start_div("dropdown-menu", array("id" => "experimentsScripts",
                        "aria-labelledby" => "dropdownMenuButton")) .
                        html_writer::start_tag("a", array("class" => "dropdown-item",
                            "onclick" => "newScript(1)")) .
                            html_writer::tag("i", "", array("class" => "fa fa-plus",
                                "aria-hidden" => "true")) . get_string('experimentDropdown_blockly', 'ejsapp') .
                        html_writer::end_tag("a") .
                    html_writer::end_div() .
                html_writer::end_div() .
                html_writer::start_div("dropdown", array("id" => "chartsDropdown")) .
                    html_writer::tag("button", get_string('data_blockly', 'ejsapp'), array("class" => "btn btn-secondary dropdown-toggle mod-ejsapp-blue",
                        "type" => "button", "id" => "dropdownMenuButton", "data-toggle" => "dropdown",
                        "aria-haspopup" => "true", "aria-expanded" => "false")) .
                    html_writer::start_div("dropdown-menu", array("id" => "chartsScripts", "aria-labelledby" =>
                        "dropdownMenuButton")) .
                        html_writer::tag("a", html_writer::tag("i", "", array("class" =>
                                "fa fa-plus", "aria-hidden" => "true")) . get_string('chartDropdown_blockly', 'ejsapp'), array("class" =>
                            "dropdown-item", "onclick" => "newScript(2)")) .
                    html_writer::end_div() .
                html_writer::end_div() .
                html_writer::start_div("dropdown", array("id" => "eventsDropdown")) .
                    html_writer::tag("button", get_string('event_blockly', 'ejsapp'), array("class" => "btn btn-secondary dropdown-toggle mod-ejsapp-red",
                        "type" => "button", "id" => "dropdownMenuButton", "data-toggle" => "dropdown",
                        "aria-haspopup" => "true", "aria-expanded" => "false")) .
                    html_writer::start_div("dropdown-menu", array("id" => "eventsScripts", "aria-labelledby" =>
                        "dropdownMenuButton")) .
                        html_writer::tag("a", html_writer::tag("i", "", array("class" =>
                                "fa fa-plus", "aria-hidden" => "true")) . get_string('eventDropdown_blockly', 'ejsapp'), array("class" =>
                            "dropdown-item", "onclick" => "newScript(3)")) .
                    html_writer::end_div() .
                html_writer::end_div() .
                html_writer::start_div("dropdown", array("id" => "allFunctionsDropdown")) .
                    html_writer::tag("button", get_string('functions', 'ejsapp'), array("class" => "btn btn-secondary dropdown-toggle mod-ejsapp-peru",
                        "type" => "button", "id" => "dropdownMenuButton", "data-toggle" => "dropdown",
                        "aria-haspopup" => "true", "aria-expanded" => "false")) .
                    html_writer::start_div("dropdown-menu", array("id" => "functionsScripts", "aria-labelledby" =>
                        "dropdownMenuButton")) ;

        $functions = $params->blocklyconf[5];
        $extranavbar = "";
        $index = 0;
        foreach ($functions as $function) {
            $name = $function[0];
            $extranavbar = $extranavbar .
                html_writer::start_div("dropdown", array("id" => "functionsDropdown")) .
                    html_writer::tag("p", $name, array("class" => "mod-ejsapp-peru")).
                    html_writer::start_div("a", array("id" => "functionsScripts" . $index, "aria-labelledby" =>
                        "dropdownMenuButton")) .
                        html_writer::tag("a", html_writer::tag("i", "", array("class" =>
                                "fa fa-plus")) . get_string('functionDropdown', 'ejsapp'), array("class" =>
                            "dropdown-item", "onclick" => "newScript(4,".$index.")")) .
                    html_writer::end_div() .
                html_writer::end_div() ;
            $index = $index+1;
        }

        $navbar2 =
                    html_writer::end_div() .
                html_writer::end_div() .


                html_writer::start_div("topnav-right", array("id" => "logs")) .
                    html_writer::tag("button", get_string('run_blockly', 'ejsapp'), array("class" => "play-code textExecutionElement",
                        "onclick" => "playCode(" . $params->blocklyconf[1] . "," . $params->blocklyconf[2] . "," .
                            $params->blocklyconf[3] . "," .  ")")) .
                    html_writer::start_tag("button", array("class" => "play-code textExecutionElement", "id" => "show_log", "onclick" => "showLog()")) .
                        html_writer::tag("i", get_string('log_blockly', 'ejsapp'), array("class" => "fa fa-bug", "aria-hidden" => "true")) .
                    html_writer::end_tag("button") .
                html_writer::end_div() .
            html_writer::end_div();

        return $navbar1 . $extranavbar . $navbar2 ;
    }

    /**
     * Prints ejsapp blockly div
     * @return string Html code that prints the html for the blockly div
     */
    public function render_ejsapp_blockly() {
        $blocklydiv =
            html_writer::start_div("blockly", array('id' => 'blocklyDiv')) .
                html_writer::start_div("box", array("id" => "ScriptBox", "style" => "display:none;")) .
                    html_writer::start_div("d-flex justify-content-between") .
                        html_writer::tag("h3", "", array("id" => "titleScriptBox")) .
                        html_writer::start_div("topnav-right") .
                            html_writer::tag("i", "", array("class" => "fa fa-expand fa-2x", "id" =>
                                "full_screen_blockly", "aria-hidden" => "true")) .
                            /*html_writer::tag("i", "", array("id" => "#ScriptBoxheader", "class" =>
                                "fa fa-arrows-alt fa-2x", "aria-hidden" => "true", "style"=>"cursor:move; margin-left:1rem",
                                "onmousedown"=>"copyToDragDiv('#ScriptBox');")) .*/
                        html_writer::end_div() .
                    html_writer::end_div() .
                    html_writer::start_div("", array("id" => "whereScriptsAre")) .
                        html_writer::div("", "", array("id" => "blocklyDivExperiments",
                            "style" => "display:none;")) .
                        html_writer::div("", "", array("id" => "blocklyDivCharts",
                            "style" => "display:none;")) .
                        html_writer::div("", "", array("id" => "blocklyDivEvents",
                            "style" => "display:none;")) .
                        html_writer::start_div("", array("id" => "ControllerDiv")) .
                            html_writer::div("", "", array("id" => "blocklyDivController")) .
                        html_writer::end_div() .
                    html_writer::end_div() .
                html_writer::end_div() .
                html_writer::start_div("modal", array("id" => "myModal")) .
                    html_writer::start_div("modal-content") .
                        html_writer::tag("span", "&times;", array("class" => "close")) .
                        html_writer::start_div("box", array("id" => "ScriptBox")) .
                            html_writer::tag("h3", "Javascript code") .
                            html_writer::div("", "", array("id" => "_javaScriptFrame")) .
                        html_writer::end_div() .
                    html_writer::end_div() .
                html_writer::end_div() .
            html_writer::end_div();

        return $blocklydiv;
    }

    /**
     * Prints ejsapp log div
     * @return string Html code that prints the html for the log div
     */
    public function render_ejsapp_log() {
        $logdiv =
            html_writer::start_div("", array("id" => "footer", "style" => "display:none;")) .
                html_writer::start_div("", array("id" => "executionLogGen", "style" => "display:none")) .
                    html_writer::tag("hr", "") .
                    html_writer::tag("h5", get_string('previousExecutions_blockly', 'ejsapp')) .
                    html_writer::div("", "", array("id" => "executionLog")) .
                html_writer::end_div() .
                html_writer::tag("hr", "") .
                html_writer::tag("textarea", get_string('error_blockly', 'ejsapp'), array("id" => "errorArea", "readonly" => "true")) .
            html_writer::end_div();

        return $logdiv;
    }
}