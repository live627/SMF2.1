<?php
/**********************************************************************************
* Post.php                                                                        *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 1.1                                    *
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

/*	The job of this file is to handle everything related to posting replies,
	new topics, quotes, and modifications to existing posts.  It also handles
	quoting posts by way of javascript.

	void Post()
		- handles showing the post screen, loading the post to be modified, and
		  loading any post quoted.
		- additionally handles previews of posts.
		- uses the Post template and language file, main sub template.
		- allows wireless access using the protocol_post sub template.
		- requires different permissions depending on the actions, but most
		  notably post_new, post_reply_own, and post_reply_any.
		- shows options for the editing and posting of calendar events and
		  attachments, as well as the posting of polls.
		- accessed from ?action=post.

	void Post2()
		- actually posts or saves the message composed with Post().
		- requires various permissions depending on the action.
		- handles attachment, post, and calendar saving.
		- sends off notifications, and allows for announcements and moderation.
		- accessed from ?action=post2.

	void AnnounceTopic()
		- handle the announce topic function (action=announce).
		- checks the topic announcement permissions and loads the announcement
		  template.
		- requires the announce_topic permission.
		- uses the ManageMembers template and Post language file.
		- call the right function based on the sub-action.

	void AnnouncementSelectMembergroup()
		- lets the user select the membergroups that will receive the topic
		  announcement.

	void AnnouncementSend()
		- splits the members to be sent a topic announcement into chunks.
		- composes notification messages in all languages needed.
		- does the actual sending of the topic announcements in chunks.
		- calculates a rough estimate of the percentage items sent.

	void notifyMembersBoard(notifyData)
		- notifies members who have requested notification for new topics
		  posted on a board of said posts.
		- receives data on the topics to send out notifications to by the passed in array.
		- only sends notifications to those who can *currently* see the topic
		  (it doesn't matter if they could when they requested notification.)
		- loads the Post language file multiple times for each language if the
		  userLanguage setting is set.

	void getTopic()
		- gets a summary of the most recent posts in a topic.
		- depends on the topicSummaryPosts setting.
		- if you are editing a post, only shows posts previous to that post.

	void QuoteFast()
		- loads a post an inserts it into the current editing text box.
		- uses the Post language file.
		- uses special (sadly browser dependent) javascript to parse entities
		  for internationalization reasons.
		- accessed with ?action=quotefast.

	void JavaScriptModify()
		// !!!
*/

function Post()
{
	global $txt, $scripturl, $topic, $db_prefix, $modSettings, $board;
	global $user_info, $sc, $board_info, $context, $settings;
	global $sourcedir, $options, $smfFunc, $language;

	loadLanguage('Post');

	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	// You can't reply with a poll... hacker.
	if (isset($_REQUEST['poll']) && !empty($topic) && !isset($_REQUEST['msg']))
		unset($_REQUEST['poll']);

	// Posting an event?
	$context['make_event'] = isset($_REQUEST['calendar']);

	// You must be posting to *some* board.
	if (empty($board) && !$context['make_event'])
		fatal_lang_error('no_board', false);

	require_once($sourcedir . '/Subs-Post.php');

	if (isset($_REQUEST['xml']))
	{
		$context['sub_template'] = 'post';

		// Just in case of an earlier error...
		$context['preview_message'] = '';
		$context['preview_subject'] = '';
	}

	// Check if it's locked.  It isn't locked if no topic is specified.
	if (!empty($topic))
	{
		$request = $smfFunc['db_query']('', '
			SELECT
				t.locked, IFNULL(ln.id_topic, 0) AS notify, t.is_sticky, t.id_poll, t.num_replies, mf.id_member,
				t.id_first_msg, mf.subject,
				CASE WHEN ml.poster_time > ml.modified_time THEN ml.poster_time ELSE ml.modified_time END AS lastPostTime
			FROM {db_prefix}topics AS t
				LEFT JOIN {db_prefix}log_notify AS ln ON (ln.id_topic = t.id_topic AND ln.id_member = {int:current_member})
				LEFT JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				LEFT JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
			WHERE t.id_topic = {int:current_topic}
			LIMIT 1',
			array(
				'current_member' => $user_info['id'],
				'current_topic' => $topic,
			)
		);
		list ($locked, $context['notify'], $sticky, $pollID, $context['num_replies'], $ID_MEMBER_POSTER, $id_first_msg, $first_subject, $lastPostTime) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		// If this topic already has a poll, they sure can't add another.
		if (isset($_REQUEST['poll']) && $pollID > 0)
			unset($_REQUEST['poll']);

		if (empty($_REQUEST['msg']))
		{
			if ($user_info['is_guest'] && !allowedTo('post_reply_any') && !allowedTo('post_unapproved_replies_any'))
				is_not_guest();

			// By default the reply will be approved...
			$context['becomes_approved'] = true;
			if ($ID_MEMBER_POSTER != $user_info['id'])
			{
				if (allowedTo('post_unapproved_replies_any') && !allowedTo('post_reply_any'))
					$context['becomes_approved'] = false;
				else
					isAllowedTo('post_reply_any');
			}
			elseif (!allowedTo('post_reply_any'))
			{
				if (allowedTo('post_unapproved_replies_own') && !allowedTo('post_reply_own'))
					$context['becomes_approved'] = false;
				else
					isAllowedTo('post_reply_own');
			}
		}
		else
		{
			$context['becomes_approved'] = true;
		}

		$context['can_lock'] = allowedTo('lock_any') || ($user_info['id'] == $ID_MEMBER_POSTER && allowedTo('lock_own'));
		$context['can_sticky'] = allowedTo('make_sticky') && !empty($modSettings['enableStickyTopics']);

		$context['notify'] = !empty($context['notify']);
		$context['sticky'] = isset($_REQUEST['sticky']) ? !empty($_REQUEST['sticky']) : $sticky;
	}
	else
	{
		$context['becomes_approved'] = true;
		if ((!$context['make_event'] || !empty($board)) && (!isset($_REQUEST['poll']) || $modSettings['pollMode'] != '1'))
		{
			if (!allowedTo('post_new') && allowedTo('post_unapproved_topics'))
				$context['becomes_approved'] = false;
			else
				isAllowedTo('post_new');
		}

		$locked = 0;
		// !!! These won't work if you're making an event.
		$context['can_lock'] = allowedTo(array('lock_any', 'lock_own'));
		$context['can_sticky'] = allowedTo('make_sticky') && !empty($modSettings['enableStickyTopics']);

		$context['notify'] = !empty($context['notify']);
		$context['sticky'] = !empty($_REQUEST['sticky']);
	}

	// !!! These won't work if you're posting an event!
	$context['can_notify'] = allowedTo('mark_any_notify');
	$context['can_move'] = allowedTo('move_any');
	// You can only annouce topics that will get approved...
	$context['can_announce'] = allowedTo('announce_topic') && $context['becomes_approved'];
	$context['locked'] = !empty($locked) || !empty($_REQUEST['lock']);
	// Generally don't show the approval box... (Assume we want things approved)
	$context['show_approval'] = false;

	// An array to hold all the attachments for this topic.
	$context['current_attachments'] = array();

	// Don't allow a post if it's locked and you aren't all powerful.
	if ($locked && !allowedTo('moderate_board'))
		fatal_lang_error('topic_locked', false);

	// Check the users permissions - is the user allowed to add or post a poll?
	if (isset($_REQUEST['poll']) && $modSettings['pollMode'] == '1')
	{
		// New topic, new poll.
		if (empty($topic))
			isAllowedTo('poll_post');
		// This is an old topic - but it is yours!  Can you add to it?
		elseif ($user_info['id'] == $ID_MEMBER_POSTER && !allowedTo('poll_add_any'))
			isAllowedTo('poll_add_own');
		// If you're not the owner, can you add to any poll?
		else
			isAllowedTo('poll_add_any');

		require_once($sourcedir . '/Subs-Members.php');
		$allowedVoteGroups = groupsAllowedTo('poll_vote', $board);

		// Set up the poll options.
		$context['poll_options'] = array(
			'max_votes' => empty($_POST['poll_max_votes']) ? '1' : max(1, $_POST['poll_max_votes']),
			'hide' => empty($_POST['poll_hide']) ? 0 : $_POST['poll_hide'],
			'expire' => !isset($_POST['poll_expire']) ? '' : $_POST['poll_expire'],
			'change_vote' => isset($_POST['poll_change_vote']),
			'guest_vote' => isset($_POST['poll_guest_vote']),
			'guest_vote_enabled' => in_array(-1, $allowedVoteGroups['allowed']),
		);

		// Make all five poll choices empty.
		$context['choices'] = array(
			array('id' => 0, 'number' => 1, 'label' => '', 'is_last' => false),
			array('id' => 1, 'number' => 2, 'label' => '', 'is_last' => false),
			array('id' => 2, 'number' => 3, 'label' => '', 'is_last' => false),
			array('id' => 3, 'number' => 4, 'label' => '', 'is_last' => false),
			array('id' => 4, 'number' => 5, 'label' => '', 'is_last' => true)
		);
	}

	if ($context['make_event'])
	{
		// They might want to pick a board.
		if (!isset($context['current_board']))
			$context['current_board'] = 0;

		// Start loading up the event info.
		$context['event'] = array();
		$context['event']['title'] = isset($_REQUEST['evtitle']) ? htmlspecialchars(stripslashes($_REQUEST['evtitle'])) : '';

		$context['event']['id'] = isset($_REQUEST['eventid']) ? (int) $_REQUEST['eventid'] : -1;
		$context['event']['new'] = $context['event']['id'] == -1;

		// Permissions check!
		isAllowedTo('calendar_post');

		// Editing an event?  (but NOT previewing!?)
		if (!$context['event']['new'] && !isset($_REQUEST['subject']))
		{
			// If the user doesn't have permission to edit the post in this topic, redirect them.
			if (($ID_MEMBER_POSTER != $user_info['id'] || !allowedTo('modify_own')) && !allowedTo('modify_any'))
			{
				require_once($sourcedir . '/Calendar.php');
				return CalendarPost();
			}

			// Get the current event information.
			$request = $smfFunc['db_query']('', '
				SELECT
					id_member, title, MONTH(start_date) AS month, DAYOFMONTH(start_date) AS day,
					YEAR(start_date) AS year, (TO_DAYS(end_date) - TO_DAYS(start_date)) AS span
				FROM {db_prefix}calendar
				WHERE id_event = {int:inject_int_1}
				LIMIT 1',
				array(
					'inject_int_1' => $context['event']['id'],
				)
			);
			$row = $smfFunc['db_fetch_assoc']($request);
			$smfFunc['db_free_result']($request);

			// Make sure the user is allowed to edit this event.
			if ($row['id_member'] != $user_info['id'])
				isAllowedTo('calendar_edit_any');
			elseif (!allowedTo('calendar_edit_any'))
				isAllowedTo('calendar_edit_own');

			$context['event']['month'] = $row['month'];
			$context['event']['day'] = $row['day'];
			$context['event']['year'] = $row['year'];
			$context['event']['title'] = $row['title'];
			$context['event']['span'] = $row['span'] + 1;
		}
		else
		{
			$today = getdate();

			// You must have a month and year specified!
			if (!isset($_REQUEST['month']))
				$_REQUEST['month'] = $today['mon'];
			if (!isset($_REQUEST['year']))
				$_REQUEST['year'] = $today['year'];

			$context['event']['month'] = (int) $_REQUEST['month'];
			$context['event']['year'] = (int) $_REQUEST['year'];
			$context['event']['day'] = isset($_REQUEST['day']) ? $_REQUEST['day'] : ($_REQUEST['month'] == $today['mon'] ? $today['mday'] : 0);
			$context['event']['span'] = isset($_REQUEST['span']) ? $_REQUEST['span'] : 1;

			// Make sure the year and month are in the valid range.
			if ($context['event']['month'] < 1 || $context['event']['month'] > 12)
				fatal_lang_error('invalid_month', false);
			if ($context['event']['year'] < $modSettings['cal_minyear'] || $context['event']['year'] > $modSettings['cal_maxyear'])
				fatal_lang_error('invalid_year', false);

			// Get a list of boards they can post in.
			$boards = boardsAllowedTo('post_new');
			if (empty($boards))
				fatal_lang_error('cannot_post_new', 'user');

			// Load a list of boards for this event in the context.
			require_once($sourcedir . '/Subs-MessageIndex.php');
			$boardListOptions = array(
				'included_boards' => in_array(0, $boards) ? null : $boards,
				'not_redirection' => true,
				'use_permissions' => true,
				'selected_board' => empty($context['current_board']) ? $modSettings['cal_defaultboard'] : $context['current_board'],
			);
			$context['event']['categories'] = getBoardList($boardListOptions);
		}

		// Find the last day of the month.
		$context['event']['last_day'] = (int) strftime('%d', mktime(0, 0, 0, $context['event']['month'] == 12 ? 1 : $context['event']['month'] + 1, 0, $context['event']['month'] == 12 ? $context['event']['year'] + 1 : $context['event']['year']));

		$context['event']['board'] = !empty($board) ? $board : $modSettings['cal_defaultboard'];
	}

	if (empty($context['post_errors']))
		$context['post_errors'] = array();

	// See if any new replies have come along.
	if (empty($_REQUEST['msg']) && !empty($topic))
	{
		if (empty($options['no_new_reply_warning']) && isset($_REQUEST['num_replies']))
		{
			$newReplies = $context['num_replies'] > $_REQUEST['num_replies'] ? $context['num_replies'] - $_REQUEST['num_replies'] : 0;

			if (!empty($newReplies))
			{
				if ($newReplies == 1)
					$txt['error_new_reply'] = isset($_GET['num_replies']) ? $txt['error_new_reply_reading'] : $txt['error_new_reply'];
				else
					$txt['error_new_replies'] = sprintf(isset($_GET['num_replies']) ? $txt['error_new_replies_reading'] : $txt['error_new_replies'], $newReplies);

				// If they've come from the display page then we treat the error differently....
				if (isset($_GET['num_replies']))
					$newRepliesError = $newReplies;
				else
					$context['post_error'][$newReplies == 1 ? 'new_reply' : 'new_replies'] = true;

				$modSettings['topicSummaryPosts'] = $newReplies > $modSettings['topicSummaryPosts'] ? max($modSettings['topicSummaryPosts'], 5) : $modSettings['topicSummaryPosts'];
			}
		}
		// Check whether this is a really old post being bumped...
		if (!empty($modSettings['oldTopicDays']) && $lastPostTime + $modSettings['oldTopicDays'] * 86400 < time() && empty($sticky) && !isset($_REQUEST['subject']))
			$oldTopicError = true;
	}

	// Get a response prefix (like 'Re:') in the default forum language.
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

	// Previewing, modifying, or posting?
	if (isset($_REQUEST['message']) || !empty($context['post_error']))
	{
		// Validate inputs.
		if (empty($context['post_error']))
		{
			if (htmltrim__recursive(htmlspecialchars__recursive($_REQUEST['subject'])) == '')
				$context['post_error']['no_subject'] = true;
			if (htmltrim__recursive(htmlspecialchars__recursive($_REQUEST['message'])) == '')
				$context['post_error']['no_message'] = true;
			if (!empty($modSettings['max_messageLength']) && strlen($_REQUEST['message']) > $modSettings['max_messageLength'])
				$context['post_error']['long_message'] = true;

			// Are you... a guest?
			if ($user_info['is_guest'])
			{
				$_REQUEST['guestname'] = !isset($_REQUEST['guestname']) ? '' : trim($_REQUEST['guestname']);
				$_REQUEST['email'] = !isset($_REQUEST['email']) ? '' : trim($_REQUEST['email']);

				// Validate the name and email.
				if (!isset($_REQUEST['guestname']) || trim(strtr($_REQUEST['guestname'], '_', ' ')) == '')
					$context['post_error']['no_name'] = true;
				elseif ($smfFunc['strlen']($_REQUEST['guestname']) > 25)
					$context['post_error']['long_name'] = true;
				else
				{
					require_once($sourcedir . '/Subs-Members.php');
					if (isReservedName(htmlspecialchars($_REQUEST['guestname']), 0, true, false))
						$context['post_error']['bad_name'] = true;
				}

				if (empty($modSettings['guest_post_no_email']))
				{
					if (!isset($_REQUEST['email']) || $_REQUEST['email'] == '')
						$context['post_error']['no_email'] = true;
					elseif (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $smfFunc['db_unescape_string']($_REQUEST['email'])) == 0)
						$context['post_error']['bad_email'] = true;
				}
			}

			// This is self explanatory - got any questions?
			if (isset($_REQUEST['question']) && trim($_REQUEST['question']) == '')
				$context['post_error']['no_question'] = true;

			// This means they didn't click Post and get an error.
			$really_previewing = true;
		}
		else
		{
			if (!isset($_REQUEST['subject']))
				$_REQUEST['subject'] = '';
			if (!isset($_REQUEST['message']))
				$_REQUEST['message'] = '';
			if (!isset($_REQUEST['icon']))
				$_REQUEST['icon'] = 'xx';

			// They are previewing if they asked to preview (i.e. came from quick reply).
			$really_previewing = !empty($_POST['preview']);
		}

		// In order to keep the approval status flowing through, we have to pass it through the form...
		$context['becomes_approved'] = empty($_REQUEST['not_approved']);
		$context['show_approval'] = isset($_REQUEST['approve']) ? ($_REQUEST['approve'] ? 2 : 1) : 0;
		$context['can_announce'] &= $context['becomes_approved'];

		// Set up the inputs for the form.
		$form_subject = strtr($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_REQUEST['subject'])), array("\r" => '', "\n" => '', "\t" => ''));
		$form_message = $smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_REQUEST['message']), ENT_QUOTES);

		// Make sure the subject isn't too long - taking into account special characters.
		if ($smfFunc['strlen']($form_subject) > 100)
			$form_subject = $smfFunc['substr']($form_subject, 0, 100);

		// Have we inadvertently trimmed off the subject of useful information?
		if ($smfFunc['htmltrim']($form_subject) === '')
			$context['post_error']['no_subject'] = true;

		// Any errors occurred?
		if (!empty($context['post_error']))
		{
			loadLanguage('Errors');

			$context['error_type'] = 'minor';

			$context['post_error']['messages'] = array();
			foreach ($context['post_error'] as $post_error => $dummy)
			{
				if ($post_error == 'messages')
					continue;

				if ($post_error == 'long_message')
					$txt['error_' . $post_error] = sprintf($txt['error_' . $post_error], $modSettings['max_messageLength']);

				$context['post_error']['messages'][] = $txt['error_' . $post_error];

				// If it's not a minor error flag it as such.
				if (!in_array($post_error, array('new_reply', 'not_approved', 'new_replies', 'old_topic')))
					$context['error_type'] = 'serious';
			}
		}

		if (isset($_REQUEST['poll']))
		{
			$context['question'] = isset($_REQUEST['question']) ? $smfFunc['htmlspecialchars']($smfFunc['db_unescape_string'](trim($_REQUEST['question']))) : '';

			$context['choices'] = array();
			$choice_id = 0;

			$_POST['options'] = empty($_POST['options']) ? array() : htmlspecialchars__recursive(unescapestring__recursive($_POST['options']));
			foreach ($_POST['options'] as $option)
			{
				if (trim($option) == '')
					continue;

				$context['choices'][] = array(
					'id' => $choice_id++,
					'number' => $choice_id,
					'label' => $option,
					'is_last' => false
				);
			}

			if (count($context['choices']) < 2)
			{
				$context['choices'][] = array(
					'id' => $choice_id++,
					'number' => $choice_id,
					'label' => '',
					'is_last' => false
				);
				$context['choices'][] = array(
					'id' => $choice_id++,
					'number' => $choice_id,
					'label' => '',
					'is_last' => false
				);
			}
			$context['choices'][count($context['choices']) - 1]['is_last'] = true;
		}

		// Are you... a guest?
		if ($user_info['is_guest'])
		{
			$_REQUEST['guestname'] = !isset($_REQUEST['guestname']) ? '' : trim($_REQUEST['guestname']);
			$_REQUEST['email'] = !isset($_REQUEST['email']) ? '' : trim($_REQUEST['email']);

			$_REQUEST['guestname'] = htmlspecialchars($_REQUEST['guestname']);
			$context['name'] = $_REQUEST['guestname'];
			$_REQUEST['email'] = htmlspecialchars($_REQUEST['email']);
			$context['email'] = $_REQUEST['email'];

			$user_info['name'] = $_REQUEST['guestname'];
		}

		// Only show the preview stuff if they hit Preview.
		if ($really_previewing == true || isset($_REQUEST['xml']))
		{
			// Set up the preview message and subject and censor them...
			$context['preview_message'] = $form_message;
			preparsecode($form_message, true);
			preparsecode($context['preview_message']);

			// Do all bulletin board code tags, with or without smileys.
			$context['preview_message'] = parse_bbc($context['preview_message'], isset($_REQUEST['ns']) ? 0 : 1);

			if ($form_subject != '')
			{
				$context['preview_subject'] = $form_subject;

				censorText($context['preview_subject']);
				censorText($context['preview_message']);
			}
			else
				$context['preview_subject'] = '<i>' . $txt['no_subject'] . '</i>';

			// Protect any CDATA blocks.
			if (isset($_REQUEST['xml']))
				$context['preview_message'] = strtr($context['preview_message'], array(']]>' => ']]]]><![CDATA[>'));
		}

		// Set up the checkboxes.
		$context['notify'] = !empty($_REQUEST['notify']);
		$context['use_smileys'] = !isset($_REQUEST['ns']);

		$context['icon'] = isset($_REQUEST['icon']) ? preg_replace('~[\./\\\\*\':"<>]~', '', $_REQUEST['icon']) : 'xx';

		// Set the destination action for submission.
		$context['destination'] = 'post2;start=' . $_REQUEST['start'] . (isset($_REQUEST['msg']) ? ';msg=' . $_REQUEST['msg'] . ';sesc=' . $sc : '') . (isset($_REQUEST['poll']) ? ';poll' : '');
		$context['submit_label'] = isset($_REQUEST['msg']) ? $txt['save'] : $txt['post'];

		// Previewing an edit?
		if (isset($_REQUEST['msg']))
		{
			if (!empty($modSettings['attachmentEnable']))
			{
				$request = $smfFunc['db_query']('', '
					SELECT IFNULL(size, -1) AS filesize, filename, id_attach, approved
					FROM {db_prefix}attachments
					WHERE id_msg = {int:inject_int_1}
						AND attachment_type = {int:inject_int_2}',
					array(
						'inject_int_1' => (int) $_REQUEST['msg'],
						'inject_int_2' => 0,
					)
				);
				while ($row = $smfFunc['db_fetch_assoc']($request))
				{
					if ($row['filesize'] <= 0)
						continue;
					$context['current_attachments'][] = array(
						'name' => $row['filename'],
						'id' => $row['id_attach'],
						'approved' => $row['approved'],
					);
				}
				$smfFunc['db_free_result']($request);
			}

			// Allow moderators to change names....
			if (allowedTo('moderate_forum') && !empty($topic))
			{
				$request = $smfFunc['db_query']('', '
					SELECT id_member, poster_name, poster_email
					FROM {db_prefix}messages
					WHERE id_msg = {int:inject_int_1}
						AND id_topic = {int:current_topic}
					LIMIT 1',
					array(
						'current_topic' => $topic,
						'inject_int_1' => (int) $_REQUEST['msg'],
					)
				);
				$row = $smfFunc['db_fetch_assoc']($request);
				$smfFunc['db_free_result']($request);

				if (empty($row['id_member']))
				{
					$context['name'] = htmlspecialchars($row['poster_name']);
					$context['email'] = htmlspecialchars($row['poster_email']);
				}
			}
		}

		// No check is needed, since nothing is really posted.
		checkSubmitOnce('free');
	}
	// Editing a message...
	elseif (isset($_REQUEST['msg']))
	{
		checkSession('get');
		$_REQUEST['msg'] = (int) $_REQUEST['msg'];

		// Get the existing message.
		$request = $smfFunc['db_query']('', '
			SELECT
				m.id_member, m.modified_time, m.smileys_enabled, m.body,
				m.poster_name, m.poster_email, m.subject, m.icon, m.approved,
				IFNULL(a.size, -1) AS filesize, a.filename, a.id_attach,
				a.approved AS attachment_approved, t.id_member_started AS id_member_poster,
				m.poster_time
			FROM {db_prefix}messages AS m
				INNER JOIN {db_prefix}topics AS t ON (t.id_topic = {int:current_topic})
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_msg = m.id_msg AND a.attachment_type = {int:inject_int_1})
			WHERE m.id_msg = {int:inject_int_2}
				AND m.id_topic = {int:current_topic}',
			array(
				'current_topic' => $topic,
				'inject_int_1' => 0,
				'inject_int_2' => $_REQUEST['msg'],
			)
		);
		// The message they were trying to edit was most likely deleted.
		// !!! Change this error message?
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('no_board', false);
		$row = $smfFunc['db_fetch_assoc']($request);

		$attachment_stuff = array($row);
		while ($row2 = $smfFunc['db_fetch_assoc']($request))
			$attachment_stuff[] = $row2;
		$smfFunc['db_free_result']($request);

		if ($row['id_member'] == $user_info['id'] && !allowedTo('modify_any'))
		{
			// Give an extra five minutes over the disable time threshold, so they can type - assuming the post is public.
			if ($row['approved'] && !empty($modSettings['edit_disable_time']) && $row['poster_time'] + ($modSettings['edit_disable_time'] + 5) * 60 < time())
				fatal_lang_error('modify_post_time_passed', false);
			elseif ($row['id_member_poster'] == $user_info['id'] && !allowedTo('modify_own'))
				isAllowedTo('modify_replies');
			else
				isAllowedTo('modify_own');
		}
		elseif ($row['id_member_poster'] == $user_info['id'] && !allowedTo('modify_any'))
			isAllowedTo('modify_replies');
		else
			isAllowedTo('modify_any');

		// When was it last modified?
		if (!empty($row['modified_time']))
			$context['last_modified'] = timeformat($row['modified_time']);

		// Get the stuff ready for the form.
		$form_subject = $row['subject'];
		$form_message = un_preparsecode($row['body']);
		censorText($form_message);
		censorText($form_subject);

		// Check the boxes that should be checked.
		$context['use_smileys'] = !empty($row['smileys_enabled']);
		$context['icon'] = $row['icon'];

		// Show an "approve" box if the user can approve it, and the message isn't approved.
		if (!$row['approved'])
			$context['show_approval'] = allowedTo('approve_posts');

		// Load up 'em attachments!
		foreach ($attachment_stuff as $attachment)
		{
			if ($attachment['filesize'] >= 0 && !empty($modSettings['attachmentEnable']))
				$context['current_attachments'][] = array(
					'name' => $attachment['filename'],
					'id' => $attachment['id_attach'],
					'approved' => $attachment['attachment_approved'],
				);
		}

		// Allow moderators to change names....
		if (allowedTo('moderate_forum') && empty($row['id_member']))
		{
			$context['name'] = htmlspecialchars($row['poster_name']);
			$context['email'] = htmlspecialchars($row['poster_email']);
		}

		// Set the destinaton.
		$context['destination'] = 'post2;start=' . $_REQUEST['start'] . ';msg=' . $_REQUEST['msg'] . ';sesc=' . $sc . (isset($_REQUEST['poll']) ? ';poll' : '');
		$context['submit_label'] = $txt['save'];
	}
	// Posting...
	else
	{
		// By default....
		$context['use_smileys'] = true;
		$context['icon'] = 'xx';

		if ($user_info['is_guest'])
		{
			$context['name'] = isset($_SESSION['guest_name']) ? $_SESSION['guest_name'] : '';
			$context['email'] =isset($_SESSION['guest_email']) ? $_SESSION['guest_email'] :  '';
		}
		$context['destination'] = 'post2;start=' . $_REQUEST['start'] . (isset($_REQUEST['poll']) ? ';poll' : '');

		$context['submit_label'] = $txt['post'];

		// Posting a quoted reply?
		if (!empty($topic) && !empty($_REQUEST['quote']))
		{
			checkSession('get');

			// Make sure they _can_ quote this post, and if so get it.
			$request = $smfFunc['db_query']('', '
				SELECT m.subject, IFNULL(mem.real_name, m.poster_name) AS poster_name, m.poster_time, m.body
				FROM {db_prefix}messages AS m
					INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board AND ' . $user_info['query_see_board'] . ')
					LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
				WHERE m.id_msg = {int:inject_int_1}
					' . (allowedTo('approve_posts') ? '' : ' AND m.approved = {int:inject_int_2}') . '
				LIMIT 1',
				array(
					'inject_int_1' => (int) $_REQUEST['quote'],
					'inject_int_2' => 1,
				)
			);
			if ($smfFunc['db_num_rows']($request) == 0)
				fatal_lang_error('quoted_post_deleted', false);
			list ($form_subject, $mname, $mdate, $form_message) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			// Add 'Re: ' to the front of the quoted subject.
			if (trim($context['response_prefix']) != '' && $smfFunc['strpos']($form_subject, trim($context['response_prefix'])) !== 0)
				$form_subject = $context['response_prefix'] . $form_subject;

			// Censor the message and subject.
			censorText($form_message);
			censorText($form_subject);

			$form_message = preg_replace('~<br(?: /)?' . '>~i', "\n", $form_message);

			// Remove any nested quotes, if necessary.
			if (!empty($modSettings['removeNestedQuotes']))
				$form_message = preg_replace(array('~\n?\[quote.*?\].+?\[/quote\]\n?~is', '~^\n~', '~\[/quote\]~'), '', $form_message);

			// Add a quote string on the front and end.
			$form_message = '[quote author=' . $mname . ' link=topic=' . $topic . '.msg' . (int) $_REQUEST['quote'] . '#msg' . (int) $_REQUEST['quote'] . ' date=' . $mdate . ']' . "\n" . $form_message . "\n" . '[/quote]';
		}
		// Posting a reply without a quote?
		elseif (!empty($topic) && empty($_REQUEST['quote']))
		{
			// Get the first message's subject.
			$form_subject = $first_subject;

			// Add 'Re: ' to the front of the subject.
			if (trim($context['response_prefix']) != '' && $form_subject != '' && $smfFunc['strpos']($form_subject, trim($context['response_prefix'])) !== 0)
				$form_subject = $context['response_prefix'] . $form_subject;

			// Censor the subject.
			censorText($form_subject);

			$form_message = '';
		}
		else
		{
			$form_subject = isset($_GET['subject']) ? $_GET['subject'] : '';
			$form_message = '';
		}
	}

	// !!! This won't work if you're posting an event.
	if (allowedTo('post_attachment') || allowedTo('post_unapproved_attachments'))
	{
		if (empty($_SESSION['temp_attachments']))
			$_SESSION['temp_attachments'] = array();

		if (!empty($modSettings['currentAttachmentUploadDir']))
		{
			if (!is_array($modSettings['attachmentUploadDir']))
				$modSettings['attachmentUploadDir'] = unserialize($modSettings['attachmentUploadDir']);

			// Just use the current path for temp files.
			$current_attach_dir = $modSettings['attachmentUploadDir'][$modSettings['currentAttachmentUploadDir']];
		}
		else
			$current_attach_dir = $modSettings['attachmentUploadDir'];

		// If this isn't a new post, check the current attachments.
		if (isset($_REQUEST['msg']))
		{
			$request = $smfFunc['db_query']('', '
				SELECT COUNT(*), SUM(size)
				FROM {db_prefix}attachments
				WHERE id_msg = {int:inject_int_1}
					AND attachment_type = {int:inject_int_2}',
				array(
					'inject_int_1' => (int) $_REQUEST['msg'],
					'inject_int_2' => 0,
				)
			);
			list ($quantity, $total_size) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);
		}
		else
		{
			$quantity = 0;
			$total_size = 0;
		}

		$temp_start = 0;

		if (!empty($_SESSION['temp_attachments']))
			foreach ($_SESSION['temp_attachments'] as $attachID => $name)
			{
				$temp_start++;

				if (preg_match('~^post_tmp_' . $user_info['id'] . '_\d+$~', $attachID) == 0)
				{
					unset($_SESSION['temp_attachments'][$attachID]);
					continue;
				}

				if (!empty($_POST['attach_del']) && !in_array($attachID, $_POST['attach_del']))
				{
					$deleted_attachments = true;
					unset($_SESSION['temp_attachments'][$attachID]);
					@unlink($current_attach_dir . '/' . $attachID);
					continue;
				}

				$quantity++;
				$total_size += filesize($current_attach_dir . '/' . $attachID);

				$context['current_attachments'][] = array(
					'name' => getAttachmentFilename($name, false, null, true),
					'id' => $attachID,
					'approved' => 1,
				);
			}

		if (!empty($_POST['attach_del']))
		{
			$del_temp = array();
			foreach ($_POST['attach_del'] as $i => $dummy)
				$del_temp[$i] = (int) $dummy;

			foreach ($context['current_attachments'] as $k => $dummy)
				if (!in_array($dummy['id'], $del_temp))
				{
					$context['current_attachments'][$k]['unchecked'] = true;
					$deleted_attachments = !isset($deleted_attachments) || is_bool($deleted_attachments) ? 1 : $deleted_attachments + 1;
					$quantity--;
				}
		}

		if (!empty($_FILES['attachment']))
			foreach ($_FILES['attachment']['tmp_name'] as $n => $dummy)
			{
				if ($_FILES['attachment']['name'][$n] == '')
					continue;

				if (!is_uploaded_file($_FILES['attachment']['tmp_name'][$n]) || (@ini_get('open_basedir') == '' && !file_exists($_FILES['attachment']['tmp_name'][$n])))
					fatal_lang_error('attach_timeout', 'critical');

				if (!empty($modSettings['attachmentSizeLimit']) && $_FILES['attachment']['size'][$n] > $modSettings['attachmentSizeLimit'] * 1024)
					fatal_lang_error('file_too_big', false, array($modSettings['attachmentSizeLimit']));

				$quantity++;
				if (!empty($modSettings['attachmentNumPerPostLimit']) && $quantity > $modSettings['attachmentNumPerPostLimit'])
					fatal_lang_error('attachments_limit_per_post', false, array($modSettings['attachmentNumPerPostLimit']));

				$total_size += $_FILES['attachment']['size'][$n];
				if (!empty($modSettings['attachmentPostLimit']) && $total_size > $modSettings['attachmentPostLimit'] * 1024)
					fatal_lang_error('file_too_big', false, array($modSettings['attachmentPostLimit']));

				if (!empty($modSettings['attachmentCheckExtensions']))
				{
					if (!in_array(strtolower(substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1)), explode(',', strtolower($modSettings['attachmentExtensions']))))
						fatal_error($_FILES['attachment']['name'][$n] . '.<br />' . $txt['cant_upload_type'] . ' ' . $modSettings['attachmentExtensions'] . '.', false);
				}

				if (!empty($modSettings['attachmentDirSizeLimit']))
				{
					// Make sure the directory isn't full.
					$dirSize = 0;
					$dir = @opendir($current_attach_dir) or fatal_lang_error('cant_access_upload_path', 'critical');
					while ($file = readdir($dir))
					{
						if (substr($file, 0, -1) == '.')
							continue;

						if (preg_match('~^post_tmp_\d+_\d+$~', $file) != 0)
						{
							// Temp file is more than 5 hours old!
							if (filemtime($current_attach_dir . '/' . $file) < time() - 18000)
								@unlink($current_attach_dir . '/' . $file);
							continue;
						}

						$dirSize += filesize($current_attach_dir . '/' . $file);
					}
					closedir($dir);

					// Too big!  Maybe you could zip it or something...
					if ($_FILES['attachment']['size'][$n] + $dirSize > $modSettings['attachmentDirSizeLimit'] * 1024)
						fatal_lang_error('ran_out_of_space');
				}

				if (!is_writable($current_attach_dir))
					fatal_lang_error('attachments_no_write', 'critical');

				$attachID = 'post_tmp_' . $user_info['id'] . '_' . $temp_start++;
				$_SESSION['temp_attachments'][$attachID] = $smfFunc['db_unescape_string'](basename($_FILES['attachment']['name'][$n]));
				$context['current_attachments'][] = array(
					'name' => basename($smfFunc['db_unescape_string']($_FILES['attachment']['name'][$n])),
					'id' => $attachID,
					'approved' => 1,
				);

				$destName = $current_attach_dir . '/' . $attachID;

				if (!move_uploaded_file($_FILES['attachment']['tmp_name'][$n], $destName))
					fatal_lang_error('attach_timeout', 'critical');
				@chmod($destName, 0644);
			}
	}

	// If we are coming here to make a reply, and someone has already replied... make a special warning message.
	if (isset($newRepliesError))
	{
		$context['post_error']['messages'][] = $newRepliesError == 1 ? $txt['error_new_reply'] : $txt['error_new_replies'];
		$context['error_type'] = 'minor';
	}

	if (isset($oldTopicError))
	{
		$context['post_error']['messages'][] = sprintf($txt['error_old_topic'], $modSettings['oldTopicDays']);
		$context['error_type'] = 'minor';
	}

	// What are you doing?  Posting a poll, modifying, previewing, new post, or reply...
	if (isset($_REQUEST['poll']))
		$context['page_title'] = $txt['new_poll'];
	elseif ($context['make_event'])
		$context['page_title'] = $context['event']['id'] == -1 ? $txt['calendar_post_event'] : $txt['calendar_edit'];
	elseif (isset($_REQUEST['msg']))
		$context['page_title'] = $txt['modify_msg'];
	elseif (isset($_REQUEST['subject'], $context['preview_subject']))
		$context['page_title'] = $txt['preview'] . ' - ' . strip_tags($context['preview_subject']);
	elseif (empty($topic))
		$context['page_title'] = $txt['start_new_topic'];
	else
		$context['page_title'] = $txt['post_reply'];

	// Build the link tree.
	if (empty($topic))
		$context['linktree'][] = array(
			'name' => '<i>' . $txt['start_new_topic'] . '</i>'
		);
	else
		$context['linktree'][] = array(
			'url' => $scripturl . '?topic=' . $topic . '.' . $_REQUEST['start'],
			'name' => $form_subject,
			'extra_before' => '<span' . ($settings['linktree_inline'] ? ' class="smalltext"' : '') . '><b class="nav">' . $context['page_title'] . ' ( </b></span>',
			'extra_after' => '<span' . ($settings['linktree_inline'] ? ' class="smalltext"' : '') . '><b class="nav"> )</b></span>'
		);

	// If they've unchecked an attachment, they may still want to attach that many more files, but don't allow more than num_allowed_attachments.
	// !!! This won't work if you're posting an event.
	$context['num_allowed_attachments'] = min($modSettings['attachmentNumPerPostLimit'] - count($context['current_attachments']) + (isset($deleted_attachments) ? $deleted_attachments : 0), $modSettings['attachmentNumPerPostLimit']);
	$context['can_post_attachment'] = !empty($modSettings['attachmentEnable']) && $modSettings['attachmentEnable'] == 1 && (allowedTo('post_attachment') || allowedTo('post_unapproved_attachments')) && $context['num_allowed_attachments'] > 0;
	$context['can_post_attachment_unapproved'] = allowedTo('post_attachment');

	$context['subject'] = addcslashes($form_subject, '"');
	$context['message'] = str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), $form_message);

	// Needed for the editor and message icons.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'message',
		'value' => $context['message'],
	);
	create_control_richedit($editorOptions);

	// Store the ID.
	$context['post_box_name'] = $editorOptions['id'];

	$context['attached'] = '';
	$context['allowed_extensions'] = strtr($modSettings['attachmentExtensions'], array(',' => ', '));
	$context['make_poll'] = isset($_REQUEST['poll']);

	// Message icons - customized icons are off?
	$context['icons'] = getMessageIcons($board);

	if (!empty($context['icons']))
		$context['icons'][count($context['icons']) - 1]['is_last'] = true;

	$context['icon_url'] = '';
	for ($i = 0, $n = count($context['icons']); $i < $n; $i++)
	{
		$context['icons'][$i]['selected'] = $context['icon'] == $context['icons'][$i]['value'];
		if ($context['icons'][$i]['selected'])
			$context['icon_url'] = $context['icons'][$i]['url'];
	}
	if (empty($context['icon_url']))
	{
		$context['icon_url'] = $settings[file_exists($settings['theme_dir'] . '/images/post/' . $context['icon'] . '.gif') ? 'images_url' : 'default_images_url'] . '/post/' . $context['icon'] . '.gif';
		array_unshift($context['icons'], array(
			'value' => $context['icon'],
			'name' => $txt['current_icon'],
			'url' => $context['icon_url'],
			'is_last' => empty($context['icons']),
			'selected' => true,
		));
	}

	if (isset($topic) && !empty($modSettings['topicSummaryPosts']))
		getTopic();

	$context['back_to_topic'] = isset($_REQUEST['goback']) || (isset($_REQUEST['msg']) && !isset($_REQUEST['subject']));
	$context['show_additional_options'] = !empty($_POST['additional_options']) || !empty($_SESSION['temp_attachments']) || !empty($deleted_attachments);

	$context['is_new_topic'] = empty($topic);
	$context['is_new_post'] = !isset($_REQUEST['msg']);
	$context['is_first_post'] = $context['is_new_topic'] || (isset($_REQUEST['msg']) && $_REQUEST['msg'] == $id_first_msg);

	// Do we need to show the visual verification image?
	$context['visual_verification'] = !$user_info['is_mod'] && !$user_info['is_admin'] && !empty($modSettings['posts_require_captcha']) && $user_info['posts'] < $modSettings['posts_require_captcha'];
	if ($context['visual_verification'])
	{
		$context['use_graphic_library'] = in_array('gd', get_loaded_extensions());
		$context['verification_image_href'] = $scripturl . '?action=verificationcode;rand=' . md5(rand());

		// Skip I, J, L, O, Q, S and Z.
		$character_range = array_merge(range('A', 'H'), array('K', 'M', 'N', 'P', 'R'), range('T', 'Y'));

		// Generate a new code.
		$_SESSION['visual_verification_code'] = '';
		for ($i = 0; $i < 5; $i++)
			$_SESSION['visual_verification_code'] .= $character_range[array_rand($character_range)];
	}

	// Register this form in the session variables.
	checkSubmitOnce('register');

	// Finally, load the template.
	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_post';
	elseif (!isset($_REQUEST['xml']))
		loadTemplate('Post');
}

function Post2()
{
	global $board, $topic, $txt, $db_prefix, $modSettings, $sourcedir, $context;
	global $user_info, $board_info, $options, $smfFunc;

	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['message_mode']) && isset($_REQUEST['message']))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		// We strip and add slashes back here - so we don't forget!
		$_REQUEST['message'] = $smfFunc['db_unescape_string']($_REQUEST['message']);
		$_REQUEST['message'] = html_to_bbc($_REQUEST['message']);
		$_REQUEST['message'] = $smfFunc['db_escape_string']($_REQUEST['message']);

		// We need to unhtml it now as it gets done shortly.
		$_REQUEST['message'] = un_htmlspecialchars($_REQUEST['message']);

		// We need this for everything else.
		$_POST['message'] = $_REQUEST['message'];
	}

	// Previewing? Go back to start.
	if (isset($_REQUEST['preview']))
		return Post();

	// Prevent double submission of this form.
	checkSubmitOnce('check');

	// No errors as yet.
	$post_errors = array();

	// If the session has timed out, let the user re-submit their form.
	if (checkSession('post', '', false) != '')
		$post_errors[] = 'session_timeout';

	// Wrong verification code?
	if (!$user_info['is_admin'] && !$user_info['is_mod'] && !empty($modSettings['posts_require_captcha']) && $user_info['posts'] < $modSettings['posts_require_captcha'] && (empty($_REQUEST['visual_verification_code']) || strtoupper($_REQUEST['visual_verification_code']) !== $_SESSION['visual_verification_code']))
		$post_errors[] = 'wrong_verification_code';

	require_once($sourcedir . '/Subs-Post.php');
	loadLanguage('Post');

	// Replying to a topic?
	if (!empty($topic) && !isset($_REQUEST['msg']))
	{
		$request = $smfFunc['db_query']('', '
			SELECT t.locked, t.is_sticky, t.id_poll, t.num_replies, m.id_member
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE t.id_topic = {int:current_topic}
			LIMIT 1',
			array(
				'current_topic' => $topic,
			)
		);
		list ($tmplocked, $tmpstickied, $pollID, $num_replies, $ID_MEMBER_POSTER) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		// Don't allow a post if it's locked.
		if ($tmplocked != 0 && !allowedTo('moderate_board'))
			fatal_lang_error('topic_locked', false);

		// Sorry, multiple polls aren't allowed... yet.  You should stop giving me ideas :P.
		if (isset($_REQUEST['poll']) && $pollID > 0)
			unset($_REQUEST['poll']);

		// Do the permissions and approval stuff...
		$becomesApproved = true;
		if ($ID_MEMBER_POSTER != $user_info['id'])
		{
			if (allowedTo('post_unapproved_replies_any') && !allowedTo('post_reply_any'))
				$becomesApproved = false;
			else
				isAllowedTo('post_reply_any');
		}
		elseif (!allowedTo('post_reply_any'))
		{
			if (allowedTo('post_unapproved_replies_own') && !allowedTo('post_reply_own'))
				$becomesApproved = false;
			else
				isAllowedTo('post_reply_own');
		}

		if (isset($_POST['lock']))
		{
			// Nothing is changed to the lock.
			if ((empty($tmplocked) && empty($_POST['lock'])) || (!empty($_POST['lock']) && !empty($tmplocked)))
				unset($_POST['lock']);
			// You're have no permission to lock this topic.
			elseif (!allowedTo(array('lock_any', 'lock_own')) || (!allowedTo('lock_any') && $user_info['id'] != $ID_MEMBER_POSTER))
				unset($_POST['lock']);
			// You are allowed to (un)lock your own topic only.
			elseif (!allowedTo('lock_any'))
			{
				// You cannot override a moderator lock.
				if ($tmplocked == 1)
					unset($_POST['lock']);
				else
					$_POST['lock'] = empty($_POST['lock']) ? 0 : 2;
			}
			// Hail mighty moderator, (un)lock this topic immediately.
			else
				$_POST['lock'] = empty($_POST['lock']) ? 0 : 1;
		}

		// So you wanna (un)sticky this...let's see.
		if (isset($_POST['sticky']) && (empty($modSettings['enableStickyTopics']) || $_POST['sticky'] == $tmpstickied || !allowedTo('make_sticky')))
			unset($_POST['sticky']);

		// If the number of replies has changed, if the setting is enabled, go back to Post() - which handles the error.
		$newReplies = isset($_POST['num_replies']) && $num_replies > $_POST['num_replies'] ? $num_replies - $_POST['num_replies'] : 0;
		if (empty($options['no_new_reply_warning']) && !empty($newReplies))
		{
			$_REQUEST['preview'] = true;
			return Post();
		}

		$posterIsGuest = $user_info['is_guest'];
	}

	// Posting a new topic.
	elseif (empty($topic))
	{
		// Do like, the permissions, for safety and stuff...
		$becomesApproved = true;
		if (!isset($_REQUEST['poll']) || $modSettings['pollMode'] != '1')
		{
			if (!allowedTo('post_new') && allowedTo('post_unapproved_topics'))
				$becomesApproved = false;
			else
				isAllowedTo('post_new');
		}

		if (isset($_POST['lock']))
		{
			// New topics are by default not locked.
			if (empty($_POST['lock']))
				unset($_POST['lock']);
			// Besides, you need permission.
			elseif (!allowedTo(array('lock_any', 'lock_own')))
				unset($_POST['lock']);
			// A moderator-lock (1) can override a user-lock (2).
			else
				$_POST['lock'] = allowedTo('lock_any') ? 1 : 2;
		}

		if (isset($_POST['sticky']) && (empty($modSettings['enableStickyTopics']) || empty($_POST['sticky']) || !allowedTo('make_sticky')))
			unset($_POST['sticky']);

		$posterIsGuest = $user_info['is_guest'];
	}

	// Modifying an existing message?
	elseif (isset($_REQUEST['msg']) && !empty($topic))
	{
		$_REQUEST['msg'] = (int) $_REQUEST['msg'];

		$request = $smfFunc['db_query']('', '
			SELECT
				m.id_member, m.poster_name, m.poster_email, m.poster_time, m.approved,
				t.id_first_msg, t.locked, t.is_sticky, t.id_member_started AS id_member_poster
			FROM {db_prefix}messages AS m
				INNER JOIN {db_prefix}topics AS t ON (t.id_topic = {int:current_topic})
			WHERE m.id_msg = {int:inject_int_1}
			LIMIT 1',
			array(
				'current_topic' => $topic,
				'inject_int_1' => $_REQUEST['msg'],
			)
		);
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('cant_find_messages', false);
		$row = $smfFunc['db_fetch_assoc']($request);
		$smfFunc['db_free_result']($request);

		if (!empty($row['locked']) && !allowedTo('moderate_board'))
			fatal_lang_error('topic_locked', false);

		if (isset($_POST['lock']))
		{
			// Nothing changes to the lock status.
			if ((empty($_POST['lock']) && empty($row['locked'])) || (!empty($_POST['lock']) && !empty($row['locked'])))
				unset($_POST['lock']);
			// You're simply not allowed to (un)lock this.
			elseif (!allowedTo(array('lock_any', 'lock_own')) || (!allowedTo('lock_any') && $user_info['id'] != $row['id_member_poster']))
				unset($_POST['lock']);
			// You're only allowed to lock your own topics.
			elseif (!allowedTo('lock_any'))
			{
				// You're not allowed to break a moderator's lock.
				if ($row['locked'] == 1)
					unset($_POST['lock']);
				// Lock it with a soft lock or unlock it.
				else
					$_POST['lock'] = empty($_POST['lock']) ? 0 : 2;
			}
			// You must be the moderator.
			else
				$_POST['lock'] = empty($_POST['lock']) ? 0 : 1;
		}

		// Change the sticky status of this topic?
		if (isset($_POST['sticky']) && (!allowedTo('make_sticky') || $_POST['sticky'] == $row['is_sticky']))
			unset($_POST['sticky']);

		if ($row['id_member'] == $user_info['id'] && !allowedTo('modify_any'))
		{
			if ($row['approved'] && !empty($modSettings['edit_disable_time']) && $row['poster_time'] + ($modSettings['edit_disable_time'] + 5) * 60 < time())
				fatal_lang_error('modify_post_time_passed', false);
			elseif ($row['id_member_poster'] == $user_info['id'] && !allowedTo('modify_own'))
				isAllowedTo('modify_replies');
			else
				isAllowedTo('modify_own');
		}
		elseif ($row['id_member_poster'] == $user_info['id'] && !allowedTo('modify_any'))
		{
			isAllowedTo('modify_replies');

			// If you're modifying a reply, I say it better be logged...
			$moderationAction = true;
		}
		else
		{
			isAllowedTo('modify_any');

			// Log it, assuming you're not modifying your own post.
			if ($row['id_member'] != $user_info['id'])
				$moderationAction = true;
		}

		$posterIsGuest = empty($row['id_member']);

		// Can they approve it?
		$can_approve = allowedTo('approve_posts');
		$becomesApproved = $can_approve && !$row['approved'] ? (isset($_REQUEST['approved']) ? 1 : 0) : $row['approved'];
		$approve_has_changed = $row['approved'] != $becomesApproved;

		if (!allowedTo('moderate_forum') || !$posterIsGuest)
		{
			$_POST['guestname'] = $smfFunc['db_escape_string']($row['poster_name']);
			$_POST['email'] = $smfFunc['db_escape_string']($row['poster_email']);
		}
	}

	// If the poster is a guest evaluate the legality of name and email.
	if ($posterIsGuest)
	{
		$_POST['guestname'] = !isset($_POST['guestname']) ? '' : trim($_POST['guestname']);
		$_POST['email'] = !isset($_POST['email']) ? '' : trim($_POST['email']);

		if ($_POST['guestname'] == '' || $_POST['guestname'] == '_')
			$post_errors[] = 'no_name';
		if ($smfFunc['strlen']($_POST['guestname']) > 25)
			$post_errors[] = 'long_name';

		if (empty($modSettings['guest_post_no_email']))
		{
			// Only check if they changed it!
			if (!isset($row) || $row['poster_email'] != $_POST['email'])
			{
				if (!allowedTo('moderate_forum') && (!isset($_POST['email']) || $_POST['email'] == ''))
					$post_errors[] = 'no_email';
				if (!allowedTo('moderate_forum') && preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $smfFunc['db_unescape_string']($_POST['email'])) == 0)
					$post_errors[] = 'bad_email';
			}

			// Now make sure this email address is not banned from posting.
			isBannedEmail($_POST['email'], 'cannot_post', sprintf($txt['you_are_post_banned'], $txt['guest_title']));
		}

		// In case they are making multiple posts this visit, help them along by storing their name.
		if (empty($post_errors))
		{
			$_SESSION['guest_name'] = $_POST['guestname'];
			$_SESSION['guest_email'] = $_POST['email'];
		}
	}

	// Check the subject and message.
	if (!isset($_POST['subject']) || $smfFunc['htmltrim']($smfFunc['htmlspecialchars']($_POST['subject'])) === '')
		$post_errors[] = 'no_subject';
	if (!isset($_POST['message']) || $smfFunc['htmltrim']($smfFunc['htmlspecialchars']($_POST['message']), ENT_QUOTES) === '')
		$post_errors[] = 'no_message';
	elseif (!empty($modSettings['max_messageLength']) && $smfFunc['strlen']($_POST['message']) > $modSettings['max_messageLength'])
		$post_errors[] = 'long_message';
	else
	{
		// Prepare the message a bit for some additional testing.
		$_POST['message'] = $smfFunc['db_escape_string']($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_POST['message']), ENT_QUOTES));

		// Preparse code. (Zef)
		if ($user_info['is_guest'])
			$user_info['name'] = $_POST['guestname'];
		preparsecode($_POST['message']);

		// Let's see if there's still some content left without the tags.
		if ($smfFunc['htmltrim'](strip_tags(parse_bbc($_POST['message'], false), '<img>')) === '' && (!allowedTo('admin_forum') || strpos($_POST['message'], '[html]') === false))
			$post_errors[] = 'no_message';
	}
	if (isset($_POST['calendar']) && !isset($_REQUEST['deleteevent']) && $smfFunc['htmltrim']($_POST['evtitle']) === '')
		$post_errors[] = 'no_event';
	// You are not!
	if (isset($_POST['message']) && strtolower($_POST['message']) == 'i am the administrator.' && !$user_info['is_admin'])
		fatal_error('Knave! Masquerader! Charlatan!', false);

	// Validate the poll...
	if (isset($_REQUEST['poll']) && $modSettings['pollMode'] == '1')
	{
		if (isset($topic) && !isset($_REQUEST['msg']))
			fatal_lang_error('no_access', false);

		// This is a new topic... so it's a new poll.
		if (empty($topic))
			isAllowedTo('poll_post');
		// Can you add to your own topics?
		elseif ($user_info['id'] == $row['id_member_poster'] && !allowedTo('poll_add_any'))
			isAllowedTo('poll_add_own');
		// Can you add polls to any topic, then?
		else
			isAllowedTo('poll_add_any');

		if (!isset($_POST['question']) || trim($_POST['question']) == '')
			$post_errors[] = 'no_question';

		$_POST['options'] = empty($_POST['options']) ? array() : htmltrim__recursive($_POST['options']);

		// Get rid of empty ones.
		foreach ($_POST['options'] as $k => $option)
			if ($option == '')
				unset($_POST['options'][$k], $_POST['options'][$k]);

		// What are you going to vote between with one choice?!?
		if (count($_POST['options']) < 2)
			$post_errors[] = 'poll_few';
	}

	if ($posterIsGuest)
	{
		// If user is a guest, make sure the chosen name isn't taken.
		require_once($sourcedir . '/Subs-Members.php');
		if (isReservedName($_POST['guestname'], 0, true, false) && (!isset($row['poster_name']) || $_POST['guestname'] != $row['poster_name']))
			$post_errors[] = 'bad_name';
	}
	// If the user isn't a guest, get his or her name and email.
	elseif (!isset($_REQUEST['msg']))
	{
		$_POST['guestname'] = $smfFunc['db_escape_string']($user_info['username']);
		$_POST['email'] = $smfFunc['db_escape_string']($user_info['email']);
	}

	// Any mistakes?
	if (!empty($post_errors))
	{
		loadLanguage('Errors');
		// Previewing.
		$_REQUEST['preview'] = true;

		$context['post_error'] = array('messages' => array());
		foreach ($post_errors as $post_error)
		{
			$context['post_error'][$post_error] = true;
			if ($post_error == 'long_message')
				$txt['error_' . $post_error] = sprintf($txt['error_' . $post_error], $modSettings['max_messageLength']);

			$context['post_error']['messages'][] = $txt['error_' . $post_error];
		}

		return Post();
	}

	// Make sure the user isn't spamming the board.
	if (!isset($_REQUEST['msg']))
		spamProtection('post');

	// At about this point, we're posting and that's that.
	ignore_user_abort(true);
	@set_time_limit(300);

	// Add special html entities to the subject, name, and email.
	$_POST['subject'] = strtr($smfFunc['htmlspecialchars']($_POST['subject']), array("\r" => '', "\n" => '', "\t" => ''));
	$_POST['guestname'] = htmlspecialchars($_POST['guestname']);
	$_POST['email'] = htmlspecialchars($_POST['email']);

	// At this point, we want to make sure the subject isn't too long.
	if ($smfFunc['strlen']($_POST['subject']) > 100)
		$_POST['subject'] = $smfFunc['db_escape_string']($smfFunc['substr']($smfFunc['db_unescape_string']($_POST['subject']), 0, 100));

	// Make the poll...
	if (isset($_REQUEST['poll']))
	{
		// Make sure that the user has not entered a ridiculous number of options..
		if (empty($_POST['poll_max_votes']) || $_POST['poll_max_votes'] <= 0)
			$_POST['poll_max_votes'] = 1;
		elseif ($_POST['poll_max_votes'] > count($_POST['options']))
			$_POST['poll_max_votes'] = count($_POST['options']);
		else
			$_POST['poll_max_votes'] = (int) $_POST['poll_max_votes'];

		// Just set it to zero if it's not there..
		if (!isset($_POST['poll_hide']))
			$_POST['poll_hide'] = 0;
		else
			$_POST['poll_hide'] = (int) $_POST['poll_hide'];
		$_POST['poll_change_vote'] = isset($_POST['poll_change_vote']) ? 1 : 0;

		$_POST['poll_guest_vote'] = isset($_POST['poll_guest_vote']) ? 1 : 0;
		// Make sure guests are actually allowed to vote generally.
		if ($_POST['poll_guest_vote'])
		{
			require_once($sourcedir . '/Subs-Members.php');
			$allowedVoteGroups = groupsAllowedTo('poll_vote', $board);
			if (!in_array(-1, $allowedVoteGroups['allowed']))
				$_POST['poll_guest_vote'] = 0;
		}

		// If the user tries to set the poll too far in advance, don't let them.
		if (!empty($_POST['poll_expire']) && $_POST['poll_expire'] < 1)
			fatal_lang_error('poll_range_error', false);
		// Don't allow them to select option 2 for hidden results if it's not time limited.
		elseif (empty($_POST['poll_expire']) && $_POST['poll_hide'] == 2)
			$_POST['poll_hide'] = 1;

		// Clean up the question and answers.
		$_POST['question'] = htmlspecialchars($_POST['question']);
		$_POST['question'] = preg_replace('~&amp;#(\d{4,5}|[2-9]\d{2,4}|1[2-9]\d);~', '&#$1;', $_POST['question']);
		$_POST['options'] = htmlspecialchars__recursive($_POST['options']);
	}

	// Check if they are trying to delete any current attachments....
	if (isset($_REQUEST['msg'], $_POST['attach_del']) && (allowedTo('post_attachment') || allowedTo('post_unapproved_attachments')))
	{
		$del_temp = array();
		foreach ($_POST['attach_del'] as $i => $dummy)
			$del_temp[$i] = (int) $dummy;

		require_once($sourcedir . '/ManageAttachments.php');
		removeAttachments('a.attachment_type = 0 AND a.id_msg = ' . (int) $_REQUEST['msg'] . ' AND a.id_attach NOT IN (' . implode(', ', $del_temp) . ')');
	}

	// ...or attach a new file...
	if (isset($_FILES['attachment']['name']) || !empty($_SESSION['temp_attachments']))
	{
		// Verify they can post them!
		if (!allowedTo('post_unapproved_attachments'))
			isAllowedTo('post_attachment');

		// Make sure we're uploading to the right place.
		if (!empty($modSettings['currentAttachmentUploadDir']))
		{
			if (!is_array($modSettings['attachmentUploadDir']))
				$modSettings['attachmentUploadDir'] = unserialize($modSettings['attachmentUploadDir']);

			// The current directory, of course!
			$current_attach_dir = $modSettings['attachmentUploadDir'][$modSettings['currentAttachmentUploadDir']];
		}
		else
			$current_attach_dir = $modSettings['attachmentUploadDir'];

		// If this isn't a new post, check the current attachments.
		if (isset($_REQUEST['msg']))
		{
			$request = $smfFunc['db_query']('', '
				SELECT COUNT(*), SUM(size)
				FROM {db_prefix}attachments
				WHERE id_msg = {int:inject_int_1}
					AND attachment_type = {int:inject_int_2}',
				array(
					'inject_int_1' => (int) $_REQUEST['msg'],
					'inject_int_2' => 0,
				)
			);
			list ($quantity, $total_size) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);
		}
		else
		{
			$quantity = 0;
			$total_size = 0;
		}

		if (!empty($_SESSION['temp_attachments']))
			foreach ($_SESSION['temp_attachments'] as $attachID => $name)
			{
				if (preg_match('~^post_tmp_' . $user_info['id'] . '_\d+$~', $attachID) == 0)
					continue;

				if (!empty($_POST['attach_del']) && !in_array($attachID, $_POST['attach_del']))
				{
					unset($_SESSION['temp_attachments'][$attachID]);
					@unlink($current_attach_dir . '/' . $attachID);
					continue;
				}

				$_FILES['attachment']['tmp_name'][] = $attachID;
				$_FILES['attachment']['name'][] = $smfFunc['db_escape_string']($name);
				$_FILES['attachment']['size'][] = filesize($current_attach_dir . '/' . $attachID);
				list ($_FILES['attachment']['width'][], $_FILES['attachment']['height'][]) = @getimagesize($current_attach_dir . '/' . $attachID);

				unset($_SESSION['temp_attachments'][$attachID]);
			}

		if (!isset($_FILES['attachment']['name']))
			$_FILES['attachment']['tmp_name'] = array();

		$attachIDs = array();
		foreach ($_FILES['attachment']['tmp_name'] as $n => $dummy)
		{
			if ($_FILES['attachment']['name'][$n] == '')
				continue;

			// Have we reached the maximum number of files we are allowed?
			$quantity++;
			if (!empty($modSettings['attachmentNumPerPostLimit']) && $quantity > $modSettings['attachmentNumPerPostLimit'])
				fatal_lang_error('attachments_limit_per_post', false, array($modSettings['attachmentNumPerPostLimit']));

			// Check the total upload size for this post...
			$total_size += $_FILES['attachment']['size'][$n];
			if (!empty($modSettings['attachmentPostLimit']) && $total_size > $modSettings['attachmentPostLimit'] * 1024)
				fatal_lang_error('file_too_big', false, array($modSettings['attachmentPostLimit']));

			$attachmentOptions = array(
				'post' => isset($_REQUEST['msg']) ? $_REQUEST['msg'] : 0,
				'poster' => $user_info['id'],
				'name' => $_FILES['attachment']['name'][$n],
				'tmp_name' => $_FILES['attachment']['tmp_name'][$n],
				'size' => $_FILES['attachment']['size'][$n],
				'approved' => allowedTo('post_attachment'),
			);

			if (createAttachment($attachmentOptions))
			{
				$attachIDs[] = $attachmentOptions['id'];
				if (!empty($attachmentOptions['thumb']))
					$attachIDs[] = $attachmentOptions['thumb'];
			}
			else
			{
				if (in_array('could_not_upload', $attachmentOptions['errors']))
					fatal_lang_error('attach_timeout', 'critical');
				if (in_array('too_large', $attachmentOptions['errors']))
					fatal_lang_error('file_too_big', false, array($modSettings['attachmentSizeLimit']));
				if (in_array('bad_extension', $attachmentOptions['errors']))
					fatal_error($attachmentOptions['name'] . '.<br />' . $txt['cant_upload_type'] . ' ' . $modSettings['attachmentExtensions'] . '.', false);
				if (in_array('directory_full', $attachmentOptions['errors']))
					fatal_lang_error('ran_out_of_space', 'critical');
				if (in_array('bad_filename', $attachmentOptions['errors']))
					fatal_error(basename($attachmentOptions['name']) . '.<br />' . $txt['restricted_filename'] . '.', 'critical');
				if (in_array('taken_filename', $attachmentOptions['errors']))
					fatal_lang_error('filename_exisits');
			}
		}
	}

	// Make the poll...
	if (isset($_REQUEST['poll']))
	{
		// Create the poll.
		$smfFunc['db_query']('', '
			INSERT INTO {db_prefix}polls
				(question, hide_results, max_votes, expire_time, id_member, poster_name, change_vote, guest_vote)
			VALUES (SUBSTRING(\'' . $_POST['question'] . '\', 1, 255), ' . $_POST['poll_hide'] . ', ' . $_POST['poll_max_votes'] . ',
				' . (empty($_POST['poll_expire']) ? '0' : time() + $_POST['poll_expire'] * 3600 * 24) . ', ' . $user_info['id'] . ', SUBSTRING(\'' . $_POST['guestname'] . '\', 1, 255),
				' . $_POST['poll_change_vote'] . ', ' . $_POST['poll_guest_vote'] . ')',
			array(
			)
		);
		$id_poll = $smfFunc['db_insert_id']( $db_prefix . 'polls', 'id_poll');

		// Create each answer choice.
		$i = 0;
		$pollOptions = array();
		foreach ($_POST['options'] as $option)
		{
			$pollOptions[] = array($id_poll, $i, $option);
			$i++;
		}

		$smfFunc['db_insert']('insert',
			$db_prefix . 'poll_choices',
			array('id_poll' => 'int', 'id_choice' => 'int', 'label' => 'string-255'),
			$pollOptions,
			array('id_poll', 'id_choice')
		);
	}
	else
		$id_poll = 0;

	// Creating a new topic?
	$newTopic = empty($_REQUEST['msg']) && empty($topic);

	$_POST['icon'] = !empty($attachIDs) && $_POST['icon'] == 'xx' ? 'clip' : $_POST['icon'];

	// Collect all parameters for the creation or modification of a post.
	$msgOptions = array(
		'id' => empty($_REQUEST['msg']) ? 0 : (int) $_REQUEST['msg'],
		'subject' => $_POST['subject'],
		'body' => $_POST['message'],
		'icon' => preg_replace('~[\./\\\\*:"\'<>]~', '', $_POST['icon']),
		'smileys_enabled' => !isset($_POST['ns']),
		'attachments' => empty($attachIDs) ? array() : $attachIDs,
		'approved' => $becomesApproved,
	);
	$topicOptions = array(
		'id' => empty($topic) ? 0 : $topic,
		'board' => $board,
		'poll' => isset($_REQUEST['poll']) ? $id_poll : null,
		'lock_mode' => isset($_POST['lock']) ? (int) $_POST['lock'] : null,
		'sticky_mode' => isset($_POST['sticky']) && !empty($modSettings['enableStickyTopics']) ? (int) $_POST['sticky'] : null,
		'mark_as_read' => true,
		'is_approved' => empty($topic) || !empty($board_info['cur_topic_approved']),
	);
	$posterOptions = array(
		'id' => $user_info['id'],
		'name' => $_POST['guestname'],
		'email' => $_POST['email'],
		'update_post_count' => !$user_info['is_guest'] && !isset($_REQUEST['msg']) && $board_info['posts_count'],
	);

	// This is an already existing message. Edit it.
	if (!empty($_REQUEST['msg']))
	{
		// Have admins allowed people to hide their screwups?
		if (time() - $row['poster_time'] > $modSettings['edit_wait_time'] || $user_info['id'] != $row['id_member'])
		{
			$msgOptions['modify_time'] = time();
			$msgOptions['modify_name'] = $smfFunc['db_escape_string']($user_info['name']);
		}

		// This will save some time...
		if (empty($approve_has_changed))
			unset($msgOptions['approved']);

		modifyPost($msgOptions, $topicOptions, $posterOptions);
	}
	// This is a new topic or an already existing one. Save it.
	else
	{
		createPost($msgOptions, $topicOptions, $posterOptions);

		if (isset($topicOptions['id']))
			$topic = $topicOptions['id'];
	}

	// Editing or posting an event?
	if (isset($_POST['calendar']) && (!isset($_REQUEST['eventid']) || $_REQUEST['eventid'] == -1))
	{
		require_once($sourcedir . '/Subs-Calendar.php');

		// Make sure they can link an event to this post.
		canLinkEvent();

		// Insert the event.
		$eventOptions = array(
			'board' => $board,
			'topic' => $topic,
			'title' => $_POST['evtitle'],
			'member' => $user_info['id'],
			'start_date' => sprintf('%04d-%02d-%02d', $_POST['year'], $_POST['month'], $_POST['day']),
			'span' => isset($_POST['span']) && $_POST['span'] > 0 ? min((int) $modSettings['cal_maxspan'], (int) $_POST['span'] - 1) : 0,
		);
		insertEvent($eventOptions);
	}
	elseif (isset($_POST['calendar']))
	{
		$_REQUEST['eventid'] = (int) $_REQUEST['eventid'];

		// Validate the post...
		require_once($sourcedir . '/Subs-Calendar.php');
		validateEventPost();

		// If you're not allowed to edit any events, you have to be the poster.
		if (!allowedTo('calendar_edit_any'))
		{
			// Get the event's poster.
			$request = $smfFunc['db_query']('', '
				SELECT id_member
				FROM {db_prefix}calendar
				WHERE id_event = {int:inject_int_1}',
				array(
					'inject_int_1' => $_REQUEST['eventid'],
				)
			);
			$row2 = $smfFunc['db_fetch_assoc']($request);
			$smfFunc['db_free_result']($request);

			// Silly hacker, Trix are for kids. ...probably trademarked somewhere, this is FAIR USE! (parody...)
			isAllowedTo('calendar_edit_' . ($row2['id_member'] == $user_info['id'] ? 'own' : 'any'));
		}

		// Delete it?
		if (isset($_REQUEST['deleteevent']))
			$smfFunc['db_query']('', '
				DELETE FROM {db_prefix}calendar
				WHERE id_event = {int:inject_int_1}',
				array(
					'inject_int_1' => $_REQUEST['eventid'],
				)
			);
		// ... or just update it?
		else
		{
			$span = !empty($modSettings['cal_allowspan']) && !empty($_REQUEST['span']) ? min((int) $modSettings['cal_maxspan'], (int) $_REQUEST['span'] - 1) : 0;
			$start_time = mktime(0, 0, 0, (int) $_REQUEST['month'], (int) $_REQUEST['day'], (int) $_REQUEST['year']);

			$smfFunc['db_query']('', '
				UPDATE {db_prefix}calendar
				SET end_date = {date:inject_date_1},
					start_date = {date:inject_date_2},
					title = {string:inject_string_1}
				WHERE id_event = {int:inject_int_1}',
				array(
					'inject_date_1' => strftime('%Y-%m-%d', $start_time + $span * 86400),
					'inject_date_2' => strftime('%Y-%m-%d', $start_time),
					'inject_int_1' => $_REQUEST['eventid'],
					'inject_string_1' => $smfFunc['db_escape_string']($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_REQUEST['evtitle']), ENT_QUOTES)),
				)
			);
		}
		updateSettings(array(
			'calendar_updated' => time(),
		));
	}

	// Marking read should be done even for editing messages....
	if (!$user_info['is_guest'])
	{
		// Mark all the parents read.  (since you just posted and they will be unread.)
		if (!empty($board_info['parent_boards']))
		{
			$smfFunc['db_query']('', '
				UPDATE {db_prefix}log_boards
				SET id_msg = {int:inject_int_1}
				WHERE id_member = {int:current_member}
					AND id_board IN ({array_int:inject_array_int_1})',
				array(
					'current_member' => $user_info['id'],
					'inject_array_int_1' => array_keys($board_info['parent_boards']),
					'inject_int_1' => $modSettings['maxMsgID'],
				)
			);
		}
	}

	// Turn notification on or off.  (note this just blows smoke if it's already on or off.)
	if (!empty($_POST['notify']))
	{
		if (allowedTo('mark_any_notify'))
			$smfFunc['db_insert']('ignore',
				$db_prefix . 'log_notify',
				array('id_member' => 'int', 'id_topic' => 'int', 'id_board' => 'int'),
				array($user_info['id'], $topic, 0),
				array('id_member', 'id_topic', 'id_board')
			);
	}
	elseif (!$newTopic)
		$smfFunc['db_query']('', '
			DELETE FROM {db_prefix}log_notify
			WHERE id_member = {int:current_member}
				AND id_topic = {int:current_topic}',
			array(
				'current_member' => $user_info['id'],
				'current_topic' => $topic,
			)
		);

	// Log an act of moderation - modifying.
	if (!empty($moderationAction))
		logAction('modify', array('topic' => $topic, 'message' => (int) $_REQUEST['msg'], 'member' => $row['id_member'], 'board' => $board));

	if (isset($_POST['lock']) && $_POST['lock'] != 2)
		logAction('lock', array('topic' => $topicOptions['id'], 'board' => $topicOptions['board']));

	if (isset($_POST['sticky']) && !empty($modSettings['enableStickyTopics']))
		logAction('sticky', array('topic' => $topicOptions['id'], 'board' => $topicOptions['board']));


	// Notify any members who have notification turned on for this topic - only do this if it's going to be approved(!)
	if ($becomesApproved)
	{
		if ($newTopic)
		{
			$notifyData = array(
				'body' => $smfFunc['db_unescape_string']($_POST['message']),
				'subject' => $_POST['subject'],
				'name' => $user_info['name'],
				'poster' => $user_info['id'],
				'msg' => $msgOptions['id'],
				'board' => $board,
				'topic' => $topic,
			);
			notifyMembersBoard($notifyData);
		}
		elseif (empty($_REQUEST['msg']))
			sendNotifications($topic, 'reply');
	}

	// Returning to the topic?
	if (!empty($_REQUEST['goback']))
	{
		// Mark the board as read.... because it might get confusing otherwise.
		$smfFunc['db_query']('', '
			UPDATE {db_prefix}log_boards
			SET id_msg = {int:inject_int_1}
			WHERE id_member = {int:current_member}
				AND id_board = {int:current_board}',
			array(
				'current_board' => $board,
				'current_member' => $user_info['id'],
				'inject_int_1' => $modSettings['maxMsgID'],
			)
		);
	}

	if ($board_info['num_topics'] == 0)
		cache_put_data('board-' . $board, null, 120);

	if (!empty($_POST['announce_topic']))
		redirectexit('action=announce;sa=selectgroup;topic=' . $topic . (!empty($_POST['move']) && allowedTo('move_any') ? ';move' : '') . (empty($_REQUEST['goback']) ? '' : ';goback'));

	if (!empty($_POST['move']) && allowedTo('move_any'))
		redirectexit('action=movetopic;topic=' . $topic . '.0' . (empty($_REQUEST['goback']) ? '' : ';goback'));

	// Return to post if the mod is on.
	if (isset($_REQUEST['msg']) && !empty($_REQUEST['goback']))
		redirectexit('topic=' . $topic . '.msg' . $_REQUEST['msg'] . '#msg' . $_REQUEST['msg'], $context['browser']['is_ie']);
	elseif (!empty($_REQUEST['goback']))
		redirectexit('topic=' . $topic . '.new#new', $context['browser']['is_ie']);
	// Dut-dut-duh-duh-DUH-duh-dut-duh-duh!  *dances to the Final Fantasy Fanfare...*
	else
		redirectexit('board=' . $board . '.0');
}

// General function for topic announcements.
function AnnounceTopic()
{
	global $context, $txt;

	isAllowedTo('announce_topic');

	validateSession();

	loadLanguage('Post');
	loadTemplate('Post');

	$subActions = array(
		'selectgroup' => 'AnnouncementSelectMembergroup',
		'send' => 'AnnouncementSend',
	);

	$context['page_title'] = $txt['announce_topic'];

	// Call the function based on the sub-action.
	$subActions[isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'selectgroup']();
}

// Allow a user to chose the membergroups to send the announcement to.
function AnnouncementSelectMembergroup()
{
	global $db_prefix, $txt, $context, $topic, $board, $board_info, $smfFunc;

	$groups = array_merge($board_info['groups'], array(1));
	foreach ($groups as $id => $group)
		$groups[$id] = (int) $group;

	$context['groups'] = array();
	if (in_array(0, $groups))
	{
		$context['groups'][0] = array(
			'id' => 0,
			'name' => $txt['announce_regular_members'],
			'member_count' => 'n/a',
		);
	}

	// Get all membergroups that have access to the board the announcement was made on.
	$request = $smfFunc['db_query']('', '
		SELECT mg.id_group, mg.group_name, COUNT(mem.id_member) AS num_members
		FROM {db_prefix}membergroups AS mg
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_group = mg.id_group OR FIND_IN_SET(mg.id_group, mem.additional_groups) OR mg.id_group = mem.id_post_group)
		WHERE mg.id_group IN ({array_int:inject_array_int_1})
		GROUP BY mg.id_group
		ORDER BY mg.min_posts, CASE WHEN mg.id_group < {int:inject_int_1} THEN mg.id_group ELSE 4 END, mg.group_name',
		array(
			'inject_array_int_1' => $groups,
			'inject_int_1' => 4,
		)
	);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['groups'][$row['id_group']] = array(
			'id' => $row['id_group'],
			'name' => $row['group_name'],
			'member_count' => $row['num_members'],
		);
	}
	$smfFunc['db_free_result']($request);

	// Get the subject of the topic we're about to announce.
	$request = $smfFunc['db_query']('', '
		SELECT m.subject
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
		WHERE t.id_topic = {int:current_topic}',
		array(
			'current_topic' => $topic,
		)
	);
	list ($context['topic_subject']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	censorText($context['announce_topic']['subject']);

	$context['move'] = isset($_REQUEST['move']) ? 1 : 0;
	$context['go_back'] = isset($_REQUEST['goback']) ? 1 : 0;

	$context['sub_template'] = 'announce';
}

// Send the announcement in chunks.
function AnnouncementSend()
{
	global $db_prefix, $topic, $board, $board_info, $context, $modSettings;
	global $language, $scripturl, $txt, $user_info, $sourcedir, $smfFunc;

	checkSession();

	// !!! Might need an interface?
	$chunkSize = empty($modSettings['mail_queue']) ? 50 : 500;

	$context['start'] = empty($_REQUEST['start']) ? 0 : (int) $_REQUEST['start'];
	$groups = array_merge($board_info['groups'], array(1));

	if (!empty($_POST['membergroups']))
		$_POST['who'] = explode(',', $_POST['membergroups']);

	// Check whether at least one membergroup was selected.
	if (empty($_POST['who']))
		fatal_lang_error('no_membergroup_selected');

	// Make sure all membergroups are integers and can access the board of the announcement.
	foreach ($_POST['who'] as $id => $mg)
		$_POST['who'][$id] = in_array((int) $mg, $groups) ? (int) $mg : 0;

	// Get the topic subject and censor it.
	$request = $smfFunc['db_query']('', '
		SELECT m.id_msg, m.subject, m.body
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
		WHERE t.id_topic = {int:current_topic}',
		array(
			'current_topic' => $topic,
		)
	);
	list ($id_msg, $context['topic_subject'], $message) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	censorText($context['topic_subject']);
	censorText($message);

	$message = trim(un_htmlspecialchars(strip_tags(strtr(parse_bbc($message, false, $id_msg), array('<br />' => "\n", '</div>' => "\n", '</li>' => "\n", '&#91;' => '[', '&#93;' => ']')))));

	// We need this in order to be able send emails.
	require_once($sourcedir . '/Subs-Post.php');

	// Select the email addresses for this batch.
	$request = $smfFunc['db_query']('', '
		SELECT mem.id_member, mem.email_address, mem.lngfile
		FROM {db_prefix}members AS mem
		WHERE mem.id_member != {int:current_member}' . (!empty($modSettings['allow_disableAnnounce']) ? '
			AND mem.notify_announcements = {int:inject_int_1}' : '') . '
			AND mem.is_activated = {int:inject_int_1}
			AND (mem.id_group IN ({array_int:inject_array_int_1}) OR mem.id_post_group IN ({array_int:inject_array_int_1}) OR FIND_IN_SET({string:inject_string_1}, mem.additional_groups))
			AND mem.id_member > {int:inject_int_2}
		ORDER BY mem.id_member
		LIMIT ' . $chunkSize,
		array(
			'current_member' => $user_info['id'],
			'inject_array_int_1' => $_POST['who'],
			'inject_int_1' => 1,
			'inject_int_2' => $context['start'],
			'inject_string_1' => implode(', mem.additional_groups) OR FIND_IN_SET(', $_POST['who']),
		)
	);

	// All members have received a mail. Go to the next screen.
	if ($smfFunc['db_num_rows']($request) == 0)
	{
		if (!empty($_REQUEST['move']) && allowedTo('move_any'))
			redirectexit('action=movetopic;topic=' . $topic . '.0' . (empty($_REQUEST['goback']) ? '' : ';goback'));
		elseif (!empty($_REQUEST['goback']))
			redirectexit('topic=' . $topic . '.new;boardseen#new', $context['browser']['is_ie']);
		else
			redirectexit('board=' . $board . '.0');
	}

	// Loop through all members that'll receive an announcement in this batch.
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$cur_language = empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile'];

		// If the language wasn't defined yet, load it and compose a notification message.
		if (!isset($announcements[$cur_language]))
		{
			$replacements = array(
				'TOPICSUBJECT' => $context['topic_subject'],
				'MESSAGE' => $message,
				'TOPICLINK' => $scripturl . '?topic=' . $topic . '.0',
			);

			$emaildata = loadEmailTemplate('new_announcement', $replacements, $cur_language);

			$announcements[$cur_language] = array(
				'subject' => $emaildata['subject'],
				'body' => $emaildata['body'],
				'recipients' => array(),
			);
		}

		$announcements[$cur_language]['recipients'][$row['id_member']] = $row['email_address'];
		$context['start'] = $row['id_member'];
	}
	$smfFunc['db_free_result']($request);

	// For each language send a different mail - low priority...
	foreach ($announcements as $lang => $mail)
		sendmail($mail['recipients'], $mail['subject'], $mail['body'], null, null, false, 0);

	$context['percentage_done'] = round(100 * $context['start'] / $modSettings['latestMember'], 1);

	$context['move'] = empty($_REQUEST['move']) ? 0 : 1;
	$context['go_back'] = empty($_REQUEST['goback']) ? 0 : 1;
	$context['membergroups'] = implode(',', $_POST['who']);
	$context['sub_template'] = 'announcement_send';

	// Go back to the correct language for the user ;).
	if (!empty($modSettings['userLanguage']))
		loadLanguage('Post');
}

// Notify members of a new post.
function notifyMembersBoard(&$topicData)
{
	global $txt, $scripturl, $db_prefix, $language, $user_info;
	global $modSettings, $sourcedir, $board, $smfFunc, $context;

	require_once($sourcedir . '/Subs-Post.php');

	// Do we have one or lots of topics?
	if (isset($topicData['body']))
		$topicData = array($topicData);

	// Find out what boards we have... and clear out any rubbish!
	$boards = array();
	foreach ($topicData as $key => $topic)
	{
		if (!empty($topic['board']))
			$boards[$topic['board']][] = $key;
		else
		{
			unset($topic[$key]);
			continue;
		}

		// Censor the subject and body...
		censorText($topicData[$key]['subject']);
		censorText($topicData[$key]['body']);

		$topicData[$key]['subject'] = un_htmlspecialchars($topicData[$key]['subject']);
		$topicData[$key]['body'] = trim(un_htmlspecialchars(strip_tags(strtr(parse_bbc($topicData[$key]['body'], false), array('<br />' => "\n", '</div>' => "\n", '</li>' => "\n", '&#91;' => '[', '&#93;' => ']')))));
	}

	// Just the board numbers.
	$board_index = array_unique(array_keys($boards));

	if (empty($board_index))
		return;

	// Yea, we need to add this to the digest queue.
	$digest_insert = array();
	foreach ($topicData as $id => $data)
		$digest_insert[] = '(' . $data['topic'] . ', ' . $data['msg'] . ', \'topic\', ' . $user_info['id'] . ')';
	$smfFunc['db_query']('', '
		INSERT INTO {db_prefix}log_digest
			(id_topic, id_msg, note_type, exclude)
		VALUES
			' . implode(', ', $digest_insert),
		array(
		)
	);

	// Find the members with notification on for these boards.
	$members = $smfFunc['db_query']('', '
		SELECT
			mem.id_member, mem.email_address, mem.notify_regularity, mem.notify_send_body, mem.lngfile,
			ln.sent, ln.id_board, mem.id_group, mem.additional_groups, b.member_groups,
			mem.id_post_group
		FROM {db_prefix}log_notify AS ln
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = ln.id_board)
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = ln.id_member)
		WHERE ln.id_board IN ({array_int:inject_array_int_1})
			AND mem.id_member != {int:current_member}
			AND mem.is_activated = {int:inject_int_1}
			AND mem.notify_types != {int:inject_int_2}
			AND mem.notify_regularity < {int:inject_int_3}
		ORDER BY mem.lngfile',
		array(
			'current_member' => $user_info['id'],
			'inject_array_int_1' => $board_index,
			'inject_int_1' => 1,
			'inject_int_2' => 4,
			'inject_int_3' => 2,
		)
	);
	while ($rowmember = $smfFunc['db_fetch_assoc']($members))
	{
		if ($rowmember['id_group'] != 1)
		{
			$allowed = explode(',', $rowmember['member_groups']);
			$rowmember['additional_groups'] = explode(',', $rowmember['additional_groups']);
			$rowmember['additional_groups'][] = $rowmember['id_group'];
			$rowmember['additional_groups'][] = $rowmember['id_post_group'];

			if (count(array_intersect($allowed, $rowmember['additional_groups'])) == 0)
				continue;
		}

		$langloaded = loadLanguage('EmailTemplates', empty($rowmember['lngfile']) || empty($modSettings['userLanguage']) ? $language : $rowmember['lngfile'], false);

		// Now loop through all the notifications to send for this board.
		if (empty($boards[$rowmember['id_board']]))
			continue;

		$sentOnceAlready = 0;
		foreach ($boards[$rowmember['id_board']] as $key)
		{
			// Don't notify the guy who started the topic!
			//!!! In this case actually send them a "it's approved hooray" email
			if ($topicData[$key]['poster'] == $rowmember['id_member'])
				continue;

			// Setup the string for adding the body to the message, if a user wants it.
			$send_body = empty($modSettings['disallow_sendBody']) && !empty($rowmember['notify_send_body']);

			$replacements = array(
				'TOPICSUBJECT' => $topicData[$key]['subject'],
				'TOPICLINK' => $scripturl . '?topic=' . $topicData[$key]['topic'] . '.new#new',
				'MESSAGE' => $topicData[$key]['body'],
				'UNSUBSCRIBELINK' => $scripturl . '?action=notifyboard;board=' . $topicData[$key]['board'] . '.0',
			);

			if (!$send_body)
				unset($replacements['MESSAGE']);

			// Figure out which email to send off
			$emailtype = '';

			// Send only if once is off or it's on and it hasn't been sent.
			if (!empty($rowmember['notify_regularity']) && !$sentOnceAlready && empty($rowmember['sent']))
				$emailtype = 'notify_boards_once';
			elseif (empty($rowmember['notify_regularity']))
				$emailtype = 'notify_boards';

			if (!empty($emailtype))
			{
				$emailtype .= $send_body ? '_body' : '';
				$emaildata = loadEmailTemplate($emailtype, $replacements, $langloaded);
				sendmail($rowmember['email_address'], $emaildata['subject'], $emaildata['body']);
			}

			$sentOnceAlready = 1;
		}
	}
	$smfFunc['db_free_result']($members);

	// Sent!
	$smfFunc['db_query']('', '
		UPDATE {db_prefix}log_notify
		SET sent = {int:inject_int_1}
		WHERE id_board IN ({array_int:inject_array_int_1})
			AND id_member != {int:current_member}',
		array(
			'current_member' => $user_info['id'],
			'inject_array_int_1' => $board_index,
			'inject_int_1' => 1,
		)
	);
}

// Get the topic for display purposes.
function getTopic()
{
	global $topic, $db_prefix, $modSettings, $context, $smfFunc;

	// Calculate the amount of new replies.
	$newReplies = empty($_REQUEST['num_replies']) || $context['num_replies'] <= $_REQUEST['num_replies'] ? 0 : $context['num_replies'] - $_REQUEST['num_replies'];

	if (isset($_REQUEST['xml']))
		$limit = '
		LIMIT ' . (empty($newReplies) ? '0' : $newReplies);
	else
		$limit = empty($modSettings['topicSummaryPosts']) ? '' : '
		LIMIT ' . (int) $modSettings['topicSummaryPosts'];

	// If you're modifying, get only those posts before the current one. (otherwise get all.)
	$request = $smfFunc['db_query']('', '
		SELECT IFNULL(mem.real_name, m.poster_name) AS poster_name, m.poster_time, m.body, m.smileys_enabled, m.id_msg
		FROM {db_prefix}messages AS m
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
		WHERE m.id_topic = {int:current_topic}' . (isset($_REQUEST['msg']) ? '
			AND m.id_msg < {int:id_msg}' : '') .
			(allowedTo('approve_posts') ? '' : ' AND m.approved = {int:approved}') . '
		ORDER BY m.id_msg DESC' . $limit,
		array(
			'current_topic' => $topic,
			'id_msg' => isset($_REQUEST['msg']) ? (int) $_REQUEST['msg'] : 0,
			'approved' => 1,
		)
	);
	$context['previous_posts'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Censor, BBC, ...
		censorText($row['body']);
		$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

		// ...and store.
		$context['previous_posts'][] = array(
			'poster' => $row['poster_name'],
			'message' => $row['body'],
			'time' => timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time']),
			'id' => $row['id_msg'],
			'is_new' => !empty($newReplies),
		);

		if (!empty($newReplies))
			$newReplies--;
	}
	$smfFunc['db_free_result']($request);
}

function QuoteFast()
{
	global $db_prefix, $modSettings, $user_info, $txt, $settings, $context;
	global $sourcedir, $smfFunc;

	loadLanguage('Post');
	if (!isset($_REQUEST['xml']))
		loadTemplate('Post');

	checkSession('get');

	include_once($sourcedir . '/Subs-Post.php');

	$moderate_boards = boardsAllowedTo('moderate_board');

	// Where we going if we need to?
	$context['post_box_name'] = isset($_GET['pb']) ? $_GET['pb'] : '';

	$request = $smfFunc['db_query']('', '
		SELECT IFNULL(mem.real_name, m.poster_name) AS poster_name, m.poster_time, m.body, m.id_topic, m.subject
		FROM ({db_prefix}messages AS m, {db_prefix}topics AS t)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board AND ' . $user_info['query_see_board'] . ')
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
		WHERE m.id_msg = {int:inject_int_1}' .
			(allowedTo('approve_posts') ? '' : ' AND m.approved = {int:inject_int_2}') . '
			AND t.id_topic = m.id_topic' . (isset($_REQUEST['modify']) || (!empty($moderate_boards) && $moderate_boards[0] == 0) ? '' : '
			AND (t.locked = {int:inject_int_3}' . (empty($moderate_boards) ? '' : ' OR b.id_board IN ({array_int:inject_array_int_1})') . ')') . '
		LIMIT 1',
		array(
			'inject_array_int_1' => $moderate_boards,
			'inject_int_1' => (int) $_REQUEST['quote'],
			'inject_int_2' => 1,
			'inject_int_3' => 0,
		)
	);
	$context['close_window'] = $smfFunc['db_num_rows']($request) == 0;

	$context['sub_template'] = 'quotefast';
	if ($smfFunc['db_num_rows']($request) != 0)
	{
		$row = $smfFunc['db_fetch_assoc']($request);
		$smfFunc['db_free_result']($request);

		// Remove special formatting we don't want anymore.
		$row['body'] = un_preparsecode($row['body']);

		// Censor the message!
		censorText($row['body']);

		$row['body'] = preg_replace('~<br(?: /)?' . '>~i', "\n", $row['body']);

		// Want to modify a single message by double clicking it?
		if (isset($_REQUEST['modify']))
		{
			censorText($row['subject']);

			$context['sub_template'] = 'modifyfast';
			$context['message'] = array(
				'id' => $_REQUEST['quote'],
				'body' => $row['body'],
				'subject' => addcslashes($row['subject'], '"'),
			);

			return;
		}

		// Remove any nested quotes.
		if (!empty($modSettings['removeNestedQuotes']))
			$row['body'] = preg_replace(array('~\n?\[quote.*?\].+?\[/quote\]\n?~is', '~^\n~', '~\[/quote\]~'), '', $row['body']);

		// Make the body HTML if need be.
		if (!empty($_REQUEST['mode']))
		{
			require_once($sourcedir . '/Subs-Editor.php');
			$row['body'] = bbc_to_html($row['body']);
			$lb = '<br />';
		}
		else
			$lb = "\n";

		// Add a quote string on the front and end.
		$context['quote']['xml'] = '[quote author=' . $row['poster_name'] . ' link=topic=' . $row['id_topic'] . '.msg' . (int) $_REQUEST['quote'] . '#msg' . (int) $_REQUEST['quote'] . ' date=' . $row['poster_time'] . ']' . $lb . $row['body'] . $lb . '[/quote]';
		$context['quote']['text'] = strtr(un_htmlspecialchars($context['quote']['xml']), array('\'' => '\\\'', '\\' => '\\\\', "\n" => '\\n', '</script>' => '</\' + \'script>'));
		$context['quote']['xml'] = strtr($context['quote']['xml'], array('&nbsp;' => '&#160;', '<' => '&lt;', '>' => '&gt;'));

		$context['quote']['mozilla'] = strtr($smfFunc['htmlspecialchars']($context['quote']['text']), array('&quot;' => '"'));
	}
	// !!! Needs a nicer interface.
	// In case our message has been removed in the meantime.
	elseif (isset($_REQUEST['modify']))
	{
		$context['sub_template'] = 'modifyfast';
		$context['message'] = array(
			'id' => 0,
			'body' => '',
			'subject' => '',
		);
	}
	else
		$context['quote'] = array(
			'xml' => '',
			'mozilla' => '',
			'text' => '',
		);
}

function JavaScriptModify()
{
	global $db_prefix, $sourcedir, $modSettings, $board, $topic, $txt;
	global $user_info, $context, $smfFunc, $language;

	// We have to have a topic!
	if (empty($topic))
		obExit(false);

	checkSession('get');
	require_once($sourcedir . '/Subs-Post.php');

	// Assume the first message if no message ID was given.
	$request = $smfFunc['db_query']('', '
			SELECT
				t.locked, t.num_replies, t.id_member_started, t.id_first_msg,
				m.id_msg, m.id_member, m.poster_time, m.subject, m.smileys_enabled, m.body, m.icon,
				m.modified_time, m.modified_name, m.approved
			FROM {db_prefix}messages AS m
				INNER JOIN {db_prefix}topics AS t ON (t.id_topic = {int:current_topic})
			WHERE m.id_msg = {int:inject_int_1}
				AND m.id_topic = {int:current_topic}' .
				(allowedTo('approve_posts') ? '' : ' AND m.approved = {int:inject_int_2}'),
			array(
				'current_topic' => $topic,
				'inject_int_1' => empty($_REQUEST['msg']) ? 't.id_first_msg' : (int) $_REQUEST['msg'],
				'inject_int_2' => 1,
			)
		);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_board', false);
	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// Change either body or subject requires permissions to modify messages.
	if (isset($_POST['message']) || isset($_POST['subject']) || isset($_REQUEST['icon']))
	{
		if (!empty($row['locked']))
			isAllowedTo('moderate_board');

		if ($row['id_member'] == $user_info['id'] && !allowedTo('modify_any'))
		{
			if ($row['approved'] && !empty($modSettings['edit_disable_time']) && $row['poster_time'] + ($modSettings['edit_disable_time'] + 5) * 60 < time())
				fatal_lang_error('modify_post_time_passed', false);
			elseif ($row['id_member_started'] == $user_info['id'] && !allowedTo('modify_own'))
				isAllowedTo('modify_replies');
			else
				isAllowedTo('modify_own');
		}
		// Otherwise, they're locked out; someone who can modify the replies is needed.
		elseif ($row['id_member_started'] == $user_info['id'] && !allowedTo('modify_any'))
			isAllowedTo('modify_replies');
		else
			isAllowedTo('modify_any');

		// Only log this action if it wasn't your message.
		$moderationAction = $row['id_member'] != $user_info['id'];
	}

	$post_errors = array();
	if (isset($_POST['subject']) && $smfFunc['htmltrim']($smfFunc['htmlspecialchars']($_POST['subject'])) !== '')
	{
		$_POST['subject'] = strtr($smfFunc['htmlspecialchars']($_POST['subject']), array("\r" => '', "\n" => '', "\t" => ''));

		// Maximum number of characters.
		if ($smfFunc['strlen']($_POST['subject']) > 100)
			$_POST['subject'] = $smfFunc['db_escape_string']($smfFunc['substr']($smfFunc['db_unescape_string']($_POST['subject']), 0, 100));
	}
	elseif (isset($_POST['subject']))
	{
		$post_errors[] = 'no_subject';
		unset($_POST['subject']);
	}

	if (isset($_POST['message']))
	{
		if ($smfFunc['htmltrim']($smfFunc['htmlspecialchars']($_POST['message'])) === '')
		{
			$post_errors[] = 'no_message';
			unset($_POST['message']);
		}
		elseif (!empty($modSettings['max_messageLength']) && $smfFunc['strlen']($_POST['message']) > $modSettings['max_messageLength'])
		{
			$post_errors[] = 'long_message';
			unset($_POST['message']);
		}
		else
		{
			$_POST['message'] = $smfFunc['db_escape_string']($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_POST['message']), ENT_QUOTES));

			preparsecode($_POST['message']);

			if ($smfFunc['htmltrim'](strip_tags(parse_bbc($_POST['message'], false), '<img>')) === '')
			{
				$post_errors[] = 'no_message';
				unset($_POST['message']);
			}
		}
	}

	if (isset($_POST['lock']))
	{
		if (!allowedTo(array('lock_any', 'lock_own')) || (!allowedTo('lock_any') && $user_info['id'] != $row['id_member']))
			unset($_POST['lock']);
		elseif (!allowedTo('lock_any'))
		{
			if ($row['locked'] == 1)
				unset($_POST['lock']);
			else
				$_POST['lock'] = empty($_POST['lock']) ? 0 : 2;
		}
		elseif (!empty($row['locked']) && !empty($_POST['lock']) || $_POST['lock'] == $row['locked'])
			unset($_POST['lock']);
		else
			$_POST['lock'] = empty($_POST['lock']) ? 0 : 1;
	}

	if (isset($_POST['sticky']) && !allowedTo('make_sticky'))
		unset($_POST['sticky']);


	if (empty($post_errors))
	{
		$msgOptions = array(
			'id' => $row['id_msg'],
			'subject' => isset($_POST['subject']) ? $_POST['subject'] : null,
			'body' => isset($_POST['message']) ? $_POST['message'] : null,
			'icon' => isset($_REQUEST['icon']) ? preg_replace('~[\./\\\\*\':"<>]~', '', $_REQUEST['icon']) : null,
		);
		$topicOptions = array(
			'id' => $topic,
			'board' => $board,
			'lock_mode' => isset($_POST['lock']) ? (int) $_POST['lock'] : null,
			'sticky_mode' => isset($_POST['sticky']) && !empty($modSettings['enableStickyTopics']) ? (int) $_POST['sticky'] : null,
			'mark_as_read' => true,
		);
		$posterOptions = array();

		// Only consider marking as editing if they have edited the subject, message or icon.
		if ((isset($_POST['subject']) && $_POST['subject'] != $row['subject']) || (isset($_POST['message']) && $_POST['message'] != $row['body']) || (isset($_REQUEST['icon']) && $_REQUEST['icon'] != $row['icon']))
		{
			// And even then only if the time has passed...
			if (time() - $row['poster_time'] > $modSettings['edit_wait_time'] || $user_info['id'] != $row['id_member'])
			{
				$msgOptions['modify_time'] = time();
				$msgOptions['modify_name'] = $smfFunc['db_escape_string']($user_info['name']);
			}
		}

		modifyPost($msgOptions, $topicOptions, $posterOptions);

		// If we didn't change anything this time but had before put back the old info.
		if (!isset($msgOptions['modify_time']) && !empty($row['modified_time']))
		{
			$msgOptions['modify_time'] = $row['modified_time'];
			$msgOptions['modify_name'] = $row['modified_name'];
		}

		// Changing the first subject updates other subjects to 'Re: new_subject'.
		if (isset($_POST['subject']) && isset($_REQUEST['change_all_subjects']) && $row['id_first_msg'] == $row['id_msg'] && !empty($row['num_replies']) && (allowedTo('modify_any') || ($row['id_member_started'] == $user_info['id'] && allowedTo('modify_replies'))))
		{
			// Get the proper (default language) response prefix first.
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

			$smfFunc['db_query']('', '
				UPDATE {db_prefix}messages
				SET subject = {string:inject_string_1}
				WHERE id_topic = {int:current_topic}
					AND id_msg != {int:inject_int_1}',
				array(
					'current_topic' => $topic,
					'inject_int_1' => $row['id_first_msg'],
					'inject_string_1' => $context['response_prefix'] . $_POST['subject'],
				)
			);
		}

		if ($moderationAction)
			logAction('modify', array('topic' => $topic, 'message' => $row['id_msg'], 'member' => $row['id_member_started'], 'board' => $board));
	}

	if (isset($_REQUEST['xml']))
	{
		$context['sub_template'] = 'modifydone';
		if (empty($post_errors) && isset($msgOptions['subject']) && isset($msgOptions['body']))
		{
			$context['message'] = array(
				'id' => $row['id_msg'],
				'modified' => array(
					'time' => isset($msgOptions['modify_time']) ? timeformat($msgOptions['modify_time']) : '',
					'timestamp' => isset($msgOptions['modify_time']) ? forum_time(true, $msgOptions['modify_time']) : 0,
					'name' => isset($msgOptions['modify_time']) ? $smfFunc['db_unescape_string']($msgOptions['modify_name']) : '',
				),
				'subject' => $smfFunc['db_unescape_string']($msgOptions['subject']),
				'first_in_topic' => $row['id_msg'] == $row['id_first_msg'],
				'body' => strtr($smfFunc['db_unescape_string']($msgOptions['body']), array(']]>' => ']]]]><![CDATA[>')),
			);

			censorText($context['message']['subject']);
			censorText($context['message']['body']);

			$context['message']['body'] = parse_bbc($context['message']['body'], $row['smileys_enabled'], $row['id_msg']);
		}
		// Topic?
		elseif (empty($post_errors))
		{
			$context['sub_template'] = 'modifytopicdone';
			$context['message'] = array(
				'id' => $row['id_msg'],
				'modified' => array(
					'time' => isset($msgOptions['modify_time']) ? timeformat($msgOptions['modify_time']) : '',
					'timestamp' => isset($msgOptions['modify_time']) ? forum_time(true, $msgOptions['modify_time']) : 0,
					'name' => isset($msgOptions['modify_time']) ? $smfFunc['db_unescape_string']($msgOptions['modify_name']) : '',
				),
				'subject' => isset($msgOptions['subject']) ? $smfFunc['db_unescape_string']($msgOptions['subject']) : '',
			);

			censorText($context['message']['subject']);
		}
		else
		{
			$context['message'] = array(
				'id' => $row['id_msg'],
				'errors' => array(),
				'error_in_subject' => in_array('no_subject', $post_errors),
				'error_in_body' => in_array('no_message', $post_errors) || in_array('long_message', $post_errors),
			);

			loadLanguage('Errors');
			foreach ($post_errors as $post_error)
			{
				if ($post_error == 'long_message')
					$context['message']['errors'][] = sprintf($txt['error_' . $post_error], $modSettings['max_messageLength']);
				else
					$context['message']['errors'][] = $txt['error_' . $post_error];
			}
		}
	}
	else
		obExit(false);
}

?>