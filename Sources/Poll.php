<?php
/**********************************************************************************
* Poll.php                                                                        *
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

/*	This file contains the functions for voting, locking, removing and editing
	polls. Note that that posting polls is done in Post.php.

	void Vote()
		- is called to register a vote in a poll.
		- must be called with a topic and option specified.
		- uses the Post language file.
		- requires the poll_vote permission.
		- upon successful completion of action will direct user back to topic.
		- is accessed via ?action=vote.

	void LockVoting()
		- is called to lock or unlock voting on a poll.
		- must be called with a topic specified in the URL.
		- an admin always has over riding permission to lock a poll.
		- if not an admin must have poll_lock_any permission.
		- otherwise must be poll starter with poll_lock_own permission.
		- upon successful completion of action will direct user back to topic.
		- is accessed via ?action=lockVoting.

	void EditPoll()
		- is called to display screen for editing or adding a poll.
		- must be called with a topic specified in the URL.
		- if the user is adding a poll to a topic, must contain the variable
		  'add' in the url.
		- uses the Post language file.
		- uses the Poll template (main sub template.).
		- user must have poll_edit_any/poll_add_any permission for the relevant
		  action.
		- otherwise must be poll starter with poll_edit_own permission for
		  editing, or be topic starter with poll_add_any permission for adding.
		- is accessed via ?action=editpoll.

	void EditPoll2()
		- is called to update the settings for a poll, or add a new one.
		- must be called with a topic specified in the URL.
		- user must have poll_edit_any/poll_add_any permission for the relevant
		  action.
		- otherwise must be poll starter with poll_edit_own permission for
		  editing, or be topic starter with poll_add_any permission for adding.
		- in the case of an error will redirect back to EditPoll and display
		  the relevant error message.
		- upon successful completion of action will direct user back to topic.
		- is accessed via ?action=editpoll2.

	void RemovePoll()
		- is called to remove a poll from a topic.
		- must be called with a topic specified in the URL.
		- user must have poll_remove_any permission.
		- otherwise must be poll starter with poll_remove_own permission.
		- upon successful completion of action will direct user back to topic.
		- is accessed via ?action=removepoll.
*/

// Allow the user to vote.
function Vote()
{
	global $topic, $txt, $db_prefix, $user_info, $smfFunc;

	// Make sure you can vote.
	isAllowedTo('poll_vote');

	// Even with poll_vote permission we would never be able to register you.
	if ($user_info['is_guest'])
		fatal_lang_error('cannot_poll_vote', 'permission');

	loadLanguage('Post');

	// Check if they have already voted, or voting is locked.
	$request = $smfFunc['db_query']('', "
		SELECT IFNULL(lp.id_choice, -1) AS selected, p.voting_locked, p.id_poll, p.expire_time, p.max_votes, p.change_vote
		FROM {$db_prefix}topics AS t
			INNER JOIN {$db_prefix}polls AS p ON (p.id_poll = t.id_poll)
			LEFT JOIN {$db_prefix}log_polls AS lp ON (p.id_poll = lp.id_poll AND lp.id_member = $user_info[id])
		WHERE t.id_topic = $topic
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('smf27', false);
	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// Is voting locked or has it expired?
	if (!empty($row['voting_locked']) || (!empty($row['expire_time']) && time() > $row['expire_time']))
		fatal_lang_error('smf27', false);

	// If they have already voted and aren't allowed to change their vote - hence they are outta here!
	if ($row['selected'] != -1 && empty($row['change_vote']))
		fatal_lang_error('smf27', false);
	// Otherwise if they can change their vote yet they haven't sent any options... remove their vote and redirect.
	elseif (!empty($row['change_vote']))
	{
		$pollOptions = array();

		// Find out what they voted for before.
		$request = $smfFunc['db_query']('', "
			SELECT id_choice
			FROM {$db_prefix}log_polls
			WHERE id_member = $user_info[id]
				AND id_poll = $row[id_poll]", __FILE__, __LINE__);
		while ($choice = $smfFunc['db_fetch_row']($request))
			$pollOptions[] = $choice[0];
		$smfFunc['db_free_result']($request);

		// Just skip it if they had voted for nothing before.
		if (!empty($pollOptions))
		{
			// Update the poll totals.
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}poll_choices
				SET votes = votes - 1
				WHERE id_poll = $row[id_poll]
					AND id_choice IN (" . implode(', ', $pollOptions) . ")
					AND votes > 0", __FILE__, __LINE__);

			// Delete off the log.
			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}log_polls
				WHERE id_member = $user_info[id]
					AND id_poll = $row[id_poll]", __FILE__, __LINE__);
		}

		// Redirect back to the topic so the user can vote again!
		if (empty($_POST['options']))
			redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
	}

	// Make sure the option(s) are valid.
	if (empty($_POST['options']))
		fatal_lang_error('smf26', false);

	// Too many options checked!
	if (count($_REQUEST['options']) > $row['max_votes'])
		fatal_lang_error('poll_error1', false, array($row['max_votes']));

	$pollOptions = array();
	$setString = '';
	foreach ($_REQUEST['options'] as $id)
	{
		$id = (int) $id;

		$pollOptions[] = $id;
		$setString .= "
				($row[id_poll], $user_info[id], $id),";
	}
	$setString = substr($setString, 0, -1);

	// Add their vote to the tally.
	$smfFunc['db_query']('', "
		INSERT IGNORE INTO {$db_prefix}log_polls
			(id_poll, id_member, id_choice)
		VALUES $setString", __FILE__, __LINE__);
	if (db_affected_rows() != 0)
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}poll_choices
			SET votes = votes + 1
			WHERE id_poll = $row[id_poll]
				AND id_choice IN (" . implode(', ', $pollOptions) . ")", __FILE__, __LINE__);

	// Return to the post...
	redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

// Lock the voting for a poll.
function LockVoting()
{
	global $topic, $db_prefix, $user_info, $smfFunc;

	checkSession('get');

	// Get the poll starter, ID, and whether or not it is locked.
	$request = $smfFunc['db_query']('', "
		SELECT t.id_member_started, t.id_poll, p.voting_locked
		FROM ({$db_prefix}topics AS t, {$db_prefix}polls AS p)
		WHERE t.id_topic = $topic
			AND p.id_poll = t.id_poll
		LIMIT 1", __FILE__, __LINE__);
	list ($memberID, $pollID, $voting_locked) = $smfFunc['db_fetch_row']($request);

	// If the user _can_ modify the poll....
	if (!allowedTo('poll_lock_any'))
		isAllowedTo('poll_lock_' . ($user_info['id'] == $memberID ? 'own' : 'any'));

	// It's been locked by a non-moderator.
	if ($voting_locked == '1')
		$voting_locked = '0';
	// Locked by a moderator, and this is a moderator.
	elseif ($voting_locked == '2' && allowedTo('moderate_board'))
		$voting_locked = '0';
	// Sorry, a moderator locked it.
	elseif ($voting_locked == '2' && !allowedTo('moderate_board'))
		fatal_lang_error('smf31', 'user');
	// A moderator *is* locking it.
	elseif ($voting_locked == '0' && allowedTo('moderate_board'))
		$voting_locked = '2';
	// Well, it's gonna be locked one way or another otherwise...
	else
		$voting_locked = '1';

	// Lock!  *Poof* - no one can vote.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}polls
		SET voting_locked = $voting_locked
		WHERE id_poll = $pollID", __FILE__, __LINE__);

	redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

// Ask what to change in a poll.
function EditPoll()
{
	global $txt, $db_prefix, $user_info, $context, $topic, $smfFunc;

	if (empty($topic))
		fatal_lang_error(1, false);

	loadLanguage('Post');
	loadTemplate('Poll');

	$context['can_moderate_poll'] = allowedTo('moderate_board');
	$context['start'] = (int) $_REQUEST['start'];
	$context['is_edit'] = isset($_REQUEST['add']) ? 0 : 1;

	// Check if a poll currently exists on this topic, and get the id, question and starter.
	$request = $smfFunc['db_query']('', "
		SELECT
			t.id_member_started, p.id_poll, p.question, p.hide_results, p.expire_time, p.max_votes, p.change_vote,
			p.id_member AS pollStarter
		FROM {$db_prefix}topics AS t
			LEFT JOIN {$db_prefix}polls AS p ON (p.id_poll = t.id_poll)
		WHERE t.id_topic = $topic
		LIMIT 1", __FILE__, __LINE__);

	// Assume the the topic exists, right?
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('smf232');
	// Get the poll information.
	$pollinfo = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// If we are adding a new poll - make sure that there isn't already a poll there.
	if (!$context['is_edit'] && !empty($pollinfo['id_poll']))
		fatal_lang_error('poll_already_exists');
	// Otherwise, if we're editing it, it does exist I assume?
	elseif ($context['is_edit'] && empty($pollinfo['id_poll']))
		fatal_lang_error('poll_not_found');

	// Can you do this?
	if ($context['is_edit'] && !allowedTo('poll_edit_any'))
		isAllowedTo('poll_edit_' . ($user_info['id'] == $pollinfo['id_member_started'] || ($pollinfo['pollStarter'] != 0 && $user_info['id'] == $pollinfo['pollStarter']) ? 'own' : 'any'));
	elseif (!$context['is_edit'] && !allowedTo('poll_add_any'))
		isAllowedTo('poll_add_' . ($user_info['id'] == $pollinfo['id_member_started'] ? 'own' : 'any'));

	// Want to make sure before you actually submit?  Must be a lot of options, or something.
	if (isset($_POST['preview']))
	{
		$question = $smfFunc['htmlspecialchars'](stripslashes($_POST['question']));

		// Basic theme info...
		$context['poll'] = array(
			'id' => $pollinfo['id_poll'],
			'question' => $question,
			'hide_results' => empty($_POST['poll_hide']) ? 0 : $_POST['poll_hide'],
			'change_vote' => isset($_POST['poll_change_vote']),
			'max_votes' => empty($_POST['poll_max_votes']) ? '1' : max(1, $_POST['poll_max_votes']),
		);

		// Start at number one with no last id to speak of.
		$number = 1;
		$last_id = 0;

		// Get all the choices - if this is an edit.
		if ($context['is_edit'])
		{
			$request = $smfFunc['db_query']('', "
				SELECT label, votes, id_choice
				FROM {$db_prefix}poll_choices
				WHERE id_poll = $pollinfo[id_poll]", __FILE__, __LINE__);
			$context['choices'] = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				// Get the highest id so we can add more without reusing.
				if ($row['id_choice'] >= $last_id)
					$last_id = $row['id_choice'] + 1;

				// They cleared this by either omitting it or emptying it.
				if (!isset($_POST['options'][$row['id_choice']]) || $_POST['options'][$row['id_choice']] == '')
					continue;

				censorText($row['label']);

				// Add the choice!
				$context['choices'][$row['id_choice']] = array(
					'id' => $row['id_choice'],
					'number' => $number++,
					'votes' => $row['votes'],
					'label' => $row['label'],
					'is_last' => false
				);
			}
			$smfFunc['db_free_result']($request);
		}

		// Work out how many options we have, so we get the 'is_last' field right...
		$totalPostOptions = 0;
		foreach ($_POST['options'] as $id => $label)
			if ($label != '')
				$totalPostOptions++;

		$count = 1;
		// If an option exists, update it.  If it is new, add it - but don't reuse ids!
		foreach ($_POST['options'] as $id => $label)
		{
			$label = stripslashes($smfFunc['htmlspecialchars']($label));
			censorText($label);

			if (isset($context['choices'][$id]))
				$context['choices'][$id]['label'] = $label;
			elseif ($label != '')
				$context['choices'][] = array(
					'id' => $last_id++,
					'number' => $number++,
					'label' => $label,
					'votes' => -1,
					'is_last' => $count++ == $totalPostOptions && $totalPostOptions > 1 ? true : false,
				);
		}

		// Make sure we have two choices for sure!
		if ($totalPostOptions < 2)
		{
			// Need two?
			if ($totalPostOptions == 0)
				$context['choices'][] = array(
					'id' => $last_id++,
					'number' => $number++,
					'label' => '',
					'votes' => -1,
					'is_last' => false
				);
			$poll_errors[] = 'poll_few';
		}

		// Always show one extra box...
		$context['choices'][] = array(
			'id' => $last_id++,
			'number' => $number++,
			'label' => '',
			'votes' => -1,
			'is_last' => true
		);

		if (allowedTo('moderate_board'))
			$context['poll']['expiration'] = $_POST['poll_expire'];

		// Check the question/option count for errors.
		if (trim($_POST['question']) == '' && empty($context['poll_error']))
			$poll_errors[] = 'no_question';

		// No check is needed, since nothing is really posted.
		checkSubmitOnce('free');

		// Take a check for any errors... assuming we haven't already done so!
		if (!empty($poll_errors) && empty($context['poll_error']))
		{
			loadLanguage('Errors');

			$context['poll_error'] = array('messages' => array());
			foreach ($poll_errors as $poll_error)
			{
				$context['poll_error'][$poll_error] = true;
				$context['poll_error']['messages'][] = $txt['error_' . $poll_error];
			}
		}
	}
	else
	{
		// Basic theme info...
		$context['poll'] = array(
			'id' => $pollinfo['id_poll'],
			'question' => $pollinfo['question'],
			'hide_results' => $pollinfo['hide_results'],
			'max_votes' => $pollinfo['max_votes'],
			'change_vote' => !empty($pollinfo['change_vote']),
		);

		// Poll expiration time?
		$context['poll']['expiration'] = empty($pollinfo['expire_time']) || !allowedTo('moderate_board') ? '' : ceil($pollinfo['expire_time'] <= time() ? -1 : ($pollinfo['expire_time'] - time()) / (3600 * 24));

		// Get all the choices - if this is an edit.
		if ($context['is_edit'])
		{
			$request = $smfFunc['db_query']('', "
				SELECT label, votes, id_choice
				FROM {$db_prefix}poll_choices
				WHERE id_poll = $pollinfo[id_poll]", __FILE__, __LINE__);
			$context['choices'] = array();
			$number = 1;
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				censorText($row['label']);

				$context['choices'][$row['id_choice']] = array(
					'id' => $row['id_choice'],
					'number' => $number++,
					'votes' => $row['votes'],
					'label' => $row['label'],
					'is_last' => false
				);
			}
			$smfFunc['db_free_result']($request);

			$last_id = max(array_keys($context['choices'])) + 1;

			// Add an extra choice...
			$context['choices'][] = array(
				'id' => $last_id,
				'number' => $number,
				'votes' => -1,
				'label' => '',
				'is_last' => true
			);
		}
		// New poll?
		else
		{
			// Setup the default poll options.
			$context['poll'] = array(
				'id' => 0,
				'question' => '',
				'hide_results' => 0,
				'max_votes' => 1,
				'change_vote' => 0,
				'expiration' => '',
			);

			// Make all five poll choices empty.
			$context['choices'] = array(
				array('id' => 0, 'number' => 1, 'votes' => -1, 'label' => '', 'is_last' => false),
				array('id' => 1, 'number' => 2, 'votes' => -1, 'label' => '', 'is_last' => false),
				array('id' => 2, 'number' => 3, 'votes' => -1, 'label' => '', 'is_last' => false),
				array('id' => 3, 'number' => 4, 'votes' => -1, 'label' => '', 'is_last' => false),
				array('id' => 4, 'number' => 5, 'votes' => -1, 'label' => '', 'is_last' => true)
			);
		}
	}
	$context['page_title'] = $context['is_edit'] ? $txt['smf39'] : $txt['add_poll'];

	// Build the link tree.
	$context['linktree'][] = array(
		'name' => $context['page_title']
	);

	// Register this form in the session variables.
	checkSubmitOnce('register');
}

// Change a poll...
function EditPoll2()
{
	global $txt, $topic, $board, $db_prefix, $context;
	global $modSettings, $user_info, $smfFunc;

	if (checkSession('post', '', false) != '')
		$poll_errors[] = 'session_timeout';

	if (isset($_POST['preview']))
		return EditPoll();

	// HACKERS (!!) can't edit :P.
	if (empty($topic))
		fatal_lang_error(1, false);

	// Is this a new poll, or editing an existing?
	$isEdit = isset($_REQUEST['add']) ? 0 : 1;

	// Get the starter and the poll's ID - if it's an edit.
	$request = $smfFunc['db_query']('', "
		SELECT t.id_member_started, t.id_poll, p.id_member AS pollStarter
		FROM {$db_prefix}topics AS t
			LEFT JOIN {$db_prefix}polls AS p ON (p.id_poll = t.id_poll)
		WHERE t.id_topic = $topic
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('smf232');
	$bcinfo = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// Check their adding/editing is valid.
	if (!$isEdit && !empty($bcinfo['id_poll']))
		fatal_lang_error('poll_already_exists');
	// Are we editing a poll which doesn't exist?
	elseif ($isEdit && empty($bcinfo['id_poll']))
		fatal_lang_error('poll_not_found');

	// Check if they have the power to add or edit the poll.
	if ($isEdit && !allowedTo('poll_edit_any'))
		isAllowedTo('poll_edit_' . ($user_info['id'] == $bcinfo['id_member_started'] || ($bcinfo['pollStarter'] != 0 && $user_info['id'] == $bcinfo['pollStarter']) ? 'own' : 'any'));
	elseif (!$isEdit && !allowedTo('poll_add_any'))
		isAllowedTo('poll_add_' . ($user_info['id'] == $bcinfo['id_member_started'] ? 'own' : 'any'));

	$optionCount = 0;
	// Ensure the user is leaving a valid amount of options - there must be at least two.
	foreach ($_POST['options'] as $k => $option)
	{
		if (trim($option) != '')
			$optionCount++;
	}
	if ($optionCount < 2)
		$poll_errors[] = 'poll_few';

	// Also - ensure they are not removing the question.
	if (trim($_POST['question']) == '')
		$poll_errors[] = 'no_question';

	// Got any errors to report?
	if (!empty($poll_errors))
	{
		loadLanguage('Errors');
		// Previewing.
		$_POST['preview'] = true;

		$context['poll_error'] = array('messages' => array());
		foreach ($poll_errors as $poll_error)
		{
			$context['poll_error'][$poll_error] = true;
			$context['poll_error']['messages'][] = $txt['error_' . $poll_error];
		}

		return EditPoll();
	}

	// Prevent double submission of this form.
	checkSubmitOnce('check');

	// Now we've done all our error checking, let's get the core poll information cleaned... question first.
	$_POST['question'] = $smfFunc['htmlspecialchars']($_POST['question']);

	$_POST['poll_hide'] = (int) $_POST['poll_hide'];
	$_POST['poll_change_vote'] = isset($_POST['poll_change_vote']) ? 1 : 0;

	// Ensure that the number options allowed makes sense, and the expiration date is valid.
	if (!$isEdit || allowedTo('moderate_board'))
	{
		if (empty($_POST['poll_expire']) && $_POST['poll_hide'] == 2)
			$_POST['poll_hide'] = 1;
		else
			$_POST['poll_expire'] = empty($_POST['poll_expire']) ? '0' : time() + $_POST['poll_expire'] * 3600 * 24;

		if (empty($_POST['poll_max_votes']) || $_POST['poll_max_votes'] <= 0)
			$_POST['poll_max_votes'] = 1;
		else
			$_POST['poll_max_votes'] = (int) $_POST['poll_max_votes'];
	}

	// If we're editing, let's commit the changes.
	if ($isEdit)
	{
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}polls
			SET question = '$_POST[question]', change_vote = $_POST[poll_change_vote]," . (allowedTo('moderate_board') ? "
				hide_results = $_POST[poll_hide], expire_time = $_POST[poll_expire], max_votes = $_POST[poll_max_votes]" : "
				hide_results = CASE WHEN expire_time = 0 AND $_POST[poll_hide] = 2 THEN 1 ELSE $_POST[poll_hide] END") . "
			WHERE id_poll = $bcinfo[id_poll]", __FILE__, __LINE__);
	}
	// Otherwise, let's get our poll going!
	else
	{
		// Create the poll.
		$smfFunc['db_query']('', "
			INSERT INTO {$db_prefix}polls
				(question, hide_results, max_votes, expire_time, id_member, poster_name, change_vote)
			VALUES (SUBSTRING('$_POST[question]', 1, 255), $_POST[poll_hide], $_POST[poll_max_votes], $_POST[poll_expire], $user_info[id], SUBSTRING('$user_info[username]', 1, 255), $_POST[poll_change_vote])", __FILE__, __LINE__);

		// Set the poll ID.
		$bcinfo['id_poll'] = db_insert_id("{$db_prefix}polls", 'id_poll');

		// Link the poll to the topic
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}topics
			SET id_poll = $bcinfo[id_poll]
			WHERE id_topic = $topic", __FILE__, __LINE__);
	}

	// Get all the choices.  (no better way to remove all emptied and add previously non-existent ones.)
	$request = $smfFunc['db_query']('', "
		SELECT id_choice
		FROM {$db_prefix}poll_choices
		WHERE id_poll = $bcinfo[id_poll]", __FILE__, __LINE__);
	$choices = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$choices[] = $row['id_choice'];
	$smfFunc['db_free_result']($request);

	$delete_options = array();
	foreach ($_POST['options'] as $k => $option)
	{
		// Make sure the key is numeric for sanity's sake.
		$k = (int) $k;

		// They've cleared the box.  Either they want it deleted, or it never existed.
		if (trim($option) == '')
		{
			// They want it deleted.  Bye.
			if (in_array($k, $choices))
				$delete_options[] = $k;

			// Skip the rest...
			continue;
		}

		// Dress the option up for its big date with the database.
		$option = $smfFunc['htmlspecialchars']($option);

		// If it's already there, update it.  If it's not... add it.
		if (in_array($k, $choices))
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}poll_choices
				SET label = '$option'
				WHERE id_poll = $bcinfo[id_poll]
					AND id_choice = $k", __FILE__, __LINE__);
		else
			$smfFunc['db_query']('', "
				INSERT INTO {$db_prefix}poll_choices
					(id_poll, id_choice, label, votes)
				VALUES ($bcinfo[id_poll], $k, SUBSTRING('$option', 1, 255), 0)", __FILE__, __LINE__);
	}

	// I'm sorry, but... well, no one was choosing you.  Poor options, I'll put you out of your misery.
	if (!empty($delete_options))
	{
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_polls
			WHERE id_poll = $bcinfo[id_poll]
				AND id_choice IN (" . implode(', ', $delete_options) . ")", __FILE__, __LINE__);
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}poll_choices
			WHERE id_poll = $bcinfo[id_poll]
				AND id_choice IN (" . implode(', ', $delete_options) . ")", __FILE__, __LINE__);
	}

	// Shall I reset the vote count, sir?
	if (isset($_POST['resetVoteCount']))
	{
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}poll_choices
			SET votes = 0
			WHERE id_poll = $bcinfo[id_poll]", __FILE__, __LINE__);
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_polls
			WHERE id_poll = $bcinfo[id_poll]", __FILE__, __LINE__);
	}

	// Off we go.
	redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

// Remove a poll from a topic without removing the topic.
function RemovePoll()
{
	global $topic, $db_prefix, $user_info, $smfFunc;

	// Make sure the topic is not empty.
	if (empty($topic))
		fatal_lang_error(1, false);

	// Check permissions.
	if (!allowedTo('poll_remove_any'))
	{
		$request = $smfFunc['db_query']('', "
			SELECT t.id_member_started, p.id_member AS pollStarter
			FROM ({$db_prefix}topics AS t, {$db_prefix}polls AS p)
			WHERE t.id_topic = $topic
				AND p.id_poll = t.id_poll
			LIMIT 1", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error(1);
		list ($topicStarter, $pollStarter) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		isAllowedTo('poll_remove_' . ($topicStarter == $user_info['id'] || ($pollStarter != 0 && $user_info['id'] == $pollStarter) ? 'own' : 'any'));
	}

	// Retrieve the poll ID.
	$request = $smfFunc['db_query']('', "
		SELECT id_poll
		FROM {$db_prefix}topics
		WHERE id_topic = $topic
		LIMIT 1", __FILE__, __LINE__);
	list ($pollID) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Remove all user logs for this poll.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_polls
		WHERE id_poll = $pollID", __FILE__, __LINE__);
	// Remove all poll choices.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}poll_choices
		WHERE id_poll = $pollID", __FILE__, __LINE__);
	// Remove the poll itself.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}polls
		WHERE id_poll = $pollID", __FILE__, __LINE__);
	// Finally set the topic poll ID back to 0!
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}topics
		SET id_poll = 0
		WHERE id_topic = $topic", __FILE__, __LINE__);

	// Take the moderator back to the topic.
	redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

?>