<?php
/**********************************************************************************
* ScheduledTasks.php                                                              *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 1                                       *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
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

/*	This file is automatically called and handles all manner of scheduled things. 

	void AutoTask()
		//!!!

	void scheduled_approval_notification()
		// !!!

	void scheduled_daily_maintenance()
		// !!!

	void scheduled_auto_optimize()
		// !!!

	void scheduled_daily_digest()
		// !!!

	void scheduled_weekly_digest()
		// !!!

	void ReduceMailQueue(int number, bool override)
		// !!!

	void CalculateNextTrigger(array tasks)
		// !!!

	int next_time(int regularity, char unit, int offset)
		// !!!

	void loadEssentialThemeData()
		// !!!

	void scheduled_fetchSMfiles()
		// !!!

	void scheduled_birthdayemails()
		// !!!
*/

// This function works out what to do!
function AutoTask()
{
	global $db_prefix, $time_start, $modSettings, $smfFunc;

	// Special case for doing the mail queue.
	if (isset($_GET['scheduled']) && $_GET['scheduled'] == 'mailq')
		ReduceMailQueue();
	else
	{
		// Select the next task to do.
		$request = $smfFunc['db_query']('', "
			SELECT id_task, task, next_time, time_offset, time_regularity, time_unit
			FROM {$db_prefix}scheduled_tasks
			WHERE disabled = 0
				AND next_time <= " . time() . "
			ORDER BY next_time ASC
			LIMIT 1", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) != 0)
		{
			// The two important things really...
			$row = $smfFunc['db_fetch_assoc']($request);

			// When should this next be run?
			$next_time = next_time($row['time_regularity'], $row['time_unit'], $row['time_offset']);

			// How long in seconds it the gap?
			$duration = $row['time_regularity'];
			if ($row['time_unit'] == 'm')
				$duration *= 60;
			elseif ($row['time_unit'] == 'h')
				$duration *= 3600;
			elseif ($row['time_unit'] == 'd')
				$duration *= 86400;
			elseif ($row['time_unit'] == 'w')
				$duration *= 604800;

			// If we were really late running this task actually skip the next one.
			if (time() + ($duration / 2) > $next_time)
				$next_time += $duration;

			// Update it now, so no others run this!
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}scheduled_tasks
				SET next_time = $next_time
				WHERE id_task = $row[id_task]
					AND next_time = $row[next_time]", __FILE__, __LINE__);
			$affected_rows = db_affected_rows();

			// The function must exist or we are wasting our time, plus do some timestamp checking, and database check!
			if (function_exists('scheduled_' . $row['task']) && (!isset($_GET['ts']) || $_GET['ts'] == $row['next_time']) && $affected_rows)
			{
				ignore_user_abort(true);

				// Do the task...
				$completed = call_user_func('scheduled_' . $row['task']);

				// Log that we did it ;)
				if ($completed)
				{
					$total_time = round(array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)), 3);
					$smfFunc['db_query']('', "
						INSERT INTO {$db_prefix}log_scheduled_tasks
							(id_task, time_run, time_taken)
						VALUES
							($row[id_task], " . time() . ", $total_time)", __FILE__, __LINE__);
				}
			}
		}
		$smfFunc['db_free_result']($request);

		// Get the next timestamp right.
		$request = $smfFunc['db_query']('', "
			SELECT next_time
			FROM {$db_prefix}scheduled_tasks
			WHERE disabled = 0
			ORDER BY next_time ASC
			LIMIT 1", __FILE__, __LINE__);
		// No new task scheduled yet?
		if ($smfFunc['db_num_rows']($request) === 0)
			$nextEvent = time() + 86400;
		else
			list ($nextEvent) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		updateSettings(array('next_task_time' => $nextEvent));
	}

	// Shall we return?
	if (!isset($_GET['scheduled']))
		return true;

	// Finally, send some stuff...
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	exit;
}

// Function to sending out approval notices to moderators etc.
function scheduled_approval_notification()
{
	global $db_prefix, $scripturl, $modSettings, $mbname, $txt, $sourcedir, $smfFunc;

	// Grab all the items awaiting approval and sort type then board - clear up any things that are no longer relevant.
	$request = $smfFunc['db_query']('', "
		SELECT aq.id_msg, aq.id_attach, aq.id_event, m.id_topic, m.id_board, m.subject, t.id_first_msg,
			b.id_profile
		FROM {$db_prefix}approval_queue AS aq
			INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = aq.id_msg)
			INNER JOIN {$db_prefix}topics AS t ON (t.id_topic = m.id_topic)
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board)", __FILE__, __LINE__);
	$notices = array();
	$profiles = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// If this is no longer around we'll ignore it.
		if (empty($row['id_topic']))
			continue;

		// What type is it?
		if ($row['id_first_msg'] && $row['id_first_msg'] == $row['id_msg'])
			$type = 'topic';
		elseif ($row['id_attach'])
			$type = 'attach';
		else
			$type = 'msg';

		// Add it to the array otherwise.
		$notices[$row['id_board']][$type][] = array(
			'subject' => $row['subject'],
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#msg' . $row['id_msg'],
		);

		// Store the profile for a bit later.
		$profiles[$row['id_board']] = $row['id_profile'];
	}
	$smfFunc['db_free_result']($request);

	// Delete it all!
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}approval_queue", __FILE__, __LINE__);

	// If nothing quit now.
	if (empty($notices))
		return true;

	// Now we need to think about finding out *who* can approve - this is hard!

	// First off, get all the groups with this permission and sort by board.
	$request = $smfFunc['db_query']('', "
		SELECT id_group, id_profile, add_deny
		FROM {$db_prefix}board_permissions
		WHERE permission = 'approve_posts'
			AND id_profile IN (" . implode(', ', $profiles) . ")", __FILE__, __LINE__);
	$perms = array();
	$addGroups = array(1);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Sorry guys, but we have to ignore guests AND members - it would be too many otherwise.
		if ($row['id_group'] < 2)
			continue;

		$perms[$row['id_profile']][$row['add_deny'] ? 'add' : 'deny'][] = $row['id_group'];

		// Anyone who can access has to be considered.
		if ($row['add_deny'])
			$addGroups[] = $row['id_group'];
	}
	$smfFunc['db_free_result']($request);

	// Grab the moderators if they have permission!
	$mods = array();
	$members = array();
	if (in_array(2, $addGroups))
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_member, id_board
			FROM {$db_prefix}moderators", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			$mods[$row['id_member']][$row['id_board']] = true;
			// Make sure they get included in the big loop.
			$members[] = $row['id_member'];
		}
		$smfFunc['db_free_result']($request);
	}

	// Come along one and all... until we reject you ;)
	$request = $smfFunc['db_query']('', "
		SELECT id_member, real_name, email_address, lngfile, id_group, additional_groups, mod_prefs
		FROM {$db_prefix}members
		WHERE id_group IN (" . implode(', ', $addGroups) . ")
			OR FIND_IN_SET(" . implode(', additional_groups) OR FIND_IN_SET(', $addGroups) . ", additional_groups)
			" . (empty($members) ? '' : " OR id_member IN (" . implode(', ', $members) . ")") . "
		ORDER BY lngfile", __FILE__, __LINE__);
	$members = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Check they are interested.
		if (!empty($row['mod_prefs']))
		{
			list(,, $pref_binary) = explode('|', $row['mod_prefs']);
			if (!($pref_binary & 4))
				continue;
		}

		$members[$row['id_member']] = array(
			'id' => $row['id_member'],
			'groups' => array_merge(explode(',', $row['additional_groups']), array($row['id_group'])),
			'language' => $row['lngfile'],
			'email' => $row['email_address'],
			'name' => $row['real_name'],		
		);
	}
	$smfFunc['db_free_result']($request);

	// Get the mailing stuff.
	require_once($sourcedir . '/Subs-Post.php');
	// Need the below for loadLanguage to work!
	loadEssentialThemeData();

	// Finally, loop through each member, work out what they can do, and send it.
	foreach ($members as $id => $member)
	{
		$emailbody = '';

		// Load the language file as required.
		if (empty($current_language) || $current_language != $member['language'])
			$current_language = loadLanguage('Admin', $member['language'], false);

		// Loop through each notice...
		foreach ($notices as $board => $notice)
		{
			$access = false;

			// Can they mod in this board?
			if (isset($mods[$id][$board]))
				$access = true;

			// Do the group check...
			if (!$access && isset($perms[$profiles[$board]]['add']))
			{
				// They can access?!
				if (array_intersect($perms[$profiles[$board]]['add'], $member['groups']))
					$access = true;

				// If they have deny rights don't consider them!
				if (isset($perms[$profiles[$board]]['deny']))
					if (array_intersect($perms[$profiles[$board]]['deny'], $member['groups']))
						$access = false;
			}

			// Finally, fix it for admins!
			if (in_array(1, $member['groups']))
				$access = true;

			// If they can't access it then give it a break!
			if (!$access)
				continue;

			foreach ($notice as $type => $items)
			{
				// Build up the top of this section.
				$emailbody .= $txt['scheduled_approval_email_' . $type] . "\n" .
					"------------------------------------------------------\n";

				foreach ($items as $item)
					$emailbody .= $item['subject'] . ' - ' . $item['href'] . "\n";

				$emailbody .= "\n";
			}
		}

		if ($emailbody == '')
			continue;

		$replacements = array(
			'REALNAME' => $member['name'],
			'BODY' => $emailbody,
		);

		$emaildata = loadEmailTemplate('scheduled_approval', $replacements);

		// Send the actual email.
		sendmail($member['email'], $emaildata['subject'], $emaildata['body']);
	}

	// All went well!
	return true;
}

// Do some daily cleaning up.
function scheduled_daily_maintenance()
{
	global $smfFunc, $db_prefix, $modSettings;

	// First clean out the data cache.
	clean_cache('data');

	// Then delete some settings that needn't be set if they are otherwise empty.
	$emptySettings = array('warning_mute', 'warning_moderate', 'warning_watch', 'warning_show');

	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}settings
		WHERE variable IN ('" . implode("', '", $emptySettings) . "')
			AND (value = 0 OR value = '')", __FILE__, __LINE__);

	// If warning decrement is enabled and we have people who have not had a new warning in 24 hours, lower their warning level.
	list (, , $modSettings['warning_decrement']) = explode(',', $modSettings['warning_settings']);
	if ($modSettings['warning_decrement'])
	{
		// Find every member who has a warning level...
		$request = $smfFunc['db_query']('', "
			SELECT id_member, warning
			FROM {$db_prefix}members
			WHERE warning > 0", __FILE__, __LINE__);
		$members = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$members[$row['id_member']] = $row['warning'];
		$smfFunc['db_free_result']($request);

		// Have some members to check?
		if (!empty($members))
		{
			// Find out when they were last warned.
			$request = $smfFunc['db_query']('', "
				SELECT id_recipient, MAX(log_time) AS last_warning
				FROM {$db_prefix}log_comments
				WHERE id_recipient IN (" . implode(',', array_keys($members)) . ")
					AND comment_type = 'warning'
				GROUP BY id_recipient", __FILE__, __LINE__);
			$member_changes = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				// More than 24 hours ago?
				if ($row['last_warning'] <= time() - 86400)
					$member_changes[] = array(
						'id' => $row['id_recipient'],
						'warning' => $members[$row['id_recipient']] >= $modSettings['warning_decrement'] ? $members[$row['id_recipient']] - $modSettings['warning_decrement'] : 0,
					);
			}
			$smfFunc['db_free_result']($request);

			// Have some members to change?
			if (!empty($member_changes))
				foreach ($member_changes as $change)
					$smfFunc['db_query']('', "
						UPDATE {$db_prefix}members
						SET warning = $change[warning]
						WHERE id_member = $change[id]", __FILE__, __LINE__);
		}
	}

	// Log we've done it...
	return true;
}

// Auto optimize the database?
function scheduled_auto_optimize()
{
	global $db_prefix, $modSettings, $smfFunc;

	// By default do it now!
	$delay = false;
	// As a kind of hack, if the server load is too great delay, but only by a bit!
	if (!empty($modSettings['load_average']) && !empty($modSettings['loadavg_auto_opt']) && $modSettings['load_average'] >= $modSettings['loadavg_auto_opt'])
		$delay = true;

	// Otherwise are we restricting the number of people online for this?
	if (!empty($modSettings['autoOptMaxOnline']))
	{
		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}log_online", __FILE__, __LINE__);
		list ($dont_do_it) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		if ($dont_do_it > $modSettings['autoOptMaxOnline'])
			$delay = true;
	}

	// If we are gonna delay, do so now!
	if ($delay)
		return false;

	db_extend();

	// Get all the tables.
	$tables = $smfFunc['db_list_tables']();

	// Actually do the optimisation.
	foreach ($tables as $table)
		$smfFunc['db_optimize_table']($table);

	// Return for the log...
	return true;
}

// Send out a daily email of all subscribed topics.
function scheduled_daily_digest()
{
	global $db_prefix, $is_weekly, $txt, $mbname, $scripturl, $sourcedir, $smfFunc, $context, $modSettings;

	// We'll want this...
	require_once($sourcedir . '/Subs-Post.php');
	loadEssentialThemeData();

	$is_weekly = !empty($is_weekly) ? 1 : 0;

	// Right - get all the notification data FIRST.
	$request = $smfFunc['db_query']('', "
		SELECT ln.id_topic, COALESCE(t.id_board, ln.id_board) AS id_board, mem.email_address, mem.member_name, mem.notify_types,
			mem.lngfile, mem.id_member
		FROM {$db_prefix}log_notify AS ln
			INNER JOIN {$db_prefix}members AS mem ON (mem.id_member = ln.id_member
				AND mem.notify_regularity = " . ($is_weekly ? '3' : '2') . ")
			LEFT JOIN {$db_prefix}topics AS t ON (ln.id_topic != 0 AND t.id_topic = ln.id_topic)", __FILE__, __LINE__);
	$members = array();
	$langs = array();
	$notify = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (!isset($members[$row['id_member']]))
		{
			$members[$row['id_member']] = array(
				'email' => $row['email_address'],
				'name' => $row['member_name'],
				'id' => $row['id_member'],
				'notifyMod' => $row['notify_types'] < 3 ? true : false,
				'lang' => $row['lngfile'],
			);
			$langs[$row['lngfile']] = $row['lngfile'];
		}

		// Store this useful data!
		$boards[$row['id_board']] = $row['id_board'];
		if ($row['id_topic'])
			$notify['topics'][$row['id_topic']][] = $row['id_member'];
		else
			$notify['boards'][$row['id_board']][] = $row['id_member'];
	}
	$smfFunc['db_free_result']($request);

	if (empty($boards))
		return true;

	// Just get the board names.
	$request = $smfFunc['db_query']('', "
		SELECT id_board, name
		FROM {$db_prefix}boards
		WHERE id_board IN (" . implode(',', $boards) . ")", __FILE__, __LINE__);
	$boards = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$boards[$row['id_board']] = $row['name'];
	$smfFunc['db_free_result']($request);

	if (empty($boards))
		return true;

	// Get the actual topics...
	$request = $smfFunc['db_query']('', "
		SELECT ld.note_type, t.id_topic, t.id_board, t.id_member_started, m.id_msg, m.subject,
			b.name AS board_name
		FROM {$db_prefix}log_digest AS ld
			INNER JOIN {$db_prefix}topics AS t ON (t.id_topic = ld.id_topic
				AND t.id_board IN (" . implode(',', array_keys($boards)) . "))
			INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)
		WHERE " . ($is_weekly ? 'ld.daily != 2' : 'ld.daily IN (0, 2)'), __FILE__, __LINE__);
	$types = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (!isset($types[$row['note_type']][$row['id_board']]))
			$types[$row['note_type']][$row['id_board']] = array(
				'lines' => array(),
				'name' => $row['board_name'],
				'id' => $row['id_board'],
			);

		if ($row['note_type'] == 'reply')
		{
			if (isset($types[$row['note_type']][$row['id_board']]['lines'][$row['id_topic']]))
				$types[$row['note_type']][$row['id_board']]['lines'][$row['id_topic']]['count']++;
			else
				$types[$row['note_type']][$row['id_board']]['lines'][$row['id_topic']] = array(
					'id' => $row['id_topic'],
					'subject' => un_htmlspecialchars($row['subject']),
					'count' => 1,
				);
		}
		elseif ($row['note_type'] == 'topic')
		{
			if (!isset($types[$row['note_type']][$row['id_board']]['lines'][$row['id_topic']]))
				$types[$row['note_type']][$row['id_board']]['lines'][$row['id_topic']] = array(
					'id' => $row['id_topic'],
					'subject' => un_htmlspecialchars($row['subject']),
				);
		}
		else
		{
			if (!isset($types[$row['note_type']][$row['id_board']]['lines'][$row['id_topic']]))
				$types[$row['note_type']][$row['id_board']]['lines'][$row['id_topic']] = array(
					'id' => $row['id_topic'],
					'subject' => un_htmlspecialchars($row['subject']),
					'starter' => $row['id_member_started'],
				);
		}

		$types[$row['note_type']][$row['id_board']]['lines'][$row['id_topic']]['members'] = array();
		if (!empty($notify['topics'][$row['id_topic']]))
			$types[$row['note_type']][$row['id_board']]['lines'][$row['id_topic']]['members'] = array_merge($types[$row['note_type']][$row['id_board']]['lines'][$row['id_topic']]['members'], $notify['topics'][$row['id_topic']]);
		if (!empty($notify['boards'][$row['id_board']]))
			$types[$row['note_type']][$row['id_board']]['lines'][$row['id_topic']]['members'] = array_merge($types[$row['note_type']][$row['id_board']]['lines'][$row['id_topic']]['members'], $notify['boards'][$row['id_board']]);
	}
	$smfFunc['db_free_result']($request);

	if (empty($types))
		return true;

	// Let's load all the languages into a cache thingy.
	$langtxt = array();
	foreach ($langs as $lang)
	{
		loadLanguage('Post', $lang);
		loadLanguage('index', $lang);
		loadLanguage('EmailTemplates', $lang);
		$langtxt[$lang] = array(
			'subject' => $txt['digest_subject_' . ($is_weekly ? 'weekly' : 'daily')],
			'char_set' => $txt['lang_character_set'],
			'intro' => sprintf($txt['digest_intro_' . ($is_weekly ? 'weekly' : 'daily')], $mbname),
			'new_topics' => $txt['digest_new_topics'],
			'topic_lines' => $txt['digest_new_topics_line'],
			'new_replies' => $txt['digest_new_replies'],
			'mod_actions' => $txt['digest_mod_actions'],
			'replies_one' => $txt['digest_new_replies_one'],
			'replies_many' => $txt['digest_new_replies_many'],
			'sticky' => $txt['digest_mod_act_sticky'],
			'lock' => $txt['digest_mod_act_lock'],
			'unlock' => $txt['digest_mod_act_unlock'],
			'remove' => $txt['digest_mod_act_remove'],
			'move' => $txt['digest_mod_act_move'],
			'merge' => $txt['digest_mod_act_merge'],
			'split' => $txt['digest_mod_act_split'],
			'bye' => $txt['regards_team'],
		);
	}

	// Right - send out the silly things - this will take quite some space!
	$emails = array();
	foreach ($members as $mid => $member)
	{
		// Right character set!
		$context['character_set'] = empty($modSettings['global_character_set']) ? $langtxt[$lang]['char_set'] : $modSettings['global_character_set'];

		// Do the start stuff!
		$email = array(
			'subject' => $mbname . ' - ' . $langtxt[$lang]['subject'],
			'body' => $member['name'] . ",\n\n" . $langtxt[$lang]['intro'] . "\n" . $scripturl . '?action=profile;u=' . $member['id'] . ';sa=notification' . "\n",
			'email' => $member['email'],
		);

		// All new topics?
		if (isset($types['topic']))
		{
			$titled = false;
			foreach ($types['topic'] as $id => $board)
				foreach ($board['lines'] as $topic)
					if (in_array($mid, $topic['members']))
					{
						if (!$titled)
						{
							$email['body'] .= "\n" . $langtxt[$lang]['new_topics'] . ":\n-----------------------------------------------";
							$titled = true;
						}
						$email['body'] .= "\n" . sprintf($langtxt[$lang]['topic_lines'], $topic['subject'], $board['name']);
					}
			if ($titled)
				$email['body'] .= "\n";
		}

		// What about replies?
		if (isset($types['reply']))
		{
			$titled = false;
			foreach ($types['reply'] as $id => $board)
				foreach ($board['lines'] as $topic)
					if (in_array($mid, $topic['members']))
					{
						if (!$titled)
						{
							$email['body'] .= "\n" . $langtxt[$lang]['new_replies'] . ":\n-----------------------------------------------";
							$titled = true;
						}
						$email['body'] .= "\n" . ($topic['count'] == 1 ? sprintf($langtxt[$lang]['replies_one'], $topic['subject']) : sprintf($langtxt[$lang]['replies_many'], $topic['count'], $topic['subject']));
					}

			if ($titled)
				$email['body'] .= "\n";
		}

		// Finally, moderation actions!
		$titled = false;
		foreach ($types as $note_type => $type)
		{
			if ($note_type == 'topic' || $note_type == 'reply')
				continue;

			foreach ($type as $id => $board)
				foreach ($board['lines'] as $topic)
					if (in_array($mid, $topic['members']))
					{
						if (!$titled)
						{
							$email['body'] .= "\n" . $langtxt[$lang]['mod_actions'] . ":\n-----------------------------------------------";
							$titled = true;
						}
						$email['body'] .= "\n" . sprintf($langtxt[$lang][$note_type], $topic['subject']);
					}

		}
		if ($titled)
			$email['body'] .= "\n";

		// Then just say our goodbyes!
		$email['body'] .= "\n\n" . $txt['regards_team'];

		// Send it - low priority!
		sendmail($email['email'], $email['subject'], $email['body'], null, null, false, 0);
	}

	// Clean up...
	if ($is_weekly)
	{
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_digest
			WHERE daily != 0", __FILE__, __LINE__);
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}log_digest
			SET daily = 2
			WHERE daily = 0", __FILE__, __LINE__);
	}
	else
	{
		// Clear any only weekly ones, and stop us from sending daily again.
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_digest
			WHERE daily = 2", __FILE__, __LINE__);
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}log_digest
			SET daily = 1
			WHERE daily = 0", __FILE__, __LINE__);
	}

	// Just incase the member changes their settings mark this as sent.
	$members = array_keys($members);
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}log_notify
		SET sent = 1
		WHERE id_member IN (" . implode(',', $members) . ")", __FILE__, __LINE__);

	// Log we've done it...
	return true;
}

// Like the daily stuff - just seven times less regular ;)
function scheduled_weekly_digest()
{
	global $db_prefix, $is_weekly;

	// We just pass through to the daily function - avoid duplication!
	$is_weekly = true;
	return scheduled_daily_digest();
}

// Send a bunch of emails from the mail queue.
function ReduceMailQueue($number = false, $override_limit = false)
{
	global $db_prefix, $modSettings, $smfFunc, $sourcedir;

	// By default send 20 at once.
	if (!$number)
		$number = empty($modSettings['mail_quantity']) ? 20 : $modSettings['mail_quantity'];

	// If we came with a timestamp, and that doesn't match the next event, then someone else has beaten us.
	if (isset($_GET['ts']) && $_GET['ts'] != $modSettings['mail_next_send'])
		return false;

	// By default move the next sending on by 10 seconds, and require an affected row.
	if (!$override_limit)
	{
		$delay = !empty($modSettings['mail_limit']) && $modSettings['mail_limit'] < 5 ? 10 : 2;
		
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}settings
			SET value = " . (time() + $delay) . "
			WHERE variable = 'mail_next_send'
				AND value = '$modSettings[mail_next_send]'", __FILE__, __LINE__);
		if ($smfFunc['db_affected_rows']() == 0)
			return false;
		$modSettings['mail_next_send'] = time() + $delay;
	}

	// If we're not overriding how many are we allow to send?
	if (!$override_limit && !empty($modSettings['mail_limit']))
	{
		list ($mt, $mn) = @explode('|', $modSettings['mail_recent']);

		// Nothing worth noting...
		if (empty($mn) || $mt < time() + 60)
		{
			$number = min($number, $modSettings['mail_limit']);
			$mt = time();
			$mn = $number;
		}
		// Otherwise we have a few more we can spend?
		elseif ($mn < $modSettings['mail_limit'])
		{
			$number = min($modSettings['mail_limit'] - $mn, $number);
			$mn = $number;
		}
		// No more I'm afraid, return!
		else
			return false;

		// Reflect that we're about to send some, do it now to be safe.
		updateSettings(array('mail_recent' => $mt . '|' . $mn));
	}

	// Now we know how many we're sending, let's send them.
	$request = $smfFunc['db_query']('', "
		SELECT /*!40001 SQL_NO_CACHE */ id_mail, recipient, body, subject, headers, send_html
		FROM {$db_prefix}mail_queue
		ORDER BY priority DESC, id_mail ASC
		LIMIT $number", __FILE__, __LINE__);
	$ids = array();
	$emails = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// We want to delete these from the database ASAP, so just get the data and go.
		$ids[] = $row['id_mail'];
		$emails[] = array(
			'to' => $row['recipient'],
			'body' => $row['body'],
			'subject' => $row['subject'],
			'headers' => $row['headers'],
			'send_html' => $row['send_html'],
		);
	}
	$smfFunc['db_free_result']($request);

	// Delete, delete, delete!!!
	if (!empty($ids))
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}mail_queue
			WHERE id_mail IN (" . implode(',', $ids) . ")", __FILE__, __LINE__);

	// Don't believe we have any left?
	if (count($ids) < $number)
	{
		// Only update the setting if no-one else has beaten us to it.
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}settings
			SET value = '0'
			WHERE variable = 'mail_next_send'
				AND value = '$modSettings[mail_next_send]'", __FILE__, __LINE__);
	}

	if (empty($ids))
		return false;

	if (!empty($modSettings['mail_type']) && $modSettings['smtp_host'] != '')
		require_once($sourcedir . '/Subs-Post.php');

	// Send each email, yea!
	foreach ($emails as $email)
	{
		if (empty($modSettings['mail_type']) || $modSettings['smtp_host'] == '')
		{
			$email['subject'] = strtr($email['subject'], array("\r" => '', "\n" => ''));
			if (!empty($modSettings['mail_strip_carriage']))
			{
				$email['body'] = strtr($email['body'], array("\r" => ''));
				$email['headers'] = strtr($email['headers'], array("\r" => ''));
			}

			// No point logging a specific error here, as we have no language. PHP error is helpful anyway...
			mail(strtr($email['to'], array("\r" => '', "\n" => '')), $email['subject'], $email['body'], $email['headers']);

			// Try to stop a timeout, this would be bad...
			@set_time_limit(300);
			if (function_exists('apache_reset_timeout'))
				apache_reset_timeout();
		}
		else
			smtp_mail(array($email['to']), $email['subject'], $email['body'], $email['send_html'] ? $email['headers'] : "Mime-Version: 1.0\r\n" . $email['headers']);
	}

	// Had something to send...
	return true;
}

// Calculate the next time the passed tasks should be triggered.
function CalculateNextTrigger($tasks = array(), $forceUpdate = false)
{
	global $db_prefix, $modSettings, $smfFunc;

	$task_query = '';
	if (!is_array($tasks))
		$tasks = array($tasks);

	// Actually have something passed?
	if (!empty($tasks))
	{
		if (!isset($tasks[0]) || is_numeric($tasks[0]))
			$task_query = ' AND id_task IN (' . implode(',', $tasks) . ')';
		else
			$task_query = ' AND task IN (\'' . implode('\',\'', $tasks) . '\')';
	}
	$nextTaskTime = empty($tasks) ? time() + 86400 : $modSettings['next_task_time'];

	// Get the critical info for the tasks.
	$request = $smfFunc['db_query']('', "
		SELECT id_task, next_time, time_offset, time_regularity, time_unit
		FROM {$db_prefix}scheduled_tasks
		WHERE disabled = 0
			$task_query", __FILE__, __LINE__);
	$tasks = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$next_time = next_time($row['time_regularity'], $row['time_unit'], $row['time_offset']);

		// Only bother moving the task if it's out of place or we're forcing it!
		if ($forceUpdate || $next_time < $row['next_time'] || $row['next_time'] < time())
			$tasks[$row['id_task']] = $next_time;
		else
			$next_time = $row['next_time'];

		// If this is sooner than the current next task, make this the next task.
		if ($next_time < $nextTaskTime)
			$nextTaskTime = $next_time;
	}
	$smfFunc['db_free_result']($request);

	// Now make the changes!
	foreach ($tasks as $id => $time)
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}scheduled_tasks
			SET next_time = $time
			WHERE id_task = $id", __FILE__, __LINE__);

	// If the next task is now different update.
	if ($modSettings['next_task_time'] != $nextTaskTime)
		updateSettings(array('next_task_time' => $nextTaskTime));
}

// Simply returns a time stamp of the next instance of these time parameters.
function next_time($regularity, $unit, $offset)
{
	// Just incase!
	if ($regularity == 0)
		$regularity = 2;

	$curHour = date("H", time());
	$curMin = date("i", time());
	$next_time = 9999999999;

	// If the unit is minutes only check regularity in minutes.
	if ($unit == 'm')
	{
		$off = date("i", $offset);

		// If it's now just pretend it ain't,
		if ($off == $curMin)
			$next_time = time() + $regularity;
		else
		{
			// Make sure that the offset is always in the past.
			$off = $off > $curMin ? $off - 60 : $off;

			while ($off <= $curMin)
				$off += $regularity;

			// Now we know when the time should be!
			$next_time = time() + 60 * ($off - $curMin);
		}
	}
	// Otherwise, work out what the offset would be with todays date.
	else
	{
		$next_time = mktime(date("H", $offset), date("i", $offset), 0, date("m"), date("d"), date("Y"));

		// Make the time offset in the past!
		if ($next_time > time())
		{
			$next_time -= 86400;
		}

		// Default we'll jump in hours.
		$applyOffset = 3600;
		// 24 hours = 1 day.
		if ($unit == 'd')
			$applyOffset = 86400;
		// Otherwise a week.
		if ($unit == 'w')
			$applyOffset = 604800;

		$applyOffset *= $regularity;

		// Just add on the offset.
		while ($next_time <= time())
		{
			$next_time += $applyOffset;
		}
	}

	return $next_time;
}

// This loads the bare minimum data to allow us to load language files!
function loadEssentialThemeData()
{
	global $settings, $modSettings, $db_prefix, $smfFunc;

	// Get all the default theme variables.
	$result = $smfFunc['db_query']('', "
		SELECT id_theme, variable, value
		FROM {$db_prefix}themes
		WHERE id_member = 0
			AND id_theme IN (1, $modSettings[theme_guests])", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		$settings[$row['variable']] = $row['value'];

		// Is this the default theme?
		if (in_array($row['variable'], array('theme_dir', 'theme_url', 'images_url')) && $row['id_theme'] == '1')
			$settings['default_' . $row['variable']] = $row['value'];
	}
	$smfFunc['db_free_result']($result);
}

function scheduled_fetchSMfiles()
{
	global $db_prefix, $sourcedir, $txt, $language, $settings, $forum_version, $modSettings, $smfFunc;

	// What files do we want to get
	$request = $smfFunc['db_query']('', "
		SELECT id_file, filename, path, parameters
		FROM {$db_prefix}admin_info_files", __FILE__, __LINE__);

	$js_files = array();
	
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$js_files[$row['id_file']] = array(
			'filename' => $row['filename'],
			'path' => $row['path'],
			'parameters' => sprintf($row['parameters'], $language, urlencode($modSettings['time_format']), urlencode($forum_version)),
		);
	}
	
	$smfFunc['db_free_result']($request);

	// We're gonna need fetch_web_data() to pull this off.
	require_once($sourcedir . '/Subs-Package.php');

	// Just in case we run into a problem.
	loadEssentialThemeData();
	loadLanguage('Errors', $language);
	
	foreach($js_files AS $ID_FILE => $file)
	{
		// Create the url
		$server = empty($file['path']) || substr($file['path'], 0, 7) != 'http://' ? 'http://www.simplemachines.org' : '';
		$url = $server . (!empty($file['path']) ? $file['path'] : $file['path']) . $file['filename'] . (!empty($file['parameters']) ? '?' . $file['parameters'] : '');

		// Get the file
		$file_data = fetch_web_data($url);

		// If we got an error log it
		if ($file_data === false)
		{
			log_error(sprintf($txt['st_cannot_retrieve_file'], $url));
			// No more to do with this file, on to the next
			continue;
		}

		// Save the file to the database.
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}admin_info_files
			SET data = SUBSTRING('" . $smfFunc['db_escape_string']($file_data) . "', 1, 65534)
			WHERE id_file = $ID_FILE", __FILE__, __LINE__);
	}
	return true;
}

function scheduled_birthdayemails()
{
	global $db_prefix, $modSettings, $sourcedir, $mbname, $txt, $smfFunc;

	// Need this in order to load the language files.
	loadEssentialThemeData();

	// Going to need this to send the emails.
	require_once($sourcedir . '/Subs-Post.php');

	// Get the month and day of today.
	$month = date('n'); // Month without leading zeros.
	$day = date('j'); // Day without leading zeros.

	// So who are the lucky ones?  Don't include those who don't want them.
	$result = $smfFunc['db_query']('', "
		SELECT id_member, real_name, lngfile, email_address
		FROM {$db_prefix}members
		WHERE MONTH(birthdate) = $month
			AND DAYOFMONTH(birthdate) = $day
			AND notify_announcements = 1
			AND YEAR(birthdate) > 1", __FILE__, __LINE__);

	// Group them by languages.
	$birthdays = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		if (!isset($birthdays[$row['lngfile']]))
			$birthdays[$row['lngfile']] = array();
		$birthdays[$row['lngfile']][$row['id_member']] = array(
			'name' => $row['real_name'],
			'email' => $row['email_address']
		);
	}
	$smfFunc['db_free_result']($result);

	// Send out the greetings!
	foreach($birthdays AS $lang => $recps)
	{
		foreach($recps AS $recp)
		{
			$replacements = array(
				'REALNAME' => $recp['name'],
			);
			
			$emaildata = loadEmailTemplate('happy_birthday', $replacements, $lang);
			
			sendmail($recp['email'], $emaildata['subject'], $emaildata['body']);

			// Try to stop a timeout, this would be bad...
			@set_time_limit(300);
			if (function_exists('apache_reset_timeout'))
				apache_reset_timeout();

		}
	}

	// Flush the mail queue, just in case.
	AddMailQueue(true);

	return true;
}
?>