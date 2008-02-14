<?php
/**********************************************************************************
* Subs-OpenID.php                                                                 *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 2.1                                    *
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

/*	This file handles all of the OpenID interfacing and communications.
	void smf_openID_validate(string openid_url, bool allow_immediate_validation = true)
		- openid_uri is the URI given by the user
		- Validates the URI and changes it to a fully canonicalize URL
		- Determines the IDP server and delegation
		- optional array of fields to restore when validation complete.
		- Redirects the user to the IDP for validation

*/

function smf_openID_validate($openid_uri, $return = false, $save_fields = array())
{
	global $sourcedir, $scripturl, $boardurl, $modSettings;

	$openid_url = smf_openID_canonize($openid_uri);

	$response_data = smf_openID_getServerInfo($openid_url);

	if (($assoc = smf_openID_getAssociation($response_data['server'])) == null)
		$assoc = smf_openID_makeAssociation($response_data['server']);

	// Before we go wherever it is we are going, store the GET and POST data, because it might be useful when we get back.
	$request_time = time();
	// Just in case they are doing something else at this time.
	while(isset($_SESSION['openid']['saved_data'][$request_time]))
		$request_time = md5($request_time);

	$_SESSION['openid']['saved_data'][$request_time] = array(
		'get' => $_GET,
		'post' => $_POST,
		'openid_uri' => $openid_url,
		'cookieTime' => $modSettings['cookieTime'],
	);

	$parameters = array(
		'openid.mode=checkid_setup',
		'openid.trust_root=' . urlencode($scripturl),
		'openid.identity=' .  urlencode(empty($response_data['delegate']) ? $openid_url : $response_data['delegate']),
		'openid.assoc_handle=' . urlencode($assoc['handle']),
		'openid.return_to=' . urlencode($scripturl . '?action=openidreturn&sa=' . $_REQUEST['action'] . '&t=' . $request_time . (!empty($save_fields) ? '&sf=' . base64_encode(serialize($save_fields)) : '')),
	);

	// If they are logging in but don't yet have an account or they are registering, lets request some additional information
	if (($_REQUEST['action'] == 'login2' && !smf_openid_member_exists($openid_url)) || ($_REQUEST['action'] == 'register' || $_REQUEST['action'] == 'register2'))
	{
		// Email is required.
		$parameters[] = 'openid.sreg.required=email';
		// The rest is just optional.
		$parameters[] = 'openid.sreg.optional=nickname,dob,gender';
	}

	$redir_url = $response_data['server'] . '?' . implode('&', $parameters);

	if ($return)
		return $redir_url;
	else
		redirectexit($redir_url);
}

function smf_openID_getAssociation($server, $handle = null, $no_delete = false)
{
	global $smcFunc;

	if (!$no_delete)
	{
		// Delete the already expired associations.
		$smcFunc['db_query']('openid_delete_assoc_old', '
			DELETE FROM {db_prefix}openid_assoc
			WHERE expires <= {int:current_time}',
			array(
				'current_time' => time(),
			)
		);
	}

	// Get the association that has the longest lifetime from now.
	$request = $smcFunc['db_query']('openid_select_assoc', '
		SELECT server_url, handle, secret, issued, expires, assoc_type
		FROM {db_prefix}openid_assoc
		WHERE server_url = {string:server_url}' . ($handle === null ? '' : '
			AND handle = {string:handle}') . '
		ORDER BY expires DESC',
		array(
			'server_url' => $server,
			'handle' => $handle,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
		return null;

	$return = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	return $return;
}

function smf_openID_makeAssociation($server)
{
	global $smcFunc, $modSettings;

	$parameters = array(
		'openid.mode=associate',
		'openid.session_type=',
	);

	// The data to post to the server.
	$post_data = implode('&', $parameters);
	$data = fetch_web_data($server, $post_data);

	// parse the data given
	preg_match_all('~^([^:]+):(.+)$~m', $data, $matches);
	$assoc_data = array();

	foreach ($matches[1] as $key => $match)
		$assoc_data[$match] = $matches[2][$key];

	if (!isset($assoc_data['assoc_type']) || empty($assoc_data['mac_key']))
		fatal_lang_error('openid_server_bad_response');

	$secret = $assoc_data['mac_key'];

	// Clean things up a bit
	$handle = isset($assoc_data['assoc_handle']) ? $assoc_data['assoc_handle'] : '';
	$issued = time();
	$expires = $issued + min((int)$assoc_data['expires_in'], 60);
	$assoc_type = isset($assoc_data['assoc_type']) ? $assoc_data['assoc_type'] : '';

	// Store the data
	$smcFunc['db_insert']('replace',
		'{db_prefix}openid_assoc',
		array('server_url' => 'string', 'handle' => 'string', 'secret' => 'string', 'issued' => 'int', 'expires' => 'int', 'assoc_type' => 'string'),
		array($server, $handle, $secret, $issued, $expires, $assoc_type),
		array('server_url', 'handle')
	);

	return array(
		'server' => $server,
		'handle' => $assoc_data['assoc_handle'],
		'secret' => $secret,
		'issued' => $issued,
		'expires' => $expires,
		'assoc_type' => $assoc_data['assoc_type'],
	);
}

function smf_openID_removeAssociation($handle)
{
	global $smcFunc;

	$smcFunc['db_query']('openid_remove_association', '
		DELETE FROM {db_prefix}openid_assoc
		WHERE handle = {string:handle}',
		array(
			'handle' => $handle,
		)
	);
}

function smf_openID_return()
{
	global $smcFunc, $user_info, $user_profile, $sourcedir, $modSettings, $context, $sc, $user_settings;

	if (!isset($_GET['openid_mode']))
		fatal_lang_error('openid_return_no_mode');

	// !!! Check for error status!
	if ($_GET['openid_mode'] != 'id_res')
		fatal_lang_error('openid_not_resolved');

	// SMF has this annoying habit of removing the + from the base64 encoding.  So lets put them back.
	foreach (array('openid_assoc_handle', 'openid_invalidate_handle', 'openid_sig', 'sf') AS $key)
		if (isset($_GET[$key]))
			$_GET[$key] = str_replace(' ', '+', $_GET[$key]);

	// Did they tell us to remove any associations?
	if (!empty($_GET['openid_invalidate_handle']))
		smf_openid_removeAssociation($_GET['openid_invalidate_handle']);

	$server_info = smf_openid_getServerInfo($_GET['openid_identity']);

	// Get the association data.
	$assoc = smf_openID_getAssociation($server_info['server'], $_GET['openid_assoc_handle'], true);
	if ($assoc === null)
		fatal_lang_error('openid_no_assoc');

	$secret = base64_decode($assoc['secret']);

	$signed = explode(',', $_GET['openid_signed']);
	$verify_str = '';
	foreach ($signed AS $sign)
	{
		$verify_str .= $sign . ':' . strtr($_GET['openid_' . str_replace('.', '_', $sign)], array('&amp;' => '&')) . "\n";
	}

	$verify_str = base64_encode(sha1_hmac($verify_str, $secret));

	if ($verify_str != $_GET['openid_sig'])
	{
		fatal_lang_error('openid_sig_invalid', 'critical');
	}

	if (!isset($_SESSION['openid']['saved_data'][$_GET['t']]))
		fatal_lang_error('openid_load_data');

	$openid_uri = $_SESSION['openid']['saved_data'][$_GET['t']]['openid_uri'];
	$modSettings['cookieTime'] = $_SESSION['openid']['saved_data'][$_GET['t']]['cookieTime'];

	if (empty($openid_uri))
		fatal_lang_error('openid_load_data');

	// Any save fields to restore?
	$context['openid_save_fields'] = isset($_GET['sf']) ? unserialize(base64_decode($_GET['sf'])) : array();

	// Is there a user with this OpenID_uri?
	$result = $smcFunc['db_query']('', '
		SELECT passwd, id_member, id_group, lngfile, is_activated, email_address, additional_groups, member_name, password_salt
		FROM {db_prefix}members
		WHERE openid_uri = {string:openid_uri}',
		array(
			'openid_uri' => $openid_uri,
		)
	);

	if ($smcFunc['db_num_rows']($result) == 0)
	{
		// Need to this user over to the registration page.
		$_SESSION['openid'] = array(
			'verified' => true,
			'openid_uri' => $openid_uri,
			'nickname' => $_GET['openid_sreg_nickname'],
			'email' => $_GET['openid_sreg_email'],
		);

		if (isset($_GET['openid_sreg_dob']))
			$_SESSION['openid']['dob'] = $_GET['openid_sreg_dob'];

		if (isset($_GET['openid_sreg_gender']))
			$_SESSION['openid']['gender'] = $_GET['openid_sreg_gender'];

		// Were we just verifying the registration state?
		if (isset($_GET['sa']) && $_GET['sa'] == 'register2')
		{
			require_once($sourcedir . '/Register.php');
			return Register2(true);
		}
		else
			redirectexit('action=register');
	}
	else
	{
		$user_settings = $smcFunc['db_fetch_assoc']($result);
		$smcFunc['db_free_result']($result);

		$user_settings['passwd'] = sha1(strtolower($user_settings['member_name']) . $secret);
		$user_settings['password_salt'] = substr(md5(rand()), 0, 4);

		updateMemberData($user_settings['id_member'], array('passwd' => $user_settings['passwd'], 'password_salt' => $user_settings['password_salt']));

		// Cleanup on Aisle 5.
		$_SESSION['openid'] = array(
			'verified' => true,
			'openid_uri' => $_SESSION['openid']['openid_uri'],
		);

		require_once($sourcedir . '/LogInOut.php');

		if (!checkActivation())
			return;

		DoLogin();
	}
}

function smf_openID_canonize($uri)
{
	// !!! Add in discovery.

	if (strpos($uri, 'http://') !== 0)
		$uri = 'http://' . $uri;

	if (strpos(substr($uri, strpos($uri, '://') + 3), '/') === false)
		$uri .= '/';

	return $uri;
}

function smf_openid_member_exists($url)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('openid_member_exists', '
		SELECT mem.id_member, mem.member_name
		FROM {db_prefix}members AS mem
		WHERE mem.openid_uri = {string:openid_uri}',
		array(
			'openid_uri' => $url,
		)
	);
	$member = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	return $member;
}

/*
function smf_openID_get_keys($p)
{
	global $modSettings;

	// First step, can we even support using keys?
	if (!function_exists('bcpow'))
		return null;

	// Ok lets take the easy way out, are their any keys already defined for us?
	if (!empty($modSettings['dh_keys']))
	{
		// Sweeeet!
		list($public, $private) = explode("\n", $modSettings['dh_keys']);
		return array(
			'public' => base64_decode($public),
			'private' => base64_decode($private),
		);
	}

	// Dang it, now I have to do math.  And it's not just ordinary math, its the evil big interger math.  This will take a few seconds.
	$private = smf_openid_generate_private_key($p);
	$public = bcpowmod(2, $private, $p);

	// Now that we did all that work, lets save it so we don't have to keep doing it.
	$keys = array('dh_keys' => base64_encode($public) . "\n" . base64_encode($private));
	updateSettings($keys);

	return array(
		'public' => $public,
		'private' => $private,
	);
}

function smf_openid_generate_private_key($p_value)
{
	static $cache = array();

	$byte_string = long_to_binary($p_value);

	if (isset($cache[$byte_string]))
		list ($dup, $num_bytes) = $cache[$byte_string];
	else
	{
		$num_bytes = strlen($byte_string) - ($byte_string[0] == "\x00" ? 1 : 0);

		$max_rand = bcpow(256, $num_bytes);

		$dup = bcmod($max_rand, $num_bytes);

		$cache[$byte_string] = array($dup, $num_bytes);
	}

	do
	{
		$str = '';
		for($i = 0; $i < $num_bytes; $i += 4)
			$str .= pack('L', mt_rand());

		$bytes = "\x00" . $str;

		$num = binary_to_long($bytes);
	} while (bccomp($num, $dup) < 0);

	return bcadd(bcmod($num, $p_value), 1);
}*/

function smf_openID_getServerInfo($openid_url)
{
	global $sourcedir;

	require_once($sourcedir . '/Subs-Package.php');

	// Get the html and parse it for the openid variable which will tell us where to go.
	$webdata = fetch_web_data($openid_url);

	$response_data = array();

	if (preg_match_all('~<link rel="openid.(server|delegate)" +href="([^"]+)" ?/?>~', $webdata, $matches) == 0)
		fatal_lang_error('openid_server_bad_response');

	foreach ($matches[1] as $key => $match)
		$response_data[$match] = $matches[2][$key];

	if (empty($response_data['server']))
		fatal_lang_error('openid_server_bad_response');

	return $response_data;
}

function sha1_hmac($data, $key)
{

	if (strlen($key) > 64)
		$key = sha1_raw($key);

	// Pad the key if need be.
	$key = str_pad($key, 64, chr(0x00));
	$ipad = str_repeat(chr(0x36), 64);
	$opad = str_repeat(chr(0x5c), 64);
	$hash1 = sha1_raw(($key ^ $ipad) . $data);
	$hmac = sha1_raw(($key ^ $opad) . $hash1);
	return $hmac;
}

function sha1_raw($text)
{
	if (version_compare(PHP_VERSION, 'PHP 5.0.0') >= 0)
		return sha1($text, true);

	$hex = sha1($text);
	$raw = '';
	for ($i = 0; $i < 40; $i += 2)
	{
		$hexcode = substr($hex, $i, 2);
		$charcode = (int)base_convert($hexcode, 16, 10);
		$raw .= chr($charcode);
	}

	return $raw;
}

function binary_to_long($str)
{
	$bytes = array_merge(unpack('C*', $str));

	$n = 0;

	foreach ($bytes as $byte)
	{
		$n = bcmul($n, 256);
		$n = bcadd($n, $byte);
	}

	return $n;
}

function long_to_binary($value)
{
	$cmp = bccomp($value, 0);
	if ($cmp < 0)
		fatal_error('Only non-negative integers allowed.');

	if ($cmp == 0)
		return "\x00";

	$bytes = array();

	while (bccomp($value, 0) > 0)
	{
		array_unshift($bytes, bcmod($value, 256));
		$value = bcdiv($value, 256);
	}

	if ($bytes && ($bytes[0] > 127))
		array_unshift($bytes, 0);

	$return = '';
	foreach ($bytes as $byte)
		$return .= pack('C', $byte);

	return $return;
}

?>