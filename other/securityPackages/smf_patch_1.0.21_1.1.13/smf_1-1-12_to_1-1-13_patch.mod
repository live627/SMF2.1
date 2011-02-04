
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
$languagedir/index.english.php
</edit file>


<search for>
// Version: 1.1.9; index
</search for>

<replace>
// Version: 1.1.13; index
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
* Software Version:           SMF 1.1.5                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.13                                          *
</replace>

<search for>
		foreach ($_POST['news'] as $i => $news)
		{
			if (trim($news) == '')
				unset($_POST['news'][$i]);
			else
				preparsecode($_POST['news'][$i]);
		}
</search for>

<replace>
		foreach ($_POST['news'] as $i => $news)
		{
			if (trim($news) == '')
				unset($_POST['news'][$i]);
			else
			{
				$_POST['news'][$i] = $func['htmlspecialchars']($_POST['news'][$i], ENT_QUOTES);
				preparsecode($_POST['news'][$i]);
			}
		}
</replace>

<search for>
	foreach (explode("\n", $modSettings['news']) as $id => $line)
		$context['admin_current_news'][$id] = array(
			'id' => $id,
			'unparsed' => $func['htmlspecialchars'](un_preparsecode($line)),
			'parsed' => preg_replace('~<([/]?)form[^>]*?[>]*>~i', '<em class="smalltext">&lt;$1form&gt;</em>', parse_bbc($line)),
</search for>

<replace>
	foreach (explode("\n", $modSettings['news']) as $id => $line)
		$context['admin_current_news'][$id] = array(
			'id' => $id,
			'unparsed' => un_preparsecode($line),
			'parsed' => preg_replace('~<([/]?)form[^>]*?[>]*>~i', '<em class="smalltext">&lt;$1form&gt;</em>', parse_bbc($line)),
		);
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
	true:	allow guest access to the script regardless
*/
$ssi_guest_access = false;

require(dirname(__FILE__) . '/SSI.php');
</replace>


<edit file>
$boarddir/SSI.php
</edit file>

<search for>
* Software Version:           SMF 1.1.7                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.13                                          *
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
