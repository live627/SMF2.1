<edit file>
$boarddir/index.php
</edit file>

<search for>
* Software Version:           SMF 1.0.18                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
$forum_version = 'SMF 1.0.18';
</search for>

<replace>
$forum_version = 'SMF 1.0.19';
</replace>



<edit file>
$sourcedir/BoardIndex.php
</edit file>

<search for>
* Software Version:           SMF 1.0.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
				'can_collapse' => isset($row_board['canCollapse']) && $row_board['canCollapse'] == 1,
				'collapse_href' => isset($row_board['canCollapse']) ? $scripturl . '?action=collapse;c=' . $row_board['ID_CAT'] . ';sa=' . ($row_board['isCollapsed'] > 0 ? 'expand' : 'collapse;') . '#' . $row_board['ID_CAT'] : '',
</search for>

<replace>
				'can_collapse' => isset($row_board['canCollapse']) && $row_board['canCollapse'] == 1,
				'collapse_href' => isset($row_board['canCollapse']) ? $scripturl . '?action=collapse;c=' . $row_board['ID_CAT'] . ';sa=' . ($row_board['isCollapsed'] > 0 ? 'expand' : 'collapse') . ';sesc=' . $context['session_id'] . '#' . $row_board['ID_CAT'] : '',
</replace>



<edit file>
$sourcedir/Display.php
</edit file>

<search for>
* Software Version:           SMF 1.0.17                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
function Download()
{
	global $txt, $modSettings, $db_prefix, $user_info, $scripturl, $context;
</search for>

<replace>
function Download()
{
	global $txt, $modSettings, $db_prefix, $user_info, $scripturl, $context, $topic;
</replace>


<search for>
		isAllowedTo('view_attachments');

		// Make sure this attachment is on this board.
		// NOTE: We must verify that $topic is the attachment's topic, or else the permission check above is broken.
		$request = db_query("
			SELECT a.filename, a.ID_ATTACH, a.file_hash
			FROM {$db_prefix}boards AS b, {$db_prefix}messages AS m, {$db_prefix}attachments AS a
			WHERE b.ID_BOARD = m.ID_BOARD
				AND $user_info[query_see_board]
				AND m.ID_MSG = a.ID_MSG
				AND a.ID_ATTACH = $_REQUEST[id]
			LIMIT 1", __FILE__, __LINE__);
	}
</search for>

<replace>
		// This checks only the current board for $board/$topic's permissions.
		isAllowedTo('view_attachments');

		// Make sure this attachment is on this board.
		// NOTE: We must verify that $topic is the attachment's topic, or else the permission check above is broken.
		$request = db_query("
			SELECT a.filename, a.ID_ATTACH, a.file_hash
			FROM ({$db_prefix}boards AS b, {$db_prefix}messages AS m, {$db_prefix}attachments AS a)
			WHERE b.ID_BOARD = m.ID_BOARD
				AND $user_info[query_see_board]
				AND m.ID_MSG = a.ID_MSG
				AND m.ID_TOPIC = $topic
				AND a.ID_ATTACH = $_REQUEST[id]
			LIMIT 1", __FILE__, __LINE__);
	}
</replace>



<edit file>
$sourcedir/Load.php
</edit file>

<search for>
* Software Version:           SMF 1.0.17                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
		$modSettings[$row[0]] = $row[1];
	mysql_free_result($request);
</search for>

<replace>
		$modSettings[$row[0]] = $row[1];
	mysql_free_result($request);

	// Setting the timezone is a requirement for some functions in PHP >= 5.1.
	if (isset($modSettings['default_timezone']) && function_exists('date_default_timezone_set'))
		date_default_timezone_set($modSettings['default_timezone']);
</replace>



<edit file>
$sourcedir/ManageMembers.php
</edit file>

<search for>
* Software Version:           SMF 1.0.18                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
	// Check input after a member search has been submitted.
	if ($context['sub_action'] == 'query' && empty($_REQUEST['params']))
</search for>

<replace>
	// Build a search for a specific group or post group.
	if ($context['sub_action'] === 'query')
	{
		if (isset($_GET['group']))
			$_POST['membergroups'] = array(
				array((int) $_GET['group']),
				array((int) $_GET['group']),
			);
		elseif (isset($_GET['pgroup']))
			$_POST['postgroups'] = array((int) $_GET['pgroup']);
	}

	if ($context['sub_action'] == 'query' && !empty($_REQUEST['params']) && empty($_POST))
	{
		$search_params = base64_decode(stripslashes($_REQUEST['params']));
		$_POST += addslashes__recursive(@unserialize($search_params));
	}

	// Check input after a member search has been submitted.
	if ($context['sub_action'] == 'query')
</replace>


<search for>
	elseif ($context['sub_action'] == 'query')
		$where = base64_decode($_REQUEST['params']);
</search for>

<replace>
	else
		$search_params = null;
</replace>


<search for>
	// Construct the additional URL part with the query info in it.
	$context['params_url'] = $context['sub_action'] == 'query' ? ';sa=query;params=' . base64_encode($where) : '';
</search for>

<replace>
	// Construct the additional URL part with the query info in it.
	$context['params_url'] = $context['sub_action'] == 'query' ? ';sa=query;params=' . $search_params : '';
</replace>



<edit file>
$sourcedir/ManagePermissions.php
</edit file>

<search for>
* Software Version:           SMF 1.0.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
			'href' => $scripturl . '?action=viewmembers;sa=query;params=' . base64_encode('ID_GROUP = 0'),
			'link' => '<a href="' . $scripturl . '?action=viewmembers;sa=query;params=' . base64_encode('ID_GROUP = 0') . '">' . $num_members . '</a>',
</search for>

<replace>
			'href' => $scripturl . '?action=viewmembers;sa=query;group=0',
			'link' => '<a href="' . $scripturl . '?action=viewmembers;sa=query;group=0">' . $num_members . '</a>',
</replace>


<search for>
			'href' => $scripturl . '?action=viewmembers;sa=query;params=' . base64_encode($row['minPosts'] == -1 ? "ID_GROUP = $row[ID_GROUP] OR FIND_IN_SET($row[ID_GROUP], additionalGroups)" : "ID_POST_GROUP = $row[ID_GROUP]"),
			'link' => '<a href="' . $scripturl . '?action=viewmembers;sa=query;params=' . base64_encode($row['minPosts'] == -1 ? "ID_GROUP = $row[ID_GROUP] OR FIND_IN_SET($row[ID_GROUP], additionalGroups)" : "ID_POST_GROUP = $row[ID_GROUP]") . '">' . $row['num_members'] . '</a>',
</search for>

<replace>
			'href' => $scripturl . '?action=viewmembers;sa=query' . ($row['minPosts'] == -1 ? ';group = ' . (int) $row['ID_GROUP'] : 'pgroup=' . $row['ID_GROUP']),
			'link' => '<a href="' . $scripturl . '?action=viewmembers;sa=query' . ($row['minPosts'] == -1 ? ';group = ' . (int) $row['ID_GROUP'] : 'pgroup=' . $row['ID_GROUP']) . '">' . $row['num_members'] . '</a>',
</replace>



<edit file>
$sourcedir/ManageSmileys.php
</edit file>

<search for>
* Software Version:           SMF 1.0.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
			'path' => $set,
			'name' => $set_names[$i],
			'selected' => $set == $modSettings['smiley_sets_default']
		);
}
</search for>

<replace>
			'path' => htmlspecialchars($set),
			'name' => htmlspecialchars($set_names[$i]),
			'selected' => $set == $modSettings['smiley_sets_default']
		);
}
</replace>


<search for>
			'path' => $set,
			'name' => $set_names[$i],
			'selected' => $set == $modSettings['smiley_sets_default']
		);

	// Importing any smileys from an existing set?
</search for>

<replace>
			'path' => htmlspecialchars($set),
			'name' => htmlspecialchars($set_names[$i]),
			'selected' => $set == $modSettings['smiley_sets_default']
		);

	// Importing any smileys from an existing set?
</replace>


<search for>
		if (isset($context['smiley_sets'][$_GET['id']]))
			ImportSmileys($context['smiley_sets'][$_GET['id']]['path']);
</search for>

<replace>
		if (isset($context['smiley_sets'][$_GET['id']]))
			ImportSmileys(un_htmlspecialchars($context['smiley_sets'][$_GET['id']]['path']));
</replace>


<search for>
			'path' => $set,
			'name' => $set_names[$i],
			'selected' => $set == $modSettings['smiley_sets_default']
		);

	// Submitting a form?
</search for>

<replace>
			'path' => htmlspecialchars($set),
			'name' => htmlspecialchars($set_names[$i]),
			'selected' => $set == $modSettings['smiley_sets_default']
		);

	// Submitting a form?
</replace>


<search for>
			foreach ($context['smiley_sets'] as $set)
			{
				if (!is_writable($context['smileys_dir'] . '/' . $set['path']))
</search for>

<replace>
			foreach ($context['smiley_sets'] as $set)
			{
				if (!is_writable($context['smileys_dir'] . '/' . un_htmlspecialchars($set['path'])))
</replace>


<search for>
			// Keep going until we find a set the file doesn't exist in. (or maybe it exists in all of them?)
			while (isset($context['smiley_sets'][$i]) && file_exists($context['smileys_dir'] . '/' . $context['smiley_sets'][$i]['path'] . '/' . $destName))
</search for>

<replace>
			// Keep going until we find a set the file doesn't exist in. (or maybe it exists in all of them?)
			while (isset($context['smiley_sets'][$i]) && file_exists($context['smileys_dir'] . '/' . un_htmlspecialchars($context['smiley_sets'][$i]['path']) . '/' . $destName))
</replace>


<search for>
			if (isset($context['smiley_sets'][$i]['path']))
			{
				$smileyLocation = $context['smileys_dir'] . '/' . $context['smiley_sets'][$i]['path'] . '/' . $destName;
</search for>

<replace>
			if (isset($context['smiley_sets'][$i]['path']))
			{
				$smileyLocation = $context['smileys_dir'] . '/' . un_htmlspecialchars($context['smiley_sets'][$i]['path']) . '/' . $destName;
</replace>


<search for>
				for ($n = count($context['smiley_sets']); $i < $n; $i++)
				{
					$currentPath = $context['smileys_dir'] . '/' . $context['smiley_sets'][$i]['path'] . '/' . $destName;
</search for>

<replace>
				for ($n = count($context['smiley_sets']); $i < $n; $i++)
				{
					$currentPath = $context['smileys_dir'] . '/' . un_htmlspecialchars($context['smiley_sets'][$i]['path']) . '/' . $destName;
</replace>


<search for>
				if (!isset($_FILES['individual_' . $set['name']]['name']) || $_FILES['individual_' . $set['name']]['name'] == '')
					continue;
</search for>

<replace>
				$set['name'] = un_htmlspecialchars($set['name']);
				$set['path'] = un_htmlspecialchars($set['path']);

				if (!isset($_FILES['individual_' . $set['name']]['name']) || $_FILES['individual_' . $set['name']]['name'] == '')
					continue;
</replace>


<search for>
		foreach ($context['smiley_sets'] as $smiley_set)
		{
			if (!file_exists($context['smileys_dir'] . '/' . $smiley_set['path']))
</search for>

<replace>
		foreach ($context['smiley_sets'] as $smiley_set)
		{
			if (!file_exists($context['smileys_dir'] . '/' . un_htmlspecialchars($smiley_set['path'])))
</replace>


<search for>
			$dir = dir($context['smileys_dir'] . '/' . $smiley_set['path']);
			while ($entry = $dir->read())
			{
				if (!in_array($entry, $context['filenames']) && in_array(strrchr($entry, '.'), array('.jpg', '.gif', '.jpeg', '.png')))
					$context['filenames'][strtolower($entry)] = array(
						'id' => htmlspecialchars($entry),
						'selected' => false,
					);
			}
			$dir->close();
		}
		ksort($context['filenames']);
	}

	// Create a new smiley from scratch.
</search for>

<replace>
			$dir = dir($context['smileys_dir'] . '/' . un_htmlspecialchars($smiley_set['path']));
			while ($entry = $dir->read())
			{
				if (!in_array($entry, $context['filenames']) && in_array(strrchr($entry, '.'), array('.jpg', '.gif', '.jpeg', '.png')))
					$context['filenames'][strtolower($entry)] = array(
						'id' => htmlspecialchars($entry),
						'selected' => false,
					);
			}
			$dir->close();
		}
		ksort($context['filenames']);
	}

	// Create a new smiley from scratch.
</replace>


<search for>
			'path' => $set,
			'name' => $set_names[$i],
			'selected' => $set == $modSettings['smiley_sets_default']
		);

	// Prepare overview of all (custom) smileys.
</search for>

<replace>
			'path' => htmlspecialchars($set),
			'name' => htmlspecialchars($set_names[$i]),
			'selected' => $set == $modSettings['smiley_sets_default']
		);

	// Prepare overview of all (custom) smileys.
</replace>


<search for>
				foreach ($context['smileys'] as $smiley_id => $smiley)
					if (!file_exists($modSettings['smileys_dir'] . '/' . $smiley_set['path'] . '/' . $smiley['filename']))
</search for>

<replace>
				foreach ($context['smileys'] as $smiley_id => $smiley)
					if (!file_exists($modSettings['smileys_dir'] . '/' . un_htmlspecialchars($smiley_set['path']) . '/' . $smiley['filename']))
</replace>


<search for>
				'path' => $set,
				'name' => $set_names[$i],
				'selected' => $set == $modSettings['smiley_sets_default']
			);

		$context['selected_set'] = $modSettings['smiley_sets_default'];
</search for>

<replace>
				'path' => htmlspecialchars($set),
				'name' => htmlspecialchars($set_names[$i]),
				'selected' => $set == $modSettings['smiley_sets_default']
			);

		$context['selected_set'] = $modSettings['smiley_sets_default'];
</replace>


<search for>
			foreach ($context['smiley_sets'] as $smiley_set)
			{
				if (!file_exists($context['smileys_dir'] . '/' . $smiley_set['path']))
</search for>

<replace>
			foreach ($context['smiley_sets'] as $smiley_set)
			{
				if (!file_exists($context['smileys_dir'] . '/' . un_htmlspecialchars($smiley_set['path'])))
</replace>


<search for>
				$dir = dir($context['smileys_dir'] . '/' . $smiley_set['path']);
				while ($entry = $dir->read())
				{
					if (!in_array($entry, $context['filenames']) && in_array(strrchr($entry, '.'), array('.jpg', '.gif', '.jpeg', '.png')))
						$context['filenames'][strtolower($entry)] = array(
							'id' => htmlspecialchars($entry),
							'selected' => false,
						);
				}
				$dir->close();
			}
			ksort($context['filenames']);
		}

		$request = db_query("
</search for>

<replace>
				$dir = dir($context['smileys_dir'] . '/' . un_htmlspecialchars($smiley_set['path']));
				while ($entry = $dir->read())
				{
					if (!in_array($entry, $context['filenames']) && in_array(strrchr($entry, '.'), array('.jpg', '.gif', '.jpeg', '.png')))
						$context['filenames'][strtolower($entry)] = array(
							'id' => htmlspecialchars($entry),
							'selected' => false,
						);
				}
				$dir->close();
			}
			ksort($context['filenames']);
		}

		$request = db_query("
</replace>



<edit file>
$sourcedir/PackageGet.php
</edit file>

<search for>
* Software Version:           SMF 1.0.17                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
	$fp = fopen($boarddir . '/Packages/server.list', 'a');
	fputs($fp, $_POST['servername'] . '|^|' . $_POST['serverurl'] . "\n");
</search for>

<replace>
	$fp = fopen($boarddir . '/Packages/server.list', 'a');
	fputs($fp, htmlspecialchars($_POST['servername']) . '|^|' . htmlspecialchars($_POST['serverurl']) . "\n");
</replace>


<search for>
	// Get the current server list.
	if (!file_exists($boarddir . '/Packages/server.list'))
</search for>

<replace>
	checkSession('get');

	// Get the current server list.
	if (!file_exists($boarddir . '/Packages/server.list'))
</replace>



<edit file>
$sourcedir/Packages.php
</edit file>

<search for>
* Software Version:           SMF 1.0.18                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
	// Can't delete what's not there.
	if (file_exists($boarddir . '/Packages/' . $_GET['package']))
</search for>

<replace>
	// Can't delete what's not there.
	if (file_exists($boarddir . '/Packages/' . $_GET['package']) && (substr($_GET['package'], -4) == '.zip' || substr($_GET['package'], -4) == '.tgz' || substr($_GET['package'], -7) == '.tar.gz') && substr($_GET['package'], 0, 1) != '.')
</replace>


<search for>
		updateSettings(array(
			'package_server' => $_POST['pack_server'],
			'package_port' => $_POST['pack_port'],
</search for>

<replace>
		checkSession('post');

		updateSettings(array(
			'package_server' => $_POST['pack_server'],
			'package_port' => $_POST['pack_port'],
</replace>



<edit file>
$sourcedir/Poll.php
</edit file>

<search for>
* Software Version:           SMF 1.0.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
		$pollOptions = array();

		// Find out what they voted for before.
</search for>

<replace>
		checkSession('request')
		$pollOptions = array();

		// Find out what they voted for before.
</replace>


<search for>
	// Check permissions.
</search for>

<replace>
	checkSession('get');

	// Check permissions.
</replace>



<edit file>
$sourcedir/Post.php
</edit file>

<search for>
* Software Version:           SMF 1.0.17                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
					$context['current_attachments'][] = array(
						'name' => $row['filename'],
</search for>

<replace>
					$context['current_attachments'][] = array(
						'name' => htmlspecialchars($row['filename']),
</replace>


<search for>
				$context['current_attachments'][] = array(
					'name' => $attachment['filename'],
</search for>

<replace>
				$context['current_attachments'][] = array(
					'name' => htmlspecialchars($attachment['filename']),
</replace>


<search for>
	// Editing a message...
	elseif (isset($_REQUEST['msg']))
	{
		checkSession('get');

</search for>

<replace>
	// Editing a message...
	elseif (isset($_REQUEST['msg']))
	{
</replace>


<search for>
		// Posting a quoted reply?
		if (!empty($topic) && !empty($_REQUEST['quote']))
		{
			checkSession('get');

</search for>

<replace>
		// Posting a quoted reply?
		if (!empty($topic) && !empty($_REQUEST['quote']))
		{
</replace>


<edit file>
$sourcedir/Profile.php
</edit file>

<search for>
* Software Version:           SMF 1.0.17                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
			$_POST['websiteUrl'] = 'http://' . $_POST['websiteUrl'];
		if (strlen($_POST['websiteUrl']) < 8)
</search for>

<replace>
			$_POST['websiteUrl'] = 'http://' . $_POST['websiteUrl'];
		if (strlen($_POST['websiteUrl']) < 8 || (substr($_POST['websiteUrl'], 0, 7) !== 'http://' && substr($_POST['websiteUrl'], 0, 8) !== 'https://'))
</replace>


<search for>
			'id' => $set,
			'name' => $set_names[$i],
</search for>

<replace>
			'id' => htmlspecialchars($set),
			'name' => htmlspecialchars($set_names[$i]),
</replace>


<search for>
		if ($context['smiley_sets'][$i]['selected'])
			$context['member']['smiley_set']['name'] = $set_names[$i];
</search for>

<replace>
		if ($context['smiley_sets'][$i]['selected'])
			$context['member']['smiley_set']['name'] = htmlspecialchars($set_names[$i]);
</replace>



<edit file>
$sourcedir/SplitTopics.php
</edit file>

<search for>
* Software Version:           SMF 1.0.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
	// Handle URLs from MergeTopics1.
	if (!empty($_GET['from']) && !empty($_GET['to']))
</search for>

<replace>
	checkSession('request');

	// Handle URLs from MergeTopics1.
	if (!empty($_GET['from']) && !empty($_GET['to']))
</replace>



<edit file>
$sourcedir/Subs.php
</edit file>

<search for>
* Software Version:           SMF 1.0.17                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
	if (setlocale(LC_TIME, @$txt['lang_locale']))
	{
		$str = ereg_replace('%a', ucwords(strftime('%a', $time)), $str);
		$str = ereg_replace('%A', ucwords(strftime('%A', $time)), $str);
		$str = ereg_replace('%b', ucwords(strftime('%b', $time)), $str);
		$str = ereg_replace('%B', ucwords(strftime('%B', $time)), $str);
	}
	else
	{
		// Do-it-yourself time localization.  Fun.
		$str = ereg_replace('%a', @$days_short[(int) strftime('%w', $time)], $str);
		$str = ereg_replace('%A', @$days[(int) strftime('%w', $time)], $str);
		$str = ereg_replace('%b', @$months_short[(int) strftime('%m', $time)], $str);
		$str = ereg_replace('%B', @$months[(int) strftime('%m', $time)], $str);
		$str = ereg_replace('%p', (strftime('%H', $time) < 12 ? 'am' : 'pm'), $str);
	}
</search for>

<replace>
	if (setlocale(LC_TIME, $txt['lang_locale']))
	{
		foreach (array('%a', '%A', '%b', '%B') as $token)
			if (strpos($str, $token) !== false)
				$str = str_replace($token, !empty($txt['lang_capitalize_dates']) ? $smcFunc['ucwords'](strftime($token, $time)) : strftime($token, $time), $str);
	}
	else
	{
		// Do-it-yourself time localization.  Fun.
		foreach (array('%a' => 'days_short', '%A' => 'days', '%b' => 'months_short', '%B' => 'months') as $token => $text_label)
			if (strpos($str, $token) !== false)
				$str = str_replace($token, ${$text_label}[(int) strftime($token === '%a' || $token === '%A' ? '%w' : '%m', $time)], $str);
		if (strpos($str, '%p'))
			$str = str_replace('%p', (strftime('%H', $time) < 12 ? 'am' : 'pm'), $str);
	}
</replace>


<search for>
					$php_parts[$php_i] = preg_replace(array('~(?<=[\s>\.(;\'"])((?:http|https|ftp|ftps)://[\w\-_@:|]+(?:\.[\w\-_]+)*(?::\d+)?(?:/[\w\-_\~%\.@,\?&;=#+:\']*|\([\w\-_\~%\.@,\?&;=#()+:\']*)*[/\w\-_\~%@\?;=#])~i', '~(?<=[\s>(\'])(www(?:\.[\w\-_]+)+(?::\d+)?(?:/[\w\-_\~%\.@,\?&;=#+:\']*|\([\w\-_\~%\.@,\?&;=#()+:\']*)*[/\w\-_\~%@\?;=#])~i'), array('[url]$1[/url]', '[url=http://$1]$1[/url]'), $php_parts[$php_i]);
</search for>

<replace>

					// Only do this if the preg survives.
					if (is_string($result = preg_replace(array(
						'~(?<=[\s>\.(;\'"]|^)((?:http|https|ftp|ftps)://[\w\-_%@:|]+(?:\.[\w\-_%]+)*(?::\d+)?(?:/[\w\-_\~%\.@,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i', 
						'~(?<=[\s>(\'<]|^)(www(?:\.[\w\-_]+)+(?::\d+)?(?:/[\w\-_\~%\.@,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i'
					), array(
						'[url]$1[/url]',
						'[url=http://$1]$1[/url]'
					), $php_parts[$php_i])))
						$php_parts[$php_i] = $result;

</replace>


<search for>
		$message = preg_replace('~&lt;a\s+href=(?:&quot;)?(?:\[url\])?((?:http://|ftp:/\|https://|ftps://|mailto:).+?)(?:\[/url\])?(?:&quot;)?&gt;(.+?)&lt;/a&gt;~ie', '\'<a href="$1">\' . preg_replace(\'~(\[url.*?\]|\[/url\])~\', \'\', \'$2\') . \'</a>\'', $message);

		// Do <img ... /> - with security... action= -> action-.
		preg_match_all('~&lt;img\s+src=(?:&quot;)?(?:\[url\])?((?:http://|ftp://|https://|ftps://).+?)(?:\[/url\])?(?:&quot;)?(?:\s+alt=(?:&quot;)?(.*?)(?:&quot;)?)?(?:\s?/)?&gt;~i', $message, $matches, PREG_PATTERN_ORDER);
</search for>

<replace>
		$message = preg_replace('~&lt;a\s+href=(?:&quot;)?(?:\[url\])?((?:http://|ftp:/\|https://|ftps://|mailto:)\S+?)(?:\[/url\])?(?:&quot;)?&gt;(.+?)&lt;/a&gt;~ie', '\'<a href="$1">\' . preg_replace(\'~(\[url.*?\]|\[/url\])~\', \'\', \'$2\') . \'</a>\'', $message);

		// Do <img ... /> - with security... action= -> action-.
		preg_match_all('~&lt;img\s+src=(?:&quot;)?(?:\[url\])?((?:http://|ftp://|https://|ftps://)\S+?)(?:\[/url\])?(?:&quot;)?(?:\s+alt=&quot.*?&quot;)?(?:\s?/)?&gt;~i', $message, $matches, PREG_PATTERN_ORDER);
</replace>



<edit file>
$sourcedir/Subs-Auth.php
</edit file>

<search for>
* Software Version:           SMF 1.0.18                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
	foreach ($_GET as $k => $v)
		$context['get_data'] .= $k . '=' . $v . ';';
</search for>

<replace>
	foreach ($_GET as $k => $v)
		$context['get_data'] .= urlencode($k) . '=' . urlencode($v) . ';';
</replace>


<search for>
		return '
<input type="hidden" name="' . $k . '" value="' . htmlspecialchars(stripslashes($v)) . '" />';
</search for>

<replace>
		return '
<input type="hidden" name="' . htmlspecialchars($k) . '" value="' . htmlspecialchars(stripslashes($v)) . '" />';
</replace>



<edit file>
$sourcedir/Subs-Boards.php
</edit file>

<search for>
* Software Version:           SMF 1.0.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
	$_REQUEST['c'] = (int) $_REQUEST['c'];
</search for>

<replace>
	checkSession('request');

	$_REQUEST['c'] = (int) $_REQUEST['c'];
</replace>



<edit file>
$sourcedir/Themes.php
</edit file>

<search for>
* Software Version:           SMF 1.0.15                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.19                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
	foreach ($sets as $i => $set)
		$context['smiley_sets'][$set] = $set_names[$i];
</search for>

<replace>
	foreach ($sets as $i => $set)
		$context['smiley_sets'][$set] = htmlspecialchars($set_names[$i]);
</replace>



<edit file>
$themedir/Display.template.php
</edit file>

<search for>
// Version: 1.0.3; Display
</search for>

<replace>
// Version: 1.0.19; Display
</replace>


<search for>
			echo '
									<a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';num_replies=', $context['num_replies'], ';sesc=', $context['session_id'], '" onclick="if (!currentSwap) doQuote(', $message['id'], '); else window.location.href = this.href; return false;">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/quote.gif" alt="' . $txt[145] . '" border="0" />' : $txt[145]), '</a>';
</search for>

<replace>
			echo '
									<a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';num_replies=', $context['num_replies'], '" onclick="if (!currentSwap) doQuote(', $message['id'], '); else window.location.href = this.href; return false;">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/quote.gif" alt="' . $txt[145] . '" border="0" />' : $txt[145]), '</a>';
</replace>


<search for>
			echo '
									<a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';num_replies=', $context['num_replies'], ';sesc=', $context['session_id'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/quote.gif" alt="' . $txt[145] . '" border="0" />' : $txt[145]), '</a>';
</search for>

<replace>
			echo '
									<a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';num_replies=', $context['num_replies'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/quote.gif" alt="' . $txt[145] . '" border="0" />' : $txt[145]), '</a>';
</replace>



<edit file>
$themedir/Packages.template.php
</edit file>

<search for>
// Version: 1.0.16; Packages
</search for>

<replace>
// Version: 1.0.19; Packages
</replace>


<search for>
							<td>
								<a href="' . $scripturl . '?action=pgremove;server=' . $server['id'] . '">[ ' . $txt['smf138'] . ' ]</a>
</search for>

<replace>
							<td>
								<a href="' . $scripturl . '?action=pgremove;server=' . $server['id'] . ';sesc=', $context['session_id'], '">[ ' . $txt['smf138'] . ' ]</a>
</replace>



<edit file>
$themedir/SplitTopics.template.php
</edit file>

<search for>
// Version: 1.0; SplitTopics
</search for>

<replace>
// Version: 1.0.19; SplitTopics
</replace>


<search for>
					</td>
				</tr>
			</table>
</search for>

<replace>
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
					</td>
				</tr>
			</table>
</replace>


