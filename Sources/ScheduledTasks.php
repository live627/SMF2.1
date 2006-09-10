<?php
/******************************************************************************
* ScheduledTasks.php                                                          *
*******************************************************************************
* SMF: Simple Machines Forum                                                  *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                *
* =========================================================================== *
* Software Version:           SMF 2.0 Alpha                                   *
* Software by:                Simple Machines (http://www.simplemachines.org) *
* Copyright 2001-2006 by:     Lewis Media (http://www.lewismedia.com)         *
* Support, News, Updates at:  http://www.simplemachines.org                   *
*******************************************************************************
* This program is free software; you may redistribute it and/or modify it     *
* under the terms of the provided license as published by Lewis Media.        *
*                                                                             *
* This program is distributed in the hope that it is and will be useful,      *
* but WITHOUT ANY WARRANTIES; without even any implied warranty of            *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                        *
*                                                                             *
* See the "license.txt" file for details of the Simple Machines license.      *
* The latest version can always be found at http://www.simplemachines.org.    *
******************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file is automatically called and handles all manner of scheduled things. 

	void AutoTask()
		//!!!

	void scheduled_approval_notification()
		// !!!

	void scheduled_clean_cache()
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
*/

// This function works out what to do!
function AutoTask()
{
	global $db_prefix, $time_start, $modSettings;

	// Special case for doing the mail queue.
	if (isset($_GET['scheduled']) && $_GET['scheduled'] == 'mailq')
		ReduceMailQueue();
	else
	{
		// Select the next task to do.
		$request = db_query("
			SELECT ID_TASK, task, nextTime, timeOffset, timeRegularity, timeUnit
			FROM {$db_prefix}scheduled_tasks
			WHERE disabled = 0
				AND nextTime <= " . time() . "
				AND nextTime != 0
			ORDER BY nextTime ASC
			LIMIT 1", __FILE__, __LINE__);
		if (mysql_num_rows($request) != 0)
		{
			// The two important things really...
			$row = mysql_fetch_assoc($request);

			// When should this next be run?
			$nextTime = next_time($row['timeRegularity'], $row['timeUnit'], $row['timeOffset']);

			// How long in seconds it the gap?
			$duration = $row['timeRegularity'];
			if ($row['timeUnit'] == 'm')
				$duration *= 60;
			elseif ($row['timeUnit'] == 'h')
				$duration *= 3600;
			elseif ($row['timeUnit'] == 'd')
				$duration *= 86400;
			elseif ($row['timeUnit'] == 'w')
				$duration *= 604800;

			// If we were really late running this task actually skip the next one.
			if (time() + ($duration / 2) > $nextTime)
				$nextTime += $duration;

			// Update it now, so no others run this!
			db_query("
				UPDATE {$db_prefix}scheduled_tasks
				SET nextTime = $nextTime
				WHERE ID_TASK = $row[ID_TASK]
					AND nextTime = $row[nextTime]", __FILE__, __LINE__);
			$affected_rows = db_affected_rows();

			// The function must exist or we are wasting our time, plus do some timestamp checking, and database check!
			if (function_exists('scheduled_' . $row['task']) && (!isset($_GET['ts']) || $_GET['ts'] == $row['nextTime']) && $affected_rows)
			{
				ignore_user_abort(true);

				// Do the task...
				$completed = call_user_func('scheduled_' . $row['task']);

				// Log that we did it ;)
				if ($completed)
				{
					$total_time = round(array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)), 3);
					db_query("
						INSERT INTO {$db_prefix}log_scheduled_tasks
							(ID_TASK, timeRun, timeTaken)
						VALUES
							($row[ID_TASK], " . time() . ", $total_time)", __FILE__, __LINE__);
				}
			}
		}
		mysql_free_result($request);

		// Get the next timestamp right.
		$request = db_query("
			SELECT nextTime
			FROM {$db_prefix}scheduled_tasks
			WHERE disabled = 0
				AND nextTime != 0
			ORDER BY nextTime ASC
			LIMIT 1", __FILE__, __LINE__);
		// No new task scheduled yet?
		if (mysql_num_rows($request) === 0)
			$nextEvent = 0;
		else
			list ($nextEvent) = mysql_fetch_row($request);
		mysql_free_result($request);

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
	global $db_prefix, $scripturl, $modSettings, $mbname, $txt, $sourcedir;

	// Grab all the items awaiting approval and sort type then board - clear up any things that are no longer relvant.
	$request = db_query("
		SELECT aq.ID_MSG, aq.ID_ATTACH, aq.ID_EVENT, m.ID_TOPIC, m.ID_BOARD, m.subject, t.ID_FIRST_MSG,
			b.ID_PROFILE
		FROM ({$db_prefix}approval_queue AS aq, {$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
		WHERE m.ID_MSG = aq.ID_MSG
			AND t.ID_TOPIC = m.ID_TOPIC
			AND b.ID_BOARD = m.ID_BOARD", __FILE__, __LINE__);
	$notices = array();
	$profiles = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// If this is no longer around we'll ignore it.
		if (empty($row['ID_TOPIC']))
			continue;

		// What type is it?
		if ($row['ID_FIRST_MSG'] && $row['ID_FIRST_MSG'] == $row['ID_MSG'])
			$type = 'topic';
		elseif ($row['ID_ATTACH'])
			$type = 'attach';
		else
			$type = 'msg';

		// Add it to the array otherwise.
		$notices[$row['ID_BOARD']][$type][] = array(
			'subject' => $row['subject'],
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'],
		);

		// Store the profile for a bit later.
		$profiles[$row['ID_BOARD']] = $row['ID_PROFILE'];
	}
	mysql_free_result($request);

	// Delete it all!
	db_query("
		DELETE FROM {$db_prefix}approval_queue", __FILE__, __LINE__);

	// If nothing quit now.
	if (empty($notices))
		return true;

	// Now we need to think about finding out *who* can approve - this is hard!

	// First off, get all the groups with this permission and sort by board.
	$request = db_query("
		SELECT ID_GROUP, ID_PROFILE, addDeny
		FROM {$db_prefix}board_permissions
		WHERE permission = 'approve_posts'
			AND ID_PROFILE IN (" . implode(', ', $profiles) . ")", __FILE__, __LINE__);
	$perms = array();
	$addGroups = array(1);
	while ($row = mysql_fetch_assoc($request))
	{
		// Sorry guys, but we have to ignore guests AND members - it would be too many otherwise.
		if ($row['ID_GROUP'] < 2)
			continue;

		$perms[$row['ID_PROFILE']][$row['addDeny'] ? 'add' : 'deny'][] = $row['ID_GROUP'];

		// Anyone who can access has to be considered.
		if ($row['addDeny'])
			$addGroups[] = $row['ID_GROUP'];
	}
	mysql_free_result($request);

	// Grab the moderators if they have permission!
	$mods = array();
	$members = array();
	if (in_array(2, $addGroups))
	{
		$request = db_query("
			SELECT ID_MEMBER, ID_BOARD
			FROM {$db_prefix}moderators", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			$mods[$row['ID_MEMBER']][$row['ID_BOARD']] = true;
			// Make sure they get included in the big loop.
			$members[] = $row['ID_MEMBER'];
		}
		mysql_free_result($request);
	}

	// Come along one and all... until we reject you ;)
	$request = db_query("
		SELECT ID_MEMBER, realName, emailAddress, lngfile, ID_GROUP, additionalGroups
		FROM {$db_prefix}members
		WHERE ID_GROUP IN (" . implode(', ', $addGroups) . ")
			OR FIND_IN_SET(" . implode(', additionalGroups) OR FIND_IN_SET(', $addGroups) . ", additionalGroups)
			" . (empty($members) ? '' : " OR ID_MEMBER IN (" . implode(', ', $members) . ")") . "
		ORDER BY lngfile", __FILE__, __LINE__);
	$members = array();
	while ($row = mysql_fetch_assoc($request))
		$members[$row['ID_MEMBER']] = array(
			'id' => $row['ID_MEMBER'],
			'groups' => array_merge(explode(',', $row['additionalGroups']), array($row['ID_GROUP'])),
			'language' => $row['lngfile'],
			'email' => $row['emailAddress'],
			'name' => $row['realName'],		
		);
	mysql_free_result($request);

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

		// Send the actual email.
		sendmail($member['email'], sprintf($txt['scheduled_approval_email_subject'], $mbname),
			"$member[name],\n\n" .
			sprintf($txt['scheduled_approval_email'], $mbname) . "\n\n" .
			$emailbody .
			$txt['scheduled_approval_email_review'] . "\n" .
			"$scripturl\n\n" .
			$mbname);
	}

	// All went well!
	return true;
}

// Empty out the cache folder.
function scheduled_clean_cache()
{
	clean_cache('data');

	// Log we've done it...
	return true;
}

// Auto optimize the database?
function scheduled_auto_optimize()
{
	global $db_prefix, $modSettings;

	// By default do it now!
	$delay = false;
	// As a kind of hack, if the server load is too great delay, but only by a bit!
	if (!empty($modSettings['load_average']) && !empty($modSettings['loadavg_auto_opt']) && $modSettings['load_average'] >= $modSettings['loadavg_auto_opt'])
		$delay = true;

	// Otherwise are we restricting the number of people online for this?
	if (!empty($modSettings['autoOptMaxOnline']))
	{
		$request = db_query("
			SELECT COUNT(*)
			FROM {$db_prefix}log_online", __FILE__, __LINE__);
		list ($dont_do_it) = mysql_fetch_row($request);
		mysql_free_result($request);

		if ($dont_do_it > $modSettings['autoOptMaxOnline'])
			$delay = true;
	}

	// If we are gonna delay, do so now!
	if ($delay)
		return false;

	// Handle if things are prefixed with a database name.
	if (preg_match('~^`(.+?)`\.(.+?)$~', $db_prefix, $match) != 0)
	{
		$request = db_query("
			SHOW TABLES
			FROM `" . strtr($match[1], array('`' => '')) . "`
			LIKE '" . str_replace('_', '\_', $match[2]) . "%'", __FILE__, __LINE__);
	}
	else
	{
		$request = db_query("
			SHOW TABLES
			LIKE '" . str_replace('_', '\_', $db_prefix) . "%'", __FILE__, __LINE__);
	}

	$tables = array();
	while ($row = mysql_fetch_row($request))
		$tables[] = $row[0];
	mysql_free_result($request);

	// Actually do the optimisation.
	foreach ($tables as $table)
		db_query("
			OPTIMIZE TABLE `$table`", __FILE__, __LINE__);

	// Return for the log...
	return true;
}

// Send out a daily email of all subscribed topics.
function scheduled_daily_digest()
{
	global $db_prefix, $weekly, $txt, $mbname, $scripturl, $sourcedir, $smfFunc, $context, $modSettings;

	// We'll want this...
	require_once($sourcedir . '/Subs-Post.php');
	loadEssentialThemeData();

	$is_weekly = !empty($is_weekly) ? 1 : 0;

	// Right - get all the notification data FIRST.
	$request = db_query("
		SELECT ln.ID_TOPIC, IFNULL(t.ID_BOARD, ln.ID_BOARD) AS ID_BOARD, mem.emailAddress, mem.memberName, mem.notifyTypes,
			mem.lngfile, mem.ID_MEMBER
		FROM ({$db_prefix}log_notify AS ln, {$db_prefix}members AS mem)
			LEFT JOIN {$db_prefix}topics AS t ON (ln.ID_TOPIC != 0 && t.ID_TOPIC = ln.ID_TOPIC)
		WHERE mem.ID_MEMBER = ln.ID_MEMBER
			AND mem.notifyRegularity = " . ($is_weekly ? '3' : '2') . "
			", __FILE__, __LINE__);
	$members = array();
	$langs = array();
	$notify = array();
	while ($row = mysql_fetch_assoc($request))
	{
		if (!isset($members[$row['ID_MEMBER']]))
		{
			$members[$row['ID_MEMBER']] = array(
				'email' => $row['emailAddress'],
				'name' => $row['memberName'],
				'id' => $row['ID_MEMBER'],
				'notifyMod' => $row['notifyTypes'] < 3 ? true : false,
				'lang' => $row['lngfile'],
			);
			$langs[$row['lngfile']] = $row['lngfile'];
		}

		// Store this useful data!
		$boards[$row['ID_BOARD']] = $row['ID_BOARD'];
		if ($row['ID_TOPIC'])
			$notify['topics'][$row['ID_TOPIC']][] = $row['ID_MEMBER'];
		else
			$notify['boards'][$row['ID_BOARD']][] = $row['ID_MEMBER'];
	}
	mysql_free_result($request);

	if (empty($boards))
		return true;

	// Just get the board names.
	$request = db_query("
		SELECT ID_BOARD, name
		FROM {$db_prefix}boards
		WHERE ID_BOARD IN (" . implode(',', $boards) . ")", __FILE__, __LINE__);
	$boards = array();
	while ($row = mysql_fetch_assoc($request))
		$boards[$row['ID_BOARD']] = $row['name'];
	mysql_free_result($request);

	if (empty($boards))
		return true;

	// Get the actual topics...
	$request = db_query("
		SELECT ld.note_type, t.ID_TOPIC, t.ID_BOARD, t.ID_MEMBER_STARTED, m.ID_MSG, m.subject,
			b.name AS boardName
		FROM ({$db_prefix}log_digest AS ld, {$db_prefix}topics AS t,
			{$db_prefix}messages AS m, {$db_prefix}boards AS b)
		WHERE " . ($is_weekly ? 'ld.daily != 2' : 'ld.daily IN (0, 2)') . "
			AND t.ID_TOPIC = ld.ID_TOPIC
			AND t.ID_BOARD IN (" . implode(',', array_keys($boards)) . ")
			AND b.ID_BOARD = t.ID_BOARD
			AND m.ID_MSG = t.ID_FIRST_MSG", __FILE__, __LINE__);
	$types = array();
	while ($row = mysql_fetch_assoc($request))
	{
		if (!isset($types[$row['note_type']][$row['ID_BOARD']]))
			$types[$row['note_type']][$row['ID_BOARD']] = array(
				'lines' => array(),
				'name' => $row['boardName'],
				'id' => $row['ID_BOARD'],
			);

		if ($row['note_type'] == 'reply')
		{
			if (isset($types[$row['note_type']][$row['ID_BOARD']]['lines'][$row['ID_TOPIC']]))
				$types[$row['note_type']][$row['ID_BOARD']]['lines'][$row['ID_TOPIC']]['count']++;
			else
				$types[$row['note_type']][$row['ID_BOARD']]['lines'][$row['ID_TOPIC']] = array(
					'id' => $row['ID_TOPIC'],
					'subject' => un_htmlspecialchars($row['subject']),
					'count' => 1,
				);
		}
		elseif ($row['note_type'] == 'topic')
		{
			if (!isset($types[$row['note_type']][$row['ID_BOARD']]['lines'][$row['ID_TOPIC']]))
				$types[$row['note_type']][$row['ID_BOARD']]['lines'][$row['ID_TOPIC']] = array(
					'id' => $row['ID_TOPIC'],
					'subject' => un_htmlspecialchars($row['subject']),
				);
		}
		else
		{
			if (!isset($types[$row['note_type']][$row['ID_BOARD']]['lines'][$row['ID_TOPIC']]))
				$types[$row['note_type']][$row['ID_BOARD']]['lines'][$row['ID_TOPIC']] = array(
					'id' => $row['ID_TOPIC'],
					'subject' => un_htmlspecialchars($row['subject']),
					'starter' => $row['ID_MEMBER_STARTED'],
				);
		}

		$types[$row['note_type']][$row['ID_BOARD']]['lines'][$row['ID_TOPIC']]['members'] = array();
		if (!empty($notify['topics'][$row['ID_TOPIC']]))
			$types[$row['note_type']][$row['ID_BOARD']]['lines'][$row['ID_TOPIC']]['members'] = array_merge($types[$row['note_type']][$row['ID_BOARD']]['lines'][$row['ID_TOPIC']]['members'], $notify['topics'][$row['ID_TOPIC']]);
		if (!empty($notify['boards'][$row['ID_BOARD']]))
			$types[$row['note_type']][$row['ID_BOARD']]['lines'][$row['ID_TOPIC']]['members'] = array_merge($types[$row['note_type']][$row['ID_BOARD']]['lines'][$row['ID_TOPIC']]['members'], $notify['boards'][$row['ID_BOARD']]);
	}
	mysql_free_result($request);

	if (empty($types))
		return true;

	// Let's load all the languages into a cache thingy.
	$langtxt = array();
	foreach ($langs as $lang)
	{
		loadLanguage('Post', $lang);
		loadLanguage('index', $lang);
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
			'bye' => sprintf($txt['regards_team'], $context['forum_name']),
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
		$email['body'] .= "\n\n" . sprintf($txt['regards_team'], $context['forum_name']);

		// Send it - low priority!
		sendmail($email['email'], $email['subject'], $email['body'], null, null, false, 0);
	}

	// Clean up...
	if ($is_weekly)
	{
		db_query("
			DELETE FROM {$db_prefix}log_digest
			WHERE daily != 0", __FILE__, __LINE__);
		db_query("
			UPDATE {$db_prefix}log_digest
			SET daily = 2
			WHERE daily = 0", __FILE__, __LINE__);
	}
	else
	{
		// Clear any only weekly ones, and stop us from sending daily again.
		db_query("
			DELETE FROM {$db_prefix}log_digest
			WHERE daily = 2", __FILE__, __LINE__);
		db_query("
			UPDATE {$db_prefix}log_digest
			SET daily = 1
			WHERE daily = 0", __FILE__, __LINE__);
	}

	// Just incase the member changes their settings mark this as sent.
	$members = array_keys($members);
	db_query("
		UPDATE {$db_prefix}log_notify
		SET sent = 1
		WHERE ID_MEMBER IN (" . implode(',', $members) . ")", __FILE__, __LINE__);

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
	global $db_prefix, $modSettings;

	// By default send 20 at once.
	if (!$number)
		$number = empty($modSettings['mail_quantity']) ? 20 : $modSettings['mail_quantity'];

	// If we came with a timestamp, and that doesn't match the next event, then someone else has beaten us.
	if (isset($_GET['ts']) && $_GET['ts'] != $modSettings['mail_next_send'])
		return false;

	// By default move the next sending on by 10 seconds, and require an affected row.
	if (!$override_limit)
	{
		$delay = !empty($modSettings['mail_limit']) && $modSettings['mail_limit'] < 5 ? 20 : 10;
		
		db_query("
			UPDATE {$db_prefix}settings
			SET value = " . (time() + $delay) . "
			WHERE variable = 'mail_next_send'
				AND value = '$modSettings[mail_next_send]'", __FILE__, __LINE__);
		if (mysql_affected_rows() == 0)
			return false;
		$modSettings['mail_next_send'] = $delay;
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
	$request = db_query("
		SELECT /*!40001 SQL_NO_CACHE */ ID_MAIL, recipient, body, subject, headers, send_html
		FROM {$db_prefix}mail_queue
		ORDER BY priority DESC, ID_MAIL ASC
		LIMIT $number", __FILE__, __LINE__);
	$ids = array();
	$emails = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// We want to delete these from the database ASAP, so just get the data and go.
		$ids[] = $row['ID_MAIL'];
		$emails[] = array(
			'to' => $row['recipient'],
			'body' => $row['body'],
			'subject' => $row['subject'],
			'headers' => $row['headers'],
			'send_html' => $row['send_html'],
		);
	}
	mysql_free_result($request);

	// Delete, delete, delete!!!
	if (!empty($ids))
		db_query("
			DELETE FROM {$db_prefix}mail_queue
			WHERE ID_MAIL IN (" . implode(',', $ids) . ")
			LIMIT $number", __FILE__, __LINE__);

	// Don't believe we have any left?
	if (count($ids) < $number)
	{
		// Only update the setting if no-one else has beaten us to it.
		db_query("
			UPDATE {$db_prefix}settings
			SET value = '0'
			WHERE variable = 'mail_next_send'
				AND value = '$modSettings[mail_next_send]'", __FILE__, __LINE__);
	}

	if (empty($ids))
		return false;

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
	global $db_prefix, $modSettings;

	$task_query = '';
	if (!is_array($tasks))
		$tasks = array($tasks);

	// Actually have something passed?
	if (!empty($tasks))
	{
		if (!isset($tasks[0]) || is_numeric($tasks[0]))
			$task_query = ' AND ID_TASK IN (' . implode(',', $tasks) . ')';
		else
			$task_query = ' AND task IN (\'' . implode('\',\'', $tasks) . '\')';
	}
	$nextTaskTime = empty($tasks) ? 9999999999 : $modSettings['next_task_time'];

	// Get the critical info for the tasks.
	$request = db_query("
		SELECT ID_TASK, nextTime, timeOffset, timeRegularity, timeUnit
		FROM {$db_prefix}scheduled_tasks
		WHERE disabled = 0
			$task_query", __FILE__, __LINE__);
	$tasks = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$nextTime = next_time($row['timeRegularity'], $row['timeUnit'], $row['timeOffset']);

		// Only bother moving the task if it's out of place or we're forcing it!
		if ($forceUpdate || $nextTime < $row['nextTime'] || $row['nextTime'] < time())
			$tasks[$row['ID_TASK']] = $nextTime;
		else
			$nextTime = $row['nextTime'];

		// If this is sooner than the current next task, make this the next task.
		if ($nextTime < $nextTaskTime)
			$nextTaskTime = $nextTime;
	}
	mysql_free_result($request);

	// Now make the changes!
	foreach ($tasks as $id => $time)
		db_query("
			UPDATE {$db_prefix}scheduled_tasks
			SET nextTime = $time
			WHERE ID_TASK = $id", __FILE__, __LINE__);

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
	$nextTime = 9999999999;

	// If the unit is minutes only check regularity in minutes.
	if ($unit == 'm')
	{
		$off = date("i", $offset);

		// If it's now just pretend it ain't,
		if ($off == $curMin)
			$nextTime = time() + $regularity;
		else
		{
			// Make sure that the offset is always in the past.
			$off = $off > $curMin ? $off - 60 : $off;

			while ($off <= $curMin)
				$off += $regularity;

			// Now we know when the time should be!
			$nextTime = time() + 60 * ($off - $curMin);
		}
	}
	// Otherwise, work out what the offset would be with todays date.
	else
	{
		$nextTime = mktime(date("H", $offset), date("i", $offset), 0, date("m"), date("d"), date("Y"));

		// Make the time offset in the past!
		if ($nextTime > time())
		{
			$nextTime -= 86400;
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
		while ($nextTime <= time())
		{
			$nextTime += $applyOffset;
		}
	}

	return $nextTime;
}

// This loads the bare minimum data to allow us to load language files!
function loadEssentialThemeData()
{
	global $settings, $modSettings, $db_prefix;

	// Get all the default theme variables.
	$result = db_query("
		SELECT ID_THEME, variable, value
		FROM {$db_prefix}themes
		WHERE ID_MEMBER = 0
			AND ID_THEME IN (1, $modSettings[theme_guests])", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($result))
	{
		$settings[$row['variable']] = $row['value'];

		// Is this the default theme?
		if (in_array($row['variable'], array('theme_dir', 'theme_url', 'images_url')) && $row['ID_THEME'] == '1')
			$settings['default_' . $row['variable']] = $row['value'];
	}
	mysql_free_result($result);
}

function scheduled_fetchSMfiles()
{
	global $db_prefix, $sourcedir, $txt, $language, $settings, $forum_version, $modSettings;

	// What files do we want to get
	$request = db_query("
		SELECT ID_FILE, filename, path, parameters
		FROM {$db_prefix}admin_info_files", __FILE__, __LINE__);

	$js_files = array();
	
	while ($row = mysql_fetch_assoc($request))
	{
		$js_files[$row['ID_FILE']] = array(
			'filename' => $row['filename'],
			'path' => $row['path'],
			'parameters' => sprintf($row['parameters'], $language, urlencode($modSettings['time_format']), urlencode($forum_version)),
		);
	}
	
	mysql_free_result($request);

	// We're gonna need fetch_web_data() to pull this off.
	require_once($sourcedir . '/Subs-Package.php');

	// Just in case we run into a problem.
	loadEssentialThemeData();
	loadLanguage('Errors', $language);
	
	foreach($js_files AS $ID_FILE => $file)
	{
		// Create the url
		$url = 'http://www.simplemachines.org' . (!empty($file['path']) ? $file['path'] : '') . $file['filename'] . (!empty($file['parameters']) ? '?' . $file['parameters'] : '');

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
		db_query("
			UPDATE {$db_prefix}admin_info_files
			SET data = SUBSTRING('" . addslashes($file_data) . "', 1, 65534)
			WHERE ID_FILE = $ID_FILE
			LIMIT 1", __FILE__, __LINE__);
	}
	return true;
}

function scheduled_birthdayemails()
{
	global $db_prefix, $modSettings, $sourcedir, $mbname, $txt;

	// Need this in order to load the language files.
	loadEssentialThemeData();

	// Going to need this to send the emails.
	require_once($sourcedir . '/Subs-Post.php');

	// Get the month and day of today.
	$month = date('n'); // Month without leading zeros.
	$day = date('j'); // Day without leading zeros.

	// So who are the lucky ones?  Don't include those who don't want them.
	$result = db_query("
		SELECT ID_MEMBER, realName, lngfile, emailAddress
		FROM {$db_prefix}members
		WHERE MONTH(birthdate) = $month
			AND DAY(birthdate) = $day
			AND notifyAnnouncements = 1", __FILE__, __LINE__);

	// Group them by languages.
	$birthdays = array();
	while ($row = mysql_fetch_assoc($result))
	{
		if (!isset($birthdays[$row['lngfile']]))
			$birthdays[$row['lngfile']] = array();
		$birthdays[$row['lngfile']][$row['ID_MEMBER']] = array(
			'name' => $row['realName'],
			'email' => $row['emailAddress']
		);
	}
	mysql_free_result($result);

	// Send out the greetings!
	foreach($birthdays AS $lang)
	{
		// Why the PM language file?  Um because the birthday message is personal?
		loadLanguage('PersonalMessage', $lang);

		foreach($lang AS $recp)
		{
			sendmail($recp['email'], sprintf($txt['birthday_email_subject'], $mbname, $recp['name']), sprintf($txt['birthday_email'], $mbname, $recp['name']));

			// Try to stop a timeout, this would be bad...
			@set_time_limit(300);
			if (function_exists('apache_reset_timeout'))
				apache_reset_timeout();

		}
	}

	return true;
}
?>