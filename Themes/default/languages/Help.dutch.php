<?php
// Version: 2.0 Alpha; Help

// Important! Before editing these language files please read the text at the topic of index.dutch.php.

global $helptxt;

$helptxt = array();

$txt[1006] = 'Sluit dit scherm';

$helptxt['manage_boards'] = '
	<b>Boards bewerken</b><br />
	In dit menu kun je boards aanmaken, rangschikken en verwijderen en de
	categorie&euml;n daarboven. Bijvoorbeeld, als je een brede site hebt die
	informatie biedt op het gebied van &quot;Sport&quot; en &quot;Auto\'s&quot;
	en &quot;Muziek&quot;, dan zou je die op het hoogste niveau zetten en er
	categorie&euml;n van maken. Onder elk van deze categorie&euml;n zou je
	waarschijnlijk een hi&euml;rarchie van &quot;sub-categorie&euml;n willen
	bouwen, ofwel &quot;boards&quot; met daarbinnen de verschillende berichten.
	Het is een simpele hi&euml;rarchie, met deze structuur:<br />
	<ul>
		<li>
			<b>Sport</b>
			&nbsp;- Een &quot;categorie&quot;
		</li>
		<ul>
			<li>
				<b>Basketbal</b>
				&nbsp;- Een board onder de categorie &quot;Sport&quot;
			</li>
			<ul>
				<li>
					<b>Uitslagen</b>
					&nbsp;- Een sub-board onder het board &quot;Basketbal&quot;
				</li>
			</ul>
			<li><b>Voetbal</b>
			&nbsp;- Een board onder de categorie &quot;Sport&quot;</li>
		</ul>
	</ul>
	Met categorie&euml;n kun je een forum in globale onderwerpen verdelen
	(&quot;Auto\'s, Sport&quot;), en de &quot;boards&quot; daaronder zijn de
	eigenlijke gebieden waaronder leden hun berichten kunnen plaatsen. Een
	gebruiker die ge&iuml;nteresseerd is in Pinos zou een bericht plaatsen
	onder &quot;Autos->Pinto&quot;. Categorie&euml;n zorgen ervoor dat mensen
	snel kunnen vinden waarin ze ge&iuml;nteresseerd zijn: in plaats van een
	&quot;Winkel&quot;, heb je een &quot;Doe-het-zelf-winkel&quot; en een
	&quot;Kledingswinkel&quot; waar je naar toe kunt gaan. Dit vereenvoudigt je
	zoektocht naar &quot;regenpijplijm&quot; omdat je dan direct naar de
	Doe-het-zelf gaat in plaats van de Kledingwinkel (waar het onwaarschijnlijk
	is om regenpijplijm te vinden).<br />
	Zoals hierboven opgemerkt, is een board een hoofdonderwerp onder een
	globale categorie. Als je wilt discussieren over &quot;Pinto\'s&quot;, dan
	zou je naar de &quot;Auto&quot; categorie en dan naar het &quot;Pinto&quot;
	board gaan, om je mening in dat board te plaatsen.<br />
	De beheerfuncties onder dit menu item zijn het aanmaken van nieuwe boards
	onder elke categorie, het rangschikken van boards (plaats &quot;Pinto&quot;
	onder &quot;Chevy&quot;) of het verwijderen van een volledig board.';

$helptxt['edit_news'] = '<b>SMF-nieuws bewerken</b><br />
	Dit geeft je de mogelijkheid om de tekst te wijzigen van de nieuwsitems die
	op de forumindexpagina te zien zijn. Voeg een willekeurig item toe (bijv.
	&quot;Vergeet de bijeenkomst komende dinsdag niet&quot;). Elk nieuws item
	moet in een aparte invoervak ingevuld worden en wordt willekeurig
	getoond.';

$helptxt['view_members'] = '
	<ul>
		<li>
			<b>Bekijk alle leden</b><br />
			Je kunt alle leden van het forum bekijken. Je ziet een lijst van gekoppelde
			ledennamen. Je kunt op elk van die namen klikken om details over die leden
			(website, leeftijd, etc.) op te vragen en (als je administrator bent) te
			wijzigen. Je hebt volledige controle over leden, inclusief de mogelijkheid
			om ze te verwijderen van het forum.<br /><br />
		</li>
		<li>
			<b>Wacht op goedkeuring</b><br />
			Dit gedeelte zie je alleen als je hebt ingesteld dat de admin nieuwe leden dient goed te keuren. Iedereen die zich aanmeld
			op het forum wordt alleen lid als deze aanmelding wordt goedgekeurd door de admin. Dit gedeelte geeft alle leden weer die
			nog wachten op goedkeuring, onder vermelding van hun e-mailadres en IP-adres. Je kunt hier goedkeuren of afkeuren (verwijderen)
			door het vakje aan te vinken naast de aanmelding en de actie te kiezen uit de drop-down box onderaan die je uitgevoerd wilt zien.
			Bij het afkeuren van een aanmelding kun je kiezen om deze te verwijderen met of zonder deze persoon op de hoogte te stellen van je keuze.<br /><br />
		</li>
		<li>
			<b>Wachten op activering</b><br />
			Dit gedeelte zie je alleen als je hebt ingesteld dat de aanmeldingen dienen te worden goedgekeurd. Dit gedeelte toont alle
			leden die hun account nog niet hebben geactiveerd. Hier kun je de aanmeldingen accepteren, afwijzen of de leden een herinnering
			sturen die hun account nog niet hebben geactiveerd. Zoals hierboven kun je de persoon ook een e-mail sturen met de keuze die je
			hebt gemaakt.<br /><br />
		</li>
	</ul>';

$helptxt['ban_members'] = '<b>Ban leden</b><br />
	SMF biedt de mogelijkheid om gebruikers te &quot;bannen&quot;, zodat mensen die
	het vertrouwen van het forum hebben geschonden door te spammen, mensen
	lastig te vallen, etc. worden geweerd. Als een administrator, als je de
	berichten bekijkt, kun je het IP-adres dat een gebruiker had op het moment
	van plaatsen, zien. In de banlijst kun je simpelweg dat IP-adres intypen,
	opslaan en dan kunnen ze niet langer meer berichten plaatsen vanaf die
	locatie.<br />
	Je kunt ook mensen bannen vanaf hun e-mailadres.';

$helptxt['modsettings'] ='<b>Ge&iuml;nstalleerde \'Mods\' Instellingen en Opties</b><br />
	SMF heeft enkele voor-ge&iuml;nstalleerde mods, die je aan en uit kunt
	schakelen in dit menu.';

$helptxt['number_format'] = '<b>Standaard nummerformaat</b><br />
	Je kunt deze instelling gebruiken om de manier waarop nummers op je forum getoond worden te veranderen. Het formaat van deze instelling is:<br />
	<div style="margin-left: 2ex;">1,234.00</div><br />
	Waar \',\' het karakter is om duizendtallen op te splitsen en \'.\' het karakter voor decimale punt en het aantal nullen bepaalt de nauwkeurigheid van de afrondingen.';

$helptxt['time_format'] = '<b>Tijd weergave</b><br />
	Je kunt zelf de tijd en datumweergave aanpassen. Er zijn een hoop
	mogelijkheden, maar het is vrij simpel. Gebruik de volgende codes (voor
	meer informatie kun je kijken op
	<a href="http://www.php.net/manual/function.strftime.php">php.net</a>).<br />
	<br />
	De volgende conversie specifiers worden herkend in de format string: <br />
	<span class="smalltext">
	&nbsp;&nbsp;%a - afgekorte weekdag naam<br />
	&nbsp;&nbsp;%A - volledige weekdag naam<br />
	&nbsp;&nbsp;%b - afgekorte maand naam<br />
	&nbsp;&nbsp;%B - volledige maand naam<br />
	&nbsp;&nbsp;%d - dag van de maand (01 tot 31) <br />
	&nbsp;&nbsp;%D<b>*</b> - hetzelfde als %m/%d/%y <br />
	&nbsp;&nbsp;%e<b>*</b> - dag van de maand (1 tot 31) <br />
	&nbsp;&nbsp;%H - uur gebruik makend van een 24 uurs klok (van 00 tot 23) <br />
	&nbsp;&nbsp;%I - uur gebruik makend van een 12 uurs klok (van 01 tot 12) <br />
	&nbsp;&nbsp;%m - maand als decimaal nummer (01 to 12) <br />
	&nbsp;&nbsp;%M - minuut als decimaal nummer <br />
	&nbsp;&nbsp;%p - &quot;am&quot; of &quot;pm&quot; afhankelijk van de
	huidige	tijd<br />
	&nbsp;&nbsp;%R<b>*</b> - tijd gebruik makend van een 24 uurs klok <br />
	&nbsp;&nbsp;%S - seconde als decimaal nummer <br />
	&nbsp;&nbsp;%T<b>*</b> - huidige tijd, hetzelfde als %H:%M:%S <br />
	&nbsp;&nbsp;%y - 2-cijferige jaaraanduiding (00 to 99) <br />
	&nbsp;&nbsp;%Y - 4-cijferige jaaraanduiding<br />
	&nbsp;&nbsp;%Z - tijdzone of naam of afkorting <br />
	&nbsp;&nbsp;%% - een letterlijk \'%\' karakter <br />
	<br />
	<i>* Werkt niet op Windows gebaseerde servers.</i></span>';

$helptxt['live_news'] = '<b>Live aankondigingen</b><br />
	Deze box toont recente aankonigingen van <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.
	Je zou hier af en toe eens moeten langskomen voor updates, nieuwe releases en andere belangrijke informatie van het Simple Machines Team.';

// Update from English files!
$helptxt['registrations'] = '<b>Registratiebeheer</b><br />
	Deze sectie bevat alle functies die nodig zouden kunnen zijn voor nieuwe registraties te beheren op het forum. Het bevat 3 secties
	die zichtbaar zijn afhankelijk van je instellingen. Te weten:<br /><br />
	<ul>
		<li>
			<b>Registreren nieuw lid</b><br />
			Je kunt hier zelf leden registreren. Dit kan handig zijn bij forums waar het registreren van nieuwe leden dicht gezet is,
			of in gevallen waar de admin een test account wil aanmaken. Als de optie is geactiveerd dat het nieuwe lid een e-mail krijgt
			waarin een link is bijgesloten, dient men die te volgen om de account te activeren. Je kunt op deze manier ook leden een nieuw
			wachtwoord mailen indien daarom wordt verzocht.<br /><br />
		</li>
		<li>
			<b>Forumregels bewerken</b><br />
			Hiermee kun je de tekst instellen die wordt getoond wanneer leden zich
			registreren voor je forum. Je de standaard bijgeleverde forumregels
			bewerken of verwijderen.
		</li>
		<li>
			<b>Instellingen</b><br />
			Deze sectie is alleen zichtbaar als je de \'Beheer forum en database\' permissie hebt. Vanaf dit scherm kun je instellen welke registratiemethode op je forum wordt gebruikt alsmede andere registratie-gerelateerde instellingen.
		</li>
	</ul>';
$helptxt['modlog'] = '<b>Moderatielog</b><br />
	Dit gedeelte logt alle activiteiten die een admin of moderator uitvoert op het forum. Om er zeker van te zijn dat deze activiteiten
	niet door de moderators kunnen worden gewist, kunnen de regels niet worden gewist gedurende 24 uur na de betreffende activiteit.
	Dee \'objecten\' kolom toont alle variabelen die bij de actie horen.';
$helptxt['error_log'] = '<b>Foutenlog</b><br />
	Het foutenlog toont alle fouten die het forum genereert. Het laat alle fouten zien, gesorteerd op datum. De volgorde kun je aanpassen door
	op het zwarte pijltje naast iedere datum. Je kunt ook nog de fouten filteren door op de afbeelding naast iedere foutmelding te klikken.
	Zo kun je bijvoorbeeld per lid filteren. Als een filter actief is, zie je alleen de resultaten die bij dat filter behoren.';
$helptxt['theme_settings'] = '<b>Thema-instellingen</b><br />
	Het instellingen scherm laat je alle instellingen van een bepaald thema aanpassen. Je kunt hier bijvoorbeeld de thema directory en URL informatie
	wijzigen maar ook opties aanpassen die betrekking hebben op de lay-out van het forum. De meeste thema\'s hebben een aantal opties die kunnen worden
	geconfigureerd door leden om zo aan hun persoonlijke wensen tegemoet te komen.';
// Update from English files!
$helptxt['smileys'] = '<b>Smileysets en iconen</b><br />
	Hier kun je smileys en smileysets toevoegen en verwijderen. Denk er aan dat als je een smiley in de ene set toevoegt, deze in alle sets moet voorkomen - anders zou het verwarrend worden voor leden die verschillende sets gebruiken.';
$helptxt['calendar'] = '<b>Beheer kalender</b><br />
	Hier kun je de huidige kalenderinstellingen bewerken en feestdagen die op de kalender zichtbaar zijn.';

$helptxt['serversettings'] = '<b>Serverinstellingen</b><br />
	Hier kun je de kernconfiguratie van je forum instellen. Deze sectie bevat de database- en URL-instellingen, alsook andere
	belangrijke configuratieitems zoals mailinstellingen en caching. Denk goed na wanneer je een instelling wil veranderen, want een fout
	zou het forum ontoegankelijk kunnen maken.';

$helptxt['topicSummaryPosts'] = 'Hier geef je aan hoeveel berichten je totaal op &eacute;&eacute;n pagina wilt
	zien in een bepaalde topic.';
$helptxt['enableAllMessages'] = 'Stel dit in op het <em>maximaal</em> aantal berichten dat een topic mag hebben voor het tonen van de \'allemaal\'-link. Als dit lager wordt ingesteld dan de &quot;Maximum aantal berichten vertonen op &eacute;&eacute;n pagina&quot;-instelling, zal de link simpelweg nooit getoond worden, terwijl een te hoge instelling het forum zou kunnen vertragen.';
$helptxt['enableStickyTopics'] = 'Sticky topics zijn berichten die bovenaan de berichtenlijst
	blijven staan.  Meestal gebruikt men dat voor belangrijke berichten.
	Alleen moderators en admins kunnen een topic Sticky maken.';
$helptxt['allow_guestAccess'] = 'Als dit vak niet is aangevinkt, zullen gasten niets anders kunnen doen dan de primaire acties - inloggen, registreren, wachtwoordherinnering opvragen, etc. - op je forum. Dit is niet hetzelfde als gasten de toegang verhinderen tot de boards.';
$helptxt['userLanguage'] = 'Deze functie geeft de gebruiker de mogelijkheid zelf een taal
	te kiezen waarin het forum wordt weergegeven.  De standaardwaarde waarin het forum
	staat ingesteld, blijft het zelfde.';
$helptxt['trackStats'] = 'Statistieken:<br />
	De laatste berichten en de meest populaire
	berichten worden getoond. Ook diverse andere statistieken worden getoond,
	zoals het hoogst aantal leden online, nieuwe leden en nieuwe berichten.
	<hr />
	Pageviews:<br />
	Voegt een extra kolom toe aan de statistieken pagina met daarin het aantal
	pageviews op het forum.';
$helptxt['titlesEnable'] = 'Extra titels activeren geeft de leden de mogelijkheid om zelf
	een extra titel onder hun gebruikersnaam te kiezen.<br />
	<i>voorbeeld:</i><br />
	Jeff<br />
	Toffe gast';
$helptxt['topbottomEnable'] = 'Dit voegt de OMHOOG en OMLAAG knoppen toe, zodat men dmv.
	&eacute;&eacute;n druk op de knop naar boven of naar beneden kan, zonder
	te hoeven scrollen.';
$helptxt['onlineEnable'] = 'Dit toont een afbeelding waaraan je kunt zien of het
	betreffende lid online of offline is';
$helptxt['todayMod'] = 'Met deze functie aangevinkt wordt er in plaats van de datum
	<b>vandaag</b> of </b>gisteren</b> weergegeven.';
$helptxt['enablePreviousNext'] = 'Hier wordt een link weergegeven naar een <b>volgend</b> of
	<b>vorig</b> bericht.';
$helptxt['pollMode'] = 'Deze functie is om leden toe te staan om polls te starten.
	Je kunt hierin onderscheid maken in alleen admins of ook alle leden.<br />
	Ook kun je bepalen wie de polls mogen bewerken.';
$helptxt['enableVBStyleLogin'] = 'Dit laat een klein inlog veld zien onderaan je forum.';
$helptxt['enableCompressedOutput'] = 'Deze optie zorgt voor een lager bandbreedte gebruik, echter:
	zlib moet zijn ge&iuml;nstalleerd.';
$helptxt['databaseSession_enable'] = 'Deze optie maakt gebruik van de database voor het opslaan van sessies - het werkt het best op load balanced servers, maar helpt tegen allerlei sessie timeout problemen en zou het forum sneller kunnen maken.';
$helptxt['databaseSession_loose'] = 'Het aanzetten van deze optie bespaart bandbreedte van je forum en zorgt ervoor dat het klikken op \'terug\' niet de pagina zal herladen - het nadeel is echter dat de (nieuwe) iconen niet worden geactualiseerd net als enkele andere dingen (tenzij je naar die pagina klikt in plaats van terug gaat naar die pagina).';
$helptxt['databaseSession_lifetime'] = 'Dit is het aantal seconden dat een sessie blijft bestaan nadat deze voor het laatst is benaderd. Als de sessie niet benaderd wordt voor een te lange tijd, dan treedt er een \'timeout\' op. Een waarde hoger dan 2400 wordt aanbevolen.';
$helptxt['enableErrorLogging'] = 'Dit zal alle fouten loggen, zodat je naderhand kunt bekijken
	wat er fout is gegaan.';
$helptxt['allow_disableAnnounce'] = 'Hierdoor kunnen gebruikers de aankondiginging, die door &quot;kondig topic aan&quote aan te vinken bij het plaatsen van een bericht wordt verstuurd, uitzetten.';
$helptxt['disallow_sendBody'] = 'Deze optie schakelt de optie uit om de teksten van reacties en bericht in de notificatie-mailtjes te ontvangen<br /><br />Vaak reageren leden op de notifactie-e-mail wat in de meeste gevallen betekent dat de forumbeheerder de reactie ontvangt.';
$helptxt['compactTopicPagesEnable'] = 'Deze optie zorgt voor een selectieve weergave van het aantal
	pagina\'s.<br />
	<i>Voorbeeld:</i><br />
	&quot;3&quot; resultaat: 1 ... 4 [5] 6 ... 9 <br />
	&quot;5&quot; resultaat: 1 ... 3 4 [5] 6 7 ... 9';
$helptxt['timeLoadPageEnable'] = 'Dit geeft de tijd in seconden weer, die het forum nodig had
	om de pagina op te bouwen. Zichtbaar onderaan het forum.';
$helptxt['removeNestedQuotes'] = 'Deze optie zorgt ervoor dat alleen de quote uit het
	oorspronkelijke bericht wordt weergegeven, en niet de andere quotes.';
$helptxt['simpleSearch'] = 'Deze optie zorgt voor een simpele weergave van de zoekpagina,
	met daaronder een link naar een geavanceerde zoekpagina.';
$helptxt['max_image_width'] = 'Deze optie gebruik je om een maximum formaat aan te geven bij
	een afbeelding. Afbeeldingen die kleiner zijn dan het maximum ingestelde
	formaat worden niet uitgerekt.';
$helptxt['mail_type'] = 'Met deze instelling kun je kiezen of je de standaard PHP-instellingen wilt gebruiken of dat je deze wilt vervangen door SMTP-instellingen. PHP ondersteunt geen authenticatie voor SMTP (wat door veel servers wel vereist wordt), dus als je dat wel wilt, moet je SMTP selecteren. Denk erom dat SMTP langzamer kan werken en bovendien zullen sommige servers geen gebruikersnaam en wachtwoord accepteren.<br /><br />Je hoeft geen SMTP-instellingen in te vullen als je de standaard PHP-instellingen gebruikt.';
$helptxt['attachment_manager_settings'] = 'Attachments are files that members can upload, and attach to a post.<br /><br />
	<b>Check attachment extension</b>:<br /> Do you want to check the extension of the files?<br />
	<b>Allowed attachment extensions</b>:<br /> You can set the allowed extensions of attached files.<br />
	<b>Attachments directory</b>:<br /> The path to your attachment folder<br />(example: /home/sites/yoursite/www/forum/attachments)<br />
	<b>Max attachment folder space</b> (in KB):<br /> Select how large the attachment folder can be, including all files within it.<br />
	<b>Max attachment size per post</b> (in KB):<br /> Select the maximum filesize of all attachments made per post.  If this is lower than the per-attachment limit, this will be the limit.<br />
	<b>Max size per attachment</b> (in KB):<br /> Select the maximum filesize of each separate attachment.<br />
	<b>Max number of attachments per post</b>:<br /> Select the number of attachments a person can make, per post.<br />
	<b>Display attachment as picture in posts</b>:<br /> If the uploaded file is a picture, this will show it underneath the post.<br />
	<b>Resize images when showing under posts</b>:<br /> If the above option is selected, this will save a separate (smaller) attachment for the thumbnail to decrease bandwidth.<br />
	<b>Maximum width and height of thumbnails</b>:<br /> Only used with the &quot;Resize images when showing under posts&quot; option, the maximum width and height to resize attachments down from.  They will be resized proportionally.';
$helptxt['karmaMode'] = 'Karma is een feature dat de populariteit van een lid kan
	aangeven. Je kunt het aantal berichten opgeven vanaf waar je een &quot;karma&quot;
	kunt hebben, de wachttijd aangeven tussen het stemmen en of de admins zich
	ook aan die wachttijd moeten houden.';
$helptxt['cal_enabled'] = 'De kalender kan worden gebruikt om verjaardagen te tonen of om belangrijke gebeurtenissen op het forum aan te duiden.<br /><br />
	<b>Toon de dagen als link naar \'Post gebeurtenis\'</b>:<br />De leden kunnen voor die dag een gebeurtenis aangeven door op die datum te klikken.<br />
	<b>Toon weeknummers</b>:<br />Show which week it is.<br />
	<b>Max aantal dagen vooruit op de forumindexpagina</b>:<br />Als deze op 7 staat, zullen de gebeurtenissen van de komende week getoond worden.<br />
	<b>Toon feestdagen op de forumindexpagina</b>:<br />Toon de aankomende feestdagen in de kalenderbalk op de forumindexpagina.<br />
	<b>Toon verjaardagen op de forumindexpagina</b>:<br />Toon de aankomende verjaardagen in de kalenderbalk op de forumindexpagina.<br />
	<b>Toon gebeurtenissen op de forumindexpagina</b>:<br />Toon de aankomende gebeurtenissen in de kalenderbalk op de forumindexpagina.<br />
	<b>Standaard board om in te posten</b>:<br />In welk board moeten standaard de gebeurtenissen gepost worden?<br />
	<b>Sta niet aan een topic gekoppelde gebeurtenissen toe</b>:<br />Sta leden toe om gebeurtenissen te plaatsen die niet aan een bericht in het forum gekoppeld zijn.<br />
	<b>Minimum jaar</b>:<br />Kies het &quot;eerste&quot; jaar op de kalender.<br />
	<b>Maximum jaar</b>:<br />Kies het &quot;laatste&quot; jaar op de kalender.<br />
	<b>Kleur voor verjaardagen</b>:<br />Geef de kleur van de verjaardagstekst in het kalenderscherm<br />
	<b>Kleur voor gebeurtenissen</b>:<br />Geef de kleur van de gebeurtenissen in het kalenderscherm<br />
	<b>Kleur voor feestdagen</b>:<br />Geef de kleur van de feestdagen in het kalenderscherm<br />
	<b>Gebeurtenissen over meerdere dagen toestaan?</b>:<br />Aanvinken om gebeurtenissen van meerdere dagen toe te staan.<br />
	<b>Maximaal aantal dagen voor een gebeurtenis</b>:<br />Geef het maximum aantal dagen dat een gebeurtenis mag duren op de kalender.<br /><br />
	Onthoud dat het gebruik van de kalender (plaatsen van gebeurtenissen, bekijken van gebeurtenissen, etc.) afhankelijk is van permissies die ingesteld worden bij het permissiebeheer.';
$helptxt['localCookies'] = 'SMF gebruikt cookies om inlog informatie op te slaan op de
	computer. Cookies kunnen globaal worden opgeslagen (myserver.com) of lokaal
	(myserver.com/path/to/forum).<br />
	Vink deze optie aan als je problemen ondervindt met gebruikers die spontaan
	worden uitgelogd.<hr />
	Globaal opgeslagen cookies zijn een stuk minder veilig indien er sprake is
	van een \'shared webserver\' (zoals Tripod).<hr />
	lokaal opgeslagen cookies werken niet buiten de forum map, dus wanneer je
	forum staat op www.myserver.com/forum, kunnen pagina\'s zoals
	www.myserver.com/index.php niet de informatie opvragen. Zeker bij gebruik
	van SSI.php, zijn globaal opgeslagen cookies aanbevolen.';
$helptxt['enableBBC'] = 'Deze optie biedt de leden de mogelijkheid om Bulletin Board Code (BBC) te gebruiken op het forum, waardoor ze afbeeldingen kunnen tonen en andere opmaak mogelijkheden krijgen.';
$helptxt['time_offset'] = 'Niet alle forumbeheerders willen dat het forum dezelfde tijd gebruiken als de server waarop het forum wordt gehost. Gebruik deze optie om het tijdsverschil (in uren) aan te geven tussen de huidige tijd en de server tijd. Negatieve en decimale waardes zijn toegestaan.';
$helptxt['spamWaitTime'] = 'Hier kun je aangeven hoeveel tijd er dient te zitten tussen het plaatsen van berichten. Dit kan voorkomen dat mensen gaan &quot;spammen&quot; op het forum.';

$helptxt['enablePostHTML'] = 'Hiermee kun je enkele standaard HTML-tags mee
	plaatsen: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;pre&gt;, &lt;blockquote&gt;, &lt;img src=&quot;&quot;
	/&gt;, &lt;a href=&quot;&quot;&gt; en &lt;br /&gt;.';

$helptxt['themes'] = 'Hier kun je aangeven of het standaardthema gekozen kan
	worden, welk thema gasten zullen gebruiken en andere opties selecteren.
	Klik op een thema rechts om de instellingen ervan te wijzigen.';
$helptxt['theme_install'] = 'Je kunt hier nieuwe thema\'s installeren.  Je kunt dit doen vanuit een reeds bestaande directory, door een archief voor het thema te uploaden, of door het standaard thema te kopie&euml;ren.<br /><br />Let op dat het archief of de directory een <tt>theme_info.xml</tt> definitie bestand dient te bevatten.';
$helptxt['enableEmbeddedFlash'] = 'Deze optie zorgt ervoor dat je gebruikers flash
	direct kunnen toevoegen aan hun berichten, net als plaatjes. Dit kan
	mogelijk een beveiligingsrisico zijn, hoewel slechts weinig mensen het
	succesvol hebben misbruikt. GEBRUIK OP EIGEN RISICO!';
$helptxt['xmlnews_enable'] = 'Zorgt ervoor dat mensen kunnen verwijzen naar
	<a href="{$scripturl}?action=.xml;sa=news">Actuele berichten</a> en
	gelijkwaardige gegevens.  Het is ook aan te bevelen dat je de grootte van
	de (nieuws)berichten te beperken, omdat wanneer rss data bij sommige
	clients zoals Trillian getoond wordt het te verwachten is dat het wordt
	afgekapt.';
$helptxt['hotTopicPosts'] = 'Verander het aantal berichten per topic om de
	status &quot;Populair topic&quot; of &quot;Zeer populair
	topic&quot;.';
$helptxt['globalCookies'] = '
	Je kunt hiermee onafhankelijk van het subdomein de forumcookies gebruiken.  Bijvoorbeeld als....<br />
	Je website staat op http://www.simplemachines.org/,<br />
	en je forum staat op http://forum.simplemachines.org/,<br />
	met deze optie kun je de forum cookies gebruiken op je (hoofd)website.';
$helptxt['securityDisable'] = 'Dit <i>deactiveert</i> de extra wachtwoord controle voor het admin gedeelte. Het wordt aangeraden dit NIET te deactiveren!';
$helptxt['securityDisable_why'] = 'Dit is je huidige wachtwoord.<br /><br />Dit is een extra controle om te bepalen dat <b>jij</b> toegang hebt tot dit gedeelte.';
$helptxt['emailmembers'] = 'In dit bericht kun je een aantal &quot;variabelen&quot; gebruiken.  Te weten:<br />
	{\$board_url} - De URL naar je forum.<br />
	{\$current_time} - De huidige tijd.<br />
	{\$member.email} - Het huidige e-mailadres van het lid.<br />
	{\$member.link} - De huidige link van de gebruiker.<br />
	{\$member.id} - Het huidige ID van het lid.<br />
	{\$member.name} - De huidige naam van het lid.<br />
	{\$latest_member.link} - Het meest recent aangemelde lid link.<br />
	{\$latest_member.id} - Het meest recent aangemelde lid ID.<br />
	{\$latest_member.name} - Het meest recent aangemelde lid naam.';
$helptxt['attachmentEncryptFilenames'] = 'Versleutelen van bestandsnamen van bijlagen zorgt ervoor dat je meerdere bestanden met dezelfde naam kunt gebruiken en verhoogt de veiligheid.  Het kan echter er ook voor zorgen dat het herstellen van de database een stuk lastiger wordt indien er iets
	is fout gegaan.';

$helptxt['failed_login_threshold'] = 'Stel het aantal pogingen in die een gebruiker kan wagen alvorens naar het scherm te worden gestuurd waar hij zijn wachtwoord kan laten opsturen.';
$helptxt['oldTopicDays'] = 'Als deze optie is ingesteld, wordt er een waarschuwing getoond wanneer een lid probeert te reageren op een topic waarop al een (gespecificeerd) aantal dagen niet gereageerd is. Zet deze instelling op 0 om deze feature uit te schakelen.';
$helptxt['edit_wait_time'] = 'Aantal seconden die gebruikt mogen worden om een bericht te bewerken.';
$helptxt['edit_disable_time'] = 'Aantal minuten die mogen verstrijken, voordat een lid zijn bericht niet langer meer kan wijzigen. Stel op 0 in om deze feature uit te schakelen.<br /><br /><i>Let op: dit be&iuml;nvloedt niet gebruikers met de permissie om andermans bericht aan te passen.</i>';
$helptxt['enableSpellChecking'] = 'Spellingscontrole activeren. De pspell library dient ge&iuml;nstalleerd te zijn op de server en de PHP configuratie moet ingesteld zijn om de pspell library te gebruiken. Jouw server heeft deze mogelijkheid ' . (function_exists('pspell_new') ? 'WEL' : 'NIET') . ' actief.';
$helptxt['lastActive'] = 'Stel hier het aantal minuten in dat leden nog actief worden weergegeven op het forum na hun laatste activiteit. Standaard is dit 15 minuten.';

$helptxt['autoOptDatabase'] = 'Deze optie optimaliseert de database om de zoveel dagen. Stel het in op 1 als je dit elke dag wilt doen. Je kunt ook het maximum aantal gebruikers opgeven dat online mag zijn op dat moment zodat de server niet te zwaar belast zal worden.';
$helptxt['autoFixDatabase'] = 'Dit zal automatisch beschadigde tabellen repareren.  Je krijgt een e-mail als dit gebeurt.';

$helptxt['enableParticipation'] = 'Dit laat een icoon zien bij de topics waar een gebruiker een bericht in geplaatst heeft.';

$helptxt['db_persist'] = 'Houdt de verbinding actief om de performance op te schroeven.  Als je niet op een dedicated server wordt gehost, kan dit problemen met je host opleveren.';
// Untranslated!
$helptxt['ssi_db_user'] = 'Optional setting to use a different database user and password when you are using SSI.php.';

$helptxt['queryless_urls'] = 'Dit past het formaat van de URLs een beetje aan zodat zoekmachines er beter mee overweg kunnen. Ze zullen er uit zien als index.php/topic,1.html.<br /><br />Deze feature zal ' . (strpos(php_sapi_name(), 'apache') !== false ? 'wel' : 'niet') . ' werken op jouw server.';
$helptxt['countChildPosts'] = 'Het selecteren van deze optie zorgt ervoor dat berichten en topics in de sub-boards meegeteld worden op de indexpagina.<br /><br />Dit maakt de zaak merkbaar langzamer, maar het betekent wel dat een hoofdboard zonder berichten erin niet op \'0\' komt te staan.';
$helptxt['fixLongWords'] = 'Deze optie zorgt ervoor dat woorden langer dan een bepaalde lengte zullen worden opgesplitst zodat de lay-out van het forum niet wordt verstoord.';
$helptxt['allow_ignore_boards'] = 'Checking this option will allow users to select boards they wish to ignore.';

$helptxt['who_enabled'] = 'Deze optie laat je kiezen of je wilt dat leden van elkaar kunnen zien waar ze zijn en wat ze aan het doen zijn.';

$helptxt['recycle_enable'] = '&quot;Recyclen&quot; van verwijderde topics en berichten naar het aangegeven board.';

$helptxt['enableReportPM'] = 'Deze optie zorgt ervoor dat je gebruikers persoonlijke berichten kunnen melden aan het beheerteam. Dit kan handig zijn om misbruik van persoonlijke berichten in de gaten te houden.';
$helptxt['max_pm_recipients'] = 'Deze optie stelt je in staat een maximum aantal ontvangers per persoonlijk bericht dat een forumlid stuurt in te stellen. Dit kan helpen om misbruik van het PM-systeem te voorkomen. Merk op dat leden die nieuwsbrieven kunnen versturen uitgezonderd zijn. Stel in op nul voor geen limiet.';
// Untranslated!
$helptxt['pm_posts_verification'] = 'This setting will force users to enter a code shown on a verification image each time they are sending a personal message. Only users with a post count below the number set will need to enter the code - this should help combat automated spamming scripts.';
$helptxt['pm_posts_per_hour'] = 'This will limit the number of personal messages which may be sent by a user in a one hour period. This does not affect admins or moderators.';

$helptxt['default_personalText'] = 'Stelt de standaard tekst in die een gebruiker als &quot;persoonlijke tekst&quot; zal hebben.';

$helptxt['modlog_enabled'] = 'Logt alle moderatie acties.';

$helptxt['guest_hideContacts'] = 'Indien geselecteerd, verbergt deze optie alle e-mailadressen en messenger contactgegevens van de leden voor gasten op je forum';

$helptxt['registration_method'] = 'Deze optie bepaalt welke registratie methode wordt gebruikt op het forum. Je kunt kiezen uit:<br /><br />
	<ul>
		<li>
			<b>Registratie uit:</b><br />
				Blokkeert het registreren, wat inhoudt dat niemand meer kan registreren op je forum.<br />
		</li><li>
			<b>Directe registratie</b><br />
				Nieuwe leden kunnen meteen inloggen en posten na registratie op je forum.<br />
		</li><li>
			<b>Leden activeren</b><br />
				Leden die zich hebben geregistreerd krijgen een e-mail bericht met een link die ze dienen te volgen alvorens ze lid kunnen worden.<br />
		</li><li>
			<b>Leden goedkeuren</b><br />
				Nieuwe leden dienen eerst te worden goedgekeurd door de admin alvorens ze lid kunnen worden.
		</li>
	</ul>';
$helptxt['send_validation_onChange'] = 'Als deze optie is geactiveerd moeten de leden hun account opnieuw activeren aan de hand van een e-mailbericht dat ze krijgen op het nieuwe e-mailadres';
$helptxt['send_welcomeEmail'] = 'Als deze optie is geactiveerd krijgen nieuwe leden een welkomst-e-mail bericht gestuurd';
$helptxt['password_strength'] = 'Deze instelling bepaalt de vereiste sterkte van wachtwoorden die gebruikt worden door je forumleden. Hoe &quot;sterker&quot; het wachtwoord, hoe moeilijker het is om iemands account te kraken.
	De mogelijke instellingen zijn:
	<ul>
		<li><b>Laag:</b> Het wachtwoord moet minimaal vier karakters lang zijn.</li>
		<li><b>Middel:</b> Het wachtwoord moet minstens acht karakters lang zijn and mag niet bestaan uit een gebruikersnaam of een e-mailadres.</li>
		<li><b>Hoog:</b> Net zoals bij middel, behalve dat het wachtwoord ook een mix van hoofd- en kleine letters moet bevatten en tenminste &eactue;&eacute;n nummer.</li>
	</ul>';

$helptxt['coppaAge'] = 'De waarde in dit vak bepaalt de minimumleeftijd die nieuwe leden moeten hebben om directe toegang tot het forum te krijgen.
	Bij registratie wordt hen gevraagd om te bevestigen of ze ouder dan deze leeftijd zijn en zo niet wordt hun aanvraag afgewezen of opgeschort totdat de ouder toestemming gegeven hebben - afhankelijk van het gekozen type restrictie.
	Als een waarde van 0 is ingevuld, dan zullen alle andere leeftijdsinstellingen worden genegeerd.';
$helptxt['coppaType'] = 'Indien leeftijdsrestricties zijn ingesteld, bepaalt deze instelling wat er gebeurt als een lid onder de minimale leeftijd zich probeert te registreren op je forum. Er zijn twee mogelijke keuzes:
	<ul>
		<li>
			<b>Verwerp de registratie:</b><br />
				De registratie van elk nieuw lid dat onder de minimum leeftijd is zal per direct worden verworpen.<br />
		</li><li>
			<b>Vereis ouderlijke goedkeuring</b><br />
				Elk nieuw lid dat zich probeert te registreren en onder de minimaal toegestane leeftijd is, krijgt een markering in zijn account in afwachting op goedkeuring en krijgt een formulier te zien waarin zijn ouders toestemming kunnen geven om lid te worden van het forum.
				Ze krijgen ook de contactgegevens van het forum zoals ingesteld op de instellingen-pagina zien, zodat ze het formulier naar de forumbeheerder per e-mail of fax kunnen sturen.
		</li>
	</ul>';
$helptxt['coppaPost'] = 'De contactgegevens zijn verplicht, zodat formulieren die toestemming geven voor minderjarige registratie gestuurd kunnen worden naar de forumbeheerder. Deze gegevens zullen getoond worden aan alle nieuwe minderjarigen en zijn vereist voor goedkeuring van ouders of voogden. In ieder geval moet het postadres of het faxnummer ingevuld zijn.';

$helptxt['allow_hideOnline'] = 'Met deze optie geselecteerd kunnen leden hun online status voor andere gebruikers (behalve beheerders) verbergen. Indien uitgeschakeld, kunnen alleen gebruikers die het forum kunnen beheren, hun online status verbergen. Het uitschakleen van deze optie zal niemands status veranderen - het voorkomt alleen dat ze zichzelf kunnen verbergen in de toekomst.';
$helptxt['allow_hideEmail'] = 'Als deze optie is geactiveerd kunnen leden hun e-mailadres verbergen voor anderen. De beheerder kan echter alle e-mailadressen gewoon zien.';

$helptxt['latest_support'] = 'Je ziet hier de meest voorkomende problemen en vragen m.b.t. de server configuratie. Deze informatie wordt niet geregistreerd.<br /><br />Indien je blijft zien &quot;Retrieving support information...&quot;, kan je pc waarschijnlijk geen verbinding maken met <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.';
$helptxt['latest_packages'] = 'Hier kun je enkele populaire of willekeurige packages of mods vinden, met snelle en simpele installaties.<br /><br />Als dit niet verschijnt kan de pc waarschijnlijk geen verbinding maken met <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.';
$helptxt['latest_themes'] = 'Hier zie je de laatste en meest populaire thema\'s van <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.  Het kan zijn dat het niet verschijnt als je pc geen verbinding kan maken met <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.';

$helptxt['secret_why_blank'] = 'Voor de veiligheid is het antwoord op je vraag (en ook je wachtwoord) ge-encrypt zodat alleen SMF kan bepalen of het antwoord juist is, het kan je dus niet aangeven (en ook niet iemand anders!) wat het antwoord of je wachtwoord is.';
$helptxt['moderator_why_missing'] = 'Daar modereren gedaan wordt op basis van boards, dien je leden moderator te maken via de <a href="javascript:window.open(\'{$scripturl}?action=admin;area=manageboards\'); self.close();">board management interface</a>.';

$helptxt['permissions'] = 'Permissies geven aan welke groepen wel of niet bepaalde rechten in bepaalde boards hebben.<br /><br />Je kunt meerdere boards tegelijk bewerken of bij een specifieke groep kijken door te klikken op \'Bewerken.\'';
$helptxt['permissions_board'] = 'Als een board is ingesteld op \'Globaal,\' houdt dit in dat het board geen speciale permissies heeft.  \'Lokaal\' houdt in dat het board specifieke permissies heeft - apart van de Globale permissies.  Op deze manier kun je boards hebben die meer of minder permissies hebben dan een ander board, zonder dit voor ieder board apart in te moeten stellen.';
$helptxt['permissions_quickgroups'] = 'Deze stellen je in staat om &quot;standaard&quot; permissies te gebruiken - standaard betekent \'niets speciaals\', beperkt betekent \'als een gast\', moderator betekent \'wat een moderator heeft\', en \'onderhoud\' betekent permissies die erg dicht in de buurt liggen van een admin.';
$helptxt['permissions_deny'] = 'Het ontzeggen van permissies kan handig zijn wanneer je voor een zeker lid een permissie wilt wegnemen. Je kunt leden toevoegen aan een ledengroep met een \'ontzeg\'-permissie om hen die permissies te ontzeggen.<br /><br />Gebruik het voorzichtig, een ontzegde permissie blijft ontzegd, ongeacht welke andere ledengroep het lid in zit.';
$helptxt['permissions_postgroups'] = 'Met permissies voor bericht-gerelateerde groepen, kun je permissies aan leden toekennen die een bepaald aantal berichten heeft geplaatst. De bericht-gerelateerde permissies worden <em>toegevoegd</em> aan de permissies van de reguliere ledengroepen.';
$helptxt['membergroup_guests'] = 'De gasten-ledengroep bestaat uit alle gebruikers die niet zijn ingelogd.';
$helptxt['membergroup_regular_members'] = 'De \'reguliere leden\' groep bestaat uit alle leden die ingelogd zijn, maar niet een primaire ledengroep zijn toegewezen.';
$helptxt['membergroup_administrator'] = 'De beheerder kan, per definitie, alles doen en alle boards zien. Er zijn geen permissie-instellingen voor de beheerder.';
$helptxt['membergroup_moderator'] = 'De moderator-ledengroep is een speciale ledengroep. Permissies en instellingen ingesteld voor deze groep, zijn van toepassing op board-moderators maar alleen <em>op de boards die zij modereren</em>. Buiten die boards zijn ze net als elk ander lid.';
$helptxt['membergroups'] = 'In SMF zijn er twee types ledengroepen waar je leden lid van kunnen zijn. Dit zijn:
	<ul>
		<li><b>Reguliere groepen:</b> Een reguliere groep is een groep waar een lid niet automatisch in terecht komt. Om een lid toe te wijzen aan een groep, ga naar zijn profiel en klik &quot;Account Instellingen&quot;. Van hier kun je een willekeurig aantal reguliere groepen toewijzen waarvan een lid deel zal zijn.</li>
		<li><b>Bericht-gerelateerde groepen:</b> In tegenstelling tot de reguliere groepen, kunnen bericht-gerelateerde groepen niet worden toegewezen. In plaats daarvan worden leden automatisch toegewezen aan een bericht-gerelateerde groep bij het bereiken van een minimaal aantal berichten, die benodigd is voor die groep.</li>
	</ul>';

$helptxt['calendar_how_edit'] = 'Je kunt deze events bewerken door op het rode sterretje naast de naam te klikken.';

$helptxt['maintenance_general'] = 'Hier kun je alle tabellen in je database optimaliseren (dit maakt ze kleiner en sneller!), zorg ervoor dat je de nieuwste versies hebt, vind alle fouten die je forum overhoop kunnen halen, herbereken de totalen en leeg alle logs.<br /><br />De laatste twee zouden voorkomen moeten worden, behalve als er iets mis is, maar het kan geen kwaad ze te gebruiken.';
$helptxt['maintenance_backup'] = 'Hier kun je een kopie maken van alle berichten, instellingen, leden en alle andere informatie uit de database in &eacute;&eacute;n groot bestand.<br /><br />Doe dit wekelijks, om altijd een goede backup te hebben.';
$helptxt['maintenance_rot'] = 'Hier kun je <b>volledig</b> en <b>onomkeerbaar</b> oude topics verwijderen.  Het wordt aangeraden eerst een backup te maken.<br /><br />Wees voorzichtig met het gebruik van deze optie.';

$helptxt['avatar_server_stored'] = 'Hier kunnen leden een avatar kiezen die is opgeslagen op je server. Normaal gesproken zijn ze te vinden onder de dezelfde plek als SMF in de avatar directory te vinden.<br />Tip : Als je directories aanmaakt in de map waar de avatars staan, kun je &quot;categorie&euml;n&quot; maken van de avatars.';
$helptxt['avatar_external'] = 'Met deze optie actief kunnen leden een URL opgeven naar hun avatar.  Het nadeel hiervan is dat in sommige gevallen er avatars zullen worden gebruikt die erg groot zijn of niet op je forum kunnen in verband met wat de afbeelding voorstelt.';
$helptxt['avatar_download_external'] = 'Met deze optie wordt de URL die door de gebruiker is opgegeven, benaderd om de avatar te downloaden. Als dat gelukt is, zal de avatar als uploadbare avatar behandeld worden.';
$helptxt['avatar_upload'] = 'Deze optie is vrijwel gelijk aan &quot;Sta leden toe een externe avatar te gebruiken&quot;, alleen heb je nu een betere controle over de avatars, gaat het resizen sneller en hoeven de leden hun avatar niet elders onder te brengen.<br /><br />Het nadeel kan echter zijn dat het een hoop ruimte kan gaan kosten op je server.';
$helptxt['avatar_download_png'] = 'PNG bestanden zijn groter, maar hebben een betere kwaliteit.  Als dit is uitgevinkt, zal er gebruik worden gemaakt van JPEG - vaak leiner, maar ook een slechtere kwaliteit.';

$helptxt['disableHostnameLookup'] = 'Dit deactiveert de mogelijkheid om hostnamen te zoeken, wat op sommige servers erg traag kan zijn.  Let op dat het bannen ook minder effectief zal worden.';

$helptxt['search_weight_frequency'] = 'Gewichtsfactoren worden gebruikt om de relevantie van zoekresultaten te bepalen. Verander de gewichten zo dat ze overeenkomen met wat belangrijk is voor jouw forum. Bijvoorbeeld, een nieuws site zal een relatief hoge waarde hebben voor \'ouderdom van laatste overeenkomende bericht\'. Alle waardes zijn relatief ten opzichte van elkaar en zouden positieve gehele getallen moeten zijn.<br /><br />Deze factor telt het aantal met de zoektermen overeenkomende berichten en deelt ze door het totaal aantal berichten in het topic.';
$helptxt['search_weight_age'] = 'Gewichtsfactoren worden gebruikt om de relevantie van zoekresultaten te bepalen. Verander de gewichten zo dat ze overeenkomen met wat belangrijk is voor jouw forum. Bijvoorbeeld, een nieuws site zal een relatief hoge waarde hebben voor \'ouderdom van laatste overeenkomende bericht\'. Alle waardes zijn relatief ten opzichte van elkaar en zouden positieve gehele getallen moeten zijn.<br /><br />Deze factor bepaalt de ouderdom van het laatste overeenkomende bericht binnen een topic. Hoe recenter het bericht, hoe hoger de score.';
$helptxt['search_weight_length'] = 'Gewichtsfactoren worden gebruikt om de relevantie van zoekresultaten te bepalen. Verander de gewichten zo dat ze overeenkomen met wat belangrijk is voor jouw forum. Bijvoorbeeld, een nieuws site zal een relatief hoge waarde hebben voor \'ouderdom van laatste overeenkomende bericht\'. Alle waardes zijn relatief ten opzichte van elkaar en zouden positieve gehele getallen moeten zijn.<br /><br />Deze factor is gebaseerd op de topicgrootte. Hoe meer berichten in het topic, hoe hoger de score.';
$helptxt['search_weight_subject'] = 'Gewichtsfactoren worden gebruikt om de relevantie van zoekresultaten te bepalen. Verander de gewichten zo dat ze overeenkomen met wat belangrijk is voor jouw forum. Bijvoorbeeld, een nieuws site zal een relatief hoge waarde hebben voor \'ouderdom van laatste overeenkomende bericht\'. Alle waardes zijn relatief ten opzichte van elkaar en zouden positieve gehele getallen moeten zijn.<br /><br />Deze factor kijkt naar of een zoekterm in het onderwerp van het topic voorkomt.';
$helptxt['search_weight_first_message'] = 'Gewichtsfactoren worden gebruikt om de relevantie van zoekresultaten te bepalen. Verander de gewichten zo dat ze overeenkomen met wat belangrijk is voor jouw forum. Bijvoorbeeld, een nieuws site zal een relatief hoge waarde hebben voor \'ouderdom van laatste overeenkomende bericht\'. Alle waardes zijn relatief ten opzichte van elkaar en zouden positieve gehele getallen moeten zijn.<br /><br />Deze factor kijkt naar of een zoekterm in het eerste bericht van het topic voorkomt.';
$helptxt['search_weight_sticky'] = 'Gewichtsfactoren worden gebruikt om de relevantie van zoekresultaten te bepalen. Verander de gewichten zo dat ze overeenkomen met wat belangrijk is voor jouw forum. Bijvoorbeeld, een nieuws site zal een relatief hoge waarde hebben voor \'ouderdom van laatste overeenkomende bericht\'. Alle waardes zijn relatief ten opzichte van elkaar en zouden positieve gehele getallen moeten zijn.<br /><br />Deze factor kijkt naar of een topic sticky is en maakt de relevantheidsscore hoger als dat het geval is.';
$helptxt['search'] = 'Pas hier alle instellingen voor de zoekfunctie aan.';
$helptxt['search_why_use_index'] = 'Een zoekindex kan de zoekprestaties van je forum zeer goed ten gunste komen. Met name wanneer het aantal berichten op je forum groeit, kan zoeken zonder indices lang duren en druk op je database veroorzaken. Als je forum groter dan 50.000 berichten is, is het zeker te overwegen om een zoekindex aan te laten maken.<br /><br />Merk op dat zoekindices vrij veel ruimte innemen. Een fulltext-index is een standaardindex van MySQL. Het is relatief compact (ongeveer dezelfde grootte als de berichtentabel), maar veel woorden worden niet ge&iuml;ndexeerd en het kan sommige zoekopdrachten erg vertragen. De aangepaste indices zijn over het algemeen groter (afhankelijk van je configuratie kan het tot drie maal groter zijn dan de berichtentabel), maar zijn prestaties zijn zeker beter en komen de stabiliteit ook zeker ten goede.';

$helptxt['see_admin_ip'] = 'IP-adressen worden getoond aan beheerders en moderators om het modereren te vergemakkelijken en het eenvoudiger te maken om mensen met slechte bedoelingen te traceren. Bedenk wel dat IP-adressen niet altijd identificerend zijn en dat IP-adressen van mensen van tijd tot tijd kan veranderen. <br /><br />Leden mogen ook hun eigen IP-adres zien.';
$helptxt['see_member_ip'] = 'Je IP-adres is zichtbaar voor jou en de moderators. Bedenk dat deze informatie je niet identificeert en dat de meeste IP-adressen af en toe veranderen.<br /><br />Je kunt het IP-adres van andere leden niet zien en zij kunnen het jouwe niet zien.';

$helptxt['ban_cannot_post'] = 'De \'kan niet posten\' restrictie zet het forum in een alleen-lezen-stand voor de gebande gebruiker. De gebruiker kan niet nieuwe topics maken, reageren op bestaande topics, persoonlijke berichten versturen of stemmen in polls. Wel kan de gebande gebruiker nog steeds persoonlijke berichten en topics lezen.<br /><br />Een waarschuwingsbericht wordt aan de gebruikers getoond die op deze manier zijn geband.';

$helptxt['posts_and_topics'] = '
	<ul>
		<li>
			<b>Berichteninstellingen</b><br />
			Wijzig de instellingen die betrekking hebben op het plaatsen van berichten en de manier waarop de berichten worden weergegeven. Ook kun je hier de spellingscontrole aan zetten.
		</li><li>
			<b>Bulletin Board Code</b><br />
			Stel hier de code in waarmee forumberichten de juiste vormgeving kunnen krijgen. Stel in welke codes wel en welke codes niet gebruikt mogen worden.
		</li><li>
			<b>Gecensureerde woorden</b>
			Om het taalgebruik op het forum in toom te houden, kun je bepaalde woorden censureren. Deze functie stelt je in staat om verboden woorden om te zetten in onschuldige varianten.
		</li><li>
			<b>Topicinstellingen</b>
			Stel alles in met betrekking tot topics. Hoeveel er op een pagina gaan, of sticky topics ingeschakeld zijn, bij welk aantal berichten een topic \'hot\' is, etc.
		</li>
	</ul>';

?>