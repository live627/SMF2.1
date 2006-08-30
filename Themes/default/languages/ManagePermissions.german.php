<?php
// Version: 2.0 Alpha; ManagePermissions

$txt['permissions_title'] = 'Berechtigungen editieren';
$txt['permissions_modify'] = '&Auml;ndern';
$txt['permissions_access'] = 'Zugriff';
$txt['permissions_allowed'] = 'Erlaubt';
$txt['permissions_denied'] = 'Verboten';

$txt['permissions_switch'] = 'Wechseln zu';
$txt['permissions_global'] = 'Global';
$txt['permissions_local'] = 'Lokal';

// Untranslated!
$txt['permissions_by_profile'] = 'Permissions by Profile';
$txt['permissions_profile'] = 'Profile';
$txt['permissions_profiles_change_for_board'] = 'Edit Permission Profile For: &quot;%s&quot;';
$txt['permissions_profiles_select_type'] = 'Type of permission profile to use';
$txt['permissions_profiles_predefined'] = 'Predefined Profile';
$txt['permissions_profiles_as_board'] = 'Use Custom Profile From';
$txt['permissions_profiles_custom_type'] = 'Custom Profile';
$txt['permissions_profiles_customize'] = 'Customize';
$txt['permissions_profile_custom'] = 'Custom';
$txt['permissions_profile_as_board'] = '%s\'s Profile';
$txt['permissions_profile_default'] = 'Default';
$txt['permissions_profile_no_polls'] = 'No Polls';
$txt['permissions_profile_reply_only'] = 'Reply Only';
$txt['permissions_profile_read_only'] = 'Read Only';

// Untranslated!
$txt['permissions_profile_edit'] = 'Edit Profiles';
$txt['permissions_profile_new'] = 'New Profile';
$txt['permissions_profile_new_create'] = 'Create';
$txt['permissions_profile_name'] = 'Profile Name';
$txt['permissions_profile_do_edit'] = 'Edit';
$txt['permissions_profile_do_delete'] = 'Delete';
$txt['permissions_profile_copy_from'] = 'Copy Permissions From';

// Untranslated!
$txt['permissions_includes_inherited'] = 'Inherited Groups';

$txt['permissions_groups'] = 'Berechtigungen der Mitgliedergruppen';
$txt['permissions_all'] = 'alle';
$txt['permissions_none'] = 'keine';
$txt['permissions_set_permissions'] = 'Berechtigungen speichern';

$txt['permissions_with_selection'] = 'Mit der Auswahl';
$txt['permissions_apply_pre_defined'] = 'Verwende definiertes Berechtigungsprofil';
$txt['permissions_select_pre_defined'] = 'W&auml;hle ein definiertes Profil';
$txt['permissions_copy_from_board'] = 'Berechtigungen von diesem Board kopieren';
$txt['permissions_select_board'] = 'W&auml;hlen Sie ein Board';
$txt['permissions_like_group'] = 'Setze Berechtigung wie diese Gruppe';
$txt['permissions_select_membergroup'] = 'W&auml;hle Mitgliedergruppe';
$txt['permissions_add'] = 'F&uuml;ge Berechtigung hinzu';
$txt['permissions_remove'] = 'L&ouml;sche Berechtigung';
$txt['permissions_deny'] = 'Verbiete Berechtigung';
$txt['permissions_select_permission'] = 'W&auml;hle Berechtigung';

// All of the following block of strings should not use entities, instead use \\" for &quot; etc.
$txt['permissions_only_one_option'] = 'Sie können nur eine Aktion zum modifizieren der Berechtigung wählen';
$txt['permissions_no_action'] = 'Keine Aktion ausgewählt';
$txt['permissions_deny_dangerous'] = 'Sie verbieten eine oder mehrere Berechtigungen.\\nDies kann unter Umst&auml;nden unvorhergesehene Folgen nach sich ziehen, wenn Sie nicht kontrolliert haben, ob ein Mitglied \\"f&auml;lschlicherweise\\" in der betreffenden Gruppe ist.\\n\\nSind Sie sicher, dass Sie fortfahren möchten?';

$txt['permissions_boards'] = 'Berechtigungen je Board';

$txt['permissions_modify_group'] = 'Gruppe &auml;ndern';
$txt['permissions_general'] = 'Generelle Berechtigungen';
// Untranslated!
$txt['permissions_board'] = 'Default Board Permissions';
$txt['permissions_commit'] = '&Auml;nderungen speichern';
// Untranslated!
$txt['permissions_on'] = 'in profile';
$txt['permissions_local_for'] = 'Permissions for group';
$txt['permissions_option_on'] = 'E';
$txt['permissions_option_off'] = 'X';
$txt['permissions_option_deny'] = 'V';
$txt['permissions_option_desc'] = 'F&uuml;r jede Berechtigung k&ouml;nnen Sie \'Erlauben\' (E), \'Nicht erlauben\' (X) oder <span style="color: red;">\'Verbieten\' (V)</span> ausw&auml;hlen.<br /><br />Beachten Sie: Wenn Sie eine Berechtigung verbieten, kann kein Mitglied - weder Moderator noch andere - in dieser Gruppe die betreffende Funktion nutzen.<br />Deshalb benutzen Sie diese Option mit gr&ouml;&szlig;ter Sorgfalt und nur wenn es <b>n&ouml;tig</b> ist. \'Nicht erlauben\' verbietet nur so lange, bis es an anderer Stelle erlaubt wird.';

$txt['permissiongroup_general'] = 'Generell';
$txt['permissionname_view_stats'] = 'Forum-Statistiken anschauen';
$txt['permissionhelp_view_stats'] = 'Die Forum-Statistiken fassen alle Statistiken wie Mitgliederanzahl, t&auml;glich geschriebene Beitr&auml;ge und mehrere Top 10 Statistiken zusammen. Wenn Sie diese Berechtigung aktivieren, wird der Link (\'Weitere Statistiken\') im unteren Teil des Board-Index hinzugef&uuml;gt.';
$txt['permissionname_view_mlist'] = 'Mitgliederliste anschauen';
$txt['permissionhelp_view_mlist'] = 'Die Mitgliederliste zeigt alle Mitglieder des Forums an. Sie kann sortiert und durchsucht werden und ist vom Board-Index und innerhalb der Statistiken verlinkt, wenn Sie dort auf die Mitgliederanzahl klicken.';
$txt['permissionname_who_view'] = '\'Wer ist online\' anschauen';
$txt['permissionhelp_who_view'] = '\'Wer ist online\' zeigt alle Mitglieder, die aktuell online sind und was sie gerade machen. Diese Berechtigung funktioniert nur, wenn Sie die Funktion in den \'Forum-Einstellungen\' aktiviert haben. Sie k&ouml;nnen die Aktionen anschauen, wenn Sie auf den Link \'Wer ist online\' im Board-Index klicken.';
$txt['permissionname_search_posts'] = 'Nach Beitr&auml;gen und Themen suchen';
$txt['permissionhelp_search_posts'] = 'Die Berechtigung \'Suche\' erlaubt dem Benutzer alle Boards zu durchsuchen, auf die er zugreifen darf. Wenn diese Berechtigung aktiviert ist, befindet sich der \'Suchen\' Button in der Menuleiste.';
$txt['permissionname_karma_edit'] = 'Karma anderer Benutzer ver&auml;ndern';
$txt['permissionhelp_karma_edit'] = 'Karma ist eine Funktion, die den Beliebtheitsgrad eines Benutzers anzeigt. Um sie benutzen zu k&ouml;nnen, m&uuml;ssen Sie es in den \'Forum-Einstellungen\' aktiviert haben. Diese Berechtigung erlaubt es einer Mitgliedergruppe eine Wertung abzugeben und steht G&auml;sten nicht zur Verf&uuml;gung.';

$txt['permissiongroup_pm'] = 'Private Mitteilungen';
$txt['permissionname_pm_read'] = 'Private Mitteilungen lesen';
$txt['permissionhelp_pm_read'] = 'Diese Berechtigung erlaubt dem Benutzer seine Privaten Mitteilungen zu lesen. Ohne diese Berechtigung kann er nicht auf diesen Bereich zugreifen.';
$txt['permissionname_pm_send'] = 'Private Mitteilungen senden';
$txt['permissionhelp_pm_send'] = 'Diese Berechtigung erlaubt dem Benutzer das Senden von Privaten Mitteilungen. Ben&ouml;tigt \'Private Mitteilungen lesen\'.';

$txt['permissiongroup_calendar'] = 'Kalender';
$txt['permissionname_calendar_view'] = 'Kalender anschauen';
$txt['permissionhelp_calendar_view'] = 'Der Kalender zeigt f&uuml;r jeden Monat die Geburtstage, Ereignisse und Ferien an. Diese Berechtigung erlaubt den Zugang zum Kalender, blendet den entsprechenden Button in der Menuleiste ein und zeigt im Board-Index eine Liste mit den aktuellen und kommenden Geburtstagen, Ereignissen und Ferien. Der Kalender muss dazu unter \'Kalender verwalten\' eingestellt werden.';
$txt['permissionname_calendar_post'] = 'Ereignisse im Kalender erstellen';
$txt['permissionhelp_calendar_post'] = 'Ereignisse sind Themen, die zu einem bestimmten Tag oder Zeitraum verlinkt werden. Ereignisse k&ouml;nnen nur im Kalender erstellt werden, wenn der Benutzer die Berechtigung hat, neue Beitr&auml;ge zu erstellen.';
$txt['permissionname_calendar_edit'] = 'Ereignisse editieren';
$txt['permissionhelp_calendar_edit'] = 'Ereignisse sind Themen, die zu einem bestimmten Tag oder Zeitraum verlinkt werden. Sie k&ouml;nnen editiert werden, indem man auf den kleinen Stern (*) neben dem Ereignis-Titel im Kalender klickt. Um sie editieren zu k&ouml;nnen, ben&ouml;tigt der Benutzer ausreichend Rechte zum &auml;dern des ersten Beitrags des betreffenden Themas.';
$txt['permissionname_calendar_edit_own'] = 'Eigene Ereignisse';
$txt['permissionname_calendar_edit_any'] = 'Jedes Ereignis';

$txt['permissiongroup_maintenance'] = 'Forum Administration';
$txt['permissionname_admin_forum'] = 'Forum und Datenbank administrieren';
$txt['permissionhelp_admin_forum'] = 'Diese Berechtigung erlaubt dem Benutzer das:<ul><li>&Auml;ndern der Einstellungen des Forums, der Datenbank und der Themes</li><li>Verwalten von Paketen</li><li>Benutzen der Wartungsfunktionen</li><li>Anschauen der Fehler- bzw. Moderatoren-Protokollen</li></ul> Benutzen Sie diese Berechtigung mit Vorsicht, da sie sehr m&auml;chtig ist.';
$txt['permissionname_manage_boards'] = 'Boards und Kategorien verwalten';
$txt['permissionhelp_manage_boards'] = 'Diese Berechtigung erlaubt das Erstellen, &Auml;ndern und L&ouml;schen von Boards oder Kategorien.';
$txt['permissionname_manage_attachments'] = 'Anh&auml;nge und Benutzerbilder verwalten';
$txt['permissionhelp_manage_attachments'] = 'Diese Berechtigung erlaubt den Zugriff auf die Verwaltung der Dateianh&auml;nge, wo alle Anh&auml;nge und Benutzerbilder aufgelistet und entfernt werden k&ouml;nnen.';
$txt['permissionname_manage_smileys'] = 'Smileys verwalten';
$txt['permissionhelp_manage_smileys'] = 'Diese Berechtigung erlaubt den Zugriff auf die Smiley Verwaltung, wo Sie Smileys bzw. Smiley-Sets hinzuf&uuml;gen, &auml;ndern und l&ouml;schen k&ouml;nnen.';
$txt['permissionname_edit_news'] = 'News &auml;ndern';
$txt['permissionhelp_edit_news'] = 'Diese Berechtigung erlaubt das &Auml;ndern der News-Meldungen. Um sie zu benutzen, muss die News Funktion in den Einstellungen aktiviert sein.';
// Untranslated!
$txt['permissionname_access_mod_center'] = 'Access the moderation center';
$txt['permissionhelp_access_mod_center'] = 'With this permission any members of this group can access the moderation center from where they will have access to functionality to ease moderation. Note that this does not in itself grant any moderation privileges.';

$txt['permissiongroup_member_admin'] = 'Mitglieder Administration';
$txt['permissionname_moderate_forum'] = 'Mitglieder verwalten';
$txt['permissionhelp_moderate_forum'] = 'Diese Berechtigung beinhaltet alle wichtigen Funktionen der Mitgliederverwaltung:<ul><li>Zugriff auf die Registrierungsverwaltung</li><li>Zugriff auf den Mitglieder anzeigen/l&ouml;schen Bildschirm</li><li>erweiterte Profil Informationen, inkl. IP/Benutzer beobachten und versteckten Online Status</li><li>Aktivierung von Zug&auml;ngen</li><li>Benachrichtigungen &uuml;ber Zustimmungen erhalten und aktivieren von Zug&auml;ngen</li><li>immun gegen Ignorierfunktion der Privaten Mitteilungen</li><li>weitere kleine Funktionen</li></ul>';
$txt['permissionname_manage_membergroups'] = 'Mitgliedergruppen verwalten und zuordnen';
$txt['permissionhelp_manage_membergroups'] = 'Diese Berechtigung erlaubt das Editieren von Mitgliedergruppen und zuordnen von Benutzern zu anderen Mitgliedergruppen.';
$txt['permissionname_manage_permissions'] = 'Berechtigungen verwalten';
$txt['permissionhelp_manage_permissions'] = 'Diese Berechtigung erlaubt das &Auml;ndern aller Berechtigungen einer Mitgliedergruppe bzw. eines Boards (global und lokal).';
$txt['permissionname_manage_bans'] = 'Bann-Liste verwalten';
$txt['permissionhelp_manage_bans'] = 'Diese Berechtigung erlaubt das Hinzuf&uuml;gen oder L&ouml;schen von Benutzernamen, IP-Adressen, Hostnamen und E-Mail Adressen zu einer Liste von gebannten Benutzern. Sie erlaubt weiterhin das Anschauen bzw. L&ouml;schen von Protokolleintr&auml;gen der gebannten Benutzer, die probieren sich wieder einzuloggen.';
$txt['permissionname_send_mail'] = 'Forum E-Mail an Mitglieder senden';
$txt['permissionhelp_send_mail'] = 'Sendet eine E-Mail oder wahlweise eine Private Mitteilung an alle Mitglieder oder an eine bestimmte Gruppe. Ben&ouml;tigt die Berechtigung \'Private Mitteilungen senden\'.';

$txt['permissiongroup_profile'] = 'Benutzerprofile';
$txt['permissionname_profile_view'] = 'Profil und Statistiken anschauen';
$txt['permissionhelp_profile_view'] = 'Diese Berechtigung erlaubt das Anklicken des Benutzernamens und somit das Betrachten des Profils, der Statistiken und den Beitr&auml;gen des Benutzers.';
$txt['permissionname_profile_view_own'] = 'Eigenes Profil';
$txt['permissionname_profile_view_any'] = 'Alle Profile';
$txt['permissionname_profile_identity'] = 'Zugangseinstellungen';
$txt['permissionhelp_profile_identity'] = 'Zugangseinstellungen sind die einfachsten Einstellungen im Profil des Benutzers wie Passwort, E-Mail, Mitgliedergruppe und bevorzugte Sprache.';
$txt['permissionname_profile_identity_own'] = 'Eigenes Profil';
$txt['permissionname_profile_identity_any'] = 'Alle Profile';
$txt['permissionname_profile_extra'] = 'Erweiterte Profileinstellungen';
$txt['permissionhelp_profile_extra'] = 'Erweiterte Profileinstellungen inklusive Optionen f&uuml;r das Benutzerbild, Theme Einstellungen, Benachrichtigungen und Private Mitteilungen.';
$txt['permissionname_profile_extra_own'] = 'Eigenes Profil';
$txt['permissionname_profile_extra_any'] = 'Alle Profile';
$txt['permissionname_profile_title'] = 'Pers&ouml;nlichen Titel &auml;ndern';
$txt['permissionhelp_profile_title'] = 'Der pers&ouml;nliche Titel wird im Beitrag unterhalb des Namens angezeigt (wenn f&uuml;r diesen Benutzer vorhanden).';
$txt['permissionname_profile_title_own'] = 'Eigenes Profil';
$txt['permissionname_profile_title_any'] = 'Alle Profile';
$txt['permissionname_profile_remove'] = 'Zugang l&ouml;schen';
$txt['permissionhelp_profile_remove'] = 'Diese Berechtigung erlaubt dem Benutzer seinen Zugang zu l&ouml;schen, wenn sie auf \'Eigenes Profil\' gestellt ist.';
$txt['permissionname_profile_remove_own'] = 'Eigener Zugang';
$txt['permissionname_profile_remove_any'] = 'Jeder Zugang';
$txt['permissionname_profile_server_avatar'] = 'W&auml;hlen Sie ein Benutzerbild vom Server';
$txt['permissionhelp_profile_server_avatar'] = 'Aktivieren Sie die Option, um den Benutzern die M&ouml;glichkeit zu geben, ein auf diesem Server gespeichertes Benutzerbild auszuw&auml;hlen.';
$txt['permissionname_profile_upload_avatar'] = 'Benutzerbild auf diesen Server hochladen';
$txt['permissionhelp_profile_upload_avatar'] = 'Erlaubt den Benutzern das Hochladen ihrer eigenen Benutzerbilder auf diesen Server.';
$txt['permissionname_profile_remote_avatar'] = 'Externes Benutzerbild w&auml;hlen';
$txt['permissionhelp_profile_remote_avatar'] = 'Externe Benutzerbilder k&ouml;nnen die Geschwindigkeit des Forums herabsetzen, so dass es sinnvoll ist, bestimmten Gruppen dieses Recht zu entziehen. ';

$txt['permissiongroup_general_board'] = 'Generell';
$txt['permissionname_moderate_board'] = 'Board moderieren';
$txt['permissionhelp_moderate_board'] = 'Diese Berechtigung gibt dem Moderator weitere Funktionen, die ihn zu einem richtigen Moderator zu machen. Das umfasst die M&ouml;glichkeit auf geschlossene Themen zu antworten, die Ablaufzeit von Umfragen zu &auml;ndern und die Umfragenergebnisse jederzeit einzusehen.';

$txt['permissiongroup_topic'] = 'Themen';
$txt['permissionname_post_new'] = 'Neues Thema erstellen';
$txt['permissionhelp_post_new'] = 'Diese Berechtigung erlaubt dem Benutzer neue Themen zu starten. Sie erlaubt jedoch nicht, auf Themen zu antworten.';
$txt['permissionname_merge_any'] = 'Themen zusammenf&uuml;hren';
$txt['permissionhelp_merge_any'] = 'F&uuml;hrt zwei oder mehr Themen zusammen. Die Reihenfolge der Beitr&auml;ge basiert auf der Zeit, wann diese geschrieben worden sind, d.h. der &auml;lteste Beitrag ist am Schluss des neuen Themas. Der Benutzer kann nur Themen in Boards zusammenf&uuml;hren, zu denen er auch Zugriff hat. Um gleichzeitig mehrere Themen zusammen zu f&uuml;hren, muss die Funktion Schnellmoderation im Profil aktiviert sein.';
$txt['permissionname_split_any'] = 'Themen teilen';
$txt['permissionhelp_split_any'] = 'Teilt ein Thema in zwei einzelne Themen.';
$txt['permissionname_send_topic'] = 'Themen an Freund senden';
$txt['permissionhelp_send_topic'] = 'Diese Berechtigung erlaubt es dem Benutzer, einem Freund einen Hinweis auf ein Thema per E-Mail zu senden. Dazu muss er die E-Mail Adresse eingeben und kann einen Nachricht dazu verfassen.';
$txt['permissionname_make_sticky'] = 'Top Themen erstellen';
$txt['permissionhelp_make_sticky'] = 'Top Themen werden immer als erste Themen in einem Board angezeigt. Sie sind n&uuml;tzlich f&uuml;r Ank&uuml;ndigungen oder wichtige Hinweise.';
$txt['permissionname_move'] = 'Thema verschieben';
$txt['permissionhelp_move'] = 'Verschiebt ein Thema von einem Board in ein anderes. Die Benutzer k&ouml;nnen nur die Boards als Ziel w&auml;hlen, auf welche sie Zugriff haben.';
$txt['permissionname_move_own'] = 'Eigenes Thema';
$txt['permissionname_move_any'] = 'Jedes Thema';
$txt['permissionname_lock'] = 'Themen schlie&szlig;en';
$txt['permissionhelp_lock'] = 'Diese Berechtigung erlaubt dem Benutzer das Schlie&szlig;en von Themen. Danach kann kein Benutzer mehr auf das Thema antworten, au&szlig;er Benutzer mit \'Moderator\' Rechten.';
$txt['permissionname_lock_own'] = 'Eigene Themen';
$txt['permissionname_lock_any'] = 'Alle Themen';
$txt['permissionname_remove'] = 'Themen l&ouml;schen';
$txt['permissionhelp_remove'] = 'Diese Berechtigung erlaubt das L&ouml;schen ganzer Themen. Bitte beachten Sie, dass mit dieser Funktino keine einzelnen Beitr&auml;ge gel&ouml;scht werden d&uuml;rfen!';
$txt['permissionname_remove_own'] = 'Eigene Themen';
$txt['permissionname_remove_any'] = 'Alle Themen';
$txt['permissionname_post_reply'] = 'Antworten auf Themen schreiben';
$txt['permissionhelp_post_reply'] = 'Diese Berechtigung erlaubt das Antworten auf Themen.';
$txt['permissionname_post_reply_own'] = 'Eigene Themen';
$txt['permissionname_post_reply_any'] = 'Alle Themen';
$txt['permissionname_modify_replies'] = 'Antworten auf eigene Themen &auml;ndern';
$txt['permissionhelp_modify_replies'] = 'Diese Berechtigung erlaubt das nachtr&auml;gliche &Auml;ndern aller Beitr&auml;ge in einem vom ihm erstellten Themen.';
$txt['permissionname_delete_replies'] = 'Antworten auf eigene Themen l&ouml;schen';
$txt['permissionhelp_delete_replies'] = 'Diese Berechtigung erlaubt es dem Benutzer, alle Antworten auf ein von ihm erstelltes Thema zu l&ouml;schen.';
$txt['permissionname_announce_topic'] = 'Thema ank&uuml;ndigen';
$txt['permissionhelp_announce_topic'] = 'Diese Berechtigung erlaubt das Senden einer Ank&uuml;ndigungs E-Mail &uuml;ber ein Thema an alle Mitglieder oder an bestimmte Mitgliedergruppen.';

$txt['permissiongroup_post'] = 'Beitr&auml;ge';
$txt['permissionname_delete'] = 'Beitr&auml;ge l&ouml;schen';
$txt['permissionhelp_delete'] = 'Erlaubt dem Benutzer Beitr&auml;ge zu l&ouml;schen, erlaubt jedoch nicht, den ersten Beitrag eines Themas zu l&ouml;schen.';
$txt['permissionname_delete_own'] = 'Eigener Beitrag';
$txt['permissionname_delete_any'] = 'Alle Beitr&auml;ge';
$txt['permissionname_modify'] = 'Beitr&auml;ge editieren';
$txt['permissionhelp_modify'] = 'Erlaubt das Editieren von Beitr&auml;gen';
$txt['permissionname_modify_own'] = 'Eigener Beitrag';
$txt['permissionname_modify_any'] = 'Alle Beitr&auml;ge';
$txt['permissionname_report_any'] = 'Beitr&auml;ge dem Moderator melden';
$txt['permissionhelp_report_any'] = 'Diese Berechtigung f&uuml;gt einen Link zu jedem Beitrag hinzu, welcher es dem Benutzer erlaubt, diesen einem Moderator zu melden. Bei einer Meldung erhalten alle Moderatoren des Boards diese Nachricht mit einer Beschreibung des Benutzers und dem Link zum entsprechenden Beitrag.';

$txt['permissiongroup_poll'] = 'Umfragen';
$txt['permissionname_poll_view'] = 'Umfrage anschauen';
$txt['permissionhelp_poll_view'] = 'Diese Berechtigung erlaubt dem Benutzer das Anschauen einer Umfrage. Ohne diese Berechtigung sieht der Benutzer nur das Thema ohne die Umfrage.';
$txt['permissionname_poll_vote'] = 'Abstimmen in Umfragen';
$txt['permissionhelp_poll_vote'] = 'Diese Berechtigung erlaubt einem registrierten Benutzer das Abgeben einer Stimme bei Umfragen. Die Funktion gilt nicht f&uuml;r G&auml;ste.';
$txt['permissionname_poll_post'] = 'Umfragen erstellen';
$txt['permissionhelp_poll_post'] = 'Diese Berechtigung erlaubt dem Benutzer eine neue Umfrage zu erstellen.';
$txt['permissionname_poll_add'] = 'Umfragen zu Themen hinzuf&uuml;gen';
$txt['permissionhelp_poll_add'] = 'F&uuml;gt einen Umfrage zu einem existierenden Thema hinzu. Diese Berechtigung erfordert die M&ouml;glichkeit, den ersten Beitrag eines Themas zu &auml;ndern.';
$txt['permissionname_poll_add_own'] = 'Eigene Themen';
$txt['permissionname_poll_add_any'] = 'Alle Themen';
$txt['permissionname_poll_edit'] = 'Umfragen editieren';
$txt['permissionhelp_poll_edit'] = 'Diese Berechtigung erlaubt die &Auml;nderung einer bestehenden Umfrage sowie das Zur&uuml;cksetzen der Stimmen auf Null. Um die Anzahl der max. abzugebenen Stimmen und die Laufzeit der Umfrage zu &auml;ndern, muss der Benutzer \'Moderator\' Rechte besitzen.';
$txt['permissionname_poll_edit_own'] = 'Eigene Umfrage';
$txt['permissionname_poll_edit_any'] = 'Alle Umfragen';
$txt['permissionname_poll_lock'] = 'Umfragen schlie&szlig;en';
$txt['permissionhelp_poll_lock'] = 'Verhindert das weitere Abstimmen in der betreffenden Umfrage.';
$txt['permissionname_poll_lock_own'] = 'Eigene Umfragen';
$txt['permissionname_poll_lock_any'] = 'Alle Umfragen';
$txt['permissionname_poll_remove'] = 'Umfragen l&ouml;schen';
$txt['permissionhelp_poll_remove'] = 'Diese Berechtigung erlaubt das L&ouml;schen einer Umfrage.';
$txt['permissionname_poll_remove_own'] = 'Eigene Umfrage';
$txt['permissionname_poll_remove_any'] = 'Alle Umfragen';

// Untranslated!
$txt['permissiongroup_approval'] = 'Post Moderation';
$txt['permissionname_approve_posts'] = 'Approve items awaiting moderation';
$txt['permissionhelp_approve_posts'] = 'This permission allows a user to approve all unapproved items on a board.';
$txt['permissionname_post_unapproved_replies'] = 'Post unapproved replies';
$txt['permissionhelp_post_unapproved_replies'] = 'This permission allows a user to post replies to a topic which will not be shown until approved by a moderator.';
$txt['permissionname_post_unapproved_replies_own'] = 'Own topic';
$txt['permissionname_post_unapproved_replies_any'] = 'Any topic';
$txt['permissionname_post_unapproved_topics'] = 'Post unapproved topics';
$txt['permissionhelp_post_unapproved_topics'] = 'This permission allows a user to post a new topic which will require approval before being shown.';
$txt['permissionname_post_unapproved_attachments'] = 'Post unapproved attachments';
$txt['permissionhelp_post_unapproved_attachments'] = 'This permission allows a user to attach files to their posts which will then require approval before being shown to other users.';

$txt['permissiongroup_notification'] = 'Benachrichtigungen';
$txt['permissionname_mark_any_notify'] = 'Benachrichtigung bei neuen Antworten';
$txt['permissionhelp_mark_any_notify'] = 'Diese Berechtigung erlaubt das Einstellen von Benachrichtigungen f&uuml;r gew&uuml;nschte Themen.';
$txt['permissionname_mark_notify'] = 'Benachrichtigung bei neuen Themen';
$txt['permissionhelp_mark_notify'] = 'Diese Berechtigung erlaubt das Aktivieren von Benachrichtigungen bei Boards, sobald ein neues Thema in diesem geschrieben wurde.';

$txt['permissiongroup_attachment'] = 'Dateianh&auml;nge';
$txt['permissionname_view_attachments'] = 'Dateianh&auml;nge anschauen';
$txt['permissionhelp_view_attachments'] = 'Dateianh&auml;nge sind Dateien, die an einen Beitrag angef&uuml;gt worden sind. Diese Funktion kann im Bereich \'Dateianh&auml;nge verwalten\' aktiviert und eingestellt werden. Dateianh&auml;nge sind nicht direkt anw&auml;hlbar und k&ouml;nnen f&uuml;r die Benutzer blockiert werden, die diese Berechtigung nicht besitzen.';
$txt['permissionname_post_attachment'] = 'Dateianh&auml;nge erstellen';
$txt['permissionhelp_post_attachment'] = 'Dateianh&auml;nge sind Dateien, die an einen Beitrag angef&uuml;gt worden sind. Ein Beitag kann mehrere Dateianh&auml;nge enthalten.';

$txt['permissionicon'] = '';

$txt['permission_settings_title'] = 'Einstellungen';
$txt['groups_manage_permissions'] = 'Gruppen, die Berechtigungen verwalten d&uuml;rfen';
$txt['permission_settings_submit'] = 'Speichern';
$txt['permission_settings_enable_deny'] = 'Aktiviert das Verbieten von Berechtigungen';
// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['permission_disable_deny_warning'] = 'Das Deaktivieren dieser Option &auml;ndert die \\\'Verboten\\\'-Berechtigung in \\\'Nicht erlauben\\\'.';
$txt['permission_by_membergroup_desc'] = 'Hier k&ouml;nnen Sie die globalen Berechtigungen f&uuml;r jede Mitgliedergruppe einstellen. Diese Berechtigungen gelten in allen Boards, wo sie nicht von lokalen Berechtigungen &uuml;berschrieben werden, welche im Bereich \'Berechtigungen je Board\' erstellt werden k&ouml;nnen.';
$txt['permission_by_board_desc'] = 'Hier k&ouml;nnen Sie einstellen, ob ein Board die globalen Berechtigungen benutzt oder ein eigenes Schema verwendet. Sollten Sie lokale Berechtigungen verwenden, k&ouml;nnen Sie im betreffenden Board f&uuml;r jede Mitgliedergruppe individuelle Berechtigungen eingeben.';
$txt['permission_settings_desc'] = 'W&auml;hlen Sie hier, wer die Erlaubnis zum &Auml;ndern der Berechtigungen hat und wie komplex das Berechtigungssystem sein soll.';
$txt['permission_settings_enable_postgroups'] = 'Aktiviere Berechtigungen f&uuml;r beitragsbasierende Gruppen';
// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['permission_disable_postgroups_warning'] = 'Das Deaktivieren dieser Option entfernt die gesetzten Berechtigungen der beitragsbasierenden Gruppen.';

?>