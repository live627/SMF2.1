<?php

require_once('./SSI.php');
require_once "./vendor/autoload.php";
$db_show_debug = true;
$cache_memcached = 'localhost';
$pg_cache_server = 'localhost';
$pg_cache_user = 'postgres';
$pg_cache_passwd = '';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

ob_end_clean();

add_integration_function('integrate_verify_user', 'FeignLoginIntegration');

function FeignLoginIntegration()
{
	global $mem;

	return $mem;
}
function FeignLogin($id = 1)
{
	global $mem;
	$mem = $id;
	loadUserSettings();
	loadPermissions();
}
$smcFunc['db_query']('', '
	UPDATE {db_prefix}scheduled_tasks
	SET disabled = 1');
$smcFunc['db_query']('truncate_table', '
	TRUNCATE {db_prefix}mail_queue');
loadTheme();
FeignLogin(1);

add_integration_function('integrate_outgoing_email', 'SendMailToQueue');

function SendMailToQueue(&$subject, &$message, &$headers, &$to_array)
{
	return AddMailQueue(false, $to_array, $subject, $message, $headers);

	//return true;
}