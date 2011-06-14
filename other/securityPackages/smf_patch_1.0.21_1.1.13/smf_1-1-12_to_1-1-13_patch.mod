<edit file>
$boarddir/index.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.1.12                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.1.13                                          *
</replace>

<search for>
$forum_version = 'SMF 1.1.12';
</search for>

<replace>
$forum_version = 'SMF 1.1.13';
</replace>


<edit file>
$sourcedir/Search.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.1.5                                           *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.1.13                                          *
</replace>

<search for>
					FROM ({$db_prefix}topics AS t, {$db_prefix}" . ($createTemporary ? 'tmp_' : '') . "log_search_topics AS lst)
					WHERE lst.ID_TOPIC = t.ID_TOPIC" . (empty($modSettings['search_max_results']) ? '' : "
					LIMIT " . ($modSettings['search_max_results'] - $_SESSION['search_cache']['num_results'])), __FILE__, __LINE__);
</search for>

<replace>
					FROM ({$db_prefix}topics AS t, {$db_prefix}" . ($createTemporary ? 'tmp_' : '') . "log_search_topics AS lst)
					WHERE " . ($createTemporary ? '' : 'lst.ID_SEARCH = ' . $_SESSION['search_cache']['ID_SEARCH'] . ' AND ') . 'lst.ID_TOPIC = t.ID_TOPIC' . (empty($modSettings['search_max_results']) ? '' : "
					LIMIT " . ($modSettings['search_max_results'] - $_SESSION['search_cache']['num_results'])), __FILE__, __LINE__);
</replace>


<edit file>
$sourcedir/ManageNews.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.1.5                                           *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.1.13                                          *
</replace>

<search for>
			if (trim($news) == '')
				unset($_POST['news'][$i]);
			else
				preparsecode($_POST['news'][$i]);
</search for>

<replace>
			if (trim($news) == '')
				unset($_POST['news'][$i]);
			else
			{
				$_POST['news'][$i] = $func['htmlspecialchars']($_POST['news'][$i], ENT_QUOTES);
				preparsecode($_POST['news'][$i]);
			}
</replace>

<search for>
			'unparsed' => $func['htmlspecialchars'](un_preparsecode($line)),
</search for>

<replace>
			'unparsed' => un_preparsecode($line),
</replace>


<edit file>
$boarddir/ssi_examples.php
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
* Software Version:           SMF 1.1.7                                           *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.1.13                                          *
</replace>

<search for>
// Load the stuff like the menu bar, etc.
if (isset($ssi_layers))
</search for>

<replace>
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
* Software Version:           SMF 1.1.9                                           *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.1.13                                          *
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
* Software Version:           SMF 1.1.11                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.1.13                                          *
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