##############
# EJSApp 1.4 #
##############

1. Content
==========

This plugin lets you add any Java applet created with Easy Java Simulations (EJS) to your 
Moodle course.
The Java applets should be compiled with version 4.37 (build 20120715 or later) of EJS to 
properly work.

2. License
==========

EJSApp is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

EJSApp is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

The GNU General Public License is available on <http://www.gnu.org/licenses/>

3. Installation
===============

If you downloaded this plugin from github, you will need to change the folder's name to
ejsapp. If you downloaded it from Moodle.org, then you are fine.

This is a module plugin for Moodle so you should place the ejsapp folder in your /mod
folder,inside you Moodle installation.
This module has been tested in Moodle versions 2.0, 2.1, 2.2 and 2.3.

This module enhances its functionality when used along with the ejsappbooking module and/or
the ejsapp_file_browser and the ejsapp_collab_session blocks.
You can find and download them at the plugins section in the Moodle.org webpage or at
https://github.com/UNEDLabs.

An explanation of EJSApp is included in the folder "doc". There, you will also find a txt 
file with relevant links.

-----------------------------------------------------------------------------------------------
 IMPORTANT: For Unix Moodle servers (e.g., Linux and Mac systems), EJSApp requires that (i) the 
 apache user is the owner of the "jarfiles" dir inside the "ejsapp" dir, and (ii) it has        
 permissions to read, write and execute the jarfiles dir.                                          
                                                                                                
 The following points describe how to do it:                                                    
                                                                                                
 1) go to the ejsapp dir:                                                                       
 $ cd ejsapp                                                                                    
                                                                                                
 2) Change the owner of the jarfiles dir to apache.                                             
 For instance,                                                                                  
 2.a) in Linux CentOS, the apache user is "apache", so you should write                         
 $ chown -R apache jarfiles                                                                     
 2.b) in Linux OpenSuse, the apache user is "wwwrun", so you should write                       
 $ chown -R wwwrun jarfiles                                                                     
                                                                                                
 3) Change the permissions of the apache user:                                                  
 $ chmod -R 700 jarfiles                                                                        
 -----------------------------------------------------------------------------------------------

4. Configuration
================

When installing ejsapp for the first time, you will need to set four variables:

   columns_width: This is the total width occupied by your columns (in pixels) in your Moodle
                  visual theme. This variable is used to resize the applet size when embedded
		  in Moodle and the "Preserve original applet layout" options is set to no.
		  Default configuration works well with the default theme and with many others
                  based on the two columns format. However, other themes may require changes 
                  in this variable.

   collaborative_port: When the "EJSApp Collaborative Sessions" block is also installed, this
		       variable sets the port used for establishing TCP connections in the 
                       collaborative sessions.

   sarlab_IP:	  This variable defines the IP(s) direction(s) of the SARLAB system(s) used for 
                  managing the access to the remote laboratories. If left empty, the plugin
                  understands that SARLAB is not used.

   sarlab_port:	  This variable defines the port(s) used to communicate with the SARLAB 
                  system(s). If left empty, the plugin understands that SARLAB is not used.
                                              
5. Testing
==========
The "test" directory of ejsapp includes an EJS simulation that can be used 
for testing purposes.

6. Authors
==========
EJSApp has been developed by:
 - Luis de la Torre: ldelatorre@dia.uned.es
 - Ruben Heradio: rheradio@issi.uned.es

  at the Computer Science and Automatic Control Department, Spanish Open University (UNED), 
  Madrid, Spain.                                                      
