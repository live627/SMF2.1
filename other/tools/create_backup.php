<?php
/**********************************************************************************
* create_backup.php                                                               *
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

if (function_exists('set_magic_quotes_runtime'))
	@set_magic_quotes_runtime(0);
error_reporting(E_ALL);
if (@ini_get('session.save_handler') == 'user')
	@ini_set('session.save_handler', 'files');
@session_start();

if (function_exists('get_magic_quotes_gpc') && @get_magic_quotes_gpc() == 1)
	foreach ($_POST as $k => $v)
		$_POST[$k] = stripslashes($v);

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
		$db_type = isset($_POST['db_type']) ? $_POST['db_type'] : 'mysql';
		$db_server = isset($_POST['db_server']) ? $_POST['db_server'] : @ini_get('mysql.default_host') or $db_server = 'localhost';
		$db_user = isset($_POST['db_user']) ? $_POST['db_user'] : @ini_get('mysql.default_user');
		$db_name = isset($_POST['db_name']) ? $_POST['db_name'] : @ini_get('mysql.default_user');
		$db_passwd = @ini_get('mysql.default_password');
		$db_prefix = isset($_POST['db_prefix']) ? $_POST['db_prefix'] : '';

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

	if (!isset($_POST['path']))
		$_POST['path'] = substr(__FILE__, 0, strlen(dirname(__FILE__)) + 1) . 'database_' . strftime('%Y-%m-%d') . '.sql';
	$_SESSION['smf_create_backup'] = null;

	if ($error_message != '')
		echo '
					<div class="error_message">
						', $error_message, '
					</div>';

	echo '
				<div class="panel">
					<form action="', $_SERVER['PHP_SELF'], '?step=2" method="post">
						<h2>MySQL connection details</h2>
						<h3>Please enter your database details below to create the backup.</h3>

						<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 2ex;">
							<tr>
								<td width="20%" valign="top" class="textbox"><label for="db_server">Database Type:</label></td>
								<td>
									<select name="db_type" id="db_type">
										<option value="mysql"', $db_type == 'mysql' ? ' selected="selected"' : '', '>MySQL</option>
										<option value="postgresql"', $db_type == 'postgresql' ? ' selected="selected"' : '', '>PostgreSQL</option>
										<option value="sqlite"', $db_type == 'sqlite' ? ' selected="selected"' : '', '>SQLite</option>
									</select>
								</td>
							<tr>
								<td width="20%" valign="top" class="textbox"><label for="db_server">Database server name:</label></td>
								<td>
									<input type="text" name="db_server" id="db_server" value="', $db_server, '" size="30" class="input_text" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">This is nearly always localhost - so if you don\'t know, try localhost.</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="db_user">Database username:</label></td>
								<td>
									<input type="text" name="db_user" id="db_user" value="', $db_user, '" size="30" class="input_text" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">Fill in the username you need to connect to your database here.<br />If you don\'t know what it is, try the username of your ftp account, most of the time they are the same.</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="db_passwd">Database password:</label></td>
								<td>
									<input type="password" name="db_passwd" id="db_passwd" value="', $db_passwd, '" size="30" class="input_password" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">Here, put the password you need to connect to your database.<br />If you don\'t know this, you should try the password to your ftp account.</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="db_name">database name:</label></td>
								<td>
									<input type="text" name="db_name" id="db_name" value="', empty($db_name) ? 'smf' : $db_name, '" size="30" class="input_text" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">Fill in the name of the database you want to backup.</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="db_prefix">Table prefix:</label></td>
								<td>
									<input type="text" name="db_prefix" id="db_prefix" value="', empty($db_prefix) ? '' : $db_prefix, '" size="30" class="input_text" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">Fill in a prefix to only backup tables that start with this prefix.<br />Normally, you can leave this blank to get a full backup.</div>
								</td>
							</tr>
						</table>

						<h2>Database backup file</h2>
						<h3>The database backup will be created as a file on your server.  Please specify where you want it saved.<br />', !function_exists('gzencode') ? '<strong>Warning</strong>: To create a compressed backups, the <strong>zlib library</strong> is needed, which you don\'t seem to have on this server.' : '', '</h3>

						<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 2ex;">
							<tr>
								<td width="20%" valign="top" class="textbox"><label for="path">Path to backup file:</label></td>
								<td>
									<input type="text" name="path" id="path" value="', $_POST['path'], '" size="60" style="width: 90%;" /><br class="input_text" />
									<div style="font-size: smaller; margin-bottom: 2ex;">The default value for this field is a file in this script\'s directory.</div>
								</td>';

	if (function_exists('gzencode'))
		echo '
							</tr><tr>
								<td width="20%" valign="top" class="textbox">Compress backup:</td>
								<td>
									<script type="text/javascript"><!-- // --><![CDATA[
										function fixExtension(el)
										{
											if (el.form.path.value.substr(el.form.path.value.length - 4) == ".sql" && el.checked)
												el.form.path.value += ".gz";
											else if (el.form.path.value.substr(el.form.path.value.length - 3) == ".gz" && !el.checked)
												el.form.path.value = el.form.path.value.substr(0, el.form.path.value.length - 3);
										}
									// ]]></script>
									<label for="compress"><input type="checkbox" name="compress" id="compress" value="1"', isset($_POST['compress']) ? ' checked="checked"' : '', ' onchange="fixExtension(this);" class="input_check" /> Compress the backup with gzip.</label><div style="font-size: smaller;">Please note that this will only compress the backup after it is complete.</div><br />
								</td>';

	echo '
							</tr>
						</table>

						<div class="righttext" style="margin: 1ex;"><input type="submit" value="Proceed" class="button_submit" /></div>
					</form>
				</div>';

	return true;
}

function step2()
{
	global $start_time, $table_sizes, $total_size, $before_length, $write_data, $smcFunc;

	$db_connection = smc_compat_database($_POST['db_type'], $_POST['db_server'], $_POST['db_user'], $_POST['db_password'], $_POST['db_name']);
	if (!$db_connection)
		return step1('Cannot connect to the database server with the supplied data.<br /><br />If you are not sure about what to type in, please contact your host.');

	$_GET['table'] = (int) @$_GET['table'];
	$_GET['row'] = (int) @$_GET['row'];

	if (isset($_SESSION['smf_create_backup']) && is_array($_SESSION['smf_create_backup']))
		list ($_GET['table'], $_GET['row']) = $_SESSION['smf_create_backup'];

	if (!empty($_POST['compress']) && function_exists('gzopen'))
	{
		$fopen = 'gzopen';
		$fclose = 'gzclose';
		$fwrite = 'gzwrite';
	}
	else
	{
		$fopen = 'fopen';
		$fclose = 'fclose';
		$fwrite = 'fwrite';
	}

	if (empty($_GET['table']) && empty($_GET['row']))
	{
		// Do file creation checking here, offer to FTP it, etc.
		$fp = @$fopen($_POST['path'], 'wb');
		if (!$fp && file_exists(dirname($_POST['path'])))
		{
			if (!get_ftp_info())
				return false;
			$fp = @$fopen($_POST['path'], 'wb');
			if (!$fp)
				return step1(sprintf('Unable to create the specified backup file, &quot;<em>%s</em>&quot;.', $_POST['path']));
		}
		elseif (!$fp)
			return step1(sprintf('Unable to create the specified backup file, &quot;<em>%s</em>&quot;.', $_POST['path']));

		// SQL Dump Header.
		$fwrite($fp,
			'# ==========================================================' . "\n" .
			'#' . "\n" .
			'# Database dump of tables in `' . $_POST['db_name'] . '`' . "\n" .
			'# ' . strftime('%Y-%m-%d') . "\n" .
			'#' . "\n" .
			'# ==========================================================' . "\n" .
			"\n");
	}
	else
		$fp = $fopen($_POST['path'], 'ab');

	$start_time = time();

	$result = $smcFunc['db_query']('', '
		SHOW TABLE STATUS{raw:like}',
		array(
			'like' => $_POST['db_prefix'] == '' ? '' : '
		LIKE "' . strtr($_POST['db_prefix'], array('_' => '\_')) . '"'
	));
	$tables = array();
	$table_row_lengths = array();
	$table_sizes = array();
	$total_size = 0;
	while ($table = $smcFunc['db_fetch_assoc']($result))
	{
		$tables[] = $table['Name'];
		$table_row_lengths[] = @$table['Avg_row_length'];
		$table_sizes[] = $table['Data_length'] + 1024;
		$total_size += $table['Data_length'] + 1024;
	}
	$smcFunc['db_free_result'](result);

	$result = $smcFunc['db_get_version']();
	list ($database_version) = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);

	// At first, this says "memory hog", but second it says "you can hit F5 if something goes wrong + no constant file access."
	$write_data = '';

	// For statistics.... speed, mostly.
	if (!empty($_POST['compress']) && function_exists('gzopen'))
		$before_length = gztell($fp);
	else
		$before_length = ftell($fp);

	for ($table = 0, $num_tables = count($tables); $table < $num_tables; $table++)
	{
		if ($table < $_GET['table'])
			continue;

		if ($_POST['db_type'] == 'mysql' && version_compare($database_version, '4.1.8') >= 0)
			$smcFunc['db_query']('', 'START TRANSACTION WITH CONSISTENT SNAPSHOT');
		else
			$smcFunc['db_query']('', '/*!32317 BEGIN */');
		$smcFunc['db_query']('', "/*!32317 SET AUTOCOMMIT = 0 */", array());

		if (empty($_GET['row']))
			$write_data .= "\n" .
				'#' . "\n" .
				'# Table structure for table `' . $tables[$table] . '`' . "\n" .
				'#' . "\n" .
				"\n" .
				'DROP TABLE IF EXISTS `' . $tables[$table] . '`;' . "\n" .
				"\n" .
				getCreateTable($tables[$table]) . ';' . "\n";

		$result = $smcFunc('', '
			SELECT COUNT(*)
			FROM {raw:table}',
			array(
				'table' => '`' . $tables[$table] . '`'
		));
		list ($num_rows) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		if ($num_rows == 0)
		{
			$_GET['row'] = 0;
			nextRow($_GET['row'], $table, $num_rows, $num_tables);
			continue;
		}

		if (empty($_GET['row']))
		{
			$write_data .= "\n" .
				'#' . "\n" .
				'# Dumping data in `' . $tables[$table] . '`' . "\n" .
				'#' . "\n" .
				"\n" .
				'/*!40000 ALTER TABLE `' . $tables[$table] . '` DISABLE KEYS */;' . "\n" .
				"\n";
		}

		$row = $_GET['row'];
		while ($row < $num_rows)
		{
			$result = $smcFunc['db_query']('', '
				SELECT /*!40001 SQL_NO_CACHE */ *
				FROM {raw:table}
				LIMIT {int:row}, {int:limit}',
				array(
					'table' => '`' . $tables[$table] . '`',
					'row' => $row,
					'limit' => $table_row_lengths[$table] >= 100 ? 96 : 192
			));
			$data = '';
			$i = 0;
			while ($values = $smcFunc['db_fetch_assoc']($result))
			{
				if ($data == '')
					$data = 'INSERT INTO `' . $tables[$table] . '`' . "\n" .
						"\t(`" . implode('`, `', array_keys($values)) . '`)' . "\n" .
						'VALUES ';

				// Get the fields in this row...
				$field_list = array();
				foreach ($values as $value)
				{
					if ($value == null)
						$field_list[] = 'NULL';
					elseif (is_numeric($value))
						$field_list[] = $value;
					else
						$field_list[] = $smcFunc['db_quote']('{string:value}', array('value' => $value));
				}

				$data .= '(' . implode(', ', $field_list) . '),' . "\n\t";
				$i++;

				if ($i % 10 == 0 && time() - $start_time > 15)
					break;
			}
			$write_data .= substr($data, 0, -3) . ';' . "\n";

			$row += $i;
			$smcFunc['db_free_result']($result);

			nextRow($row, $table, $num_rows, $num_tables, $fp);
		}

		$write_data .= "\n" .
			'/*!40000 ALTER TABLE `' . $tables[$table] . '` ENABLE KEYS */;' . "\n" .
			"\n" .
			'# --------------------------------------------------------' . "\n";
		$_GET['row'] = 0;

		$smcFunc['db_query']('', "/*!32317 COMMIT */", array());
	}

	$fwrite($fp, $write_data);
	$fclose($fp);

	echo '
				<div class="panel">
					<h2>Backup process complete!</h2>

					Congratulations!  Your database backup has been created successfully (assuming no errors were shown during processing).<br />
					<br />';

	if (dirname($_POST['path']) == dirname(__FILE__))
		echo '
					You can <a href="', basename($_POST['path']), '">download the backup now</a> if you wish to.  Please note that it\'s recommended that you put the backup in a place others cannot access by URL.<br />
					<br />';

	echo '
					If you had any problems, please <a href="http://www.simplemachines.org/community/index.php">tell us about them</a> so that we can help you get them resolved.
					<br />
					Good luck!<br />
					Simple Machines
				</div>';
}

function get_ftp_info()
{
	if (file_exists($_POST['path']))
		chmod($_POST['path'], 0777);

	if (isset($_POST['ftp_username']))
	{
		$ftp = new ftp_connection($_POST['ftp_server'], $_POST['ftp_port'], $_POST['ftp_username'], $_POST['ftp_password']);

		if ($ftp->error === false)
		{
			// Try it without /home/abc just in case they messed up.
			if (!$ftp->chdir($_POST['ftp_path']))
			{
				$ftp_error = $ftp->last_message;
				$ftp->chdir(preg_replace('~^/home[2]?/[^/]+?~', '', $_POST['ftp_path']));
			}

			if ($_POST['ftp_path'] != '')
			{
				$ftp_root = strtr(dirname(__FILE__), array($_POST['ftp_path'] => ''));
				if (substr($ftp_root, -1) == '/')
					$ftp_root = substr($ftp_root, 0, -1);
			}
			else
				$ftp_root = dirname(__FILE__);

			if ($ftp_root != '')
				$ftp_file = strtr($_POST['path'], array($ftp_root => ''));
			else
				$ftp_file = $_POST['path'];

			$ftp->create_file($ftp_file);
			@chmod($_POST['path'], 0777);
			if (file_exists($_POST['path']) && !is_writable($_POST['path']))
				$ftp->chmod($ftp_file, 0777);

			if ($ftp->error === false || is_writable($_POST['path']))
				return true;
		}

		if (!isset($ftp_error))
			$ftp_error = $ftp->last_message;
	}

	if (!isset($ftp) || $ftp->error !== false)
	{
		if (!isset($ftp))
			$ftp = new ftp_connection(null);
		// Save the error so we can mess with listing...
		elseif ($ftp->error !== false && !isset($ftp_error))
			$ftp_error = $ftp->last_message === null ? '' : $ftp->last_message;

		list ($username, $detect_path, $found_path) = $ftp->detect_path(dirname(__FILE__));

		if ($found_path || !isset($_POST['ftp_path']))
			$_POST['ftp_path'] = $detect_path;

		if (!isset($_POST['ftp_username']))
			$_POST['ftp_username'] = $username;

		echo '
				<div class="panel">
					<h2>FTP Information</h2>
					<h3>The file you specified either could not be created or could not be written to.  Please enter your FTP connection details so this tool can create the file for you.</h3>';

		if (isset($ftp_error))
			echo '
					<div class="error_message">
						<div style="color: red;">
							Unable to connect to FTP server with this combination of details.<br />
							<br />
							<code>', $ftp_error, '</code>
						</div>
					</div>
					<br />';

		echo '
					<form action="', $_SERVER['PHP_SELF'], '?step=2" method="post">

						<table width="520" cellspacing="0" cellpadding="0" border="0" align="center" style="margin-bottom: 1ex;">
							<tr>
								<td width="26%" valign="top" class="textbox"><label for="ftp_server">Server:</label></td>
								<td>
									<div style="float: right; margin-right: 1px;"><label for="ftp_port" class="textbox"><strong>Port:&nbsp;</strong></label> <input type="text" size="3" name="ftp_port" id="ftp_port" value="', isset($_POST['ftp_port']) ? $_POST['ftp_port'] : '21', '" class="input_text" /></div>
									<input type="text" size="30" name="ftp_server" id="ftp_server" value="', isset($_POST['ftp_server']) ? $_POST['ftp_server'] : 'localhost', '" style="width: 70%;" class="input_text" />
									<div style="font-size: smaller; margin-bottom: 2ex;">This should be the server and port for your FTP server.</div>
								</td>
							</tr><tr>
								<td width="26%" valign="top" class="textbox"><label for="ftp_username">Username:</label></td>
								<td>
									<input type="text" size="50" name="ftp_username" id="ftp_username" value="', isset($_POST['ftp_username']) ? $_POST['ftp_username'] : '', '" style="width: 99%;" class="input_text" />
									<div style="font-size: smaller; margin-bottom: 2ex;">The username to login with. <em>This will not be saved anywhere.</em></div>
								</td>
							</tr><tr>
								<td width="26%" valign="top" class="textbox"><label for="ftp_password">Password:</label></td>
								<td>
									<input type="password" size="50" name="ftp_password" id="ftp_password" style="width: 99%;" class="input_password" />
									<div style="font-size: smaller; margin-bottom: 3ex;">The password to login with. <em>This will not be saved anywhere.</em></div>
								</td>
							</tr><tr>
								<td width="26%" valign="top" class="textbox"><label for="ftp_path">FTP Path:</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="text" size="50" name="ftp_path" id="ftp_path" value="', $_POST['ftp_path'], '" style="width: 99%;" class="input_text" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', !empty($found_path) ? 'This path was automatically detected.' : 'This is the <em>relative</em> path to this file as seen in an FTP client.', '</div>
								</td>
							</tr>
						</table>

						<input type="hidden" name="db_server" value="', $_POST['db_server'], '" />
						<input type="hidden" name="db_user" value="', $_POST['db_user'], '" />
						<input type="hidden" name="db_passwd" value="', $_POST['db_passwd'], '" />
						<input type="hidden" name="db_name" value="', $_POST['db_name'], '" />
						<input type="hidden" name="db_prefix" value="', $_POST['db_prefix'], '" />
						<input type="hidden" name="path" value="', $_POST['path'], '" />
						<input type="hidden" name="compress" value="', !empty($_POST['compress']) ? '1' : '0', '" />

						<div class="righttext" style="margin: 1ex; margin-top: 2ex;"><input type="submit" value="Connect" class="button_submit" /></div>
					</form>
				</div>';
	}

	return false;
}

function show_header()
{
	global $start_time;
	$start_time = time();

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta name="robots" content="noindex" />
		<title>Backup Creation Tool</title>
		<style type="text/css">
			body
			{
				font-family: Verdana, sans-serif;
				background-color: #d4d4d4;
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
				background-color: #e1e1e1;
				margin: 1ex 4ex;
				padding: 1.5ex;
			}
			div.panel
			{
				border: 1px solid gray;
				background-color: #f0f0f0;
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
			.centertext
			{
				margin: 0 auto;
				text-align: center;
			}
			.righttext
			{
				margin-left: auto;
				margin-right: 0;
				text-align: right;
			}
			.lefttext
			{
				margin-left: 0;
				margin-right: auto;
				text-align: left;
			}
		</style>
	</head>
	<body>
		<div id="header">
			', file_exists(dirname(__FILE__) . '/Themes/default/images/smflogo.gif') ? '<a href="http://www.simplemachines.org/" target="_blank"><img src="Themes/default/images/smflogo.gif" style="width: 250px; float: right;" alt="Simple Machines" border="0" /></a>
			' : '', '<div title="Belthasar">Backup Creation Tool</div>
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

function nextRow($row, $table, $max_rows, $max_tables, $fp = null)
{
	global $start_time, $table_sizes, $total_size, $before_length, $write_data;

	@set_time_limit(300);
	if (function_exists('apache_reset_timeout'))
		@apache_reset_timeout();

	if (!isset($_GET['table']) || $_GET['table'] < $table)
		$_GET['table'] = $table;
	if (!isset($_GET['row']) || $_GET['row'] < $row)
		$_GET['row'] = $row;
	$_SESSION['smf_create_backup'] = array($_GET['table'], $_GET['row']);

	if (strlen($write_data) > 32768)
	{
		if (!empty($_POST['compress']) && function_exists('gzopen'))
			gzwrite($fp, $write_data);
		else
			fwrite($fp, $write_data);
		$write_data = '';
	}

	if (time() - $start_time <= 15)
		return;

	if ($fp)
	{
		if (!empty($_POST['compress']) && function_exists('gzopen'))
		{
			$now_length = gztell($fp);
			gzwrite($fp, $write_data);
			gzclose($fp);
		}
		else
		{
			$now_length = ftell($fp);
			fwrite($fp, $write_data);
			fclose($fp);
		}
	}

	$query_string = '';
	foreach ($_GET as $k => $v)
		$query_string .= '&amp;' . $k . '=' . $v;
	if (strlen($query_string) != 0)
		$query_string = '?' . substr($query_string, 5);

	$current_size = 0;
	for ($i = 0; $i < $table; $i++)
		$current_size += $table_sizes[$i];

	if ($max_rows != 0)
		$current_size += ($table_sizes[$table] * $row) / $max_rows;
	else
		$current_size += $table_sizes[$table];

	$percentage = round(($current_size * 100) / $total_size);

	if (time() - $start_time > 0)
		$speed = ($now_length - $before_length) / (time() - $start_time);
	else
		$speed = 0;

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

			<p>Please note that this percentage only makes a rough estimate of the data to be backed up.  Especially when you have a large database, it may not seem to move quickly at times.  It is only an approximation of progress.</p>
			<p>Data is currently being written at approximately ', round($speed / 1024, 3), ' kilobytes per second.</p>

			<form action="', $_SERVER['PHP_SELF'], $query_string, '" method="post" name="autoSubmit">
				<input type="hidden" name="db_type" value="', $_POST['db_type'], '" />
				<input type="hidden" name="db_server" value="', $_POST['db_server'], '" />
				<input type="hidden" name="db_user" value="', $_POST['db_user'], '" />
				<input type="hidden" name="db_passwd" value="', $_POST['db_passwd'], '" />
				<input type="hidden" name="db_name" value="', $_POST['db_name'], '" />
				<input type="hidden" name="db_prefix" value="', $_POST['db_prefix'], '" />
				<input type="hidden" name="path" value="', $_POST['path'], '" />
				<input type="hidden" name="compress" value="', !empty($_POST['compress']) ? '1' : '0', '" />

				<div class="righttext" style="margin: 1ex;"><input name="b" type="submit" value="Continue" class="button_submit" /></div>
			</form>
			<script type="text/javascript"><!-- // --><![CDATA[
				window.onload = doAutoSubmit;
				var countdown = 2;

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

// Get the schema (CREATE) for a table.
function getCreateTable($tableName)
{
	// Start the create table...
	$schema_create = 'CREATE TABLE `' . $tableName . '` (' . "\n";

	// Find all the fields.
	$result = $smcFunc['db_query']('', '
		SHOW FIELDS
		FROM {raw:table}}',
		array(
			'table' => '`' . $tableName . '`',
	));
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		// Make the CREATE for this column.
		$schema_create .= ' `' . $row['Field'] . '` ' . $row['Type'] . ($row['Null'] != 'YES' ? ' NOT NULL' : '');

		// Add a default...?
		if (isset($row['Default']))
			$schema_create .= ' default ' . (is_numeric($row['Default']) ? $row['Default'] : $smcFunc['db_quote']('{string:value}', array('value' => $row['Default'])));

		// And now any extra information. (such as auto_increment.)
		$schema_create .= ($row['Extra'] != '' ? ' ' . $row['Extra'] : '') . ',' . "\n";
	}
	$smcFunc['db_free_result']($result);

	// Take off the last comma.
	$schema_create = substr($schema_create, 0, -2);

	// Find the keys.
	$result = $smcFunc['db_query']('', '
		SHOW KEYS
		FROM {raw:table}}',
		array(
			'table' => '`' . $tableName . '`',
	));
	$indexes = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		// IS this a primary key, unique index, or regular index?
		$row['Key_name'] = $row['Key_name'] == 'PRIMARY' ? 'PRIMARY KEY' : (empty($row['Non_unique']) ? 'UNIQUE ' : ($row['Comment'] == 'FULLTEXT' || (isset($row['Index_type']) && $row['Index_type'] == 'FULLTEXT') ? 'FULLTEXT ' : 'KEY ')) . '`' . $row['Key_name'] . '`';

		// Is this the first column in the index?
		if (empty($indexes[$row['Key_name']]))
			$indexes[$row['Key_name']] = array();

		// A sub part, like only indexing 15 characters of a varchar.
		if (!empty($row['Sub_part']))
			$indexes[$row['Key_name']][$row['Seq_in_index']] = '`' . $row['Column_name'] . '`(' . $row['Sub_part'] . ')';
		else
			$indexes[$row['Key_name']][$row['Seq_in_index']] = '`' . $row['Column_name'] . '`';
	}
	$smcFunc['db_free_result']($result);

	// Build the CREATEs for the keys.
	foreach ($indexes as $keyname => $columns)
	{
		// Ensure the columns are in proper order.
		ksort($columns);

		$schema_create .= ',' . "\n" . ' ' . $keyname . ' (' . implode($columns, ', ') . ')';
	}

	// Now just get the comment and type... (MyISAM, etc.)
	$result = mysql_query("
		SHOW TABLE STATUS
		LIKE '" . strtr($tableName, array('_' => '\\_', '%' => '\\%')) . "'");
	$row = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	// Probably MyISAM.... and it might have a comment.
	$schema_create .= "\n" . ') ' . (!empty($row['Engine']) ? 'ENGINE' : 'TYPE') . '=' . (isset($row['Type']) ? $row['Type'] : $row['Engine']) . ($row['Comment'] != '' ? ' COMMENT="' . $row['Comment'] . '"' : '');

	return $schema_create;
}

// http://www.faqs.org/rfcs/rfc959.html
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
			$data .= fread($fp, 4096);
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

// Compat mode!
function smc_compat_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, $db_options = array())
{
	global $mysql_set_mod, $sourcedir, $db_connection, $db_prefix, $smcFunc;

	if (!empty($db_options['persist']))
		$db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);
	else
		$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);

	// Something's wrong, show an error if its fatal (which we assume it is)
	if (!$db_connection)
	{
		if (!empty($db_options['non_fatal']))
			return null;
		else
		{
			if (file_exists($sourcedir . '/Subs-Auth.php'))
			{
				require_once($sourcedir . '/Subs-Auth.php');
				show_db_error();
			}
			exit('Sorry, SMF was unable to connect to database.');
		}
	}

	// Select the database, unless told not to
	if (empty($db_options['dont_select_db']) && !@mysql_select_db($db_name, $connection) && empty($db_options['non_fatal']))
	{
		if (file_exists($sourcedir . '/Subs-Auth.php'))
		{
			require_once($sourcedir . '/Subs-Auth.php');
			show_db_error();
		}
		exit('Sorry, SMF was unable to connect to database.');
	}
	else
		$db_prefix = is_numeric(substr($db_prefix, 0, 1)) ? $db_name . '.' . $db_prefix : '`' . $db_name . '`.' . $db_prefix;

	// Some core functions.
	function smf_db_replacement__callback($matches)
	{
		global $db_callback, $user_info, $db_prefix;

		list ($values, $connection) = $db_callback;

		if ($matches[1] === 'db_prefix')
			return $db_prefix;

		if ($matches[1] === 'query_see_board')
			return $user_info['query_see_board'];

		if ($matches[1] === 'query_wanna_see_board')
			return $user_info['query_wanna_see_board'];

		if (!isset($matches[2]))
			smf_db_error_backtrace('Invalid value inserted or no type specified.', '', E_USER_ERROR, __FILE__, __LINE__);

		if (!isset($values[$matches[2]]))
			smf_db_error_backtrace('The database value you\'re trying to insert does not exist: ' . htmlspecialchars($matches[2]), '', E_USER_ERROR, __FILE__, __LINE__);

		$replacement = $values[$matches[2]];

		switch ($matches[1])
		{
			case 'int':
				if (!is_numeric($replacement) || (string) $replacement !== (string) (int) $replacement)
					smf_db_error_backtrace('Wrong value type sent to the database. Integer expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
				return (string) (int) $replacement;
			break;

			case 'string':
			case 'text':
				return sprintf('\'%1$s\'', mysql_real_escape_string($replacement, $connection));
			break;

			case 'array_int':
				if (is_array($replacement))
				{
					if (empty($replacement))
						smf_db_error_backtrace('Database error, given array of integer values is empty. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

					foreach ($replacement as $key => $value)
					{
						if (!is_numeric($value) || (string) $value !== (string) (int) $value)
							smf_db_error_backtrace('Wrong value type sent to the database. Array of integers expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

						$replacement[$key] = (string) (int) $value;
					}

					return implode(', ', $replacement);
				}
				else
					smf_db_error_backtrace('Wrong value type sent to the database. Array of integers expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

			break;

			case 'array_string':
				if (is_array($replacement))
				{
					if (empty($replacement))
						smf_db_error_backtrace('Database error, given array of string values is empty. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

					foreach ($replacement as $key => $value)
						$replacement[$key] = sprintf('\'%1$s\'', mysql_real_escape_string($value, $connection));

					return implode(', ', $replacement);
				}
				else
					smf_db_error_backtrace('Wrong value type sent to the database. Array of strings expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
			break;

			case 'date':
				if (preg_match('~^(\d{4})-([0-1]?\d)-([0-3]?\d)$~', $replacement, $date_matches) === 1)
					return sprintf('\'%04d-%02d-%02d\'', $date_matches[1], $date_matches[2], $date_matches[3]);
				else
					smf_db_error_backtrace('Wrong value type sent to the database. Date expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
			break;

			case 'float':
				if (!is_numeric($replacement))
					smf_db_error_backtrace('Wrong value type sent to the database. Floating point number expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
				return (string) (float) $replacement;
			break;

			case 'identifier':
				// Backticks inside identifiers are supported as of MySQL 4.1. We don't need them for SMF.
				return '`' . strtr($replacement, array('`' => '', '.' => '')) . '`';
			break;

			case 'raw':
				return $replacement;
			break;

			default:
				smf_db_error_backtrace('Undefined type used in the database query. (' . $matches[1] . ':' . $matches[2] . ')', '', false, __FILE__, __LINE__);
			break;
		}
	}

	// Because this is just compat mode, this is good enough.
	function smf_db_query($execute = true, $db_string, $db_values)
	{
		global $db_callback, $db_connection;

		// Only bother if there's something to replace.
		if (strpos($db_string, '{') !== false)
		{
			// This is needed by the callback function.
			$db_callback = array($db_values, $db_connection);

			// Do the quoting and escaping
			$db_string = preg_replace_callback('~{([a-z_]+)(?::([a-zA-Z0-9_-]+))?}~', 'smf_db_replacement__callback', $db_string);

			// Clear this global variable.
			$db_callback = array();
		}

		// We actually make the query in compat mode.
		if ($execute === false)
			return $db_string;
		return mysql_query($db_string, $db_connection);
	}

	// Insert some data...
	function smf_db_insert($method = 'replace', $table, $columns, $data, $keys, $disable_trans = false)
	{
		global $smcFunc, $db_connection, $db_prefix;

		// With nothing to insert, simply return.
		if (empty($data))
			return;

		// Replace the prefix holder with the actual prefix.
		$table = str_replace('{db_prefix}', $db_prefix, $table);

		// Inserting data as a single row can be done as a single array.
		if (!is_array($data[array_rand($data)]))
			$data = array($data);

		// Create the mold for a single row insert.
		$insertData = '(';
		foreach ($columns as $columnName => $type)
		{
			// Are we restricting the length?
			if (strpos($type, 'string-') !== false)
				$insertData .= sprintf('SUBSTRING({string:%1$s}, 1, ' . substr($type, 7) . '), ', $columnName);
			else
				$insertData .= sprintf('{%1$s:%2$s}, ', $type, $columnName);
		}
		$insertData = substr($insertData, 0, -2) . ')';

		// Create an array consisting of only the columns.
		$indexed_columns = array_keys($columns);

		// Here's where the variables are injected to the query.
		$insertRows = array();
		foreach ($data as $dataRow)
			$insertRows[] = smf_db_query(true, $insertData, array_combine($indexed_columns, $dataRow));

		// Determine the method of insertion.
		$queryTitle = $method == 'replace' ? 'REPLACE' : ($method == 'ignore' ? 'INSERT IGNORE' : 'INSERT');

		// Do the insert.
		$smcFunc['db_query']('', '
			' . $queryTitle . ' INTO ' . $table . '(`' . implode('`, `', $indexed_columns) . '`)
			VALUES
				' . implode(',
				', $insertRows),
			array(
				'security_override' => true,
			)
		);
	}

	// Now, go functions, spread your love.
	$smcFunc['db_free_result'] = 'mysql_free_result';
	$smcFunc['db_fetch_row'] = 'mysql_fetch_row';
	$smcFunc['db_fetch_assoc'] = 'mysql_fetch_assoc';
	$smcFunc['db_num_rows'] = 'mysql_num_rows';
	$smcFunc['db_insert'] = 'smf_db_insert';
	$smcFunc['db_query'] = 'smf_db_query';
	$smcFunc['db_quote'] = 'smf_db_query';
	$smcFunc['db_error'] = 'mysql_error';

	return $db_connection;
}

function smc_compat_database($db_type, $db_server, $db_user, $db_passwd, $db_name)
{
	global $smcFunc, $db_connection;

	// Gonna need a lot of memory.
	if (@ini_get('memory_limit') < 128)
		@ini_set('memory_limit', '128M');
	@set_time_limit(300);
	ignore_user_abort(true);
	if (function_exists('apache_reset_timeout'))
		@apache_reset_timeout();

	// Attempt to make a connection.
	$db_connection = false;
	if (file_exists(dirname(__FILE__) . '/Settings.php'))
		require_once(dirname(__FILE__) . '/Settings.php');
	if (isset($sourcedir))
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

		// compat mode. Active!
		if (!file_exists($sourcedir . '/Subs-Db-' . $db_type . '.php') && $db_type == 'mysql')
		{
			// First try a persistent connection.
			$db_connection = smc_compat_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true, 'persist' => true));

			if (!$db_connection)
				$db_connection = smc_compat_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true));
		}
		else
		{
			require_once($sourcedir . '/Subs-Db-' . $db_type . '.php');
			$db_connection = smf_db_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true, 'persist' => true));

			if (!$db_connection)
				$db_connection = smf_db_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true));
		}
	}
	else
	{
		$db_connection = smc_compat_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true, 'persist' => true));

		if (!$db_connection)
			$db_connection = smc_compat_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true));
	}

	// No version?
	if (empty($smcFunc['db_get_version']) && function_exists('db_extend'))
		db_extend('extra');
	if (empty($smcFunc['db_get_version']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT VERSION()',
			array(
		));
		list ($smcFunc['db_get_version']) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
	}

	return $db_connection;
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