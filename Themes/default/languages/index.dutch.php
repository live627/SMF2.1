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
$txt['lang_locale'] = 'nl';
$txt['lang_dictionary'] = 'nl';
$txt['lang_spelling'] = '';

// Character set and right to left?
$txt['lang_character_set'] = 'ISO-8859-1';
$txt['lang_rtl'] = false;

$txt['days'] = array('zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag');
$txt['days_short'] = array('zo', 'ma', 'di', 'wo', 'do', 'vr', 'za');
// Months must start with 1 => 'January'. (or translated, of course.)
$txt['months'] = array(1 => 'januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december');
$txt['months_titles'] = array(1 => 'januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december');
$txt['months_short'] = array(1 => 'jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec');

$txt['newmessages0'] = 'is nieuw';
$txt['newmessages1'] = 'zijn nieuw';
$txt['newmessages3'] = 'Nieuw';
$txt['newmessages4'] = ',';

$txt['admin'] = 'Beheer';
// Untranslated!
$txt['moderate'] = 'Moderate';

$txt['save'] = 'Opslaan';

$txt['modify'] = 'Verander';
$txt['forum_index'] = '%1$s - Forumindex';
$txt['members'] = 'Geregistreerde leden';
$txt['board_name'] = 'Forumnaam';
$txt['posts'] = 'Berichten';

$txt['member_postcount'] = 'Berichten';
$txt['no_subject'] = '(Geen onderwerp)';
$txt['view_profile'] = 'Bekijk profiel';
$txt['guest_title'] = 'Gast';
$txt['author'] = 'Auteur';
$txt['on'] = 'Gepost op';
$txt['remove'] = 'Verwijder';
$txt['start_new_topic'] = 'Begin een nieuw topic';

$txt['login'] = 'Inloggen';
// Use numeric entities in the below string.
$txt['username'] = 'Gebruikersnaam';
$txt['password'] = 'Wachtwoord';

$txt['username_no_exist'] = 'Gebruikersnaam bestaat niet.';

$txt['board_moderator'] = 'Board-moderator';
$txt['remove_topic'] = 'Verwijder topic';
$txt['topics'] = 'topics';
$txt['modify_msg'] = 'Bewerk bericht';
$txt['name'] = 'Naam';
$txt['email'] = 'E-mail';
$txt['subject'] = 'Onderwerp';
$txt['message'] = 'Bericht';

$txt['profile'] = 'Profiel';

$txt['choose_pass'] = 'Kies wachtwoord';
$txt['verify_pass'] = 'Wachtwoord nog een keer';
$txt['position'] = 'Positie';

$txt['profile_of'] = 'Bekijk profiel van';
$txt['total'] = 'Totaal';
$txt['posts_made'] = 'aantal berichten';
$txt['website'] = 'Website';
$txt['register'] = 'Registreren';

$txt['message_index'] = 'Berichtenindex';
$txt['news'] = 'Nieuws';
$txt['home'] = 'Startpagina';

$txt['lock_unlock'] = 'Sluiten/Openen topic';
$txt['post'] = 'Verzenden';
$txt['error_occured'] = 'Er is een fout opgetreden !';
$txt['at'] = 'om';
$txt['logout'] = 'Uitloggen';
$txt['started_by'] = 'Gestart door';
$txt['replies'] = 'Reacties';
$txt['last_post'] = 'Laatste bericht';
$txt['admin_login'] = 'Administratie Login';
// Use numeric entities in the below string.
$txt['topic'] = 'Topic';
$txt['help'] = 'Help';
$txt['remove_message'] = 'Verwijder berichten';
$txt['notify'] = 'Bericht';
$txt['notify_request'] = 'Wil je een e-mail ontvangen als iemand antwoord geeft op dit topic?';
// Use numeric entities in the below string.
$txt['regards_team'] = 'Met vriendelijke groet,' . "\n\n" . 'Het ' . $context['forum_name'] . ' team.';
$txt['notify_replies'] = 'Bericht bij reacties';
$txt['move_topic'] = 'Verplaats topic';
$txt['move_to'] = 'Verplaats naar';
$txt['pages'] = 'Pagina\'s';
$txt['users_active'] = 'Gebruikers actief in de laatste %1$d minuten';
$txt['personal_messages'] = 'Persoonlijk bericht';
$txt['reply_quote'] = 'Antwoord met citaat';
$txt['reply'] = 'Antwoord';
// Untranslated!
$txt['approve'] = 'Approve';
$txt['approve_all'] = 'approve all';
$txt['attach_awaiting_approve'] = 'Attachments awaiting approval';

$txt['msg_alert_none'] = 'Geen berichten...';
$txt['msg_alert_you_have'] = 'je hebt';
$txt['msg_alert_messages'] = 'persoonlijke berichten';
$txt['remove_message'] = 'Verwijder dit bericht';

$txt['online_users'] = 'Gebruikers Online';
$txt['personal_message'] = 'Priv&eacute; berichten';
$txt['jump_to'] = 'Ga naar';
$txt['go'] = 'ga';
$txt['are_sure_remove_topic'] = 'Weet je zeker dat je dit topic wilt verwijderen?';
$txt['yes'] = 'Ja';
$txt['no'] = 'Nee';

$txt['search_results'] = 'Zoek resultaten';
$txt['search_end_results'] = 'Einde van de resultaten';
$txt['search_no_results'] = 'Sorry, geen berichten gevonden';
$txt['search_on'] = 'op';

$txt['search'] = 'Zoek';
$txt['all'] = 'Allemaal';

$txt['back'] = 'Terug';
$txt['password_reminder'] = 'Wachtwoord vergeten?';
$txt['topic_started'] = 'Topic gestart door';
$txt['title'] = 'Titel';
$txt['post_by'] = 'Bericht door';
$txt['memberlist_searchable'] = 'Doorzoekbare lijst van alle geregistreerde leden.';
$txt['welcome_member'] = 'Een warm welkom voor';
$txt['admin_center'] = 'Beheerscherm';
$txt['last_edit'] = 'Laatste verandering';
$txt['notify_deactivate'] = 'Wil je de mogelijkheid van antwoord uit zetten in deze topic ?';

$txt['recent_posts'] = 'Recente berichten';

$txt['location'] = 'Lokatie';
$txt['gender'] = 'Geslacht';
$txt['date_registered'] = 'Datum van registratie';

$txt['recent_view'] = 'Bekijk de meest recente berichten op het forum.';
$txt['recent_updated'] = 'is een van de meest recente topics';

$txt['male'] = 'Man';
$txt['female'] = 'Vrouw';

$txt['error_invalid_characters_username'] = 'Onjuiste letters/cijfers gebruikt in je gebruikersnaam.';

$txt['welcome_guest'] = 'Welkom, <b>%1$s</b>. Alsjeblieft <a href="' . $scripturl . '?action=login">inloggen</a> of <a href="' . $scripturl . '?action=register">registreren</a>.';
$txt['welcome_guest_activate'] = '<br />De <a href="' . $scripturl . '?action=activate">activerings e-mail</a> gemist?';
$txt['hello_member'] = 'Hoi,';
// Use numeric entities in the below string.
$txt['hello_guest'] = 'Welkom,';
$txt['welmsg_hey'] = 'Hoi,';
$txt['welmsg_welcome'] = 'Welkom,';
$txt['welmsg_please'] = 'Alsjeblieft';
$txt['select_destination'] = 'Selecteer een bestemming';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['posted_by'] = 'Geplaatst door';

$txt['icon_smiley'] = 'Lachebek';
$txt['icon_angry'] = 'Boos';
$txt['icon_cheesy'] = 'Lachen';
$txt['icon_laugh'] = 'Lach';
$txt['icon_sad'] = 'Droevig';
$txt['icon_wink'] = 'Knipoog';
$txt['icon_grin'] = 'Grijns';
$txt['icon_shocked'] = 'Geschrokken';
$txt['icon_cool'] = 'Cool';
$txt['icon_huh'] = 'Verbaasd';
$txt['icon_rolleyes'] = 'Rollende ogen';
$txt['icon_tongue'] = 'Tong';
$txt['icon_embarrassed'] = 'Beschaamd';
$txt['icon_lips'] = 'Lippen verzegeld';
$txt['icon_undecided'] = 'Ik weet het niet';
$txt['icon_kiss'] = 'Kus';
$txt['icon_cry'] = 'Huilen';

$txt['moderator'] = 'Beheerder';
$txt['moderators'] = 'Beheerders';

$txt['mark_board_read'] = 'Markeer alle berichten als gelezen';
$txt['views'] = 'Gelezen';
$txt['new'] = 'Nieuw';

$txt['view_all_members'] = 'Bekijk alle gebruikers';
$txt['view'] = 'Bekijk';
$txt['email'] = 'E-mail';

// Untranslated!
$txt['viewing_members'] = 'Viewing Members %1$s to %2$s';
$txt['of_total_members'] = 'of %1$s total members';

$txt['forgot_your_password'] = 'Wachtwoord vergeten?';

$txt['date'] = 'Datum';
// Use numeric entities in the below string.
$txt['from'] = 'Van';
$txt['subject'] = 'Onderwerp';
$txt['check_new_messages'] = 'Controleer op nieuwe berichten';
$txt['to'] = 'Naar';

$txt['board_topics'] = 'Topics';
$txt['members_title'] = 'Leden';
$txt['members_list'] = 'Ledenlijst';
$txt['new_posts'] = 'Nieuw bericht';
$txt['old_posts'] = 'Geen nieuw bericht';

$txt['sendtopic_send'] = 'Zenden';

$txt['time_offset'] = 'Tijdafwijking';
$txt['or'] = 'of';

$txt['no_matches'] = 'Sorry, niets relevants is gevonden';

$txt['notification'] = 'Bericht';

$txt['your_ban'] = 'Sorry %s, je bent gebanned voor het gebruik van dit forum!';
// !!! Untranslated
$txt['your_ban_expires'] = 'Your ban is set to expire %s';
$txt['your_ban_expires_never'] = 'Your ban is not set to expire.';

$txt['mark_as_read'] = 'Markeer alle berichten als gelezen';

$txt['hot_topics'] = 'Populair topic (meer dan %1$d reacties)';
$txt['very_hot_topics'] = 'Zeer populair topic (meer dan %1$d reacties)';
$txt['locked_topic'] = 'Gesloten topic';
$txt['normal_topic'] = 'Normaal topic';
$txt['participation_caption'] = 'Topic waaraan je hebt deelgenomen';

$txt['go_caps'] = 'GA!';

$txt['print'] = 'Print';
$txt['profile'] = 'Profiel';
$txt['topic_summary'] = 'Samenvatting van topic';
$txt['not_applicable'] = 'Niet aanwezig';
$txt['message_lowercase'] = 'persoonlijk bericht';
$txt['name_in_use'] = 'Deze naam is al in gebruik bij een ander lid.';

$txt['total_members'] = 'Totaal aantal leden';
$txt['total_posts'] = 'Totaal aantal berichten';
$txt['total_topics'] = 'Totaal aantal topics';

$txt['mins_logged_in'] = 'Aantal minuten dat je blijft<br />ingelogd.';

$txt['preview'] = 'Bekijken';
$txt['always_logged_in'] = 'Blijf ingelogd';

$txt['logged'] = 'Gelogd';
// Use numeric entities in the below string.
$txt['ip'] = 'IP';

$txt['icq'] = 'ICQ';
$txt['www'] = 'WWW';

$txt['by'] = 'door';

$txt['hours'] = 'uren';
$txt['days_word'] = 'dagen';

$txt['newest_member'] = ', ons nieuwste lid.';

$txt['search_for'] = 'zoek naar';

$txt['aim'] = 'AIM';
// In this string, please use +'s for spaces.
$txt['aim_default_message'] = 'Hi.+Are+you+there?';
$txt['yim'] = 'YIM';

$txt['maintain_mode_on'] = 'Onthoud dat dit forum in de onderhoudsmodus staat!';

$txt['read'] = 'gelezen';
$txt['times'] = 'keer';

$txt['forum_stats'] = 'Statistieken';
$txt['latest_member'] = 'Laatste lid';
$txt['total_cats'] = 'Totaal categorie&euml;n';
$txt['latest_post'] = 'Laatste bericht';

$txt['you_have'] = 'Je hebt';
$txt['click'] = 'klik';
$txt['here'] = 'hier';
$txt['to_view'] = 'om ze te bekijken.';

$txt['total_boards'] = 'Totaal aantal boards';

$txt['print_page'] = 'Print pagina';

$txt['valid_email'] = 'Dit moet een geldig e-mailadres zijn.';

$txt['geek'] = 'een heleboel';
$txt['info_center_title'] = '%s - Info Center';

$txt['send_topic'] = 'Stuur dit topic';

$txt['sendtopic_title'] = 'Stuur dit onderwerp &#171; %s &#187; naar een vriend!';
// Use numeric entities in the below three strings.
$txt['sendtopic_dear'] = 'Beste %s,';
$txt['sendtopic_this_topic'] = 'Dit topic moet je eens lezen: %s, op %s.  Om het te bekijken kun je op de volgende link klikken';
$txt['sendtopic_thanks'] = 'Bedankt';
$txt['sendtopic_sender_name'] = 'Je naam';
$txt['sendtopic_sender_email'] = 'Je e-mailadres';
$txt['sendtopic_receiver_name'] = 'Naam van je vriend';
$txt['sendtopic_receiver_email'] = 'E-mailadres van je vriend';
$txt['sendtopic_comment'] = 'Voeg een opmerking toe';
// Use numeric entities in the below string.
$txt['sendtopic2'] = 'Er is een opmerking over dit topic toegevoegd';

$txt['hide_email'] = 'E-mail verbergen voor anderen?';

$txt['check_all'] = 'Vink alles aan';

// Use numeric entities in the below string.
$txt['database_error'] = 'Databasefout';
$txt['try_again'] = 'Probeer het opnieuw, gaat het weer fout, meld het dan aan de admin.';
$txt['file'] = 'Bestand';
$txt['line'] = 'Regel';
// Use numeric entities in the below string.
$txt['tried_to_repair'] = 'SMF heeft een fout in je database gedetecteerd en automatisch geprobeerd deze te repareren. Als de problemen blijven bestaan of als je voortdurend deze e-mails krijgt, neem contact op met je provider.';
$txt['database_error_versions'] = '<b>Let op:</b> Het ziet er naar uit dat je database geupgrade zou moeten worden. De versie van je forumbestanden is momenteel %s, terwijl je database op dit moment nog staat op versie SMF %s. Het is aan te bevelen om de laatste versie van upgrade.php uit te voeren.';
$txt['template_parse_error'] = 'Template Parse Error!';
$txt['template_parse_error_message'] = 'Het lijkt erop dat er iets verkeerd gegaan is op het forum met het template systeem. Dit probleeem zou alleen tijdelijk moeten zijn, kom later terug en probeer het opnieuw. Als je dit probleem blijft krijgen, neem dan contact op met de administrator.<br /><br />Je kunt ook proberen om <a href="javascript:location.reload();">deze pagina te verversen</a>.';
$txt['template_parse_error_details'] = 'There was a problem loading the <tt><b>%1$s</b></tt> template or language file.  Please check the syntax and try again - remember, single quotes (<tt>\'</tt>) often have to be escaped with a slash (<tt>\\</tt>).  To see more specific error information from PHP, try <a href="' . $boardurl . '%1$s">accessing the file directly</a>.<br /><br />You may want to try to <a href="javascript:location.reload();">refresh this page</a> or <a href="' . $scripturl . '?theme=1">use the default theme</a>.';

$txt['today'] = '<b>Vandaag</b> om ';
$txt['yesterday'] = '<b>Gisteren</b> om ';
$txt['new_poll'] = 'Plaats een nieuwe poll';
$txt['poll_question'] = 'Vraag';
$txt['poll_vote'] = 'Stem';
$txt['poll_total_voters'] = 'Totaal aantal stemmen';
$txt['shortcuts'] = 'sneltoetsen: gebruik alt+s om te verzenden/posten, of alt+p om te bekijken';
$txt['poll_results'] = 'Bekijk de resultaten';
$txt['poll_lock'] = 'Vergrendel de poll';
$txt['poll_unlock'] = 'Ontgrendel de poll';
$txt['poll_edit'] = 'Bewerk de poll';
$txt['poll'] = 'Poll';
$txt['one_day'] = '1 dag';
$txt['one_week'] = '1 week';
$txt['one_month'] = '1 maand';
$txt['forever'] = 'blijvend';
$txt['quick_login_dec'] = 'Login met gebruikersnaam, wachtwoord en sessielengte';
$txt['one_hour'] = '1 uur';
$txt['moved'] = 'VERPLAATST';
$txt['moved_why'] = 'Geef even een korte beschrijving waarom<br />dit topic wordt verplaatst.';
$txt['board'] = 'Board';
$txt['in'] = 'in';
$txt['smf96'] = 'Sticky Topic';

$txt['delete'] = 'Verwijder';

$txt['your_pms'] = 'Je persoonlijke berichten';

$txt['kilobyte'] = 'KB';

$txt['more_stats'] = '[Meer statistieken]';

// Use numeric entities in the below three strings.
$txt['code'] = 'Code';
$txt['quote_from'] = 'Citaat van';
$txt['quote'] = 'Citaat';

// Untranslated
$txt['merge_to_topic_id'] = 'ID of target topic';
$txt['split'] = 'Splits topic';
$txt['merge'] = 'Voeg topic samen';
$txt['subject_new_topic'] = 'Titel van het nieuwe topic';
$txt['split_this_post'] = 'Splits alleen dit bericht';
$txt['split_after_and_this_post'] = 'Splits topic vanaf dit bericht.';
$txt['select_split_posts'] = 'Selecteer berichten om te splitsen.';
$txt['new_topic'] = 'Nieuw topic';
$txt['split_successful'] = 'Onderwerp succesvol gesplitst in twee topics.';
$txt['origin_topic'] = 'Oorspronkelijke topic';
$txt['please_select_split'] = 'Selecteer welke berichten je wilt afsplitsen.';
$txt['merge_successful'] = 'Topics succesvol samengevoegd.';
$txt['new_merged_topic'] = 'Nieuw samengevoegd topic';
$txt['topic_to_merge'] = 'Topic dat moet worden samengevoegd';
$txt['target_board'] = 'Doelboard';
$txt['target_topic'] = 'Doeltopic ';
$txt['merge_confirm'] = 'Weet je zeker dat je de twee topics wilt samenvoegen?';
$txt['with'] = 'met';
$txt['merge_desc'] = 'Deze optie zal de twee topics samenvoegen. De berichten zullen worden gesorteerd op datum, dus het eerst geplaatste bericht zal bovenaan komen te staan.';

$txt['set_sticky'] = 'Maak topic sticky';
$txt['set_nonsticky'] = 'Maak topic niet-sticky';
$txt['set_lock'] = 'Sluit topic';
$txt['set_unlock'] = 'Slot verwijderen';

$txt['search_advanced'] = 'Geavanceerd zoeken';

$txt['security_risk'] = 'GROOT BEVEILIGINGSRISICO:';
$txt['not_removed'] = 'Je hebt het volgende bestand niet verwijderd: ';

// Untranslated!
$txt['cache_writable_head'] = 'Performance Warning';
$txt['cache_writable'] = 'The cache directory is not writable - this will adversely affect the performance of your forum.';

$txt['page_created'] = 'Pagina opgebouwd in ';
$txt['seconds_with'] = ' seconden met ';
$txt['queries'] = ' queries.';

$txt['report_to_mod_func'] = 'Gebruik deze functie om de moderators en administrators op de hoogte te stellen van berichten die verkeerd geplaatst zijn of in overtreding zijn met de regels van het forum.<br /><i>Houd er rekening mee dat je e-mailadres zal worden getoond aan de moderators bij het gebruik van deze functie.</i>';

$txt['online'] = 'Online';
$txt['offline'] = 'Offline';
$txt['pm_online'] = 'Persoonlijk bericht (Online)';
$txt['pm_offline'] = 'Persoonlijk bericht (Offline)';
$txt['status'] = 'Status';

$txt['go_up'] = 'Omhoog';
$txt['go_down'] = 'Omlaag';

$forum_copyright = '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by %s</a> |
 <a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy; 2006, Simple Machines LLC</a>';

$txt['birthdays'] = 'Verjaardagen:';
$txt['events'] = 'Gebeurtenissen:';
$txt['birthdays_upcoming'] = 'Aankomende verjaardagen:';
$txt['events_upcoming'] = 'Aankomende evenementen:';
// Prompt for holidays in the calendar, leave blank to just display the holiday's name.
$txt['calendar_prompt'] = '';
$txt['calendar_month'] = 'Maand:';
$txt['calendar_year'] = 'Jaar:';
$txt['calendar_day'] = 'Dag:';
$txt['calendar_event_title'] = 'Titel:';
$txt['calendar_post_in'] = 'Post in:';
$txt['calendar_edit'] = 'Bewerk deze gebeurtenis';
$txt['event_delete_confirm'] = 'Deze gebeurtenis verwijderen?';
$txt['event_delete'] = 'Verwijder deze gebeurtenis';
$txt['calendar_post_event'] = 'Post gebeurtenis';
$txt['calendar'] = 'Kalender';
$txt['calendar_link'] = 'Link naar de kalender';
$txt['calendar_link_event'] = 'Link gebeurtenis';
$txt['calendar_upcoming'] = 'Aankomende kalender';
$txt['calendar_today'] = 'Kalender van vandaag';
$txt['calendar_week'] = 'Week';
$txt['calendar_numb_days'] = 'Aantal dagen:';
$txt['calendar_how_edit'] = 'Hoe bewerk je deze gebeurtenissen?';
$txt['calendar_link_event'] = 'Koppel gebeurtenis aan bericht:';
$txt['calendar_confirm_delete'] = 'Weet je zeker dat je deze gebeurtenis wilt verwijderen?';
$txt['calendar_linked_events'] = 'Gekoppelde gebeurtenissen';

$txt['moveTopic1'] = 'Plaats een verwijstopic';
$txt['moveTopic2'] = 'Wijzig het onderwerp van dit bericht';
$txt['moveTopic3'] = 'Nieuwe onderwerp';
$txt['moveTopic4'] = 'Verander het onderwerp van elk bericht';

$txt['theme_template_error'] = 'Kan template \'%s\' niet laden.';
$txt['theme_language_error'] = 'Kan taalbestand \'%s\' niet laden.';

$txt['parent_boards'] = 'Sub-boards';

$txt['smtp_no_connect'] = 'Kan geen verbinding krijgen met de SMTP host';
$txt['smtp_port_ssl'] = 'SMTP-poortinstelling onjuist; het zou 465 moeten zijn voor SSL-servers.';
$txt['smtp_bad_response'] = 'Kan geen responsie codes van de mail server krijgen';
$txt['smtp_error'] = 'Problemen opgetreden gedurende het verzenden van mail. Foutmelding: ';
$txt['mail_send_unable'] = 'Kon mail niet verzenden naar e-mailadres \'%s\'';

$txt['mlist_search'] = 'Zoek op gebruikersnaam';
$txt['mlist_search_again'] = 'Herhaal zoekopdracht';
$txt['mlist_search_email'] = 'Zoek op e-mailadres';
$txt['mlist_search_messenger'] = 'Zoek op MSN-Messengeradres';
$txt['mlist_search_group'] = 'Zoek op ledengroep';
$txt['mlist_search_name'] = 'Zoek op naam';
$txt['mlist_search_website'] = 'Zoek op website';
$txt['mlist_search_results'] = 'Zoekresultaten voor';

$txt['attach_downloaded'] = 'gedownload';
$txt['attach_viewed'] = 'bekeken';
$txt['attach_times'] = 'keer';

$txt['msn'] = 'MSN';

$txt['settings'] = 'Instellingen';
$txt['never'] = 'Nooit';
$txt['more'] = 'meer';

$txt['hostname'] = 'Hostnaam';
$txt['you_are_post_banned'] = 'Sorry %s, je bent geband van het plaatsen van berichten of het versturen van Persoonlijke Bericthen op dit forum.';
$txt['ban_reason'] = 'Reden';

$txt['tables_optimized'] = 'Databasetabellen geoptimaliseerd';

$txt['add_poll'] = 'Voeg poll toe';
$txt['poll_options6'] = 'Je kunt slechts %s opties selecteren.';
$txt['poll_remove'] = 'Verwijder poll';
$txt['poll_remove_warn'] = 'Weet je zeker dat je deze poll van dit topic wilt verwijderen?';
$txt['poll_results_expire'] = 'Resultaten zullen worden getoond als de poll is gesloten';
$txt['poll_expires_on'] = 'Poll sluit';
$txt['poll_expired_on'] = 'Poll gesloten';
$txt['poll_change_vote'] = 'Verwijder stem';
$txt['poll_return_vote'] = 'Stemopties';

// Untranslated!
$txt['quick_mod_approve'] = 'Approve selected';
$txt['quick_mod_remove'] = 'Verwijder selectie';
$txt['quick_mod_lock'] = 'Sluit selectie';
$txt['quick_mod_sticky'] = 'Maak selectie sticky';
$txt['quick_mod_move'] = 'Verplaats selectie naar';
$txt['quick_mod_merge'] = 'Voeg selectie samen';
$txt['quick_mod_markread'] = 'Markeer selectie als gelezen';
$txt['quick_mod_go'] = 'Voer uit!';
$txt['quickmod_confirm'] = 'Weet je zeker dat je dit wilt doen?';

$txt['spell_check'] = 'Spellingscontrole';

$txt['quick_reply'] = 'Snel beantwoorden';
$txt['quick_reply_desc'] = 'Met <i>Snel beantwoorden</i> kun je bulletin board code en smileys gebruiken zoals je dat zou doen in een normaal bericht, maar dan eenvoudiger.';
$txt['quick_reply_warning'] = '<b>Waarschuwing</b>: topic is op dit moment gesloten! Alleen moderators en administrators kunnen reageren.';
// Untranslated!
$txt['wait_for_approval'] = 'Note: this post will not display until it\'s been approved by a moderator.';

$txt['notification_enable_board'] = 'Weet je zeker dat je notificatie van nieuwe berichten voor dit board wilt activeren?';
$txt['notification_disable_board'] = 'Weet je zeker dat je notificatie van nieuwe berichten voor dit board wilt deactiveren?';
$txt['notification_enable_topic'] = 'Weet je zeker dat je notificatie van nieuwe berichten voor dit topic wilt activeren?';
$txt['notification_disable_topic'] = 'Weet je zeker dat je notificatie van nieuwe berichten voor dit topic wilt deactiveren?';

$txt['report_to_mod'] = 'Meld dit bericht aan de moderator';

$txt['unread_topics_visit'] = 'Recente ongelezen topics';
$txt['unread_topics_visit_none'] = 'Geen ongelezen topics gevonden sinds je laatste bezoek <a href="' . $scripturl . '?action=unread;all">Klik hier om alle ongelezen berichten te tonen</a>.';
$txt['unread_topics_all'] = 'Alle ongelezen berichten';
$txt['unread_replies'] = 'Ongelezen berichten';

$txt['who_title'] = 'Wie is online?';
$txt['who_and'] = ' en ';
$txt['who_viewing_topic'] = ' bekijken dit topic.';
$txt['who_viewing_board'] = ' bekijken dit board.';
$txt['who_member'] = 'Lid';

$txt['powered_by_php'] = 'Powered by PHP';
$txt['powered_by_mysql'] = 'Powered by MySQL';
$txt['valid_html'] = 'Valid HTML 4.01!';
$txt['valid_xhtml'] = 'Valid XHTML 1.0!';
$txt['valid_css'] = 'Valid CSS!';

$txt['guest'] = 'gast';
$txt['guests'] = 'gasten';
$txt['user'] = 'lid';
$txt['users'] = 'leden';

$txt['hidden'] = 'verborgen';
$txt['buddy'] = 'vriend';
$txt['buddies'] = 'vrienden';
$txt['most_online_ever'] = 'Meeste online ooit';
$txt['most_online_today'] = 'Meeste online vandaag';

$txt['merge_select_target_board'] = 'Selecteer het doel-board waar het samen te voegen topic terecht komt';
$txt['merge_select_poll'] = 'Selecteer welke poll het samengevoegde topic moet krijgen';
$txt['merge_topic_list'] = 'Selecteer de samen te voegen topics';
$txt['merge_select_subject'] = 'Selecteer het onderwerp van het samengevoegde topic';
$txt['merge_custom_subject'] = 'Aangepast onderwerp';
$txt['merge_enforce_subject'] = 'Verander het onderwerp van alle berichten';
$txt['merge_include_notifications'] = 'neem notificatie mee?';
$txt['merge_check'] = 'Samenvoegen?';
$txt['merge_no_poll'] = 'Geen poll';

$txt['response_prefix'] = 'Re: ';
$txt['current_icon'] = 'Huidige icoon';

$txt['smileys_current'] = 'Huidige Smileyset';
$txt['smileys_none'] = 'Geen Smileys';
$txt['smileys_forum_board_default'] = 'Forum- of boardstandaard';

$txt['search_results'] = 'Zoekresultaten';
$txt['search_no_results'] = 'Geen resultaten gevonden';

$txt['totalTimeLogged1'] = 'Totale tijd ingelogd: ';
$txt['totalTimeLogged2'] = ' dagen, ';
$txt['totalTimeLogged3'] = ' uren en ';
$txt['totalTimeLogged4'] = ' minuten.';
$txt['totalTimeLogged5'] = 'd ';
$txt['totalTimeLogged6'] = 'u ';
$txt['totalTimeLogged7'] = 'm';

$txt['approve_thereis'] = 'Er is';
$txt['approve_thereare'] = 'Er zijn';
$txt['approve_member'] = 'een lid';
$txt['approve_members'] = 'leden';
$txt['approve_members_waiting'] = 'in de wachtrij.';

$txt['notifyboard_turnon'] = 'Wil je een notificatie e-mail ontvangen wanneer iemand een nieuw discussietopic start in dit board?';
$txt['notifyboard_turnoff'] = 'Weet je zeker dat je notificatie-e-mails wilt ontvangen bij nieuwe discussietopics in dit board?';

$txt['activate_code'] = 'Je activeringscode is';

$txt['find_members'] = 'Zoek leden';
$txt['find_username'] = 'Naam, gebruikersnaam, of e-mailadres';
$txt['find_buddies'] = 'Toon alleen vrienden?';
$txt['find_wildcards'] = 'Toegestane jokertekens: *, ?';
$txt['find_no_results'] = 'Geen resultaten gevonden';
$txt['find_results'] = 'Resultaten';
$txt['find_close'] = 'Sluiten';

$txt['unread_since_visit'] = 'Toon ongelezen berichten sinds je laatste bezoek.';
$txt['show_unread_replies'] = 'Toon nieuwe reacties op jouw berichten.';

$txt['change_color'] = 'Pas de kleur aan';

$txt['quickmod_delete_selected'] = 'Verwijder selectie';

// In this string, don't use entities. (&amp;, etc.)
$txt['show_personal_messages'] = 'Je hebt één of meerdere nieuwe berichten.\\nWil je ze nu bekijken (in een nieuw venster)?';

$txt['previous_next_back'] = '&laquo; vorige';
$txt['previous_next_forward'] = 'volgende &raquo;';

$txt['movetopic_auto_board'] = '[BOARD]';
$txt['movetopic_auto_topic'] = '[TOPIC LINK]';
$txt['movetopic_default'] = 'Dit topic is verplaatst naar ' . $txt['movetopic_auto_board'] . ".\n\n" . $txt['movetopic_auto_topic'];

$txt['upshrink_description'] = 'Klap de kop in of uit.';

$txt['mark_unread'] = 'Markeer als ongelezen';

$txt['ssi_not_direct'] = 'Het is niet mogelijk om SSI.php direct per URL te benaderen; Gebruik het pad (%s) of voeg ?ssi_function=something toe.';
$txt['ssi_session_broken'] = 'SSI.php kon geen sessie laden! Dit zou tot problemen kunnen leiden bij het uitloggen en andere functies - zorg ervoor dat SSI.php ingevoegd staat voor ook maar *iets* anders in je scripts!';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['preview_title'] = 'Bekijk bericht';
$txt['preview_fetch'] = 'Haalt concept op...';
$txt['preview_new'] = 'Nieuw bericht';
$txt['error_while_submitting'] = 'Er is een fout opgetreden bij het versturen van dit bericht.';

$txt['split_selected_posts'] = 'Geselecteerde berichten';
$txt['split_selected_posts_desc'] = 'De berichten hieronder worden na het splitsen een nieuw topic.';
$txt['split_reset_selection'] = 'deselecteer alles';

$txt['modify_cancel'] = 'Annuleren';
$txt['mark_read_short'] = 'Markeer gelezen';

$txt['pm_short'] = 'Mijn berichten';
$txt['hello_member_ndt'] = 'Hallo';

// Untranslated!
$txt['unapproved_posts'] = 'Unapproved Posts (Topics: %d, Posts: %d)';
$txt['ajax_in_progress'] = 'Loading...';
$txt['mod_reports_waiting'] = 'There are currently %1$d moderator reports open.';
$txt['view_unread_category'] = 'Unread Posts';
?>
