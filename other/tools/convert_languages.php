<?php
// Version: 2.0 Beta 3

// Use this to convert theme and language files to use the new SMF 2.0 indexes. Simple
// drop this into your SMF directory and run from the browser!

require_once('Settings.php');
require_once($sourcedir . '/FixLanguage.php');

$_GET['step'] = 'doStep' . (isset($_GET['step']) ? (int) $_GET['step'] : 0);
$curdir = dirname(__FILE__) . '/Themes/';

$doSources = true;

loadDirs();

show_header();
$_GET['step']();
show_footer();

function loadDirs()
{
	global $curdir, $dirs, $doSources, $sourcedir, $boarddir;

	// Load up all the directories.
	$dir = dir($curdir);
	while ($direntry = $dir->read())
	{
		// Found the language directory?
		if (file_exists($curdir . $direntry . '/languages'))
		{
			$dirs['lang'][] = $curdir . $direntry . '/languages';
		}
		if (file_exists($curdir . $direntry . '/index.template.php'))
			$dirs['theme'][] = $curdir . $direntry;
	}
	$dir->close();

	if ($doSources)
	{
		$dirs['theme'][] = $sourcedir;
		$dirs['theme'][] = $boarddir;
	}
}

function doStep0()
{
	global $dirs;

	$languages = array();
	$files = array();
	$not_writable = false;

	foreach ($dirs['lang'] as $langdir)
	{
		$dir = dir($langdir);
		preg_match('~/Themes/(\w*)/~', $langdir, $matches);
		$theme_name = $matches[1];
		while ($entry = $dir->read())
		{
			// Found a language? If so add it.
			if (substr($entry, 0, 6) == 'index.' && substr($entry, -4) == '.php' && strlen($entry) > 10)
				$languages[substr($entry, 6, -4)] = ucwords(substr($entry, 6, -4));
			// Just a file, is it writable?
			if (substr($entry, -4) == '.php' && strlen($entry) > 10)
			{
				$files[$entry . '(' . $theme_name . ')'] = is_writable($langdir . '/' . $entry);
				if ($files[$entry . '(' . $theme_name . ')'] == 0)
					$not_writable = true;
			}
		}
		$dir->close();
	}

	// Found SOME language??
	if (empty($languages))
		throwError('No language files found!!');

	// Display the info.
	echo '
		<h3>The converter has found the following languages installed on your forum:</h3>
		<ul>';
	foreach ($languages as $lang => $display)
		echo '
			<li>', $display, '</li>';
	echo '
		</ul>';

	$row_count = 0;
	echo '
		<h3>Within this directory, the following language files were found:</h3>';
	if ($not_writable)
		echo '
		<h4 style="color: red;">Note that not all files are writable, any files that are shown in red must be made writable before you can continue</h4>';
	echo '
		<table align="center" width="90%">';
	foreach ($files as $name => $writable)
	{
		if ($row_count == 0)
			echo '<tr>';
		echo '
			<td width="33%"><li style="', $writable ? '' : 'color: red;', 'font-size: 8pt;">', $name, '</li></td>';
		$row_count++;
		if ($row_count == 3)
		{
			echo '</tr>';
			$row_count = 0;
		}
	}
	if ($row_count != 3)
	{
		for (; $row_count < 4; $row_count++)
			echo '
			<td width="33%"></td>';
		echo '</tr>';
	}
	echo '
		</table><br />';

	// Start the conversion?
	if (!$not_writable)
	{
		echo '
		<form action="convert_languages.php?step=1" method="post">
			<div class="centertext">
				<input type="submit" value="Start Conversion" />
			</div>
		</form><br />';
	}
}

// Do general stuff - in language files only.
function doStep1()
{
	global $dirs, $txtChanges, $long_warning;

	// Foreach file, do general replacement tasks
	$files = array();
	$needsRunning = false;
	foreach ($dirs['lang'] as $langdir)
	{
		$dir = dir($langdir);
		while ($entry = $dir->read())
		{
			// Got a language file... good
			if (substr($entry, -4) == '.php' && strlen($entry) > 10)
			{
				$needs_edit = false;

				// Load the file.
				$fileContents = implode('', file($langdir . '/' . $entry));

				// First try find the info line.
				list ($type, $lang) = explode('.', $entry);

				// The warning for editing files direct?
				if ($type != 'index' && $type != 'Install' && preg_match('~//\sVersion:[\s\d\w\.]*;\s*' . $type . '\s*//\s[\w\d\s!\.&;]*index\.' . $lang . '\.php\.~', $fileContents, $matches) == false)
				{
					$fileContents = preg_replace('~(//\sVersion:[\s\d\w\.]*;\s*' . $type . '\s*)~', "$1// Important! Before editing these language files please read the text at the topic of index.$lang.php.\n\n", $fileContents);
					$needs_edit = true;
				}
				// Instructions on index?
				if ($type == 'index' && preg_match('~//\sVersion:[\s\d\w\.]*;\s*' . $type . '\s*/\*~', $fileContents, $matches) == false)
				{
					$long_warning = '/* Important note about language files in SMF 2.0 upwards:
		1) All language entries in SMF 2.0 are cached. All edits should therefore be made through the admin menu. If you do
		   edit a language file manually you will not see the changes in SMF until the cache refreshes. To manually refresh
		   the cache go to Admin => Maintenance => Clean Cache.

		2) Please also follow the following rules:

			a) All strings should use single quotes, not double quotes for enclosing the string.
			b) As a result of (a) all newline characters (etc) need to be escaped. i.e. "\\n" is now \'\\\\\\\\n\'.

	*/';
					$fileContents = preg_replace('~(//\sVersion:[\s\d\w\.]*;\s*' . $type . '\s*)~', "$1$long_warning\n\n", $fileContents);

					$needs_edit = true;
				}
				// Fix up the help file with existing indexes.
				if ($type == 'Help' && preg_match('~\\t\{\$~', $fileContents))
				{
					$fileContents = preg_replace('~\\t\{\$~', "\t{" . '\\\\' . "$", $fileContents);
					$needs_edit = true;
				}
				// Remove double quotes where easy.
				if ($type != 'Install' && preg_match('~"\\\\n"~', $fileContents, $matches))
				{
					$fileContents = preg_replace('~"\\\\n"~', '\'\\\\\\\\n\'', $fileContents);
					// Fix for the comment.
					$fileContents = strtr($fileContents, array('i.e. \'\\\\n\'' => 'i.e. "\\n"'));
					$needs_edit = true;
				}
				// More double quotes
				if ($type != 'Install' && preg_match('~"\\\\n\\\\n"~', $fileContents, $matches))
				{
					$fileContents = preg_replace('~"\\\\n\\\\n"~', '\'\\\\\\\\n\\\\\\\\n\'', $fileContents);
					// Fix for the comment.
					$fileContents = strtr($fileContents, array('i.e. \'\\\\n\\\\n\'' => 'i.e. "\\n\\n"'));
					$needs_edit = true;
				}
				// More silly amounts of joins.
				if ($type != 'Install' && preg_match('~\' \. \'~', $fileContents, $matches))
				{
					$fileContents = preg_replace('~\' \. \'~', '', $fileContents);
					$needs_edit = true;
				}
				// Scripturl/Boardurl?
				if ($type != 'Install' && $type != 'Help' && preg_match('~\$(scripturl|boardurl)~', $fileContents, $match))
				{
					$fileContents = preg_replace('~\$(scripturl|boardurl)~', "#$1", $fileContents);
				}
				// Forumname/images/regards?
				if ($type != 'Install' && $type != 'Help' && preg_match('~\$(context|settings|txt)\[\'?(forum_name|images_url|130|regards_team)\'?\]~', $fileContents, $match))
				{
					$fileContents = preg_replace('~\$((context|settings|txt)\[\'?(forum_name|images_url|130|regards_team)\'?\])~', "#$1", $fileContents);
				}
				// Remove variables.
				if ($type != 'Install' && preg_match('~\' \. \$(\w*) \. \'~', $fileContents, $match))
				{
					$fileContents = preg_replace('~\' \. \$(\w*) \. \'~', "%s", $fileContents);
					$needs_edit = true;
				}
				// And any double arrays.
				if ($type != 'Install' && preg_match('~\' \. \$(\w*)\[\'?([\d\w]*)\'?\] \. \'~', $fileContents))
				{
					$fileContents = preg_replace('~\' \. \$(\w*)\[\'?([\d\w]*)\'?\] \. \'~', "%s", $fileContents);
					$needs_edit = true;
				}
				// Do the same for ones which are only half opened.
				if ($type != 'Install' && preg_match('~\$(\w*) \. \'~', $fileContents))
				{
					$fileContents = preg_replace('~\$(\w*) \. \'~', "'%s", $fileContents);
					$needs_edit = true;
				}
				// And any double arrays.
				if ($type != 'Install' && preg_match('~\$(\w*)\[\'?([\d\w]*)\'?\] \. \'~', $fileContents))
				{
					$fileContents = preg_replace('~\$(\w*)\[\'?([\d\w]*)\'?\] \. \'~', "'%s", $fileContents);
					$needs_edit = true;
				}
				// Put back in any variables.
				if ($type != 'Install' && $type != 'Help' && preg_match('~#(context|settings|txt|boardurl|scripturl)~', $fileContents, $match))
				{
					$fileContents = preg_replace('~#(context|settings|txt|boardurl|scripturl)~', "$$1", $fileContents);
				}

				if ($needs_edit)
				{
					$files[$entry] = array(
						'name' => substr($entry, 0, strpos($entry, '.')),
					);

					$fp = fopen($langdir . '/' . $entry, 'w');
					fwrite($fp, $fileContents);
					fclose($fp);
				}
			}
		}
		$dir->close();
	}

	// Now list what we need to do.
	echo '
		<h3>Provided generic language file changes to the following files:</h3>
		<ul>';

	foreach ($files as $filename => $data)
		echo '
			<li style="color: green;">', $filename, ' (', $data['name'], ')</li>';
	echo '
		</ul><br />';

	echo '
		<form action="convert_languages.php?step=2" method="post">
			<div class="centertext">
				<input type="submit" value="Continue to index changes" />
			</div>
		</form><br />';
}

// Test the changes to be made.
function doStep2()
{
	global $dirs, $txtChanges;

	// Foreach file, check whether any entries exist, and then work out whether they need to be made.
	$files = array();
	$needsRunning = false;

	foreach ($dirs['lang'] as $langdir)
	{
		$dir = dir($langdir);
		while ($entry = $dir->read())
		{
			// Got a language file... good
			if (substr($entry, -4) == '.php' && strlen($entry) > 10)
			{
				$name = substr($entry, 0, strpos($entry, '.'));
				$files[$entry] = array(
					'name' => $name,
					'entries' => isset($txtChanges[$name]) ? count($txtChanges[$name]) : 0,
					'need_replacing' => 0,
				);
				// Load the file.
				$fileContents = implode('', file($langdir . '/' . $entry));

				foreach ($txtChanges as $type => $set)
					foreach ($txtChanges[$type] as $find => $replace)
					{
						// Normal entries.
						if (is_integer($find))
							$find2 = '$txt[' . $find . ']';
						else
							$find2 = '$txt[\'' . $find . '\']';
						if (strpos($fileContents, $find2) !== false)
						{
							$files[$entry]['need_replacing']++;
							$needsRunning = true;
						}
					}
			}
		}
		$dir->close();
	}

	// Now list what we need to do.
	echo '
		<h3>Below is a summary of the entries for each file, and the number of changes which need to be made. Those files which are out of date are highlighted in red, upto date files are shown in green</h3>
		<ul>';

	foreach ($files as $filename => $data)
		echo '
			<li style="color: ', $data['need_replacing'] ? 'red' : 'green', ';">', $filename, ' (', $data['name'], ') - Total Entries: ', $data['entries'], ', Changes Needed: ', $data['need_replacing'], '</li>';
	echo '
		</ul><br />';

	// Actually do the work?
	if ($needsRunning)
	{
		echo '
		<form action="convert_languages.php?step=3" method="post">
			<div class="centertext">
				<input type="submit" value="Make The Changes" />
			</div>
		</form><br />';
	}
	// Otherwise onwards!
	else
	{
		echo '
		<form action="convert_languages.php?step=4" method="post">
			<div class="centertext">
				<input type="submit" value="Skip" />
			</div>
		</form><br />';
	}
}

// Test the changes to be made.
function doStep3()
{
	global $dirs, $txtChanges;

	// Foreach file, check whether any entries exist, and then work out whether they need to be made.
	foreach ($dirs['lang'] as $langdir)
	{
		$dir = dir($langdir);
		while ($entry = $dir->read())
		{
			// Got a language file... good
			if (substr($entry, -4) == '.php' && strlen($entry) > 10)
			{
				$name = substr($entry, 0, strpos($entry, '.'));

				// Load the file.
				$fileContents = implode('', file($langdir . '/' . $entry));

				$findArray = array();
				$replaceArray = array();
				foreach ($txtChanges as $type => $set)
					foreach ($txtChanges[$type] as $find => $replace)
					{
						$find2 = is_integer($find) ? '$txt[' . $find . ']' : '$txt[\'' . $find . '\']';

						if (strpos($fileContents, $find2) !== false)
						{
							$findArray[] = $find2;
							if (is_integer($replace))
								$replaceArray[] = '$txt[' . $replace . ']';
							else
								$replaceArray[] = '$txt[\'' . $replace . '\']';
						}
					}

				if (!empty($findArray))
				{
					$fileContents = str_replace($findArray, $replaceArray, $fileContents);

					$fp = fopen($langdir . '/' . $entry, 'w');
					fwrite($fp, $fileContents);
					fclose($fp);
				}
			}
		}
		$dir->close();
	}

	// On to theme changes
	echo '
		<form action="convert_languages.php?step=4" method="post">
			<div class="centertext">
				<input type="submit" value="Continue to Theme Conversion" />
			</div>
		</form><br />';
}

// Do all non-language files
function doStep4()
{
	global $dirs, $txtChanges;

	// Foreach file, check whether any entries exist, and then work out whether they need to be made.
	$files = array();
	$needsRunning = false;

	foreach ($dirs['theme'] as $themedir)
	{
		preg_match('~/Themes/(\w*)~', $themedir, $matches);
		$theme_name = isset($matches[1]) ? $matches[1] : 'Source';

		$dir = dir($themedir);
		while ($entry = $dir->read())
		{
			// Got a source file... good
			if (substr($entry, -4) == '.php' && strlen($entry) > 7)
			{
				$files[$entry . $theme_name] = array(
					'filename' => $entry,
					'name' => $theme_name,
					'need_replacing' => 0,
				);
				// Load the file.
				$fileContents = implode('', file($themedir . '/' . $entry));
				foreach ($txtChanges as $type => $section)
				{
					foreach ($txtChanges[$type] as $find => $replace)
					{
						// Normal entries.
						if (is_integer($find))
							$find = '$txt[' . $find . ']';
						else
							$find = '$txt[\'' . $find . '\']';
						if (strpos($fileContents, $find) !== false)
						{
							$files[$entry . $theme_name]['need_replacing']++;
							$needsRunning = true;
						}

						// Or ones which are surrounded by quotes..
						$find = '\'$txt[' . $find . ']\'';
						if (strpos($fileContents, $find) !== false)
						{
							$files[$entry . $theme_name]['need_replacing']++;
							$needsRunning = true;
						}
					}
				}
			}
		}
		$dir->close();
	}

	// Now list what we need to do.
	echo '
		<h3>Below is a summary of the entries for each file, and the number of changes which need to be made. Those files which are out of date are highlighted in red, upto date files are shown in green</h3>
		<ul>';

	foreach ($files as $filename => $data)
		echo '
			<li style="color: ', $data['need_replacing'] ? 'red' : 'green', ';">', $data['filename'], ' (', $data['name'], ') - Changes Needed: ', $data['need_replacing'], '</li>';
	echo '
		</ul><br />';

	// Actually do the work!
	echo '
		<form action="convert_languages.php?step=5" method="post">
			<div class="centertext">
				<input type="submit" value="Continue" />
			</div>
		</form><br />';
}

// Make the theme changes.
function doStep5()
{
	global $dirs, $txtChanges;

	// Foreach file, simply make the changes
	foreach ($dirs['theme'] as $themedir)
	{
		$dir = dir($themedir);
		while ($entry = $dir->read())
		{
			// Got a source file... good
			if (substr($entry, -4) == '.php' && strlen($entry) > 7)
			{
				$name = substr($entry, 0, strpos($entry, '.'));

				// Load the file.
				$fileContents = implode('', file($themedir . '/' . $entry));

				$findArray = array();
				$replaceArray = array();
				foreach ($txtChanges as $type => $section)
				{
					foreach ($txtChanges[$type] as $find => $replace)
					{
						$find2 = is_integer($find) ? '$txt[' . $find . ']' : '$txt[\'' . $find . '\']';

						if (strpos($fileContents, $find2) !== false)
						{
							$findArray[] = $find2;
							if (is_integer($replace))
								$replaceArray[] = '$txt[' . $replace . ']';
							else
								$replaceArray[] = '$txt[\'' . $replace . '\']';
						}

						// Check for ones in quotes too.
						$find2 = '\'$txt[' . $find . ']\'';

						if (strpos($fileContents, $find2) !== false)
						{
							$findArray[] = $find2;
							$replaceArray[] = '\'$txt[' . $replace . ']\'';
						}
					}
				}

				$fileContents = str_replace($findArray, $replaceArray, $fileContents);

				// Get in some sprintf.
				if (strpos($entry, 'template') !== false)
				{
					$changes = array(
						'~([^\(])\$txt\[\'users_active\'\]~' => '$1sprintf($txt[\'users_active\'], $modSettings[\'lastActive\'])',
						'~([^\(])\$txt\[\'welcome_guest\'\]~' => '$1sprintf($txt[\'welcome_guest\'], $txt[\'guest_title\'])',
						'~([^\(])\$txt\[\'hot_topics\'\]~' => '$1sprintf($txt[\'hot_topics\'], $modSettings[\'hotTopicPosts\'])',
						'~([^\(])\$txt\[\'very_hot_topics\'\]~' => '$1sprintf($txt[\'very_hot_topics\'], $modSettings[\'hotTopicVeryPosts\'])',
						'~([^\(])\$txt\[\'info_center_title\'\]~' => '$1sprintf($txt[\'info_center_title\'], $context[\'forum_name\'])',
						'~([^\(])\$txt\[\'login_with_forum\'\]~' => '$1sprintf($txt[\'login_with_forum\'], $context[\'forum_name\'])',
					);
					$before = strlen($fileContents);
					$fileContents = preg_replace(array_keys($changes), array_values($changes), $fileContents);
					if (strlen($fileContents) != $before)
						$findArray[] = 1;
				}

				// Write the changes.
				if (!empty($findArray))
				{
					$fp = fopen($themedir . '/' . $entry, 'w');
					fwrite($fp, $fileContents);
					fclose($fp);
				}
			}
		}
		$dir->close();
	}

	// On to end.
	return doStep6();
}

// Finish.
function doStep6()
{
	echo '
	<h1>Conversion Complete</h1><br />
	<h3>Please ensure you delete this file!</h3>';
}

// Just throw an error message!
function throwError($message)
{
	echo '
		<h2 align="center" style="color: red;">', $message, '</h2>';
	show_footer();
	exit;
}

function show_header()
{
	global $converting_name;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>SMF Language Converter</title>
		<style type="text/css"><!--
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
				margin: 20px 30px;
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
		--></style>
	</head>
	<body>
		<div id="header">
			<div title="Vivi">SMF Language Converter</div>
		</div>
		<div id="content">';
}

// Show the footer.
function show_footer()
{
	echo '
		</div>
	</body>
</html>';
}

?>