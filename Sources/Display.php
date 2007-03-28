<?php
/**********************************************************************************
* Display.php                                                                     *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Alpha                                       *
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

/*	This is perhaps the most important and probably most accessed files in all
	of SMF.  This file controls topic, message, and attachment display.  It
	does so with the following functions:

	void Display()
		- loads the posts in a topic up so they can be displayed.
		- supports wireless, using wap/wap2/imode and the Wireless templates.
		- uses the main sub template of the Display template.
		- requires a topic, and can go to the previous or next topic from it.
		- jumps to the correct post depending on a number/time/IS_MSG passed.
		- depends on the defaultMaxMessages and enableAllMessages settings.
		- is accessed by ?topic=id_topic.START.

	array prepareDisplayContext(bool reset = false)
		- actually gets and prepares the message context.
		- starts over from the beginning if reset is set to true, which is
		  useful for showing an index before or after the posts.

	void Download()
		- downloads an attachment or avatar, and increments the downloads.
		- requires the view_attachments permission. (not for avatars!)
		- disables the session parser, and clears any previous output.
		- depends on the attachmentUploadDir setting being correct.
		- is accessed via the query string ?action=dlattach.
		- views to attachments and avatars do not increase hits and are not
		  logged in the "Who's Online" log.

	array loadAttachmentContext(int id_msg)
		- loads an attachment's contextual data including, most importantly,
		  its size if it is an image.
		- expects the $attachments array to have been filled with the proper
		  attachment data, as Display() does.
		- requires the view_attachments permission to calculate image size.
		- attempts to keep the "aspect ratio" of the posted image in line,
		  even if it has to be resized by the max_image_width and
		  max_image_height settings.

	int approved_attach_sort(array a, array b)
		// !!!

	void QuickInTopicModeration()
		// !!!

*/

// The central part of the board - topic display.
function Display()
{
	global $scripturl, $txt, $db_prefix, $modSettings, $context, $settings;
	global $options, $sourcedir, $user_info, $board_info, $topic, $board;
	global $attachments, $messages_request, $topicinfo, $language, $smfFunc;

	// What are you gonna display if these are empty?!
	if (empty($topic))
		fatal_lang_error('no_board', false);

	// Load the proper template and/or sub template.
	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_display';
	else
		loadTemplate('Display');

	// Not only does a prefetch make things slower for the server, but it makes it impossible to know if they read it.
	if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
	{
		ob_end_clean();
		header('HTTP/1.1 403 Prefetch Forbidden');
		die;
	}

	// Find the previous or next topic.  Make a fuss if there are no more.
	if (isset($_REQUEST['prev_next']) && ($_REQUEST['prev_next'] == 'prev' || $_REQUEST['prev_next'] == 'next'))
	{
		// No use in calculating the next topic if there's only one.
		if ($board_info['num_topics'] > 1)
		{
			// Just prepare some variables that are used in the query.
			$gt_lt = $_REQUEST['prev_next'] == 'prev' ? '>' : '<';
			$order = $_REQUEST['prev_next'] == 'prev' ? '' : ' DESC';

			$request = $smfFunc['db_query']('', "
				SELECT t2.id_topic
				FROM {$db_prefix}topics AS t
					INNER JOIN {$db_prefix}topics AS t2 ON (" . (empty($modSettings['enableStickyTopics']) ? "
					t2.id_last_msg $gt_lt t.id_last_msg" : "
					(t2.id_last_msg $gt_lt t.id_last_msg AND t2.is_sticky $gt_lt= t.is_sticky) OR t2.is_sticky $gt_lt t.is_sticky") . ")
				WHERE t.id_topic = $topic
					AND t2.id_board = $board
					" . (allowedTo('approve_posts') ? '' : ' AND t2.approved = 1') . "
				ORDER BY" . (empty($modSettings['enableStickyTopics']) ? '' : " t2.is_sticky$order,") . " t2.id_last_msg$order
				LIMIT 1", __FILE__, __LINE__);

			// No more left.
			if ($smfFunc['db_num_rows']($request) == 0)
			{
				$smfFunc['db_free_result']($request);

				// Roll over - if we're going prev, get the last - otherwise the first.
				$request = $smfFunc['db_query']('', "
					SELECT id_topic
					FROM {$db_prefix}topics
					WHERE id_board = $board
						" . (allowedTo('approve_posts') ? '' : ' AND approved = 1') . "
					ORDER BY" . (empty($modSettings['enableStickyTopics']) ? '' : " is_sticky$order,") . " id_last_msg$order
					LIMIT 1", __FILE__, __LINE__);
			}

			// Now you can be sure $topic is the id_topic to view.
			list ($topic) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			$context['current_topic'] = $topic;
		}

		// Go to the newest message on this topic.
		$_REQUEST['start'] = 'new';

		// Duplicate link!  Tell the robots not to link this.
		$context['robot_no_index'] = true;
	}

	// Add 1 to the number of views of this topic.
	if (empty($_SESSION['last_read_topic']) || $_SESSION['last_read_topic'] != $topic)
	{
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}topics
			SET num_views = num_views + 1
			WHERE id_topic = $topic", __FILE__, __LINE__);

		$_SESSION['last_read_topic'] = $topic;
	}

	// Get all the important topic info.
	$request = $smfFunc['db_query']('', "
		SELECT
			t.num_replies, t.num_views, t.locked, ms.subject, t.is_sticky, t.id_poll,
			t.id_member_started, t.id_first_msg, t.id_last_msg, t.approved,
			" . ($user_info['is_guest'] ? '0' : 'IFNULL(lt.id_msg, -1) + 1') . " AS new_from
		FROM {$db_prefix}topics AS t
			INNER JOIN {$db_prefix}messages AS ms ON (ms.id_msg = t.id_first_msg)" . ($user_info['is_guest'] ? '' : "
			LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.id_topic = $topic AND lt.id_member = $user_info[id])") ."
		WHERE t.id_topic = $topic
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('not_a_topic', false);
	$topicinfo = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// The start isn't a number; it's information about what to do, where to go.
	if (!is_numeric($_REQUEST['start']))
	{
		// Redirect to the page and post with new messages, originally by Omar Bazavilvazo.
		if ($_REQUEST['start'] == 'new')
		{
			// Guests automatically go to the last topic.
			if ($user_info['is_guest'])
			{
				$context['start_from'] = $topicinfo['num_replies'];
				$_REQUEST['start'] = empty($options['view_newest_first']) ? $context['start_from'] : 0;
			}
			else
			{
				// Find the earliest unread message in the topic. (the use of topics here is just for both tables.)
				$request = $smfFunc['db_query']('', "
					SELECT IFNULL(lt.id_msg, IFNULL(lmr.id_msg, -1)) + 1 AS new_from
					FROM {$db_prefix}topics AS t
						LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.id_topic = $topic AND lt.id_member = $user_info[id])
						LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.id_board = $board AND lmr.id_member = $user_info[id])
					WHERE t.id_topic = $topic
					LIMIT 1", __FILE__, __LINE__);
				list ($new_from) = $smfFunc['db_fetch_row']($request);
				$smfFunc['db_free_result']($request);

				// Fall through to the next if statement.
				$_REQUEST['start'] = 'msg' . $new_from;
			}
		}

		// Start from a certain time index, not a message.
		if (substr($_REQUEST['start'], 0, 4) == 'from')
		{
			$timestamp = (int) substr($_REQUEST['start'], 4);
			if ($timestamp === 0)
				$_REQUEST['start'] = 0;
			else
			{
				// Find the number of messages posted before said time...
				$request = $smfFunc['db_query']('', "
					SELECT COUNT(*)
					FROM {$db_prefix}messages
					WHERE poster_time < $timestamp
						AND id_topic = $topic", __FILE__, __LINE__);
				list ($context['start_from']) = $smfFunc['db_fetch_row']($request);
				$smfFunc['db_free_result']($request);

				// Handle view_newest_first options, and get the correct start value.
				$_REQUEST['start'] = empty($options['view_newest_first']) ? $context['start_from'] : $topicinfo['num_replies'] - $context['start_from'];
			}
		}
			
		// Link to a message...
		elseif (substr($_REQUEST['start'], 0, 3) == 'msg')
		{
			$virtual_msg = (int) substr($_REQUEST['start'], 3);
			if ($virtual_msg >= $topicinfo['id_last_msg'])
				$context['start_from'] = $topicinfo['num_replies'];
			elseif ($virtual_msg <= $topicinfo['id_first_msg'])
				$context['start_from'] = 0;
			else
			{
				// Find the start value for that message......
				$request = $smfFunc['db_query']('', "
					SELECT COUNT(*)
					FROM {$db_prefix}messages
					WHERE id_msg < $virtual_msg
						AND id_topic = $topic
						AND approved = 1", __FILE__, __LINE__);
				list ($context['start_from']) = $smfFunc['db_fetch_row']($request);
				$smfFunc['db_free_result']($request);
			}
			
			// We need to reverse the start as well in this case.
			$_REQUEST['start'] = empty($options['view_newest_first']) ? $context['start_from'] : $topicinfo['num_replies'] - $context['start_from'];

			$context['robot_no_index'] = true;
		}
	}

	// Create a previous next string if the selected theme has it as a selected option.
	$context['previous_next'] = $modSettings['enablePreviousNext'] ? '<a href="' . $scripturl . '?topic=' . $topic . '.0;prev_next=prev#new">' . $txt['previous_next_back'] . '</a> <a href="' . $scripturl . '?topic=' . $topic . '.0;prev_next=next#new">' . $txt['previous_next_forward'] . '</a>' : '';

	// Check if spellchecking is both enabled and actually working. (for quick reply.)
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	// Are we showing signatures - or disabled fields?
	$context['signature_enabled'] = substr($modSettings['signature_settings'], 0, 1) == 1;
	$context['disabled_fields'] = isset($modSettings['disabled_profile_fields']) ? array_flip(explode(',', $modSettings['disabled_profile_fields'])) : array();

	// Censor the title...
	censorText($topicinfo['subject']);
	$context['page_title'] = $topicinfo['subject'];

	$context['num_replies'] = $topicinfo['num_replies'];
	$context['topic_first_message'] = $topicinfo['id_first_msg'];

	// Is this topic sticky, or can it even be?
	$topicinfo['is_sticky'] = empty($modSettings['enableStickyTopics']) ? '0' : $topicinfo['is_sticky'];

	// Default this topic to not marked for notifications... of course...
	$context['is_marked_notify'] = false;

	// Guests can't mark topics read or for notifications, just can't sorry.
	if (!$user_info['is_guest'])
	{
		$smfFunc['db_insert']('replace',
			"{$db_prefix}log_topics",
			array('id_member', 'id_topic', 'id_msg'),
			array($user_info['id'], $topic, $modSettings['maxMsgID']),
			array('id_member', 'id_topic'), __FILE__, __LINE__
		);

		// Check for notifications on this topic OR board.
		$request = $smfFunc['db_query']('', "
			SELECT sent, id_topic
			FROM {$db_prefix}log_notify
			WHERE (id_topic = $topic OR id_board = $board)
				AND id_member = $user_info[id]
			LIMIT 2", __FILE__, __LINE__);
		$do_once = true;
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			// Find if this topic is marked for notification...
			if (!empty($row['id_topic']))
				$context['is_marked_notify'] = true;

			// Only do this once, but mark the notifications as "not sent yet" for next time.
			if (!empty($row['sent']) && $do_once)
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}log_notify
					SET sent = 0
					WHERE (id_topic = $topic OR id_board = $board)
						AND id_member = $user_info[id]", __FILE__, __LINE__);
				$do_once = false;
			}
		}

		// Have we recently cached the number of new topics in this board, and it's still a lot?
		if (isset($_REQUEST['topicseen']) && isset($_SESSION['topicseen_cache'][$board]) && $_SESSION['topicseen_cache'][$board] > 5)
			$_SESSION['topicseen_cache'][$board]--;
		// Mark board as seen if this is the only new topic.
		elseif (isset($_REQUEST['topicseen']))
		{
			// Use the mark read tables... and the last visit to figure out if this should be read or not.
			$request = $smfFunc['db_query']('', "
				SELECT COUNT(*)
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.id_board = $board AND lb.id_member = $user_info[id])
					LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = $user_info[id])
				WHERE t.id_board = $board
					AND t.id_last_msg > IFNULL(lb.id_msg, 0)
					AND t.id_last_msg > IFNULL(lt.id_msg, 0)" . (empty($_SESSION['id_msg_last_visit']) ? '' : "
					AND t.id_last_msg > $_SESSION[id_msg_last_visit]"), __FILE__, __LINE__);
			list ($numNewTopics) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			// If there're no real new topics in this board, mark the board as seen.
			if (empty($numNewTopics))
				$_REQUEST['boardseen'] = true;
			else
				$_SESSION['topicseen_cache'][$board] = $numNewTopics;
		}
		// Probably one less topic - maybe not, but even if we decrease this too fast it will only make us look more often.
		elseif (isset($_SESSION['topicseen_cache'][$board]))
			$_SESSION['topicseen_cache'][$board]--;

		// Mark board as seen if we came using last post link from BoardIndex. (or other places...)
		if (isset($_REQUEST['boardseen']))
		{
			$smfFunc['db_insert']('replace',
				"{$db_prefix}log_boards",
				array('id_msg', 'id_member', 'id_board'),
				array($modSettings['maxMsgID'], $user_info['id'], $board),
				array('id_member', 'id_board'), __FILE__, __LINE__
			);
		}
	}

	// Let's get nosey, who is viewing this topic?
	if (!empty($settings['display_who_viewing']))
	{
		// Start out with no one at all viewing it.
		$context['view_members'] = array();
		$context['view_members_list'] = array();
		$context['view_num_hidden'] = 0;

		// Search for members who have this topic set in their GET data.
		$request = $smfFunc['db_query']('', "
			SELECT
				lo.id_member, lo.log_time, mem.real_name, mem.member_name, mem.show_online,
				mg.online_color, mg.id_group, mg.group_name
			FROM {$db_prefix}log_online AS lo
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lo.id_member)
				LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = 0 THEN mem.id_post_group ELSE mem.id_group END)
			WHERE INSTR(lo.url, 's:5:\"topic\";i:$topic;') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if (empty($row['id_member']))
				continue;

			if (!empty($row['online_color']))
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" style="color: ' . $row['online_color'] . ';">' . $row['real_name'] . '</a>';
			else
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';

			$is_buddy = in_array($row['id_member'], $user_info['buddies']);
			if ($is_buddy)
				$link = '<b>' . $link . '</b>';

			// Add them both to the list and to the more detailed list.
			if (!empty($row['show_online']) || allowedTo('moderate_forum'))
				$context['view_members_list'][$row['log_time'] . $row['member_name']] = empty($row['show_online']) ? '<i>' . $link . '</i>' : $link;
			$context['view_members'][$row['log_time'] . $row['member_name']] = array(
				'id' => $row['id_member'],
				'username' => $row['member_name'],
				'name' => $row['real_name'],
				'group' => $row['id_group'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => $link,
				'is_buddy' => $is_buddy,
				'hidden' => empty($row['show_online']),
			);

			if (empty($row['show_online']))
				$context['view_num_hidden']++;
		}

		// The number of guests is equal to the rows minus the ones we actually used ;).
		$context['view_num_guests'] = $smfFunc['db_num_rows']($request) - count($context['view_members']);
		$smfFunc['db_free_result']($request);

		// Sort the list.
		krsort($context['view_members']);
		krsort($context['view_members_list']);
	}

	// If all is set, but not allowed... just unset it.
	if (isset($_REQUEST['all']) && empty($modSettings['enableAllMessages']))
		unset($_REQUEST['all']);
	// Otherwise, it must be allowed... so pretend start was -1.
	elseif (isset($_REQUEST['all']))
	{
		$_REQUEST['start'] = -1;
		$context['robot_no_index'] = true;
	}

	// Construct the page index, allowing for the .START method...
	$context['page_index'] = constructPageIndex($scripturl . '?topic=' . $topic . '.%d', $_REQUEST['start'], $topicinfo['num_replies'] + 1, $modSettings['defaultMaxMessages'], true);
	$context['start'] = $_REQUEST['start'];

	// This is information about which page is current, and which page we're on - in case you don't like the constructed page index. (again, wireles..)
	$context['page_info'] = array(
		'current_page' => $_REQUEST['start'] / $modSettings['defaultMaxMessages'] + 1,
		'num_pages' => floor($topicinfo['num_replies'] / $modSettings['defaultMaxMessages']) + 1
	);

	// Figure out all the link to the next/prev/first/last/etc. for wireless mainly.
	$context['links'] = array(
		'first' => $_REQUEST['start'] >= $modSettings['defaultMaxMessages'] ? $scripturl . '?topic=' . $topic . '.0' : '',
		'prev' => $_REQUEST['start'] >= $modSettings['defaultMaxMessages'] ? $scripturl . '?topic=' . $topic . '.' . ($_REQUEST['start'] - $modSettings['defaultMaxMessages']) : '',
		'next' => $_REQUEST['start'] + $modSettings['defaultMaxMessages'] < $topicinfo['num_replies'] + 1 ? $scripturl . '?topic=' . $topic. '.' . ($_REQUEST['start'] + $modSettings['defaultMaxMessages']) : '',
		'last' => $_REQUEST['start'] + $modSettings['defaultMaxMessages'] < $topicinfo['num_replies'] + 1 ? $scripturl . '?topic=' . $topic. '.' . (floor($topicinfo['num_replies'] / $modSettings['defaultMaxMessages']) * $modSettings['defaultMaxMessages']) : '',
		'up' => $scripturl . '?board=' . $board . '.0'
	);

	// If they are viewing all the posts, show all the posts, otherwise limit the number.
	if (!empty($modSettings['enableAllMessages']) && $topicinfo['num_replies'] + 1 > $modSettings['defaultMaxMessages'] && $topicinfo['num_replies'] + 1 < $modSettings['enableAllMessages'])
	{
		if (isset($_REQUEST['all']))
		{
			// No limit! (actually, there is a limit, but...)
			$modSettings['defaultMaxMessages'] = -1;
			$context['page_index'] .= empty($modSettings['compactTopicPagesEnable']) ? '<b>' . $txt['all'] . '</b> ' : '[<b>' . $txt['all'] . '</b>] ';

			// Set start back to 0...
			$_REQUEST['start'] = 0;
		}
		// They aren't using it, but the *option* is there, at least.
		else
			$context['page_index'] .= '&nbsp;<a href="' . $scripturl . '?topic=' . $topic . '.0;all">' . $txt['all'] . '</a> ';
	}

	// Build the link tree.
	$context['linktree'][] = array(
		'url' => $scripturl . '?topic=' . $topic . '.0',
		'name' => $topicinfo['subject'],
		'extra_before' => $settings['linktree_inline'] ? $txt['topic'] . ': ' : ''
	);

	// Build a list of this board's moderators.
	$context['moderators'] = &$board_info['moderators'];
	$context['link_moderators'] = array();
	if (!empty($board_info['moderators']))
	{
		// Add a link for each moderator...
		foreach ($board_info['moderators'] as $mod)
			$context['link_moderators'][] = '<a href="' . $scripturl . '?action=profile;u=' . $mod['id'] . '" title="' . $txt['board_moderator'] . '">' . $mod['name'] . '</a>';

		// And show it after the board's name.
		$context['linktree'][count($context['linktree']) - 2]['extra_after'] = ' (' . (count($context['link_moderators']) == 1 ? $txt['moderator'] : $txt['moderators']) . ': ' . implode(', ', $context['link_moderators']) . ')';
	}

	// Information about the current topic...
	$context['is_locked'] = $topicinfo['locked'];
	$context['is_sticky'] = $topicinfo['is_sticky'];
	$context['is_very_hot'] = $topicinfo['num_replies'] >= $modSettings['hotTopicVeryPosts'];
	$context['is_hot'] = $topicinfo['num_replies'] >= $modSettings['hotTopicPosts'];
	$context['is_approved'] = $topicinfo['approved'];

	// We don't want to show the poll icon in the topic class here, so pretend it's not one.
	$context['is_poll'] = false;
	determineTopicClass($context);

	$context['is_poll'] = $topicinfo['id_poll'] > 0 && $modSettings['pollMode'] == '1' && allowedTo('poll_view');

	// Did this user start the topic or not?
	$context['user']['started'] = $user_info['id'] == $topicinfo['id_member_started'] && !$user_info['is_guest'];
	$context['topic_starter_id'] = $topicinfo['id_member_started'];

	// Set the topic's information for the template.
	$context['subject'] = $topicinfo['subject'];
	$context['num_views'] = $topicinfo['num_views'];
	$context['mark_unread_time'] = $topicinfo['new_from'];

	// For quick reply we need a response prefix in the default forum language.
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

	// If we want to show event information in the topic, prepare the data.
	if (allowedTo('calendar_view') && !empty($modSettings['cal_showInTopic']) && !empty($modSettings['cal_enabled']))
	{
		// First, try create a better time format, ignoring the "time" elements.
		if (preg_match('~%[AaBbCcDdeGghjmuYy](?:[^%]*%[AaBbCcDdeGghjmuYy])*~', $user_info['time_format'], $matches) == 0 || empty($matches[0]))
			$date_string = $user_info['time_format'];
		else
			$date_string = $matches[0];

		// Any calendar information for this topic?
		$request = $smfFunc['db_query']('', "
			SELECT cal.id_event, cal.start_date, cal.end_date, cal.title, cal.id_member, mem.real_name
			FROM {$db_prefix}calendar AS cal
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = cal.id_member)
			WHERE cal.id_topic = $topic
			ORDER BY start_date", __FILE__, __LINE__);
		$context['linked_calendar_events'] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			// Prepare the dates for being formatted.
			$start_date = sscanf($row['start_date'], '%04d-%02d-%02d');
			$start_date = mktime(12, 0, 0, $start_date[1], $start_date[2], $start_date[0]);
			$end_date = sscanf($row['end_date'], '%04d-%02d-%02d');
			$end_date = mktime(12, 0, 0, $end_date[1], $end_date[2], $end_date[0]);

			$context['linked_calendar_events'][] = array(
				'id' => $row['id_event'],
				'title' => $row['title'],
				'can_edit' => allowedTo('calendar_edit_any') || ($row['id_member'] == $user_info['id'] && allowedTo('calendar_edit_own')),
				'modify_href' => $scripturl . '?action=post;msg=' . $topicinfo['id_first_msg'] . ';topic=' . $topic . '.0;calendar;eventid=' . $row['id_event'] . ';sesc=' . $context['session_id'],
				'start_date' => timeformat($start_date, $date_string),
				'start_timestamp' => forum_time(true, $start_date),
				'end_date' => timeformat($end_date, $date_string),
				'end_timestamp' => forum_time(true, $start_date),
				'is_last' => false
			);
		}
		$smfFunc['db_free_result']($request);

		if (!empty($context['linked_calendar_events']))
			$context['linked_calendar_events'][count($context['linked_calendar_events']) - 1]['is_last'] = true;
	}

	// Create the poll info if it exists.
	if ($context['is_poll'])
	{
		// Get the question and if it's locked.
		$request = $smfFunc['db_query']('', "
			SELECT
				p.question, p.voting_locked, p.hide_results, p.expire_time, p.max_votes, p.change_vote,
				p.id_member, IFNULL(mem.real_name, p.poster_name) AS poster_name
			FROM {$db_prefix}polls AS p
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = p.id_member)
			WHERE p.id_poll = $topicinfo[id_poll]
			LIMIT 1", __FILE__, __LINE__);
		$pollinfo = $smfFunc['db_fetch_assoc']($request);
		$smfFunc['db_free_result']($request);

		$request = $smfFunc['db_query']('', "
			SELECT COUNT(DISTINCT id_member) AS total
			FROM {$db_prefix}log_polls
			WHERE id_poll = $topicinfo[id_poll]", __FILE__, __LINE__);
		list ($pollinfo['total']) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		// Get all the options, and calculate the total votes.
		$request = $smfFunc['db_query']('', "
			SELECT pc.id_choice, pc.label, pc.votes, IFNULL(lp.id_choice, -1) AS voted_this
			FROM {$db_prefix}poll_choices AS pc
				LEFT JOIN {$db_prefix}log_polls AS lp ON (lp.id_choice = pc.id_choice AND lp.id_poll = $topicinfo[id_poll] AND lp.id_member = $user_info[id])
			WHERE pc.id_poll = $topicinfo[id_poll]", __FILE__, __LINE__);
		$pollOptions = array();
		$realtotal = 0;
		$pollinfo['has_voted'] = false;
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			censorText($row['label']);
			$pollOptions[$row['id_choice']] = $row;
			$realtotal += $row['votes'];
			$pollinfo['has_voted'] |= $row['voted_this'] != -1;
		}
		$smfFunc['db_free_result']($request);

		// Set up the basic poll information.
		$context['poll'] = array(
			'id' => $topicinfo['id_poll'],
			'image' => 'normal_' . (empty($pollinfo['voting_locked']) ? 'poll' : 'locked_poll'),
			'question' => parse_bbc($pollinfo['question']),
			'total_votes' => $pollinfo['total'],
			'change_vote' => !empty($pollinfo['change_vote']),
			'is_locked' => !empty($pollinfo['voting_locked']),
			'options' => array(),
			'lock' => allowedTo('poll_lock_any') || ($context['user']['started'] && allowedTo('poll_lock_own')),
			'edit' => allowedTo('poll_edit_any') || ($context['user']['started'] && allowedTo('poll_edit_own')),
			'allowed_warning' => $pollinfo['max_votes'] > 1 ? sprintf($txt['poll_options6'], $pollinfo['max_votes']) : '',
			'is_expired' => !empty($pollinfo['expire_time']) && $pollinfo['expire_time'] < time(),
			'expire_time' => !empty($pollinfo['expire_time']) ? timeformat($pollinfo['expire_time']) : 0,
			'has_voted' => !empty($pollinfo['has_voted']),
			'starter' => array(
				'id' => $pollinfo['id_member'],
				'name' => $row['poster_name'],
				'href' => $pollinfo['id_member'] == 0 ? '' : $scripturl . '?action=profile;u=' . $pollinfo['id_member'],
				'link' => $pollinfo['id_member'] == 0 ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $pollinfo['id_member'] . '">' . $row['poster_name'] . '</a>'
			)
		);

		// You're allowed to vote if:
		// 1. the poll did not expire, and
		// 2. you're not a guest... and
		// 3. you're not trying to view the results, and
		// 4. the poll is not locked, and
		// 5. you have the proper permissions, and
		// 6. you haven't already voted before.
		$context['allow_vote'] = !$context['poll']['is_expired'] && !$user_info['is_guest'] && empty($pollinfo['voting_locked']) && allowedTo('poll_vote') && !$context['poll']['has_voted'];

		// You're allowed to view the results if:
		// 1. you're just a super-nice-guy, or
		// 2. anyone can see them (hide_results == 0), or
		// 3. you can see them after you voted (hide_results == 1), or
		// 4. you've waited long enough for the poll to expire. (whether hide_results is 1 or 2.)
		$context['allow_poll_view'] = allowedTo('moderate_board') || $pollinfo['hide_results'] == 0 || ($pollinfo['hide_results'] == 1 && $context['poll']['has_voted']) || $context['poll']['is_expired'];
		$context['poll']['show_results'] = $context['allow_poll_view'] && isset($_REQUEST['viewResults']);

		// You're allowed to change your vote if:
		// 1. the poll did not expire, and
		// 2. you're not a guest... and
		// 3. the poll is not locked, and
		// 4. you have the proper permissions, and
		// 5. you have already voted, and
		// 6. the poll creator has said you can!
		$context['allow_change_vote'] = !$context['poll']['is_expired'] && !$user_info['is_guest'] && empty($pollinfo['voting_locked']) && allowedTo('poll_vote') && $context['poll']['has_voted'] && $context['poll']['change_vote'];

		// Calculate the percentages and bar lengths...
		$divisor = $realtotal == 0 ? 1 : $realtotal;

		// Determine if a decimal point is needed in order for the options to add to 100%.
		$precision = $realtotal == 100 ? 0 : 1;

		// Now look through each option, and...
		foreach ($pollOptions as $i => $option)
		{
			// First calculate the percentage, and then the width of the bar...
			$bar = round(($option['votes'] * 100) / $divisor, $precision);
			$barWide = $bar == 0 ? 1 : floor(($bar * 8) / 3);

			// Now add it to the poll's contextual theme data.
			$context['poll']['options'][$i] = array(
				'id' => 'options-' . $i,
				'percent' => $bar,
				'votes' => $option['votes'],
				'voted_this' => $option['voted_this'] != -1,
				'bar' => '<span style="white-space: nowrap;"><img src="' . $settings['images_url'] . '/poll_left.gif" alt="" /><img src="' . $settings['images_url'] . '/poll_middle.gif" width="' . $barWide . '" height="12" alt="-" /><img src="' . $settings['images_url'] . '/poll_right.gif" alt="" /></span>',
				'bar_width' => $barWide,
				'option' => parse_bbc($option['label']),
				'vote_button' => '<input type="' . ($pollinfo['max_votes'] > 1 ? 'checkbox' : 'radio') . '" name="options[]" id="options-' . $i . '" value="' . $i . '" class="check" />'
			);
		}
	}

	// Calculate the fastest way to get the messages!
	$ascending = empty($options['view_newest_first']);
	$start = $_REQUEST['start'];
	$limit = $modSettings['defaultMaxMessages'];
	$firstIndex = 0;
	if ($start > $topicinfo['num_replies'] / 2 && $modSettings['defaultMaxMessages'] != -1)
	{
		$ascending = !$ascending;
		$limit = $topicinfo['num_replies'] < $start + $limit ? $topicinfo['num_replies'] - $start + 1 : $limit;
		$start = $topicinfo['num_replies'] < $start + $limit ? 0 : $topicinfo['num_replies'] - $start - $limit + 1;
		$firstIndex = $limit - 1;
	}

	// Get each post and poster in this topic.
	$request = $smfFunc['db_query']('', "
		SELECT id_msg, id_member
		FROM {$db_prefix}messages
		WHERE id_topic = $topic
			" . (allowedTo('approve_posts') ? '' : ' AND approved = 1') . "
		ORDER BY id_msg " . ($ascending ? '' : 'DESC') . ($modSettings['defaultMaxMessages'] == -1 ? '' : "
		LIMIT $start, $limit"), __FILE__, __LINE__);

	$messages = array();
	$posters = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (!empty($row['id_member']))
			$posters[] = $row['id_member'];
		$messages[] = $row['id_msg'];
	}
	$smfFunc['db_free_result']($request);
	$posters = array_unique($posters);

	$attachments = array();

	// If there _are_ messages here... (probably an error otherwise :!)
	if (!empty($messages))
	{
		// Fetch attachments.
		if (!empty($modSettings['attachmentEnable']) && allowedTo('view_attachments'))
		{
			$request = $smfFunc['db_query']('', "
				SELECT
					a.id_attach, a.id_msg, a.filename, IFNULL(a.size, 0) AS filesize, a.downloads, a.approved,
					a.width, a.height" . (empty($modSettings['attachmentShowImages']) || empty($modSettings['attachmentThumbnails']) ? '' : ",
					IFNULL(thumb.id_attach, 0) AS id_thumb, thumb.width AS thumb_width, thumb.height AS thumb_height") . "
				FROM {$db_prefix}attachments AS a" . (empty($modSettings['attachmentShowImages']) || empty($modSettings['attachmentThumbnails']) ? '' : "
					LEFT JOIN {$db_prefix}attachments AS thumb ON (thumb.id_attach = a.id_thumb)") . "
				WHERE a.id_msg IN (" . implode(',', $messages) . ")
					AND a.attachment_type = 0
					" . (allowedTo('approve_posts') ? '' : ' AND a.approved = 1'), __FILE__, __LINE__);
			$temp = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				$temp[$row['id_attach']] = $row;

				if (!isset($attachments[$row['id_msg']]))
					$attachments[$row['id_msg']] = array();
			}
			$smfFunc['db_free_result']($request);

			// This is better than sorting it with the query...
			ksort($temp);

			foreach ($temp as $row)
				$attachments[$row['id_msg']][] = $row;
		}

		// What?  It's not like it *couldn't* be only guests in this topic...
		if (!empty($posters))
			loadMemberData($posters);
		$messages_request = $smfFunc['db_query']('', "
			SELECT
				id_msg, icon, subject, poster_time, poster_ip, id_member, modified_time, modified_name, body,
				smileys_enabled, poster_name, poster_email, approved,
				id_msg_modified < $topicinfo[new_from] AS isRead
			FROM {$db_prefix}messages
			WHERE id_msg IN (" . implode(',', $messages) . ")
			ORDER BY id_msg" . (empty($options['view_newest_first']) ? '' : ' DESC'), __FILE__, __LINE__);

		// Go to the last message if the given time is beyond the time of the last message.
		if (isset($context['start_from']) && $context['start_from'] >= $topicinfo['num_replies'])
			$context['start_from'] = $topicinfo['num_replies'];

		// Since the anchor information is needed on the top of the page we load these variables beforehand.
		$context['first_message'] = isset($messages[$firstIndex]) ? $messages[$firstIndex] : $messages[0];
		if (empty($options['view_newest_first']))
			$context['first_new_message'] = isset($context['start_from']) && $_REQUEST['start'] == $context['start_from'];
		else
			$context['first_new_message'] = isset($context['start_from']) && $_REQUEST['start'] == $topicinfo['num_replies'] - $context['start_from'];
	}
	else
	{
		$messages_request = false;
		$context['first_message'] = 0;
		$context['first_new_message'] = false;
	}

	$context['jump_to'] = array(
		'label' => addslashes(un_htmlspecialchars($txt['jump_to'])),
		'board_name' => un_htmlspecialchars($board_info['name']),
		'child_level' => $board_info['child_level'],
	);

	// Set the callback.  (do you REALIZE how much memory all the messages would take?!?)
	$context['get_message'] = 'prepareDisplayContext';

	// Basic settings.... may be converted over at some point.
	$context['allow_hide_email'] = !empty($modSettings['allow_hide_email']) || ($user_info['is_guest'] && !empty($modSettings['guest_hideContacts']));

	// Now set all the wonderful, wonderful permissions... like moderation ones...
	$common_permissions = array(
		'can_approve' => 'approve_posts',
		'can_ban' => 'manage_bans',
		'can_sticky' => 'make_sticky',
		'can_merge' => 'merge_any',
		'can_split' => 'split_any',
		'calendar_post' => 'calendar_post',
		'can_mark_notify' => 'mark_any_notify',
		'can_send_topic' => 'send_topic',
		'can_send_pm' => 'pm_send',
		'can_report_moderator' => 'report_any',
		'can_moderate_forum' => 'moderate_forum'
	);
	foreach ($common_permissions as $contextual => $perm)
		$context[$contextual] = allowedTo($perm);

	// Permissions with _any/_own versions.  $context[YYY] => ZZZ_any/_own.
	$anyown_permissions = array(
		'can_move' => 'move',
		'can_lock' => 'lock',
		'can_delete' => 'remove',
		'can_add_poll' => 'poll_add',
		'can_remove_poll' => 'poll_remove',
		'can_reply' => 'post_reply',
		'can_reply_unapproved' => 'post_unapproved_replies',
	);
	foreach ($anyown_permissions as $contextual => $perm)
		$context[$contextual] = allowedTo($perm . '_any') || ($context['user']['started'] && allowedTo($perm . '_own'));

	// Cleanup all the permissions with extra stuff...
	$context['can_mark_notify'] &= !$context['user']['is_guest'];
	$context['can_sticky'] &= !empty($modSettings['enableStickyTopics']);
	$context['calendar_post'] &= !empty($modSettings['cal_enabled']);
	$context['can_add_poll'] &= $modSettings['pollMode'] == '1' && $topicinfo['id_poll'] <= 0;
	$context['can_remove_poll'] &= $modSettings['pollMode'] == '1' && $topicinfo['id_poll'] > 0;
	$context['can_reply'] &= empty($topicinfo['locked']) || allowedTo('moderate_board');
	// Handle approval flags...
	$context['can_reply_approved'] = $context['can_reply'];
	$context['can_reply'] |= $context['can_reply_unapproved'];

	// Start this off for quick moderation - it will be or'd for each post.
	$context['can_remove_post'] = allowedTo('delete_any') || (allowedTo('delete_replies') && $context['user']['started']);

	// Wireless shows a "more" if you can do anything special.
	if (WIRELESS && WIRELESS_PROTOCOL != 'wap')
	{
		//!!! Add banning.
		$context['wireless_more'] = $context['can_sticky'] || $context['can_lock'] || allowedTo('modify_any');
		$context['wireless_moderate'] = isset($_GET['moderate']) ? ';moderate' : '';
	}

	// Load up the "double post" sequencing magic.
	if (!empty($options['display_quick_reply']))
		checkSubmitOnce('register');
}

// Callback for the message display.
function prepareDisplayContext($reset = false)
{
	global $settings, $txt, $modSettings, $scripturl, $options, $user_info, $smfFunc;
	global $memberContext, $context, $messages_request, $topic, $attachments, $topicinfo;

	static $counter = null;

	// If the query returned false, bail.
	if ($messages_request == false)
		return false;

	// Remember which message this is.  (ie. reply #83)
	if ($counter === null || $reset)
		$counter = empty($options['view_newest_first']) ? $context['start'] : $context['num_replies'] - $context['start'];

	// Start from the beginning...
	if ($reset)
		return @$smfFunc['db_data_seek']($messages_request, 0);

	// Attempt to get the next message.
	$message = $smfFunc['db_fetch_assoc']($messages_request);
	if (!$message)
		return false;

	// $context['icon_sources'] says where each icon should come from - here we set up the ones which will always exist!
	if (empty($context['icon_sources']))
	{
		$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
		$context['icon_sources'] = array();
		foreach ($stable_icons as $icon)
			$context['icon_sources'][$icon] = 'images_url';
	}

	// Message Icon Management... check the images exist.
	if (empty($modSettings['messageIconChecks_disable']))
	{
		// If the current icon isn't known, then we need to do something...
		if (!isset($context['icon_sources'][$message['icon']]))
			$context['icon_sources'][$message['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $message['icon'] . '.gif') ? 'images_url' : 'default_images_url';
	}
	elseif (!isset($context['icon_sources'][$message['icon']]))
		$context['icon_sources'][$message['icon']] = 'images_url';

	// If you're a lazy bum, you probably didn't give a subject...
	$message['subject'] = $message['subject'] != '' ? $message['subject'] : $txt['no_subject'];

	// Are you allowed to remove at least a single reply?
	$context['can_remove_post'] |= allowedTo('delete_own') && (empty($modSettings['edit_disable_time']) || $message['poster_time'] + $modSettings['edit_disable_time'] * 60 >= time()) && $message['id_member'] == $user_info['id'];

	// If it couldn't load, or the user was a guest.... someday may be done with a guest table.
	if (!loadMemberContext($message['id_member']))
	{
		// Notice this information isn't used anywhere else....
		$memberContext[$message['id_member']]['name'] = $message['poster_name'];
		$memberContext[$message['id_member']]['id'] = 0;
		$memberContext[$message['id_member']]['group'] = $txt['guest_title'];
		$memberContext[$message['id_member']]['link'] = $message['poster_name'];
		$memberContext[$message['id_member']]['email'] = $message['poster_email'];
		$memberContext[$message['id_member']]['hide_email'] = $message['poster_email'] == '' || (!empty($modSettings['guest_hideContacts']) && $user_info['is_guest']);
		$memberContext[$message['id_member']]['is_guest'] = true;
	}
	else
	{
		$memberContext[$message['id_member']]['can_view_profile'] = allowedTo('profile_view_any') || ($message['id_member'] == $user_info['id'] && allowedTo('profile_view_own'));
		$memberContext[$message['id_member']]['is_topic_starter'] = $message['id_member'] == $context['topic_starter_id'];
	}

	$memberContext[$message['id_member']]['ip'] = $message['poster_ip'];

	// Do the censor thang.
	censorText($message['body']);
	censorText($message['subject']);

	// Run BBC interpreter on the message.
	$message['body'] = parse_bbc($message['body'], $message['smileys_enabled'], $message['id_msg']);

	// Compose the memory eat- I mean message array.
	$output = array(
		'attachment' => loadAttachmentContext($message['id_msg']),
		'alternate' => $counter % 2,
		'id' => $message['id_msg'],
		'href' => $scripturl . '?topic=' . $topic . '.msg' . $message['id_msg'] . '#msg' . $message['id_msg'],
		'link' => '<a href="' . $scripturl . '?topic=' . $topic . '.msg' . $message['id_msg'] . '#msg' . $message['id_msg'] . '" rel="nofollow">' . $message['subject'] . '</a>',
		'member' => &$memberContext[$message['id_member']],
		'icon' => $message['icon'],
		'icon_url' => $settings[$context['icon_sources'][$message['icon']]] . '/post/' . $message['icon'] . '.gif',
		'subject' => $message['subject'],
		'time' => timeformat($message['poster_time']),
		'timestamp' => forum_time(true, $message['poster_time']),
		'counter' => $counter,
		'modified' => array(
			'time' => timeformat($message['modified_time']),
			'timestamp' => forum_time(true, $message['modified_time']),
			'name' => $message['modified_name']
		),
		'body' => $message['body'],
		'new' => empty($message['isRead']),
		'approved' => $message['approved'],
		'first_new' => isset($context['start_from']) && $context['start_from'] == $counter,
		'can_approve' => !$message['approved'] && $context['can_approve'],
		'can_modify' => allowedTo('modify_any') || (allowedTo('modify_replies') && $context['user']['started']) || (allowedTo('modify_own') && $message['id_member'] == $user_info['id'] && (empty($modSettings['edit_disable_time']) || $message['poster_time'] + $modSettings['edit_disable_time'] * 60 > time())),
		'can_remove' => allowedTo('delete_any') || (allowedTo('delete_replies') && $context['user']['started']) || (allowedTo('delete_own') && $message['id_member'] == $user_info['id'] && (empty($modSettings['edit_disable_time']) || $message['poster_time'] + $modSettings['edit_disable_time'] * 60 > time())),
		'can_see_ip' => allowedTo('moderate_forum') || ($message['id_member'] == $user_info['id'] && !empty($user_info['id'])),
	);

	if (empty($options['view_newest_first']))
		$counter++;
	else
		$counter--;

	return $output;
}

// Download an attachment.
function Download()
{
	global $txt, $modSettings, $db_prefix, $user_info, $scripturl, $context, $sourcedir, $smfFunc;

	$context['no_last_modified'] = true;

	// Make sure some attachment was requested!
	if (!isset($_REQUEST['attach']) && !isset($_REQUEST['id']))
		fatal_lang_error('no_access', false);

	$_REQUEST['attach'] = isset($_REQUEST['attach']) ? (int) $_REQUEST['attach'] : (int) $_REQUEST['id'];

	if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'avatar')
	{
		$request = $smfFunc['db_query']('', "
			SELECT filename, id_attach, attachment_type, approved
			FROM {$db_prefix}attachments
			WHERE id_attach = $_REQUEST[attach]
				AND id_member > 0
			LIMIT 1", __FILE__, __LINE__);
		$_REQUEST['image'] = true;
	}
	// This is just a regular attachment...
	else
	{
		isAllowedTo('view_attachments');

		// Make sure this attachment is on this board.
		$request = $smfFunc['db_query']('', "
			SELECT a.filename, a.id_attach, a.attachment_type, a.approved
			FROM {$db_prefix}attachments AS a
				INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = a.id_msg)
				INNER JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board AND $user_info[query_see_board])
			WHERE a.id_attach = $_REQUEST[attach]
			LIMIT 1", __FILE__, __LINE__);
	}
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_access', false);
	list ($real_filename, $id_attach, $attachment_type, $is_approved) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// If it isn't yet approved, do they have permission to view it?
	if (!$is_approved && ($attachment_type == 0 || $attachment_type == 3))
		isAllowedTo('approve_posts');
		
	// Update the download counter (unless it's a thumbnail).
	if ($attachment_type != 3)
		$smfFunc['db_query']('attach_download_increase', "
			UPDATE LOW_PRIORITY {$db_prefix}attachments
			SET downloads = downloads + 1
			WHERE id_attach = $id_attach", __FILE__, __LINE__);

	$filename = getAttachmentFilename($real_filename, $_REQUEST['attach']);

	// This is done to clear any output that was made before now. (would use ob_clean(), but that's PHP 4.2.0+...)
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']) && @version_compare(PHP_VERSION, '4.2.0') >= 0 && @filesize($filename) <= 4194304)
		@ob_start('ob_gzhandler');
	else
	{
		ob_start();
		header('Content-Encoding: none');
	}

	// No point in a nicer message, because this is supposed to be an attachment anyway...
	if (!file_exists($filename))
	{
		loadLanguage('Errors');

		header('HTTP/1.0 404 ' . $txt['attachment_not_found']);
		header('Content-Type: text/plain; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

		// We need to die like this *before* we send any anti-caching headers as below.
		die('404 - ' . $txt['attachment_not_found']);
	}

	// If it hasn't been modified since the last time this attachement was retrieved, there's no need to display it again.
	if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
	{
		list($modified_since) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
		if (strtotime($modified_since) >= filemtime($filename))
		{
			ob_end_clean();

			// Answer the question - no, it hasn't been modified ;).
			header('HTTP/1.1 304 Not Modified');
			exit;
		}
	}

	// Check whether the ETag was sent back, and cache based on that...
	$file_md5 = '"' . md5_file($filename) . '"';
	if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $file_md5) !== false)
	{
		ob_end_clean();

		header('HTTP/1.1 304 Not Modified');
		exit;
	}

	// Send the attachment headers.
	header('Pragma: ');
	if (!$context['browser']['is_gecko'])
		header('Content-Transfer-Encoding: binary');
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 525600 * 60) . ' GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
	header('Accept-Ranges: bytes');
	header('Set-Cookie:');
	header('Connection: close');
	header('ETag: ' . $file_md5);

	if (filesize($filename) != 0)
	{
		$size = @getimagesize($filename);
		if (!empty($size))
		{
			// What headers are valid?
			$validTypes = array(
				1 => 'gif',
				2 => 'jpeg',
				3 => 'png',
				5 => 'psd',
				6 => 'bmp',
				7 => 'tiff',
				8 => 'tiff',
				9 => 'jpeg',
				14 => 'iff',
			);

			// Stupid damn IE - work around it and it's exploits.
			$fp = fopen($filename, 'rb');
			if ($fp)
			{
				if (preg_match('~<script|<embed|<object|<html|<head|<body~si', fread($fp, 250)))
					$size = array(2 => 'invalid');
				fclose($fp);
			}

			// Do we have a mime type we can simpy use?
			if (!empty($size['mime']))
				header('Content-Type: ' . $size['mime']);
			elseif (isset($validTypes[$size[2]]))
				header('Content-Type: image/' . $validTypes[$size[2]]);
			// Otherwise - let's think safety first... it might not be an image...
			elseif (isset($_REQUEST['image']))
				unset($_REQUEST['image']);
		}
		// Once again - safe!
		elseif (isset($_REQUEST['image']))
			unset($_REQUEST['image']);
	}

	if (!isset($_REQUEST['image']))
	{
		header('Content-Disposition: attachment; filename="' . $real_filename . '"');
		header('Content-Type: application/octet-stream');
	}

	// If this has an "image extension" - but isn't actually an image - then ensure it isn't cached cause of silly IE.
	if (!isset($_REQUEST['image']) && in_array(substr($real_filename, -4), array('.gif', '.jpg', '.bmp', '.png', 'jpeg', 'tiff')))
    		header('Cache-Control: no-cache'); 
    	else
		header('Cache-Control: max-age=' . (525600 * 60) . ', private');

	if (empty($modSettings['enableCompressedOutput']) || filesize($filename) > 4194304)
		header('Content-Length: ' . filesize($filename));

	// Try to buy some time...
	@set_time_limit(0);

	// For text files.....
	if (!isset($_REQUEST['image']) && in_array(substr($real_filename, -4), array('.txt', '.css', '.htm', '.php', '.xml')))
	{
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false)
			$callback = create_function('$buffer', 'return preg_replace(\'~[\r]?\n~\', "\r\n", $buffer);');
		elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false)
			$callback = create_function('$buffer', 'return preg_replace(\'~[\r]?\n~\', "\r", $buffer);');
		else
			$callback = create_function('$buffer', 'return preg_replace(\'~\r~\', "\r\n", $buffer);');
	}

	// Since we don't do output compression for files this large...
	if (filesize($filename) > 4194304)
	{
		// Forcibly end any output buffering going on.
		if (function_exists('ob_get_level'))
		{
			while (@ob_get_level() > 0)
				@ob_end_clean();
		}
		else
		{
			@ob_end_clean();
			@ob_end_clean();
			@ob_end_clean();
		}

		$fp = fopen($filename, 'rb');
		while (!feof($fp))
		{
			if (isset($callback))
				echo $callback(fread($fp, 8192));
			else
				echo fread($fp, 8192);
			flush();
		}
		fclose($fp);
	}
	// On some of the less-bright hosts, readfile() is disabled.  It's just a faster, more byte safe, version of what's in the if.
	elseif (isset($callback) || @readfile($filename) == null)
		echo isset($callback) ? $callback(file_get_contents($filename)) : file_get_contents($filename);

	obExit(false);
}

function loadAttachmentContext($id_msg)
{
	global $attachments, $modSettings, $txt, $scripturl, $topic, $db_prefix, $sourcedir, $smfFunc;

	// Set up the attachment info - based on code by Meriadoc.
	$attachmentData = array();
	$have_unapproved = false;
	if (isset($attachments[$id_msg]) && !empty($modSettings['attachmentEnable']))
	{
		foreach ($attachments[$id_msg] as $i => $attachment)
		{
			$attachmentData[$i] = array(
				'id' => $attachment['id_attach'],
				'name' => $attachment['filename'],
				'downloads' => $attachment['downloads'],
				'size' => round($attachment['filesize'] / 1024, 2) . ' ' . $txt['kilobyte'],
				'byte_size' => $attachment['filesize'],
				'href' => $scripturl . '?action=dlattach;topic=' . $topic . '.0;attach=' . $attachment['id_attach'],
				'link' => '<a href="' . $scripturl . '?action=dlattach;topic=' . $topic . '.0;attach=' . $attachment['id_attach'] . '">' . $attachment['filename'] . '</a>',
				'is_image' => !empty($attachment['width']) && !empty($attachment['height']) && !empty($modSettings['attachmentShowImages']),
				'is_approved' => $attachment['approved'],
			);

			// If something is unapproved we'll note it so we can sort them.
			if (!$attachment['approved'])
				$have_unapproved = true;

			if (!$attachmentData[$i]['is_image'])
				continue;

			$attachmentData[$i]['real_width'] = $attachment['width'];
			$attachmentData[$i]['width'] = $attachment['width'];
			$attachmentData[$i]['real_height'] = $attachment['height'];
			$attachmentData[$i]['height'] = $attachment['height'];

			// Let's see, do we want thumbs?
			if (!empty($modSettings['attachmentThumbnails']) && !empty($modSettings['attachmentThumbWidth']) && !empty($modSettings['attachmentThumbHeight']) && ($attachment['width'] > $modSettings['attachmentThumbWidth'] || $attachment['height'] > $modSettings['attachmentThumbHeight']) && strlen($attachment['filename']) < 249)
			{
				// A proper thumb doesn't exist yet? Create one!
				if (empty($attachment['id_thumb']) || $attachment['thumb_width'] > $modSettings['attachmentThumbWidth'] || $attachment['thumb_height'] > $modSettings['attachmentThumbHeight'] || ($attachment['thumb_width'] < $modSettings['attachmentThumbWidth'] && $attachment['thumb_height'] < $modSettings['attachmentThumbHeight']))
				{
					$filename = getAttachmentFilename($attachment['filename'], $attachment['id_attach']);

					require_once($sourcedir . '/Subs-Graphics.php');
					if (createThumbnail($filename, $modSettings['attachmentThumbWidth'], $modSettings['attachmentThumbHeight']))
					{
						// Calculate the size of the created thumbnail.
						list ($attachment['thumb_width'], $attachment['thumb_height']) = @getimagesize($filename . '_thumb');
						$thumb_size = filesize($filename . '_thumb');

						$thumb_filename = $smfFunc['db_escape_string']($attachment['filename'] . '_thumb');

						// Add this beauty to the database.
						$smfFunc['db_query']('', "
							INSERT INTO {$db_prefix}attachments
								(id_msg, attachment_type, filename, size, width, height)
							VALUES ($id_msg, 3, '$thumb_filename', " . (int) $thumb_size . ", " . (int) $attachment['thumb_width'] . ", " . (int) $attachment['thumb_height'] . ")", __FILE__, __LINE__);
						$attachment['id_thumb'] = db_insert_id("{$db_prefix}attachments", 'id_attach');
						if (!empty($attachment['id_thumb']))
						{
							$smfFunc['db_query']('', "
								UPDATE {$db_prefix}attachments
								SET id_thumb = $attachment[id_thumb]
								WHERE id_attach = $attachment[id_attach]", __FILE__, __LINE__);

							$thumb_realname = getAttachmentFilename($thumb_filename, $attachment['id_thumb'], true);
							rename($filename . '_thumb', $modSettings['attachmentUploadDir'] . '/' . $thumb_realname);
						}
					}
				}

				$attachmentData[$i]['width'] = $attachment['thumb_width'];
				$attachmentData[$i]['height'] = $attachment['thumb_height'];
			}

			if (!empty($attachment['id_thumb']))
				$attachmentData[$i]['thumbnail'] = array(
					'id' => $attachment['id_thumb'],
					'href' => $scripturl . '?action=dlattach;topic=' . $topic . '.0;attach=' . $attachment['id_thumb'] . ';image',
				);
			$attachmentData[$i]['thumbnail']['has_thumb'] = !empty($attachment['id_thumb']);

			// If thumbnails are disabled, check the maximum size of the image.
			if (!$attachmentData[$i]['thumbnail']['has_thumb'] && ((!empty($modSettings['max_image_width']) && $attachment['width'] > $modSettings['max_image_width']) || (!empty($modSettings['max_image_height']) && $attachment['height'] > $modSettings['max_image_height'])))
			{
				if (!empty($modSettings['max_image_width']) && (empty($modSettings['max_image_height']) || $attachment['height'] * $modSettings['max_image_width'] / $attachment['width'] <= $modSettings['max_image_height']))
				{
					$attachmentData[$i]['width'] = $modSettings['max_image_width'];
					$attachmentData[$i]['height'] = floor($attachment['height'] * $modSettings['max_image_width'] / $attachment['width']);
				}
				elseif (!empty($modSettings['max_image_width']))
				{
					$attachmentData[$i]['width'] = floor($attachment['width'] * $modSettings['max_image_height'] / $attachment['height']);
					$attachmentData[$i]['height'] = $modSettings['max_image_height'];
				}
			}
			elseif ($attachmentData[$i]['thumbnail']['has_thumb'])
			{
				// If the image is too large to show inline, make it a popup.
				if (((!empty($modSettings['max_image_width']) && $attachmentData[$i]['real_width'] > $modSettings['max_image_width']) || (!empty($modSettings['max_image_height']) && $attachmentData[$i]['real_height'] > $modSettings['max_image_height'])))
					$attachmentData[$i]['thumbnail']['javascript'] = "return reqWin('" . $attachmentData[$i]['href'] . ";image', " . ($attachment['width'] + 20) . ', ' . ($attachment['height'] + 20) . ', true);';
				else
					$attachmentData[$i]['thumbnail']['javascript'] = 'return expandThumb(' . $attachment['id_attach'] . ');';
			}

			if (!$attachmentData[$i]['thumbnail']['has_thumb'])
				$attachmentData[$i]['downloads']++;
		}
	}

	// Do we need to instigate a sort?
	if ($have_unapproved)
		usort($attachmentData, 'approved_attach_sort');

	return $attachmentData;
}

// A sort function for putting unapproved attachments first.
function approved_attach_sort($a, $b)
{
	if ($a['is_approved'] == $b['is_approved'])
		return 0;

	return $a['is_approved'] > $b['is_approved'] ? -1 : 1;
}

// In-topic quick moderation.
function QuickInTopicModeration()
{
	global $sourcedir, $db_prefix, $topic, $board, $user_info, $smfFunc, $modSettings;

	// Check the session = get or post.
	checkSession('request');

	require_once($sourcedir . '/RemoveTopic.php');

	if (empty($_REQUEST['msgs']))
		redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);

	$messages = array();
	foreach ($_REQUEST['msgs'] as $dummy)
		$messages[] = (int) $dummy;

	// Allowed to delete any message?
	if (allowedTo('delete_any'))
		$allowed_all = true;
	// Allowed to delete replies to their messages?
	elseif (allowedTo('delete_replies'))
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_member_started
			FROM {$db_prefix}topics
			WHERE id_topic = $topic
			LIMIT 1", __FILE__, __LINE__);
		list ($starter) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		$allowed_all = $starter == $user_info['id'];
	}
	else
		$allowed_all = false;

	// Make sure they're allowed to delete their own messages, if not any.
	if (!$allowed_all)
		isAllowedTo('delete_own');

	// Allowed to remove which messages?
	$request = $smfFunc['db_query']('', "
		SELECT id_msg, subject, id_member, poster_time
		FROM {$db_prefix}messages
		WHERE id_msg IN (" . implode(', ', $messages) . ")
			AND id_topic = $topic" . (!$allowed_all ? "
			AND id_member = $user_info[id]" : '') . "
		LIMIT " . count($messages), __FILE__, __LINE__);
	$messages = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (!$allowed_all && !empty($modSettings['edit_disable_time']) && $row['poster_time'] + $modSettings['edit_disable_time'] * 60 < time())
			continue;

		$messages[$row['id_msg']] = array($row['subject'], $row['id_member']);
	}
	$smfFunc['db_free_result']($request);

	// Get the first message in the topic - because you can't delete that!
	$request = $smfFunc['db_query']('', "
		SELECT id_first_msg, id_last_msg
		FROM {$db_prefix}topics
		WHERE id_topic = $topic
		LIMIT 1", __FILE__, __LINE__);
	list ($first_message, $last_message) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Delete all the messages we know they can delete. ($messages)
	foreach ($messages as $message => $info)
	{
		// Just skip the first message.
		if ($message == $first_message && $message != $last_message)
			continue;

		removeMessage($message);

		// Log this moderation action ;).
		if (allowedTo('delete_any') && (!allowedTo('delete_own') || $info[1] != $user_info['id']))
			logAction('delete', array('topic' => $topic, 'subject' => $info[0], 'member' => $info[1], 'board' => $board));
	}

	redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

?>