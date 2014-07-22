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
$string['noejsapps'] = 'No hay actividades EJSApp en este curso';

$string['state_load_msg'] = 'Se va a actualizar el estado del laboratorio';
$string['state_fail_msg'] = 'Error al intentar cargar el estado';

$string['exp_load_msg'] = 'Se va a ejecutar un experimento para este laboratorio';
$string['exp_fail_msg'] = 'Error al intentar ejecutar el experimento';

$string['more_text'] = 'Texto optional tras el applet';

$string['jar_file'] = 'Archivo .jar o .zip que encapsula el laboratorio EJsS';

$string['appletfile'] = 'Easy Java(script) Simulation';
$string['appletfile_required'] = 'Se debe seleccionar un archivo .jar o .zip';
$string['appletfile_help'] = 'Selecione el archivo .jar o .zip que encapsula el laboratorio EJsS (Easy Java(script) Simulation). La p&aacute;gina oficial de EJsS es http://fem.um.es/Ejs/';

$string['applet_size_conf'] = 'Reescalado del applet';
$string['applet_size_conf_help'] = 'Tres opciones: 1) "Mantener tama&ntilde;o original" mantendr&aacute; el tama&ntilde;o original del applet en EJS, 2) "Permitir que Moodle fije el tama&ntilde;o" redimensionar&aacute; el applet para que ocupe todo el espacio posible a la par que respeta la relaci&oacute;n de tama&ntilde;o original, 3) "Permitir que el usuario fije el tama&ntilde;o" permitir&aacute; al usuario establecer el tama&ntilde;o del applet y seleccionar si desea mantener, o no, su relaci&oacute;n de tama&ntilde;o original.';
$string['preserve_applet_size'] = 'Mantener tama&ntilde;o original';
$string['moodle_resize'] = 'Permitir que Moodle fije el tama&ntilde;o';
$string['user_resize'] = 'Permitir que el usuario fije el tama&ntilde;o';

$string['preserve_aspect_ratio'] = 'Mantener relaci&oacute;n de tama&ntilde;o';
$string['preserve_aspect_ratio_help'] = 'Si selecciona esta opci&oacute;n, se respetar&aacute; la relaci&oacute;n de tama&ntilde;o original del applet. En ese caso, el usuario podr&aacute; modificar la anchura del applet y el sistema ajustar&aacute; autom&aacute;ticamente el valor para su altura. Si no se selecciona, el usuario podr&aacute; fijar tanto su anchura como su altura.';

$string['custom_width'] = 'Anchura del applet (px)';
$string['custom_width_required'] = 'ATENCI&Oacute;N: La anchura del applet no ha sido fijada. Debes proporcionar un valor distinto.';

$string['custom_height'] = 'Altura del applet (px)';
$string['custom_height_required'] = 'ATENCI&Oacute;N: La altura del applet no ha sido fijada. Debes proporcionar un valor distinto.';

$string['appwording'] = 'Enunciado';

$string['state_file'] = 'Archivo .xml con el estado que este laboratorio EJS debe leer';

$string['statefile'] = 'Estado del Easy Java Simulation';
$string['statefile_help'] = 'Seleccione el archivo .xml con el estado que la aplicaci&oacute;n EJS debe cargar al ejecutarse.';

$string['personalize_vars'] = 'Personalizar variables del laboratorio EJS';

$string['use_personalized_vars'] = 'Personalizar variables para cada usuario?';
$string['use_personalized_vars_help'] = 'Seleccione "ss&iacute;" si conoce el nombre de alguna de las variables en el modelo EJS y deseas que adquieran valores diferentes para cada usuario que acceda a esta aplicaci&oacute;n.';

$string['var_name'] = 'Nombre {no}';
$string['var_name_help'] = 'Nombre de la variable en el modelo EJS.';

$string['var_type'] = 'Tipo {no}';
$string['var_type_help'] = 'Tipo de la variable en el modelo EJS.';

$string['min_value'] = 'Valor m&iacute;nimo {no}';
$string['min_value_help'] = 'M&iacute;nimo valor permitido para la variable.';

$string['max_value'] = 'M&aacute;ximo valor {no}';
$string['max_value_help'] = 'M&aacute;ximo valor permitido para la variable.';

$string['vars_required'] = 'ATENCI&Oacute;N: Si desea utilizar variables personalizadas, debe espeficificar al menos una.';
$string['vars_incorrect_type'] = 'ATENCI&Oacute;N: El tipo y los valores especificados para esta variable no se corresponden entre s&iacute;.';

$string['experiment_file'] = 'Archivo .exp con el experimento que la aplicaci&oacute;n EJS debe ejecutar al cargarse';

$string['expfile'] = 'Experimento del Easy Java Simulation';
$string['expfile_help'] = 'Seleccione el archivo .exp con el experimento que la aplicaci&oacute;n EJS debe ejecutar al cargarse.';

$string['rem_lab_conf'] = 'Configuraci&oacute;n del laboratorio remoto';

$string['is_rem_lab'] = 'Sistema experimental remoto?';
$string['is_rem_lab_help'] = 'Si este EJSApp conecta a recursos reales de manera remota Y quieres que el Sistema de Reservas EJSApp controle su acceso, selecciona "s&iacute;". En caso contrario, selecciona "no".';

$string['sarlab'] = "Usar Sarlab?";
$string['sarlab_help'] = "Seleccionar 'ss&iacute;' unicamente si se esta usando Sarlab; un sistema que gestiona las conexiones a recursos de laboratorios remotos";

$string['sarlab_instance'] = "Servidor Sarlab para este laboratorio";
$string['sarlab_instance_help'] = "El orden se corresponde con aquel usado para los valores en las variables sarlab_IP y sarlab_port fijados en la p&aacute;gina de configuraci&oacute;n de ejsapp";

$string['sarlab_collab'] = "Usar acceso colaborativo de Sarlab?";
$string['sarlab_collab_help'] = "Si deseas que Sarlab ofrezca la opci&oacute;n de acceso colaborativo a este laboratorio remoto o no";

$string['practiceintro'] = 'Identificador(es) de pr&aacute;ctica en Sarlab';
$string['practiceintro_help'] = 'El identificador de la(s) pr&aacute;ctica(s), tal y como est&aacute; configurado en Sarlab, que desea usar con este sistema experimental.';
$string['practiceintro_required'] = 'ATENCI&Oacute;N: Debe especificar al menos una pr&aacute;ctica.';

$string['ip_lab'] = 'direcci&oacute;n IP';
$string['ip_lab_help'] = 'Direcci&oacute;n IP del sistema experimental.  Si est&aacute; usando Sarlab, no tiene que preocuparse de este par&aacute;metro.';
$string['ip_lab_required'] = 'ATENCI&Oacute;N: Debe proporcionar una direcci&oacute;n IP valida.';

$string['port'] = 'Puerto';
$string['port_help'] = 'El puerto a usar para establecer la comunicaci&oacute;n. Si est&aacute; usando Sarlab, no tiene que preocuparse de este par&aacute;metro.';
$string['port_required'] = 'ATENCI&Oacute;N: Debe proporcionar un puerto v&aacute;lido.';

$string['active'] = 'Disponible';
$string['active_help'] = 'Si este laboratorio remoto se encuentra operativo en este momento o no.';

$string['free_access'] = 'Acceso libre';
$string['free_access_help'] = 'Habilitar el acceso libre (sin necesidad de realizar reservas) a este laboratorio remoto.';

$string['totalslots'] = 'Horas de trabajo totales';
$string['totalslots_help'] = 'Cantidad total de horas m&aacute;ximas que se le permitir&aacute; usar a cada alumno para trabajar con este laboratorio.';
$string['weeklyslots'] = 'Horas de trabajo semanales';
$string['weeklyslots_help'] = 'Cantidad semanal de horas m&aacute;ximas que se le permitir&aacute; usar a cada alumno para trabajar con este laboratorio.';
$string['dailyslots'] = 'Horas de trabajo diarias';
$string['dailyslots_help'] = 'Cantidad diaria de horas m&aacute;ximas que se le permitir&aacute; usar a cada alumno para trabajar con este laboratorio.';

$string['file_error'] = "No pudo abrirse el fichero en el servidor";
$string['manifest_error'] = " > No se ha podido encontrar o abrir el manifiesto .mf. Revise el fichero que ha cargado.";
$string['EJS_version'] = "ATENCI&Oacute;N: El applet no fu&eacute; generado con EJS 4.37 (build 121201), o superior. Recomp&iacute;lalo con una versi&oacute;n m&aacute;s moderna de EJS.";

$string['inactive_lab'] = 'El laboratorio remoto es&aacute; inactivo en este momento.';
$string['no_booking'] = 'No tiene reserva para este laboratorio en este horario.';
$string['collab_access'] = 'Sin embargo, puede trabajar en modo colaborativo si ha sido invitado por un usuario con una reserva activa.';
$string['check_bookings'] = 'Consulte sus reservas activas con el sistema de reservas.';
$string['lab_in_use'] = 'El laboratorio est&aacute; ocupado en este instante. Pruebe de nuevo más adelante.';

$string['ejsapp_error'] = 'La actividad EJSApp a la que est&aacute; tratando de acceder no existe.';

//Capabilities
$string['ejsapp:accessremotelabs'] = "Acceso a todos los laboratorios remotos";
$string['ejsapp:addinstance'] = "Añadir una nueva actividad EJSApp";
$string['ejsapp:view'] = "Ver una actividad EJSApp";
$string['ejsapp:requestinformation'] = "Pedir informaci&oacute;n para plugins de terceros";

//Events
$string['event_working'] = "Working with the EJSApp activity";
$string['event_wait'] = "Waiting for the lab to be free";
$string['event_book'] = "Need to make a booking";
$string['event_collab'] = "Working with the EJSApp activity in collaborative mode";
$string['event_inactive'] = "Lab is inactive";

//Settings
$string['default_certificate_set'] = "Opciones del certificado de confianza. (Importante s&oacute;lo si se desea firmar de manera autom&aacute;tica los applets subidos con EJSApp)";
$string['certificate_path'] = "Ruta al fichero del certificado de confianza";
$string['certificate_path_description'] = "La ruta en el servidor Moodle al fichero del certificado de confianza que se usar&aacute; para firmar los applets de Java";
$string['certificate_password'] = "Contraseña del certificado de confianza";
$string['certificate_password_description'] = "La contraseña requerida para usar el certificado de confianza";
$string['certificate_alias'] = "Alias del certificado de confianza";
$string['certificate_alias_description'] = "El alias asignado al certificado de confianza";
$string['default_communication_set'] = "Opciones de comunicaci&oacute;n. (Importante s&oacute;lo si tambi&eacute;n usa Sarlab";
$string['sarlab_IP'] = "Nombre y direcci&aacute;n IP del servidor Sarlab";
$string['sarlab_IP_description'] = "Si usa Sarlab (un sistema que gestiona las conexiones a recursos de laboratorios remotos), debe proporcionar la direcci&oacute;n IP del servidor que ejecuta el sistema Sarlab que desea utilizar. En caso contrario, esta variable no se usa, de modo que puede dejar el valor por defecto. Si tiene m&aacute;s de un servidor Sarlab (por ejemplo, uno en 127.0.0.1 y otro en 127.0.0.2), inserte las direcciones IP separadas por puntos y comas: 127.0.0.1;127.0.0.2. Adem&aacute;s, puede proporcionar un nombre para identificar cada servidor Sarlab: 'Sarlab Madrid'127.0.0.1;'Sarlab Huelva'127.0.0.2";
$string['sarlab_port'] = "Puerto(s) de comunicaci&oacute; con Sarlab";
$string['sarlab_port_description'] = "Si usa Sarlab (un sistema que gestiona las conexiones a recursos de laboratorios remotos), debe proporcionar un puerto v&aacute;lido para establecer las comunicaciones necesarias con el servidor de Sarlab. En caso contrario, esta variable no se usa, de modo que puede dejar el valor por defecto. Si tiene m&aacute;s de un servidor Sarlab (por ejemplo, uno usando el puerto 443 y un segundo usando tambi&eacute;n el puerto 443), inserte los valores separados por puntos y comas: 443;443";
$string['sarlab_enc_key'] = "Clave de encriptaci&oacute;n para comunicarse con Sarlab";
$string['sarlab_enc_key_description'] = "Si usa Sarlab (un sistema que gestiona las conexiones a recursos de laboratorios remotos), debe proporcionar la clave de 16 caracteres para encriptar/desencriptar las comunicaciones con el servidor Sarlab (esta clave debe ser la misma que la configurada en el servidor Sarlab). En caso contrario, esta variable no se usa, de modo que puede dejar el valor por defecto.";