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
 * Interactions with EjsS activities.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2016 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'mod_ejsapp/screenfull'], function($, sf) {
    var t = {
        fullScreen: function() {
            var screenfull = sf.init();
            var chart = $('#slideshow-wrapper')[0];
            $('#full_screen_chart').on('click', function () {
                if (screenfull.enabled) {
                    screenfull.request(chart);
                }
            });
            var blockly = $('#whereScriptsAre')[0];
            $('#full_screen_blockly').on('click', function () {
                if (screenfull.enabled) {
                    screenfull.request(blockly);
                }
            });
        },

        addLog: function(url_add_log, url_max_time, is_rem_lab, htmlid, frequency, max_time) {
            var handleSuccessAddLog = function(o) {
                /*success handler code*/
            };
            var handleFailureAddLog = function(o) {
                /*failure handler code*/
            };
            var callbackAddLog = {
                success: handleSuccessAddLog,
                failure: handleFailureAddLog
            };
            var handleSuccessKickOut = function(o) {
                var div = $('"#' + htmlid + '"');
                div.innerHTML = o.responseText;
            };
            var handleFailureKickOut = function(o) {
                /*failure handler code*/
            };
            var callbackKickOut = {
                success: handleSuccessKickOut,
                failure: handleFailureKickOut
            };
            if (typeof max_time !== 'undefined') {
                max_times = Math.round(max_time/frequency); // A user can occupy a remote lab just for max_times seconds.
            }
            var counter = 0;
            var checkActivity = function() {
                //Y.use('yui2-connection', 'yui2-dom', function(Y) {
                    // Call php code to insert log in Moodle table.
                    $.get(url_add_log, callbackAddLog);
                    counter++;
                    if (typeof max_time !== 'undefined') {
                        if (counter >= max_times) {
                            // Call php code to refresh view.php and kick the user from the remote lab.
                            $.get(url_max_time, callbackKickOut);
                            clearInterval(checkUserActivity);
                        }
                    }
                //});
            };
            // Call a first time.
            checkActivity();
            // Call periodically.
            var checkUserActivity = setInterval(checkActivity, 1000 * frequency);
        },

        countdown: function(url, htmlid, initial_remaining_time, frequency, seconds_label, refresh_label) {
            var handleSuccess = function(o) {
                var response = o.responseText;
                remaining_time = response.substring(0, response.indexOf(' '));
                remaining_time_client = remaining_time;
                if (remaining_time > 0) {
                    var div = $('"#' + htmlid + '"');
                    div.innerHTML = response;
                }
            };
            var handleFailure = function(o) {
                /*failure handler code*/
            };
            var callback = {
                success: handleSuccess,
                failure: handleFailure
            };
            var counter = 0;
            var remaining_time =  initial_remaining_time;
            var remaining_time_client = remaining_time;
            var updateRemainingTimeServer = function() {
                //Y.use('yui2-connection', 'yui2-dom', function(Y) {
                    // Call php code to update the remaining time till the remote lab is free again.
                    var final_url = url + '&remaining_time=' + remaining_time;
                    $.get(final_url, callback);
                    if (remaining_time > 0) { //still counting
                        counter++;
                    } else { // End, user can try refreshing the window.
                        clearInterval(intervalServer);
                    }
                //});
            };
            var intervalServer = setInterval(updateRemainingTimeServer, 1000 * frequency);
            var updateRemainingTimeClient = function() {
                //Y.use('yui2-connection', 'yui2-dom', function(Y) {
                    var div = $('"#' + htmlid + '"');
                    if (remaining_time_client > 0) { // Still counting.
                        remaining_time_client--;
                        div.innerHTML = remaining_time_client + seconds_label;
                    } else { // End, user can try refreshing the window.
                        div.innerHTML = refresh_label;
                        clearInterval(intervalClient);
                    }
                //});
            };
            updateRemainingTimeClient();
            var intervalClient = setInterval(updateRemainingTimeClient, 1000);
        }
    };
    return t;
});