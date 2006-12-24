<?php
/**********************************************************************************
* PersonalMessage.php                                                             *
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

/*	This file is mainly meant for viewing personal messages.  It also sends,
	deletes, and marks personal messages.  For compatibility reasons, they are
	often called "instant messages".  The following functions are used:

	void MessageMain()
		// !!! ?action=pm

	void messageIndexBar(string area)
		// !!!

	void MessageFolder()
		// !!! ?action=pm;sa=folder

	void prepareMessageContext(type reset = 'subject', bool reset = false)
		// !!!

	void MessageSearch()
		// !!!

	void MessageSearch2()
		// !!!

	void MessagePost()
		// !!! ?action=pm;sa=post

	void messagePostError(array error_types, string to, string bcc)
		// !!!

	void MessagePost2()
		// !!! ?action=pm;sa=post2

	void WirelessAddBuddy()
		// !!!

	void MessageActionsApply()
		// !!! ?action=pm;sa=pmactions

	void MessageKillAllQuery()
		// !!! ?action=pm;sa=killall

	void MessageKillAll()
		// !!! ?action=pm;sa=killall2

	void MessagePrune()
		// !!! ?action=pm;sa=prune

	void deleteMessages(array personal_messages, string folder,
			int owner = user)
		// !!!

	void markMessages(array personal_messages = all, int label = all,
			int owner = user)
		- marks the specified personal_messages read.
		- if label is set, only marks messages with that label.
		- if owner is set, marks messages owned by that member id.

	void ManageLabels()
		// !!!

	void MessageSettings()
		// !!!

	void ReportMessage()
		- allows the user to report a personal message to an administrator.
		- in the first instance requires that the ID of the message to report
		  is passed through $_GET.
		- allows the user to report to either a particular administrator - or
		  the whole admin team.
		- will forward on a copy of the original message without allowing the
		  reporter to make changes.
		- uses the report_message sub-template.

	void ManageRules()
		// !!!

	void LoadRules()
		// !!!

	void ApplyRules()
		// !!!
*/

// This helps organize things...
function MessageMain()
{
	global $txt, $scripturl, $sourcedir, $context, $user_info, $user_settings, $db_prefix, $smfFunc;

	// No guests!
	is_not_guest();

	// You're not supposed to be here at all, if you can't even read PMs.
	isAllowedTo('pm_read');

	// This file contains the basic functions for sending a PM.
	require_once($sourcedir . '/Subs-Post.php');

	if (loadLanguage('PersonalMessage', '', false) === false)
		loadLanguage('InstantMessage');
	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_pm';
	else
	{
		if (loadTemplate('PersonalMessage', false) === false)
			loadTemplate('InstantMessage');
	}

	// Load up the members maximum message capacity.
	if (!$user_info['is_admin'] && ($context['message_limit'] = cache_get_data('msgLimit:' . $user_info['id'], 360)) === null)
	{
		echo 'ahhshshs';
		// !!! Why do we do this?  It seems like if they have any limit we should use it.
		$request = $smfFunc['db_query']('', "
			SELECT MAX(max_messages) AS topLimit, MIN(max_messages) AS bottomLimit
			FROM {$db_prefix}membergroups
			WHERE id_group IN (" . implode(', ', $user_info['groups']) . ')', __FILE__, __LINE__);
		list ($maxMessage, $minMessage) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		$context['message_limit'] = $minMessage == 0 ? 0 : $maxMessage;

		// Save us doing it again!
		cache_put_data('msgLimit:' . $user_info['id'], $context['message_limit'], 360);
	}
	else
		$context['message_limit'] = 0;

	// Prepare the context for the capacity bar.
	if (!empty($context['message_limit']))
	{
		$bar = ($user_info['messages'] * 100) / $context['message_limit'];

		$context['limit_bar'] = array(
			'messages' => $user_info['messages'],
			'allowed' => $context['message_limit'],
			'percent' => $bar,
			'bar' => min(100, (int) $bar),
			'text' => sprintf($txt['pm_currently_using'], $user_info['messages'], round($bar, 1)),
		);
	}

	// Now we have the labels, and assuming we have unsorted mail, apply our rules!
	if ($user_settings['new_pm'])
	{
		$context['labels'] = $user_settings['message_labels'] == '' ? array() : explode(',', $user_settings['message_labels']);
		foreach ($context['labels'] as $k => $v)
			$context['labels'][(int) $k] = array('id' => $k, 'name' => trim($v), 'messages' => 0, 'unread_messages' => 0);
		$context['labels'][-1] = array('id' => -1, 'name' => $txt['pm_msg_label_inbox'], 'messages' => 0, 'unread_messages' => 0);

		ApplyRules();
		updateMemberData($user_info['id'], array('new_pm' => 0));
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}pm_recipients
			SET is_new = 0
			WHERE id_member = $user_info[id]", __FILE__, __LINE__);
	}

	// Load the label data.
	if ($user_settings['new_pm'] || ($context['labels'] = cache_get_data('labelCounts:' . $user_info['id'], 720)) === null)
	{
		$context['labels'] = $user_settings['message_labels'] == '' ? array() : explode(',', $user_settings['message_labels']);
		foreach ($context['labels'] as $k => $v)
			$context['labels'][(int) $k] = array('id' => $k, 'name' => trim($v), 'messages' => 0, 'unread_messages' => 0);
		$context['labels'][-1] = array('id' => -1, 'name' => $txt['pm_msg_label_inbox'], 'messages' => 0, 'unread_messages' => 0);

		// Looks like we need to reseek!
		$result = $smfFunc['db_query']('', "
			SELECT labels, is_read, COUNT(*) AS num
			FROM {$db_prefix}pm_recipients
			WHERE id_member = $user_info[id]
			GROUP BY labels, is_read", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($result))
		{
			$this_labels = explode(',', $row['labels']);
			foreach ($this_labels as $this_label)
			{
				$context['labels'][(int) $this_label]['messages'] += $row['num'];
				if (!($row['is_read'] & 1))
					$context['labels'][(int) $this_label]['unread_messages'] += $row['num'];
			}
		}
		$smfFunc['db_free_result']($result);

		// Store it please!
		cache_put_data('labelCounts:' . $user_info['id'], $context['labels'], 720);
	}

	// This determines if we have more labels than just the standard inbox.
	$context['currently_using_labels'] = count($context['labels']) > 1 ? 1 : 0;

	// Some stuff for the labels...
	$context['current_label_id'] = isset($_REQUEST['l']) && isset($context['labels'][(int) $_REQUEST['l']]) ? (int) $_REQUEST['l'] : -1;
	$context['current_label'] = &$context['labels'][(int) $context['current_label_id']]['name'];
	$context['folder'] = !isset($_REQUEST['f']) || $_REQUEST['f'] != 'sent' ? 'inbox' : 'sent';

	// This is convenient.  Do you know how annoying it is to do this every time?!
	$context['current_label_redirect'] = 'action=pm;f=' . $context['folder'] . (isset($_GET['start']) ? ';start=' . $_GET['start'] : '') . (isset($_REQUEST['l']) ? ';l=' . $_REQUEST['l'] : '');

	// Build the linktree for all the actions...
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=pm',
		'name' => $txt['personal_messages']
	);

	// Preferences...
	$context['display_mode'] = $user_settings['pm_prefs'] & 3;

	$subActions = array(
		'addbuddy' => 'WirelessAddBuddy',
		'manlabels' => 'ManageLabels',
		'manrules' => 'ManageRules',
		'pmactions' => 'MessageActionsApply',
		'prune' => 'MessagePrune',
		'removeall' => 'MessageKillAllQuery',
		'removeall2' => 'MessageKillAll',
		'report' => 'ReportMessage',
		'search' => 'MessageSearch',
		'search2' => 'MessageSearch2',
		'send' => 'MessagePost',
		'send2' => 'MessagePost2',
		'settings' => 'MessageSettings',
	);

	if (!isset($_REQUEST['sa']) || !isset($subActions[$_REQUEST['sa']]))
		MessageFolder();
	else
	{
		messageIndexBar($_REQUEST['sa']);
		$subActions[$_REQUEST['sa']]();
	}
}

// A sidebar to easily access different areas of the section
function messageIndexBar($area)
{
	global $txt, $context, $scripturl, $sc, $modSettings, $settings, $user_info;

	$context['pm_areas'] = array(
		'folders' => array(
			'title' => $txt['pm_messages'],
			'areas' => array(
				'send' => array('link' => '<a href="' . $scripturl . '?action=pm;sa=send">' . $txt['new_message'] . '</a>', 'href' => $scripturl . '?action=pm;sa=send'),
				'' => array(),
				'inbox' => array('link' => '<a href="' . $scripturl . '?action=pm">' . $txt['inbox'] . '</a>', 'href' => $scripturl . '?action=pm'),
				'sent' => array('link' => '<a href="' . $scripturl . '?action=pm;f=sent">' . $txt['sent_items'] . '</a>', 'href' => $scripturl . '?action=pm;f=sent'),
			),
		),
		'labels' => array(
			'title' => $txt['pm_labels'],
			'areas' => array(),
		),
		'actions' => array(
			'title' => $txt['pm_actions'],
			'areas' => array(
				'search' => array('link' => '<a href="' . $scripturl . '?action=pm;sa=search">' . $txt['pm_search_bar_title'] . '</a>', 'href' => $scripturl . '?action=pm;sa=search'),
				'prune' => array('link' => '<a href="' . $scripturl . '?action=pm;sa=prune">' . $txt['pm_prune'] . '</a>', 'href' => $scripturl . '?action=pm;sa=prune'),
			),
		),
		'pref' => array(
			'title' => $txt['pm_preferences'],
			'areas' => array(
				'manlabels' => array('link' => '<a href="' . $scripturl . '?action=pm;sa=manlabels">' . $txt['pm_manage_labels'] . '</a>', 'href' => $scripturl . '?action=pm;sa=manlabels'),
				'manrules' => array('link' => '<a href="' . $scripturl . '?action=pm;sa=manrules">' . $txt['pm_manage_rules'] . '</a>', 'href' => $scripturl . '?action=pm;sa=manrules'),
				'settings' => array('link' => '<a href="' . $scripturl . '?action=pm;sa=settings">' . $txt['pm_settings'] . '</a>', 'href' => $scripturl . '?action=pm;sa=settings'),
			),
		),
	);

	// Handle labels.
	if (empty($context['currently_using_labels']))
		unset($context['pm_areas']['labels']);
	else
	{
		// Note we send labels by id as it will have less problems in the querystring.
		foreach ($context['labels'] as $label)
		{
			if ($label['id'] == -1)
				continue;
			$context['pm_areas']['labels']['areas']['label' . $label['id']] = array(
				'link' => '<a href="' . $scripturl . '?action=pm;l=' . $label['id'] . '">' . $label['name'] . '</a>',
				'href' => $scripturl . '?action=pm;l=' . $label['id'],
				'unread_messages' => &$context['labels'][(int) $label['id']]['unread_messages'],
				'messages' => &$context['labels'][(int) $label['id']]['messages'],
			);
		}
	}

	$context['pm_areas']['folders']['areas']['inbox']['unread_messages'] = &$context['labels'][-1]['unread_messages'];
	$context['pm_areas']['folders']['areas']['inbox']['messages'] = &$context['labels'][-1]['messages'];

	// Do we have a limit on the amount of messages we can keep?
	if (!empty($context['message_limit']))
	{
		$bar = round(($user_info['messages'] * 100) / $context['message_limit'], 1);

		$context['limit_bar'] = array(
			'messages' => $user_info['messages'],
			'allowed' => $context['message_limit'],
			'percent' => $bar,
			'bar' => $bar > 100 ? 100 : (int) $bar,
			'text' => sprintf($txt['pm_currently_using'], $user_info['messages'], $bar)
		);

		// Force it in to somewhere.
		$context['pm_areas']['pref']['areas']['limit_bar'] = array('limit_bar' => true);
	}

	// Where we are now.
	$context['pm_area'] = $area;

	// obExit will know what to do!
	if (!WIRELESS)
		$context['template_layers'][] = 'pm';
}

// A folder, ie. inbox/sent etc.
function MessageFolder()
{
	global $txt, $scripturl, $db_prefix, $modSettings, $context, $subjects_request;
	global $messages_request, $user_info, $recipients, $options, $smfFunc, $memberContext, $user_settings;

	// Changing view?
	if (isset($_GET['view']))
	{
		$context['display_mode'] = $context['display_mode'] > 1 ? 0 : $context['display_mode'] + 1;
		updateMemberData($user_info['id'], array('pm_prefs' => ($user_settings['pm_prefs'] & 252) | $context['display_mode']));
	}

	// Make sure the starting location is valid.
	if (isset($_GET['start']) && $_GET['start'] != 'new')
		$_GET['start'] = (int) $_GET['start'];
	elseif (!isset($_GET['start']) && !empty($options['view_newest_pm_first']))
		$_GET['start'] = 0;
	else
		$_GET['start'] = 'new';

	// Set up some basic theme stuff.
	$context['allow_hide_email'] = !empty($modSettings['allow_hide_email']);
	$context['from_or_to'] = $context['folder'] != 'sent' ? 'from' : 'to';
	$context['get_pmessage'] = 'prepareMessageContext';
	$context['signature_enabled'] = substr($modSettings['signature_settings'], 0, 1) == 1;

	$labelQuery = $context['folder'] != 'sent' ? "
			AND FIND_IN_SET('$context[current_label_id]', pmr.labels)" : '';

	// Set the index bar correct!
	messageIndexBar($context['current_label_id'] == -1 ? $context['folder'] : 'label' . $context['current_label_id']);

	// Sorting the folder.
	$sort_methods = array(
		'date' => 'pm.id_pm',
		'name' => "IFNULL(mem.real_name, '')",
		'subject' => 'pm.subject',
	);

	// They didn't pick one, use the forum default.
	if (!isset($_GET['sort']) || !isset($sort_methods[$_GET['sort']]))
	{
		$context['sort_by'] = 'date';
		$_GET['sort'] = 'pm.id_pm';
		$descending = false;
	}
	// Otherwise use the defaults: ascending, by date.
	else
	{
		$context['sort_by'] = $_GET['sort'];
		$_GET['sort'] = $sort_methods[$_GET['sort']];
		$descending = isset($_GET['desc']);
	}

	if (!empty($options['view_newest_pm_first']))
		$descending = !$descending;

	$context['sort_direction'] = $descending ? 'down' : 'up';

	// Why would you want access to your sent items if you're not allowed to send anything?
	if ($context['folder'] == 'sent')
		isAllowedTo('pm_send');

	// Set the text to resemble the current folder.
	$pmbox = $context['folder'] != 'sent' ? $txt['inbox'] : $txt['sent_items'];
	$txt['delete_all'] = str_replace('PMBOX', $pmbox, $txt['delete_all']);

	// Now, build the link tree!
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=pm;f=' . $context['folder'],
		'name' => $pmbox
	);

	// Build it further for a label.
	if ($context['current_label_id'] != -1)
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=pm;f=' . $context['folder'] . ';l=' . $context['current_label_id'],
			'name' => $txt['pm_current_label'] . ': ' . $context['current_label']
		);

	// If we're in group mode then... group!
	$groupQuery = $context['display_mode'] == 2 ? 'GROUP BY pm.id_pm_head' : '';

	// Figure out how many messages there are.
	if ($context['folder'] == 'sent')
		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}personal_messages AS pm
			WHERE pm.id_member_from = $user_info[id]
				AND pm.deleted_by_sender = 0
			$groupQuery", __FILE__, __LINE__);
	else
		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}pm_recipients AS pmr" . ($context['display_mode'] == 2 ? "
				INNER JOIN {$db_prefix}personal_messages AS pm ON (pm.id_pm = pmr.id_pm)" : '') . "
			WHERE pmr.id_member = $user_info[id]
				AND pmr.deleted = 0$labelQuery
				$groupQuery", __FILE__, __LINE__);
	list ($max_messages) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Only show the button if there are messages to delete.
	$context['show_delete'] = $max_messages > 0;

	// Start on the last page.
	if (!is_numeric($_GET['start']) || $_GET['start'] >= $max_messages)
		$_GET['start'] = ($max_messages - 1) - (($max_messages - 1) % $modSettings['defaultMaxMessages']);
	elseif ($_GET['start'] < 0)
		$_GET['start'] = 0;

	// ... but wait - what if we want to start from a specific message?
	if (isset($_GET['pmid']))
	{
		$_GET['pmid'] = (int) $_GET['pmid'];
		$context['current_pm'] = $_GET['pmid'];

		// With only one page of PM's we're gonna want page 1.
		if ($max_messages <= $modSettings['defaultMaxMessages'])
			$_GET['start'] = 0;
		// If we pass kstart we assume we're in the right place.
		elseif (!isset($_GET['kstart']))
		{
			if ($context['folder'] == 'sent')
				$request = $smfFunc['db_query']('', "
					SELECT COUNT(*)
					FROM {$db_prefix}personal_messages
					WHERE id_member_from = $user_info[id]
						AND deleted_by_sender = 0
						AND id_pm " . ($descending ? '>' : '<') . " $_GET[pmid]
						$groupQuery", __FILE__, __LINE__);
			else
				$request = $smfFunc['db_query']('', "
					SELECT COUNT(*)
					FROM {$db_prefix}pm_recipients AS pmr" . ($context['display_mode'] == 2 ? "
						INNER JOIN {$db_prefix}personal_messages AS pm ON (pm.id_pm = pmr.id_pm)" : '') . "
					WHERE pmr.id_member = $user_info[id]
						AND pmr.deleted = 0$labelQuery
						AND id_pm " . ($descending ? '>' : '<') . " $_GET[pmid]
						$groupQuery", __FILE__, __LINE__);

			list ($_GET['start']) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			// To stop the page index's being abnormal, start the page on the page the message would normally be located on...
			$_GET['start'] = $modSettings['defaultMaxMessages'] * (int) ($_GET['start'] / $modSettings['defaultMaxMessages']);
		}
	}

	// Set up the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=pm;f=' . $context['folder'] . (isset($_REQUEST['l']) ? ';l=' . (int) $_REQUEST['l'] : '') . ';sort=' . $context['sort_by'] . (isset($_GET['desc']) ? ';desc' : ''), $_GET['start'], $max_messages, $modSettings['defaultMaxMessages']);
	$context['start'] = $_GET['start'];

	// Determine the navigation context (especially useful for the wireless template).
	$context['links'] = array(
		'first' => $_GET['start'] >= $modSettings['defaultMaxMessages'] ? $scripturl . '?action=pm;start=0' : '',
		'prev' => $_GET['start'] >= $modSettings['defaultMaxMessages'] ? $scripturl . '?action=pm;start=' . ($_GET['start'] - $modSettings['defaultMaxMessages']) : '',
		'next' => $_GET['start'] + $modSettings['defaultMaxMessages'] < $max_messages ? $scripturl . '?action=pm;start=' . ($_GET['start'] + $modSettings['defaultMaxMessages']) : '',
		'last' => $_GET['start'] + $modSettings['defaultMaxMessages'] < $max_messages ? $scripturl . '?action=pm;start=' . (floor(($max_messages - 1) / $modSettings['defaultMaxMessages']) * $modSettings['defaultMaxMessages']) : '',
		'up' => $scripturl,
	);
	$context['page_info'] = array(
		'current_page' => $_GET['start'] / $modSettings['defaultMaxMessages'] + 1,
		'num_pages' => floor(($max_messages - 1) / $modSettings['defaultMaxMessages']) + 1
	);

	// First work out what messages we need to see - if grouped is a little trickier...
	if ($context['display_mode'] == 2)
	{
		$request = $smfFunc['db_query']('', "
			SELECT MAX(pm.id_pm) AS id_pm, pm.id_pm_head
			FROM {$db_prefix}personal_messages AS pm" . ($context['folder'] == 'sent' ? ($context['sort_by'] == 'name' ? "
				LEFT JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.id_pm = pm.id_pm)" : '') : "
				INNER JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.id_pm = pm.id_pm
					AND pmr.id_member = $user_info[id]
					AND pmr.deleted = 0
					$labelQuery)") . ($context['sort_by'] == 'name' ? ("
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = " . ($context['folder'] == 'sent' ? 'pmr.id_member' : 'pm.id_member_from') . ")") : '') . "
			WHERE " . ($context['folder'] == 'sent' ? "pm.id_member_from = $user_info[id]
				AND pm.deleted_by_sender = 0" : "1=1") . (empty($_GET['pmsg']) ? '' : "
				AND pm.id_pm = " . (int) $_GET['pmsg']) . "
			GROUP BY pm.id_pm_head
			ORDER BY " . ($_GET['sort'] == 'pm.id_pm' && $context['folder'] != 'sent' ? 'pmr.id_pm' : $_GET['sort']) . ($descending ? ' DESC' : ' ASC') . (empty($_GET['pmsg']) ? "
			LIMIT $_GET[start], $modSettings[defaultMaxMessages]" : ''), __FILE__, __LINE__);
	}
	// This is kinda simple!
	else
	{
		// !!!SLOW This query uses a filesort. (inbox only.)
		$request = $smfFunc['db_query']('', "
			SELECT pm.id_pm, pm.id_pm_head, pm.id_member_from
			FROM {$db_prefix}personal_messages AS pm" . ($context['folder'] == 'sent' ? '' . ($context['sort_by'] == 'name' ? "
				LEFT JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.id_pm = pm.id_pm)" : '') : "
				INNER JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.id_pm = pm.id_pm
					AND pmr.id_member = $user_info[id]
					AND pmr.deleted = 0
					$labelQuery)") . ($context['sort_by'] == 'name' ? ("
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = " . ($context['folder'] == 'sent' ? 'pmr.id_member' : 'pm.id_member_from') . ")") : '') . "
			WHERE " . ($context['folder'] == 'sent' ? "pm.id_member_from = $user_info[id]
				AND pm.deleted_by_sender = 0" : "1=1") . (empty($_GET['pmsg']) ? '' : "
				AND pm.id_pm = " . (int) $_GET['pmsg']) . "
			ORDER BY " . ($_GET['sort'] == 'pm.id_pm' && $context['folder'] != 'sent' ? 'pmr.id_pm' : $_GET['sort']) . ($descending ? ' DESC' : ' ASC') . (empty($_GET['pmsg']) ? "
			LIMIT $_GET[start], $modSettings[defaultMaxMessages]" : ''), __FILE__, __LINE__);
	}
	// Load the id_pms and initialize recipients.
	$pms = array();
	$lastData = array();
	$posters = $context['folder'] == 'sent' ? array($user_info['id']) : array();
	$recipients = array();

	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (!isset($recipients[$row['id_pm']]))
		{
			if (isset($row['id_member_from']))
				$posters[] = $row['id_member_from'];
			$pms[$row['id_pm']] = $row['id_pm'];
			$recipients[$row['id_pm']] = array(
				'to' => array(),
				'bcc' => array()
			);
		}

		// Keep track of the last message so we know what the head is without another query!
		if (empty($context['current_pm']) || $context['current_pm'] == $row['id_pm'])
			$lastData = array(
				'id' => $row['id_pm'],
				'head' => $row['id_pm_head'],
			);
	}
	$smfFunc['db_free_result']($request);

	if (!empty($pms))
	{
		// Select the correct current message.
		if (empty($context['current_pm']))
			$context['current_pm'] = $lastData['id'];

		// This is a list of the pm's that are used for "full" display.
		if ($context['display_mode'] == 0)
			$display_pms = $pms;
		else
			$display_pms = array($context['current_pm']);

		// At this point we know the main id_pm's. But - if we are looking at conversations we need the others!
		if ($context['display_mode'] == 2)
		{
			$request = $smfFunc['db_query']('', "
				SELECT pm.id_pm, pm.id_member_from
				FROM {$db_prefix}personal_messages AS pm
					INNER JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.id_pm = pm.id_pm)
				WHERE pm.id_pm_head = $lastData[head]
					AND (pm.id_member_from = $user_info[id] OR pmr.id_member = $user_info[id])
				ORDER BY pm.id_pm", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				if (!isset($recipients[$row['id_pm']]))
					$recipients[$row['id_pm']] = array(
						'to' => array(),
						'bcc' => array()
					);
				$display_pms[] = $row['id_pm'];
				$posters[] = $row['id_member_from'];
			}
			$smfFunc['db_free_result']($request);
		}

		// This is pretty much EVERY pm!
		$all_pms = array_merge($pms, $display_pms);
		$all_pms = array_unique($all_pms);

		// Get recipients (don't include bcc-recipients for your inbox, you're not supposed to know :P).
		$request = $smfFunc['db_query']('', "
			SELECT pmr.id_pm, mem_to.id_member AS id_member_to, mem_to.real_name AS to_name, pmr.bcc, pmr.labels, pmr.is_read
			FROM {$db_prefix}pm_recipients AS pmr
				LEFT JOIN {$db_prefix}members AS mem_to ON (mem_to.id_member = pmr.id_member)
			WHERE pmr.id_pm IN (" . implode(', ', $all_pms) . ")", __FILE__, __LINE__);
		$context['message_labels'] = array();
		$context['message_replied'] = array();
		$context['message_unread'] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if ($context['folder'] == 'sent' || empty($row['bcc']))
				$recipients[$row['id_pm']][empty($row['bcc']) ? 'to' : 'bcc'][] = empty($row['id_member_to']) ? $txt['guest_title'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member_to'] . '">' . $row['to_name'] . '</a>';

			if ($row['id_member_to'] == $user_info['id'] && $context['folder'] != 'sent')
			{
				$context['message_replied'][$row['id_pm']] = $row['is_read'] & 2;
				$context['message_unread'][$row['id_pm']] = $row['is_read'] == 0;

				$row['labels'] = $row['labels'] == '' ? array() : explode(',', $row['labels']);
				foreach ($row['labels'] as $v)
				{
					if (isset($context['labels'][(int) $v]))
						$context['message_labels'][$row['id_pm']][(int) $v] = array('id' => $v, 'name' => $context['labels'][(int) $v]['name']);
				}
			}
		}
		$smfFunc['db_free_result']($request);

		// Load any users....
		$posters = array_unique($posters);
		if (!empty($posters))
			loadMemberData($posters);

		// If we're on grouped/restricted view get a restricted list of messages.
		if ($context['display_mode'] != 0)
		{
			// Get the order right.
			$orderBy = array();
			foreach (array_reverse($pms) as $pm)
				$orderBy[] = 'pm.id_pm = ' . $pm;

			// Seperate query for these bits!
			$subjects_request = $smfFunc['db_query']('', "
				SELECT pm.id_pm, pm.subject, pm.id_member_from, pm.msgtime, pm.from_name
				FROM {$db_prefix}personal_messages AS pm
				WHERE pm.id_pm IN (" . implode(',', $pms) . ")
				ORDER BY " . implode(', ', $orderBy) . "
				LIMIT " . count($pms), __FILE__, __LINE__);
		}


		// Execute the query!
		$messages_request = $smfFunc['db_query']('', "
			SELECT pm.id_pm, pm.subject, pm.id_member_from, pm.body, pm.msgtime, pm.from_name
			FROM {$db_prefix}personal_messages AS pm" . ($context['folder'] == 'sent' ? "
				LEFT JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.id_pm = pm.id_pm)" : '') . ($context['sort_by'] == 'name' ? "
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = " . ($context['folder'] == 'sent' ? 'pmr.id_member' : 'pm.id_member_from') . ")" : '') . "
			WHERE pm.id_pm IN (" . implode(',', $display_pms) . ")" . ($context['folder'] == 'sent' ? "
			GROUP BY pm.id_pm" : '') . "
			ORDER BY " . ($context['display_mode'] == 2 ? 'pm.id_pm' : $_GET['sort']) . ($descending ? ' DESC' : ' ASC') . "
			LIMIT " . count($display_pms), __FILE__, __LINE__);
	}
	else
		$messages_request = false;

	$context['can_send_pm'] = allowedTo('pm_send');
	if (!WIRELESS)
		$context['sub_template'] = 'folder';
	$context['page_title'] = $txt['pm_inbox'];

	// Finally mark the relevant messages as read.
	if ($context['folder'] != 'sent' && !empty($context['labels'][(int) $context['current_label_id']]['unread_messages']))
	{
		// If the display mode is "old sk00l" do them all... 
		if ($context['display_mode'] == 0)
			markMessages(null, $context['current_label_id']);
		// Otherwise do just the current one!
		elseif (!empty($context['current_pm']))
			markMessages($display_pms, $context['current_label_id']);
	}
}

// Get a personal message for the theme.  (used to save memory.)
function prepareMessageContext($type = 'subject', $reset = false)
{
	global $txt, $scripturl, $modSettings, $context, $messages_request, $memberContext, $recipients, $smfFunc;
	global $user_info, $subjects_request;

	// Count the current message number....
	static $counter = null;
	if ($counter === null || $reset)
		$counter = $context['start'];

	static $temp_pm_selected = null;
	if ($temp_pm_selected === null)
	{
		$temp_pm_selected = isset($_SESSION['pm_selected']) ? $_SESSION['pm_selected'] : array();
		$_SESSION['pm_selected'] = array();
	}

	// If we're in non-boring view do something exciting!
	if ($context['display_mode'] != 0 && $subjects_request && $type == 'subject')
	{
		$subject = $smfFunc['db_fetch_assoc']($subjects_request);
		if (!$subject)
			return(false);

		$subject['subject'] = $subject['subject'] == '' ? $txt['no_subject'] : $subject['subject'];
		censorText($subject['subject']);

		$output = array(
			'id' => $subject['id_pm'],
			'member' => array(
				'link' => '<a href="' . $scripturl . '?action=profile">' . $user_info['name'] . '</a>',
			),
			'recipients' => &$recipients[$subject['id_pm']],
			'subject' => $subject['subject'],
			'time' => timeformat($subject['msgtime']),
			'timestamp' => forum_time(true, $subject['msgtime']),
			'number_recipients' => count($recipients[$subject['id_pm']]['to']),
			'labels' => &$context['message_labels'][$subject['id_pm']],
			'fully_labeled' => count($context['message_labels'][$subject['id_pm']]) == count($context['labels']),
			'is_replied_to' => &$context['message_replied'][$subject['id_pm']],
			'is_unread' => &$context['message_unread'][$subject['id_pm']],
			'is_selected' => !empty($temp_pm_selected) && in_array($subject['id_pm'], $temp_pm_selected),
		);

		return $output;
	}

	// Bail if it's false, ie. no messages.
	if ($messages_request == false)
		return false;

	// Reset the data?
	if ($reset == true)
		return @$smfFunc['db_data_seek']($messages_request, 0);

	// Get the next one... bail if anything goes wrong.
	$message = $smfFunc['db_fetch_assoc']($messages_request);
	if (!$message)
		return(false);

	// Use '(no subject)' if none was specified.
	$message['subject'] = $message['subject'] == '' ? $txt['no_subject'] : $message['subject'];

	// Load the message's information - if it's not there, load the guest information.
	if (!loadMemberContext($message['id_member_from']))
	{
		$memberContext[$message['id_member_from']]['name'] = $message['from_name'];
		$memberContext[$message['id_member_from']]['id'] = 0;
		$memberContext[$message['id_member_from']]['group'] = $txt['guest_title'];
		$memberContext[$message['id_member_from']]['link'] = $message['from_name'];
		$memberContext[$message['id_member_from']]['email'] = '';
		$memberContext[$message['id_member_from']]['hide_email'] = true;
		$memberContext[$message['id_member_from']]['is_guest'] = true;
	}

	// Censor all the important text...
	censorText($message['body']);
	censorText($message['subject']);

	// Run UBBC interpreter on the message.
	$message['body'] = parse_bbc($message['body'], true, 'pm' . $message['id_pm']);

	// Send the array.
	$output = array(
		'alternate' => $counter % 2,
		'id' => $message['id_pm'],
		'member' => &$memberContext[$message['id_member_from']],
		'subject' => $message['subject'],
		'time' => timeformat($message['msgtime']),
		'timestamp' => forum_time(true, $message['msgtime']),
		'counter' => $counter,
		'body' => $message['body'],
		'recipients' => &$recipients[$message['id_pm']],
		'number_recipients' => count($recipients[$message['id_pm']]['to']),
		'labels' => &$context['message_labels'][$message['id_pm']],
		'fully_labeled' => count($context['message_labels'][$message['id_pm']]) == count($context['labels']),
		'is_replied_to' => &$context['message_replied'][$message['id_pm']],
		'is_unread' => &$context['message_unread'][$message['id_pm']],
		'is_selected' => !empty($temp_pm_selected) && in_array($message['id_pm'], $temp_pm_selected),
	);

	$counter++;

	return $output;
}

function MessageSearch()
{
	global $context, $txt, $scripturl, $modSettings;

	if (isset($_REQUEST['params']))
	{
		$temp_params = explode('|"|', base64_decode(strtr($_REQUEST['params'], array(' ' => '+'))));
		$context['search_params'] = array();
		foreach ($temp_params as $i => $data)
		{
			@list ($k, $v) = explode('|\'|', $data);
			$context['search_params'][$k] = $smfFunc['db_unescape_string']($v);
		}
	}
	if (isset($_REQUEST['search']))
		$context['search_params']['search'] = $smfFunc['db_unescape_string'](un_htmlspecialchars($_REQUEST['search']));

	if (isset($context['search_params']['search']))
		$context['search_params']['search'] = htmlspecialchars($context['search_params']['search']);
	if (isset($context['search_params']['userspec']))
		$context['search_params']['userspec'] = htmlspecialchars($smfFunc['db_unescape_string']($context['search_params']['userspec']));

	if (!empty($context['search_params']['searchtype']))
		$context['search_params']['searchtype'] = 2;

	if (!empty($context['search_params']['minage']))
		$context['search_params']['minage'] = (int) $context['search_params']['minage'];

	if (!empty($context['search_params']['maxage']))
		$context['search_params']['maxage'] = (int) $context['search_params']['maxage'];

	$context['search_params']['subject_only'] = !empty($context['search_params']['subject_only']);
	$context['search_params']['show_complete'] = !empty($context['search_params']['show_complete']);

	// Create the array of labels to be searched.
	$context['search_labels'] = array();
	$searchedLabels = isset($context['search_params']['labels']) && $context['search_params']['labels'] != '' ? explode(',', $context['search_params']['labels']) : array();
	foreach ($context['labels'] as $label)
	{
		$context['search_labels'][] = array(
			'id' => $label['id'],
			'name' => $label['name'],
			'checked' => !empty($searchedLabels) ? in_array($label['id'], $searchedLabels) : true,
		);
	}

	// Are all the labels checked?
	$context['check_all'] = empty($searchedLabels) || count($context['search_labels']) == count($searchedLabels);

	// Load the error text strings if there were errors in the search.
	if (!empty($context['search_errors']))
	{
		loadLanguage('Errors');
		$context['search_errors']['messages'] = array();
		foreach ($context['search_errors'] as $search_error => $dummy)
		{
			if ($search_error == 'messages')
				continue;

			$context['search_errors']['messages'][] = $txt['error_' . $search_error];
		}
	}

	$context['simple_search'] = isset($context['search_params']['advanced']) ? empty($context['search_params']['advanced']) : !empty($modSettings['simpleSearch']) && !isset($_REQUEST['advanced']);
	$context['page_title'] = $txt['pm_search_title'];
	$context['sub_template'] = 'search';
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=pm;sa=search',
		'name' => $txt['pm_search_bar_title'],
	);
}

function MessageSearch2()
{
	global $scripturl, $modSettings, $user_info, $context, $txt, $db_prefix;
	global $memberContext, $smfFunc;

	if (!empty($context['load_average']) && !empty($modSettings['loadavg_search']) && $context['load_average'] >= $modSettings['loadavg_search'])
		fatal_lang_error('loadavg_search_disabled', false);

	// !!! For the moment force the folder to the inbox.
	$context['folder'] = 'inbox';

	// Some useful general permissions.
	$context['can_send_pm'] = allowedTo('send_pm');

	// Some hardcoded veriables that can be tweaked if required.
	$maxMembersToSearch = 500;

	// Extract all the search parameters.
	$search_params = array();
	if (isset($_REQUEST['params']))
	{
		$temp_params = explode('|"|', base64_decode(strtr($_REQUEST['params'], array(' ' => '+'))));
		foreach ($temp_params as $i => $data)
		{
			@list ($k, $v) = explode('|\'|', $data);
			$search_params[$k] = $smfFunc['db_unescape_string']($v);
		}
	}

	$context['start'] = isset($_GET['start']) ? (int) $_GET['start'] : 0;

	// Store whether simple search was used (needed if the user wants to do another query).
	if (!isset($search_params['advanced']))
		$search_params['advanced'] = empty($_REQUEST['advanced']) ? 0 : 1;

	// 1 => 'allwords' (default, don't set as param) / 2 => 'anywords'.
	if (!empty($search_params['searchtype']) || (!empty($_REQUEST['searchtype']) && $_REQUEST['searchtype'] == 2))
		$search_params['searchtype'] = 2;

	// Minimum age of messages. Default to zero (don't set param in that case).
	if (!empty($search_params['minage']) || (!empty($_REQUEST['minage']) && $_REQUEST['minage'] > 0))
		$search_params['minage'] = !empty($search_params['minage']) ? (int) $search_params['minage'] : (int) $_REQUEST['minage'];

	// Maximum age of messages. Default to infinite (9999 days: param not set).
	if (!empty($search_params['maxage']) || (!empty($_REQUEST['maxage']) && $_REQUEST['maxage'] != 9999))
		$search_params['maxage'] = !empty($search_params['maxage']) ? (int) $search_params['maxage'] : (int) $_REQUEST['maxage'];

	$search_params['subject_only'] = !empty($search_params['subject_only']) || !empty($_REQUEST['subject_only']);
	$search_params['show_complete'] = !empty($search_params['show_complete']) || !empty($_REQUEST['show_complete']);

	// Default the user name to a wildcard matching every user (*).
	if (!empty($search_params['user_spec']) || (!empty($_REQUEST['userspec']) && $_REQUEST['userspec'] != '*'))
		$search_params['userspec'] = isset($search_params['userspec']) ? $search_params['userspec'] : $_REQUEST['userspec'];

	// If there's no specific user, then don't mention it in the main query.
	if (empty($search_params['userspec']))
		$userQuery = '';
	else
	{
		$userString = strtr($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($search_params['userspec']), ENT_QUOTES), array('&quot;' => '"'));
		$userString = strtr($userString, array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_'));

		preg_match_all('~"([^"]+)"~', $userString, $matches);
		$possible_users = array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $userString)));

		for ($k = 0, $n = count($possible_users); $k < $n; $k++)
		{
			$possible_users[$k] = trim($possible_users[$k]);

			if (strlen($possible_users[$k]) == 0)
				unset($possible_users[$k]);
		}

		// Who matches those criteria?
		// !!! This doesn't support sent item searching.
		$request = $smfFunc['db_query']('', "
			SELECT id_member
			FROM {$db_prefix}members
			WHERE real_name LIKE '" . implode("' OR real_name LIKE '", $possible_users) . "'", __FILE__, __LINE__);
		// Simply do nothing if there're too many members matching the criteria.
		if ($smfFunc['db_num_rows']($request) > $maxMembersToSearch)
			$userQuery = '';
		elseif ($smfFunc['db_num_rows']($request) == 0)
			$userQuery = "AND pm.id_member_from = 0 AND (pm.from_name LIKE '" . implode("' OR pm.from_name LIKE '", $possible_users) . "')";
		else
		{
			$memberlist = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$memberlist[] = $row['id_member'];
			$userQuery = "AND (pm.id_member_from IN (" . implode(', ', $memberlist) . ") OR (pm.id_member_from = 0 AND (pm.from_name LIKE '" . implode("' OR pm.from_name LIKE '", $possible_users) . "')))";
		}
		$smfFunc['db_free_result']($request);
	}

	// Setup the sorting variables...
	// !!! Add more in here!
	$sort_columns = array(
		'pm.id_pm',
	);
	if (empty($search_params['sort']) && !empty($_REQUEST['sort']))
		list ($search_params['sort'], $search_params['sort_dir']) = array_pad(explode('|', $_REQUEST['sort']), 2, '');
	$search_params['sort'] = !empty($search_params['sort']) && in_array($search_params['sort'], $sort_columns) ? $search_params['sort'] : 'pm.id_pm';
	$search_params['sort_dir'] = !empty($search_params['sort_dir']) && $search_params['sort_dir'] == 'asc' ? 'asc' : 'desc';

	// Sort out any labels we may be searching by.
	$labelQuery = '';
	if ($context['folder'] == 'inbox' && !empty($search_params['advanced']) && $context['currently_using_labels'])
	{
		// Came here from pagination?  Put them back into $_REQUEST for sanitization.
		if (isset($search_params['labels']))
			$_REQUEST['searchlabel'] = explode(',', $search_params['labels']);

		// Assuming we have some labels - make them all integers.
		if (!empty($_REQUEST['searchlabel']) && is_array($_REQUEST['searchlabel']))
		{
			foreach ($_REQUEST['searchlabel'] as $key => $id)
				$_REQUEST['searchlabel'][$key] = (int) $id;
		}
		else
			$_REQUEST['searchlabel'] = array();

		// Now that everything is cleaned up a bit, make the labels a param.
		$search_params['labels'] = implode(',', $_REQUEST['searchlabel']);

		// No labels selected? That must be an error!
		if (empty($_REQUEST['searchlabel']))
			$context['search_errors']['no_labels_selected'] = true;
		// Otherwise prepare the query!
		elseif (count($_REQUEST['searchlabel']) != count($context['labels']))
			$labelQuery = "
			AND (FIND_IN_SET('" . implode("', pmr.labels) OR FIND_IN_SET('", $_REQUEST['searchlabel']) . "', pmr.labels))";
	}

	// What are we actually searching for?
	$search_params['search'] = !empty($search_params['search']) ? $search_params['search'] : (isset($_REQUEST['search']) ? $smfFunc['db_unescape_string']($_REQUEST['search']) : '');
	// If we ain't got nothing - we should error!
	if (!isset($search_params['search']) || $search_params['search'] == '')
		$context['search_errors']['invalid_search_string'] = true;

	// Extract phrase parts first (e.g. some words "this is a phrase" some more words.)
	preg_match_all('~(?:^|\s)([-]?)"([^"]+)"(?:$|\s)~' . ($context['utf8'] ? 'u' : ''), $search_params['search'], $matches, PREG_PATTERN_ORDER);
	$searchArray = $matches[2];

	// Remove the phrase parts and extract the words.
	$tempSearch = explode(' ', preg_replace('~(?:^|\s)([-]?)"([^"]+)"(?:$|\s)~' . ($context['utf8'] ? 'u' : ''), ' ', $search_params['search']));

	// A minus sign in front of a word excludes the word.... so...
	$excludedWords = array();

	// .. first, we check for things like -"some words", but not "-some words".
	foreach ($matches[1] as $index => $word)
		if ($word == '-')
		{
			$word = $smfFunc['strtolower'](trim($searchArray[$index]));
			if (strlen($word) > 0)
				$excludedWords[] = $smfFunc['db_escape_string']($word);
			unset($searchArray[$index]);
		}

	// Now we look for -test, etc.... normaller.
	foreach ($tempSearch as $index => $word)
		if (strpos(trim($word), '-') === 0)
		{
			$word = substr($smfFunc['strtolower'](trim($word)), 1);
			if (strlen($word) > 0)
				$excludedWords[] = $smfFunc['db_escape_string']($word);
			unset($tempSearch[$index]);
		}

	$searchArray = array_merge($searchArray, $tempSearch);

	// Trim everything and make sure there are no words that are the same.
	foreach ($searchArray as $index => $value)
	{
		$searchArray[$index] = $smfFunc['strtolower'](trim($value));
		if ($searchArray[$index] == '')
			unset($searchArray[$index]);
		else
		{
			// Sort out entities first.
			$searchArray[$index] = $smfFunc['htmlspecialchars']($searchArray[$index]);
			$searchArray[$index] = $smfFunc['db_escape_string']($searchArray[$index]);
		}
	}
	$searchArray = array_unique($searchArray);

	// Create an array of replacements for highlighting.
	$context['mark'] = array();
	foreach ($searchArray as $word)
		$context['mark'][$word] = '<b class="highlight">' . $word . '</b>';

	// This contains *everything*
	$searchWords = array_merge($searchArray, $excludedWords);

	// Make sure at least one word is being searched for.
	if (empty($searchArray))
		$context['search_errors']['invalid_search_string'] = true;

	// Sort out the search query so the user can edit it - if they want.
	$context['search_params'] = $search_params;
	if (isset($context['search_params']['search']))
		$context['search_params']['search'] = htmlspecialchars($context['search_params']['search']);
	if (isset($context['search_params']['userspec']))
		$context['search_params']['userspec'] = htmlspecialchars($context['search_params']['userspec']);

	// Now we have all the parameters, combine them together for pagination and the like...
	$context['params'] = array();
	foreach ($search_params as $k => $v)
		$context['params'][] = $k . '|\'|' . $smfFunc['db_escape_string']($v);
	$context['params'] = base64_encode(implode('|"|', $context['params']));

	// Compile the subject query part.
	$andQueryParts = array();

	foreach ($searchWords as $index => $word)
	{
		if ($word == '')
			continue;

		if ($search_params['subject_only'])
			$andQueryParts[] = "pm.subject" . (in_array($word, $excludedWords) ? ' NOT' : '') . " LIKE '%" . strtr($word, array('_' => '\\_', '%' => '\\%')) . "%'";
		else
			$andQueryParts[] = '(pm.subject' . (in_array($word, $excludedWords) ? ' NOT' : '') . " LIKE '%" . strtr($word, array('_' => '\\_', '%' => '\\%')) . "%' " . (in_array($word, $excludedWords) ? 'AND pm.body NOT' : 'OR pm.body') . " LIKE '%" . strtr($word, array('_' => '\\_', '%' => '\\%')) . "%')";
	}

	$searchQuery = ' 1';
	if (!empty($andQueryParts))
		$searchQuery = implode(!empty($search_params['searchtype']) && $search_params['searchtype'] == 2 ? ' OR ' : ' AND ', $andQueryParts);

	// If we have errors - return back to the first screen...
	if (!empty($context['search_errors']))
	{
		$_REQUEST['params'] = $context['params'];
		return MessageSearch();
	}

	// Get the amount of results.
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}pm_recipients AS pmr
			INNER JOIN {$db_prefix}personal_messages AS pm ON (pm.id_pm = pmr.id_pm)
		WHERE " . ($context['folder'] == 'inbox' ? "
			pmr.id_member = $user_info[id]
			AND pmr.deleted = 0" : "
			pm.id_member_from = $user_info[id]
			AND pm.deleted_by_sender = 0") . "
			$userQuery$labelQuery
			AND ($searchQuery)", __FILE__, __LINE__);
	list ($numResults) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Get all the matching messages... using standard search only (No caching and the like!)
	// !!! This doesn't support sent item searching yet.
	$request = $smfFunc['db_query']('', "
		SELECT pm.id_pm, pm.id_member_from
		FROM {$db_prefix}pm_recipients AS pmr
			INNER JOIN {$db_prefix}personal_messages AS pm ON (pm.id_pm = pmr.id_pm)
		WHERE " . ($context['folder'] == 'inbox' ? "
			pmr.id_member = $user_info[id]
			AND pmr.deleted = 0" : "
			pm.id_member_from = $user_info[id]
			AND pm.deleted_by_sender = 0") . "
			$userQuery$labelQuery
			AND ($searchQuery)
		ORDER BY $search_params[sort] $search_params[sort_dir]
		LIMIT $context[start], $modSettings[search_results_per_page]", __FILE__, __LINE__);
	$foundMessages = array();
	$posters = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$foundMessages[] = $row['id_pm'];
		$posters[] = $row['id_member_from'];
	}
	$smfFunc['db_free_result']($request);

	// Load the users...
	$posters = array_unique($posters);
	if (!empty($posters))
		loadMemberData($posters);

	// Sort out the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=pm;sa=search2;params=' . $context['params'], $_GET['start'], $numResults, $modSettings['search_results_per_page'], false);

	$context['message_labels'] = array();
	$context['message_replied'] = array();
	$context['personal_messages'] = array();

	if (!empty($foundMessages))
	{
		// Now get recipients (but don't include bcc-recipients for your inbox, you're not supposed to know :P!)
		$request = $smfFunc['db_query']('', "
			SELECT
				pmr.id_pm, mem_to.id_member AS id_member_to, mem_to.real_name AS to_name,
				pmr.bcc, pmr.labels, pmr.is_read
			FROM {$db_prefix}pm_recipients AS pmr
				LEFT JOIN {$db_prefix}members AS mem_to ON (mem_to.id_member = pmr.id_member)
			WHERE pmr.id_pm IN (" . implode(', ', $foundMessages) . ")", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if ($context['folder'] == 'sent' || empty($row['bcc']))
				$recipients[$row['id_pm']][empty($row['bcc']) ? 'to' : 'bcc'][] = empty($row['id_member_to']) ? $txt['guest_title'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member_to'] . '">' . $row['to_name'] . '</a>';

			if ($row['id_member_to'] == $user_info['id'] && $context['folder'] != 'sent')
			{
				$context['message_replied'][$row['id_pm']] = $row['is_read'] & 2;

				$row['labels'] = $row['labels'] == '' ? array() : explode(',', $row['labels']);
				// This is a special need for linking to messages.
				foreach ($row['labels'] as $v)
				{
					if (isset($context['labels'][(int) $v]))
						$context['message_labels'][$row['id_pm']][(int) $v] = array('id' => $v, 'name' => $context['labels'][(int) $v]['name']);

					// Here we find the first label on a message - for linking to posts in results
					if (!isset($context['first_label'][$row['id_pm']]) && !in_array('-1', $row['labels']))
						$context['first_label'][$row['id_pm']] = (int) $v;
				}
			}
		}

		// Prepare the query for the callback!
		$request = $smfFunc['db_query']('', "
			SELECT pm.id_pm, pm.subject, pm.id_member_from, pm.body, pm.msgtime, pm.from_name
			FROM {$db_prefix}personal_messages AS pm
			WHERE pm.id_pm IN (" . implode(',', $foundMessages) . ")
			ORDER BY $search_params[sort] $search_params[sort_dir]
			LIMIT " . count($foundMessages), __FILE__, __LINE__);
		$counter = 0;
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			// If there's no message subject, use the default.
			$row['subject'] = $row['subject'] == '' ? $txt['no_subject'] : $row['subject'];

			// Load this posters context info, if it ain't there then fill in the essentials...
			if (!loadMemberContext($row['id_member_from']))
			{
				$memberContext[$row['id_member_from']]['name'] = $row['from_name'];
				$memberContext[$row['id_member_from']]['id'] = 0;
				$memberContext[$row['id_member_from']]['group'] = $txt['guest_title'];
				$memberContext[$row['id_member_from']]['link'] = $row['from_name'];
				$memberContext[$row['id_member_from']]['email'] = '';
				$memberContext[$row['id_member_from']]['hide_email'] = true;
				$memberContext[$row['id_member_from']]['is_guest'] = true;
			}

			// Censor anything we don't want to see...
			censorText($row['body']);
			censorText($row['subject']);

			// Parse out any BBC...
			$row['body'] = parse_bbc($row['body'], true, 'pm' . $row['id_pm']);

			$href = $scripturl . '?action=pm;f=' . $context['folder'] . (isset($context['first_label'][$row['id_pm']]) ? ';l=' . $context['first_label'][$row['id_pm']] : '') . ';pmid='. $row['id_pm'] . '#msg' . $row['id_pm'];
			$context['personal_messages'][] = array(
				'id' => $row['id_pm'],
				'member' => &$memberContext[$row['id_member_from']],
				'subject' => $row['subject'],
				'body' => $row['body'],
				'time' => timeformat($row['msgtime']),
				'recipients' => &$recipients[$row['id_pm']],
				'labels' => &$context['message_labels'][$row['id_pm']],
				'fully_labeled' => count($context['message_labels'][$row['id_pm']]) == count($context['labels']),
				'is_replied_to' => &$context['message_replied'][$row['id_pm']],
				'href' => $href,
				'link' => '<a href="' . $href . '">' . $row['subject'] . '</a>',
				'counter' => ++$counter,
			);
		}
		$smfFunc['db_free_result']($request);
	}

	// Finish off the context.
	$context['page_title'] = $txt['pm_search_title'];
	$context['sub_template'] = 'search_results';
	$context['pm_area'] = 'search';
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=pm;sa=search',
		'name' => $txt['pm_search_bar_title'],
	);
}

// Send a new message?
function MessagePost()
{
	global $txt, $sourcedir, $db_prefix, $scripturl, $modSettings;
	global $context, $options, $smfFunc, $language, $user_info;

	isAllowedTo('pm_send');

	if (loadLanguage('PersonalMessage', '', false) === false)
		loadLanguage('InstantMessage');
	// Just in case it was loaded from somewhere else.
	if (!WIRELESS)
	{
		if (loadTemplate('PersonalMessage', false) === false)
			loadTemplate('InstantMessage');
		$context['sub_template'] = 'send';
	}

	// Extract out the spam settings - cause it's neat.
	list ($modSettings['max_pm_recipients'], $modSettings['pm_posts_verification'], $modSettings['pm_posts_per_hour']) = explode(',', $modSettings['pm_spam_settings']);

	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	// Set the title...
	$context['page_title'] = $txt['send_message'];

	$context['reply'] = isset($_REQUEST['pmsg']) || isset($_REQUEST['quote']);

	// Check whether we've gone over the limit of messages we can send per hour.
	if (!empty($modSettings['pm_posts_per_hour']) && !allowedTo(array('admin_forum', 'moderate_forum', 'send_mail')) && empty($user_info['mod_cache']['bq']) && empty($user_info['mod_cache']['gq']))
	{
		// How many messages have they sent this last hour?
		$request = $smfFunc['db_query']('', "
			SELECT COUNT(pr.id_pm) AS postCount
			FROM {$db_prefix}personal_messages AS pm
				INNER JOIN {$db_prefix}pm_recipients AS pr ON (pr.id_pm = pm.id_pm)
			WHERE pm.id_member_from = $user_info[id]
				AND pm.msgtime > " . (time() - 3600), __FILE__, __LINE__);
		list ($postCount) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		if (!empty($postCount) && $postCount >= $modSettings['pm_posts_per_hour'])
			fatal_lang_error('pm_too_many_per_hour', true, array($modSettings['pm_posts_per_hour']));
	}

	// Quoting/Replying to a message?
	if (!empty($_REQUEST['pmsg']))
	{
		$_REQUEST['pmsg'] = (int) $_REQUEST['pmsg'];

		// Get the quoted message (and make sure you're allowed to see this quote!).
		$request = $smfFunc['db_query']('', "
			SELECT
				pm.id_pm, CASE WHEN pm.id_pm_head = 0 THEN pm.id_pm ELSE pm.id_pm_head END AS pm_head,
				pm.body, pm.subject, pm.msgtime, mem.member_name, IFNULL(mem.id_member, 0) AS id_member,
				IFNULL(mem.real_name, pm.from_name) AS real_name
			FROM {$db_prefix}personal_messages AS pm" . ($context['folder'] == 'sent' ? '' : "
				INNER JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.id_pm = $_REQUEST[pmsg])") . "
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = pm.id_member_from)
			WHERE pm.id_pm = $_REQUEST[pmsg]" . ($context['folder'] == 'sent' ? "
				AND pm.id_member_from = $user_info[id]" : "
				AND pmr.id_member = $user_info[id]") . "
			LIMIT 1", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('pm_not_yours', false);
		$row_quoted = $smfFunc['db_fetch_assoc']($request);
		$smfFunc['db_free_result']($request);

		// Censor the message.
		censorText($row_quoted['subject']);
		censorText($row_quoted['body']);

		// Add 'Re: ' to it....
		if (!isset($context['response_prefix']) && !($context['response_prefix'] = cache_get_data('response_prefix')))
		{
			if ($language === $user_info['language'])
				$context['response_prefix'] = $txt['response_prefix'];
			else
			{
				loadLanguage('index', $language, false);
				$context['response_prefix'] = $txt['response_prefix'];
				loadLanguage('index');
			}
			cache_put_data('response_prefix', $context['response_prefix'], 600);
		}
		$form_subject = $row_quoted['subject'];
		if ($context['reply'] && trim($context['response_prefix']) != '' && $smfFunc['strpos']($form_subject, trim($context['response_prefix'])) !== 0)
			$form_subject = $context['response_prefix'] . $form_subject;

		if (isset($_REQUEST['quote']))
		{
			// Remove any nested quotes and <br />...
			$form_message = preg_replace('~<br( /)?' . '>~i', "\n", $row_quoted['body']);
			if (!empty($modSettings['removeNestedQuotes']))
				$form_message = preg_replace(array('~\n?\[quote.*?\].+?\[/quote\]\n?~is', '~^\n~', '~\[/quote\]~'), '', $form_message);
			if (empty($row_quoted['id_member']))
				$form_message = '[quote author=&quot;' . $row_quoted['real_name'] . "&quot;]\n" . $form_message . "\n[/quote]";
			else
				$form_message = '[quote author=' . $row_quoted['real_name'] . ' link=action=profile;u=' . $row_quoted['id_member'] . ' date=' . $row_quoted['msgtime'] . "]\n" . $form_message . "\n[/quote]";
		}
		else
			$form_message = '';

		// Do the BBC thang on the message.
		$row_quoted['body'] = parse_bbc($row_quoted['body'], true, 'pm' . $row_quoted['id_pm']);

		// Set up the quoted message array.
		$context['quoted_message'] = array(
			'id' => $row_quoted['id_pm'],
			'pm_head' => $row_quoted['pm_head'],
			'member' => array(
				'name' => $row_quoted['real_name'],
				'username' => $row_quoted['member_name'],
				'id' => $row_quoted['id_member'],
				'href' => !empty($row_quoted['id_member']) ? $scripturl . '?action=profile;u=' . $row_quoted['id_member'] : '',
				'link' => !empty($row_quoted['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row_quoted['id_member'] . '">' . $row_quoted['real_name'] . '</a>' : $row_quoted['real_name'],
			),
			'subject' => $row_quoted['subject'],
			'time' => timeformat($row_quoted['msgtime']),
			'timestamp' => forum_time(true, $row_quoted['msgtime']),
			'body' => $row_quoted['body']
		);
	}
	else
	{
		$context['quoted_message'] = false;
		$form_subject = '';
		$form_message = '';
	}

	// Sending by ID?  Replying to all?  Fetch the real_name(s).
	if (isset($_REQUEST['u']))
	{
		// Store all the members who are getting this...
		$membersTo = array();

		// If the user is replying to all, get all the other members this was sent to..
		if ($_REQUEST['u'] == 'all' && isset($row_quoted))
		{
			// Firstly, to reply to all we clearly already have $row_quoted - so have the original member from.
			$membersTo[] = '&quot;' . $row_quoted['real_name'] . '&quot;';

			// Now to get the others.
			$request = $smfFunc['db_query']('', "
				SELECT mem.real_name
				FROM {$db_prefix}pm_recipients AS pmr
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = pmr.id_member)
				WHERE pmr.id_pm = $_REQUEST[pmsg]
					AND pmr.id_member != $user_info[id]
					AND bcc = 0", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$membersTo[] = '&quot;' . htmlspecialchars($row['real_name']) . '&quot;';
			$smfFunc['db_free_result']($request);
		}
		else
		{
			$_REQUEST['u'] = explode(',', $_REQUEST['u']);
			foreach ($_REQUEST['u'] as $key => $uID)
				$_REQUEST['u'][$key] = (int) $uID;

			$request = $smfFunc['db_query']('', "
				SELECT real_name
				FROM {$db_prefix}members
				WHERE id_member IN (" . implode(', ', $_REQUEST['u']) . ")
				LIMIT " . count($_REQUEST['u']), __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$membersTo[] = '&quot;' . $row['real_name'] . '&quot;';
			$smfFunc['db_free_result']($request);
		}

		// Create the 'to' string - Quoting it, just in case it's something like bob,i,like,commas,man.
		$_REQUEST['to'] = implode(', ', $membersTo);
	}

	// Set the defaults...
	$context['subject'] = $form_subject != '' ? $form_subject : $txt['no_subject'];
	$context['message'] = str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $form_message);
	$context['to'] = isset($_REQUEST['to']) ? $smfFunc['db_unescape_string']($_REQUEST['to']) : '';
	$context['bcc'] = isset($_REQUEST['bcc']) ? $smfFunc['db_unescape_string']($_REQUEST['bcc']) : '';
	$context['post_error'] = array();
	$context['copy_to_outbox'] = !empty($options['copy_to_outbox']);

	// And build the link tree.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=pm;sa=send',
		'name' => $txt['new_message']
	);

	$context['visual_verification'] = !$user_info['is_admin'] && !empty($modSettings['pm_posts_verification']) && $user_info['posts'] < $modSettings['pm_posts_verification'];
	if ($context['visual_verification'])
	{
		$context['use_graphic_library'] = in_array('gd', get_loaded_extensions());
		$context['verificiation_image_href'] = $scripturl . '?action=verificationcode;rand=' . md5(rand());

		// Skip I, J, L, O, Q, S and Z.
		$character_range = array_merge(range('A', 'H'), array('K', 'M', 'N', 'P', 'R'), range('T', 'Y'));

		// Generate a new code.
		$_SESSION['visual_verification_code'] = '';
		for ($i = 0; $i < 5; $i++)
			$_SESSION['visual_verification_code'] .= $character_range[array_rand($character_range)];
	}

	// Register this form and get a sequence number in $context.
	checkSubmitOnce('register');
}

// An error in the message...
function messagePostError($error_types, $to, $bcc)
{
	global $txt, $context, $scripturl, $modSettings, $db_prefix;
	global $smfFunc, $user_info;

	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	if (!WIRELESS)
		$context['sub_template'] = 'send';

	if (isset($_REQUEST['u']))
		$_REQUEST['u'] = is_array($_REQUEST['u']) ? $_REQUEST['u'] : explode(',', $_REQUEST['u']);

	$context['page_title'] = $txt['send_message'];

	// Set everything up like before....
	$context['to'] = $smfFunc['db_unescape_string']($to);
	$context['bcc'] = $smfFunc['db_unescape_string']($bcc);
	$context['subject'] = isset($_REQUEST['subject']) ? $smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_REQUEST['subject'])) : '';
	$context['message'] = isset($_REQUEST['message']) ? str_replace(array('  '), array('&nbsp; '), $smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_REQUEST['message']))) : '';
	$context['copy_to_outbox'] = !empty($_REQUEST['outbox']);
	$context['reply'] = !empty($_REQUEST['replied_to']);

	if ($context['reply'])
	{
		$_REQUEST['replied_to'] = (int) $_REQUEST['replied_to'];

		$request = $smfFunc['db_query']('', "
			SELECT
				pm.id_pm, CASE WHEN pm.id_pm_head = 0 THEN pm.id_pm ELSE pm.id_pm_head END AS pm_head,
				pm.body, pm.subject, pm.msgtime, mem.member_name, IFNULL(mem.id_member, 0) AS id_member,
				IFNULL(mem.real_name, pm.from_name) AS real_name
			FROM {$db_prefix}personal_messages AS pm" . ($context['folder'] == 'sent' ? '' : "
				INNER JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.id_pm = $_REQUEST[replied_to])") . ")
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = pm.id_member_from)
			WHERE pm.id_pm = $_REQUEST[replied_to]" . ($context['folder'] == 'sent' ? "
				AND pm.id_member_from = $user_info[id]" : "
				AND pmr.id_member = $user_info[id]") . "
			LIMIT 1", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('pm_not_yours', false);
		$row_quoted = $smfFunc['db_fetch_assoc']($request);
		$smfFunc['db_free_result']($request);

		censorText($row_quoted['subject']);
		censorText($row_quoted['body']);

		$context['quoted_message'] = array(
			'id' => $row_quoted['id_pm'],
			'pm_head' => $row_quoted['pm_head'],
			'member' => array(
				'name' => $row_quoted['real_name'],
				'username' => $row_quoted['member_name'],
				'id' => $row_quoted['id_member'],
				'href' => !empty($row_quoted['id_member']) ? $scripturl . '?action=profile;u=' . $row_quoted['id_member'] : '',
				'link' => !empty($row_quoted['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row_quoted['id_member'] . '">' . $row_quoted['real_name'] . '</a>' : $row_quoted['real_name'],
			),
			'subject' => $row_quoted['subject'],
			'time' => timeformat($row_quoted['msgtime']),
			'timestamp' => forum_time(true, $row_quoted['msgtime']),
			'body' => parse_bbc($row_quoted['body'], true, 'pm' . $row_quoted['id_pm']),
		);
	}

	// Build the link tree....
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=pm;sa=send',
		'name' => $txt['new_message']
	);

	// Set each of the errors for the template.
	loadLanguage('Errors');
	$context['post_error'] = array(
		'messages' => array(),
	);
	foreach ($error_types as $error_type)
	{
		$context['post_error'][$error_type] = true;
		if (isset($txt['error_' . $error_type]))
		{
			if ($error_type == 'long_message')
				$txt['error_' . $error_type] = sprintf($txt['error_' . $error_type], $modSettings['max_messageLength']);
			$context['post_error']['messages'][] = $txt['error_' . $error_type];
		}
	}

	// Check whether we need to show the code again.
	$context['visual_verification'] = !$user_info['is_admin'] && !empty($modSettings['pm_posts_verification']) && $user_info['posts'] < $modSettings['pm_posts_verification'];
	if ($context['visual_verification'])
	{
		$context['use_graphic_library'] = in_array('gd', get_loaded_extensions());
		$context['verificiation_image_href'] = $scripturl . '?action=verificationcode;rand=' . md5(rand());
	}

	// No check for the previous submission is needed.
	checkSubmitOnce('free');

	// Acquire a new form sequence number.
	checkSubmitOnce('register');
}

// Send it!
function MessagePost2()
{
	global $txt, $context, $sourcedir;
	global $db_prefix, $user_info, $modSettings, $scripturl, $smfFunc;

	isAllowedTo('pm_send');
	require_once($sourcedir . '/Subs-Auth.php');

	if (loadLanguage('PersonalMessage', '', false) === false)
		loadLanguage('InstantMessage');

	// Extract out the spam settings - it saves database space!
	list ($modSettings['max_pm_recipients'], $modSettings['pm_posts_verification'], $modSettings['pm_posts_per_hour']) = explode(',', $modSettings['pm_spam_settings']);

	// Check whether we've gone over the limit of messages we can send per hour - fatal error if fails!
	if (!empty($modSettings['pm_posts_per_hour']) && !allowedTo(array('admin_forum', 'moderate_forum', 'send_mail')) && empty($user_info['mod_cache']['bq']) && empty($user_info['mod_cache']['gq']))
	{
		// How many have they sent this last hour?
		$request = $smfFunc['db_query']('', "
			SELECT COUNT(pr.id_pm) AS postCount
			FROM {$db_prefix}personal_messages AS pm
				INNER JOIN {$db_prefix}pm_recipients AS pr ON (pr.id_pm = pm.id_pm)
			WHERE pm.id_member_from = $user_info[id]
				AND pm.msgtime > " . (time() - 3600), __FILE__, __LINE__);
		list ($postCount) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		if (!empty($postCount) && $postCount >= $modSettings['pm_posts_per_hour'])
			fatal_lang_error('pm_too_many_per_hour', true, array($modSettings['pm_posts_per_hour']));
	}

	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_POST['editor_mode']) && isset($_POST['message']))
	{
		require_once($sourcedir . '/Subs-Editor.php');
		// We strip and add slashes back here - so we don't forget!
		$_POST['message'] = $smfFunc['db_unescape_string']($_POST['message']);
		$_POST['message'] = html_to_bbc($_POST['message']);
		$_POST['message'] = $smfFunc['db_escape_string']($_POST['message']);

		// We need to unhtml it now as it gets done shortly.
		$_POST['message'] = un_htmlspecialchars($_POST['message']);

		// We need this incase of errors etc.
		$_REQUEST['message'] = $_POST['message'];
	}

	// Initialize the errors we're about to make.
	$post_errors = array();

	// If your session timed out, show an error, but do allow to re-submit.
	if (checkSession('post', '', false) != '')
		$post_errors[] = 'session_timeout';

	$_REQUEST['subject'] = isset($_REQUEST['subject']) ? trim($_REQUEST['subject']) : '';
	$_REQUEST['to'] = empty($_POST['to']) ? (empty($_GET['to']) ? '' : $_GET['to']) : $smfFunc['db_unescape_string']($_POST['to']);
	$_REQUEST['bcc'] = empty($_POST['bcc']) ? (empty($_GET['bcc']) ? '' : $_GET['bcc']) : $smfFunc['db_unescape_string']($_POST['bcc']);

	// Did they make any mistakes?
	if ($_REQUEST['subject'] == '')
		$post_errors[] = 'no_subject';
	if (!isset($_REQUEST['message']) || $_REQUEST['message'] == '')
		$post_errors[] = 'no_message';
	elseif (!empty($modSettings['max_messageLength']) && $smfFunc['strlen']($_REQUEST['message']) > $modSettings['max_messageLength'])
		$post_errors[] = 'long_message';
	if (empty($_REQUEST['to']) && empty($_REQUEST['bcc']) && empty($_REQUEST['u']))
		$post_errors[] = 'no_to';

	// Wrong verification code?
	if (!$user_info['is_admin'] && !empty($modSettings['pm_posts_verification']) && $user_info['posts'] < $modSettings['pm_posts_verification'] && (empty($_REQUEST['visual_verification_code']) || strtoupper($_REQUEST['visual_verification_code']) !== $_SESSION['visual_verification_code']))
		$post_errors[] = 'wrong_verification_code';

	// If they did, give a chance to make ammends.
	if (!empty($post_errors))
		return messagePostError($post_errors, $smfFunc['htmlspecialchars']($_REQUEST['to']), $smfFunc['htmlspecialchars']($_REQUEST['bcc']));

	// Want to take a second glance before you send?
	if (isset($_REQUEST['preview']))
	{
		// Set everything up to be displayed.
		$context['preview_subject'] = $smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_REQUEST['subject']));
		$context['preview_message'] = $smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_REQUEST['message']), ENT_QUOTES);
		preparsecode($context['preview_message'], true);

		// Parse out the BBC if it is enabled.
		$context['preview_message'] = parse_bbc($context['preview_message']);

		// Censor, as always.
		censorText($context['preview_subject']);
		censorText($context['preview_message']);

		// Set a descriptive title.
		$context['page_title'] = $txt['preview'] . ' - ' . $context['preview_subject'];

		// Pretend they messed up :P.
		return messagePostError(array(), htmlspecialchars($_REQUEST['to']), htmlspecialchars($_REQUEST['bcc']));
	}

	// Protect from message spamming.
	spamProtection('spam');

	// Prevent double submission of this form.
	checkSubmitOnce('check');

	// Initialize member ID array.
	$recipients = array(
		'to' => array(),
		'bcc' => array()
	);

	// Format the to and bcc members.
	$input = array(
		'to' => array(),
		'bcc' => array()
	);

	if (empty($_REQUEST['u']))
	{
		// To who..?
		if (!empty($_REQUEST['to']))
		{
			// We're going to take out the "s anyway ;).
			$_REQUEST['to'] = strtr($_REQUEST['to'], array('\\"' => '"'));

			preg_match_all('~"([^"]+)"~', $_REQUEST['to'], $matches);
			$input['to'] = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_REQUEST['to']))));
		}

		// Your secret's safe with me!
		if (!empty($_REQUEST['bcc']))
		{
			// We're going to take out the "s anyway ;).
			$_REQUEST['bcc'] = strtr($_REQUEST['bcc'], array('\\"' => '"'));

			preg_match_all('~"([^"]+)"~', $_REQUEST['bcc'], $matches);
			$input['bcc'] = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_REQUEST['bcc']))));
		}

		foreach ($input as $rec_type => $rec)
		{
			foreach ($rec as $index => $member)
				if (strlen(trim($member)) > 0)
					$input[$rec_type][$index] = $smfFunc['htmlspecialchars']($smfFunc['strtolower']($smfFunc['db_unescape_string'](trim($member))));
				else
					unset($input[$rec_type][$index]);
		}

		// Find the requested members - bcc and to.
		$foundMembers = findMembers(array_merge($input['to'], $input['bcc']));

		// Store IDs of the members that were found.
		foreach ($foundMembers as $member)
		{
			// It's easier this way.
			$member['name'] = strtr($member['name'], array('&#039;' => '\''));

			foreach ($input as $rec_type => $to_members)
				if (array_intersect(array($smfFunc['strtolower']($member['username']), $smfFunc['strtolower']($member['name']), $smfFunc['strtolower']($member['email'])), $to_members))
				{
					$recipients[$rec_type][] = $member['id'];

					// Get rid of this username. The ones that remain were not found.
					$input[$rec_type] = array_diff($input[$rec_type], array($smfFunc['strtolower']($member['username']), $smfFunc['strtolower']($member['name']), $smfFunc['strtolower']($member['email'])));
				}
		}
	}
	else
	{
		$_REQUEST['u'] = explode(',', $_REQUEST['u']);
		foreach ($_REQUEST['u'] as $key => $uID)
			$_REQUEST['u'][$key] = (int) $uID;

		$request = $smfFunc['db_query']('', "
			SELECT id_member
			FROM {$db_prefix}members
			WHERE id_member IN (" . implode(',', $_REQUEST['u']) . ")
			LIMIT " . count($_REQUEST['u']), __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$recipients['to'][] = $row['id_member'];
		$smfFunc['db_free_result']($request);
	}

	// Before we send the PM, let's make sure we don't have an abuse of numbers.
	if (!empty($modSettings['max_pm_recipients']) && count($recipients['to']) + count($recipients['bcc']) > $modSettings['max_pm_recipients'] && !allowedTo(array('moderate_forum', 'send_mail', 'admin_forum')))
	{
		$context['send_log'] = array(
			'sent' => array(),
			'failed' => array(sprintf($txt['pm_too_many_recipients'], $modSettings['max_pm_recipients'])),
		);
	}
	// Do the actual sending of the PM.
	else
	{
		if (!empty($recipients['to']) || !empty($recipients['bcc']))
			$context['send_log'] = sendpm($recipients, $_REQUEST['subject'], $_REQUEST['message'], !empty($_REQUEST['outbox']), null, !empty($_REQUEST['pm_head']) ? (int) $_REQUEST['pm_head'] : 0);
		else
			$context['send_log'] = array(
				'sent' => array(),
				'failed' => array()
			);
	}

	// Add a log message for all recipients that were not found.
	foreach ($input as $rec_type => $rec)
	{
		// Either bad_to or bad_bcc.
		if (!empty($rec) && !in_array('bad_' . $rec_type, $post_errors))
			$post_errors[] = 'bad_' . $rec_type;
		foreach ($rec as $i => $member)
		{
			$input[$rec_type][$i] = un_htmlspecialchars($member);
			$context['send_log']['failed'][] = sprintf($txt['pm_error_user_not_found'], $input[$rec_type][$i]);
		}
	}

	// Mark the message as "replied to".
	if (!empty($context['send_log']['sent']) && !empty($_REQUEST['replied_to']) && isset($_REQUEST['f']) && $_REQUEST['f'] == 'inbox')
	{
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}pm_recipients
			SET is_read = is_read | 2
			WHERE id_pm = " . (int) $_REQUEST['replied_to'] . "
				AND id_member = $user_info[id]", __FILE__, __LINE__);
	}

	// If one or more of the recipient were invalid, go back to the post screen with the failed usernames.
	if (!empty($context['send_log']['failed']))
		return messagePostError($post_errors, empty($input['to']) ? '' : '&quot;' . implode('&quot;, &quot;', $input['to']) . '&quot;', empty($input['bcc']) ? '' : '&quot;' . implode('&quot;, &quot;', $input['bcc']) . '&quot;');

	// Go back to the where they sent from, if possible...
	redirectexit($context['current_label_redirect']);
}

// This function lists all buddies for wireless protocols.
function WirelessAddBuddy()
{
	global $scripturl, $txt, $db_prefix, $user_info, $context, $smfFunc;

	isAllowedTo('pm_send');
	$context['page_title'] = $txt['wireless_pm_add_buddy'];

	$current_buddies = empty($_REQUEST['u']) ? array() : explode(',', $_REQUEST['u']);
	foreach ($current_buddies as $key => $buddy)
		$current_buddies[$key] = (int) $buddy;

	$base_url = $scripturl . '?action=pm;sa=send;u=' . (empty($current_buddies) ? '' : implode(',', $current_buddies) . ',');
	$context['pm_href'] = $scripturl . '?action=pm;sa=send' . (empty($current_buddies) ? '' : ';u=' . implode(',', $current_buddies));

	$context['buddies'] = array();
	if (!empty($user_info['buddies']))
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_member, real_name
			FROM {$db_prefix}members
			WHERE id_member IN (" . implode(',', $user_info['buddies']) . ")
			ORDER BY real_name
			LIMIT " . count($user_info['buddies']), __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$context['buddies'][] = array(
				'id' => $row['id_member'],
				'name' => $row['real_name'],
				'selected' => in_array($row['id_member'], $current_buddies),
				'add_href' => $base_url . $row['id_member'],
			);
		$smfFunc['db_free_result']($request);
	}
}

// This function performs all additional stuff...
function MessageActionsApply()
{
	global $txt, $db_prefix, $context, $user_info, $options, $smfFunc;

	checkSession('request');

	if (isset($_REQUEST['del_selected']))
		$_REQUEST['pm_action'] = 'delete';

	if (isset($_REQUEST['pm_action']) && $_REQUEST['pm_action'] != '' && !empty($_REQUEST['pms']) && is_array($_REQUEST['pms']))
	{
		foreach ($_REQUEST['pms'] as $pm)
			$_REQUEST['pm_actions'][(int) $pm] = $_REQUEST['pm_action'];
	}

	if (empty($_REQUEST['pm_actions']))
		redirectexit($context['current_label_redirect']);

	$to_delete = array();
	$to_label = array();
	$label_type = array();
	foreach ($_REQUEST['pm_actions'] as $pm => $action)
	{
		if ($action === 'delete')
			$to_delete[] = (int) $pm;
		else
		{
			if (substr($action, 0, 4) == 'add_')
			{
				$type = 'add';
				$action = substr($action, 4);
			}
			elseif (substr($action, 0, 4) == 'rem_')
			{
				$type = 'rem';
				$action = substr($action, 4);
			}
			else
				$type = 'unk';

			if ($action == '-1' || $action == '0' || (int) $action > 0)
			{
				$to_label[(int) $pm] = (int) $action;
				$label_type[(int) $pm] = $type;
			}
		}
	}

	// Deleting, it looks like?
	if (!empty($to_delete))
		deleteMessages($to_delete, $context['folder']);

	// Are we labeling anything?
	if (!empty($to_label) && $context['folder'] == 'inbox')
	{
		$updateErrors = 0;

		// Get information about each message...
		$request = $smfFunc['db_query']('', "
			SELECT id_pm, labels
			FROM {$db_prefix}pm_recipients
			WHERE id_member = $user_info[id]
				AND id_pm IN (" . implode(',', array_keys($to_label)) . ")
			LIMIT " . count($to_label), __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			$labels = $row['labels'] == '' ? array('-1') : explode(',', trim($row['labels']));

			// Already exists?  Then... unset it!
			$ID_LABEL = array_search($to_label[$row['id_pm']], $labels);
			if ($ID_LABEL !== false && $label_type[$row['id_pm']] !== 'add')
				unset($labels[$ID_LABEL]);
			elseif ($label_type[$row['id_pm']] !== 'rem')
				$labels[] = $to_label[$row['id_pm']];

			if (!empty($options['pm_remove_inbox_label']) && $to_label[$row['id_pm']] != '-1' && ($key = array_search('-1', $labels)) !== false)
				unset($labels[$key]);

			$set = implode(',', array_unique($labels));
			if ($set == '')
				$set = '-1';

			// Check that this string isn't going to be too large for the database.
			if ($set > 60)
				$updateErrors++;
			else
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}pm_recipients
					SET labels = '$set'
					WHERE id_pm = $row[id_pm]
						AND id_member = $user_info[id]", __FILE__, __LINE__);
			}
		}
		$smfFunc['db_free_result']($request);

		// Any errors?
		// !!! Separate the sprintf?
		if (!empty($updateErrors))
			fatal_lang_error('labels_too_many', true, array($updateErrors));
	}

	// Back to the folder.
	$_SESSION['pm_selected'] = array_keys($to_label);
	redirectexit($context['current_label_redirect'] . (count($to_label) == 1 ? '#' . $_SESSION['pm_selected'][0] : ''), count($to_label) == 1 && $context['browser']['is_ie']);
}

// Are you sure you want to PERMANENTLY (mostly) delete ALL your messages?
function MessageKillAllQuery()
{
	global $txt, $context;

	// Only have to set up the template....
	$context['sub_template'] = 'ask_delete';
	$context['page_title'] = $txt['delete_all'];
	$context['delete_all'] = $_REQUEST['f'] == 'all';

	// And set the folder name...
	$txt['delete_all'] = str_replace('PMBOX', $context['folder'] != 'sent' ? $txt['inbox'] : $txt['sent_items'], $txt['delete_all']);
}

// Delete ALL the messages!
function MessageKillAll()
{
	global $context;

	checkSession('get');

	// If all then delete all messages the user has.
	if ($_REQUEST['f'] == 'all')
		deleteMessages(null, null);
	// Otherwise just the selected folder.
	else
		deleteMessages(null, $_REQUEST['f'] != 'sent' ? 'inbox' : 'sent');

	// Done... all gone.
	redirectexit($context['current_label_redirect']);
}

// This function allows the user to delete all messages older than so many days.
function MessagePrune()
{
	global $txt, $context, $db_prefix, $user_info, $scripturl, $smfFunc;

	// Actually delete the messages.
	if (isset($_REQUEST['age']))
	{
		checkSession();

		// Calculate the time to delete before.
		$deleteTime = time() - (86400 * (int) $_REQUEST['age']);

		// Array to store the IDs in.
		$toDelete = array();

		// Select all the messages they have sent older than $deleteTime.
		$request = $smfFunc['db_query']('', "
			SELECT id_pm
			FROM {$db_prefix}personal_messages
			WHERE deleted_by_sender = 0
				AND id_member_from = $user_info[id]
				AND msgtime < $deleteTime", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_row']($request))
			$toDelete[] = $row[0];
		$smfFunc['db_free_result']($request);

		// Select all messages in their inbox older than $deleteTime.
		$request = $smfFunc['db_query']('', "
			SELECT pmr.id_pm
			FROM {$db_prefix}pm_recipients AS pmr
				INNER JOIN {$db_prefix}personal_messages AS pm ON (pm.id_pm = pmr.id_pm)
			WHERE pmr.deleted = 0
				AND pmr.id_member = $user_info[id]
				AND pm.msgtime < $deleteTime", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$toDelete[] = $row['id_pm'];
		$smfFunc['db_free_result']($request);

		// Delete the actual messages.
		deleteMessages($toDelete);

		// Go back to their inbox.
		redirectexit($context['current_label_redirect']);
	}

	// Build the link tree elements.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=pm;sa=prune',
		'name' => $txt['pm_prune']
	);

	$context['sub_template'] = 'prune';
	$context['page_title'] = $txt['pm_prune'];
}

// Delete the specified personal messages.
function deleteMessages($personal_messages, $folder = null, $owner = null)
{
	global $db_prefix, $user_info, $smfFunc;

	if ($owner === null)
		$owner = array($user_info['id']);
	elseif (empty($owner))
		return;
	elseif (!is_array($owner))
		$owner = array($owner);

	if ($personal_messages !== null)
	{
		if (empty($personal_messages) || !is_array($personal_messages))
			return;

		foreach ($personal_messages as $index => $delete_id)
			$personal_messages[$index] = (int) $delete_id;

		$where =  '
				AND id_pm IN (' . implode(', ', array_unique($personal_messages)) . ')';
	}
	else
		$where = '';

	if ($folder == 'sent' || $folder === null)
	{
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}personal_messages
			SET deleted_by_sender = 1
			WHERE id_member_from IN (" . implode(', ', $owner) . ")
				AND deleted_by_sender = 0$where", __FILE__, __LINE__);
	}
	if ($folder != 'sent' || $folder === null)
	{
		// Calculate the number of messages each member's gonna lose...
		$request = $smfFunc['db_query']('', "
			SELECT id_member, COUNT(*) AS numDeletedMessages, CASE WHEN is_read & 1 >= 1 THEN 1 ELSE 0 END AS is_read
			FROM {$db_prefix}pm_recipients
			WHERE id_member IN (" . implode(', ', $owner) . ")
				AND deleted = 0$where
			GROUP BY id_member, is_read", __FILE__, __LINE__);
		// ...And update the statistics accordingly - now including unread messages!.
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if ($row['is_read'])
				updateMemberData($row['id_member'], array('instant_messages' => $where == '' ? 0 : "instant_messages - $row[numDeletedMessages]"));
			else
				updateMemberData($row['id_member'], array('instant_messages' => $where == '' ? 0 : "instant_messages - $row[numDeletedMessages]", 'unread_messages' => $where == '' ? 0 : "unread_messages - $row[numDeletedMessages]"));

			// If this is the current member we need to make their message count correct.
			if ($user_info['id'] == $row['id_member'])
			{
				$user_info['messages'] -= $row['numDeletedMessages'];
				if (!($row['is_read']))
					$user_info['unread_messages'] -= $row['numDeletedMessages'];
			}
		}
		$smfFunc['db_free_result']($request);

		// Do the actual deletion.
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}pm_recipients
			SET deleted = 1
			WHERE id_member IN (" . implode(', ', $owner) . ")
				AND deleted = 0$where", __FILE__, __LINE__);
	}

	// If sender and recipients all have deleted their message, it can be removed.
	$request = $smfFunc['db_query']('', "
		SELECT pm.id_pm, pmr.id_pm AS recipient
		FROM {$db_prefix}personal_messages AS pm
			LEFT JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.id_pm = pm.id_pm AND deleted = 0)
		WHERE pm.deleted_by_sender = 1
			" . str_replace('id_pm', 'pm.id_pm', $where) . "
		HAVING recipient IS null", __FILE__, __LINE__);
	$remove_pms = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$remove_pms[] = $row['id_pm'];
	$smfFunc['db_free_result']($request);

	if (!empty($remove_pms))
	{
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}personal_messages
			WHERE id_pm IN (" . implode(', ', $remove_pms) . ")", __FILE__, __LINE__);

		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}pm_recipients
			WHERE id_pm IN (" . implode(', ', $remove_pms) . ')', __FILE__, __LINE__);
	}
}

// Mark personal messages read.
function markMessages($personal_messages = null, $label = null, $owner = null)
{
	global $user_info, $db_prefix, $context, $smfFunc;

	if ($owner === null)
		$owner = $user_info['id'];

	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}pm_recipients
		SET is_read = is_read | 1
		WHERE id_member = $owner
			AND NOT (is_read & 1 >= 1)" . ($label === null ? '' : "
			AND FIND_IN_SET($label, labels)") . ($personal_messages !== null ? "
			AND id_pm IN (" . implode(', ', $personal_messages) . ")" : ''), __FILE__, __LINE__);

	//!!! Decide if we actually want to do this - I think it fasely shows no unread messages when at point of loading page they are not read.
	/*if ($owner == $user_info['id'])
	{
		foreach ($context['labels'] as $label)
			$context['labels'][(int) $label['id']]['unread_messages'] = 0;
	}*/

	// If something wasn't marked as read, get the number of unread messages remaining.
	if (db_affected_rows() > 0)
	{
		$result = $smfFunc['db_query']('', "
			SELECT labels, COUNT(*) AS num
			FROM {$db_prefix}pm_recipients
			WHERE id_member = $owner
				AND NOT (is_read & 1 >= 1)
			GROUP BY labels", __FILE__, __LINE__);
		$total_unread = 0;
		while ($row = $smfFunc['db_fetch_assoc']($result))
		{
			$total_unread += $row['num'];

			if ($owner != $user_info['id'])
				continue;

			$this_labels = explode(',', $row['labels']);
			foreach ($this_labels as $this_label)
				$context['labels'][(int) $this_label]['unread_messages'] += $row['num'];
		}
		$smfFunc['db_free_result']($result);

		updateMemberData($owner, array('unread_messages' => $total_unread));

		// If it was for the current member, reflect this in the $user_info array too.
		if ($owner == $user_info['id'])
			$user_info['unread_messages'] = $total_unread;
	}
}

// This function handles adding, deleting and editing labels on messages.
function ManageLabels()
{
	global $txt, $context, $db_prefix, $user_info, $scripturl, $smfFunc;

	// Build the link tree elements...
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=pm;sa=manlabels',
		'name' => $txt['pm_manage_labels']
	);

	$context['page_title'] = $txt['pm_manage_labels'];
	$context['sub_template'] = 'labels';

	$the_labels = array();
	// Add all existing labels to the array to save, slashing them as necessary...
	foreach ($context['labels'] as $label)
	{
		if ($label['id'] != -1)
			$the_labels[$label['id']] = $smfFunc['db_escape_string']($label['name']);
	}

	if (isset($_GET['sesc']))
	{
		// This will be for updating messages.
		$message_changes = array();
		$new_labels = array();
		$rule_changes = array();

		// Will most likely need this.
		LoadRules();

		// Adding a new label?
		if (isset($_POST['add']))
		{
			$_POST['label'] = strtr($smfFunc['htmlspecialchars'](trim($_POST['label'])), array(',' => '&#044;'));

			if ($smfFunc['strlen']($_POST['label']) > 30)
				$_POST['label'] = $smfFunc['substr']($_POST['label'], 0, 30);
			if ($_POST['label'] != '')
				$the_labels[] = $_POST['label'];
		}
		// Deleting an existing label?
		elseif (isset($_POST['delete'], $_POST['delete_label']))
		{
			$i = 0;
			foreach ($the_labels as $id => $name)
			{
				if (isset($_POST['delete_label'][$id]))
				{
					unset($the_labels[$id]);
					$message_changes[$id] = true;
				}
				else
					$new_labels[$id] = $i++;
			}
		}
		// The hardest one to deal with... changes.
		elseif (isset($_POST['save']) && !empty($_POST['label_name']))
		{
			$i = 0;
			foreach ($the_labels as $id => $name)
			{
				if ($id == -1)
					continue;
				elseif (isset($_POST['label_name'][$id]))
				{
					$_POST['label_name'][$id] = trim(strtr($smfFunc['htmlspecialchars']($_POST['label_name'][$id]), array(',' => '&#044;')));

					if ($smfFunc['strlen']($_POST['label_name'][$id]) > 30)
						$_POST['label_name'][$id] = $smfFunc['substr']($_POST['label_name'][$id], 0, 30);
					if ($_POST['label_name'][$id] != '')
					{
						$the_labels[(int) $id] = $_POST['label_name'][$id];
						$new_labels[$id] = $i++;
					}
					else
					{
						unset($the_labels[(int) $id]);
						$message_changes[(int) $id] = true;
					}
				}
				else
					$new_labels[$id] = $i++;
			}
		}

		// Save the label status.
		updateMemberData($user_info['id'], array('message_labels' => "'" . implode(',', $the_labels) . "'"));

		// Update all the messages currently with any label changes in them!
		if (!empty($message_changes))
		{
			$searchArray = array_keys($message_changes);

			if (!empty($new_labels))
			{
				for ($i = max($searchArray) + 1, $n = max(array_keys($new_labels)); $i <= $n; $i++)
					$searchArray[] = $i;
			}

			// Now find the messages to change.
			$request = $smfFunc['db_query']('', "
				SELECT id_pm, labels
				FROM {$db_prefix}pm_recipients
				WHERE FIND_IN_SET('" . implode("', labels) OR FIND_IN_SET('", $searchArray) . "', labels)
					AND id_member = $user_info[id]", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				// Do the long task of updating them...
				$toChange = explode(',', $row['labels']);

				foreach ($toChange as $key => $value)
					if (in_array($value, $searchArray))
					{
						if (isset($new_labels[$value]))
							$toChange[$key] = $new_labels[$value];
						else
							unset($toChange[$key]);
					}

				if (empty($toChange))
					$toChange[] = '-1';

				// Update the message.
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}pm_recipients
					SET labels = '" . implode(',', array_unique($toChange)) . "'
					WHERE id_pm = $row[id_pm]
						AND id_member = $user_info[id]", __FILE__, __LINE__);
			}
			$smfFunc['db_free_result']($request);

			// Now do the same the rules - check through each rule.
			foreach ($context['rules'] as $k => $rule)
			{
				// Each action...
				foreach ($rule['actions'] as $k2 => $action)
				{
					if ($action['t'] != 'lab' || !in_array($action['v'], $searchArray))
						continue;

					$rule_changes[] = $rule['id'];
					// If we're here we have a label which is either changed or gone...
					if (isset($new_labels[$action['v']]))
						$context['rules'][$k]['actions'][$k2]['v'] = $new_labels[$action['v']];
					else
						unset($context['rules'][$k]['actions'][$k2]);
				}
			}
		}

		// If we have rules to change do so now.
		if (!empty($rule_changes))
		{
			$rule_changes = array_unique($rule_changes);
			// Update/delete as appropriate.
			foreach ($rule_changes as $k => $id)
				if (!empty($context['rules'][$id]['actions']))
				{
					$smfFunc['db_query']('', "
						UPDATE {$db_prefix}pm_rules
						SET actions = '" . $smfFunc['db_escape_string'](serialize($context['rules'][$id]['actions'])) . "'
						WHERE id_rule = $id
							AND id_member = $user_info[id]", __FILE__, __LINE__);
					unset($rule_changes[$k]);
				}

			// Anything left here means it's lost all actions...
			if (!empty($rule_changes))
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}pm_rules
					WHERE id_rule IN (" . implode(', ', $rule_changes) . ")
							AND id_member = $user_info[id]", __FILE__, __LINE__);
		}

		// To make the changes appear right away, redirect.
		redirectexit('action=pm;sa=manlabels');
	}
}

// Edit Personal Message Settings
function MessageSettings()
{
	global $txt, $user_settings, $user_info, $db_prefix, $context, $db_prefix, $sourcedir, $smfFunc;

	// Need this for custom fields!
	require_once($sourcedir . '/Profile.php');

	// Are we saving?
	if (isset($_REQUEST['save']))
	{
		// Validate and set the ignorelist...
		$_POST['pm_ignore_list'] = preg_replace('~&amp;#(\d{4,5}|[2-9]\d{2,4}|1[2-9]\d);~', '&#$1;', $_POST['pm_ignore_list']);
		$_POST['pm_ignore_list'] = strtr(trim($_POST['pm_ignore_list']), array('\\\'' => '&#039;', "\n" => "', '", "\r" => '', '&quot;' => ''));

		if (preg_match('~(\A|,)\*(\Z|,)~s', $_POST['pm_ignore_list']) == 0)
		{
			$result = $smfFunc['db_query']('', "
				SELECT id_member
				FROM {$db_prefix}members
				WHERE member_name IN ('$_POST[pm_ignore_list]') OR real_name IN ('$_POST[pm_ignore_list]')
				LIMIT " . (substr_count($_POST['pm_ignore_list'], '\', \'') + 1), __FILE__, __LINE__);
			$_POST['pm_ignore_list'] = '';
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$_POST['pm_ignore_list'] .= $row['id_member'] . ',';
			$smfFunc['db_free_result']($result);

			// !!! Did we find all the members?
			$_POST['pm_ignore_list'] = substr($_POST['pm_ignore_list'], 0, -1);
		}
		else
			$_POST['pm_ignore_list'] = '*';

		// Save the member settings!
		updateMemberData($user_info['id'], array(
			'pm_ignore_list' => '\'' . $smfFunc['db_escape_string']($_POST['pm_ignore_list']) . '\'',
			'pm_email_notify' => (int) $_POST['pm_email_notify'],
			'pm_prefs' => (int) $_POST['pm_display_mode'],
		));

		// We'll save the theme settings too!
		makeThemeChanges($user_info['id'], $user_info['theme']);

		// Redirect to reload.
		redirectexit('action=pm;sa=settings');
	}

	// Tell the template what they are....
	$context['sub_template'] = 'message_settings';
	$context['page_title'] = $txt['pm_settings'];
	$context['send_email'] = $user_settings['pm_email_notify'];

	if ($user_settings['pm_ignore_list'] != '*')
	{
		$result = $smfFunc['db_query']('', "
			SELECT real_name
			FROM {$db_prefix}members
			WHERE FIND_IN_SET(id_member, '" . $user_settings['pm_ignore_list'] . "')
			LIMIT " . (substr_count($user_settings['pm_ignore_list'], ',') + 1), __FILE__, __LINE__);
		$pm_ignore_list = '';
		while ($row = $smfFunc['db_fetch_assoc']($result))
			$pm_ignore_list .= "\n" . $row['real_name'];
		$smfFunc['db_free_result']($result);

		$pm_ignore_list = substr($pm_ignore_list, 1);
	}
	else
		$pm_ignore_list = '*';

	// Get all their "buddies"...
	$result = $smfFunc['db_query']('', "
		SELECT real_name
		FROM {$db_prefix}members
		WHERE FIND_IN_SET(id_member, '" . $user_settings['buddy_list'] . "')
		LIMIT " . (substr_count($user_settings['buddy_list'], ',') + 1), __FILE__, __LINE__);
	$buddy_list = '';
	while ($row = $smfFunc['db_fetch_assoc']($result))
		$buddy_list .= "\n" . $row['real_name'];
	$smfFunc['db_free_result']($result);

	$context['buddy_list'] = substr($buddy_list, 1);
	$context['ignore_list'] = $pm_ignore_list;

	loadCustomFields($user_info['id'], 'pmprefs');
}

// Allows a user to report a personal message they receive to the administrator.
function ReportMessage()
{
	global $txt, $context, $scripturl, $sourcedir, $db_prefix;
	global $user_info, $language, $modSettings, $smfFunc;

	// Check that this feature is even enabled!
	if (empty($modSettings['enableReportPM']) || empty($_REQUEST['pmsg']))
		fatal_lang_error(1, false);

	$context['pm_id'] = (int) $_REQUEST['pmsg'];
	$context['page_title'] = $txt['pm_report_title'];

	// If we're here, just send the user to the template, with a few useful context bits.
	if (!isset($_REQUEST['report']))
	{
		$context['sub_template'] = 'report_message';

		// !!! I don't like being able to pick who to send it to.  Favoritism, etc. sucks.
		// Now, get all the administrators.
		$request = $smfFunc['db_query']('', "
			SELECT id_member, real_name
			FROM {$db_prefix}members
			WHERE id_group = 1 OR FIND_IN_SET(1, additional_groups)
			ORDER BY real_name", __FILE__, __LINE__);
		$context['admins'] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$context['admins'][$row['id_member']] = $row['real_name'];
		$smfFunc['db_free_result']($request);

		// How many admins in total?
		$context['admin_count'] = count($context['admins']);
	}
	// Otherwise, let's get down to the sending stuff.
	else
	{
		// First, pull out the message contents, and verify it actually went to them!
		$request = $smfFunc['db_query']('', "
			SELECT pm.subject, pm.body, pm.msgtime, pm.id_member_from, IFNULL(m.real_name, pm.from_name) AS senderName
			FROM {$db_prefix}personal_messages AS pm
				INNER JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.id_pm = pm.id_pm)
				LEFT JOIN {$db_prefix}members AS m ON (m.id_member = pm.id_member_from)
			WHERE pm.id_pm = $context[pm_id]
				AND pmr.id_member = $user_info[id]
				AND pmr.deleted = 0
			LIMIT 1", __FILE__, __LINE__);
		// Can only be a hacker here!
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error(1, false);
		list ($subject, $body, $time, $memberFromID, $memberFromName) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		// Remove the line breaks...
		$body = preg_replace('~<br( /)?' . '>~i', "\n", $body);

		// Get any other recipients of the email.
		$request = $smfFunc['db_query']('', "
			SELECT mem_to.id_member AS id_member_to, mem_to.real_name AS to_name, pmr.bcc
			FROM {$db_prefix}pm_recipients AS pmr
				LEFT JOIN {$db_prefix}members AS mem_to ON (mem_to.id_member = pmr.id_member)
			WHERE pmr.id_pm = $context[pm_id]
				AND pmr.id_member != $user_info[id]", __FILE__, __LINE__);
		$recipients = array();
		$hidden_recipients = 0;
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			// If it's hidden still don't reveal their names - privacy after all ;)
			if ($row['bcc'])
				$hidden_recipients++;
			else
				$recipients[] = '[url=' . $scripturl . '?action=profile;u=' . $row['id_member_to'] . ']' . $row['to_name'] . '[/url]';
		}
		$smfFunc['db_free_result']($request);

		if ($hidden_recipients)
			$recipients[] = sprintf($txt['pm_report_pm_hidden'], $hidden_recipients);

		// Now let's get out and loop through the admins.
		$request = $smfFunc['db_query']('', "
			SELECT id_member, real_name, lngfile
			FROM {$db_prefix}members
			WHERE (id_group = 1 OR FIND_IN_SET(1, additional_groups))
				" . (empty($_REQUEST['ID_ADMIN']) ? '' : 'AND id_member = ' . (int) $_REQUEST['ID_ADMIN']) . "
			ORDER BY lngfile", __FILE__, __LINE__);

		// Maybe we shouldn't advertise this?
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error(1, false);

		$memberFromName = un_htmlspecialchars($memberFromName);

		// Prepare the message storage array.
		$messagesToSend = array();
		// Loop through each admin, and add them to the right language pile...
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			// Need to send in the correct language!
			$cur_language = empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile'];

			if (!isset($messagesToSend[$cur_language]))
			{
				if (loadLanguage('PersonalMessage', $cur_language, false) === false)
					loadLanguage('InstantMessage', $cur_language);

				// Make the body.
				$report_body = str_replace(array('{REPORTER}', '{SENDER}'), array(un_htmlspecialchars($user_info['name']), $memberFromName), $txt['pm_report_pm_user_sent']);
				// !!! I don't think this handles slashes in the reason properly.
				$report_body .= "\n[b]$_REQUEST[reason][/b]\n\n";
				if (!empty($recipients))
					$report_body .= $txt['pm_report_pm_other_recipients'] . " " . implode(', ', $recipients) . "\n\n";
				$report_body .= $txt['pm_report_pm_unedited_below'] . "\n[quote author=" . (empty($memberFromID) ? '&quot;' . $memberFromName . '&quot;' : $memberFromName . ' link=action=profile;u=' . $memberFromID . ' date=' . $time) . "]\n" . un_htmlspecialchars($body) . '[/quote]';

				// Plonk it in the array ;)
				$messagesToSend[$cur_language] = array(
					'subject' => ($smfFunc['strpos']($subject, $txt['pm_report_pm_subject']) === false ? $txt['pm_report_pm_subject'] : '') . $subject,
					'body' => $report_body,
					'recipients' => array(
						'to' => array(),
						'bcc' => array()
					),
				);
			}

			// Add them to the list.
			$messagesToSend[$cur_language]['recipients']['to'][$row['id_member']] = $row['id_member'];
		}
		$smfFunc['db_free_result']($request);

		// Send a different email for each language.
		foreach ($messagesToSend as $lang => $message)
			sendpm($message['recipients'], $message['subject'], $message['body']);

		// Give the user their own language back!
		if (!empty($modSettings['userLanguage']))
		{
			if (loadLanguage('PersonalMessage', '', false) === false)
				loadLanguage('InstantMessage');
		}

		// Leave them with a template.
		$context['sub_template'] = 'report_message_complete';
	}
}

// List all rules, and allow adding/entering etc....
function ManageRules()
{
	global $txt, $context, $db_prefix, $user_info, $scripturl, $smfFunc;

	// The link tree - gotta have this :o
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=pm;sa=manrules',
		'name' => $txt['pm_manage_rules']
	);

	$context['page_title'] = $txt['pm_manage_rules'];
	$context['sub_template'] = 'rules';

	// Load them... load them!!
	LoadRules();

	// Likely to need all the groups!
	$request = $smfFunc['db_query']('', "
		SELECT mg.id_group, mg.group_name, IFNULL(gm.id_member, 0) AS can_moderate, mg.hidden
		FROM {$db_prefix}membergroups AS mg
			LEFT JOIN {$db_prefix}group_moderators AS gm ON (gm.id_group = mg.id_group AND gm.id_member = $user_info[id])
		WHERE mg.min_posts = -1
			AND mg.id_group != 3
		ORDER BY mg.group_name", __FILE__, __LINE__);
	$context['groups'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Hide hidden groups!
		if ($row['hidden'] && !$row['can_moderate'] && !allowedTo('manage_membergroups'))
			continue;

		$context['groups'][$row['id_group']] = $row['group_name'];
	}
	$smfFunc['db_free_result']($request);

	// Applying all rules?
	if (isset($_GET['apply']))
	{
		ApplyRules(true);
		redirectexit('action=pm;sa=manrules');
	}
	// Editing a specific one?
	if (isset($_GET['add']))
	{
		$context['rid'] = isset($_GET['rid']) && isset($context['rules'][$_GET['rid']])? (int) $_GET['rid'] : 0;
		$context['sub_template'] = 'add_rule';

		// Current rule information...
		if ($context['rid'])
		{
			$context['rule'] = $context['rules'][$context['rid']];
			$members = array();
			// Need to get member names!
			foreach ($context['rule']['criteria'] as $k => $criteria)
				if ($criteria['t'] == 'mid' && !empty($criteria['v']))
					$members[(int) $criteria['v']] = $k;

			if (!empty($members))
			{
				$request = $smfFunc['db_query']('', "
					SELECT id_member, member_name
					FROM {$db_prefix}members
					WHERE id_member IN (" . implode(', ', array_keys($members)) . ")", __FILE__, __LINE__);
				while ($row = $smfFunc['db_fetch_assoc']($request))
					$context['rule']['criteria'][$members[$row['id_member']]]['v'] = $row['member_name'];
				$smfFunc['db_free_result']($request);
			}
		}
		else
			$context['rule'] = array(
				'id' => '',
				'name' => '',
				'criteria' => array(),
				'actions' => array(),
				'logic' => 'and',
			);
	}
	// Saving?
	elseif (isset($_GET['save']))
	{
		$context['rid'] = isset($_GET['rid']) && isset($context['rules'][$_GET['rid']])? (int) $_GET['rid'] : 0;

		// Name is easy!
		$ruleName = trim($_POST['rule_name']);
		if (empty($ruleName))
			fatal_lang_error('pm_rule_no_name', false);

		// Sanity check...
		if (empty($_POST['ruletype']) || empty($_POST['acttype']))
			fatal_lang_error('pm_rule_no_criteria', false);

		// Let's do the criteria first - it's also hardest!
		$criteria = array();
		foreach ($_POST['ruletype'] as $ind => $type)
		{
			// Check everything is here...
			if ($type == 'gid' && (!isset($_POST['ruledefgroup'][$ind]) || !isset($context['groups'][$_POST['ruledefgroup'][$ind]])))
				continue;
			elseif ($type != 'bud' && !isset($_POST['ruledef'][$ind]))
				continue;

			// Members need to be found.
			if ($type == 'mid')
			{
				$name = trim($_POST['ruledef'][$ind]);
				$name = $smfFunc['db_escape_string'](stripslashes($name));
				$request = $smfFunc['db_query']('', "
					SELECT id_member
					FROM {$db_prefix}members
					WHERE real_name = '$name'
						OR member_name = '$name'", __FILE__, __LINE__);
				if ($smfFunc['db_num_rows']($request) == 0)
					continue;
				list ($memID) = $smfFunc['db_fetch_row']($request);
				$smfFunc['db_free_result']($request);

				$criteria[] = array('t' => 'mid', 'v' => $memID);
			}
			elseif ($type == 'bud')
				$criteria[] = array('t' => 'bud', 'v' => 1);
			elseif ($type == 'gid')
				$criteria[] = array('t' => 'gid', 'v' => (int) $_POST['ruledefgroup'][$ind]);
			elseif (in_array($type, array('sub', 'msg')) && trim($smfFunc['db_unescape_string']($_POST['ruledef'][$ind])) != '')
				$criteria[] = array('t' => $type, 'v' => trim($smfFunc['db_unescape_string']($_POST['ruledef'][$ind])));
				
		}

		// Also do the actions!
		$actions = array();
		$doDelete = 0;
		$isOr = $_POST['rule_logic'] == 'or' ? 1 : 0;
		foreach ($_POST['acttype'] as $ind => $type)
		{
			// Picking a valid label?
			if ($type == 'lab' && (!isset($_POST['labdef'][$ind]) || !isset($context['labels'][$_POST['labdef'][$ind] - 1])))
				continue;

			// Record what we're doing.
			if ($type == 'del')
				$doDelete = 1;
			elseif ($type == 'lab')
				$actions[] = array('t' => 'lab', 'v' => (int) $_POST['labdef'][$ind] - 1);
		}

		if (empty($criteria) || (empty($actions) && !$doDelete))
			fatal_lang_error('pm_rule_no_criteria', false);

		// What are we storing?
		$criteria = $smfFunc['db_escape_string'](serialize($criteria));
		$actions = $smfFunc['db_escape_string'](serialize($actions));

		// Create the rule?
		if (empty($context['rid']))
			$smfFunc['db_query']('', "
				INSERT INTO {$db_prefix}pm_rules
					(id_member, rule_name, criteria, actions, delete_pm, is_or)
				VALUES
					($user_info[id], '$ruleName', '$criteria', '$actions', $doDelete, $isOr)", __FILE__, __LINE__);
		else
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}pm_rules
				SET rule_name = '$ruleName', criteria = '$criteria', actions = '$actions',
					delete_pm = $doDelete, is_or = $isOr
				WHERE id_rule = $context[rid]
					AND id_member = $user_info[id]", __FILE__, __LINE__);

		redirectexit('action=pm;sa=manrules');
	}
	// Deleting?
	elseif (isset($_POST['delselected']) && !empty($_POST['delrule']))
	{
		$toDelete = array();
		foreach ($_POST['delrule'] as $k => $v)
			$toDelete[] = (int) $k;

		if (!empty($toDelete))
			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}pm_rules
				WHERE id_rule IN (" . implode(', ', $toDelete) . ")
					AND id_member = $user_info[id]", __FILE__, __LINE__);

		redirectexit('action=pm;sa=manrules');
	}	
}

// This will apply rules to all unread messages. If all_messages is set will, clearly, do it to all!
function ApplyRules($all_messages = false)
{
	global $user_info, $smfFunc, $db_prefix, $context, $options;

	// Want this - duh!
	loadRules();

	// No rules?
	if (empty($context['rules']))
		return;

	// Just unread ones?
	$ruleQuery = $all_messages ? '' : " AND pmr.is_new = 1";

	//!!! Apply all should have timeout protection!
	// Get all the messages that match this.
	$request = $smfFunc['db_query']('', "
		SELECT
			pmr.id_pm, pm.id_member_from, pm.subject, pm.body, mem.id_group, pmr.labels
		FROM {$db_prefix}pm_recipients AS pmr
			INNER JOIN {$db_prefix}personal_messages AS pm ON (pm.id_pm = pmr.id_pm)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = pm.id_member_from)
		WHERE pmr.id_member = $user_info[id]
			AND pmr.deleted = 0
			$ruleQuery", __FILE__, __LINE__);
	$actions = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		foreach ($context['rules'] as $rule)
		{
			$match = false;
			// Loop through all the criteria hoping to make a match.
			foreach ($rule['criteria'] as $c)
			{
				$match = false;
				if (($c['t'] == 'mid' && $c['v'] == $row['id_member_from']) || ($c['t'] == 'gid' && $c['v'] == $row['id_group']) || ($c['t'] == 'sub' && strpos($row['subject'], $c['v']) !== false) || ($c['t'] == 'msg' && strpos($row['body'], $c['v']) !== false))
					$match = true;
				// If we're adding and one criteria don't match then we stop!
				elseif ($rule['logic'] == 'and')
				{
					$match = false;
					break;
				}
			}

			// If we have a match the rule must be true - act!
			if ($match)
			{
				if ($rule['delete'])
					$actions['deletes'][] = $row['id_pm'];
				else
				{
					foreach ($rule['actions'] as $a)
					{
						if ($a['t'] == 'lab')
						{
							// Get a basic pot started!
							if (!isset($actions['labels'][$row['id_pm']]))
								$actions['labels'][$row['id_pm']] = explode(',', $row['labels']);
							$actions['labels'][$row['id_pm']][] = $a['v'];
						}
					}
    			}
			}
  		}
	}
	$smfFunc['db_free_result']($request);

	// Deletes are easy!
	if (!empty($actions['deletes']))
		deleteMessages($actions['deletes']);

	// Relabel?
	if (!empty($actions['labels']))
	{
		foreach ($actions['labels'] as $pm => $labels)
		{
			// Quickly check each label is valid!
			$realLabels = array();
			foreach ($context['labels'] as $label)
				if (in_array($label['id'], $labels) && ($label['id'] != -1 || empty($options['pm_remove_inbox_label'])))
					$realLabels[] = $label['id'];

			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}pm_recipients
				SET labels = '" . (empty($realLabels) ? '' : implode(',', $realLabels)) . "'
				WHERE id_pm = $pm
					AND id_member = $user_info[id]", __FILE__, __LINE__);
		}
	}
}

// Load up all the rules for the current user.
function LoadRules($reload = false)
{
	global $user_info, $context, $smfFunc, $db_prefix;

	if (isset($context['rules']) && !$reload)
		return;

	$request = $smfFunc['db_query']('', "
		SELECT
			id_rule, rule_name, criteria, actions, delete_pm, is_or
		FROM {$db_prefix}pm_rules
		WHERE id_member = $user_info[id]", __FILE__, __LINE__);
	$context['rules'] = array();
	// Simply fill in the data!
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['rules'][$row['id_rule']] = array(
			'id' => $row['id_rule'],
			'name' => $row['rule_name'],
			'criteria' => unserialize($row['criteria']),
			'actions' => unserialize($row['actions']),
			'delete' => $row['delete_pm'],
			'logic' => $row['is_or'] ? 'or' : 'and',
		);

		if ($row['delete_pm'])
			$context['rules'][$row['id_rule']]['actions'][] = array('t' => 'del', 'v' => 1);
 	}
 	$smfFunc['db_free_result']($request);
}

?>