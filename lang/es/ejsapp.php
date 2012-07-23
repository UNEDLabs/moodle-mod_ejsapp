<?php

// This file is part of the Moodle module "EJSApp"
//
// EJSApp is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
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
 * Spanish strings for ejsapp
 *
 * @package    mod
 * @subpackage ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'EJSApp';
$string['modulenameplural'] = 'EJSApps';
$string['modulename_help'] = 'El módulo de actividad EJSApp permite a un profesor añadir applets de Java creados con Easy Java Simulations (EJS) en sus cursos de Moodle.

Los applets de EJS quedarán embebidos dentro de los cursos de Moodle. El profesor puede seleccionar si mantener el tamaño original del applet o permitir que Moodle lo reescale de acuerdo al espacio disponible. Si el applet fue compilado con la opción "Añadir soporte idiomas" en EJS, el applet embebido en Moodle con la actividad EJSApp configurará automáticamente su idioma a aquel seleccionado por el usuario de Moodle, si esto es posible. Esta actividad es compatible con la configuración de restricciones de acceso condicional.

Cuando se usa junto al Navegador EJSApp de Ficheros, los estudiantes pueden guardar el estado del applet EJS, cuando lo están ejecutando, simplemente pulsando con el botón derecho del ratón sobre el applet y seleccionando la opción adecuada en el menú que aparece. La información de estos estados se graba en un fichero .xml que es guardado en el area de ficheros privados (Navegador EJSApp de Ficheros). Estos estados pueden recuperarse de dos maneras distintas: pulsando sobre los ficheros .xml en el Navegador EJSApp de Ficheros o pulsando con el botón derecho del ratón sobre el applet EJS y seleccionando la opción adecuada en el menú. Si el applet EJS está preparado para tal efecto, también puede grabar ficheros de texto o imágenes y guardarlos en el área de ficheros privados.

Cuando se usa junto al bloque EJSApp de Sesiones Colaborativas, los usuarios de Moodle pueden trabajar con el mismo applet EJS de una manera síncrona, es decir, de tal forma que el applet mostrará el mismo estado para todos los usuarios en la sesión colaborativa. Gracias a este bloque, los usuarios pueden crear sesiones, invitar a otros usuarios y trabajar juntos con la misma actividad EJSApp.';
$string['ejsappname'] = 'Nombre del laboratorio';
$string['ejsappname_help'] = 'Nombre con que figurar&aacute; el laboratorio en el curso';
$string['ejsapp'] = 'EJSApp';
$string['pluginadministration'] = 'Administraci&oacute;n del EJSApp';
$string['pluginname'] = 'EJSApp';

$string['state_load_msg'] = 'Se va a actualizar el estado del laboratorio';
$string['state_fail_msg'] = 'Fallo al cargar el estado';

$string['jar_file'] = 'Archivo .jar que encapsula el laboratorio EJS';

$string['appletfile'] = 'Easy Java Simulation';
$string['appletfile_required'] = 'Se debe seleccionar un archivo .jar';
$string['appletfile_help'] = 'Selecione el archivo .jar que encapsula el laboratorio EJS (Easy Java Simulation). La pagina oficial de EJS es http://fem.um.es/Ejs/';

$string['preserve_applet_size'] = 'Preservar el tama&ntilde;o del applet';
$string['preserve_applet_size_help'] = 'Si selecciona esta opci&oacute;n, el applet se visualizar&aacute; con el tama&ntilde;o especificado con EJS. En caso contraro, moodle recalcular&aacute; el tama&ntilde;o';
$string['is_rem_lab'] = '¿Sistema experimental remoto?';
$string['is_rem_lab_help'] = 'Si este EJSApp conecta a recursos reales de manera remota, selecciona "sí". En caso contrario, selecciona "no".';

$string['rem_lab_conf'] = 'Configuracion del laboratorio remoto';
$string['ip_lab'] = 'IP direction';
$string['ip_lab_help'] = 'Esperimental system IP direction.';
$string['ip_lab_required'] = 'Debe proporcionar una direccion IP valida.';
$string['totalslots'] = 'Horas de trabajo totales';
$string['totalslots_help'] = 'Cantidad total de horas máximas que se le permitirá usar a cada alumno para trabajar con este laboratorio.';
$string['weeklyslots'] = 'Horas de trabajo semanales';
$string['weeklyslots_help'] = 'Cantidad semanal de horas máximas que se le permitirá usar a cada alumno para trabajar con este laboratorio.';
$string['dailyslots'] = 'Horas de trabajo diarias';
$string['dailyslots_help'] = 'Cantidad diaria de horas máximas que se le permitirá usar a cada alumno para trabajar con este laboratorio.';

$string['file_error'] = "No pudo abrirse el fichero en el servidor";
$string['manifest_error'] = " > No se ha podido encontrar o abrir el manifiesto .mf. Revise el fichero que ha cargado.";