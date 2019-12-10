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
 * Websocket service for connecting to ENLARGE experiences.
 *
 * @package    mod_ejsapp
 * @copyright  2016 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function() {
    var ws;
    var t = {
        enlargeWebSocket: function(command, IP, port, idExp, expTime, user, password, jarPath) {
            ws = new WebSocket("ws://127.0.0.1:8887");
            ws.onopen = function() {
                // Websocket is connected, send data using send().
                ws.send("Message to send \r\n");
                ws.connectExperience(command, IP, port, idExp, expTime, user, password, jarPath);
                console.log("Connected to ENLARGE experience: " + idExp);
            };

            ws.onmessage = function (evt) {
                console.log("Message from myFrontier server: " + evt.data);
            };

            ws.onerror = function() {
                if (ws.readyState === 1 || ws.readyState === 2) {
                    ws.send("exit");
                }
                alert("You need to download, install and/or run the ENLARGE service.");
                var a = document.createElement("a");
                a.download = "myDiscovery_win64.exe";
                a.href = "https://irs.nebsyst.com/assets/install_myDiscovery_win64.exe";
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

        connectExperience: function(command, IP, port, idExp, expTime, user, password, jarPath) {
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

        stopExperience: function() {
            ws.send('{"command":"exit"}');
        },

        stopExperienceOnLeave: function() {
            window.onbeforeunload = function() {
                ws.resetExperience();
                ws.stopExperience();
            };
        },

        resetExperience: function() {
            ws.send('{"command":"reset"}');
        }
    };
    return t;
});