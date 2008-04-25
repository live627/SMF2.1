<?php
/**********************************************************************************
* DbExtra-sqlite.php                                                              *
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

/*	This file contains rarely used extended database functionality.

	// !!!

	string db_insert_sql(string table_name)
		- gets all the necessary INSERTs for the table named table_name.
		- goes in 250 row segments.
		- returns the query to insert the data back in.
		- returns an empty string if the table was empty.

	string db_table_sql(string table_name)
		- dumps the CREATE for the specified table. (by table_name.)
		- returns the CREATE statement.

*/

// Add the file functions to the $smcFunc array.
function db_extra_init()
{
	global $smcFunc;

	if (!isset($smcFunc['db_backup_table']) || $smcFunc['db_backup_table'] != 'smf_db_backup_table')
		$smcFunc += array(
			'db_backup_table' => 'smf_db_backup_table',
			'db_optimize_table' => 'smf_db_optimize_table',
			'db_insert_sql' => 'smf_db_insert_sql',
			'db_table_sql' => 'smf_db_table_sql',
			'db_list_tables' => 'smf_db_list_tables',
			'db_get_backup' => 'smf_db_get_backup',
			'db_get_version' => 'smf_db_get_version',
		);
}

// Backup $table to $backup_table.
function smf_db_backup_table($table, $backup_table)
{
	global $smcFunc, $db_prefix;

	$table = str_replace('{db_prefix}', $db_prefix, $table);

	$result = $smcFunc['db_query']('', '
		SHOW CREATE TABLE {raw:table}',
		array(
			'table' => $table,
		)
	);
	list (, $create) = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);

	$create = preg_split('/[\n\r]/', $create);

	$auto_inc = '';
	// Default engine type.
	$engine = 'MyISAM';
	$charset = '';
	$collate = '';

	foreach ($create as $k => $l)
	{
		// Get the name of the auto_increment column.
		if (strpos($l, 'auto_increment'))
			$auto_inc = trim($l);

		// For the engine type, see if we can work out what it is.
		if (strpos($l, 'ENGINE') !== false || strpos($l, 'TYPE') !== false)
		{
			// Extract the engine type.
			preg_match('~(ENGINE|TYPE)=(\w+)(\sDEFAULT)?(\sCHARSET=(\w+))?(\sCOLLATE=(\w+))?~', $l, $match);

			if (!empty($match[1]))
				$engine = $match[1];

			if (!empty($match[2]))
				$engine = $match[2];

			if (!empty($match[5]))
				$charset = $match[5];

			if (!empty($match[7]))
				$collate = $match[7];
		}

		// Skip everything but keys...
		if (strpos($l, 'KEY') === false)
			unset($create[$k]);
	}

	if (!empty($create))
		$create = '(
			' . implode('
			', $create) . ')';
	else
		$create = '';

	$smcFunc['db_query']('', '
		DROP TABLE IF EXISTS {raw:backup_table}',
		array(
			'backup_table' => $backup_table,
		)
	);

	$request = $smcFunc['db_query']('', '
		CREATE TABLE {raw:backup_table} {raw:create}
		TYPE={raw:engine}' . (empty($charset) ? '' : ' CHARACTER SET {raw:charset}' . (empty($collate) ? '' : ' COLLATE {raw:collate}')) . '
		SELECT *
		FROM {raw:table}',
		array(
			'backup_table' => $backup_table,
			'table' => $table,
			'create' => $create,
			'engine' => $engine,
			'charset' => empty($charset) ? '' : $charset,
			'collate' => empty($collate) ? '' : $collate,
		)
	);

	if ($auto_inc != '')
	{
		if (preg_match('~\`(.+?)\`\s~', $auto_inc, $match) != 0 && substr($auto_inc, -1, 1) == ',')
			$auto_inc = substr($auto_inc, 0, -1);

		$smcFunc['db_query']('', '
			ALTER TABLE {raw:backup_table}
			CHANGE COLUMN {raw:column_detail} {raw:auto_inc}',
			array(
				'backup_table' => $backup_table,
				'column_detail' => $match[1],
				'auto_inc' => $auto_inc,
			)
		);
	}

	return $request;
}

// Optimize a table - return data freed!
function smf_db_optimize_table($table)
{
	global $smcFunc, $db_prefix;

	$table = str_replace('{db_prefix}', $db_prefix, $table);

	return 0;
}

// List all the tables in the database.
function smf_db_list_tables($db = false, $filter = false)
{
	global $smcFunc;

	$filter = $filter == false ? '' : ' AND name LIKE \'' . $filter . '\'';

	$request = $smcFunc['db_query']('', '
		SELECT name
		FROM sqlite_master
		WHERE type = {string:type}
		{raw:filter}
		ORDER BY name',
		array(
			'type' => 'table',
			'filter' => $filter,
		)
	);
	$tables = array();
	while ($row = $smcFunc['db_fetch_row']($request))
		$tables[] = $row[0];
	$smcFunc['db_free_result']($request);

	return $tables;
}

// Get the content (INSERTs) for a table.
function smf_db_insert_sql($tableName)
{
	global $smcFunc, $db_prefix;

	$tableName = str_replace('{db_prefix}', $db_prefix, $tableName);

	// This will be handy...
	$crlf = "\r\n";

	// Get everything from the table.
	$result = $smcFunc['db_query']('', '
		SELECT
		FROM {raw:table}',
		array(
			'table' => $tableName,
		)
	);

	// The number of rows, just for record keeping and breaking INSERTs up.
	$num_rows = $smcFunc['db_num_rows']($result);
	$current_row = 0;

	if ($num_rows == 0)
		return '';

	$fields = array_keys($smcFunc['db_fetch_assoc']($result));
	$smcFunc['db_data_seek']($result, 0);

	// Start it off with the basic INSERT INTO.
	$data = '';
	$insert_msg = $crlf . 'INSERT INTO ' . $tableName . $crlf . "\t" . '(' . implode(', ', $fields) . ')' . $crlf . 'VALUES ' . $crlf . "\t";

	// Loop through each row.
	while ($row = $smcFunc['db_fetch_row']($result))
	{
		$current_row++;

		// Get the fields in this row...
		$field_list = array();
		for ($j = 0; $j < $smcFunc['db_num_fields']($result); $j++)
		{
			// Try to figure out the type of each field. (NULL, number, or 'string'.)
			if (!isset($row[$j]))
				$field_list[] = 'NULL';
			elseif (is_numeric($row[$j]))
				$field_list[] = $row[$j];
			else
				$field_list[] = '\'' . $smcFunc['db_escape_string']($row[$j]) . '\'';
		}

		// 'Insert' the data.
		$data .= $insert_msg . '(' . implode(', ', $field_list) . ');';
	}
	$smcFunc['db_free_result']($result);

	// Return an empty string if there were no rows.
	return $num_rows == 0 ? '' : $data;
}

// Get the schema (CREATE) for a table.
function smf_db_table_sql($tableName)
{
	global $smcFunc, $db_prefix;

	$tableName = str_replace('{db_prefix}', $db_prefix, $tableName);

	// This will be needed...
	$crlf = "\r\n";

	// Start the create table...
	$schema_create = 'CREATE TABLE ' . $tableName . ' (' . $crlf;
	$index_create = '';
	$seq_create = '';

	// Find all the fields.
	$result = $smcFunc['db_query']('', '
		SELECT column_name, column_default, is_nullable, data_type, character_maximum_length
		FROM information_schema.columns
		WHERE table_name = {string:table}
		ORDER BY ordinal_position',
		array(
			'table' => $tableName,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		if ($row['data_type'] == 'character varying')
			$row['data_type'] = 'varchar';
		elseif ($row['data_type'] == 'character')
			$row['data_type'] = 'char';
		if ($row['character_maximum_length'])
			$row['data_type'] .= '(' . $row['character_maximum_length'] . ')';

		// Make the CREATE for this column.
		$schema_create .= '  ' . $row['column_name'] . ' ' . $row['data_type'] . ($row['is_nullable'] != 'YES' ? ' NOT NULL' : '');

		// Add a default...?
		if (trim($row['column_default']) != '')
		{
			$schema_create .= ' default ' . $row['column_default'] . '';

			// Auto increment?
			if (preg_match('~nextval\(\'(.+?)\'(.+?)*\)~i', $row['column_default'], $matches) != 0)
			{
				// Get to find the next variable first!
				$count_req = $smcFunc['db_query']('', '
					SELECT MAX({raw:column})
					FROM {raw:table}',
					array(
						'column' => $row['column_name'],
						'table' => $tableName,
					)
				);
				list ($max_ind) = $smcFunc['db_fetch_row']($count_req);
				$smcFunc['db_free_result']($count_req);
				//!!! Get the right bloody start!
				$seq_create .= 'CREATE SEQUENCE ' . $matches[1] . ' START WITH ' . ($max_ind+ 1) . ';' . $crlf . $crlf;
			}
		}

		$schema_create .= ',' . $crlf;
	}
	$smcFunc['db_free_result']($result);

	// Take off the last comma.
	$schema_create = substr($schema_create, 0, -strlen($crlf) - 1);

	$result = $smcFunc['db_query']('', '
		SELECT CASE WHEN i.indisprimary THEN 1 ELSE 0 END AS is_primary, pg_get_indexdef(i.indexrelid) AS inddef
		FROM pg_class AS c, pg_class AS c2, pg_index AS i
		WHERE c.relname = {string:table}
			AND c.oid = i.indrelid
			AND i.indexrelid = c2.oid',
		array(
			'table' => $tableName,
		)
	);
	$indexes = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		if ($row['is_primary'])
		{
			if (preg_match('~\(([^\)]+?)\)~i', $row['inddef'], $matches) == 0)
				continue;

			$index_create .= $crlf . 'ALTER TABLE ' . $tableName . ' ADD PRIMARY KEY (' . $matches[1] . ');';
		}
		else
			$index_create .= $crlf . $row['inddef'] . ';';
	}
	$smcFunc['db_free_result']($result);

	// Finish it off!
	$schema_create .= $crlf . ');';

	return $seq_create . $schema_create . $index_create;
}

// Get the version number.
function smf_db_get_version()
{
	return sqlite_libversion();
}

// Simple return the database - and die!
function smf_db_get_backup()
{
	global $db_name;

	if (substr($db_name, -3) != 'db')
		$db_name .= '.db';

	// Add more info if zipped...
	$ext = '';
	if (isset($_REQUEST['compress']) && function_exists('gzencode'))
		$ext = '.gz';

	// Do the remaining headers.
	header('Content-Disposition: attachment; filename="' . $db_name . $ext . '"');
	header('Cache-Control: private');
	header('Connection: close');

	// Literally dump the contents.  Try reading the file first.
	if (@readfile($db_name) == null)
		echo file_get_contents($db_name);

	obExit(false);
}

?>