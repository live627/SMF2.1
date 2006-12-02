<?php

error_reporting(E_ALL);

header('Content-Type: text/javascript');

if (empty($_GET['format']))
	$_GET['format'] = '%B %d, %Y, %I:%M:%S %p';

$latest_news = array(
	array(
		'time' => 1111111111,

		'subject_english' => 'SMF 1.1',
		'message_english' => 'SMF 1.1 has gone gold!  If you are using an older version, please upgrade as soon as possible - many things have been changed and fixed, and mods and packages will expect you to be using 1.1.  If you need any help upgrading custom modifications to the new version, please feel free to ask us at our forum.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=130740.0',
		'author_english' => 'Grudge',
	),
	array(
		'time' => 1156203139,

		'subject_english' => 'SMF 1.1 RC3',
		'message_english' => 'Release Candidate 3 of SMF 1.1 has been released! This is the final update to SMF 1.1 before it goes final - and includes UTF support as well as numerous bug fixes. Please read the annoucement for details - and only upgrade if you are comfortable running software yet to go gold.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=107112.0',
		'author_english' => 'Grudge',
	),
	array(
		'time' => 1156203139,

		'subject_english' => 'SMF 1.0.8',
		'message_english' => 'A security issue has been reported in PHP causing a vulnerability in SMF. A patch has been released to upgrade Simple Machines Forum from 1.0.7 to 1.0.8. You are encouraged to update immediately, using the package manager or otherwise.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=107135.0',
		'author_english' => 'Compuart',
	),
	array(
		'time' => 1143671532,

		'subject_english' => 'SMF 1.0.7 and patch for SMF 1.1 RC2',
		'message_english' => 'A security issue has been discovered in both SMF 1.0 and SMF 1.1. Therefor a patch has been released that will upgrade SMF 1.0.6 to 1.0.7 and update SMF 1.1 RC2. You are encouraged to update immediately, using the package manager or otherwise.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=78841.0',
		'author_english' => 'Compuart',

		'subject_dutch' => 'SMF 1.0.7 en patch voor SMF 1.1 RC2',
		'message_dutch' => 'Een beveiligingsprobleem is gevonden in zowel SMF 1.0 als SMF 1.1. Daarom is een patch uitgebracht die SMF 1.0.6 opwaardeert naar 1.0.7 en SMF 1.1 RC2 bijwerkt. Het is aan te raden om direct SMF up te daten via het pakket beheer of op andere wijze.',
	),
	array(
		'time' => 1138448185,

		'subject_english' => 'SMF 1.0.6',
		'message_english' => 'SMF 1.0.6 has been released.  This release addresses a potential security issue as well as a few minor bugs found since the 1.0.5 release. You are encouraged to update immediately, using the package manager or otherwise. This update does not apply to the 1.1 line!',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=68110.0',
		'author_english' => 'Grudge',

		'subject_finnish' => 'SMF 1.0.6',
		'message_finnish' => 'SMF 1.0.6 on julkaistu. Tämä julkaisu korjaa mahdollisen tietoturva-aukon, sekä joitain pienempiä virheitä jotka ovat löytyneet 1.0.5 version julkaisun jälkeen. Suosittelemme päivittämään välittömästi pakettien hallinnan kautta tai muutoin. Tämä päivitys ei koske 1.1 version käyttäjiä!',

		'subject_dutch' => 'SMF 1.0.6',
		'message_dutch' => 'SMF 1.0.6 is uitgebracht! Deze versie dicht een potentieel beveiligingslek en bevat een paar bugfixes voor de 1.0.5 versie. We vragen je om zeker up te daten via het pakket beheer of onze website. Gebruikers van de 1.1 beta lijn hoeven niet up te daten.',

		'subject_turkish' => 'SMF 1.0.6',
		'message_turkish' => 'SMF nin 1.0.6 sürümü çikti.  Bu sürümde 1.0.5 de bulunan bazi küçük hatalar ve potansiyel bir güvenlik açigi düzeltilmis bulunuyor. Bu güncellemeyi paket yöneticinizi veya diger metotlari kullanarak hemen uygulamaniz önerilir.',

		'subject_german' => 'SMF 1.0.6',
		'message_german' => 'SMF 1.0.6 wurde ver&ouml;ffentlicht. Diese Version behebt ein potentielles Sicherheitsrisiko und mehrere kleine Fehler, die nach Version 1.0.5 gefunden worden sind. Sie k&ouml;nnen Ihr Forum sofort mit Hilfe des Paket-Managers aktualisieren oder das Update-Paket herunterladen. Diese Version darf nicht mit SMF 1.1 benutzt werden!', 
	),
	array(
		'time' => 1138108185,

		'subject_english' => 'Bug in Firefox 1.5',
		'message_english' => 'There is a bug in Firefox 1.5 which can cause server issues for forums running SMF 1.1 (RC1/RC2). There is a simple fix which can be downloaded from the Simple Machines forum.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=66862.0',
		'author_english' => 'Grudge',
	),

	array(
		'time' => 1136059100,

		'subject_english' => 'SMF 1.1 RC2',
		'message_english' => 'The second (and final) Release Candidate of SMF 1.1 has been released! Please read the announcement for details - and please update only if you are certain you are comfortable with software that hasn\'t gone gold yet. There is no package manager style update for this version.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=62731.0',
		'author_english' => 'Grudge',

		'subject_dutch' => 'SMF 1.1 RC2',
		'message_dutch' => 'De tweede (en laatste) Release Candidate van SMF 1.1 is nu beschikbaar! Als je meer wil weten kan je terecht in de aankondiging. Dit pakket kan mogelijk nog enkele fouten bevatten, upgrade dus enkel wanneer je ervaring hebt met dit soort software. Er is geen Pakketbeheer update beschikbaar voor deze versie.',

		'subject_french' => 'SMF 1.1 RC2',
		'message_french' => 'Le deuxième (et finale) candidat de dégagement de SMF 1.1 a été libéré ! Veuillez lire l\'annonce pour des détails - et la mettez à jour svp seulement si vous êtes certain que vous soyez confortable avec le logiciel qui n\'a pas été fait à l\'or encore. Il n\'y a aucune mise à jour de \'Package Manager\' pour cette version.',

		'subject_spanish' => 'SMF 1.1 RC2',
		'message_spanish' => 'El segundo (y el final) Lanzamiento Release Candidate de SMF 1.1 ha sido publicado! Porfavor lea el aviso para m&aacute;s detalles - y porfavor actualiza s&oacute;lo si est&aacute; conforme con lo que este sistema ha realizado anteriormente. No hay actualizaci&oacute;n para esta versi&oacute;n desde el manejador de paquetes.',

		'subject_turkish' => 'SMF 1.1 RC2',
		'message_turkish' => 'SMF 1.1 için son RC sürüm artik indirilebilir! Lütfen daha detayli bilgi için duyuruyu okuyunuz - ve gelistirilme asamasindaki yazilimlari kullanmakta tereddütleriniz varsa güncellemeyi yapmayiniz. Bu sürüm için paket yöneticisi üzerinden güncellemeler mümkün degildir.',
	),
/*
	array(
		'time' => 1127227317,

		'subject_english' => 'SMF 1.1 RC1',
		'message_english' => 'SMF releases the first Release Candidate of SMF 1.1!  Please read the announcement for details - and only update if you are certain you are comfortable with software that hasn\'t gone gold yet.  There is no package manager style update for this version.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=50176.0',
		'author_english' => 'Compuart',

		'subject_dutch' => 'SMF 1.1 RC1',
		'message_dutch' => 'SMF brengt de eerste Release Candidate van SMF 1.1 uit!  Lees de aankondiging voor alle details - en voer alleen een update uit als je je vertrouwd voelt met software waar nog niet de uiteindelijke versie van uit is. Er is voor deze versie geen mogelijkheid om via de package manager te upgraden.',
		'author_dutch' => 'Compuart',

		'subject_german' => 'SMF 1.1 RC1',
		'message_german' => 'Das erste Release Candidate von SMF 1.1 steht zum Download bereit! Bitte lesen Sie das Ank&uuml;ndigungsthema f&uuml;r weitere Informationen und aktualisieren Sie Ihr Forum nur, wenn Sie sich mit Beta Software gen&uuml;gend auskennen! F&uuml;r diese Version gibt es kein Paket-Manager Update.',
		'author_german' => 'Dani&euml;l D.',
	),

	array(
		'time' => 1120005510,

		'subject_english' => 'SMF 1.1 Beta 3 Public',
		'message_english' => 'The first public beta of SMF 1.1 has been released!  Please read the announcement for details - and only update if you are certain you are comfortable with beta software.  There is no package manager style update for this version.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=40085.0',
		'author_english' => '[Unknown]',

		'subject_finnish' => 'SMF 1.1 Beta 3 Public',
		'message_finnish' => 'Ensimmäinen julkinen beta SMF 1.1:stä on julkaistu! Ole hyvä ja lue tiedotteesta tarkemmin - ja päivitä vain jos olet varma että haluat käyttää beta vaiheessa olevaa ohjelmistoa.  Tähän versioon ei voi päivittää pakettien hallinnan kautta.',

		'subject_french' => 'SMF 1.1 Beta 3 &Eacute;dition Publique',
		'message_french' => 'La premi&egrave;re version beta publique de SMF 1.1 est sortie&nbsp;!  Veuillez lire le sujet d\'annonces pour plus de d&eacute;tails - et veuillez ne mettre &agrave; jour votre forum que si vous &ecirc;tes confortable avec les logiciels en version de test.  Il n\'y a aucune mise &agrave; jour via le Gestionnaire de paquets possible pour cette version.',

		'subject_german' => 'SMF 1.1 Beta 3 Public',
		'message_german' => 'Die erste &ouml;ffentliche Beta von SMF 1.1 steht zum Download bereit! Bitte lesen Sie das Ank&uuml;ndigungsthema f&uuml;r weitere Informationen und aktualisieren Sie Ihr Forum nur, wenn Sie sich mit Beta Software gen&uuml;gend auskennen! F&uuml;r diese Version gibt es kein Paket-Manager Update.',
	),

	array(
		'time' => 1119285674,

		'subject_english' => 'SMF 1.0.5',
		'message_english' => 'SMF 1.0.5 has now been released.  This release addresses a security issue and you are encouraged to update immediately, using the package manager or otherwise.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=39395.0',
		'author_english' => 'Compuart',

		'subject_german' => 'SMF 1.0.5',
		'message_german' => 'SMF 1.0.5 wurde ver&ouml;ffentlicht! Diese Version behebt ein Sicherheitsproblem und sollte SOFORT &uuml;ber den Paket-Manager oder das FTP Programm installiert werden!',
	),

	array(
		'time' => 1118089337,

		'subject_english' => 'SMF 1.0.4',
		'message_english' => 'SMF 1.0.4 is now available, and we strongly advise you to update to the latest version.  This new version fixes a few bugs found since the release of 1.0.3, and addresses a problem with parsing nested BBC tags. The update can be applied simply by clicking on the Package Manager, or by visiting the Simple Machines site.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=37856.0',
		'author_english' => 'Grudge',

		'subject_dutch' => 'SMF 1.0.4',
		'message_dutch' => 'SMF 1.0.4 is nu beschikbaar en we adviseren je dringend om naar de laatste versie te updaten. Deze nieuwe versie lost een aantal problemen op die gevonden zijn sinds het uitbrengen van 1.0.3, waaronder het omzetten van bulletin board code tags. De update kan simpelweg worden uitgevoerd door op de Package Manager te klikken of de site van Simple Machines te bezoeken.',

		'subject_german' => 'SMF 1.0.4',
		'message_german' => 'SMF 1.0.4 ist verf&uuml;gbar und sollte umgehend von Ihnen installiert werden! Diese Version behebt ein paar Fehler, die in 1.0.3 aufgetaucht sind und korrigiert Probleme mit verschachtelten BBC Tags. Das Update kann einfach im Paket-Manager installiert werden oder Sie besuchen die Simple Machines Seite und laden es dort herunter.',

		'subject_finnish' => 'SMF 1.0.4',
		'message_finnish' => 'SMF 1.0.4 on saatavilla, ja on eritt&auml;in suositeltavaa ett&auml; p&auml;ivit&auml;t t&auml;h&auml;n uusimpaan versioon. T&auml;m&auml; versio sis&auml;lt&auml;&auml; korjaukset muutamiin bugeihin jotka on l&ouml;ytyneet 1.0.3 version julkaisun j&auml;lkeen, sek&auml; korjaa ongelman joka liittyy sis&auml;kk&auml;isten BBC tagien k&auml;sittelyyn. P&auml;ivityksen voit asentaa helpoiten Pakettien hallinnan kautta, tai vierailemalla Simple Machines sivustolla.',
	),

	array(
		'time' => 1115747316,

		'subject_english' => 'SMF 1.1 Beta 2',
		'message_english' => 'Many changes and fixes have gone into SMF 1.1 Beta 2 since the Beta 1 release.  If you are are using Beta 1, we strongly encourage you to update as soon as you can.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=35539.0',
		'author_english' => 'David',

		'subject_german' => 'SMF 1.1 Beta 2',
		'message_german' => 'Viele &Auml;nderungen und Fixes sind in der Beta 2 von SMF 1.1 gemacht worden. Sollten Sie Beta 1 benutzen, empfehlen wir dringend ein Update sobald es Ihnen m&ouml;glich ist.',
	),

	array(
		'time' => 1112071534,

		'subject_english' => 'SMF 1.0.3',
		'message_english' => 'More bugfixes are now available in SMF 1.0.3.  While this doesn\'t contain any known critical security updates, we still strongly recommend you update - with the package manager or otherwise!  If you\'re a Charter Member, we also have SMF 1.1 Beta 1 available for testing!',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=31337.0',
		'author_english' => 'Owdy',

		'subject_german' => 'SMF 1.0.3',
		'message_german' => 'In dieser Version wurden weitere Fehler beseitigt. Obwohl es keine Sicherheitsfehler sind, sollten Sie auf diese Version aktualisieren - mit Hilfe des Paket-Managers oder des Update-/Upgrade-Paketes. Sie sind Charter Member? Dann k&ouml;nnen Sie ab sofort die SMF 1.1 Beta 1 testen!',

		'subject_spanish' => 'SMF 1.0.3',
		'message_spanish' => 'Más seguridad en SMF 1.0.3. Esto no contiene una actualización crítica, pero seguimos recomendandole actualizar, con el manejador de paquetes ó de cualquier forma. Si eres un Charter Member, ya puedes probar el SMF 1.1 Beta 1. !',

		'subject_french' => 'SMF 1.0.3',
		'message_french' => 'De nouveauz correctifs sont maintenant disponible dans SMF 1.0.2.  M&ecirc;me si cette nouvelle version ne corrige aucune faille de s&eacute;curit&eacute;majeure, nous vous recommendons tout de m&ecirc;me fortement de mettre &agrave; jour votre forum -- par l\'interm&eacute;diaire du Gestionnaire de paquets ou autrement!  Si vous &ecirc;tes un Membre Privil&egrave;ge, nous tenons &agrave; vous informer de la mise &agrave; disposition de SMF 1.1 Beta 1 pour tester.',

		'subject_finnish' => 'SMF 1.0.3',
		'message_finnish' => 'Joitakin uusia korjauksia saatavilla versiossa SMF 1.0.3.  Vaikka mitään tiedossa olevia tietoturva-aukkoja ei olekaan, suosittelemme silti päivittämään tähän uusimpaan versioon - pakettien hallinnasta tai muutoin! Charter Jäsenille on saatavilla myös SMF 1.1 Beta 1 testausta varten!',
	),
	array(
		'time' => 1108368000,

		'subject_english' => 'SMF 1.0.2',
		'message_english' => 'A few more minor problems have been found, most of which are server specific issues - but one issue in the package manager.  An upgrade is highly recommended.  A package-style update is available from your package manager, and the new files are available on the download page.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=27828.0',
		'author_english' => 'Meriadoc',

		'subject_german' => 'SMF 1.0.2',
 		'message_german' => 'Ein paar kleine Fehler wurden in SMF gefunden, die meisten jedoch nur Server betreffend und einer im Paket-Manager. Ein Update wird dringend empfohlen! Im Paket-Manager oder auf der Downloadseite von Simplemachines finden Sie das neue Update..',

		'subject_finnish' => 'SMF 1.0.2',

		'subject_french' => 'SMF 1.0.2',
	),

	array(
		'time' => 1105522217,

		'subject_english' => 'SMF 1.0.1',
		'message_english' => 'As was inevitable, a few small mistakes have been found in 1.0.  A package-style update is available from your package manager, and the new files are available on the download page.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=23874.0',
		'author_english' => 'Bostasp',

		'subject_german' => 'SMF 1.0.1',
		'message_german' => 'Leider war es unvermeidbar, dass in SMF 1.0 ein paar Fehler gefunden worden sind. Ein Paket-Update finden Sie im Paket Manager oder auf der Downloadseite von SMF.',

		'subject_finnish' => 'SMF 1.0.1',
		'message_finnish' => 'Kuten odotettavissa oli, muutamia pieniä virheitä on löydetty versiosta 1.0. Pakettien hallinnassa on päivityspaketti odottamassa, sekä uudet tiedostot ovat saatavissa myös ladattavat alueella.',

		'subject_french' => 'SMF 1.0.1',
		'message_french' => 'C\'&eacute;tait in&eacute;vitable, quelques erreurs ont &eacute;t&eacute; d&eacute;couvertes dans la version 1.0.  Un mise &agrave; jour style paquet est disponible &agrave; partir de votre Gestionnaire de paquets, et les nouveaux fichiers sont disponibles sur la page de t&eacute;l&eacute;chargements.',
	),

	array(
		'time' => 1104369919,

		'subject_english' => 'SMF 1.0 Final Released',
		'message_english' => 'SMF 1.0 has finally gone gold.  If you are using an older version, please upgrade as soon as possible - many things have been changed and fixed, and mods and packages will expect you to be using 1.0.  If you need any help upgrading custom modifications to the new version, please feel free to ask us at our forum.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=22607.0',
		'author_english' => '[Unknown]',

		'subject_german' => 'SMF 1.0 Final verf&uuml;gbar',
		'message_german' => 'SMF 1.0 wurde endlich ver&ouml;ffentlicht! Wenn Sie eine &auml;ltere Version nutzen sollten, aktualisieren Sie diese bitte sobald wie m&ouml;glich. Viele Dinge wurden ge&auml;ndert und viele Fehler beseitigt - Modifikationen und Pakete sollten in der n&auml;chsten Zeit angepasst werden. Wenn Sie Hilfe bei der Aktualisierung von Modifikationen ben&ouml;tigen sollten, z&ouml;gern Sie nicht in unserem Forum zu fragen.',

		'subject_french' => 'Sortie de SMF 1.0 Version Finale',
		'message_french' => 'SMF 1.0 est maintenant en version finale.  Si vous utilisez une ancienne version de SMF, veuillez le mettre &agrave; jour le plus t&ocirc;t possible - plusieurs choses ont &eacute;t&eacute; chang&eacute;es et corrig&eacute;es, et les mods et paquets fonctionneront d&eacute;sormais avec la version 1.0.  Si vous avez besoin d\'une quelconque aide pour mettre &agrave; jour vos modifications pour la nouvelle version, n\'h&eacute;sitez pas &agrave; demander de l\'aide sur notre forum.',

		'subject_finnish' => 'SMF 1.0 Final julkaistu',
		'message_finnish' => 'SMF 1.0 on vihdoin saatavilla. Jos k&auml;yt&auml;t vanhempaa versiota, p&auml;ivit&auml; mahdollisimman nopeasti - koska moni asia on muuttettu ja korjattu, sek&auml; kaikki muokkaukset ja paketit tulevat edellytt&auml;m&auml;&auml;n ett&auml; k&auml;yt&auml;t  1.0 versiota. Jos tarvitset apua omien muokkaustesi p&auml;ivitt&auml;misess&auml;, pyyd&auml; ihmeess&auml; apua foorumiltamme.',

		'subject_portuguese' => 'Lan&ccedil;amento do SMF 1.0 Final',
		'message_portuguese' => 'O SMF chegou finalmente &agrave; sua vers&atilde;o final.  Se est&aacute; a usar uma vers&atilde;o mais antiga, por favor actualize o seu f&oacute;rum o mais rapidamente poss&iacute;vel - muitos erros foram corrigidos, algumas fun&ccedil;&otilde;es foram alteradas, e os MODs e Pacotes dispon&iacute;veis s&oacute; funcionar&atilde;o nesta vers&atilde;o final. Se necessitar de alguma ajuda para fazer a actualiza&ccedil;&atilde;o do seu f&oacute;rum para a nova vers&atilde;o, esteja &agrave; vontade para pedir ajuda no nosso f&oacute;rum.',

		'subject_chinese-traditional' => base64_decode('U01GIDEuMCClv6ahqqmlu8TApVg='),
		'message_chinese-traditional' => base64_decode('U01GIDEuMCCkd7hnpb+moaq6tKOo0aRVuPwuIKZwqkexeqXOqrqsT8LCqrqqqaW7LCC90LrJp9an87dzIC0gq9ymaLDdw0Skd7hnuNGoTSwgpX6xvq5NpfO3fLCys12xeqq6qqmlu6xPIDEuMC4gpnCqR7vdrW7AsKajp/O3c7F6qrq9177Cqc6lfrG+LCC90KjsqXik6L3XvsK0TahEqPOnVS4='),
	),

	array(
		'time' => 1103674952,

		'subject_english' => 'A PHP Security Hole!',
		'message_english' => 'A new security hole has been found in PHP (the language SMF is written in.)  Please ask your host to upgrade to PHP 4.3.10 or 5.0.3 as soon as possible.  In the mean time, you can try applying the patch available in the package manager.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=22008.0',
		'author_english' =>  'Peter Duggan',

 		'subject_german' => 'Eine PHP Sicherheitsl&uuml;cke!',
		'message_german' => 'Eine neue Sicherheitsl&uuml;cke wurde in PHP gefunden (Programmiersprache von SMF). Fragen Sie bei Ihrem Host an, ob er so bald wie m&ouml;glich auf PHP 4.3.10 oder 5.0.3 aktualisiert. Sie k&ouml;nnen bis dahin auch einen Patch installieren, den Sie im Paket-Manager finden.',
	),

	array(
		'time' => 1099987200,

		'subject_english' => 'SMF 1.0 (preview) for Charter Members!',
		'message_english' => 'A preview of the final release, 1.0, is now available to all our Charter Members.  Many things have been resolved, and stability has increased.  Please upgrade as soon as possible, if you can.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=19461.0',
		'author_english' => '[Unknown]',

		'subject_french' => 'SMF 1.0 (pr&eacute;-version) pour les Charter Members!',
		'message_french' => 'Une pr&eacute;-version de la version finale, 1.0, est maintenant disponible pour tous les Charter Members.  Plusieurs choses ont &eacute;t&eacute; corrig&eacute;es, et la stabilit&eacute; a &eacute;t&eacute; accrue.  Veuillez mettre &agrave; jour votre forum le plus rapidement possible, si vous le pouvez.',

		'subject_german' => 'SMF 1.0 (Vorschau) f&uuml;r Charter Members!',
		'message_german' => 'Eine Vorschau der finalen Version 1.0 ist jetzt f&uuml;r die Charter Members verf&uuml;gbar. Viele Fehler wurden beseitigt und die Stabilit&auml;t verbessert. Bitte aktualisieren Sie Ihr Forum m&ouml;glichst bald.',

		'subject_spanish' => 'SMF 1.0 (preview) para Miembros Charter',
		'message_spanish' => 'Una vista preliminar de la versi&oacute;n final, 1.0, est&aacute; ahora disponible a todos nuestros Miembros Charter. Muchas cosas han sido resueltas, y la estabilidad fue aumentada. Por favor actualice lo antes posible, si puede.',

		'subject_finnish' => 'SMF 1.0 (preview) Charter j&auml;senille!',
		'message_finnish' => 'Esiversio lopullisesta julkaisusta, 1.0:sta, on nyt saatavilla kaikille Charter j&auml;senille. Monta asiaa on korjattu, sek&auml; vakautta on lis&auml;tty.  Ole hyv&auml; ja p&auml;ivit&auml; mahdollisimman nopeasti.',

		'subject_portuguese' => 'Lan&ccedil;amento do SMF 1.0 (vers&atilde;o pr&eacute;via) para Membros Charter!',
		'message_portuguese' => 'Est&aacute; dispon&iacute;nvel uma vers&atilde;o pr&eacute;via do SMF 1.0 final para os Membros Charter. Muitos erros foram resolvidos, e a estabilidade foi melhorada. Por favor actualize o seu f&oacute;rum o mais rapidamente poss&iacute;vel.',

		'subject_chinese-traditional' => base64_decode('U01GIDEuMCAouXfE/aqppbspILW5IENoYXJ0ZXIgTWVtYmVycyE='),
		'message_chinese-traditional' => base64_decode('pb+moaqppbsgMS4wIKq6uXfE/aqppbukd7SjqNG1uSBDaGFydGVyIE1lbWJlcnMuIKvcpmiw3cNEpHe4Z7jRqE0sIKRdpPG4+8OtqXcuIL3Qusmn1qfzt3Oo7LPMt3Oquqqppbsu'),
	),

	array(
		'time' => 1097471374,

		'subject_english' => 'Last Release Candidate',
		'message_english' => 'The final release candidate is now out.  Quite a number of bugs have been fixed in this release, so for the most stable experience please upgrade as soon as you are able.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=17961.0',
		'author_english' => '[Unknown]',

		'subject_french' => 'Derni&egrave;re version candidate',
		'message_french' => 'La derni&egrave;re version candidate est maintenant disponible. Beaucoup de bogues ont &eacute;t&eacute; corrig&eacute;s dans cette version, donc pour profiter de l\'exp&eacute;rience la plus stable possible, songez &agrave; la mise &agrave; jour le plus t&ocirc;t possible.',

		'subject_german' => 'Letztes Release Candidate',
		'message_german' => 'Das letzte Release Candidate ist erschienen. Eine gro&szlig;e Anzahl an Bugs wurde in dieser Version behoben. F&uuml;r die stabilste Version von SMF aktualisieren Sie bitte Ihr Forum so schnell wie m&ouml;glich.',

		'subject_spanish' => 'Ultima Versi&oacute;n Candidato',
		'message_spanish' => 'La &uacute;ltima versi&oacute;n candidato ya sido liberada. Unos cuantos problemas fueron solucionados en esta versi&oacute;n, asi que para la experiencia m&aacute;s estable, por favor haga la actualizaci&oacute;n tan pronto le sea posible.',
	),

	array(
		'time' => 1096182000,

		'subject_english' => 'SMF 1.0 RC2 for Charter Members',
		'message_english' => 'SMF 1.0 RC2 has been released to Charter Members.  If you are a Charter Member, please upgrade as soon as possible, or ask us to do so.  Many issues and bugs were fixed in this release.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=17132.0',
		'author_english' => '[Unknown]',

		'subject_german' => 'SMF 1.0 RC2 f&uuml;r Charter Members',
		'message_german' => 'SMF 1.0 RC2 steht den Charter Members zur Verf&uuml;gung. Sollten Sie ein Charter Member sein, aktualisieren Sie Ihr Forum so bald wie m&ouml;glich oder fragen Sie uns es zu tun. Viele Fehler und Probleme wurden in dieser Version behoben.',

		'subject_spanish' => 'SMF 1.0 RC2 para Miembros Charter',
		'message_spanish' => 'SMF 1.0 RC2 ha sido liberada a los Miembros Charter.  Si es un Miembro Charter, por favor actualice lo antes posible, o p&iacute;danos que lo hagamos por usted.  Muchos puntos y problemas han sido solucionados en esta versi&oacute;n.',

		'subject_dutch' => 'SMF 1.0 RC2 voor Charter Members',
		'message_dutch' => 'SMF 1.0 RC2 is uitgebracht voor Charter Members.  Ale je een Charter Member bent, upgrade zo snel mogelijk of vraag ons om het te doen.  Veel problemen en bugs zijn in deze versie gefixt.',
	),

	array(
		'time' => 1094981935,

		'subject_english' => 'Announcing Our First Contest',
		'message_english' => 'Simple Machines is holding a Theme Contest.  This means new themes, contest prizes, and more.  Check it out.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=16539.0',
		'author_english' => '[Unknown]',

		'subject_german' => 'Erster Wettbewerb angek&uuml;ndigt',
		'message_german' => 'Simple Machines hat einen Theme Wettbewerb angek&uuml;ndigt. Das hei&szlig;t neue Themes, Preise und mehr. Probieren Sie es aus!',

		'subject_spanish' => 'Anunciando Nuestro Primer Concurso',
		'message_spanish' => 'Simple Machines est&aacute; creando un concurso de Temas.  TEsto significa nuevos temas, premios del concurso, y m&aacute;s.  Compru&eacute;belo.',
	),

	array(
		'time' => 1092131875,

		'subject_english' => 'SMF 1.0 RC1 Publicly Available',
		'message_english' => 'Release Candidate 1 is now available to the public!  This new release includes a lot of stability improvements, and is very recommended over any previous to it.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=14930.0',
		'author_english' => '[Unknown]',

		'subject_german' => 'SMF 1.0 RC1 Publicly verf&uuml;gbar',
		'message_german' => 'Release Candidate 1 ist ab sofort f&uuml;r die &Ouml;ffentlichkeit verf&uuml;gbar! Diese neue Version enth&auml;lt eine Menge neuer Stabilit&auml;ts Features und wird gegen&uuml;ber &auml;lteren Versionen empfohlen.',

		'subject_spanish' => 'SMF 1.0 RC1 P&uacute;blicamente Disponible',
		'message_spanish' => '¡Listo Rc1 est&aacute; ahora disponible al p&uacute;blico!  Esta nueva versi&oacute;n incluye  mejoras de estabilidad, y ha terminado muy recomendado cualquier anterior a &eacute;l.',
	),

	array(
		'time' => 1091905322,

		'subject_english' => 'SMF 1.0 RC2',
		'message_english' => 'Release Candidate 1 has been released to Charter Members.  It will soon be released to everyone else, so hold on to your hats.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=14808.0',
		'author_english' => 'Compuart',

		'subject_german' => 'SMF 1.0 RC2',
		'message_german' => 'Release Candidate 1 steht den Charter Members zur Verf&uuml;gung. Die Version wird in kurzer Zeit allen Benutzer zur Verf&uuml;gung stehen, also seien Sie gespannt!',

		'subject_spanish' => 'SMF 1.0 RC2',
		'message_spanish' => 'Candidato 1 se ha soltado para Charter Members .  Se soltar&aacute; pronto a todos los dem&aacute;s, as&iacute; que af&eacute;rrese a sus sombreros.',
	),

	array(
		'time' => 1087058126,

		'subject_english' => 'SMF 1.0 Beta 6',
		'message_english' => 'Beta 6 is now available to Charter Members.  This update includes a lot of functionality changes, and is the last beta before the release candidate stage.  This release will only be for Charter Members.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=12393.0',
		'author_english' => 'Grudge',

		'subject_german' => 'SMF 1.0 Beta 6',
		'message_german' => 'Beta 6 steht den Charter Members zur Verf&uuml;gung. Diese Version wurde f&uuml;r bessere Funktionalit&auml;t optimiert und ist die letzte vor dem Release Candidate. Beta 6 wird nur den Charter Members zur Verg&uuml;gung stehen.',

		'subject_spanish' => 'SMF 1.0 Beta 6',
		'message_spanish' => 'Beta 6 est&aacute; ahora disponible para Charter Members.  Esta actualizaci&oacute;n incluye m&aacute;s funcionalidad, y es la &uacute;ltima beta antes de la fase de candidato de descargo.  Esta descarga s&oacute;lo ser&aacute; para los Miembros de la Carta constitucional.',
	),

	array(
		'time' => 1082954911,

		'subject_english' => 'SMF 1.0 Beta 5 Public!',
		'message_english' => 'Beta 5 Public is now available.  This is an important update, and includes a lot of functionality changes.  Please upgrade as soon as possible.',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=10131.0',
		'author_english' => '[Unknown]',

		'subject_german' => 'SMF 1.0 Beta 5 Public!',
		'message_german' => 'Beta 5 Public ist nun verf&uuml;gbar. Dies ist ein wichtiges Update und enth&auml;lt eine Vielzahl an Ver&auml;nderungen und neuen Funktionen. Bitte aktualisieren Sie Ihre Version so bald wie m&ouml;glich!',

		'subject_spanish' => 'SMF 1.0 Beta 5 P&uacute;blica!',
		'message_spanish' => 'Beta 5 P&uacute;blico est&aacute; ahora disponible.  &eacute;sta es una actualizaci&oacute;n importante, y incluye muchos cambios de funcionalidad.  Por favor actualice lo m&aacute;s pronto posible.',
	),

	array(
		'time' => 1082579416,

		'subject_english' => 'SMF 1.0 Beta 5 for Charter Members',
		'message_english' => 'Beta 5 is now ready for out Charter Members.  It includes a lot of changes and new features, so be sure to upgrade as soon as possible!',
		'href_english' => 'http://www.simplemachines.org/community/index.php?topic=9850.0',
		'author_english' => '[Unknown]',

		'subject_german' => 'SMF 1.0 Beta 5 f&uuml;r Charter Members',
		'message_german' => 'Beta 5 steht den Charter Members zur Verf&uuml;gung. Sie enth&auml;lt eine Vielzahl an Ver&auml;nderungen und neuen Funktionen. Bitte aktualisieren Sie Ihre Version so bald wie m&ouml;glich!',

		'subject_spanish' => 'SMF 1.0 Beta 5 para Charter Members',
		'message_spanish' => 'Beta 5 est&aacute; ahora lista para fuera los Miembros de la Carta constitucional.  ¡Incluye muchos cambios y los nuevos rasgos, as&iacute; que est&eacute; seguro actualizar lo m&aacute;s pronto posible!',
	),*/
);

echo '
window.smfAnnouncements = [';

for ($i = 0, $n = count($latest_news); $i < $n; $i++)
{
	echo '
	{
		subject: \'', addslashes(isset($latest_news[$i]['subject_' . @$_GET['language']]) ? $latest_news[$i]['subject_' . @$_GET['language']] : $latest_news[$i]['subject_english']), '\',
		href: \'', addslashes(isset($latest_news[$i]['href_' . @$_GET['language']]) ? $latest_news[$i]['href_' . @$_GET['language']] : $latest_news[$i]['href_english']), '\',
		time: \'', addslashes(strftime(@$_GET['format'], $latest_news[$i]['time'])), '\',
		author: \'', addslashes(isset($latest_news[$i]['author_' . @$_GET['language']]) ? $latest_news[$i]['author_' . @$_GET['language']] : $latest_news[$i]['author_english']), '\',
		message: \'', addslashes(isset($latest_news[$i]['message_' . @$_GET['language']]) ? $latest_news[$i]['message_' . @$_GET['language']] : $latest_news[$i]['message_english']), '\'
	}';

	if ($i != $n - 1)
		echo ',';
}

echo '
];';

/*
	Area for putting possible future update information, you can set the following variables.

		window.smfUpdateNotice: Override the default window notice.
		window.smfUpdatePackage: Name of the update package to use.
		window.smfUpdateTitle: Override default title display in window.
		window.smfUpdateCritical: If set will make the notice displayed red (or critical for the theme.)

	Note: In the smfUpdateNotice message, an element should exist with the id update-link.

	Example:

if (window.smfVersion < "SMF 1.1" && window.smfVersion != "SMF 1.0.4")
{
	window.smfUpdateNotice = 'Please <a href="" id="update-link">update now</a>';
	window.smfUpdatePackage = "http://mods.simplemachines.org/download/" + window.smfVersion.replace(/[\. ]/g, "_") + "_to_1-0-4.tar.gz";
	window.smfUpdateTitle = "Update Title!";
	window.smfUpdateCritical = true;
}

*/

?>

if (window.smfVersion < "SMF 1.1")
{
	window.smfUpdateNotice = 'SMF 1.1 Final has now been released. To take advantage of the improvements available in SMF 1.1 we recommend upgrading as soon as is practical.';
	window.smfUpdateCritical = false;
}

if (document.getElementById("yourVersion"))
{
	var yourVersion = getInnerHTML(document.getElementById("yourVersion"));
	if (yourVersion == "SMF 1.0.4")
		window.smfUpdatePackage = "http://mods.simplemachines.org/downloads/smf_1-0-5_package.tar.gz";
	else if (yourVersion == "SMF 1.0.5" || yourVersion == "SMF 1.0.6")
	{
		window.smfUpdatePackage = "http://mods.simplemachines.org/downloads/smf_patch_1.0.7_1.1-RC2-1.tar.gz";
		window.smfUpdateCritical = false;
	}
	else if (yourVersion == "SMF 1.0.7")
	{
		window.smfUpdatePackage = "http://mods.simplemachines.org/downloads/smf_1-0-8_package.tar.gz";
	}
}

if (document.getElementById('credits'))
	setInnerHTML(document.getElementById('credits'), getInnerHTML(document.getElementById('credits')).replace(/anyone we may have missed/, '<span title="And you thought you had escaped the credits, hadn\'t you, Zef Hemel?">anyone we may have missed</span>'));