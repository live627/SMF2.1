<?php
	global $db_server, $db_user, $db_passwd;
	global $db_name, $db_connection;

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

	updateSettings(array('smfVersion', 'SMF 2.0 RC1-1'));
}
elseif (function_exists('db_query'))
{
	// Simpler times.
	db_query('
		ALTER IGNORE TABLE ' . $db_prefix . 'attachments
		ADD COLUMN file_hash varchar(40) NOT NULL default \'\'
	', false, false);

	$request = db_query('
		SELECT value
		FROM ' . $db_prefix . 'settings
		WHERE variable = "smfVersion"', false, false);
}
else
{
	// No SSI present? Try it the even more basic way.
	if (file_exists(dirname(__FILE__) . '/../../Settings.php'))
		require_once(dirname(__FILE__) . '/../../Settings.php');
	else
		require_once(dirname(__FILE__) . '/Settings.php');

	if (empty($db_persist))
		$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);
	else
		$db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);

	if (!$db_connection || !@mysql_select_db($db_name, $db_connection))
		trigger_error('Unable to connect to database', E_USER_ERROR);

	mysql_query('
		ALTER IGNORE TABLE ' . $db_prefix . 'attachments
		ADD COLUMN file_hash varchar(40) NOT NULL default \'\'
	');

	$request = mysql_query('
		SELECT value
		FROM ' . $db_prefix . 'settings
		WHERE variable = "smfVersion"');
}

// Maybe we can try to update the SMF version as well.
if (!empty($request) && mysql_num_rows($reuqest) > 0)
{
	list($old_version) = mysql_fetch_row($request);

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
			$new_version = $parts[count($parts - 1)] += 1;
	}

	// Now make the changes, first try db_query.
	if (!empty($version) && function_exists('db_query'))
		db_query('UPDATE ' . $db_prefx . 'settings
			SET value = "' . $new_version . '"
			WHERE variable = "smfVersion"
				AND value = "' . $old_version . '"', false, false);
	elseif (!empty($version))
		mysql_query('UPDATE ' . $db_prefx . 'settings
			SET value = "' . $new_version . '"
			WHERE variable = "smfVersion"
				AND value = "' . $old_version . '"');
}

?>