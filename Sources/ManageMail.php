<?php
/**********************************************************************************
* ManageMail.php                                                                  *
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

/*	This file is all about mail, how we love it so. In particular it handles the admin side of
	mail configuration, as well as reviewing the mail queue - if enabled.

	void ManageMail()
		// !!

	void BrowseMailQueue()
		// !!

	void ModifyMailSettings()
		// !!

	void ClearMailQueue()
		// !!

*/

// This function passes control through to the relevant section
function ManageMail()
{
	global $context, $txt, $scripturl, $modSettings, $sourcedir;

	// You need to be an admin to edit settings!
	isAllowedTo('admin_forum');

	loadLanguage('Help');
	loadLanguage('ManageMail');

	// We'll need the utility functions from here.
	require_once($sourcedir . '/ManageServer.php');

	$context['page_title'] = $txt['mailqueue_title'];
	$context['sub_template'] = 'show_settings';

	$subActions = array(
		'browse' => 'BrowseMailQueue',
		'clear' => 'ClearMailQueue',
		'settings' => 'ModifyMailSettings',
	);

	// By default we want to browse
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'browse';
	$context['sub_action'] = $_REQUEST['sa'];

	// Load up all the tabs...
	$context['admin_tabs'] = array(
		'title' => $txt['mailqueue_title'],
		'help' => '',
		'description' => $txt['mailqueue_desc'],
		'tabs' => array(
			'browse' => array(
				'title' => $txt['mailqueue_browse'],
				'href' => $scripturl . '?action=admin;area=mailqueue;sa=browse;sesc=' . $context['session_id'],
			),
			'settings' => array(
				'title' => $txt['mailqueue_settings'],
				'href' => $scripturl . '?action=admin;area=mailqueue;sa=settings;sesc=' . $context['session_id'],
				'is_last' => true,
			),
		),
	);

	// Select the right tab based on the sub action.
	if (isset($context['admin_tabs']['tabs'][$context['sub_action']]))
		$context['admin_tabs']['tabs'][$context['sub_action']]['is_selected'] = true;
	else
		$context['admin_tabs']['tabs']['browse']['is_selected'] = true;

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']]();
}

// Display the mail queue...
function BrowseMailQueue()
{
	global $scripturl, $context, $db_prefix, $modSettings, $txt, $smfFunc;

	// How many items do we have?
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*) AS queueSize, MIN(time_sent) AS oldest
		FROM {$db_prefix}mail_queue", __FILE__, __LINE__);
	list ($mailQueueSize, $mailOldest) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$context['oldest_mail'] = empty($mailOldest) ? 'N/A' : time_since(time() - $mailOldest);
	$context['mail_queue_size'] = comma_format($mailQueueSize);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=mailqueue;sa=browse', $_REQUEST['start'], $mailQueueSize, 20);
	$context['start'] = $_REQUEST['start'];

	// Even if it's disabled we should still show the mail queue, incase there's stuff left!
	$request = $smfFunc['db_query']('', "
		SELECT id_mail, time_sent, recipient, priority, subject
		FROM {$db_prefix}mail_queue
		ORDER BY id_mail ASC
		LIMIT $context[start], 20", __FILE__, __LINE__);
	$context['mails'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['mails'][] = array(
			'id' => $row['id_mail'],
			'time' => timeformat($row['time_sent']),
			'age' => time_since(time() - $row['time_sent']),
			'recipient' => '<a href="mailto:' . $row['recipient'] . '">' . $row['recipient'] . '</a>',
			'priority' => $row['priority'],
			'priority_text' => isset($txt['mq_priority_' . $row['priority']]) ? $txt['mq_priority_' . $row['priority']] : $txt['mq_priority_3'],
			'subject' => strlen($row['subject']) > 50 ? substr($row['subject'], 0, 47) . '...' : $row['subject'],
		);
	}
	$smfFunc['db_free_result']($request);

	// Setup the template stuff.
	loadTemplate('ManageMail');
	$context['sub_template'] = 'browse';
}

function ModifyMailSettings()
{
	global $txt, $scripturl, $context, $settings;

	$config_vars = array(
			// Mail queue stuff, this rocks ;)
			array('check', 'mail_queue'),
			array('int', 'mail_limit'),
			array('int', 'mail_quantity'),
		'',
			// SMTP stuff.
			array('select', 'mail_type', array($txt['mail_type_default'], 'SMTP')),
			array('text', 'smtp_host'),
			array('text', 'smtp_port'),
			array('text', 'smtp_username'),
			array('password', 'smtp_password'),
		'',
	);

	// Saving?
	if (isset($_GET['save']))
	{
		// Make the SMTP password a little harder to see in a backup etc.
		if (!empty($_POST['smtp_password'][1]))
		{
			$_POST['smtp_password'][0] = base64_encode($_POST['smtp_password'][0]);
			$_POST['smtp_password'][1] = base64_encode($_POST['smtp_password'][1]);
		}
		checkSession();

		saveDBSettings($config_vars);
		redirectexit('action=admin;area=mailqueue;sa=settings');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=mailqueue;save;sa=settings';
	$context['settings_title'] = $txt['mailqueue_settings'];

	prepareDBSettingContext($config_vars);
}

// This function clears the mail queue of all emails, and at the end redirects to browse.
function ClearMailQueue()
{
	global $sourcedir, $db_prefix, $smfFunc;

	checkSession('get');

	// This is certainly needed!
	require_once($sourcedir . '/ScheduledTasks.php');

	// If we don't yet have the total to clear, find it.
	if (!isset($_GET['te']))
	{
		// How many items do we have?
		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*) AS queueSize
			FROM {$db_prefix}mail_queue", __FILE__, __LINE__);
		list ($_GET['te']) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
	}
	else
		$_GET['te'] = (int) $_GET['te'];

	$_GET['sent'] = isset($_GET['sent']) ? (int) $_GET['sent'] : 0;

	// Send 50 at a time, then go for a break...
	while (ReduceMailQueue(50, true) === true)
	{
		// Sent another 50.
		$_GET['sent'] += 50;
		pauseMailQueueClear();
	}

	return BrowseMailQueue();
}

// Used for pausing the mail queue.
function pauseMailQueueClear()
{
	global $context, $txt, $time_start;

	// Try get more time...
	@set_time_limit(600);
	if (function_exists('apache_reset_timeout'))
		apache_reset_timeout();

	// Have we already used our maximum time?
	if (time() - array_sum(explode(' ', $time_start)) < 5)
		return;

	$context['continue_get_data'] = '?action=admin;area=mailqueue;sa=clear;te=' . $_GET['te'] . ';sent=' . $_GET['sent'] . ';sesc=' . $context['session_id'];
	$context['page_title'] = $txt['not_done_title'];
	$context['continue_post_data'] = '';
	$context['continue_countdown'] = '2';
	$context['sub_template'] = 'not_done';

	// Keep browse selected.
	$context['selected'] = 'browse';

	// What percent through are we?
	$context['continue_percent'] = round(($_GET['sent'] / $_GET['te']) * 100, 1);

	// Never more than 100%!
	$context['continue_percent'] = min($context['continue_percent'], 100);

	obExit();
}

// Little function to calculate how long ago a time was.
function time_since($time_diff)
{
	global $txt;

	if ($time_diff < 0)
		$time_diff = 0;

	// Just do a bit of an if fest...
	if ($time_diff > 86400)
	{
		$days = round($time_diff / 86400, 1);
		return sprintf($days == 1 ? $txt['mq_day'] : $txt['mq_days'], $time_diff / 86400);
	}
	// Hours?
	elseif ($time_diff > 3600)
	{
		$hours = round($time_diff / 3600, 1);
		return sprintf($hours == 1 ? $txt['mq_hour'] : $txt['mq_hours'], $hours);
	}
	// Minutes?
	elseif ($time_diff > 60)
	{
		$minutes = (int) ($time_diff / 60);
		return sprintf($minutes == 1 ? $txt['mq_minute'] : $txt['mq_minutes'], $minutes);
	}
	// Otherwise must be second
	else
		return sprintf($time_diff == 1 ? $txt['mq_second'] : $txt['mq_seconds'], $time_diff);
}

?>