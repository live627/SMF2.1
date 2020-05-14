<?php

require_once('./SSI.php');
require_once "./vendor/autoload.php";
$db_show_debug = true;
$cache_memcached = 'localhost';
$pg_cache_server = 'localhost';
$pg_cache_user = 'postgres';
$pg_cache_passwd = '';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

add_integration_function('integrate_verify_user', 'FeignLogin', false);

function FeignLogin()
{
	remove_integration_function('integrate_verify_user', 'FeignLogin', false);

	return 1;
}
loadUserSettings();
loadTheme();

add_integration_function('integrate_outgoing_email', 'SendMailToQueue', false);

function SendMailToQueue(&$subject, &$message, &$headers, &$to_array)
{
	return AddMailQueue(false, $to_array, $subject, $message, $headers);

	//return true;
}