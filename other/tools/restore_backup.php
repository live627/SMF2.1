<?php
/**********************************************************************************
* restore_backup.php                                                              *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
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


@set_magic_quotes_runtime(0);
error_reporting(E_ALL);
if (@ini_get('session.save_handler') == 'user')
	@ini_set('session.save_handler', 'files');
@session_start();

if (get_magic_quotes_gpc() == 1)
{
	foreach ($_POST as $k => $v)
		$_POST[$k] = stripslashes($v);
}

if (isset($_GET['paths']))
	step3();

show_header();

if (isset($_POST['path']))
	step2();
else
	step1();

show_footer();

function step1($error_message = '')
{
	if (file_exists(dirname(__FILE__) . '/Settings.php'))
		include_once(dirname(__FILE__) . '/Settings.php');

	if (!isset($db_server))
	{
		// Set up the defaults.
		$db_server = isset($_POST['db_server']) ? $_POST['db_server'] : @ini_get('mysql.default_host') or $db_server = 'localhost';
		$db_user = isset($_POST['db_user']) ? $_POST['db_user'] : @ini_get('mysql.default_user');
		$db_name = isset($_POST['db_name']) ? $_POST['db_name'] : @ini_get('mysql.default_user');
		$db_passwd = @ini_get('mysql.default_password');

		// This is just because it makes it easier for people on Tripod :P.
		if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'members.lycos.co.uk' && defined('LOGIN'))
		{
			$db_user = LOGIN;
			$db_name = LOGIN . '_uk_db';
		}

		// Should we use a non standard port?
		$db_port = @ini_get('mysql.default_port');
		if (!empty($db_port))
			$db_server .= ':' . $db_port;
	}
	else
		$db_passwd = '';

	if ($error_message != '')
		echo '
					<div class="error_message">
						', $error_message, '
					</div>';

	echo '
				<div class="panel">
					<form action="', $_SERVER['PHP_SELF'], '?step=2" method="post">
						<h2>MySQL connection details</h2>
						<h3>Please enter your database details below.  Please note that the table prefix name from your previous installation will be used.</h3>

						<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 2ex;">
							<tr>
								<td width="20%" valign="top" class="textbox"><label for="db_server">MySQL server name:</label></td>
								<td>
									<input type="text" name="db_server" id="db_server" value="', $db_server, '" size="30" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">This is nearly always localhost - so if you don\'t know, try localhost.</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="db_user">MySQL username:</label></td>
								<td>
									<input type="text" name="db_user" id="db_user" value="', $db_user, '" size="30" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">Fill in the username you need to connect to your MySQL database here.<br />If you don\'t know what it is, try the username of your ftp account, most of the time they are the same.</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="db_passwd">MySQL password:</label></td>
								<td>
									<input type="password" name="db_passwd" id="db_passwd" value="', $db_passwd, '" size="30" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">Here, put the password you need to connect to your MySQL database.<br />If you don\'t know this, you should try the password to your ftp account.</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="db_name">MySQL database name:</label></td>
								<td>
									<input type="text" name="db_name" id="db_name" value="', empty($db_name) ? 'smf' : $db_name, '" size="30" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">Fill in the name of the database you want to use for SMF to store its data in.<br />If this database does not exist, this installer will try to create it.</div>
								</td>
							</tr>
						</table>

						<h2>Database backup file</h2>
						<h3>Please upload your database backup file (it may be a <tt>.sql</tt> file, <tt>.sql.gz</tt> file, or a <tt>.sql.zip</tt> file) through FTP or other means, and enter the path here.<br />', !function_exists('gzcompress') ? '<b>Warning</b>: To restore compressed backups, the <b>zlib library</b> is needed, which you don\'t seem to have on this server.' : 'Please note that if this file is compressed, it may be replaced by an uncompressed version during this process.', @ini_get('allow_url_fopen') ? '<br />If your backup is uncompressed, you can also specify a URL to it here.' : '', '</h3>

						<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 2ex;">
							<tr>
								<td width="20%" valign="top" class="textbox"><label for="path">Path/URL to backup file:</label></td>
								<td>
									<input type="text" name="path" id="path" value="', isset($_POST['path']) ? $_POST['path'] : substr(__FILE__, 0, strlen(dirname(__FILE__)) + 1), '" size="60" style="width: 90%;" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">The default value for this field is the path to this file.<br />If you put the database dump in the same place, just add its name.</div>
								</td>
							</tr>
						</table>

						<h2>Before you continue...</h2>
						<h3>Please note that any existing tables will be deleted.  Please verify your connection info and create any necessary backups before continuing!</h3>

						<div align="right" style="margin: 1ex;"><input type="submit" value="Proceed" /></div>
					</form>
				</div>';

	return true;
}

function step2()
{
	global $start_time;

	$db_connection = @mysql_connect($_POST['db_server'], $_POST['db_user'], $_POST['db_passwd']);
	if (!$db_connection)
		return step1('Cannot connect to the MySQL database server with the supplied data.  If you are not sure about what to type in, please contact your host.<br /><br /><tt>' . mysql_error() . '</tt>');

	if ($_POST['db_name'] != '')
		mysql_query("
			CREATE DATABASE IF NOT EXISTS `$_POST[db_name]`", $db_connection);

	if (!mysql_select_db($_POST['db_name'], $db_connection))
		return step1(sprintf('This tool was unable to access the &quot;<i>%s</i>&quot; database.  With some hosts, you have to create the database in your administration panel before SMF can use it.  Some also add prefixes - like your username - to your database names.', $_POST['db_name']));

	// This is going to *burn* memory...
	if (@ini_get('memory_limit') < 24)
		@ini_set('memory_limit', '128M');
	@set_time_limit(300);
	if (function_exists('apache_reset_timeout'))
		apache_reset_timeout();

	$data = @read_gz_file($_POST['path']);

	if ($data == false)
		return step1('There was a problem reading that file; please check the path and try again.');

	if (!is_resource($data) && strlen($data) > filesize($_POST['path']))
	{
		// We do this because decompressing the file every time is expensive.
		$fp = @fopen($_POST['path'], 'wb');
		if ($fp)
		{
			fwrite($fp, $data);
			fclose($fp);

			$data = fopen($_POST['path'], 'rb');
		}
	}

	if (!is_resource($data))
	{
		$data = explode("\n", $data);
		$data_len = count($data);
	}
	else
	{
		fseek($data, (int) @$_GET['line']);
		$data_len = null;
	}

	$start_time = time();

	$current_statement = '';
	$failures = array();
	for ($count = $data_len === null ? 0 : (int) @$_GET['line']; ($data_len === null && !feof($data)) || $count < $data_len; $count++)
	{
		if (is_resource($data))
		{
			$line = fgets($data, 131072);
			if (substr($line, -1) == "\n")
				$line = substr($line, 0, -1);
		}
		else
		{
			if (isset($_GET['line']) && $count <= $_GET['line'])
				continue;

			$line = &$data[$count];
		}

		// No comments allowed!
		if (substr(trim($line), 0, 1) != '#')
			$current_statement .= "\n" . rtrim($line);

		// Is this the end of the query string?
		if (empty($current_statement) || (preg_match('~;[\s]*$~s', $line) == 0 && (($data_len === null && !feof($data)) || $count != $data_len)))
			continue;

		if (preg_match('~^\s*INSERT (?:IGNORE)? INTO [`]?([^\s\n\r]+?)[`]?~', $current_statement, $match) != 0)
			mysql_query("
				ALTER TABLE `$match[1]`
				DISABLE KEYS");

		if (!mysql_query($current_statement))
		{
			$error_message = mysql_error($db_connection);

			// Error 1050: Table already exists!
			if (strpos($error_message, 'already exists') === false)
				$failures[$count] = $error_message;
			elseif (preg_match('~^\s*CREATE TABLE [`]?([^\s\n\r]+?)[`]?~', $current_statement, $match) != 0)
			{
				mysql_query("
					DROP TABLE `$match[1]`");
				mysql_query($current_statement);
			}
		}

		if (preg_match('~^\s*INSERT (?:IGNORE)? INTO [`]?([^\s\n\r]+?)[`]?~', $current_statement, $match) != 0)
			mysql_query("
				ALTER TABLE `$match[1]`
				ENABLE KEYS");

		$current_statement = '';
		nextLine(is_resource($data) ? ftell($data) : $count, is_resource($data) ? filesize($_POST['path']) : count($data), $failures);
	}

	if (!empty($failures))
	{
		echo '
				<div class="error_message">
					<div style="color: red;">Some of the queries were not executed properly.  Technical information about the queries:</div>
					<div style="margin: 2.5ex;">';

		foreach ($failures as $line => $fail)
			echo '
						<b>Line #', $line + 1, ':</b> ', nl2br(htmlspecialchars($fail)), '<br />';

		echo '
					</div>
				</div>';
	}

	echo '
				<div class="panel">
					<h2>Restoration process complete!</h2>

					Congratulations!  Your database backup has been restored successfully.<br />
					<br />';

	if (file_exists(dirname(__FILE__) . '/Settings.php') && is_writable(dirname(__FILE__) . '/Settings.php') && defined('SID') && SID == '')
	{
		echo '
					<label for="fix_paths"><input type="checkbox" id="fix_paths" onclick="doThePaths(this);" /> Attempt to fix the database\'s paths for this server.</label><br />
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						function doThePaths(theCheck)
						{
							var theImage = document.getElementById ? document.getElementById("auto_paths") : document.all.auto_paths;

							theImage.src = "', $_SERVER['PHP_SELF'], '?paths&" + (new Date().getTime());
							theImage.width = 0;
							theCheck.disabled = true;
						}
					// ]]></script>
					<img src="about:blank" width="0" alt="" id="auto_paths" /><br /><br />';

		$_SESSION['temp_db'] = array($_POST['db_server'], $_POST['db_user'], $_POST['db_passwd'], $_POST['db_name']);
	}

	echo '
					If you had any problems, please <a href="http://www.simplemachines.org/community/index.php">tell us about them</a> so that we can help you get them resolved.
					<br />
					Good luck!<br />
					Simple Machines
				</div>';

	return false;
}

function step3()
{
	if (!isset($_SESSION['temp_db']))
		die;

	list ($_POST['db_server'], $_POST['db_user'], $_POST['db_passwd'], $_POST['db_name']) = $_SESSION['temp_db'];
	$_SESSION['temp_db'] = '';

	$db_connection = mysql_connect($_POST['db_server'], $_POST['db_user'], $_POST['db_passwd']);
	mysql_select_db($_POST['db_name'], $db_connection);

	$request = mysql_query("
		SHOW TABLES LIKE '%log_topics'");
	if (mysql_num_rows($request) == 1)
		list ($db_prefix) = preg_replace('~log_topics$~', '', mysql_fetch_row($request));
	else
		die;
	mysql_free_result($request);

	// What host and port are we on?
	$host = empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST'];

	// Now, to put what we've learned together... and add a path.
	$url = 'http://' . $host . substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
	$mydir = strtr(dirname(__FILE__), '\\', '/');

	$updates = array();
	if (file_exists($mydir . '/Sources'))
	{
		$updates['boarddir'] = $mydir;
		$updates['boardurl'] = $url;
		$updates['sourcedir'] = $mydir . '/Sources';
	}
	$updates['db_prefix'] = $db_prefix;
	$updates['db_name'] = $_POST['db_name'];
	$updates['db_server'] = $_POST['db_server'];
	$updates['db_user'] = $_POST['db_user'];
	$updates['db_passwd'] = addslashes($_POST['db_passwd']);
	updateSettingsFile($updates);

	$updates = array();
	if (file_exists($mydir . '/attachments'))
		$updates['attachmentUploadDir'] = $mydir . '/attachments';

	if (file_exists($mydir . '/avatars'))
	{
		$updates['avatar_directory'] = $mydir . '/avatars';
		$updates['avatar_url'] = $url . '/avatars';
	}

	if (file_exists($mydir . '/Smileys'))
	{
		$updates['smileys_dir'] = $mydir . '/Smileys';
		$updates['smileys_url'] = $url . '/Smileys';
	}

	if (!empty($updates))
	{
		$setString = '';
		foreach ($updates as $var => $val)
			$setString .= "
				('$var', SUBSTRING('$val', 1, 65534)),";

		mysql_query("
			REPLACE INTO {$db_prefix}settings
				(variable, value)
			VALUES" . substr($setString, 0, -1), $db_connection);
	}

	$result = mysql_query("
		SELECT id_theme, value
		FROM {$db_prefix}themes
		WHERE variable = 'theme_dir'", $db_connection) or die(mysql_error($db_connection));
	$updates = array();
	while ($row = mysql_fetch_assoc($result))
	{
		if (strpos($row['value'], '/Themes/') !== false && file_exists($mydir . '/Themes/' . basename($row['value'])))
			$updates[$row['id_theme']] = 'Themes/' . basename($row['value']);
	}
	mysql_free_result($result);

	if (!empty($updates))
	{
		$setString = '';
		foreach ($updates as $theme => $path)
			$setString .= "
				($theme, 0, 'theme_dir', '$mydir/$path'),
				($theme, 0, 'theme_url', '$url/$path'),
				($theme, 0, 'images_url', '$url/$path/images'),";

		mysql_query("
			REPLACE INTO {$db_prefix}themes
				(id_theme, id_member, variable, value)
			VALUES" . substr($setString, 0, -1), $db_connection);
	}

	die;
}

function show_header()
{
	global $start_time;
	$start_time = time();

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>Backup Restoration Tool</title>
		<style type="text/css">
			body
			{
				font-family: Verdana, sans-serif;
				background-color: #D4D4D4;
				margin: 0;
			}
			body, td
			{
				font-size: 10pt;
			}
			div#header
			{
				background-color: white;
				padding: 22px 4% 12px 4%;
				font-family: Georgia, serif;
				font-size: xx-large;
				border-bottom: 1px solid black;
				height: 40px;
			}
			div#content
			{
				padding: 20px 30px;
			}
			div.error_message
			{
				border: 2px dashed red;
				background-color: #E1E1E1;
				margin: 1ex 4ex;
				padding: 1.5ex;
			}
			div.panel
			{
				border: 1px solid gray;
				background-color: #F0F0F0;
				margin: 1ex 0;
				padding: 1.2ex;
			}
			div.panel h2
			{
				margin: 0;
				margin-bottom: 0.5ex;
				padding-bottom: 3px;
				border-bottom: 1px dashed black;
				font-size: 14pt;
				font-weight: normal;
			}
			div.panel h3
			{
				margin: 0;
				margin-bottom: 2ex;
				font-size: 10pt;
				font-weight: normal;
			}
			form
			{
				margin: 0;
			}
			td.textbox
			{
				padding-top: 2px;
				font-weight: bold;
				white-space: nowrap;
				padding-right: 2ex;
			}
		</style>
	</head>
	<body>
		<div id="header">
			', file_exists(dirname(__FILE__) . '/Themes/default/images/smflogo.gif') ? '<a href="http://www.simplemachines.org/" target="_blank"><img src="Themes/default/images/smflogo.gif" style="width: 250px; float: right;" alt="Simple Machines" border="0" /></a>
			' : '', '<div title="Vandole">Backup Restoration Tool</div>
		</div>
		<div id="content">';
}

function show_footer()
{
	echo '
		</div>
	</body>
</html>';
}

function read_gz_file($gzfilename)
{
	$fp = @fopen($gzfilename, 'rb');
	if ($fp === false)
		return false;

	$data = fread($fp, 2);

	if ((ord($data{0}) != 31 || ord($data{1}) != 139) && $data != 'PK')
	{
		fclose($fp);
		return @fopen($gzfilename, 'rb');
	}

	while (!feof($fp))
		$data .= fread($fp, 16384);
	fclose($fp);

	return read_gz_data($data);
}

function read_gz_data($data)
{
	$id = unpack('H2a/H2b', substr($data, 0, 2));
	if (strtolower($id['a'] . $id['b']) != '1f8b')
	{
		// Okay, this ain't no tar.gz, but maybe it's a zip file.
		if (substr($data, 0, 2) == 'PK')
			return read_zip_data($data);
		else
			return $data;
	}

	$flags = unpack('Ct/Cf', substr($data, 2, 2));

	// Not deflate!
	if ($flags['t'] != 8)
		return false;
	$flags = $flags['f'];

	$offset = 10;
	$octdec = array('mode', 'uid', 'gid', 'size', 'mtime', 'checksum', 'type');

	// "Read" the filename and comment. // !!! Might be mussed.
	if ($flags & 12)
	{
		while ($flags & 8 && $data{$offset++} != "\0")
			$offset;
		while ($flags & 4 && $data{$offset++} != "\0")
			$offset;
	}

	$crc = unpack('Vcrc32/Visize', substr($data, strlen($data) - 8, 8));
	return gzinflate(substr($data, $offset, strlen($data) - 8 - $offset));
}

function read_zip_data($data)
{
	// Look for the PK header...
	if (substr($data, 0, 2) != 'PK')
		return $data;

	// Find the central whosamawhatsit at the end; if there's a comment it's a pain.
	if (substr($data, -22, 4) == 'PK' . chr(5) . chr(6))
		$p = -22;
	else
	{
		// Have to find where the comment begins, ugh.
		for ($p = -22; $p > -strlen($data); $p--)
		{
			if (substr($data, $p, 4) == 'PK' . chr(5) . chr(6))
				break;
		}
	}

	// Get the basic zip file info.
	$zip_info = unpack('vfiles/Vsize/Voffset', substr($data, $p + 10, 10));

	$p = $zip_info['offset'];
	for ($i = 0; $i < $zip_info['files']; $i++)
	{
		// Make sure this is a file entry...
		if (substr($data, $p, 4) != 'PK' . chr(1) . chr(2))
			return false;

		// Get all the important file information.
		$file_info = unpack('Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', substr($data, $p + 16, 30));
		$file_info['filename'] = substr($data, $p + 46, $file_info['filename_len']);

		// Skip all the information we don't care about anyway.
		$p += 46 + $file_info['filename_len'] + $file_info['extra_len'] + $file_info['comment_len'];

		if (substr($file_info['filename'], -1, 1) != '/')
		{
			// Check that the data is there and does exist.
			if (substr($data, $file_info['offset'], 4) != 'PK' . chr(3) . chr(4))
				return false;

			// Get the actual compressed data.
			$file_info['data'] = substr($data, $file_info['offset'] + 30 + $file_info['filename_len'] + $file_info['extra_len'], $file_info['compressed_size']);

			// Only inflate it if we need to ;).
			if ($file_info['compressed_size'] != $file_info['size'])
				$file_info['data'] = @gzinflate($file_info['data']);

			return $file_info['data'];
		}
	}

	return false;
}

function nextLine($line, $max, $failures)
{
	global $start_time;

	@set_time_limit(300);
	if (function_exists('apache_reset_timeout'))
		apache_reset_timeout();

	if (!isset($_GET['line']) || $_GET['line'] < $line)
		$_GET['line'] = $line;

	if (time() - $start_time <= 16)
		return;

	$query_string = '';
	foreach ($_GET as $k => $v)
		$query_string .= '&amp;' . $k . '=' . $v;
	if (strlen($query_string) != 0)
		$query_string = '?' . substr($query_string, 5);

	$percentage = round(($line * 100) / $max);

	if (!empty($failures))
	{
		echo '
				<div class="error_message">
					<div style="color: red;">Some of the queries were not executed properly.  Technical information about the queries:</div>
					<div style="margin: 2.5ex;">';

		foreach ($failures as $line => $fail)
			echo '
						<b>Line #', $line + 1, ':</b> ', nl2br(htmlspecialchars($fail)), '<br />';

		echo '
					</div>
				</div>';
	}

	echo '
		<div class="panel">
			<h2>Not quite done yet! (approximately ', $percentage, '%)</h2>
			<h3>
				This tool has been paused to avoid overloading your server.  Don\'t worry, nothing\'s wrong - simply click the <label for="continue">continue button</label> below to keep going.
			</h3>

			<div style="font-size: 8pt; width: 60%; height: 1.2em; margin: auto; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
				<div style="width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $percentage, '%</div>
				<div style="width: ', $percentage, '%; height: 1.2em; z-index: 1; background-color: #6279ff;">&nbsp;</div>
			</div>

			<p>Please note that this percentage, regrettably, is not terribly accurate, and is only an approximation of progress.</p>

			<form action="', $_SERVER['PHP_SELF'], $query_string, '" method="post" name="autoSubmit">
				<input type="hidden" name="db_server" value="', $_POST['db_server'], '" />
				<input type="hidden" name="db_user" value="', $_POST['db_user'], '" />
				<input type="hidden" name="db_passwd" value="', $_POST['db_passwd'], '" />
				<input type="hidden" name="db_name" value="', $_POST['db_name'], '" />
				<input type="hidden" name="path" value="', $_POST['path'], '" />

				<div align="right" style="margin: 1ex;"><input name="b" type="submit" value="Continue" /></div>
			</form>
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				window.onload = doAutoSubmit;
				var countdown = 3;

				function doAutoSubmit()
				{
					if (countdown == 0)
						document.autoSubmit.submit();
					else if (countdown == -1)
						return;

					document.autoSubmit.b.value = "Continue (" + countdown + ")";
					countdown--;

					setTimeout("doAutoSubmit();", 1000);
				}
			// ]]></script>
		</div>';

	show_footer();
	exit;
}

function updateSettingsFile($vars)
{
	// Modify Settings.php.
	$settingsArray = file(dirname(__FILE__) . '/Settings.php');

	// !!! Do we just want to read the file in clean, and split it this way always?
	if (count($settingsArray) == 1)
		$settingsArray = preg_split('~[\r\n]~', $settingsArray[0]);

	for ($i = 0, $n = count($settingsArray); $i < $n; $i++)
	{
		// Remove the redirect...
		if (trim($settingsArray[$i]) == 'if (file_exists(dirname(__FILE__) . \'/install.php\'))')
		{
			$settingsArray[$i] = '';
			$settingsArray[$i++] = '';
			$settingsArray[$i++] = '';
			continue;
		}
		elseif (substr(trim($settingsArray[$i]), -16) == '/install.php\');' && substr(trim($settingsArray[$i]), 0, 26) == 'header(\'Location: http://\'')
		{
			$settingsArray[$i] = '';
			continue;
		}

		if (trim($settingsArray[$i]) == '?' . '>')
			$settingsArray[$i] = '';

		// Don't trim or bother with it if it's not a variable.
		if (substr($settingsArray[$i], 0, 1) != '$')
			continue;

		$settingsArray[$i] = rtrim($settingsArray[$i]) . "\n";

		foreach ($vars as $var => $val)
			if (strncasecmp($settingsArray[$i], '$' . $var, 1 + strlen($var)) == 0)
			{
				$comment = strstr($settingsArray[$i], '#');
				$settingsArray[$i] = '$' . $var . ' = \'' . $val . '\';' . ($comment != '' ? "\t\t" . $comment : "\n");
				unset($vars[$var]);
			}
	}

	// Uh oh... the file wasn't empty... was it?
	if (!empty($vars))
	{
		$settingsArray[$i++] = '';
		foreach ($vars as $var => $val)
			$settingsArray[$i++] = '$' . $var . ' = \'' . $val . '\';' . "\n";
	}

	// Blank out the file - done to fix a oddity with some servers.
	$fp = @fopen(dirname(__FILE__) . '/Settings.php', 'w');
	if (!$fp)
		return false;
	fclose($fp);

	$fp = fopen(dirname(__FILE__) . '/Settings.php', 'r+');

	// Gotta have one of these ;).
	if (trim($settingsArray[0]) != '<?php')
		fwrite($fp, "<?php\n");

	$lines = count($settingsArray);
	for ($i = 0; $i < $lines - 1; $i++)
	{
		// Don't just write a bunch of blank lines.
		if ($settingsArray[$i] != '' || @$settingsArray[$i - 1] != '')
			fwrite($fp, strtr($settingsArray[$i], "\r", ''));
	}
	fwrite($fp, $settingsArray[$i] . '?' . '>');
	fclose($fp);

	return true;
}

?>