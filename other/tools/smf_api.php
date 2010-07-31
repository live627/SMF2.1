<?php
/**********************************************************************************
* smf_api.php                                                                     *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 RC3                                         *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2010 by:     Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This file, and ONLY this file is released under the terms of the BSD License.   *
*                                                                                 *
* Redistribution and use in source and binary forms, with or without              *
* modification, are permitted provided that the following conditions are met:     *
*                                                                                 *
* Redistributions of source code must retain the above copyright notice, this     *
* list of conditions and the following disclaimer.                                *
* Redistributions in binary form must reproduce the above copyright notice, this  *
* list of conditions and the following disclaimer in the documentation and/or     *
* other materials provided with the distribution.                                 *
* Neither the name of Simple Machines LLC nor the names of its contributors may   *
* be used to endorse or promote products derived from this software without       *
* specific prior written permission.                                              *
*                                                                                 *
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"     *
* AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE       *
* IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE      *
* ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE        *
* LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR             *
* CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE *
* GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)     *
* HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT      *
* LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT   *
* OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. *
**********************************************************************************/

// !!! Groups, Member data?  Pull specific fields?

/*	This file includes functions that may help integration with other scripts
	and programs, such as portals.  It is independent of SMF, and meant to run
	without disturbing your script.  It defines several functions, all of
	which start with the smf_ prefix.  These are:

	bool smf_setLoginCookie(int length, string username or int id_member,
			string password, bool encrypted = true)
		- sets a cookie and session variables to log the user in, for length
		  seconds from now.
		- will find the id_member for you if you specify a username.
		- please ensure that the username has slashes added to it.
		- does no authentication, but if the cookie is wrong it won't work.
		- expects the password to be pre-encrypted if encrypted is true.
		- returns false on failure (unlikely!), true on success.
		- you should call smf_authenticateUser after calling this.

	bool smf_authenticateUser()
		- authenticates the user with the current cookie ro session data.
		- loads data into the $smf_user_info variable.
		- returns false if it was unable to authenticate, true otherwise.
		- it would be good to call this at the beginning.

	int smf_registerMember(string username, string email, string password,
			array extra_fields = none, array theme_options = none)
		// !!!

	void smf_logOnline(string action = $_GET['action'])
		- logs the currently authenticated user or guest as online.
		- may not always log, because it delays logging if at all possible.
		- uses the action specified as the action in the log, a good example
		  would be "coppermine" or similar.
		- you can add entries to the Modifications language files so as to
		  make this action show up properly on Who's Online - see Who.php for
		  more details.

	bool smf_is_online(string username or int id_member)
		- checks if the specified member is currently online.
		- will find the appropriate id_member if username is given instead.
		- returns true if they are online, false otherwise.

	string smf_logError(string error_message, string file, int line)
		- logs an error, assuming error logging is enabled.
		- filename and line should be __FILE__ and __LINE__, respectively.
		- returns the error message. (ie. die(log_error($msg));)

	string smf_formatTime(int time)
		- formats the timestamp time into a readable string.
		- adds the appropriate offsets to make the time equivalent to the
		  user's time.
		- return the readable representation as a string.

	resource smf_query(string query, string file, int line)
		- executes a query using SMF's database connection.
		- keeps a count of queries in the $smf_settings['db_count'] setting.
		- if an error occurs while executing the query, additionally logs an
		  error in SMF's error log with the proper information.
		- does not do any crashed table prevention.

	bool smf_allowedTo(string permission)
		- checks to see if the user is allowed to do the specified permission
		  or any of an array of permissions.
		- always returns true for administrators.
		- does not account for banning restrictions.
		- caches all available permissions upon first call.
		- does not provide access to board permissions.
		- returns null if no connection to the database has been made, and
		  true or false depending on the user's permissions.

	void smf_loadThemeData(int id_theme = default)
		- if no id_theme is passed, the user's default theme will be used.
		- allows 'theme' in the URL to specify the theme, only if id_theme is
		  not passed.
		- loads theme settings into $smf_settings['theme'].
		- loads theme options into $smf_user_info['theme'].
		- does nothing if no connection has been made to the database.
		- should be called after loading user information.

	void smf_loadSession()
		- loads the session, whether from the database or from files.
		- makes the session_id available in $smf_user_info.
		- will override session handling if the setting is enabled in SMF's
		  configuration.

	bool smf_sessionOpen(string save_path, string session_name)
	bool smf_sessionClose()
	bool smf_sessionRead(string session_id)
	bool smf_sessionWrite(string session_id, string data)
	bool smf_sessionDestroy(string session_id)
	bool smf_sessionGC(int max_lifetime)
		- called only by internal PHP session handling functions.

	---------------------------------------------------------------------------
	It also defines the following important variables:

	array $smf_settings
		- includes all the major settings from Settings.php, as well as all
		  those from the settings table.
		- if smf_loadThemeData has been called, the theme settings will be
		  available from the theme index.

	array $smf_user_info
		- only contains useful information after authentication.
		- major indexes are is_guest and is_admin, which easily and quickly
		  tell you about the user's status.
		- also includes id, name, email, messages, unread_messages, and many
		  other values from the members table.
		- you can also use the groups index to find what groups the user is in.
		- if smf_loadSession has been called, the session code is stored under
		  session_id.
		- if smf_loadThemeData has been called, the theme options will be
		  available from the theme index.
*/

// This is just because SMF in general hates magic quotes at runtime.
if (function_exists('set_magic_quotes_runtime'))
	@set_magic_quotes_runtime(0);

// Hopefully the forum is in the same place as this script.
require_once(dirname(__FILE__) . '/Settings.php');

global $smcFunc, $smf_settings, $smf_user_info, $smf_connection;

// If $maintenance is set to 2, don't connect to the database at all.
if ($maintenance != 2)
{
	define('SMF', 1);

	if (empty($smcFunc))
		$smcFunc = array();

	// Default the database type to MySQL.
	if (empty($db_type) || !file_exists($sourcedir . '/Subs-Db-' . $db_type . '.php'))
		$db_type = 'mysql';

	require_once($sourcedir . '/Errors.php');
	require_once($sourcedir . '/Subs.php');
	require_once($sourcedir . '/Load.php');
	require_once($sourcedir . '/Security.php');
	require_once($sourcedir . '/Subs-Auth.php');
	require_once($sourcedir . '/Subs-Db-' . $db_type . '.php');
	$db_connection = smf_db_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true));

	$request = $smcFunc['db_query']('', '
		SELECT variable, value
		FROM {db_prefix}settings', array());
	$smf_settings = array();
	while ($row = @$smcFunc['db_fech_row']($request))
		$smf_settings[$row[0]] = $row[1];
	$smcFunc['db_free_result']($request);
}

// Load stuff from the Settings.php file into $smf_settings.
$smf_settings['cookiename'] = $cookiename;
$smf_settings['language'] = $language;
$smf_settings['forum_name'] = $mbname;
$smf_settings['forum_url'] = $boardurl;
$smf_settings['webmaster_email'] = $webmaster_email;
$smf_settings['db_prefix'] = $db_prefix;

$smf_user_info = array();

// Actually set the login cookie...
function smf_setLoginCookie($cookie_length, $id, $password = '', $encrypted = true)
{
	// This should come from Settings.php, hopefully.
	global $smcFunc, $smf_connection, $smf_settings;

	// The $id is not numeric; it's probably a username.
	if (!is_integer($id))
	{
		if (!$smf_connection)
			return false;

		// Save for later use.
		$username = $id;

		$result = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {raw:smf_db_prefix}members
			WHERE member_name = {string:username}
			LIMIT 1',
			array(
				'smf_db_prefix' => $smf_settings['db_prefix'],
				'username' => $username,
		));
		list ($id) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		// It wasn't found, after all?
		if (empty($id))
		{
			$id = (int) $username;
			unset($username);
		}
	}

	// Oh well, I guess it just was not to be...
	if (empty($id))
		return false;

	// The password isn't encrypted, do so.
	if (!$encrypted)
	{
		if (!$smf_connection)
			return false;

		$result = $smcFunc['db_query']('', '
			SELECT member_name, password_salt
			FROM {raw:smf_db_prefix}members
			WHERE id_member = {int:id_member}
			LIMIT 1',
			array(
				'smf_db_prefix' => $smf_settings['db_prefix'],
				'id_member' => (int) $id,
		));
		list ($username, $salt) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		if (empty($username))
			return false;

		$password = sha1(sha1(strtolower($username) . $password) . $salt);
	}

	function smf_cookie_url($local, $global)
	{
		global $smcFunc, $smf_settings;
		// Use PHP to parse the URL, hopefully it does its job.
		$parsed_url = parse_url($smf_settings['forum_url']);

		// Set the cookie to the forum's path only?
		if (empty($parsed_url['path']) || !$local)
			$parsed_url['path'] = '';

		// This is probably very likely for apis and such, no?
		if ($global)
		{
			// Try to figure out where to set the cookie; this can be confused, though.
			if (preg_match('~(?:[^\.]+\.)?(.+)\z~i', $parsed_url['host'], $parts) == 1)
				$parsed_url['host'] = '.' . $parts[1];
		}
		// If both options are off, just use no host and /.
		elseif (!$local)
			$parsed_url['host'] = '';
	}

	// The cookie may already exist, and have been set with different options.
	$cookie_state = (empty($smf_settings['localCookies']) ? 0 : 1) | (empty($smf_settings['globalCookies']) ? 0 : 2);
	if (isset($_COOKIE[$smf_settings['cookiename']]))
	{
		$array = @unserialize($_COOKIE[$smf_settings['cookiename']]);

		if (isset($array[3]) && $array[3] != $cookie_state)
		{
			$cookie_url = smf_cookie_url($array[3] & 1 > 0, $array[3] & 2 > 0);
			setcookie($smf_settings['cookiename'], serialize(array(0, '', 0)), time() - 3600, $parsed_url['path'] . '/', $parsed_url['host'], 0);
		}
	}

	// Get the data and path to set it on.
	$data = serialize(empty($id) ? array(0, '', 0) : array($id, $password, time() + $cookie_length));
	$parsed_url = smf_cookie_url(!empty($smf_settings['localCookies']), !empty($smf_settings['globalCookies']));

	// Set the cookie, $_COOKIE, and session variable.
	setcookie($smf_settings['cookiename'], $data, time() + $cookie_length, $parsed_url['path'] . '/', $parsed_url['host'], 0);
	$_COOKIE[$smf_settings['cookiename']] = $data;
	$_SESSION['login_' . $smf_settings['cookiename']] = $data;

	return true;
}

function smf_authenticateUser()
{
	global $smcFunc, $smf_connection, $smf_settings;

	// No connection, no authentication!
	if (!$smf_connection)
		return false;

	// Check first the cookie, then the session.
	if (isset($_COOKIE[$smf_settings['cookiename']]))
	{
		$_COOKIE[$smf_settings['cookiename']] = stripslashes($_COOKIE[$smf_settings['cookiename']]);

		// Fix a security hole in PHP 4.3.9 and below...
		if (preg_match('~^a:[34]:\{i:0;(i:\d{1,6}|s:[1-8]:"\d{1,8}");i:1;s:(0|40):"([a-fA-F0-9]{40})?";i:2;[id]:\d{1,14};(i:3;i:\d;)?\}$~', $_COOKIE[$smf_settings['cookiename']]) == 1)
		{
			list ($id_member, $password) = @unserialize($_COOKIE[$smf_settings['cookiename']]);
			$id_member = !empty($id_member) ? (int) $id_member : 0;
		}
		else
			$id_member = 0;
	}
	elseif (isset($_SESSION['login_' . $smf_settings['cookiename']]))
	{
		list ($id_member, $password, $login_span) = @unserialize(stripslashes($_SESSION['login_' . $smf_settings['cookiename']]));
		$id_member = !empty($id_member) && $login_span > time() ? (int) $id_member : 0;
	}
	else
		$id_member = 0;

	// Don't even bother if they have no authentication data.
	if (!empty($id_member))
	{
		$request = $smcFunc['db_query']('', '
			SELECT *
			FROM {raw:smf_db_prefix}
			WHERE id_member = {int:id_member}
			LIMIT 1',
			array(
				'smf_db_prefix' => $smf_settings['db_prefix'],
				'id_member' => $id_member,
		));
		// Did we find 'im?  If not, junk it.
		if (mysql_num_rows($request) != 0)
		{
			// The base settings array.
			$smf_user_info += $smcFunc['db_fetch_assoc']($request);

			if (strlen($password) == 40)
				$check = sha1($smf_user_info['passwd'] . $smf_user_info['password_salt']) == $password;
			else
				$check = false;

			// Wrong password or not activated - either way, you're going nowhere.
			$id_member = $check && ($smf_user_info['is_activated'] == 1 || $smf_user_info['is_activated'] == 11) ? $smf_user_info['id_member'] : 0;
		}
		else
			$id_member = 0;
		$smcFunc['db_free_result']($request);
	}

	if (empty($id_member))
		$smf_user_info = array('groups' => array(-1));
	else
	{
		if (empty($smf_user_info['additional_groups']))
			$smf_user_info['groups'] = array($smf_user_info['id_group'], $smf_user_info['id_post_group']);
		else
			$smf_user_info['groups'] = array_merge(
				array($smf_user_info['id_group'], $smf_user_info['id_post_group']),
				explode(',', $smf_user_info['additional_groups'])
			);
	}

	// A few things to make life easier...
	$smf_user_info['id'] = &$smf_user_info['id_member'];
	$smf_user_info['username'] = &$smf_user_info['member_name'];
	$smf_user_info['name'] = &$smf_user_info['real_name'];
	$smf_user_info['email'] = &$smf_user_info['email_address'];
	$smf_user_info['messages'] = &$smf_user_info['instant_messages'];
	$smf_user_info['unread_messages'] = &$smf_user_info['unread_messages'];
	$smf_user_info['language'] = empty($smf_user_info['lngfile']) || empty($smf_settings['userLanguage']) ? $smf_settings['language'] : $smf_user_info['lngfile'];
	$smf_user_info['is_guest'] = $id_member == 0;
	$smf_user_info['is_admin'] = in_array(1, $smf_user_info['groups']);

	// This might be set to "forum default"...
	if (empty($smf_user_info['time_format']))
		$smf_user_info['time_format'] = $smf_settings['time_format'];

	return !$smf_user_info['is_guest'];
}

function smf_registerMember($username, $email, $password, $extra_fields = array(), $theme_options = array())
{
	global $smcFunc, $smf_settings, $smf_connection;

	// No connection means no registrations...
	if (!$smf_connection)
		return false;

	// Can't use that username.
	if (preg_match('~[<>&"\'=\\\]~', $username) === 1 || $username === '_' || $username === '|' || strpos($username, '[code') !== false || strpos($username, '[/code') !== false || strlen($username) > 25)
		return false;

	// Make sure the email is valid too.
	if (empty($email) || preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $email) === 0 || strlen($email) > 255)
		return false;

	// !!! Validate username isn't already used?  Validate reserved, etc.?

	$register_vars = array(
		'member_name' => "'$username'",
		'real_name' => "'$username'",
		'email_address' => "'" . addslashes($email) . "'",
		'passwd' => "'" . sha1(strtolower($username) . $password) . "'",
		'password_salt' => "'" . substr(md5(mt_rand()), 0, 4) . "'",
		'posts' => '0',
		'date_registered' => (string) time(),
		'is_activated' => '1',
		'personal_text' => "'" . addslashes($smf_settings['default_personal_text']) . "'",
		'pm_email_notify' => '1',
		'id_theme' => '0',
		'id_post_group' => '4',
		'lngfile' => "''",
		'buddy_list' => "''",
		'pm_ignore_list' => "''",
		'message_labels' => "''",
		'website_title' => "''",
		'website_url' => "''",
		'location' => "''",
		'icq' => "''",
		'aim' => "''",
		'yim' => "''",
		'msn' => "''",
		'time_format' => "''",
		'signature' => "''",
		'avatar' => "''",
		'usertitle' => "''",
		'member_ip' => "''",
		'member_ip2' => "''",
		'secret_question' => "''",
		'secret_answer' => "''",
		'validation_code' => "''",
		'additional_groups' => "''",
		'smiley_set' => "''",
		'password_salt' => "''",
	);

	$register_vars = array_values($extra_fields + $register_vars);
	// We could, build a custom function to figure out if it is a string or int, but assuming string is quicker.
	$register_keys = array_combine(array_keys($extra_fields + $register_vars), array_fill(0, count($register_vars), 'string'));

	$smcFunc['db_insert']('insert',
		$smf_settings['db_prefix'] . 'members',
		$register_keys,
		$register_vars,
		array('id_member')
	);
	$id_member = $smcFunc['db_insert_id']($smf_connection);

	$smcFunc['db_query']('', '
		UPDATE {raw:smf_db_prefix}
		SET value = value + 1
		WHERE variable = {string:totalMembers}',
			array(
				'smf_db_prefix' => $smf_settings['db_prefix'],
				'totalMembers' => 'totalMembers',
		));
	$smcFunc['db_insert']('replace',
		$smf_settings['db_prefix'] . 'settings',
		array('variable' => 'string', 'value' => 'string'),
		array(
			array('latestMember', $id_member),
			array('latestRealName', $username),
		),
		array('variable', 'value')
	);
	$smcFunc['db_query']('', '
		UPDATE {raw:smf_db_prefix}log_activity
		SET registers = registers + 1
		WHERE date = {string:cur_date}',
			array(
				'smf_db_prefix' => $smf_settings['db_prefix'],
				'cur_date' => strftime('%Y-%m-%d'),
		));
	if ($smcFunc['db_affected_rows']($smf_connection) == 0)
		$smcFunc['db_insert']('insert',
			$smf_settings['db_prefix'] . 'log_activity',
			array('date' => 'string', 'registers' => 'int'),
			array(strftime('%Y-%m-%d'), 1),
			array('date', 'registers')
		);

	// Theme variables too?
	if (!empty($theme_options))
	{
		$inserts = array();
		foreach ($theme_options as $var => $val)
			$inserts[] = array($memberID, substr($var, 0, 255), substr($val, 0, 65534));

		$smcFunc['db_insert']('insert',
			$smf_settings['db_prefix'] . 'themes',
			array('id_member' => 'int', 'variable' => 'string-255', 'value' => 'string-65534'),
			$inserts,
			array('id_member', 'id_theme')
		);
	}

	return $id_member;
}

// Log the current user online.
function smf_logOnline($action = null)
{
	global $smcFunc, $smf_settings, $smf_connection, $smf_user_info;

	if (!$smf_connection)
		return false;

	// Determine number of seconds required.
	$lastActive = $smf_settings['lastActive'] * 60;

	// Don't mark them as online more than every so often.
	if (empty($_SESSION['log_time']) || $_SESSION['log_time'] < (time() - 8))
		$_SESSION['log_time'] = time();
	else
		return;

	$serialized = $_GET;
	$serialized['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
	unset($serialized['sesc']);
	if ($action !== null)
		$serialized['action'] = $action;

	$serialized = addslashes(serialize($serialized));

	// Guests use 0, members use id_member.
	if ($smf_user_info['is_guest'])
	{
		$smcFunc['db_query']('', '
			DELETE FROM {raw:smf_db_prefix}log_online
			WHERE log_time < {int:last_active} OR session = {string:cur_ip}',
			array(
				'smf_db_prefix' => $smf_settings['db_prefix'],
				'last_active' => time() - $lastActive,
				'cur_ip' => 'ip' . $_SERVER['REMOTE_ADDR'],
		));
		$smcFunc['db_insert']('insert',
			$smf_settings['db_prefix'] . 'log_online',
			array('session' => 'string', 'id_member' => 'int', 'ip' => 'raw', 'url' => 'string', 'url' => 'string', 'log_time' => 'int'),
			array('ip' . $_SERVER['REMOTE_ADDR'], 0, 'IFNULL(INET_ATON("' . $_SERVER['REMOTE_ADDR'] . '"), 0)', $serialized, time()),
			array('session', 'id_member')
		);
	}
	else
	{
		$smcFunc['db_query']('', '
			DELETE FROM {raw:smf_db_prefix}log_online
			WHERE log_time < {int:last_active} OR id_member = {int:cur_id} OR session = {string:cur_session}',
			array(
				'smf_db_prefix' => $smf_settings['db_prefix'],
				'last_active' => time() - $lastActive,
				'cur_id' => $smf_user_info['id'],
				'cur_session' => @session_id(),
		));
		$smcFunc['db_insert']('',
			$smf_settings['db_prefix'] . 'log_online',
			array('session' => 'string', 'id_member' => 'int', 'ip' => 'raw', 'url' => 'string', 'log_time' => 'int'),
			array(@session_id(), $smf_user_info['id'], 'IFNULL(INET_ATON("' . $_SERVER['REMOTE_ADDR'] . '"), 0)', $serialized, time()),
			array('session', 'id_member')
		);
	}
}

function smf_is_online($user)
{
	global $smcFunc, $smf_settings, $smf_connection;

	if (!$smf_connection)
		return false;

	$result = $smcFunc['db_query']('', '
		SELECT lo.id_member
		FROM {raw:smf_db_prefix}log_online AS lo' . (!is_integer($user) ? '
			LEFT JOIN {raw:smf_db_prefix}members AS mem ON (mem.id_member = lo.id_member)' : '') . '
		WHERE lo.id_member = {int:user}'. (!is_integer($user) ? ' OR mem.member_name = {string:user}' : '') . '
		LIMIT 1',
			array(
				'smf_db_prefix' => $smf_settings['db_prefix'],
				'user' => (int) $user,
		));
	$return = mysql_num_rows($result) != 0;
	$smcFunc['db_free_result']($result);

	return $return;
}

// Log an error, if the option is on.
function smf_logError($error_message, $file = null, $line = null)
{
	global $smcFunc, $smf_settings, $smf_connection;

	// Check if error logging is actually on and we're connected...
	if (empty($smf_settings['enableErrorLogging']) || !$smf_connection)
		return $error_message;

	// Basically, htmlspecialchars it minus &. (for entities!)
	$error_message = strtr($error_message, array('<' => '&lt;', '>' => '&gt;', '"' => '&quot;'));
	$error_message = strtr($error_message, array('&lt;br /&gt;' => '<br />', '&lt;b&gt;' => '<strong>', '&lt;/b&gt;' => '</strong>', "\n" => '<br />'));

	// Add a file and line to the error message?
	if ($file != null)
		$error_message .= '<br />' . $file;
	if ($line != null)
		$error_message .= '<br />' . $line;

	// Just in case there's no id_member or IP set yet.
	if (empty($smf_user_info['id']))
		$smf_user_info['id'] = 0;

	// Insert the error into the database.
	$smcFunc['db_insert']('insert',
		$smf_settings['db_prefix'] . 'log_errors',
		array(
			'id_member' => 'int', 'log_time' => 'int', 'ip' => 'string', 'url' => 'string', 'message' => 'string', 'session' => 'string'
		),
		array($smf_user_info['id'], time(), $_SERVER['REMOTE_ADDR'], empty($_SERVER['QUERY_STRING']) ? '' : addslashes(htmlspecialchars('?' . $_SERVER['QUERY_STRING']))), addslashes($error_message), @session_id(),
		array(
			'id_error'
	));

	// Return the message to make things simpler.
	return $error_message;
}

// Format a time to make it look purdy.
function smf_formatTime($log_time)
{
	global $smf_user_info, $smf_settings;

	// Offset the time - but we can't have a negative date!
	$time = max($log_time + (@$smf_user_info['time_offset'] + $smf_settings['time_offset']) * 3600, 0);

	// Format some in caps, and then any other characters..
	return strftime(strtr(!empty($smf_user_info['time_format']) ? $smf_user_info['time_format'] : $smf_settings['time_format'], array('%a' => ucwords(strftime('%a', $time)), '%A' => ucwords(strftime('%A', $time)), '%b' => ucwords(strftime('%b', $time)), '%B' => ucwords(strftime('%B', $time)))), $time);
}

// Mother, may I?
function smf_allowedTo($permission)
{
	global $smcFunc, $smf_settings, $smf_user_info, $smf_connection;

	if (!$smf_connection)
		return null;

	// Administrators can do all, and everyone can do nothing.
	if ($smf_user_info['is_admin'] || empty($permission))
		return true;

	if (!isset($smf_user_info['permissions']))
	{
		$result = $smcFunc['db_query']('', '
			SELECT permission, add_deny
			FROM {raw:smf_db_prefix}permissions
			WHERE id_group IN ({array_int:user_groups})',
			array(
				'smf_db_prefix' => $smf_settings['db_prefix'],
				'user_groups' => $smf_user_info['groups'],
		));
		$removals = array();
		$smf_user_info['permissions'] = array();
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			if (empty($row['add_deny']))
				$removals[] = $row['permission'];
			else
				$smf_user_info['permissions'][] = $row['permission'];
		}
		$smcFunc['db_free_result']($result);

		// And now we get rid of the removals ;).
		if (!empty($smf_settings['permission_enable_deny']))
			$smf_user_info['permissions'] = array_diff($smf_user_info['permissions'], $removals);
	}

	// So.... can you?
	if (!is_array($permission) && in_array($permission, $smf_user_info['permissions']))
		return true;
	elseif (is_array($permission) && count(array_intersect($permission, $smf_user_info['permissions'])) != 0)
		return true;
	else
		return false;
}

function smf_loadThemeData($id_theme = 0)
{
	global $smcFunc, $smf_settings, $smf_user_info, $smf_connection;

	if (!$smf_connection)
		return null;

	// The theme was specified by parameter.
	if (!empty($id_theme))
		$theme = (int) $id_theme;
	// The theme was specified by REQUEST.
	elseif (!empty($_REQUEST['theme']))
	{
		$theme = (int) $_REQUEST['theme'];
		$_SESSION['id_theme'] = $theme;
	}
	// The theme was specified by REQUEST... previously.
	elseif (!empty($_SESSION['id_theme']))
		$theme = (int) $_SESSION['id_theme'];
	// The theme is just the user's choice. (might use ?board=1;theme=0 to force board theme.)
	elseif (!empty($smf_user_info['theme']) && !isset($_REQUEST['theme']))
		$theme = $smf_user_info['theme'];
	// The theme is the forum's default.
	else
		$theme = $smf_settings['theme_guests'];

	// Verify the id_theme... no foul play.
	if (!empty($smf_settings['knownThemes']) && !empty($smf_settings['theme_allow']))
	{
		$themes = explode(',', $smf_settings['knownThemes']);
		if (!in_array($theme, $themes))
			$theme = $smf_settings['theme_guests'];
		else
			$theme = (int) $theme;
	}
	else
		$theme = (int) $theme;

	$member = empty($smf_user_info['id']) ? -1 : $smf_user_info['id'];

	// Load variables from the current or default theme, global $smcFunc, or this user's.
	$result = $smcFunc['db_query']('', '
		SELECT variable, value, id_member, id_theme
		FROM {raw:smf_db_prefix}themes
		WHERE id_member IN (-1, 0, {int:current_member})
			AND id_theme' . ($theme == 1 ? ' = 1' : ' IN ({int:current_theme}, 1)'),
			array(
				'smf_db_prefix' => $smf_settings['db_prefix'],
				'current_member' => $member,
				'current_theme' => $theme,
		));
	// Pick between $smf_settings['theme'] and $smf_user_info['theme'] depending on whose data it is.
	$themeData = array(0 => array(), $member => array());
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		// If this is the themedir of the default theme, store it.
		if (in_array($row['variable'], array('theme_dir', 'theme_url', 'images_url')) && $row['id_theme'] == '1' && empty($row['id_member']))
			$themeData[0]['default_' . $row['variable']] = $row['value'];

		// If this isn't set yet, is a theme option, or is not the default theme..
		if (!isset($themeData[$row['id_member']][$row['variable']]) || $row['id_theme'] != '1')
			$themeData[$row['id_member']][$row['variable']] = substr($row['variable'], 0, 5) == 'show_' ? $row['value'] == '1' : $row['value'];
	}
	$smcFunc['db_free_result']($result);

	$smf_settings['theme'] = $themeData[0];
	$smf_user_info['theme'] = $themeData[$member];

	if (!empty($themeData[-1]))
		foreach ($themeData[-1] as $k => $v)
		{
			if (!isset($smf_user_info['theme'][$k]))
				$smf_user_info['theme'][$k] = $v;
		}

	$smf_settings['theme']['theme_id'] = $theme;

	$smf_settings['theme']['actual_theme_url'] = $smf_settings['theme']['theme_url'];
	$smf_settings['theme']['actual_images_url'] = $smf_settings['theme']['images_url'];
	$smf_settings['theme']['actual_theme_dir'] = $smf_settings['theme']['theme_dir'];
}

// Attempt to start the session, unless it already has been.
function smf_loadSession()
{
	global $HTTP_SESSION_VARS, $smf_connection, $smf_settings, $smf_user_info;

	// Attempt to change a few PHP settings.
	@ini_set('session.use_cookies', true);
	@ini_set('session.use_only_cookies', false);
	@ini_set('arg_separator.output', '&amp;');

	// If it's already been started... probably best to skip this.
	if ((@ini_get('session.auto_start') == 1 && !empty($smf_settings['databaseSession_enable'])) || session_id() == '')
	{
		// Attempt to end the already-started session.
		if (@ini_get('session.auto_start') == 1)
			@session_write_close();

		// This is here to stop people from using bad junky PHPSESSIDs.
		if (isset($_REQUEST[session_name()]) && preg_match('~^[A-Za-z0-9]{32}$~', $_REQUEST[session_name()]) == 0 && !isset($_COOKIE[session_name()]))
			$_COOKIE[session_name()] = md5(md5('smf_sess_' . time()) . mt_rand());

		// Use database sessions?
		if (!empty($smf_settings['databaseSession_enable']) && $smf_connection)
			session_set_save_handler('smf_sessionOpen', 'smf_sessionClose', 'smf_sessionRead', 'smf_sessionWrite', 'smf_sessionDestroy', 'smf_sessionGC');
		elseif (@ini_get('session.gc_maxlifetime') <= 1440 && !empty($smf_settings['databaseSession_lifetime']))
			@ini_set('session.gc_maxlifetime', max($smf_settings['databaseSession_lifetime'], 60));

		session_start();
	}

	// While PHP 4.1.x should use $_SESSION, it seems to need this to do it right.
	if (@version_compare(PHP_VERSION, '4.2.0') == -1)
		$HTTP_SESSION_VARS['smf_php_412_bugfix'] = true;

	// Set the randomly generated code.
	if (!isset($_SESSION['rand_code']))
		$_SESSION['rand_code'] = md5(session_id() . mt_rand());
	$smf_user_info['session_id'] = &$_SESSION['rand_code'];

	if (!isset($_SESSION['USER_AGENT']))
		$_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
}

function smf_sessionOpen($save_path, $session_name)
{
	return true;
}

function smf_sessionClose()
{
	return true;
}

function smf_sessionRead($session_id)
{
	global $smcFunc, $smf_settings;

	if (preg_match('~^[A-Za-z0-9]{16,32}$~', $session_id) == 0)
		return false;

	// Look for it in the database.
	$result = $smcFunc['db_query']('', '
		SELECT data
		FROM {raw:smf_db_prefix}sessions
		WHERE session_id = {string:session_id}
		LIMIT 1',
		array(
			'smf_db_prefix' => $smf_settings['db_prefix'],
			'session_id' => $session_id,
		)
	);
	list ($sess_data) = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);

	return $sess_data;
}

function smf_sessionWrite($session_id, $data)
{
	global $smcFunc, $smf_settings, $smf_connection;

	if (preg_match('~^[A-Za-z0-9]{16,32}$~', $session_id) == 0)
		return false;

	// First try to update an existing row...
	$result = $smcFunc['db_query']('', '
		UPDATE {raw:smf_db_prefix}sessions
		SET data = {string:data}, last_update = {int:last_update}
		WHERE session_id = {string:session_id}',
		array(
			'smf_db_prefix' => $smf_settings['db_prefix'],
			'last_update' => time(),
			'data' => $data,
			'session_id' => $session_id,
		)
	);

	// If that didn't work, try inserting a new one.
	if ($smcFunc['db_affected_rows']() == 0)
		$result = $smcFunc['db_insert']('ignore',
			$smf_settings['db_prefix'] . 'sessions',
			array('session_id' => 'string', 'data' => 'string', 'last_update' => 'int'),
			array($session_id, $data, time()),
			array('session_id')
		);

	return $result;
}

function smf_sessionDestroy($session_id)
{
	global $smcFunc, $smf_settings;

	if (preg_match('~^[A-Za-z0-9]{16,32}$~', $session_id) == 0)
		return false;

	// Just delete the row...
	return $smcFunc['db_query']('', '
		DELETE FROM {raw:smf_db_prefix}sessions
		WHERE session_id = {string:session_id}',
		array(
			'smf_db_prefix' => $smf_settings['db_prefix'],
			'session_id' => $session_id,
		));
}

function smf_sessionGC($max_lifetime)
{
	global $smcFunc, $smf_settings;

	// Just set to the default or lower?  Ignore it for a higher value. (hopefully)
	if ($max_lifetime <= 1440 && !empty($smf_settings['databaseSession_lifetime']))
		$max_lifetime = max($smf_settings['databaseSession_lifetime'], 60);

	// Clean up ;).
	return $smcFunc['db_query']('', '
		DELETE FROM {raw:smf_db_prefix}sessions
		WHERE last_update < {int:old_sessions}',
		array(
			'smf_db_prefix' => $smf_settings['db_prefix'],
			'old_sessions' => time() - $max_lifetime,
		));
}

// Define the sha1 function, if it doesn't exist (but the built in one would be faster.)
if (!function_exists('sha1'))
{
	function sha1($str)
	{
		// If we have mhash loaded in, use it instead!
		if (function_exists('mhash') && defined('MHASH_SHA1'))
			return bin2hex(mhash(MHASH_SHA1, $str));

		$nblk = (strlen($str) + 8 >> 6) + 1;
		$blks = array_pad(array(), $nblk * 16, 0);

		for ($i = 0; $i < strlen($str); $i++)
			$blks[$i >> 2] |= ord($str{$i}) << (24 - ($i % 4) * 8);

		$blks[$i >> 2] |= 0x80 << (24 - ($i % 4) * 8);

		return sha1_core($blks, strlen($str) * 8);
	}

	// This is the core SHA-1 calculation routine, used by sha1().
	function sha1_core($x, $len)
	{
		@$x[$len >> 5] |= 0x80 << (24 - $len % 32);
		$x[(($len + 64 >> 9) << 4) + 15] = $len;

		$w = array();
		$a = 1732584193;
		$b = -271733879;
		$c = -1732584194;
		$d = 271733878;
		$e = -1009589776;

		for ($i = 0, $n = count($x); $i < $n; $i += 16)
		{
			$olda = $a;
			$oldb = $b;
			$oldc = $c;
			$oldd = $d;
			$olde = $e;

			for ($j = 0; $j < 80; $j++)
			{
				if ($j < 16)
					$w[$j] = @$x[$i + $j];
				else
					$w[$j] = sha1_rol($w[$j - 3] ^ $w[$j - 8] ^ $w[$j - 14] ^ $w[$j - 16], 1);

				$t = sha1_rol($a, 5) + sha1_ft($j, $b, $c, $d) + $e + $w[$j] + sha1_kt($j);
				$e = $d;
				$d = $c;
				$c = sha1_rol($b, 30);
				$b = $a;
				$a = $t;
			}

			$a += $olda;
			$b += $oldb;
			$c += $oldc;
			$d += $oldd;
			$e += $olde;
		}

		return dechex($a) . dechex($b) . dechex($c) . dechex($d) . dechex($e);
	}

	function sha1_ft($t, $b, $c, $d)
	{
		if ($t < 20)
			return ($b & $c) | ((~$b) & $d);
		if ($t < 40)
			return $b ^ $c ^ $d;
		if ($t < 60)
			return ($b & $c) | ($b & $d) | ($c & $d);

		return $b ^ $c ^ $d;
	}

	function sha1_kt($t)
	{
		return $t < 20 ? 1518500249 : ($t < 40 ? 1859775393 : ($t < 60 ? -1894007588 : -899497514));
	}

	function sha1_rol($num, $cnt)
	{
		$z = 0x80000000;
		if ($z & $num)
			$a = ($num >> 1 & (~$z | 0x40000000)) >> (31 - $cnt);
		else
			$a = $num >> (32 - $cnt);

		return ($num << $cnt) | $a;
	}
}

// Compat array_fill
if (!function_exists('array_fill'))
{
	// Thanks to: http://php.net/array-fill#30798
	function array_fill($start, $length, $value)
	{
		$return = array();
		for ($i = $start; $i < $length + $start; ++$i) {
			$return[$i] = $value;
		}
		return $return;
	}
}

// Compat array_combine
if (!function_exists('array_combine'))
{
	function array_combine($keys, $values)
	{
		$ret = array();
		if (($array_error = !is_array($keys) || !is_array($values)) || empty($values) || ($count=count($keys)) != count($values))
		{
			trigger_error('array_combine(): Both parameters should be non-empty arrays with an equal number of elements', E_USER_WARNING);

			if ($array_error)
				return;
			return false;
		}

		// Ensure that both arrays aren't associative arrays.
		$keys = array_values($keys);
		$values = array_values($values);

		for ($i=0; $i < $count; $i++)
			$ret[$keys[$i]] = $values[$i];

		return $ret;
	}
}

?>