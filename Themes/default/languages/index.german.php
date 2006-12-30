<?php
// Version: 2.0 Alpha; index

/* Important note about language files in SMF 2.0 upwards:
	1) All language entries in SMF 2.0 are cached. All edits should therefore be made through the admin menu. If you do
	   edit a language file manually you will not see the changes in SMF until the cache refreshes. To manually refresh
	   the cache go to Admin => Maintenance => Clean Cache.

	2) Please also note that strings should use single quotes, not double quotes for enclosing the string
	   except for line breaks.

*/

global $forum_copyright, $forum_version, $webmaster_email;

// Locale (strftime, pspell_new) and spelling. (pspell_new, can be left as '' normally.)
// For more information see:
//   - http://www.php.net/function.pspell-new
//   - http://www.php.net/function.setlocale
// Again, SPELLING SHOULD BE '' 99% OF THE TIME!!  Please read this!
$txt['lang_locale'] = 'de_DE';
$txt['lang_dictionary'] = 'de';
$txt['lang_spelling'] = '';

// Character set and right to left?
$txt['lang_character_set'] = 'ISO-8859-1';
$txt['lang_rtl'] = false;

$txt['days'] = array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');
$txt['days_short'] = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');
// Months must start with 1 => 'January'. (or translated, of course.)
$txt['months'] = array(1 => 'Januar', 'Februar', 'M&auml;rz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
$txt['months_titles'] = array(1 => 'Januar', 'Februar', 'M&auml;rz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
$txt['months_short'] = array(1 => 'Jan', 'Feb', 'M&auml;r', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez');

$txt['newmessages0'] = 'ist neu';
$txt['newmessages1'] = 'sind neu';
$txt['newmessages3'] = 'Neu';
$txt['newmessages4'] = ',';

$txt['admin'] = 'Administrator';
// Untranslated!
$txt['moderate'] = 'Moderate';

$txt['save'] = 'Speichern';

$txt['modify'] = '&Auml;ndern';
$txt['forum_index'] = '%1$s - Index';
$txt['members'] = 'Mitglieder';
$txt['board_name'] = 'Boardname';
$txt['posts'] = 'Beitr&auml;ge';

$txt['no_subject'] = '(Kein Betreff)';
$txt['view_profile'] = 'Profil anzeigen';
$txt['guest_title'] = 'Gast';
$txt['author'] = 'Autor';
$txt['on'] = 'am';
$txt['remove'] = 'L&ouml;schen';
$txt['start_new_topic'] = 'Neues Thema starten';

$txt['login'] = 'Login';
// Use numeric entities in the below string.
$txt['username'] = 'Benutzername';
$txt['password'] = 'Passwort';

$txt['username_no_exist'] = 'Benutzername nicht vorhanden.';

$txt['board_moderator'] = 'Moderator';
$txt['remove_topic'] = 'Thema l&ouml;schen';
$txt['topics'] = 'Themen';
$txt['modify_msg'] = 'Beitrag &auml;ndern';
$txt['name'] = 'Name';
$txt['email'] = 'E-Mail';
$txt['subject'] = 'Betreff';
$txt['message'] = 'Text';

$txt['profile'] = 'Profil';

$txt['choose_pass'] = 'Passwort w&auml;hlen';
$txt['verify_pass'] = 'Passwort wiederholen';
$txt['position'] = 'Position';

$txt['profile_of'] = 'Profil anzeigen von';
$txt['total'] = 'Alle';
$txt['posts_made'] = 'Beitr&auml;ge';
$txt['website'] = 'Webseite';
$txt['register'] = 'Registrieren';

$txt['message_index'] = 'Themen-Index';
$txt['news'] = 'News';
$txt['home'] = '&Uuml;bersicht';

$txt['lock_unlock'] = 'Thema schlie&szlig;en/&ouml;ffnen';
$txt['post'] = 'Schreiben';
$txt['error_occured'] = 'Ein Fehler ist aufgetreten!';
$txt['at'] = 'von';
$txt['logout'] = 'Ausloggen';
$txt['started_by'] = 'Begonnen von';
$txt['replies'] = 'Antworten';
$txt['last_post'] = 'Letzter Beitrag';
$txt['admin_login'] = 'Administrator Login';
// Use numeric entities in the below string.
$txt['topic'] = 'Thema';
$txt['help'] = 'Hilfe';
$txt['remove_message'] = 'Beitrag l&ouml;schen';
$txt['notify'] = 'Benachrichtigen';
$txt['notify_request'] = 'M&ouml;chten Sie eine Benachrichtigung per E-Mail, wenn eine Antwort zu diesem Thema geschrieben wird?';
$txt['regards_team'] = 'Lieben Gru&#223;,' . "\n" . 'das %1$s Team.';
// Use numeric entities in the below string.
$txt['notify_replies'] = '&#220;ber Antworten benachrichtigen';
$txt['move_topic'] = 'Thema verschieben';
$txt['move_to'] = 'Verschieben nach';
$txt['pages'] = 'Seiten';
$txt['users_active'] = 'Aktive Benutzer in den letzten %1$d Minuten';
$txt['personal_messages'] = 'Private Mitteilungen';
$txt['reply_quote'] = 'Zitieren';
$txt['reply'] = 'Antwort';
// Untranslated!
$txt['approve'] = 'Approve';
$txt['approve_all'] = 'approve all';
$txt['attach_awaiting_approve'] = 'Attachments awaiting approval';

$txt['msg_alert_none'] = 'Keine Nachrichten...';
$txt['msg_alert_you_have'] = 'Sie haben';
$txt['msg_alert_messages'] = 'Nachrichten';
$txt['remove_message'] = 'Nachricht l&ouml;schen';

$txt['online_users'] = 'Benutzer Online';
$txt['personal_message'] = 'Private Mitteilung';
$txt['jump_to'] = 'Gehe zu';
$txt['go'] = 'Los';
$txt['are_sure_remove_topic'] = 'Sind Sie sicher, dass Sie dieses Thema l&ouml;schen wollen?';
$txt['yes'] = 'Ja';
$txt['no'] = 'Nein';

$txt['search_results'] = 'Suchergebnisse';
$txt['search_end_results'] = 'Ende der Ergebnisse';
$txt['search_no_results'] = 'Keine &Uuml;bereinstimmungen gefunden';
$txt['search_on'] = 'am';

$txt['search'] = 'Suche';
$txt['all'] = 'Alle';

$txt['back'] = 'Zur&uuml;ck';
$txt['password_reminder'] = 'Passwort erinnern';
$txt['topic_started'] = 'Thema gestartet von';
$txt['title'] = 'Titel';
$txt['post_by'] = 'Beitrag von';
$txt['memberlist_searchable'] = 'Liste aller registrierten Mitglieder';
$txt['welcome_member'] = 'Herzlich Willkommen';
$txt['admin_center'] = 'Administrator-Center';
$txt['last_edit'] = 'Letzte &Auml;nderung';
$txt['notify_deactivate'] = 'M&ouml;chten Sie die E-Mail Benachrichtigung zu diesem Thema deaktivieren?';

$txt['recent_posts'] = 'Neueste Beitr&auml;ge';

$txt['location'] = 'Ort';
$txt['gender'] = 'Geschlecht';
$txt['date_registered'] = 'Registrierungsdatum';

$txt['recent_view'] = 'Anzeigen der neuesten Beitr&auml;ge';
$txt['recent_updated'] = 'ist das zuletzt ge&auml;nderte Thema';

$txt['male'] = 'M&auml;nnlich';
$txt['female'] = 'Weiblich';

$txt['error_invalid_characters_username'] = 'Ung&uuml;ltiges Zeichen im Benutzernamen.';

$txt['welcome_guest'] = 'Willkommen <b>%1$s</b>. Bitte <a href="' . $scripturl . '?action=login">einloggen</a> oder <a href="' . $scripturl . '?action=register">registrieren</a>.';
$txt['welcome_guest_activate'] = '<br />Haben Sie Ihre <a href="' . $scripturl . '?action=activate">Aktivierungs E-Mail</a> &uuml;bersehen?';
$txt['hello_member'] = 'Hallo';
// Use numeric entities in the below string.
$txt['hello_guest'] = 'Willkommen';
$txt['welmsg_hey'] = 'Hallo';
$txt['welmsg_welcome'] = 'Willkommen';
$txt['welmsg_please'] = 'Bitte';
$txt['select_destination'] = 'Bitte w&auml;hlen Sie ein Ziel';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['posted_by'] = 'Autor';

$txt['icon_smiley'] = 'Smiley';
$txt['icon_angry'] = '&Auml;rgerlich';
$txt['icon_cheesy'] = 'L&auml;chelnd';
$txt['icon_laugh'] = 'Lachend';
$txt['icon_sad'] = 'Traurig';
$txt['icon_wink'] = 'Zwinkernd';
$txt['icon_grin'] = 'Grinsend';
$txt['icon_shocked'] = 'Schockiert';
$txt['icon_cool'] = 'Cool';
$txt['icon_huh'] = 'Huch';
$txt['icon_rolleyes'] = 'Augen rollen';
$txt['icon_tongue'] = 'Zunge';
$txt['icon_embarrassed'] = 'Verlegen';
$txt['icon_lips'] = 'Schweigend';
$txt['icon_undecided'] = 'Unentschlossen';
$txt['icon_kiss'] = 'K&uuml;sschen';
$txt['icon_cry'] = 'Weinen';

$txt['moderator'] = 'Moderator';
$txt['moderators'] = 'Moderatoren';

$txt['mark_board_read'] = 'Alle Themen im Board als gelesen markieren';
$txt['views'] = 'Aufrufe';
$txt['new'] = 'Neu';

$txt['view_all_members'] = 'Mitglieder anzeigen';
$txt['view'] = 'Anzeigen';
$txt['email'] = 'E-Mail';

// Untranslated!
$txt['viewing_members'] = 'Viewing Members %1$s to %2$s';
$txt['of_total_members'] = 'of %1$s total members';

$txt['forgot_your_password'] = 'Passwort vergessen?';

$txt['date'] = 'Datum';
// Use numeric entities in the below string.
$txt['from'] = 'Von';
$txt['subject'] = 'Betreff';
$txt['check_new_messages'] = 'Neue Nachrichten abholen';
$txt['to'] = 'An';

$txt[330] = 'Themen';
$txt['members'] = 'Mitglieder';
$txt['members_list'] = 'Mitgliederliste';
$txt['new_posts'] = 'Neue Beitr&auml;ge';
$txt['old_posts'] = 'Keine neuen Beitr&auml;ge';

$txt['sendtopic_send'] = 'Senden';

$txt['time_offset'] = 'Zeitverschiebung';
$txt['or'] = 'oder';

$txt['no_matches'] = 'Keine &Uuml;bereinstimmungen gefunden';

$txt['notification'] = 'Benachrichtigung';

$txt['your_ban'] = '%s, Sie sind aus diesem Forum verbannt!';
// !!! Untranslated
$txt['your_ban_expires'] = 'Your ban is set to expire %s';
$txt['your_ban_expires_never'] = 'Your ban is not set to expire.';

$txt['mark_as_read'] = 'Alle Beitr&auml;ge als gelesen markieren';

$txt['hot_topics'] = 'Hei&szlig;es Thema (mehr als %1$d Antworten)';
$txt['very_hot_topics'] = 'Sehr hei&szlig;es Thema (mehr als %1$d Antworten)';
$txt['locked_topic'] = 'Thema geschlossen';
$txt['normal_topic'] = 'Normales Thema';
$txt['participation_caption'] = 'Themen auf die Sie geantwortet haben';

$txt['go_caps'] = 'Los';

$txt['print'] = 'Drucken';
$txt['profile'] = 'Profil';
$txt['topic_summary'] = 'Zusammenfassung';
$txt['not_applicable'] = 'Nicht verf&uuml;gbar';
$txt[471] = 'Nachricht';
$txt['name_in_use'] = 'Dieser Name ist bereits in Verwendung.';

$txt['total_members'] = 'Mitglieder insgesamt';
$txt['total_posts'] = 'Beitr&auml;ge insgesamt';
$txt['total_topics'] = 'Themen insgesamt';

$txt['mins_logged_in'] = 'Sitzungsl&auml;nge in Minuten';

$txt['preview'] = 'Vorschau';
$txt['always_logged_in'] = 'Immer eingeloggt bleiben';

$txt['logged'] = 'Gespeichert';
// Use numeric entities in the below string.
$txt['ip'] = 'IP';

$txt['icq'] = 'ICQ';
$txt['www'] = 'WWW';

$txt['by'] = 'von';

$txt['hours'] = 'Stunden';
$txt['days_word'] = 'Tage';

$txt['newest_member'] = ', unser neuestes Mitglied.';

$txt['search_for'] = 'Suchen nach';

$txt['aim'] = 'AIM';
// In this string, please use +'s for spaces.
$txt['aim_default_message'] = 'Hallo.+Sind+Sie+online?';
$txt['yim'] = 'YIM';

$txt['maintain_mode_on'] = 'Nicht vergessen, das Forum ist im \'Wartungsmodus\'!';

$txt['read'] = 'Gelesen';
$txt['times'] = 'mal';

$txt['forum_stats'] = 'Forum-Statistiken';
$txt['latest_member'] = 'Neuestes Mitglied';
$txt['total_cats'] = 'Kategorien insgesamt';
$txt['latest_post'] = 'Letzter Beitrag';

$txt['you_have'] = 'Sie haben';
$txt['click'] = 'Klicken Sie';
$txt['here'] = 'hier';
$txt['to_view'] = 'um sie zu sehen.';

$txt['total_boards'] = 'Boards insgesamt';

$txt['print_page'] = 'Seite drucken';

$txt['valid_email'] = 'Es muss eine g&uuml;ltige E-Mail Adresse sein.';

$txt['geek'] = 'Ich bin ein Freak!!';
$txt['info_center_title'] = '%s - Info-Center';

$txt['send_topic'] = 'Senden Sie dieses Thema';

$txt['sendtopic_title'] = 'Senden Sie das Thema &quot;%s&quot; einem Freund.';
// Use numeric entities in the below three strings.
$txt['sendtopic_dear'] = 'Hallo %s,';
$txt['sendtopic_this_topic'] = 'Sehen Sie sich bitte folgendes Thema an: %s, am %s. Klicken Sie dazu auf den Link';
$txt['sendtopic_thanks'] = 'Danke';
$txt['sendtopic_sender_name'] = 'Ihr Name';
$txt['sendtopic_sender_email'] = 'Ihre E-Mail Adresse';
$txt['sendtopic_receiver_name'] = 'Name des Empf&auml;ngers';
$txt['sendtopic_receiver_email'] = 'E-Mail Adresse des Empf&auml;ngers';
$txt['sendtopic_comment'] = 'Kommentar hinzuf&uuml;gen';
// Use numeric entities in the below string.
$txt['sendtopic2'] = 'Ein Kommentar wurde zu diesem Thema hinzugef&#252;gt';

$txt['hide_email'] = 'E-Mail Adresse nicht anzeigen (empfohlen)?';

$txt['check_all'] = 'Alle markieren';

// Use numeric entities in the below string.
$txt['database_error'] = 'Datenbankfehler';
$txt['try_again'] = 'Bitte versuchen Sie es nochmal. Sollte der Fehler wieder auftreten, informieren Sie bitte den Administrator.';
$txt['file'] = 'Datei';
$txt['line'] = 'Zeile';
// Use numeric entities in the below string.
$txt['tried_to_repair'] = 'SMF hat einen Datenbankfehler entdeckt und versucht ihn automatisch zu reparieren. Wenn Sie erneut Probleme haben sollten oder weiterhin diese E-Mails erhalten, kontaktieren Sie bitte Ihren Serveranbieter.';
$txt['database_error_versions'] = '<b>Achtung:</b> Ihre Datenbank scheint veraltet zu sein! Ihre Dateien haben die Version %s, wogegen die Datenbank die Version %s hat. Es wird dringend empfohlen, die neueste Version der upgrade.php auszuführen.';
$txt['template_parse_error'] = 'Template Parse Error!';
$txt['template_parse_error_message'] = 'Ein Fehler ist im Templatesystem des Forums aufgetreten! Dieses Problem sollte nur tempor&auml;r auftreten, bitte versuchen Sie es sp&auml;ter nochmal. Sollten Sie die Fehlermeldung weiterhin erhalten, kontaktieren Sie bitte den Administrator.<br /><br />Sie k&ouml;nnen versuchen die Seite zu <a href="javascript:location.reload();">aktualisieren</a>.';
$txt['template_parse_error_details'] = 'Ein Problem trat beim Laden des <tt><b>%1$s</b></tt> Templates oder der Sprachdatei auf. Bitte &uuml;berpr&uuml;fen Sie die Syntax und probieren es erneut. Bitte beachten Sie, dass einzelne Anf&uuml;hrungszeichen (<tt>\'</tt>) oft mit einem Slash (<tt>\\</tt>) auskommentiert werden m&uuml;ssen. Um n&auml;here Informationen von PHP zum Fehler zu erhalten, probieren Sie <a href="' . $boardurl . '%1$s">die Seite direkt aufzurufen</a>.<br /><br />Sie k&ouml;nnen auch versuchen, die Seite zu <a href="javascript:location.reload();">aktualisieren</a> oder das <a href="' . $scripturl . '?theme=1">Standard-Theme</a> zu benutzen.';

$txt['today'] = '<b>Heute</b> um ';
$txt['yesterday'] = '<b>Gestern</b> um ';
$txt['new_poll'] = 'Neue Umfrage';
$txt['poll_question'] = 'Frage';
$txt['poll_vote'] = 'Abstimmen';
$txt['poll_total_voters'] = 'Stimmen insgesamt';
$txt['shortcuts'] = 'Shortcuts: Alt+S f&uuml;r das Absenden oder Alt+P f&uuml;r die Vorschau';
$txt['poll_results'] = 'Ergebnisse anzeigen';
$txt['poll_lock'] = 'Umfrage schlie&szlig;en';
$txt['poll_unlock'] = 'Umfrage &ouml;ffnen';
$txt['poll_edit'] = 'Umfrage editieren';
$txt['poll'] = 'Umfrage';
$txt['one_day'] = '1 Tag';
$txt['one_week'] = '1 Woche';
$txt['one_month'] = '1 Monat';
$txt['forever'] = 'Immer';
$txt['quick_login_dec'] = 'Einloggen mit Benutzername, Passwort und Sitzungsl&auml;nge';
$txt['one_hour'] = '1 Stunde';
$txt['moved'] = 'VERSCHOBEN';
$txt['moved_why'] = 'Bitte geben Sie einen kurzen Hinweis ein, <br />warum das Thema verschoben wird.';
$txt['board'] = 'Board';
$txt['in'] = 'in';
$txt['smf96'] = 'Top Thema';

$txt['delete'] = 'L&ouml;schen';

$txt['your_pms'] = 'Ihre Privaten Mitteilungen';

$txt['kilobyte'] = 'KB';

$txt['more_stats'] = '[Weitere Statistiken]';

// Use numeric entities in the below three strings.
$txt['code'] = 'Code';
$txt['quote_from'] = 'Zitat von';
$txt['quote'] = 'Zitat';

// Untranslated
$txt['merge_to_topic_id'] = 'ID of target topic';
$txt['split'] = 'Thema teilen';
$txt['merge'] = 'Themen zusammenf&uuml;hren';
$txt['subject_new_topic'] = 'Betreff f&uuml;r das neue Thema';
$txt['split_this_post'] = 'Nur diesen Beitrag trennen.';
$txt['split_after_and_this_post'] = 'Thema bis und inkl. diesem Beitrag aufteilen.';
$txt['select_split_posts'] = 'Beitr&auml;ge ausw&auml;hlen, welche geteilt werden sollen.';
$txt['new_topic'] = 'Neues Thema';
$txt['split_successful'] = 'Thema erfolgreich in zwei Themen aufgeteilt.';
$txt['origin_topic'] = 'Urspr&uuml;ngliches Thema';
$txt['please_select_split'] = 'Bitte w&auml;hlen Sie die Beitr&auml;ge aus, die Sie trennen m&ouml;chten.';
$txt['merge_successful'] = 'Themen erfolgreich zusammengef&uuml;hrt.';
$txt['new_merged_topic'] = 'Neu zusammengef&uuml;hrtes Thema';
$txt['topic_to_merge'] = 'Thema, welches zusammengef&uuml;hrt werden soll';
$txt['target_board'] = 'Ziel-Board';
$txt['target_topic'] = 'Ziel-Thema';
$txt['merge_confirm'] = 'Sind Sie sicher, dass Sie folgende Themen zusammenf&uuml;hren m&ouml;chten';
$txt['with'] = 'mit';
$txt['merge_desc'] = 'Diese Funktion wird die Beitr&auml;ge von zwei Themen zu einem Thema zusammenf&uuml;hren. Die Beitr&auml;ge werden zeitlich sortiert sein, d.h. der &auml;lteste Beitrag wird der erste im zusammengef&uuml;hrten Thema sein.';

$txt['set_sticky'] = 'Thema fixieren';
$txt['set_nonsticky'] = 'Fixierung des Themas entfernen';
$txt['set_lock'] = 'Thema schlie&szlig;en';
$txt['set_unlock'] = 'Thema &ouml;ffnen';

$txt['search_advanced'] = 'Erweiterte Suche';

$txt['security_risk'] = 'GROSSES SICHERHEITSRISIKO:';
$txt['not_removed'] = 'Sie haben folgende Datei(en) nicht gel&ouml;scht: ';

// Untranslated!
$txt['cache_writable_head'] = 'Performance Warning';
$txt['cache_writable'] = 'The cache directory is not writable - this will adversely affect the performance of your forum.';

$txt['page_created'] = 'Seite erstellt in ';
$txt['seconds_with'] = ' Sekunden mit ';
$txt['queries'] = ' Zugriffen.';

$txt['report_to_mod_func'] = 'Benutzen Sie diese Funktion, um Moderatoren/Administratoren &uuml;ber einen missbr&auml;uchlich oder falsch geschriebenen Beitrag zu informieren.<br /><i>Bitte beachten Sie, dass Ihre E-Mail Adresse zum betreffenden Moderator gesendet wird, wenn Sie diese Funktion benutzen.</i>';

$txt['online'] = 'Online';
$txt['offline'] = 'Offline';
$txt['pm_online'] = 'Private Mitteilung (Online)';
$txt['pm_offline'] = 'Private Mitteilung (Offline)';
$txt['status'] = 'Status';

$txt['go_up'] = 'Nach oben';
$txt['go_down'] = 'Nach unten';

$forum_copyright = '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by %s</a> | 
<a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy; 2001-2006, Lewis Media</a>';

$txt['birthdays'] = 'Geburtstage:';
$txt['events'] = 'Ereignisse:';
$txt['birthdays_upcoming'] = 'Zuk&uuml;nftige Geburtstage:';
$txt['events_upcoming'] = 'Zuk&uuml;nftige Ereignisse:';
// Prompt for holidays in the calendar, leave blank to just display the holiday's name.
$txt['calendar5'] = '';
$txt['calendar_month'] = 'Monat:';
$txt['calendar_year'] = 'Jahr:';
$txt['calendar_day'] = 'Tag:';
$txt['calendar_event_title'] = 'Ereignis-Titel:';
$txt['calendar_post_in'] = 'Erstellen in:';
$txt['calendar_edit'] = 'Ereignis editieren';
$txt['calendar21'] = 'Dieses Ereignis löschen?';
$txt['calendar22'] = 'L&ouml;sche Ereignis';
$txt['calendar_post_event'] = 'Erstelle Ereignis';
$txt['calendar'] = 'Kalender';
$txt['calendar_link'] = 'Link zum Kalender';
$txt['calendar_upcoming'] = 'Ereignis verlinken';
$txt['calendar47'] = 'Zuk&uuml;nftige Ereignisse';
$txt['calendar_today'] = 'Heutige Ereignisse';
$txt['calendar_week'] = 'Woche';
$txt['calendar_numb_days'] = 'Anzahl der Tage:';
$txt['calendar_how_edit'] = 'Wie &auml;ndert man diese Ereignisse?';
$txt['calendar_link_event'] = 'Ereignis zum Beitrag verlinken:';
$txt['calendar_confirm_delete'] = 'Sind Sie sicher, dass Sie dieses Ereignis l&ouml;schen m&ouml;chten?';
$txt['calendar_linked_events'] = 'Verlinkte Ereignisse';

$txt['moveTopic1'] = 'Einen Umleitungshinweis angeben';
$txt['moveTopic2'] = 'Titel des Themas &auml;ndern';
$txt['moveTopic3'] = 'Neuer Titel';
$txt['moveTopic4'] = 'Titel jedes Themas &auml;ndern';

$txt['theme_template_error'] = 'Kann das \'%s\' Template nicht laden.';
$txt['theme_language_error'] = 'Kann die \'%s\' Sprachdatei nicht laden.';

$txt['parent_boards'] = 'Untergeordnete Boards';

$txt['smtp_no_connect'] = 'Kann nicht zu SMTP Server verbinden';
$txt['smtp_port_ssl'] = 'SMTP Port Einstellung ist nicht korrekt; sie sollte auf 465 f&uuml;r SSL Server stehen.';
$txt['smtp_bad_response'] = 'Konnte Antwortcodes des E-Mail-Servers nicht empfangen';
$txt['smtp_error'] = 'Probleme beim Versenden der E-Mail. Fehler: ';
$txt['mail_send_unable'] = 'Die E-Mail konnte nicht an \'%s\' versendet werden.';

$txt['mlist_search'] = 'Mitglieder suchen';
$txt['mlist_search_again'] = 'Erneut suchen';
$txt['mlist_search_email'] = 'Nach E-Mail Adresse suchen';
$txt['mlist_search_messenger'] = 'Nach Messenger Spitzname suchen';
$txt['mlist_search_group'] = 'Nach Position suchen';
$txt['mlist_search_name'] = 'Nach Namen suchen';
$txt['mlist_search_website'] = 'Nach Webseite suchen';
$txt['mlist_search_results'] = 'Suchergebnisse f&uuml;r';

$txt['attach_downloaded'] = 'runtergeladen';
$txt['attach_viewed'] = 'angeschaut';
$txt['attach_times'] = 'Mal';

$txt['msn'] = 'MSN';

$txt['settings'] = 'Einstellungen';
$txt['never'] = 'Nie';
$txt['more'] = 'mehr';

$txt['hostname'] = 'Hostname';
$txt['you_are_post_banned'] = 'Entschuldigung %s, Ihnen ist das Schreiben in diesem Forum verboten worden.';
$txt['ban_reason'] = 'Grund';

$txt['tables_optimized'] = 'Tabellen der Datenbank optimiert';

$txt['add_poll'] = 'Umfrage hinzuf&uuml;gen';
$txt['poll_options6'] = 'Sie d&uuml;rfen nur %s Optionen w&auml;hlen.';
$txt['poll_remove'] = 'Umfrage entfernen';
$txt['poll_remove_warn'] = 'Sind Sie sicher, dass Sie die Umfrage vom Thema entfernen m&ouml;chten?';
$txt['poll_results_expire'] = 'Die Resultate werden angezeigt, wenn die Umfrage geschlossen wird';
$txt['poll_expires_on'] = 'Umfrage schlie&szlig;t';
$txt['poll_expired_on'] = 'Umfrage geschlossen';
$txt['poll_change_vote'] = 'Abstimmung &auml;ndern';
$txt['poll_return_vote'] = 'Abstimmungsoptionen';

// Untranslated!
$txt['quick_mod_approve'] = 'Approve selected';
$txt['quick_mod_remove'] = 'Markierte entfernen';
$txt['quick_mod_lock'] = 'Markierte &ouml;ffnen/schlie&szlig;en';
$txt['quick_mod_sticky'] = 'Markierte fixieren';
$txt['quick_mod_move'] = 'Markierte verschieben nach';
$txt['quick_mod_merge'] = 'Markierte zusammenf&uuml;hren';
$txt['quick_mod_markread'] = 'Markierte als gelesen kennzeichnen';
$txt['quick_mod_go'] = 'Los';
$txt['quickmod_confirm'] = 'Sind Sie sicher, dass Sie das tun wollen?';

$txt['spell_check'] = 'Rechtschreibung pr&uuml;fen';

$txt['quick_reply'] = 'Schnellantwort';
$txt['quick_reply_desc'] = 'Bei der <i>Schnellantwort</i> k&ouml;nnen Sie Bulletin Board Code und Smileys wie im normalen Beitrag benutzen.';
$txt['quick_reply_warning'] = 'Warnung: Das Thema ist momentan geschlossen!<br />Nur Administratoren und Moderatoren k&ouml;nnen antworten.';
// Untranslated!
$txt['wait_for_approval'] = 'Note: this post will not display until it\'s been approved by a moderator.';

$txt['notification_enable_board'] = 'Sind Sie sicher, dass Sie Benachrichtigungen über neue Themen in diesem Board aktivieren m&ouml;chten?';
$txt['notification_disable_board'] = 'Sind Sie sicher, dass Sie Benachrichtigungen über neue Themen in diesem Board deaktivieren m&ouml;chten?';
$txt['notification_enable_topic'] = 'Sind Sie sicher, dass Sie Benachrichtigungen &uuml;ber neue Beitr&auml;ge in diesem Thema aktivieren m&ouml;chten?';
$txt['notification_disable_topic'] = 'Sind Sie sicher, dass Sie Benachrichtigungen &uuml;ber neue Beitr&auml;ge in diesem Thema deaktivieren m&ouml;chten?';

$txt['report_to_mod'] = 'Moderator informieren';

$txt['unread_topics_visit'] = 'Neue ungelesene Themen';
$txt['unread_topics_visit_none'] = 'Keine ungelesenen Themen seit dem letzten Besuch gefunden. <a href="' . $scripturl . '?action=unread;all">Klicken Sie hier, um alle ungelesenen Themen zu suchen.</a>.';
$txt['unread_topics_all'] = 'Alle ungelesenen Themen';
$txt['unread_replies'] = 'Aktualisierte Themen';

$txt['who_title'] = 'Wer ist online';
$txt['who_and'] = ' und ';
$txt['who_viewing_topic'] = ' betrachten dieses Thema.';
$txt['who_viewing_board'] = ' betrachten dieses Board.';
$txt['who_member'] = 'Mitglieder';

$txt['powered_by_php'] = 'Powered by PHP';
$txt['powered_by_mysql'] = 'Powered by MySQL';
$txt['valid_html'] = 'Pr&uuml;fe HTML 4.01';
$txt['valid_xhtml'] = 'Pr&uuml;fe XHTML 1.0';
$txt['valid_css'] = 'Pr&uuml;fe CSS';

$txt['guest'] = 'Gast';
$txt['guests'] = 'G&auml;ste';
$txt['user'] = 'Mitglied';
$txt['users'] = 'Mitglieder';
$txt['hidden'] = 'Versteckte';
$txt['buddy'] = 'Buddy';
$txt['buddies'] = 'Buddies';
$txt['most_online_ever'] = 'Am meisten online (gesamt)';
$txt['most_online_today'] = 'Am meisten online (heute)';

$txt['merge_select_target_board'] = 'W&auml;hlen Sie das Ziel-Board des zusammengef&uuml;hrten Themas';
$txt['merge_select_poll'] = 'W&auml;hlen Sie die Umfrage, welche das zusammengf&uuml;hrte Thema haben soll';
$txt['merge_topic_list'] = 'W&auml;hlen Sie die Themen, die zusammengef&uuml;hrt werden sollen';
$txt['merge_select_subject'] = 'W&auml;hlen Sie den Titel des zusammengef&uuml;hrten Themas';
$txt['merge_custom_subject'] = 'Neuer Titel';
$txt['merge_enforce_subject'] = '&Auml;ndere Titel aller Beitr&auml;ge';
$txt['merge_include_notifications'] = 'Inklusive Benachrichtigungen?';
$txt['merge_check'] = 'Zusammenf&uuml;hren?';
$txt['merge_no_poll'] = 'Keine Umfrage';

$txt['response_prefix'] = 'Re: ';
$txt['current_icon'] = 'Aktuelles Symbol';

$txt['smileys_current'] = 'Aktuelles Smiley-Set';
$txt['smileys_none'] = 'Keine Smileys';
$txt['smileys_forum_board_default'] = 'Forum/Board Standard';

$txt['search_results'] = 'Suchergebnisse';
$txt['search_no_results'] = 'Keine Ergebnisse gefunden';

$txt['totalTimeLogged1'] = 'Insgesamt eingeloggt: ';
$txt['totalTimeLogged2'] = ' Tage, ';
$txt['totalTimeLogged3'] = ' Stunden und ';
$txt['totalTimeLogged4'] = ' Minuten';
$txt['totalTimeLogged5'] = 'T ';
$txt['totalTimeLogged6'] = 'S ';
$txt['totalTimeLogged7'] = 'M';

$txt['approve_thereis'] = 'Es gibt';
$txt['approve_thereare'] = 'Es gibt';
$txt['approve_member'] = 'ein Mitglied, ';
$txt['approve_members'] = 'Mitglieder, ';
$txt['approve_members_waiting'] = 'welche(s) eine Genehmigung erwarten/erwartet.';

$txt['notifyboard_turnon'] = 'M&ouml;chten Sie eine Benachrichtigungs E-Mail, wenn jemand ein neues Thema in diesem Board schreibt?';
$txt['notifyboard_turnoff'] = 'M&ouml;chten Sie keine Benachrichtigung mehr, wenn jemand ein neues Thema in diesem Board schreibt?';

$txt['activate_code'] = 'Ihr Aktivierungscode ist';

$txt['find_members'] = 'Suche Mitglieder';
$txt['find_username'] = 'Name, Benutzername oder E-Mail Adresse';
$txt['find_buddies'] = 'Nur die Buddies zeigen?';
$txt['find_wildcards'] = 'Wildcards erlauben: *,?';
$txt['find_no_results'] = 'Kein Ergebnis gefunden';
$txt['find_results'] = 'Ergebnis';
$txt['find_close'] = 'Schlie&szlig;en';

$txt['unread_since_visit'] = 'Ungelesene Beitr&auml;ge seit Ihrem letzten Besuch.';
$txt['show_unread_replies'] = 'Ungelesene Antworten zu Ihren Beitr&auml;gen.';

$txt['change_color'] = 'Farbe &auml;ndern';

$txt['quickmod_delete_selected'] = 'Ausgew&auml;hlte l&ouml;schen';

// In this string, don't use entities. (&amp;, etc.)
$txt['show_personal_messages'] = 'Sie haben eine oder mehrere neue Private Mitteilungen erhalten.' . "\n" . 'Möchten Sie diese lesen?';

$txt['previous_next_back'] = '&laquo; vorheriges';
$txt['previous_next_forward'] = 'n&auml;chstes &raquo;';

$txt['movetopic_auto_board'] = '[BOARD]';
$txt['movetopic_auto_topic'] = '[THEMEN LINK]';
$txt['movetopic_default'] = 'Dieses Thema wurde verschoben nach ' . $txt['movetopic_auto_board'] . ".\n\n" . $txt['movetopic_auto_topic'];

$txt['upshrink_description'] = 'Ein- oder Ausklappen der Kopfzeile';

$txt['mark_unread'] = 'Als ungelesen markieren';

$txt['ssi_not_direct'] = 'Bitte greifen Sie nicht direkt mit der URL auf die SSI.php zu. Benutzen Sie stattdessen den Pfad (%s) oder f&uuml;gen Sie ?ssi_function=irgendwas der URL hinzu.';
$txt['ssi_session_broken'] = 'SSI.php konnte die Sitzung nicht laden! Das kann zu Problemen mit dem Ausloggen und anderen Funktionen f&uuml;hren - bitte &uuml;berpr&uuml;fen Sie, ob SSI.php in Ihren Skripts vor jeglichem(!) anderen Code aufgerufen wird!';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['preview_title'] = 'Vorheriger Beitrag';
$txt['preview_fetch'] = 'Lade Vorschau...';
$txt['preview_new'] = 'Neue Nachricht';
$txt['error_while_submitting'] = 'Fehler beim Schreiben des Beitrages.';

$txt['split_selected_posts'] = 'Ausgew&auml;hlte Beitr&auml;ge';
$txt['split_selected_posts_desc'] = 'Die unten stehenden Beitr&auml;ge werden zusammen ein neues Thema bilden.';
$txt['split_reset_selection'] = 'Auswahl l&ouml;schen';

$txt['modify_cancel'] = 'Abbrechen';
$txt['mark_read_short'] = 'Alles gelesen';

// !!! Untranslated!
$txt['pm_short'] = 'My Messages';
$txt['hello_member_ndt'] = 'Hello';

// Untranslated!
$txt['unapproved_posts'] = 'Unapproved Posts (Topics: %d, Posts: %d)';
$txt['ajax_in_progress'] = 'Loading...';
$txt['mod_reports_waiting'] = 'There are currently %1$d moderator reports open.';
$txt['view_unread_category'] = 'Unread Posts';
?>
