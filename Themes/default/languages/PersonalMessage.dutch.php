<?php
// Version: 2.0 Alpha; PersonalMessage

// Important! Before editing these language files please read the text at the topic of index.dutch.php.

$txt[143] = 'Persoonlijke berichten index';
$txt[148] = 'Verzend bericht';
$txt[150] = 'Aan';
$txt[1502] = 'Bcc';
$txt[316] = 'Inbox';
// Untranslated!
$txt['sent_items'] = 'Sent Items';
$txt[321] = 'Nieuw bericht';
$txt[411] = 'Verwijder berichten';
// Don't translate "PMBOX" in this string.
$txt[412] = 'Verwijder alle berichten in je inbox of uitbox';
$txt[413] = 'Weet je zeker dat je alle berichten wil verwijderen?';
$txt[535] = 'Ontvanger';
// Don't translate the word "SUBJECT" here, as it is used to format the message - use numeric entities as well.
$txt[561] = 'Nieuw persoonlijk bericht: SUBJECT';
// Don't translate SENDER or MESSAGE in this language string; they are replaced with the corresponding text - use numeric entities too.
$txt[562] = 'Je hebt zojuist een persoonlijk bericht ontvangen van SENDER op ' . $context['forum_name'] . '.' . "\n\n" . 'BELANGRIJK: Onthoud dat dit alleen maar een notificatie is. Reageer niet op deze e-mail.' . "\n\n" . 'Het bericht wat je werd gestuurd was:' . "\n\n" . 'MESSAGE';
$txt[748] = '(meerdere geadresseerden als \'naam1, naam2\')';
// Use numeric entities in the below string.
$txt['instant_reply'] = 'Beantwoord dit persoonlijk bericht hier:';

$txt['smf249'] = 'Weet je zeker dat je alle geselecteerde berichten wilt verwijderen?';

$txt['sent_to'] = 'Verzonden aan';
$txt['reply_to_all'] = 'Reageer op allen';

$txt['pm_capacity'] = 'Capaciteit';
$txt['pm_currently_using'] = '%s berichten, %s%% vol.';

$txt['pm_error_user_not_found'] = 'Kan gebruiker \'%s\' niet vinden.';
$txt['pm_error_ignored_by_user'] = 'Gebruiker \'%s\' heeft je PM geblokkeerd.';
$txt['pm_error_data_limit_reached'] = 'PM kon niet verstuurd worden aan \'%s\' vanwege het overschrijden van de PM limiet.';
$txt['pm_successfully_sent'] = 'PM succesvol verstuurd aan \'%s\'.';
$txt['pm_send_report'] = 'Verzendrapport';
$txt['pm_save_outbox'] = 'Bewaar een kopie in mijn postvak uit';
$txt['pm_undisclosed_recipients'] = 'Niet-getoonde ontvangers';

$txt['pm_read'] = 'Gelezen';
$txt['pm_replied'] = 'Geantwoord aan';

$txt['pm_prune'] = 'Opschonen berichten';
$txt['pm_prune_desc1'] = 'Verwijder alle persoonlijke berichten ouder dan';
$txt['pm_prune_desc2'] = 'dagen.';
$txt['pm_prune_warning'] = 'Weet je zeker dat je je persoonlijke berichten wilt opschonen?';

$txt['pm_actions_title'] = 'Extra acties';
$txt['pm_actions_delete_selected'] = 'Verwijder selectie';
$txt['pm_actions_filter_by_label'] = 'Filter op basis van labels';
$txt['pm_actions_go'] = 'Voer uit';

$txt['pm_apply'] = 'Pas toe';
$txt['pm_manage_labels'] = 'Beheer labels';
$txt['pm_labels_delete'] = 'Weet je zeker dat je de geselecteerde labels wilt verwijderen?';
$txt['pm_labels_desc'] = 'Vanaf hier kun je labels toevoegen, bewerken of verwijderen die gebruikt worden in je persoonlijke berichten centrum.';
$txt['pm_label_add_new'] = 'Voeg nieuw label toe';
$txt['pm_label_name'] = 'Label naam';
$txt['pm_labels_no_exist'] = 'Je hebt op dit moment geen labels ingesteld!';

$txt['pm_current_label'] = 'Label';
$txt['pm_msg_label_title'] = 'Labels';
$txt['pm_msg_label_apply'] = 'Voeg label toe';
$txt['pm_msg_label_remove'] = 'Verwijder label';
$txt['pm_msg_label_inbox'] = 'Inbox';
$txt['pm_sel_label_title'] = 'Geselecteerde label';

$txt['pm_labels'] = 'Labels';
$txt['pm_messages'] = 'Berichten';
// Untranslated!
$txt['pm_actions'] = 'Actions';
$txt['pm_preferences'] = 'Voorkeuren';

$txt['pm_is_replied_to'] = 'Je hebt dit bericht doorgestuurd of beantwoord.';

// Reporting messages.
$txt['pm_report_to_admin'] = 'Rapporteer aan de beheerder';
$txt['pm_report_title'] = 'Rapporteer dit bericht';
$txt['pm_report_desc'] = 'Vanuit deze pagina kun je het persoonlijke bericht dat je hebt gekregen rapporteren aan het beheerteam. Ben er zeker van een beschrijving bij te voegen waarin je uitlegt waarom je het bericht rapporteerd, omdat dit meeverzonden wordt bij het originele bericht.';
$txt['pm_report_admins'] = 'Verstuur naar de beheerder';
$txt['pm_report_all_admins'] = 'Verstuur naar alle beheerders';
$txt['pm_report_reason'] = 'Reden waarom je dit bericht rapporteert';
$txt['pm_report_message'] = 'Rapporteer bericht';

// Important - The following strings should use numeric entities.
$txt['pm_report_pm_subject'] = '[REPORT] ';
// In the below string, do not translate "{REPORTER}" or "{SENDER}".
$txt['pm_report_pm_user_sent'] = '{REPORTER} heeft onderstaand bericht, verzonden door {SENDER} gerapporteerd, met de volgende reden:';
$txt['pm_report_pm_other_recipients'] = 'Andere ontvangers van dit bericht waren:';
$txt['pm_report_pm_hidden'] = '%d verborgen ontvanger(s)';
$txt['pm_report_pm_unedited_below'] = 'Hieronder staat de originele inhoud van het bericht dat gerapporteerd werd:';
$txt['pm_report_pm_sent'] = 'Verzonden op:';

$txt['pm_report_done'] = 'Bedankt voor het rapporteren van dit bericht. Je zult spoedig een reactie van het beheerteam krijgen.';
$txt['pm_report_return'] = 'Terug naar je postvak in';

$txt['pm_search_title'] = 'Zoeken naar Persoonlijke Berichten';
$txt['pm_search_bar_title'] = 'Zoek berichten';
$txt['pm_search_text'] = 'Zoek naar';
$txt['pm_search_go'] = 'Zoeken';
$txt['pm_search_advanced'] = 'Geavanceerd zoeken';
$txt['pm_search_user'] = 'per gebruiker';
$txt['pm_search_match_all'] = 'Zoek naar alle woorden';
$txt['pm_search_match_any'] = 'Zoek naar enkele woorden';
$txt['pm_search_options'] = 'Opties';
$txt['pm_search_post_age'] = 'Leeftijd';
$txt['pm_search_show_complete'] = 'Toon het volledige bericht in de resultaten.';
$txt['pm_search_subject_only'] = 'Zoek alleen naar onderwerp en auteur.';
$txt['pm_search_between'] = 'Tussen';
$txt['pm_search_between_and'] = 'en';
$txt['pm_search_between_days'] = 'dagen';
$txt['pm_search_order'] = 'Resultaten ordenen op';
$txt['pm_search_choose_label'] = 'Kies de labels waarbinnen gezocht moet worden, of selecteer alles';

$txt['pm_search_results'] = 'Zoekresultaten';
$txt['pm_search_none_found'] = 'Geen berichten gevonden';

$txt['pm_search_orderby_relevant_first'] = 'Meest relevante eerst';
$txt['pm_search_orderby_recent_first'] = 'Meest recente eerst';
$txt['pm_search_orderby_old_first'] = 'Oudste eerst';

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

$txt[325] = 'Negeerlijstje';
$txt[326] = 'Iedere regel slechts 1 gebruikersnaam !';
$txt[327] = 'Stuur een e-mail zodra je een persoonlijk bericht krijgt:';
$txt['email_notify_never'] = 'Nooit';
$txt['email_notify_buddies'] = 'Alleen van vrienden';
$txt['email_notify_always'] = 'Altijd';

$txt['copy_to_outbox'] = 'Sla standaard een Persoonlijk Bericht op in m\'n postvak uit.';
$txt['popup_messages'] = 'Een pop-up scherm tonen indien je een nieuwe persoonlijk bericht hebt?';
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