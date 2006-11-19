<?php
// Version: 2.0 Alpha; index

/* Important note about language files in SMF 2.0 upwards:
	1) All language entries in SMF 2.0 are cached. All edits should therefore be made through the admin menu. If you do
	   edit a language file manually you will not see the changes in SMF until the cache refreshes. To manually refresh
	   the cache go to Admin => Maintenance => Clean Cache.

	2) Please also follow the following rules:

		a) All strings should use single quotes, not double quotes for enclosing the string.
		b) As a result of (a) all newline characters (etc) need to be escaped. i.e. "\\n" is now \'\\\\\\\\n\'.

*/

global $forum_copyright, $forum_version, $webmaster_email;

// Locale (strftime, pspell_new) and spelling. (pspell_new, can be left as '' normally.)
// For more information see:
//   - http://www.php.net/function.pspell-new
//   - http://www.php.net/function.setlocale
// Again, SPELLING SHOULD BE '' 99% OF THE TIME!!  Please read this!
$txt['lang_locale'] = 'spanish';
$txt['lang_dictionary'] = 'es';
$txt['lang_spelling'] = '';

// Character set and right to left?
$txt['lang_character_set'] = 'ISO-8859-1';
$txt['lang_rtl'] = false;

$txt['days'] = array('Domingo', 'Lunes', 'Martes', 'Mi&eacute;rcoles', 'Jueves', 'Viernes', 'S&aacute;bado');
$txt['days_short'] = array('Dom', 'Lun', 'Mar', 'Mi&eacute;', 'Jue', 'Vie', 'S&aacute;b');
// Months must start with 1 => 'January'. (or translated, of course.)
$txt['months'] = array(1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
$txt['months_titles'] = array(1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
$txt['months_short'] = array(1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');

$txt['newmessages0'] = 'es nuevo';
$txt['newmessages1'] = 'son nuevos';
$txt['newmessages3'] = 'Nuevo(s)';
$txt['newmessages4'] = ',';

$txt['admin'] = 'Administraci&oacute;n';
// Untranslated!
$txt['moderate'] = 'Moderate';

$txt['save'] = 'Guardar';

$txt['modify'] = 'Modificar';
$txt['forum_index'] = '%1$s - &Iacute;ndice';
$txt['members'] = 'Usuarios';
$txt['board_name'] = 'Nombre del foro';
$txt['posts'] = 'Mensajes';

$txt['no_subject'] = '(Sin asunto)';
$txt['view_profile'] = 'Ver Perfil';
$txt['guest_title'] = 'Visitante';
$txt['author'] = 'Autor';
$txt['on'] = 'en';
$txt['remove'] = 'Eliminar';
$txt['start_new_topic'] = 'Crear nuevo tema';

$txt['login'] = 'Ingresar';
// Use numeric entities in the below string.
$txt['username'] = 'Usuario';
$txt['password'] = 'Contrase&ntilde;a';

$txt['username_no_exist'] = 'Nombre de usuario no existente.';

$txt['board_moderator'] = 'Moderador del Foro';
$txt['remove_topic'] = 'Eliminar Tema';
$txt['topics'] = 'Temas';
$txt['modify_msg'] = 'Modificar mensaje';
$txt['name'] = 'Nombre';
$txt['email'] = 'Email';
$txt['subject'] = 'Asunto';
$txt['message'] = 'Mensaje';

$txt['profile'] = 'Perfil';

$txt['choose_pass'] = 'Escoge contrase&ntilde;a';
$txt['verify_pass'] = 'Verifica contrase&ntilde;a';
$txt['position'] = 'Grupo';

$txt['profile_of'] = 'Ver perfil de';
$txt['total'] = 'Total';
$txt['posts_made'] = 'Mensajes';
$txt['website'] = 'Web';
$txt['register'] = 'Registrarse';

$txt['message_index'] = '&Iacute;ndice de Mensajes';
$txt['news'] = 'Noticias';
$txt['home'] = 'Inicio';

$txt['lock_unlock'] = 'Bloquear/Desbloquear Tema';
$txt['post'] = 'Publicar';
$txt['error_occured'] = '&iexcl;Un error ha ocurrido!';
$txt['at'] = 'a las';
$txt['logout'] = 'Salir';
$txt['started_by'] = 'Iniciado por';
$txt['replies'] = 'Respuestas';
$txt['last_post'] = '&Uacute;ltimo mensaje';
$txt['admin_login'] = 'Ingresar a Administraci&oacute;n';
// Use numeric entities in the below string.
$txt['topic'] = 'Tema';
$txt['help'] = 'Ayuda';
$txt['remove_message'] = 'Eliminar mensaje';
$txt['notify'] = 'Notificar';
$txt['notify_request'] = '&#191;Deseas una notificaci&oacute;n por email si alguien responde a este tema?';
// Use numeric entities in the below string.
$txt['regards_team'] = 'Saludos,\\nEl equipo %1$s.';
$txt['notify_replies'] = 'Notificar respuestas';
$txt['move_topic'] = 'Mover tema';
$txt['move_to'] = 'Mover a';
$txt['pages'] = 'P&aacute;ginas';
$txt['users_active'] = 'Usuarios activos en los &uacute;ltimos %1$d minutos';
$txt['personal_messages'] = 'Mensajes Personales';
$txt['reply_quote'] = 'Responder con cita';
$txt['reply'] = 'Respuesta';
// Untranslated!
$txt['approve'] = 'Approve';
$txt['approve_all'] = 'approve all';
$txt['attach_awaiting_approve'] = 'Attachments awaiting approval';

$txt['msg_alert_none'] = 'No tienes mensajes...';
$txt['msg_alert_you_have'] = 'tienes';
$txt['msg_alert_messages'] = 'mensajes';
$txt['remove_message'] = 'Borrar este mensaje';

$txt['online_users'] = 'Usuarios en L&iacute;nea';
$txt['personal_message'] = 'Mensaje Personal';
$txt['jump_to'] = 'Ir a';
$txt['go'] = 'ir';
$txt['are_sure_remove_topic'] = '&#191;Est&aacute;s seguro de borrar este tema?';
$txt['yes'] = 'S&iacute;';
$txt['no'] = 'No';

$txt['search_results'] = 'Resultados de la b&uacute;squeda';
$txt['search_end_results'] = 'Fin de resultados';
$txt['search_no_results'] = 'Lo siento, no se encontraron mensajes';
$txt['search_on'] = 'en';

$txt['search'] = 'Buscar';
$txt['all'] = 'Todos';

$txt['back'] = 'Atr&aacute;s';
$txt['password_reminder'] = 'Contrase&ntilde;a recordatorio';
$txt['topic_started'] = 'Mensaje iniciado por';
$txt['title'] = 'T&iacute;tulo';
$txt['post_by'] = 'Publicado por';
$txt['memberlist_searchable'] = 'Lista (con opci&oacute;n de b&uacute;squeda) de todos los usuarios registrados.';
$txt['welcome_member'] = 'Por favor, da la bienvenida a';
$txt['admin_center'] = 'Centro de Administraci&oacute;n SMF';
$txt['last_edit'] = '&Uacute;ltima modificaci&oacute;n';
$txt['notify_deactivate'] = '&iquest;Deseas desactivar la notificaci&oacute;n en este tema?';

$txt['recent_posts'] = 'Mensajes recientes';

$txt['location'] = 'Ubicaci&oacute;n';
$txt['gender'] = 'Sexo';
$txt['date_registered'] = 'Fecha de registro';

$txt['recent_view'] = 'Ver los mensajes m&aacute;s recientes del foro.';
$txt['recent_updated'] = 'es el tema actualizado m&aacute;s recientemente';

$txt['male'] = 'Masculino';
$txt['female'] = 'Femenino';

$txt['error_invalid_characters_username'] = 'Car&aacute;cter inv&aacute;lido en el nombre de usuario.';

$txt['welcome_guest'] = 'Bienvenido(a), <b>%1$s</b>. Favor de <a href="' . $scripturl . '?action=login">ingresar</a> o <a href="' . $scripturl . '?action=register">registrarse</a>.';
$txt['welcome_guest_activate'] = '<br />&iquest;Perdiste tu  <a href="' . $scripturl . '?action=activate">email de activaci&oacute;n?</a>';
$txt['hello_member'] = 'Hola,';
// Use numeric entities in the below string.
$txt['hello_guest'] = 'Bienvenido(a),';
$txt['welmsg_hey'] = 'Hola,';
$txt['welmsg_welcome'] = 'Bienvenido(a),';
$txt['welmsg_please'] = 'Por favor';
$txt['select_destination'] = 'Por favor selecciona un destino';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['posted_by'] = 'Publicado por';

$txt['icon_smiley'] = 'Sonrisa';
$txt['icon_angry'] = 'Enojado';
$txt['icon_cheesy'] = 'Cheesy';
$txt['icon_laugh'] = 'Risa';
$txt['icon_sad'] = 'Triste';
$txt['icon_wink'] = 'Gi&ntilde;ar';
$txt['icon_grin'] = 'Sonreir';
$txt['icon_shocked'] = 'Impresionado';
$txt['icon_cool'] = 'Cool';
$txt['icon_huh'] = 'Huh';
$txt['icon_rolleyes'] = 'Girar ojos';
$txt['icon_tongue'] = 'Lengua';
$txt['icon_embarrassed'] = 'Avergonzado';
$txt['icon_lips'] = 'Labios sellados';
$txt['icon_undecided'] = 'Indeciso';
$txt['icon_kiss'] = 'Beso';
$txt['icon_cry'] = 'Llorar';

$txt['moderator'] = 'Moderador';
$txt['moderators'] = 'Moderadores';

$txt['mark_board_read'] = 'Marcar Temas como le&iacute;dos para este foro';
$txt['views'] = 'Vistas';
$txt['new'] = 'Nuevo';

$txt['view_all_members'] = 'Ver todos los usuarios';
$txt['view'] = 'Ver';
$txt['email'] = 'Email';

// Untranslated!
$txt['viewing_members'] = 'Viewing Members %1$s to %2$s';
$txt['of_total_members'] = 'of %1$s total members';

$txt['forgot_your_password'] = '&iquest;Olvidaste tu contrase&ntilde;a?';

$txt[317] = 'Fecha';
// Use numeric entities in the below string.
$txt[318] = 'De';
$txt[319] = 'Asunto';
$txt[322] = 'Recibir Nuevos Mensajes';
$txt[324] = 'Para';

$txt[330] = 'Temas';
$txt[331] = 'Usuarios';
$txt[332] = 'Lista de usuarios';
$txt[333] = 'Nuevos Mensajes';
$txt[334] = 'No hay nuevos Mensajes';

$txt['sendtopic_send'] = 'Enviar';

$txt[371] = 'Diferencia Horaria';
$txt[377] = 'o';

$txt[398] = 'Lo siento, no se encontraron mensajes';

$txt[418] = 'Notificaci&oacute;n';

$txt[430] = 'Lo siento %s, tienes el acceso denegado a este foro!';
// !!! Untranslated
$txt['your_ban_expires'] = 'Your ban is set to expire %s';
$txt['your_ban_expires_never'] = 'Your ban is not set to expire.';

$txt[452] = 'Marcar TODOS los mensajes como le&iacute;dos';

$txt['hot_topics'] = 'Tema candente (M&aacute;s de %1$d respuestas)';
$txt['very_hot_topics'] = 'Tema muy candente (M&aacute;s de %1$d respuestas)';
$txt[456] = 'Tema bloqueado';
$txt[457] = 'Tema normal';
$txt['participation_caption'] = 'Temas en los que has publicado';

$txt[462] = 'IR';

$txt[465] = 'Imprimir';
$txt[467] = 'Perfil';
$txt[468] = 'Sumario de Temas';
$txt[470] = 'N/A';
$txt[471] = 'mensaje';
$txt[473] = 'Este nombre est&aacute; en uso por otro usuario.';

$txt[488] = 'Total de Usuarios';
$txt[489] = 'Total de Mensajes';
$txt[490] = 'Total de Temas';

$txt[497] = 'Duraci&oacute;n de la sesi&oacute;n en minutos';

$txt[507] = 'Previsualizar';
$txt[508] = 'Recordar siempre Usuario/Contrase&ntilde;a';

$txt[511] = 'En l&iacute;nea';
// Use numeric entities in the below string.
$txt[512] = 'IP';

$txt[513] = 'ICQ';
$txt[515] = 'WWW';

$txt[525] = 'por';

$txt[578] = 'horas';
$txt[579] = 'd&iacute;as';

$txt[581] = ', nuestro usuario m&aacute;s reciente.';

$txt[582] = 'Buscar por';

$txt[603] = 'AIM';
// In this string, please use +'s for spaces.
$txt['aim_default_message'] = '&iquest;Est&aacute;s.+ahi?';
$txt[604] = 'YIM';

$txt[616] = 'Recuerda, este foro est&aacute; en \'Modo de Mantenimiento\'.';

$txt[641] = 'Le&iacute;do';
$txt[642] = 'veces';

$txt[645] = 'Estad&iacute;sticas SMF';
$txt[656] = '&Uacute;ltimo usuario';
$txt[658] = 'Total de Categor&iacute;as';
$txt[659] = '&Uacute;ltimo mensaje';

$txt[660] = 'Tienes';
$txt[661] = 'Haz click';
$txt[662] = 'aqu&iacute;';
$txt[663] = 'para verlos.';

$txt[665] = 'Total de Foros';

$txt[668] = 'Imprimir P&aacute;gina';

$txt[679] = 'Debe ser una direcci&oacute;n v&aacute;lida de email.';

$txt[683] = 'un mont&oacute;n';
$txt['info_center_title'] = '%s - Centro de Informaci&oacute;n';

$txt[707] = 'Enviar tema';

$txt['sendtopic_title'] = 'Enviar tema &#171; %s &#187; a un amigo.';
// Use numeric entities in the below three strings.
$txt['sendtopic_dear'] = 'Estimado %s,';
$txt['sendtopic_this_topic'] = 'Quiero que revises el siguiente tema: %s, en %s. Para verlo, haz click en la siguiente liga';
$txt['sendtopic_thanks'] = 'Gracias';
$txt['sendtopic_sender_name'] = 'Tu nombre';
$txt['sendtopic_sender_email'] = 'Tu direcci&oacute;n de email';
$txt['sendtopic_receiver_name'] = 'Nombre del destinatario';
$txt['sendtopic_receiver_email'] = 'Direcci&oacute;n email del destinatario';
$txt['sendtopic_comment'] = 'Agregar un comentario';
// Use numeric entities in the below string.
$txt['sendtopic2'] = 'Un comentario acerca de este tema ha sido agregado';

$txt[721] = 'Esconder email del p&uacute;blico';

$txt[737] = 'Seleccionar todo';

// Use numeric entities in the below string.
$txt[1001] = 'Error en la Base de Datos';
$txt[1002] = 'Por favor intenta nuevamente.  Si esta pantalla aparece nuevamente, notifica del error a un administrador.';
$txt[1003] = 'Archivo';
$txt[1004] = 'L&iacute;nea';
// Use numeric entities in the below string.
$txt[1005] = 'SMF ha detectado errores en la base de datos, y los ha tratado de corregir autom&#225;ticamente.  Si los problemas persisten, o sigues obteniendo estos correos, favor de contactar a tu proveedor de webhosting.';
$txt['database_error_versions'] = '<b>Nota:</b> Parece que tu base de datos puede necesitar una actualizaci&oacute;n. La versi&oacute;n de los archivos de tu foro est&aacute;n en la versi&oacute;n %s, mientras que tu base de datos est&aacute; en la versi&oacute;n de SMF %s. Te recomendamos que ejecutes la &uacute;ltima versi&oacuten de upgrade.php.';
$txt['template_parse_error'] = '&iexcl;Error al parsear el Template!';
$txt['template_parse_error_message'] = 'Parece que algo se ha estropeado en el foro con el sistema de temas.  Este problema puede que solamente sea temporal, por favor, regresa en unos momentos e intentalo nuevamente.  Si continuas viendo este mensaje, por favor contacta al administrador.<br /><br />Puedes intentar <a href="javascript:location.reload();">actualizar esta p&aacute;gina</a>.';
$txt['template_parse_error_details'] = 'Hubo un problema cargando el tema o archivo de idioma <tt><b>%1$s</b></tt>.  Por favor revisa la sintaxis e intenta nuevamente - recuerda, los ap&oacute;strofes (<tt>\'</tt>) por lo general deben tener una secuencia de escape con la diagonal invertida (<tt>\\</tt>).  Para ver informaci&#243;n especifica del error del sitio de PHP intenta <a href="' . $boardurl . '%1$s">acceder al archivo directamente</a>.<br /><br />Puedes intentar <a href="javascript:location.reload();">actualizar esta p&aacute;gina</a> o <a href="' . $scripturl . '?theme=1">usar el tema de default</a>.';

$txt['smf10'] = '<b>Hoy a las</b> ';
$txt['smf10b'] = '<b>Ayer</b> a las ';
$txt['smf20'] = 'Publicar Nueva Encuesta';
$txt['smf21'] = 'Pregunta';
$txt['smf23'] = 'Enviar voto';
$txt['smf24'] = 'Total de votos';
$txt['smf25'] = 'acceso r&aacute;pido: presiona alt+s para publicar o alt+p para previsualizar';
$txt['smf29'] = 'Ver resultados';
$txt['smf30'] = 'Bloquear Encuesta';
$txt['smf30b'] = 'Desbloquear Encuesta';
$txt['smf39'] = 'Editar Encuesta';
$txt['smf43'] = 'Encuesta';
$txt['smf47'] = '1 D&iacute;a';
$txt['smf48'] = '1 Semana';
$txt['smf49'] = '1 Mes';
$txt['smf50'] = 'Siempre';
$txt['smf52'] = 'Ingresar con nombre de usuario, contrase&ntilde;a y duraci&oacute;n de la sesi&oacute;n';
$txt['smf53'] = '1 Hora';
$txt['smf56'] = 'MOVIDO';
$txt['smf57'] = 'Por favor introduce una breve descripci&oacute;n de<br />por qu&eacute; este tema se est&aacute; moviendo.';
$txt['smf82'] = 'Foro';
$txt['smf88'] = 'en';
$txt['smf96'] = 'Tema Fijado';

$txt['smf138'] = 'Borrar';

$txt['smf199'] = 'Tus Mensajes personales';

$txt['smf211'] = 'KB';

$txt['smf223'] = '[M&aacute;s Estad&iacute;sticas]';

// Use numeric entities in the below three strings.
$txt['smf238'] = 'C&#243;digo';
$txt['smf239'] = 'Cita de';
$txt['quote'] = 'Citar';

// Untranslated
$txt['merge_to_topic_id'] = 'ID of target topic';
$txt['split'] = 'Dividir Tema';
$txt['merge'] = 'Combinar Temas';
$txt['smf254'] = 'Asunto para el nuevo tema';
$txt['smf255'] = 'S&oacute;lo dividir este mensaje.';
$txt['smf256'] = 'Dividir tema a partir de este mensaje (incluy&eacute;ndolo).';
$txt['smf257'] = 'Selecciona los mensajes a dividir.';
$txt['smf258'] = 'Nuevo Mensaje';
$txt['smf259'] = 'El tema se ha dividido satisfactoriamente en dos temas.';
$txt['smf260'] = 'Tema de Origen';
$txt['smf261'] = 'Por favor selecciona qu&eacute; mensajes deseas dividir.';
$txt['smf264'] = 'Los temas han sido satisfactoriamente combinados.';
$txt['smf265'] = 'Nuevo Tema Combinado';
$txt['smf266'] = 'Tema a ser combinado';
$txt['smf267'] = 'Foro destino';
$txt['smf269'] = 'Tema destino';
$txt['smf274'] = '&iquest;Est&aacute;s seguro que deseas combinar?';
$txt['smf275'] = 'con';
$txt['smf276'] = 'Esta funci&oacute;n combinar&aacute; los mensajes de dos temas en un tema. Los mensajes ser&aacute;n ordenados de acuerdo con la fecha en que se publicaron. Por lo tanto, el mensaje publicado m&aacute;s recientemente ser&aacute; el primer mensaje del tema combinado.';

$txt['smf277'] = 'Fijar tema';
$txt['smf278'] = 'Desfijar tema';
$txt['smf279'] = 'Bloquear tema';
$txt['smf280'] = 'Desbloquear tema';

$txt['smf298'] = 'B&uacute;squeda Avanzada';

$txt['smf299'] = 'RIESGO MAYOR DE SEGURIDAD:';
$txt['smf300'] = 'No has borrado ';

// Untranslated!
$txt['cache_writable_head'] = 'Performance Warning';
$txt['cache_writable'] = 'The cache directory is not writable - this will adversely affect the performance of your forum.';

$txt['smf301'] = 'P&aacute;gina creada en ';
$txt['smf302'] = ' segundos con ';
$txt['smf302b'] = ' queries.';

$txt['smf315'] = 'Usa esta funci&oacute;n para informar a los moderadores y administradores de un mensaje abusivo, o publicado incorrectamente.<br /><i>Es importante mencionar que tu direcci&oacute;n de email ser&aacute; revelada al moderador si usas esta funci&oacute;n.</i>';

$txt['online2'] = 'Conectado';
$txt['online3'] = 'Desconectado';
$txt['online4'] = 'Mensaje Personal (Conectado)';
$txt['online5'] = 'Mensaje Personal (Desconectado)';
$txt['online8'] = 'Status';

$txt['topbottom4'] = 'Ir Arriba';
$txt['topbottom5'] = 'Ir Abajo';

$forum_copyright = '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by %s</a> | 
<a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy; 2001-2006, Lewis Media</a>';

$txt['calendar3'] = 'Cumplea&ntilde;os:';
$txt['calendar4'] = 'Eventos:';
$txt['calendar3b'] = 'Cumplea&ntilde;os pr&oacute;ximos:';
$txt['calendar4b'] = 'Eventos pr&oacute;ximos:';
$txt['calendar5'] = ''; // Prompt for holidays in the calendar, leave blank to just display the holiday's name.
$txt['calendar9'] = 'Mes:';
$txt['calendar10'] = 'A&ntilde;o:';
$txt['calendar11'] = 'D&iacute;a:';
$txt['calendar12'] = 'T&iacute;tulo del Evento:';
$txt['calendar13'] = 'Publicar en:';
$txt['calendar20'] = 'Editar evento';
$txt['calendar21'] = '&iquest;Borrar este evento?';
$txt['calendar22'] = 'Borrar evento';
$txt['calendar_post_event'] = 'Publicar evento';
$txt['calendar24'] = 'Calendario';
$txt['calendar37'] = 'Ligar al calendario';
$txt['calendar43'] = 'Enlazar evento';
$txt['calendar47'] = 'Calendario de eventos pr&oacute;ximos';
$txt['calendar47b'] = 'Calendario de Hoy';
$txt['calendar51'] = 'Semana';
$txt['calendar54'] = 'N&uacute;mero de D&iacute;as:';
$txt['calendar_how_edit'] = '&iquest;c&oacute;mo editas esos eventos?';
$txt['calendar_link_event'] = 'Enlazar Evento al Mensaje:';
$txt['calendar_confirm_delete'] = '&#191;Est&#225;s seguro que deseas borrar este evento?';
$txt['calendar_linked_events'] = 'Eventos Ligados';

$txt['moveTopic1'] = 'Publicar un tema de redireccionamiento';
$txt['moveTopic2'] = 'Cambiar el t&iacute;tulo del tema';
$txt['moveTopic3'] = 'Nuevo asunto';
$txt['moveTopic4'] = 'Cambiar el asunto de cada mensaje';

$txt['theme_template_error'] = 'No se pudo cargar la plantilla \'%s\'.';
$txt['theme_language_error'] = 'No se pudo cargar el archivo de idioma \'%s\'.';

$txt['parent_boards'] = 'Subforos';

$txt['smtp_no_connect'] = 'No fue posible conectarse al servidor SMTP';
// Untranslated!
$txt['smtp_port_ssl'] = 'SMTP port setting incorrect; it should be 465 for SSL servers.';
$txt['smtp_bad_response'] = 'No se pudieron obterer los codigos de respuesta del servidor de mail';
$txt['smtp_error'] = 'Hubo problemas al enviar el mail. Error: ';
$txt['mail_send_unable'] = 'No se le pudo enviar el email a la direcci&oacute;n \'%s\'';

$txt['mlist_search'] = 'Buscar por usuarios';
$txt['mlist_search2'] = 'Buscar nuevamente';
$txt['mlist_search_email'] = 'Buscar por direcci&oacute;n de email';
$txt['mlist_search_messenger'] = 'Buscar por nick de messenger';
$txt['mlist_search_group'] = 'Buscar por grupo';
$txt['mlist_search_name'] = 'Buscar por nombre';
$txt['mlist_search_website'] = 'Buscar por sitio Web';
$txt['mlist_search_results'] = 'Buscar resultados por';

$txt['attach_downloaded'] = 'descargado';
$txt['attach_viewed'] = 'visto';
$txt['attach_times'] = 'veces';

$txt['msn'] = 'MSN';

$txt['settings'] = 'Configuraci&oacute;n';
$txt['never'] = 'Nunca';
$txt['more'] = 'm&aacute;s';

$txt['hostname'] = 'Nombre del servidor';
$txt['you_are_post_banned'] = 'Lo sentimos %s, tienes el restringido el poder publicar mensajes o enviar mensajes personales en el foro.';
$txt['ban_reason'] = 'Raz&oacute;n';

$txt['tables_optimized'] = 'Tablas de la base de datos optimizadas';

$txt['add_poll'] = 'Agregar encuesta';
$txt['poll_options6'] = 'Puedes seleccionar hasta %s opciones.';
$txt['poll_remove'] = 'Eliminar encuesta';
$txt['poll_remove_warn'] = '&iquest;Est&aacute;s seguro que deseas eliminar esta encuesta del tema?';
$txt['poll_results_expire'] = 'Los resultados se mostrar&aacute;n una vez que la encuesta se haya cerrado';
$txt['poll_expires_on'] = 'La votaci&oacute;n se cierra';
$txt['poll_expired_on'] = 'Votaci&oacute;n cerrada';
$txt['poll_change_vote'] = 'Eliminar Voto';
$txt['poll_return_vote'] = 'Opciones de votaci&oacute;n';

// Untranslated!
$txt['quick_mod_approve'] = 'Approve selected';
$txt['quick_mod_remove'] = 'Eliminar seleccionado(s)';
$txt['quick_mod_lock'] = 'Bloquear seleccionado(s)';
$txt['quick_mod_sticky'] = 'Fijar seleccionado(s)';
$txt['quick_mod_move'] = 'Mover seleccionado(s) a';
$txt['quick_mod_merge'] = 'Combinar seleccionado(s)';
$txt['quick_mod_markread'] = 'Marcar seleccionados como le&iacute;dos';
$txt['quick_mod_go'] = '&iexcl;Ir!';
$txt['quickmod_confirm'] = '&#191;Est&#225;s seguro que deseas hacer esto?';

$txt['spell_check'] = 'Revisar Ortograf&iacute;a';

$txt['quick_reply_1'] = 'Respuesta r&aacute;pida';
$txt['quick_reply_2'] = 'En la <i>Respuesta r&aacute;pida</i> puedes usar BBC y smileys como lo har&iacute;as en un mensaje normal, pero de una manera m&aacute;s conveniente.';
$txt['quick_reply_warning'] = '&iexcl;Advertencia: el tema est&aacute; bloqueado!<br />Solamente admins y moderadores pueden responder.';
// Untranslated!
$txt['wait_for_approval'] = 'Note: this post will not display until it\'s been approved by a moderator.';

$txt['notification_enable_board'] = '&iquest;Est&aacute;s seguro que deseas activar la notificaci&oacute;n de nuevos temas para este foro?';
$txt['notification_disable_board'] = '&iquest;Est&aacute;s seguro que deseas desactivar la notificaci&oacute;n de nuevos temas para este foro?';
$txt['notification_enable_topic'] = '&iquest;Est&aacute;s seguro que deseas activar la notificaci&oacute;n de nuevas respuestas para este tema?';
$txt['notification_disable_topic'] = '&iquest;Est&aacute;s seguro que deseas desactivar la notificaci&oacute;n de nuevas respuestas para este tema?';

$txt['rtm1'] = 'Reportar al moderador';

$txt['unread_topics_visit'] = 'Nuevos temas no le&iacute;dos';
$txt['unread_topics_visit_none'] = 'No se han encontrado temas no le&iacute;dos desde tu &uacute;ltima visita.  <a href="' . $scripturl . '?action=unread;all">Haz <i>click</i> aqu&iacute; para intentar todos los temas no le&iacute;dos</a>.';
$txt['unread_topics_all'] = 'Todos los temas no le&iacute;dos';
$txt['unread_replies'] = 'Temas actualizados';

$txt['who_title'] = 'Qui&eacute;n est&aacute; en l&iacute;nea';
$txt['who_and'] = ' y ';
$txt['who_viewing_topic'] = ' est&aacute;n viendo este tema.';
$txt['who_viewing_board'] = ' est&aacute;n viendo este foro.';
$txt['who_member'] = 'Usuario';

$txt['powered_by_php'] = 'Powered by PHP';
$txt['powered_by_mysql'] = 'Powered by MySQL';
$txt['valid_html'] = 'HTML 4.01 v&aacute;lido';
$txt['valid_xhtml'] = 'XHTML 1.0 v&aacute;lido!';
$txt['valid_css'] = 'CSS v&aacute;lido!';

$txt['guest'] = 'Visitante';
$txt['guests'] = 'Visitantes';
$txt['user'] = 'Usuario';
$txt['users'] = 'Usuarios';
$txt['hidden'] = 'Oculto(s)';
$txt['buddy'] = 'Amigo';
$txt['buddies'] = 'Amigos';
// Untranslated!
$txt['most_online_ever'] = 'Most Online Ever';
$txt['most_online_today'] = 'Most Online Today';

$txt['merge_select_target_board'] = 'Selecciona el foro destino del tema combinado';
$txt['merge_select_poll'] = 'Selecciona cual encuesta tendr&aacute; el tema combinado';
$txt['merge_topic_list'] = 'Selecciona los temas a combinar';
$txt['merge_select_subject'] = 'Selecciona el t&iacute;tulo del tema combinado';
$txt['merge_custom_subject'] = 'T&iacute;tulo personalizado';
$txt['merge_enforce_subject'] = 'Cambiar el t&iacute;tulo de todos los mensajes';
$txt['merge_include_notifications'] = '&iquest;Incluir notificaciones?';
$txt['merge_check'] = '&iquest;Combinar?';
$txt['merge_no_poll'] = 'Sin encuesta';

$txt['response_prefix'] = 'Re: ';
$txt['current_icon'] = 'Icono actual';

$txt['smileys_current'] = 'Conjunto actual de Smileys';
$txt['smileys_none'] = 'Sin Smileys';
$txt['smileys_forum_board_default'] = 'Las que el foro est&eacute; utilizando por defecto';

$txt['search_results'] = 'Resultados de la b&uacute;squeda';
$txt['search_no_results'] = 'No se encontraron resultados';

$txt['totalTimeLogged1'] = 'Tiempo total en l&iacute;nea: ';
$txt['totalTimeLogged2'] = ' d&iacute;as, ';
$txt['totalTimeLogged3'] = ' horas y ';
$txt['totalTimeLogged4'] = ' minutos.';
$txt['totalTimeLogged5'] = 'd ';
$txt['totalTimeLogged6'] = 'h ';
$txt['totalTimeLogged7'] = 'm';

$txt['approve_thereis'] = 'Hay';
$txt['approve_thereare'] = 'Hay';
$txt['approve_member'] = 'un usuario';
$txt['approve_members'] = 'usuarios';
$txt['approve_members_waiting'] = 'esperando aprobaci&oacute;n.';

$txt['notifyboard_turnon'] = '&iquest;Deseas una notificaci&oacute;n por email cuando alguien publique un nuevo tema en este foro?';
$txt['notifyboard_turnoff'] = '&iquest;Est&aacute;s seguro que NO deseas recibir notificaciones de temas nuevos en este foro?';

$txt['activate_code'] = 'Tu c&#243;digo de activaci&#243;n es';

$txt['find_members'] = 'Buscar usuarios';
$txt['find_username'] = 'Nombre, nombre de usuario, o direcci&oacute;n de email';
$txt['find_buddies'] = '&iquest;Mostrar amigos solamente?';
$txt['find_wildcards'] = 'C&oacute;modines permitidos: *, ?';
$txt['find_no_results'] = 'No se encontraron resultados';
$txt['find_results'] = 'Resultados';
$txt['find_close'] = 'Cerrar';

$txt['unread_since_visit'] = 'Mostrar mensajes no le&iacute;dos desde la &uacute;ltima visita.';
$txt['show_unread_replies'] = 'Mostrar nuevas respuestas a tus mensajes.';

$txt['change_color'] = 'Cambiar Color';

$txt['quickmod_delete_selected'] = 'Borrar seleccionados';

// In this string, don't use entities. (&amp;, etc.)
$txt['show_personal_messages'] = 'Has recibido uno o m&#225;s nuevos mensajes personales.\\n&#191;Deseas verlos ahora (en una nueva ventana)?';

$txt['previous_next_back'] = '&laquo; anterior';
$txt['previous_next_forward'] = 'pr&oacute;ximo &raquo;';

$txt['movetopic_auto_board'] = '[FORO]';
$txt['movetopic_auto_topic'] = '[URL DEL TEMA]';
$txt['movetopic_default'] = 'El tema ha sido movido a ' . $txt['movetopic_auto_board'] . ".\n\n" . $txt['movetopic_auto_topic'];

$txt['upshrink_description'] = 'Encoger o expandir encabezado.';

$txt['mark_unread'] = 'Marcar como no le&iacute;dos';

$txt['ssi_not_direct'] = 'Por favor no acceses SSI.php usando directamente el URL; mejor usa la ubicaci&oacute;n (%s) o agrega ?ssi_function=algun_valor.';
$txt['ssi_session_broken'] = '&iexcl;SSI.php no pudo cargar una sesi&oacute;n!  Esto puede causar problemas con algunas funciones, tales como ingresar o salir - &iexcl;Favor de asegurarse que SSI.php est&eacute; incluido siempre al principio *antes de cualquier otro c&oacute;digo* en todos tus scripts!';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['preview_title'] = 'Previsualizar mensaje';
$txt['preview_fetch'] = 'Obteniendo la previsualizaci&oacute;n...';
$txt['preview_new'] = 'Nuevo mensaje';
$txt['error_while_submitting'] = 'Hubo un error mientras se enviaba este mensaje.';

$txt['split_selected_posts'] = 'Mensajes seleccionados';
$txt['split_selected_posts_desc'] = 'Los mensajes mostrados a continuaci&oacute;n formar&aacute;n un nuevo tema una vez divididos.';
$txt['split_reset_selection'] = 'reinicializar selecci&oacute;n';

// !!! Untranslated!
$txt['modify_cancel'] = 'Cancel';
$txt['mark_read_short'] = 'Marcar como le&iacute;do';

$txt['pm_short'] = 'My Messages';
$txt['hello_member_ndt'] = 'Hello';

// Untranslated!
$txt['unapproved_posts'] = 'Unapproved Posts (Topics: %d, Posts: %d)';
$txt['ajax_in_progress'] = 'Loading...';
$txt['mod_reports_waiting'] = 'There are currently %1$d moderator reports open.';
$txt['view_unread_category'] = 'Unread Posts';
?>
