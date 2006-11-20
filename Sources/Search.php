<?php
/******************************************************************************
* Search.php                                                                  *
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

/*	These functions are here for searching, and they are:

	void PlushSearch1()
		- shows the screen to search forum posts (action=search), and uses the
		  simple version if the simpleSearch setting is enabled.
		- uses the main sub template of the Search template.
		- uses the Search language file.
		- requires the search_posts permission.
		- decodes and loads search parameters given in the URL (if any).
		- the form redirects to index.php?action=search2.

	void PlushSearch2()
		- checks user input and searches the messages table for messages
		  matching the query.
		- requires the search_posts permission.
		- uses the results sub template of the Search template.
		- uses the Search language file.
		- stores the results into the search cache.
		- show the results of the search query.

	array prepareSearchContext(bool reset = false)
		- callback function for the results sub template.
		- loads the necessary contextual data to show a search result.

	int searchSort(string $wordA, string $wordB)
		- callback function for usort used to sort the fulltext results.
		- the order of sorting is: large words, small words, large words that
		  are excluded from the search, small words that are excluded.
*/

// Ask the user what they want to search for.
function PlushSearch1()
{
	global $txt, $scripturl, $db_prefix, $modSettings, $user_info, $context, $smfFunc;

	// Is the load average too high to allow searching just now?
	if (!empty($context['load_average']) && !empty($modSettings['loadavg_search']) && $context['load_average'] >= $modSettings['loadavg_search'])
		fatal_lang_error('loadavg_search_disabled', false);

	loadLanguage('Search');
	loadTemplate('Search');

	// Check the user's permissions.
	isAllowedTo('search_posts');

	// Link tree....
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=search',
		'name' => $txt['search']
	);

	// If you got back from search2 by using the linktree, you get your original search parameters back.
	if (isset($_REQUEST['params']))
	{
		$temp_params = explode('|"|', base64_decode(strtr($_REQUEST['params'], array(' ' => '+'))));
		$context['search_params'] = array();
		foreach ($temp_params as $i => $data)
		{
			@list ($k, $v) = explode('|\'|', $data);
			$context['search_params'][$k] = stripslashes($v);
		}
		if (isset($context['search_params']['brd']))
			$context['search_params']['brd'] = $context['search_params']['brd'] == '' ? array() : explode(',', $context['search_params']['brd']);
	}
	if (isset($_REQUEST['search']))
		$context['search_params']['search'] = stripslashes(un_htmlspecialchars($_REQUEST['search']));

	if (isset($context['search_params']['search']))
		$context['search_params']['search'] = htmlspecialchars($context['search_params']['search']);
	if (isset($context['search_params']['userspec']))
		$context['search_params']['userspec'] = htmlspecialchars(stripslashes($context['search_params']['userspec']));
	if (!empty($context['search_params']['searchtype']))
		$context['search_params']['searchtype'] = 2;
	if (!empty($context['search_params']['minage']))
		$context['search_params']['minage'] = (int) $context['search_params']['minage'];
	if (!empty($context['search_params']['maxage']))
		$context['search_params']['maxage'] = (int) $context['search_params']['maxage'];

	$context['search_params']['show_complete'] = !empty($context['search_params']['show_complete']);
	$context['search_params']['subject_only'] = !empty($context['search_params']['subject_only']);

	// Load the error text strings if there were errors in the search.
	if (!empty($context['search_errors']))
	{
		loadLanguage('Errors');
		$context['search_errors']['messages'] = array();
		foreach ($context['search_errors'] as $search_error => $dummy)
		{
			if ($search_error === 'messages')
				continue;

			$context['search_errors']['messages'][] = $txt['error_' . $search_error];
		}
	}

	// Find all the boards this user is allowed to see.
	$request = $smfFunc['db_query']('', "
		SELECT b.id_cat, c.name AS cat_name, b.id_board, b.name, b.child_level
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		WHERE $user_info[query_see_board]", __FILE__, __LINE__);
	$context['num_boards'] = $smfFunc['db_num_rows']($request);
	$context['categories'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// This category hasn't been set up yet..
		if (!isset($context['categories'][$row['id_cat']]))
			$context['categories'][$row['id_cat']] = array(
				'id' => $row['id_cat'],
				'name' => $row['cat_name'],
				'boards' => array()
			);

		// Set this board up, and let the template know when it's a child.  (indent them..)
		$context['categories'][$row['id_cat']]['boards'][$row['id_board']] = array(
			'id' => $row['id_board'],
			'name' => $row['name'],
			'child_level' => $row['child_level'],
			'selected' => (empty($context['search_params']['brd']) && (empty($modSettings['recycle_enable']) || $row['id_board'] != $modSettings['recycle_board']) && !in_array($row['id_board'], $user_info['ignoreboards'])) || (!empty($context['search_params']['brd']) && in_array($row['id_board'], $context['search_params']['brd']))
		);
	}
	$smfFunc['db_free_result']($request);

	// Now, let's sort the list of categories into the boards for templates that like that.
	$temp_boards = array();
	foreach ($context['categories'] as $category)
	{
		$temp_boards[] = array(
			'name' => $category['name'],
			'child_ids' => array_keys($category['boards'])
		);
		$temp_boards = array_merge($temp_boards, array_values($category['boards']));
	}

	$max_boards = ceil(count($temp_boards) / 2);
	if ($max_boards == 1)
		$max_boards = 2;

	// Now, alternate them so they can be shown left and right ;).
	$context['board_columns'] = array();
	for ($i = 0; $i < $max_boards; $i++)
	{
		$context['board_columns'][] = $temp_boards[$i];
		if (isset($temp_boards[$i + $max_boards]))
			$context['board_columns'][] = $temp_boards[$i + $max_boards];
		else
			$context['board_columns'][] = array();
	}

	if (!empty($_REQUEST['topic']))
	{
		$context['search_params']['topic'] = (int) $_REQUEST['topic'];
		$context['search_params']['show_complete'] = true;
	}
	if (!empty($context['search_params']['topic']))
	{
		$context['search_params']['topic'] = (int) $context['search_params']['topic'];

		$context['search_topic'] = array(
			'id' => $context['search_params']['topic'],
			'href' => $scripturl . '?topic=' . $context['search_params']['topic'] . '.0',
		);

		$request = $smfFunc['db_query']('', "
			SELECT ms.subject
			FROM ({$db_prefix}topics AS t, {$db_prefix}boards AS b, {$db_prefix}messages AS ms)
			WHERE b.id_board = t.id_board
				AND t.id_topic = " . $context['search_params']['topic'] . "
				AND ms.id_msg = t.id_first_msg
				AND $user_info[query_see_board]
				AND t.approved = 1
			LIMIT 1", __FILE__, __LINE__);

		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('topic_gone', false);

		list ($context['search_topic']['subject']) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		$context['search_topic']['link'] = '<a href="' . $context['search_topic']['href'] . '">' . $context['search_topic']['subject'] . '</a>';
	}

	// Simple or not?
	$context['simple_search'] = isset($context['search_params']['advanced']) ? empty($context['search_params']['advanced']) : !empty($modSettings['simpleSearch']) && !isset($_REQUEST['advanced']);
	$context['page_title'] = $txt[183];
}

// Gather the results and show them.
function PlushSearch2()
{
	global $scripturl, $modSettings, $sourcedir, $txt, $db_prefix, $db_connection;
	global $user_info, $context, $options, $messages_request, $boards_can;
	global $excludedWords, $participants, $smfFunc;

	// !!! Add spam protection.

	if (!empty($context['load_average']) && !empty($modSettings['loadavg_search']) && $context['load_average'] >= $modSettings['loadavg_search'])
		fatal_lang_error('loadavg_search_disabled', false);

	// No, no, no... this is a bit hard on the server, so don't you go prefetching it!
	if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
	{
		ob_end_clean();
		header('HTTP/1.1 403 Forbidden');
		die;
	}

	$weight_factors = array(
		'frequency',
		'age',
		'length',
		'subject',
		'first_message',
		'sticky',
	);

	$weight = array();
	$weight_total = 0;
	foreach ($weight_factors as $weight_factor)
	{
		$weight[$weight_factor] = empty($modSettings['search_weight_' . $weight_factor]) ? 0 : (int) $modSettings['search_weight_' . $weight_factor];
		$weight_total += $weight[$weight_factor];
	}

	// Zero weight.  Weightless :P.
	if (empty($weight_total))
		fatal_lang_error('search_invalid_weights');

	// These vars don't require an interface, the're just here for tweaking.
	$recentPercentage = 0.30;
	$humungousTopicPosts = 200;
	$maxMembersToSearch = 500;
	$maxMessageResults = empty($modSettings['search_max_results']) ? 0 : $modSettings['search_max_results'] * 5;

	// Start with no errors.
	$context['search_errors'] = array();

	// Number of pages hard maximum - normally not set at all.
	$modSettings['search_max_results'] = empty($modSettings['search_max_results']) ? 200 * $modSettings['search_results_per_page'] : (int) $modSettings['search_max_results'];

	loadLanguage('Search');
	if (!isset($_REQUEST['xml']))
		loadTemplate('Search');

	// Are you allowed?
	isAllowedTo('search_posts');

	require_once($sourcedir . '/Display.php');

	if (!empty($modSettings['search_index']) && $modSettings['search_index'] == 'fulltext')
	{
		// Try to determine the minimum number of letters for a fulltext search.
		$request = $smfFunc['db_query']('', "
			SHOW VARIABLES
			LIKE 'ft_min_word_len'", false, false);
		if ($request !== false && $smfFunc['db_num_rows']($request) == 1)
		{
			list (, $min_word_length) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);
		}
		// 4 is the MySQL default...
		else
			$min_word_length = '4';

		// Some MySQL versions are superior to others :P.
		$canDoBooleanSearch = version_compare($smfFunc['db_server_info']($db_connection), '4.0.1', '>=') == 1;

		// Get a list of banned fulltext words.
		$banned_words = empty($modSettings['search_banned_words']) ? array() : explode(',', addslashes($modSettings['search_banned_words']));
	}
	elseif (!empty($modSettings['search_index']) && $modSettings['search_index'] == 'custom' && !empty($modSettings['search_custom_index_config']))
	{
		$customIndexSettings = unserialize($modSettings['search_custom_index_config']);

		$min_word_length = $customIndexSettings['bytes_per_word'];
		$banned_words = empty($modSettings['search_stopwords']) ? array() : explode(',', addslashes($modSettings['search_stopwords']));
	}
	else
		$modSettings['search_index'] = '';

	// $search_params will carry all settings that differ from the default search parameters.
	// That way, the URLs involved in a search page will be kept as short as possible.
	$search_params = array();

	if (isset($_REQUEST['params']))
	{
		$temp_params = explode('|"|', base64_decode(strtr($_REQUEST['params'], array(' ' => '+'))));
		foreach ($temp_params as $i => $data)
		{
			@list ($k, $v) = explode('|\'|', $data);
			$search_params[$k] = stripslashes($v);
		}
		if (isset($search_params['brd']))
			$search_params['brd'] = empty($search_params['brd']) ? array() : explode(',', $search_params['brd']);
	}

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

	// Searching a specific topic?
	if (!empty($_REQUEST['topic']))
	{
		$search_params['topic'] = (int) $_REQUEST['topic'];
		$search_params['show_complete'] = true;
	}
	elseif (!empty($search_params['topic']))
		$search_params['topic'] = (int) $search_params['topic'];

	if (!empty($search_params['minage']) || !empty($search_params['maxage']))
	{
		$request = $smfFunc['db_query']('', "
			SELECT " . (empty($search_params['maxage']) ? '0, ' : 'IFNULL(MIN(id_msg), -1), ') . (empty($search_params['minage']) ? '0' : 'IFNULL(MAX(id_msg), -1)') . "
			FROM {$db_prefix}messages
			WHERE " . (empty($search_params['minage']) ? '1' : 'poster_time <= ' . (time() - 86400 * $search_params['minage'])) . (empty($search_params['maxage']) ? '' : "
				AND poster_time >= " . (time() - 86400 * $search_params['maxage'])) . "
				AND approved = 1", __FILE__, __LINE__);
		list ($minMsgID, $maxMsgID) = $smfFunc['db_fetch_row']($request);
		if ($minMsgID < 0 || $maxMsgID < 0)
			$context['search_errors']['no_messages_in_time_frame'] = true;
		$smfFunc['db_free_result']($request);
	}

	// Default the user name to a wildcard matching every user (*).
	if (!empty($search_params['userspec']) || (!empty($_REQUEST['userspec']) && $_REQUEST['userspec'] != '*'))
		$search_params['userspec'] = isset($search_params['userspec']) ? $search_params['userspec'] : $_REQUEST['userspec'];

	// If there's no specific user, then don't mention it in the main query.
	if (empty($search_params['userspec']))
		$userQuery = '';
	else
	{
		$userString = strtr($smfFunc['htmlspecialchars'](stripslashes($search_params['userspec']), ENT_QUOTES), array('&quot;' => '"'));
		$userString = strtr($userString, array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_'));

		preg_match_all('~"([^"]+)"~', $userString, $matches);
		$possible_users = array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $userString)));

		for ($k = 0, $n = count($possible_users); $k < $n; $k++)
		{
			$possible_users[$k] = trim($possible_users[$k]);

			if (strlen($possible_users[$k]) == 0)
				unset($possible_users[$k]);
		}

		// Retrieve a list of possible members.
		$request = $smfFunc['db_query']('', "
			SELECT id_member
			FROM {$db_prefix}members
			WHERE real_name LIKE '" . implode("' OR real_name LIKE '", $possible_users) . "'", __FILE__, __LINE__);
		// Simply do nothing if there're too many members matching the criteria.
		if ($smfFunc['db_num_rows']($request) > $maxMembersToSearch)
			$userQuery = '';
		elseif ($smfFunc['db_num_rows']($request) == 0)
			$userQuery = "m.id_member = 0 AND (m.poster_name LIKE '" . implode("' OR m.poster_name LIKE '", $possible_users) . "')";
		else
		{
			$memberlist = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$memberlist[] = $row['id_member'];
			$userQuery = "(m.id_member IN (" . implode(', ', $memberlist) . ") OR (m.id_member = 0 AND (m.poster_name LIKE '" . implode("' OR m.poster_name LIKE '", $possible_users) . "')))";
		}
		$smfFunc['db_free_result']($request);
	}

	// If the boards were passed by URL (params=), temporarily put them back in $_REQUEST.
	if (!empty($search_params['brd']) && is_array($search_params['brd']))
		$_REQUEST['brd'] = $search_params['brd'];

	// Make sure all boards are integers.
	if (!empty($_REQUEST['brd']))
		foreach ($_REQUEST['brd'] as $id => $brd)
			$_REQUEST['brd'][$id] = (int) $brd;

	// Special case for boards: searching just one topic?
	if (!empty($search_params['topic']))
	{
		$request = $smfFunc['db_query']('', "
			SELECT b.id_board
			FROM ({$db_prefix}topics AS t, {$db_prefix}boards AS b)
			WHERE b.id_board = t.id_board
				AND t.id_topic = " . $search_params['topic'] . "
				AND $user_info[query_see_board]
				AND t.approved = 1
			LIMIT 1", __FILE__, __LINE__);

		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('topic_gone', false);

		$search_params['brd'] = array();
		list ($search_params['brd'][0]) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
	}
	// Select all boards you've selected AND are allowed to see.
	elseif ($user_info['is_admin'] && (!empty($search_params['advanced']) || !empty($_REQUEST['brd'])))
		$search_params['brd'] = empty($_REQUEST['brd']) ? array() : $_REQUEST['brd'];
	else
	{
		$see_board = empty($search_parms['advanced']) ? 'query_wanna_see_board' : 'query_see_board';
		$request = $smfFunc['db_query']('', "
			SELECT b.id_board
			FROM {$db_prefix}boards AS b
			WHERE $user_info[$see_board]" . (empty($_REQUEST['brd']) ? (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
				AND b.id_board != $modSettings[recycle_board]" : '') : "
				AND b.id_board IN (" . implode(', ', $_REQUEST['brd']) . ")"), __FILE__, __LINE__);
		$search_params['brd'] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$search_params['brd'][] = $row['id_board'];
		$smfFunc['db_free_result']($request);

		// This error should pro'bly only happen for hackers.
		if (empty($search_params['brd']))
			$context['search_errors']['no_boards_selected'] = true;
	}

	if (count($search_params['brd']) != 0)
	{
		// If we've selected all boards, this parameter can be left empty.
		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}boards", __FILE__, __LINE__);
		list ($num_boards) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		if (count($search_params['brd']) == $num_boards)
			$boardQuery = '';
		elseif (count($search_params['brd']) == $num_boards - 1 && !empty($modSettings['recycle_board']) && !in_array($modSettings['recycle_board'], $search_params['brd']))
			$boardQuery = '!= ' . $modSettings['recycle_board'];
		else
			$boardQuery = 'IN (' . implode(', ', $search_params['brd']) . ')';
	}
	else
		$boardQuery = '';

	

	$search_params['show_complete'] = !empty($search_params['show_complete']) || !empty($_REQUEST['show_complete']);
	$search_params['subject_only'] = !empty($search_params['subject_only']) || !empty($_REQUEST['subject_only']);

	$context['compact'] = !$search_params['show_complete'];

	// Get the sorting parameters right. Default to sort by relevance descending.
	$sort_columns = array(
		'relevance',
		'num_replies',
		'id_msg',
	);
	if (empty($search_params['sort']) && !empty($_REQUEST['sort']))
		list ($search_params['sort'], $search_params['sort_dir']) = array_pad(explode('|', $_REQUEST['sort']), 2, '');
	$search_params['sort'] = !empty($search_params['sort']) && in_array($search_params['sort'], $sort_columns) ? $search_params['sort'] : 'relevance';
	if (!empty($search_params['topic']) && $search_params['sort'] === 'num_replies')
		$search_params['sort'] = 'id_msg';

	// Sorting direction: descending unless stated otherwise.
	$search_params['sort_dir'] = !empty($search_params['sort_dir']) && $search_params['sort_dir'] == 'asc' ? 'asc' : 'desc';

	// Determine some values needed to calculate the relevance.
	$minMsg = (int) ((1 - $recentPercentage) * $modSettings['maxMsgID']);
	$recentMsg = $modSettings['maxMsgID'] - $minMsg;


	// *** Parse the search query

	// Unfortunately, searching for words like this is going to be slow, so we're blacklisting them.
	// !!! Setting to add more here?
	// !!! Maybe only blacklist if they are the only word, or "any" is used?
	$blacklisted_words = array('img', 'url', 'quote', 'www', 'http', 'the', 'is', 'it', 'are', 'if');

	// What are we searching for?
	if (empty($search_params['search']))
	{
		if (isset($_GET['search']))
			$search_params['search'] = un_htmlspecialchars($_GET['search']);
		elseif (isset($_POST['search']))
			$search_params['search'] = stripslashes($_POST['search']);
		else
			$search_params['search'] = '';
	}

	// Nothing??
	if (!isset($search_params['search']) || $search_params['search'] == '')
		$context['search_errors']['invalid_search_string'] = true;

	// Change non-word characters into spaces.
	$stripped_query = preg_replace('~([\x0B\0' . ($context['utf8'] ? ($context['server']['complex_preg_chars'] ? '\x{C2A0}' : chr(0xC2) . chr(0xA0)) : '\xA0') . '\t\r\s\n(){}\\[\\]<>!@$%^*.,:+=`\~\?/\\\\]|&(amp|lt|gt|quot);)+~' . ($context['utf8'] ? 'u' : ''), ' ', $search_params['search']);

	// Make the query lower case. It's gonna be case insensitive anyway.
	$stripped_query = un_htmlspecialchars($smfFunc['strtolower']($stripped_query));

	// This (hidden) setting will do fulltext searching in the most basic way.
	if (!empty($modSettings['search_simple_fulltext']))
		$stripped_query = strtr($stripped_query, array('"' => ''));

	$no_regexp = preg_match('~&#(\d{1,7}|x[0-9a-fA-F]{1,6});~', $stripped_query) === 1;

	// Extract phrase parts first (e.g. some words "this is a phrase" some more words.)
	preg_match_all('/(?:^|\s)([-]?)"([^"]+)"(?:$|\s)/', $stripped_query, $matches, PREG_PATTERN_ORDER);
	$phraseArray = $matches[2];

	// Remove the phrase parts and extract the words.
	$wordArray = explode(' ', preg_replace('~(?:^|\s)([-]?)"([^"]+)"(?:$|\s)~' . ($context['utf8'] ? 'u' : ''), ' ', $search_params['search']));

	// A minus sign in front of a word excludes the word.... so...
	$excludedWords = array();
	$excludedIndexWords = array();
	$excludedSubjectWords = array();
	$excludedPhrases = array();

	// .. first, we check for things like -"some words", but not "-some words".
	foreach ($matches[1] as $index => $word)
		if ($word === '-')
		{
			if (($word = trim($phraseArray[$index], '-_\' ')) !== '' && !in_array($word, $blacklisted_words))
				$excludedWords[] = addslashes($word);
			unset($phraseArray[$index]);
		}

	// Now we look for -test, etc.... normaller.
	foreach ($wordArray as $index => $word)
		if (strpos(trim($word), '-') === 0)
		{
			if (($word = trim($word, '-_\' ')) !== '' && !in_array($word, $blacklisted_words))
				$excludedWords[] = addslashes($word);
			unset($wordArray[$index]);
		}

	// The remaining words and phrases are all included.
	$searchArray = array_merge($phraseArray, $wordArray);

	// Trim everything and make sure there are no words that are the same.
	foreach ($searchArray as $index => $value)
	{
		if (($searchArray[$index] = trim($value, '-_\' ')) === '' || in_array($searchArray[$index], $blacklisted_words))
			unset($searchArray[$index]);
		else
			$searchArray[$index] = addslashes($searchArray[$index]);
	}
	$searchArray = array_slice(array_unique($searchArray), 0, 10);

	// Create an array of replacements for highlighting.
	$context['mark'] = array();
	foreach ($searchArray as $word)
		$context['mark'][$word] = '<b class="highlight">' . $word . '</b>';

	// Initialize two arrays storing the words that have to be searched for.
	$orParts = array();
	$searchWords = array();

	// Make sure at least one word is being searched for.
	if (empty($searchArray))
		$context['search_errors']['invalid_search_string'] = true;
	// All words/sentences must match.
	elseif (empty($search_params['searchtype']))
		$orParts[0] = $searchArray;
	// Any word/sentence must match.
	else
		foreach ($searchArray as $index => $value)
			$orParts[$index] = array($value);

	// Make sure the excluded words are in all or-branches.
	foreach ($orParts as $orIndex => $andParts)
		foreach ($excludedWords as $word)
			$orParts[$orIndex][] = $word;

	// Determine the or-branches and the fulltext search words.
	foreach ($orParts as $orIndex => $andParts)
	{
		$searchWords[$orIndex] = array(
			'indexed_words' => array(),
			'words' => array(),
			'subject_words' => array(),
			'all_words' => array(),
		);

		// Sort the indexed words (large words -> small words -> excluded words).
		if (!empty($modSettings['search_index']))
			usort($orParts[$orIndex], 'searchSort');

		foreach ($orParts[$orIndex] as $word)
		{
			$is_excluded = in_array($word, $excludedWords);

			$searchWords[$orIndex]['all_words'][] = $word;

			$subjectWords = text2words(stripslashes($word));
			if (!$is_excluded || count($subjectWords) === 1)
			{
				$searchWords[$orIndex]['subject_words'] = array_merge($searchWords[$orIndex]['subject_words'], $subjectWords);
				if ($is_excluded)
					$excludedSubjectWords = array_merge($excludedSubjectWords, $subjectWords);
			}
			else
				$excludedPhrases[] = $word;

			if (!empty($modSettings['search_index']))
			{
				$subwords = text2words(stripslashes($word), $modSettings['search_index'] === 'fulltext' ? null : $min_word_length, $modSettings['search_index'] === 'custom');

				if (($modSettings['search_index'] === 'custom' || ($modSettings['search_index'] === 'fulltext' && !$canDoBooleanSearch && count($subwords) > 1)) && empty($modSettings['search_force_index']))
					$searchWords[$orIndex]['words'][] = $word;

				if ($modSettings['search_index'] === 'fulltext' && $canDoBooleanSearch)
				{
					$fulltextWord = count($subwords) === 1 ? $word : '"' . $word . '"';
					$searchWords[$orIndex]['indexed_words'][] = $fulltextWord;
					if ($is_excluded)
						$excludedIndexWords[] = $fulltextWord;
				}

				// Excluded phrases don't benefit from being split into subwords.
				elseif (count($subwords) > 1 && $is_excluded)
					continue;

				else
				{
					$relyOnIndex = true;
					foreach ($subwords as $subword)
					{
						if (($modSettings['search_index'] === 'custom' || strlen(stripslashes($subword)) >= $min_word_length) && !in_array($subword, $banned_words))
						{
							$searchWords[$orIndex]['indexed_words'][] = $subword;
							if ($is_excluded)
								$excludedIndexWords[] = $subword;
						}
						elseif (!in_array($subword, $banned_words))
							$relyOnIndex = false;
					}

					if ($modSettings['search_index'] === 'fulltext' && $canDoBooleanSearch && !$relyOnIndex && empty($modSettings['search_force_index']))
						$searchWords[$orIndex]['words'][] = $word;
				}
			}
		}

		// Search_force_index requires all AND parts to have at least one fulltext word.
		if (!empty($modSettings['search_force_index']) && empty($searchWords[$orIndex]['indexed_words']))
		{
			$context['search_errors']['query_not_specific_enough'] = true;
			break;
		}

		// Make sure we aren't searching for too many indexed words.
		else
		{
			$searchWords[$orIndex]['indexed_words'] = array_slice($searchWords[$orIndex]['indexed_words'], 0, 7);
			$searchWords[$orIndex]['subject_words'] = array_slice($searchWords[$orIndex]['subject_words'], 0, 7);
		}
	}

	// *** Spell checking
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	if ($context['show_spellchecking'])
	{
		// Windows fix.
		ob_start();
		$old = error_reporting(0);
		pspell_new('en');
		$pspell_link = pspell_new($txt['lang_dictionary'], $txt['lang_spelling'], '', strtr($txt['lang_character_set'], array('iso-' => 'iso', 'ISO-' => 'iso')), PSPELL_FAST | PSPELL_RUN_TOGETHER);
		error_reporting($old);

		if (!$pspell_link)
			$pspell_link = pspell_new('en', '', '', '', PSPELL_FAST | PSPELL_RUN_TOGETHER);

		ob_end_clean();

		$did_you_mean = array('search' => array(), 'display' => array());
		$found_misspelling = false;
		foreach ($searchArray as $word)
		{
			if (empty($pspell_link))
				continue;

			$word = stripslashes($word);
			// Don't check phrases.
			if (preg_match('~^\w+$~', $word) === 0)
			{
				$did_you_mean['search'][] = '"' . $word . '"';
				$did_you_mean['display'][] = '&quot;' . $smfFunc['htmlspecialchars']($word) . '&quot;';
				continue;
			}
			// For some strange reason spell check can crash PHP on decimals.
			elseif (preg_match('~\d~', $word) === 1)
			{
				$did_you_mean['search'][] = $word;
				$did_you_mean['display'][] = $smfFunc['htmlspecialchars']($word);
				continue;
			}
			elseif (pspell_check($pspell_link, $word))
			{
				$did_you_mean['search'][] = $word;
				$did_you_mean['display'][] = $smfFunc['htmlspecialchars']($word);
				continue;
			}

			$suggestions = pspell_suggest($pspell_link, $word);
			foreach ($suggestions as $i => $s)
			{
				// Search is case insensitive.
				if ($smfFunc['strtolower']($s) == $smfFunc['strtolower']($word))
					unset($suggestions[$i]);
			}

			// Anything found?  If so, correct it!
			if (!empty($suggestions))
			{
				$suggestions = array_values($suggestions);
				$did_you_mean['search'][] = $suggestions[0];
				$did_you_mean['display'][] = '<em><b>' . $smfFunc['htmlspecialchars']($suggestions[0]) . '</b></em>';
				$found_misspelling = true;
			}
			else
			{
				$did_you_mean['search'][] = $word;
				$did_you_mean['display'][] = $smfFunc['htmlspecialchars']($word);
			}
		}

		if ($found_misspelling)
		{
			// Don't spell check excluded words, but add them still...
			$temp_excluded = array('search' => array(), 'display' => array());
			foreach ($excludedWords as $word)
			{
				$word = stripslashes($word);

				if (preg_match('~^\w+$~', $word) == 0)
				{
					$temp_excluded['search'][] = '-"' . $word . '"';
					$temp_excluded['display'][] = '-&quot;' . $smfFunc['htmlspecialchars']($word) . '&quot;';
				}
				else
				{
					$temp_excluded['search'][] = '-' . $word;
					$temp_excluded['display'][] = '-' . $smfFunc['htmlspecialchars']($word);
				}
			}

			$did_you_mean['search'] = array_merge($did_you_mean['search'], $temp_excluded['search']);
			$did_you_mean['display'] = array_merge($did_you_mean['display'], $temp_excluded['display']);

			$temp_params = $search_params;
			$temp_params['search'] = implode(' ', $did_you_mean['search']);
			if (isset($temp_params['brd']))
				$temp_params['brd'] = implode(',', $temp_params['brd']);
			$context['params'] = array();
			foreach ($temp_params as $k => $v)
				$context['did_you_mean_params'][] = $k . '|\'|' . addslashes($v);
			$context['did_you_mean_params'] = base64_encode(implode('|"|', $context['did_you_mean_params']));
			$context['did_you_mean'] = implode(' ', $did_you_mean['display']);
		}
	}

	// Let the user adjust the search query, should they wish?
	$context['search_params'] = $search_params;
	if (isset($context['search_params']['search']))
		$context['search_params']['search'] = $smfFunc['htmlspecialchars']($context['search_params']['search']);
	if (isset($context['search_params']['userspec']))
		$context['search_params']['userspec'] = $smfFunc['htmlspecialchars']($context['search_params']['userspec']);


	// *** Encode all search params

	// All search params have been checked, let's compile them to a single string... made less simple by PHP 4.3.9 and below.
	$temp_params = $search_params;
	if (isset($temp_params['brd']))
		$temp_params['brd'] = implode(',', $temp_params['brd']);
	$context['params'] = array();
	foreach ($temp_params as $k => $v)
		$context['params'][] = $k . '|\'|' . addslashes($v);
	$context['params'] = base64_encode(implode('|"|', $context['params']));

	// ... and add the links to the link tree.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=search;params=' . $context['params'],
		'name' => $txt['search']
	);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=search2;params=' . $context['params'],
		'name' => $txt['search_results']
	);


	// *** A last error check

	// One or more search errors? Go back to the first search screen.
	if (!empty($context['search_errors']))
	{
		$_REQUEST['params'] = $context['params'];
		return PlushSearch1();
	}


/*	// !!! This doesn't seem too urgent anymore. Can we remove it?
	if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
	{
		// !!! Change error message...
		if (cache_get_data('search_start:' . ($user_info['is_guest'] ? $user_info['ip'] : $user_info['id']), 90) == 1)
			fatal_lang_error('loadavg_search_disabled', false);
		cache_put_data('search_start:' . ($user_info['is_guest'] ? $user_info['ip'] : $user_info['id']), 1, 90);
	}*/

	// *** Reserve an ID for caching the search results.

	// Update the cache if the current search term is not yet cached.
	$update_cache = empty($_SESSION['search_cache']) || ($_SESSION['search_cache']['params'] != $context['params']);
	if ($update_cache)
	{
		// Increase the pointer...
		$modSettings['search_pointer'] = empty($modSettings['search_pointer']) ? 0 : (int) $modSettings['search_pointer'];
		// ...and store it right off.
		updateSettings(array('search_pointer' => $modSettings['search_pointer'] >= 255 ? 0 : $modSettings['search_pointer'] + 1));
		// As long as you don't change the parameters, the cache result is yours.
		$_SESSION['search_cache'] = array(
			'id_search' => $modSettings['search_pointer'],
			'num_results' => -1,
			'params' => $context['params'],
		);

		// Clear the previous cache of the final results cache.
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_search_results
			WHERE id_search = " . $_SESSION['search_cache']['id_search'], __FILE__, __LINE__);

		if ($search_params['subject_only'])
		{
			foreach ($searchWords as $orIndex => $words)
			{
				$subject_query = array(
					'from' => array(
						"{$db_prefix}topics AS t",
					),
					'left_join' => array(),
					'where' => array(
						't.approved = 1',
					),
				);

				$numTables = 0;
				$prev_join = 0;
				$numSubjectResults = 0;
				foreach ($words['subject_words'] as $subjectWord)
				{
					$numTables++;
					if (in_array($subjectWord, $excludedSubjectWords))
					{
						$subject_query['left_join'][] = "{$db_prefix}log_search_subjects AS subj$numTables ON (subj$numTables.word " . (empty($modSettings['search_match_words']) ? "LIKE '%$subjectWord%'" : "= '$subjectWord'") . " AND subj$numTables.id_topic = t.id_topic)";
						$subject_query['where'][] = "(subj$numTables.word IS NULL)";
					}
					else
					{
						$subject_query['from'][] = "{$db_prefix}log_search_subjects AS subj$numTables";
						$subject_query['where'][] = "subj$numTables.word " . (empty($modSettings['search_match_words']) ? "LIKE '%$subjectWord%'" : "= '$subjectWord'");
						$subject_query['where'][] = "subj$numTables.id_topic = " . ($prev_join === 0 ? 't' : 'subj' . $prev_join) . '.id_topic';
						$prev_join = $numTables;
					}
				}

				if (!empty($userQuery))
				{
					if (!in_array("{$db_prefix}messages AS m", $subject_query['from']))
					{
						$subject_query['from'][] = "{$db_prefix}messages AS m";
						$subject_query['where'][] = 'm.id_topic = t.id_topic';
					}
					$subject_query['where'][] = $userQuery;
				}
				if (!empty($search_params['topic']))
					$subject_query['where'][] = 't.id_topic = ' . $search_params['topic'];
				if (!empty($minMsgID))
					$subject_query['where'][] = 't.id_first_msg >= ' . $minMsgID;
				if (!empty($maxMsgID))
					$subject_query['where'][] = 't.id_last_msg <= ' . $maxMsgID;
				if (!empty($boardQuery))
					$subject_query['where'][] = 't.id_board ' . $boardQuery;
				if (!empty($excludedPhrases))
				{
					if (!in_array("{$db_prefix}messages AS m", $subject_query['from']))
					{
						$subject_query['from'][] = "{$db_prefix}messages AS m";
						$subject_query['where'][] = 'm.id_msg = t.id_first_msg';
					}
					foreach ($excludedPhrases as $phrase)
						$subject_query['where'][] = 'm.subject NOT ' . (empty($modSettings['search_match_words']) || $no_regexp ? " LIKE '%" . strtr($phrase, array('_' => '\\_', '%' => '\\%')) . "%'" : " RLIKE '[[:<:]]" . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $phrase), '\\\'') . "[[:>:]]'");
				}

				$smfFunc['db_query']('', "
					INSERT IGNORE INTO {$db_prefix}log_search_results
						(id_search, id_topic, relevance, id_msg, num_matches)
					SELECT 
						" . $_SESSION['search_cache']['id_search'] . ",
						t.id_topic,
						1000 * (
							$weight[frequency] / (t.num_replies + 1) +
							$weight[age] * IF(t.id_first_msg < $minMsg, 0, (t.id_first_msg - $minMsg) / $recentMsg) +
							$weight[length] * IF(t.num_replies < $humungousTopicPosts, t.num_replies / $humungousTopicPosts, 1) +
							$weight[subject] +
							$weight[sticky] * t.is_sticky
						) / $weight_total AS relevance,
						" . (empty($userQuery) ? 't.id_first_msg' : 'm.id_msg') . ",
						1
					FROM (" . implode(', ', $subject_query['from']) . ')' . (empty($subject_query['left_join']) ? '' : "
						LEFT JOIN " . implode("
						LEFT JOIN ", $subject_query['left_join'])) . "
					WHERE " . implode("
						AND ", $subject_query['where']) . (empty($modSettings['search_max_results']) ? '' : "
					LIMIT " . ($modSettings['search_max_results'] - $numSubjectResults)), __FILE__, __LINE__);

				$numSubjectResults += db_affected_rows();
				
				if (!empty($modSettings['search_max_results']) && $numSubjectResults >= $modSettings['search_max_results'])
					break;
			}

			$_SESSION['search_cache']['num_results'] = $numSubjectResults;
		}
		else
		{
			$main_query = array(
				'select' => array(
					'id_search' => $_SESSION['search_cache']['id_search'],
					'relevance' => '0',
				),
				'weights' => array(),
				'from' => array(
					"{$db_prefix}topics AS t",
					"{$db_prefix}messages AS m",
				),
				'left_join' => array(),
				'where' => array(
					't.id_topic = m.id_topic',
				),
				'group_by' => array(),
			);

			if (empty($search_params['topic']))
			{
				$main_query['select']['id_topic'] = 't.id_topic';
				$main_query['select']['id_msg'] = 'MAX(m.id_msg) AS id_msg';
				$main_query['select']['num_matches'] = 'COUNT(*) AS num_matches';

				$main_query['weights'] = array(
					'frequency' => 'COUNT(*) / (t.num_replies + 1)',
					'age' => "IF(MAX(m.id_msg) < $minMsg, 0, (MAX(m.id_msg) - $minMsg) / $recentMsg)",
					'length' => "IF(t.num_replies < $humungousTopicPosts, t.num_replies / $humungousTopicPosts, 1)",
					'subject' => '0',
					'first_message' => "IF(MIN(m.id_msg) = t.id_first_msg, 1, 0)",
					'sticky' => 't.is_sticky',
				);

				$main_query['group_by'][] = 't.id_topic';
			}
			else
			{
				// This is outrageous!
				$main_query['select']['id_topic'] = 'm.id_msg AS id_topic';
				$main_query['select']['id_msg'] = 'm.id_msg';
				$main_query['select']['num_matches'] = '1 AS num_matches';

				$main_query['weights'] = array(
					'age' => "((m.id_msg - t.id_first_msg) / IF(t.id_last_msg = t.id_first_msg, 1, t.id_last_msg - t.id_first_msg))",
					'first_message' => "IF(m.id_msg = t.id_first_msg, 1, 0)",
				);

				$main_query['where'][] = 't.id_topic = ' . $search_params['topic'];
			}


			// *** Get the subject results.

			$numSubjectResults = 0;
			if (empty($search_params['topic']))
			{
				// Create a temporary table to store some preliminary results in.
				$smfFunc['db_query']('', "
					DROP TABLE IF EXISTS {$db_prefix}tmp_log_search_topics", __FILE__, __LINE__);
				$createTemporary = $smfFunc['db_query']('', "
					CREATE TEMPORARY TABLE {$db_prefix}tmp_log_search_topics (
						id_topic mediumint(8) unsigned NOT NULL default '0',
						PRIMARY KEY (id_topic)
					) TYPE=HEAP", false, false) !== false;

				// Clean up some previous cache.
				if (!$createTemporary)
					$smfFunc['db_query']('', "
						DELETE FROM {$db_prefix}log_search_topics
						WHERE id_search = " . $_SESSION['search_cache']['id_search'], __FILE__, __LINE__);

				foreach ($searchWords as $orIndex => $words)
				{
					$subject_query = array(
						'from' => array(
							"{$db_prefix}topics AS t",
						),
						'left_join' => array(),
						'where' => array(),
					);

					$numTables = 0;
					$prev_join = 0;
					foreach ($words['subject_words'] as $subjectWord)
					{
						$numTables++;
						if (in_array($subjectWord, $excludedSubjectWords))
						{
							if (!in_array("{$db_prefix}messages AS m", $subject_query['from']))
							{
								$subject_query['from'][] = "{$db_prefix}messages AS m";
								$subject_query['where'][] = 'm.id_msg = t.id_first_msg';
							}
							$subject_query['left_join'][] = "{$db_prefix}log_search_subjects AS subj$numTables ON (subj$numTables.word " . (empty($modSettings['search_match_words']) ? "LIKE '%$subjectWord%'" : "= '$subjectWord'") . " AND subj$numTables.id_topic = t.id_topic)";
							$subject_query['where'][] = "(subj$numTables.word IS NULL)";
							$subject_query['where'][] = 'm.body NOT ' . (empty($modSettings['search_match_words']) || $no_regexp ? " LIKE '%" . strtr($subjectWord, array('_' => '\\_', '%' => '\\%')) . "%'" : " RLIKE '[[:<:]]" . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $subjectWord), '\\\'') . "[[:>:]]'");
						}
						else
						{
							$subject_query['from'][] = "{$db_prefix}log_search_subjects AS subj$numTables";
							$subject_query['where'][] = "subj$numTables.word " . (empty($modSettings['search_match_words']) ? "LIKE '%$subjectWord%'" : "= '$subjectWord'");
							$subject_query['where'][] = "subj$numTables.id_topic = " . ($prev_join === 0 ? 't' : 'subj' . $prev_join) . '.id_topic';
							$prev_join = $numTables;
						}
					}

					if (!empty($userQuery))
					{
						if (!in_array("{$db_prefix}messages AS m", $subject_query['from']))
						{
							$subject_query['from'][] = "{$db_prefix}messages AS m";
							$subject_query['where'][] = 'm.id_msg = t.id_first_msg';
						}
						$subject_query['where'][] = $userQuery;
					}
					if (!empty($search_params['topic']))
						$subject_query['where'][] = 't.id_topic = ' . $search_params['topic'];
					if (!empty($minMsgID))
						$subject_query['where'][] = 't.id_first_msg >= ' . $minMsgID;
					if (!empty($maxMsgID))
						$subject_query['where'][] = 't.id_last_msg <= ' . $maxMsgID;
					if (!empty($boardQuery))
						$subject_query['where'][] = 't.id_board ' . $boardQuery;
					if (!empty($excludedPhrases))
					{
						if (!in_array("{$db_prefix}messages AS m", $subject_query['from']))
						{
							$subject_query['from'][] = "{$db_prefix}messages AS m";
							$subject_query['where'][] = 'm.id_msg = t.id_first_msg';
						}
						foreach ($excludedPhrases as $phrase)
						{
							$subject_query['where'][] = 'm.subject NOT ' . (empty($modSettings['search_match_words']) || $no_regexp ? " LIKE '%" . strtr($phrase, array('_' => '\\_', '%' => '\\%')) . "%'" : " RLIKE '[[:<:]]" . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $phrase), '\\\'') . "[[:>:]]'");
							$subject_query['where'][] = 'm.body NOT ' . (empty($modSettings['search_match_words']) || $no_regexp ? " LIKE '%" . strtr($phrase, array('_' => '\\_', '%' => '\\%')) . "%'" : " RLIKE '[[:<:]]" . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $phrase), '\\\'') . "[[:>:]]'");
						}
					}


					$smfFunc['db_query']('', "
						INSERT IGNORE INTO {$db_prefix}" . ($createTemporary ? 'tmp_' : '') . "log_search_topics
							(" . ($createTemporary ? '' : 'id_search, ') . "id_topic)
						SELECT " . ($createTemporary ? '' : $_SESSION['search_cache']['id_search'] . ', ') . "t.id_topic
						FROM (" . implode(', ', $subject_query['from']) . ')' . (empty($subject_query['left_join']) ? '' : "
							LEFT JOIN " . implode("
							LEFT JOIN ", $subject_query['left_join'])) . "
						WHERE " . implode("
							AND ", $subject_query['where']) . (empty($modSettings['search_max_results']) ? '' : "
						LIMIT " . ($modSettings['search_max_results'] - $numSubjectResults)), __FILE__, __LINE__);

					$numSubjectResults += db_affected_rows();
					
					if (!empty($modSettings['search_max_results']) && $numSubjectResults >= $modSettings['search_max_results'])
						break;
				}

				if ($numSubjectResults !== 0)
				{
					$main_query['weights']['subject'] = 'IF(lst.id_topic IS NULL, 0, 1)';
					$main_query['left_join'][] = "{$db_prefix}" . ($createTemporary ? 'tmp_' : '') . "log_search_topics AS lst ON (" . ($createTemporary ? '' : 'lst.id_search = ' . $_SESSION['search_cache']['id_search'] . ' AND ') . "lst.id_topic = t.id_topic)";
				}
			}

			$indexedResults = 0;
			if (!empty($modSettings['search_index']))
			{
				$smfFunc['db_query']('', "
					DROP TABLE IF EXISTS {$db_prefix}tmp_log_search_messages", __FILE__, __LINE__);

				$createTemporary = $smfFunc['db_query']('', "
					CREATE TEMPORARY TABLE {$db_prefix}tmp_log_search_messages (
						id_msg int(10) unsigned NOT NULL default '0',
						PRIMARY KEY (id_msg)
					) TYPE=HEAP", false, false) !== false;

				if (!$createTemporary)
					$smfFunc['db_query']('', "
						DELETE FROM {$db_prefix}log_search_messages
						WHERE id_search = " . $_SESSION['search_cache']['id_search'], __FILE__, __LINE__);

				foreach ($searchWords as $orIndex => $words)
				{

					// *** Do the fulltext search.

					if (!empty($words['indexed_words']) && $modSettings['search_index'] == 'fulltext')
					{
						$fulltext_query = array(
							'insert_into' => $db_prefix . ($createTemporary ? 'tmp_' : '') . 'log_search_messages',
							'select' => array(
								'id_msg' => 'id_msg',
							),
							'where' => array(),
						);

						if (!$createTemporary)
							$fulltext_query['select']['id_search'] = $_SESSION['search_cache']['id_search'];

						if (empty($modSettings['search_simple_fulltext']))
							foreach ($words['words'] as $regularWord)
								$fulltext_query['where'][] = 'body' . (in_array($regularWord, $excludedWords) ? ' NOT' : '') . (empty($modSettings['search_match_words']) || $no_regexp ? " LIKE '%" . strtr($regularWord, array('_' => '\\_', '%' => '\\%')) . "%'" : " RLIKE '[[:<:]]" . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $regularWord), '\\\'') . "[[:>:]]'");

						if (!empty($userQuery))
							$fulltext_query['where'][] = strtr($userQuery, array('m.' => ''));
						if (!empty($search_params['topic']))
							$fulltext_query['where'][] = 'id_topic = ' . $search_params['topic'];
						if (!empty($minMsgID))
							$fulltext_query['where'][] = 'id_msg >= ' . $minMsgID;
						if (!empty($maxMsgID))
							$fulltext_query['where'][] = 'id_msg <= ' . $maxMsgID;
						if (!empty($boardQuery))
							$fulltext_query['where'][] = 'id_board ' . $boardQuery;
						if (!empty($excludedPhrases) && empty($modSettings['search_force_index']))
							foreach ($excludedPhrases as $phrase)
								$fulltext_query['where'][] = 'subject NOT ' . (empty($modSettings['search_match_words']) || $no_regexp ? " LIKE '%" . strtr($phrase, array('_' => '\\_', '%' => '\\%')) . "%'" : " RLIKE '[[:<:]]" . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $phrase), '\\\'') . "[[:>:]]'");
						if (!empty($excludedSubjectWords) && empty($modSettings['search_force_index']))
							foreach ($excludedSubjectWords as $excludedWord)
								$fulltext_query['where'][] = 'subject NOT ' . (empty($modSettings['search_match_words']) || $no_regexp ? " LIKE '%" . strtr($excludedWord, array('_' => '\\_', '%' => '\\%')) . "%'" : " RLIKE '[[:<:]]" . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $excludedWord), '\\\'') . "[[:>:]]'");

						if (!empty($modSettings['search_simple_fulltext']))
							$fulltext_query['where'][] = "MATCH (body) AGAINST ('" . implode(' ', array_diff($words['indexed_words'], $excludedIndexWords)) . "')";
						elseif ($canDoBooleanSearch)
						{
							$where = "MATCH (body) AGAINST ('";
							foreach ($words['indexed_words'] as $fulltextWord)
								$where .= (in_array($fulltextWord, $excludedIndexWords) ? '-' : '+') . $fulltextWord . ' ';
							$fulltext_query['where'][] = substr($where, 0, -1) . "' IN BOOLEAN MODE)";
						}
						else
							foreach ($words['indexed_words'] as $fulltextWord)
								$fulltext_query['where'][] = (in_array($fulltextWord, $excludedIndexWords) ? 'NOT ' : '') . "MATCH (body) AGAINST ('$fulltextWord')";

						$smfFunc['db_query']('', "
							INSERT IGNORE INTO $fulltext_query[insert_into]
								(" . implode(', ', array_keys($fulltext_query['select'])) . ")
							SELECT " . implode(', ', $fulltext_query['select']) . "
							FROM {$db_prefix}messages
							WHERE " . implode("
								AND ", $fulltext_query['where']) . (empty($maxMessageResults) ? '' : "
							LIMIT " . ($maxMessageResults - $indexedResults)), __FILE__, __LINE__);

						$indexedResults += db_affected_rows();

						if (!empty($maxMessageResults) && $indexedResults >= $maxMessageResults)
							break;
					}


					// *** Do the custom index search.

					elseif (!empty($words['indexed_words']) && $modSettings['search_index'] == 'custom')
					{
						$custom_query = array(
							'insert_into' => $db_prefix . ($createTemporary ? 'tmp_' : '') . 'log_search_messages',
							'select' => array(
								'id_msg' => 'm.id_msg',
							),
							'from' => array(
								"{$db_prefix}messages AS m",
							),
							'left_join' => array(),
							'where' => array(),
						);

						if (!$createTemporary)
							$custom_query['select']['id_search'] = $_SESSION['search_cache']['id_search'];
						
						foreach ($words['words'] as $regularWord)
							$custom_query['where'][] = 'm.body' . (in_array($regularWord, $excludedWords) ? ' NOT' : '') . (empty($modSettings['search_match_words']) || $no_regexp ? " LIKE '%" . strtr($regularWord, array('_' => '\\_', '%' => '\\%')) . "%'" : " RLIKE '[[:<:]]" . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $regularWord), '\\\'') . "[[:>:]]'");

						if (!empty($userQuery))
							$custom_query['where'][] = $userQuery;
						if (!empty($search_params['topic']))
							$custom_query['where'][] = 'm.id_topic = ' . $search_params['topic'];
						if (!empty($minMsgID))
							$custom_query['where'][] = 'm.id_msg >= ' . $minMsgID;
						if (!empty($maxMsgID))
							$custom_query['where'][] = 'm.id_msg <= ' . $maxMsgID;
						if (!empty($boardQuery))
							$custom_query['where'][] = 'm.id_board ' . $boardQuery;
						if (!empty($excludedPhrases) && empty($modSettings['search_force_index']))
							foreach ($excludedPhrases as $phrase)
								$fulltext_query['where'][] = 'subject NOT ' . (empty($modSettings['search_match_words']) || $no_regexp ? " LIKE '%" . strtr($phrase, array('_' => '\\_', '%' => '\\%')) . "%'" : " RLIKE '[[:<:]]" . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $phrase), '\\\'') . "[[:>:]]'");
						if (!empty($excludedSubjectWords) && empty($modSettings['search_force_index']))
							foreach ($excludedSubjectWords as $excludedWord)
								$fulltext_query['where'][] = 'subject NOT ' . (empty($modSettings['search_match_words']) || $no_regexp ? " LIKE '%" . strtr($excludedWord, array('_' => '\\_', '%' => '\\%')) . "%'" : " RLIKE '[[:<:]]" . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $excludedWord), '\\\'') . "[[:>:]]'");

						$numTables = 0;
						$prev_join = 0;
						foreach ($words['indexed_words'] as $indexedWord)
						{
							$numTables++;
							if (in_array($indexedWord, $excludedIndexWords))
							{
								$custom_query['left_join'][] = "{$db_prefix}log_search_words AS lsw$numTables ON (lsw$numTables.ID_WORD = $indexedWord AND lsw$numTables.id_msg = m.id_msg)";
								$custom_query['where'][] = "(lsw$numTables.ID_WORD IS NULL)";
							}
							else
							{
								$custom_query['from'][] = "{$db_prefix}log_search_words AS lsw$numTables";
								$custom_query['where'][] = "lsw$numTables.ID_WORD = $indexedWord";
								$custom_query['where'][] = "lsw$numTables.id_msg = " . ($prev_join === 0 ? 'm' : 'lsw' . $prev_join) . '.id_msg';
								$prev_join = $numTables;
							}
						}
						$smfFunc['db_query']('', "
							INSERT IGNORE INTO $custom_query[insert_into]
								(" . implode(', ', array_keys($custom_query['select'])) . ")
							SELECT " . implode(', ', $custom_query['select']) . "
							FROM (" . implode(', ', $custom_query['from']) . ')' . (empty($custom_query['left_join']) ? '' : "
								LEFT JOIN " . implode("
								LEFT JOIN ", $custom_query['left_join'])) . "
							WHERE " . implode("
								AND ", $custom_query['where']) . (empty($maxMessageResults) ? '' : "
							LIMIT " . ($maxMessageResults - $indexedResults)), __FILE__, __LINE__);

						$indexedResults += db_affected_rows();

						if (!empty($maxMessageResults) && $indexedResults >= $maxMessageResults)
							break;
					}
				}

				if (empty($indexedResults) && empty($numSubjectResults) && !empty($modSettings['search_force_index']))
				{
					$context['search_errors']['query_not_specific_enough'] = true;
					$_REQUEST['params'] = $context['params'];
					return PlushSearch1();
				}
				elseif (!empty($indexedResults))
				{
					$main_query['from'][] = $db_prefix . ($createTemporary ? 'tmp_' : '') . 'log_search_messages AS lsm';
					$main_query['where'][] = 'lsm.id_msg = m.id_msg';
					if (!$createTemporary)
						$main_query['where'][] = 'lsm.id_search = ' . $_SESSION['search_cache']['id_search'];
				}
			}

			// Not using an index? All conditions have to be carried over.
			else
			{
				$orWhere = array();
				foreach ($searchWords as $orIndex => $words)
				{
					$where = array();
					foreach ($words['all_words'] as $regularWord)
					{
						$where[] = 'm.body' . (in_array($regularWord, $excludedWords) ? ' NOT' : '') . (empty($modSettings['search_match_words']) || $no_regexp ? " LIKE '%" . strtr($regularWord, array('_' => '\\_', '%' => '\\%')) . "%'" : " RLIKE '[[:<:]]" . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $regularWord), '\\\'') . "[[:>:]]'");
						if (in_array($regularWord, $excludedWords))
							$where[] = 'm.subject NOT' . (empty($modSettings['search_match_words']) || $no_regexp ? " LIKE '%" . strtr($regularWord, array('_' => '\\_', '%' => '\\%')) . "%'" : " RLIKE '[[:<:]]" . addcslashes(preg_replace(array('/([\[\]$.+*?|{}()])/'), array('[$1]'), $regularWord), '\\\'') . "[[:>:]]'");
					}
					if (!empty($where))
						$orWhere[] = count($where) > 1 ? '(' . implode(' AND ', $where) . ')' : $where[0];
				}
				if (!empty($orWhere))
					$main_query['where'][] = count($orWhere) > 1 ? '(' . implode(' OR ', $orWhere) . ')' : $orWhere[0];

				if (!empty($userQuery))
					$main_query['where'][] = $userQuery;
				if (!empty($search_params['topic']))
					$main_query['where'][] = 'm.id_topic = ' . $search_params['topic'];
				if (!empty($minMsgID))
					$main_query['where'][] = 'm.id_msg >= ' . $minMsgID;
				if (!empty($maxMsgID))
					$main_query['where'][] = 'm.id_msg <= ' . $maxMsgID;
				if (!empty($boardQuery))
					$main_query['where'][] = 'm.id_board ' . $boardQuery;
			}

			if (!empty($indexedResults) || empty($modSettings['search_index']))
			{
				$relevance = '1000 * (';
				$new_weight_total = 0;
				foreach ($main_query['weights'] as $type => $value)
				{
					$relevance .= $weight[$type] . ' * ' . $value . ' + ';
					$new_weight_total += $weight[$type];
				}
				$main_query['select']['relevance'] = substr($relevance, 0, -3) . ") / $new_weight_total AS relevance";

				$smfFunc['db_query']('', "
					INSERT IGNORE INTO {$db_prefix}log_search_results
						(" . implode(', ', array_keys($main_query['select'])) . ")
					SELECT
						" . implode(',
						', $main_query['select']) . "
					FROM (" . implode(', ', $main_query['from']) . ')' . (empty($main_query['left_join']) ? '' : "
						LEFT JOIN " . implode("
						LEFT JOIN ", $main_query['left_join'])) . "
					WHERE " . implode("
						AND ", $main_query['where']) . (empty($main_query['group_by']) ? '' : "
					GROUP BY " . implode(', ', $main_query['group_by'])) . (empty($modSettings['search_max_results']) ? '' : "
					LIMIT $modSettings[search_max_results]"), __FILE__, __LINE__);

				$_SESSION['search_cache']['num_results'] = db_affected_rows();
			}

			// Insert subject-only matches.
			if ($_SESSION['search_cache']['num_results'] < $modSettings['search_max_results'] && $numSubjectResults !== 0)
			{
				$smfFunc['db_query']('', "
					INSERT IGNORE INTO {$db_prefix}log_search_results
						(id_search, id_topic, relevance, id_msg, num_matches)
					SELECT
						" . $_SESSION['search_cache']['id_search'] . ",
						t.id_topic,
						1000 * (
							$weight[frequency] / (t.num_replies + 1) +
							$weight[age] * IF(t.id_first_msg < $minMsg, 0, (t.id_first_msg - $minMsg) / $recentMsg) +
							$weight[length] * IF(t.num_replies < $humungousTopicPosts, t.num_replies / $humungousTopicPosts, 1) +
							$weight[subject] +
							$weight[sticky] * t.is_sticky
						) / $weight_total AS relevance,
						t.id_first_msg,
						1
					FROM ({$db_prefix}topics AS t, {$db_prefix}" . ($createTemporary ? 'tmp_' : '') . "log_search_topics AS lst)
					WHERE lst.id_topic = t.id_topic" . (empty($modSettings['search_max_results']) ? '' : "
					LIMIT " . ($modSettings['search_max_results'] - $_SESSION['search_cache']['num_results'])), __FILE__, __LINE__);

				$_SESSION['search_cache']['num_results'] += db_affected_rows();
			}
			else
				$_SESSION['search_cache']['num_results'] = 0;
		}
	}
	// *** Retrieve the results to be shown on the page

	$participants = array();
	$request = $smfFunc['db_query']('', "
		SELECT " . (empty($search_params['topic']) ? 'lsr.id_topic' : $search_params['topic'] . ' AS id_topic') . ", lsr.id_msg, lsr.relevance, lsr.num_matches
		FROM ({$db_prefix}log_search_results AS lsr" . ($search_params['sort'] == 'num_replies' ? ", {$db_prefix}topics AS t" : '') . ")
		WHERE id_search = " . $_SESSION['search_cache']['id_search'] . ($search_params['sort'] == 'num_replies' ? "
			AND t.id_topic = lsr.id_topic" : '') . "
		ORDER BY $search_params[sort] $search_params[sort_dir]
		LIMIT " . (int) $_REQUEST['start'] . ", $modSettings[search_results_per_page]", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['topics'][$row['id_msg']] = array(
			'id' => $row['id_topic'],
			'relevance' => round($row['relevance'] / 10, 1) . '%',
			'num_matches' => $row['num_matches'],
			'matches' => array(),
		);
		// By default they didn't participate in the topic!
		$participants[$row['id_topic']] = false;
	}
	$smfFunc['db_free_result']($request);

	// Now that we know how many results to expect we can start calculating the page numbers.
	$context['page_index'] = constructPageIndex($scripturl . '?action=search2;params=' . $context['params'], $_REQUEST['start'], $_SESSION['search_cache']['num_results'], $modSettings['search_results_per_page'], false);

	if (!empty($context['topics']))
	{
		// Create an array for the permissions.
		$boards_can = array(
			'post_reply_own' => boardsAllowedTo('post_reply_own'),
			'post_reply_any' => boardsAllowedTo('post_reply_any'),
			'mark_any_notify' => boardsAllowedTo('mark_any_notify')
		);

		// How's about some quick moderation?
		if (!empty($options['display_quick_mod']) && !empty($context['topics']))
		{
			$boards_can['lock_any'] = boardsAllowedTo('lock_any');
			$boards_can['lock_own'] = boardsAllowedTo('lock_own');
			$boards_can['make_sticky'] = boardsAllowedTo('make_sticky');
			$boards_can['move_any'] = boardsAllowedTo('move_any');
			$boards_can['move_own'] = boardsAllowedTo('move_own');
			$boards_can['remove_any'] = boardsAllowedTo('remove_any');
			$boards_can['remove_own'] = boardsAllowedTo('remove_own');
			$boards_can['merge_any'] = boardsAllowedTo('merge_any');

			$context['can_lock'] = in_array(0, $boards_can['lock_any']);
			$context['can_sticky'] = in_array(0, $boards_can['make_sticky']) && !empty($modSettings['enableStickyTopics']);
			$context['can_move'] = in_array(0, $boards_can['move_any']);
			$context['can_remove'] = in_array(0, $boards_can['remove_any']);
			$context['can_merge'] = in_array(0, $boards_can['merge_any']);
		}

		// Load the posters...
		$request = $smfFunc['db_query']('', "
			SELECT id_member
			FROM {$db_prefix}messages
			WHERE id_member != 0
				AND id_msg IN (" . implode(', ', array_keys($context['topics'])) . ")
			LIMIT " . count($context['topics']), __FILE__, __LINE__);
		$posters = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$posters[] = $row['id_member'];
		$smfFunc['db_free_result']($request);

		if (!empty($posters))
			loadMemberData(array_unique($posters));

		// Get the messages out for the callback - select enough that it can be made to look just like Display.
		$messages_request = $smfFunc['db_query']('', "
			SELECT
				m.id_msg, m.subject, m.poster_name, m.poster_email, m.poster_time, m.id_member,
				m.icon, m.poster_ip, m.body, m.smileys_enabled, m.modified_time, m.modified_name,
				first_m.id_msg AS first_msg, first_m.subject AS first_subject, first_m.icon AS first_icon, first_m.poster_time AS first_poster_time,
				first_mem.id_member AS first_member_id, IFNULL(first_mem.real_name, first_m.poster_name) AS first_member_name,
				last_m.id_msg AS last_msg, last_m.poster_time AS last_poster_time, last_mem.id_member AS last_member_id,
				IFNULL(last_mem.real_name, last_m.poster_name) AS last_member_name, last_m.icon AS last_icon, last_m.subject AS last_subject,
				t.id_topic, t.is_sticky, t.locked, t.id_poll, t.num_replies, t.num_views,
				b.id_board, b.name AS board_name, c.id_cat, c.name AS cat_name
			FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}boards AS b, {$db_prefix}categories AS c, {$db_prefix}messages AS first_m, {$db_prefix}messages AS last_m)
				LEFT JOIN {$db_prefix}members AS first_mem ON (first_mem.id_member = first_m.id_member)
				LEFT JOIN {$db_prefix}members AS last_mem ON (last_mem.id_member = first_m.id_member)
			WHERE m.id_msg IN (" . implode(', ', array_keys($context['topics'])) . ")
				AND t.id_topic = m.id_topic
				AND b.id_board = t.id_board
				AND c.id_cat = b.id_cat
				AND first_m.id_msg = t.id_first_msg
				AND last_m.id_msg = t.id_last_msg
				AND m.approved = 1
			ORDER BY FIND_IN_SET(m.id_msg, '" . implode(',', array_keys($context['topics'])) . "')
			LIMIT " . count($context['topics']), __FILE__, __LINE__);
		// Note that the reg-exp slows things alot, but makes things make a lot more sense.

		// If we want to know who participated in what then load this now.
		if (!empty($modSettings['enableParticipation']) && !$user_info['is_guest'])
		{
			$result = $smfFunc['db_query']('', "
				SELECT id_topic
				FROM {$db_prefix}messages
				WHERE id_topic IN (" . implode(', ', array_keys($participants)) . ")
					AND id_member = $user_info[id]
				GROUP BY id_topic
				LIMIT " . count($participants), __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$participants[$row['id_topic']] = true;
			$smfFunc['db_free_result']($result);
		}
	}

	// Consider the search complete!
	if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
		cache_put_data('search_start:' . ($user_info['is_guest'] ? $user_info['ip'] : $user_info['id']), null, 90);

	$context['key_words'] = &$searchArray;

	// Set the basic stuff for the template.
	$context['allow_hide_email'] = !empty($modSettings['allow_hide_email']);

	// Setup the default topic icons... for checking they exist and the like!
	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$context['icon_sources'] = array();
	foreach ($stable_icons as $icon)
		$context['icon_sources'][$icon] = 'images_url';

	$context['sub_template'] = 'results';
	$context['page_title'] = $txt['search_results'];
	$context['get_topics'] = 'prepareSearchContext';
	$context['can_send_pm'] = allowedTo('pm_send');

	$context['jump_to'] = array(
		'label' => addslashes(un_htmlspecialchars($txt['jump_to'])),
		'board_name' => addslashes(un_htmlspecialchars($txt['select_destination'])),
	);

	// Get a list of boards to move these messages to.
	if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && !empty($context['topics']) && $context['can_move'])
	{
		$request = $smfFunc['db_query']('', "
			SELECT c.name AS cat_name, c.id_cat, b.id_board, b.name AS board_name, b.child_level
			FROM {$db_prefix}boards AS b
				LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)
			WHERE $user_info[query_see_board]", __FILE__, __LINE__);

		$board_count = $smfFunc['db_num_rows']($request);
		
		$context['move_to_boards'] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if (!isset($context['move_to_boards'][$row['id_cat']]))
				$context['move_to_boards'][$row['id_cat']] = array(
					'id' => $row['id_cat'],
					'name' => $row['cat_name'],
					'boards' => array(),
				);

			$context['move_to_boards'][$row['id_cat']]['boards'][] = array(
				'id' => $row['id_board'],
				'name' => $row['board_name'],
				'child_level' => $row['child_level'],
				'selected' => !empty($_SESSION['move_to_topic']) && $_SESSION['move_to_topic'] == $row['id_board'],
			);
		}
		$smfFunc['db_free_result']($request);
	}
}

// Callback to return messages - saves memory.
// !!! Fix this, update it, whatever... from Display.php mainly.
function prepareSearchContext($reset = false)
{
	global $txt, $modSettings, $db_prefix, $scripturl, $user_info;
	global $memberContext, $context, $settings, $options, $messages_request;
	global $boards_can, $participants, $smfFunc;

	// Remember which message this is.  (ie. reply #83)
	static $counter = null;
	if ($counter == null || $reset)
		$counter = $_REQUEST['start'] + 1;

	// If the query returned false, bail.
	if ($messages_request == false)
		return false;

	// Start from the beginning...
	if ($reset)
		return @$smfFunc['db_data_seek']($messages_request, 0);

	// Attempt to get the next message.
	$message = $smfFunc['db_fetch_assoc']($messages_request);
	if (!$message)
		return false;

	// Can't have an empty subject can we?
	$message['subject'] = $message['subject'] != '' ? $message['subject'] : $txt['no_subject'];

	$message['first_subject'] = $message['first_subject'] != '' ? $message['first_subject'] : $txt['no_subject'];
	$message['last_subject'] = $message['last_subject'] != '' ? $message['last_subject'] : $txt['no_subject'];

	// If it couldn't load, or the user was a guest.... someday may be done with a guest table.
	if (!loadMemberContext($message['id_member']))
	{
		// Notice this information isn't used anywhere else.... *cough guest table cough*.
		$memberContext[$message['id_member']]['name'] = $message['poster_name'];
		$memberContext[$message['id_member']]['id'] = 0;
		$memberContext[$message['id_member']]['group'] = $txt['guest_title'];
		$memberContext[$message['id_member']]['link'] = $message['poster_name'];
		$memberContext[$message['id_member']]['email'] = $message['poster_email'];
	}
	$memberContext[$message['id_member']]['ip'] = $message['poster_ip'];

	// Do the censor thang...
	censorText($message['body']);
	censorText($message['subject']);

	censorText($message['first_subject']);
	censorText($message['last_subject']);

	// Shorten this message if necessary.
	if ($context['compact'])
	{
		// Set the number of characters before and after the searched keyword.
		$charLimit = 40;

		$message['body'] = strtr($message['body'], array("\n" => ' ', '<br />' => "\n"));
		$message['body'] = parse_bbc($message['body'], $message['smileys_enabled'], $message['id_msg']);
		$message['body'] = strip_tags(strtr($message['body'], array('</div>' => '<br />')), '<br>');

		if (strlen($message['body']) > $charLimit)
		{
			if (empty($context['key_words']))
				$message['body'] = $smfFunc['strlen']($message['body']) > $charLimit ? $smfFunc['substr']($message['body'], 0, $charLimit) . '<b>...</b>' : $message['body'];
			else
			{
				$matchString = '';
				$force_partial_word = false;
				foreach ($context['key_words'] as $keyword)
				{
					$keyword = preg_replace('~(&amp;#(\d{1,7}|x[0-9a-fA-F]{1,6});)~e', '$GLOBALS[\'smfFunc\'][\'entity_fix\'](\'\\2\')', strtr($keyword, array('\\\'' => '\'', '&' => '&amp;')));

					if (preg_match('~[\'\.,/@%&;:(){}\[\]_\-+\\\\]$~', $keyword) != 0 || preg_match('~^[\'\.,/@%&;:(){}\[\]_\-+\\\\]~', $keyword) != 0)
						$force_partial_word = true;
					$matchString .= strtr(preg_quote($keyword, '/'), array('\*' => '.+?')) . '|';
				}
				$matchString = substr($matchString, 0, -1);

				$message['body'] = un_htmlspecialchars(strtr($message['body'], array('&nbsp;' => ' ', '<br />' => "\n", '&#91;' => '[', '&#93;' => ']', '&#58;' => ':', '&#64;' => '@')));

				if (empty($modSettings['search_method']) || $force_partial_word)
					preg_match_all('/([^\s\W]{' . $charLimit . '}[\s\W]|[\s\W].{0,' . $charLimit . '}?|^)(' . $matchString . ')(.{0,' . $charLimit . '}[\s\W]|[^\s\W]{' . $charLimit . '})/is' . ($context['utf8'] ? 'u' : ''), $message['body'], $matches);
				else
					preg_match_all('/([^\s\W]{' . $charLimit . '}[\s\W]|[\s\W].{0,' . $charLimit . '}?[\s\W]|^)(' . $matchString . ')([\s\W].{0,' . $charLimit . '}[\s\W]|[\s\W][^\s\W]{' . $charLimit . '})/is' . ($context['utf8'] ? 'u' : ''), $message['body'], $matches);

				$message['body'] = '';
				foreach ($matches[0] as $index => $match)
				{
					$match = strtr(htmlspecialchars($match, ENT_QUOTES), array("\n" => '<br />'));
					$message['body'] .= '<b>...</b>&nbsp;' . $match . '&nbsp;<b>...</b><br />';
				}
			}

			// Re-fix the international characters.
			$message['body'] = preg_replace('~(&amp;#(\d{1,7}|x[0-9a-fA-F]{1,6});)~e', '$GLOBALS[\'smfFunc\'][\'entity_fix\'](\'\\2\')', $message['body']);
		}
	}
	else
	{
		// Run UBBC interpreter on the message.
		$message['body'] = parse_bbc($message['body'], $message['smileys_enabled'], $message['id_msg']);
	}

	// Sadly, we need to check the icon ain't broke.
	if (empty($modSettings['messageIconChecks_disable']))
	{
		if (!isset($context['icon_sources'][$message['first_icon']]))
			$context['icon_sources'][$message['first_icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $message['first_icon'] . '.gif') ? 'images_url' : 'default_images_url';
		if (!isset($context['icon_sources'][$message['last_icon']]))
			$context['icon_sources'][$message['last_icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $message['last_icon'] . '.gif') ? 'images_url' : 'default_images_url';
		if (!isset($context['icon_sources'][$message['icon']]))
			$context['icon_sources'][$message['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $message['icon'] . '.gif') ? 'images_url' : 'default_images_url';
	}
	else
	{
		if (!isset($context['icon_sources'][$message['first_icon']]))
			$context['icon_sources'][$message['first_icon']] = 'images_url';
		if (!isset($context['icon_sources'][$message['last_icon']]))
			$context['icon_sources'][$message['last_icon']] = 'images_url';
		if (!isset($context['icon_sources'][$message['icon']]))
			$context['icon_sources'][$message['icon']] = 'images_url';
	}

	$output = array_merge($context['topics'][$message['id_msg']], array(
		'is_sticky' => !empty($modSettings['enableStickyTopics']) && !empty($message['is_sticky']),
		'is_locked' => !empty($message['locked']),
		'is_poll' => $modSettings['pollMode'] == '1' && $message['id_poll'] > 0,
		'is_hot' => $message['num_replies'] >= $modSettings['hotTopicPosts'],
		'is_very_hot' => $message['num_replies'] >= $modSettings['hotTopicVeryPosts'],
		'posted_in' => $participants[$message['id_topic']],
		'views' => $message['num_views'],
		'replies' => $message['num_replies'],
		'can_reply' => in_array($message['id_board'], $boards_can['post_reply_any']) || in_array(0, $boards_can['post_reply_any']),
		'can_mark_notify' => in_array($message['id_board'], $boards_can['mark_any_notify']) || in_array(0, $boards_can['mark_any_notify']) && !$context['user']['is_guest'],
		'first_post' => array(
			'id' => $message['first_msg'],
			'time' => timeformat($message['first_poster_time']),
			'timestamp' => forum_time(true, $message['first_poster_time']),
			'subject' => $message['first_subject'],
			'href' => $scripturl . '?topic=' . $message['id_topic'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $message['id_topic'] . '.0">' . $message['first_subject'] . '</a>',
			'icon' => $message['first_icon'],
			'icon_url' => $settings[$context['icon_sources'][$message['first_icon']]] . '/post/' . $message['first_icon'] . '.gif',
			'member' => array(
				'id' => $message['first_member_id'],
				'name' => $message['first_member_name'],
				'href' => !empty($message['first_member_id']) ? $scripturl . '?action=profile;u=' . $message['first_member_id'] : '',
				'link' => !empty($message['first_member_id']) ? '<a href="' . $scripturl . '?action=profile;u=' . $message['first_member_id'] . '" title="' . $txt['profile_of'] . ' ' . $message['first_member_name'] . '">' . $message['first_member_name'] . '</a>' : $message['first_member_name']
			)
		),
		'last_post' => array(
			'id' => $message['last_msg'],
			'time' => timeformat($message['last_poster_time']),
			'timestamp' => forum_time(true, $message['last_poster_time']),
			'subject' => $message['last_subject'],
			'href' => $scripturl . '?topic=' . $message['id_topic'] . ($message['num_replies'] == 0 ? '.0' : '.msg' . $message['last_msg']) . '#msg' . $message['last_msg'],
			'link' => '<a href="' . $scripturl . '?topic=' . $message['id_topic'] . ($message['num_replies'] == 0 ? '.0' : '.msg' . $message['last_msg']) . '#msg' . $message['last_msg'] . '">' . $message['last_subject'] . '</a>',
			'icon' => $message['last_icon'],
			'icon_url' => $settings[$context['icon_sources'][$message['last_icon']]] . '/post/' . $message['last_icon'] . '.gif',
			'member' => array(
				'id' => $message['last_member_id'],
				'name' => $message['last_member_name'],
				'href' => !empty($message['last_member_id']) ? $scripturl . '?action=profile;u=' . $message['last_member_id'] : '',
				'link' => !empty($message['last_member_id']) ? '<a href="' . $scripturl . '?action=profile;u=' . $message['last_member_id'] . '" title="' . $txt['profile_of'] . ' ' . $message['last_member_name'] . '">' . $message['last_member_name'] . '</a>' : $message['last_member_name']
			)
		),
		'board' => array(
			'id' => $message['id_board'],
			'name' => $message['board_name'],
			'href' => $scripturl . '?board=' . $message['id_board'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $message['id_board'] . '.0">' . $message['board_name'] . '</a>'
		),
		'category' => array(
			'id' => $message['id_cat'],
			'name' => $message['cat_name'],
			'href' => $scripturl . '#' . $message['id_cat'],
			'link' => '<a href="' . $scripturl . '#' . $message['id_cat'] . '">' . $message['cat_name'] . '</a>'
		)
	));
	determineTopicClass($output);

	if ($output['posted_in'])
		$output['class'] = 'my_' . $output['class'];

	$body_highlighted = $message['body'];
	$subject_highlighted = $message['subject'];

	if (!empty($options['display_quick_mod']))
	{
		$started = $output['first_post']['member']['id'] == $user_info['id'];

		$output['quick_mod'] = array(
			'lock' => in_array(0, $boards_can['lock_any']) || in_array($output['board']['id'], $boards_can['lock_any']) || ($started && (in_array(0, $boards_can['lock_own']) || in_array($output['board']['id'], $boards_can['lock_own']))),
			'sticky' => (in_array(0, $boards_can['make_sticky']) || in_array($output['board']['id'], $boards_can['make_sticky'])) && !empty($modSettings['enableStickyTopics']),
			'move' => in_array(0, $boards_can['move_any']) || in_array($output['board']['id'], $boards_can['move_any']) || ($started && (in_array(0, $boards_can['move_own']) || in_array($output['board']['id'], $boards_can['move_own']))),
			'remove' => in_array(0, $boards_can['remove_any']) || in_array($output['board']['id'], $boards_can['remove_any']) || ($started && (in_array(0, $boards_can['remove_own']) || in_array($output['board']['id'], $boards_can['remove_own']))),
		);

		$context['can_lock'] |= $output['quick_mod']['lock'];
		$context['can_sticky'] |= $output['quick_mod']['sticky'];
		$context['can_move'] |= $output['quick_mod']['move'];
		$context['can_remove'] |= $output['quick_mod']['remove'];
		$context['can_merge'] |= in_array($output['board']['id'], $boards_can['merge_any']);
	}

	foreach ($context['key_words'] as $query)
	{
		// Fix the international characters in the keyword too.
		$query = strtr($smfFunc['htmlspecialchars']($query), array('\\\'' => '\''));

		$body_highlighted = preg_replace('/((<[^>]*)|' . preg_quote(strtr($query, array('\'' => '&#039;')), '/') . ')/ie' . ($context['utf8'] ? 'u' : ''), "'\$2' == '\$1' ? stripslashes('\$1') : '<b class=\"highlight\">\$1</b>'", $body_highlighted);
		$subject_highlighted = preg_replace('/(' . preg_quote($query, '/') . ')/i' . ($context['utf8'] ? 'u' : ''), '<b class="highlight">$1</b>', $subject_highlighted);
	}

	$output['matches'][] = array(
		'id' => $message['id_msg'],
		'attachment' => loadAttachmentContext($message['id_msg']),
		'alternate' => $counter % 2,
		'member' => &$memberContext[$message['id_member']],
		'icon' => $message['icon'],
		'icon_url' => $settings[$context['icon_sources'][$message['icon']]] . '/post/' . $message['icon'] . '.gif',
		'subject' => $message['subject'],
		'subject_highlighted' => $subject_highlighted,
		'time' => timeformat($message['poster_time']),
		'timestamp' => forum_time(true, $message['poster_time']),
		'counter' => $counter,
		'modified' => array(
			'time' => timeformat($message['modified_time']),
			'timestamp' => forum_time(true, $message['modified_time']),
			'name' => $message['modified_name']
		),
		'body' => $message['body'],
		'body_highlighted' => $body_highlighted,
		'start' => 'msg' . $message['id_msg']
	);
	$counter++;

	return $output;
}

// This function compares the length of two strings plus a little.
function searchSort($a, $b)
{
	global $modSettings, $excludedWords;

	$x = strlen($a) - (in_array($a, $excludedWords) ? 1000 : 0);
	$y = strlen($b) - (in_array($b, $excludedWords) ? 1000 : 0);
	if ($modSettings['search_index'] == 'fulltext')
		return $x < $y ? 1 : ($x > $y ? -1 : 0);
	else
		return $y < $x ? 1 : ($y > $x ? -1 : 0);
}

?>