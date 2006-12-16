<?php
// Version: 2.0 Alpha; PersonalMessage

// Important! Before editing these language files please read the text at the topic of index.german.php.

$txt[143] = 'Private Mitteilungen';
$txt[148] = 'Senden';
$txt[150] = 'An';
$txt[1502] = 'Bcc';
$txt[316] = 'Posteingang';
$txt[320] = 'Postausgang';
$txt[321] = 'Neue Mitteilung';
$txt[411] = 'L&ouml;sche Mitteilung';
// Don't translate "PMBOX" in this string.
$txt[412] = 'Alle Mitteilungen in Ihrer PMBOX l&ouml;schen';
$txt[413] = 'Sind Sie sicher, dass Sie alle Mitteilungen l&ouml;schen wollen?';
$txt[535] = 'Empf&auml;nger';
// Don't translate the word "SUBJECT" here, as it is used to format the message - use numeric entities as well.
$txt[561] = 'Neue Private Mitteilung: SUBJECT';
// Don't translate SENDER or MESSAGE in this language string; they are replaced with the corresponding text - use numeric entities too.
$txt[562] = 'Sie haben eine Private Mitteilung von SENDER im Forum ' . $context['forum_name'] . ' erhalten.\\n\\nWICHTIG: Das ist nur eine Benachrichtigung - bitte antworten Sie nicht auf diese E-Mail!\\n\\nDie Nachricht, die an Sie gesendet wurde:\\n\\nMESSAGE';
$txt[748] = '(mehrere Empf&auml;nger als \'username1, username2\')';
// Use numeric entities in the below string.
$txt['instant_reply'] = 'Auf diese Mitteilung antworten:';

$txt['smf249'] = 'Sind Sie sicher, dass Sie die ausgew&auml;hlten Privaten Mitteilungen l&ouml;schen m&ouml;chten?';

$txt['sent_to'] = 'Senden an';
$txt['reply_to_all'] = 'Allen antworten';

$txt['pm_capacity'] = 'Kapazit&auml;t';
$txt['pm_currently_using'] = '%s Mitteilungen, %s%% voll.';

$txt['pm_error_user_not_found'] = 'Kann Mitglied \'%s\' nicht finden.';
$txt['pm_error_ignored_by_user'] = 'Mitglied \'%s\' hat Ihre Mitteilungen geblockt.';
$txt['pm_error_data_limit_reached'] = 'Mitteilung konnte wegen des max. Limit nicht an \'%s\' gesendet werden.';
$txt['pm_successfully_sent'] = 'Mitteilung erfolgreich an \'%s\' gesendet.';
$txt['pm_send_report'] = 'Report senden';
$txt['pm_save_outbox'] = 'Kopie im Ausgang speichern';
$txt['pm_undisclosed_recipients'] = 'Verdeckter Empf&auml;nger';

$txt['pm_read'] = 'Lesen';
$txt['pm_replied'] = 'Antwort an';

$txt['pm_prune'] = 'Mitteilungen bereinigen';
$txt['pm_prune_desc1'] = 'Alle Privaten Mitteilungen &auml;lter als';
$txt['pm_prune_desc2'] = 'Tage l&ouml;schen.';
$txt['pm_prune_warning'] = 'Sind Sie sicher, dass Sie Ihre Mitteilungen bereinigen m&ouml;chten?';

$txt['pm_actions_title'] = 'Weitere Funktionen';
$txt['pm_actions_delete_selected'] = 'Markierte entfernen';
$txt['pm_actions_filter_by_label'] = 'Nach Label filtern';
$txt['pm_actions_go'] = 'Los';

$txt['pm_apply'] = '&Uuml;bernehmen';
$txt['pm_manage_labels'] = 'Labels verwalten';
$txt['pm_labels_delete'] = 'Sind Sie sicher, dass Sie die ausgew&auml;hlten Labels l&ouml;schen m&ouml;chten?';
$txt['pm_labels_desc'] = 'Hier k&ouml;nnen Sie Labels zu Ihren Privaten Mitteilungen hinzuf&uuml;gen, editieren und l&ouml;schen.';
$txt['pm_label_add_new'] = 'Neues Label hinzuf&uuml;gen';
$txt['pm_label_name'] = 'Label Name';
$txt['pm_labels_no_exist'] = 'Sie haben noch keine Labels erstellt!';

$txt['pm_current_label'] = 'Label';
$txt['pm_msg_label_title'] = 'Mitteilung kennzeichnen';
$txt['pm_msg_label_apply'] = 'Label hinzuf&uuml;gen';
$txt['pm_msg_label_remove'] = 'Label entfernen';
$txt['pm_msg_label_inbox'] = 'Posteingang';
$txt['pm_sel_label_title'] = 'Ausgew&auml;hlte kennzeichnen';

$txt['pm_labels'] = 'Labels';
$txt['pm_messages'] = 'Mitteilungen';
// Untranslated!
$txt['pm_actions'] = 'Actions';
$txt['pm_preferences'] = 'Einstellungen';

$txt['pm_is_replied_to'] = 'Sie haben diese Mitteilung weitergeleitet oder schon darauf geantwortet.';

// Reporting messages.
$txt['pm_report_to_admin'] = 'Administrator informieren';
$txt['pm_report_title'] = 'Private Mitteilung melden';
$txt['pm_report_desc'] = 'Hier k&ouml;nnen Sie Private Mitteilungen den Administratoren des Forums melden. Bitte f&uuml;gen Sie eine kurze Beschreibung an, warum Sie diese Mitteilung melden m&ouml;chten. Die Beschreibung wird mit der Originalnachricht versendet.';
$txt['pm_report_admins'] = 'An folgenden Administrator melden';
$txt['pm_report_all_admins'] = 'An alle Administratoren melden';
$txt['pm_report_reason'] = 'Grund f&uuml;r die Meldung der Mitteilung';
$txt['pm_report_message'] = 'Mitteilung melden';

// Important - The following strings should use numeric entities.
$txt['pm_report_pm_subject'] = '[MELDUNG] ';
// In the below string, do not translate "{REPORTER}" or "{SENDER}".
$txt['pm_report_pm_user_sent'] = '{REPORTER} hat die untenstehende Mitteilung, die von {SENDER} gesendet wurde, mit folgendem Grund gemeldet:';
$txt['pm_report_pm_other_recipients'] = 'Andere Empf&#228;nger der Meldung:';
$txt['pm_report_pm_hidden'] = '%d versteckte Empf&#228;nger';
$txt['pm_report_pm_unedited_below'] = 'Der Originaltext der gemeldeten Mitteilung lautet wie folgt:';
$txt['pm_report_pm_sent'] = 'Gesendet:';

$txt['pm_report_done'] = 'Vielen Dank f&uuml;r das Melden der Mitteilung. Sie sollten in K&uuml;rze von den Administratoren eine Antwort erhalten.';
$txt['pm_report_return'] = 'Zur&uuml;ck zum Posteingang';

$txt['pm_search_title'] = 'Private Mitteilungen durchsuchen';
$txt['pm_search_bar_title'] = 'Private Mitteilungen durchsuchen';
$txt['pm_search_text'] = 'Suche nach';
$txt['pm_search_go'] = 'Suchen';
$txt['pm_search_advanced'] = 'Erweiterte Suche';
$txt['pm_search_user'] = 'nach Mitglied';
$txt['pm_search_match_all'] = '&Uuml;bereinstimmung aller W&ouml;rter';
$txt['pm_search_match_any'] = '&Uuml;bereinstimmung eines Wortes';
$txt['pm_search_options'] = 'Optionen';
$txt['pm_search_post_age'] = 'Alter';
$txt['pm_search_show_complete'] = 'Zeige vollst&auml;ndige Mitteilung in Suchergebnis.';
$txt['pm_search_subject_only'] = 'Suche nur nach Betreff und Autor.';
$txt['pm_search_between'] = 'Zwischen';
$txt['pm_search_between_and'] = 'und';
$txt['pm_search_between_days'] = 'Tagen';
$txt['pm_search_order'] = 'Ergebnisse sortieren nach';
$txt['pm_search_choose_label'] = 'Labels zum Suchen ausw&auml;hlen oder alle durchsuchen';

$txt['pm_search_results'] = 'Suchresultate';
$txt['pm_search_none_found'] = 'Keine Mitteilungen gefunden';

$txt['pm_search_orderby_relevant_first'] = 'Gr&ouml;&szlig;te Relevanz zuerst';
$txt['pm_search_orderby_recent_first'] = 'Neueste Mitteilungen zuerst';
$txt['pm_search_orderby_old_first'] = '&Auml;lteste Mitteilungen zuerst';

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

$txt[325] = 'Liste der ignorierten Benutzer';
$txt[326] = 'F&uuml;gen Sie einen Benutzername pro Zeile hinzu oder geben Sie * ein, um alle Mitteilungen zu ignorieren.';
$txt[327] = 'Eine E-Mail senden, wenn Sie Private Mitteilungen erhalten?';
$txt['email_notify_never'] = 'Nie';
$txt['email_notify_buddies'] = 'Nur von Buddies';
$txt['email_notify_always'] = 'Immer';

$txt['copy_to_outbox'] = 'Eine Kopie jeder Privaten Mitteilung im Ausgang ablegen?';
$txt['popup_messages'] = 'PopUp Fenster anzeigen, wenn Sie neue Private Mitteilungen erhalten?';
//!!! Untranslated
$txt['pm_remove_inbox_label'] = 'Remove the inbox label when applying another label';

//!!! Untranslated
$txt['pm_manage_rules'] = 'Manage Rules';
$txt['pm_manage_rules_desc'] = 'Message rules allow you to automatically sort incoming messages dependant on a set of criteria you define. Below are all the rules you currently have setup. To edit a rule simply click the rule name.';
$txt['pm_rules_none'] = 'You have not yet setup any message rules.';
$txt['pm_rule_title'] = 'Rule';
$txt['pm_add_rule'] = 'Add New Rule';
$txt['pm_edit_rule'] = 'Edit Rule';
$txt['pm_rule_save'] = 'Save Rule';
$txt['pm_delete_selected_rule'] = 'Delete Selected Rules';
// Use entities in the below string.
$txt['pm_js_delete_rule_confirm'] = 'Are you sure you wish to delete the selected rules?';
$txt['pm_rule_name'] = 'Name';
$txt['pm_rule_name_desc'] = 'Name to remember this rule by';
$txt['pm_rule_description'] = 'Description';
$txt['pm_rule_not_defined'] = 'Add some criteria to begin building this rule description.';
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