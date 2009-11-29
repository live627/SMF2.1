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
</replace>


<search for>
	// Check input after a member search has been submitted.
	if ($context['sub_action'] == 'query' && empty($_REQUEST['params']))
</search for>

<replace>
	// Check input after a member search has been submitted.
	if ($context['sub_action'] == 'query')
</replace>


<search for>
	}
	// If the query information was already packed in the URL, decode it.
	// !!! Change this.
</search for>

<replace>

		$search_params = base64_encode(serialize(stripslashes__recursive($_POST)));
	}
	// If the query information was already packed in the URL, decode it.
	// !!! Change this.
</replace>


<search for>
	// If the query information was already packed in the URL, decode it.
	// !!! Change this.
	elseif ($context['sub_action'] == 'query')
		$where = base64_decode(strtr($_REQUEST['params'], array(' ' => '+')));
</search for>

<replace>
	// Check input after a member search has been submitted.
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
</search for>

<replace>

	// This array houses all the valid search types.
	$searchTypes = array(
		'action' => array('sql' => 'lm.action', 'label' => $txt['modlog_action']),
		'member' => array('sql' => 'mem.realName', 'label' => $txt['modlog_member']),
		'group' => array('sql' => 'mg.groupName', 'label' => $txt['modlog_position']),
		'ip' => array('sql' => 'lm.ip', 'label' => $txt['modlog_ip'])
	);

</replace>


<search for>
	{
		// This array houses all the valid search types.
		$searchTypes = array(
			'action' => array('sql' => 'lm.action', 'label' => $txt['modlog_action']),
			'member' => array('sql' => 'mem.realName', 'label' => $txt['modlog_member']),
			'group' => array('sql' => 'mg.groupName', 'label' => $txt['modlog_position']),
			'ip' => array('sql' => 'lm.ip', 'label' => $txt['modlog_ip'])
		);
</search for>

<replace>

		$search_params_string = empty($_REQUEST['search']) ? '' : $_REQUEST['search'];
	else
		$search_params_string = $search_params['string'];
</replace>


<search for>
		$search_params = array(
			'string' => empty($_REQUEST['search']) ? '' : $_REQUEST['search'],
			'type' => isset($_REQUEST['search_type']) && isset($searchTypes[$_REQUEST['search_type']]) ? $_REQUEST['search_type'] : isset($searchTypes[$context['order']]) ? $context['order'] : 'member',
			'type_sql' => isset($_REQUEST['search_type']) && isset($searchTypes[$_REQUEST['search_type']]) ? $searchTypes[$_REQUEST['search_type']]['sql'] : isset($searchTypes[$context['order']]) ? $context['columns'][$context['order']]['sql'] : 'mem.realName',
			'type_label' => isset($_REQUEST['search_type']) && isset($searchTypes[$_REQUEST['search_type']]) ? $searchTypes[$_REQUEST['search_type']]['label'] : isset($searchTypes[$context['order']]) ? $context['columns'][$context['order']]['label'] : $txt['modlog_member'],
		);
	}
</search for>

<replace>

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
		'type' => $search_params['type'],
		'label' => $search_params['type_label']
</search for>

<replace>
		'type' => $search_params['type'],
		'label' => $searchTypes[$search_params_type]['label'],
</replace>


<search for>
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))" . (!empty($search_params['string']) ? "
		WHERE INSTR($search_params[type_sql], '$search_params[string]')" : ''), __FILE__, __LINE__);
</search for>

<replace>
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))" . (!empty($search_params['string']) ? "
		WHERE INSTR($search_params_column, '$search_params[string]')" : ''), __FILE__, __LINE__);
</replace>


<search for>
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))" . (!empty($search_params['string']) ? "
		WHERE INSTR($search_params[type_sql], '$search_params[string]')" : '') . "
</search for>

<replace>
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))" . (!empty($search_params['string']) ? "
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

