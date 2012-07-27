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
$string['modulename_help'] = 'El m�dulo de actividad EJSApp permite a un profesor a�adir applets de Java creados con Easy Java Simulations (EJS) en sus cursos de Moodle.

Los applets de EJS quedar�n embebidos dentro de los cursos de Moodle. El profesor puede seleccionar si mantener el tama�o original del applet o permitir que Moodle lo reescale de acuerdo al espacio disponible. Si el applet fue compilado con la opci�n "A�adir soporte idiomas" en EJS, el applet embebido en Moodle con la actividad EJSApp configurar� autom�ticamente su idioma a aquel seleccionado por el usuario de Moodle, si esto es posible. Esta actividad es compatible con la configuraci�n de restricciones de acceso condicional.

Cuando se usa junto al Navegador EJSApp de Ficheros, los estudiantes pueden guardar el estado del applet EJS, cuando lo est�n ejecutando, simplemente pulsando con el bot�n derecho del rat�n sobre el applet y seleccionando la opci�n adecuada en el men� que aparece. La informaci�n de estos estados se graba en un fichero .xml que es guardado en el area de ficheros privados (Navegador EJSApp de Ficheros). Estos estados pueden recuperarse de dos maneras distintas: pulsando sobre los ficheros .xml en el Navegador EJSApp de Ficheros o pulsando con el bot�n derecho del rat�n sobre el applet EJS y seleccionando la opci�n adecuada en el men�. Si el applet EJS est� preparado para tal efecto, tambi�n puede grabar ficheros de texto o im�genes y guardarlos en el �rea de ficheros privados.

Cuando se usa junto al bloque EJSApp de Sesiones Colaborativas, los usuarios de Moodle pueden trabajar con el mismo applet EJS de una manera s�ncrona, es decir, de tal forma que el applet mostrar� el mismo estado para todos los usuarios en la sesi�n colaborativa. Gracias a este bloque, los usuarios pueden crear sesiones, invitar a otros usuarios y trabajar juntos con la misma actividad EJSApp.';
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

$string['applet_size_conf'] = 'Reescalado del applet';
$string['applet_size_conf_help'] = 'Tres opciones: 1) "Mantener tama�o original" mantendr� el tama�o original del applet en EJS, 2) "Permitir que Moodle fije el tama�o" redimensionar� el applet para que ocupe todo el espacio posible a la par que respeta la relaci�n de tama�o original, 3) "Permitir que el usuario fije el tama�o" permitir� al usuario establecer el tama�o del applet y seleccionar si desea mantener, o no, su relaci�n de tama�o original.';
$string['preserve_applet_size'] = 'Mantener tama�o original';
$string['moodle_resize'] = 'Permitir que Moodle fije el tama�o';
$string['user_resize'] = 'Permitir que el usuario fije el tama�o';

$string['preserve_aspect_ratio'] = 'Mantener relaci�n de tama�o';
$string['preserve_aspect_ratio_help'] = 'Si selecciona esta opci&oacute;n, se respetar� la relaci�n de tama�o original del applet. En ese caso, el usuario podr� modificar la anchura del applet y el sistema ajustar� autom�ticamente el valor para su altura. Si no se selecciona, el usuario podr� fijar tanto su anchura como su altura.';

$string['custom_width'] = 'Anchura del applet (px)';
$string['custom_width_required'] = 'ATENCION: La anchura del applet no fue fijada. Debes proporcionar un valor distinto.';

$string['custom_height'] = 'Altura del applet (px)';
$string['custom_height_required'] = 'ATENCION: La altura del applet no fue fijada. Debes proporcionar un valor distinto.';

$string['appwording'] = 'Enunciado';

$string['rem_lab_conf'] = 'Configuracion del laboratorio remoto';
$string['is_rem_lab'] = '�Sistema experimental remoto?';
$string['is_rem_lab_help'] = 'Si este EJSApp conecta a recursos reales de manera remota, selecciona "s�". En caso contrario, selecciona "no".';
$string['ip_lab'] = 'IP direction';
$string['ip_lab_help'] = 'Esperimental system IP direction.';
$string['ip_lab_required'] = 'ATENCION: Debe proporcionar una direccion IP valida.';
$string['totalslots'] = 'Horas de trabajo totales';
$string['totalslots_help'] = 'Cantidad total de horas m�ximas que se le permitir� usar a cada alumno para trabajar con este laboratorio.';
$string['weeklyslots'] = 'Horas de trabajo semanales';
$string['weeklyslots_help'] = 'Cantidad semanal de horas m�ximas que se le permitir� usar a cada alumno para trabajar con este laboratorio.';
$string['dailyslots'] = 'Horas de trabajo diarias';
$string['dailyslots_help'] = 'Cantidad diaria de horas m�ximas que se le permitir� usar a cada alumno para trabajar con este laboratorio.';

$string['file_error'] = "No pudo abrirse el fichero en el servidor";
$string['manifest_error'] = " > No se ha podido encontrar o abrir el manifiesto .mf. Revise el fichero que ha cargado.";

//Settings
$string['columns_width'] = "Ancho de columnas";
$string['columns_width_description'] = "Ancho total ocupado (en px) por las columnas en tu tema visual de Moodle";
$string['collaborative_port'] = "Puerto para sesiones colaborativas";
$string['collaborative_port_description'] = "Puerto usado para establecer la comunicacion en las sesiones colaborativas (requiere el bloque EJSApp collab sessions)";
$string['sarlab'] = "Usar Sarlab?";
$string['sarlab_description'] = "Seleccionar 'si' unicamente si se esta usando Sarlab; un system que gestiona las conexiones a recursos de laboratorios remotos";