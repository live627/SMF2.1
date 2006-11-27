<?php
/**********************************************************************************
* DbExtra-mysql.php                                                               *
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

// Add the file functions to the $smfFunc array.
function db_extra_init()
{
	global $smfFunc;

	if (!isset($smfFunc['db_backup_table']) || $smfFunc['db_backup_table'] != 'db_backup_table')
		$smfFunc += array(
			'db_backup_table' => 'db_backup_table',
			'db_optimize_table' => 'db_optimize_table',
			'db_insert_sql' => 'db_insert_sql',
			'db_table_sql' => 'db_table_sql',
			'db_list_tables' => 'db_list_tables',
		);
}

// Backup $table to $backup_table.
function db_backup_table($table, $backup_table)
{
	global $smfFunc;

	$result = $smfFunc['db_query']('', "
		SHOW CREATE TABLE " . $table, false, false);
	list (, $create) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

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

	$smfFunc['db_query']('', "
		DROP TABLE IF EXISTS " . $backup_table, false, false);

	$request = $smfFunc['db_query']('', "
		CREATE TABLE " . $backup_table . " $create
		TYPE=$engine" . (empty($charset) ? '' : " CHARACTER SET $charset" . (empty($collate) ? '' : " COLLATE $collate")) . "
		SELECT *
		FROM " . $table, false, false);

	if ($auto_inc != '')
	{
		if (preg_match('~\`(.+?)\`\s~', $auto_inc, $match) != 0 && substr($auto_inc, -1, 1) == ',')
			$auto_inc = substr($auto_inc, 0, -1);

		$smfFunc['db_query']('', "
			ALTER TABLE " . $backup_table . "
			CHANGE COLUMN $match[1] $auto_inc", false, false);
	}

	return $request;
}

// Optimize a table - return data freed!
function db_optimize_table($table)
{
	global $smfFunc;

	$request = $smfFunc['db_query']('', "
			OPTIMIZE TABLE `' . $table[0] . '`", false, false);
	if (!$request)
		return -1;

	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	if (isset($table['Data_free']))
			return $table['Data_free'] / 1024;
	else
		return 0;
}

// List all the tables in the database.
function db_list_tables($db = false, $filter = false)
{
	global $db_name, $smfFunc;

	$db = $db == false ? $db_name : $db;
	$filter = $filter == false ? '' : " LIKE $filter";

	$smfFunc['db_query']('', "
		SHOW TABLES
		FROM $db
		$filter", false, false);
	$tables = array();
	while ($row = $smfFunc['db_fetch_row']($request))
		$tables[] = $row[0];
	$smfFunc['db_free_result']($request);

	return $tables;
}

// Get the content (INSERTs) for a table.
function db_insert_sql($tableName)
{
	global $smfFunc;

	// This will be handy...
	$crlf = "\r\n";

	// Get everything from the table.
	$result = $smfFunc['db_query']('', "
		SELECT /*!40001 SQL_NO_CACHE */ *
		FROM `$tableName`", false, false);

	// The number of rows, just for record keeping and breaking INSERTs up.
	$num_rows = $smfFunc['db_num_rows']($result);
	$current_row = 0;

	if ($num_rows == 0)
		return '';

	$fields = array_keys($smfFunc['db_fetch_assoc']($result));
	$smfFunc['db_data_seek']($result, 0);

	// Start it off with the basic INSERT INTO.
	$data = 'INSERT INTO `' . $tableName . '`' . $crlf . "\t(`" . implode('`, `', $fields) . '`)' . $crlf . 'VALUES ';

	// Loop through each row.
	while ($row = $smfFunc['db_fetch_row']($result))
	{
		$current_row++;

		// Get the fields in this row...
		$field_list = array();
		for ($j = 0; $j < $smfFunc['db_num_fields']($result); $j++)
		{
			// Try to figure out the type of each field. (NULL, number, or 'string'.)
			if (!isset($row[$j]))
				$field_list[] = 'NULL';
			elseif (is_numeric($row[$j]))
				$field_list[] = $row[$j];
			else
				$field_list[] = "'" . $smfFunc['db_escape_string']($row[$j]) . "'";
		}

		// 'Insert' the data.
		$data .= '(' . implode(', ', $field_list) . ')';

		// Start a new INSERT statement after every 250....
		if ($current_row > 249 && $current_row % 250 == 0)
			$data .= ';' . $crlf . 'INSERT INTO `' . $tableName . '`' . $crlf . "\t(`" . implode('`, `', $fields) . '`)' . $crlf . 'VALUES ';
		// All done!
		elseif ($current_row == $num_rows)
			$data .= ';' . $crlf;
		// Otherwise, go to the next line.
		else
			$data .= ',' . $crlf . "\t";
	}
	$smfFunc['db_free_result']($result);

	// Return an empty string if there were no rows.
	return $num_rows == 0 ? '' : $data;
}

// Get the schema (CREATE) for a table.
function db_table_sql($tableName)
{
	global $smfFunc;

	// This will be needed...
	$crlf = "\r\n";

	// Drop it if it exists.
	$schema_create = 'DROP TABLE IF EXISTS `' . $tableName . '`;' . $crlf . $crlf;

	// Start the create table...
	$schema_create .= 'CREATE TABLE `' . $tableName . '` (' . $crlf;

	// Find all the fields.
	$result = $smfFunc['db_query']('', "
		SHOW FIELDS
		FROM `$tableName`", false, false);
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		// Make the CREATE for this column.
		$schema_create .= '  ' . $row['Field'] . ' ' . $row['Type'] . ($row['Null'] != 'YES' ? ' NOT NULL' : '');

		// Add a default...?
		if (isset($row['Default']))
		{
			// Make a special case of auto-timestamp.
			if ($row['Default'] == 'CURRENT_TIMESTAMP')
				$schema_create .= ' /*!40102 NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP */';
			else
				$schema_create .= ' default ' . (is_numeric($row['Default']) ? $row['Default'] : "'" . $smfFunc['db_escape_string']($row['Default']) . "'");
		}

		// And now any extra information. (such as auto_increment.)
		$schema_create .= ($row['Extra'] != '' ? ' ' . $row['Extra'] : '') . ',' . $crlf;
	}
	$smfFunc['db_free_result']($result);

	// Take off the last comma.
	$schema_create = substr($schema_create, 0, -strlen($crlf) - 1);

	// Find the keys.
	$result = $smfFunc['db_query']('', "
		SHOW KEYS
		FROM `$tableName`", false, false);
	$indexes = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		// IS this a primary key, unique index, or regular index?
		$row['Key_name'] = $row['Key_name'] == 'PRIMARY' ? 'PRIMARY KEY' : (empty($row['Non_unique']) ? 'UNIQUE ' : ($row['Comment'] == 'FULLTEXT' || (isset($row['Index_type']) && $row['Index_type'] == 'FULLTEXT') ? 'FULLTEXT ' : 'KEY ')) . $row['Key_name'];

		// Is this the first column in the index?
		if (empty($indexes[$row['Key_name']]))
			$indexes[$row['Key_name']] = array();

		// A sub part, like only indexing 15 characters of a varchar.
		if (!empty($row['Sub_part']))
			$indexes[$row['Key_name']][$row['Seq_in_index']] = $row['Column_name'] . '(' . $row['Sub_part'] . ')';
		else
			$indexes[$row['Key_name']][$row['Seq_in_index']] = $row['Column_name'];
	}
	$smfFunc['db_free_result']($result);

	// Build the CREATEs for the keys.
	foreach ($indexes as $keyname => $columns)
	{
		// Ensure the columns are in proper order.
		ksort($columns);

		$schema_create .= ',' . $crlf . '  ' . $keyname . ' (' . implode($columns, ', ') . ')';
	}

	// Now just get the comment and type... (MyISAM, etc.)
	$result = $smfFunc['db_query']('', "
		SHOW TABLE STATUS
		LIKE '" . strtr($tableName, array('_' => '\\_', '%' => '\\%')) . "'", false, false);
	$row = $smfFunc['db_fetch_assoc']($result);
	$smfFunc['db_free_result']($result);

	// Probably MyISAM.... and it might have a comment.
	$schema_create .= $crlf . ') TYPE=' . (isset($row['Type']) ? $row['Type'] : $row['Engine']) . ($row['Comment'] != '' ? ' COMMENT="' . $row['Comment'] . '"' : '');

	return $schema_create;
}

?>
