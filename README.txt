##############
# EJSApp 2.0 #
##############

1. Content
==========

This plugin lets you add any Java applet or Javascript application created with Easy Java 
Simulations (EJS) to your Moodle course.
The Java applets should be compiled with version 4.37 (build 20120715 or later) of EJS to 
properly work. EJSApp also allows you to add EJS Javascript applications, which are
generated with EJS version 5.0 or later.

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
This module has been tested in all Moodle 2.x versions.

This module enhances its functionality when used along with the ejsappbooking module and/or
the ejsapp_file_browser, the ejsapp_collab_session blocks and the osp repository plugin.
You can find and download them at https://moodle.org/plugins/browse.php?list=set&id=27, in
the plugins section in the Moodle.org webpage or at https://github.com/UNEDLabs.

An explanation of EJSApp is included in the folder "doc". There, you will also find a txt 
file with relevant links.

 WARNING: If you are updating ejsapp from a previous version, DO NOT replace/delete your old 
 jarfiles directory inside your old ejsapp directory.

------------------------------------------------------------------------------------------------
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
------------------------------------------------------------------------------------------------

------------------------------------------------------------------------------------------------
 IMPORTANT: For Unix Moodle servers (e.g., Linux and Mac systems), if you have a trust certificate 
 and you want to use the option to automatically sign the Java applets, you must give the apache 
 user permissions over the jarsigner (Java installation folder) and the sign.sh script (included
 with this plugin) files.
                                                                                                
 The following points describe how to do it:                                                    
                                                                                                
 1) Install jarsigner alternative
 $ alternatives --install /usr/bin/jarsigner jarsigner /usr/java/jdk1.7.0_51/bin/jarsigner 20000
 $ alternatives --set jarsigner /usr/java/jdk1.7.0_51/bin/jarsigner                                                                                    
                                                                                                
 2) Change the owner of the jarsigner file to apache.                                             
 For instance,                                                                                  
 2.a) in Linux CentOS, you should write                         
 $ chown apache /usr/bin/jarsigner                                                                     
 2.b) in Linux OpenSuse,you should write                       
 $ chown wwwrun /usr/bin/jarsigner                                                                     
                                                                                                
 3) Set owner of mod/ejsapp/sign.sh to apache user in your machine (Note you may need to repeat
 this step when you update your EJSApp plugin).
 $ cd ejsapp
 3.a) in Linux CentOS, you should write                         
 $ chown apache sign.sh                                                                     
 3.b) in Linux OpenSuse,you should write                       
 $ chown wwwrun sign.sh                                                                       
------------------------------------------------------------------------------------------------

4. Configuration
================

When installing ejsapp for the first time, you will need to set a few variables:

   certificate_path:	This variable defines the absolute path to the trust certificate file.

   certificate_password:This variable must contain the password of the trust certificate.

   certificate_alias: 	This variable stores the alias given to your trust certificate.

   sarlab_IP:	  	This variable defines the IP(s) address(es) of the SARLAB system(s) used for 
                  	managing the access to the remote laboratories. If left empty, the plugin
                  	understands that SARLAB is not used.

   sarlab_port:		This variable defines the port(s) used to communicate with the SARLAB 
                  	system(s). If left empty, the plugin understands that SARLAB is not used.

   sarlab_enc_key:	This 16 characters long variable must be configure to match exactly the key set
		  	        in the SARLAB system(s).
                                              
5. Testing
==========
The "test" directory of ejsapp includes a two EJS applications (one is a java applet and the other
one is a javascript simulation) that can be used for testing purposes.

6. Authors
==========
EJSApp has been developed by:
 - Luis de la Torre: ldelatorre@dia.uned.es
 - Ruben Heradio: rheradio@issi.uned.es

  at the Computer Science and Automatic Control Department, Spanish Open University (UNED), 
  Madrid, Spain.