<?php

$dirname = dirname(__FILE__);
require_once($dirname . '/help-list.php');

// For generating the file.
$current_version = '2.0 Beta 3 Public';

// Start with an index validation.
echo '<b>Starting Verification of help index</b><br />';
foreach ($txt as $index => $codes)
{
	echo 'Verifying <b>' . $index . '</b> entries.<br />';
	$found = array();
	foreach ($codes as $line => $key)
	{
		if (in_array($key, $found))
			echo '<b style="color: red;">Error: &quot;' . $key . '&quot; on line ' . $line . ' is already declared!</b><br />';
		else
			$found[] = $key;
	}
}

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'english';

$do_template = isset($_GET['template']);

$dh = opendir($dirname);
$translation = array();
while (($filename = readdir($dh)) !== false)
{
	// Not the right one?
	if (preg_match('~(.+?).' . $lang . '.xml~', $filename, $match) == false || !isset($txt[$match[1]]))
		continue;

	else
		$page = $match[1];

	$data = file($dirname . '/' . $filename);
	$real_line = -1;

	// Now pull out all the strings.
	foreach ($data as $line => $row)
	{
		$real_line++;

		// If this is an entity line work out if it's specific to this language file.
		if (preg_match('~\s*<!ENTITY\s(.+?)\s~', $row, $match) != false)
		{
			if ($match[1] != 'space' && $match[1] != 'copyright')
			{
				$real_line--;
				continue;
			}
		}

		// Untranslated info?
		if (preg_match('~\s*<!-- Untranslated! -->\s*~', $row, $match) != false)
		{
			$real_line--;
			continue;
		}

		if (preg_match('~\s*<.+?>(.+)</.+?>\s*$~', $row, $match) == false || !isset($txt[$page][$real_line + 1]))
			continue;

		// Do some processing on the line.
		$current = $match[1];
		$current = strtr($current, array('&space;' => ' ', '&copyright;' => '&copy;', '<action>' => '<strong>',
			'</action>' => '</strong>', '<term>' => '<strong>', '</term>' => '</strong>',
			'<check>' => '<strong>', '</check>' => '</strong>',
			'<field>' => '<strong>', '</field>' => '</strong>',
			'<option>' => '<strong>', '</option>' => '</strong>',
			'<screen>' => '<strong>', '</screen>' => '</strong>',
			'<dialog>' => '<strong>', '</dialog>' => '</strong>',
			'<html-b>' => '<b>', '</html-b>' => '</b>',
			'<![CDATA[' => '', ']]>' => '',
			'<emphasis>' => '<em>', '</emphasis>' => '</em>',
			'<html-i>' => '<i>', '</html-i>' => '</i>',
			'<icon>' => '<strong>', '</icon>' => '</strong>', '<sort-by>' => '<strong>', '</sort-by>' => '</strong>'));
		if ($real_line == 157 && $page == 'posting')
			$current = substr($current, 4);

		// Do we have any links to translate out?
		$link_split = preg_split('~<link\s*((ref|page|site)="(.+?)")*\s*((ref|page|site)="(.+?)")*>(.+?)</link>~', trim($current), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		// Is this easy?
		if (!isset($link_split[1]))
		{
			// Just add it to the array.
			if ($page == 'index' && $real_line == 11)
				$translation['smf_user_help'] = substr($current, 0, strpos($current, ':'));
			else
				$translation[$page . '_' . $txt[$page][$real_line + 1]] = $current;
				echo $page . '_' . $txt[$page][$real_line + 1];
				echo $current;
		}
		else
		{
			$state = 0;
			$count_normal = 0;
			$count_link = 0;
			$link_ref = '_link';
			$link_type = '';
			foreach ($link_split as $ind => $part)
			{
				// Just got normal text?
				if ($state == 0 && !in_array($link_split[$ind + 1], array('ref', 'page', 'site')))
				{
					$count_normal++;
					$translation[$page . '_' . $txt[$page][$real_line + 1] . '_part' . $count_normal] = $part;
				}
				// Otherwise we are starting a link... but this is crap.
				elseif ($state == 0)
				{
					$state = 1;
					$link_type = $part;
				}
				// Note the link type.
				elseif ($state == 1)
				{
					$link_type = $part;
					$state = 2;
				}
				// This is just the reference name for the link...
				elseif ($state == 2)
				{
					if ($link_type == 'site')
						$link_ref .= '_site' . $count_link;
					else
						$link_ref .= '_' . $part;

					$state = 3;
				}
				// If this is another reference, note it!
				elseif ($state == 3 && in_array($link_split[$ind + 1], array('ref', 'page', 'site')))
				{
					// Go back to get the reference name.
					$state = 1;
				}
				// Finally, the name of the link
				elseif ($state == 3)
				{
					// Add this link to the array.
					$translation[$page . '_' . $txt[$page][$real_line + 1] . strtr($link_ref, "'", "")] = $part;
					$count_link++;
					$state = 0;
				}
			}
		}
		//print_r($link_split);
	}
}
closedir($dh);

// What if we are doing the templates, this is getting serious...
if ($do_template)
{
	// Crazy Matt is going to sort by value length, and going to do it a long but easy way cause this needn't be quick.
	/*$temp_sort = array();
	foreach ($translation as $key => $value)
		$temp_sort[strlen($value)] = array(
			'key' => $key,
			'value' => $value
		);

	krsort($temp_sort);

	$template_entries = array();
	foreach ($temp_sort as $entry)
		$template_entries[$entry['key']] = $entry['value'];*/

	// Some common replacements.
	$from = array(
		'~<table\s*summary="(.+?)"~',
		'~\.\./images~',
		'~(\w+)\.' . $lang . '\.html~',
	);
	$to = array(
		'<table',
		'\', $settings[\'images_url\'], \'',
		"', \$scripturl, '?action=help;page=$1",
	);

	$cur_template = '';
	$fhw = null;
	$cur_offset = 0;
	foreach ($translation as $key => $value)
	{
		$temp_tem = substr($key, 0, strpos($key, '_'));
		if ($temp_tem != $cur_template)
		{
			$cur_template = $temp_tem;

			// Close the existing file.
			if ($fhw != null)
			{
				fwrite($fhw, $data);
				fclose($fhw);
			}

			$data = implode('', file($dirname . '/' . $cur_template . '.' . $lang . '.tmp'));
			// Some common bits...
			$data = preg_replace($from, $to, $data);
			$fhw = fopen($dirname . '/' . $cur_template . '.' . $lang . '.tem', 'w+');
			$cur_offset = 0;
		}

		//$data = str_replace($value, $key, $data);
		// Step through the data trying to find all the strings!
		if (($found_offset = strpos($data, $value, $cur_offset)) !== false)
		{
			$data = substr($data, 0, $found_offset) . ('\', $txt[\'manual_' . $key . '\'], \'') . substr($data, ($found_offset + strlen($value)));
			$cur_offset = $found_offset + strlen('\', $txt[\'manual_' . $key . '\'], \'');
		}
	}

	// Leave things tidy.
	if ($fhw != null)
	{
		// Sub in the normal stuff...
		//$data = preg_replace($from, $to, $data);

		fwrite($fhw, $data);
		fclose($fhw);
	}
}

// Slash the translation!
foreach ($translation as $key => $value)
{
	$final_translation['manual_' . $key] = strtr($value, array("'" => "\'"));
}

// Write it.
$ft = fopen('../languages/Manual.' . $lang . '.php', 'w+');
$last = '';
// Versioning...
fwrite($ft, "<" . "?php\n// Version: $current_version; Manual\n\n");
fwrite($ft, "/* Everything in this file is for the Simple Machines help manual\n   If you are looking at translating this into another language please\n   visit the Simple Machines website for tools to assist! */\n");

foreach ($final_translation as $k => $v)
{
	// File..
	$file_name = substr($k, 7, (strpos($k, '_', 9) - 7));

	if ($last != $file_name)
	{
		fwrite($ft, "\n// Entries for template: $file_name.\n");
		$last = $file_name;
	}
	fwrite($ft, '$txt[\'' . $k . '\'] = \'' . $v . '\';' . "\n");
}
fwrite($ft, "\n?" . ">");
fclose($ft);

?>