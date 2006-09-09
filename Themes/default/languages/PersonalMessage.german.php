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
$txt[562] = 'Sie haben eine Private Mitteilung von SENDER im Forum {$context.forum_name} erhalten.\\n\\nWICHTIG: Das ist nur eine Benachrichtigung - bitte antworten Sie nicht auf diese E-Mail!\\n\\nDie Nachricht, die an Sie gesendet wurde:\\n\\nMESSAGE';
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
$txt['pm_too_many_recipients'] = 'Sie k&ouml;nnen keine Privaten Mitteilungen an mehr wie %d Empf&auml;nger gleichzeitig schicken.';
// Untranslated!
$txt['pm_too_many_per_hour'] = 'You have exceeded the limit of %d personal messages per hour.';
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
$txt['labels_too_many'] = '%s Mitteilungen haben schon die max. Anzahl an erlaubten Labels!';

$txt['pm_labels'] = 'Labels';
$txt['pm_messages'] = 'Mitteilungen';
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

?>