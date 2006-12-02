<?php
/**********************************************************************************
* Subs-Editor.php                                                                 *
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

if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file contains those functions specific to the editing box and is
	generally used for WYSIWYG type functionality. Doing all this is the
	following:

	void EditorMain()
		// !!

	void bbc_to_html()
		// !!

	void html_to_bbc()
		// !!

	array getMessageIcons(int board_id)
	- retrieves a list of message icons.
	- based on the settings, the array will either contain a list of default
	  message icons or a list of custom message icons retrieved from the
	  database.
	- the board_id is needed for the custom message icons (which can be set for
	  each board individually).
*/

// At the moment this is only used for returning WYSIWYG data...
function EditorMain()
{
	global $context, $smfFunc;

	checkSession('get');

	if (!isset($_REQUEST['view']) || !isset($_REQUEST['message']))
		fatal_lang_error(1);

	$context['sub_template'] = 'sendbody';

	$context['view'] = (int) $_REQUEST['view'];

	$_REQUEST['message'] = un_htmlspecialchars($_REQUEST['message']);
	$_REQUEST['message'] = $smfFunc['db_unescape_string']($_REQUEST['message']);

	// Put back in special characters.
	$_REQUEST['message'] = strtr($_REQUEST['message'], array('#smcol#' => ';', '#smlt#' => '&lt;', '#smgt#' => '&gt;', '#smamp#' => '&amp;'));

	// Return the right thing for the mode.
	if ($context['view'])
	{
		$context['message'] = bbc_to_html($_REQUEST['message']);
	}
	else
	{
		$context['message'] = html_to_bbc($_REQUEST['message']);
	}

	$context['message'] = $smfFunc['htmlspecialchars']($context['message']);
}

// Convert only the BBC that can be edited in HTML mode for the editor.
function bbc_to_html($text)
{
	global $modSettings, $smfFunc;

	// What tags do we allow?
	$allowed_tags = array('b', 'u', 'i', 's', 'hr', 'list', 'li', 'font', 'size', 'color', 'img', 'pre', 'left', 'center', 'right', 'url', 'email', 'ftp', 'sub', 'sup', 'tt');
	$text = parse_bbc($text, true, '', $allowed_tags);

	// Fix for having a line break then a thingy.
	$text = strtr($text, array('<br /><hr />' => '<hr />', '<br /><div' => '<div'));

	// Note that IE doesn't understand spans really - make them something "legacy"
	$working_html = array(
		'~<del>(.+?)</del>~i' => "<strike>$1</strike>",
		'~<span\sstyle="text-decoration:\sunderline;">(.+?)</span>~i' => "<u>$1</u>",
		'~<span\sstyle="color:\s*([#\d\w]+);">(.+?)</span>~i' => "<font color=\"$1\">$2</font>",
		'~<span\sstyle="font-family:\s*([#\d\w\s]+);">(.+?)</span>~i' => "<font face=\"$1\">$2</font>",
		'~<div\sstyle="text-align:\s*(left|center|right);">(.+?)</div>~i' => "<p align=\"$1\">$2</p>",
	);
	$text = preg_replace(array_keys($working_html), array_values($working_html), $text);

	// Parse unique ID's into the smileys - using the double space.
	$i = 1;
	$text = preg_replace('~(\s|&nbsp;){1}?<(img\ssrc="' . preg_quote($modSettings['smileys_url'], '~') . '/.+?/(.+?)".*?)bordeR="0" />~e', "'<' . \$smfFunc['db_unescape_string']('$2') . 'border=\"0\" id=\"smiley_' . \$i++ . '_$3\" />'", $text);

	return $text;
}

// The harder one - wysiwyg to BBC!
function html_to_bbc($text)
{
	global $db_prefix, $modSettings, $smfFunc;

	// Remove any newlines - as they are useless.
	$text = strtr($text, array("\n" => ''));

	// Do the smileys ultra first!
	preg_match_all('~<img\s+[^<>]*?id="*smiley_\d+_([^<>]+?)[\s"/>]\s*[^<>]*?/*>~i', $text, $matches);
	if (!empty($matches))
	{
		// Easy if it's not custom.
		if (empty($modSettings['smiley_enable']))
		{
			$smileysfrom = array('>:D', ':D', '::)', '>:(', ':)', ';)', ';D', ':(', ':o', '8)', ':P', '???', ':-[', ':-X', ':-*', ':\'(', ':-\\', '^-^', 'O0', 'C:-)', '0:)');
			$smileysto = array('evil.gif', 'cheesy.gif', 'rolleyes.gif', 'angry.gif', 'smiley.gif', 'wink.gif', 'grin.gif', 'sad.gif', 'shocked.gif', 'cool.gif', 'tongue.gif', 'huh.gif', 'embarrassed.gif', 'lipsrsealed.gif', 'kiss.gif', 'cry.gif', 'undecided.gif', 'azn.gif', 'afro.gif', 'police.gif', 'angel.gif');

			foreach ($matches[1] as $k => $file)
			{
				$found = array_search($file, $smileysto);
				if ($found)
					$matches[1][$k] = ' ' . $smileysfrom[$found];
				else
					$matches[1][$k] = '';
			}
		}
		else
		{
			// Load all the smileys.
			$names = array();
			foreach ($matches[1] as $file)
				$names[] = '\'' . $smfFunc['db_escape_string']($file) . '\'';
			$names = array_unique($names);

			if (!empty($names))
			{
				$request = $smfFunc['db_query']('', "
					SELECT code, filename
					FROM {$db_prefix}smileys
					WHERE filename IN (" . implode(', ', $names) . ")", __FILE__, __LINE__);
				$mappings = array();
				while ($row = $smfFunc['db_fetch_assoc']($request))
					$mappings[$row['filename']] = $row['code'];
				$smfFunc['db_free_result']($request);
	
				foreach ($matches[1] as $k => $file)
					if (isset($mappings[$file]))
						$matches[1][$k] = isset($mappings[$file]) ? ' ' . $mappings[$file] : '';
			}
		}

		// Replace the tags!
		$text = str_replace($matches[0], $matches[1], $text);
	}

	// Start by pulling out any styles from existing tags.
	while (preg_match('~<([A-Za-z]+)\s+[^<>]*?(style="*(([^<>"]+))"*)[^<>]*?(/?)>~i', $text, $matches) != false)
	{
		// Find the position in the text of this tag.
		$start_pos = strpos($text, $matches[0]);
		if ($start_pos === false)
			break;

		// Does it have an end tag?
		if ($matches[5] != '/' && strpos($text, '</' . $matches[1] . '>', $start_pos) !== false)
  			$end_pos = strpos($text, '</' . $matches[1] . '>', $start_pos);
		else
			$end_pos = $start_pos + strlen($matches[0]);

		// Now we know our insertion points - let's see what we need to insert.
		$styles = explode(';', $matches[3]);
		$tags = array();
		$extra_attr = '';

		foreach ($styles as $item)
		{
			if (trim($item) == '')
				continue;

			$item = strtr($item, '=', ':');
			@list ($s, $v) = explode(':', $item);

			if (empty($v))
				continue;
			$s = trim(strtolower($s));
			$v = trim(strtolower($v));

			// Now - the switch - what do we do with it?
			if ($s == 'font-weight')
			{
				if ($v == 'bold')
					$tags[] = array('[b]', '[/b]');
   			}
   			elseif ($s == 'text-decoration')
			{
				if ($v == 'underline')
					$tags[] = array('[u]', '[/u]');
				elseif ($v == 'line-through')
					$tags[] = array('[s]', '[/s]');
   			}
   			elseif ($s == 'text-align')
			{
				if ($v == 'left')
					$tags[] = array('[left]', '[/left]');
				elseif ($v == 'center')
					$tags[] = array('[center]', '[/center]');
				elseif ($v == 'right')
					$tags[] = array('[right]', '[/right]');
   			}
   			elseif ($s == 'font-style')
			{
				if ($v == 'italic')
					$tags[] = array('[u]', '[/u]');
   			}
   			// Font colors?
   			elseif ($s == 'color')
			{
				$tags[] = array('[color=' . $v . ']', '[/color]');
   			}
   			// Font size?
   			elseif ($s == 'font-size')
			{
				$tags[] = array('[size=' . $v . ']', '[/size]');
   			}
   			// Font family?
   			elseif ($s == 'font-family')
			{
				$tags[] = array('[font=' . $v . ']', '[/font]');
   			}
   			// This is a hack for images with dimensions embedded.
   			elseif ($s == 'width' || $s == 'height')
			{
				preg_match('~(\d+)~i', $v, $dim);
				if (!empty($dim[1]))
					$extra_attr .= ' ' . $s . '="' . $dim[1] . '"';
			}
			// Another hack - for lists this time.
			elseif ($s == 'list-style-type')
			{
				preg_match('~(none|disc|circle|square|decimal|decimal-leading-zero|lower-roman|upper-roman|lower-alpha|upper-alpha|lower-greek|lower-latin|upper-latin|hebrew|armenian|georgian|cjk-ideographic|hiragana|katakana|hiragana-iroha|katakana-iroha)~i', $v, $type);
				if (!empty($type[1]))
					$extra_attr .= ' listtype="' . $type[1] . '"';
			}
  		}

		// Add in all our new tags.
		$before = $after = '';
		foreach ($tags as $tag)
		{
			$before .= $tag[0];
			if (isset($tag[1]))
				$after = $tag[1] . $after;
  		}

		// Remove the style from that tag so it's never checked again.
		$tag = substr($text, $start_pos, strlen($matches[0]));
		$content = substr($text, $start_pos + strlen($matches[0]), $end_pos - $start_pos - strlen($matches[0]));
		$tag = str_replace($matches[2], $extra_attr, $tag);

		// Put the tags back into the body.
		$text = substr($text, 0, $start_pos) . $before . $tag . $content . $after . substr($text, $end_pos);
 	}

	// Let's pull out any legacy alignments.
	while (preg_match('~<([A-Za-z]+)\s+[^<>]*?(align="*(left|center|right)"*)[^<>]*?(/?)>~i', $text, $matches) != false)
	{
		// Find the position in the text of this tag over again.
		$start_pos = strpos($text, $matches[0]);
		if ($start_pos === false)
			break;

		// End tag?
		if ($matches[4] != '/' && strpos($text, '</' . $matches[1] . '>', $start_pos) !== false)
  			$end_pos = strpos($text, '</' . $matches[1] . '>', $start_pos);
		else
			$end_pos = $start_pos + strlen($matches[0]);

		// Remove the align from that tag so it's never checked again.
		$tag = substr($text, $start_pos, strlen($matches[0]));
		$content = substr($text, $start_pos + strlen($matches[0]), $end_pos - $start_pos - strlen($matches[0]));
		$tag = str_replace($matches[2], '', $tag);

		// Put the tags back into the body.
		$text = substr($text, 0, $start_pos) . '[' . $matches[3] . ']' . $tag . $content . '[/' . $matches[3] . ']' . substr($text, $end_pos + strlen('</' . $matches[1] . '>'));
 	}
 
	// Let's do some special stuff for fonts - cause we all love fonts.
	while (preg_match('~<font\s+([A-Za-z]+="*[#\w\s]+"*)\s*([A-Za-z]+="*[#\w\s]+"*)*\s*([A-Za-z]+="*[#\w\s]+"*)*[^<>]*?>~i', $text, $matches) != false)
	{
	  	$tags = array();

		// Find the position of this again.
	  	$start_pos = strpos($text, $matches[0]);
		if ($start_pos === false)
			break;

		// This must have an end tag!
		$end_pos = strpos(strtolower($text), '</font>', $start_pos);
		if ($end_pos === false)
			break;

		for ($i = 1; $i < 4; $i++)
		{
			if (!empty($matches[$i]))
			{
				$matches[$i] = strtr($matches[$i], array('"' => ''));
				list ($s, $v) = explode('=', $matches[$i]);
				if (empty($v))
					continue;
				$s = trim(strtolower($s));

				if ($s == 'size')
					$tags[] = array('[size=' . (int) trim($v) . ']', '[/size]');
				elseif ($s == 'face')
					$tags[] = array('[font=' . trim(strtolower($v)) . ']', '[/font]');
				elseif ($s == 'color')
					$tags[] = array('[color=' . trim(strtolower($v)) . ']', '[/color]');
   			}
  		}

		// As before add in our tags.
		$before = $after = '';
		foreach ($tags as $tag)
		{
			$before .= $tag[0];
			if (isset($tag[1]))
				$after = $tag[1] . $after;
  		}

		// Remove the tag so it's never checked again.
		$content = substr($text, $start_pos + strlen($matches[0]), $end_pos - $start_pos - strlen($matches[0]));

		// Put the tags back into the body.
		$text = substr($text, 0, $start_pos) . $before . $content . $after . substr($text, $end_pos + 7);
 	}

	// Try our hand at all manner of lists - doesn't matter if we mess up the children as the BBC will clean it.
	$text = preg_replace('~<(ol|ul)\s*[^<>]*?(listtype="([\w-]+)")*[^<>]*?>(.+?)</(ol|ul)>~ie', "'[list' . (strlen('$3') > 1 ? ' type=$3' : '') . ']$4[/list]'", $text);
	$text = preg_replace('~<li\s*[^<>]*?>(.+?)</li>~i', "[li]$1[/li]", $text);
 
	// What about URL's - the pain in the ass of the tag world.
	while (preg_match('~<a\s+([^<>]*)>([^(<a)]*)</a>~i', $text, $matches) != false)
	{
		// Find the position of the URL.
	  	$start_pos = strpos($text, $matches[0]);
		if ($start_pos === false)
			break;
		$end_pos = $start_pos + strlen($matches[0]);

		$tag_type = 'url';
		$href = '';

		$attrs = explode(' ', $matches[1]);
		foreach ($attrs as $attrib)
		{
			@list ($k, $v) = explode('=', $attrib);
			if (empty($v))
				continue;

			$v = strtr($v, array('"' => ''));

			if (trim($k) == 'href')
			{
				$href = trim($v);
				if (substr($href, 0, 6) == 'ftp://')
					$tag_type = 'ftp';
				elseif (substr($href, 0, 7) == 'mailto:')
				{
					$tag_type = 'email';
					$href = substr($href, 7);
				}
			}
		}

		$tag = '';
		if ($href != '')
		{
			if ($matches[2] == $href)
				$tag = '[' . $tag_type . ']' . $href . '[/' . $tag_type . ']';
			else
				$tag = '[' . $tag_type . '=' . $href . ']' . $matches[2] . '[/' . $tag_type . ']';
		}

		// Replace the tag
		$text = substr($text, 0, $start_pos) . $tag . substr($text, $end_pos);
 	}
 
 	// I love my own image...
 	while (preg_match('~<img\s+([^<>]*)/*>~i', $text, $matches) != false)
	{
		// Find the position of the image.
	  	$start_pos = strpos($text, $matches[0]);
		if ($start_pos === false)
			break;
		$end_pos = $start_pos + strlen($matches[0]);

		$params = '';
		$had_params = array();
		$src = '';

		$attrs = explode(' ', $matches[1]);
		foreach ($attrs as $attrib)
		{
			@list ($k, $v) = explode('=', $attrib);
			if (empty($v))
				continue;

			$v = strtr($v, array('"' => ''));
			$k = trim($k);

			if (trim($v) == '')
				continue;

			if (in_array($k, $had_params))
				continue;

			if (in_array($k, array('width', 'height')))
				$params .= ' ' . $k . '=' . (int) $v;
			elseif ($k == 'alt')
				$params .= ' alt=' . trim($v);
			elseif ($k == 'src')
				$src = trim($v);

			$had_params[] = $k;
		}

		$tag = '';
		if (!empty($src))
		{
			$tag = '[img' . $params . ']' . $src . '[/img]';
		}

		// Replace the tag
		$text = substr($text, 0, $start_pos) . $tag . substr($text, $end_pos);
 	}
 
	// The final bits are the easy ones - tags which map to tags which map to tags - etc etc.
	$tags = array(
		'~<b(\s(.)*?)*?>~i' => '[b]',
		'~</b>~i' => '[/b]',
		'~<i(\s(.)*?)*?>~i' => '[i]',
		'~</i>~i' => '[/i]',
		'~<u(\s(.)*?)*?>~i' => '[u]',
		'~</u>~i' => '[/u]',
		'~<strong(\s(.)*?)*?>~i' => '[b]',
		'~</strong>~i' => '[/b]',
		'~<em(\s(.)*?)*?>~i' => '[i]',
		'~</em>~i' => '[/i]',
		'~<strike(\s(.)*?)*?>~i' => '[s]',
		'~</strike>~i' => '[/s]',
		'~<del(\s(.)*?)*?>~i' => '[s]',
		'~</del>~i' => '[/s]',
		'~<center(\s(.)*?)*?>~i' => '[center]',
		'~</center>~i' => '[/center]',
		'~<pre(\s(.)*?)*?>~i' => '[pre]',
		'~</pre>~i' => '[/pre]',
		'~<sub(\s(.)*?)*?>~i' => '[sub]',
		'~</sub>~i' => '[/sub]',
		'~<sup(\s(.)*?)*?>~i' => '[sup]',
		'~</sup>~i' => '[/sup]',
		'~<tt(\s(.)*?)*?>~i' => '[tt]',
		'~</tt>~i' => '[/tt]',
		'~<table(\s(.)*?)*?>~i' => '[table]',
		'~</table>~i' => '[/table]',
		'~<tr(\s(.)*?)*?>~i' => '[tr]',
		'~</tr>~i' => '[/tr]',
		'~<td(\s(.)*?)*?>~i' => '[td]',
		'~</td>~i' => '[/td]',
		'~<br\s*/*>~i' => "\n",
		'~<hr[^<>]*>~i' => '[hr]',
	);
	$text = preg_replace(array_keys($tags), array_values($tags), $text);

	// But really - at the end of the day - we remove every other bit of html crap!
	$text = strip_tags($text);

	return $text;
}

function getMessageIcons($board_id)
{
	global $modSettings, $context, $db_prefix, $txt, $settings, $smfFunc;

	if (empty($modSettings['messageIcons_enable']))
	{
		loadLanguage('Post');

		$icons = array(
			array('value' => 'xx', 'name' => $txt[281]),
			array('value' => 'thumbup', 'name' => $txt[282]),
			array('value' => 'thumbdown', 'name' => $txt[283]),
			array('value' => 'exclamation', 'name' => $txt[284]),
			array('value' => 'question', 'name' => $txt[285]),
			array('value' => 'lamp', 'name' => $txt[286]),
			array('value' => 'smiley', 'name' => $txt['icon_smiley']),
			array('value' => 'angry', 'name' => $txt['icon_angry']),
			array('value' => 'cheesy', 'name' => $txt['icon_cheesy']),
			array('value' => 'grin', 'name' => $txt['icon_grin']),
			array('value' => 'sad', 'name' => $txt['icon_sad']),
			array('value' => 'wink', 'name' => $txt['icon_wink'])
		);

		foreach ($icons as $k => $dummy)
		{
			$icons[$k]['url'] = $settings['images_url'] . '/post/' . $dummy['value'] . '.gif';
			$icons[$k]['is_last'] = false;
		}
	}
	// Otherwise load the icons, and check we give the right image too...
	else
	{
		if (($temp = cache_get_data('posting_icons-' . $board_id, 480)) == null)
		{
			$request = $smfFunc['db_query']('select_message_icons', "
				SELECT title, filename
				FROM {$db_prefix}message_icons
				WHERE id_board IN (0, $board_id)", __FILE__, __LINE__);
			$icon_data = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$icon_data[] = $row;
			$smfFunc['db_free_result']($request);

			cache_put_data('posting_icons-' . $board_id, $icon_data, 480);
		}
		else
			$icon_data = $temp;

		$icons = array();
		foreach ($icon_data as $icon)
		{
			$icons[] = array(
				'value' => $icon['filename'],
				'name' => $icon['title'],
				'url' => $settings[file_exists($settings['theme_dir'] . '/images/post/' . $icon['filename'] . '.gif') ? 'images_url' : 'default_images_url'] . '/post/' . $icon['filename'] . '.gif',
				'is_last' => false,
			);
		}
	}

	return $icons;
}

?>