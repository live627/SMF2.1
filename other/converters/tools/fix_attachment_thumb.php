<?php
/**********************************************************************************
* fix_attachment_thumb.php                                                        *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1                                             *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
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

// This setting is used to disable the use of the script completely.
$disabled = 0;

// Check if the script is disabled.  If so then die!!!!!!!! >:D
if ($disabled)
	die('The use of this script us currently disabled.  Please edit the file and change $disabled = 1 to $disable = 0.  This will allow you to use this script.  Once you are done using it, it\'s recommended that you change it back to 1.');

// Load the required file.
if (file_exists(dirname(__FILE__) . '/SSI.php'))
	require_once('SSI.php');
else
	die ('Please move this file to your forum\'s root dir');

// Check if allowed to be in here
isAllowedTo('admin_forum');

// Disable magic quotes, report errors and ignore user abort.
@set_magic_quotes_runtime(0);
error_reporting(E_ALL);
ignore_user_abort(true);

// Substep?
$_GET['step'] = isset($_GET['step']) ? (int) $_GET['step'] : 0;
$_GET['start'] = isset($_GET['start']) ? (int) $_GET['start'] : 0;

// Now the fun part.  Show them a header.
show_header();

show_form();

show_footer();

function fixAttachments()
{
	global $db_prefix, $context, $smfFunc;

	// Extensions
	$imageExtension = array('jpg', 'gif', 'png');

	if (count($imageExtension) > 1)
	{
		$extensions = "(filename LIKE '%.$imageExtension[0]'";

		for ($i = 1; $i < count($imageExtension); $i++)
			$extensions .= " OR filename LIKE '%." . trim($imageExtension[$i]) . "'";

		$extensions .= ')';
	}
	else
		$extensions = "(filename LIKE '%." . trim($imageExtension[0]) . "')";

	// Get the attachments w/o a width and height.
	$request = db_query("
		SELECT ID_ATTACH, filename, width, height
		FROM {$db_prefix}attachments
		WHERE $extensions
			AND width = 0
			AND height = 0
			AND ID_THUMB = 0
			AND ID_MEMBER = 0
		LIMIT $_GET[start], 100", __FILE__, __LINE__);

	// Save to an array.
	$attachments = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Need to find out the correct name to loop up the image.
		$filename = getLegacyAttachmentFilename($row['filename'], $row['ID_ATTACH']);

		// Get the width and height for it.
		list ($width, $height) = getimagesize($filename);

		$attachments[$row['ID_ATTACH']] = array(
			'ID_ATTACH' => $row['ID_ATTACH'],
			'filename' => $row['filename'],
			'width' => $width,
			'height' => $height,
			'pass' => empty($width) || empty($height) ? false : true,
		);

		// Update it.
		foreach ($attachments as $attachment)
		{
			if ($attachment['pass'])
				db_query("
					UPDATE {$db_prefix}attachments
					SET
						width = $attachment[width],
						height = $attachment[height]
					WHERE ID_ATTACH = $attachment[ID_ATTACH]", __FILE__, __LINE__);
		}
	}
	mysql_free_result($request);

	// Are we done?
	if (count($attachments) < 100)
		$_GET['step'] = 2;
	else
		$_GET['start'] += 100;

	// Return it.
	return $attachments;
}

function show_form()
{
	// Some info.
	echo '
				<h2>Fix Image Attachments Dimensions</h2>
				<h3>This script will fix the width and height of attachments that don\'thave one set.
				 This script is mainly for those that have converted but have missing thumbnails.
				 Once you get the width and height for the attachments, thumbnails will be created when you enter the topic.
				</h3>';

	// Start?
	if ($_GET['step'] === 0)
		echo '
				<form action="', $_SERVER['PHP_SELF'], '?step=1;start=', $_GET['start'], '" method="post">
					<div class="righttext" style="margin: 1ex;"><input name="letsgo" type="submit" value="Start" class="button_submit" /></div>
				</form>';

	if ($_GET['step'] === 1)
	{
		$attachments = fixAttachments();

		// Do we have anything?
		if (!empty($attachments))
		{
			// Nice Table.
			echo '
					<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%" class="bordercolor">
						<tr class="titlebg">
							<td width="2%" align="center">ID_ATTACH</td>
							<td>Filename</td>
							<td width="3%" align="center">Width</td>
							<td width="3%" align="center">Height</td>
							<td width="6%" align="center">Fixed?</td>
						</tr>';

			// Loop.
			$alternate = true;
			foreach ($attachments as $attachment)
			{
				echo '
						<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
							<td>', $attachment['ID_ATTACH'], '</td>
							<td>', $attachment['filename'], '</td>
							<td>', $attachment['width'], 'px</td>
							<td>', $attachment['height'], 'px</td>
							<td style="background-color: ', $attachment['pass'] ? 'green' : 'red', ';">', $attachment['pass'] ? 'Fixed' : 'Not Fixed', '</td>
						</tr>';
				$alternate = !$alternate;
			}

			echo '
					</table>';
		}

		// Remove it if we on step 2.
		if ($_GET['step'] == 1)
			echo '
				<form action="', $_SERVER['PHP_SELF'], '?step=', $_GET['step'], ';start=', $_GET['start'], '" method="post" name="autoSubmit">
					<div class="righttext" style="margin: 1ex;"><input name="b" type="submit" value="Continue" class="button_submit" /></div>
				</form>';
	}

	if ($_GET['step'] == 2 && $_GET['start'] == 0)
	{
		echo '
				<h2>Done!.  Images should now have a correct width and height.</h2>';
	}
}

function show_header()
{
	global $txt;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
	<title>Fix Image Attachments Dimensions</title>
		<style type="text/css">
			/* Normal, standard links. */
			a:link
			{
				color: #476C8E;
				text-decoration: none;
			}
			a:visited
			{
				color: #476C8E;
				text-decoration: none;
			}
			a:hover
			{
				text-decoration: underline;
			}
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
			.titlebg, tr.titlebg th, tr.titlebg td, .titlebg2, tr.titlebg2 th, tr.titlebg2 td
			{
				color: black;
				font-style: normal;
				background: url(Themes/default/images/titlebg.jpg) #E9F0F6 repeat-x;
				border-bottom: solid 1px #9BAEBF;
				border-top: solid 1px #FFFFFF;
				padding-left: 10px;
				padding-right: 10px;
			}
			.titlebg, .titlebg a:link, .titlebg a:visited
			{
				font-weight: bold;
				color: black;
				font-style: normal;
			}
			.titlebg a:hover
			{
				color: #404040;
			}
			.bordercolor
			{
				background-color: #ADADAD;
				padding: 0px;
			}
			.windowbg
			{
				color: #000000;
				background-color: #ECEDF3;
			}
			.windowbg2
			{
				color: #000000;
				background-color: #F6F6F6;
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
		<script type="text/javascript"><!-- // --><![CDATA[
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
	</head>
	<body>
		<div id="header">
			<a href="http://www.simplemachines.org/" target="_blank"><img src="Themes/default/images/smflogo.gif" style=" float: right;" alt="Simple Machines" border="0" /></a>
			<div title="Monkey boy was here!">Fix Image Attachments Dimensions</div>
		</div>
		<div id="content">
			<div class="panel">';
}

// Show the footer.
function show_footer()
{
	echo '
			</div>
		</div>
	</body>
</html>';
}

?>