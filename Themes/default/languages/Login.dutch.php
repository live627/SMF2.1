<?php
// Version: 2.0 Alpha; Login

// Important! Before editing these language files please read the text at the topic of index.dutch.php.

$txt[37] = 'Je moet wel een gebruikersnaam invullen.';
$txt[38] = 'Wachtwoord was leeg';
$txt[39] = 'Wachtwoord niet correct';
$txt[98] = 'Kies gebruikersnaam';
$txt[155] = 'Even geduld';
$txt[245] = 'Je registratie is gelukt!';
$txt[431] = 'Succes! Je bent een lid van dit forum.';
// Use numeric entities in the below string.
$txt[492] = 'en je wachtwoord is';
$txt[500] = 'Vul een geldig e-mailadres in. (%s)';
$txt[517] = 'Verplichte velden';
$txt[520] = 'Wordt alleen gebruikt voor indentificatie door SMF. Je kunt speciale karakters gebruiken nadat je bent ingelogd, door je naam aan te passen in je profiel.';
$txt[585] = 'Ik ga akkoord';
$txt[586] = 'Ik ga niet akkoord';
$txt[633] = 'Waarschuwing!';
$txt[634] = 'Alleen geregistreerde leden mogen in dit gedeelte komen.';
$txt[635] = 'Log hieronder in of klik op';
$txt[636] = 'deze link';
$txt[637] = 'om jezelf vrijblijvend te registreren op het {$context.forum_name} forum.';
// Use numeric entities in the below two strings.
$txt[701] = 'je kunt het nog veranderen nadat je bent ingelogd door naar je profiel te gaan:';
$txt[719] = 'Je gebruikersnaam is: ';
$txt[730] = 'Dit e-mailadres (%s) is al in gebruik door een geregistreerd lid! Als je denkt dat dit een vergissing is, ga dan naar de login pagina en gebruik de wachtwoord vergeten optie met dit e-mailadres.';

$txt['login_hash_error'] = 'De wachtwoordbeveiliging is recentelijk opgewaardeerd. Voer je wachtwoord opnieuw in.';

$txt['register_age_confirmation'] = 'Ik ben tenminste %d jaar oud';

// Use numeric entities in the below six strings.
$txt['register_subject'] = 'Welkom op ' . $context['forum_name'];

// For the below three messages, %1$s is the display name, %2$s is the username, %3$s is the password, %4$s is the activation code, and %5$s is the activation link (the last two are only for activation.)
$txt['register_immediate_message'] = 'Je bent nu geregistreerd met een account op {$context.forum_name}, %1$s!\\n\\nDe gebruikersnaam van je account is %2$s en je wachtwoord is %3$s.\\n\\nJe kunt je wachtwoord veranderen, nadat je bent ingelogd, door naar je profiel te gaan of door na het inloggen naar deze pagina te gaan:\\n\\n{$scripturl}?action=profile\\n\\n' . $txt[130];
$txt['register_activate_message'] = 'Je bent nu geregistreerd met een account bij {$context.forum_name}, %1$s!\\n\\nDe gebruikersnaam van je account is %2$s en je wachtwoord is %3$s (welke later kan worden aangepast)\\n\\nVoordat je kunt inloggen moet je eerst je account activeren. Om dat te doen klik op de volgende link:\\n\\n%5$s\\n\\nMocht je problemen hebben bij de activering, gebruik de code "%4$s".\\n\\n' . $txt[130];
$txt['register_pending_message'] = 'Je registratieverzoek bij {$context.forum_name} is ontvangen, %1$s.\\n\\nDe gebruikersnaam waarmee je geregistreerd bent was %2$s en het wachtwoord was %3$s.\\n\\nVoordat je in kunt loggen en gebruik kunt gaan maken van het forum, moet je verzoek beoordeeld en goedgekeurd worden.  Als dit gebeurt, ontvang je een andere e-mail vanaf dit adres.\\n\\n' . $txt[130];

// For the below two messages, %1$s is the user's display name, %2$s is their username, %3$s is the activation code, and %4$s is the activation link (the last two are only for activation.)
$txt['resend_activate_message'] = 'Je bent nu geregistreerd met een account bij {$context.forum_name}, %1$s!\\n\\nJe gebruikersnaam is "%2$s".\\n\\nVoordat je in kunt loggen moet je eerst je account activeren. Om dat te doen, klik op de volgende link:\\n\\n%4$s\\n\\nMocht je problemen hebben bij de activering, gebruik de code "%3$s".\\n\\n' . $txt[130];
$txt['resend_pending_message'] = 'Je registratieverzoek bij {$context.forum_name} is ontvangen, %1$s.\\n\\nDe gebruikersnaam waarmee je geregistreerd bent was %2$s.\\n\\nVoordat je in kunt loggen en beginnen met het gebruik van het forum moet je verzoek eerst beoordeeld en goedgekeurd worden. Als dit gebeurt, ontvang je een andere e-mail vanaf dit adres.\\n\\n' . $txt[130];

$txt['ban_register_prohibited'] = 'Sorry, je mag je niet registreren op dit forum';
$txt['under_age_registration_prohibited'] = 'Sorry, maar gebruikers onder de leeftijd van %d mogen zich niet registreren op dit forum';

$txt['activate_account'] = 'Accountactivering';
$txt['activate_success'] = 'Je accountactivering is succesvol. Je kunt nu inloggen.';
$txt['activate_not_completed1'] = 'Je emailadres moet eerst gevalideerd worden voor je kunt inloggen.';
$txt['activate_not_completed2'] = 'Een nieuwe activeringsemail nodig?';
$txt['activate_after_registration'] = 'Bedankt voor het registeren. Je ontvangt een e-mail met een link om je account te activeren.  Als je na verloop van tijd geen e-mail hebt ontvangen, check dan je spamfolder.';
$txt['invalid_userid'] = 'Gebruiker bestaat niet';
$txt['invalid_activation_code'] = 'Ongeldige activering code';
$txt['invalid_activation_username'] = 'Gebruikersnaam of e-mailadres';
$txt['invalid_activation_new'] = 'Type als je met een foutief e-mailadres bent geregistreerd het juiste e-mailadres en je wachtwoord hier in.';
$txt['invalid_activation_new_email'] = 'Nieuw e-mailadres';
$txt['invalid_activation_password'] = 'Oude wachtwoord';
$txt['invalid_activation_resend'] = 'Activeringscode opnieuw versturen';
$txt['invalid_activation_known'] = 'Als je de activeringscode al kent, kun je die hier intypen.';
$txt['invalid_activation_retry'] = 'Activeringscode';
$txt['invalid_activation_submit'] = 'Activeren';

$txt['coppa_not_completed1'] = 'De forumbeheerder heeft nog geen toestemming van je ouders/voogden ontvangen voor je account.';
$txt['coppa_not_completed2'] = 'Meer details nodig?';

$txt['awaiting_delete_account'] = 'Je hebt je account verwijderd! Als je je account wilt herstellen, vink &quot;Herstel mijn account&quot; aan en probeer opnieuw.';
$txt['undelete_account'] = 'Herstel mijn account';

$txt['change_email_success'] = 'Je e-mailadres is veranderd en een nieuwe activeringsmail is verstuurd naar het nieuwe adres.';
$txt['resend_email_success'] = 'Een nieuwe activeringsmail is succesvol verstuurd.';
// Use numeric entities in the below three strings.
$txt['change_password'] = 'Nieuwe wachtwoord gegevens';
$txt['change_password_1'] = 'Je inloggegevens voor';
$txt['change_password_2'] = 'zijn veranderd en je wachtwoord is opnieuw ingesteld. Hieronder zijn je nieuwe inlogdetails.';

$txt['maintenance3'] = 'Het forum bevindt zich in de onderhoudsmodus.';

// These two are used as a javascript alert; please use international characters directly, not as entities.
$txt['register_agree'] = 'Lees/accepteer de regels om dit formulier in te sturen.';
$txt['register_passwords_differ_js'] = 'Wachtwoord is niet hetzelfde!';

$txt['approval_after_registration'] = 'Bedankt voor je registratie. De admin moet je registratie goedkeuren alvorens je kunt inloggen. Je ontvangt hierover zeer binnenkort een e-mail.';

$txt['admin_settings_desc'] = 'Hier kun je diverse instellingen met betrekking tot registratie van nieuwe leden veranderen.';

$txt['admin_setting_registration_method'] = 'Registratiemethode voor nieuwe leden';
$txt['admin_setting_registration_disabled'] = 'Registratie niet geactiveerd';
$txt['admin_setting_registration_standard'] = 'Directe registratie';
$txt['admin_setting_registration_activate'] = 'Activeren leden';
$txt['admin_setting_registration_approval'] = 'Toestaan leden';
$txt['admin_setting_notify_new_registration'] = 'Breng administrators op de hoogte als een nieuw lid zich geregistreerd heeft';
$txt['admin_setting_send_welcomeEmail'] = 'Stuur een welkomst-e-mail naar het nieuwe lid als je geen wachtwoord via e-mail verstuurt?';

$txt['admin_setting_password_strength'] = 'Vereiste kwaliteit van gebruikerswachtwoorden';
$txt['admin_setting_password_strength_low'] = 'Laag - minimaal 4 karakters';
$txt['admin_setting_password_strength_medium'] = 'Gemiddeld - mag niet de gebruiksnaam bevatten';
$txt['admin_setting_password_strength_high'] = 'Hoog - mix van verschillende karakters';

//!!! Untranslated
$txt['admin_setting_disable_visual_verification'] = 'Disable the use of the visual verification on registration';

$txt['admin_setting_coppaAge'] = 'Leeftijd waar beneden de resticties gelden';
$txt['admin_setting_coppaAge_desc'] = '(Stel op  0 in om uit te schakelen)';
$txt['admin_setting_coppaType'] = 'Te nemen actie als een gebruiker onder de minimumleeftijd zich registreert';
$txt['admin_setting_coppaType_reject'] = 'Verwerp de registratie';
$txt['admin_setting_coppaType_approval'] = 'Vereis ouderlijke goedkeuring';
$txt['admin_setting_coppaPost'] = 'Postadres waar het goedkeuringsformulier naar toe gestuurd moet worden';
$txt['admin_setting_coppaPost_desc'] = 'Alleen van toepassing als de leeftijdsrestrictie ingesteld staat';
$txt['admin_setting_coppaFax'] = 'Faxnummer waar de goedkeuring naar gefaxt kan worden';
$txt['admin_setting_coppaPhone'] = 'Telefoonnummer voor ouders om contact op te nemen voor leeftijdsrestrictievragen';
$txt['admin_setting_coppa_require_contact'] = 'Je moet of een postadres of een faxnummer invoeren als ouderlijke goedkeuring vereist is.';

$txt['admin_register'] = 'Registratie van een nieuw lid';
$txt['admin_register_desc'] = 'Vanaf hier kun je nieuwe leden registreren en hen, indien gewenst, de details mailen.';
$txt['admin_register_username'] = 'Nieuwe gebruikersnaam';
$txt['admin_register_email'] = 'E-mailadres';
$txt['admin_register_password'] = 'Wachtwoord';
$txt['admin_register_username_desc'] = 'Gebruikersnaam van het nieuwe lid';
$txt['admin_register_email_desc'] = 'E-mail van het lid';
$txt['admin_register_password_desc'] = 'Nieuw wachtwoord voor gebruiker';
$txt['admin_register_email_detail'] = 'E-mail nieuw wachtwoord naar gebruiker';
$txt['admin_register_email_detail_desc'] = 'E-mailadres vereist zelfs indien niet aangevinkt';
$txt['admin_register_email_activate'] = 'Gebruiker dient zijn account te activeren';
$txt['admin_register_group'] = 'Primaire ledengroep';
$txt['admin_register_group_desc'] = 'Primaire ledengroep waar het nieuwe lid toe zal behoren';
$txt['admin_register_group_none'] = '(geen primaire ledengroep)';
$txt['admin_register_done'] = 'Lid %s succesvol geregistreerd!';

$txt['admin_browse_register_new'] = 'Registreer nieuw lid';

// Use numeric entities in the below three strings.
$txt['admin_notify_subject'] = 'Een nieuw lid heeft zich geregistreerd.';
$txt['admin_notify_profile'] = '%s heeft zich net als nieuw lid op je forum geregistreerd. Klik op de link onderaan om zijn/haar profiel te bekijken.';
$txt['admin_notify_approval'] = 'Voordat dit lid kan beginnen met het plaatsen van berichten moet zijn account worden goedgekeurd. Klik op de link hieronder om naar het goedkeuringsscherm te gaan.';

$txt['coppa_title'] = 'Leeftijdsbeperkt forum';
$txt['coppa_after_registration'] = 'Dank je voor je regstratie op {$context.forum_name}.<br /><br />Als lid onder de leeftijd van {MINIMUM_AGE} jaar is er een wettelijke verplichting om de goedkeuring van ouders of voogd te krijgen voordat je gebruik mag maken van je account. Print onderstaand formulier uit om activering van je account te regelen:';
$txt['coppa_form_link_popup'] = 'Laad het formulier in een nieuw venster';
$txt['coppa_form_link_download'] = 'Download formulier';
$txt['coppa_send_to_one_option'] = 'Zorg er daarna voor dat je ouders of voogd het ingevulde formulier opsturen per:';
$txt['coppa_send_to_two_options'] = 'Zorg er daarna voor dat je ouders of voogd het ingevulde formulier opsturen per:';
$txt['coppa_send_by_post'] = 'Post, naar het volgende adres:';
$txt['coppa_send_by_fax'] = 'Fax, naar het volgende nummer:';
$txt['coppa_send_by_phone'] = 'Eventueel kun je hen ook laten bellen naar de forumbeheerder op {PHONE_NUMBER}.';

$txt['coppa_form_title'] = 'Toestemmingsformulier voor registratie op ' . $context['forum_name'];
$txt['coppa_form_address'] = 'Adres';
$txt['coppa_form_date'] = 'Datum';
$txt['coppa_form_body'] = 'Ik {PARENT_NAME},<br /><br />geef toestemming aan {CHILD_NAME} (naam kind) om volwaardig geregistreerd lid te worden op het forum: {$context.forum_name}, met de gebruikersnaam: {USER_NAME}.<br /><br />Ik begrijp dat bepaalde persoonlijke informatie door {USER_NAME} ingevuld getoond kan worden aan andere gebruikers van het forum.<br /><br />Getekend:<br />{PARENT_NAME} (Ouder/Voogd).';

$txt['visual_verification_label'] = 'Visuele verificatie';
$txt['visual_verification_description'] = 'Typ de letters die getoond worden in het plaatje';
$txt['visual_verification_sound'] = 'Luister naar de letters';
$txt['visual_verification_sound_again'] = 'Speel opnieuw';
$txt['visual_verification_sound_close'] = 'Sluit venster';
// Untranslated!
$txt['visual_verification_request_new'] = 'Request another image';
$txt['visual_verification_sound_direct'] = 'Having problems hearing this?  Try a direct link to it.';

?>