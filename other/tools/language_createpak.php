<?php
/******************************************************************************
* language_createpak.php                                                      *
*******************************************************************************
* SMF: Simple Machines Forum                                                  *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                *
* =========================================================================== *
* Software Version:           SMF 2.0 Alpha                                   *
* Software by:                Simple Machines (http://www.simplemachines.org) *
* Copyright 2001-2006 by:     Lewis Media (http://www.lewismedia.com)         *
* Support, News, Updates at:  http://www.simplemachines.org                   *
*******************************************************************************
* This program is free software; you may redistribute it and/or modify it     *
* under the terms of the provided license as published by Lewis Media.        *
*                                                                             *
* This program is distributed in the hope that it is and will be useful,      *
* but WITHOUT ANY WARRANTIES; without even any implied warranty of            *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                        *
*                                                                             *
* See the "license.txt" file for details of the Simple Machines license.      *
* The latest version can always be found at http://www.simplemachines.org.    *
******************************************************************************/
/*	                                                                          *
*	See language_settings.php for a description of the language tools         *
*	                                                                          *
******************************************************************************/

require_once('./language_settings.php');

// Initialize languages.
$languages = array();
$dir = dir($basedir);
while ($entry = $dir->read())
{
	if (substr($entry, 0, 1) != '.' && is_dir($basedir . '/' .$entry))
	{
		$languages[$entry] = array(
			'id' => $entry,
			'versions' => array(),
		);
		$dir2 = dir($basedir . '/' . $entry);
		while ($entry2 = $dir2->read())
		{
			if (substr($entry2, 0, 1) != '.' && is_dir($basedir . '/' . $entry . '/' . $entry2))
			{
				$languages[$entry]['versions'][$entry2] = array(
					'id' => $entry2,
				);
			}
		}
		$dir2->close();
	}
}
$dir->close();



if (empty($_REQUEST['step']))
{
	echo '
	<form action="', $_SERVER['PHP_SELF'], '" method="post">
		<table>
			<tr>
				<th align="right">Language to be packed:</th>
				<td><select name="target_language" style="width: 20em;">';
	foreach ($languages as $lang)
		echo '
					<option value="', $lang['id'], '">', $lang['id'], '</option>';
	echo '
				</select></td>
			</tr><tr>
				<td align="right" colspan="2">
					<input type="hidden" name="step" value="2" />
					<input type="submit" value="Continue" />
				</td>
			</tr>
		</table>
	</form>';
}

elseif ($_REQUEST['step'] == 2)
{
	echo '
	<form action="', $_SERVER['PHP_SELF'], '" method="post">
		<table>
			<tr>
				<th align="right">Version to be packed:</th>
				<td><select name="target_name" style="width: 20em;">';
	foreach ($languages[$_REQUEST['target_language']]['versions'] as $version)
		echo '
					<option value="', $version['id'], '">', $_REQUEST['target_language'], ' ', $version['id'], '</option>';
	echo '
				</select></td>
			</tr><tr>
				<td align="right" colspan="2">
					<input type="hidden" name="step" value="3" />
					<input type="hidden" name="target_language" value="', $_REQUEST['target_language'], '" />
					<input type="submit" value="Continue" />
				</td>
			</tr>
		</table>
	</form>';
}

elseif ($_REQUEST['step'] == 3)
{
	chdir($basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['target_name']);
	exec($bsdtar . ' -cyf ../smf_' . $_REQUEST['target_name'] . '_' . $_REQUEST['target_language'] . '.tar.bz2 *', $output);
	print_r($output);

	chdir($basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['target_name']);
	exec($bsdtar . ' -czf ../smf_' . $_REQUEST['target_name'] . '_' . $_REQUEST['target_language'] . '.tar.gz *', $output);
	print_r($output);

	chdir($basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['target_name']);
	exec($zip . ' -rq ../smf_' . $_REQUEST['target_name'] . '_' . $_REQUEST['target_language'] . '.zip *', $output);
	print_r($output);
}

?>