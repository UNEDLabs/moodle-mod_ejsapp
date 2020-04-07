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
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
//
// EJSApp has been developed by:
// - Luis de la Torre: ldelatorre@dia.uned.es
// - Ruben Heradio: rheradio@issi.uned.es
//
// at the Computer Science and Automatic Control, Spanish Open University
// (UNED), Madrid, Spain.

/**
 * Spanish strings for ejsapp
 *
 * @package    mod_ejsapp
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'EJSApp';
$string['modulenameplural'] = 'EJSApps';
$string['modulename_help'] = 'El m&oacute;dulo de actividad EJSApp permite a un profesor a&ntilde;adir aplicaciones Javascript y applets de Java creados con Easy Java/Javascript Simulations (EjsS) en sus cursos de Moodle.

Las aplicaciones Javascript de EjsS quedar&aacute;n embebidos dentro de los cursos de Moodle y los applets Java se ejecutar&aacute; como aplicaciones de escritorio, lanzadas mediante el protocolo JNLP. Si la aplicaci&oacute;n fue compilada con la opci&oacute;n "A&ntilde;adir soporte idiomas" en EjsS, el laboratorio embebido en Moodle con la actividad EJSApp configurar&aacute; autom&aacute;ticamente su idioma a aquel seleccionado por el usuario de Moodle, si esto es posible.

Cuando se usa junto al Navegador EJSApp de Ficheros, y si la aplicaci&oacute; est&ha sido preparada en EjsS para tal efecto, los estudiantes pueden guardar el estado de la aplicaci$oacute, as&iacute; como ficheros de texto o im&aacute;genes y guardarlos en el bloque Navegador EJSApp de Ficheros. La informaci&oacute;n de estos estados se graba en un fichero .json que es guardado en el area de ficheros privados (Navegador EJSApp de Ficheros). Estos estados pueden recuperarse pulsando sobre los ficheros .xml o .json en el Navegador EJSApp de Ficheros.

Cuando se usa junto al bloque EJSApp de Sesiones Colaborativas, los usuarios de Moodle pueden trabajar con el mismo laboratorio EjsS de una manera s&iacute;ncrona, es decir, de tal forma que la aplicaci&oacute; mostrar&aacute; el mismo estado para todos los usuarios en la sesi&oacute;n colaborativa. Gracias a este bloque, los usuarios pueden crear sesiones, invitar a otros usuarios y trabajar juntos con la misma actividad EJSApp.

Cuando se usa junto al bloque Remlab Manager, las actividades EJSApp que sean laboratorios remotos pueden beneficiarse de numerosas opciones de gesti$oacute que aporta dicho bloque.

Cuando se usa junto a la actividad EJSApp Booking System, las sesiones de experimentación con las actividades EJSApp que sean laboratorios remotos se gestionar&aacute; y organizar&aacuten desde dicho m&oacute;dulo.';
$string['ejsappname'] = 'Nombre del laboratorio';
$string['ejsappname_help'] = 'Nombre con que figurar&aacute; el laboratorio en el curso';
$string['ejsapp'] = 'EJSApp';
$string['pluginadministration'] = 'Administraci&oacute;n del EJSApp';
$string['pluginname'] = 'EJSApp';
$string['noejsapps'] = 'No hay actividades EJSApp en este curso';

$string['state_load_msg'] = 'Se va a actualizar el estado del laboratorio';
$string['state_fail_msg'] = 'Error al intentar cargar el estado';

$string['controller_load_msg'] = 'Se va a cargar un controlador para el laboratorio';
$string['controller_fail_msg'] = 'Error al intentar cargar el controlador';

$string['recording_load_msg'] = 'Se va a ejecutar una grabaci&oacute;n para este laboratorio';
$string['recording_fail_msg'] = 'Error al intentar ejecutar la grabaci&oacute;n';

$string['more_text'] = 'Texto optional tras el laboratorio EjsS';

$string['jar_file'] = 'Archivo .jar o .zip que encapsula el laboratorio EjsS';

$string['appletfile'] = 'Easy Java(script) Simulation';
$string['appletfile_required'] = 'Se debe seleccionar un archivo .jar o .zip';
$string['appletfile_help'] = 'Selecione el archivo .jar o .zip que encapsula el laboratorio EjsS (Easy Java/Javascript Simulations). La p&aacute;gina oficial de EjsS es http://fem.um.es/Ejs/';

$string['appwording'] = 'Enunciado';

$string['css_style'] = 'Hoja de estilos CSS para una aplicaci&oacute;n Javascript';

$string['css_rules'] = 'Crea tus propias reglas css para cambiar el aspecto visual de la aplicaci&oacute;n javascript';
$string['css_rules_help'] = '¡Importante! Escriba cada selector y el comienzo de su declaraci&oacute;n (la llave) en la misma l&iacute;nnea.';

$string['state_file'] = 'Archivo .json con el estado que este laboratorio EjsS debe leer';

$string['statefile'] = 'Estado del Easy Java(script) Simulation';
$string['statefile_help'] = 'Seleccione el archivo .json con el estado que la aplicaci&oacute;n Javascript EjsS debe cargar al ejecutarse.';

$string['recording_file'] = 'Archivo .rec con la grabaci&oacute;n que la aplicaci&oacute;n EjsS debe ejecutar al cargarse';

$string['recordingfile'] = 'Grabaci&oacute;n del Easy Java(script) Simulation';
$string['recordingfile_help'] = 'Seleccione el archivo .rec con la grabación de la interacción que la aplicaci&oacute;n EjsS debe ejecutar al cargarse.';

$string['personalize_vars'] = 'Personalizar variables del laboratorio EjsS';

$string['use_personalized_vars'] = 'Personalizar variables para cada usuario?';
$string['use_personalized_vars_help'] = 'Seleccione "ss&iacute;" si conoce el nombre de alguna de las variables en el modelo EjsS y deseas que adquieran valores diferentes para cada usuario que acceda a esta aplicaci&oacute;n.';

$string['var_name'] = 'Nombre {no}';
$string['var_name_help'] = 'Nombre de la variable en el modelo EjsS.';

$string['var_type'] = 'Tipo {no}';
$string['var_type_help'] = 'Tipo de la variable en el modelo EjsS.';

$string['min_value'] = 'Valor m&iacute;nimo {no}';
$string['min_value_help'] = 'M&iacute;nimo valor permitido para la variable.';

$string['max_value'] = 'M&aacute;ximo valor {no}';
$string['max_value_help'] = 'M&aacute;ximo valor permitido para la variable.';

$string['vars_required'] = 'ATENCI&Oacute;N: Si desea utilizar variables personalizadas, debe espeficificar al menos una.';
$string['vars_incorrect_type'] = 'ATENCI&Oacute;N: El tipo y los valores especificados para esta variable no se corresponden entre s&iacute;.';

$string['programming_config'] = 'Configurar uso de Blockly y editor de c&oacute;digo ACE';

$string['use_blockly'] = 'Habilitar Blockly/ACE';
$string['use_blockly_help'] = 'Cuando se usa esta opci&oacute;n, la actividad EJSApp mostrar&aacute; un espacio para programar en Blockly. Los programas creados en Blockly podr&aacute;n interactuar con el laboratorio virtual o remoto. Tambi&eacute;n puede usarse un editor de c&oacute;digo para sobrescribir funciones del laboratorio.';
$string['charts_blockly'] = 'Habilitar gr&aacute;ficas';
$string['events_blockly'] = 'Habilitar eventos';
$string['functions'] = 'Habilitar reescribir funciones';
$string['func_language'] = 'Lenguaje de programaci&oacute;n';
$string['func_language_help'] = 'El lenguaje de programaci&oacute;n a usar para sobrescribir las funciones. Para cualquier elecc&oacute;n distinta de Blockly, aparece el editor de c&oacute;digo ACE.';
$string['func_name'] = 'Funci&oacute;n para ser reescrita';
$string['func_name_help'] = 'Nombre de la variable que almacena la funci&oacute;n que quieres permitir que sea sobrescrita.';
$string['remote_function'] = 'La funci&oacute;n se ejecuta en el servidor';
$string['blocklyfile'] = 'Programa blockly inicial';
$string['blocklyfile_help'] = 'Puedes seleccionar un fichero .blk que especifique que programa blockly debe cargarse inicialmente.';
$string['experiment_blockly'] = "Experimentos";
$string['data_blockly'] = "Datos y Gráficas";
$string['event_blockly'] = "Eventos";
$string['functions'] = "Funciones";
$string['experimentDropdown_blockly'] = " Nuevo código de experimento";
$string['chartDropdown_blockly'] = " Nuevo código de gráfica";
$string['eventDropdown_blockly'] = " Nuevo código de evento";
$string['functionDropdown'] = " Reescribir función";
$string['run_blockly'] = " Ejecutar";
$string['log_blockly'] = " Registro";
$string['error_blockly'] = " Errores:";
$string['previousExecutions_blockly'] = " Ejecuciones previas:";

$string['rem_lab_conf'] = 'Configuraci&oacute;n del laboratorio remoto';

$string['is_rem_lab'] = 'Sistema experimental remoto?';
$string['is_rem_lab_help'] = 'Si este EJSApp conecta a recursos reales de manera remota Y quieres que el Sistema de Reservas EJSApp controle su acceso, selecciona "s&iacute;". En caso contrario, selecciona "no". NOTA: Necesita el bloque Remlab Manager para que esta opci&oacute;n est&eacute; disponible.';

$string['practiceintro'] = 'Identificador de pr&aacute;ctica';
$string['practiceintro_help'] = 'El identificador de la pr&aacute;ctica, que desea usar con este sistema experimental.';
$string['practiceintro_required'] = 'ATENCI&Oacute;N: Si desea configurar esta actividad como un laboratorio remoto, necesita especificar un identificador de pr&aacute; que est&eacute; previamente definido en el bloque Remlab Manager.';

$string['record_interactions'] = 'Registrar las acciones de los usuarios';
$string['record_interactions_help'] = 'Cuando esta opci&oacute;n se marca como \'s&iacute;\', Moodle almacenar&aacute; las interacciones de los usuarios con el laboratorio EjsS: pulsaciones en botones, cambios de par&aacute;metros, etc. Activar si se desea aplicar t&eacute;cnicas de learning analytics.';
$string['record_mouse_events'] = 'Registrar los eventos del rat&oacute;n';
$string['record_mouse_events_help'] = 'Registrar los eventos de movimiento del rat&oacute;n generar&aacute; conjuntos de datos m&aacute;s grandes. Puede que desee dejar esta opci&oacute;n como \'no\' si no cree que esta informaci&oacute;n resulte &uacute;til para realizar learning analytics sobre este laboratorio.';

$string['file_error'] = "No pudo abrirse el fichero en el servidor";
$string['manifest_error'] = " > No se ha podido encontrar o abrir el manifiesto .mf. Revise el fichero que ha cargado.";
$string['EJS_version'] = "ATENCI&Oacute;N: El applet no fu&eacute; generado con EjsS 4.37 (build 121201), o superior. Recomp&iacute;lelo con una versi&oacute;n m&aacute;s moderna de EjsS.";
$string['EJS_codebase'] = "ATENCI&Oacute;N: El manifest del applet que ha subido no especifica este servidor Moodle en el par&aacute;metro 'codebase', de modo que no ha sido firmado.";

$string['inactive_lab'] = 'El laboratorio remoto est&aacute; inactivo en este momento.';
$string['no_booking'] = 'No tiene reserva para este laboratorio en este horario.';
$string['collab_access'] = 'Esta es una sesi&oacute;n colaborativa.';
$string['check_bookings'] = 'Consulte sus reservas activas con el sistema de reservas.';
$string['lab_in_use'] = 'El laboratorio est&aacute; ocupado o reiniciandose en este instante. Pruebe de nuevo m&aacute;s adelante.';
$string['booked_lab'] = 'Este laboratorio ha sido reservado para esta hora en un curso distinto. Pruebe de nuevo m&aacute;s adelante.';
$string['forbid_lti'] = 'No tienes permisos para acceder a este laboratorio.';

$string['ejsapp_error'] = 'La actividad EJSApp a la que est&aacute; tratando de acceder no existe.';

$string['personal_vars_button'] = 'Ver variables personalizadas';
$string['rewrite_functions_button'] = 'Seleccionar funciones de laboratorio a sobreescribir';

// Strings in lib.php.
$string['deletedlogs'] = 'Borrar todas las entradas del log';
$string['deletedlegacylogs'] = 'Borrar todas las entradas del log antiguo';
$string['deletedrecords'] = 'Borrar todas acciones grabadas de usuario en actividades ejsapp';
$string['deletedpersonalvars'] = 'Borrar todas las variables personalizadas';
$string['deletedgrades'] = 'Borrar todas las calificaciones de actividades ejsapp';

// Strings in personalized_vars_values.php.
$string['rewriteFuncs_pageTitle'] = 'Habilitar la reescritura de funciones del laboratorio';

// Strings in personalized_vars_values.php.
$string['personalVars_pageTitle'] = 'Valores de las variables personalizadas';
$string['users_ejsapp_selection'] = 'Seleccione los usuarios y la actividad EJSApp';
$string['ejsapp_activity_selection'] = 'Selecci&oacute;n de la actividad EJSApp';
$string['variable_name'] = 'Variable';
$string['variable_value'] = 'Valor';
$string['export_all_data'] = 'Exportar datos para todas las actividades EJSApp en este curso';
$string['export_this_data'] = 'Exportar datos para esta actividad EJSApp';
$string['no_ejsapps'] = 'La actividad EJSApp seleccionada no tiene variables personalizadas';
$string['personalized_values'] = 'valores_personalizdos_';

// Strings in leave_or_kick_out.php.
$string['time_is_up'] = 'Se ha agotado su tiempo con el laboratorio remoto. Si desea seguir trabajando con &eacute;l, haga una nueva reserva y/o refresque esta p&aacute;gina.';

// Strings in countdown.php.
$string['seconds'] = 'segundos restantes.';
$string['refresh'] = 'Pruebe a refrescar su ventana ahora.';

// Strings in generate_embedding_code.php.
$string['end_message'] = 'Fin de la reproducci&oacute;n';

// Blockly XML configuration file.
$string['xml_logic'] = 'Lógica';
$string['xml_loops'] = 'Bucles';
$string['xml_maths'] = 'Matemáticas';
$string['xml_text'] = 'Texto';
$string['xml_lists'] = 'Arrays';
$string['xml_variables'] = 'Variables';
$string['xml_functions'] = 'Funciones';
$string['xml_lab'] = 'Laboratorio';
$string['xml_lab_variables'] = 'Variables';
$string['xml_lab_execution'] = 'Ejecución';
$string['xml_lab_functions'] = 'Funciones';
$string['xml_lab_control'] = 'Control';
$string['xml_lab_var_boolean'] = 'Booleanas';
$string['xml_lab_var_string'] = 'Textos';
$string['xml_lab_var_number'] = 'Numéricas';
$string['xml_lab_var_others'] = 'Otras';
$string['blocklyfile'] = 'Bloques iniciales';
$string['blocklyfile_help'] = 'Puede seleccionar un fichero .blk que especifique los bloques que deben mostrarse inicialmente en Blockly.';
$string['xml_lab_charts'] = 'Gráficas';

// Capabilities.
$string['ejsapp:accessremotelabs'] = "Acceso a todos los laboratorios remotos";
$string['ejsapp:addinstance'] = "Añadir una nueva actividad EJSApp";
$string['ejsapp:view'] = "Ver una actividad EJSApp";
$string['ejsapp:requestinformation'] = "Pedir informaci&oacute;n para plugins de terceros";

// Events.
$string['event_viewed'] = "EJSApp activity viewed";
$string['event_working'] = "Working with the EJSApp activity";
$string['event_wait'] = "Waiting for the lab to be free";
$string['event_book'] = "Need to make a booking";
$string['event_collab'] = "Working with the EJSApp activity in collaborative mode";
$string['event_inactive'] = "Lab is inactive";
$string['event_booked'] = "Lab is booked in a different course";
$string['event_left'] = "Left the EJSApp activity";

// Settings.
$string['default_general_set'] = "Opciones generales";
$string['check_activity'] = "Comprobar actividad";
$string['check_activity_description'] = "Con que frecuencia se comprueba la actividad de los usuarios en EJSApp (s)";
$string['server_id'] = "ID del sitio en ENLARGE IRS";
$string['server_id_description'] = "ID usado para registrar este sitio Moodle en ENLARGE IRS (https://irs.nebsyst.com). Dejar en blanco si el sitio no ha sido registrado";
$string['default_certificate_set'] = "Opciones del certificado de confianza. (Importante s&oacute;lo si se desea firmar de manera autom&aacute;tica los applets subidos con EJSApp)";
$string['certificate_path'] = "Ruta al fichero del certificado de confianza";
$string['certificate_path_description'] = "La ruta en el servidor Moodle al fichero del certificado de confianza que se usar&aacute; para firmar los applets de Java";
$string['certificate_password'] = "Contraseña del certificado de confianza";
$string['certificate_password_description'] = "La contraseña requerida para usar el certificado de confianza";
$string['certificate_alias'] = "Alias del certificado de confianza";
$string['certificate_alias_description'] = "El alias asignado al certificado de confianza";

// Privacy
$string['privacy:metadata:ejsapp_records'] = 'Contiene las interacciones de los usuarios (eventos del raton) realizadas en las aplicaciones EjsS.';
$string['privacy:metadata:ejsapp_records:time'] = 'El tiempo en el que tuvo lugar la accion.';
$string['privacy:metadata:ejsapp_records:userid'] = 'El ID del usuario que realiza la accion.';
$string['privacy:metadata:ejsapp_records:ejsappid'] = 'El ID de la actividad EJSApp sobre la que se realiza la accion.';
$string['privacy:metadata:ejsapp_records:sessionsid'] = 'El ID de la sesion.';
$string['privacy:metadata:ejsapp_records:actions'] = 'Una descripcion de las acciones que se realizaron.';