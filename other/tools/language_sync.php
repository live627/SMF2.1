<?php
/**********************************************************************************
* language_sync.php                                                               *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 3                                      *
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

/**********************************************************************************
*	See language_settings.php for a description of the language tools             *
**********************************************************************************/

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
					'files' => isset($_REQUEST['target_name']) && ($entry === $_REQUEST['target_language'] || $entry === 'english') ? readDirRecursive($basedir . '/' . $entry . '/' . $entry2) : array(),
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
				<th align="right">Target version:</th>
				<td><select name="target_version" style="width: 20em;">';
	foreach ($languages['english']['versions'] as $version)
		echo '
					<option value="', $version['id'], '">', $version['id'], '</option>';
	echo '
				</select></td>
			</tr><tr>
				<th align="right">Target language:</th>
				<td><select name="target_language" style="width: 20em;">';
	foreach ($languages as $lang)
		if ($lang['id'] != 'english')
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
				<th align="right">Source version:</th>
				<td><select name="src_version" style="width: 20em;">';
	foreach ($languages[$_REQUEST['target_language']]['versions'] as $version)
		echo '
					<option value="', $version['id'], '">', $_REQUEST['target_language'], ' ', $version['id'], '</option>';
	echo '
				</select></td>
			</tr><tr>
				<th align="right">Target name:</th>
				<td><input type="text" name="target_name" value="', $_REQUEST['target_version'], '_', strftime('%Y%m%d_%H%M%S'), '" style="width: 20em;" /></td>
			</tr><tr>
				<th align="right">Create .tar.gz archive:</th>
				<td><input type="checkbox" name="create_gz" value="1" checked="checked" /></td>
			</tr><tr>
				<th align="right">Create .tar.bz2 archive:</th>
				<td><input type="checkbox" name="create_bz2" value="1" checked="checked" /></td>
			</tr><tr>
				<th align="right">Create .zip archive:</th>
				<td><input type="checkbox" name="create_zip" value="1" checked="checked" /></td>
			</tr><tr>
				<td align="right" colspan="2">
					<input type="hidden" name="step" value="3" />
					<input type="hidden" name="target_language" value="', $_REQUEST['target_language'], '" />
					<input type="hidden" name="target_version" value="', $_REQUEST['target_version'], '" />
					<input type="submit" value="Continue" />
				</td>
			</tr>
		</table>
	</form>';
}
elseif ($_REQUEST['step'] == 3)
{
	// Create target directory.
	if (is_dir($basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['target_name']))
		die('Target directory already exists.');
	mkdir($basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['target_name']);


	// Loop through the English files.
	if (!isset($languages['english']['versions'][$_REQUEST['target_version']]))
		die('English reference language not found.');
	foreach ($languages['english']['versions'][$_REQUEST['target_version']]['files'] as $cur_file)
	{
		// Make sure the directory of this file exists in the target directory.
		$cur_dir = strtr(substr($cur_file, 1, strrpos($cur_file, '/')), array('english' => $_REQUEST['target_language']));
		$tmp_dir = $basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['target_name'];
		foreach (explode('/', $cur_dir) as $sub_dir)
		{
			$tmp_dir .= '/' . $sub_dir;

			if (!empty($cur_dir) && !file_exists($tmp_dir))
				mkdir($tmp_dir);
		}

		// If it's PHP then it's probably a language file.
		if (strrchr($cur_file, '.') === '.php')
		{
			// If the file doesn't exist in the other language, simply copy the english file.
			if (!file_exists($basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['src_version'] . strtr($cur_file, array('english' => $_REQUEST['target_language']))))
			{
				echo 'There\'s no reference file for ', $cur_file, ' yet. Using the english version.<br />';
				$ref_lang = readLanguage($basedir . '/english/' . $_REQUEST['target_version'] . $cur_file);
			}
			else
				$ref_lang = readLanguage($basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['src_version'] . strtr($cur_file, array('english' => $_REQUEST['target_language'])));

			$english_lang = readLanguage($basedir . '/english/' . $_REQUEST['target_version'] . $cur_file);

			// The english file is used as template...
			$target_lang = $english_lang;
			// ...but of course with a different path.
			$target_lang['path'] = $basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['target_name'] . strtr($cur_file, array('english' => $_REQUEST['target_language']));

			$untranslated = false;
			foreach ($english_lang['lines'] as $key => $line)
			{
				if ($line['new_paragraph'])
					$untranslated = false;
				if (isset($ref_lang['lines'][$key]))
				{
					$target_lang['lines'][$key] = $ref_lang['lines'][$key];
					unset($ref_lang['lines'][$key]);
					$untranslated = false;
				}
				elseif (!$untranslated)
				{
					$untanslated = true;
					$target_lang['lines'][$key]['pre_comment'][] = "// Untranslated!\n";
				}
			}
			foreach ($ref_lang['lines'] as $line)
				echo 'Reference language ', $ref_lang['path'], ' has ', $line['var_name'], $line['hash'] === null ? '' : '[' . (empty($line['hash']) ? '' : $line['hash']) . ']', ' translated, which isn\'t used in the english file.<br />';

			writeLanguage($target_lang);
		}

		// Otherwise it's an image or whatever.
		else
		{
			if (file_exists($basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['src_version'] . strtr($cur_file, array('english' => $_REQUEST['target_language']))))
			{
				if (!copy($basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['src_version'] . strtr($cur_file, array('english' => $_REQUEST['target_language'])), $basedir . '/' . $_REQUEST['target_language'] . '/' .  $_REQUEST['target_name'] . strtr($cur_file, array('english' => $_REQUEST['target_language']))))
					echo 'Error copying file ', $basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['src_version'] . strtr($cur_file, array('english' => $_REQUEST['target_language'])), ' to ', $basedir . '/' . $_REQUEST['target_language'] . '/' .  $_REQUEST['target_name'] . strtr($cur_file, array('english' => $_REQUEST['target_language'])), '<br />';
			}
			else
			{
				if (!copy($basedir . '/english/' . $_REQUEST['target_version'] . $cur_file, $basedir . '/' . $_REQUEST['target_language'] . '/' .  $_REQUEST['target_name'] . strtr($cur_file, array('english' => $_REQUEST['target_language']))))
					echo 'Error copying file ', $basedir . '/english/' . $_REQUEST['target_version'] . $cur_file, ' to ', $basedir . '/' . $_REQUEST['target_language'] . '/' .  $_REQUEST['target_name'] . strtr($cur_file, array('english' => $_REQUEST['target_language'])), '<br />';
			}
		}
	}

	if (!empty($_REQUEST['create_bz2']))
	{
		chdir($basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['target_name']);
		exec($bsdtar . ' -cyf ../smf_' . $_REQUEST['target_name'] . '_' . $_REQUEST['target_language'] . '.tar.bz2 *', $output);
	}
	if (!empty($_REQUEST['create_gz']))
	{
		chdir($basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['target_name']);
		exec($bsdtar . ' -czf ../smf_' . $_REQUEST['target_name'] . '_' . $_REQUEST['target_language'] . '.tar.gz *', $output);
		print_r($output);
	}
	if (!empty($_REQUEST['create_zip']))
	{
		chdir($basedir . '/' . $_REQUEST['target_language'] . '/' . $_REQUEST['target_name']);
		exec($zip . ' -rq ../smf_' . $_REQUEST['target_name'] . '_' . $_REQUEST['target_language'] . '.zip *', $output);
		print_r($output);
	}
}

function readLanguage($filename)
{
	echo 'Opening ', $filename, '.<br />';
	$tokens = token_get_all(implode(' ', file($filename)));

	$num_tokens = count($tokens);
	$top_of_file = true;
	$cur_line = array(
		'new_paragraph' => false,
		'pre_comment' => array(),
		'var_name' => '',
		'hash' => null,
		'tokens' => array(),
	);
	$file = array(
		'top_comment' => '',
		'globals' => array(),
		'path' => $filename,
		'lines' => array(),
	);
	for($i = 0; $i < $num_tokens; $i++)
	{
		switch (is_array($tokens[$i]) ? $tokens[$i][0] : $tokens[$i])
		{

		// Ignore the php open and closing tags.
		case T_OPEN_TAG:
		case T_CLOSE_TAG:
			break;

		case T_WHITESPACE:
			if (!$top_of_file && substr_count($tokens[$i][1], "\n") > 1)
				$cur_line['new_paragraph'] = true;
			break;

		case T_COMMENT:
			if ($top_of_file && empty($file['top_comment']))
				$file['top_comment'] = $tokens[$i][1];
			else
				$cur_line['pre_comment'][] = trim($tokens[$i][1], "\r\n") . "\n";
			break;

		case T_GLOBAL:
			while ($tokens[++$i] !== ';')
				if (is_array($tokens[$i]) && $tokens[$i][0] === T_VARIABLE)
					$file['globals'][] = $tokens[$i][1];
			break;

		case T_VARIABLE:
			$top_of_file = false;
			$cur_line['var_name'] = $tokens[$i][1];
			if ($tokens[$i + 1] === '[')
			{
				if ($tokens[$i + 2] === ']')
				{
					$cur_line['hash'] = '';
					$i += 2;
				}
				else
				{
					$cur_line['hash'] = $tokens[$i + 2][1];
					$i += 3;
				}
			}
			while ($tokens[++$i] !== ';')
				$cur_line['tokens'][] = $tokens[$i];
			$keyname = substr($cur_line['var_name'], 1) . '_' . trim($cur_line['hash'], "'");
			if (isset($file['lines'][$keyname]))
				echo '<b>Warning:</b> ', $cur_line['var_name'], $cur_line['hash'] === null ? '' : '[' . (empty($cur_line['hash']) ? '' : $cur_line['hash']) . ']', ' already set before in file ', $filename, '<br />';
			else
				$file['lines'][$keyname] = $cur_line;
			$cur_line = array(
				'new_paragraph' => false,
				'pre_comment' => array(),
				'var_name' => '',
				'hash' => null,
				'tokens' => array(),
			);
			break;
		}
	}

	return $file;
}

function writeLanguage($language)
{
	$content = "<?php\n";
	if (!empty($language['top_comment']))
		$content .= rtrim($language['top_comment'], "\n\r") . "\n\n";
	if (!empty($language['globals']))
		$content .= 'global ' . implode(', ', $language['globals']) . ";\n\n";

	$on_top = true;
	foreach ($language['lines'] as $line)
	{
		$content .= ($line['new_paragraph'] ? "\n" : '') . implode('', $line['pre_comment']) . $line['var_name'] . ($line['hash'] === null ? '' : '[' . (empty($line['hash']) ? '' : $line['hash']) . ']');
		foreach ($line['tokens'] as $token)
			$content .= is_array($token) ? $token[1] : $token;
		$content .= ";\n";
		$on_top = false;
	}
	$content .= (empty($language['lines']) ? '' : "\n") . '?>';

	file_put_contents($language['path'], strtr(strtr($content, array("\r" => '', "\n" => "\r\n")), array("\n " => "\n")));
}

function readDirRecursive($directory, $base = '')
{
	$dir = dir($directory);
	$entries = array();
	while ($entry = $dir->read())
	{
		if (substr($entry, 0, 1) == '.')
			continue;
		elseif (is_dir($directory . '/' . $entry))
			$entries = array_merge($entries, readDirRecursive($directory . '/' . $entry, $base . '/' . $entry));
		else
			$entries[] = $base . '/'.  $entry;
	}
	$dir->close();

	return $entries;
}

?>