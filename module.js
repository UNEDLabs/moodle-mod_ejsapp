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

M.mod_ejsapp.init_add_log = function(Y, url, moodle_version, frequency){
    var handleSuccess = function(o) {
        /*success handler code*/
    };
    var handleFailure = function(o) {
        /*failure handler code*/
    };
    var callback = {
        success:handleSuccess,
        failure:handleFailure
    };
    var max_times = Math.round(3600/frequency); //A user can occupy a lab just for one hour
    var counter = 0;
    var checkActivity = function() {
        if (counter < max_times) {
            //Call php code to insert log in Moodle table
            Y.use('yui2-connection', function(Y) {
                    if (moodle_version >= 2012120300) { //Moodle 2.4 or higher
                        YAHOO = Y.YUI2;
                    }
                    YAHOO.util.Connect.asyncRequest('GET', url, callback);
                    counter++;
            });
        } else clearInterval(checkActivity);
    }
    //Call a first time:
    checkActivity();
    //Call periodically:
    setInterval(checkActivity,1000*frequency);
};

/*M.mod_ejsapp.countdown = function(Y, url, moodle_version){
    var handleSuccess = function(o) {
        /*success handler code*/
    /*};
    var handleFailure = function(o) {
        /*failure handler code*/
    /*};
    var callback = {
        success:handleSuccess,
        failure:handleFailure
    };
    var max_times = 3600;
    var counter = 1;
    var checkActivity = function() {
        if (counter < max_times) {
            //Call php code to insert log in Moodle table
            Y.use('yui2-connection', function(Y) {
                if (moodle_version >= 2012120300) { //Moodle 2.4 or higher
                    YAHOO = Y.YUI2;
                }
                YAHOO.util.Connect.asyncRequest('GET', url, callback);
                counter++;
            });
        } else clearInterval(checkActivity);
    }
    setInterval(checkActivity,1000);
};*/