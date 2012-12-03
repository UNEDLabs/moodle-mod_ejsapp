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
$string['modulename_help'] = 'El m&oacute;dulo de actividad EJSApp permite a un profesor a&ntilde;adir applets de Java creados con Easy Java Simulations (EJS) en sus cursos de Moodle.

Los applets de EJS quedar&aacute;n embebidos dentro de los cursos de Moodle. El profesor puede seleccionar si mantener el tama&ntilde;o original del applet o permitir que Moodle lo reescale de acuerdo al espacio disponible. Si el applet fue compilado con la opci&oacute;n "A&ntilde;adir soporte idiomas" en EJS, el applet embebido en Moodle con la actividad EJSApp configurar&aacute; autom&aacute;ticamente su idioma a aquel seleccionado por el usuario de Moodle, si esto es posible. Esta actividad es compatible con la configuraci&oacute;n de restricciones de acceso condicional.

Cuando se usa junto al Navegador EJSApp de Ficheros, los estudiantes pueden guardar el estado del applet EJS, cuando lo est&eacute;n ejecutando, simplemente pulsando con el bot&oacute;n derecho del rat&oacute;n sobre el applet y seleccionando la opci&oacute;n adecuada en el men&uacute; que aparece. La informaci&oacute;n de estos estados se graba en un fichero .xml que es guardado en el area de ficheros privados (Navegador EJSApp de Ficheros). Estos estados pueden recuperarse de dos maneras distintas: pulsando sobre los ficheros .xml en el Navegador EJSApp de Ficheros o pulsando con el bot&oacute;n derecho del rat&oacute;n sobre el applet EJS y seleccionando la opci&oacute;n adecuada en el men&uacute;. Si el applet EJS est&aacute; preparado para tal efecto, tambi&eacute;n puede grabar ficheros de texto o im&aacute;genes y guardarlos en el &aacute;rea de ficheros privados.

Cuando se usa junto al bloque EJSApp de Sesiones Colaborativas, los usuarios de Moodle pueden trabajar con el mismo applet EJS de una manera s&iacute;ncrona, es decir, de tal forma que el applet mostrar&aacute; el mismo estado para todos los usuarios en la sesi&oacute;n colaborativa. Gracias a este bloque, los usuarios pueden crear sesiones, invitar a otros usuarios y trabajar juntos con la misma actividad EJSApp.';
$string['ejsappname'] = 'Nombre del laboratorio';
$string['ejsappname_help'] = 'Nombre con que figurar&aacute; el laboratorio en el curso';
$string['ejsapp'] = 'EJSApp';
$string['pluginadministration'] = 'Administraci&oacute;n del EJSApp';
$string['pluginname'] = 'EJSApp';

$string['state_load_msg'] = 'Se va a actualizar el estado del laboratorio';
$string['state_fail_msg'] = 'Fallo al cargar el estado';

$string['more_text'] = 'Texto optional tras el applet';

$string['jar_file'] = 'Archivo .jar que encapsula el laboratorio EJS';

$string['appletfile'] = 'Easy Java Simulation';
$string['appletfile_required'] = 'Se debe seleccionar un archivo .jar';
$string['appletfile_help'] = 'Selecione el archivo .jar que encapsula el laboratorio EJS (Easy Java Simulation). La p&aacute;gina oficial de EJS es http://fem.um.es/Ejs/';

$string['applet_size_conf'] = 'Reescalado del applet';
$string['applet_size_conf_help'] = 'Tres opciones: 1) "Mantener tama&ntilde;o original" mantendr&aacute; el tama&ntilde;o original del applet en EJS, 2) "Permitir que Moodle fije el tama&ntilde;o" redimensionar&aacute; el applet para que ocupe todo el espacio posible a la par que respeta la relaci&oacute;n de tama&ntilde;o original, 3) "Permitir que el usuario fije el tama&ntilde;o" permitir&aacute; al usuario establecer el tama&ntilde;o del applet y seleccionar si desea mantener, o no, su relaci&oacute;n de tama&ntilde;o original.';
$string['preserve_applet_size'] = 'Mantener tama&ntilde;o original';
$string['moodle_resize'] = 'Permitir que Moodle fije el tama&ntilde;o';
$string['user_resize'] = 'Permitir que el usuario fije el tama&ntilde;o';

$string['preserve_aspect_ratio'] = 'Mantener relaci&oacute;n de tama&ntilde;o';
$string['preserve_aspect_ratio_help'] = 'Si selecciona esta opci&oacute;n, se respetar&aacute; la relaci&oacute;n de tama&ntilde;o original del applet. En ese caso, el usuario podr&aacute; modificar la anchura del applet y el sistema ajustar&aacute; autom&aacute;ticamente el valor para su altura. Si no se selecciona, el usuario podr&aacute; fijar tanto su anchura como su altura.';

$string['custom_width'] = 'Anchura del applet (px)';
$string['custom_width_required'] = 'ATENCI&Oacute;N: La anchura del applet no fue fijada. Debes proporcionar un valor distinto.';

$string['custom_height'] = 'Altura del applet (px)';
$string['custom_height_required'] = 'ATENCI&Oacute;N: La altura del applet no fue fijada. Debes proporcionar un valor distinto.';

$string['appwording'] = 'Enunciado';

$string['rem_lab_conf'] = 'Configuraci&oacute;n del laboratorio remoto';

$string['is_rem_lab'] = 'Sistema experimental remoto?';
$string['is_rem_lab_help'] = 'Si este EJSApp conecta a recursos reales de manera remota, selecciona "s&iacute;". En caso contrario, selecciona "no".';

$string['sarlab'] = "Usar Sarlab?";
$string['sarlab_help'] = "Seleccionar 'ss&iacute;' unicamente si se esta usando Sarlab; un sistema que gestiona las conexiones a recursos de laboratorios remotos";

$string['sarlab_instance'] = "Servidor Sarlab para este laboratorio";
$string['sarlab_instance_help'] = "El orden se corresponde con aquel usado para los valores en las variables sarlab_IP y sarlab_port fijados en la p&aacute;gina de configuraci&oacute;n de ejsapp";

$string['ip_lab'] = 'direccis&oacute;n IP';
$string['ip_lab_help'] = 'Direcci&oacute;n IP del sistema experimental.  Si est&aacute; usando Sarlab, no tiene que preocuparse de este par&aacute;metro.';
$string['ip_lab_required'] = 'ATENCIs&Oacute;N: Debe proporcionar una direccis&oacute;n IP valida.';
$string['port'] = 'Puerto';
$string['port_help'] = 'El puerto a usar para establecer la comunicaci&oacute;n. Si est&aacute; usando Sarlab, no tiene que preocuparse de este par&aacute;metro.';
$string['port_required'] = 'ATENCIs&Oacute;N: Debe proporcionar un puerto v&aacute;lido.';
$string['practiceintro'] = 'Identificador de pr&aacute;ctica en Sarlab';
$string['practiceintro_help'] = 'Pr&aacute;cticas (separadas por punto y coma) configuradas en Sarlab para este sistema experimental.';
$string['practiceintro_required'] = 'ATENCIs&Oacute;N: Debe especificar al menos una pr&aacute;ctica.';
$string['totalslots'] = 'Horas de trabajo totales';
$string['totalslots_help'] = 'Cantidad total de horas m&aacute;ximas que se le permitir&aacute; usar a cada alumno para trabajar con este laboratorio.';
$string['weeklyslots'] = 'Horas de trabajo semanales';
$string['weeklyslots_help'] = 'Cantidad semanal de horas m&aacute;ximas que se le permitir&aacute; usar a cada alumno para trabajar con este laboratorio.';
$string['dailyslots'] = 'Horas de trabajo diarias';
$string['dailyslots_help'] = 'Cantidad diaria de horas m&aacute;ximas que se le permitir&aacute; usar a cada alumno para trabajar con este laboratorio.';

$string['file_error'] = "No pudo abrirse el fichero en el servidor";
$string['manifest_error'] = " > No se ha podido encontrar o abrir el manifiesto .mf. Revise el fichero que ha cargado.";

$string['no_booking'] = 'No tiene reserva para este laboratorio en este horario.';
$string['check_bookings'] = 'Consulte sus reservas activas con el sistema de reservas.';

//Settings
$string['default_display_set'] = "Opciones de visualizaci&oacute;n por defecto";
$string['default_communication_set'] = "Opciones de comunicaci&oacute;n por defecto";
$string['columns_width'] = "Ancho de columnas";
$string['columns_width_description'] = "Ancho total ocupado (en px) por las columnas en tu tema visual de Moodle";
//$string['sarlab_description'] = "Seleccionar 'si' unicamente si se esta usando Sarlab; un sistema que gestiona las conexiones a recursos de laboratorios remotos";
$string['collaborative_port'] = "Puerto para sesiones colaborativas";
$string['collaborative_port_description'] = "Puerto usado para establecer la comunicacis&oacute;n en las sesiones colaborativas (requiere el bloque EJSApp collab sessions)";
$string['sarlab_IP'] = "Direcci&aacute;n IP del servidor Sarlab";
$string['sarlab_IP_description'] = "Si usa Sarlab (un sistema que gestiona las conexiones a recursos de laboratorios remotos), debe proporcionar la direcci&oacute;n IP del servidor que ejecuta el sistema Sarlab que desea utilizar. En caso contrario, este valor no es usado, de modo que puede dejar el valor por defecto";
$string['sarlab_port'] = "Puerto de comunicaciones con Sarlab";
$string['sarlab_port_description'] = "Si usa Sarlab (un sistema que gestiona las conexiones a recursos de laboratorios remotos), debe proporcionar un puerto v&aacute;lido para establecer las comunicaciones necesarias con el servidor de Sarlab. En caso contrario, este valor no es usado, de modo que puede dejar el valor por defecto";