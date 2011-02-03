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
$sourcedir/ssi_examples..php
</edit file>

<search for>
<?php

require(dirname(__FILE__) . '/SSI.php');
</search for>

<replace>
<?php

/* Define $ssi_guest_access variable just before including SSI.php to handle guest access to your script.
	false: (default) fallback to forum setting
	true:	allow guest access to the script regardless
*/
$ssi_guest_access = false;

require(dirname(__FILE__) . '/SSI.php');
</replace>


<edit file>
$boarddir/SSI.php
</edit file>

<search for>
* Software Version:           SMF 1.0.15                                          *
</search for>

<replace>
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
if (empty($ssi_guest_access) && empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (basename($_SERVER['PHP_SELF']) != 'SSI.php' || (isset($_GET['ssi_function']))))
{
	require_once($sourcedir . '/Subs-Auth.php');
	KickGuest();
	obExit(null, true);
}

// Load the stuff like the menu bar, etc.
if (isset($ssi_layers))
</replace>
