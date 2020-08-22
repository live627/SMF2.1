<?php

require_once('./SSI.php');
require_once "./vendor/autoload.php";
$db_show_debug = true;
$cache_memcached = 'localhost';
$pg_cache_server = 'localhost';
$pg_cache_user = 'postgres';
$pg_cache_passwd = '';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
  ob_implicit_flush();
function FeignLoginIntegration()
{
	global $mem;
	remove_integration_function('integrate_verify_user', 'FeignLoginIntegration', false);

	return $mem;
}
function FeignLogin(int $id = 1)
{
	global $mem;
	$mem = $id;
	add_integration_function('integrate_verify_user', 'FeignLoginIntegration', false);
	loadUserSettings();
	loadPermissions();
}
$smcFunc['db_query']('', '
	UPDATE {db_prefix}scheduled_tasks
	SET disabled = 1');
$smcFunc['db_query']('truncate_table', '
	TRUNCATE {db_prefix}mail_queue');
loadTheme();
FeignLogin();

add_integration_function('integrate_outgoing_email', 'SendMailToQueue', false);

function SendMailToQueue(&$subject, &$message, &$headers, &$to_array)
{
	return AddMailQueue(false, $to_array, $subject, $message, $headers);

	//return true;
}