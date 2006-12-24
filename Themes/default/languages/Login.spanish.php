<?php
// Version: 2.0 Alpha; Login

// Important! Before editing these language files please read the text at the topic of index.spanish.php.

$txt['need_username'] = 'Debes escribir un nombre de usuario.';
$txt['no_password'] = 'No proporcionaste tu contrase&ntilde;a.';
$txt['incorrect_password'] = 'Contrase&ntilde;a incorrecta';
$txt['choose_username'] = 'Escoge un nombre de usuario';
$txt[155] = 'Modo de Mantenimiento';
$txt['registration_successful'] = 'Registro con &eacute;xito';
$txt['now_a_member'] = '&iexcl;Felicidades! Ahora eres miembro del foro.';
// Use numeric entities in the below string.
$txt['your_password'] = 'y tu contrase&#241;a es';
$txt['valid_email_needed'] = 'Por favor introduce una direcci&oacute;n de email v&aacute;lida. (%s)';
$txt['required_info'] = 'Informaci&oacute;n Requerida';
$txt['identification_by_smf'] = 'Usado s&oacute;lo para identificaci&oacute;n por SMF. Puedes usar caracteres especiales despu&eacute;s de haber ingresado, cambiando tu nombre a mostrar en tu perfil.';
$txt['agree'] = 'Acepto';
$txt['decline'] = 'No acepto';
$txt['warning'] = '&iexcl;Advertencia!';
$txt['only_members_can_access'] = 'Solamente usuarios registrados tienen acceso a esta secci&oacute;n.';
$txt['login_below'] = 'Por favor ingresa abajo o haz click';
$txt['login_or_register'] = '-aqu&iacute;-';
$txt['login_with_forum'] = 'para registrar una cuenta en %s.';
// Use numeric entities in the below two strings.
$txt['may_change_in_profile'] = 'Lo puedes cambiar despu&#233;s de ingresar en la p&#225;gina de perfil, o visitando esta p&#225;gina despu&#233;s de que ingreses:';
$txt['your_username_is'] = 'Tu nombre de usuario es: ';

$txt['login_hash_error'] = 'El esquema para la seguridad de las contrase&ntilde;as ha sido actualizado recientemente.  Por favor, introduce tu contrase&ntilde;a nuevamente.';

$txt['register_age_confirmation'] = 'Tengo por lo menos %d a&ntilde;os';

// Use numeric entities in the below six strings.
$txt['register_subject'] = 'Bienvenido a ' . $context['forum_name'];

// For the below three messages, %1$s is the display name, %2$s is the username, %3$s is the password, %4$s is the activation code, and %5$s is the activation link (the last two are only for activation.)
$txt['register_immediate_message'] = 'Haz registrado una cuenta en  ' . $context['forum_name'] . ', %1$s!' . "\n\n" . 'El nombre de usuario de tu cuenta es %2$s y su contrase&#241;a es %3$s.' . "\n\n" . 'Puedes cambiar tu contrase&#241;a despues que ingreses, en la p&#225;gina de perfil, o visitando esta p&#225;gina una vez que ingreses:' . "\n\n" .$scripturl . '?action=profile' . "\n\n" . $txt['regards_team'];
$txt['register_activate_message'] = 'Haz registrado una cuenta en ' . $context['forum_name'] . ', %1$s!' . "\n\n" . 'El nombre de usuario de tu cuenta es %2$s y su contrase&#241;a es %3$s (que puede ser cambiada despu&#233;s.)' . "\n\n" . 'Antes de que puedas ingresar, debes primero activar tu cuenta. Para hacerlo, por favor sigue este enlace:' . "\n\n" . '%5$s' . "\n\n" . 'En caso que tengas alg&#250;n problema con la activaci&#243;n, por favor usa el c&#243;digo "%4$s".' . "\n\n" . $txt['regards_team'];
$txt['register_pending_message'] = 'Tu solicitud de registro en ' . $context['forum_name'] . ' ha sido recibida, %1$s.' . "\n\n" . 'El nombre de usuario con el que te registraste fue %2$s y su contrase&#241;a fue %3$s.' . "\n\n" . 'Antes de que puedas ingresar y utilizar el foro, tu solicitud ser&#225; revisada y aprobada.  Cuando esto suceda, recibir&#225;s otro email desde esta direcci&#243;n.' . "\n\n" . $txt['regards_team'];

// For the below two messages, %1$s is the user's display name, %2$s is their username, %3$s is the activation code, and %4$s is the activation link (the last two are only for activation.)
$txt['resend_activate_message'] = 'Haz registrado una cuenta en ' . $context['forum_name'] . ', %1$s!' . "\n\n" . 'Tu nombre de usuario es "%2$s".' . "\n\n" . 'Antes de que puedas ingresar, debes primero activar tu cuenta. Para hacerlo, por favor sigue este enlace:' . "\n\n" . '%4$s' . "\n\n" . 'En caso que tengas alg&#250;n problema con la activaci&#243;n, por favor usa el c&#243;digo "%3$s".' . "\n\n" . $txt['regards_team'];
$txt['resend_pending_message'] = 'Tu solicitud de registro en ' . $context['forum_name'] . ' ha sido recibida, %1$s.' . "\n\n" . 'El nombre de usuario con el que te registraste fue %2$s.' . "\n\n" . 'Antes de que puedas ingresar y utilizar el foro, tu solicitud ser&#225; revisada y aprobada.  Cuando esto suceda, recibir&#225;s otro email desde esta direcci&#243;n.' . "\n\n" . $txt['regards_team'];

$txt['ban_register_prohibited'] = 'Lo siento, no est&aacute;s autorizado para registrarte en este foro';
$txt['under_age_registration_prohibited'] = 'Lo sentimos, pero no se permite el registro en este foro de personas menores de %d a&ntilde;os';

$txt['activate_account'] = 'Activaci&oacute;n de la cuenta';
$txt['activate_success'] = 'Tu cuenta ha sido activada satisfactoriamente. Ahora puedes proceder a ingresar al foro.';
$txt['activate_not_completed1'] = 'Tu cuenta de email necesita ser validada antes de que puedas ingresar.';
$txt['activate_not_completed2'] = '&iquest;Necesitas otro email de activaci&oacute;n?';
// Untranslated!  Last part.
$txt['activate_after_registration'] = 'Gracias por registrarte. Recibir&aacute;s en breve un email con un enlace para activar tu cuenta.  If you don\'t receive an email after some time, check your spam folder.';
$txt['invalid_userid'] = 'El usuario no existe';
$txt['invalid_activation_code'] = 'C&oacute;digo de activaci&oacute;n inv&aacute;lido';
$txt['invalid_activation_username'] = 'Nombre de usuario o email';
$txt['invalid_activation_new'] = 'Si te registraste con una direcci&oacute;n de email incorrecta, escribe aqu&iacute; una nueva junto con tu contrase&ntilde;a.';
$txt['invalid_activation_new_email'] = 'Nueva direcci&oacute;n de email';
$txt['invalid_activation_password'] = 'Contrase&ntilde;a anterior';
$txt['invalid_activation_resend'] = 'Reenviar c&oacute;digo de activaci&oacute;n';
$txt['invalid_activation_known'] = 'Si ya conoces tu c&oacutedigo de activaci&oacute;n, escr&iacute;belo aqu&iacute;.';
$txt['invalid_activation_retry'] = 'C&oacute;digo de activaci&oacute;n';
$txt['invalid_activation_submit'] = 'Activar';

$txt['coppa_no_concent'] = 'El administrador no ha recibido a&uacute;n el consentimiento de tus padres/tutor para tu cuenta.';
$txt['coppa_need_more_details'] = '&iquest;Necesitas m&aacute;s detalles?';

$txt['awaiting_delete_account'] = '&iexcl;Tu cuenta ha sido marcada para borrarse!<br />Si deseas restaurar tu cuenta, For favor selecciona la casilla &quot;Reactivar mi cuenta&quot;, e ingresa nuevamente.';
$txt['undelete_account'] = 'Reactivar mi cuenta';

// Use numeric entities in the below three strings.
$txt['change_password'] = 'Detalles de la nueva contrase&#241;a';
$txt['change_password_login'] = 'Tus datos para ingresar en';
$txt['change_password_new'] = 'han sido cambiados y tu contrase&#241;a ha sido reseteada. Debajo est&#225;n tus nuevos datos para ingresar.';

$txt['maintenance3'] = 'Este foro est&aacute; en modo de mantenimiento.';

// These two are used as a javascript alert; please use international characters directly, not as entities.
$txt['register_agree'] = 'Favor de leer/aceptar los t&#233;rminos para poder enviar la forma.';
$txt['register_passwords_differ_js'] = 'No coinciden las contrase&#241;as.';

$txt['approval_after_registration'] = 'Gracias por registrarte. El administrador debe aprobar tu registro antes de que puedas empezar a usar tu cuenta, recibir&aacute;s un email a la brevedad posible notific&aacute;ndote de la decisi&oacute;n del administrador.';

$txt['admin_settings_desc'] = 'Aqu&iacute; puedes cambiar varios par&aacute;metros relacionados con el registro de nuevos usuarios.';

$txt['admin_setting_registration_method'] = 'M&eacute;todo de registro utilizado para los nuevos usuarios';
$txt['admin_setting_registration_disabled'] = 'Registro Desactivado';
$txt['admin_setting_registration_standard'] = 'Registro Inmediato';
$txt['admin_setting_registration_activate'] = 'Activaci&oacute;n por el Usuario';
$txt['admin_setting_registration_approval'] = 'Aprobaci&oacute;n de Usuarios';
$txt['admin_setting_notify_new_registration'] = 'Notificar a los administradores cuando un nuevo usuario se registre';
$txt['admin_setting_send_welcomeEmail'] = 'Mandar email de bienvenida a los nuevos usuarios';

// Untranslated!
$txt['admin_setting_password_strength'] = 'Required strength for user passwords';
$txt['admin_setting_password_strength_low'] = 'Low - 4 character minimum';
$txt['admin_setting_password_strength_medium'] = 'Medium - cannot contain username';
$txt['admin_setting_password_strength_high'] = 'High - mixture of different characters';

//!!! Untranslated
$txt['admin_setting_disable_visual_verification'] = 'Disable the use of the visual verification on registration';


$txt['admin_setting_coppaAge'] = 'Edad debajo de la cual se aplicar&aacute;n restricciones en el registro';
$txt['admin_setting_coppaAge_desc'] = '(Pon 0 para desactivarlo)';
$txt['admin_setting_coppaType'] = 'Acci&oacute;n que se tomar&aacute; cuando un usuario por debajo de la edad m&iacute;nima se registre';
$txt['admin_setting_coppaType_reject'] = 'Rechazar su registro';
$txt['admin_setting_coppaType_approval'] = 'Solicitar la aprobaci&oacute;n de los padres/tutor';
$txt['admin_setting_coppaPost'] = 'Direcci&oacute;n postal a la que se debe enviar la autorizaci&oacute;n';
$txt['admin_setting_coppaPost_desc'] = 'Solo aplica cuando la restricci&oacute;n de edad est&aacute; activada';
$txt['admin_setting_coppaFax'] = 'N&uacute;mero de fax al cual las formas de aprobaci&oacute;n deber&aacute;n enviarse';
$txt['admin_setting_coppaPhone'] = 'T&eacute;lefono en el que se pueden contactar a los padres/tutor con preguntas referentes a la restricci&oacute;n de edad';

$txt['admin_register'] = 'Registro de un nuevo usuario';
$txt['admin_register_desc'] = 'Desde aqu&iacute; puedes registrar en el foro nuevos usuarios y, de as&iacute; desearlo, enviarles sus detalles por email.';
$txt['admin_register_username'] = 'Nuevo Nombre de usuario';
$txt['admin_register_email'] = 'Nueva Direcci&oacute;n de email';
$txt['admin_register_password'] = 'Contrase&ntilde;a';
$txt['admin_register_username_desc'] = 'Nombre de usuario para el nuevo usuario';
$txt['admin_register_email_desc'] = 'Direcci&oacute;n email del usuario<br />(requerida si seleccionaste la opci&oacute;n de enviarles por email sus detalles)';
$txt['admin_register_password_desc'] = 'Nueva contrase&ntilde;a para el usuario';
$txt['admin_register_email_detail'] = 'Enviar por email la nueva contrase&ntilde;a al usuario';
$txt['admin_register_email_detail_desc'] = 'Es necesaria la direcci&oacute;n email, a&uacute;n si no est&aacute; seleccionado';
$txt['admin_register_email_activate'] = 'Pedirle al usuario que active la cuenta';
$txt['admin_register_group'] = 'Grupo primario';
$txt['admin_register_group_desc'] = 'Grupo de usuario primario al que el nuevo usuario pertenecer&aacute;';
$txt['admin_register_group_none'] = '(sin grupo primario)';
// Untranslated!
$txt['admin_register_done'] = 'Member %s has been registered successfully!';

$txt['admin_browse_register_new'] = 'Registrar nuevo usuario';

// Use numeric entities in the below three strings.
$txt['admin_notify_subject'] = 'Un nuevo usuario se ha suscrito';
$txt['admin_notify_profile'] = '%s ha firmado como nuevo usuario del foro. Haz <i>click</i> en el enlace para ver su perfil.';
$txt['admin_notify_approval'] = 'Antes de que este usuario pueda publicar mensajes debe tener primero su cuenta aprobada. Haz <i>click</i> en el enlace para ir a la pantalla de aprobaci&#243;n.';

$txt['coppa_title'] = 'Foro con restricci&oacute;n de edad';
$txt['coppa_after_registration'] = 'Gracias por registrarte con ' . $context['forum_name'] . '.<br /><br />Debido a que est&aacute;s por debajo de la edad de {MINIMUM_AGE}, hay un requerimiento legal
	para obtener el permiso de tus padres/tutor antes de que puedas empezar a usar tu cuenta. Para proceder con la activaci&oacute;n de la cuenta por favor imprime la forma que a continuaci&oacute;n se muestra:';
$txt['coppa_form_link_popup'] = 'Cargar la forma en una ventana nueva';
$txt['coppa_form_link_download'] = 'Descargar forma';
$txt['coppa_send_to_one_option'] = 'Entonces solic&iacute;tale a tus padres/tutor que envien la forma propiamente llenada por:';
$txt['coppa_send_to_two_options'] = 'Entonces solic&iacute;tale a tus padres/tutor que envien la forma propiamente llenada por cualquiera de estas dos opciones:';
$txt['coppa_send_by_post'] = 'Correo, a la siguiente direcci&oacute;n:';
$txt['coppa_send_by_fax'] = 'Fax, al siguiente n&uacute;mero:';
$txt['coppa_send_by_phone'] = 'Alternativamente, haz que le hablen al administrador al n&uacute;mero {PHONE_NUMBER}.';

$txt['coppa_form_title'] = 'Forma de permiso para registrarse en ' . $context['forum_name'];
$txt['coppa_form_address'] = 'Direcci&oacute;n';
$txt['coppa_form_date'] = 'Fecha';
$txt['coppa_form_body'] = 'Yo {PARENT_NAME},<br /><br />doy el permiso para que {CHILD_NAME} (child name) se convierta en un usuario registrado del foro: ' . $context['forum_name'] . ', con el nombre de usuario: {USER_NAME}.<br /><br />Entiendo que cierta informaci&oacute;n personal proporcionada por {USER_NAME} puede que sea mostrada a otros usuarios del foro.<br /><br />Firmado por:<br />{PARENT_NAME} (Padre/Tutor).';

// Untranslated!
$txt['visual_verification_label'] = 'Visual verification';
$txt['visual_verification_description'] = 'Type the letters shown in the picture';
$txt['visual_verification_sound'] = 'Listen to the letters';
$txt['visual_verification_sound_again'] = 'Play again';
$txt['visual_verification_sound_close'] = 'Close window';
$txt['visual_verification_request_new'] = 'Request another image';
$txt['visual_verification_sound_direct'] = 'Having problems hearing this?  Try a direct link to it.';

?>