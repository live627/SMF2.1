<edit file>
$boarddir/index.php
</edit file>

<search for>
* Software Version:           SMF 1.1.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
$forum_version = 'SMF 1.1.10';
</search for>

<replace>
$forum_version = 'SMF 1.1.11';
</replace>



<edit file>
$sourcedir/BoardIndex.php
</edit file>

<search for>
* Software Version:           SMF 1.1                                             *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
				'collapse_href' => isset($row_board['canCollapse']) ? $scripturl . '?action=collapse;c=' . $row_board['ID_CAT'] . ';sa=' . ($row_board['isCollapsed'] > 0 ? 'expand' : 'collapse;') . '#' . $row_board['ID_CAT'] : '',
</search for>

<replace>
				'collapse_href' => isset($row_board['canCollapse']) ? $scripturl . '?action=collapse;c=' . $row_board['ID_CAT'] . ';sa=' . ($row_board['isCollapsed'] > 0 ? 'expand' : 'collapse') . ';sesc=' .$context['session_id'] . '#' . $row_board['ID_CAT'] : '',
</replace>



<edit file>
$sourcedir/Display.php
</edit file>

<search for>
* Software Version:           SMF 1.1.9                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
function Download()
{
	global $txt, $modSettings, $db_prefix, $user_info, $scripturl, $context, $sourcedir;
</search for>

<replace>
function Download()
{
	global $txt, $modSettings, $db_prefix, $user_info, $scripturl, $context, $sourcedir, $topic;
</replace>


<search for>
		isAllowedTo('view_attachments');

		// Make sure this attachment is on this board.
</search for>

<replace>
		// This checks only the current board for $board/$topic's permissions.
		isAllowedTo('view_attachments');

		// Make sure this attachment is on this board.
</replace>


<search for>
		$request = db_query("
			SELECT a.filename, a.ID_ATTACH, a.attachmentType, a.file_hash
			FROM ({$db_prefix}boards AS b, {$db_prefix}messages AS m, {$db_prefix}attachments AS a)
</search for>

<replace>
		// NOTE: We must verify that $topic is the attachment's topic, or else the permission check above is broken.
		$request = db_query("
			SELECT a.filename, a.ID_ATTACH, a.attachmentType, a.file_hash
			FROM ({$db_prefix}boards AS b, {$db_prefix}messages AS m, {$db_prefix}attachments AS a)
</replace>


<search for>
				AND a.ID_ATTACH = $_REQUEST[attach]
			LIMIT 1", __FILE__, __LINE__);
	}
</search for>

<replace>
				AND m.ID_TOPIC = $topic
				AND a.ID_ATTACH = $_REQUEST[attach]
			LIMIT 1", __FILE__, __LINE__);
	}
</replace>



<edit file>
$sourcedir/Load.php
</edit file>

<search for>
* Software Version:           SMF 1.1.9                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
	// This isn't meant to be reliable, it's just meant to catch most bots to prevent PHPSESSID from showing up.
</search for>

<replace>
	// 1.1.x doesn't detect IE8, so render as IE7.
	$context['html_headers'] .= '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />';

	// This isn't meant to be reliable, it's just meant to catch most bots to prevent PHPSESSID from showing up.
</replace>



<edit file>
$sourcedir/ManageAttachments.php
</edit file>

<search for>
* Software Version:           SMF 1.1.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
				'href' => $row['attachmentType'] == 1 ? $modSettings['custom_avatar_url'] . '/' . $row['filename'] : ($scripturl . '?action=dlattach;' . ($context['browse_type'] == 'avatars' ? 'type=avatar;' : 'topic=' . $row['ID_TOPIC'] . '.0;') . 'id=' . $row['ID_ATTACH']),
				'link' => '<a href="' . ($row['attachmentType'] == 1 ? $modSettings['custom_avatar_url'] . '/' . $row['filename'] : ($scripturl . '?action=dlattach;' . ($context['browse_type'] == 'avatars' ? 'type=avatar;' : 'topic=' . $row['ID_TOPIC'] . '.0;') . 'id=' . $row['ID_ATTACH'])) . '"' . (empty($row['width']) || empty($row['height']) ? '' : ' onclick="return reqWin(this.href + \'' . ($modSettings['custom_avatar_url'] ? '' : ';image') . '\', ' . ($row['width'] + 20) . ', ' . ($row['height'] + 20) . ', true);"') . '>' . htmlspecialchars($row['filename']) . '</a>'
</search for>

<replace>
				'href' => $row['attachmentType'] == 1 ? $modSettings['custom_avatar_url'] . '/' . $row['filename'] : ($scripturl . '?action=dlattach;' . ($context['browse_type'] == 'avatars' ? 'type=avatar;' : 'topic=' . $row['ID_TOPIC'] . '.0;') . 'id=' . $row['ID_ATTACH']),
				'link' => '<a href="' . ($row['attachmentType'] == 1 ? $modSettings['custom_avatar_url'] . '/' . $row['filename'] : ($scripturl . '?action=dlattach;' . ($context['browse_type'] == 'avatars' ? 'type=avatar;' : 'topic=' . $row['ID_TOPIC'] . '.0;') . 'id=' . $row['ID_ATTACH'])) . '"' . (empty($row['width']) || empty($row['height']) ? '' : ' onclick="return reqWin(this.href + \'' . ($row['attachmentType'] == 1 ? '' : ';image') . '\', ' . ($row['width'] + 20) . ', ' . ($row['height'] + 20) . ', true);"') . '>' . htmlspecialchars($row['filename']) . '</a>'
</replace>


<search for>
			a.filename, a.file_hash, a.attachmentType, a.ID_ATTACH, a.ID_MEMBER" . ($query_type == 'messages' ? ', m.ID_MSG' : ', a.ID_MSG') . ",
			IFNULL(thumb.ID_ATTACH, 0) AS ID_THUMB, thumb.filename AS thumb_filename, thumb_parent.ID_ATTACH AS ID_PARENT
</search for>

<replace>
			a.filename, a.file_hash, a.attachmentType, a.ID_ATTACH, a.ID_MEMBER" . ($query_type == 'messages' ? ', m.ID_MSG' : ', a.ID_MSG') . ",
			IFNULL(thumb.ID_ATTACH, 0) AS ID_THUMB, thumb.filename AS thumb_filename, thumb_parent.ID_ATTACH AS ID_PARENT,
			thumb.file_hash as thumb_file_hash
</replace>


<search for>
			if (!empty($row['ID_THUMB']) && $autoThumbRemoval)
			{
				$thumb_filename = getAttachmentFilename($row['thumb_filename'], $row['ID_THUMB'], false, $row['file_hash']);
</search for>

<replace>
			if (!empty($row['ID_THUMB']) && $autoThumbRemoval)
			{
				$thumb_filename = getAttachmentFilename($row['thumb_filename'], $row['ID_THUMB'], false, $row['thumb_file_hash']);
</replace>



<edit file>
$sourcedir/ManageCalendar.php
</edit file>

<search for>
* Software Version:           SMF 1.1                                             *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>

	// Submitting?
	if (isset($_POST['sc']) && (isset($_REQUEST['delete']) || $_REQUEST['title'] != ''))
</search for>

<replace>
	
	// Cast this for safety...
	if (isset($_REQUEST['holiday']))
		$_REQUEST['holiday'] = (int) $_REQUEST['holiday'];

	// Submitting?
	if (isset($_POST['sc']) && (isset($_REQUEST['delete']) || $_REQUEST['title'] != ''))
</replace>



<edit file>
$sourcedir/ManageMembers.php
</edit file>

<search for>
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
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
	}
	// If the query information was already packed in the URL, decode it.
	// !!! Change this.
	elseif ($context['sub_action'] == 'query')
		$where = base64_decode(strtr($_REQUEST['params'], array(' ' => '+')));

	// Construct the additional URL part with the query info in it.
	$context['params_url'] = $context['sub_action'] == 'query' ? ';sa=query;params=' . base64_encode($where) : '';
</search for>

<replace>

		$search_params = base64_encode(serialize(stripslashes__recursive($_POST)));
	}
	else
		$search_params = null;

	// Construct the additional URL part with the query info in it.
	$context['params_url'] = $context['sub_action'] == 'query' ? ';sa=query;params=' . $search_params : '';
</replace>



<edit file>
$sourcedir/ManagePermissions.php
</edit file>

<search for>
* Software Version:           SMF 1.1.5                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
			'can_search' => true,
			'href' => $scripturl . '?action=viewmembers;sa=query;params=' . base64_encode('ID_GROUP = 0'),
</search for>

<replace>
			'can_search' => true,
			'href' => $scripturl . '?action=viewmembers;sa=query;group=0',
</replace>


<search for>
			'can_search' => $row['ID_GROUP'] != 3,
			'href' => $scripturl . '?action=viewmembers;sa=query;params=' . base64_encode($row['minPosts'] == -1 ? "ID_GROUP = $row[ID_GROUP] OR FIND_IN_SET($row[ID_GROUP], additionalGroups)" : "ID_POST_GROUP = $row[ID_GROUP]"),
</search for>

<replace>
			'can_search' => $row['ID_GROUP'] != 3,
			'href' => $scripturl . '?action=viewmembers;sa=query' . ($row['minPosts'] == -1 ? ';group=' . (int) $row['ID_GROUP'] : ';pgroup=' . (int) $row['ID_GROUP']),
</replace>



<edit file>
$sourcedir/ManageSmileys.php
</edit file>

<search for>
* Software Version:           SMF 1.1.1                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
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
			ImportSmileys($context['smiley_sets'][$_GET['set']]['path']);
</search for>

<replace>
			ImportSmileys(un_htmlspecialchars($context['smiley_sets'][$_GET['set']]['path']));
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
				if (!is_writable($context['smileys_dir'] . '/' . $set['path']))
</search for>

<replace>
				if (!is_writable($context['smileys_dir'] . '/' . un_htmlspecialchars($set['path'])))
</replace>


<search for>
			while (isset($context['smiley_sets'][$i]) && file_exists($context['smileys_dir'] . '/' . $context['smiley_sets'][$i]['path'] . '/' . $destName))
</search for>

<replace>
			while (isset($context['smiley_sets'][$i]) && file_exists($context['smileys_dir'] . '/' . un_htmlspecialchars($context['smiley_sets'][$i]['path']) . '/' . $destName))
</replace>


<search for>
				$smileyLocation = $context['smileys_dir'] . '/' . $context['smiley_sets'][$i]['path'] . '/' . $destName;
				move_uploaded_file($_FILES['uploadSmiley']['tmp_name'], $smileyLocation);
				@chmod($smileyLocation, 0644);

				// Now, we want to move it from there to all the other sets.
				for ($n = count($context['smiley_sets']); $i < $n; $i++)
				{
					$currentPath = $context['smileys_dir'] . '/' . $context['smiley_sets'][$i]['path'] . '/' . $destName;
</search for>

<replace>
				$smileyLocation = $context['smileys_dir'] . '/' . un_htmlspecialchars($context['smiley_sets'][$i]['path']) . '/' . $destName;
				move_uploaded_file($_FILES['uploadSmiley']['tmp_name'], $smileyLocation);
				@chmod($smileyLocation, 0644);

				// Now, we want to move it from there to all the other sets.
				for ($n = count($context['smiley_sets']); $i < $n; $i++)
				{
					$currentPath = $context['smileys_dir'] . '/' . un_htmlspecialchars($context['smiley_sets'][$i]['path']) . '/' . $destName;
</replace>


<search for>
				if (!isset($_FILES['individual_' . $set['name']]['name']) || $_FILES['individual_' . $set['name']]['name'] == '')
</search for>

<replace>
				$set['name'] = un_htmlspecialchars($set['name']);
				$set['path'] = un_htmlspecialchars($set['path']);

				if (!isset($_FILES['individual_' . $set['name']]['name']) || $_FILES['individual_' . $set['name']]['name'] == '')
</replace>


<search for>
			if (!file_exists($context['smileys_dir'] . '/' . $smiley_set['path']))
				continue;

			$dir = dir($context['smileys_dir'] . '/' . $smiley_set['path']);
</search for>

<replace>
			if (!file_exists($context['smileys_dir'] . '/' . un_htmlspecialchars($smiley_set['path'])))
				continue;

			$dir = dir($context['smileys_dir'] . '/' . un_htmlspecialchars($smiley_set['path']));
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
					if (!file_exists($modSettings['smileys_dir'] . '/' . $smiley_set['path'] . '/' . $smiley['filename']))
</search for>

<replace>
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
				if (!file_exists($context['smileys_dir'] . '/' . $smiley_set['path']))
					continue;

				$dir = dir($context['smileys_dir'] . '/' . $smiley_set['path']);
</search for>

<replace>
				if (!file_exists($context['smileys_dir'] . '/' . un_htmlspecialchars($smiley_set['path'])))
					continue;

				$dir = dir($context['smileys_dir'] . '/' . un_htmlspecialchars($smiley_set['path']));
</replace>



<edit file>
$sourcedir/Modlog.php
</edit file>

<search for>
* Software Version:           SMF 1.1                                             *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
	// If we have no search, a broken search, or a new search - then create a new array.
	if (!isset($search_params['string']) || (!empty($_REQUEST['search']) && $search_params['string'] != $_REQUEST['search']))
	{
		// This array houses all the valid search types.
		$searchTypes = array(
			'action' => array('sql' => 'lm.action', 'label' => $txt['modlog_action']),
			'member' => array('sql' => 'mem.realName', 'label' => $txt['modlog_member']),
			'group' => array('sql' => 'mg.groupName', 'label' => $txt['modlog_position']),
			'ip' => array('sql' => 'lm.ip', 'label' => $txt['modlog_ip'])
		);

		$search_params = array(
			'string' => empty($_REQUEST['search']) ? '' : $_REQUEST['search'],
			'type' => isset($_REQUEST['search_type']) && isset($searchTypes[$_REQUEST['search_type']]) ? $_REQUEST['search_type'] : isset($searchTypes[$context['order']]) ? $context['order'] : 'member',
			'type_sql' => isset($_REQUEST['search_type']) && isset($searchTypes[$_REQUEST['search_type']]) ? $searchTypes[$_REQUEST['search_type']]['sql'] : isset($searchTypes[$context['order']]) ? $context['columns'][$context['order']]['sql'] : 'mem.realName',
			'type_label' => isset($_REQUEST['search_type']) && isset($searchTypes[$_REQUEST['search_type']]) ? $searchTypes[$_REQUEST['search_type']]['label'] : isset($searchTypes[$context['order']]) ? $context['columns'][$context['order']]['label'] : $txt['modlog_member'],
		);
	}
</search for>

<replace>
	// This array houses all the valid search types.
	$searchTypes = array(
		'action' => array('sql' => 'lm.action', 'label' => $txt['modlog_action']),
		'member' => array('sql' => 'mem.realName', 'label' => $txt['modlog_member']),
		'group' => array('sql' => 'mg.groupName', 'label' => $txt['modlog_position']),
		'ip' => array('sql' => 'lm.ip', 'label' => $txt['modlog_ip'])
	);

	if (!isset($search_params['string']) || (!empty($_REQUEST['search']) && $search_params['string'] != $_REQUEST['search']))
		$search_params_string = empty($_REQUEST['search']) ? '' : $_REQUEST['search'];
	else
		$search_params_string = $search_params['string'];

	if (isset($_REQUEST['search_type']) || empty($search_params['type']) || !isset($searchTypes[$search_params['type']]))
		$search_params_type = isset($_REQUEST['search_type']) && isset($searchTypes[$_REQUEST['search_type']]) ? $_REQUEST['search_type'] : (isset($searchTypes[$context['order']]) ? $context['order'] : 'member');
	else
		$search_params_type = $search_params['type'];

	$search_params_column = $searchTypes[$search_params_type]['sql'];
	$search_params = array(
		'string' => $search_params_string,
		'type' => $search_params_type,
	);
</replace>


<search for>
		'label' => $search_params['type_label']
</search for>

<replace>
		'label' => $searchTypes[$search_params_type]['label'],
</replace>


<search for>
		WHERE INSTR($search_params[type_sql], '$search_params[string]')" : ''), __FILE__, __LINE__);
</search for>

<replace>
		WHERE INSTR($search_params_column, '$search_params[string]')" : ''), __FILE__, __LINE__);
</replace>


<search for>
		WHERE INSTR($search_params[type_sql], '$search_params[string]')" : '') . "
</search for>

<replace>
		WHERE INSTR($search_params_column, '$search_params[string]')" : '') . "
</replace>



<edit file>
$sourcedir/PackageGet.php
</edit file>

<search for>
* Software Version:           SMF 1.1.9                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
			'name' => $row['name'],
			'url' => $row['url'],
</search for>

<replace>
			'name' => htmlspecialchars($row['name']),
			'url' => htmlspecialchars($row['url']),
</replace>


<search for>
function PackageServerRemove()
{
	global $db_prefix;
</search for>

<replace>
function PackageServerRemove()
{
	global $db_prefix;

	checkSession('get');
</replace>



<edit file>
$sourcedir/Packages.php
</edit file>

<search for>
* Software Version:           SMF 1.1.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
	$_GET['package'] = preg_replace('~[\.]+~', '.', strtr($_GET['package'], '/', '_'));

	// Can't delete what's not there.
	if (file_exists($boarddir . '/Packages/' . $_GET['package']))
</search for>

<replace>
	$_GET['package'] = preg_replace('~[\.]+~', '.', strtr($_GET['package'], array('/' => '_', '\\' => '_')));

	// Can't delete what's not there.
	if (file_exists($boarddir . '/Packages/' . $_GET['package']) && (substr($_GET['package'], -4) == '.zip' || substr($_GET['package'], -4) == '.tgz' || substr($_GET['package'], -7) == '.tar.gz' || is_dir($boarddir . '/Packages/' . $_GET['package'])) && $_GET['package'] != 'backups' && substr($_GET['package'], 0, 1) != '.')
</replace>


<search for>
	if (isset($_POST['submit']))
	{
</search for>

<replace>
	if (isset($_POST['submit']))
	{
		checkSession('post');

</replace>



<edit file>
$sourcedir/Poll.php
</edit file>

<search for>
* Software Version:           SMF 1.1                                             *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
	elseif (!empty($row['changeVote']))
	{
</search for>

<replace>
	elseif (!empty($row['changeVote']))
	{
		checkSession('request');
</replace>


<search for>
	// Check permissions.
	if (!allowedTo('poll_remove_any'))
</search for>

<replace>
	checkSession('get');

	// Check permissions.
	if (!allowedTo('poll_remove_any'))
</replace>



<edit file>
$sourcedir/Post.php
</edit file>

<search for>
* Software Version:           SMF 1.1.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
						'name' => $row['filename'],
</search for>

<replace>
						'name' => htmlspecialchars($row['filename']),
</replace>


<search for>
					'name' => $attachment['filename'],
</search for>

<replace>
					'name' => htmlspecialchars($attachment['filename']),
</replace>



<edit file>
$sourcedir/Profile.php
</edit file>

<search for>
* Software Version:           SMF 1.1.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
		if (strlen($_POST['websiteUrl']) < 8)
</search for>

<replace>
		if (strlen($_POST['websiteUrl']) < 8 || (substr($_POST['websiteUrl'], 0, 7) !== 'http://' && substr($_POST['websiteUrl'], 0, 8) !== 'https://'))
</replace>


<search for>
			'id' => $set,
			'name' => $set_names[$i],
			'selected' => $set == $context['member']['smiley_set']['id']
		);

		if ($context['smiley_sets'][$i]['selected'])
			$context['member']['smiley_set']['name'] = $set_names[$i];
</search for>

<replace>
			'id' => htmlspecialchars($set),
			'name' => htmlspecialchars($set_names[$i]),
			'selected' => $set == $context['member']['smiley_set']['id']
		);

		if ($context['smiley_sets'][$i]['selected'])
			$context['member']['smiley_set']['name'] = htmlspecialchars($set_names[$i]);
</replace>



<edit file>
$sourcedir/SplitTopics.php
</edit file>

<search for>
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>

	// Handle URLs from MergeIndex.
</search for>

<replace>
	checkSession('request');

	// Handle URLs from MergeIndex.
</replace>



<edit file>
$sourcedir/Subs.php
</edit file>

<search for>
* Software Version:           SMF 1.1.9                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
				$data = preg_replace('~&lt;a\s+href=(?:&quot;)?((?:http://|ftp://|https://|ftps://|mailto:).+?)(?:&quot;)?&gt;~i', '[url=$1]', $data);
</search for>

<replace>
				$data = preg_replace('~&lt;a\s+href=((?:&quot;)?)((?:https?://|ftps?://|mailto:)\S+?)\\1&gt;~i', '[url=$2]', $data);
</replace>


<search for>
				preg_match_all('~&lt;img\s+src=(?:&quot;)?((?:http://|ftp://|https://|ftps://).+?)(?:&quot;)?(?:\s+alt=(?:&quot;)?(.*?)(?:&quot;)?)?(?:\s?/)?&gt;~i', $data, $matches, PREG_PATTERN_ORDER);
</search for>

<replace>
				preg_match_all('~&lt;img\s+src=((?:&quot;)?)((?:https?://|ftps?://)\S+?)\\1(?:\s+alt=(&quot;.*?&quot;|\S*?))?(?:\s?/)?&gt;~i', $data, $matches, PREG_PATTERN_ORDER);
</replace>


<search for>
					foreach ($matches[1] as $match => $imgtag)
					{
						// No alt?
						if (!isset($matches[2][$match]))
							$matches[2][$match] = '';
</search for>

<replace>
					foreach ($matches[2] as $match => $imgtag)
					{
						$alt = empty($matches[3][$match]) ? '' : ' alt=' . preg_replace('~^&quot;|&quot;$~', '', $matches[3][$match]);
</replace>


<search for>
							$replaces[$matches[0][$match]] = '<img src="' . $imgtag . '" width="' . $width . '" height="' . $height . '" alt="' . $matches[2][$match] . '" border="0" />';
						}
						else
							$replaces[$matches[0][$match]] = '<img src="' . $imgtag . '" alt="' . $matches[2][$match] . '" border="0" />';
</search for>

<replace>
							$replaces[$matches[0][$match]] = '[img width=' . $width . ' height=' . $height . $alt . ']' . $imgtag . '[/img]';
						}
						else
							$replaces[$matches[0][$match]] = '[img' . $alt . ']' . $imgtag . '[/img]';
</replace>


<search for>
			$smileytocache[] = '<img src="' . $modSettings['smileys_url'] . '/' . $user_info['smiley_set'] . '/' . $smileysto[$i] . '" alt="' . strtr(htmlspecialchars($smileysdescs[$i]), array(':' => '&#58;', '(' => '&#40;', ')' => '&#41;', '$' => '&#36;', '[' => '&#091;')) . '" border="0" />';
</search for>

<replace>
			$smileytocache[] = '<img src="' . htmlspecialchars($modSettings['smileys_url'] . '/' . $user_info['smiley_set'] . '/' . $smileysto[$i]) . '" alt="' . strtr(htmlspecialchars($smileysdescs[$i]), array(':' => '&#58;', '(' => '&#40;', ')' => '&#41;', '$' => '&#36;', '[' => '&#091;')) . '" border="0" />';
</replace>



<edit file>
$sourcedir/Subs-Auth.php
</edit file>

<search for>
* Software Version:           SMF 1.1.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
				$context['get_data'] .= $k . '=' . $v . ';';
			// If it changed, put it out there, but with an ampersand.
			elseif ($temp[$k] != $_GET[$k])
				$context['get_data'] .= $k . '=' . $v . '&amp;';
</search for>

<replace>
				$context['get_data'] .= urlencode($k) . '=' . urlencode($v) . ';';
			// If it changed, put it out there, but with an ampersand.
			elseif ($temp[$k] != $_GET[$k])
				$context['get_data'] .= urlencode($k) . '=' . urlencode($v) . '&amp;';
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
<input type="hidden" name="' . $k . '" value="' . strtr(stripslashes($v), array('"' => '&quot;', '<' => '&lt;', '>' => '&gt;')) . '" />';
</search for>

<replace>
<input type="hidden" name="' . htmlspecialchars($k) . '" value="' . strtr(stripslashes($v), array('"' => '&quot;', '<' => '&lt;', '>' => '&gt;')) . '" />';
</replace>



<edit file>
$sourcedir/Subs-Boards.php
</edit file>

<search for>
* Software Version:           SMF 1.1.5                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
	$_REQUEST['c'] = (int) $_REQUEST['c'];
</search for>

<replace>
	checkSession('request');

	$_REQUEST['c'] = (int) $_REQUEST['c'];
</replace>



<edit file>
$sourcedir/Subs-Graphics.php
</edit file>

<search for>
* Software Version:           SMF 1.1.9                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
	// Ask for more memory: we need it for this, and it'll only happen once!
	@ini_set('memory_limit', '48M');
</search for>

<replace>
	// Ask for more memory: we need it for this, and it'll only happen once!
	@ini_set('memory_limit', '90M');
</replace>



<edit file>
$sourcedir/Themes.php
</edit file>

<search for>
* Software Version:           SMF 1.1.7                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.11                                          *
</replace>


<search for>
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
		$context['smiley_sets'][$set] = $set_names[$i];
</search for>

<replace>
		$context['smiley_sets'][$set] = htmlspecialchars($set_names[$i]);
</replace>



<edit file>
$themedir/Packages.template.php
</edit file>

<search for>
// Version: 1.1.8; Packages
</search for>

<replace>
// Version: 1.1.11; Packages
</replace>


<search for>
								<a href="' . $scripturl . '?action=packageget;sa=remove;server=' . $server['id'] . '">[ ' . $txt['smf138'] . ' ]</a>
</search for>

<replace>
								<a href="' . $scripturl . '?action=packageget;sa=remove;server=' . $server['id'] . ';sesc=', $context['session_id'], '">[ ' . $txt['smf138'] . ' ]</a>
</replace>



<edit file>
$themedir/SplitTopics.template.php
</edit file>

<search for>
// Version: 1.1; SplitTopics
</search for>

<replace>
// Version: 1.1.11; SplitTopics
</replace>


<search for>
						<input type="hidden" name="sa" value="execute" />
</search for>

<replace>
						<input type="hidden" name="sa" value="execute" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
</replace>

