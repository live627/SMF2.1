<?php
/**********************************************************************************
* upgrade.php                                                                     *
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


// Version information...
define('SMF_VERSION', '2.0 Alpha');
define('SMF_LANG_VERSION', '2.0 Alpha');

$GLOBALS['required_php_version'] = '4.1.0';
$GLOBALS['required_mysql_version'] = '3.23.28';

$databases = array(
	'mysql' => array(
		'name' => 'MySQL',
		'version' => '3.23.28',
		'version_check' => 'return min(mysql_get_server_info(), mysql_get_client_info());',
		'utf8_support' => true,
		'utf8_version' => '4.1.0',
		'utf8_version_check' => 'return mysql_get_server_info();',
		'alter_support' => true,
	),
	'postgresql' => array(
		'name' => 'PostgreSQL',
		'version' => '8.0.1',
		'version_check' => '$version = pg_version(); return $version[\'client\'];',
		'always_has_db' => true,
	),
);

// General options for the script.
$timeLimitThreshold = 3;
$upgrade_path = dirname(__FILE__);
$upgradeurl = $_SERVER['PHP_SELF'];
// Where the SMF images etc are kept.
$smfsite = 'http://www.simplemachines.org/smf';
// Disable the need for admins to login?
$disable_security = 0;
// How long, in seconds, must admin be inactive to allow someone else to run?
$upcontext['inactive_timeout'] = 10;

// All the steps in detail.
// Number,Name,Function,Progress Weight.
$upcontext['steps'] = array(
	0 => array(1, 'Login', 'WelcomeLogin', 2),
	1 => array(2, 'Upgrade Options', 'UpgradeOptions', 2),
	2 => array(3, 'Backup', 'BackupDatabase', 10),
	3 => array(4, 'Database Changes', 'DatabaseChanges', 65),
	4 => array(5, 'Cleanup Mods', 'CleanupMods', 10),
	5 => array(6, 'Upgrade Templates', 'UpgradeTemplate', 10),
	6 => array(7, 'Delete Upgrade', 'DeleteUpgrade', 1),
);
// Just to remember which one has files in it.
$upcontext['database_step'] = 3;
set_time_limit(5000);
// Clean the upgrade path if this is from the client.
if (!empty($_SERVER['argv']) && php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']))
	for ($i = 1; $i < $_SERVER['argc']; $i++)
	{
		if (preg_match('~^--path=(.+)$~', $_SERVER['argv'][$i], $match) != 0)
			$upgrade_path = substr($match[1], -1) == '/' ? substr($match[1], 0, -1) : $match[1];
	}

// Load this now just because we can.
require_once($upgrade_path . '/Settings.php');

// Are we logged in?
if (isset($upgradeData))
{
	$upcontext['user'] = unserialize(base64_decode($upgradeData));
	$upcontext['started'] = $upcontext['user']['started'];
	$upcontext['updated'] = $upcontext['user']['updated'];
}
else
{
	$upcontext['started'] = time();
	$upcontext['updated'] = time();
	$upcontext['user'] = array(
		'id' => 0,
		'name' => 'Guest',
		'pass' => 0,
		'started' => $upcontext['started'],
		'updated' => $upcontext['updated'],
	);
}

// Are we going to be using SSI at this point?
if (isset($_GET['ssi']))
{
	$ssi_maintenance_off = true;
	require_once($upgrade_path . '/SSI.php');
	require_once($sourcedir . '/Subs-Package.php');
	initialize_inputs();
}
// If not we need to do some setup ourselves as SMF is out of the picture.
else
	loadEssentialData();

// All the non-SSI stuff.
if (!function_exists('un_htmlspecialchars'))
{
	function un_htmlspecialchars($string)
	{
		return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES)) + array('&#039;' => '\'', '&nbsp;' => ' '));
	}
}

if (!function_exists('text2words'))
{
	function text2words($text)
	{
		global $smfFunc;

		// Step 1: Remove entities/things we don't consider words:
		$words = preg_replace('~([\x0B\0\xA0\t\r\s\n(){}\\[\\]<>!@$%^*.,:+=`\~\?/\\\\]|&(amp|lt|gt|quot);)+~', ' ', $text);
	
		// Step 2: Entities we left to letters, where applicable, lowercase.
		$words = preg_replace('~([^&\d]|^)[#;]~', '$1 ', un_htmlspecialchars(strtolower($words)));
	
		// Step 3: Ready to split apart and index!
		$words = explode(' ', $words);
		$returned_words = array();
		foreach ($words as $word)
		{
			$word = trim($word, '-_\'');

			if ($word != '')
				$returned_words[] = $smfFunc['db_escape_string'](substr($word, 0, 20));
		}

		return array_unique($returned_words);
	}
}

if (!function_exists('clean_cache'))
{
	// Empty out the cache folder.
	function clean_cache($type = '')
	{
		global $cachedir;

		// No directory = no game.
		if (!is_dir($cachedir))
			return;
	
		$dh = opendir($cachedir);
		while ($file = readdir($dh))
		{
			if (!$type || substr($file, 0, strlen($type)) == $type)
				@unlink($cachedir . '/' . $file);
		}
		closedir($dh);
	}
}

// MD5 Encryption.
if (!function_exists('md5_hmac'))
{
	function md5_hmac($data, $key)
	{
		if (strlen($key) > 64)
			$key = pack('H*', md5($key));
		$key  = str_pad($key, 64, chr(0x00));
	
		$k_ipad = $key ^ str_repeat(chr(0x36), 64);
		$k_opad = $key ^ str_repeat(chr(0x5c), 64);
	
		return md5($k_opad . pack('H*', md5($k_ipad . $data)));
	}
}

// http://www.faqs.org/rfcs/rfc959.html
if (!class_exists('ftp_connection'))
{
	class ftp_connection
	{
		var $connection = 'no_connection', $error = false, $last_message, $pasv = array();
	
		// Create a new FTP connection...
		function ftp_connection($ftp_server, $ftp_port = 21, $ftp_user = 'anonymous', $ftp_pass = 'ftpclient@simplemachines.org')
		{
			if ($ftp_server !== null)
				$this->connect($ftp_server, $ftp_port, $ftp_user, $ftp_pass);
		}
	
		function connect($ftp_server, $ftp_port = 21, $ftp_user = 'anonymous', $ftp_pass = 'ftpclient@simplemachines.org')
		{
			if (substr($ftp_server, 0, 6) == 'ftp://')
				$ftp_server = substr($ftp_server, 6);
			elseif (substr($ftp_server, 0, 7) == 'ftps://')
				$ftp_server = 'ssl://' . substr($ftp_server, 7);
			if (substr($ftp_server, 0, 7) == 'http://')
				$ftp_server = substr($ftp_server, 7);
			$ftp_server = strtr($ftp_server, array('/' => '', ':' => '', '@' => ''));
	
			// Connect to the FTP server.
			$this->connection = @fsockopen($ftp_server, $ftp_port, $err, $err, 5);
			if (!$this->connection)
			{
				$this->error = 'bad_server';
				return;
			}
	
			// Get the welcome message...
			if (!$this->check_response(220))
			{
				$this->error = 'bad_response';
				return;
			}
	
			// Send the username, it should ask for a password.
			fwrite($this->connection, 'USER ' . $ftp_user . "\r\n");
			if (!$this->check_response(331))
			{
				$this->error = 'bad_username';
				return;
			}
	
			// Now send the password... and hope it goes okay.
			fwrite($this->connection, 'PASS ' . $ftp_pass . "\r\n");
			if (!$this->check_response(230))
			{
				$this->error = 'bad_password';
				return;
			}
		}
	
		function chdir($ftp_path)
		{
			if (!is_resource($this->connection))
				return false;
	
			// No slash on the end, please...
			if (substr($ftp_path, -1) == '/')
				$ftp_path = substr($ftp_path, 0, -1);
	
			fwrite($this->connection, 'CWD ' . $ftp_path . "\r\n");
			if (!$this->check_response(250))
			{
				$this->error = 'bad_path';
				return false;
			}
	
			return true;
		}
	
		function chmod($ftp_file, $chmod)
		{
			if (!is_resource($this->connection))
				return false;
	
			// Convert the chmod value from octal (0777) to text ("777").
			fwrite($this->connection, 'SITE CHMOD ' . decoct($chmod) . ' ' . $ftp_file . "\r\n");
			if (!$this->check_response(200))
			{
				$this->error = 'bad_file';
				return false;
			}
	
			return true;
		}
	
		function unlink($ftp_file)
		{
			// We are actually connected, right?
			if (!is_resource($this->connection))
				return false;
	
			// Delete file X.
			fwrite($this->connection, 'DELE ' . $ftp_file . "\r\n");
			if (!$this->check_response(250))
			{
				fwrite($this->connection, 'RMD ' . $ftp_file . "\r\n");
	
				// Still no love?
				if (!$this->check_response(250))
				{
					$this->error = 'bad_file';
					return false;
				}
			}
	
			return true;
		}
	
		function check_response($desired)
		{
			// Wait for a response that isn't continued with -, but don't wait too long.
			$time = time();
			do
				$this->last_message = fgets($this->connection, 1024);
			while (substr($this->last_message, 3, 1) != ' ' && time() - $time < 5);
	
			// Was the desired response returned?
			return is_array($desired) ? in_array(substr($this->last_message, 0, 3), $desired) : substr($this->last_message, 0, 3) == $desired;
		}
	
		function passive()
		{
			// We can't create a passive data connection without a primary one first being there.
			if (!is_resource($this->connection))
				return false;
	
			// Request a passive connection - this means, we'll talk to you, you don't talk to us.
			@fwrite($this->connection, "PASV\r\n");
			$time = time();
			do
				$response = fgets($this->connection, 1024);
			while (substr($response, 3, 1) != ' ' && time() - $time < 5);
	
			// If it's not 227, we weren't given an IP and port, which means it failed.
			if (substr($response, 0, 4) != '227 ')
			{
				$this->error = 'bad_response';
				return false;
			}
	
			// Snatch the IP and port information, or die horribly trying...
			if (preg_match('~\((\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))\)~', $response, $match) == 0)
			{
				$this->error = 'bad_response';
				return false;
			}
	
			// This is pretty simple - store it for later use ;).
			$this->pasv = array('ip' => $match[1] . '.' . $match[2] . '.' . $match[3] . '.' . $match[4], 'port' => $match[5] * 256 + $match[6]);
	
			return true;
		}
	
		function create_file($ftp_file)
		{
			// First, we have to be connected... very important.
			if (!is_resource($this->connection))
				return false;
	
			// I'd like one passive mode, please!
			if (!$this->passive())
				return false;
	
			// Seems logical enough, so far...
			fwrite($this->connection, 'STOR ' . $ftp_file . "\r\n");
	
			// Okay, now we connect to the data port.  If it doesn't work out, it's probably "file already exists", etc.
			$fp = @fsockopen($this->pasv['ip'], $this->pasv['port'], $err, $err, 5);
			if (!$fp || !$this->check_response(150))
			{
				$this->error = 'bad_file';
				@fclose($fp);
				return false;
			}
	
			// This may look strange, but we're just closing it to indicate a zero-byte upload.
			fclose($fp);
			if (!$this->check_response(226))
			{
				$this->error = 'bad_response';
				return false;
			}
	
			return true;
		}
	
		function list_dir($ftp_path = '', $search = false)
		{
			// Are we even connected...?
			if (!is_resource($this->connection))
				return false;
	
			// Passive... non-agressive...
			if (!$this->passive())
				return false;
	
			// Get the listing!
			fwrite($this->connection, 'LIST -1' . ($search ? 'R' : '') . ($ftp_path == '' ? '' : ' ' . $ftp_path) . "\r\n");
	
			// Connect, assuming we've got a connection.
			$fp = @fsockopen($this->pasv['ip'], $this->pasv['port'], $err, $err, 5);
			if (!$fp || !$this->check_response(array(150, 125)))
			{
				$this->error = 'bad_response';
				@fclose($fp);
				return false;
			}
	
			// Read in the file listing.
			$data = '';
			while (!feof($fp))
				$data .= fread($fp, 4096);;
			fclose($fp);
	
			// Everything go okay?
			if (!$this->check_response(226))
			{
				$this->error = 'bad_response';
				return false;
			}
	
			return $data;
		}
	
		function locate($file, $listing = null)
		{
			if ($listing === null)
				$listing = $this->list_dir('', true);
			$listing = explode("\n", $listing);
	
			@fwrite($this->connection, "PWD\r\n");
			$time = time();
			do
				$response = fgets($this->connection, 1024);
			while (substr($response, 3, 1) != ' ' && time() - $time < 5);
	
			// Check for 257!
			if (preg_match('~^257 "(.+?)" ~', $response, $match) != 0)
				$current_dir = strtr($match[1], array('""' => '"'));
			else
				$current_dir = '';
	
			for ($i = 0, $n = count($listing); $i < $n; $i++)
			{
				if (trim($listing[$i]) == '' && isset($listing[$i + 1]))
				{
					$current_dir = substr(trim($listing[++$i]), 0, -1);
					$i++;
				}
	
				// Okay, this file's name is:
				$listing[$i] = $current_dir . '/' . trim(strlen($listing[$i]) > 30 ? strrchr($listing[$i], ' ') : $listing[$i]);
	
				if (substr($file, 0, 1) == '*' && substr($listing[$i], -(strlen($file) - 1)) == substr($file, 1))
					return $listing[$i];
				if (substr($file, -1) == '*' && substr($listing[$i], 0, strlen($file) - 1) == substr($file, 0, -1))
					return $listing[$i];
				if (basename($listing[$i]) == $file || $listing[$i] == $file)
					return $listing[$i];
			}
	
			return false;
		}
	
		function create_dir($ftp_dir)
		{
			// We must be connected to the server to do something.
			if (!is_resource($this->connection))
				return false;
	
			// Make this new beautiful directory!
			fwrite($this->connection, 'MKD ' . $ftp_dir . "\r\n");
			if (!$this->check_response(257))
			{
				$this->error = 'bad_file';
				return false;
			}
	
			return true;
		}
	
		function detect_path($filesystem_path, $lookup_file = null)
		{
			$username = '';
	
			if (isset($_SERVER['DOCUMENT_ROOT']))
			{
				if (preg_match('~^/home[2]?/([^/]+?)/public_html~', $_SERVER['DOCUMENT_ROOT'], $match))
				{
					$username = $match[1];
	
					$path = strtr($_SERVER['DOCUMENT_ROOT'], array('/home/' . $match[1] . '/' => '', '/home2/' . $match[1] . '/' => ''));
	
					if (substr($path, -1) == '/')
						$path = substr($path, 0, -1);
	
					if (strlen(dirname($_SERVER['PHP_SELF'])) > 1)
						$path .= dirname($_SERVER['PHP_SELF']);
				}
				elseif (substr($filesystem_path, 0, 9) == '/var/www/')
					$path = substr($filesystem_path, 8);
				else
					$path = strtr(strtr($filesystem_path, array('\\' => '/')), array($_SERVER['DOCUMENT_ROOT'] => ''));
			}
			else
				$path = '';
	
			if (is_resource($this->connection) && $this->list_dir($path) == '')
			{
				$data = $this->list_dir('', true);
	
				if ($lookup_file === null)
					$lookup_file = $_SERVER['PHP_SELF'];
	
				$found_path = dirname($this->locate('*' . basename(dirname($lookup_file)) . '/' . basename($lookup_file), $data));
				if ($found_path == false)
					$found_path = dirname($this->locate(basename($lookup_file)));
				if ($found_path != false)
					$path = $found_path;
			}
			elseif (is_resource($this->connection))
				$found_path = true;
	
			return array($username, $path, isset($found_path));
		}
	
		function close()
		{
			// Goodbye!
			fwrite($this->connection, "QUIT\r\n");
			fclose($this->connection);
	
			return true;
		}
	}
}

// Have we got tracking data - if so use it (It will be clean!)
if (isset($_GET['data']))
{
	$upcontext['upgrade_status'] = unserialize(base64_decode($_GET['data']));
	$upcontext['current_step'] = $upcontext['upgrade_status']['curstep'];
	$upcontext['language'] = $upcontext['upgrade_status']['lang'];
	$upcontext['rid'] = $upcontext['upgrade_status']['rid'];
	$is_debug = $upcontext['upgrade_status']['debug'];
	$support_js = $upcontext['upgrade_status']['js'];
}
// Set the defaults.
else
{
	$upcontext['current_step'] = 0;
	$upcontext['rid'] = rand(0, 5000);
	$upcontext['upgrade_status'] = array(
		'curstep' => 0,
		'lang' => isset($_GET['lang']) ? $_GET['lang'] : $language,
		'rid' => $upcontext['rid'],
		'pass' => 0,
		'debug' => 0,
		'js' => 0,
	);
	$upcontext['language'] = $upcontext['upgrade_status']['lang'];
}

// This only exists if we're on SMF ;)
if (isset($modSettings['smfVersion']))
{
	$request = $smfFunc['db_query']('', "
		SELECT variable, value
		FROM {$db_prefix}themes
		WHERE id_theme = 1
			AND variable IN ('theme_url', 'images_url')", false, false) or die($smfFunc['db_error']($db_connection));
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$modSettings[$row['variable']] = $row['value'];
	$smfFunc['db_free_result']($request);
}

if (!isset($modSettings['theme_url']))
{
	$modSettings['theme_url'] = 'Themes/default';
	$modSettings['images_url'] = 'Themes/default/images';
}
if (!isset($settings['default_theme_url']))
	$settings['default_theme_url'] = $modSettings['theme_url'];

$upcontext['is_large_forum'] = (empty($modSettings['smfVersion']) || $modSettings['smfVersion'] <= '1.1 RC1') && !empty($modSettings['totalMessages']) && $modSettings['totalMessages'] > 75000;
// Default title...
$upcontext['page_title'] = isset($modSettings['smfVersion']) ? 'Updating Your SMF Install!' : 'Upgrading from YaBB SE!';

if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']))
{
	$command_line = true;

	cmdStep0();
	exit;
}
else
	$command_line = false;

// Don't error if we're using xml.
if (isset($_GET['xml']))
	$upcontext['return_error'] = true;

// Loop through all the steps doing each one as required.
$upcontext['overall_percent'] = 0;
foreach ($upcontext['steps'] as $num => $step)
{
	if ($num >= $upcontext['current_step'])
	{
		// The current weight of this step in terms of overall progress.
		$upcontext['step_weight'] = $step[3];

		// We cannot procede if we're not logged in.
		if ($num != 0 && !$disable_security && $upcontext['user']['pass'] != $upcontext['upgrade_status']['pass'])
		{
			$upcontext['steps'][0][2]();
			break;
		}

		// Call the step and if it returns false that means pause!
		if (function_exists($step[2]) && $step[2]() === false)
			break;
		elseif (function_exists($step[2]))
			$upcontext['current_step']++;
	}
	$upcontext['overall_percent'] += $step[3];
}

upgradeExit();

// Exit the upgrade script.
function upgradeExit()
{
	global $upcontext, $upgradeurl, $boarddir;

	// Save where we are...
	if (!empty($upcontext['current_step']) && !empty($upcontext['user']['id']))
	{
		$upcontext['user']['step'] = $upcontext['current_step'];
		$upcontext['user']['substep'] = $_GET['substep'];
		$upcontext['user']['updated'] = time();
		$upgradeData = base64_encode(serialize($upcontext['user']));
		copy($boarddir . '/Settings.php', $boarddir . '/Settings_bak.php');
		changeSettings(array('upgradeData' => '"' . $upgradeData . '"'));
	}

	// Handle the progress of the step, if any.
	if (!empty($upcontext['step_progress']) && isset($upcontext['steps'][$upcontext['current_step']]))
	{
		$upcontext['step_progress'] = round($upcontext['step_progress'], 1);
		$upcontext['overall_percent'] += $upcontext['step_progress'] * ($upcontext['steps'][$upcontext['current_step']][3] / 100);
	}
	$upcontext['overall_percent'] = (int) $upcontext['overall_percent'];

	if (!isset($_GET['xml']))
		template_upgrade_above();
	else
	{
		header('Content-Type: text/xml; charset=ISO-8859-1');
		// Sadly we need to retain the $_GET data thanks to the old upgrade scripts.
		$upcontext['get_data'] = array();
		foreach ($_GET as $k => $v)
		{
			if (substr($k, 0, 3) != 'amp' && !in_array($k, array('xml', 'substep', 'lang', 'data', 'step', 'filecount')))
			{
				$upcontext['get_data'][$k] = $v;
			}
		}
		template_xml_above();
	}

	// Call the template.
	if (isset($upcontext['sub_template']))
	{
		$upcontext['upgrade_status']['curstep'] = $upcontext['current_step'];
		$upcontext['form_url'] = $upgradeurl . '?step=' . $upcontext['current_step'] . '&amp;substep=' . $_GET['substep'] . '&amp;data=' . base64_encode(serialize($upcontext['upgrade_status']));

		// Custom stuff to pass back?
		if (!empty($upcontext['query_string']))
			$upcontext['form_url'] .= $upcontext['query_string'];

		call_user_func('template_' . $upcontext['sub_template']);
	}

	// Show the footer.
	if (!isset($_GET['xml']))
		template_upgrade_below();
	else
		template_xml_below();

	// Bang - gone!
	die();
}

// Used to direct the user to another location.
function redirectLocation($location, $addForm = true)
{
	global $upgradeurl, $upcontext;

	// Are we providing the core info?
	if ($addForm)
	{
		$upcontext['upgrade_status']['curstep'] = $upcontext['current_step'];
		$location = $upgradeurl . '?step=' . $upcontext['current_step'] . '&amp;substep=' . $_GET['substep'] . '&amp;data=' . base64_encode(serialize($upcontext['upgrade_status'])) . $location;
	}

	while (@ob_end_clean());
	header('Location: ' . $location);
	die();
}

// Load all essential data and connect to the DB as this is pre SSI.php
function loadEssentialData()
{
	global $db_server, $db_user, $db_passwd, $db_name, $db_connection, $db_prefix, $db_character_set, $db_type;
	global $modSettings, $sourcedir, $smfFunc, $upcontext;

	// Do the non-SSI stuff...
	@set_magic_quotes_runtime(0);
	error_reporting(E_ALL);
	define('SMF', 1);

	// Start the session.
	if (@ini_get('session.save_handler') == 'user')
		@ini_set('session.save_handler', 'files');
	@session_start();

	if (empty($smfFunc))
		$smfFunc = array();

	// Initialize everything...
	initialize_inputs();

	// Get the database going!
	if (empty($db_type))
		$db_type = 'mysql';
	if (file_exists($sourcedir . '/Subs-Db-' . $db_type . '.php'))
	{
		require_once($sourcedir . '/Subs-Db-' . $db_type . '.php');

		// Make the connection...
		$db_connection = smf_db_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix);

		if ($db_type == 'mysql' && isset($db_character_set) && preg_match('~^\w+$~', $db_character_set) === 1)
			$smfFunc['db_query']('', "
			SET NAMES $db_character_set", false, false);

		// Load the modSettings data...
		$request = $smfFunc['db_query']('', "
			SELECT variable, value
			FROM {$db_prefix}settings", false, false) or die($smfFunc['db_error']($db_connection));
		$modSettings = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$modSettings[$row['variable']] = $row['value'];
		$smfFunc['db_free_result']($request);
	}

	// If they don't have the file, they're going to get a warning anyway so we won't need to clean request vars.
	if (file_exists($sourcedir . '/QueryString.php'))
	{
		require_once($sourcedir . '/QueryString.php');
		cleanRequest();
	}
}

function initialize_inputs()
{
	global $sourcedir, $start_time, $upcontext, $db_type;

	$start_time = time();

	umask(0);

	// Fun.  Low PHP version...
	if (!isset($_GET))
	{
		$GLOBALS['_GET']['step'] = 0;
		return;
	}

	ob_start();

	// Better to upgrade cleanly and fall apart than to screw everything up if things take too long.
	ignore_user_abort(true);

	// This is really quite simple; if ?delete is on the URL, delete the upgrader...
	if (isset($_GET['delete']))
	{
		@unlink(__FILE__);

		// And the extra little files ;).
		@unlink(dirname(__FILE__) . '/upgrade_1-0.sql');
		@unlink(dirname(__FILE__) . '/upgrade_1-1.sql');
		@unlink(dirname(__FILE__) . '/webinstall.php');

		$dh = opendir(dirname(__FILE__));
		while ($file = readdir($dh))
		{
			if (preg_match('~upgrade_\d-\d_([A-Za-z])+\.sql~i', $file, $matches) && isset($matches[1]))
				@unlink(dirname(__FILE__) . '/' . $file);
  		}
  		closedir($dh);

		header('Location: http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']) . dirname($_SERVER['PHP_SELF']) . '/Themes/default/images/blank.gif');
		exit;
	}

	// Something is causing this to happen, and it's annoying.  Stop it.
	$temp = 'upgrade_php?step';
	while (strlen($temp) > 4)
	{
		if (isset($_GET[$temp]))
			unset($_GET[$temp]);
		$temp = substr($temp, 1);
	}

	// Force a step, defaulting to 0.
	$_GET['step'] = (int) @$_GET['step'];
	$_GET['substep'] = (int) @$_GET['substep'];
}

// Step 0 - Let's welcome them in and ask them to login!
function WelcomeLogin()
{
	global $boarddir, $sourcedir, $db_prefix, $language, $modSettings, $cachedir, $upgradeurl, $upcontext, $disable_security;
	global $smfFunc, $db_type, $databases;

	$upcontext['sub_template'] = 'welcome_message';

	// Do they meet the install requirements?
	if (!php_version_check())
	{
		throw_error('Warning!  You do not appear to have a version of PHP installed on your webserver that meets SMF\'s minimum installations requirements.<br /><br />Please ask your host to upgrade.');
		return false;
	}
	if (!db_version_check())
	{
		throw_error('Your ' . $databases[$db_type]['name'] . ' version does not meet the minimum requirements of SMF.<br /><br />Please ask your host to upgrade.');
		return false;
	}

	// Do they have ALTER privileges?
	if (!empty($databases[$db_type]['alter_support']) && $smfFunc['db_query']('alter_boards', "ALTER TABLE {$db_prefix}boards ORDER BY id_board", false, false) === false)
	{
		throw_error('The ' . $databases[$db_type]['name'] . ' user you have set in Settings.php does not have proper privileges.<br /><br />Please ask your host to give this user the ALTER, CREATE, and DROP privileges.');
		return false;
	}

	// Check for some key files - one template, one language, and a new and an old source file.
	$check = @file_exists($boarddir . '/Themes/default/index.template.php')
		&& @file_exists($sourcedir . '/QueryString.php')
		&& @file_exists($sourcedir . '/ManageBoards.php')
		&& @file_exists(dirname(__FILE__) . '/upgrade_2-0_mysql.sql')
		&& @file_exists(dirname(__FILE__) . '/upgrade_1-1.sql')
		&& @file_exists(dirname(__FILE__) . '/upgrade_1-0.sql');
	if (!$check && !isset($modSettings['smfVersion']))
	{
		// Don't tell them what files exactly because it's a spot check - just like teachers don't tell which problems they are spot checking, that's dumb.
		throw_error('The upgrader was unable to find some crucial files.<br /><br />Please make sure you uploaded all of the files included in the package, including the Themes, Sources, and other directories.');
		return false;
	}

	// Do a quick version spot check.
	$temp = substr(@implode('', @file($boarddir . '/index.php')), 0, 4096);
	preg_match('~\*\s*Software\s+Version:\s+SMF\s+(.+?)[\s]{2}~i', $temp, $match);
	if (empty($match[1]) || $match[1] != SMF_VERSION)
	{
		throw_error('The upgrader found some old or outdated files.<br /><br />Please make certain you uploaded the new versions of all the files included in the package.');
		return false;
	}

	// What absolutely needs to be writable?
	$writable_files = array(
		$boarddir . '/Settings.php',
		$boarddir . '/Settings_bak.php',
	);

	$cachedir_temp = empty($cachedir) ? $boarddir . '/cache' : $cachedir;
	if (!file_exists($cachedir_temp))
		@mkdir($cachedir_temp);
	$writable_files[] = $cachedir_temp;

	if (!makeFilesWritable($writable_files))
		return false;

	// Check agreement.txt. (it may not exist, in which case $boarddir must be writable.)
	if (isset($modSettings['agreement']) && (!is_writable($boarddir) || file_exists($boarddir . '/agreement.txt')) && !is_writable($boarddir . '/agreement.txt'))
	{
		throw_error('The upgrader was unable to obtain write access to agreement.txt.<br /><br />If you are using a linux or unix based server, please ensure that the file is chmod\'d to 777, or if it does not exist that the directory this upgrader is in is 777.<br />If your server is running Windows, please ensure that the internet guest account has the proper permissions on it or its folder.');
		return false;
	}
	// Upgrade the agreement.
	elseif (isset($modSettings['agreement']))
	{
		$fp = fopen($boarddir . '/agreement.txt', 'w');
		fwrite($fp, $modSettings['agreement']);
		fclose($fp);
	}

	if (!file_exists($boarddir . '/Themes/default/languages/index.' . basename($language, '.lng') . '.php') && !isset($modSettings['smfVersion']) && !isset($_GET['lang']))
	{
		throw_error('The upgrader was unable to find language files for the language specified in Settings.php.<br />SMF will not work without the primary language files installed.<br /><br />Please either install them, or <a href="' . $upgradeurl . '?step=0;lang=english">use english instead</a>.');
		return false;
	}
	else
	{
		$temp = substr(@implode('', @file($boarddir . '/Themes/default/languages/index.' . (basename($upcontext['language'], '.lng')) . '.php')), 0, 4096);
		preg_match('~(?://|/\*)\s*Version:\s+(.+?);\s*index(?:[\s]{2}|\*/)~i', $temp, $match);

		if (empty($match[1]) || $match[1] != SMF_LANG_VERSION)
		{
			throw_error('The upgrader found some old or outdated language files.<br /><br />Please make certain you uploaded the new versions of all the files included in the package, even the theme and language files for the default theme.');
			return false;
		}
	}

	// Are we trying to login?
	if (isset($_POST['user']))
	{
		// Before 2.0 these column names were different!
		$oldDB = false;
		if (empty($db_type) || $db_type == 'mysql')
		{
			$request = $smfFunc['db_query']('', "
				SHOW COLUMNS
				FROM {$db_prefix}members
				LIKE 'memberName'", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($request) != 0)
				$oldDB = true;
			$smfFunc['db_free_result']($request);
		}

		// Get what we believe to be their details.
		if ($oldDB)
			$request = $smfFunc['db_query']('', "
				SELECT id_member, memberName AS member_name, passwd, id_group,
				additionalGroups AS additional_groups
				FROM {$db_prefix}members
				WHERE memberName = '" . $smfFunc['db_escape_string']($_POST['user']) . "'", false, false);
		else
			$request = $smfFunc['db_query']('', "
				SELECT id_member, member_name, passwd, id_group, additional_groups
				FROM {$db_prefix}members
				WHERE member_name = '" . $smfFunc['db_escape_string']($_POST['user']) . "'", false, false);
		if ($smfFunc['db_num_rows']($request) != 0)
		{
			list ($id_member, $name, $password, $id_group, $addGroups) = $smfFunc['db_fetch_row']($request);

			$groups = explode(',', $addGroups);
			$groups[] = $id_group;

			// Figure out the password using SMF's encryption - if what they typed is right.
			if (isset($_REQUEST['hash_passwrd']) && strlen($_REQUEST['hash_passwrd']) == 40)
			{
				// Challenge passed.
				if ($_REQUEST['hash_passwrd'] == sha1($password . $upcontext['rid']))
					$sha_passwd = $password;
			}
			else
				$sha_passwd = sha1(strtolower($name) . un_htmlspecialchars($smfFunc['db_unescape_string']($_REQUEST['passwrd'])));
		}
		else
			$upcontext['username_incorrect'] = true;
		$upcontext['username'] = $_POST['user'];
		$smfFunc['db_free_result']($request);

		// Track whether javascript works!
		if (!empty($_POST['js_works']))
			$upcontext['upgrade_status']['js'] = 1;
		// Note down the version we are coming from.
		if (!empty($modSettings['smfVersion']) && empty($upcontext['user']['version']))
			$upcontext['user']['version'] = $modSettings['smfVersion'];

		// Didn't get anywhere?
		if ((empty($sha_passwd) || $password != $sha_passwd) && empty($upcontext['username_incorrect']) && !$disable_security)
		{
			// MD5?
			$md5pass = md5_hmac($_REQUEST['passwrd'], strtolower($_POST['user']));
			if ($md5pass != $password)
			{
				$upcontext['password_failed'] = true;
				// Disable the hashing this time.
				$upcontext['disable_login_hashing'] = true;
			}
		}

		if ((empty($upcontext['password_failed']) && !empty($name)) || $disable_security)
		{
			// Set the password.
			if (!$disable_security)
			{
				// Do we actually have permission?
				if (!in_array(1, $groups))
				{
					$request = $smfFunc['db_query']('', "
						SELECT permission
						FROM {$db_prefix}permissions
						WHERE id_group IN (" . implode(',', $groups) . ")
							AND permission = 'admin_forum'", false, false);
					if ($smfFunc['db_num_rows']($request) == 0)
						throw_error('You need to be an admin to perform an upgrade!');
					$smfFunc['db_free_result']($request);
				}

				$upcontext['user']['id'] = $id_member;
				$upcontext['user']['name'] = $name;
			}
			else
			{
				$upcontext['user']['id'] = 1;
				$upcontext['user']['name'] = 'Guest';
			}
			$upcontext['user']['pass'] = rand(0,60000);
			// This basically is used to match the GET variables to Settings.php.
			$upcontext['upgrade_status']['pass'] = $upcontext['user']['pass'];

			// If we're resuming set the step and substep to be correct.
			if (isset($_POST['cont']))
			{
				// Note it's -1 as it will get autoinc.
				$upcontext['current_step'] = $upcontext['user']['step'] - 1;
				$_GET['substep'] = $upcontext['user']['substep'];
			}

			return true;
		}
	}

	// All ready - pause and wait for input.
	return false;
}

// Step 1: Do the maintenance and backup.
function UpgradeOptions()
{
	global $db_prefix, $command_line, $modSettings, $is_debug, $smfFunc;
	global $boarddir, $boardurl, $sourcedir, $maintenance, $mmessage, $cachedir, $upcontext, $db_type;

	$upcontext['sub_template'] = 'upgrade_options';
	$upcontext['page_title'] = 'Upgrade Options';

	// If we've not submitted then we're done.
	if (empty($_POST['upcont']))
		return false;

	// Firstly, if they're enabling SM stat collection just do it.
	if (!empty($_POST['stats']) && substr($boardurl, 0, 16) != 'http://localhost' && empty($modSettings['allow_sm_stats']))
	{
		// Attempt to register the site etc.
		$fp = @fsockopen("www.simplemachines.org", 80, $errno, $errstr);
		if ($fp)
		{
			$out = "GET /smf/stats/register_stats.php?site=" . base64_encode($boardurl) . " HTTP/1.1\r\n";
			$out .= "Host: www.simplemachines.org\r\n";
			$out .= "Connection: Close\r\n\r\n";
			fwrite($fp, $out);

			$return_data = '';
			while (!feof($fp))
				$return_data .= fgets($fp, 128);

			fclose($fp);

			// Get the unique site ID.
			preg_match('~SITE-ID:\s(\w{10})~', $return_data, $ID);

			if (!empty($ID[1]))
				$smfFunc['db_insert']('replace',
					"{$db_prefix}settings",
					array('variable', 'value'),
					array('\'allow_sm_stats\'', '\'' . $ID[1] . '\''),
					array('variable')
				);
		}
	}
	else
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}settings
			WHERE variable = 'allow_sm_stats'", false, false);

	$changes = array();

	$changes['language'] = '\'' . $_POST['lang'] . '\'';

	if (!empty($_POST['maint']))
	{
		$changes['maintenance'] = '2';
		// Remember what it was...
		$upcontext['user']['main'] = $maintenance;

		if (!empty($_POST['maintitle']))
		{
			$changes['mtitle'] = '\'' . $_POST['maintitle'] . '\'';
			$changes['mmessage'] = '\'' . $_POST['mainmessage'] . '\'';
		}
		else
		{
			$changes['mtitle'] = '\'Upgrading the forum...\'';
			$changes['mmessage'] = '\'Don\\\'t worry, we will be back shortly with an updated forum.  It will only be a minute ;).\'';
		}
	}

	if ($command_line)
		echo ' * Updating Settings.php...';

	// Backup the current one first.
	copy($boarddir . '/Settings.php', $boarddir . '/Settings_bak.php');

	// Fix some old paths.
	if (substr($boarddir, 0, 1) == '.')
		$changes['boarddir'] = '\'' . fixRelativePath($boarddir) . '\'';

	if (substr($sourcedir, 0, 1) == '.')
		$changes['sourcedir'] = '\'' . fixRelativePath($sourcedir) . '\'';

	if (empty($cachedir) || substr($cachedir, 0, 1) == '.')
		$changes['cachedir'] = '\'' . fixRelativePath($boarddir) . '/cache\'';

	// Not had the database type added before?
	if (empty($db_type))
		$changes['db_type'] = 'mysql';

	// !!! Maybe change the cookie name if going to 1.1, too?

	// Update Settings.php with the new settings.
	changeSettings($changes);

	if ($command_line)
		echo " Successful.\n";

	// Are we doing debug?
	if (isset($_POST['debug']))
	{
		$upcontext['upgrade_status']['debug'] = true;
		$is_debug = true;
	}

	// If we're not backing up then jump one.
	if (empty($_POST['backup']))
		$upcontext['current_step']++;

	// If we've got here then let's proceed to the next step!
	return true;
}

// Backup the database - why not...
function BackupDatabase()
{
	global $upcontext, $db_prefix, $command_line, $is_debug, $support_js, $file_steps, $smfFunc;

	$upcontext['sub_template'] = isset($_GET['xml']) ? 'backup_xml' : 'backup_database';
	$upcontext['page_title'] = 'Backup Database';

	// Done it already - js wise?
	if (!empty($_POST['backup_done']))
		return true;

	if (preg_match('~^`(.+?)`\.(.+?)$~', $db_prefix, $match) != 0)
	{
		$result = upgrade_query("
			SHOW TABLES
			FROM `" . strtr($match[1], array('`' => '')) . "`
			LIKE '" . str_replace('_', '\_', $match[2]) . "%'");
	}
	else
	{
		$result = upgrade_query("
			SHOW TABLES
			LIKE '" . str_replace('_', '\_', $db_prefix) . "%'");
	}

	$table_names = array();
	while ($row = $smfFunc['db_fetch_row']($result))
		if (substr($row[0], 0, 7) !== 'backup_')
			$table_names[] = $row[0];
	$smfFunc['db_free_result']($result);

	$upcontext['table_count'] = count($table_names);
	$upcontext['cur_table_num'] = $_GET['substep'];
	$upcontext['cur_table_name'] = str_replace($db_prefix, '', isset($table_names[$_GET['substep']]) ? $table_names[$_GET['substep']] : $table_names[0]);
	$upcontext['step_progress'] = (int) (($upcontext['cur_table_num'] / $upcontext['table_count']) * 100);
	// For non-java auto submit...
	$file_steps = $upcontext['table_count'];

	// What ones have we already done?
	foreach ($table_names as $id => $table)
		if ($id < $_GET['substep'])
			$upcontext['previous_tables'][] = $table;

	if ($command_line)
		echo ' * ';

	// If we don't support javascript we backup here.
	if (!$support_js || isset($_GET['xml']))
	{
		db_extend();
		// Backup each table!
		for ($substep = $_GET['substep'], $n = count($table_names); $substep < $n; $substep++)
		{
			$upcontext['cur_table_name'] = str_replace($db_prefix, '', $table_names[$substep]);
			$upcontext['cur_table_num'] = $substep + 1;

			$upcontext['step_progress'] = (int) (($upcontext['cur_table_num'] / $upcontext['table_count']) * 100);

			// Do we need to pause?
			nextSubstep($substep);

			backupTable($table_names[$substep]);

			// If this is XML to keep it nice for the user do one table at a time anyway!
			if (isset($_GET['xml']))
				return upgradeExit();
		}
	
		if ($is_debug && $command_line)
		{
			echo "\n Successful.'\n";
			flush();
		}
		$upcontext['step_progress'] = 100;
		$upcontext['current_step']++;
		$_GET['substep'] = 0;
		// Make sure we move on!
		return true;
	}

	// Either way next place to post will be database changes!
	$_GET['substep'] = 0;
	return false;
}

// Backup one table...
function backupTable($table)
{
	global $is_debug, $command_line, $db_prefix, $smfFunc;

	if ($is_debug && $command_line)
	{
		echo "\n +++ Backing up \"" . str_replace($db_prefix, '', $table) . '"...';
		flush();
	}

	$smfFunc['db_backup_table']($table, 'backup_' . $table);

	if ($is_debug && $command_line)
		echo ' done.';
}

// Step 2: Everything.
function DatabaseChanges()
{
	global $db_prefix, $modSettings, $command_line, $smfFunc;
	global $language, $boardurl, $sourcedir, $boarddir, $upcontext, $support_js, $db_type;

	// Have we just completed this?
	if (!empty($_POST['database_done']))
		return true;

	$upcontext['sub_template'] = isset($_GET['xml']) ? 'database_xml' : 'database_changes';
	$upcontext['page_title'] = 'Database Changes';

	// All possible files.
	// Name, <version, insert_on_complete
	$files = array(
		array('upgrade_1-0.sql', '1.1', '1.1 RC0'),
		array('upgrade_1-1.sql', '2.0', '2.0 a'),
		array('upgrade_2-0_' . $db_type . '.sql', '3.0', SMF_VERSION),
	);

	// How many files are there in total?
	if (isset($_GET['filecount']))
		$upcontext['file_count'] = (int) $_GET['filecount'];
	else
	{
		$upcontext['file_count'] = 0;
		foreach ($files as $file)
		{
			if (!isset($modSettings['smfVersion']) || $modSettings['smfVersion'] < $file[1])
				$upcontext['file_count']++;
		}
	}

	// Do each file!
	$did_not_do = count($files) - $upcontext['file_count'];
	$upcontext['step_progress'] = 0;
	$upcontext['cur_file_num'] = 0;
	foreach ($files as $file)
	{
		if ($did_not_do)
			$did_not_do--;
		else
		{
			$upcontext['cur_file_num']++;
			$upcontext['cur_file_name'] = $file[0];
			// Do we actually need to do this still?
			if (!isset($modSettings['smfVersion']) || $modSettings['smfVersion'] < $file[1])
			{
				$nextFile = parse_sql(dirname(__FILE__) . '/' . $file[0]);
				if ($nextFile)
				{
					// Only update the version of this if complete.
					$smfFunc['db_insert']('replace',
						"{$db_prefix}settings",
						array('variable', 'value'),
						array('\'smfVersion\'', '\'' . $file[2] . '\''),
						array('variable')
					);
			
					$modSettings['smfVersion'] = $file[2];
				}

				// If this is XML we only do this stuff once.
				if (isset($_GET['xml']))
				{
					// Flag to move on to the next.
					$upcontext['completed_step'] = true;
					// Did we complete the whole file?
					if ($nextFile)
						$upcontext['current_debug_item_num'] = -1;
					return upgradeExit();
				}
				elseif ($support_js)
					break;
			}
			// Set the progress bar to be right as if we had - even if we hadn't...
			$upcontext['step_progress'] = ($upcontext['cur_file_num'] / $upcontext['file_count']) * 100;
		}
	}

	$_GET['substep'] = 0;
	// So the template knows we're done.
	if (!$support_js)
	{
		$upcontext['changes_complete'] = true;
		return true;
	}
	return false;
}

// Clean up any mods installed...
function CleanupMods()
{
	global $db_prefix, $modSettings, $upcontext, $boarddir, $sourcedir, $settings, $smfFunc;

	// If we get here withOUT SSI we need to redirect to ensure we get it!
	if (!isset($_GET['ssi']))
		redirectLocation(';ssi=1');

	$upcontext['sub_template'] = 'clean_mods';
	$upcontext['page_title'] = 'Cleanup Modifications';

	// If we're on the second redirect continue...
	if (isset($_POST['cleandone2']))
		return true;

	// Load all theme paths....
	$request = $smfFunc['db_query']('', "
		SELECT id_theme, variable, value
		FROM {$db_prefix}themes
		WHERE id_member = 0
			AND variable IN ('theme_dir', 'images_url')", false, false);
	$theme_paths = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if ($row['id_theme'] == 1)
			$settings['default_' . $row['variable']] = $row['value'];
		elseif ($row['variable'] == 'theme_dir')
			$theme_paths[$row['id_theme']][$row['variable']] = $row['value'];
	}
	$smfFunc['db_free_result']($request);

	// Are there are mods installed that may need uninstalling?
	$request = $smfFunc['db_query']('', "
		SELECT id_install, filename, name, themes_installed, version
		FROM {$db_prefix}log_packages
		WHERE install_state = 1
		ORDER BY time_installed DESC", false, false);
	$upcontext['packages'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Work out the status.
		if (!file_exists($boarddir . '/Packages/' . $row['filename']))
		{
			$status = 'Missing';
			$status_color = 'red';
			$result = 'Removed';
		}
		else
		{
			$status = 'Installed';
			$status_color = 'green';
			$result = 'No Action Needed';
		}

		$upcontext['packages'][$row['id_install']] = array(
			'id' => $row['id_install'],
			'themes' => explode(',', $row['themes_installed']),
			'name' => $row['name'],
			'filename' => $row['filename'],
			'missing_file' => file_exists($boarddir . '/Packages/' . $row['filename']) ? 0 : 1,
			'files' => array(),
			'file_count' => 0,
			'status' => $status,
			'result' => $result,
			'color' => $status_color,
			'version' => $row['version'],
			'needs_removing' => false,
		);
	}
	$smfFunc['db_free_result']($request);

	// Don't carry on if there are none.
	if (empty($upcontext['packages']))
		return true;

	// Setup some basics.
	if (!empty($upcontext['user']['version']))
		$_SESSION['version_emulate'] = $upcontext['user']['version'];

	if (!mktree($boarddir . '/Packages/temp', 0755))
	{
		deltree($boarddir . '/Packages/temp', false);
		if (!mktree($boarddir . '/Packages/temp', 0777))
		{
			deltree($boarddir . '/Packages/temp', false);
			//!!! Error here - plus chmod!
		}
	}

	// We're gonna be doing some removin'
	$test = isset($_POST['cleandone']) ? false : true;
	foreach ($upcontext['packages'] as $id => $package)
	{
		// Can't do anything about this....
		if ($package['missing_file'])
			continue;

		// Not testing *and* this wasn't checked?
		if (!$test && (!isset($_POST['remove']) || !isset($_POST['remove'][$id])))
			continue;

		// What are the themes this was installed into?
		$cur_theme_paths = array();
		foreach ($theme_paths as $tid => $data)
			if ($tid != 1 && in_array($tid, $package['themes']))
				$cur_theme_paths[$tid] = $data;

		// Get the modifications data if applicable.
		$filename = $package['filename'];
		$packageInfo = getPackageInfo($filename);
		$info = parsePackageInfo($packageInfo['xml'], $test, 'uninstall');
		// Also get the reinstall details...
		if (isset($_POST['remove']))
			$infoInstall = parsePackageInfo($packageInfo['xml'], true);

		if (is_file($boarddir . '/Packages/' . $filename))
			read_tgz_file($boarddir . '/Packages/' . $filename, $boarddir . '/Packages/temp');
		else
			copytree($boarddir . '/Packages/' . $filename, $boarddir . '/Packages/temp');

		// Work out how we uninstall...
		foreach ($info as $change)
		{
			// Work out two things:
			// 1) Whether it's installed at the moment - and if so whether its fully installed, and:
			// 2) Whether it could be installed on the new version.
			if ($change['type'] == 'modification')
			{
				$contents = @file_get_contents($boarddir . '/Packages/temp/' . $upcontext['base_path'] . $change['filename']);
				if ($change['boardmod'])
					$results = parseBoardMod($contents, $test, $change['reverse'], $cur_theme_paths);
				else
					$results = parseModification($contents, $test, $change['reverse'], $cur_theme_paths);

				$files = array();
				foreach ($results as $action)
				{
					// Something we can remove? Probably means it existed!
					if (($action['type'] == 'replace' || $action['type'] == 'append') && !in_array($action['filename'], $files))
						$files[] = $action['filename'];
					if ($action['type'] == 'failure')
					{
						$upcontext['packages'][$id]['needs_removing'] = true;
						$upcontext['packages'][$id]['status'] = 'Reinstall Required';
						$upcontext['packages'][$id]['color'] = '#FD6435';
					}
				}
			}
		}

		// Store this info for the template as appropriate.
		$upcontext['packages'][$id]['files'] = $files;
		$upcontext['packages'][$id]['file_count'] = count($files);

		// If we've done something save the changes!
		if (!$test)
			package_flush_cache();

		// Are we attempting to reinstall this thing?
		if (isset($_POST['remove']) && !$test && isset($infoInstall))
		{
			// Need to extract again I'm afraid.
			if (is_file($boarddir . '/Packages/' . $filename))
				read_tgz_file($boarddir . '/Packages/' . $filename, $boarddir . '/Packages/temp');
			else
				copytree($boarddir . '/Packages/' . $filename, $boarddir . '/Packages/temp');

			$errors = false;
			$upcontext['packages'][$id]['result'] = 'Removed';
			foreach ($infoInstall as $change)
			{
				if ($change['type'] == 'modification')
				{
					$contents = @file_get_contents($boarddir . '/Packages/temp/' . $upcontext['base_path'] . $change['filename']);
					if ($change['boardmod'])
						$results = parseBoardMod($contents, true, $change['reverse'], $cur_theme_paths);
					else
						$results = parseModification($contents, true, $change['reverse'], $cur_theme_paths);
	
					// Are there any errors?
					foreach ($results as $action)
						if ($action['type'] == 'failure')
							$errors = true;
				}
			}
			if (!$errors)
			{
				$upcontext['packages'][$id]['result'] = 'Reinstalled';
				$upcontext['packages'][$id]['color'] = 'green';
				foreach ($infoInstall as $change)
				{
					if ($change['type'] == 'modification')
					{
						$contents = @file_get_contents($boarddir . '/Packages/temp/' . $upcontext['base_path'] . $change['filename']);
						if ($change['boardmod'])
							$results = parseBoardMod($contents, false, $change['reverse'], $cur_theme_paths);
						else
							$results = parseModification($contents, false, $change['reverse'], $cur_theme_paths);
					}
				}

				// Save the changes.
				package_flush_cache();
			}
		}
	}
		
	if (file_exists($boarddir . '/Packages/temp'))
		deltree($boarddir . '/Packages/temp');

	// Removing/Reinstalling any packages?
	if (isset($_POST['remove']))
	{
		$deletes = array();
		foreach ($_POST['remove'] as $id => $dummy)
			$deletes[] = (int) $id;

		if (!empty($deletes))
			upgrade_query("
				UPDATE {$db_prefix}log_packages
				SET install_state = 0
				WHERE id_install IN (" . implode(',', $deletes) . ")");

		$upcontext['sub_template'] = 'cleanup_done';
		return false;
	}
	else
	{
		$allgood = true;
		// Is there actually anything that needs our attention?
		foreach ($upcontext['packages'] as $package)
			if ($package['color'] != 'green')
				$allgood = false;

		if ($allgood)
			return true;
	}

	$_GET['substep'] = 0;
	return isset($_POST['cleandone']) ? true : false;
}

// Make any necessary template changes.
function UpgradeTemplate()
{
	global $upcontext, $boarddir, $boardurl, $db_prefix, $command_line, $is_debug, $sourcedir, $txtChanges, $smfFunc;

	$upcontext['page_title'] = 'Upgrade Templates';
	$upcontext['sub_template'] = 'upgrade_templates';
	$endl = $command_line ? "\n" : '<br />' . "\n";

	// We'll want this.
	require_once($sourcedir . '/FixLanguage.php');

	// First work out where on earth we are...
	if (isset($_GET['forreal']))
	{
		$upcontext['is_test'] = false;
		$upcontext['temp_progress'] = (int) $_GET['substep'];
	}
	else
	{
		$upcontext['is_test'] = true;
		$upcontext['temp_progress'] = isset($_GET['substep']) ? (int) $_GET['substep'] : 0;
	}

	// Remembering where we are?
	if (isset($_POST['languages']))
		$upcontext['languages'] = unserialize(base64_decode($_POST['languages']));
	else
		$upcontext['languages'] = array();

	if (isset($_POST['themes']))
		$upcontext['themes'] = unserialize(base64_decode($_POST['themes']));
	else
		$upcontext['themes'] = array();

	// If we're not testing before we start making changes let's work out what needs changing and make it writable!
	if (!$upcontext['is_test'] && isset($_POST['writable_files']))
	{
		$writable_files = unserialize(base64_decode($_POST['writable_files']));
		if (!makeFilesWritable($writable_files))
		{
			// Make sure we don't forget what we have to do!
			$upcontext['writable_files'] = $writable_files;
			return false;
		}
	}
	elseif (isset($_POST['writable_files']))
		$writable_files = unserialize(base64_decode($_POST['writable_files']));
	else
		$writable_files = array();

	// Starting off?
	if ($upcontext['temp_progress'] == 0)
	{
		$upcontext['languages'] = array();
		$upcontext['themes'] = array();
	}

	// Load up all the theme/lang directories - we'll want these.
	$request = $smfFunc['db_query']('', "
		SELECT id_theme, value
		FROM {$db_prefix}themes
		WHERE id_member = 0
			AND variable = 'theme_dir'", false, false);
	$theme_dirs = array();
	$lang_dirs = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$theme_dirs[$row['id_theme']] = $row['value'];
		if (file_exists($row['value'] . '/languages') && is_dir($row['value'] . '/languages'))
			$lang_dirs[$row['id_theme']] = $row['value'] . '/languages';
	}
	$smfFunc['db_free_result']($request);

	// Calculate how many steps in total.
	$upcontext['file_count'] = 0;
	$upcontext['current_message'] = '';
	foreach ($lang_dirs as $id => $langdir)
	{
		$dir = dir($langdir);
		while ($entry = $dir->read())
			$upcontext['file_count']++;
		$dir->close();
	}
	foreach ($theme_dirs as $id => $themedir)
	{
		if (!file_exists($themedir))
			continue;

		$dir = dir($themedir);
		while ($entry = $dir->read())
			$upcontext['file_count']++;
		$dir->close();
	}
	$upcontext['step_progress'] = ($upcontext['is_test'] ? 0 : 50) + (int) (($upcontext['temp_progress'] / $upcontext['file_count']) * 50);

	// Attempt to check all the language files.
	$current_pos = 0;
	foreach ($lang_dirs as $id => $langdir)
	{
		$dir = dir($langdir);
		while ($entry = $dir->read())
		{
			// Done this already?
			$current_pos++;
			if ($upcontext['temp_progress'] >= $current_pos)
				continue;
			$upcontext['temp_progress'] = $current_pos;

			// Found a language? If so add it.
			if (preg_match('~(.+?)\.(.+?)\.php~', $entry, $matches) && isset($matches[2]))
			{
				$upcontext['current_message'] = ($upcontext['is_test'] ? 'Testing' : 'Updating') . ' language file &quot;' . (strlen($matches[0]) > 40 ? '...' . substr(($matches[0]), -40): ($matches[0])) . '&quot...';
				$edit_count = fixLanguageFile($langdir . '/' . $matches[0], $matches[1], $matches[2], $upcontext['is_test']);
				// Are there actually any edits to be made?
				if ($edit_count != -1)
				{
					// Just to pick up on some of the "scaby" things...
					if ($edit_count == 0)
						$edit_count = 1;

					if (!isset($upcontext['languages'][$matches[2]]))
						$upcontext['languages'][$matches[2]] = array(
							'name' => ucwords($matches[2]),
							'files' => array(),
							'writable' => true,
							'edit_count' => 0,
						);

					$upcontext['languages'][$matches[2]]['files'][] = array(
						'name' => $matches[1],
						'dir' => $langdir . '/' . $matches[0],
						'writable' => is_writable($langdir . '/' . $matches[0]),
						'edits' => $edit_count,
					);
					$upcontext['languages'][$matches[2]]['writable'] &= is_writable($langdir . '/' . $matches[0]);
					$upcontext['languages'][$matches[2]]['edit_count'] += $edit_count;
					$writable_files[] = $langdir . '/' . $matches[0];
				}
			}
			$upcontext['writable_files'] = $writable_files;
			nextSubstep($current_pos);
		}
		$dir->close();
	}

	// Now do any templates.
	foreach ($theme_dirs as $id => $themedir)
	{
		// Just get the main directory name...
		preg_match('~/Themes/(\w*)~', $themedir, $matches);
		$theme_name = isset($matches[1]) ? $matches[1] : $themedir;

		//!!! Uncomment before release!
		//if (in_array($theme_name, array('default', 'classic', 'babylon')))
			//continue;

		if (!file_exists($themedir))
			continue;

		$dir = dir($themedir);
		while ($entry = $dir->read())
		{
			// What about this one - done before?
			$current_pos++;
			if ($upcontext['temp_progress'] >= $current_pos)
				continue;
			$upcontext['temp_progress'] = $current_pos;

			// Got a template file... good
			if (substr($entry, -4) == '.php' && strpos($entry, 'template') !== false)
			{
				$upcontext['current_message'] = ($upcontext['is_test'] ? 'Testing' : 'Updating') . ' template file &quot;' . (strlen($themedir . '/' . $entry) > 40 ? '...' . substr(($themedir . '/' . $entry), -40): ($themedir . '/' . $entry)) . '&quot...';
				$edit_count = fixTemplateFile($themedir . '/' . $entry, $upcontext['is_test']);
				if ($edit_count != -1)
				{
					if (!isset($upcontext['themes'][$theme_name]))
					{
						$upcontext['themes'][$theme_name] = array(
							'name' => ucwords($theme_name),
							'files' => array(),
							'writable' => true,
							'edit_count' => 0,
						);
					}

					$upcontext['themes'][$theme_name]['files'][] = array(
						'name' => substr($entry, 0, strpos($entry, '.')),
						'dir' => $themedir . '/' . $entry,
						'writable' => is_writable($themedir . '/' . $entry),
						'edits' => $edit_count,
					);
					$upcontext['themes'][$theme_name]['writable'] &= is_writable($themedir . '/' . $entry);
					$upcontext['themes'][$theme_name]['edit_count'] += $edit_count;
					$writable_files[] = $themedir . '/' . $entry;
				}
			}
			$upcontext['writable_files'] = $writable_files;
			nextSubstep($current_pos);
		}
		$dir->close();
	}

	// Check we can write to it all... yea!
	makeFilesWritable($writable_files);
	$upcontext['writable_files'] = $writable_files;

	// Converting an old YaBBSE template?
	$upcontext['can_upgrade_yabbse'] = file_exists($boarddir . '/template.php') || file_exists($boarddir . '/template.html') && !file_exists($boarddir . '/Themes/converted');
	if (isset($_GET['conv']) && !file_exists($boarddir . '/Themes/converted'))
	{
		if ($is_debug && $command_line)
			echo ' +++ ';

		require_once($sourcedir . '/Themes.php');

		mkdir($boarddir . '/Themes/converted', 0777);

		convert_template($boarddir . '/Themes/converted');

		// Copy over the default index.php file.
		copy($boarddir . '/Themes/classic/index.php', $boarddir . '/Themes/converted/index.php');
		@chmod($boarddir . '/Themes/converted/index.php', 0777);

		// Now set up the "converted" theme.
		$values = array(
			'name' => 'Converted Theme from YaBB SE',
			'theme_url' => $boardurl . '/Themes/classic',
			'images_url' => $boardurl . '/Themes/classic/images',
			'theme_dir' => strtr($boarddir, array('\\' => '/')) . '/Themes/converted',
			'base_theme_dir' => strtr($boarddir, array('\\' => '/')) . '/Themes/classic',
		);

		// Get an available id_theme first...
		$request = $smfFunc['db_query']('', "
			SELECT MAX(id_theme) + 1
			FROM {$db_prefix}themes", false, false);
		list ($id_theme) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		$themeData = array();
		foreach ($values as $variable => $value)
			$themeData[] = array(0, $id_theme, '\'' . $variable . '\'', '\'' . $value . '\'');

		if (!empty($themeData))
		{
			$smfFunc['db_insert']('ignore',
				"{$db_prefix}themes",
				array('id_member', 'id_theme', 'variable', 'value'),
				$themeData,
				array('id_theme', 'id_member', 'variable')
			);
		}

		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}settings
			SET value = CONCAT(value, ',$id_theme')
			WHERE variable = 'knownThemes'", false, false);

		$smfFunc['db_insert']('replace',
				"{$db_prefix}settings",
				array('variable', 'value'),
				array(array('\'theme_guests\'', $id_theme), array('\'smiley_sets_default\'', '\'classic\'')),
				array('variable')
			);

		if ($is_debug && $command_line)
			echo ' done.', $endl;
	}

	// If we're here we can get ready to do it for real.
	$upcontext['is_test'] = false;
	$upcontext['temp_progress'] = 0;
	$_GET['substep'] = 0;

	// If there is nothing it's true anyway!
	if (!$upcontext['can_upgrade_yabbse'] && empty($upcontext['languages']) && empty($upcontext['themes']))
		return true;

	return isset($_GET['forreal']) ? true : false;
}

// Delete the damn thing!
function DeleteUpgrade()
{
	global $command_line, $language, $upcontext, $boarddir;

	$upcontext['sub_template'] = 'upgrade_complete';
	$upcontext['page_title'] = 'Upgrade Complete';

	$endl = $command_line ? "\n" : '<br />' . "\n";

	$changes = array(
		'language' => '\'' . (substr($language, -4) == '.lng' ? substr($language, 0, -4) : $language) . '\'',
		'db_error_send' => '1',
		'upgradeData' => '#remove#',
	);

	// Are we in maintenance mode?
	if (isset($upcontext['user']['main']))
	{
		if ($command_line)
			echo ' * ';
		$upcontext['removed_maintenance'] = true;
		$changes['maintenance'] = $upcontext['user']['main'];
	}

	// Wipe this out...
	$upcontext['user'] = array();

	// Make a backup of Settings.php first as otherwise earlier changes are lost.
	copy($boarddir . '/Settings.php', $boarddir . '/Settings_bak.php');
	changeSettings($changes);

	// Clean any old cache files away.
	clean_cache();

	// Can we delete the file?
	$upcontext['can_delete_script'] = is_writable(dirname(__FILE__)) || is_writable(__FILE__);

	if ($command_line)
	{
		echo $endl;
		echo 'Upgrade Complete!', $endl;
		echo 'Please delete this file as soon as possible for security reasons.', $endl;
		return true;
	}

	// Make sure it says we're done.
	$upcontext['overall_percent'] = 100;
	if (isset($upcontext['step_progress']))
		unset($upcontext['step_progress']);

	$_GET['substep'] = 0;
	return false;
}

function convertSettingsToTheme()
{
	global $db_prefix, $modSettings, $smfFunc;

	$values = array(
		'show_latest_member' => @$GLOBALS['showlatestmember'],
		'show_bbc' => isset($GLOBALS['showyabbcbutt']) ? $GLOBALS['showyabbcbutt'] : @$GLOBALS['showbbcbutt'],
		'show_modify' => @$GLOBALS['showmodify'],
		'show_user_images' => @$GLOBALS['showuserpic'],
		'show_blurb' => @$GLOBALS['showusertext'],
		'show_gender' => @$GLOBALS['showgenderimage'],
		'show_newsfader' => @$GLOBALS['shownewsfader'],
		'display_recent_bar' => @$GLOBALS['Show_RecentBar'],
		'show_member_bar' => @$GLOBALS['Show_MemberBar'],
		'linktree_link' => @$GLOBALS['curposlinks'],
		'show_profile_buttons' => @$GLOBALS['profilebutton'],
		'show_mark_read' => @$GLOBALS['showmarkread'],
		'show_board_desc' => @$GLOBALS['ShowBDescrip'],
		'newsfader_time' => @$GLOBALS['fadertime'],
		'use_image_buttons' => empty($GLOBALS['MenuType']) ? 1 : 0,
		'enable_news' => @$GLOBALS['enable_news'],
		'linktree_inline' => @$modSettings['enableInlineLinks'],
		'return_to_post' => @$modSettings['returnToPost'],
	);

	$themeData = array();
	foreach ($values as $variable => $value)
	{
		if (!isset($value) || $value === null)
			$value = 0;

		$themeData[] = array(0, 1, '\'' . $variable . '\'', '\'' . $value . '\'');
	}
	if (!empty($themeData))
	{
		$smfFunc['db_insert']('ignore',
			"{$db_prefix}themes",
			array('id_member', 'id_theme', 'variable', 'value'),
			$themeData,
			array('id_member', 'id_theme', 'variable')
		);
	}
}

// This function only works with MySQL but that's fine as it is only used for v1.0.
function convertSettingstoOptions()
{
	global $db_prefix, $modSettings, $smfFunc;

	// Format: new_setting -> old_setting_name.
	$values = array(
		'calendar_start_day' => 'cal_startmonday',
		'view_newest_first' => 'viewNewestFirst',
		'view_newest_pm_first' => 'viewNewestFirst',
	);

	foreach ($values as $variable => $value)
	{
		if (empty($modSettings[$value[0]]))
			continue;

		$smfFunc['db_query']('', "
			INSERT IGNORE INTO {$db_prefix}themes
				(id_member, id_theme, variable, value)
			SELECT id_member, 1, '$variable', '" . $modSettings[$value[0]] . "'
			FROM {$db_prefix}members", __FILE__, __LINE__);

		$smfFunc['db_query']('', "
			INSERT IGNORE INTO {$db_prefix}themes
				(id_member, id_theme, variable, value)
			VALUES (-1, 1, '$variable', '" . $modSettings[$value[0]] . "')", __FILE__, __LINE__);
	}
}

function changeSettings($config_vars)
{
	global $boarddir;

	$settingsArray = file($boarddir . '/Settings_bak.php');

	if (count($settingsArray) == 1)
		$settingsArray = preg_split('~[\r\n]~', $settingsArray[0]);

	for ($i = 0, $n = count($settingsArray); $i < $n; $i++)
	{
		// Don't trim or bother with it if it's not a variable.
		if (substr($settingsArray[$i], 0, 1) != '$')
			continue;

		$settingsArray[$i] = trim($settingsArray[$i]) . "\n";

		foreach ($config_vars as $var => $val)
		{
			if (isset($settingsArray[$i]) && strncasecmp($settingsArray[$i], '$' . $var, 1 + strlen($var)) == 0)
			{
				if ($val == '#remove#')
					unset($settingsArray[$i]);
				else
				{
					$comment = strstr(substr($settingsArray[$i], strpos($settingsArray[$i], ';')), '#');
					$settingsArray[$i] = '$' . $var . ' = ' . $val . ';' . ($comment != '' ? "\t\t" . $comment : "\n");
				}

				unset($config_vars[$var]);
			}
		}

		if (isset($settingsArray[$i]))
		{
			if (trim(substr($settingsArray[$i], 0, 2)) == '?' . '>')
				$end = $i;
		}
	}

	// Assume end-of-file if the end wasn't found.
	if (empty($end) || $end < 10)
		$end = count($settingsArray) - 1;

	if (!empty($config_vars))
	{
		$settingsArray[$end++] = '';
		foreach ($config_vars as $var => $val)
			$settingsArray[$end++] = '$' . $var . ' = ' . $val . ';' . "\n";
	}
	// This should be the last line and even last bytes of the file.
	$settingsArray[$end] = '?' . '>';

	// Blank out the file - done to fix a oddity with some servers.
	$fp = fopen($boarddir . '/Settings.php', 'w');
	fclose($fp);

	$fp = fopen($boarddir . '/Settings.php', 'r+');
	for ($i = 0; $i < $end; $i++)
	{
		if (isset($settingsArray[$i]))
			fwrite($fp, strtr($settingsArray[$i], "\r", ''));
	}
	fwrite($fp, rtrim($settingsArray[$i]));
	fclose($fp);
}

function php_version_check()
{
	$minver = explode('.', $GLOBALS['required_php_version']);
	$curver = explode('.', PHP_VERSION);

	return !(($curver[0] <= $minver[0]) && ($curver[1] <= $minver[1]) && ($curver[1] <= $minver[1]) && ($curver[2][0] < $minver[2][0]));
}

function db_version_check()
{
	global $db_type, $databases;

	$curver = eval($databases[$db_type]['version_check']);
	$curver = preg_replace('~\-.+?$~', '', $curver);

	return version_compare($databases[$db_type]['version'], $curver) <= 0;
}

function getMemberGroups()
{
	global $db_prefix, $smfFunc;
	static $member_groups = array();

	if (!empty($member_groups))
		return $member_groups;

	$request = $smfFunc['db_query']('', "
		SELECT group_name, id_group
		FROM {$db_prefix}membergroups
		WHERE id_group = 1 OR id_group > 7", false, false);
	if ($request === false)
	{
		$request = $smfFunc['db_query']('', "
			SELECT membergroup, id_group
			FROM {$db_prefix}membergroups
			WHERE id_group = 1 OR id_group > 7", false, false);
	}
	while ($row = $smfFunc['db_fetch_row']($request))
		$member_groups[trim($row[0])] = $row[1];
	$smfFunc['db_free_result']($request);

	return $member_groups;
}

function ip2range($fullip)
{
	$ip_parts = explode('.', $fullip);
	if (count($ip_parts) != 4)
		return array();
	$ip_array = array();
	for ($i = 0; $i < 4; $i++)
	{
		if ($ip_parts[$i] == '*')
			$ip_array[$i] = array('low' => '0', 'high' => '255');
		elseif (preg_match('/^(\d{1,3})\-(\d{1,3})$/', $ip_parts[$i], $range))
			$ip_array[$i] = array('low' => $range[1], 'high' => $range[2]);
		elseif (is_numeric($ip_parts[$i]))
			$ip_array[$i] = array('low' => $ip_parts[$i], 'high' => $ip_parts[$i]);
	}
	if (count($ip_array) == 4)
		return $ip_array;
	else
		return array();
}



function fixRelativePath($path)
{
	global $install_path;

	// Fix the . at the start, clear any duplicate slashes, and fix any trailing slash...
	return addslashes(preg_replace(array('~^\.([/\\\]|$)~', '~[/]+~', '~[\\\]+~', '~[/\\\]$~'), array($install_path . '$1', '/', '\\', ''), $path));
}

function parse_sql($filename)
{
	global $db_prefix, $boarddir, $boardurl, $command_line, $file_steps, $step_progress, $custom_warning;
	global $upcontext, $support_js, $is_debug, $smfFunc, $db_connection;

/*
	Failure allowed on:
		- INSERT INTO but not INSERT IGNORE INTO.
		- UPDATE IGNORE but not UPDATE.
		- ALTER TABLE and ALTER IGNORE TABLE.
		- DROP TABLE.
	Yes, I realize that this is a bit confusing... maybe it should be done differently?

	If a comment...
		- begins with --- it is to be output, with a break only in debug mode. (and say successful\n\n if there was one before.)
		- begins with ---# it is a debugging statement, no break - only shown at all in debug.
		- is only ---#, it is "done." and then a break - only shown in debug.
		- begins with ---{ it is a code block terminating at ---}.

	Every block of between "--- ..."s is a step.  Every "---#" section represents a substep.

	Replaces the following variables:
		- {$boarddir}
		- {$boardurl}
		- {$db_prefix}
*/

	// Our custom error handler - does nothing but does stop public errors from XML!
	function sql_error_handler($errno, $errstr, $errfile, $errline)
	{
		global $support_js;

		if ($support_js)
			return true;
		else
			echo 'Error: ' . $errstr . ' File: ' . $errfile . ' Line: ' . $errline;
	}

	// Make our own error handler.
	set_error_handler('sql_error_handler');

	$endl = $command_line ? "\n" : '<br />' . "\n";

	$lines = file($filename);

	$current_type = 'sql';
	$current_data = '';
	$substep = 0;
	$last_step = '';

	// Make sure all newly created tables will have the proper characters set.
	if (isset($db_character_set) && $db_character_set === 'utf8')
		$lines = str_replace(') TYPE=MyISAM;', ') TYPE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;', $lines);

	// Count the total number of steps within this file - for progress.
	$file_steps = substr_count(implode('', $lines), '---#');
	$upcontext['total_items'] = substr_count(implode('', $lines), '--- ');
	$upcontext['debug_items'] = $file_steps;
	$upcontext['current_item_num'] = 0;
	$upcontext['current_item_name'] = '';
	$upcontext['current_debug_item_num'] = 0;
	$upcontext['current_debug_item_name'] = '';
	// This array keeps a record of what we've done incase java is dead...
	$upcontext['actioned_items'] = array();

	$done_something = false;

	foreach ($lines as $line_number => $line)
	{
		$do_current = $substep >= $_GET['substep'];
		

		// Get rid of any comments in the beginning of the line...
		if (substr(trim($line), 0, 2) === '/*')
			$line = preg_replace('~/\*.+?\*/~', '', $line);

		// Always flush.  Flush, flush, flush.  Flush, flush, flush, flush!  FLUSH!
		if ($is_debug && !$support_js)
			flush();

		if (trim($line) === '')
			continue;

		if (trim(substr($line, 0, 3)) === '---')
		{
			$type = substr($line, 3, 1);

			// An error??
			if (trim($current_data) != '' && $type !== '}')
			{
				$upcontext['error_message'] = 'Error in upgrade script - line ' . $line_number . '!' . $endl;
				if ($command_line)
					echo $upcontext['error_message'];
			}

			if ($type == ' ')
			{
				if (!$support_js && $do_current && $_GET['substep'] != 0 && $command_line)
				{
					echo ' Successful.', $endl;
					flush();
				}

				$last_step = htmlspecialchars(rtrim(substr($line, 4)));
				$upcontext['current_item_num']++;
				$upcontext['current_item_name'] = $last_step;

				if ($do_current)
				{
					$upcontext['actioned_items'][] = $last_step;
					if ($command_line)
						echo ' * ';
				}
			}
			elseif ($type == '#')
			{
				$upcontext['step_progress'] += (100 / $upcontext['file_count']) / $file_steps;

				$upcontext['current_debug_item_num']++;
				if (trim($line) != '---#')
				{
					$upcontext['current_debug_item_name'] = htmlspecialchars(rtrim(substr($line, 4)));

					// Have we already done something?
					if (isset($_GET['xml']) && $done_something)
						return $upcontext['current_debug_item_num'] >= $upcontext['debug_items'] ? true : false;
				}

				if ($do_current)
				{
					if (trim($line) == '---#' && $command_line)
						echo ' done.', $endl;
					elseif ($command_line)
						echo ' +++ ', rtrim(substr($line, 4));
					elseif (trim($line) != '---#')
					{
						if ($is_debug)
							$upcontext['actioned_items'][] = htmlspecialchars(rtrim(substr($line, 4)));
					}
				}

				if ($substep < $_GET['substep'] && $substep + 1 >= $_GET['substep'])
				{
					if ($command_line)
						echo ' * ';
					else
						$upcontext['actioned_items'][] = $last_step;
				}

				// Small step!
				nextSubstep(++$substep);
			}
			elseif ($type == '{')
				$current_type = 'code';
			elseif ($type == '}')
			{
				$current_type = 'sql';

				if (!$do_current)
				{
					$current_data = '';
					continue;
				}

				if (eval('global $db_prefix, $modSettings; ' . $current_data) === false)
				{
					$upcontext['error_message'] = 'Error in upgrade script ' . basename($filename) . ' on line ' . $line_number . '!' . $endl;
					if ($command_line)
						echo $upcontext['error_message'];
				}

				// Done with code!
				$current_data = '';
				$done_something = true;
			}

			continue;
		}

		$current_data .= $line;
		if ((!$support_js || isset($_GET['xml'])) && substr(rtrim($current_data), -1) === ';' && $current_type === 'sql')
		{
			if (!$do_current)
			{
				$current_data = '';
				continue;
			}

			$current_data = strtr(substr(rtrim($current_data), 0, -1), array('{$db_prefix}' => $db_prefix, '{$boarddir}' => $boarddir, '{$sboarddir}' => addslashes($boarddir), '{$boardurl}' => $boardurl));

			upgrade_query($current_data);
			// !!! This will be how it kinda does it once mysql all stripped out - needed for postgre (etc).
			/*
			$result = $smfFunc['db_query']('', $current_data, false, false);
			// Went wrong?
			if (!$result)
			{
				// Bit of a bodge - do we want the error?
				if (!empty($upcontext['return_error']))
				{
					$upcontext['error_message'] = $smfFunc['db_error']($db_connection);
					return false;
				}
			}*/

			$current_data = '';
			$done_something = true;
		}
		// If this is xml based and we're just getting the item name then that's grand.
		elseif ($support_js && !isset($_GET['xml']) && $upcontext['current_debug_item_name'] != '')
			return false;

		// Clean up by cleaning any step info.
		$step_progress = array();
		$custom_warning = '';
	}

	// Put back the error handler.
	restore_error_handler();

	if ($command_line)
	{
		echo " Successful.\n";
		flush();
	}

	$_GET['substep'] = 0;
	return true;
}

function upgrade_query($string, $unbuffered = false)
{
	global $db_connection, $db_server, $db_user, $db_passwd, $command_line, $upcontext, $upgradeurl;

	// Get the query result!
	$result = $unbuffered ? mysql_unbuffered_query($string) : mysql_query($string);

	// Failure?!
	if ($result !== false)
		return $result;

	$mysql_error = mysql_error($db_connection);
	$mysql_errno = mysql_errno($db_connection);
	$error_query = in_array(substr(trim($string), 0, 11), array('INSERT INTO', 'UPDATE IGNO', 'ALTER TABLE', 'DROP TABLE ', 'ALTER IGNOR'));

	// Error numbers:
	//    1016: Can't open file '....MYI'
	//    1050: Table already exists.
	//    1054: Unknown column name.
	//    1060: Duplicate column name.
	//    1061: Duplicate key name.
	//    1062: Duplicate entry for unique key.
	//    1068: Multiple primary keys.
	//    1072: Key column '%s' doesn't exist in table.
	//    1091: Can't drop key, doesn't exist.
	//    1146: Table doesn't exist.
	//    2013: Lost connection to server during query.

	if ($mysql_errno == 1016)
	{
		if (preg_match('~\'([^\.\']+)~', $mysql_error, $match) != 0 && !empty($match[1]))
			mysql_query("
				REPAIR TABLE `$match[1]`");

		$result = mysql_query($string);
		if ($result !== false)
			return $result;
	}
	elseif ($mysql_errno == 2013)
	{
		$db_connection = mysql_connect($db_server, $db_user, $db_passwd);
		mysql_select_db($db_name, $db_connection);

		if ($db_connection)
		{
			$result = mysql_query($string);

			if ($result !== false)
				return $result;
		}
	}
	// Duplicate column name... should be okay ;).
	elseif (in_array($mysql_errno, array(1060, 1061, 1068, 1091)))
		return false;
	// Duplicate insert... make sure it's the proper type of query ;).
	elseif (in_array($mysql_errno, array(1054, 1062, 1146)) && $error_query)
		return false;
	// Creating an index on a non-existent column.
	elseif ($mysql_errno == 1072)
		return false;
	elseif ($mysql_errno == 1050 && substr(trim($string), 0, 12) == 'RENAME TABLE')
		return false;

	// Get the query string so we pass everything.
	$query_string = '';
	foreach ($_GET as $k => $v)
		$query_string .= ';' . $k . '=' . $v;
	if (strlen($query_string) != 0)
		$query_string = '?' . substr($query_string, 1);

	if ($command_line)
	{
		echo 'Unsuccessful!  MySQL error message:', "\n", mysql_error(), "\n";
		die;
	}

	// Bit of a bodge - do we want the error?
	if (!empty($upcontext['return_error']))
	{
		$upcontext['error_message'] = $mysql_error;
		return false;
	}

	echo '
			<b>Unsuccessful!</b><br />

			<div style="margin: 2ex;">
				This query:
				<blockquote><tt>' . nl2br(htmlspecialchars(trim($string))) . ';</tt></blockquote>

				Caused the error:
				<blockquote>' . nl2br(htmlspecialchars($mysql_error)) . '</blockquote>
			</div>

			<form action="', $upgradeurl, $query_string, '" method="post">
				<input type="submit" value="Try again" />
			</form>
		</div>';

	upgradeExit();
}

// This performs a table alter, but does it unbuffered so the script can time out professionally.
function protected_alter($change, $substep)
{
	global $db_prefix, $smfFunc;

	// Firstly, check whether the current index/column exists.
	$found = false;
	if ($change['type'] === 'column')
	{
		$request = upgrade_query("
			SHOW COLUMNS
			FROM {$db_prefix}$change[table]");
		if ($request !== false)
		{
			while ($row = $smfFunc['db_fetch_row']($request))
				$found |= $row[0] === $change['name'];
			$smfFunc['db_free_result']($request);
		}
	}
	elseif ($change['type'] === 'index')
	{
		$request = upgrade_query("
			SHOW INDEX
			FROM {$db_prefix}$change[table]");
		if ($request !== false)
		{
			$cur_index = array();

			while ($row = $smfFunc['db_fetch_assoc']($request))
				if ($row['Key_name'] === $change['name'])
					$cur_index[(int) $row['Seq_in_index']] = $row['Column_name'];

			ksort($cur_index, SORT_NUMERIC);
			$found = array_values($cur_index) === $change['target_columns'];

			$smfFunc['db_free_result']($request);
		}
	}

	// If we're trying to add and it's added, we're done.
	if ($found && in_array($change['method'], array('add', 'change')))
		return true;
	// Otherwise if we're removing and it wasn't found we're also done.
	elseif (!$found && in_array($change['method'], array('remove', 'change_remove')))
		return true;

	// Not found it yet? Bummer! How about we see if we're currently doing it?
	$running = false;
	$found = false;
	while (1 == 1)
	{
		$request = upgrade_query("
			SHOW FULL PROCESSLIST");
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if (strpos($row['Info'], "ALTER TABLE {$db_prefix}$change[table]") !== false && strpos($row['Info'], $change['text']) !== false)
				$found = true;
		}

		// Can't find it? Then we need to run it fools!
		if (!$found && !$running)
		{
			$smfFunc['db_free_result']($request);

			$success = upgrade_query("
				ALTER TABLE {$db_prefix}$change[table]
				$change[text]", true) !== false;
			
			if (!$success)
				return false;

			// Return
			$running = true;
		}
		// What if we've not found it, but we'd ran it already? Must of completed.
		elseif (!$found)
		{
			$smfFunc['db_free_result']($request);
			return true;
		}

		// Pause execution for a sec or three.
		sleep(3);

		// Can never be too well protected.
		nextSubstep($substep);
	}

	// Protect it.
	nextSubstep($substep);
}

// Alter a text column definition preserving its character set.
function textfield_alter($change, $substep)
{
	global $db_prefix, $databases, $db_type, $smfFunc;

	// Versions of MySQL < 4.1 wouldn't benefit from character set detection.
	if (empty($databases[$db_type]['utf8_support']) || version_compare($databases[$db_type]['utf8_version'], eval($databases[$db_type]['utf8_version_check'])) > 0)
	{
		$column_fix = true;
		$null_fix = !$change['null_allowed'];
	}
	else
	{
		$request = $smfFunc['db_query']('', "
			SHOW FULL COLUMNS
			FROM {$db_prefix}$change[table]
			LIKE '$change[column]'", false, false);
		if ($smfFunc['db_num_rows']($request) === 0)
			die('Unable to find column ' . $change['column'] . ' inside table ' . $db_prefix . $change['table']);
		$table_row = $smfFunc['db_fetch_assoc']($request);
		$smfFunc['db_free_result']($request);

		// If something of the current column definition is different, fix it.
		$column_fix = $table_row['Type'] !== $change['type'] || (strtolower($table_row['Null']) === 'yes') !== $change['null_allowed'] || ($table_row['Default'] == NULL) !== !isset($change['default']) || (isset($change['default']) && $change['default'] !== $table_row['Default']);

		// Columns that previously allowed null, need to be converted first.
		$null_fix = strtolower($table_row['Null']) === 'yes' && !$change['null_allowed'];

		// Get the character set that goes with the collation of the column.
		if ($column_fix && !empty($table_row['Collation']))
		{
			$request = $smfFunc['db_query']('', "
				SHOW COLLATION
				LIKE '$table_row[Collation]'", false, false);
			// No results? Just forget it all together.
			if ($smfFunc['db_num_rows']($request) === 0)
				unset($table_row['Collation']);
			else
				$collation_info = $smfFunc['db_fetch_assoc']($request);
			$smfFunc['db_free_result']($request);
		}
	}

	if ($column_fix)
	{
		// Make sure there are no NULL's left.
		if ($null_fix)
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}$change[table]
				SET $change[column] = '" . (isset($change['default']) ? $smfFunc['db_escape_string']($change['default']) : '') . "'
				WHERE $change[column] IS NULL", false, false);

		// Do the actual alteration.
		$smfFunc['db_query']('', "
			ALTER TABLE {$db_prefix}$change[table]
			CHANGE COLUMN $change[column] $change[column] $change[type]" . (isset($collation_info['Charset']) ? " CHARACTER SET $collation_info[Charset] COLLATE $collation_info[Collation]" : '') . ($change['null_allowed'] ? '' : ' NOT NULL') . (isset($change['default']) ? " default '" . $smfFunc['db_escape_string']($change['default']) . "'" : ''), false, false);
	}
	nextSubstep($substep);
}

function nextSubstep($substep)
{
	global $start_time, $timeLimitThreshold, $command_line, $file_steps, $modSettings, $custom_warning;
	global $step_progress, $is_debug, $upcontext;

	if ($_GET['substep'] < $substep)
		$_GET['substep'] = $substep;

	if ($command_line)
	{
		if (time() - $start_time > 1 && empty($is_debug))
		{
			echo '.';
			$start_time = time();
		}
		return;
	}

	@set_time_limit(300);
	if (function_exists('apache_reset_timeout'))
		apache_reset_timeout();

	if (time() - $start_time <= $timeLimitThreshold)
		return;

	// Do we have some custom step progress stuff?
	if (!empty($step_progress))
	{
		$upcontext['substep_progress'] = 0;
		$upcontext['substep_progress_name'] = $step_progress['name'];
		if ($step_progress['current'] > $step_progress['total'])
			$upcontext['substep_progress'] = 99.9;
		else
			$upcontext['substep_progress'] = ($step_progress['current'] / $step_progress['total']) * 100;

		// Make it nicely rounded.
		$upcontext['substep_progress'] = round($upcontext['substep_progress'], 1);
	}

	// If this is XML we just exit right away!
	if (isset($_GET['xml']))
		return upgradeExit();

	// We're going to pause after this!
	$upcontext['pause'] = true;

	$upcontext['query_string'] = '';
	foreach ($_GET as $k => $v)
	{
		if ($k != 'data' && $k != 'substep' && $k != 'step')
			$upcontext['query_string'] .= ';' . $k . '=' . $v;
	}

	// Custom warning?
	if (!empty($custom_warning))
		$upcontext['custom_warning'] = $custom_warning;

	upgradeExit();
}

function cmdStep0()
{
	global $boarddir, $sourcedir, $db_prefix, $language, $modSettings, $start_time, $cachedir, $databases, $db_type;
	$start_time = time();

	ob_end_clean();
	ob_implicit_flush(true);
	@set_time_limit(0);

	if (!isset($_SERVER['argv']))
		$_SERVER['argv'] = array();
	$_GET['maint'] = 1;

	foreach ($_SERVER['argv'] as $i => $arg)
	{
		if (preg_match('~^--language=(.+)$~', $arg, $match) != 0)
			$_GET['lang'] = $match[1];
		elseif (preg_match('~^--path=(.+)$~', $arg) != 0)
			continue;
		elseif ($arg == '--no-maintenance')
			$_GET['maint'] = 0;
		elseif ($arg == '--debug')
			$_GET['debug'] = 1;
		elseif ($arg == '--backup')
			$_GET['backup'] = 1;
		elseif ($arg == '--template' && (file_exists($boarddir . '/template.php') || file_exists($boarddir . '/template.html') && !file_exists($boarddir . '/Themes/converted')))
			$_GET['conv'] = 1;
		elseif ($i != 0)
		{
			echo 'SMF Command-line Upgrader
Usage: /path/to/php -f ' . basename(__FILE__) . ' -- [OPTION]...

    --language=LANG         Reset the forum\'s language to LANG.
    --no-maintenance        Don\'t put the forum into maintenance mode.
    --debug                 Output debugging information.
    --backup                Create backups of tables with "backup_" prefix.';
			if (file_exists($boarddir . '/template.php') || file_exists($boarddir . '/template.html') && !file_exists($boarddir . '/Themes/converted'))
				echo '
    --template              Convert the YaBB SE template file.';
			echo "\n";
			exit;
		}
	}

	if (!php_version_check())
		print_error('Error: PHP ' . PHP_VERSION . ' does not match version requirements.', true);
	if (!db_version_check())
		print_error('Error: ' . $databases[$db_type]['name'] . ' ' . $databases[$db_type]['version'] . ' does not match minimum requirements.', true);

	if (!empty($databases[$db_type]['alter_support']) && $smfFunc['db_query']('alter_boards', "ALTER TABLE {$db_prefix}boards ORDER BY id_board", false, false) === false)
		print_error('Error: The ' . $databases[$db_type]['name'] . ' account in Settings.php does not have sufficient privileges.', true);

	$check = @file_exists($boarddir . '/Themes/default/index.template.php')
		&& @file_exists($sourcedir . '/QueryString.php')
		&& @file_exists($sourcedir . '/ManageBoards.php');
	if (!$check && !isset($modSettings['smfVersion']))
		print_error('Error: Some files are missing or out-of-date.', true);

	// Do a quick version spot check.
	$temp = substr(@implode('', @file($boarddir . '/index.php')), 0, 4096);
	preg_match('~\*\s*Software\s+Version:\s+SMF\s+(.+?)[\s]{2}~i', $temp, $match);
	if (empty($match[1]) || $match[1] != SMF_VERSION)
		print_error('Error: Some files have not yet been updated properly.');

	// Make sure Settings.php is writable.
	if (!is_writable($boarddir . '/Settings.php'))
		@chmod($boarddir . '/Settings.php', 0777);
	if (!is_writable($boarddir . '/Settings.php'))
		print_error('Error: Unable to obtain write access to "Settings.php".');

	// Make sure Settings.php is writable.
	if (!is_writable($boarddir . '/Settings_bak.php'))
		@chmod($boarddir . '/Settings_bak.php', 0777);
	if (!is_writable($boarddir . '/Settings_bak.php'))
		print_error('Error: Unable to obtain write access to "Settings_bak.php".');

	if (isset($modSettings['agreement']) && (!is_writable($boarddir) || file_exists($boarddir . '/agreement.txt')) && !is_writable($boarddir . '/agreement.txt'))
		print_error('Error: Unable to obtain write access to "agreement.txt".');
	elseif (isset($modSettings['agreement']))
	{
		$fp = fopen($boarddir . '/agreement.txt', 'w');
		fwrite($fp, $modSettings['agreement']);
		fclose($fp);
	}

	// Make sure Themes is writable.
	if (!is_writable($boarddir . '/Themes'))
		@chmod($boarddir . '/Themes', 0777);

	if (!is_writable($boarddir . '/Themes') && !isset($modSettings['smfVersion']))
		print_error('Error: Unable to obtain write access to "Themes".');

	// Make sure cache directory exists and is writable!
	$cachedir_temp = empty($cachedir) ? $boarddir . '/cache' : $cachedir;
	if (!file_exists($cachedir_temp))
		@mkdir($cachedir_temp);

	if (!is_writable($cachedir_temp))
		@chmod($cachedir_temp, 0777);

	if (!is_writable($cachedir_temp))
		print_error('Error: Unable to obtain write access to "cache".');

	if (!file_exists($boarddir . '/Themes/default/languages/index.' . basename($language, '.lng') . '.php') && !isset($modSettings['smfVersion']) && !isset($_GET['lang']))
		print_error('Error: Unable to find language files!');
	else
	{
		$temp = substr(@implode('', @file($boarddir . '/Themes/default/languages/index.' . (isset($_GET['lang']) ? $_GET['lang'] : basename($language, '.lng')) . '.php')), 0, 4096);
		preg_match('~(?://|/\*)\s*Version:\s+(.+?);\s*index(?:[\s]{2}|\*/)~i', $temp, $match);

		if (empty($match[1]) || $match[1] != SMF_LANG_VERSION)
			print_error('Error: Language files out of date.');
	}

	return doStep1();
}

function print_error($message, $fatal = false)
{
	static $fp = null;

	if ($fp === null)
		$fp = fopen('php://stderr', 'wb');

	fwrite($fp, $message . "\n");

	if ($fatal)
		exit;
}

function throw_error($message)
{
	global $upcontext;

	$upcontext['error_msg'] = $message;
	$upcontext['sub_template'] = 'error_message';
}

// Check files are writable - make them writable if necessary...
function makeFilesWritable(&$files)
{
	global $upcontext, $boarddir;

	if (empty($files))
		return true;

	$failure = false;
	// On linux, it's easy - just use is_writable!
	if (substr(__FILE__, 1, 2) != ':\\')
	{
		foreach ($files as $k => $file)
		{
			if (!is_writable($file))
			{
				@chmod($file, 0755);

				// Well, 755 hopefully worked... if not, try 777.
				if (!is_writable($file) && !@chmod($file, 0777))
					$failure = true;
				// Otherwise remove it as it's good!
				else
					unset($files[$k]);
			}
			else
				unset($files[$k]);
		}
	}
	// Windows is trickier.  Let's try opening for r+...
	else
	{
		foreach ($files as $k => $file)
		{
			// Folders can't be opened for write... but the index.php in them can ;).
			if (is_dir($file))
				$file .= '/index.php';

			// Funny enough, chmod actually does do something on windows - it removes the read only attribute.
			@chmod($file, 0777);
			$fp = @fopen($file, 'r+');

			// Hmm, okay, try just for write in that case...
			if (!$fp)
				$fp = @fopen($file, 'w');

			if (!$fp)
				$failure = true;
			else
				unset($files[$k]);
			@fclose($fp);
		}
	}

	if (empty($files))
		return true;

	if (!isset($_SERVER))
		return !$failure;

	// What still needs to be done?
	$upcontext['chmod']['files'] = $files;

	// If it's windows it's a mess...
	if ($failure && substr(__FILE__, 1, 2) == ':\\')
	{
		$upcontext['chmod']['ftp_error'] = 'total_mess';

		return false;
	}
	// We're going to have to use... FTP!
	elseif ($failure)
	{
		// Load any session data we might have...
		if (!isset($_POST['ftp_username']) && isset($_SESSION['installer_temp_ftp']))
		{
			$upcontext['chmod']['server'] = $_SESSION['installer_temp_ftp']['server'];
			$upcontext['chmod']['port'] = $_SESSION['installer_temp_ftp']['port'];
			$upcontext['chmod']['username'] = $_SESSION['installer_temp_ftp']['username'];
			$upcontext['chmod']['password'] = $_SESSION['installer_temp_ftp']['password'];
			$upcontext['chmod']['path'] = $_SESSION['installer_temp_ftp']['path'];
		}
		// Or have we submitted?
		elseif (isset($_POST['ftp_username']))
		{
			$upcontext['chmod']['server'] = $_POST['ftp_server'];
			$upcontext['chmod']['port'] = $_POST['ftp_port'];
			$upcontext['chmod']['username'] = $_POST['ftp_username'];
			$upcontext['chmod']['password'] = $_POST['ftp_password'];
			$upcontext['chmod']['path'] = $_POST['ftp_path'];
		}

		if (isset($upcontext['chmod']['username']))
		{
			$ftp = new ftp_connection($upcontext['chmod']['server'], $upcontext['chmod']['port'], $upcontext['chmod']['username'], $upcontext['chmod']['password']);

			if ($ftp->error === false)
			{
				// Try it without /home/abc just in case they messed up.
				if (!$ftp->chdir($upcontext['chmod']['path']))
				{
					$upcontext['chmod']['ftp_error'] = $ftp->last_message;
					$ftp->chdir(preg_replace('~^/home[2]?/[^/]+?~', '', $upcontext['chmod']['path']));
				}
			}
		}

		if (!isset($ftp) || $ftp->error !== false)
		{
			if (!isset($ftp))
				$ftp = new ftp_connection(null);
			// Save the error so we can mess with listing...
			elseif ($ftp->error !== false && !isset($upcontext['chmod']['ftp_error']))
				$upcontext['chmod']['ftp_error'] = $ftp->last_message === null ? '' : $ftp->last_message;

			list ($username, $detect_path, $found_path) = $ftp->detect_path(dirname(__FILE__));

			if ($found_path || !isset($upcontext['chmod']['path']))
				$upcontext['chmod']['path'] = $detect_path;

			if (!isset($upcontext['chmod']['username']))
				$upcontext['chmod']['username'] = $username;

			return false;
		}
		else
		{
			// We want to do a relative path for FTP.
			if (!in_array($upcontext['chmod']['path'], array('', '/')))
			{
				$ftp_root = strtr($boarddir, array($upcontext['chmod']['path'] => ''));
				if (substr($ftp_root, -1) == '/' && ($upcontext['chmod']['path'] == '' || substr($upcontext['chmod']['path'], 0, 1) == '/'))
				$ftp_root = substr($ftp_root, 0, -1);
			}
			else
				$ftp_root = $boarddir;

			// Save the info for next time!
			$_SESSION['installer_temp_ftp'] = array(
				'server' => $upcontext['chmod']['server'],
				'port' => $upcontext['chmod']['port'],
				'username' => $upcontext['chmod']['username'],
				'password' => $upcontext['chmod']['password'],
				'path' => $upcontext['chmod']['path'],
				'root' => $ftp_root,
			);

			foreach ($files as $k => $file)
			{
				if (!is_writable($file))
					$ftp->chmod($file, 0755);
				if (!is_writable($file))
					$ftp->chmod($file, 0777);

				// Assuming that didn't work calculate the path without the boarddir.
				if (!is_writable($file))
				{
					if (strpos($file, $boarddir) === 0)
					{
						$ftp_file = strtr($file, array($_SESSION['installer_temp_ftp']['root'] => ''));
						$ftp->chmod($ftp_file, 0755);
						if (!is_writable($file))
							$ftp->chmod($ftp_file, 0777);
						// Sometimes an extra slash can help...
						$ftp_file = '/' . $ftp_file;
						if (!is_writable($file))
							$ftp->chmod($ftp_file, 0755);
						if (!is_writable($file))
							$ftp->chmod($ftp_file, 0777);
					}
				}

				if (is_writable($file))
					unset($files[$k]);
			}

			$ftp->close();
		}
	}

	if (empty($files))
		return true;

	// What remains?
	$upcontext['chmod']['files'] = $files;

	return false;
}

/******************************************************************************
******************* Templates are below this point ****************************
******************************************************************************/

// This is what is displayed if there's any chmod to be done. If not it returns nothing...
function template_chmod()
{
	global $upcontext, $upgradeurl;

	// Don't call me twice!
	if (!empty($upcontext['chmod_called']))
		return;

	$upcontext['chmod_called'] = true;

	// Nothing?
	if (empty($upcontext['chmod']['files']) && empty($upcontext['chmod']['ftp_error']))
		return;

	//!!! Temporary!
	$txt['error_ftp_no_connect'] = 'Unable to connect to FTP server with this combination of details.';
	$txt['ftp_login'] = 'Your FTP connection information';
	$txt['ftp_login_info'] = 'This web installer needs your FTP information in order to automate the installation for you.  Please note that none of this information is saved in your installation, it is just used to setup SMF.';
	$txt['ftp_server'] = 'Server';
	$txt['ftp_server_info'] = 'The address (often localhost) and port for your FTP server.';
	$txt['ftp_port'] = 'Port';
	$txt['ftp_username'] = 'Username';
	$txt['ftp_username_info'] = 'The username to login with. <i>This will not be saved anywhere.</i>';
	$txt['ftp_password'] = 'Password';
	$txt['ftp_password_info'] = 'The password to login with. <i>This will not be saved anywhere.</i>';
	$txt['ftp_path'] = 'Install Path';
	$txt['ftp_path_info'] = 'This is the <i>relative</i> path you use in your FTP client <a href="' . $_SERVER['PHP_SELF'] . '?ftphelp" onclick="window.open(this.href, \'\', \'width=450,height=250\');return false;" target="_blank">(more help)</a>.';
	$txt['ftp_path_found_info'] = 'The path in the box above was automatically detected.';
	$txt['ftp_path_help'] = 'Your FTP path is the path you see when you log in to your FTP client.  It commonly starts with &quot;<tt>www</tt>&quot;, &quot;<tt>public_html</tt>&quot;, or &quot;<tt>httpdocs</tt>&quot; - but it should include the directory SMF is in too, such as &quot;/public_html/forum&quot;.  It is different from your URL and full path.<br /><br />Files in this path may be overwritten, so make sure it\'s correct.';
	$txt['ftp_path_help_close'] = 'Close';
	$txt['ftp_connect'] = 'Connect';

	// Was it a problem with Windows?
	if (!empty($upcontext['chmod']['ftp_error']) && $upcontext['chmod']['ftp_error'] == 'total_mess')
	{
		echo '
			<div class="error_message">
				<div style="color: red;">The following files need to be writable to continue the upgrade. Please ensure the Windows permissions are correctly set to allow this:</div>
				<ul style="margin: 2.5ex; font-family: monospace;">
				<li>' . implode('</li>
				<li>', $upcontext['chmod']['files']). '</li>
			</ul>
			</div>';

		return false;
	}

	echo '
		<div class="panel">
			<h2>Your FTP connection information</h2>
			<h3>The upgrader can fix any issues with file permissions to make upgrading as simple as possible. Simply enter your connection information below or alternatively click <a href="#" onclick="alert(\'The following files needs to be made writable to continue:\\n', implode('\\n', $upcontext['chmod']['files']), '\'); return false;">here</a> for a list of files which need to be changed.</h3>';
	
	if (!empty($upcontext['chmod']['ftp_error']))
		echo '
			<div class="error_message">
				<div style="color: red;">
					The following error was encountered when trying to connect:<br />
					<br />
					<code>', $upcontext['chmod']['ftp_error'], '</code>
				</div>
			</div>
			<br />';
	
	echo '
	<form action="', $upcontext['form_url'], '" method="post">
		<table width="520" cellspacing="0" cellpadding="0" border="0" align="center" style="margin-bottom: 1ex;">
			<tr>
				<td width="26%" valign="top" class="textbox"><label for="ftp_server">', $txt['ftp_server'], ':</label></td>
				<td>
					<div style="float: right; margin-right: 1px;"><label for="ftp_port" class="textbox"><b>', $txt['ftp_port'], ':&nbsp;</b></label> <input type="text" size="3" name="ftp_port" id="ftp_port" value="', isset($upcontext['chmod']['port']) ? $upcontext['chmod']['port'] : '21', '" /></div>
					<input type="text" size="30" name="ftp_server" id="ftp_server" value="', isset($upcontext['chmod']['server']) ? $upcontext['chmod']['server'] : 'localhost', '" style="width: 70%;" />
					<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['ftp_server_info'], '</div>
				</td>
			</tr><tr>
				<td width="26%" valign="top" class="textbox"><label for="ftp_username">', $txt['ftp_username'], ':</label></td>
				<td>
					<input type="text" size="50" name="ftp_username" id="ftp_username" value="', isset($upcontext['chmod']['username']) ? $upcontext['chmod']['username'] : '', '" style="width: 99%;" />
					<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['ftp_username_info'], '</div>
				</td>
			</tr><tr>
				<td width="26%" valign="top" class="textbox"><label for="ftp_password">', $txt['ftp_password'], ':</label></td>
				<td>
					<input type="password" size="50" name="ftp_password" id="ftp_password" style="width: 99%;" />
					<div style="font-size: smaller; margin-bottom: 3ex;">', $txt['ftp_password_info'], '</div>
				</td>
			</tr><tr>
				<td width="26%" valign="top" class="textbox"><label for="ftp_path">', $txt['ftp_path'], ':</label></td>
				<td style="padding-bottom: 1ex;">
					<input type="text" size="50" name="ftp_path" id="ftp_path" value="', isset($upcontext['chmod']['path']) ? $upcontext['chmod']['path'] : '', '" style="width: 99%;" />
					<div style="font-size: smaller; margin-bottom: 2ex;">', !empty($upcontext['chmod']['path']) ? $txt['ftp_path_found_info'] : $txt['ftp_path_info'], '</div>
				</td>
			</tr>
		</table>

		<div align="right" style="margin: 1ex;"><input type="submit" value="', $txt['ftp_connect'], '" /></div>
	</div>';
}

function template_upgrade_above()
{
	global $modSettings, $txt, $smfsite, $settings, $upcontext, $upgradeurl;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>SMF Upgrade Utility</title>
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/script.js"></script>
		<link rel="stylesheet" type="text/css" href="', $smfsite, '/style.css" />
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var smf_scripturl = \'', $upgradeurl, '\';
			var smf_charset = \'', (empty($modSettings['global_character_set']) ? (empty($txt['lang_character_set']) ? 'ISO-8859-1' : $txt['lang_character_set']) : $modSettings['global_character_set']), '\';
			startPercent = ', $upcontext['overall_percent'], ';

			// This function dynamically updates the step progress bar - and overall one as required.
			function updateStepProgress(current, max, overall_weight)
			{
				// What out the actual percent.
				var width = parseInt((current / max) * 100);
				if (document.getElementById(\'step_progress\'))
				{
					document.getElementById(\'step_progress\').style.width = width + "%";
					setInnerHTML(document.getElementById(\'step_text\'), width + "%");
				}
				if (overall_weight && document.getElementById(\'overall_progress\'))
				{
					overall_width = parseInt(startPercent + width * (overall_weight / 100));
					document.getElementById(\'overall_progress\').style.width = overall_width + "%";
					setInnerHTML(document.getElementById(\'overall_text\'), overall_width + "%");
				}
			}
		// ]]></script>
	</head>
	<body>
		<div id="header">
			<a href="http://www.simplemachines.org/" target="_blank"><img src="', $smfsite, '/smflogo.gif" style=" float: right;" alt="Simple Machines" border="0" /></a>
			<div title="Radical Dreamers">SMF Upgrade Utility</div>
		</div>
		<div id="content">
			<table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding-top: 1ex;">
			<tr>
				<td width="180" valign="top" style="padding-right: 10px;">
					<table border="0" cellpadding="8" cellspacing="0" class="tborder" width="170">
						<tr>
							<td class="titlebg">Steps</td>
						</tr>
						<tr>
							<td class="windowbg">';

	foreach ($upcontext['steps'] as $num => $step)
		echo '
						<span class="', $num < $upcontext['current_step'] ? 'stepdone' : ($num == $upcontext['current_step'] ? 'stepcurrent' : 'stepwaiting'), '">Step ', $step[0], ': ', $step[1], '</span><br />';

	echo '
							</td>
						</tr>
						<tr>
							<td class="titlebg">Progress</td>
						</tr>
						<tr>
							<td class="windowbg">
								<div class="smalltext" style="text-align: center; padding: 3px 3px 6px 3px;">Overall Progress:</div>
								<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
									<div id="overall_text" style="padding-top: 1pt; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $upcontext['overall_percent'], '%</div>
									<div id="overall_progress" style="width: ', $upcontext['overall_percent'], '%; height: 12pt; z-index: 1; background-color: lime;">&nbsp;</div>
								</div>';

	if (isset($upcontext['step_progress']))
		echo '
								<div class="smalltext" style="text-align: center; padding: 3px 3px 6px 3px;">Step Progress:</div>
								<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
									<div id="step_text" style="padding-top: 1pt; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $upcontext['step_progress'], '%</div>
									<div id="step_progress" style="width: ', $upcontext['step_progress'], '%; height: 12pt; z-index: 1; background-color: #FFD000;">&nbsp;</div>
								</div>';

	echo '
								<div id="substep_bar_div" class="smalltext" style="display: ', isset($upcontext['substep_progress']) ? '' : 'none', ';">', isset($upcontext['substep_progress_name']) ? trim(strtr($upcontext['substep_progress_name'], array('.' => ''))) : '', ':</div>
								<div id="substep_bar_div2" style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative; display: ', isset($upcontext['substep_progress']) ? '' : 'none', ';">
									<div id="substep_text" style="padding-top: 1pt; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', isset($upcontext['substep_progress']) ? $upcontext['substep_progress'] : '', '%</div>
									<div id="substep_progress" style="width: ', isset($upcontext['substep_progress']) ? $upcontext['substep_progress'] : 0, '%; height: 12pt; z-index: 1; background-color: #EEBAF4;">&nbsp;</div>
								</div>';

	// How long have we been running this?
	$elapsed = time() - $upcontext['started'];
	$mins = (int) ($elapsed / 60);
	$seconds = $elapsed - $mins * 60;
	echo '
								<div class="smalltext" style="padding: 5px; text-align: center;">Time Elapsed:</div>
								<div class="smalltext" style="color: blue; text-align: center;"><span id="mins_elapsed">', $mins, '</span> mins, <span id="secs_elapsed">', $seconds, '</span> seconds.';

	echo '
							</td>
						</tr>
					</table>
				</td>
				<td width="100%" valign="top">
					<div class="panel">
						<h2>', $upcontext['page_title'], '</h2>
						<div style="max-height: 250px; overflow: auto;">';
}

function template_upgrade_below()
{
	global $upcontext;

	if (!empty($upcontext['pause']))
		echo '
			<i>Incomplete.</i><br />

			<h2 style="margin-top: 2ex;">Not quite done yet!</h2>
			<h3>
				This upgrade has been paused to avoid overloading your server.  Don\'t worry, nothing\'s wrong - simply click the <label for="continue">continue button</label> below to keep going.
			</h3>';

	if (!empty($upcontext['custom_warning']))
		echo '
		<div style="margin: 2ex; padding: 2ex; border: 2px dashed #cc3344; color: black; background-color: #ffe4e9;">
			<div style="float: left; width: 2ex; font-size: 2em; color: red;">!!</div>
			<b style="text-decoration: underline;">Note!</b><br />
			<div style="padding-left: 6ex;">', $upcontext['custom_warning'], '</div>
		</div>';

	echo '
		<div align="right" style="margin: 1ex;">';

	if (!empty($upcontext['continue']))
		echo '
				<input type="submit" id="contbutt" name="contbutt" value="Continue" ', $upcontext['continue'] == 2 ? 'disabled="disabled"' : '', '/>';

	echo '
		</div>
			</form>';

	echo '
					</div>
				</td>
			</tr>
		</table>
		</div>';

	// Are we on a pause?
	if (!empty($upcontext['pause']))
	{
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			window.onload = doAutoSubmit;
			var countdown = 3;

			function doAutoSubmit()
			{
				if (countdown == 0)
					document.upform.submit();
				else if (countdown == -1)
					return;

				document.getElementById(\'contbutt\').value = "Continue (" + countdown + ")";
				countdown--;

				setTimeout("doAutoSubmit();", 1000);
			}
		// ]]></script>';
	}

	echo '
	</body>
</html>';
}

function template_xml_above()
{
	global $upcontext;

	echo '<', '?xml version="1.0" encoding="ISO-8859-1"?', '>
	<smf>';

	if (!empty($upcontext['get_data']))
		foreach ($upcontext['get_data'] as $k => $v)
			echo '
		<get key="', $k, '">', $v, '</get>';
}

function template_xml_below()
{
	global $upcontext;

	echo '
		</smf>';
}

function template_error_message()
{
	global $upcontext;

	echo '
	<div class="error_message">
		<div style="color: red;">
			', $upcontext['error_msg'], '
		</div>
		<br />
		<a href="', $_SERVER['PHP_SELF'], '">Click here to try again.</a>
	</div>';
}

function template_welcome_message()
{
	global $upcontext, $modSettings, $upgradeurl, $disable_security, $settings;

	echo '
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/sha1.js"></script>
			<h3>Thank you for choosing to upgrade to SMF ', SMF_VERSION, '. All files appear to be in place, and we\'re ready to proceed.</h3>';

	template_chmod();

	// For large, pre 1.1 RC2 forums give them a warning about the possible impact of this upgrade!
	if ($upcontext['is_large_forum'])
		echo '
		<div style="margin: 2ex; padding: 2ex; border: 2px dashed #cc3344; color: black; background-color: #ffe4e9;">
			<div style="float: left; width: 2ex; font-size: 2em; color: red;">!!</div>
			<b style="text-decoration: underline;">Warning!</b><br />
			<div style="padding-left: 6ex;">
				This upgrade script has detected that your forum contains a lot of data which needs upgrading. This
				process may take quite some time depending on your server and forum size, and for very large forums (~300,000 messages) may take several
				hours to complete.
			</div>
		</div>';

	// Is there someone already doing this?
	if (!empty($upcontext['user']['id']) && (time() - $upcontext['started'] < 72600 || time() - $upcontext['updated'] < 3600))
	{
		$ago = time() - $upcontext['started'];
		if ($ago < 60)
			$ago = $ago . ' seconds';
		elseif ($ago < 3600)
			$ago = (int) ($ago / 60) . ' minutes';
		else
			$ago = (int) ($ago / 3600) . ' hours';

		$active = time() - $upcontext['updated'];
		if ($active < 60)
			$updated = $active . ' seconds';
		elseif ($active < 3600)
			$updated = (int) ($active / 60) . ' minutes';
		else
			$updated = (int) ($active / 3600) . ' hours';

		echo '
		<div style="margin: 2ex; padding: 2ex; border: 2px dashed #cc3344; color: black; background-color: #ffe4e9;">
			<div style="float: left; width: 2ex; font-size: 2em; color: red;">!!</div>
			<b style="text-decoration: underline;">Warning!</b><br />
			<div style="padding-left: 6ex;">
				&quot;', $upcontext['user']['name'], '&quot; has been running the upgrade script for the last ', $ago, ' - and was last active ', $updated, ' ago.';

		if ($active < 600)
			echo '
				We recommend that you do not run this script unless you are sure that ', $upcontext['user']['name'], ' has completed their upgrade.';

		if ($active > $upcontext['inactive_timeout'])
			echo '
				<br /><br />You can choose to either run the upgrade again from the beginning - or alternatively continue from the last step reached during the last upgrade.';
		else
			echo '
				<br /><br />This upgrade script cannot be run until ', $upcontext['user']['name'], ' has been inactive for at least ', round($upcontext['inactive_timeout'] / 60, 1), ' minutes!';

		echo '
			</div>
		</div>';
	}

	echo '
			<form action="', $upcontext['form_url'], '&amp;lang=', $upcontext['language'], '" method="post" name="upform" id="upform" ', empty($upcontext['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $upcontext['rid'] . '\');"' : '', '>
			<b>Admin Login: ', $disable_security ? '(DISABLED)' : '', '</b>
			<h3>For security purposes please login with your admin account to proceed with the upgrade.<h3>
			<table>
				<tr valign="top">
					<td><b ', $disable_security ? 'style="color: gray;"' : '', '>Username:</b></td>
					<td>
						<input type="text" name="user" value="', !empty($upcontext['username']) ? $upcontext['username'] : '', '" ', $disable_security ? 'disabled="disabled"' : '', ' />';

	if (!empty($upcontext['username_incorrect']))
		echo '
						<div class="smalltext" style="color: red;">Username Incorrect</div>';

	echo '
					</td>
				</tr>
				<tr valign="top">
					<td><b ', $disable_security ? 'style="color: gray;"' : '', '>Password:</b></td>
					<td>
						<input type="password" name="passwrd" value="" ', $disable_security ? 'disabled="disabled"' : '', '/>
						<input type="hidden" name="hash_passwrd" value="" />';

	if (!empty($upcontext['password_failed']))
		echo '
						<div class="smalltext" style="color: red;">Password Incorrect</div>';

	echo '
					</td>
				</tr>';

	// Can they continue?
	if (!empty($upcontext['user']['id']) && time() - $upcontext['user']['updated'] > $upcontext['inactive_timeout'])
	{
		echo '
				<tr>
					<td colspan="2">
						<label for="cont"><input type="checkbox" id="cont" name="cont" checked="checked" />Continue from step reached during last execution of upgrade script.</label>
					</td>
				</tr>';		
	}

	echo '
			</table><br />
			<span class="smalltext">
				<b>Note:</b> If necessary the above security check can be bypassed for users who may administrate a server but not have admin rights on the forum. In order the bypass the above check simply open &quot;upgrade.php&quot; in a text editor and replace &quot;$disable_security = 0;&quot; with &quot;$disable_security = 1;&quot; and refresh this page.
			</span>
			<input type="hidden" name="login_attempt" id="login_attempt" value="1" />
			<input type="hidden" name="js_works" id="js_works" value="0" />';				

	// Say we want the continue button!
	$upcontext['continue'] = !empty($upcontext['user']['id']) && time() - $upcontext['user']['updated'] < $upcontext['inactive_timeout'] ? 2 : 1;

	// This defines whether javascript is going to work elsewhere :D
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			if (window.XMLHttpRequest && document.getElementById(\'js_works\'))
			{
				document.getElementById(\'js_works\').value = 1;
			}
		// ]]></script>';
}

function template_upgrade_options()
{
	global $upcontext, $modSettings, $upgradeurl, $disable_security, $settings, $boarddir, $db_prefix, $mmessage, $mtitle;

	echo '
			<h3>Before the upgrade get\'s underway please review the options below - and hit continue when you\'re ready to begin.
			<form action="', $upcontext['form_url'], '" method="post" name="upform" id="upform">
				<table cellpadding="1" cellspacing="0">
					<tr valign="top">
						<td width="2%">
							<input type="checkbox" name="backup" id="backup" value="1" />
						</td>
						<td width="100%">
							<label for="backup">Backup tables in your database with the prefix &quot;backup_' . $db_prefix . '&quot;.</label>', isset($modSettings['smfVersion']) ? '' : ' (recommended!)', '
						</td>
					</tr>
					<tr valign="top">
						<td width="2%">
							<input type="checkbox" name="maint" id="maint" value="1" checked="checked" />
						</td>
						<td width="100%">
							<label for="maint">Put the forum into maintenance mode during upgrade.</label> <span class="smalltext">(<a href="#" onclick="document.getElementById(\'mainmess\').style.display = document.getElementById(\'mainmess\').style.display == \'\' ? \'none\' : \'\'">Customize</a>)</span>
							<div id="mainmess" style="display: none;">
								<b class="smalltext">Maintenance Title: </b><br />
								<input type="text" name="maintitle" size="30" value="', htmlspecialchars($mtitle), '" /><br />
								<b class="smalltext">Maintenance Message: </b><br />
								<textarea name="mainmessage" rows="3" cols="50">', htmlspecialchars($mmessage), '</textarea>
							</div>
						</td>
					</tr>
					<tr valign="top">
						<td width="2%">
							<input type="checkbox" name="debug" id="debug" value="1" />
						</td>
						<td width="100%">
							<label for="debug">Output extra debugging information</label>
						</td>
					</tr>
					<tr valign="top">
						<td width="2%">
							<input type="checkbox" name="stats" id="stats" value="1" ', empty($modSettings['allow_sm_stats']) ? '' : 'checked="checked"', ' />
						</td>
						<td width="100%">
							<label for="stats">
								Allow Simple Machines to Collect Basic Stats Monthly.<br />
								<span class="smalltext">If enabled, this will allow Simple Machines to visit your site once a month to collect basic statistics. This will help us make decisions as to which configurations to optimise the software for. For more information please visit our <a href="http://www.simplemachines.org/about/stats.php" target="_blank">info page</a>.</span>
							</label>
						</td>
					</tr>
				</table>
				<input type="hidden" name="lang" value="', $upcontext['language'], '" />
				<input type="hidden" name="upcont" value="1" />';

	// We need a normal continue button here!
	$upcontext['continue'] = 1;
}

// Template for the database backup tool/
function template_backup_database()
{
	global $upcontext, $modSettings, $upgradeurl, $disable_security, $settings, $support_js, $is_debug;

	echo '
			<h3>Please wait whilst a backup is created. For large forums this may take some time!</h3>';

	echo '
			<form action="', $upcontext['form_url'], '&amp;lang=', $upcontext['language'], '" name="upform"  id="upform" method="post">
			<input type="hidden" name="backup_done" id="backup_done" value="0" />
			<b>Completed <span id="tab_done">', $upcontext['cur_table_num'], '</span> out of ', $upcontext['table_count'], ' tables.</b>
			<span id="debuginfo"></span>';

	// Dont any tables so far?
	if (!empty($upcontext['previous_tables']))
		foreach ($upcontext['previous_tables'] as $table)
			echo '
			<br />Completed Table: &quot;', $table, '&quot;.';

	echo '
			<h3 id="current_tab_div">Current Table: &quot;<span id="current_table">', $upcontext['cur_table_name'], '</span>&quot;</h3>
			<br /><span id="commess" style="font-weight: bold; display: ', $upcontext['cur_table_num'] == $upcontext['table_count'] ? 'inline' : 'none', ';">Backup Complete! Click Continue to Proceed.</span>';

	// Continue please!
	$upcontext['continue'] = $support_js ? 2 : 1;

	// If javascript allows we want to do this using XML.
	if ($support_js)
	{
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var lastTable = ', $upcontext['cur_table_num'], ';
			function getNextTables()
			{
				getXMLDocument(\'', $upcontext['form_url'], '&amp;xml&amp;substep=\' + lastTable, onBackupUpdate);
			}

			// Got an update!
			function onBackupUpdate(oXMLDoc)
			{
				var sTableName = "";
				var iTableNum = 0;
				for (var i = 0; i < oXMLDoc.getElementsByTagName("table")[0].childNodes.length; i++)
					sTableName += oXMLDoc.getElementsByTagName("table")[0].childNodes[i].nodeValue;
				iTableNum = oXMLDoc.getElementsByTagName("table")[0].getAttribute("num");

				// Update the page.
				setInnerHTML(document.getElementById(\'tab_done\'), iTableNum);
				setInnerHTML(document.getElementById(\'current_table\'), sTableName);
				lastTable = iTableNum;
				updateStepProgress(iTableNum, ', $upcontext['table_count'], ', ', $upcontext['step_weight'], ');';

		// If debug flood the screen.
		if ($is_debug)
			echo '
				setOuterHTML(document.getElementById(\'debuginfo\'), \'<br />Completed Table: &quot;\' + sTableName + \'&quot;.<span id="debuginfo"></span>\');
				window.scroll(0,99999);';

		echo '
				// Get the next update...
				if (iTableNum == ', $upcontext['table_count'], ')
				{
					document.getElementById(\'commess\').style.display = "";
					document.getElementById(\'current_tab_div\').style.display = "none";
					document.getElementById(\'contbutt\').disabled = 0;
					document.getElementById(\'backup_done\').value = 1;
				}
				else
					getNextTables();
			}
			getNextTables();
		// ]]></script>';
	}
}

function template_backup_xml()
{
	global $upcontext, $settings, $options, $txt;

	echo '
	<table num="', $upcontext['cur_table_num'], '">', $upcontext['cur_table_name'], '</table>';
}

// Here is the actual "make the changes" template!
function template_database_changes()
{
	global $upcontext, $modSettings, $upgradeurl, $disable_security, $settings, $support_js, $is_debug, $timeLimitThreshold;

	echo '
		<h3>Executing database changes - this step may take quite some time for larger forums.</h3>';

	echo '
		<form action="', $upcontext['form_url'], '&amp;lang=', $upcontext['language'], '&amp;filecount=', $upcontext['file_count'], '" name="upform"  id="upform" method="post">
		<input type="hidden" name="database_done" id="database_done" value="0" />';

	// No javascript looks rubbish!
	if (!$support_js)
	{
		foreach ($upcontext['actioned_items'] as $num => $item)
		{
			if ($num != 0)
				echo ' Successful!';
			echo '<br />' . $item;
		}
		if (!empty($upcontext['changes_complete']))
			echo ' Successful!<br /><br /><span id="commess" style="font-weight: bold;">Database Updates Complete! Click Continue to Proceed.</span><br />';
	}
	else
	{
		// Tell them how many files we have in total.
		if ($upcontext['file_count'] > 1)
			echo '
		<b id="info1">Executing upgrade script <span id="file_done">', $upcontext['cur_file_num'], '</span> of ', $upcontext['file_count'], '.</b>';


		echo '
		<h3 id="info2"><b>Executing:</b> &quot;<span id="cur_item_name">', $upcontext['current_item_name'], '</span>&quot; (<span id="item_num">', $upcontext['current_item_num'], '</span> of <span id="total_items"><span id="item_count">', $upcontext['total_items'], '</span>', $upcontext['file_count'] > 1 ? ' - of this script' : '', ').</span></h3>
		<br /><span id="commess" style="font-weight: bold; display: ', !empty($upcontext['changes_complete']) || $upcontext['current_debug_item_num'] == $upcontext['debug_items'] ? 'inline' : 'none', ';">Database Updates Complete! Click Continue to Proceed.</span><br />';

		if ($is_debug)
		{
			echo $upcontext['current_debug_item_name'];
			for ($i = substr_count($upcontext['current_debug_item_name'], '.'); $i < 3; $i++)
				echo '.';
		}
		echo '
			<span id="debuginfo"></span>';
	}

	// Place for the XML error message.
	echo '
		<div id="error_block" style="margin: 2ex; padding: 2ex; border: 2px dashed #cc3344; color: black; background-color: #ffe4e9; display: ', empty($upcontext['error_message']) ? 'none' : '', ';">
			<div style="float: left; width: 2ex; font-size: 2em; color: red;">!!</div>
			<b style="text-decoration: underline;">Error!</b><br />
			<div style="padding-left: 6ex;" id="error_message">', isset($upcontext['error_message']) ? $upcontext['error_message'] : '', '</div>
		</div>';

	

	// We want to continue at some point!
	$upcontext['continue'] = $support_js ? 2 : 1;

	// If javascript allows we want to do this using XML.
	if ($support_js)
	{
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var lastItem = ', $upcontext['current_debug_item_num'], ';
			var curFile = ', $upcontext['cur_file_num'], ';
			var totalItems = 0;
			var prevFile = 0;
			var retryCount = 0;
			var testvar = 0;
			var timeOutID = 0;
			var getData = "";
			var debugItems = ', $upcontext['debug_items'], ';
			function getNextItem()
			{
				// We want to track this...
				if (timeOutID)
					clearTimeout(timeOutID);
				timeOutID = window.setTimeout("retTimeout()", ', (10 * $timeLimitThreshold), '000);

				getXMLDocument(\'', $upcontext['form_url'], '&amp;xml&amp;filecount=', $upcontext['file_count'], '&amp;substep=\' + lastItem + getData, onItemUpdate);
			}

			// Got an update!
			function onItemUpdate(oXMLDoc)
			{
				var sItemName = "";
				var sDebugName = "";
				var iItemNum = 0;
				var iSubStepProgress = -1;
				var iDebugNum = 0;
				var bIsComplete = 0;
				getData = "";

				// We\'ve got something - so reset the timeout!
				if (timeOutID)
					clearTimeout(timeOutID);

				// Assume no error at this time...
				document.getElementById("error_block").style.display = "none";

				// Are we getting some duff info?
				if (!oXMLDoc.getElementsByTagName("item")[0])
				{
					// Too many errors?
					if (retryCount > 15)
					{
						document.getElementById("error_block").style.display = "";
						setInnerHTML(document.getElementById("error_message"), "Error retrieving information on step: " + sDebugName);';

	if ($is_debug)
		echo '
						setOuterHTML(document.getElementById(\'debuginfo\'), \'<span style="color: red;">failed</span><span id="debuginfo"></span>\');';

	echo '
					}
					else
					{
						retryCount++;
						getNextItem();
					}
					return false;
				}

				// Never allow loops.
				if (curFile == prevFile)
				{
					retryCount++;
					if (retryCount > 10)
					{
						document.getElementById("error_block").style.display = "";
						setInnerHTML(document.getElementById("error_message"), "Upgrade script appears to be going into a loop - step: " + sDebugName);';

	if ($is_debug)
		echo '
						setOuterHTML(document.getElementById(\'debuginfo\'), \'<span style="color: red;">failed</span><span id="debuginfo"></span>\');';

	echo '
					}
				}
				retryCount = 0;

				// Is there an error?
				if (oXMLDoc.getElementsByTagName("error")[0])
				{
					var sErrorMsg = "";
					for (var i = 0; i < oXMLDoc.getElementsByTagName("error")[0].childNodes.length; i++)
						sErrorMsg += oXMLDoc.getElementsByTagName("error")[0].childNodes[i].nodeValue;
					document.getElementById("error_block").style.display = "";
					setInnerHTML(document.getElementById("error_message"), sErrorMsg);
					return false;
				}

				for (var i = 0; i < oXMLDoc.getElementsByTagName("item")[0].childNodes.length; i++)
					sItemName += oXMLDoc.getElementsByTagName("item")[0].childNodes[i].nodeValue;
				for (var i = 0; i < oXMLDoc.getElementsByTagName("debug")[0].childNodes.length; i++)
					sDebugName += oXMLDoc.getElementsByTagName("debug")[0].childNodes[i].nodeValue;
				for (var i = 0; i < oXMLDoc.getElementsByTagName("get").length; i++)
				{
					getData += "&amp;" + oXMLDoc.getElementsByTagName("get")[i].getAttribute("key") + "=";
					for (var j = 0; j < oXMLDoc.getElementsByTagName("get")[i].childNodes.length; j++)
					{
						 getData += oXMLDoc.getElementsByTagName("get")[i].childNodes[j].nodeValue;
					}
				}

				iItemNum = oXMLDoc.getElementsByTagName("item")[0].getAttribute("num");
				iDebugNum = parseInt(oXMLDoc.getElementsByTagName("debug")[0].getAttribute("num"));
				bIsComplete = parseInt(oXMLDoc.getElementsByTagName("debug")[0].getAttribute("complete"));
				iSubStepProgress = parseFloat(oXMLDoc.getElementsByTagName("debug")[0].getAttribute("percent"));

				curFile = parseInt(oXMLDoc.getElementsByTagName("file")[0].getAttribute("num"));
				debugItems = parseInt(oXMLDoc.getElementsByTagName("file")[0].getAttribute("debug_items"));
				totalItems = parseInt(oXMLDoc.getElementsByTagName("file")[0].getAttribute("items"));

				// Do we have the additional progress bar?
				if (iSubStepProgress != -1)
				{
					document.getElementById("substep_bar_div").style.display = "";
					document.getElementById("substep_bar_div2").style.display = "";
					document.getElementById("substep_progress").style.width = iSubStepProgress + "%";
					setInnerHTML(document.getElementById("substep_text"), iSubStepProgress + "%");
					setInnerHTML(document.getElementById("substep_bar_div"), sDebugName.replace(/\./g, "") + ":");
				}
				else
				{
					document.getElementById("substep_bar_div").style.display = "none";
					document.getElementById("substep_bar_div2").style.display = "none";
				}

				// Move onto the next item?
				if (bIsComplete)
					lastItem = iDebugNum;
				else
					lastItem = iDebugNum - 1;

				// Are we finished?
				if (bIsComplete && iDebugNum == -1 && curFile >= ', $upcontext['file_count'], ')
				{';

		if ($is_debug)
			echo '
					setOuterHTML(document.getElementById(\'debuginfo\'), \'done<span id="debuginfo"></span>\');';

		echo '
		
					document.getElementById(\'commess\').style.display = "";
					document.getElementById(\'contbutt\').disabled = 0;
					document.getElementById(\'database_done\').value = 1;';

		if ($upcontext['file_count'] > 1)
			echo '
					document.getElementById(\'info1\').style.display = "none";';

		echo '
					document.getElementById(\'info2\').style.display = "none";
					updateStepProgress(100, 100, ', $upcontext['step_weight'], ');
					return true;
				}
				// Was it the last step in the file?
				else if (bIsComplete && iDebugNum == -1)
				{
					lastItem = 0;
					prevFile = curFile;';

		if ($is_debug)
			echo '
					setOuterHTML(document.getElementById(\'debuginfo\'), \'done<br />Moving to next script file...<span id="debuginfo"></span>\');';

		echo '
					getNextItem();
					return true;
				}';

		// If debug scroll the screen.
		if ($is_debug)
			echo '
				if (bIsComplete)
				{
					// Give it consistant dots.
					dots = sDebugName.match(/\./g);
					numDots = dots ? dots.length : 0;
					for (var i = numDots; i < 3; i++)
						sDebugName += ".";
					setOuterHTML(document.getElementById(\'debuginfo\'), \'done<br />\' + sDebugName + \'<span id="debuginfo"></span>\');
				}
				else
					setOuterHTML(document.getElementById(\'debuginfo\'), \'...<span id="debuginfo"></span>\');
				window.scroll(0,99999);';

		echo '
				// Update the page.
				setInnerHTML(document.getElementById(\'item_num\'), iItemNum);
				setInnerHTML(document.getElementById(\'cur_item_name\'), sItemName);';

		if ($upcontext['file_count'] > 1)
		{
			echo '
				setInnerHTML(document.getElementById(\'file_done\'), curFile);
				setInnerHTML(document.getElementById(\'item_count\'), totalItems);';
		}

		echo '
				// Get the progress bar right.
				barTotal = debugItems * ', $upcontext['file_count'], ';
				barDone = (debugItems * (curFile - 1)) + lastItem;

				updateStepProgress(barDone, barTotal, ', $upcontext['step_weight'], ');

				// Finally - update the time here as it shows the server is responding!
				curTime = new Date();
				iElapsed = (curTime.getTime() / 1000 - ', $upcontext['started'], ');
				mins = parseInt(iElapsed / 60);
				secs = parseInt(iElapsed - mins * 60);
				setInnerHTML(document.getElementById("mins_elapsed"), mins);
				setInnerHTML(document.getElementById("secs_elapsed"), secs);

				getNextItem();
			}

			// What if we timeout?!
			function retTimeout(attemptAgain)
			{
				// Oh noes...
				if (!attemptAgain)
				{
					document.getElementById("error_block").style.display = "";
					setInnerHTML(document.getElementById("error_message"), "Server has not responded for ', ($timeLimitThreshold * 10), ' seconds. Please click <a href=\"#\" onclick=\"retTimeout(true); return false;\">here</a> to try this step again");
				}
				else
				{
					document.getElementById("error_block").style.display = "none";
					getNextItem();
				}
			}';

		// Start things off assuming we've not errored.
		if (empty($upcontext['error_message']))
			echo '
			getNextItem();';

		echo '
		// ]]></script>';
	}
	return;
}

function template_database_xml()
{
	global $upcontext, $settings, $options, $txt;

	echo '
	<file num="', $upcontext['cur_file_num'], '" items="', $upcontext['total_items'], '" debug_items="', $upcontext['debug_items'], '">', $upcontext['cur_file_name'], '</file>
	<item num="', $upcontext['current_item_num'], '">', $upcontext['current_item_name'], '</item>
	<debug num="', $upcontext['current_debug_item_num'], '" percent="', isset($upcontext['substep_progress']) ? $upcontext['substep_progress'] : '-1', '" complete="', empty($upcontext['completed_step']) ? 0 : 1, '">', $upcontext['current_debug_item_name'], '</debug>';

	if (!empty($upcontext['error_message']))
		echo '
	<error>', $upcontext['error_message'], '</error>';
}

function template_clean_mods()
{
	global $upcontext, $modSettings, $upgradeurl, $disable_security, $settings, $boarddir, $db_prefix, $boardurl;

	echo '
	<h3>SMF has detected some packages which were installed but not fully removed prior to upgrade. We recommend you remove the following mods and reinstall upon completion of the upgrade.</h3>
	<form action="', $upcontext['form_url'], '&amp;lang=', $upcontext['language'], '&amp;ssi=1" name="upform"  id="upform" method="post">
		<table width="90%" align="center" cellspacing="1" cellpadding="2" style="background-color: black;">
			<tr style="background-color: #EEEEEE;">
				<td width="40%"><b>Modification Name</b></td>
				<td width="10%" align="center"><b>Version</b></td>
				<td width="15%"><b>Files Affected</b></td>
				<td width="20%"><b>Status</b></td>
				<td width="5%" align="center"><b>Fix?</b></td>
			</tr>';

	foreach ($upcontext['packages'] as $package)
	{
		echo '
			<tr style="background-color: #CCCCCC;">
				<td width="40%">', $package['name'], '</td>
				<td width="10%">', $package['version'], '</td>
				<td width="15%">', $package['file_count'], ' <span class="smalltext">[<a href="#" onclick="alert(\'The following files are affected by this modification:\\n\\n', strtr(implode('<br />', $package['files']), array('\\' => '\\\\', '<br />' => "\\n")), '\'); return false;">details</a>]</td>
				<td width="20%"><span style="font-weight: bold; color: ', $package['color'], '">', $package['status'], '</span></td>
				<td width="5%" align="center"><input type="checkbox" name="remove[', $package['id'], ']" ', $package['color'] == 'green' ? 'disabled="disabled"' : 'checked="checked"', ' /></td>
			</tr>';
	}
	echo '
		</table>
		<input type="hidden" name="cleandone" value="1" />';

	// We'll want a continue button...
	$upcontext['continue'] = 1;
}

// Finished with the mods - let them know what we've done.
function template_cleanup_done()
{
	global $upcontext, $modSettings, $upgradeurl, $disable_security, $settings, $boarddir, $db_prefix, $boardurl;

	echo '
	<h3>SMF has attempted to fix and reinstall mods as required. We recommend you visit the package manager upon completing upgrade to check the status of your modifications.</h3>
	<form action="', $upcontext['form_url'], '&amp;lang=', $upcontext['language'], '&amp;ssi=1" name="upform"  id="upform" method="post">
		<table width="90%" align="center" cellspacing="1" cellpadding="2" style="background-color: black;">
			<tr style="background-color: #EEEEEE;">
				<td width="100%"><b>Actions Completed:</b></td>
			</tr>';

	foreach ($upcontext['packages'] as $package)
	{
		echo '
			<tr style="background-color: #CCCCCC;">
				<td>', $package['name'], '... <span style="font-weight: bold; color: ', $package['color'], '">', $package['result'], '</span></td>
			</tr>';
	}
	echo '
		</table>
		<input type="hidden" name="cleandone2" value="1" />';

	// We'll want a continue button...
	$upcontext['continue'] = 1;
}

// Do they want to upgrade their templates?
function template_upgrade_templates()
{
	global $upcontext, $modSettings, $upgradeurl, $disable_security, $settings, $boarddir, $db_prefix, $boardurl;

	echo '
	<h3>There have been numerous language and template changes since the previous version of SMF. On this step the upgrader can attempt to automatically make these changes in your templates to save you from doing so manually.</h3>
	<form action="', $upcontext['form_url'], '&amp;lang=', $upcontext['language'], '&amp;ssi=1', $upcontext['is_test'] ? '' : ';forreal=1', '" name="upform"  id="upform" method="post">';

	// Any files need to be writable?
	template_chmod();

	// Language/Template files need an update?
	if ($upcontext['temp_progress'] == 0 && !$upcontext['is_test'] && (!empty($upcontext['languages']) || !empty($upcontext['themes'])))
	{
		echo '
		The following template files will be updated to ensure they are compatible with this version of SMF. Note that this can only fix a limited number of compatibilty issues and in general you should seek out the latest version of these themes/language files.
		<table width="90%" align="center" cellspacing="1" cellpadding="2" style="background-color: black;">
			<tr style="background-color: #EEEEEE;">
				<td width="80%"><b>Area</b></td>
				<td width="20%" align="center"><b>Changes Required</b></td>
			</tr>';

		foreach ($upcontext['languages'] as $language)
		{
			echo '
				<tr style="background-color: #CCCCCC;">
					<td width="80%">
						&quot;', $language['name'], '&quot; Language Pack
						<div class="smalltext">(';

			foreach ($language['files'] as $k => $file)
				echo $file['name'], $k + 1 != count($language['files']) ? ', ' : ')';

			echo '
						</div>
					</td>
					<td width="20%" align="center">', $language['edit_count'] == 0 ? 1 : $language['edit_count'], '</td>
				</tr>';
		}

		foreach ($upcontext['themes'] as $theme)
		{
			echo '
				<tr style="background-color: #CCCCCC;">
					<td width="80%">
						&quot;', $theme['name'], '&quot; Theme
						<div class="smalltext">(';

			foreach ($theme['files'] as $k => $file)
				echo $file['name'], $k + 1 != count($theme['files']) ? ', ' : ')';

			echo '
						</div>
					</td>
					<td width="20%" align="center">', $theme['edit_count'] == 0 ? 1 : $theme['edit_count'], '</td>
				</tr>';
		}

		echo '
		</table>';
	}
	else
	{
		$langFiles = 0;
		$themeFiles = 0;
		if (!empty($upcontext['languages']))
			foreach ($upcontext['languages'] as $lang)
				$langFiles += count($lang['files']);
		if (!empty($upcontext['themes']))
			foreach ($upcontext['themes'] as $theme)
				$themeFiles += count($theme['files']);
		echo sprintf('Found <b>%d</b> language files and <b>%d</b> templates requiring an update so far.', $langFiles, $themeFiles) . '<br />';
	
		// What we're currently doing?
		if (!empty($upcontext['current_message']))
			echo '
				', $upcontext['current_message'];
	}

	echo '
		<input type="hidden" name="uptempdone" value="1" />';

	if (!empty($upcontext['languages']))
		echo '
		<input type="hidden" name="languages" value="', base64_encode(serialize($upcontext['languages'])), '" />';
	if (!empty($upcontext['themes']))
		echo '
		<input type="hidden" name="themes" value="', base64_encode(serialize($upcontext['themes'])), '" />';
	if (!empty($upcontext['writable_files']))
		echo '
		<input type="hidden" name="writable_files" value="', base64_encode(serialize($upcontext['writable_files'])), '" />';

	// Offer them the option to upgrade from YaBB SE?
	if (!empty($upcontext['can_upgrade_yabbse']))
		echo '
		<br /><label for="conv"><input type="checkbox" name="conv" id="conv" value="1" /> Convert the existing YaBB SE template and set it as default.</label><br />';

	// We'll want a continue button...
	$upcontext['continue'] = 1;
}

function template_upgrade_complete()
{
	global $upcontext, $modSettings, $upgradeurl, $disable_security, $settings, $boarddir, $db_prefix, $boardurl;

	echo '
	<h3>That wasn\'t so hard, was it?  Now you are ready to use <a href="', $boardurl, '/index.php">your installation of SMF</a>.  Hope you like it!</h3>';

	if (!empty($upcontext['can_delete_script']))
		echo '
			<label for="delete_self"><input type="checkbox" id="delete_self" onclick="doTheDelete(this);" /> Delete this upgrade.php and its data files now.</label> <i>(doesn\'t work on all servers.)</i>
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				function doTheDelete(theCheck)
				{
					var theImage = document.getElementById ? document.getElementById("delete_upgrader") : document.all.delete_upgrader;

					theImage.src = "', $upgradeurl, '?delete=1&ts_" + (new Date().getTime());
					theCheck.disabled = true;
				}
			// ]]></script>
			<img src="', $boardurl, '/Themes/default/images/blank.gif" alt="" id="delete_upgrader" /><br />';

	echo '<br />
			If you had any problems with this upgrade, or have any problems using SMF, please don\'t hesitate to <a href="http://www.simplemachines.org/community/index.php">look to us for assistance</a>.<br />
			<br />
			Best of luck,<br />
			Simple Machines';
}

?>
