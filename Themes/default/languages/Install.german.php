<?php
// Version: 2.0 Alpha; Install

// These should be the same as those in index.language.php.
$txt['lang_character_set'] = 'ISO-8859-1';
$txt['lang_rtl'] = false;

$txt['smf_installer'] = 'SMF Installation';
$txt['installer_language'] = 'Sprache';
$txt['installer_language_set'] = 'Speichern';
$txt['congratulations'] = 'Herzlichen Gl&uuml;ckwunsch, die Installation ist abgeschlossen!';
$txt['congratulations_help'] = 'Wenn Sie Unterst&uuml;tzung brauchen oder SMF nicht fehlerfrei l&auml;uft, k&ouml;nnen Sie <a href="http://www.simplemachines.org/community/index.php" target="_blank">im Forum</a> Hilfe anfordern.';
$txt['still_writable'] = 'Ihr Installationsverzeichnis ist noch beschreibbar! Es ist aus Sicherheitsgr&uuml;nden sinnvoll, die CHMOD Berechtigungen zu &auml;ndern, so dass es schreibgesch&uuml;tzt ist.';
$txt['delete_installer'] = 'Klicken Sie hier um die Datei install.php zu l&ouml;schen.';
$txt['delete_installer_maybe'] = '<i>(funktioniert nicht auf allen Servern)</i>';
$txt['go_to_your_forum'] = 'Jetzt k&ouml;nnen Sie <a href="%s">Ihr neu installiertes Forum</a> ansehen und benutzen. Bitte achten Sie darauf dass Sie eingeloggt sind, bevor Sie versuchen in den Administratorbereich zu gelangen.';
$txt['good_luck'] = 'Viel Gl&uuml;ck!<br />Simple Machines';

$txt['user_refresh_install'] = 'Forum aktualisiert';
$txt['user_refresh_install_desc'] = 'W&auml;hrend der Installation hat das Installationsprogramm eine oder mehrere Datenbanktabellen gefunden, welche schon existieren und ggf. neu erstellt werden.<br />Alle fehlenden Tabellen Ihrer Installation wurden mit den Standard-Daten erstellt, vorhandene Daten wurden jedoch nicht gel&ouml;scht.';

$txt['default_topic_subject'] = 'Willkommen bei SMF!';
$txt['default_topic_message'] = 'Willkommen im Simple Machines Forum!<br /><br />Wir hoffen, dass Ihnen Ihr neues Forum Spa&szlig; macht.&nbsp; Wenn Sie Probleme haben, z&ouml;gern Sie nicht uns [url=http://www.simplemachines.org/community/index.php]um Hilfe zu fragen[/url].<br /><br />Danke!<br />Simple Machines';
$txt['default_board_name'] = 'Allgemeine Diskussionen';
$txt['default_board_description'] = 'Diskutieren Sie in diesem Board &uuml;ber alles was Ihnen einf&auml;llt.';
$txt['default_category_name'] = 'Kategorie';
$txt['default_time_format'] = '%d. %B %Y, %H:%M:%S';
$txt['default_news'] = 'SMF - Neu installiert!';
$txt['default_karmaLabel'] = 'Karma:';
$txt['default_karmaSmiteLabel'] = '[negativ]';
$txt['default_karmaApplaudLabel'] = '[positiv]';
$txt['default_reserved_names'] = 'Administrator\nAdmin\nWebmaster\nGast\nroot';
$txt['default_smileyset_name'] = 'Standard';
$txt['default_classic_smileyset_name'] = 'Classic';
$txt['default_theme_name'] = 'SMF Standard-Theme - Core';
$txt['default_classic_theme_name'] = 'Classic YaBB SE Theme';
$txt['default_babylon_theme_name'] = 'Babylon Theme';

$txt['default_administrator_group'] = 'Administrator';
$txt['default_global_moderator_group'] = 'Globaler Moderator';
$txt['default_moderator_group'] = 'Moderator';
$txt['default_newbie_group'] = 'Newbie';
$txt['default_junior_group'] = 'Jr. Member';
$txt['default_full_group'] = 'Full Member';
$txt['default_senior_group'] = 'Sr. Member';
$txt['default_hero_group'] = 'Hero Member';

$txt['default_smiley_smiley'] = 'Smiley';
$txt['default_wink_smiley'] = 'Zwinkernd';
$txt['default_cheesy_smiley'] = 'L&auml;chelnd';
$txt['default_grin_smiley'] = 'Grinsend';
$txt['default_angry_smiley'] = '&Auml;rgerlich';
$txt['default_sad_smiley'] = 'Traurig';
$txt['default_shocked_smiley'] = 'Schockiert';
$txt['default_cool_smiley'] = 'Cool';
$txt['default_huh_smiley'] = 'Huch';
$txt['default_roll_eyes_smiley'] = 'Augen rollen';
$txt['default_tongue_smiley'] = 'Zunge';
$txt['default_embarrassed_smiley'] = 'Verlegen';
$txt['default_lips_sealed_smiley'] = 'Schweigend';
$txt['default_undecided_smiley'] = 'Unentschlossen';
$txt['default_kiss_smiley'] = 'K&uuml;sschen';
$txt['default_cry_smiley'] = 'Weinen';
$txt['default_evil_smiley'] = 'Teuflisch';
$txt['default_azn_smiley'] = 'Azn';
$txt['default_afro_smiley'] = 'Afro';

$txt['error_message_click'] = 'Klicken Sie hier,';
$txt['error_message_try_again'] = 'um den Schritt erneut zu versuchen.';
$txt['error_message_bad_try_again'] = 'um trotzdem zu installieren. Beachten Sie bitte, dass dies <i>nicht</i> empfehlenswert ist.';

$txt['install_settings'] = 'Einstellungen';
$txt['install_settings_info'] = 'Nur ein paar Einstellungen ;).';
$txt['install_settings_name'] = 'Name des Forums';
$txt['install_settings_name_info'] = 'Das ist der Name Ihres Forums, z.B. &quot;Test Forum&quot;.';
$txt['install_settings_name_default'] = 'Mein Forum';
$txt['install_settings_url'] = 'Forum URL';
$txt['install_settings_url_info'] = 'Das ist die URL von Ihrem Forum <b>ohne den abschlie&szlig;enden \'/\'!</b>.<br />In den meisten F&auml;llen k&ouml;nnen Sie den eingestellten Wert belassen.';
$txt['install_settings_compress'] = 'Gzip Ausgabe';
$txt['install_settings_compress_title'] = 'Komprimiere Datenausgabe um Bandbreite zu sparen.';
// In this string, you can translate the word "PASS" to change what it says when the test passes.
$txt['install_settings_compress_info'] = 'Diese Option funktioniert nicht auf allen Servern, kann aber eine Menge an Bandbreite sparen.<br />Klicken Sie <a href="install.php?obgz=1&amp;pass_string=Erfolgreich" onclick="return reqWin(this.href, 200, 60);" target="_blank">hier</a> um es zu testen (der Test sollte "Erfolgreich" zur&uuml;ckmelden).';
$txt['install_settings_dbsession'] = 'Datenbank-Sitzungen';
$txt['install_settings_dbsession_title'] = 'Benutze die Datenbank f&uuml;r Sitzungen anstatt Dateien.';
$txt['install_settings_dbsession_info1'] = 'Diese Option ist grunds&auml;tzlich immer die beste Wahl, da sie Sitzungen zuverl&auml;ssiger macht.';
$txt['install_settings_dbsession_info2'] = 'Diese Option wird wahrscheinlich nicht fehlerfrei auf diesem Server funktionieren.';
// Untranslated!
$txt['install_settings_utf8'] = 'UTF-8 Character Set';
$txt['install_settings_utf8_title'] = 'Use UTF-8 as default character set';
$txt['install_settings_utf8_info'] = 'This feature lets both the database and the forum use an international character set, UTF-8. This can be useful when you work with multiple langugages that use different character sets.';
$txt['install_settings_stats'] = 'Allow Stat Collection';
$txt['install_settings_stats_title'] = 'Allow Simple Machines to Collect Basic Stats Monthly';
$txt['install_settings_stats_info'] = 'If enabled, this will allow Simple Machines to visit your site once a month to collect basic statistics. This will help us make decisions as to which configurations to optimise the software for. For more information please visit our <a href="http://www.simplemachines.org/about/stats.php">info page</a>.';
$txt['install_settings_proceed'] = 'Weiter';

$txt['mysql_settings'] = 'MySQL Server-Einstellungen';
$txt['mysql_settings_info'] = 'Das sind die Einstellungen, die Sie f&uuml;r Ihren MySQL-Server ben&ouml;tigen. Sollten Sie die Daten nicht wissen, fragen Sie Ihren Serveranbieter.';
$txt['mysql_settings_server'] = 'MySQL-Server Name';
$txt['mysql_settings_server_info'] = 'Der Name ist meistens localhost oder eine IP-Adresse - sollten Sie es nicht wissen, probieren Sie localhost.';
$txt['mysql_settings_username'] = 'MySQL-Username';
$txt['mysql_settings_username_info'] = 'Schreiben Sie hier den Usernamen hinein, den Sie ben&ouml;tigen, um zur MySQL-Datenbank zu verbinden.<br />Sollten Sie ihn nicht kennen, probieren Sie den Usernamen Ihres FTP-Servers, oft sind diese gleich.';
$txt['mysql_settings_password'] = 'MySQL-Passwort';
$txt['mysql_settings_password_info'] = 'Schreiben Sie hier das Passwort f&uuml;r die MySQL-Datenbank hinein.<br />Sollten Sie es nicht wissen, probieren Sie das von Ihrem FTP-Zugang.';
$txt['mysql_settings_database'] = 'MySQL-Datenbankname';
$txt['mysql_settings_database_info'] = 'Schreiben Sie hier den Namen der Datenbank hinein, in der SMF seine Daten speichern soll.<br />Wenn die Datenbank nicht existiert, wird die Installation versuchen sie zu erstellen.';
$txt['mysql_settings_prefix'] = 'MySQL-Tabellen Prefix';
$txt['mysql_settings_prefix_info'] = 'Schreiben Sie hier das Prefix f&uuml;r die Tabellen hinein. <b>Installieren Sie nie zwei Foren mit dem gleichen Prefix!</b><br />Diese Angabe erlaubt mehrere Installationen in einer Datenbank.';

$txt['user_settings'] = 'Benutzerkonto erstellen';
$txt['user_settings_info'] = 'Die Installation wird nun ein Administratorkonto f&uuml;r Sie erstellen.';
$txt['user_settings_username'] = 'Benutzername';
$txt['user_settings_username_info'] = 'Schreiben Sie hier den Namen hinein, mit dem Sie sich sp&auml;ter einloggen m&ouml;chten.<br />Dieser Name kann - im Gegensatz zum angezeigten Namen - nicht ge&auml;ndert werden!';
$txt['user_settings_password'] = 'Passwort';
$txt['user_settings_password_info'] = 'Schreiben Sie hier das gew&uuml;nschte Passwort hinein und behalten Sie es gut im Kopf!';
$txt['user_settings_again'] = 'Passwort';
$txt['user_settings_again_info'] = '(zum best&auml;tigen.)';
$txt['user_settings_email'] = 'E-Mail Adresse';
$txt['user_settings_email_info'] = 'Schreiben Sie hier Ihre E-Mail Adresse hinein.  <b>Es muss eine g&uuml;ltige E-Mail Adresse sein.</b>';
$txt['user_settings_database'] = 'MySQL-Datenbank Passwort';
$txt['user_settings_database_info'] = 'Die Installation erfordert aus Sicherheitsgr&uuml;nden ein g&uuml;ltiges Datenbank Passwort, um Ihr Administratorkonto zu erstellen.';
$txt['user_settings_proceed'] = 'Fertig';

$txt['ftp_setup'] = 'FTP-Verbindungsinformationen';
$txt['ftp_setup_info'] = 'Die Installation kann via FTP zum Server verbinden und die Dateien &uuml;berschreibbar machen, welche dieserfordern. Sollte es nicht funktionieren, m&uuml;ssten Sie es manuell machen. Bitte beachten Sie, dass SSL im Moment nicht unterst&uuml;tzt wird.';
$txt['ftp_server'] = 'Server';
$txt['ftp_server_info'] = 'Schreiben Sie hier den Server und den Port f&uuml;r den FTP-Server hinein.';
$txt['ftp_port'] = 'Port';
$txt['ftp_username'] = 'Username';
$txt['ftp_username_info'] = 'Der Username zum Einloggen. <i>Er wird nirgendwo gespeichert.</i>';
$txt['ftp_password'] = 'Passwort';
$txt['ftp_password_info'] = 'Das Passwort zum Einloggen. <i>Es wird nirgendwo gespeichert.</i>';
$txt['ftp_path'] = 'Installationspfad';
$txt['ftp_path_info'] = 'Das ist der <i>relative</i> Pfad, den Sie beim FTP-Server benutzen.';
$txt['ftp_path_found_info'] = 'Der Pfad in der oberen Box wurde automatisch ausgelesen.';
$txt['ftp_connect'] = 'Verbinden';
$txt['ftp_setup_why'] = 'Was macht dieser Schritt?';
$txt['ftp_setup_why_info'] = 'Einige Dateien m&uuml;ssen &uuml;berschreibbar sein, damit SMF richtig funktioniert. Dieser Schritt erm&ouml;glicht es der Installation dies selbst zu &auml;ndern. In manchen F&auml;llen kann es vorkommen, dass es nicht funktioniert - dann &auml;ndern Sie bitte bei folgenden Dateien das Attribut (CHMOD) auf 777:';
$txt['ftp_setup_again'] = 'Erneut testen, ob die Dateien &uuml;berschreibbar sind.';

$txt['error_php_too_low'] = 'Warnung! Der Server scheint mit einer PHP Version zu laufen, welche nicht den <b>minimalen Anforderungen</b> von SMF entspricht.<br />Wenn Sie den Server nicht selbst besitzen, sollten Sie Ihren Serveranbieter fragen, ob er die Version aktualisiert, einen anderen Anbieter w&auml;hlen oder sie selbst aktualisieren wenn Sie der Besitzer sind.<br /><br />Sollten Sie sicher sein, dass die PHP Version aktuell genug ist, k&ouml;nnen Sie fortfahren, was jedoch nicht empfehlenswert ist.';
$txt['error_missing_files'] = 'Die Installationsdateien konnten nicht im Verzeichnis des Skriptes gefunden werden!<br /><br />Bitten vergewissern Sie sich, dass Sie alle Dateien - inklusive der .sql Datei - hochgeladen haben und probieren Sie es erneut.';
$txt['error_session_save_path'] = 'Bitte informieren Sie Ihren Serveranbieter, dass der <b>session.save_path der Datei php.ini</b> ung&uuml;ltig ist!  Der Pfad sollte zu einem Verzeichnis ge&auml;ndert werden, welches <b>existiert</b> und vom Benutzer <b>beschreibbar</b> ist.<br />';
$txt['error_windows_chmod'] = 'Sie benutzen einen Windows-Server und einige Dateien sind nicht &uuml;berschreibbar. Fragen Sie Ihren Serveranbieter nach <b>Schreibberechtigungen</b> f&uuml;r die Dateien Ihrer SMF Installation. Die folgenden Dateien m&uuml;ssen &uuml;berschreibbar sein:';
$txt['error_ftp_no_connect'] = 'Die Verbindung zum FTP-Server ist mit den aktuellen Daten nicht m&ouml;glich.';
$txt['error_mysql_connect'] = 'Die Verbindung zur MySQL-Datenbank ist mit den aktuellen Daten nicht m&ouml;glich.<br /><br />Wenn Sie sich nicht sicher sind, fragen Sie Ihren Serveranbieter nach den richtigen Daten.';
$txt['error_mysql_too_low'] = 'Ihre Version von MySQL ist zu alt und entspricht nicht den minimalen Anforderungen von SMF.<br /><br />Bitte fragen Sie Ihren Serveranbieter, ob er es aktualisiert oder wechseln Sie den Anbieter.';
$txt['error_mysql_database'] = 'Die Installation konnte nicht auf die &quot;<i>%s</i>&quot; Datenbank zugreifen. Bei manchen Anbietern m&uuml;ssen Sie die Datenbank erst erstellen, bevor Sie diese nutzen k&ouml;nnen. Andere f&uuml;gen dem Datenbanknamen ein Prefix hinzu, z.B. Ihren Usernamen.';
$txt['error_mysql_queries'] = 'Einige der Befehle konnten nicht richtig ausgef&uuml;hrt werden. Das kann an einer nicht unterst&uuml;tzten oder veralteten Version von MySQL h&auml;ngen.<br /><br />Technische Information der Befehle:';
$txt['error_mysql_queries_line'] = 'Zeile #';
$txt['error_mysql_missing'] = 'Die Installation konnte keine MySQL-Unterst&uuml;tzung in PHP finden. Bitte versichern Sie sich bei Ihrem Serveranbieter, dass PHP wirklich mit MySQL kompiliert wurde oder das die richtige Erweiterung geladen wird.';
$txt['error_session_missing'] = 'Das Installationsprogramm konnte die Unterst&uuml;tzung f&uuml;r Sitzungen in Ihrer PHP-Umgebung nicht ermitteln. Bitte fragen Sie Ihren Serveranbieter um sicher zu gehen, dass PHP mit der Unterst&uuml;tzung f&uuml;r Sitzungen kompiliert wurde (andererseits sollte es explizit ohne die Unterst&uuml;tzung erstellt werden).';
$txt['error_user_settings_again_match'] = 'Sie haben zwei verschiedene Passw&ouml;rter eingegeben!';
$txt['error_user_settings_taken'] = 'Ein anderes Mitglied hat sich schon mit diesem Benutzernamen/Passwort registriert.<br /><br />Ein neues Benutzerkonto wurde nicht erstellt.';
$txt['error_user_settings_query'] = 'Ein Datenbankfehler ist beim Erstellen des Administratorkontos aufgetreten. Der Fehler lautet:';
$txt['error_subs_missing'] = 'Es ist nicht m&ouml;glich, die Datei Sources/Subs.php zu finden. Bitte vergewissern Sie sich, dass Sie diese vollst&auml;ndig hochgeladen haben und versuchen Sie es erneut.';
$txt['error_mysql_alter_priv'] = 'Der angegebene MySQL-Zugang hat keine Berechtigung, Tabellen in der Datenbank zu erstellen. Dies wird jedoch f&uuml;r die Nutzung von SMF unbedingt ben&ouml;tigt!';
$txt['error_versions_do_not_match'] = 'Das Installationsprogramm hat eine andere Version von SMF gefunden. Wenn Sie das Forum aktualisieren m&ouml;chten, sollten Sie das Upgrade-Paket benutzen, nicht das normale Installationsprogramm.<br /><br />Sie k&ouml;nnen auch andere Daten benutzen oder ein Backup erstellen und die vorhandenen Daten in der Datenbank l&ouml;schen.';
$txt['error_mod_security'] = 'Das Installationsprogramm hat das Modul \'mod_security\' auf Ihrem Server gefunden. Mod_security blockiert gesendete Formulardaten, bevor SMF etwas dagegen unternehmen kann. SMF hat einen eingebauten Sicherheits-Scanner, welcher effektiver als mod_security arbeitet und keine Formulardaten blockiert.<br /><br /><a href="http://www.simplemachines.org/redirect/mod_security">Informationen &uuml;ber das Deaktivieren von mod_security</a>';
// Untranslated!
$txt['error_utf8_mysql_version'] = 'The current version of your database doesn\'t support the use of the UTF-8 character set. You can still install SMF without any problems, but only with UTF-8 support unchecked. If you would like to switch over to UTF-8 in the future (e.g. after the MySQL server of your forum has been upgraded to a version >= 4.1), you can convert your forum to UTF-8 through the admin panel.';

?>