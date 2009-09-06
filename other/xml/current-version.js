<?php

/*	As of 1.0's release, all links to this file will include $_GET['version'], which either:
		- will contain 'CVS'.
		- will be 'SMF 1.1' or higher.
		- will be 'SMF 1.0'.

	If this is not set (most likely) or is lower than 'SMF 1.0' (very unlikely, right now)
	the forum is old!  After some very normal period of time, the script should quite
	possibly start logging the referring URL - this will help us find people using older
	versions, and try to convince them to upgrade.
*/

// Try to make sure this is kept up to date every time it loads.
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT');

header('Content-Type: text/javascript');

list($modified_since) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($modified_since) >= filemtime(__FILE__))
{
	header('HTTP/1.1 304 Not Modified');
	die;
}

// Do as little PHP as poss.
if (isset($_GET['version']) && strpos($_GET['version'], '2.0') !== false)
	$version = 'SMF 2.0 RC2';
else
	$version = 'SMF 1.1.4';

echo 'window.smfVersion = "' . $version . '";';

?>