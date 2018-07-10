// This file is part of the Moodle module "EJSApp"
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
 * Interactions with EjsS applications.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2016 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'mod_ejsapp/screenfull'], function($) {
    var t = {
        recording: function(mouseevents) {
            var doit = setInterval(function() {
                if (typeof _model !== "undefined") {
                        setInterval(function() {
                        // Start recording of users interaction
                        _model.startRegister(mouseevents);
                        // Save record every 30 seconds
                        _model.sendRegister(true);
                    }, 30000);
                    // Also save before the user leaves the EJSApp activity
                    $(window).bind('beforeunload', function(){
                        _model.sendRegister(true);
                    });
                    clearInterval(doit);
                }
            }, 200);
        },

        sarlabCredentials: function(username, password) {
            var doit = setInterval(function() {
                if (typeof _model._sarlab !== "undefined") {
                    _model._sarlab.setSarlabCredentials({"username": username, "password": password});
                    clearInterval(doit);
                }
            }, 200);
        },

        sarlabRun: function(secure, sarlabIP, sarlabPath, sarlabPort, sarlabExperience, closeScreen) {
            var doit = setInterval(function() {
                if (typeof _model._sarlab !== "undefined") {
                    _model._sarlab.setSarlabInfo(secure, sarlabIP, sarlabPath, sarlabPort, sarlabExperience, closeScreen);
                    if (typeof _model._rip !== "undefined") {
                        _model._sarlab.connect(function() {
                            _model._rip.connect();
                        });
                    } else {
                        _model._sarlab.connect();
                    }
                    clearInterval(doit);
                }
            }, 200);
        },

        setCommonParameters: function(contextid, userid, ejsappid, uploadfilesurl, sendfilesurl, elementid) {
            var doit = setInterval(function() {
                if (typeof _model !== "undefined") {
                    _model.setStatusParams(contextid, userid, ejsappid, uploadfilesurl, sendfilesurl, function() {
                        document.getElementById(elementid).click();
                    });
                    clearInterval(doit);
                }
            }, 200);
        },

        addToInitialization: function(sseuri, port) {
            var doit = setInterval(function() {
                if (typeof _model !== "undefined") {
                    if (sseuri !== '') {
                        _model.addToInitialization(function() {
                            _model.setRunAlways(true);
                            _model.playCaptureStream(sseuri);
                        });
                    } else if (port !== '') {
                        _model.addToInitialization(function() {
                            _model.setRunAlways(true);
                            _model.startCaptureStream(port);
                        });
                    }
                    clearInterval(doit);
                }
            }, 200);
        },

        readStateFile: function(statefile) {
            var doit = setInterval(function() {
                if (typeof _model !== "undefined") {
                    _model.readState(statefile);
                    clearInterval(doit);
                }
            }, 200);
        },

        playRecFile: function(recfile, endmessage) {
            var doit = setInterval(function() {
                if (typeof _model !== "undefined") {
                    _model.readText(recfile, '.rec', function(content) {
                        _model.playCapture(JSON.parse(content), function() {
                            alert(endmessage);
                        });
                    });
                    clearInterval(doit);
                }
            }, 200);
        },

        readBlocklyFile: function(blkfile) {
            var doit = setInterval(function() {
                if (typeof _model !== "undefined") {
                    _model.readText(blkfile, '.blk', function(xmlText) {
                        if (xmlText) {
                            workspace.clear();
                            xmlDom = Blockly.Xml.textToDom(xmlText);
                            Blockly.Xml.domToWorkspace(xmlDom, workspace);
                        }
                    });
                    clearInterval(doit);
                }
            }, 200);
        },

        fullScreen: function() {
            const chart = $('#slideshow-wrapper')[0];
            $('#full_screen_chart').click(function() {
                if (screenfull.enabled) {
                    screenfull.request(chart);
                }
            });
            const blockly = $('#injectionDiv')[0];
            $('#full_screen_blockly').click(function() {
                if (screenfull.enabled) {
                    screenfull.request(blockly);
                }
            });
        }
    };
    return t;
});