<?php
/**********************************************************************************
* ManageMail.php                                                                  *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 2.1                                    *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2008 by:     Simple Machines LLC (http://www.simplemachines.org) *
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
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['mailqueue_title'],
		'help' => '',
		'description' => $txt['mailqueue_desc'],
	);

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']]();
}

// Display the mail queue...
function BrowseMailQueue()
{
	global $scripturl, $context, $modSettings, $txt, $smcFunc;
	global $sourcedir;

	// How many items do we have?
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS queueSize, MIN(time_sent) AS oldest
		FROM {db_prefix}mail_queue',
		array(
		)
	);
	list ($mailQueueSize, $mailOldest) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['oldest_mail'] = empty($mailOldest) ? $txt['mailqueue_oldest_not_available'] : time_since(time() - $mailOldest);
	$context['mail_queue_size'] = comma_format($mailQueueSize);

	$listOptions = array(
		'id' => 'mail_queue',
		'title' => $txt['mailqueue_browse'],
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=mailqueue',
		'default_sort_col' => 'age',
		'no_items_label' => $txt['mailqueue_no_items'],
		'get_items' => array(
			'function' => 'list_getMailQueue',
		),
		'get_count' => array(
			'function' => 'list_getMailQueueSize',
		),
		'columns' => array(
			'subject' => array(
				'header' => array(
					'value' => $txt['mailqueue_subject'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						return strlen($rowData[\'subject\']) > 50 ? sprintf(\'%1$s...\', substr(htmlspecialchars($rowData[\'subject\']), 0, 47)) : htmlspecialchars($rowData[\'subject\']);
					'),
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'subject',
					'reverse' => 'subject DESC',
				),
			),
			'recipient' => array(
				'header' => array(
					'value' => $txt['mailqueue_recipient'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="mailto:%1$s">%1$s</a>',
						'params' => array(
							'recipient' => true,
						),
					),
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'recipient',
					'reverse' => 'recipient DESC',
				),
			),
			'priority' => array(
				'header' => array(
					'value' => $txt['mailqueue_priority'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						// We probably have a text label with your priority.
						$txtKey = sprintf(\'mq_priority_%1$s\', $rowData[\'priority\']);

						// But if not, revert to priority 3.
						return isset($txt[$txtKey]) ? $txt[$txtKey] : $txt[\'mq_priority_3\'];
					'),
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'priority DESC',
					'reverse' => 'priority',
				),
			),
			'age' => array(
				'header' => array(
					'value' => $txt['mailqueue_age'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						return time_since(time() - $rowData[\'time_sent\']);
					'),
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'priority DESC',
					'reverse' => 'priority',
				),
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '[<a href="' . $scripturl . '?action=admin;area=mailqueue;sa=clear;sesc=' . $context['session_id'] . '" onclick="return confirm(\'' . $txt['mailqueue_clear_list_warning'] . '\');">' . $txt['mailqueue_clear_list'] . '</a>]',
				'class' => 'titlebg',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	loadTemplate('ManageMail');
	$context['sub_template'] = 'browse';
	return;

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=mailqueue;sa=browse', $_REQUEST['start'], $mailQueueSize, 20);
	$context['start'] = $_REQUEST['start'];

	// Even if it's disabled we should still show the mail queue, in case there's stuff left!
	$request = $smcFunc['db_query']('', '
		SELECT id_mail, time_sent, recipient, priority, subject
		FROM {db_prefix}mail_queue
		ORDER BY id_mail ASC
		LIMIT ' . $context['start'] . ', 20',
		array(
		)
	);
	$context['mails'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
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
	$smcFunc['db_free_result']($request);

	// Setup the template stuff.
	loadTemplate('ManageMail');
	$context['sub_template'] = 'browse';
}

function list_getMailQueue($start, $items_per_page, $sort)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_mail, time_sent, recipient, priority, subject
		FROM {db_prefix}mail_queue
		ORDER BY ' . $sort . '
		LIMIT ' . $start . ', ' . $items_per_page,
		array(
		)
	);
	$mails = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$mails[] = $row;
	$smcFunc['db_free_result']($request);

	return $mails;
}

function list_getMailQueueSize()
{
	global $smcFunc;

	// How many items do we have?
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS queueSize
		FROM {db_prefix}mail_queue',
		array(
		)
	);
	list ($mailQueueSize) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $mailQueueSize;
}



function ModifyMailSettings($return_config = false)
{
	global $txt, $scripturl, $context, $settings, $birthdayEmails, $modSettings;

	loadLanguage('EmailTemplates');

	$body = $birthdayEmails[empty($modSettings['birthday_email']) ? 'karlbenson1' : $modSettings['birthday_email']]['body'];
	$subject = $birthdayEmails[empty($modSettings['birthday_email']) ? 'karlbenson1' : $modSettings['birthday_email']]['subject'];

	$emails = array();
	foreach ($birthdayEmails as $index => $dummy)
		$emails[$index] = $index;

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
			array('select', 'birthday_email', $emails, 'value' => empty($modSettings['birthday_email']) ? 'karlbenson1' : $modSettings['birthday_email'], 'javascript' => 'onchange="fetch_birthday_preview()"'),
			'birthday_subject' => array('text', 'birthday_subject', 'value' => $birthdayEmails[empty($modSettings['birthday_email']) ? 'karlbenson1' : $modSettings['birthday_email']]['subject'], 'disabled' => true, 'size' => strlen($subject) + 3),
			'birthday_body' => array('large_text', 'birthday_body', 'value' => $body, 'disabled' => true, 'size' => (strlen($body) / 20) * 1.5),
		'',

	);

	if ($return_config)
		return $config_vars;

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

		// We don't want to save the subject and body previews.
		unset($config_vars['birthday_subject']);
		unset($config_vars['birthday_body']);

		saveDBSettings($config_vars);
		redirectexit('action=admin;area=mailqueue;sa=settings');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=mailqueue;save;sa=settings';
	$context['settings_title'] = $txt['mailqueue_settings'];

	prepareDBSettingContext($config_vars);

	$context['settings_insert_above'] = '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var bDay = new Array();';

	foreach ($birthdayEmails as $index => $email)
	{
		// Remove the newlines and count them.
		$newlines = count(explode("\n", $email['body']));
		$email['body'] = str_replace("\n", '<br />', $email['body']);
		$context['settings_insert_above'] .= '
			bDay[\'' . $index . '\'] = {
				subject: \'' . addslashes($email['subject']) . '\',
				body: \'' . addslashes(str_replace("\r", '', $email['body'])) . '\',
				newlines: ' . $newlines . '
			};';
	}
	$context['settings_insert_above'] .= '
		function fetch_birthday_preview()
		{
			var index = document.getElementById(\'birthday_email\').value;
			document.getElementById(\'birthday_subject\').value = bDay[index][\'subject\'];
			document.getElementById(\'birthday_subject\').size = bDay[index][\'subject\'].length + 2;
			document.getElementById(\'birthday_body\').value = bDay[index][\'body\'].replace(/<br \/>/g, "\n");
			document.getElementById(\'birthday_body\').rows = bDay[index][\'body\'].length / 30 + bDay[index][\'newlines\'];
		}
	// ]]></script>';
}

// This function clears the mail queue of all emails, and at the end redirects to browse.
function ClearMailQueue()
{
	global $sourcedir, $smcFunc;

	checkSession('get');

	// This is certainly needed!
	require_once($sourcedir . '/ScheduledTasks.php');

	// If we don't yet have the total to clear, find it.
	if (!isset($_GET['te']))
	{
		// How many items do we have?
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*) AS queueSize
			FROM {db_prefix}mail_queue',
			array(
			)
		);
		list ($_GET['te']) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
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