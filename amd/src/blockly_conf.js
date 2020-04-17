//
// EJSApp is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either moodle_version 3 of the License, or
// (at your option) any later moodle_version.
//
// EJSApp is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License is available on <http://www.gnu.org/licenses/>
//
// EJSApp has been developed by:
// - Luis de la Torre: ldelatorre@dia.uned.es
// - Ruben Heradio: rheradio@issi.uned.es
//
// at the Computer Science and Automatic Control, Spanish Open University
// (UNED), Madrid, Spain

/**
 * Blockly configuration
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2016 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    var remoteLab;
    var chartsBlockly;
    var eventsBlockly;
    var controllerBlockly;
    var time_step;
    var controllerFunctionLanguage;
    var functionToReplace;
    var remoteController;
    var codeBeforeController;
    var codeAfterController;
    var t = {
        configureBlockly: function(remote, charts, events, controller, language, functionName, server) {
            remoteLab = remote === "1";
            chartsBlockly = charts === "1";
            eventsBlockly = events === "1";
            controllerBlockly = controller === "1";
            time_step = 1;
            controllerFunctionLanguage = language;
            functionToReplace = functionName;
            remoteController = server;
            codeBeforeController = "";
            codeAfterController = "";
        },
        returnRemoteLab:function(){return remoteLab},
        returnChartsBlockly:function(){return chartsBlockly},
        returnEventsBlockly:function(){return eventsBlockly},
        returnControllerBlockly:function(){return controllerBlockly},
        returnTime_step:function(){return time_step},
        returnControllerFunctionLanguage:function(){return controllerFunctionLanguage},
        returnFunctionToReplace:function(){return functionToReplace},
        returnRemoteController:function(){return remoteController},
        returnCodeBeforeController:function(){return codeBeforeController},
        returnCodeAfterController:function(){return codeAfterController}
    };
    return t;
});