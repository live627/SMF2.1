<?php
/**********************************************************************************
* DbPackages-sqlite.php                                                           *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 4                                      *
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

/*	This file contains database functionality specifically designed for packages to utilize.

	bool smf_db_create_table(string table_name, array columns, array indexes = array(),
		array parameters = array(), string if_exists = 'ignore')
		- Can be used to create a table without worrying about schema compatabilities.
		- Will add $db_prefix to the table name unless 'no_prefix' set as a parameter.
		- If the table exists will, by default, do nothing.
		- Builds table with columns as passed to it - at least one column must be sent.
		  The columns array should have one sub-array for each column - these sub arrays contain:
			+ 'name' = Column name
			+ 'type' = Type of column - values from (smallint,mediumint,int,text,varchar,char,tinytext,mediumtext,largetext)
			+ 'size' => Size of column (If applicable) - for example 255 for a large varchar, 10 for an int etc. If not
						set SMF will pick a size.
			+ 'default' = Default value - do not set if no default required.
			+ 'null' => Can it be null (true or false) - if not set default will be false.
			+ 'auto' => Set to true to make it an auto incrementing column. Set to a numerical value to set
						from what it should begin counting.
		- Adds indexes as specified within indexes parameter. Each index should be a member of $indexes. Values are:
			+ 'name' => Index name (If left empty SMF will generate).
			+ 'type' => Type of index. Choose from 'primary', 'unique' or 'index'. If not set will default to 'index'.
			+ 'columns' => Array containing columns that form part of key - in the order the index is to be created.
		- parameters: (None yet)
		- if_exists values:
			+ 'update' will add missing columns - but NOT remove old ones.
			+ 'update_remove' will add missing columns AND remove old ones.
			+ 'ignore' will do nothing if the table exists. (And will return true)
			+ 'overwrite' will drop any existing table of the same name.
			+ 'error' will return false if the table already exists.

*/

// Add the file functions to the $smcFunc array.
function db_packages_init()
{
	global $smcFunc, $reservedTables, $db_package_log, $db_prefix;

	if (!isset($smcFunc['db_create_table']) || $smcFunc['db_create_table'] != 'db_create_table')
	{
		$smcFunc += array(
			'db_add_column' => 'smf_db_add_column',
			'db_add_index' => 'smf_db_add_index',
			'db_calculate_type' => 'smf_db_calculate_type',
			'db_change_column' => 'smf_db_change_column',
			'db_create_table' => 'smf_db_create_table',
			'db_drop_table' => 'smf_db_drop_table',
			'db_table_structure' => 'smf_db_table_structure',
			'db_list_columns' => 'smf_db_list_columns',
			'db_list_indexes' => 'smf_db_list_indexes',
			'db_remove_column' => 'smf_db_remove_column',
			'db_remove_index' => 'smf_db_remove_index',
		);
		$db_package_log = array();
	}

	// We setup an array of SMF tables we can't do auto-remove on - in case a mod writer cocks it up!
	$reservedTables = array('admin_info_files', 'approval_queue', 'attachments', 'ban_groups', 'ban_items',
		'board_permissions', 'boards', 'calendar', 'calendar_holidays', 'categories', 'collapsed_categories',
		'custom_fields', 'group_moderators', 'log_actions', 'log_activity', 'log_banned', 'log_boards',
		'log_digest', 'log_errors', 'log_floodcontrol', 'log_group_requests', 'log_karma', 'log_mark_read',
		'log_notify', 'log_online', 'log_packages', 'log_polls', 'log_reported', 'log_reported_comments',
		'log_scheduled_tasks', 'log_search_messages', 'log_search_results', 'log_search_subjects',
		'log_search_topics', 'log_topics', 'mail_queue', 'membergroups', 'members', 'message_icons',
		'messages', 'moderators', 'package_servers', 'permission_profiles', 'permissions', 'personal_messages',
		'pm_recipients', 'poll_choices', 'polls', 'scheduled_tasks', 'sessions', 'settings', 'smileys',
		'themes', 'topics');
	foreach ($reservedTables as $k => $table_name)
		$reservedTables[$k] = $db_prefix . $table_name;

	// We in turn may need the extra stuff.
	db_extend('extra');
}

// Create a table.
//!!! Add/remove indexes too?
function smf_db_create_table($table_name, $columns, $indexes = array(), $parameters = array(), $if_exists = 'update', $error = 'fatal')
{
	global $reservedTables, $smcFunc, $db_package_log, $db_prefix;

	// Append the prefix?
	if (empty($parameters['no_prefix']))
		$table_name = $db_prefix . $table_name;

	// First - no way do we touch SMF tables.
	if (in_array(strtolower($table_name), $reservedTables))
		return false;

	// Log that we'll want to remove this on uninstall.
	$db_package_log[] = array('remove_table', $table_name);

	// This table not exist?
	$tables = $smcFunc['db_list_tables']();
	foreach ($tables as $table)
	{
		if ($table == $table_name)
		{
			// This is a sad day... drop the table?
			if ($if_exists == 'overwrite')
				$smcFunc['db_drop_table']($table_name, array('no_prefix' => true));
			elseif ($if_exists == 'ignore')
				return true;
			elseif ($if_exists == 'error')
				return false;
			// Otherwise we have to sort through the columns and add/remove ones which are wrong!
			else
			{
				$old_columns = $smcFunc['db_list_columns']($table_name);
				foreach ($old_columns as $k => $v)
					$old_columns[$k] = strtolower($v);
				foreach ($columns as $column)
				{
					// Already exists?
					if (in_array(strtolower($column['name']), $old_columns))
					{
						$k = array_search(strtolower($column['name']), $old_columns);
						unset($old_columns[$k]);
					}
					// Doesn't - add it!
					else
						$smcFunc['db_add_column']($table_name, $column, array('no_prefix' => true));
				}
				// Whatever is left needs to be removed.
				if ($if_exists == 'update_remove')
				{
					foreach ($old_columns as $column)
						$smcFunc['db_remove_column']($table_name, $column, array('no_prefix' => true));
				}

				// All done!
				return true;
			}
		}
	}

	// Righty - let's do the damn thing!
	$table_query = 'CREATE TABLE ' . $table_name . "\n" .'(';
	$done_primary = false;
	foreach ($columns as $column)
	{
		// Auto increment is special
		if (!empty($column['auto']))
		{
			$table_query .= "\n" .$column['name'] .' integer PRIMARY KEY,';
			$done_primary = true;
			continue;
		}
		elseif (isset($column['default']) && $column['default'] != null)
			$default = 'default \'' . $column['default'] . '\'';
		else
			$default = '';

		// Sort out the size... and stuff...
		$column['size'] = isset($column['size']) ? $column['size'] : null;
		list ($type, $size) = $smcFunc['db_calculate_type']($column['type'], $column['size']);
		if ($size !== null)
			$type = $type . '(' . $size . ')';

		// Now just put it together!
		$table_query .= "\n\t" .$column['name'] .' ' . $type . ' ' . (!empty($column['null']) ? '' : 'NOT NULL') . ' ' . $default . ',';
	}

	// Loop through the indexes next...
	$index_queries = array();
	foreach ($indexes as $index)
	{
		$columns = implode(',', $index['columns']);

		// Is it the primary?
		if (isset($index['type']) && $index['type'] == 'primary')
		{
			// IF we've done the primary via auto_inc don't do it again!
			if (!$done_primary)
				$table_query .= "\n\t" . 'PRIMARY KEY (' . implode(',', $index['columns']) . '),';
		}
		else
		{
			if (empty($index['name']))
				$index['name'] = implode('_', $index['columns']);
			$index_queries[] = 'CREATE ' . (isset($index['type']) && $index['type'] == 'unique' ? 'UNIQUE' : '') . ' INDEX ' . $table_name . '_' . $index['name'] . ' ON ' . $table_name . ' (' . $columns . ')';
		}
	}

	// No trailing commas!
	if (substr($table_query, -1) == ',')
		$table_query = substr($table_query, 0, -1);

	$table_query .= ')';

	$smcFunc['db_transaction']('begin');
	// Do the table and indexes...
	$smcFunc['db_query']('', $table_query,
		'security_override'
	);
	foreach ($index_queries as $query)
		$smcFunc['db_query']('', $query,
		'security_override'
	);

	$smcFunc['db_transaction']('commit');
}

// Drop a table.
function smf_db_drop_table($table_name, $parameters = array(), $error = 'fatal')
{
	global $reservedTables, $smcFunc, $db_prefix;

	// What's that - you don't want my prefix?
	if (empty($parameters['no_prefix']))
		$table_name = $db_prefix . $table_name;

	// God no - dropping one of these = bad.
	if (in_array(strtolower($table_name), $reservedTables))
		return false;

	// Does it exist?
	if (in_array($table_name, $smcFunc['db_list_tables']()))
	{
		$query = 'DROP TABLE ' . $table_name;
		$smcFunc['db_query']('', $query,
		'security_override'
	);

		return true;
	}

	// Otherwise do 'nout.
	return false;
}

// Add a column.
function smf_db_add_column($table_name, $column_info, $parameters = array(), $if_exists = 'update', $error = 'fatal')
{
	global $smcFunc, $db_package_log, $txt, $db_prefix;

	// Add a prefix?
	if (empty($parameters['no_prefix']))
		$table_name = $db_prefix . $table_name;

	// Log that we will want to uninstall this!
	$db_package_log[] = array('remove_column', $table_name, $column_info['name']);

	// Does it exist - if so don't add it again!
	$columns = $smcFunc['db_list_columns']($table_name);
	foreach ($columns as $column)
		if ($column == $column_info['name'])
		{
			// If we're going to overwrite then use change column.
			if ($if_exists == 'update')
				return $smcFunc['db_change_column']($table_name, $column_info['name'], $column_info, array('no_prefix' => true));
			else
				return false;
		}

	// Get the specifics...
	$column_info['size'] = isset($column_info['size']) ? $column_info['size'] : null;
	list ($type, $size) = $smcFunc['db_calculate_type']($column_info['type'], $column_info['size']);
	if ($size !== null)
		$type = $type . '(' . $size . ')';

	// Now add the thing!
	$query = '
		ALTER TABLE ' . $table_name . '
		ADD ' . $column_info['name'] . ' ' . $type . ' ' . (empty($column_info['null']) ? 'NOT NULL' : '') . ' ' .
			(!isset($column_info['default']) ? '' : 'default \'' . $column_info['default'] . '\'');
	$smcFunc['db_query']('', $query,
		'security_override'
	);

	return true;
}

// We can't reliably do this on SQLite - damn!
function smf_db_remove_column($table_name, $column_name, $parameters = array(), $error = 'fatal')
{
	// Are we gonna prefix?
	if (empty($parameters['no_prefix']))
		$table_name = $db_prefix . $table_name;

	return true;
}

// Change a column.
function smf_db_change_column($table_name, $old_column, $column_info, $parameters = array(), $error = 'fatal')
{
	global $smcFunc, $db_prefix;

	// Prefix, prefix, where art thou prefix?
	if (empty($parameters['no_prefix']))
		$table_name = $db_prefix . $table_name;

	// Can't do anything with SQLite!
	//!!! Remove, copy, then add column?
	return true;
}

// Add an index.
function smf_db_add_index($table_name, $index_info, $parameters = array(), $if_exists = 'update', $error = 'fatal')
{
	global $smcFunc, $db_package_log, $db_prefix;

	// What you want up front?
	if (empty($parameters['no_prefix']))
		$table_name = $db_prefix . $table_name;

	// No columns = no index.
	if (empty($index_info['columns']))
		return false;
	$columns = implode(',', $index_info['columns']);

	// No name - make it up!
	if (empty($index_info['name']))
	{
		// No need for primary.
		if ($index_info['type'] == 'primary')
			$index_info['name'] = '';
		else
			$index_info['name'] = implode('_', $index_info['columns']);
	}
	else
		$index_info['name'] = $index_info['name'];

	// Log that we are going to want to remove this!
	$db_package_log[] = array('remove_index', $table_name, $index_info['name']);

	// Let's get all our indexes.
	$indexes = $smcFunc['db_list_indexes']($table_name);
	// Do we already have it?
	foreach ($indexes as $index)
	{
		if ($index['name'] == $index_info['name'] || ($index['is_primary'] && isset($index_info['type']) && $index_info['type'] == 'primary'))
		{
			// If we want to overwrite simply remove the current one then continue.
			if ($if_exists == 'update')
				$smcFunc['db_remove_index']($table_name, $index_info['name'], array('no_prefix' => true));
			else
				return false;
		}
	}

	// If we're here we know we don't have the index - so just add it.
	if (!empty($index_info['type']) && $index_info['type'] == 'primary')
	{
		//!!! Doesn't work with PRIMARY KEY yet.
	}
	else
	{
		$smcFunc['db_query']('', '
			CREATE ' . (isset($index_info['type']) && $index_info['type'] == 'unique' ? 'UNIQUE' : '') . ' INDEX ' . $index_info['name'] . ' ON ' . $table_name . ' (' . $columns . ')',
			'security_override'
		);
	}
}

// Remove an index.
function smf_db_remove_index($table_name, $index_name, $parameters = array(), $error = 'fatal')
{
	global $smcFunc, $db_prefix;

	// Nothing to hide Mr Hidey Man.
	if (empty($parameters['no_prefix']))
		$table_name = $db_prefix . $table_name;

	// Better exist!
	$indexes = $smcFunc['db_list_indexes']($table_name, true);

	foreach ($indexes as $index)
	{
		//!!! Doesn't do primary key at the moment!
		if ($index['type'] != 'primary' && $index['name'] == $index_name)
		{
			// Drop the bugger...
			$smcFunc['db_query']('', '
				DROP INDEX ' . $index_name,
				'security_override'
			);

			return true;
		}
	}

	// Not to be found ;(
	return false;
}

// Get the schema formatted name for a type.
function smf_db_calculate_type($type_name, $type_size = null, $reverse = false)
{
	// Generic => Specific.
	if (!$reverse)
	{
		$types = array(
			'mediumint' => 'int',
			'tinyint' => 'smallint',
			'mediumtext' => 'text',
			'largetext' => 'text',
		);
	}
	else
	{
		$types = array(
			'integer' => 'int',
		);
	}

	// Got it? Change it!
	if (isset($types[$type_name]))
	{
		if ($type_name == 'tinytext')
			$type_size = 255;
		$type_name = $types[$type_name];
	}
	// Numbers don't have a size.
	if (strpos($type_name, 'int') !== false)
		$type_size = null;

	return array($type_name, $type_size);
}

// Get table structure.
function smf_db_table_structure($table_name)
{
	global $smcFunc, $db_prefix;

	$table_name = str_replace('{db_prefix}', $db_prefix, $table_name);

	return array(
		'name' => $table_name,
		'columns' => $smcFunc['db_list_columns']($table_name, true),
		'indexes' => $smcFunc['db_list_indexes']($table_name, true),
	);
}

// Harder than it should be on sqlite!
function smf_db_list_columns($table_name, $detail = false)
{
	global $smcFunc, $db_prefix;

	$table_name = str_replace('{db_prefix}', $db_prefix, $table_name);

	$result = $smcFunc['db_query']('', '
		PRAGMA table_info(' . $table_name . ')',
		'security_override'
	);
	$columns = array();

	$primaries = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		if (!$detail)
		{
			$columns[] = $row['name'];
		}
		else
		{
			// Auto increment is hard to tell really... if there's only one primary it probably is.
			if ($row['pk'])
				$primaries[] = $row['name'];

			// Can we split out the size?
			if (preg_match('~(.+?)\s*(\(\d+\))~i', $row['type'], $matches))
			{
				$type = $matches[1];
				$size = $matches[2];
			}
			else
			{
				$type = $row['type'];
				$size = null;
			}

			$columns[$row['name']] = array(
				'name' => $row['name'],
				'null' => $row['notnull'] ? false : true,
				'default' => $row['dflt_value'],
				'type' => $type,
				'size' => $size,
				'auto' => false,
			);
		}
	}
	$smcFunc['db_free_result']($result);

	// Put in our guess at auto_inc.
	if (count($primaries) == 1)
		$columns[$primaries[0]]['auto'] = true;

	return $columns;
}

// What about some index information?
function smf_db_list_indexes($table_name, $detail = false)
{
	global $smcFunc, $db_prefix;

	$table_name = str_replace('{db_prefix}', $db_prefix, $table_name);

	$result = $smcFunc['db_query']('', '
		PRAGMA index_list(' . $table_name . ')',
		'security_override'
	);
	$indexes = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		if (!$detail)
			$indexes[] = $row['name'];
		else
		{
			$result2 = $smcFunc['db_query']('', '
				PRAGMA index_info(' . $row['name'] . ')',
				'security_override'
			);
			while ($row2 = $smcFunc['db_fetch_assoc']($result2))
			{
				// What is the type?
				if ($row['unique'])
					$type = 'unique';
				else
					$type = 'index';

				// This is the first column we've seen?
				if (empty($indexes[$row['name']]))
				{
					$indexes[$row['name']] = array(
						'name' => $row['name'],
						'type' => $type,
						'columns' => array(),
					);
				}

				// Add the column...
				$indexes[$row['name']]['columns'][] = $row2['name'];
			}
			$smcFunc['db_free_result']($result);
		}
	}
	$smcFunc['db_free_result']($result);

	return $indexes;
}

?>