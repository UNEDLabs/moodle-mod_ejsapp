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
//  - Luis de la Torre: ldelatorre@dia.uned.es
//	- Ruben Heradio: rheradio@issi.uned.es
//
//  at the Computer Science and Automatic Control, Spanish Open University
//  (UNED), Madrid, Spain

/** 
 * Javascript code
 * 
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  
 */

M.mod_ejsapp = {};

M.mod_ejsapp.init_add_log = function(Y, url_add_log, url_max_time, is_rem_lab, htmlid, frequency, max_time){
    var handleSuccessAddLog = function(o) {
        /*success handler code*/
    };
    var handleFailureAddLog = function(o) {
        /*failure handler code*/
    };
    var callbackAddLog = {
        success:handleSuccessAddLog,
        failure:handleFailureAddLog
    };
    var handleSuccessKickOut = function(o) {
        var div = Y.YUI2.util.Dom.get(htmlid);
        div.innerHTML = o.responseText;
    };
    var handleFailureKickOut = function(o) {
        /*failure handler code*/
    };
    var callbackKickOut = {
        success:handleSuccessKickOut,
        failure:handleFailureKickOut
    };
    var max_times = 7200;
    if (is_rem_lab == 1) max_times = Math.round(max_time/frequency); //A user can occupy a remote lab just for max_times seconds
    var counter = 0;
    var checkActivity = function() {
        Y.use('yui2-connection', 'yui2-dom', function(Y) {
            if (counter < max_times) { // on time
                //Call php code to insert log in Moodle table
                Y.YUI2.util.Connect.asyncRequest('GET', url_add_log, callbackAddLog);
                counter++;
            } else { // time is up
                if (is_rem_lab == 1) {
                    //Call php code to refresh view.php and kick the user from the remote lab
                    Y.YUI2.util.Connect.asyncRequest('GET', url_max_time, callbackKickOut);
                }
                clearInterval(checkActivity);
            }
        });
    };
    //Call a first time:
    checkActivity();
    //Call periodically:
    setInterval(checkActivity,1000*frequency);
};

M.mod_ejsapp.init_countdown = function(Y, url, action, htmlid, initial_remaining_time){
    var handleSuccess = function(o) {
        var div = Y.YUI2.util.Dom.get(htmlid);
        div.innerHTML = o.responseText;
    };
    var handleFailure = function(o) {
        /*failure handler code*/
    };
    var callback = {
        success:handleSuccess,
        failure:handleFailure,
        timeout:1000
    };
    var counter = 0;
    var skip = 1;
    if(action != "booked_lab") { //we only check with the server every ten seconds and when the lab is not booked
        skip = 0;
    }
    var remaining_time =  initial_remaining_time;
    var updateRemainingTime = function() {
        Y.use('yui2-connection', 'yui2-dom', function(Y) {
            //Call php code to update the remaining time till the remote lab is free again
            var final_url = url + '&remaining_time=' + remaining_time + '&skip=' + skip;
            Y.YUI2.util.Connect.asyncRequest('GET', final_url, callback);
            if (remaining_time > 0) { //still counting
                remaining_time = remaining_time - 1;
                counter++;
            } else { //end, user can try refreshing the window
                clearInterval(interval);
            }
        });
    };
    updateRemainingTime();
    var interval = setInterval(updateRemainingTime,1000);
};