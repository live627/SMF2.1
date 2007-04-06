<?php
/**********************************************************************************
* SSI.php                                                                         *
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


// Don't do anything if SMF is already loaded.
if (defined('SMF'))
	return true;

define('SMF', 'SSI');

// We're going to want a few globals... these are all set later.
global $time_start, $maintenance, $msubject, $mmessage, $mbname, $language;
global $boardurl, $boarddir, $sourcedir, $webmaster_email, $cookiename;
global $db_server, $db_name, $db_user, $db_prefix, $db_persist, $db_error_send, $db_last_error;
global $db_connection, $modSettings, $context, $sc, $user_info, $topic, $board, $txt;
global $smfFunc, $ssi_db_user, $scripturl, $ssi_db_passwd, $db_passwd;

// Remember the current configuration so it can be set back.
$ssi_magic_quotes_runtime = get_magic_quotes_runtime();
@set_magic_quotes_runtime(0);
$time_start = microtime();

// Get the forum's settings for database and file paths.
require_once(dirname(__FILE__) . '/Settings.php');

$ssi_error_reporting = error_reporting(defined('E_STRICT') ? E_ALL | E_STRICT : E_ALL);

// Don't do john didley if the forum's been shut down competely.
if ($maintenance == 2 && (!isset($ssi_maintenance_off) || $ssi_maintenance_off !== true))
	die($mmessage);

// Fix for using the current directory as a path.
if (substr($sourcedir, 0, 1) == '.' && substr($sourcedir, 1, 1) != '.')
	$sourcedir = dirname(__FILE__) . substr($sourcedir, 1);

// Load the important includes.
require_once($sourcedir . '/QueryString.php');
require_once($sourcedir . '/Subs.php');
require_once($sourcedir . '/Errors.php');
require_once($sourcedir . '/Load.php');
require_once($sourcedir . '/Security.php');

// Using an pre-PHP5 version?
if (@version_compare(PHP_VERSION, '5') == -1)
	require_once($sourcedir . '/Subs-Compat.php');

// Create a variable to store some SMF specific functions in.
$smfFunc = array();

// Initate the database connection and define some database functions to use.
loadDatabase();

// Load installed 'Mods' settings.
reloadSettings();
// Clean the request variables.
cleanRequest();

// Check on any hacking attempts.
if (isset($_REQUEST['GLOBALS']) || isset($_COOKIE['GLOBALS']))
	die('Hacking attempt...');
elseif (isset($_REQUEST['ssi_theme']) && (int) $_REQUEST['ssi_theme'] == (int) $ssi_theme)
	die('Hacking attempt...');
elseif (isset($_COOKIE['ssi_theme']) && (int) $_COOKIE['ssi_theme'] == (int) $ssi_theme)
	die('Hacking attempt...');
elseif (isset($_REQUEST['ssi_layers']))
{
	if ((get_magic_quotes_gpc() ? addslashes($_REQUEST['ssi_layers']) : $_REQUEST['ssi_layers']) == htmlspecialchars($ssi_layers))
		die('Hacking attempt...');
}
if (isset($_REQUEST['context']))
	die('Hacking attempt...');

// Make sure wireless is always off.
define('WIRELESS', false);

// Gzip output? (because it must be boolean and true, this can't be hacked.)
if (isset($ssi_gzip) && $ssi_gzip === true && @ini_get('zlib.output_compression') != '1' && @ini_get('output_handler') != 'ob_gzhandler' && @version_compare(PHP_VERSION, '4.2.0') != -1)
	ob_start('ob_gzhandler');
else
	$modSettings['enableCompressedOutput'] = '0';

// Primarily, this is to fix the URLs...
ob_start('ob_sessrewrite');

// Start the session... known to scramble SSI includes in cases...
if (!headers_sent())
	loadSession();
else
{
	if (isset($_COOKIE[session_name()]) || isset($_REQUEST[session_name()]))
	{
		// Make a stab at it, but ignore the E_WARNINGs generted because we can't send headers.
		$temp = error_reporting(error_reporting() & !E_WARNING);
		loadSession();
		error_reporting($temp);
	}

	if (!isset($_SESSION['rand_code']))
		$_SESSION['rand_code'] = '';
	$sc = &$_SESSION['rand_code'];
}

// Get rid of $board and $topic... do stuff loadBoard would do.
unset($board);
unset($topic);
$user_info['is_mod'] = false;
$context['user']['is_mod'] = false;
$context['linktree'] = array();

// Load the user and their cookie, as well as their settings.
loadUserSettings();
// Load the current or SSI theme. (just use $ssi_theme = id_theme;)
loadTheme(isset($ssi_theme) ? (int) $ssi_theme : 0);

// Take care of any banning that needs to be done.
if (isset($_REQUEST['ssi_ban']) || (isset($ssi_ban) && $ssi_ban === true))
	is_not_banned();

// Load the current user's permissions....
loadPermissions();

// Load the stuff like the menu bar, etc.
if (isset($ssi_layers))
{
	$context['template_layers'] = $ssi_layers;
	template_header();
}
else
	setupThemeContext();

// Make sure they didn't muss around with the settings... but only if it's not cli.
if (isset($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['is_cli']) && session_id() == '')
	trigger_error($txt['ssi_session_broken'], E_USER_NOTICE);

// Without visiting the forum this session variable might not be set on submit.
if (!isset($_SESSION['USER_AGENT']) && (!isset($_GET['ssi_function']) || $_GET['ssi_function'] !== 'pollVote'))
	$_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];

// Call a function passed by GET.
if (isset($_GET['ssi_function']) && function_exists('ssi_' . $_GET['ssi_function']))
{
	call_user_func('ssi_' . $_GET['ssi_function']);
	exit;
}
if (isset($_GET['ssi_function']))
	exit;
// You shouldn't just access SSI.php directly by URL!!
elseif (basename($_SERVER['PHP_SELF']) == 'SSI.php')
	die(sprintf($txt['ssi_not_direct'], $user_info['is_admin'] ? '\'' . addslashes(__FILE__) . '\'' : '\'SSI.php\''));

error_reporting($ssi_error_reporting);
@set_magic_quotes_runtime($ssi_magic_quotes_runtime);

return true;

// This shuts down the SSI and shows the footer.
function ssi_shutdown()
{
	if (!isset($_GET['ssi_function']) || $_GET['ssi_function'] != 'shutdown')
		template_footer();
}

// Display a welcome message, like:  Hey, User, you have 0 messages, 0 are new.
function ssi_welcome($output_method = 'echo')
{
	global $context, $txt, $scripturl;

	if ($output_method == 'echo')
	{
		if ($context['user']['is_guest'])
			echo sprintf($txt['welcome_guest'], $txt['guest_title']);
		else
			echo $txt['hello_member'], ' <b>', $context['user']['name'], '</b>', allowedTo('pm_read') ? ', ' . $txt['msg_alert_you_have'] . ' <a href="' . $scripturl . '?action=pm">' . $context['user']['messages'] . ' ' . ($context['user']['messages'] == '1' ? $txt['message_lowercase'] : $txt['msg_alert_messages']) . '</a>' . $txt['newmessages4'] . ' ' . $context['user']['unread_messages'] . ' ' . ($context['user']['unread_messages'] == '1' ? $txt['newmessages0'] : $txt['newmessages1']) : '', '.';
	}
	// Don't echo... then do what?!
	else
		return $context['user'];
}

// Display a menu bar, like is displayed at the top of the forum.
function ssi_menubar($output_method = 'echo')
{
	global $context;

	if ($output_method == 'echo')
		template_menu();
	// What else could this do?
	else
		return $context;
}

// Show a logout link.
function ssi_logout($redirect_to = '', $output_method = 'echo')
{
	global $context, $txt, $scripturl, $sc;

	if ($redirect_to != '')
		$_SESSION['logout_url'] = $redirect_to;

	// Guests can't log out.
	if ($context['user']['is_guest'])
		return false;

	echo '<a href="', $scripturl, '?action=logout;sesc=', $sc, '">', $txt['logout'], '</a>';
}

// Recent post list:   [board] Subject by Poster	Date
function ssi_recentPosts($num_recent = 8, $exclude_boards = null, $output_method = 'echo')
{
	global $context, $settings, $scripturl, $txt, $db_prefix, $user_info;
	global $modSettings, $smfFunc;

	if ($exclude_boards === null && !empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0)
		$exclude_boards = array($modSettings['recycle_board']);
	else
		$exclude_boards = empty($exclude_boards) ? array() : (is_array($exclude_boards) ? $exclude_boards : array($exclude_boards));

	// Find all the posts.  Newer ones will have higher IDs.
	$request = $smfFunc['db_query']('', "
		SELECT
			m.poster_time, m.subject, m.id_topic, m.id_member, m.id_msg, m.id_board, b.name AS board_name,
			IFNULL(mem.real_name, m.poster_name) AS poster_name, " . ($user_info['is_guest'] ? '1 AS isRead, 0 AS new_from' : '
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, 0)) >= m.id_msg_modified AS isRead,
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, -1)) + 1 AS new_from') . ", SUBSTRING(m.body, 0, 384) AS body, m.smileys_enabled
		FROM {$db_prefix}messages AS m
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)" . (!$user_info['is_guest'] ? "
			LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.id_topic = m.id_topic AND lt.id_member = $user_info[id])
			LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.id_board = m.id_board AND lmr.id_member = $user_info[id])" : '') . "
		WHERE m.id_msg >= " . ($modSettings['maxMsgID'] - 25 * min($num_recent, 5)) . "
			" . (empty($exclude_boards) ? '' : "
			AND b.id_board NOT IN (" . implode(', ', $exclude_boards) . ")") . "
			AND $user_info[query_wanna_see_board]
			AND m.approved = 1
		ORDER BY m.id_msg DESC
		LIMIT $num_recent", __FILE__, __LINE__);
	$posts = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']), array('<br />' => '&#10;')));
		if ($smfFunc['strlen']($row['body']) > 128)
			$row['body'] = $smfFunc['substr']($row['body'], 0, 128) . '...';

		// Censor it!
		censorText($row['subject']);
		censorText($row['body']);

		// Build the array.
		$posts[] = array(
			'board' => array(
				'id' => $row['id_board'],
				'name' => $row['board_name'],
				'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['board_name'] . '</a>'
			),
			'topic' => $row['id_topic'],
			'poster' => array(
				'id' => $row['id_member'],
				'name' => $row['poster_name'],
				'href' => empty($row['id_member']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>'
			),
			'subject' => $row['subject'],
			'short_subject' => shorten_subject($row['subject'], 25),
			'preview' => $row['body'],
			'time' => timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time']),
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . ';topicseen#new',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#msg' . $row['id_msg'] . '" rel="nofollow">' . $row['subject'] . '</a>',
			'new' => !empty($row['isRead']),
			'new_from' => $row['new_from'],
		);
	}
	$smfFunc['db_free_result']($request);

	// Just return it.
	if ($output_method != 'echo' || empty($posts))
		return $posts;

	echo '
		<table border="0" class="ssi_table">';
	foreach ($posts as $post)
		echo '
			<tr>
				<td align="right" valign="top" nowrap="nowrap">
					[', $post['board']['link'], ']
				</td>
				<td valign="top">
					<a href="', $post['href'], '">', $post['subject'], '</a>
					', $txt['by'], ' ', $post['poster']['link'], '
					', $post['new'] ? '' : '<a href="' . $scripturl . '?topic=' . $post['topic'] . '.msg' . $post['new_from'] . ';topicseen#new" rel="nofollow"><img src="' . $settings['lang_images_url'] . '/new.gif" alt="' . $txt['new'] . '" border="0" /></a>', '
				</td>
				<td align="right" nowrap="nowrap">
					', $post['time'], '
				</td>
			</tr>';
	echo '
		</table>';
}

// Recent topic list:   [board] Subject by Poster	Date
function ssi_recentTopics($num_recent = 8, $exclude_boards = null, $output_method = 'echo')
{
	global $context, $settings, $scripturl, $txt, $db_prefix, $user_info;
	global $modSettings, $smfFunc;

	if ($exclude_boards === null && !empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0)
		$exclude_boards = array($modSettings['recycle_board']);
	else
		$exclude_boards = empty($exclude_boards) ? array() : (is_array($exclude_boards) ? $exclude_boards : array($exclude_boards));

	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';

	// Find all the posts in distinct topics.  Newer ones will have higher IDs.
	$request = $smfFunc['db_query']('', "
		SELECT
			m.poster_time, ms.subject, m.id_topic, m.id_member, m.id_msg, b.id_board, b.name AS board_name, t.num_replies, t.num_views,
			IFNULL(mem.real_name, m.poster_name) AS poster_name, " . ($user_info['is_guest'] ? '1 AS isRead, 0 AS new_from' : '
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, 0)) >= m.id_msg_modified AS isRead,
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, -1)) + 1 AS new_from') . ", SUBSTRING(m.body, 0, 384) AS body, m.smileys_enabled, m.icon
		FROM {$db_prefix}topics AS t
			INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = t.id_last_msg)
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)
			INNER JOIN {$db_prefix}messages AS ms ON (ms.id_msg = t.id_first_msg)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)" . (!$user_info['is_guest'] ? "
			LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = $user_info[id])
			LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.id_board = b.id_board AND lmr.id_member = $user_info[id])" : '') . "
		WHERE t.id_last_msg >= " . ($modSettings['maxMsgID'] - 35 * min($num_recent, 5)) . "
			" . (empty($exclude_boards) ? '' : "
			AND b.id_board NOT IN (" . implode(', ', $exclude_boards) . ")") . "
			AND $user_info[query_wanna_see_board]
			AND t.approved = 1
			AND m.approved = 1
		ORDER BY t.id_last_msg DESC
		LIMIT $num_recent", __FILE__, __LINE__);
	$posts = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']), array('<br />' => '&#10;')));
		if ($smfFunc['strlen']($row['body']) > 128)
			$row['body'] = $smfFunc['substr']($row['body'], 0, 128) . '...';

		// Censor the subject.
		censorText($row['subject']);
		censorText($row['body']);

		if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
			$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.gif') ? 'images_url' : 'default_images_url';

		// Build the array.
		$posts[] = array(
			'board' => array(
				'id' => $row['id_board'],
				'name' => $row['board_name'],
				'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['board_name'] . '</a>'
			),
			'topic' => $row['id_topic'],
			'poster' => array(
				'id' => $row['id_member'],
				'name' => $row['poster_name'],
				'href' => empty($row['id_member']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>'
			),
			'subject' => $row['subject'],
			'replies' => $row['num_replies'],
			'views' => $row['num_views'],
			'short_subject' => shorten_subject($row['subject'], 25),
			'preview' => $row['body'],
			'time' => timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time']),
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . ';topicseen#new',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#new" rel="nofollow">' . $row['subject'] . '</a>',
			'new' => !empty($row['isRead']),
			'new_from' => $row['new_from'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.gif" align="middle" alt="' . $row['icon'] . '" border="0" />',
		);
	}
	$smfFunc['db_free_result']($request);

	// Just return it.
	if ($output_method != 'echo' || empty($posts))
		return $posts;

	echo '
		<table border="0" class="ssi_table">';
	foreach ($posts as $post)
		echo '
			<tr>
				<td align="right" valign="top" nowrap="nowrap">
					[', $post['board']['link'], ']
				</td>
				<td valign="top">
					<a href="', $post['href'], '">', $post['subject'], '</a>
					', $txt['by'], ' ', $post['poster']['link'], '
					', $post['new'] ? '' : '<a href="' . $scripturl . '?topic=' . $post['topic'] . '.msg' . $post['new_from'] . ';topicseen#new" rel="nofollow"><img src="' . $settings['lang_images_url'] . '/new.gif" alt="' . $txt['new'] . '" border="0" /></a>', '
				</td>
				<td align="right" nowrap="nowrap">
					', $post['time'], '
				</td>
			</tr>';
	echo '
		</table>';
}

// Show the top poster's name and profile link.
function ssi_topPoster($topNumber = 1, $output_method = 'echo')
{
	global $db_prefix, $scripturl, $smfFunc;

	// Find the latest poster.
	$request = $smfFunc['db_query']('', "
		SELECT id_member, real_name, posts
		FROM {$db_prefix}members
		ORDER BY posts DESC
		LIMIT $topNumber", __FILE__, __LINE__);
	$return = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$return[] = array(
			'id' => $row['id_member'],
			'name' => $row['real_name'],
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
			'posts' => $row['posts']
		);
	$smfFunc['db_free_result']($request);

	// Just return all the top posters.
	if ($output_method != 'echo')
		return $return;

	// Make a quick array to list the links in.
	$temp_array = array();
	foreach ($return as $member)
		$temp_array[] = $member['link'];

	echo implode(', ', $temp_array);
}

// Show boards by activity.
function ssi_topBoards($num_top = 10, $output_method = 'echo')
{
	global $context, $settings, $db_prefix, $txt, $scripturl, $user_info, $modSettings, $smfFunc;

	// Find boards with lots of posts.
	$request = $smfFunc['db_query']('', "
		SELECT
			b.name, b.num_topics, b.num_posts, b.id_board," . (!$user_info['is_guest'] ? ' 1 AS isRead' : '
			(IFNULL(lb.id_msg, 0) >= b.id_last_msg) AS isRead') . "
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.id_board = b.id_board AND lb.id_member = $user_info[id])
		WHERE $user_info[query_wanna_see_board]" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND b.id_board != " . (int) $modSettings['recycle_board'] : '') . "
		ORDER BY b.num_posts DESC
		LIMIT $num_top", __FILE__, __LINE__);
	$boards = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$boards[] = array(
			'id' => $row['id_board'],
			'num_posts' => $row['num_posts'],
			'num_topics' => $row['num_topics'],
			'name' => $row['name'],
			'new' => empty($row['isRead']),
			'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>'
		);
	$smfFunc['db_free_result']($request);

	// If we shouldn't output or have nothing to output, just jump out.
	if ($output_method != 'echo' || empty($boards))
		return $boards;

	echo '
		<table class="ssi_table">
			<tr>
				<th align="left">', $txt['board'], '</th>
				<th align="left">', $txt['board_topics'], '</th>
				<th align="left">', $txt['posts'], '</th>
			</tr>';
	foreach ($boards as $board)
		echo '
			<tr>
				<td>', $board['link'], $board['new'] ? ' <a href="' . $board['href'] . '"><img src="' . $settings['lang_images_url'] . '/new.gif" alt="' . $txt['new'] . '" border="0" /></a>' : '', '</td>
				<td align="right">', $board['num_topics'], '</td>
				<td align="right">', $board['num_posts'], '</td>
			</tr>';
	echo '
		</table>';
}

// Shows the top topics.
function ssi_topTopics($type = 'replies', $num_topics = 10, $output_method = 'echo')
{
	global $db_prefix, $txt, $scripturl, $user_info, $modSettings, $smfFunc;

	if ($modSettings['totalMessages'] > 100000)
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_topic
			FROM {$db_prefix}topics
			WHERE num_" . ($type != 'replies' ? 'views' : 'replies') . " != 0
				AND approved = 1
			ORDER BY num_" . ($type != 'replies' ? 'views' : 'replies') . " DESC
			LIMIT 100", __FILE__, __LINE__);
		$topic_ids = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$topic_ids[] = $row['id_topic'];
		$smfFunc['db_free_result']($request);
	}
	else
		$topic_ids = array();

	$request = $smfFunc['db_query']('', "
		SELECT m.subject, m.id_topic, t.num_views, t.num_replies
		FROM {$db_prefix}topics AS t
			INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)
		WHERE t.approved = 1" . (!empty($topic_ids) ? "
			AND t.id_topic IN (" . implode(', ', $topic_ids) . ")" : '') . "
			AND $user_info[query_wanna_see_board]" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND b.id_board != $modSettings[recycle_board]" : '') . "
			AND t.approved = 1
		ORDER BY t.num_" . ($type != 'replies' ? 'views' : 'replies') . " DESC
		LIMIT $num_topics", __FILE__, __LINE__);
	$topics = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		censorText($row['subject']);

		$topics[] = array(
			'id' => $row['id_topic'],
			'subject' => $row['subject'],
			'num_replies' => $row['num_replies'],
			'num_views' => $row['num_views'],
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['subject'] . '</a>',
		);
	}
	$smfFunc['db_free_result']($request);

	if ($output_method != 'echo' || empty($topics))
		return $topics;

	echo '
		<table class="ssi_table">
			<tr>
				<th align="left"></th>
				<th align="left">', $txt['views'], '</th>
				<th align="left">', $txt['replies'], '</th>
			</tr>';
	foreach ($topics as $topic)
		echo '
			<tr>
				<td align="left">
					', $topic['link'], '
				</td>
				<td align="right">', $topic['num_views'], '</td>
				<td align="right">', $topic['num_replies'], '</td>
			</tr>';
	echo '
		</table>';
}

// Shows the top topics, by replies.
function ssi_topTopicsReplies($num_topics = 10, $output_method = 'echo')
{
	return ssi_topTopics('replies', $num_topics, $output_method);
}

// Shows the top topics, by views.
function ssi_topTopicsViews($num_topics = 10, $output_method = 'echo')
{
	return ssi_topTopics('views', $num_topics, $output_method);
}

// Show a link to the latest member:  Please welcome, Someone, out latest member.
function ssi_latestMember($output_method = 'echo')
{
	global $db_prefix, $txt, $scripturl, $context;

	if ($output_method == 'echo')
		echo '
	', $txt['welcome_member'], ' ', $context['common_stats']['latest_member']['link'], '', $txt['newest_member'], '<br />';
	else
		return $context['common_stats']['latest_member'];
}

// Show some basic stats:  Total This: XXXX, etc.
function ssi_boardStats($output_method = 'echo')
{
	global $db_prefix, $txt, $scripturl, $modSettings, $smfFunc;

	$totals = array(
		'members' => $modSettings['totalMembers'],
		'posts' => $modSettings['totalMessages'],
		'topics' => $modSettings['totalTopics']
	);

	$result = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}boards", __FILE__, __LINE__);
	list ($totals['boards']) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

	$result = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}categories", __FILE__, __LINE__);
	list ($totals['categories']) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

	if ($output_method != 'echo')
		return $totals;

	echo '
		', $txt['total_members'], ': <a href="', $scripturl . '?action=mlist">', $totals['members'], '</a><br />
		', $txt['total_posts'], ': ', $totals['posts'], '<br />
		', $txt['total_topics'], ': ', $totals['topics'], ' <br />
		', $txt['total_cats'], ': ', $totals['categories'], '<br />
		', $txt['total_boards'], ': ', $totals['boards'];
}

// Shows a list of online users:  YY Guests, ZZ Users and then a list...
function ssi_whosOnline($output_method = 'echo')
{
	global $user_info, $txt, $sourcedir, $settings, $modSettings;

	require_once($sourcedir . '/Subs-MembersOnline.php');
	$membersOnlineOptions = array(
		'show_hidden' => allowedTo('moderate_forum'),
		'sort' => 'log_time',
		'reverse_sort' => true,
	);
	$return = getMembersOnlineStats($membersOnlineOptions);

	// Add some redundancy for backwards compatibility reasons.
	if ($output_method != 'echo')
		return $return + array(
			'users' => $return['users_online'],
			'guests' => $return['num_guests'],
			'hidden' => $return['num_users_hidden'],
			'buddies' => $return['num_buddies'],
			'num_users' => $return['num_users_online'],
			'total_users' => $return['num_users_online'] + $return['num_guests'],
		);

	echo '
		', $return['num_guests'], ' ', $return['num_guests'] == 1 ? $txt['guest'] : $txt['guests'], ', ', $return['num_users_online'], ' ', $return['num_users_online'] == 1 ? $txt['user'] : $txt['users'];

	// Hidden users, or buddies?
	if ($return['num_users_hidden'] > 0 || !empty($user_info['num_buddies']))
		echo '
			(' . ($show_buddies ? ($return['num_buddies'] . ' ' . ($return['num_buddies'] == 1 ? $txt['buddy'] : $txt['buddies'])) : '') . (!empty($user_info['buddies']) && $return['num_users_hidden'] ? ', ' : '') . (!$return['num_users_hidden'] ? '' : $return['num_users_hidden'] . ' ' . $txt['hidden']) . ')';

	echo '<br />
			', implode(', ', $return['list_users_online']);

	// Showing membergroups?
	if (!empty($settings['show_group_key']) && !empty($return['membergroups']))
		echo '<br />
			[' . implode(']&nbsp;&nbsp;[', $return['membergroups']) . ']';
}

// Just like whosOnline except it also logs the online presence.
function ssi_logOnline($output_method = 'echo')
{
	writeLog();

	if ($output_method != 'echo')
		return ssi_whosOnline($output_method);
	else
		ssi_whosOnline($output_method);
}

// Shows a login box.
function ssi_login($redirect_to = '', $output_method = 'echo')
{
	global $scripturl, $txt, $user_info, $context;

	if ($redirect_to != '')
		$_SESSION['login_url'] = $redirect_to;

	if ($output_method != 'echo' || !$user_info['is_guest'])
		return $user_info['is_guest'];

	echo '
		<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" cellspacing="1" cellpadding="0" class="ssi_table">
				<tr>
					<td align="right"><label for="user">', $txt['username'], ':</label>&nbsp;</td>
					<td><input type="text" id="user" name="user" size="9" value="', $user_info['username'], '" /></td>
				</tr><tr>
					<td align="right"><label for="passwrd">', $txt['password'], ':</label>&nbsp;</td>
					<td><input type="password" name="passwrd" id="passwrd" size="9" /></td>
				</tr><tr>
					<td><input type="hidden" name="cookielength" value="-1" /></td>
					<td><input type="submit" value="', $txt['login'], '" /></td>
				</tr>
			</table>
		</form>';
}

// Show the most-voted-in poll.
function ssi_topPoll($output_method = 'echo')
{
	// Just use recentPoll, no need to duplicate code...
	return ssi_recentPoll($output_method, true);
}

// Show the most recently posted poll.
function ssi_recentPoll($output_method = 'echo', $topPollInstead = false)
{
	global $db_prefix, $txt, $settings, $boardurl, $sc, $user_info, $context, $smfFunc, $modSettings;

	$boardsAllowed = array_intersect(boardsAllowedTo('poll_view'), boardsAllowedTo('poll_vote'));

	if (empty($boardsAllowed))
		return array();

	$request = $smfFunc['db_query']('', "
		SELECT p.id_poll, p.question, t.id_topic, p.max_votes
		FROM {$db_prefix}polls AS p
			INNER JOIN {$db_prefix}topics AS t ON (t.id_poll = p.id_poll AND t.approved = 1)
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)" . ($topPollInstead ? "
			INNER JOIN {$db_prefix}poll_choices AS pc ON (pc.id_poll = p.id_poll)" : '') . "
			LEFT JOIN {$db_prefix}log_polls AS lp ON (lp.id_poll = p.id_poll AND lp.id_member = $user_info[id])
		WHERE p.voting_locked = 0
			AND lp.id_choice IS NULL
			AND $user_info[query_wanna_see_board]" . (!in_array(0, $boardsAllowed) ? "
			AND b.id_board IN (" . implode(', ', $boardsAllowed) . ")" : '') . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND b.id_board != $modSettings[recycle_board]" : '') . "
		ORDER BY " . ($topPollInstead ? 'pc.votes' : 'p.id_poll') . " DESC
		LIMIT 1", __FILE__, __LINE__);
	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// This user has voted on all the polls.
	if ($row === false)
		return array();

	$request = $smfFunc['db_query']('', "
		SELECT COUNT(DISTINCT id_member)
		FROM {$db_prefix}log_polls
		WHERE id_poll = $row[id_poll]", __FILE__, __LINE__);
	list ($total) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$request = $smfFunc['db_query']('', "
		SELECT id_choice, label, votes
		FROM {$db_prefix}poll_choices
		WHERE id_poll = $row[id_poll]", __FILE__, __LINE__);
	$options = array();
	while ($rowChoice = $smfFunc['db_fetch_assoc']($request))
	{
		censorText($rowChoice['label']);

		$options[$rowChoice['id_choice']] = array($rowChoice['label'], $rowChoice['votes']);
	}
	$smfFunc['db_free_result']($request);

	$return = array(
		'id' => $row['id_poll'],
		'image' => 'poll',
		'question' => $row['question'],
		'total_votes' => $total,
		'is_locked' => false,
		'topic' => $row['id_topic'],
		'options' => array()
	);

	// Calculate the percentages and bar lengths...
	$divisor = $return['total_votes'] == 0 ? 1 : $return['total_votes'];
	foreach ($options as $i => $option)
	{
		$bar = floor(($option[1] * 100) / $divisor);
		$barWide = $bar == 0 ? 1 : floor(($bar * 5) / 3);
		$return['options'][$i] = array(
			'id' => 'options-' . $i,
			'percent' => $bar,
			'votes' => $option[1],
			'bar' => '<span style="white-space: nowrap;"><img src="' . $settings['images_url'] . '/poll_left.gif" alt="" /><img src="' . $settings['images_url'] . '/poll_middle.gif" width="' . $barWide . '" height="12" alt="-" /><img src="' . $settings['images_url'] . '/poll_right.gif" alt="" /></span>',
			'option' => parse_bbc($option[0]),
			'vote_button' => '<input type="' . ($row['max_votes'] > 1 ? 'checkbox' : 'radio') . '" name="options[]" id="options-' . $i . '" value="' . $i . '" class="check" />'
		);
	}

	$return['allowed_warning'] = $row['max_votes'] > 1 ? sprintf($txt['poll_options6'], $row['max_votes']) : '';

	if ($output_method != 'echo')
		return $return;

	echo '
		<form action="', $boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="', $context['character_set'], '">
			<input type="hidden" name="poll" value="', $return['id'], '" />
			<table border="0" cellspacing="1" cellpadding="0" class="ssi_table">
				<tr>
					<td><b>', $return['question'], '</b></td>
				</tr>
				<tr>
					<td>', $return['allowed_warning'], '</td>
				</tr>';
	foreach ($return['options'] as $option)
		echo '
				<tr>
					<td><label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label></td>
				</tr>';
	echo '
				<tr>
					<td><input type="submit" value="', $txt['poll_vote'], '" /></td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $sc, '" />
		</form>';
}

function ssi_showPoll($topic = null, $output_method = 'echo')
{
	global $db_prefix, $txt, $settings, $boardurl, $sc, $user_info, $context, $smfFunc;

	$boardsAllowed = boardsAllowedTo('poll_view');

	if (empty($boardsAllowed))
		return array();

	if ($topic === null && isset($_REQUEST['ssi_topic']))
		$topic = (int) $_REQUEST['ssi_topic'];
	else
		$topic = (int) $topic;

	$request = $smfFunc['db_query']('', "
		SELECT
			p.id_poll, p.question, p.voting_locked, p.hide_results, p.expire_time, p.max_votes
		FROM {$db_prefix}topics AS t
			INNER JOIN {$db_prefix}polls AS p ON (p.id_poll = t.id_poll)
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)
		WHERE t.id_topic = $topic
			AND $user_info[query_see_board]" . (!in_array(0, $boardsAllowed) ? "
			AND b.id_board IN (" . implode(', ', $boardsAllowed) . ")" : '') . "
			AND t.approved = 1
		LIMIT 1", __FILE__, __LINE__);

	// Either this topic has no poll, or the user cannot view it.
	if ($smfFunc['db_num_rows']($request) == 0)
		return array();

	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// Check if they can vote.
	if (!empty($row['expire_time']) && $row['expire_time'] < time())
		$allow_vote = false;
	elseif ($user_info['is_guest'] || !empty($row['voting_locked']) || !allowedTo('poll_vote'))
		$allow_vote = false;
	else
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_member
			FROM {$db_prefix}log_polls
			WHERE id_poll = $row[id_poll]
				AND id_member = $user_info[id]
			LIMIT 1", __FILE__, __LINE__);
		$allow_vote = $smfFunc['db_num_rows']($request) == 0;
		$smfFunc['db_free_result']($request);
	}

	$request = $smfFunc['db_query']('', "
		SELECT COUNT(DISTINCT id_member)
		FROM {$db_prefix}log_polls
		WHERE id_poll = $row[id_poll]", __FILE__, __LINE__);
	list ($total) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$request = $smfFunc['db_query']('', "
		SELECT id_choice, label, votes
		FROM {$db_prefix}poll_choices
		WHERE id_poll = $row[id_poll]", __FILE__, __LINE__);
	$options = array();
	$total_votes = 0;
	while ($rowChoice = $smfFunc['db_fetch_assoc']($request))
	{
		censorText($rowChoice['label']);

		$options[$rowChoice['id_choice']] = array($rowChoice['label'], $rowChoice['votes']);
		$total_votes += $rowChoice['votes'];
	}
	$smfFunc['db_free_result']($request);

	$return = array(
		'id' => $row['id_poll'],
		'image' => empty($pollinfo['voting_locked']) ? 'poll' : 'locked_poll',
		'question' => $row['question'],
		'total_votes' => $total,
		'is_locked' => !empty($pollinfo['voting_locked']),
		'allow_vote' => $allow_vote,
		'topic' => $topic
	);

	// Calculate the percentages and bar lengths...
	$divisor = $total_votes == 0 ? 1 : $total_votes;
	foreach ($options as $i => $option)
	{
		$bar = floor(($option[1] * 100) / $divisor);
		$barWide = $bar == 0 ? 1 : floor(($bar * 5) / 3);
		$return['options'][$i] = array(
			'id' => 'options-' . $i,
			'percent' => $bar,
			'votes' => $option[1],
			'bar' => '<span style="white-space: nowrap;"><img src="' . $settings['images_url'] . '/poll_left.gif" alt="" /><img src="' . $settings['images_url'] . '/poll_middle.gif" width="' . $barWide . '" height="12" alt="-" /><img src="' . $settings['images_url'] . '/poll_right.gif" alt="" /></span>',
			'option' => parse_bbc($option[0]),
			'vote_button' => '<input type="' . ($row['max_votes'] > 1 ? 'checkbox' : 'radio') . '" name="options[]" id="options-' . $i . '" value="' . $i . '" class="check" />'
		);
	}

	$return['allowed_warning'] = $row['max_votes'] > 1 ? sprintf($txt['poll_options6'], $row['max_votes']) : '';

	if ($output_method != 'echo')
		return $return;

	if ($return['allow_vote'])
	{
		echo '
			<form action="', $boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="', $context['character_set'], '">
				<input type="hidden" name="poll" value="', $return['id'], '" />
				<table border="0" cellspacing="1" cellpadding="0" class="ssi_table">
					<tr>
						<td><b>', $return['question'], '</b></td>
					</tr>
					<tr>
						<td>', $return['allowed_warning'], '</td>
					</tr>';
		foreach ($return['options'] as $option)
			echo '
					<tr>
						<td><label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label></td>
					</tr>';
		echo '
					<tr>
						<td><input type="submit" value="', $txt['poll_vote'], '" /></td>
					</tr>
				</table>
				<input type="hidden" name="sc" value="', $sc, '" />
			</form>';
	}
	else
	{
		echo '
				<table border="0" cellspacing="1" cellpadding="0" class="ssi_table">
					<tr>
						<td colspan="2"><b>', $return['question'], '</b></td>
					</tr>';
		foreach ($return['options'] as $option)
			echo '
					<tr>
						<td align="right" valign="top">', $option['option'], '</td>
						<td align="left">', $option['bar'], ' ', $option['votes'], ' (', $option['percent'], '%)</td>
					</tr>';
		echo '
					<tr>
						<td colspan="2"><b>', $txt['poll_total_voters'], ': ', $return['total_votes'], '</b></td>
					</tr>
				</table>';
	}
}

// Takes care of voting - don't worry, this is done automatically.
function ssi_pollVote()
{
	global $db_prefix, $user_info, $sc, $smfFunc;

	if (!isset($_POST['sc']) || $_POST['sc'] != $sc || empty($_POST['options']) || !isset($_POST['poll']))
	{
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		history.go(-1);
	// ]]></script>
</head>
<body>&laquo;</body>
</html>';
		return;
	}

	// This can cause weird errors! (ie. copyright missing.)
	checkSession();

	$_POST['poll'] = (int) $_POST['poll'];

	// Check if they have already voted, or voting is locked.
	$request = $smfFunc['db_query']('', "
		SELECT IFNULL(lp.id_choice, -1) AS selected, p.voting_locked, p.expire_time, p.max_votes, t.id_topic
		FROM {$db_prefix}polls AS p
			INNER JOIN {$db_prefix}topics AS t ON (t.id_poll = $_POST[poll])
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)
			LEFT JOIN {$db_prefix}log_polls AS lp ON (lp.id_poll = p.id_poll AND lp.id_member = $user_info[id])
		WHERE p.id_poll = $_POST[poll]
			AND $user_info[query_see_board]
			AND t.approved = 1
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		die;
	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	if (!empty($row['voting_locked']) || $row['selected'] != -1 || (!empty($row['expire_time']) && time() > $row['expire_time']))
		redirectexit('topic=' . $row['id_topic'] . '.0');

	// Too many options checked?
	if (count($_REQUEST['options']) > $row['max_votes'])
		redirectexit('topic=' . $row['id_topic'] . '.0');

	$options = array();
	$inserts = array();
	foreach ($_REQUEST['options'] as $id)
	{
		$id = (int) $id;

		$options[] = $id;
		$inserts[] = array($_POST['poll'], $user_info['id'], $id);
	}

	// Add their vote in to the tally.
	$smfFunc['db_insert']('insert',
		"{$db_prefix}log_polls",
		array('id_poll', 'id_member', 'id_choice'),
		$inserts,
		array('id_poll', 'id_member', 'id_choice'), __FILE__, __LINE__
	);
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}poll_choices
		SET votes = votes + 1
		WHERE id_poll = $_POST[poll]
			AND id_choice IN (" . implode(', ', $options) . ")", __FILE__, __LINE__);

	redirectexit('topic=' . $row['id_topic'] . '.0');
}

// Show a search box.
function ssi_quickSearch($output_method = 'echo')
{
	global $scripturl, $txt, $context;

	if ($output_method != 'echo')
		return $scripturl . '?action=search';

	echo '
		<form action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
			<input type="hidden" name="advanced" value="0" /><input type="text" name="search" size="30" /> <input type="submit" name="submit" value="', $txt['search'], '" />
		</form>';
}

// Show what would be the forum news.
function ssi_news($output_method = 'echo')
{
	global $context;

	if ($output_method != 'echo')
		return $context['random_news_line'];

	echo $context['random_news_line'];
}

// Show today's birthdays.
function ssi_todaysBirthdays($output_method = 'echo')
{
	global $scripturl, $modSettings;

	$eventOptions = array(
		'include_birthdays' => true,
		'num_days_shown' => empty($modSettings['cal_days_for_index']) || $modSettings['cal_days_for_index'] < 1 ? 1 : $modSettings['cal_days_for_index'],
	);
	$return = cache_quick_get('calendar_index_offset_' . ($user_info['time_offset'] + $modSettings['time_offset']), 'Subs-Calendar.php', 'cache_getRecentEvents', array($eventOptions));

	if ($output_method != 'echo')
		return $return['calendar_birthdays'];

	foreach ($return['calendar_birthdays'] as $member)
		echo '
			<a href="', $scripturl, '?action=profile;u=', $member['id'], '">' . $member['name'] . (isset($member['age']) ? ' (' . $member['age'] . ')' : '') . '</a>' . (!$member['is_last'] ? ', ' : '');
}

// Show today's holidays.
function ssi_todaysHolidays($output_method = 'echo')
{
	global $modSettings;

	$eventOptions = array(
		'include_holidays' => true,
		'num_days_shown' => empty($modSettings['cal_days_for_index']) || $modSettings['cal_days_for_index'] < 1 ? 1 : $modSettings['cal_days_for_index'],
	);
	$return = cache_quick_get('calendar_index_offset_' . ($user_info['time_offset'] + $modSettings['time_offset']), 'Subs-Calendar.php', 'cache_getRecentEvents', array($eventOptions));


	if ($output_method != 'echo')
		return $return['calendar_holidays'];

	echo '
		', implode(', ', $return['calendar_holidays']);
}

// Show today's events.
function ssi_todaysEvents($output_method = 'echo')
{
	global $modSettings;

	$eventOptions = array(
		'include_events' => true,
		'num_days_shown' => empty($modSettings['cal_days_for_index']) || $modSettings['cal_days_for_index'] < 1 ? 1 : $modSettings['cal_days_for_index'],
	);
	$return = cache_quick_get('calendar_index_offset_' . ($user_info['time_offset'] + $modSettings['time_offset']), 'Subs-Calendar.php', 'cache_getRecentEvents', array($eventOptions));

	if ($output_method != 'echo')
		return $return['calendar_events'];

	foreach ($return['calendar_events'] as $event)
	{
		if ($event['can_edit'])
			echo '
	<a href="' . $event['modify_href'] . '" style="color: #FF0000;">*</a> ';
		echo '
	' . $event['link'] . (!$event['is_last'] ? ', ' : '');
	}
}

// Show all calendar entires for today. (birthdays, holodays, and events.)
function ssi_todaysCalendar($output_method = 'echo')
{
	global $modSettings, $txt, $scripturl;

	$eventOptions = array(
		'include_birthdays' => true,
		'include_holidays' => true,
		'include_events' => true,
		'num_days_shown' => empty($modSettings['cal_days_for_index']) || $modSettings['cal_days_for_index'] < 1 ? 1 : $modSettings['cal_days_for_index'],
	);
	$return = cache_quick_get('calendar_index_offset_' . ($user_info['time_offset'] + $modSettings['time_offset']), 'Subs-Calendar.php', 'cache_getRecentEvents', array($eventOptions));

	if ($output_method != 'echo')
		return $return;

	if (!empty($return['calendar_holidays']))
		echo '
			<span class="holiday">' . $txt['calendar_prompt'] . ' ' . implode(', ', $return['calendar_holidays']) . '<br /></span>';
	if (!empty($return['calendar_birthdays']))
	{
		echo '
			<span class="birthday">' . $txt['birthdays_upcoming'] . '</span> ';
		foreach ($return['calendar_birthdays'] as $member)
			echo '
			<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['name'], isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>', !$member['is_last'] ? ', ' : '';
		echo '
			<br />';
	}
	if (!empty($return['calendar_events']))
	{
		echo '
			<span class="event">' . $txt['events_upcoming'] . '</span> ';
		foreach ($return['calendar_events'] as $event)
		{
			if ($event['can_edit'])
				echo '
			<a href="' . $event['modify_href'] . '" style="color: #FF0000;">*</a> ';
			echo '
			' . $event['link'] . (!$event['is_last'] ? ', ' : '');
		}
	}
}

// Show the latest news, with a template... by board.
function ssi_boardNews($board = null, $limit = null, $start = null, $length = null, $output_method = 'echo')
{
	global $scripturl, $db_prefix, $txt, $settings, $modSettings, $context;
	global $smfFunc;

	loadLanguage('Stats');

	// Must be integers....
	if ($limit === null)
		$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
	else
		$limit = (int) $limit;

	if ($start === null)
		$start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
	else
		$start = (int) $start;

	if ($board !== null)
		$board = (int) $board;
	elseif (isset($_GET['board']))
		$board = (int) $_GET['board'];

	if ($length === null)
		$length = isset($_GET['length']) ? (int) $_GET['length'] : 0;
	else
		$length = (int) $length;

	$limit = max(0, $limit);
	$start = max(0, $start);

	// Make sure guests can see this board.
	$request = $smfFunc['db_query']('', "
		SELECT id_board
		FROM {$db_prefix}boards
		WHERE " . ($board === null ? '' : "id_board = $board
			AND ") . "FIND_IN_SET(-1, member_groups)
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
	{
		if ($output_method == 'echo')
			die($txt['smf_news_error2']);
		else
			return array();
	}
	list ($board) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Load the message icons - the usual suspects.
	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';

	// Find the post ids.
	$request = $smfFunc['db_query']('', "
		SELECT id_first_msg
		FROM {$db_prefix}topics
		WHERE id_board = $board
			AND approved = 1
		ORDER BY id_first_msg DESC
		LIMIT $start, $limit", __FILE__, __LINE__);
	$posts = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$posts[] = $row['id_first_msg'];
	$smfFunc['db_free_result']($request);

	if (empty($posts))
		return array();

	// Find the posts.
	$request = $smfFunc['db_query']('', "
		SELECT
			m.icon, m.subject, m.body, IFNULL(mem.real_name, m.poster_name) AS poster_name, m.poster_time,
			t.num_replies, t.id_topic, m.id_member, m.smileys_enabled, m.id_msg, t.locked
		FROM {$db_prefix}topics AS t
			INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
		WHERE t.id_first_msg IN (" . implode(', ', $posts) . ")
		ORDER BY t.id_first_msg DESC
		LIMIT " . count($posts), __FILE__, __LINE__);
	$return = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// If we want to limit the length of the post.
		if (!empty($length) && $smfFunc['strlen']($row['body']) > $length)
		{
			$row['body'] = $smfFunc['substr']($row['body'], 0, $length);

			// The first space or line break. (<br />, etc.)
			$cutoff = max(strrpos($row['body'], ' '), strrpos($row['body'], '<'));

			if ($cutoff !== false)
				$row['body'] = $smfFunc['substr']($row['body'], 0, $cutoff);
			$row['body'] .= '...';
		}

		$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

		// Check that this message icon is there...
		if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
			$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.gif') ? 'images_url' : 'default_images_url';

		censorText($row['subject']);
		censorText($row['body']);

		$return[] = array(
			'id' => $row['id_topic'],
			'message_id' => $row['id_msg'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.gif" align="middle" alt="' . $row['icon'] . '" border="0" />',
			'subject' => $row['subject'],
			'time' => timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time']),
			'body' => $row['body'],
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['num_replies'] . ' ' . ($row['num_replies'] == 1 ? $txt['smf_news_1'] : $txt['smf_news_2']) . '</a>',
			'replies' => $row['num_replies'],
			'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'],
			'comment_link' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'] . '">' . $txt['smf_news_3'] . '</a>',
			'new_comment' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . '">' . $txt['smf_news_3'] . '</a>',
			'poster' => array(
				'id' => $row['id_member'],
				'name' => $row['poster_name'],
				'href' => !empty($row['id_member']) ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
				'link' => !empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>' : $row['poster_name']
			),
			'locked' => !empty($row['locked']),
			'is_last' => false
		);
	}
	$smfFunc['db_free_result']($request);

	if (empty($return))
		return $return;

	$return[count($return) - 1]['is_last'] = true;

	if ($output_method != 'echo')
		return $return;

	foreach ($return as $news)
	{
		echo '
			<div>
				<a href="', $news['href'], '">', $news['icon'], '</a> <b>', $news['subject'], '</b>
				<div class="smaller">', $news['time'], ' ', $txt['by'], ' ', $news['poster']['link'], '</div>

				<div class="post" style="padding: 2ex 0;">', $news['body'], '</div>

				', $news['link'], $news['locked'] ? '' : ' | ' . $news['comment_link'], '
			</div>';

		if (!$news['is_last'])
			echo '
			<hr style="margin: 2ex 0;" width="100%" />';
	}
}

// Show the most recent events.
function ssi_recentEvents($max_events = 7, $output_method = 'echo')
{
	global $db_prefix, $user_info, $scripturl, $modSettings, $txt, $sc, $smfFunc;

	// Find all events which are happening in the near future that the member can see.
	$request = $smfFunc['db_query']('', "
		SELECT
			cal.id_event, cal.start_date, cal.end_date, cal.title, cal.id_member, cal.id_topic,
			cal.id_board, t.id_first_msg, t.approved
		FROM {$db_prefix}calendar AS cal
			LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = cal.id_board)
			LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = cal.id_topic)
		WHERE cal.start_date <= '" . strftime('%Y-%m-%d', forum_time(false)) . "'
			AND cal.end_date >= '" . strftime('%Y-%m-%d', forum_time(false)) . "'
			AND (cal.id_board = 0 OR $user_info[query_wanna_see_board])
		ORDER BY cal.start_date DESC
		LIMIT $max_events", __FILE__, __LINE__);
	$return = array();
	$duplicates = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Check if we've already come by an event linked to this same topic with the same title... and don't display it if we have.
		if (!empty($duplicates[$row['title'] . $row['id_topic']]))
			continue;

		// Censor the title.
		censorText($row['title']);

		if ($row['start_date'] < strftime('%Y-%m-%d', forum_time(false)))
			$date = strftime('%Y-%m-%d', forum_time(false));
		else
			$date = $row['start_date'];

		// If the topic it is attached to is not approved then don't link it.
		if (!empty($row['id_first_msg']) && !$row['approved'])
			$row['id_board'] = $row['id_topic'] = $row['id_first_msg'] = 0;

		$return[$date][] = array(
			'id' => $row['id_event'],
			'title' => $row['title'],
			'can_edit' => allowedTo('calendar_edit_any') || ($row['id_member'] == $user_info['id'] && allowedTo('calendar_edit_own')),
			'modify_href' => $scripturl . '?action=' . ($row['id_board'] == 0 ? 'calendar;sa=post;' : 'post;msg=' . $row['id_first_msg'] . ';topic=' . $row['id_topic'] . '.0;calendar;') . 'eventid=' . $row['id_event'] . ';sesc=' . $sc,
			'href' => $row['id_board'] == 0 ? '' : $scripturl . '?topic=' . $row['id_topic'] . '.0',
			'link' => $row['id_board'] == 0 ? $row['title'] : '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['title'] . '</a>',
			'start_date' => $row['start_date'],
			'end_date' => $row['end_date'],
			'is_last' => false
		);

		// Let's not show this one again, huh?
		$duplicates[$row['title'] . $row['id_topic']] = true;
	}
	$smfFunc['db_free_result']($request);

	foreach ($return as $mday => $array)
		$return[$mday][count($array) - 1]['is_last'] = true;

	if ($output_method != 'echo' || empty($return))
		return $return;

	// Well the output method is echo.
	echo '
			<span class="event">' . $txt['events'] . '</span> ';
	foreach ($return as $mday => $array)
		foreach ($array as $event)
		{
			if ($event['can_edit'])
				echo '
				<a href="' . $event['modify_href'] . '" style="color: #FF0000;">*</a> ';

			echo '
				' . $event['link'] . (!$event['is_last'] ? ', ' : '');
		}
}

// Check the passed id_member/password.  If $is_username is true, treats $id as a username.
function ssi_checkPassword($id = null, $password = null, $is_username = false)
{
	global $db_prefix, $sourcedir, $smfFunc;

	// If $id is null, this was most likely called from a query string and should do nothing.
	if ($id === null)
		return;

	$request = $smfFunc['db_query']('', "
		SELECT passwd, member_name, is_activated
		FROM {$db_prefix}members
		WHERE " . ($is_username ? 'member_name' : 'id_member') . " = '$id'
		LIMIT 1", __FILE__, __LINE__);
	list ($pass, $user, $active) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	return sha1(strtolower($user) . $password) == $pass && $active == 1;
}

?>