<?php
// Version: 2.0 Alpha; Install

// These should be the same as those in index.language.php.
$txt['lang_character_set'] = 'ISO-8859-1';
$txt['lang_rtl'] = false;

$txt['smf_installer'] = 'SMF-installatie';
$txt['installer_language'] = 'Taal';
$txt['installer_language_set'] = 'Stel deze taal in';
$txt['congratulations'] = 'Gefeliciteerd, de installatieprocedure is voltooid!';
$txt['congratulations_help'] = 'Indien je support nodig hebt, of wanneer je forum niet weer werkt, kun je <a href="http://www.simplemachines.org/community/index.php" target="_blank">hier</a> terecht als je dat nodig hebt.';
$txt['still_writable'] = 'Je installatie directory is nog steeds schrijfbaar, je kunt beter een lagere CHMOD kiezen in verband met de veiligheid.';
$txt['delete_installer'] = 'Klik hier om het install.php bestand nu te verwijderen.';
$txt['delete_installer_maybe'] = '<i>(dit werkt niet op alle servers.)</i>';
$txt['go_to_your_forum'] = 'Je kunt <a href="%s">je nieuw ge&iuml;nstalleerde forum</a> bekijken en gebruiken. Je moet eerst zijn ingelogd, waarna je het admin gedeelte kunt benaderen.';
$txt['good_luck'] = 'Succes!<br />Simple Machines';

$txt['user_refresh_install'] = 'Forum vernieuwd';
$txt['user_refresh_install_desc'] = 'Terwijl het installatieprogramma bezig was met installeren, is gebleken dat (met de instellingen die je hebt gegeven) er een of meer te cre&euml;ren tabellen reeds bestonden.<br />Alle missende tabellen zijn alsnog gecre&euml;erd met de standaard data, maar er is geen data verwijderd uit de reeds bestaande tabellen.';

$txt['default_topic_subject'] = 'Welkom bij SMF!';
$txt['default_topic_message'] = 'Welkom bij Simple Machines Forum!<br /><br />We hopen dat je met veel plezier dit forum zult gebruiken.&nbsp; Als je problemen hebt, [url=http://www.simplemachines.org/community/index.php]vraag gerust om hulp[/url].<br /><br />Bedankt!<br />Simple Machines';
$txt['default_board_name'] = 'Algemene discussie';
$txt['default_board_description'] = 'Praat gerust over van alles en nog wat op dit board.';
$txt['default_category_name'] = 'Algemeen';
$txt['default_time_format'] = '%e %B %Y, %H:%M:%S';
$txt['default_news'] = 'SMF - Zojuist ge&iuml;nstalleerd!';
$txt['default_karmaLabel'] = 'Karma:';
$txt['default_karmaSmiteLabel'] = '[mep]';
$txt['default_karmaApplaudLabel'] = '[bejubelen]';
$txt['default_reserved_names'] = 'Admin\nWebmaster\nGuest\nroot\nBeheer\nForumbeheerder\nGast';
$txt['default_smileyset_name'] = 'Standaard';
$txt['default_classic_smileyset_name'] = 'Klassiek';
$txt['default_theme_name'] = 'SMF Standaard thema - Core';
$txt['default_classic_theme_name'] = 'Klassiek YaBB SE Thema';
$txt['default_babylon_theme_name'] = 'Babylon Thema';

$txt['default_administrator_group'] = 'Forumbeheerder';
$txt['default_global_moderator_group'] = 'Algemene moderator';
$txt['default_moderator_group'] = 'Moderator';
$txt['default_newbie_group'] = 'Nieuweling';
$txt['default_junior_group'] = 'Junior';
$txt['default_full_group'] = 'Volwaardig lid';
$txt['default_senior_group'] = 'Senior';
$txt['default_hero_group'] = 'Held';

$txt['default_smiley_smiley'] = 'Smiley';
$txt['default_wink_smiley'] = 'Knipoog';
$txt['default_cheesy_smiley'] = 'Brede lach';
$txt['default_grin_smiley'] = 'Grijns';
$txt['default_angry_smiley'] = 'Kwaad';
$txt['default_sad_smiley'] = 'Bedroefd';
$txt['default_shocked_smiley'] = 'Geschokt';
$txt['default_cool_smiley'] = 'Cool';
$txt['default_huh_smiley'] = 'Huh?';
$txt['default_roll_eyes_smiley'] = 'Rollende ogen';
$txt['default_tongue_smiley'] = 'Tong';
$txt['default_embarrassed_smiley'] = 'Verlegen';
$txt['default_lips_sealed_smiley'] = 'Ik zeg niets';
$txt['default_undecided_smiley'] = 'Onbeslist';
$txt['default_kiss_smiley'] = 'Kus';
$txt['default_cry_smiley'] = 'Huil';
$txt['default_evil_smiley'] = 'Gemeen';
$txt['default_azn_smiley'] = 'Azi&euml;';
$txt['default_afro_smiley'] = 'Afro';

$txt['error_message_click'] = 'Klik hier';
$txt['error_message_try_again'] = 'om deze stap nog eens te proberen.';
$txt['error_message_bad_try_again'] = 'om toch door te gaan met de installatie, maar let op dat dit <i>sterk</i> wordt afgeraden.';

$txt['install_settings'] = 'Standaardinstellingen';
$txt['install_settings_info'] = 'Een aantal dingen die je dient op te geven ;).';
$txt['install_settings_name'] = 'Forum naam';
$txt['install_settings_name_info'] = 'Dit is de naam van je forum, bijv. &quot;Testforum&quot;.';
$txt['install_settings_name_default'] = 'Mijn forum';
$txt['install_settings_url'] = 'Forum URL';
$txt['install_settings_url_info'] = 'Dit is de URL naar je forum <b>ZONDER de \'/\'!</b>.<br />Meestal kun je de standaard waarde in het veld zo laten- meestal klopt deze al.';
$txt['install_settings_compress'] = 'Gzip-output';
$txt['install_settings_compress_title'] = 'Comprimeer de output om bandbreedte te besparen.';
// In this string, you can translate the word "PASS" to change what it says when the test passes.
$txt['install_settings_compress_info'] = 'Deze functie werkt niet op alle servers even goed, maar kan je een hoop bandbreedte besparen.<br />Klik <a href="install.php?obgz=1&amp;pass_string=ACTIEF" onclick="return reqWin(this.href, 200, 60);" target="_blank">hier</a> om te testen of het werkt. (je zou het woord "ACTIEF" moeten zien.)';
$txt['install_settings_dbsession'] = 'Database-sessies';
$txt['install_settings_dbsession_title'] = 'Gebruik de database voor sessies in plaats van bestanden.';
$txt['install_settings_dbsession_info1'] = 'Deze feature is bijna altijd beter, omdat die sessies betrouwbaarder maakt.';
$txt['install_settings_dbsession_info2'] = 'Deze feature is meestal goed, maar zou mogelijkerwijs niet goed werken op deze server.';
$txt['install_settings_utf8'] = 'UTF-8 karakterset';
$txt['install_settings_utf8_title'] = 'Gebruik UTF-8 als standaard karakterset';
$txt['install_settings_utf8_info'] = 'Deze feature zorgt ervoor dat de database en het forum met een internationale karakterset werken. Dit kan handig zijn als je werkt met meerdere talen die verschillende karaktersets gebruiken.';
// Untranslated!
$txt['install_settings_stats'] = 'Allow Stat Collection';
$txt['install_settings_stats_title'] = 'Allow Simple Machines to Collect Basic Stats Monthly';
$txt['install_settings_stats_info'] = 'If enabled, this will allow Simple Machines to visit your site once a month to collect basic statistics. This will help us make decisions as to which configurations to optimise the software for. For more information please visit our <a href="http://www.simplemachines.org/about/stats.php">info page</a>.';
$txt['install_settings_proceed'] = 'Ga verder';

$txt['mysql_settings'] = 'MySQL-serverinstellingen';
$txt['mysql_settings_info'] = 'Deze instellingen gebruik je voor je MySQL server. Als je de gegevens niet weet, vraag deze dan aan je host of zoek ze op.';
$txt['mysql_settings_server'] = 'MySQL-servernaam';
$txt['mysql_settings_server_info'] = 'Dit is meestal localhost - dus als je het niet weet, gebruik dan localhost.';
$txt['mysql_settings_username'] = 'MySQL-gebruikersnaam';
$txt['mysql_settings_username_info'] = 'Vul hier de gebruikersnaam in die je nodig hebt om een verbinding te maken met de database.<br />Als je de gebruikersnaam niet weet, gebruik die dan van je FTP account, deze zijn veelal gelijk aan elkaar.';
$txt['mysql_settings_password'] = 'MySQL-wachtwoord';
$txt['mysql_settings_password_info'] = 'Vul hier het wachtwoord in dat je nodig hebt om een verbinding te maken met je database.<br />Als je het wachtwoord niet weet, gebruik die dan van je FTP account, deze zijn veelal gelijk aan elkaar.';
$txt['mysql_settings_database'] = 'MySQL-databasenaam';
$txt['mysql_settings_database_info'] = 'Vul hier de naam in van de database waar je SMF de data in wilt laten zetten voor je forum.<br />Als de database niet bestaat, zal er worden geprobeerd deze aan te maken.';
$txt['mysql_settings_prefix'] = 'MySQL-tabel-prefix';
$txt['mysql_settings_prefix_info'] = 'Vul hier de prefix in die je voor iedere tabel in de database wilt hebben.  <b>Installeer niet twee forums met dezelfde prefix!</b><br />Deze prefix gebruik je om meerdere installaties mogelijk te maken in dezelfde database.';

$txt['user_settings'] = 'Maak je account aan';
$txt['user_settings_info'] = 'Er wordt nu een nieuwe admin account aangemaakt voor jou.';
$txt['user_settings_username'] = 'Je gebruikersnaam';
$txt['user_settings_username_info'] = 'Kies de naam waarmee je wilt inloggen.<br />Deze kan naderhand NIET veranderd worden. De getoonde naam echter wel.';
$txt['user_settings_password'] = 'Wachtwoord';
$txt['user_settings_password_info'] = 'Vul hier het wachtwoord in dat je wilt gebruiken en onthoud deze goed!';
$txt['user_settings_again'] = 'Wachtwoord';
$txt['user_settings_again_info'] = '(controle.)';
$txt['user_settings_email'] = 'E-mailadres';
$txt['user_settings_email_info'] = 'Geef je e-mailadres ook op.  <b>Dit moet een geldig e-mailadres zijn.</b>';
$txt['user_settings_database'] = 'MySQL-databasewachtwoord';
$txt['user_settings_database_info'] = 'Uit veiligheidsoverwegingen dien je het database wachtwoord op te geven om een admin account aan te kunnen maken.';
$txt['user_settings_proceed'] = 'Klaar';

$txt['ftp_setup'] = 'FTP-verbindingsinformatie';
$txt['ftp_setup_info'] = 'Er kan een verbinding worden gemaakt via FTP om de bestanden die schrijfbaar moeten worden gemaakt als zodanig te CHMOD-en. Als dit niet wekrt, zul je dit handmatig moeten doen met een FTP programma.  Let op : SSL wordt momenteel niet ondersteund.';
$txt['ftp_server'] = 'Server';
$txt['ftp_server_info'] = 'De server en poort voor de FTP-verbinding.';
$txt['ftp_port'] = 'Poort';
$txt['ftp_username'] = 'Gebruikersnaam';
$txt['ftp_username_info'] = 'De gebruikersnaam om mee in te loggen. <i>Dit wordt nergens opgeslagen.</i>';
$txt['ftp_password'] = 'Wachtwoord';
$txt['ftp_password_info'] = 'Het wachtwoord om mee in te loggen. <i>Dit wordt nergens opgeslagen.</i>';
$txt['ftp_path'] = 'Installatiepad';
$txt['ftp_path_info'] = 'Dit is het <i>relatieve</i> pad die je gebruikt in je FTP-server.';
$txt['ftp_path_found_info'] = 'Het pad in het veld hierboven was automatisch gedetecteerd.';
$txt['ftp_connect'] = 'Verbinden';
$txt['ftp_setup_why'] = 'Waarvoor is deze stap?';
$txt['ftp_setup_why_info'] = 'Sommige bestanden moeten schrijfbaar zijn om SMF goed te laten werken. Deze stap zal proberen de bestanden schrijfbaar te maken. Echter in sommige gevallen zal dit niet werken - maak in dat geval zelf de volgende bestanden 777 (schrijfbaar):';
$txt['ftp_setup_again'] = 'om te testen of deze bestanden schrijfbaar zijn.';

$txt['error_php_too_low'] = 'Waarschuwing!  De PHP-versie die wordt gebruikt op je server voldoet niet aan de <b>minimale</b> eisen die SMF nodig heeft.<br />Als je zelf niet de host bent, dien je bij de host na te vragen of er een upgrade mogelijk is, of een andere host te gebruiken - in het geval dat je wel zelf de host bent dien je PHP te upgraden naar een recentere versie.<br /><br />Als je zeker weet dat je de juiste PHP versie hebt kun je doorgaan, maar dit wordt sterk afgeraden.';
$txt['error_missing_files'] = 'Kan belangrijke installatiebestanden in de directory waar dit script in staat niet vinden!<br /><br />Zorg ervoor dat je het gehele pakket upload, inclusief het .sql bestand, en probeer het opnieuw.';
$txt['error_session_save_path'] = 'Informeer je host dat de <b>session.save_path gespecificeerd in php.ini</b> niet juist is!  Het moet worden aangepast naar een directory die <b>bestaat</b>, en <b>schrijfbaar</b> is bij de gebruiker waar PHP draait.<br />';
$txt['error_windows_chmod'] = 'Je hebt een windows server, en enkele belangrijke bestanden zijn niet schrijfbaar.  Vraag je host <b>schrijf permissies</b> voor de bestanden bij de gebruiker waar PHP draait. De volgende bestanden of directories moeten schrijfbaar zijn:';
$txt['error_ftp_no_connect'] = 'Kan geen verbinding maken met de FTP-server met deze gegevens.';
$txt['error_mysql_connect'] = 'Kan geen verbinding maken met de MySQL-database met deze gegevens.<br /><br />Als je niet weet welke gegevens je moet ingeven, neem dan contact op met je host.';
$txt['error_mysql_too_low'] = 'De versie MySQL die wordt gebruikt is erg oud en voldoet niet aan de minimum eisen die SMF nodig heeft.<br /><br />Vraag je host om een update of een nieuwe versie, en als ze dit niet willen, probeer dan een andere host.';
$txt['error_mysql_database'] = 'Kan geen verbinding maken met de &quot;<i>%s</i>&quot; database.  Bij sommige hosts dien je een database te maken via een administratiepagina (cpanel / manager) voordat SMF het kan gebruiken.  Sommigen gebruiken ook prefixes - zoals je gebruikersnaam - bij de databasenaam.';
$txt['error_mysql_queries'] = 'Enkele queries zijn niet goed uitgevoerd.  Dit kan worden veroorzaakt door een oudere versie van MySQL.<br /><br />Technische informatie over de queries:';
$txt['error_mysql_queries_line'] = 'Regel #';
$txt['error_mysql_missing'] = 'Het installatieprogramma kon geen MySQL ondersteuning vinden in PHP. Vraag je provider of PHP wel gecompileerd is met MySQL en of de juiste extensie wel geladen is.';
$txt['error_session_missing'] = 'Het installatieprogramma kon niet detecteren of sessies voor de PHP-installatie op deze server worden ondersteund. Vraag aan je provider om je te verzekeren van het feit dat PHP gecompilleerd is met sessieondersteuning (in feite moet het sowieso expliciet worden gecompilleerd).';
$txt['error_user_settings_again_match'] = 'De wachtwoorden die je hebt opgegeven wijken af van elkaar!';
$txt['error_user_settings_taken'] = 'Sorry, er is reeds een lid geregistreerd met die gebruikersnaam en/of wachtwoord.<br /><br />Er is geen nieuwe account aangemaakt.';
$txt['error_user_settings_query'] = 'Er is een fout opgetreden in de database bij het aanmaken van een admin.  De fout was:';
$txt['error_subs_missing'] = 'Kan Sources/Subs.php niet vinden.  Zorg ervoor dat je deze upload en probeer het nog eens.';
$txt['error_mysql_alter_priv'] = 'Het MySQL account dat je specificeerde, heeft geen permissie om tabellen te wijzigen (ALTER), aan te maken (CREATE) en/of te verwijderen (DROP); dit is noodzakelijk voor het juist functioneren van SMF.';
$txt['error_versions_do_not_match'] = 'De installatie heeft een andere versie van SMF gedetecteerd aan de hand van de opgegeven informatie. Als je wilt upgraden, gebruik dan het upgradescript, en niet de installatieprocedure.<br /><br />Indien dit niet het geval is, gebruik dan andere gegevens, of maak een backup van de data en verwijder dan de huidige gegevens in de database.';
$txt['error_mod_security'] = 'De installatie heeft gedetecteerd dat de mod_security-module op je webserver is ge&iuml;nstalleerd. Mod_security blokkeert verstuurde formulieren zelfs voordat SMF er iets over kan zeggen. SMF heeft een ingebouwde veiligheidsscanner die meer effectief is dan mod_security en die ingestuurde formulieren niet blokkeert. <br /><br /><a href="http://www.simplemachines.org/redirect/mod_security">Meer informatie over het uitschakelen van mod_security</a>';
$txt['error_utf8_mysql_version'] = 'Helaas ondersteunt de huidige versie van je database het gebruik van de UTF-8 karakterset niet. Je kunt SMF zonder problemen installeren, alleen moet UTF-8-ondersteuning wel zijn uitgevinkt. Mocht je later alsnog op UTF-8 willen overgaan (bijvoorbeeld nadat de MySQL server is geupgrade naar een versie >= 4.1), dan kun je je forum via het beheerscherm converteren naar UTF-8.';

?>