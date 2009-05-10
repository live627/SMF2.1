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
}
elseif (function_exists('db_query'))
{
	// Simpler times.
	db_query('
		ALTER IGNORE TABLE ' . $db_prefix . 'attachments
		ADD COLUMN file_hash varchar(40) NOT NULL default \'\'
	', false, false);
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
}

?>