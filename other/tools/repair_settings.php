<?php
/**********************************************************************************
* repair_settings.php                                                             *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1                                             *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2008 by:     Simple Machines LLC (http://www.simplemachines.org) *
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

// We need the Settings.php info for database stuff.
if (file_exists(dirname(__FILE__) . '/Settings.php'))
	require_once(dirname(__FILE__) . '/Settings.php');

// Initialize everything and load the language files.
initialize_inputs();

$txt['smf_repair_settings'] = 'Settings Repair Tool';
$txt['no_value'] = '<i style="font-weight: normal; color: red;">Value not found!</i>';
$txt['default_value'] = 'Recommended value';
$txt['save_settings'] = 'Save Settings';
$txt['not_writable'] = 'Settings.php cannot be written to by your webserver.  Please modify the permissions on this file to allow write access.';
$txt['recommend_blank'] = '<i>(blank)</i>';
$txt['database_settings_hidden'] = 'Some settings are not being shown because the database connection information is incorrect.';

$txt['critical_settings'] = 'Critical Settings';
$txt['critical_settings_info'] = 'These are the settings most likely to be screwing up your board, but try the things below (especially the path and URL ones) if these don\'t help.  You can click on the recommendded value to use it.';
$txt['maintenance'] = 'Maintenance Mode';
$txt['maintenance0'] = 'Off (recommended)';
$txt['maintenance1'] = 'Enabled';
$txt['maintenance2'] = 'Unusable <em>(not recommended!)</em>';
$txt['language'] = 'Language File';
$txt['cookiename'] = 'Cookie Name';
$txt['queryless_urls'] = 'Queryless URLs';
$txt['queryless_urls0'] = 'Off (recommended)';
$txt['queryless_urls1'] = 'On';
$txt['enableCompressedOutput'] = 'Output Compression';
$txt['enableCompressedOutput0'] = 'Off (recommended if you have problems)';
$txt['enableCompressedOutput1'] = 'On (saves a lot of bandwidth)';
$txt['databaseSession_enable'] = 'Database driven sessions';
$txt['databaseSession_enable0'] = 'Off (not recommended)';
$txt['databaseSession_enable1'] = 'On (recommended)';

$txt['database_settings'] = 'MySQL Database Info';
$txt['database_settings_info'] = 'This is the server, username, password, and database for your server.';
$txt['db_server'] = 'Server';
$txt['db_name'] = 'Database name';
$txt['db_user'] = 'Username';
$txt['db_passwd'] = 'Password';
$txt['db_prefix'] = 'Table prefix';
$txt['db_persist'] = 'Connection type';
$txt['db_persist0'] = 'Standard (recommended)';
$txt['db_persist1'] = 'Persistent (might cause problems)';

$txt['path_url_settings'] = 'Paths &amp; URLs';
$txt['path_url_settings_info'] = 'These are the paths and URLs to your SMF installation, and can cause big problems when they are wrong.  Sorry, there are a lot of them.';
$txt['boardurl'] = 'Forum URL';
$txt['boarddir'] = 'Forum Directory';
$txt['sourcedir'] = 'Sources Directory';
$txt['cachedir'] = 'Cache Directory';
$txt['attachmentUploadDir'] = 'Attachment Directory';
$txt['avatar_url'] = 'Avatar URL';
$txt['avatar_directory'] = 'Avatar Directory';
$txt['smileys_url'] = 'Smileys URL';
$txt['smileys_dir'] = 'Smileys Directory';
$txt['theme_url'] = 'Default Theme URL';
$txt['images_url'] = 'Default Theme Images URL';
$txt['theme_dir'] = 'Default Theme Directory';

$txt['theme_path_url_settings'] = 'Paths &amp; URLs For Themes';
$txt['theme_path_url_settings_info'] = 'These are the paths and URLs to your SMF themes.';

if (isset($_POST['submit']))
	set_settings();

// Note that we're using the default URLs because we aren't even going to try to use Settings.php's settings.
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>', $txt['smf_repair_settings'], '</title>
		<script language="JavaScript" type="text/javascript" src="Themes/default/scripts/script.js"></script>
		<style type="text/css">
			body
			{
				background-color: #E5E5E8;
				margin: 0px;
				padding: 0px;
			}
			body, td
			{
				color: #000000;
				font-size: small;
				font-family: verdana, sans-serif;
			}
			div#header
			{
				background-image: url(Themes/default/images/catbg.jpg);
				background-repeat: repeat-x;
				background-color: #88A6C0;
				padding: 22px 4% 12px 4%;
				color: white;
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
				background-color: #F6F6F6;
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
				padding-', empty($txt['lang_rtl']) ? 'right' : 'left', ': 2ex;
			}
		</style>
	</head>
	<body>
		<div id="header">
			<a href="http://www.simplemachines.org/" target="_blank"><img src="Themes/default/images/smflogo.gif" style="width: 250px; float: right;" alt="Simple Machines" border="0" /></a>
			<div title="Zanarkand">', $txt['smf_repair_settings'], '</div>
		</div>
		<div id="content">';

show_settings();

echo '
		</div>
	</body>
</html>';

function initialize_inputs()
{
	global $smcFunc, $db_connection, $sourcedir, $db_server, $db_name, $db_user, $db_passwd, $db_prefix, $db_type;

	// Turn off magic quotes runtime and enable error reporting.
	@set_magic_quotes_runtime(0);
	error_reporting(E_ALL);
	if (@ini_get('session.save_handler') == 'user')
		@ini_set('session.save_handler', 'files');
	@session_start();

	// Add slashes, as long as they aren't already being added.
	if (get_magic_quotes_gpc() == 0)
	{
		foreach ($_POST as $k => $v)
		{
			if (is_array($v))
				foreach ($v as $k2 => $v2)
					$_POST[$k][$k2] = addslashes($v2);
			else
				$_POST[$k] = addslashes($v);
		}
	}

	// This is really quite simple; if ?delete is on the URL, delete the installer...
	if (isset($_GET['delete']))
	{
		@unlink(__FILE__);

		// Now just redirect to a blank.gif...
		header('Location: http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']) . dirname($_SERVER['PHP_SELF']) . '/Themes/default/images/blank.gif');
		exit;
	}

	$db_connection = false;
	if (isset($sourcedir))
	{
		define('SMF', 1);

		if (empty($smcFunc))
			$smcFunc = array();

		require_once($sourcedir . '/Errors.php');
		require_once($sourcedir . '/Subs.php');
		require_once($sourcedir . '/Load.php');
		require_once($sourcedir . '/Security.php');
		require_once($sourcedir . '/Subs-Auth.php');
		require_once($sourcedir . '/Subs-Db-' . $db_type . '.php');

		$db_connection = smf_db_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true));
	}

}

function show_settings()
{
	global $txt, $smcFunc, $db_connection;

	// Check to make sure Settings.php exists!
	if (file_exists(dirname(__FILE__) . '/Settings.php'))
		$settingsArray = file(dirname(__FILE__) . '/Settings.php');
	else
		$settingsArray = array();

	if (count($settingsArray) == 1)
		$settingsArray = preg_split('~[\r\n]~', $settingsArray[0]);

	$settings = array();
	for ($i = 0, $n = count($settingsArray); $i < $n; $i++)
	{
		$settingsArray[$i] = rtrim($settingsArray[$i]);

		if (substr($settingsArray[$i], 0, 1) == '$')
		{
			preg_match('~^[$]([a-zA-Z_]+)\s*=\s*(["\'])?(.*?)(?:\\2)?;~', $settingsArray[$i], $match);
			if (isset($match[3]))
			{
				if ($match[3] == 'dirname(__FILE__)')
					$settings[$match[1]] = dirname(__FILE__);
				elseif ($match[3] == 'dirname(__FILE__) . \'/Sources\'')
					$settings[$match[1]] = dirname(__FILE__) . '/Sources';
				elseif ($match[3] == '$boarddir . \'/Sources\'')
					$settings[$match[1]] = $settings['boarddir'] . '/Sources';
				else
					$settings[$match[1]] = stripslashes($match[3]);
			}
		}
	}

	if ($db_connection == true)
	{
		$request = $smcFunc['db_query']('', '
			SELECT DISTINCT variable, value
			FROM {db_prefix}settings',
			array(
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$settings[$row['variable']] = $row['value'];
		$smcFunc['db_free_result']($request);

		// Load all the themes.
		$request = $smcFunc['db_query']('', '
			SELECT variable, value, id_theme
			FROM {db_prefix}themes
			WHERE id_member = 0
				AND variable IN ({array_string:variables})',
			array(
			      'variables' => array('theme_dir', 'theme_url', 'images_url', 'name'),
			)
		);

		$theme_settings = array();
		while ($row = $smcFunc['db_fetch_row']($request))
			$theme_settings[$row[2]][$row[0]] = $row[1];
		$smcFunc['db_free_result']($request);

		$show_db_settings = $request;
	}
	else
		$show_db_settings = false;

	$known_settings = array(
		'critical_settings' => array(
			'maintenance' => array('flat', 'int', 2),
			'language' => array('flat', 'string', 'english'),
			'cookiename' => array('flat', 'string', 'SMFCookie11'),
			'queryless_urls' => array('db', 'int', 1),
			'enableCompressedOutput' => array('db', 'int', 1),
			'databaseSession_enable' => array('db', 'int', 1),
		),
		'database_settings' => array(
			'db_server' => array('flat', 'string', 'localhost'),
			'db_name' => array('flat', 'string'),
			'db_user' => array('flat', 'string'),
			'db_passwd' => array('flat', 'string'),
			'db_prefix' => array('flat', 'string'),
			'db_persist' => array('flat', 'int', 1),
		),
		'path_url_settings' => array(
			'boardurl' => array('flat', 'string'),
			'boarddir' => array('flat', 'string'),
			'sourcedir' => array('flat', 'string'),
			'cachedir' => array('flat', 'string'),
			'attachmentUploadDir' => array('db', 'string'),
			'avatar_url' => array('db', 'string'),
			'avatar_directory' => array('db', 'string'),
			'smileys_url' => array('db', 'string'),
			'smileys_dir' => array('db', 'string'),
		),
		'theme_path_url_settings' => array(),
	);

	$host = empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST'];
	$url = 'http://' . $host . substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
	$known_settings['path_url_settings']['boardurl'][2] = $url;
	$known_settings['path_url_settings']['boarddir'][2] = dirname(__FILE__);

	if (file_exists(dirname(__FILE__) . '/Sources'))
		$known_settings['path_url_settings']['sourcedir'][2] = realpath(dirname(__FILE__) . '/Sources');

	if (file_exists(dirname(__FILE__) . '/cache'))
		$known_settings['path_url_settings']['cachedir'][2] = realpath(dirname(__FILE__) . '/cache');

	if (file_exists(dirname(__FILE__) . '/attachments'))
		$known_settings['path_url_settings']['attachmentUploadDir'][2] = realpath(dirname(__FILE__) . '/attachments');

	if (file_exists(dirname(__FILE__) . '/avatars'))
	{
		$known_settings['path_url_settings']['avatar_url'][2] = $url . '/avatars';
		$known_settings['path_url_settings']['avatar_directory'][2] = realpath(dirname(__FILE__) . '/avatars');
	}

	if (file_exists(dirname(__FILE__) . '/Smileys'))
	{
		$known_settings['path_url_settings']['smileys_url'][2] = $url . '/Smileys';
		$known_settings['path_url_settings']['smileys_dir'][2] = realpath(dirname(__FILE__) . '/Smileys');
	}

/*	if (file_exists(dirname(__FILE__) . '/Themes/default'))
	{
		$known_settings['path_url_settings']['theme_url'][2] = $url . '/Themes/default';
		$known_settings['path_url_settings']['images_url'][2] = $url . '/Themes/default/images';
		$known_settings['path_url_settings']['theme_dir'][2] = realpath(dirname(__FILE__) . '/Themes/default');
	}
*/

	// Create the values for the themes.
	foreach ($theme_settings as $id => $theme)
	{
		$this_theme = ($pos = strpos($theme['theme_url'], '/Themes/')) !== false ? substr($theme['theme_url'], $pos+8) : '';
		if (!empty($this_theme))
			$exist = file_exists(dirname(__FILE__) . '/Themes/' . $this_theme);
		else
			$exist = false;

		$known_settings['theme_path_url_settings'] += array(
			'theme_'. $id.'_theme_url'=>array('theme', 'string', $exist && !empty($this_theme) ? $url . '/Themes/' . $this_theme : null),
			'theme_'. $id.'_images_url'=>array('theme', 'string', $exist && !empty($this_theme) ? $url . '/Themes/' . $this_theme . '/images' : null),
			'theme_' . $id . '_theme_dir' => array('theme', 'string', $exist && !empty($this_theme) ? realpath(dirname(__FILE__) . '/Themes/' . $this_theme) : null),
		);
		$settings += array(
			'theme_' . $id . '_theme_url' => $theme['theme_url'],
			'theme_' . $id . '_images_url' => $theme['images_url'],
			'theme_' . $id . '_theme_dir' => $theme['theme_dir'],
		);

		$txt['theme_' . $id . '_theme_url'] = $theme['name'] . ' URL';
		$txt['theme_' . $id . '_images_url'] = $theme['name'] . ' Images URL';
		$txt['theme_' . $id . '_theme_dir'] = $theme['name'] . ' Directory';
	}

	if ($db_connection == true)
	{
		$request = $smcFunc['db_query']('', '
			SHOW TABLES LIKE {raw:log_topics}',
			array(
			      'log_topics' => '%log_topics',
			      'db_error_skip' => true,
			)
		);
		if ($request == true)
		{
			if ($smcFunc['db_num_rows']($request) == 1)
				list ($known_settings['database_settings']['db_prefix'][2]) = preg_replace('~log_topics$~', '', mysql_fetch_row($request));
			$smcFunc['db_free_result']($request);
		}
	}
	elseif (empty($show_db_settings))
	{
		echo '
			<div class="error_message" style="margin-bottom: 2ex;">
				', $txt['database_settings_hidden'], '
			</div>';
	}

	echo '
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				// Get the inner HTML of an element.
				function getInnerHTML(element)
				{
					if (typeof(element.innerHTML) != "undefined")
						return element.innerHTML;
					else
					{
						var returnStr = "";
						for (var i = 0; i < element.childNodes.length; i++)
							returnStr += getOuterHTML(element.childNodes[i]);

						return returnStr;
					}
				}

				function getOuterHTML(node)
				{
					if (typeof(node.outerHTML) != "undefined")
						return node.outerHTML;

					var str = "";

					switch (node.nodeType)
					{
					// An element.
					case 1:
						str += "<" + node.nodeName;

						for (var i = 0; i < node.attributes.length; i++)
						{
							if (node.attributes[i].nodeValue != null)
								str += " " + node.attributes[i].nodeName + "=\"" + node.attributes[i].nodeValue + "\"";
						}

						if (node.childNodes.length == 0 && in_array(node.nodeName.toLowerCase(), ["hr", "input", "img", "link", "meta", "br"]))
							str += " />";
						else
							str += ">" + getInnerHTML(node) + "</" + node.nodeName + ">";
						break;

					// 2 is an attribute.

					// Just some text..
					case 3:
						str += node.nodeValue;
						break;

					// A CDATA section.
					case 4:
						str += "<![CDATA" + "[" + node.nodeValue + "]" + "]>";
						break;

					// Entity reference..
					case 5:
						str += "&" + node.nodeName + ";";
						break;

					// 6 is an actual entity, 7 is a PI.

					// Comment.
					case 8:
						str += "<!--" + node.nodeValue + "-->";
						break;
					}

					return str;
				}
			// ]]></script>

			<form action="', $_SERVER['PHP_SELF'], '" method="post">
				<div class="panel">';

	foreach ($known_settings as $settings_section => $section)
	{
		echo '
					<h2>', $txt[$settings_section], '</h2>
					<h3>', $txt[$settings_section . '_info'], '</h3>

					<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 3ex;">
						<tr>';

		foreach ($section as $setting => $info)
		{
			if ($info[0] != 'flat' && empty($show_db_settings))
				continue;

			echo '
							<td width="20%" valign="top" class="textbox" style="padding-bottom: 1ex;">
								<label for="', $setting, '">', $txt[$setting], ':</label>', !isset($settings[$setting]) && $info[1] != 'check' ? '<br />
								' . $txt['no_value'] : '', '
							</td>
							<td style="padding-bottom: 1ex;">';

			if ($info[1] == 'int' || $info[1] == 'check')
			{
				for ($i = 0; $i <= $info[2]; $i++)
					echo '
								<label for="', $setting, $i, '"><input type="radio" name="', $info[0], 'settings[', $setting, ']" id="', $setting, $i, '" value="', $i, '"', isset($settings[$setting]) && $settings[$setting] == $i ? ' checked="checked"' : '', ' class="check" /> ', $txt[$setting . $i], '</label><br />';
			}
			elseif ($info[1] == 'string')
			{
				echo '
								<input type="text" name="', $info[0], 'settings[', $setting, ']" id="', $setting, '" value="', isset($settings[$setting]) ? $settings[$setting] : '', '" size="', $settings_section == 'path_url_settings' || $settings_section == 'theme_path_url_settings' ? '60" style="width: 80%;' : '30', '" />';

				if (isset($info[2]))
					echo '
								<div style="font-size: smaller;">', $txt['default_value'], ': &quot;<b><a href="javascript:void(0);" onclick="document.getElementById(\'', $setting, '\').value = ', $info[2] == '' ? '\'\';">' . $txt['recommend_blank'] : 'getInnerHTML(this);">' . $info[2], '</a></b>&quot;.</div>';
			}

			echo '
							</td>
						</tr><tr>';
		}

		echo '
							<td colspan="2"></td>
						</tr>
					</table>';
	}

	echo '

					<div align="right" style="margin: 1ex;">';

	$failure = false;
	if (substr(__FILE__, 1, 2) != ':\\')
	{
		// On linux, it's easy - just use is_writable!
		$failure |= !is_writable('Settings.php') && !@chmod('Settings.php', 0777);
	}
	// Windows is trickier.  Let's try opening for r+...
	else
	{
		// Funny enough, chmod actually does do something on windows - it removes the read only attribute.
		@chmod(dirname(__FILE__) . '/' . 'Settings.php', 0777);
		$fp = @fopen(dirname(__FILE__) . '/' . 'Settings.php', 'r+');

		// Hmm, okay, try just for write in that case...
		if (!$fp)
			$fp = @fopen(dirname(__FILE__) . '/' . 'Settings.php', 'w');

		$failure |= !$fp;
		@fclose($fp);
	}

	if ($failure)
		echo '
				<input type="submit" name="submit" value="', $txt['save_settings'], '" disabled="disabled" /><br />', $txt['not_writable'];
	else
		echo '
				<input type="submit" name="submit" value="', $txt['save_settings'], '" />';

	echo '
				</div>
				</div>
			</form>';
}

function set_settings()
{
	global $smcFunc;

	$db_updates = isset($_POST['dbsettings']) ? $_POST['dbsettings'] : array();
	$theme_updates = isset($_POST['themesettings']) ? $_POST['themesettings'] : array();
	$file_updates = isset($_POST['flatsettings']) ? $_POST['flatsettings'] : array();

	$db_updates['theme_guests'] = 1;

	$settingsArray = file(dirname(__FILE__) . '/Settings.php');
	$settings = array();
	for ($i = 0, $n = count($settingsArray); $i < $n; $i++)
	{
		$settingsArray[$i] = rtrim($settingsArray[$i]);

		// Remove the redirect...
		if ($settingsArray[$i] == 'if (file_exists(dirname(__FILE__) . \'/install.php\'))')
		{
			$settingsArray[$i] = '';
			$settingsArray[$i++] = '';
			$settingsArray[$i++] = '';
			continue;
		}

		if (substr($settingsArray[$i], 0, 1) == '$' && preg_match('~^[$]([a-zA-Z_]+)\s*=\s*(["\'])?(.*?)(?:\\2)?;~', $settingsArray[$i], $match) == 1)
			$settings[$match[1]] = stripslashes($match[3]);

		foreach ($file_updates as $var => $val)
			if (strncasecmp($settingsArray[$i], '$' . $var, 1 + strlen($var)) == 0)
			{
				$comment = strstr($settingsArray[$i], '#');
				$settingsArray[$i] = '$' . $var . ' = \'' . $val . '\';' . ($comment != '' ? "\t\t" . $comment : '');
			}
	}

	// Blank out the file - done to fix a oddity with some servers.
	$fp = @fopen(dirname(__FILE__) . '/Settings.php', 'w');
	@fclose($fp);

	$fp = fopen(dirname(__FILE__) . '/Settings.php', 'r+');
	$lines = count($settingsArray);
	for ($i = 0; $i < $lines - 1; $i++)
	{
		// Don't just write a bunch of blank lines.
		if ($settingsArray[$i] != '' || $settingsArray[$i - 1] != '')
			fwrite($fp, $settingsArray[$i] . "\n");
	}
	fwrite($fp, $settingsArray[$i]);
	fclose($fp);

	// Make sure it works.
	require(dirname(__FILE__) . '/Settings.php');


	$setString = array();
	foreach ($db_updates as $var => $val)
		$setString[] = array($var, $val);

	if (!empty($setString))
		$smcFunc['db_insert']('replace',
			'{db_prefix}settings',
			array('variable' => 'string', 'value' => 'string-65534'),
			$setString,
			array('variable')
		);

	$setString = array();
	foreach ($theme_updates as $var => $val)
	{
		// Extract the data
		preg_match('~theme_([\d]+)_(.+)~', $var, $match);
		if (empty($match[0]))
			continue;

		$setString[] = array($match[1], 0, $match[2], $val);
	}

	if (!empty($setString))
		$smcFunc['db_insert']('replace',
			'{db_prefix}themes',
			array('id_theme' => 'int', 'id_member' => 'int', 'variable' => 'string', 'value' => 'string-65534'),
			$setString,
			array('id_theme', 'id_member', 'variable')
		);
}

?>