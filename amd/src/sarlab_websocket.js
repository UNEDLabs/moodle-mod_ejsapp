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
// (UNED), Madrid, Spain.

/**
 * Websocket service for connecting to Sarlab experiences.
 *
 * @package    mod_ejsapp
 * @copyright  2016 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function() {
    var ws;
    var t = {
        SarlabWebSocket : function(host, command, IP, port, idExp, expTime, user, password, jarPath) {
            ws = new WebSocket("ws://127.0.0.1:8887");
            ws.onopen = function() {
                // Websocket is connected, send data using send().
                ws.send("Message to send \r\n");
                var obj = '{'
                    +'"command":"' + command + '",'
                    +'"ip_server":"' + IP + '",'
                    +'"port_server":"' + port + '",'
                    +'"id_exp":"' + idExp + '",'
                    +'"expiration_time":"' + expTime + '",'
                    +'"user":"' + user + '",'
                    +'"password":"' + password + '"';
                if (command === 'execjar') {
                    obj += ',' + '"jar_file":"' + jarPath + '"';
                }
                obj += '}';
                ws.send(obj);
                console.log("Connected to Sarlab experience: " + idExp);
            };

            ws.onmessage = function (evt) {
                console.log("Message from Sarlab server: " + evt.data);
            };

            ws.onerror = function() {
                if (ws.readyState === 1 || ws.readyState === 2) {
                    ws.send("exit");
                }
                alert("You need to download, install and/or run the Sarlab service. Use this password to open the zip: sarlab");
                var a = document.createElement("a");
                a.download = "sarlabservice.zip";
                a.href = host + "/mod/ejsapp/sarlabservice.zip";
                a.target = "_blank";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            };

            ws.onclose = function() {
                // Websocket is closed.
                console.log("Connection has been closed.");
            };
        },

        connectExperience : function(command, IP, port, idExp, expTime, user, password, jarPath) {
            var obj = '{'
                +'"command":"' + command + '",'
                +'"ip_server":"' + IP + '",'
                +'"port_server":"' + port + '",'
                +'"id_exp":"' + idExp + '",'
                +'"expiration_time":"' + expTime + '",'
                +'"user":"' + user + '",'
                +'"password":"' + password + '"';
            if (command === 'execjar') {
                obj += ',' + '"jar_file":"' + jarPath + '"';
            }
            obj += '}';
            ws.send(obj);
        },

        stopExperience : function() {
            ws.send('{"command":"exit"}');
        },

        stopExperienceOnLeave : function() {
            window.onbeforeunload = function() {
                ws.send('{"command":"reset"}');
                ws.send('{"command":"exit"}');
            };
        },

        resetExperience : function() {
            ws.send('{"command":"reset"}');
        }
    };
    return t;
});