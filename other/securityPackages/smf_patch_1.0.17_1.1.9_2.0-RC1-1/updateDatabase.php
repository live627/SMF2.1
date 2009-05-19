<?php
	global $db_server, $db_user, $db_passwd, $db_type;
	global $db_name, $db_connection, $smcFunc, $func;

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

// If it's executed from the Packages/tmp dir.
elseif (file_exists(dirname(__FILE__) . '/../../SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/../../SSI.php');

// Using SMF 2.0?
if (!empty($smcFunc) && isset($smcFunc['db_query']))
{
	// Get our packages.
	db_extend('packages');

	// Add the new column.
	$smcFunc['db_add_column']('attachments', array(
		'name' => 'file_hash',
		'type' => 'varchar',
		'size' => 40,
	));

	// We're making database changes so the database version changes.
	updateSettings(array('smfVersion', 'SMF 2.0 RC1-1'));
}
// Using 1.0 or 1.1?
elseif (function_exists('db_query'))
{
	$has_column = db_query("
		SHOW COLUMNS
		FROM {$db_prefix}attachments
		LIKE 'file_hash'"
	, false, false);

	if (empty($has_column) || mysql_num_rows($has_column) != 1)
	{
		// Simpler times.
		db_query('
			ALTER IGNORE TABLE ' . $db_prefix . 'attachments
			ADD COLUMN file_hash varchar(40) NOT NULL default \'\'
		', false, false);
	}
	mysql_free_result($has_column);

	// We're making database changes so the database version changes.
	updateSettings(array('smfVersion', 'SMF ' . (isset($func['entity_fix']) ? '1.1.9' : '1.0.17')));
}
// Fall back / running independently without SSI? :(
else
{
	// No SSI present? Try it the even more basic way.
	if (file_exists(dirname(__FILE__) . '/../../Settings.php'))
		require_once(dirname(__FILE__) . '/../../Settings.php');
	else
		require_once(dirname(__FILE__) . '/Settings.php');

	// Using 2.0 and non-mysql.
	if (!empty($db_type) && $db_type != 'mysql')
		trigger_error('Unable to connect to database', E_USER_ERROR);

	if (empty($db_persist))
		$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);
	else
		$db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);

	if (!$db_connection || !@mysql_select_db($db_name, $db_connection))
		trigger_error('Unable to connect to database', E_USER_ERROR);

	$has_column = mysql_query('
		SHOW COLUMNS
		FROM ' . $db_prefix . 'attachments
		LIKE "file_hash"
	');

	// Does the column already exist?
	if (empty($has_column) || mysql_num_rows($has_column) != 1)
	{
		// Simpler times.
		mysql_query('
			ALTER IGNORE TABLE ' . $db_prefix . 'attachments
			ADD COLUMN file_hash varchar(40) NOT NULL default \'\'
		');
	}
	mysql_free_result($has_column);

	// Maybe we can try to update the SMF version as well.
	$request = mysql_query('
		SELECT value
		FROM ' . $db_prefix . 'settings
		WHERE variable = "smfVersion"');

	if (empty($request) || mysql_num_rows($request) > 0)
		exit;
	list($old_version) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Hopefully it is easy to find.
	if (substr($old_version, 0, 3) == '1.0')
		$new_version = '1.0.17';
	elseif (substr($old_version, 0, 3) == '1.1')
		$new_version = '1.1.9';
	elseif (substr($old_version, 0, 3) == '2.0')
		$new_version = '2.0 RC1-1';
	else
	{
		$parts = explode('.', $old_version);

		// Hopefully the last item is a int, otherwise we failed.
		if (is_int($parts[count($parts - 1)]))
		{
			$parts[count($parts - 1)] = $parts[count($parts - 1)] += 1;
			$new_version = implode('.', $parts);
		}
	}

	// Now make the changes.
	if (!empty($new_version))
		mysql_query('UPDATE ' . $db_prefix . 'settings
			SET value = "' . $new_version . '"
			WHERE variable = "smfVersion"
				AND value = "' . $old_version . '"');
}

?>