[Zenodo](https://zenodo.org/badge/latestdoi/18948/UNEDLabs/moodle-mod_ejsapp)

DOI: 10.5281/zenodo.3549877

## 1. Content

This plugin lets you add any Java applet or Javascript application created with Easy Java/Javascript Simulations (EjsS) to your
Moodle course.

The Java applets should have been compiled with version 4.37 (build 20120715 or later) of EJS while the Javascript
applications should have been created with version 5.1 (build 20150613 or later) to work properly.

This plugin will not receive further updates other than (maybe) some form of a basic version of the automatic generation of lab interfaces. For more updated versions, contact the authors and/or Nebulous Systems at https://www.nebsyst.com, https://irs.nebsyst.com or contact@nebsyst.com.

## 2. License

EJSApp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

EJSApp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

The GNU General Public License is available on <http://www.gnu.org/licenses/>

## 3. Installation

If you downloaded this plugin from github, you will need to change the folder's name to ejsapp. If you downloaded it
from Moodle.org, then you are fine.

This is a module plugin for Moodle so you should place the ejsapp folder in your /mod folder, inside your Moodle
installation directory.

This module enhances its functionality when used along with the ejsappbooking module and/or the ejsapp_file_browser,
the ejsapp_collab_session blocks and the osp repository plugin. You can find and download them at
https://moodle.org/plugins/browse.php?list=set&id=27, in the plugins section in the Moodle.org webpage or at
https://github.com/UNEDLabs.
                                                                                                                                
------------------------------------------------------------------------------------------------

## 4. Configuration

When installing ejsapp for the first time, you may need to set a few variables (in case you want to use applets):

   check_activity: How often the users' activity in EJSApp is checked (s)
   
   server_id: ID used for registering this Moodle site in ENLARGE IRS (https://irs.nebsyst.com). Leave it blank if the site is not registered

   certificate_path:	This variable defines the absolute path to the trust certificate file.

   certificate_password:This variable must contain the password of the trust certificate.

   certificate_alias: 	This variable stores the alias given to your trust certificate.

## 5. Authors

EJSApp has been developed by:
 - Luis de la Torre: ldelatorre@dia.uned.es
 - Ruben Heradio: rheradio@issi.uned.es

  at the Computer Science and Automatic Control Department, Spanish Open University (UNED), Madrid, Spain.
