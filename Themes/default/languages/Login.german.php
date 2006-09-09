<?php
// Version: 2.0 Alpha; Login

// Important! Before editing these language files please read the text at the topic of index.german.php.

$txt[37] = 'Bitte einen Benutzernamen eingeben.';
$txt[38] = 'Das Passwortfeld ist leer.';
$txt[39] = 'Das Passwort ist falsch.';
$txt[98] = 'W&auml;hle Sie einen Benutzernamen';
$txt[155] = 'Wartungsmodus';
$txt[245] = 'Die Registrierung war erfolgreich!';
$txt[431] = 'Erfolgreich! Sie sind nun ein Mitglied des Forums';
// Use numeric entities in the below string.
$txt[492] = 'und Ihr Passwort ist';
$txt[500] = 'Bitte geben Sie eine g&uuml;ltige E-Mail Adresse ein, (%s)';
$txt[517] = 'Notwendige Angaben';
$txt[520] = 'Wird nur zur Identifizierung von SMF verwendet. Spezialzeichen k&ouml;nnen Sie nach dem Login verwenden, indem Sie den Anzeigenamen im Profil &auml;ndern.';
$txt[585] = 'Ich bin einverstanden';
$txt[586] = 'Ich bin nicht einverstanden';
$txt[633] = 'Warnung!';
$txt[634] = 'Nur registrierte Mitglieder haben Zugriff auf diesen Bereich.';
$txt['login_below'] = 'Bitte einloggen oder';
$txt['login_or_register'] = 'ein neues Benutzerkonto registrieren';
$txt['login_with_forum'] = 'im %s.';
// Use numeric entities in the below two strings.
$txt[701] = 'Nach dem Einloggen k&#246;nnen Sie es in Ihrem Profil &#228;ndern oder folgende Seite benutzen:';
$txt[719] = 'Ihr Benutzername ist: ';

$txt['login_hash_error'] = 'Die Passwortsicherheit wurde aktualisiert. Bitte geben Sie Ihr Passwort erneut ein.';

$txt['register_age_confirmation'] = 'Ich bin mindestens %d Jahre alt';

// Use numeric entities in the below six strings.
$txt['register_subject'] = 'Willkommen im ' . $context['forum_name'];

// For the below three messages, %1$s is the display name, %2$s is the username, %3$s is the password, %4$s is the activation code, and %5$s is the activation link (the last two are only for activation.)
$txt['register_immediate_message'] = 'Sie sind jetzt mit einem Benutzerkonto im {$forumname} registriert, %1$s!\\n\\nIhr Benutzername ist %2$s und das Passwort lautet %3$s.\\n\\nSie k&#246;nnen Ihr Passwort nach dem Einloggen &#228;ndern, indem Sie in Ihr Profil gehen oder folgende Seite besuchen:\\n\\n{$scripturl}?action=profile\\n\\n{$regards}';
$txt['register_activate_message'] = 'Sie sind jetzt mit einem Benutzerkonto im {$forumname} registriert, %1$s!\\n\\nIhr Benutzername ist %2$s und das Passwort lautet %3$s (es kann sp&#228;ter ge&#228;ndert werden).\\n\\nBevor Sie sich einloggen k&#246;nnen, m&#252;ssen Sie auf folgender Seite Ihr Benutzerkonto aktivieren:\\n\\n%5$s\\n\\nSollten Sie Probleme mit der Aktivierung haben, benutzen Sie bitte diesen Code "%4$s".\\n\\n{$regards}';
$txt['register_pending_message'] = 'Ihre Registrierung im {$forumname} haben wir erhalten, %1$s.\\n\\nIhr gew&#228;hlter Benutzername ist %2$s und das Passwort lautet %3$s.\\n\\nBevor Sie sich einloggen und das Forum benutzen k&#246;nnen, muss Ihr Benutzerkonto zuerst vom Administrator genehmigt werden. Wenn das erfolgt ist, erhalten Sie eine weitere E-Mail.\\n\\n{$regards}';

// For the below two messages, %1$s is the user's display name, %2$s is their username, %3$s is the activation code, and %4$s is the activation link (the last two are only for activation.)
$txt['resend_activate_message'] = 'Sie sind jetzt mit einem Benutzerkonto im {$forumname} registriert, %1$s!\\n\\nIhr Benutzername ist "%2$s".\\n\\nBevor Sie sich einloggen k&#246;nnen, m&#252;ssen Sie auf folgender Seite Ihr Benutzerkonto aktivieren:\\n\\n%4$s\\n\\nSollten Sie Probleme mit der Aktivierung haben, benutzen Sie bitte diesen Code "%3$s".\\n\\n{$regards}';
$txt['resend_pending_message'] = 'Ihre Registrierung im {$forumname} haben wir erhalten, %1$s.\\n\\nIhr gew&#228;hlter Benutzername ist %2$s.\\n\\nBevor Sie sich einloggen und das Forum benutzen k&#246;nnen, muss Ihr Benutzerkonto zuerst genehmigt werden. Wenn das erfolgt ist, erhalten Sie eine weitere E-Mail.\\n\\n{$regards}';

$txt['ban_register_prohibited'] = 'Sie haben nicht die Erlaubnis, sich in diesem Forum zu registrieren';
$txt['under_age_registration_prohibited'] = 'Benutzer, die j&uuml;nger als %d Jahre sind, d&uuml;rfen sich in diesem Forum nicht registrieren';
$txt['activate_account'] = 'Kontoaktivierung';
$txt['activate_success'] = 'Ihr Benutzerkonto wurde erfolgreich aktiviert. Sie k&ouml;nnen sich jetzt einloggen.';
$txt['activate_not_completed1'] = 'Ihre E-Mail Adresse muss vor dem Einloggen &uuml;berpr&uuml;ft werden.';
$txt['activate_not_completed2'] = 'Brauchen Sie eine erneute Aktivierungs E-Mail?';
$txt['activate_after_registration'] = 'Danke f&uuml;r die Registrierung. Sie werden in K&uuml;rze eine E-Mail erhalten, mit der Sie Ihr Benutzerkonto aktivieren k&ouml;nnen. Sollten Sie nach einiger Zeit keine E-Mail erhalten haben, pr&uuml;fen Sie bitte Ihren Spam-Ordner.';
$txt['invalid_userid'] = 'Der Benutzer existiert nicht';
$txt['invalid_activation_code'] = 'Ung&uuml;ltiger Aktivierungscode';
$txt['invalid_activation_username'] = 'Benutzername oder E-Mail';
$txt['invalid_activation_new'] = 'Wenn Sie sich mit einer falschen E-Mail Adresse registriert haben, geben Sie hier eine neue Adresse und Ihr Passwort ein.';
$txt['invalid_activation_new_email'] = 'Neue E-Mail Adresse';
$txt['invalid_activation_password'] = 'Altes Passwort';
$txt['invalid_activation_resend'] = 'Aktivierungscode erneut senden';
$txt['invalid_activation_known'] = 'Wenn Sie Ihren Aktivierungscode wissen, tragen Sie ihn hier ein.';
$txt['invalid_activation_retry'] = 'Aktivierungscode';
$txt['invalid_activation_submit'] = 'Aktivieren';

$txt['coppa_not_completed1'] = 'Der Administrator hat noch keine Einwilligung der Eltern/Erziehungsberechtigten f&uuml;r Ihr Benutzerkonto erhalten.';
$txt['coppa_not_completed2'] = 'Weitere Details?';

$txt['awaiting_delete_account'] = 'Sie haben Ihr Benutzerkonto gel&ouml;scht! M&ouml;chten Sie es wiederherstellen, aktivieren Sie die &quot;Wiederherstellen meines Benutzerkontos&quot; Option und versuchen Sie es erneut.';
$txt['undelete_account'] = 'Wiederherstellen meines Benutzerkontos';

// Use numeric entities in the below three strings.
$txt['change_password'] = 'Neue Login-Daten';
$txt['change_password_1'] = 'Ihre Login-Daten von';
$txt['change_password_2'] = 'haben sich ge&#228;ndert und Ihr Passwort wurde annulliert. Es folgen Ihre neuen Daten.';

$txt['maintenance3'] = 'Dieses Forum ist im Wartungsmodus.';

// These two are used as a javascript alert; please use international characters directly, not as entities.
$txt['register_agree'] = 'Bitte lesen und akzeptieren Sie die Regeln.';
$txt['register_passwords_differ_js'] = 'Passwörter stimmen nicht überein.';

$txt['approval_after_registration'] = 'Danke f&uuml;r die Registrierung. Der Administrator muss Ihre Registrierung genehmigen, bevor Sie Ihr Benutzerkonto benutzen k&ouml;nnen. Sie werden in K&uuml;rze eine E-Mail mit der Entscheidung erhalten.';

$txt['admin_settings_desc'] = 'Hier k&ouml;nnen Sie verschiedene Einstellungen bez&uuml;glich der Registrierung neuer Mitglieder machen.';

$txt['admin_setting_registration_method'] = 'Registrierungsmethode bei neuen Mitgliedern';
$txt['admin_setting_registration_disabled'] = 'Registrierung deaktivieren';
$txt['admin_setting_registration_standard'] = 'Sofortige Registrierung';
$txt['admin_setting_registration_activate'] = 'Neue Mitglieder aktivieren';
$txt['admin_setting_registration_approval'] = 'Neue Mitglieder genehmigen';
$txt['admin_setting_notify_new_registration'] = 'Administrator benachrichtigen, wenn sich ein neuer Benutzer anmeldet?';
$txt['admin_setting_send_welcomeEmail'] = 'Willkommens E-Mail auch senden, wenn \'Neues Passwort zusenden\' nicht aktiviert ist?';

$txt['admin_setting_password_strength'] = 'Ben&ouml;tigtes Level des Passwortes';
$txt['admin_setting_password_strength_low'] = 'Niedrig - mind. 4 Zeichen';
$txt['admin_setting_password_strength_medium'] = 'Mittel - kann Benutzernamen nicht enthalten';
$txt['admin_setting_password_strength_high'] = 'Hoch - ein Mix aus Buchstaben und Zahlen';

//!!! Untranslated
$txt['admin_setting_disable_visual_verification'] = 'Disable the use of the visual verification on registration';


$txt['admin_setting_coppaAge'] = 'Altersgrenze f&uuml;r Registrierungsbeschr&auml;nkung';
$txt['admin_setting_coppaAge_desc'] = '(0 zum deaktivieren)';
$txt['admin_setting_coppaType'] = 'Aktion nach Registrierung unterhalb der Altersgrenze';
$txt['admin_setting_coppaType_reject'] = 'Registrierung ablehnen';
$txt['admin_setting_coppaType_approval'] = 'Erfordert die Genehmigung von Eltern/Erziehungsberechtigten';
$txt['admin_setting_coppaPost'] = 'Postadresse, an welche die Genehmigung gesendet werden soll';
$txt['admin_setting_coppaPost_desc'] = 'Nur ben&ouml;tigt, wenn Altersbeschr&auml;nkung aktiviert';
$txt['admin_setting_coppaFax'] = 'Faxnummer, an welche die Genehmigung gefaxt werden soll';
$txt['admin_setting_coppaPhone'] = 'Kontaktnummer f&uuml;r Eltern/Erziehungsberechtigte mit Fragen zur Altersbegrenzung';

$txt['admin_register'] = 'Registrierung neuer Mitglieder';
$txt['admin_register_desc'] = 'Hier k&ouml;nnen Sie neue Mitglieder im Forum registrieren und Ihnen bei Bedarf die Details per E-Mail schicken.';
$txt['admin_register_username'] = 'Benutzername';
$txt['admin_register_email'] = 'E-Mail Adresse';
$txt['admin_register_password'] = 'Passwort';
$txt['admin_register_username_desc'] = 'Benutzername f&uuml;r neues Mitglied';
$txt['admin_register_email_desc'] = 'E-Mail Adresse des Benutzers';
$txt['admin_register_password_desc'] = 'Passwort f&uuml;r den Benutzer';
$txt['admin_register_email_detail'] = 'Schicken Sie das neue Passwort an den Benutzer';
$txt['admin_register_email_detail_desc'] = 'E-Mail Adresse wird ben&ouml;tigt, auch wenn es deaktiviert ist';
$txt['admin_register_email_activate'] = 'Ben&ouml;tige Benutzer um Benutzerkonto zu aktivieren';
$txt['admin_register_group'] = 'Prim&auml;re Mitgliedergruppe';
$txt['admin_register_group_desc'] = 'Prim&auml;re Mitgliedergruppe, zu welcher Benutzer hinzugef&uuml;gt werden soll';
$txt['admin_register_group_none'] = '(keine prim&auml;re Mitgliedergruppe)';
$txt['admin_register_done'] = 'Benutzer %s wurde erfolgreich registriert!';

$txt['admin_browse_register_new'] = 'Neues Mitglied registrieren';

// Use numeric entities in the below three strings.
$txt['admin_notify_subject'] = 'Ein neuer Benutzer hat sich angemeldet';
$txt['admin_notify_profile'] = '%s hat sich als neues Mitglied in Ihrem Forum angemeldet. Klicken Sie folgenden Link, um das Profil zu betrachten.';
$txt['admin_notify_approval'] = 'Bevor das Mitglied Beitr&#228;ge schreiben kann, muss das Benutzerkonto mit Hilfe des folgenden Links zuerst genehmigt werden.';

$txt['coppa_title'] = 'Altersbeschr&auml;nktes Forum';
$txt['coppa_after_registration'] = 'Du hast Dich im {$forumname} registriert.<br /><br />Du f&auml;llst als Benutzer unter die Altergrenze von {MINIMUM_AGE}. Daher ist eine Genehmigung von Deinen Eltern/Erziehungsberechtigten erforderlich, welche Dir erlaubt das Benutzerkonto zu benutzen. Um das Benutzerkonto aktivieren zu lassen, drucke bitte folgendes Formular aus:';
$txt['coppa_form_link_popup'] = 'Lade das Formular in ein neues Fenster';
$txt['coppa_form_link_download'] = 'Lade das Formular herunter';
$txt['coppa_send_to_one_option'] = 'Danach m&uuml;ssen Deine Eltern/Erziehungsberechtigte das Formular per:';
$txt['coppa_send_to_two_options'] = 'Danach m&uuml;ssen Deine Eltern/Erziehungsberechtigte das Formular entweder per:';
$txt['coppa_send_by_post'] = 'Post an folgende Adresse senden:';
$txt['coppa_send_by_fax'] = 'Fax an folgende Nummer senden:';
$txt['coppa_send_by_phone'] = 'Alternativ k&ouml;nnen Sie auch den Administrator unter folgender Nummer anrufen: {PHONE_NUMBER}.';

$txt['coppa_form_title'] = 'Erlaubnis zum Registrieren im ' . $context['forum_name'];
$txt['coppa_form_address'] = 'Adresse';
$txt['coppa_form_date'] = 'Datum';
$txt['coppa_form_body'] = 'Ich, {PARENT_NAME},<br /><br />erlaube {CHILD_NAME} (Name des Kindes) ein registrierter Benutzer des Forums {$forumname} mit dem Benutzernamen {USER_NAME} zu werden.<br /><br />Ich bin damit einverstanden, dass bestimmte pers&ouml;nliche Angaben von {USER_NAME} auch von anderen Benutzern im Forum einsehbar sind.<br /><br />Unterschrift:<br />{PARENT_NAME} (Eltern/Erziehungsberechtigte).';

// Untranslated!
$txt['visual_verification_label'] = 'Visual verification';
$txt['visual_verification_description'] = 'Type the letters shown in the picture';
$txt['visual_verification_sound'] = 'Listen to the letters';
$txt['visual_verification_sound_again'] = 'Play again';
$txt['visual_verification_sound_close'] = 'Close window';
$txt['visual_verification_request_new'] = 'Request another image';
$txt['visual_verification_sound_direct'] = 'Having problems hearing this?  Try a direct link to it.';

?>