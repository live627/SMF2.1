<?php
/**********************************************************************************
* Modlog.php                                                                      *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Alpha                                       *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/*	The moderation log is this file's only job.  It views it, and that's about
	all it does.

	void ViewModlog()
		- prepares the information from the moderation log for viewing.
		- disallows the deletion of events within twenty-four hours of now.
		- requires the admin_forum permission.
		- uses the Modlog template, main sub template.
		- is accessed via ?action=moderate;area=modlog.
		
	void getModLogEntries($search_param = '', $order= '', $limit = 0)
		- Gets the moderation log entries that match the specified paramaters
		- limit can be an array with two values
		- search_param and order should be proper SQL strings or blank.  If blank they are not used.
*/

// Show the moderation log
function ViewModlog()
{
	global $db_prefix, $txt, $modSettings, $context, $scripturl, $sourcedir, $user_info, $smfFunc;

	$context['can_delete'] = allowedTo('admin_forum');
	$user_info['modlog_query'] = $context['can_delete'] ? '1=1' : (empty($user_info['mod_cache']['bq']) ? 'lm.id_action=0' : 'lm.' . $user_info['mod_cache']['bq']);

	loadTemplate('Modlog');

	$context['page_title'] = $txt['modlog_view'];

	// The number of entries to show per page of log file.
	$context['displaypage'] = 30;
	// Amount of hours that must pass before allowed to delete file.
	$context['hoursdisable'] = 24;

	// Handle deletion...
	if (isset($_POST['removeall']) && $context['can_delete'])
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_actions
			WHERE log_time < " . (time() - $context['hoursdisable'] * 3600), __FILE__, __LINE__);
	elseif (!empty($_POST['remove']) && isset($_POST['delete']) && $context['can_delete'])
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_actions
			WHERE id_action IN ('" . implode("', '", array_unique($_POST['delete'])) . "')
				AND log_time < " . (time() - $context['hoursdisable'] * 3600), __FILE__, __LINE__);

	// Pass order and direction variables to template so they can be used after a remove command.
	$context['dir'] = isset($_REQUEST['d']) ? ';d' : '';
	$context['sort_direction'] = !isset($_REQUEST['d']) ? 'down' : 'up';

	// Do the column stuff!
	$context['columns'] = array(
		'action' => array('sql' => 'lm.action', 'label' => $txt['modlog_action']),
		'time' => array('sql' => 'lm.log_time', 'label' => $txt['modlog_date']),
		'member' => array('sql' => 'mem.real_name', 'label' => $txt['modlog_member']),
		'group' => array('sql' => 'mg.group_name', 'label' => $txt['modlog_position']),
		'ip' => array('sql' => 'lm.ip', 'label' => $txt['modlog_ip'])
	);

	// Setup the direction stuff...
	$context['order'] = isset($_REQUEST['order']) && isset($context['columns'][$_REQUEST['order']]) ? $_REQUEST['order'] : 'time';
	$orderType = $context['columns'][$context['order']]['sql'];

	// If we're coming from a search, get the variables.
	if (isset($_REQUEST['params']))
	{
		$search_params = base64_decode(strtr($_REQUEST['params'], array(' ' => '+')));
		$search_params = @unserialize($search_params);

		// To be sure, let's slash all the elements.
		foreach ($search_params as $key => $value)
			$search_params[$key] = $smfFunc['db_escape_string']($value);
	}

	// If we have no search, a broken search, or a new search - then create a new array.
	if (!isset($search_params['string']) || (!empty($_REQUEST['search']) && $search_params['string'] != $_REQUEST['search']))
	{
		// This array houses all the valid search types.
		$searchTypes = array(
			'action' => array('sql' => 'lm.action', 'label' => $txt['modlog_action']),
			'member' => array('sql' => 'mem.real_name', 'label' => $txt['modlog_member']),
			'group' => array('sql' => 'mg.group_name', 'label' => $txt['modlog_position']),
			'ip' => array('sql' => 'lm.ip', 'label' => $txt['modlog_ip'])
		);

		$search_params = array(
			'string' => empty($_REQUEST['search']) ? '' : $_REQUEST['search'],
			'type' => isset($_REQUEST['search_type']) && isset($searchTypes[$_REQUEST['search_type']]) ? $_REQUEST['search_type'] : isset($searchTypes[$context['order']]) ? $context['order'] : 'member',
			'type_sql' => isset($_REQUEST['search_type']) && isset($searchTypes[$_REQUEST['search_type']]) ? $searchTypes[$_REQUEST['search_type']]['sql'] : isset($searchTypes[$context['order']]) ? $context['columns'][$context['order']]['sql'] : 'mem.real_name',
			'type_label' => isset($_REQUEST['search_type']) && isset($searchTypes[$_REQUEST['search_type']]) ? $searchTypes[$_REQUEST['search_type']]['label'] : isset($searchTypes[$context['order']]) ? $context['columns'][$context['order']]['label'] : $txt['modlog_member'],
		);
	}

	// Setup the search context.
	$context['search_params'] = empty($search_params['string']) ? '' : base64_encode(serialize($search_params));
	$context['search'] = array(
		'string' => $smfFunc['db_unescape_string']($search_params['string']),
		'type' => $search_params['type'],
		'label' => $search_params['type_label']
	);

	// Provide extra information about each column - the link, whether it's selected, etc.
	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=moderate;area=modlog;order=' . $col . ';start=0' . (empty($context['search_params']) ? '' : ';params=' . $context['search_params']);
		if (!isset($_REQUEST['d']) && $col == $context['order'])
			$context['columns'][$col]['href'] .= ';d';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $context['order'] == $col;
	}

	// This text array holds all the formatting for the supported reporting type.
	$descriptions = array(
		'approve' => $txt['modlog_ac_approved'],
		'approve_topic' => $txt['modlog_ac_approved_topic'],
		'lock' => $txt['modlog_ac_locked'],
		'sticky' => $txt['modlog_ac_stickied'],
		'modify' => $txt['modlog_ac_modified'],
		'merge' => $txt['modlog_ac_merged'],
		'split' => $txt['modlog_ac_split'],
		'move' => $txt['modlog_ac_moved'],
		'remove' => $txt['modlog_ac_removed'],
		'delete' => $txt['modlog_ac_deleted'],
		'delete_member' => $txt['modlog_ac_deleted_member'],
		'ban' => $txt['modlog_ac_banned'],
		'news' => $txt['modlog_ac_news'],
		'profile' => $txt['modlog_ac_profile'],
		'pruned' => $txt['modlog_ac_pruned'],
	);

	// If they are searching by action, then we must do some manual intervention to search in their language!
	if ($search_params['type'] == 'action' && !empty($search_params['string']))
	{
		// For the moment they can only search for ONE action!
		foreach ($descriptions as $key => $text)
		{
			if (strpos($text, $search_params['string']) !== false)
			{
				$search_params['string'] = $key;
				break;
			}
		}
	}

	// Count the amount of entries in total for pagination.
	$result = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}log_actions AS lm
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lm.id_member)
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = 0 THEN mem.id_post_group ELSE mem.id_group END)
		WHERE" . (!empty($search_params['string']) ? " INSTR($search_params[type_sql], '$search_params[string]')
			AND" : '') . " $user_info[modlog_query]", __FILE__, __LINE__);
	list ($context['entry_count']) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

	// Create the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=moderate;area=modlog;order=' . $context['order'] . $context['dir'] . (!empty($context['search_params']) ? ';params=' . $context['search_params'] : ''), $_REQUEST['start'], $context['entry_count'], $context['displaypage']);
	$context['start'] = $_REQUEST['start'];

	getModLogEntries((!empty($search_params['string']) ? " INSTR($search_params[type_sql], '$search_params[string]') AND " : '') . $user_info['modlog_query'], $orderType . (isset($_REQUEST['d']) ? '' : ' DESC'), array($context['start'], $context['displaypage']));
}


function getModLogEntries($search_param = '', $order= '', $limit = 0)
{
	global $db_prefix, $context, $scripturl, $txt, $smfFunc;

	// Construct our limit.
	if (empty($limit))
		$limit = '';
	else
	{
		$limit = 'LIMIT ' . (is_array($limit) ? implode(',', $limit) : $limit);
	}

	// Do a little bit of self protection.
	if (!isset($context['hoursdisable']))
		$context['hoursdisable'] = 24;

	// Can they see the IP address?
	$seeIP = allowedTo('moderate_forum');
	
	// Here we have the query getting the log details.
	$result = $smfFunc['db_query']('', "
		SELECT
			lm.id_action, lm.id_member, lm.ip, lm.log_time, lm.action, lm.id_board, lm.id_topic, lm.id_msg, lm.extra,
			mem.real_name, mg.group_name
		FROM {$db_prefix}log_actions AS lm
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lm.id_member)
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = 0 THEN mem.id_post_group ELSE mem.id_group END)" . (!empty($search_param) ? '
		WHERE ' . $search_param : '') . (!empty($order) ? '
		ORDER BY ' . $order : '') . "
		$limit", __FILE__, __LINE__);

	// Arrays for decoding objects into.
	$topics = array();
	$boards = array();
	$members = array();
	$context['entries'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		$row['extra'] = unserialize($row['extra']);

		// Corrupt?
		$row['extra'] = is_array($row['extra']) ? $row['extra'] : array();

		// Add on some of the column stuff info
		if (!empty($row['id_board']))
		{
			if ($row['action'] == 'move')
				$row['extra']['board_to'] = $row['id_board'];
			else
				$row['extra']['board'] = $row['id_board'];
		}

		if (!empty($row['id_topic']))
			$row['extra']['topic'] = $row['id_topic'];
		if (!empty($row['id_msg']))
			$row['extra']['message'] = $row['id_msg'];

		// Is this associated with a topic?
		if (isset($row['extra']['topic']))
			$topics[(int) $row['extra']['topic']][] = $row['id_action'];
		if (isset($row['extra']['new_topic']))
			$topics[(int) $row['extra']['new_topic']][] = $row['id_action'];

		// How about a member?
		if (isset($row['extra']['member']))
			$members[(int) $row['extra']['member']][] = $row['id_action'];

		// Associated with a board?
		if (isset($row['extra']['board_to']))
			$boards[(int) $row['extra']['board_to']][] = $row['id_action'];
		if (isset($row['extra']['board_from']))
			$boards[(int) $row['extra']['board_from']][] = $row['id_action'];
		if (isset($row['extra']['board']))
			$boards[(int) $row['extra']['board']][] = $row['id_action'];

		// IP Info?
		if (isset($row['extra']['ip_range']))
			if ($seeIP)
				$row['extra']['ip_range'] = '<a href="' . $scripturl . '?action=trackip;searchip=' . $row['extra']['ip_range'] . '">' . $row['extra']['ip_range'] . '</a>';
			else
				$row['extra']['ip_range'] = $txt['logged'];

		// Email?
		if (isset($row['extra']['email']))
			$row['extra']['email'] = '<a href="mailto:' . $row['extra']['email'] . '">' . $row['extra']['email'] . '</a>';

		// The array to go to the template. Note here that action is set to a "default" value of the action doesn't match anything in the descriptions. Allows easy adding of logging events with basic details.
		$context['entries'][$row['id_action']] = array(
			'id' => $row['id_action'],
			'ip' => $seeIP ? $row['ip'] : $txt['logged'],
			'position' => $row['group_name'],
			'moderator' => array(
				'id' => $row['id_member'],
				'name' => $row['real_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'
			),
			'time' => timeformat($row['log_time']),
			'timestamp' => forum_time(true, $row['log_time']),
			'editable' => time() > $row['log_time'] + $context['hoursdisable'] * 3600,
			'extra' => $row['extra'],
			'action' => isset($descriptions[$row['action']]) ? $descriptions[$row['action']] : $row['action'],
		);
	}
	$smfFunc['db_free_result']($result);

	if (!empty($boards))
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_board, name
			FROM {$db_prefix}boards
			WHERE id_board IN (" . implode(', ', array_keys($boards)) . ")
			LIMIT " . count(array_keys($boards)), __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			foreach ($boards[$row['id_board']] as $action)
			{
				// Make the board number into a link - dealing with moving too.
				if (isset($context['entries'][$action]['extra']['board_to']) && $context['entries'][$action]['extra']['board_to'] == $row['id_board'])
					$context['entries'][$action]['extra']['board_to'] = '<a href="' . $scripturl . '?board=' . $row['id_board'] . '">' . $row['name'] . '</a>';
				elseif (isset($context['entries'][$action]['extra']['board_from']) && $context['entries'][$action]['extra']['board_from'] == $row['id_board'])
					$context['entries'][$action]['extra']['board_from'] = '<a href="' . $scripturl . '?board=' . $row['id_board'] . '">' . $row['name'] . '</a>';
				elseif (isset($context['entries'][$action]['extra']['board']) && $context['entries'][$action]['extra']['board'] == $row['id_board'])
					$context['entries'][$action]['extra']['board'] = '<a href="' . $scripturl . '?board=' . $row['id_board'] . '">' . $row['name'] . '</a>';
			}
		}
		$smfFunc['db_free_result']($request);
	}

	if (!empty($topics))
	{
		$request = $smfFunc['db_query']('', "
			SELECT ms.subject, t.id_topic
			FROM {$db_prefix}topics AS t
				INNER JOIN {$db_prefix}messages AS ms ON (ms.id_msg = t.id_first_msg)
			WHERE t.id_topic IN (" . implode(', ', array_keys($topics)) . ")
			LIMIT " . count(array_keys($topics)), __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			foreach ($topics[$row['id_topic']] as $action)
			{
				$this_action = &$context['entries'][$action];

				// This isn't used in the current theme.
				$this_action['topic'] = array(
					'id' => $row['id_topic'],
					'subject' => $row['subject'],
					'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
					'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['subject'] . '</a>'
				);

				// Make the topic number into a link - dealing with splitting too.
				if (isset($this_action['extra']['topic']) && $this_action['extra']['topic'] == $row['id_topic'])
					$this_action['extra']['topic'] = '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.' . (isset($this_action['extra']['message']) ? 'msg' . $this_action['extra']['message'] . '#msg' . $this_action['extra']['message'] : '0') . '">' . $row['subject'] . '</a>';
				elseif (isset($this_action['extra']['new_topic']) && $this_action['extra']['new_topic'] == $row['id_topic'])
					$this_action['extra']['new_topic'] = '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.' . (isset($this_action['extra']['message']) ? 'msg' . $this_action['extra']['message'] . '#msg' . $this_action['extra']['message'] : '0') . '">' . $row['subject'] . '</a>';
			}
		}
		$smfFunc['db_free_result']($request);
	}

	if (!empty($members))
	{
		$request = $smfFunc['db_query']('', "
			SELECT real_name, id_member
			FROM {$db_prefix}members
			WHERE id_member IN (" . implode(', ', array_keys($members)) . ")
			LIMIT " . count(array_keys($members)), __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			foreach ($members[$row['id_member']] as $action)
			{
				// Not used currently.
				$context['entries'][$action]['member'] = array(
					'id' => $row['id_member'],
					'name' => $row['real_name'],
					'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'
				);
				// Make the member number into a name.
				$context['entries'][$action]['extra']['member'] = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';
			}
		}
		$smfFunc['db_free_result']($request);
	}
	
	// Make any message info links so its easier to go find that message
	foreach($context['entries'] AS $k => $entry)
		if (isset($entry['extra']['message']))
			$context['entries'][$k]['extra']['message'] = '<a href="' . $scripturl . '?msg=' . $entry['extra']['message'] . '">' . $entry['extra']['message'] . '</a>';
}

?>