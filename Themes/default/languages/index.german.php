<?php
// Version: 2.0 Alpha; index

/* Important note about language files in SMF 2.0 upwards:
	1) All language entries in SMF 2.0 are cached. All edits should therefore be made through the admin menu. If you do
	   edit a language file manually you will not see the changes in SMF until the cache refreshes. To manually refresh
	   the cache go to Admin => Maintenance => Clean Cache.

	2) Unlike earlier versions of SMF the text in these files is not *pure* PHP. Variables are parsed out when cached
	   to make understanding language entries easier. As such please follow the following rules:

		a) All individual variables need not be escaped and should be written as {$varname}. i.e. $scripturl => {$scripturl}
		b) All array variables should have their index appended to the var name above with a dot.
			e.g. $modSettings['memberCount'] => {$modSettings.memberCount}
		c) All strings should use single quotes, not double quotes for enclosing the string.
		d) As a result of (c) all newline characters (etc) need to be escaped. i.e. "\n" is now '\\n'.

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
$txt['forum_index'] = '{$context.forum_name} - Index';
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
$txt['regards_team'] = "Lieben Gru&#223;,\ndas " . '{$context.forum_name} Team.';
// Use numeric entities in the below string.
$txt['notify_replies'] = '&#220;ber Antworten benachrichtigen';
$txt['move_topic'] = 'Thema verschieben';
$txt['move_to'] = 'Verschieben nach';
$txt['pages'] = 'Seiten';
$txt['users_active'] = 'Aktive Benutzer in den letzten {$modSettings.lastActive} Minuten';
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
$txt[167] = 'Ende der Ergebnisse';
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

$txt['welcome_guest'] = 'Willkommen <b>{$txt.guest_title}</b>. Bitte <a href="{$scripturl}?action=login">einloggen</a> oder <a href="{$scripturl}?action=register">registrieren</a>.';
$txt['welcome_guest_activate'] = '<br />Haben Sie Ihre <a href="{$scripturl}?action=activate">Aktivierungs E-Mail</a> &uuml;bersehen?';
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

$txt[298] = 'Moderator';
$txt[299] = 'Moderatoren';

$txt[300] = 'Alle Themen im Board als gelesen markieren';
$txt[301] = 'Aufrufe';
$txt[302] = 'Neu';

$txt[303] = 'Mitglieder anzeigen';
$txt[305] = 'Anzeigen';
$txt[307] = 'E-Mail';

$txt[308] = 'Mitglieder anzeigen';
$txt[309] = 'von';
$txt[310] = 'Mitglieder insgesamt';
$txt[311] = 'bis';
$txt[315] = 'Passwort vergessen?';

$txt[317] = 'Datum';
// Use numeric entities in the below string.
$txt[318] = 'Von';
$txt[319] = 'Betreff';
$txt[322] = 'Neue Nachrichten abholen';
$txt[324] = 'An';

$txt[330] = 'Themen';
$txt[331] = 'Mitglieder';
$txt[332] = 'Mitgliederliste';
$txt[333] = 'Neue Beitr&auml;ge';
$txt[334] = 'Keine neuen Beitr&auml;ge';

$txt['sendtopic_send'] = 'Senden';

$txt[371] = 'Zeitverschiebung';
$txt[377] = 'oder';

$txt[398] = 'Keine &Uuml;bereinstimmungen gefunden';

$txt[418] = 'Benachrichtigung';

$txt[430] = '%s, Sie sind aus diesem Forum verbannt!';
// !!! Untranslated
$txt['your_ban_expires'] = 'Your ban is set to expire %s';
$txt['your_ban_expires_never'] = 'Your ban is not set to expire.';

$txt[452] = 'Alle Beitr&auml;ge als gelesen markieren';

$txt[454] = 'Hei&szlig;es Thema (mehr als {$modSettings.hotTopicPosts} Antworten)';
$txt[455] = 'Sehr hei&szlig;es Thema (mehr als {$modSettings.hotTopicVeryPosts} Antworten)';
$txt[456] = 'Thema geschlossen';
$txt[457] = 'Normales Thema';
$txt['participation_caption'] = 'Themen auf die Sie geantwortet haben';

$txt[462] = 'Los';

$txt[465] = 'Drucken';
$txt[467] = 'Profil';
$txt[468] = 'Zusammenfassung';
$txt[470] = 'Nicht verf&uuml;gbar';
$txt[471] = 'Nachricht';
$txt[473] = 'Dieser Name ist bereits in Verwendung.';

$txt[488] = 'Mitglieder insgesamt';
$txt[489] = 'Beitr&auml;ge insgesamt';
$txt[490] = 'Themen insgesamt';

$txt[497] = 'Sitzungsl&auml;nge in Minuten';

$txt[507] = 'Vorschau';
$txt[508] = 'Immer eingeloggt bleiben';

$txt[511] = 'Gespeichert';
// Use numeric entities in the below string.
$txt[512] = 'IP';

$txt[513] = 'ICQ';
$txt[515] = 'WWW';

$txt[525] = 'von';

$txt[578] = 'Stunden';
$txt[579] = 'Tage';

$txt[581] = ', unser neuestes Mitglied.';

$txt[582] = 'Suchen nach';

$txt[603] = 'AIM';
// In this string, please use +'s for spaces.
$txt['aim_default_message'] = 'Hallo.+Sind+Sie+online?';
$txt[604] = 'YIM';

$txt[616] = 'Nicht vergessen, das Forum ist im \'Wartungsmodus\'!';

$txt[641] = 'Gelesen';
$txt[642] = 'mal';

$txt[645] = 'Forum-Statistiken';
$txt[656] = 'Neuestes Mitglied';
$txt[658] = 'Kategorien insgesamt';
$txt[659] = 'Letzter Beitrag';

$txt[660] = 'Sie haben';
$txt[661] = 'Klicken Sie';
$txt[662] = 'hier';
$txt[663] = 'um sie zu sehen.';

$txt[665] = 'Boards insgesamt';

$txt[668] = 'Seite drucken';

$txt[679] = 'Es muss eine g&uuml;ltige E-Mail Adresse sein.';

$txt[683] = 'Ich bin ein Freak!!';
$txt[685] = '{$context.forum_name} - Info-Center';

$txt[707] = 'Senden Sie dieses Thema';

$txt['sendtopic_title'] = 'Senden Sie das Thema &quot;%s&quot; einem Freund.';
// Use numeric entities in the below three strings.
$txt['sendtopic_dear'] = 'Hallo %s,';
$txt['sendtopic_this_topic'] = 'Sehen Sie sich bitte folgendes Thema an: %s, am {$context.forum_name}. Klicken Sie dazu auf den Link';
$txt['sendtopic_thanks'] = 'Danke';
$txt['sendtopic_sender_name'] = 'Ihr Name';
$txt['sendtopic_sender_email'] = 'Ihre E-Mail Adresse';
$txt['sendtopic_receiver_name'] = 'Name des Empf&auml;ngers';
$txt['sendtopic_receiver_email'] = 'E-Mail Adresse des Empf&auml;ngers';
$txt['sendtopic_comment'] = 'Kommentar hinzuf&uuml;gen';
// Use numeric entities in the below string.
$txt['sendtopic2'] = 'Ein Kommentar wurde zu diesem Thema hinzugef&#252;gt';

$txt[721] = 'E-Mail Adresse nicht anzeigen (empfohlen)?';

$txt[737] = 'Alle markieren';

// Use numeric entities in the below string.
$txt[1001] = 'Datenbankfehler';
$txt[1002] = 'Bitte versuchen Sie es nochmal. Sollte der Fehler wieder auftreten, informieren Sie bitte den Administrator.';
$txt[1003] = 'Datei';
$txt[1004] = 'Zeile';
// Use numeric entities in the below string.
$txt[1005] = 'SMF hat einen Datenbankfehler entdeckt und versucht ihn automatisch zu reparieren. Wenn Sie erneut Probleme haben sollten oder weiterhin diese E-Mails erhalten, kontaktieren Sie bitte Ihren Serveranbieter.';
$txt['database_error_versions'] = '<b>Achtung:</b> Ihre Datenbank scheint veraltet zu sein! Ihre Dateien haben die Version {$forum_version}, wogegen die Datenbank die Version {$modSettings.smfVersion} hat. Es wird dringend empfohlen, die neueste Version der upgrade.php auszuführen.';
$txt['template_parse_error'] = 'Template Parse Error!';
$txt['template_parse_error_message'] = 'Ein Fehler ist im Templatesystem des Forums aufgetreten! Dieses Problem sollte nur tempor&auml;r auftreten, bitte versuchen Sie es sp&auml;ter nochmal. Sollten Sie die Fehlermeldung weiterhin erhalten, kontaktieren Sie bitte den Administrator.<br /><br />Sie k&ouml;nnen versuchen die Seite zu <a href="javascript:location.reload();">aktualisieren</a>.';
$txt['template_parse_error_details'] = 'Ein Problem trat beim Laden des <tt><b>%1$s</b></tt> Templates oder der Sprachdatei auf. Bitte &uuml;berpr&uuml;fen Sie die Syntax und probieren es erneut. Bitte beachten Sie, dass einzelne Anf&uuml;hrungszeichen (<tt>\'</tt>) oft mit einem Slash (<tt>\\</tt>) auskommentiert werden m&uuml;ssen. Um n&auml;here Informationen von PHP zum Fehler zu erhalten, probieren Sie <a href="{$boardurl}%1$s">die Seite direkt aufzurufen</a>.<br /><br />Sie k&ouml;nnen auch versuchen, die Seite zu <a href="javascript:location.reload();">aktualisieren</a> oder das <a href="{$scripturl}?theme=1">Standard-Theme</a> zu benutzen.';

$txt['smf10'] = '<b>Heute</b> um ';
$txt['smf10b'] = '<b>Gestern</b> um ';
$txt['smf20'] = 'Neue Umfrage';
$txt['smf21'] = 'Frage';
$txt['smf23'] = 'Abstimmen';
$txt['smf24'] = 'Stimmen insgesamt';
$txt['smf25'] = 'Shortcuts: Alt+S f&uuml;r das Absenden oder Alt+P f&uuml;r die Vorschau';
$txt['smf29'] = 'Ergebnisse anzeigen';
$txt['smf30'] = 'Umfrage schlie&szlig;en';
$txt['smf30b'] = 'Umfrage &ouml;ffnen';
$txt['smf39'] = 'Umfrage editieren';
$txt['smf43'] = 'Umfrage';
$txt['smf47'] = '1 Tag';
$txt['smf48'] = '1 Woche';
$txt['smf49'] = '1 Monat';
$txt['smf50'] = 'Immer';
$txt['smf52'] = 'Einloggen mit Benutzername, Passwort und Sitzungsl&auml;nge';
$txt['smf53'] = '1 Stunde';
$txt['smf56'] = 'VERSCHOBEN';
$txt['smf57'] = 'Bitte geben Sie einen kurzen Hinweis ein, <br />warum das Thema verschoben wird.';
$txt['smf60'] = 'Sie haben zu wenige Beitr&auml;ge geschrieben, um das Karma zu &auml;ndern - Sie brauchen mindestens ';
$txt['smf62'] = 'Sie k&ouml;nnen nicht wiederholt abstimmen. Bitte warten Sie ';
$txt['smf82'] = 'Board';
$txt['smf88'] = 'in';
$txt['smf96'] = 'Top Thema';

$txt['smf138'] = 'L&ouml;schen';

$txt['smf199'] = 'Ihre Privaten Mitteilungen';

$txt['smf211'] = 'KB';

$txt['smf223'] = '[Weitere Statistiken]';

// Use numeric entities in the below three strings.
$txt['smf238'] = 'Code';
$txt['smf239'] = 'Zitat von';
$txt['smf240'] = 'Zitat';

// Untranslated
$txt['merge_to_topic_id'] = 'ID of target topic';
$txt['smf251'] = 'Thema teilen';
$txt['smf252'] = 'Themen zusammenf&uuml;hren';
$txt['smf254'] = 'Betreff f&uuml;r das neue Thema';
$txt['smf255'] = 'Nur diesen Beitrag trennen.';
$txt['smf256'] = 'Thema bis und inkl. diesem Beitrag aufteilen.';
$txt['smf257'] = 'Beitr&auml;ge ausw&auml;hlen, welche geteilt werden sollen.';
$txt['smf258'] = 'Neues Thema';
$txt['smf259'] = 'Thema erfolgreich in zwei Themen aufgeteilt.';
$txt['smf260'] = 'Urspr&uuml;ngliches Thema';
$txt['smf261'] = 'Bitte w&auml;hlen Sie die Beitr&auml;ge aus, die Sie trennen m&ouml;chten.';
$txt['smf264'] = 'Themen erfolgreich zusammengef&uuml;hrt.';
$txt['smf265'] = 'Neu zusammengef&uuml;hrtes Thema';
$txt['smf266'] = 'Thema, welches zusammengef&uuml;hrt werden soll';
$txt['smf267'] = 'Ziel-Board';
$txt['smf269'] = 'Ziel-Thema';
$txt['smf274'] = 'Sind Sie sicher, dass Sie folgende Themen zusammenf&uuml;hren m&ouml;chten';
$txt['smf275'] = 'mit';
$txt['smf276'] = 'Diese Funktion wird die Beitr&auml;ge von zwei Themen zu einem Thema zusammenf&uuml;hren. Die Beitr&auml;ge werden zeitlich sortiert sein, d.h. der &auml;lteste Beitrag wird der erste im zusammengef&uuml;hrten Thema sein.';

$txt['smf277'] = 'Thema fixieren';
$txt['smf278'] = 'Fixierung des Themas entfernen';
$txt['smf279'] = 'Thema schlie&szlig;en';
$txt['smf280'] = 'Thema &ouml;ffnen';

$txt['smf298'] = 'Erweiterte Suche';

$txt['smf299'] = 'GROSSES SICHERHEITSRISIKO:';
$txt['smf300'] = 'Sie haben folgende Datei(en) nicht gel&ouml;scht: ';

// Untranslated!
$txt['cache_writable_head'] = 'Performance Warning';
$txt['cache_writable'] = 'The cache directory is not writable - this will adversely affect the performance of your forum.';

$txt['smf301'] = 'Seite erstellt in ';
$txt['smf302'] = ' Sekunden mit ';
$txt['smf302b'] = ' Zugriffen.';

$txt['smf315'] = 'Benutzen Sie diese Funktion, um Moderatoren/Administratoren &uuml;ber einen missbr&auml;uchlich oder falsch geschriebenen Beitrag zu informieren.<br /><i>Bitte beachten Sie, dass Ihre E-Mail Adresse zum betreffenden Moderator gesendet wird, wenn Sie diese Funktion benutzen.</i>';

$txt['online2'] = 'Online';
$txt['online3'] = 'Offline';
$txt['online4'] = 'Private Mitteilung (Online)';
$txt['online5'] = 'Private Mitteilung (Offline)';
$txt['online8'] = 'Status';

$txt['topbottom4'] = 'Nach oben';
$txt['topbottom5'] = 'Nach unten';

$forum_copyright = '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by {$forum_version}</a> | 
<a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy; 2001-2006, Lewis Media</a>';

$txt['calendar3'] = 'Geburtstage:';
$txt['calendar4'] = 'Ereignisse:';
$txt['calendar3b'] = 'Zuk&uuml;nftige Geburtstage:';
$txt['calendar4b'] = 'Zuk&uuml;nftige Ereignisse:';
// Prompt for holidays in the calendar, leave blank to just display the holiday's name.
$txt['calendar5'] = '';
$txt['calendar9'] = 'Monat:';
$txt['calendar10'] = 'Jahr:';
$txt['calendar11'] = 'Tag:';
$txt['calendar12'] = 'Ereignis-Titel:';
$txt['calendar13'] = 'Erstellen in:';
$txt['calendar20'] = 'Ereignis editieren';
$txt['calendar21'] = 'Dieses Ereignis löschen?';
$txt['calendar22'] = 'L&ouml;sche Ereignis';
$txt['calendar23'] = 'Erstelle Ereignis';
$txt['calendar24'] = 'Kalender';
$txt['calendar37'] = 'Link zum Kalender';
$txt['calendar43'] = 'Ereignis verlinken';
$txt['calendar47'] = 'Zuk&uuml;nftige Ereignisse';
$txt['calendar47b'] = 'Heutige Ereignisse';
$txt['calendar51'] = 'Woche';
$txt['calendar54'] = 'Anzahl der Tage:';
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
$txt['mlist_search2'] = 'Erneut suchen';
$txt['mlist_search_email'] = 'Nach E-Mail Adresse suchen';
$txt['mlist_search_messenger'] = 'Nach Messenger Spitzname suchen';
$txt['mlist_search_group'] = 'Nach Position suchen';
$txt['mlist_search_name'] = 'Nach Namen suchen';
$txt['mlist_search_website'] = 'Nach Webseite suchen';
$txt['mlist_search_results'] = 'Suchergebnisse f&uuml;r';

$txt['attach_downloaded'] = 'runtergeladen';
$txt['attach_viewed'] = 'angeschaut';
$txt['attach_times'] = 'Mal';

$txt['MSN'] = 'MSN';

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

$txt['quick_reply_1'] = 'Schnellantwort';
$txt['quick_reply_2'] = 'Bei der <i>Schnellantwort</i> k&ouml;nnen Sie Bulletin Board Code und Smileys wie im normalen Beitrag benutzen.';
$txt['quick_reply_warning'] = 'Warnung: Das Thema ist momentan geschlossen!<br />Nur Administratoren und Moderatoren k&ouml;nnen antworten.';
// Untranslated!
$txt['wait_for_approval'] = 'Note: this post will not display until it\'s been approved by a moderator.';

$txt['notification_enable_board'] = 'Sind Sie sicher, dass Sie Benachrichtigungen über neue Themen in diesem Board aktivieren m&ouml;chten?';
$txt['notification_disable_board'] = 'Sind Sie sicher, dass Sie Benachrichtigungen über neue Themen in diesem Board deaktivieren m&ouml;chten?';
$txt['notification_enable_topic'] = 'Sind Sie sicher, dass Sie Benachrichtigungen &uuml;ber neue Beitr&auml;ge in diesem Thema aktivieren m&ouml;chten?';
$txt['notification_disable_topic'] = 'Sind Sie sicher, dass Sie Benachrichtigungen &uuml;ber neue Beitr&auml;ge in diesem Thema deaktivieren m&ouml;chten?';

$txt['rtm1'] = 'Moderator informieren';

$txt['unread_topics_visit'] = 'Neue ungelesene Themen';
$txt['unread_topics_visit_none'] = 'Keine ungelesenen Themen seit dem letzten Besuch gefunden. <a href="{$scripturl}?action=unread;all">Klicken Sie hier, um alle ungelesenen Themen zu suchen.</a>.';
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
$txt['show_personal_messages'] = 'Sie haben eine oder mehrere neue Private Mitteilungen erhalten.\\nMöchten Sie diese lesen?';

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

?>