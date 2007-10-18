<?php
/**********************************************************************************
* ManageSearchEngines.php                                                         *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 1                                      *
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

/*	This file contains all the screens that relate to search engines.

	// !!!
*/

// Entry point for this section.
function SearchEngines()
{
	global $context, $txt, $scripturl;

	isAllowedTo('admin_forum');

	loadLanguage('Search');
	loadTemplate('ManageSearch');

	$subActions = array(
		'editspiders' => 'EditSpider',
		'logs' => 'SpiderLogs',
		'settings' => 'ManageSearchEngineSettings',
		'spiders' => 'ViewSpiders',
		'stats' => 'SpiderStats',
	);

	// Ensure we have a valid subaction.
	$context['sub_action'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'stats';

	$context['page_title'] = $txt['search_engines'];

	// Some more tab data.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['search_engines'],
		'description' => $txt['search_engines_description'],
	);

	// Call the function!
	$subActions[$context['sub_action']]();
}

// This is really just the settings page.
function ManageSearchEngineSettings($return_config = false)
{
	global $context, $txt, $db_prefix, $modSettings, $scripturl, $sourcedir, $smfFunc;

	$config_vars = array(
		// How much detail?
		array('select', 'spider_mode', array($txt['spider_mode_off'], $txt['spider_mode_standard'], $txt['spider_mode_high'], $txt['spider_mode_vhigh'])),
		'spider_group' => array('select', 'spider_group', array($txt['spider_group_none'], $txt['membergroups_members'])),
	);

	if ($return_config)
		return $config_vars;

	// We need to load the groups for the spider group thingy.
	$request = $smfFunc['db_query']('', "
		SELECT id_group, group_name
		FROM {$db_prefix}membergroups
		WHERE id_group != 1
			AND id_group != 3", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$config_vars['spider_group'][2][$row['id_group']] = $row['group_name'];
	$smfFunc['db_free_result']($request);

	// Make sure it's valid - note that regular members are given id_group = 1 which is reversed in Load.php - no admins here!
	if (isset($_POST['spider_group']) && !isset($config_vars['spider_group'][2][$_POST['spider_group']]))
		$_POST['spider_group'] = 0;

	// We'll want this for our easy save.
	require_once($sourcedir .'/ManageServer.php');

	// Setup the template.
	$context['page_title'] = $txt['settings'];
	$context['sub_template'] = 'show_settings';

	// Are we saving them - are we??
	if (isset($_GET['save']))
	{
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=sengines;sa=settings');
	}

	// Final settings...
	$context['post_url'] = $scripturl . '?action=admin;area=sengines;save;sa=settings';
	$context['settings_title'] = $txt['settings'];

	// Prepare the settings...
	prepareDBSettingContext($config_vars);
}

// View a list of all the spiders we know about.
function ViewSpiders()
{
	global $context, $txt, $sourcedir, $scripturl, $smfFunc, $db_prefix;

	if (!isset($_SESSION['spider_stat']) || $_SESSION['spider_stat'] < time() - 60)
	{
		consolidateSpiderStats();
		$_SESSION['spider_stat'] = time();
	}

	// Are we adding a new one?
	if (!empty($_POST['addSpider']))
		return EditSpider();
	// User pressed the 'remove selection button'.
	elseif (!empty($_POST['removeSpiders']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		// Make sure every entry is a proper integer.
		foreach ($_POST['remove'] as $index => $spider_id)
			$_POST['remove'][(int) $index] = (int) $spider_id;

		// Delete them all!
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}spiders
			WHERE id_spider IN (" . implode(', ', $_POST['remove']) . ')', __FILE__, __LINE__);
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_spider_hits
			WHERE id_spider IN (" . implode(', ', $_POST['remove']) . ')', __FILE__, __LINE__);
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_spider_stats
			WHERE id_spider IN (" . implode(', ', $_POST['remove']) . ')', __FILE__, __LINE__);
	}

	// Get the last seens.
	$request = $smfFunc['db_query']('', "
		SELECT id_spider, MAX(last_seen) AS last_seen_time
		FROM {$db_prefix}log_spider_stats
		GROUP BY id_spider", __FILE__, __LINE__);

	$context['spider_last_seen'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['spider_last_seen'][$row['id_spider']] = $row['last_seen_time'];
	$smfFunc['db_free_result']($request);

	$listOptions = array(
		'id' => 'spider_list',
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=sengines;sa=spiders',
		'default_sort_col' => 'name',
		'get_items' => array(
			'function' => 'list_getSpiders',
		),
		'get_count' => array(
			'function' => 'list_getNumSpiders',
		),
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['spider_name'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $scripturl;

						return sprintf(\'<a href="%1$s?action=admin;area=sengines;sa=editspiders;sid=%2$d">%3$s</a>\', $scripturl, $rowData[\'id_spider\'], htmlspecialchars($rowData[\'spider_name\']));
					'),
					'class' => 'windowbg',
				),
				'sort' => array(
					'default' => 'spider_name',
					'reverse' => 'spider_name DESC',
				),
			),
			'last_seen' => array(
				'header' => array(
					'value' => $txt['spider_last_seen'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $context, $txt;

						return isset($context[\'spider_last_seen\'][$rowData[\'id_spider\']]) ? timeformat($context[\'spider_last_seen\'][$rowData[\'id_spider\']]) : $txt[\'spider_last_never\'];
					'),
					'class' => 'windowbg',
				),
			),
			'user_agent' => array(
				'header' => array(
					'value' => $txt['spider_agent'],
				),
				'data' => array(
					'db_htmlsafe' => 'user_agent',
					'class' => 'windowbg',
				),
				'sort' => array(
					'default' => 'user_agent',
					'reverse' => 'user_agent DESC',
				),
			),
			'ip_info' => array(
				'header' => array(
					'value' => $txt['spider_ip_info'],
				),
				'data' => array(
					'db_htmlsafe' => 'ip_info',
					'class' => 'smalltext',
				),
			),
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="check" />',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="remove[]" value="%1$d" class="check" />',
						'params' => array(
							'id_spider' => false,
						),
					),
					'style' => 'text-align: center',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=sengines;sa=spiders',
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
					<input type="submit" name="addSpider" value="' . $txt['spiders_add'] . '" />
					<input type="submit" name="removeSpiders" value="' . $txt['spiders_remove_selected'] . '" onclick="return confirm(\'' . $txt['spider_remove_selected_confirm'] . '\');" />
				',
				'class' => 'titlebg',
				'style' => 'text-align: right;',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'spider_list';
}

function list_getSpiders($start, $items_per_page, $sort)
{
	global $db_prefix, $smfFunc;

	$request = $smfFunc['db_query']('', "
		SELECT id_spider, spider_name, user_agent, ip_info
		FROM {$db_prefix}spiders
		ORDER BY $sort
		LIMIT $start, $items_per_page", __FILE__, __LINE__);
	$spiders = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$spiders[$row['id_spider']] = $row;
	$smfFunc['db_free_result']($request);

	return $spiders;
}

function list_getNumSpiders()
{
	global $db_prefix, $smfFunc;

	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*) AS num_spiders
		FROM {$db_prefix}spiders", __FILE__, __LINE__);
	list ($numSpiders) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	return $numSpiders;
}

// Here we can add, and edit, spider info!
function EditSpider()
{
	global $context, $db_prefix, $smfFunc, $txt;

	// Some standard stuff.
	$context['id_spider'] = !empty($_GET['sid']) ? (int) $_GET['sid'] : 0;
	$context['page_title'] = $context['id_spider'] ? $txt['spiders_edit'] : $txt['spiders_add'];
	$context['sub_template'] = 'spider_edit';

	// Are we saving?
	if (!empty($_POST['save']))
	{
		$ips = array();
		// Check the IP range is valid.
		$ip_sets = explode(',', $_POST['spider_ip']);
		foreach ($ip_sets as $set)
		{
			$test = ip2range(trim($set));
			if (!empty($test))
				$ips[] = $set;
		}
		$ips = implode(',', $ips);

		// Goes in as it is...
		if ($context['id_spider'])
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}spiders
				SET spider_name = '$_POST[spider_name]', user_agent = '$_POST[spider_agent]',
					ip_info = '$ips'
				WHERE id_spider = $context[id_spider]", __FILE__, __LINE__);
		else
			$smfFunc['db_query']('', "
				INSERT INTO {$db_prefix}spiders
					(spider_name, user_agent, ip_info)
				VALUES
					('$_POST[spider_name]', '$_POST[spider_agent]', '$ips')", __FILE__, __LINE__);

		redirectexit('action=admin;area=sengines;sa=spiders');
	}

	// The default is new.
	$context['spider'] = array(
		'id' => 0,
		'name' => '',
		'agent' => '',
		'ip_info' => '',
	);

	// An edit?
	if ($context['id_spider'])
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_spider, spider_name, user_agent, ip_info
			FROM {$db_prefix}spiders
			WHERE id_spider = $context[id_spider]", __FILE__, __LINE__);
		if ($row = $smfFunc['db_fetch_assoc']($request))
			$context['spider'] = array(
				'id' => $row['id_spider'],
				'name' => $row['spider_name'],
				'agent' => $row['user_agent'],
				'ip_info' => $row['ip_info'],
			);
		$smfFunc['db_free_result']($request);
	}

}

//!!! Should this not be... you know... in a different file?
// Do we think the current user is a spider?
function SpiderCheck()
{
	global $modSettings, $smfFunc, $db_prefix;

	if (isset($_SESSION['id_robot']))
	{
		unset($_SESSION['id_robot']);
		// This is not a new visiting robot.
		$not_unique = true;
	}
	$_SESSION['robot_check'] = time();

	// We cache the spider data for five minutes if we can.
	if (!empty($modSettings['cache_enable']))
	{
		$spider_data = cache_get_data('spider_search', 300);
	}

	if (!isset($spider_data) || $spider_data === NULL)
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_spider, user_agent, ip_info
			FROM {$db_prefix}spiders", __FILE__, __LINE__);
		$spider_data = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			$spider_data[] = $row;

			if (!empty($modSettings['cache_enable']))
				cache_put_data('spider_search', $spider_data, 300);
		}
		$smfFunc['db_free_result']($request);
	}

	if (empty($spider_data))
		return false;

	// Only do these bits once.
	$ci_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $_SERVER['REMOTE_ADDR'], $ip_parts);

	foreach ($spider_data as $spider)
	{
		// User agent is easy.
		if (!empty($spider['user_agent']) && strpos($ci_user_agent, strtolower($spider['user_agent'])) !== false)
			$_SESSION['id_robot'] = $spider['id_spider'];
		// IP stuff is harder.
		elseif (!empty($ip_parts))
		{
			$ips = explode(',', $spider['ip_info']);
			foreach ($ips as $ip)
			{
				$ip = ip2range($ip);
				if (!empty($ip))
				{
					foreach ($ip as $k => $v)
					{
						if ($v['low'] > $ip_parts[$k + 1] || $v['high'] < $ip_parts[$k + 1])
							break;
						elseif ($k == 3)
							$_SESSION['id_robot'] = $spider['id_spider'];
					}
				}
			}
		}

		if (isset($_SESSION['id_robot']))
			break;
	}

	// If this is low server tracking then log the spider here as oppossed to the main logging function.
	if ($modSettings['spider_mode'] == 1 && !empty($_SESSION['id_robot']))
		logSpider(isset($not_unique));

	return !empty($_SESSION['id_robot']) ? $_SESSION['id_robot'] : 0;
}

// Log the spider presence online.
//!!! Different file?
function logSpider($not_unique_spider = false)
{
	global $smfFunc, $db_prefix, $modSettings, $context;

	if (empty($modSettings['spider_mode']) || empty($_SESSION['id_robot']))
		return;

	// Attempt to update today's entry.
	if ($modSettings['spider_mode'] == 1)
	{
		$date = strftime('%Y-%m-%d', forum_time(false));
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}log_spider_stats
			SET last_seen = " . time() . ($not_unique_spider ? '' : ', unique_visits = unique_visits + 1') . "
			WHERE id_spider = $_SESSION[id_robot]
				AND stat_date = '$date'", __FILE__, __LINE__);
		if ($smfFunc['db_affected_rows']() == 0)
		{
			$smfFunc['db_insert']('insert',
				"{$db_prefix}log_spider_stats",
				array('id_spider', 'last_seen', 'stat_date', 'unique_visits'),
				array($_SESSION['id_robot'], time(), "'$date'", 1),
				array('stat_date'), __FILE__, __LINE__
			);
		}
	}
	// If we're tracking better stats than track, better stats - we sort out the today thing later.
	else
	{
		if ($modSettings['spider_mode'] > 2)
		{
			$url = $_GET + array('USER_AGENT' => $_SERVER['HTTP_USER_AGENT']);
			unset($url['sesc']);
			$url = $smfFunc['db_escape_string'](serialize($url));
		}
		else
			$url = '';

		$smfFunc['db_insert']('insert',
			"{$db_prefix}log_spider_hits",
			array('id_spider', 'session', 'log_time', 'url'),
			array($_SESSION['id_robot'], "'$context[session_id]'", time(), "'$url'"),
			array(), __FILE__, __LINE__
		);
	}
}

// This function takes any unprocessed hits and turns them into stats.
function consolidateSpiderStats()
{
	global $smfFunc, $db_prefix;

	$request = $smfFunc['db_query']('', "
		SELECT id_spider, session, log_time
		FROM {$db_prefix}log_spider_hits
		WHERE processed = 0", __FILE__, __LINE__);
	$spider_hits = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$spider_hits[] = $row;
	$smfFunc['db_free_result']($request);

	if (empty($spider_hits))
		return;

	// Put them into date categories.
	$stat_updates = array();
	$session_checks = array();
	foreach ($spider_hits as $hit)
	{
		$date = strftime('%Y-%m-%d', $hit['log_time']);
		if (!isset($stat_updates[$date]))
			$stat_updates[$date] = array(
				'spiders' => array(),
				'sessions' => array(),
			);
		if (!isset($stat_updates[$date]['spiders'][$hit['id_spider']]))
			$stat_updates[$date]['spiders'][$hit['id_spider']] = array(
				'hits' => 0,
				'unique' => 0,
				'seen' => 0,
			);

		$stat_updates[$date]['spiders'][$hit['id_spider']]['hits']++;
		if ($stat_updates[$date]['spiders'][$hit['id_spider']]['seen'] < $hit['log_time'])
			$stat_updates[$date]['spiders'][$hit['id_spider']]['seen'] = $hit['log_time'];

		// Not seen this before?
		if (!in_array($hit['session'], $stat_updates[$date]['sessions']))
		{
			$stat_updates[$date]['sessions'][] = $hit['session'];
			$stat_updates[$date]['spiders'][$hit['id_spider']]['unique']++;

			// We'll need to check we haven't duplicated this.
			$session_checks[$hit['session']] = array(
				'date' => $date,
				'spider' => $hit['id_spider'],
				'time' => $hit['log_time'],
			);
		}
	}

	// Now check that we haven't already caught these sessions previously - for unique hits.
	$where_query = array();
	foreach ($session_checks as $session => $data)
		$where_query[] = '(log_time > ' . ($data['time'] - 86400) . ' AND ' . ($data['time'] + 86400) . ' AND session = \'' . $session . '\')';

	if (!empty($where_query))
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_spider, session, log_time
			FROM {$db_prefix}log_spider_hits
			WHERE processed = 1
				AND (" . implode(' OR ', $where_query) . ")", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			// Just in case...
			if (empty($stat_updates[$session_checks[$row['session']]['date']]['spiders'][$session_checks[$row['session']]['spider']]['unique']))
				continue;

			// Deduct the unique hits by one.
			$stat_updates[$session_checks[$row['session']]['date']]['spiders'][$session_checks[$row['session']]['spider']]['unique']--;
		}
		$smfFunc['db_free_result']($request);
	}

	// Now we should, finally, have accurate stat updates - action them.
	$stat_inserts = array();
	foreach ($stat_updates as $date => $stat)
	{
		// Try to update first.
		foreach ($stat['spiders'] as $id => $spider)
		{
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}log_spider_stats
				SET page_hits = page_hits + $spider[hits], unique_visits = unique_visits + $spider[unique],
					last_seen = CASE WHEN last_seen > $spider[seen] THEN last_seen ELSE $spider[seen] END
				WHERE id_spider = $id
					AND stat_date = '$date'", __FILE__, __LINE__);
			if ($smfFunc['db_affected_rows']() == 0)
				$stat_inserts[] = array("'$date'", $id, $spider['hits'], $spider['unique'], $spider['seen']);
		}
	}

	// New stats?
	if (!empty($stat_inserts))
		$smfFunc['db_insert']('insert',
			"{$db_prefix}log_spider_stats",
			array('stat_date', 'id_spider', 'page_hits', 'unique_visits', 'last_seen'),
			$stat_inserts,
			array('stat_date', 'id_spider'), __FILE__, __LINE__
		);

	// All processed.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}log_spider_hits
		SET processed = 1
		WHERE processed = 0", __FILE__, __LINE__);
}

//!!! Move this somewhere as duplicated in ManageBans.php
function ip2range($fullip)
{
	// Pretend that 'unknown' is 255.255.255.255. (since that can't be an IP anyway.)
	if ($fullip == 'unknown')
		$fullip = '255.255.255.255';

	$ip_parts = explode('.', $fullip);
	$ip_array = array();

	if (count($ip_parts) != 4)
		return array();

	for ($i = 0; $i < 4; $i++)
	{
		if ($ip_parts[$i] == '*')
			$ip_array[$i] = array('low' => '0', 'high' => '255');
		elseif (preg_match('/^(\d{1,3})\-(\d{1,3})$/', $ip_parts[$i], $range) == 1)
			$ip_array[$i] = array('low' => $range[1], 'high' => $range[2]);
		elseif (is_numeric($ip_parts[$i]))
			$ip_array[$i] = array('low' => $ip_parts[$i], 'high' => $ip_parts[$i]);
	}

	return $ip_array;
}

// See what spiders have been up to.
function SpiderLogs()
{
	global $context, $txt, $sourcedir, $scripturl, $smfFunc, $db_prefix, $modSettings;

	// Did they want to delete some entries?
	if (!empty($_POST['delete_entries']) && !empty($_POST['older']))
	{
		checkSession();

		$deleteTime = time() - (((int) $_POST['older']) * 24 * 60 * 60);

		// Delete the entires.
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_spider_hits
			WHERE log_time < $deleteTime", __FILE__, __LINE__);
	}

	$listOptions = array(
		'id' => 'spider_logs',
		'items_per_page' => 20,
		'no_items_label' => $txt['spider_logs_empty'],
		'base_href' => $scripturl . '?action=admin;area=sengines;sa=logs',
		'default_sort_col' => 'log_time',
		'get_items' => array(
			'function' => 'list_getSpiderLogs',
		),
		'get_count' => array(
			'function' => 'list_getNumSpiderLogs',
		),
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['spider'],
				),
				'data' => array(
					'db' => 'spider_name',
					'class' => 'windowbg',
				),
				'sort' => array(
					'default' => 's.spider_name',
					'reverse' => 's.spider_name DESC',
				),
			),
			'log_time' => array(
				'header' => array(
					'value' => $txt['spider_time'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						return timeformat($rowData[\'log_time\']);
					'),
					'class' => 'windowbg',
				),
				'sort' => array(
					'default' => 'sl.log_time',
					'reverse' => 'sl.log_time DESC',
				),
			),
			'viewing' => array(
				'header' => array(
					'value' => $txt['spider_viewing'],
				),
				'data' => array(
					'db' => 'url',
					'class' => 'windowbg',
				),
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'above_column_headers',
				'value' => '
					<span class="smalltext">' . $txt['spider_logs_info'] . '</span>
				',
				'class' => 'windowbg2',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	// Now determine the actions of the URLs.
	if (!empty($context['spider_logs']['rows']))
	{
		$urls = array();
		// Grab the current /url.
		foreach ($context['spider_logs']['rows'] as $k => $row)
		{
			// Feature disabled?
			if (empty($row['viewing']['value']) && isset($modSettings['spider_mode']) && $modSettings['spider_mode'] < 3)
				$context['spider_logs']['rows'][$k]['viewing']['value'] = '<em>' . $txt['spider_disabled'] . '</em>';
			else
				$urls[$k] = array($row['viewing']['value'], -1);
		}

		// Now stick in the new URLs.
		require_once($sourcedir . '/Who.php');
		$urls = determineActions($urls);
		foreach ($urls as $k => $new_url)
		{
			$context['spider_logs']['rows'][$k]['viewing']['value'] = $new_url;
		}
	}

	$context['sub_template'] = 'show_spider_logs';
	$context['default_list'] = 'spider_logs';
}

function list_getSpiderLogs($start, $items_per_page, $sort)
{
	global $db_prefix, $smfFunc;

	$request = $smfFunc['db_query']('', "
		SELECT sl.id_spider, sl.url, sl.log_time, s.spider_name
		FROM {$db_prefix}log_spider_hits AS sl
			INNER JOIN {$db_prefix}spiders AS s ON (s.id_spider = sl.id_spider)
		ORDER BY $sort
		LIMIT $start, $items_per_page", __FILE__, __LINE__);
	$spider_logs = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$spider_logs[] = $row;
	$smfFunc['db_free_result']($request);

	return $spider_logs;
}

function list_getNumSpiderLogs()
{
	global $db_prefix, $smfFunc;

	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*) AS num_logs
		FROM {$db_prefix}log_spider_hits", __FILE__, __LINE__);
	list ($numLogs) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	return $numLogs;
}

// Show the spider statistics.
function SpiderStats()
{
	global $context, $txt, $sourcedir, $scripturl, $smfFunc, $db_prefix;

	// Force an update of the stats every 60 seconds.
	if (!isset($_SESSION['spider_stat']) || $_SESSION['spider_stat'] < time() - 60)
	{
		consolidateSpiderStats();
		$_SESSION['spider_stat'] = time();
	}

	// Get the earliest and latest dates.
	$request = $smfFunc['db_query']('', "
		SELECT MIN(stat_date) AS first_date, MAX(stat_date) AS last_date
		FROM {$db_prefix}log_spider_stats", __FILE__, __LINE__);

	list ($min_date, $max_date) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$min_year = (int) substr($min_date, 0, 4);
	$max_year = (int) substr($max_date, 0, 4);
	$min_month = (int) substr($min_date, 5, 2);
	$max_month = (int) substr($max_date, 5, 2);

	// Prepare the dates for the drop down.
	$date_choices = array();
	for ($y = $min_year; $y <= $max_year; $y++)
		for ($m = 1; $m <= 12; $m++)
		{
			// This doesn't count?
			if ($y == $min_year && $m < $min_month)
				continue;
			if ($y == $max_year && $m > $max_month)
				break;

			$date_choices[$y . $m] = $txt['months_short'][$m] . ' ' . $y;
		}

	// What are we currently viewing?
	$current_date = isset($_REQUEST['new_date']) && isset($date_choices[$_REQUEST['new_date']]) ? $_REQUEST['new_date'] : $max_date;

	// Prepare the HTML.
	$date_select = '
		' . $txt['spider_stats_select_month'] . ':
		<select name="new_date" onchange="document.spider_stat_list.submit();">';
	foreach ($date_choices as $id => $text)
		$date_select .= '
			<option value="' . $id . '"' . ($current_date == $id ? ' selected="selected"' : '') . '>' . $text . '</option>';
	$date_select .= '
		</select>
		<noscript>
			<input type="submit" name="go" value="' . $txt['go'] . '" />
		</noscript>';

	// If we manually jumped to a date work out the offset.
	if (isset($_REQUEST['new_date']))
	{
		$date_query = sprintf('%04d-%02d-01', substr($current_date, 0, 4), substr($current_date, 4));

		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*) AS offset
			FROM {$db_prefix}log_spider_stats
			WHERE stat_date < '$date_query'", __FILE__, __LINE__);
		list ($_REQUEST['start']) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
	}

	$listOptions = array(
		'id' => 'spider_stat_list',
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=sengines;sa=stats',
		'default_sort_col' => 'stat_date',
		'get_items' => array(
			'function' => 'list_getSpiderStats',
		),
		'get_count' => array(
			'function' => 'list_getNumSpiderStats',
		),
		'columns' => array(
			'stat_date' => array(
				'header' => array(
					'value' => $txt['date'],
				),
				'data' => array(
					'db' => 'stat_date',
					'class' => 'windowbg',
				),
				'sort' => array(
					'default' => 'stat_date',
					'reverse' => 'stat_date DESC',
				),
			),
			'name' => array(
				'header' => array(
					'value' => $txt['spider_name'],
				),
				'data' => array(
					'db' => 'spider_name',
					'class' => 'windowbg',
				),
				'sort' => array(
					'default' => 's.spider_name',
					'reverse' => 's.spider_name DESC',
				),
			),
			'unique_visits' => array(
				'header' => array(
					'value' => $txt['spider_stats_unique_visits'],
				),
				'data' => array(
					'db_htmlsafe' => 'unique_visits',
					'class' => 'windowbg',
				),
				'sort' => array(
					'default' => 'user_agent',
					'reverse' => 'user_agent DESC',
				),
			),
			'page_hits' => array(
				'header' => array(
					'value' => $txt['spider_stats_page_hits'],
				),
				'data' => array(
					'db' => 'page_hits',
					'class' => 'windowbg',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=sengines;sa=stats',
			'name' => 'spider_stat_list',
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => $date_select,
				'class' => 'titlebg',
				'style' => 'text-align: right;',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'spider_stat_list';
}

function list_getSpiderStats($start, $items_per_page, $sort)
{
	global $db_prefix, $smfFunc;

	$request = $smfFunc['db_query']('', "
		SELECT ss.id_spider, ss.stat_date, ss.unique_visits, ss.page_hits, s.spider_name
		FROM {$db_prefix}log_spider_stats AS ss
			INNER JOIN {$db_prefix}spiders AS s ON (s.id_spider = ss.id_spider)
		ORDER BY $sort
		LIMIT $start, $items_per_page", __FILE__, __LINE__);
	$spider_stats = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$spider_stats[] = $row;
	$smfFunc['db_free_result']($request);

	return $spider_stats;
}

function list_getNumSpiderStats()
{
	global $db_prefix, $smfFunc;

	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*) AS num_stats
		FROM {$db_prefix}log_spider_stats", __FILE__, __LINE__);
	list ($numStats) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	return $numStats;
}

?>