<?php
/**********************************************************************************
* Who.php                                                                         *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 3 Public                                      *
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

/*	This file is mainly concerned, or that is to say only concerned, with the
	Who's Online list.  It contains only the following functions:

	void Who()
		- prepares the who's online data for the Who template.
		- uses the Who template (main sub template.) and language file.
		- requires the who_view permission.
		- is enabled with the who_enabled setting.
		- is accessed via ?action=who.

	array determineActions(array urls)
		- determine the actions of the members passed in urls.
		- urls should be a single url (string) or an array of arrays, each
		  inner array being (serialized request data, id_member).
		- returns an array of descriptions if you passed an array, otherwise
		  the string describing their current location.

	void Credits(bool in_admin)
		- prepares credit and copyright information for the credits page or the admin page
		- if parameter is true the it will not load the sub template nor the template file

	Adding actions to the Who's Online list:
	---------------------------------------------------------------------------
		Adding actions to this list is actually relatively easy....
		- for actions anyone should be able to see, just add a string named
		   whoall_ACTION.  (where ACTION is the action used in index.php.)
		- for actions that have a subaction which should be represented
		   differently, use whoall_ACTION_SUBACTION.
		- for actions that include a topic, and should be restricted, use
		   whotopic_ACTION.
		- for actions that use a message, by msg or quote, use whopost_ACTION.
		- for administrator-only actions, use whoadmin_ACTION.
		- for actions that should be viewable only with certain permissions,
		   use whoallow_ACTION and add a list of possible permissions to the
		   $allowedActions array, using ACTION as the key.
*/

// Who's online, and what are they doing?
function Who()
{
	global $context, $scripturl, $user_info, $txt, $modSettings, $memberContext, $smcFunc;

	// Permissions, permissions, permissions.
	isAllowedTo('who_view');

	// You can't do anything if this is off.
	if (empty($modSettings['who_enabled']))
		fatal_lang_error('who_off', false);

	// Load the 'Who' template.
	loadTemplate('Who');
	loadLanguage('Who');

	// Sort out... the column sorting.
	$sort_methods = array(
		'user' => 'mem.real_name',
		'time' => 'lo.log_time'
	);

	$show_methods = array(
		'members' => '(lo.id_member != 0)',
		'guests' => '(lo.id_member = 0)',
		'all' => '',
	);

	// Store the sort methods and the show types for use in the template.
	$context['sort_methods'] = array(
		'user' => $txt['who_user'],
		'time' => $txt['who_time'],
	);
	$context['show_methods'] = array(
		'all' => $txt['who_show_all'],
		'members' => $txt['who_show_members_only'],
		'guests' => $txt['who_show_guests_only'],
	);

	// Can they see spiders too?
	if (!empty($modSettings['show_spider_online']) && ($modSettings['show_spider_online'] < 3 || allowedTo('admin_forum')) && !empty($modSettings['spider_name_cache']))
	{
		$show_methods['spiders'] = '(lo.id_member = 0 AND lo.id_spider > 0)';
		$show_methods['guests'] = '(lo.id_member = 0 AND lo.id_spider = 0)';
		$context['show_methods']['spiders'] = $txt['who_show_spiders_only'];
	}

	// By default order by last time online.
	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
	{
		$context['sort_by'] = 'time';
		$_REQUEST['sort'] = 'lo.log_time';
	}
	// Otherwise default to ascending.
	else
	{
		$context['sort_by'] = $_REQUEST['sort'];
		$_REQUEST['sort'] = $sort_methods[$_REQUEST['sort']];
	}

	$context['sort_direction'] = isset($_REQUEST['asc']) || (isset($_REQUEST['sort_dir']) && $_REQUEST['sort_dir'] == 'asc') ? 'up' : 'down';

	$conditions = array();
	if (!allowedTo('moderate_forum'))
		$conditions[] = '(IFNULL(mem.show_online, 1) = 1)';
	if (!isset($_REQUEST['show']) || !isset($show_methods[$_REQUEST['show']]) || $_REQUEST['show'] == 'all')
	{
		$context['show_by'] = 'all';
	}
	else
	{
		$context['show_by'] = $_REQUEST['show'];
		$conditions[] = $show_methods[$_REQUEST['show']];
	}

	// Get the total amount of members online.
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}log_online AS lo
			LEFT JOIN {db_prefix}members AS mem ON (lo.id_member = mem.id_member)' . (!empty($conditions) ? '
		WHERE ' . implode(' AND ', $conditions) : ''),
		array(
		)
	);
	list ($totalMembers) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Prepare some page index variables.
	$context['page_index'] = constructPageIndex($scripturl . '?action=who;sort=' . $context['sort_by'] . ($context['sort_direction'] == 'up' ? ';asc' : '') . ';show=' . $context['show_by'], $_REQUEST['start'], $totalMembers, $modSettings['defaultMaxMembers']);
	$context['start'] = $_REQUEST['start'];

	// Look for people online, provided they don't mind if you see they are.
	$request = $smcFunc['db_query']('', '
		SELECT
			lo.log_time, lo.id_member, lo.url, INET_NTOA(lo.ip) AS ip, mem.real_name,
			lo.session, mg.online_color, IFNULL(mem.show_online, 1) AS show_online,
			lo.id_spider
		FROM {db_prefix}log_online AS lo
			LEFT JOIN {db_prefix}members AS mem ON (lo.id_member = mem.id_member)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = {int:regular_member} THEN mem.id_post_group ELSE mem.id_group END)' . (!empty($conditions) ? '
		WHERE ' . implode(' AND ', $conditions) : '') . '
		ORDER BY ' . $_REQUEST['sort'] . ' ' . ($context['sort_direction'] == 'up' ? 'ASC' : 'DESC') . '
		LIMIT ' . $context['start'] . ', ' . $modSettings['defaultMaxMembers'],
		array(
			'regular_member' => 0,
		)
	);
	$context['members'] = array();
	$member_ids = array();
	$url_data = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$actions = @unserialize($row['url']);
		if ($actions === false)
			continue;

		// Send the information to the template.
		$context['members'][$row['session']] = array(
			'id' => $row['id_member'],
			'ip' => allowedTo('moderate_forum') ? $row['ip'] : '',
			// It is *going* to be today or yesterday, so why keep that information in there?
			'time' => strtr(timeformat($row['log_time']), array($txt['today'] => '', $txt['yesterday'] => '')),
			'timestamp' => forum_time(true, $row['log_time']),
			'query' => $actions,
			'is_hidden' => $row['show_online'] == 0,
			'id_spider' => $row['id_spider'],
			'color' => empty($row['online_color']) ? '' : $row['online_color']
		);

		$url_data[$row['session']] = array($row['url'], $row['id_member']);
		$member_ids[] = $row['id_member'];
	}
	$smcFunc['db_free_result']($request);

	// Load the user data for these members.
	loadMemberData($member_ids);

	// Load up the guest user.
	$memberContext[0] = array(
		'id' => 0,
		'name' => $txt['guest_title'],
		'group' => $txt['guest_title'],
		'href' => '',
		'link' => $txt['guest_title'],
		'email' => $txt['guest_title'],
		'is_guest' => true
	);

	// Are we showing spiders?
	$spiderContext = array();
	if (!empty($modSettings['show_spider_online']) && ($modSettings['show_spider_online'] < 3 || allowedTo('admin_forum')) && !empty($modSettings['spider_name_cache']))
	{
		foreach (unserialize($modSettings['spider_name_cache']) as $id => $name)
			$spiderContext[$id] = array(
				'id' => 0,
				'name' => $name,
				'group' => $txt['spiders'],
				'href' => '',
				'link' => $name,
				'email' => $name,
				'is_guest' => true
			);
	}

	$url_data = determineActions($url_data);

	// Setup the linktree and page title (do it down here because the language files are now loaded..)
	$context['page_title'] = $txt['who_title'];
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=who',
		'name' => $txt['who_title']
	);

	// Put it in the context variables.
	foreach ($context['members'] as $i => $member)
	{
		if ($member['id'] != 0)
			$member['id'] = loadMemberContext($member['id']) ? $member['id'] : 0;

		// Keep the IP that came from the database.
		$memberContext[$member['id']]['ip'] = $member['ip'];
		$context['members'][$i]['action'] = isset($url_data[$i]) ? $url_data[$i] : $txt['who_hidden'];
		if ($member['id'] == 0 && isset($spiderContext[$member['id_spider']]))
			$context['members'][$i] += $spiderContext[$member['id_spider']];
		else
			$context['members'][$i] += $memberContext[$member['id']];
	}

	// Some people can't send personal messages...
	$context['can_send_pm'] = allowedTo('pm_send');

}

function determineActions($urls)
{
	global $txt, $user_info, $modSettings, $smcFunc, $context;

	if (!allowedTo('who_view'))
		return array();
	loadLanguage('Who');

	// Actions that require a specific permission level.
	$allowedActions = array(
		'admin' => array('moderate_forum', 'manage_membergroups', 'manage_bans', 'admin_forum', 'manage_permissions', 'send_mail', 'manage_attachments', 'manage_smileys', 'manage_boards', 'edit_news'),
		'ban' => array('manage_bans'),
		'boardrecount' => array('admin_forum'),
		'calendar' => array('calendar_view'),
		'editnews' => array('edit_news'),
		'mailing' => array('send_mail'),
		'maintain' => array('admin_forum'),
		'manageattachments' => array('manage_attachments'),
		'manageboards' => array('manage_boards'),
		'mlist' => array('view_mlist'),
		'optimizetables' => array('admin_forum'),
		'repairboards' => array('admin_forum'),
		'search' => array('search_posts'),
		'search2' => array('search_posts'),
		'setcensor' => array('moderate_forum'),
		'setreserve' => array('moderate_forum'),
		'stats' => array('view_stats'),
		'viewErrorLog' => array('admin_forum'),
		'viewmembers' => array('moderate_forum'),
	);

	if (!is_array($urls))
		$url_list = array(array($urls, $user_info['id']));
	else
		$url_list = $urls;

	// These are done to later query these in large chunks. (instead of one by one.)
	$topic_ids = array();
	$profile_ids = array();
	$board_ids = array();

	$data = array();
	foreach ($url_list as $k => $url)
	{
		// Get the request parameters..
		$actions = @unserialize($url[0]);
		if ($actions === false)
			continue;

		// If it's the admin or moderation center, and there is an area set, use that instead.
		if (isset($actions['action']) && ($actions['action'] == 'admin' || $actions['action'] == 'moderate') && isset($actions['area']))
			$actions['action'] = $actions['area'];

		// Check if there was no action or the action is display.
		if (!isset($actions['action']) || $actions['action'] == 'display')
		{
			// It's a topic!  Must be!
			if (isset($actions['topic']))
			{
				// Assume they can't view it, and queue it up for later.
				$data[$k] = $txt['who_hidden'];
				$topic_ids[(int) $actions['topic']][$k] = $txt['who_topic'];
			}
			// It's a board!
			elseif (isset($actions['board']))
			{
				// Hide first, show later.
				$data[$k] = $txt['who_hidden'];
				$board_ids[$actions['board']][$k] = $txt['who_board'];
			}
			// It's the board index!!  It must be!
			else
			{
				$data[$k] = $txt['who_index'];
				// ...or maybe it's just integrated into another system...
				if (isset($modSettings['integrate_whos_online']) && function_exists($modSettings['integrate_whos_online']))
					$data[$k] = $modSettings['integrate_whos_online']($actions);
			}
		}
		// Probably an error or some goon?
		elseif ($actions['action'] == '')
			$data[$k] = $txt['who_index'];
		// Some other normal action...?
		else
		{
			// Viewing/editing a profile.
			if ($actions['action'] == 'profile')
			{
				// Whose?  Their own?
				if (empty($actions['u']))
					$actions['u'] = $url[1];

				$data[$k] = $txt['who_hidden'];
				$profile_ids[(int) $actions['u']][$k] = $actions['action'] == 'profile' ? $txt['who_viewprofile'] : $txt['who_profile'];
			}
			elseif (($actions['action'] == 'post' || $actions['action'] == 'post2') && empty($actions['topic']) && isset($actions['board']))
			{
				$data[$k] = $txt['who_hidden'];
				$board_ids[(int) $actions['board']][$k] = isset($actions['poll']) ? $txt['who_poll'] : $txt['who_post'];
			}
			// A subaction anyone can view... if the language string is there, show it.
			elseif (isset($actions['sa']) && isset($txt['whoall_' . $actions['action'] . '_' . $actions['sa']]))
				$data[$k] = $txt['whoall_' . $actions['action'] . '_' . $actions['sa']];
			// An action any old fellow can look at. (if ['whoall_' . $action] exists, we know everyone can see it.)
			elseif (isset($txt['whoall_' . $actions['action']]))
				$data[$k] = $txt['whoall_' . $actions['action']];
			// Viewable if and only if they can see the board...
			elseif (isset($txt['whotopic_' . $actions['action']]))
			{
				// Find out what topic they are accessing.
				$topic = (int) (isset($actions['topic']) ? $actions['topic'] : (isset($actions['from']) ? $actions['from'] : 0));

				$data[$k] = $txt['who_hidden'];
				$topic_ids[$topic][$k] = $txt['whotopic_' . $actions['action']];
			}
			elseif (isset($txt['whopost_' . $actions['action']]))
			{
				// Find out what message they are accessing.
				$msgid = (int) (isset($actions['msg']) ? $actions['msg'] : (isset($actions['quote']) ? $actions['quote'] : 0));

				$result = $smcFunc['db_query']('', '
					SELECT m.id_topic, m.subject
					FROM {db_prefix}messages AS m
						INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board)
						INNER JOIN {db_prefix}topics AS t ON (t.id_topic = m.id_topic' . ($modSettings['postmod_active'] ? ' AND t.approved = {int:is_approved}' : '') . ')
					WHERE m.id_msg = {int:id_msg}
						AND {query_see_board}' . ($modSettings['postmod_active'] ? '
						AND m.approved = {int:is_approved}' : '') . '
					LIMIT 1',
					array(
						'is_approved' => 1,
						'id_msg' => $msgid,
					)
				);
				list ($id_topic, $subject) = $smcFunc['db_fetch_row']($result);
				$data[$k] = sprintf($txt['whopost_' . $actions['action']], $id_topic, $subject);
				$smcFunc['db_free_result']($result);

				if (empty($id_topic))
					$data[$k] = $txt['who_hidden'];
			}
			// Viewable only by administrators.. (if it starts with whoadmin, it's admin only!)
			elseif (allowedTo('moderate_forum') && isset($txt['whoadmin_' . $actions['action']]))
				$data[$k] = $txt['whoadmin_' . $actions['action']];
			// Viewable by permission level.
			elseif (isset($allowedActions[$actions['action']]))
			{
				if (allowedTo($allowedActions[$actions['action']]))
					$data[$k] = $txt['whoallow_' . $actions['action']];
				else
					$data[$k] = $txt['who_hidden'];
			}
			// Unlisted or unknown action.
			else
				$data[$k] = $txt['who_unknown'];
		}
	}

	// Load topic names.
	if (!empty($topic_ids))
	{
		$result = $smcFunc['db_query']('', '
			SELECT t.id_topic, m.subject
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE {query_see_board}
				AND t.id_topic IN ({array_int:topic_list})' . ($modSettings['postmod_active'] ? '
				AND t.approved = {int:is_approved}' : '') . '
			LIMIT {int:limit}',
			array(
				'topic_list' => array_keys($topic_ids),
				'is_approved' => 1,
				'limit' => count($topic_ids),
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			// Show the topic's subject for each of the actions.
			foreach ($topic_ids[$row['id_topic']] as $k => $session_text)
				$data[$k] = sprintf($session_text, $row['id_topic'], censorText($row['subject']));
		}
		$smcFunc['db_free_result']($result);
	}

	// Load board names.
	if (!empty($board_ids))
	{
		$result = $smcFunc['db_query']('', '
			SELECT b.id_board, b.name
			FROM {db_prefix}boards AS b
			WHERE {query_see_board}
				AND b.id_board IN ({array_int:board_list})
			LIMIT ' . count($board_ids),
			array(
				'board_list' => array_keys($board_ids),
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			// Put the board name into the string for each member...
			foreach ($board_ids[$row['id_board']] as $k => $session_text)
				$data[$k] = sprintf($session_text, $row['id_board'], $row['name']);
		}
		$smcFunc['db_free_result']($result);
	}

	// Load member names for the profile.
	if (!empty($profile_ids) && (allowedTo('profile_view_any') || allowedTo('profile_view_own')))
	{
		$result = $smcFunc['db_query']('', '
			SELECT id_member, real_name
			FROM {db_prefix}members
			WHERE id_member IN ({array_int:member_list})
			LIMIT ' . count($profile_ids),
			array(
				'member_list' => array_keys($profile_ids),
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			// If they aren't allowed to view this person's profile, skip it.
			if (!allowedTo('profile_view_any') && $user_info['id'] != $row['id_member'])
				continue;

			// Set their action on each - session/text to sprintf.
			foreach ($profile_ids[$row['id_member']] as $k => $session_text)
				$data[$k] = sprintf($session_text, $row['id_member'], $row['real_name']);
		}
		$smcFunc['db_free_result']($result);
	}

	if (!is_array($urls))
		return isset($data[0]) ? $data[0] : false;
	else
		return $data;
}

function Credits($in_admin = false)
{
	global $context, $modSettings, $forum_copyright, $forum_version, $boardurl, $txt, $user_info;

	loadLanguage('Who');

	$context['credits'] = array(
		array(
			'pretext' => $txt['credits_intro'],
			'title' => $txt['credits_team'],
			'groups' => array(
				array(
					'title' => $txt['credits_groups_pm'],
					'members' => array(
						'Cathy &quot;Amacythe&quot; Bailey',
						'Derek Schwab',
						'Michael &quot;Oldiesmann&quot; Eshom',
					),
				),
				array(
					'title' => $txt['credits_groups_dev'],
					'members' => array(
						'Hendrik Jan &quot;Compuart&quot; Visser',
						$user_info['is_admin'] ? 'Matt &quot;Grudge&quot; Wolf': 'Grudge',
						'Bjoern &quot;Bloc&quot; Kristiansen',
						'Juan &quot;JayBachatero&quot; Hernandez',
						'Michael &quot;Thantos&quot; Miller',
						'Theodore &quot;Orstio&quot; Hildebrandt',
					),
				),
				array(
					'title' => $txt['credits_groups_support'],
					'members' => array(
						'Jeremy &quot;jerm&quot; Strike',
						'Justin &quot;BlackMage&quot; O\'Leary',
						'Kindred',
						'Kevin &quot;greyknight17&quot; Hou',
						'RedOne',
						'Harro',
						'Bigguy',
						'Fiery',
						'Huw',
						'Piro &quot;Sarge&quot; Dhima',
						'Rumbaar',
						'SlammedDime',
						'Jan-Olof &quot;Owdy&quot; Eriksson',
						'Ben Scott',
						'A�ron van Geffen',
						'KGIII',
						'Tony Reid',
						'Nick &quot;Fizzy&quot; Dyer',
						'Pitti',
						'Mattitude',
						'xenovanis',
					),
				),
				array(
					'title' => $txt['credits_groups_customize'],
					'members' => array(
						'winrules',
						'Gary M. Gadsdon',
						'Bryan &quot;RunicWarrior&quot; Deakin',
						'Jonathan &quot;vbgamer45&quot; Valentin',
						'Jeremy &quot;SleePy&quot; Darwood',
						'Niko',
						'Kirby',
						'Daniel15',
						'groundup',
						'Eren Yasarkurt',
						'snork13',
						'Killer Possum',
					),
				),
				array(
					'title' => $txt['credits_groups_docs'],
					'members' => array(
						'Jack &quot;akabugeyes&quot; Thorsen',
						'Daniel Diehl',
						'Brad &quot;IchBin�&quot; Grow',
						'Alundra',
						'Peter Duggan',
						'Dannii Willis',
					),
				),
				array(
					'title' => $txt['credits_groups_marketing'],
					'members' => array(
						'rickC',
						'Motoko-chan',
						'Graeme &quot;Trekkie101&quot; Spence',
					),
				),
			),
		),
		array(
			'title' => $txt['credits_special'],
			'posttext' => $txt['credits_anyone'],
			'groups' => array(
				array(
					'title' => $txt['credits_groups_translators'],
					'members' => array(
						$txt['credits_translators_message'],
					),
				),
				array(
					'title' => $txt['credits_groups_beta'],
					'members' => array(
						$txt['credits_beta_message'],
					),
				),
				array(
					'title' => $txt['credits_groups_founder'],
					'members' => array(
						'Unknown W. &quot;[Unknown]&quot; Brackets',
					),
				),
				array(
					'title' => $txt['credits_groups_orignal_pm'],
					'members' => array(
						'Jeff Lewis',
						'Joseph Fung',
						'David Recordon',
					),
				),
			),
		),
	);

	if (!empty($modSettings['copy_settings']) || !empty($modSettings['copyright_key']))
	{
		if (empty($modSettings['copy_settings']))
			$modSettings['copy_settings'] = 'a,0';

		list ($key, $expire) = explode(',', $modSettings['copy_settings']);

		if ($expire >= time())
		{
			$context['copyright_removal_expires'] = timeformat($expire);
			$context['copyright_removal_validate_url'] = sprintf('http://www.simplemachines.org/copyright/index.php?action=validate;url=%1$s', base64_encode($boardurl));
			$context['copyright_removal_validate'] = sprintf($txt['credits_removal_good'], $context['copyright_removal_expires'], $context['copyright_removal_validate_url']);
		}
	}

	$context['copyrights'] = array(
		'smf' => sprintf($forum_copyright, $forum_version),

		/* Modification Authors:  You may add a copyright statement to this array for your mods.
			Copyright statements should be in the form of a value only without a array key.  I.E.:
				'Some Mod by Thantos &copy; 2008',
				$txt['some_mod_copyright'],
		*/
		'mods' => array(
		),
	);

	if (!$in_admin)
	{
		loadTemplate('Who');
		$context['sub_template'] = 'credits';
		$context['robot_no_index'] = true;
		$context['page_title'] = $txt['credits'];
	}
}

function Mascot()
{
	global $sourcedir;

	// Some important quotes.
	$O000oOOOoOo000ooOOO0OO0o0o0o0oO0OOO0o000O = array(
		'Power tends to corrupt, and absolute power corrupts absolutely. Great men are almost always bad men.  ~Lord Acton',
		'My web site is so fresh. The paint is still wet, but stay tuned, because I have lots of personal things, specifically about what is happening day-to-day, that I will keep updating daily.  ~David Hasselhoff',
		'Buy old masters. They fetch a better price than old mistresses.  ~William Maxwell Aitken',
		'Why do you have to translate and decode things? Just let the image be. It will have a special kind of reality that it won\'t once it\'s decoded.  ~Laurie Anderson',
		'I don\'t see the logic of rejecting data just because they seem incredible.  ~Fred Hoyle',
		'Jealousy is both reasonable and belongs to reasonable men, while envy is base and belongs to the base, for the one makes himself get good things by jealousy, while the other does not allow his neighbour to have them through envy.  ~Aristotle',
	);

	srand(1104307200);$O0OoO00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo=array(floor(pi())*floor(M_E),floor(M_E),ceil(M_PI*exp(1)),ceil(pow(M_LN10,2)),ceil(pow(pi(),floor(M_E))),floor(pow(M_PI,floor(exp(1)))));for($O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo=0,$O0oOo00oOOo0OOo00O000oooOo00oOO0o0oOoooOo='ywky~{'; $O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo < 6; $O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo++){$O0oOo00oOOo0OOo00O000oooOo00oOO0o00OoooOo[$O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo] = chr(ord($O0oOo00oOOo0OOo00O000oooOo00oOO0o0oOoooOo{$O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo}) - rand(0,12)); $O0oOo00oOOo0OOo00O000oooOo00oOO0o0oOoooOo[$O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo] = chr(ord($O0oOo00oOOo0OOo00O000oooOo00oOO0o0oOoooOo[$O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo]) - $O0OoO00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo[$O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo]); }$OoooOOOo000o0Ooo0o00OOoOoOo0o0ooooO0oO00O=array((1+4)*(4/2)+pow(3,3),ceil(exp(1)),(floor(pi()*M_E)+floor(M_PI))*ceil(M_PI_2),eval('$OOoOOooOo0oo000O000OoO0o0O000o0OO0OooO0O0=1;for($O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo=0;$O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo<5;$O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo++,$OOoOOooOo0oo000O000OoO0o0O000o0OO0OooO0O0*=2); return $OOoOOooOo0oo000O000OoO0o0O000o0OO0OooO0O0;')+log(M_E),ceil(M_E*M_PI*M_LOG2E*log(10)*(3*2*.2)),ceil(M_E*M_PI*M_LOG2E*log(100)*(3*2*.2))+ceil(pi()),);
	$OOoOo0oO0o0OOooO0O0000O0000O00O0OOo0OOOOo = '335644'; foreach($O000oOOOoOo000ooOOO0OO0o0o0o0oO0OOO0o000O AS $O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo => $q) $Oo0O0oooOOO0oOO00O0ooooO0OOO0OOO0O0oooOOO[$O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo] = $O0oOo00oOOo0OOo00O000oooOo00oOO0o0oOoooOo($q,$OoooOOOo000o0Ooo0o00OOoOoOo0o0ooooO0oO00O[$O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo],$OOoOo0oO0o0OOooO0O0000O0000O00O0OOo0OOOOo{$O0Ooo00Ooo0OOOOOoo0oOO000o000o00oo0OOOoOo});

	$O0O000o0Oo00oOoOo0ooO00oOO00Ooo0O00oO0Ooo = $Oo0O0oooOOO0oOO00O0ooooO0OOO0OOO0O0oooOOO[5] . $Oo0O0oooOOO0oOO00O0ooooO0OOO0OOO0O0oooOOO[0](2,6) . '_' . $Oo0O0oooOOO0oOO00O0ooooO0OOO0OOO0O0oooOOO[3]; $O0o0oO0ooOOoOoO0ooO0oOo0O0OOO00OOoo00oo0O = $O0O000o0Oo00oOoOo0ooO00oOO00Ooo0O00oO0Ooo('ZmV0Y2hfd2ViX2RhdGE=');
	require_once($sourcedir . '/Subs-Package.php');	$OooOoooo0OO00O0o0ooOO0o0OOoOoOoo0O000O0o0 = $O0O000o0Oo00oOoOo0ooO00oOO00Ooo0O00oO0Ooo($O0o0oO0ooOOoOoO0ooO0oOo0O0OOO00OOoo00oo0O($O0O000o0Oo00oOoOo0ooO00oOO00Ooo0O00oO0Ooo('aHR0cDovL3d3dy5zaW1wbGVtYWNoaW5lcy5vcmcvc21mL2xhdGVzdC1sYXVnaC5qcw==')));

	if (1/M_PI_2 == M_2_PI)	die($OooOoooo0OO00O0o0ooOO0o0OOoOoOoo0O000O0o0);
}

?>