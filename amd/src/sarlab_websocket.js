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
 * Websocket service for connecting to Sarlab experiences.
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2016 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    var t = {
        SarlabWebSocket : function(host, IP, port, idEx, expTime, puser, password) {
            ws = new WebSocket("ws://127.0.0.1:8887");

            ws.onopen = function()
            {
                // Web Socket is connected, send data using send()
                ws.send("Message to send \r\n");
                var obj = '{'
                    +'"ip_server" : ' + IP + ','
                    +'"port_server"  : ' + port + ','
                    +'"id_exp" : ' + idExp + ','
                    +'"expiration_time" : ' + expTime + ','
                    +'"user" : ' + user + ','
                    +'"password"  : ' + password
                    +'}';
                ws.send(obj);
                console.log("Connected to Sarlab experience: "+idExp);
            };

            ws.onmessage = function (evt)
            {
                var received_msg = evt.data;
                console.log("Message from Sarlab server: "+received_msg);
            };

            ws.onerror = function()
            {
                if (ws.readyState === 1 || ws.readyState === 2) ws.send("exit");
                alert("You need to download, install and/or run the Sarlab service");
                var a = document.createElement("a");
                a.download = "installsarlabservice.zip";
                a.href = host + "/mod/ejsapp/installsarlabservice.zip";
                a.target = "_blank";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                delete a;
            };

            ws.onclose = function()
            {
                // websocket is closed.
                console.log("Connection has been closed.");
            };
        },

        connectExperience : function(IP, port, idExp, expTime, user, password){
            var obj = '{'
             +'"ip_server" : ' + IP + ','
             +'"port_server"  : ' + port + ','
             +'"id_exp" : ' + idExp + ','
             +'"expiration_time" : ' + expTime + ','
             +'"user" : ' + user + ','
             +'"password"  : ' + password
             +'}';
            ws.send(obj);
        },

        stopExperience : function(){
            ws.send("exit");
        },

        stopExperienceOnLeave : function() {
            window.onbeforeunload = function() {
                ws.send("exit");
            }
        },

        resetExperience : function(){
            ws.send("reset");
        }
    };
    return t;
});