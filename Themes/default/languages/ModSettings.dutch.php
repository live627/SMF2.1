<?php
// Version: 2.0 Alpha; ModSettings

$txt['smf3'] = 'Met deze pagina kun je alle features, mods, en basis opties van het forum aan te passen.  Lees de <a href="' . $scripturl . '?action=admin;area=theme;sa=settings;th=' . $settings['theme_id'] . ';sesc=' . $context['session_id'] . '">thema instellingen</a> voor meer opties.  klik op de help icoontjes voor meer informatie over een instelling.';

$txt['mods_cat_features'] = 'Basisinstellingen';
$txt['pollMode'] = 'Pollmodus';
$txt['smf34'] = 'Polls deactiveren';
$txt['smf32'] = 'Polls activeren';
$txt['smf33'] = 'Toon polls als topics';
$txt['allow_guestAccess'] = 'Sta gasten toe het forum te bekijken';
$txt['userLanguage'] = 'Activeer voorkeur taalpakket';
$txt['allow_editDisplayName'] = 'Sta gebruikers toe om hun getoonde naam te wijzigen?';
$txt['allow_hideOnline'] = 'Sta alle niet-beheerders toe om hun online status te verbergen?';
$txt['allow_hideEmail'] = 'Gebruikers mogen e-mailadres niet zichtbaar laten zijn voor iedereen (Behalve voor de admin)?';
$txt['guest_hideContacts'] = 'Verberg contactgegevens van de leden voor gasten';
$txt['titlesEnable'] = 'Extra titels activeren';
$txt['enable_buddylist'] = 'Vriendenlijsten activeren';
$txt['default_personalText'] = 'Standaard persoonlijke tekst';
$txt['number_format'] = 'Standaard nummerformaat';
$txt['time_format'] = 'Standaard tijdsinstelling';
$txt['time_offset'] = 'Algemene tijdsafwijking <div class="smalltext">(toegevoegd aan ledenspecifieke tijdsafwijking)</div>';
$txt['failed_login_threshold'] = 'Mislukte inlogdrempel';
$txt['lastActive'] = 'Gebruikers online-tijdsdrempel';
$txt['trackStats'] = 'Statistieken activeren';
$txt['hitStats'] = 'Houdt het aantal pageviews bij (statistieken moet actief staan)';
$txt['enableCompressedOutput'] = 'Gecomprimeerde output activeren';
$txt['databaseSession_enable'] = 'Gebruik database-gebaseerde sessies';
$txt['databaseSession_loose'] = 'Browsers mogen terug naar pagina\'s uit de cache';
$txt['databaseSession_lifetime'] = 'Seconden voordat een ongebruike sessie ongeldig wordt';
$txt['enableErrorLogging'] = 'Activeer het loggen van fouten';
$txt['cookieTime'] = 'Standaard duur van het login cookie duurt (in minuten & > 5)';
$txt['localCookies'] = 'Sla cookies lokaal op<div class="smalltext">(SSI zal niet werken met deze optie aan)</div>';
$txt['globalCookies'] = 'Cookies gebruiken onafhankelijk van het subdomein?<div class="smalltext">Let op : Lokaal opslaan van cookies uitschakelen!</div>';
$txt['securityDisable'] = 'Uitschakelen beveiliging administratie?';
$txt['send_validation_onChange'] = 'E-mail een nieuw wachtwoord als een lid zijn / haar e-mailadres wijzigt?';
$txt['approveAccountDeletion'] = 'Vereis goedkeuring van een forumbeheerder wanneer een lid zijn account wist';
$txt['autoOptMaxOnline'] = 'Maximaal aantal gebruikers online tijdens optimalisatie?<div class="smalltext">(0 voor geen limiet)</div>';
$txt['autoFixDatabase'] = 'Automatisch defecte tabellen repareren?';
$txt['allow_disableAnnounce'] = 'Gebruikers toestaan om notificatie van aankondigingen boards uit te schakelen ?';
$txt['disallow_sendBody'] = 'Sta geen berichttekst in notificaties toe?';
$txt['modlog_enabled'] = 'Log moderatieacties';
$txt['queryless_urls'] = 'Toon URLs zonder ?s<div class="smalltext"><b>Alleen Apache!</b></div>';
$txt['max_image_width'] = 'Max breedte van afbeeldingen (0=geen limiet)';
$txt['max_image_height'] = 'Max hoogte van afbeeldingen (0=geen limiet)';
$txt['mail_type'] = 'Mailtype';
$txt['mail_type_default'] = '(PHP-standaard)';
$txt['smtp_host'] = 'SMTP-server';
$txt['smtp_port'] = 'SMTP-poort';
$txt['smtp_username'] = 'SMTP-gebruikersnaam';
$txt['smtp_password'] = 'SMTP-wachtwoord';
$txt['enableReportPM'] = 'Melden van persoonlijke berichten inschakelen';
$txt['max_pm_recipients'] = 'Maximum aantal ontvangers per persoonlijk bericht toegestaan:<div class="smalltext">(0 voor geen limiet, beheerders uitgezonderd)</div>';
// Untranslated!
$txt['pm_posts_verification'] = 'Post count under which users must enter code when sending messages.<div class="smalltext">(0 for no limit, admin\'s are exempt)</div>';

$txt['mods_cat_layout'] = 'Lay-out';
$txt['compactTopicPagesEnable'] = 'Activeer de compacte weergave';
$txt['smf235'] = 'Wijze van weergave bij meerdere pagina\'s:';
$txt['smf236'] = 'om weer te geven';
$txt['todayMod'] = 'Vandaag-mod activeren';
$txt['smf290'] = 'Uitgeschakeld';
$txt['smf291'] = 'Alleen Vandaag';
$txt['smf292'] = 'Vandaag en Gisteren';
$txt['topbottomEnable'] = 'De omlaag / omhoog knoppen activeren';
$txt['onlineEnable'] = 'Toon Online/Offline in berichten en IM';
$txt['enableVBStyleLogin'] = 'Toon snel-inloggen op elk venster';
$txt['defaultMaxMembers'] = 'Leden per pagina in de ledenlijst';
$txt['timeLoadPageEnable'] = 'Toon de tijd benodigd om de pagina op te bouwen';
$txt['disableHostnameLookup'] = 'Schakel het opzoeken van hostnamen uit?';
$txt['who_enabled'] = 'Wie is online activeren';

$txt['smf293'] = 'Karma';
$txt['karmaMode'] = 'Karma-modus';
$txt['smf64'] = 'Deactiveer Karma|Activeer Karma-totaal|Activeer Karma-positief/-negatief';
$txt['karmaMinPosts'] = 'Stel het minimaal # posts benodigd om Karma te kunnen bewerken';
$txt['karmaWaitTime'] = 'Stel de wachttijd in uren in';
$txt['karmaTimeRestrictAdmins'] = 'Beperk beheerder tot de wachttijd';
$txt['karmaLabel'] = 'Karma-label';
$txt['karmaApplaudLabel'] = 'Karma-positief Label';
$txt['karmaSmiteLabel'] = 'Karma-negatief Label';

$txt['caching_information'] = '<div align="center"><b><u>Belangrijk! Lees dit eerst alvorens je de optie inschakeld.</b></u></div><br />
	SMF ondersteunt caching door middel van acceleratorsoftware. De accelerators die op dit moment ondersteund worden zijn:<br />
	<ul>
		<li>APC</li>
		<li>eAccelerator</li>
		<li>Turck MMCache</li>
		<li>Memcached</li>
		<li>Zend Platform/Performance Suite (niet Zend Optimizer)</li>
	</ul>
	Caching zal alleen werken op je server als je PHP met &eacute;&eacute;n van bovenstaande optimalisatiesoftware hebt ge&iuml;nstalleerd, of memcache
	beschikbaar hebt. <br /><br />
	SMF werkt met caching op verschillende niveaus. Hoe hoger het niveau dat ingeschakeld is, hoe meer CPU-tijd gebruikt zal worden om
	gecachete informatie te verkrijgen. Als caching op jouw server beschikbaar is, wordt het aangeraden om niveau 1 eerst te proberen.
	<br /><br />
	Merk op dat als je memcache gebruikt, je serverdetails in dient te vullen in onderstaande veld. Dit dient als komma gescheiden lijst
	ingevuld te worden, zoals hieronder in het voorbeeld:<br />
	&quot;server1,server2,server3:port,server4&quot;<br /><br />
	Merk op dat wanneer er geen poort gespecificeerd is, SMF poort 11211 zal gebruiken. SMF zal proberen de laadtijden over de servers te verdelen.
	<br /><br />
	%s
	<hr />';

$txt['detected_no_caching'] = '<b style="color: red;">SMF heeft geen compatibele accelerator op jouw server kunnen vinden.</b>';
$txt['detected_APC'] = '<b style="color: green">SMF heeft gedetecteerd dat APC op je server is ge&iuml;nstalleerd.';
$txt['detected_eAccelerator'] = '<b style="color: green">SMF heeft gedetecteerd dat eAccelerator op je server is ge&iuml;nstalleerd.';
$txt['detected_MMCache'] = '<b style="color: green">SMF heeft gedetecteerd dat MMCache op je server is ge&iuml;nstalleerd.';
$txt['detected_Zend'] = '<b style="color: green">SMF heeft gedetecteerd dat Zend op je server is ge&iuml;nstalleerd.';

$txt['cache_enable'] = 'Cachingniveau';
$txt['cache_off'] = 'Geen caching';
$txt['cache_level1'] = 'Niveau 1 caching';
$txt['cache_level2'] = 'Niveau 2 caching (niet aanbevolen)';
$txt['cache_level3'] = 'Niveau 2 caching (niet aanbevolen)';
$txt['cache_memcached'] = 'Memcache-instellingen';

// Untranslated!
$txt['signature_settings'] = 'Signatures';
$txt['signature_settings_desc'] = 'Use the settings on this page to decide how member signatures should be treated in SMF.';
$txt['signature_settings_warning'] = 'Note that settings are not applied to existing signatures by default. Click <a href="' . $scripturl . '?action=admin;area=featuresettings;sa=sig;apply">here</a> to apply rules to all existing signatures.';
$txt['signature_enable'] = 'Enable signatures';
$txt['signature_max_length'] = 'Aantal toegelaten karakters in het profiel<div class="smalltext">(0 voor geen maximum)</div>';
// Untranslated!
$txt['signature_max_lines'] = 'Maximum amount of lines<div class="smalltext">(0 for no max)</div>';
$txt['signature_allowed_bbc'] = 'Allowed Bulletin Board Codes';
$txt['signature_max_images'] = 'Maximum image count<div class="smalltext">(0 for no max - excludes smileys)</div>';
$txt['signature_max_smileys'] = 'Maximum smiley count<div class="smalltext">(0 for no max)</div>';
$txt['signature_max_image_width'] = 'Maximum width of signature images (pixels)<div class="smalltext">(0 for no max)</div>';
$txt['signature_max_image_height'] = 'Maximum height of signature images (pixels)<div class="smalltext">(0 for no max)</div>';
$txt['signature_max_font_size'] = 'Maximum font size allowed in signatures<div class="smalltext">(0 for no max)</div>';

?>