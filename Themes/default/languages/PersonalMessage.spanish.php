<?php
// Version: 2.0 Alpha; PersonalMessage

// Important! Before editing these language files please read the text at the topic of index.spanish.php.

$txt[143] = '&Iacute;ndice de mensajes personales';
$txt[148] = 'Enviar mensaje';
$txt[150] = 'Para';
$txt[1502] = 'Bcc';
$txt[316] = 'Bandeja de Entrada';
// Untranslated!
$txt['sent_items'] = 'Sent Items';
$txt[321] = 'Nuevo Mensaje';
$txt[411] = 'Borrar Mensajes';
// Don't translate "PMBOX" in this string.
$txt[412] = 'Borrar todos los mensajes de tu PMBOX';
$txt[413] = '¿Estás seguro que deseas borrar todos los mensajes?';
$txt[535] = 'Destinatario';
// Don't translate the word "SUBJECT" here, as it is used to format the message - use numeric entities as well.
$txt[561] = 'Nuevo Mensaje Personal: SUBJECT';
// Don't translate SENDER or MESSAGE in this language string; they are replaced with the corresponding text - use numeric entities too.
$txt[562] = 'Acaban de enviarte un mensaje personal de parte de SENDER en ' . $context['forum_name'] . '.\\n\\nIMPORTANTE: Recuerda, esto es solamente una notificaci&oacute;n. Por favor, no respondas a este email.\\n\\nEl mensaje que te enviaron fue:\\n\\nMESSAGE';
$txt[748] = '(m&uacute;ltiples destinatarios como \'nombreusuario1, nombreusuario2\')';
// Use numeric entities in the below string.
$txt['instant_reply'] = 'Responder a este mensaje personal aqu&iacute;:';

$txt['smf249'] = '¿Deseas borrar todos los Mensajes Instant&aacute;neos seleccionados?';

$txt['sent_to'] = 'Enviado a';
$txt['reply_to_all'] = 'Responder a Todos';

$txt['pm_capacity'] = 'Capacidad';
$txt['pm_currently_using'] = '%s mensajes, %s%% lleno.';

$txt['pm_error_user_not_found'] = 'No se pudo encontrar al usuario \'%s\'.';
$txt['pm_error_ignored_by_user'] = 'El usuario \'%s\' ha bloqueado tu mensaje personal.';
$txt['pm_error_data_limit_reached'] = 'El mensaje personal no se pudo enviar a \'%s\' deb&iacute;do a que excediste el l&iacute;mite de mensajes personales.';
$txt['pm_successfully_sent'] = 'El mensaje personal se envi&oacute; satisfactoriamente a \'%s\'.';
$txt['pm_send_report'] = 'Enviar reporte';
$txt['pm_save_outbox'] = 'Guardar una copia en mi buz&oacute;n de salida';
$txt['pm_undisclosed_recipients'] = 'Destinatario(s) sin revelar';

// Untranslated!
$txt['pm_read'] = 'Read';
$txt['pm_replied'] = 'Replied To';

$txt['pm_prune'] = 'Purgar Mensajes';
$txt['pm_prune_desc1'] = 'Borrar todos tus mensajes personales m&aacute;s antiguos de ';
$txt['pm_prune_desc2'] = 'd&iacute;as.';
$txt['pm_prune_warning'] = '¿Estás seguro que deseas borrar tus mensajes personales?';

$txt['pm_actions_title'] = 'Acciones adicionales';
$txt['pm_actions_delete_selected'] = 'Borrar seleccionados';
$txt['pm_actions_filter_by_label'] = 'Filtrar por etiqueta';
$txt['pm_actions_go'] = 'Ir';

$txt['pm_apply'] = 'Aplicar';
$txt['pm_manage_labels'] = 'Administrar Etiquetas';
$txt['pm_labels_delete'] = '¿Estás seguro que deseas borrar las etiquetas seleccionadas?';
$txt['pm_labels_desc'] = 'Aqu&iacute; puedes agregar, editar y borrar etiquetas utilizadas en el centro de mensajes personales.';
$txt['pm_label_add_new'] = 'Agregar nueva etiqueta';
$txt['pm_label_name'] = 'Nombre de la etiqueta';
$txt['pm_labels_no_exist'] = '¡No tienes ninguna etiqueta dada de alta!';

$txt['pm_current_label'] = 'Etiqueta';
$txt['pm_msg_label_title'] = 'Etiquetar Mensaje';
$txt['pm_msg_label_apply'] = 'Agregar etiqueta';
$txt['pm_msg_label_remove'] = 'Eliminar etiqueta';
$txt['pm_msg_label_inbox'] = 'Bandeja de Entrada';
$txt['pm_sel_label_title'] = 'Etiquetar seleccionados';

$txt['pm_labels'] = 'Etiquetas';
$txt['pm_messages'] = 'Mensajes';
// Untranslated!
$txt['pm_actions'] = 'Actions';
$txt['pm_preferences'] = 'Configuraci&oacute;n';

$txt['pm_is_replied_to'] = 'Haz reenviado o respondido a este mensaje.';

// Reporting messages.
// Untranslated!
$txt['pm_report_to_admin'] = 'Report To Admin';
$txt['pm_report_title'] = 'Report Personal Message';
$txt['pm_report_desc'] = 'From this page you can report the personal message you received to the admin team of the forum. Please be sure to include a description of why you are reporting the message, as this will be sent along with the contents of the original message.';
$txt['pm_report_admins'] = 'Administrator to send report to';
$txt['pm_report_all_admins'] = 'Send to all forum admins';
$txt['pm_report_reason'] = 'Reason why you are reporting this message';
$txt['pm_report_message'] = 'Report Message';

// Untranslated!
// Important - The following strings should use numeric entities.
$txt['pm_report_pm_subject'] = '[REPORT] ';
// In the below string, do not translate "{REPORTER}" or "{SENDER}".
$txt['pm_report_pm_user_sent'] = '{REPORTER} has reported the below personal message, sent by {SENDER}, for the following reason:';
$txt['pm_report_pm_other_recipients'] = 'Other recipients of the message include:';
$txt['pm_report_pm_hidden'] = '%d hidden recipient(s)';
$txt['pm_report_pm_unedited_below'] = 'Below is the original contents of the personal message being reported:';
$txt['pm_report_pm_sent'] = 'Sent:';

// Untranslated!
$txt['pm_report_done'] = 'Thank you for submitting this report. You should hear back from the admin team shortly';
$txt['pm_report_return'] = 'Return to Inbox';

// Untranslated!
$txt['pm_search_title'] = 'Search Personal Messages';
$txt['pm_search_bar_title'] = 'Search Messages';
$txt['pm_search_text'] = 'Search for';
$txt['pm_search_go'] = 'Search';
$txt['pm_search_advanced'] = 'Advanced search';
$txt['pm_search_user'] = 'by user';
$txt['pm_search_match_all'] = 'Match all words';
$txt['pm_search_match_any'] = 'Match any words';
$txt['pm_search_options'] = 'Options';
$txt['pm_search_post_age'] = 'Age';
$txt['pm_search_show_complete'] = 'Show full message in results.';
$txt['pm_search_subject_only'] = 'Search by subject and author only.';
$txt['pm_search_between'] = 'Between';
$txt['pm_search_between_and'] = 'and';
$txt['pm_search_between_days'] = 'days';
$txt['pm_search_order'] = 'Order results by';
// Untranslated!
$txt['pm_search_choose_label'] = 'Choose labels to search by, or search all';

// Untranslated!
$txt['pm_search_results'] = 'Search Results';
$txt['pm_search_none_found'] = 'No Messages Found';

// Untranslated!
$txt['pm_search_orderby_relevant_first'] = 'Most relevant first';
$txt['pm_search_orderby_recent_first'] = 'Most recent first';
$txt['pm_search_orderby_old_first'] = 'Oldest first';

// Untranslated!
$txt['pm_visual_verification_label'] = 'Verification';
$txt['pm_visual_verification_desc'] = 'Please enter the code in the image above to send this pm.';
$txt['pm_visual_verification_listen'] = 'Listen to the Letters';

// Untranslated!
// %1$s is the display name, %2$s is the forum name
$txt['birthday_email'] = 'Dear %1$s,

   We here at %2$s would like to wish you a happy birthday.  May this day and the year to follow be full of joy.

Regards,
The %2$s Team.';
$txt['birthday_email_subject'] = 'Happy birthday from %1$s.';

// Untranslated!
$txt['pm_settings'] = 'Change Settings';
$txt['pm_settings_desc'] = 'From this page you can change a variety of personal messaging options - including how messages are displayed. You can also create a list of people to reject incoming messages from.';
$txt['pm_settings_save'] = 'Save Changes';

$txt[325] = 'Lista de ignorados';
$txt[326] = 'Agrega un nombre de usuario en cada l&iacute;nea.<br />O escribe * para ignorar todos los mensajes.';
$txt[327] = 'Notificar por email cada que recibas un mensaje personal:';
$txt['email_notify_never'] = 'Nunca';
$txt['email_notify_buddies'] = 'Solamente de los amigos';
$txt['email_notify_always'] = 'Siempre';

$txt['copy_to_outbox'] = 'Guardar, por defecto, una copia de cada Mensaje Personal en mi buz&oacute;n de salida.';
$txt['popup_messages'] = '&iquest;Mostrar un popup cuando recibas un nuevo mensaje?';
//!!! Untranslated
$txt['pm_remove_inbox_label'] = 'Remove the inbox label when applying another label';
$txt['pm_display_mode'] = 'Display personal messages';
$txt['pm_display_mode_all'] = 'All at once';
$txt['pm_display_mode_one'] = 'One at a time';
$txt['pm_display_mode_linked'] = 'As a conversation';
$txt['pm_change_view'] = 'Change View';

//!!! Untranslated
$txt['pm_manage_rules'] = 'Manage Rules';
$txt['pm_manage_rules_desc'] = 'Message rules allow you to automatically sort incoming messages dependant on a set of criteria you define. Below are all the rules you currently have setup. To edit a rule simply click the rule name.';
$txt['pm_rules_none'] = 'You have not yet setup any message rules.';
$txt['pm_rule_title'] = 'Rule';
$txt['pm_add_rule'] = 'Add New Rule';
$txt['pm_apply_rules'] = 'Apply Rules Now';
// Use entities in the below string.
$txt['pm_js_apply_rules_confirm'] = 'Are you sure you wish to apply the current rules to all personal messages?';
$txt['pm_edit_rule'] = 'Edit Rule';
$txt['pm_rule_save'] = 'Save Rule';
$txt['pm_delete_selected_rule'] = 'Delete Selected Rules';
// Use entities in the below string.
$txt['pm_js_delete_rule_confirm'] = 'Are you sure you wish to delete the selected rules?';
$txt['pm_rule_name'] = 'Name';
$txt['pm_rule_name_desc'] = 'Name to remember this rule by';
$txt['pm_rule_name_default'] = '[NAME]';
$txt['pm_rule_description'] = 'Description';
$txt['pm_rule_not_defined'] = 'Add some criteria to begin building this rule description.';
$txt['pm_rule_js_disabed'] = '<span style="color: red;"><b>Note:</b> You appear to have javascript disabled. We highly recommend you enable javascript to use this feature.';
$txt['pm_rule_criteria'] = 'Criteria';
$txt['pm_rule_criteria_add'] = 'Add Criteria';
$txt['pm_rule_criteria_pick'] = 'Choose Criteria';
$txt['pm_rule_mid'] = 'Sender Name';
$txt['pm_rule_gid'] = 'Sender\'s Group';
$txt['pm_rule_sub'] = 'Message Subject Contains';
$txt['pm_rule_msg'] = 'Message Body Contains';
$txt['pm_rule_bud'] = 'Sender is Buddy';
$txt['pm_rule_sel_group'] = 'Select Group';
$txt['pm_rule_logic'] = 'When Checking Criteria';
$txt['pm_rule_logic_and'] = 'All criteria must be met';
$txt['pm_rule_logic_or'] = 'Any criteria can be met';
$txt['pm_rule_actions'] = 'Actions';
$txt['pm_rule_sel_action'] = 'Select an Action';
$txt['pm_rule_add_action'] = 'Add Action';
$txt['pm_rule_label'] = 'Label message with';
$txt['pm_rule_sel_label'] = 'Select Label';
$txt['pm_rule_delete'] = 'Delete Message';
$txt['pm_rule_no_name'] = 'You forgot to enter a name for the rule.';
$txt['pm_rule_no_criteria'] = 'A rule must have at least one criteria and one action set.';
$txt['pm_rule_too_complex'] = 'The rule you are creating is too long for SMF to store. Try breaking it up into smaller rules.';

//!!! Untranslated
$txt['pm_readable_and'] = '<i>and</i>';
$txt['pm_readable_or'] = '<i>or</i>';
$txt['pm_readable_start'] = 'If ';
$txt['pm_readable_end'] = '.';
$txt['pm_readable_member'] = 'message is from &quot;{MEMBER}&quot;';
$txt['pm_readable_group'] = 'sender is from the &quot;{GROUP}&quot; group';
$txt['pm_readable_subject'] = 'message subject contains &quot;{SUBJECT}&quot;';
$txt['pm_readable_body'] = 'message body contains &quot;{BODY}&quot;';
$txt['pm_readable_buddy'] = 'sender is a buddy';
$txt['pm_readable_label'] = 'apply label &quot;{LABEL}&quot;';
$txt['pm_readable_delete'] = 'delete the message';
$txt['pm_readable_then'] = '<b>then</b>';

?>