<edit file>
$languagedir/index.english.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.0.9                                           *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.0.21                                          *
</replace>

<search for>
$forum_copyright = '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by ' . $forum_version . '</a> | 
<a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy; 2006-2009, Simple Machines LLC</a>';
</search for>

<replace>
$forum_copyright = '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by ' . $forum_version . '</a> |
<a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy; 2006-2011, Simple Machines LLC</a>';
</replace>


<edit file>
$boarddir/index.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.0.20                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.0.21                                          *
</replace>

<search for>
$forum_version = 'SMF 1.0.20';
</search for>

<replace>
$forum_version = 'SMF 1.0.21';
</replace>


<edit file>
$sourcedir/ssi_examples.php
</edit file>

<search for>
<?php

require(dirname(__FILE__) . '/SSI.php');
</search for>

<replace>
<?php

/* Define $ssi_guest_access variable just before including SSI.php to handle guest access to your script.
	false: (default) fallback to forum setting
	true: allow guest access to the script regardless
*/
$ssi_guest_access = false;

require(dirname(__FILE__) . '/SSI.php');
</replace>


<edit file>
$boarddir/SSI.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.0.15                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.0.21                                          *
</replace>

<search for>
// Load the current user's permissions....
loadPermissions();

// Load the stuff like the menu bar, etc.
if (isset($ssi_layers))
</search for>

<replace>
// Load the current user's permissions....
loadPermissions();

// Do we allow guests in here?
if (empty($ssi_guest_access) && empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && basename($_SERVER['PHP_SELF']) != 'SSI.php')
{
	require_once($sourcedir . '/Subs-Auth.php');
	KickGuest();
	obExit(null, true);
}

// Load the stuff like the menu bar, etc.
if (isset($ssi_layers))
</replace>

<search for>
// Call a function passed by GET.
if (isset($_GET['ssi_function']) && function_exists('ssi_' . $_GET['ssi_function']))
</search for>

<replace>
// Call a function passed by GET.
if (isset($_GET['ssi_function']) && function_exists('ssi_' . $_GET['ssi_function']) && (!empty($modSettings['allow_guestAccess']) || !$user_info['is_guest']))
</replace>


<edit file>
$sourcedir/QueryString.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.0.17                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.0.21                                          *
</replace>

<search for>
		// Now make absolutely sure it's a number.
		$board = (int) $_REQUEST['board'];
</search for>

<replace>
		// Now make absolutely sure it's a number.
		$board = (int) $_REQUEST['board'];
		$_REQUEST['start'] = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;
</replace>

<search for>
	// There should be a $_REQUEST['start'], some at least.  If you need to default to other than 0, use $_GET['start'].
	if (empty($_REQUEST['start']) || $_REQUEST['start'] < 0)
</search for>

<replace>
	// There should be a $_REQUEST['start'], some at least.  If you need to default to other than 0, use $_GET['start'].
	if (empty($_REQUEST['start']) || $_REQUEST['start'] < 0 || (int) $_REQUEST['start'] > 2147473647)
</replace>


<edit file>
$sourcedir/Subs.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.0.19                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.0.21                                          *
</replace>

<search for>
	// Save whether $start was less than 0 or not.
	$start_invalid = $start < 0;
</search for>

<replace>
	// Save whether $start was less than 0 or not.
	$start = (int) $start;
	$start_invalid = $start < 0;
</replace>