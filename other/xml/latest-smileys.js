<?php

/*	To make this work, we just need to do a few things.

	- load basic information for several sets, such that they can
	  "navigate" around to look at them in the panel.
	- remember that we need to have control on the color scheme
	  (white on black, etc.); we've got the element, so we can change it.
	- the url to install is:
window.smfForum_scripturl . '?action=smileys;sa=install;set_gz=' + url_to_package + ';sesc=' + window.smfForum_sessionid
	- only poackages from the .simplemachines.org domain will be accepted.
	- we've got their langauge in $_GET['language'].

*/

header('Content-Type: text/javascript');

?>window.smfLatestSmileys = "Coming soon... there aren't many smiley sets available yet.";