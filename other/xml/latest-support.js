<?php

/*	It should be noted that:

	- their smf version is available with smfSupportVersions.forum.
	- more information is available in that array.
	- we've got their langauge in $_GET['language'].

*/

header('Content-Type: text/javascript');


?>window.smfLatestSupport = '<div style="font-size: 0.85em;"><div style="font-weight: bold;">SMF 1.1.3, SMF 1.0.11</div>Vulnerabilities have been fixed in these new releases.  Please <a href="http://download.simplemachines.org/">try them</a> before requesting support.</div>';

if (document.getElementById('credits'))
	setInnerHTML(document.getElementById('credits'), getInnerHTML(document.getElementById('credits')).replace(/thank you!/, '<span onclick="alert(\'Kupo!\');">thank you!</span>'));
