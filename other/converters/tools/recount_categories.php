<?php
ob_start();
require_once(dirname(__FILE__) . '/SSI.php');
initialize_inputs();
show_header();

$my_func = 'doStep' . (!empty($_REQUEST['step']) ? $_REQUEST['step'] : 0);

if (function_exists($my_func))
	$my_func();
else
	doStep0();

show_footer();

function initialize_inputs()
{
	global $this_url, $smcFunc, $tables;

	// In SMF 2.0 we need this.
	if (function_exists('db_extend'))
		db_extend('packages');

	// Turn off magic quotes runtime and enable error reporting.
	if (function_exists('set_magic_quotes_runtime'))
		@set_magic_quotes_runtime(0);
	error_reporting(E_ALL);

	// Add slashes, as long as they aren't already being added.
	if (!function_exists('get_magic_quotes_gpc') || @get_magic_quotes_gpc() == 0)
	{
		foreach ($_POST as $k => $v)
			$_POST[$k] = addslashes($v);
	}

	$_GET['a'] = (string) @$_GET['a'];
	$this_url = 'http://' . (empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST']) . $_SERVER['PHP_SELF'];

	// Tables that have the cat id in them.
	$tables = array('categories', 'boards', 'collapsed_categories');
}

// Welcome you.
function doStep0()
{
	global $this_url, $user_info;

	// No Powers, No good.
	if ($user_info['is_guest'] || !$user_info['is_admin'])
	{
		ssi_login();
		exit;
	}

echo '
<form method="post" action="', $this_url, '?step=1">
	<div class="panel">
		<h2>Welcome, ', $user_info['username'], '</h2>
		<p>Welcome to the recount Category ID script.</p>
		<div class="error_message">BE SURE TO RUN BACKUPS BEFORE PROCEEDING WITH THIS!!!</div>
		<p>This script will recount all your category IDs to use lower numbers. Why? Well some people during conversions may receive extremely high Category IDs that can cause issues. The purpose of this script to to help prevent that by recounting the ids.</p>
		<p>Are you ready? Click <input type="submit" name="submit" value="submit" class="button_submit" /> to start</p>
	</div>
</form>';

}

// Alter the columns in preperation.
function doStep1()
{
	global $this_url, $db_prefix, $tables;

	script_modify_column('categories', 'id_cat', array('auto' => false));

	foreach ($tables AS $table)
		script_modify_column($table, 'id_cat', array('type' => 'bigint'));

	echo '
	<div class="panel">
		<h3>The conversion may start now</h3>
		<p>If you have not yet converted the forum. You should do so now as the tables have been altered to hold bigger values for their Category IDs</p>
	</div>';

	show_pause(2);
}

// Update all of the ids to be higher than 255 (Prevents issues).
function doStep2()
{
	global $this_url, $db_prefix, $tables;

	// Get the CATS.
	$request = script_query("
		SELECT
			id_cat AS cat_id, name, cat_order AS c_order
		FROM {$db_prefix}categories
		ORDER BY cat_order ASC");

	$cats = array();
	while ($row = script_fetch($request))
		$cats[$row['cat_id'] + 255] = $row['c_order'];

	foreach ($tables as $table)
		script_query("
			UPDATE {$db_prefix}{$table}
			SET id_cat = id_cat + 255");

	$order = 0;
	// Set the new order for the categories.
	foreach ($cats as $index => $cat)
	{
		++$order;

		script_query("
			UPDATE {$db_prefix}categories
			SET cat_order = {$order}
			WHERE id_cat = {$cat}
			LIMIT 1");
	}

	show_pause(3);
}

// Update the columns now
function doStep3()
{
	global $this_url, $db_prefix, $tables;

	// Get the CATS.
	$request = script_query("
		SELECT
			id_cat AS cat_id, name, cat_order AS c_order
		FROM {$db_prefix}categories
		ORDER BY cat_order ASC");

	$cats = array();
	while ($row = script_fetch($request))
		$cats[$row['cat_id']] = $row['c_order'];

	// Now we actually update it.
	foreach ($cats AS $old_id => $new_id)
	{
		++$new_id;
		// Go through all the tables quickly.
		foreach ($tables AS $table)
		{
			script_query("
				UPDATE {$db_prefix}{$table}
				SET id_cat = {$new_id}
				WHERE id_cat = {$old_id}");
		}
	}

	show_pause(4);
}

// Reset some stuff and get out.
function doStep4()
{
	global $this_url, $db_prefix, $tables;

	// Now to get the auto_increment setup.
	$request = script_query("
		SELECT MAX(id_cat) AS cat_id
		FROM {$db_prefix}categories
		LIMIT 1");

	list($max_cat_id) = script_fetch($request, true);

	if (isset($smcFunc['db_change_column']))
	{
		// Once to drop it.
		$smcFunc['db_change_column']("{$db_prefix}categories", 'id_cat',
			array(
				'name' => 'id_cat',
				'auto' => true,
			), array('no_prefix' => true));
		// Again to add it.
		$smcFunc['db_change_column']("{$db_prefix}categories", 'id_cat',
			array(
				'name' => 'id_cat',
				'auto' => true,
			), array('no_prefix' => true));
	}
	else
	{
		// Its actually easier.
		script_query("
			ALTER TABLE {$db_prefix}categories AUTO_INCREMENT=" . ++$max_cat_id);
	}

	// Change our columns back.
	foreach ($tables AS $table)
		script_modify_column($table, 'id_cat', array('type' => 'tinyint'));

	// Some manual changes.
	script_modify_column('categories', 'id_cat', array('auto' => true));

	// Call it directly
	doStep5();
}

function doStep5()
{
	echo '
	<div class="panel">
		<h2>Process completed</h2>
		<p>That wasn\'t to hard was it?</p>';

}

function show_pause($next_step)
{
	global $this_url;

	echo '
<form method="post" action="', $this_url, '?step=', $next_step, '">
	<div class="panel">
		<h2>Process paused</h2>
		<p>The script has been halted here to prevent overloading the server.</p>
		<p>Are you ready? Click <input type="submit" name="submit" value="submit" class="button_submit" /> to continue</p>
	</div>
</form>';
}
function show_header()
{
	global $start_time, $txt;
	$start_time = time();

	$smfsite = 'http://simplemachines.org/smf';

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', !empty($txt['lang_rtl']) ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', isset($txt['lang_character_set']) ? $txt['lang_character_set'] : 'ISO-8859-1', '" />
		<title>SMF Recount Category script</title>
		<script type="text/javascript" src="Themes/default/scripts/script.js"></script>
		<link rel="stylesheet" type="text/css" href="', $smfsite, '/style.css" />
	</head>
	<body>
		<div id="header">
			<a href="http://www.simplemachines.org/" target="_blank"><img src="', $smfsite, '/smflogo.gif" style="float: ', empty($txt['lang_rtl']) ? 'right' : 'left', ';" alt="Simple Machines" border="0" /></a>
			<div title="Moogle Express!">Recount Category script</div>
		</div>
		<div id="content">
			<table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding-top: 1ex;">
			<tr>
				<td width="250" valign="top" style="padding-right: 10px;">
					<table border="0" cellpadding="8" cellspacing="0" class="tborder" width="240">
						<tr>
							<td class="titlebg">Recount Steps</td>
						</tr>
						<tr>
							<td class="windowbg2">
						<span class="', empty($_REQUEST['step']) ? 'stepcurrent' : 'stepwaiting', '">Welcome</span><br />
						<span class="', !empty($_REQUEST['step']) && $_REQUEST['step'] == 1 ? 'stepcurrent' : 'stepwaiting', '">Alter Tables</span><br />
						<span class="', !empty($_REQUEST['step']) && $_REQUEST['step'] == 2 ? 'stepcurrent' : 'stepwaiting', '">Update Columns</span><br />
						<span class="', !empty($_REQUEST['step']) && $_REQUEST['step'] == 3 ? 'stepcurrent' : 'stepwaiting', '">Correct Categorys</span><br />
						<span class="', !empty($_REQUEST['step']) && $_REQUEST['step'] == 4 ? 'stepcurrent' : 'stepwaiting', '">Clean up</span><br />
							</td>
						</tr>
					</table>
				</td>
				<td width="100%" valign="top">';
}

function show_footer()
{
	echo '
		</div>
	</body>
</html>';
}

function script_query($query)
{
	global $smcFunc, $func;

	if (isset($smcFunc['db_query']))
		return $smcFunc['db_query']('', $query, 'security_override');
	elseif (function_exists('db_query'))
	{
		$query = str_replace(
			array(
				'id_cat',
				'cat_order',
			),
			array(
				'ID_CAT',
				'catOrder',
			), $query);
		$return = db_query($query, __FILE__, __LINE__);

		// We need to find our backtrace.
		if ($return !== false)
			return $return;
		else
		{
			echo 'The recount process has received an error<br />';
			echo '<blockquote>' . mysql_errno() . ':' . mysql_error() . '</blockquote><br />';
			echo 'Was caused by this query:<blockquote>' . $query . '</blockquote><br />';
			if (function_exists('debug_backtrace'))
				echo 'We attempted to find the backtrace:<pre>' . var_dump(debug_backtrace()) . '</pre>';
		}
	}
	else
		exit('No valid version of SMF found');
}

function script_fetch($resource_id, $use_row = false)
{
	global $smcFunc, $func;

	if ($use_row)
	{
		if (isset($smcFunc['db_fetch_row']))
			return $smcFunc['db_fetch_row']($resource_id);
		else
			return mysql_fetch_row($resource_id);
	}
	else
	{
		if (isset($smcFunc['db_fetch_assoc']))
			return $smcFunc['db_fetch_assoc']($resource_id);
		else
			return mysql_fetch_assoc($resource_id);
	}
}

function script_modify_column($table_name, $column_name, $column_info)
{
	global $smcFunc, $func, $db_prefix;

	if (isset($smcFunc['db_add_column']))
	{
		$column_info = array_merge($column_info, array('no_prefix' => true));
		return $smcFunc['db_change_column']("{$db_prefix}{$table_name}", $column_name, $column_info, array('no_prefix' => TRUE));
	}
	else
	{
		$columns = script_list_columns("{$db_prefix}{$table_name}");
		$old_info = null;
		foreach ($columns as $column)
			if (strtolower($column['name']) == strtolower($column_name))
				$old_info = $column;

		// Get the right bits.
		if (!isset($column_info['name']))
			$column_info['name'] = $column_name;
		if (!isset($column_info['default']))
			$column_info['default'] = $old_info['default'];
		if (!isset($column_info['null']))
			$column_info['null'] = $old_info['null'];
		if (!isset($column_info['auto']))
			$column_info['auto'] = $old_info['auto'];
		if (!isset($column_info['type']))
			$column_info['type'] = $old_info['type'];
		if (!isset($column_info['size']))
			$column_info['size'] = $old_info['size'];
		$column_info['type'] = $column_info['type'] . $column_info['size'];

		return script_query('ALTER TABLE ' . $db_prefix . $table_name . '
			CHANGE `' . $column_info['name'] . '` `' . $column_info['name'] . '` ' . $column_info['type'] . ' ' . (empty($column_info['null']) ? 'NOT NULL' : '') . ' ' .
		(empty($column_info['default']) ? '' : 'default \'' . $column_info['default'] . '\'') . ' ' .
		(empty($column_info['auto']) ? '' : 'auto_increment') . ' ');
	}
}

function script_list_columns($table_name)
{
	$result = script_query('
		SHOW FIELDS
		FROM ' . $table_name);
	$columns = array();
	while ($row = mysql_fetch_assoc($result))
	{
		// Is there an auto_increment?
		$auto = strpos($row['Extra'], 'auto_increment') !== false ? true : false;

		// Can we split out the size?
		if (preg_match('~(.+?)\s*(\(\d+\))~i', $row['Type'], $matches))
		{
			$type = $matches[1];
			$size = $matches[2];
		}
		else
		{
			$type = $row['Type'];
			$size = null;
		}

		$columns[] = array(
			'name' => $row['Field'],
			'null' => $row['Null'] != 'YES' ? false : true,
			'default' => isset($row['Default']) ? $row['Default'] : null,
			'type' => $type,
			'size' => $size,
			'auto' => $auto,
		);
	}
	mysql_free_result($result);

	return $columns;
}
?>