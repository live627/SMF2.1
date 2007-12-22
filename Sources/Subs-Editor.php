<?php
/**********************************************************************************
* Subs-Editor.php                                                                 *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 1                                       *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
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

	void theme_postbox(string message)
		- for compatibility - passes right through to the template_control_richedit function.

	void create_control_richedit(&array editorOptions)
		// !!

	void create_control_autosuggest(&array suggestOptions)
		// !!

	void fetchTagAttributes()
		// !!

	array getMessageIcons(int board_id)
	- retrieves a list of message icons.
	- based on the settings, the array will either contain a list of default
	  message icons or a list of custom message icons retrieved from the
	  database.
	- the board_id is needed for the custom message icons (which can be set for
	  each board individually).

	void AutoSuggestHandler(string checkRegistered = null)
		// !!!

	void AutoSuggest_Search_Member()
		// !!!
*/

// At the moment this is only used for returning WYSIWYG data...
function EditorMain()
{
	global $context, $smfFunc;

	checkSession('get');

	if (!isset($_REQUEST['view']) || !isset($_REQUEST['message']))
		fatal_lang_error('no_access');

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

	// Parse unique ID's and disable javascript into the smileys - using the double space.
	$i = 1;
	$text = preg_replace('~(\s|&nbsp;){1}?<(img\ssrc="' . preg_quote($modSettings['smileys_url'], '~') . '/.+?/(.+?)"\s*).*?border="0" class="smiley" />~e', "'<' . \$smfFunc['db_unescape_string']('$2') . 'border=\"0\" alt=\"\" title=\"\" onresizestart=\"return false;\" id=\"smiley_' . \$i++ . '_$3\" />'", $text);

	return $text;
}

// The harder one - wysiwyg to BBC!
function html_to_bbc($text)
{
	global $db_prefix, $modSettings, $smfFunc, $sourcedir;

	// Remove any newlines - as they are useless.
	$text = strtr($text, array("\n" => ''));

	// Though some of us love paragraphs the parser will do better with breaks.
	$text = preg_replace('~</p>\s*?<p>~i', '<br />', $text);

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
					$tags[] = array('[i]', '[/i]');
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
		$text = substr($text, 0, $start_pos) . $tag . $before . $content . $after . substr($text, $end_pos);
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
	while (preg_match('~<font\s+([^<>]*)>~i', $text, $matches) != false)
	{
		// Find the position of this again.
		$start_pos = strpos($text, $matches[0]);
		$end_pos = false;
		if ($start_pos === false)
			break;

		// This must have an end tag - and we must find the right one.
		$lower_text = strtolower($text);

		$start_pos_test = $start_pos + 4;
		// How many starting tags must we find closing ones for first?
		$start_font_tag_stack = 0;
		while ($start_pos_test < strlen($text))
		{
			// Where is the next starting font?
			$next_start_pos = strpos($lower_text, '<font', $start_pos_test);
			$next_end_pos = strpos($lower_text, '</font>', $start_pos_test);

			// Did we past another starting tag before an end one?
			if ($next_start_pos !== false && $next_start_pos < $next_end_pos)
			{
				$start_font_tag_stack++;
				$start_pos_test = $next_start_pos + 4;
			}
			// Otherwise we have an end tag but not the right one?
			elseif ($start_font_tag_stack)
			{
				$start_font_tag_stack--;
				$start_pos_test = $next_end_pos + 4;
			}
			// Otherwise we're there!
			else
			{
				$end_pos = $next_end_pos;
				break;
			}
		}
		if ($end_pos === false)
			break;

		// Now work out what the attributes are.
		$attribs = fetchTagAttributes($matches[1]);
		$tags = array();
		foreach ($attribs as $s => $v)
		{
			if ($s == 'size')
				$tags[] = array('[size=' . (int) trim($v) . ']', '[/size]');
			elseif ($s == 'face')
				$tags[] = array('[font=' . trim(strtolower($v)) . ']', '[/font]');
			elseif ($s == 'color')
				$tags[] = array('[color=' . trim(strtolower($v)) . ']', '[/color]');
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
	while (preg_match('~<a\s+([^<>]*)>([^<>]*)</a>~i', $text, $matches) != false)
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
			// Get the key and attribute.
			$key_offset = strpos($attrib, '=');
			if ($key_offset === false)
				continue;
			
			$k = trim(substr($attrib, 0, $key_offset));
			$v = trim(substr($attrib, $key_offset + 1));
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

			// External URL?
			if (trim($k) == 'target' && $tag_type == 'url')
			{
				if (trim($v) == '_blank')
					$tag_type == 'iurl';
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

	$text = strip_tags($text);

	// Some tags often end up as just dummy tags - remove those.
	$text = preg_replace('~\[[bisu]\]\s*\[/[bisu]\]~', '', $text);

	$text = legalise_bbc($text);

	return $text;
}

// Returns an array of attributes associated with a tag.
function fetchTagAttributes($text)
{
	$attribs = array();
	$key = $value = '';
	$strpos = 0;
	$tag_state = 0; // 0 = key, 1 = attribute with no string, 2 = attribute with string
	for ($i = 0; $i < strlen($text); $i++)
	{
		// We're either moving from the key to the attribute or we're in a string and this is fine.
		if ($text{$i} == '=')
		{
			if ($tag_state == 0)
				$tag_state = 1;
			elseif ($tag_state == 2)
				$value .= '=';
		}
		// A space is either moving from an attribute back to a potential key or in a string is fine.
		elseif ($text{$i} == ' ')
		{
			if ($tag_state == 2)
				$value .= ' ';
			elseif ($tag_state == 1)
			{
				$attribs[$key] = $value;
				$key = $value = '';
				$tag_state = 0;
			}
		}
		// A quote?
		elseif ($text{$i} == '"')
		{
			// Must be either going into or out of a string.
			if ($tag_state == 1)
				$tag_state = 2;
			else
				$tag_state = 1;
		}
		// Otherwise it's fine.
		else
		{
			if ($tag_state == 0)
				$key .= $text{$i};
			else
				$value .= $text{$i};
		}
	}

	// Anything left?
	if ($key != '' && $value != '')
		$attribs[$key] = $value;

	return $attribs;
}

function getMessageIcons($board_id)
{
	global $modSettings, $context, $db_prefix, $txt, $settings, $smfFunc;

	if (empty($modSettings['messageIcons_enable']))
	{
		loadLanguage('Post');

		$icons = array(
			array('value' => 'xx', 'name' => $txt['standard']),
			array('value' => 'thumbup', 'name' => $txt['thumbs_up']),
			array('value' => 'thumbdown', 'name' => $txt['thumbs_down']),
			array('value' => 'exclamation', 'name' => $txt['excamation_point']),
			array('value' => 'question', 'name' => $txt['question_mark']),
			array('value' => 'lamp', 'name' => $txt['lamp']),
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

// This is an important yet frustrating function - it attempts to clean up illegal BBC caused by browsers like Opera which don't obey the rules!!!
function legalise_bbc($text)
{
	global $modSettings;

	// We are going to cycle through the BBC and keep track of tags as they arise - in order. If get to a block level tag we're going to make sure it's not in a non-block level tag!
	// This will keep the order of tags that are open.
	$current_tags = array();
	// This will quickly let us see if the tag is active.
	$active_tags = array();

	$disabled = array();
	// Only do current tags.
	if (!empty($modSettings['disabledBBC']))
		{
			$temp = explode(',', strtolower($modSettings['disabledBBC']));

			foreach ($temp as $tag)
				$disabled[trim($tag)] = true;
		}

		if (empty($modSettings['enableEmbeddedFlash']))
			$disabled['flash'] = true;

	$all_tags = parse_bbc(false);
	$valid_tags = array();
	foreach ($all_tags as $tag)
	{
		if (!isset($disabled[$tag['tag']]))
			$valid_tags[$tag['tag']] = !empty($tag['block_level']);
	}

	// Don't worry if we're in a code/noubbc.
	$in_code_nobbc = false;

	// These keep track of where we are!
	$new_text = $text;
	$new_text_offset = 0;

	// Right - we're going to start by going through the whole lot to make sure we don't have align stuff crossed as this happens load and is stupid!
	$align_tags = array('left', 'center', 'right', 'pre');
	foreach ($align_tags as $k => $tag)
		if (!isset($valid_tags[$tag]))
			unset($align_tags[$k]);
	if (!empty($align_tags))
	{
		$current_tag = '';
		while (preg_match('~\[(/)*(' . implode('|', $align_tags) . ')\]~', $text, $matches) != false)
		{
			// Get the offset first.
			$offset = strpos($text, $matches[0]);

			// Is it a closing tag?
			if ($matches[1] == '/')
			{
				// Is it the current tag?
				if ($matches[2] == $current_tag)
				{
					$current_tag = '';
				}
				// Otherwise delete it - not important!
				else
				{
					$new_text = substr($new_text, 0, $new_text_offset + $offset) . substr($new_text, $new_text_offset + $offset + strlen($matches[0]));
					$new_text_offset -= strlen($matches[0]);
				}
			}
			// Otherwise if it's new and we have a tag already assume we DO want to change and hence close the last one.
			else
			{
				if ($current_tag != '' && $matches[2] != $current_tag)
				{
					$new_text = substr($new_text, 0, $new_text_offset + $offset) . '[/' . $current_tag . ']' . substr($new_text, $new_text_offset + $offset);
					$new_text_offset += strlen('[/' . $current_tag . ']');
				}
				// A repeat tag gets removed.
				elseif ($matches[2] == $current_tag)
				{
					$new_text = substr($new_text, 0, $new_text_offset + $offset) . substr($new_text, $new_text_offset + $offset + strlen($matches[0]));
					$new_text_offset -= strlen($matches[0]);
				}
				$current_tag = $matches[2];
			}

			// Finally trim text again.
			$text = substr($text, $offset + strlen($matches[0]));
			$new_text_offset += $offset + strlen($matches[0]);
		}
	}

	// Quickly remove any tags which are back to back.
	$strip_b2b_tags = array();
	foreach ($valid_tags as $tag => $dummy)
		$strip_b2b_tags['~\[' . $tag . '\]\s*\[/' . $tag . '\]~'] = '';
	$lastlen = 0;
	while (strlen($new_text) != $lastlen)
	{
		$lastlen = strlen($new_text);
		$new_text = preg_replace(array_keys($strip_b2b_tags), array_values($strip_b2b_tags), $new_text);
	}

	// In case things changed above set these back to normal.
	$in_code_nobbc = false;
	$text = $new_text;
	$new_text_offset = 0;

	for ($i = 0; $i < strlen($text); $i++)
	{
		// Got a start of a tag?
		if ($text{$i} == '[')
		{
			// Is this actually an end tag?
			if ($text{$i + 1} == '/')
			{
				preg_match('~\[/([A-Za-z]+)\]~', substr($text, $i), $matches);
				// Is it valid, eh?
				if (!empty($matches) && isset($valid_tags[$matches[1]]))
				{
					// These are special.
					if ($matches[1] == 'code' || $matches[1] == 'nobbc')
						$in_code_nobbc = false;
					// As long as we're not in code and nobbc and it's been started note it's no longer in action.
					elseif (!$in_code_nobbc && isset($active_tags[$matches[1]]))
					{
						$to_add_back = array();
						// We need to make sure we have the tags closed in the right order.
						while ($tag = array_pop($current_tags))
						{
							// This was the one we are closing so stop.
							if ($tag['type'] == $matches[1])
								break;
							else
							{
								$to_add_back[] = $tag;
							}
						}
						// Add the other tags back as they were in the wrong order before.
						foreach (array_reverse($to_add_back) as $tag)
						{
							$new_text = substr($new_text, 0, $i + $new_text_offset) . '[/' . $tag['type'] . ']' . substr($new_text, $i + $new_text_offset);
							$new_text_offset += strlen('[/' . $tag['type'] . ']');
						}
						// And reopen...
						foreach (array_reverse($to_add_back) as $tag)
						{
							$new_text = substr($new_text, 0, $i + strlen($matches[0]) + $new_text_offset) . $tag['content'] . substr($new_text, $i + strlen($matches[0]) + $new_text_offset);
							$new_text_offset += strlen($tag['content']);
						}

						// Set what the tags are these days...
						foreach ($to_add_back as $tag)
							array_push($current_tags, $tag);

						unset($active_tags[$matches[1]]);
					}
					// What if it's a block level tag we are ending? We need to close any open tags and reopen them afterwards!
					elseif (!$in_code_nobbc && $valid_tags[$matches[1]])
					{
						// Close all the current tags...
						foreach ($current_tags as $tag)
						{
							$new_text = substr($new_text, 0, $i + $new_text_offset) . '[/' . $tag['type'] . ']' . substr($new_text, $i + $new_text_offset);
							$new_text_offset += strlen('[/' . $tag['type'] . ']');
						}

						// The tags are now reversed!
						$current_tags = array_reverse($current_tags);

						// ... and reopen them again.
						foreach ($current_tags as $tag)
						{
							$new_text = substr($new_text, 0, $i + strlen($matches[0]) + $new_text_offset) . $tag['content'] . substr($new_text, $i + strlen($matches[0]) + $new_text_offset);
							$new_text_offset += strlen($tag['content']);
						}
					}
				}

				// Now move on.
				$i += strlen($matches[0]) - 1;
			}
			else
			{
				// Get the tag.
				preg_match('~\[([A-Za-z]+)[^\]\s]*\]~', substr($text, $i), $matches);

				// It's possible that this wasn't actually a tag after all - if not continue!
				if (!isset($matches[0]) || strpos(substr($text, $i), $matches[0]) !== 0)
					$matches = array();

				// Is it actually valid?!
				if (!empty($matches) && isset($valid_tags[$matches[1]]))
				{
					// If it's code or nobbc we need to note it.
					if ($matches[1] == 'code' || $matches[1] == 'nobbc')
						$in_code_nobbc = true;
					// Not block level?
					elseif (!$in_code_nobbc && !$valid_tags[$matches[1]])
					{
						// Can't have two tags active that are the same - close the previous one!
						if (isset($active_tags[$matches[1]]))
						{
							// First add in the new closing tag...
							$new_text = substr($new_text, 0, $i + $new_text_offset) . '[/' . $matches[1] . ']' . substr($new_text, $i + $new_text_offset);
							$new_text_offset += strlen('[/' . $matches[1] . ']');

							// Then find and remove the next one!
							$tag_offset = strpos($new_text, '[/' . $matches[1] . ']', $i + $new_text_offset);
							if ($tag_offset !== false)
								$new_text = substr($new_text, 0, $tag_offset) . substr($new_text, $tag_offset + strlen('[/' . $matches[1] . ']'));

						}
						$active_tags[$matches[1]] = $matches[0];
						$tag = array(
							'type' => $matches[1],
							'content' => $matches[0],
						);
						array_push($current_tags, $tag);
					}
					// If it's a block level then we need to close all active tags and reopen them!
					elseif (!$in_code_nobbc)
					{
						// Close all the old ones.
						foreach (array_reverse($current_tags) as $tag)
						{
							$new_text = substr($new_text, 0, $i + $new_text_offset) . '[/' . $tag['type'] . ']' . substr($new_text, $i + $new_text_offset);
							$new_text_offset += strlen('[/' . $tag['type'] . ']');
						}
						// Open all the new ones again!
						foreach (array_reverse($current_tags) as $tag)
						{
							$new_text = substr($new_text, 0, $i + strlen($matches[0]) + $new_text_offset) . $tag['content'] . substr($new_text, $i + strlen($matches[0]) + $new_text_offset);
							$new_text_offset += strlen($tag['content']);
						}
					}
				}
				// Move on quite a bit!
				elseif (!empty($matches))
					$i += strlen($matches[0]) - 1;
			}
		}
	}

	// Final clean up of back to back tags.
	$lastlen = 0;
	while (strlen($new_text) != $lastlen)
	{
		$lastlen = strlen($new_text);
		$new_text = preg_replace(array_keys($strip_b2b_tags), array_values($strip_b2b_tags), $new_text);
	}

	return $new_text;
}

// Compatibility function - used in 1.1 for showing a post box.
function theme_postbox($msg)
{
	global $context;

	return template_control_richedit($context['post_box_name']);
}

// Creates a box that can be used for richedit stuff like BBC, Smileys etc.
function create_control_richedit($editorOptions)
{
	global $txt, $modSettings, $db_prefix, $options, $smfFunc;
	global $context, $settings, $user_info, $sourcedir;

	// Is this the first richedit - if so we need to ensure some template stuff is initialised.
	if (empty($context['controls']['richedit']))
	{
		// Some general stuff.
		loadTemplate('GenericControls');
		$settings['smileys_url'] = $modSettings['smileys_url'] . '/' . $user_info['smiley_set'];

		// This really has some WYSIWYG stuff.
		$context['html_headers'] .= '
		<link rel="stylesheet" type="text/css" id="rich_edit_css" href="' . $settings['default_theme_url'] . '/css/editor.css" />

		
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var smf_smileys_url = \'' . $settings['smileys_url'] . '\';
		// ]]></script>
		<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/editor.js"></script>';
	}

	// Every control must have a ID!
	assert(isset($editorOptions['id']));
	assert(isset($editorOptions['value']));

	// Start off the editor...
	$context['controls']['richedit'][$editorOptions['id']] = array(
		'id' => $editorOptions['id'],
		'value' => $editorOptions['value'],
		'rich_value' => bbc_to_html($editorOptions['value']),
		'rich_active' => empty($modSettings['disable_wysiwyg']) && (!empty($options['wysiwyg_default']) || !empty($editorOptions['force_rich']) || !empty($_REQUEST[$editorOptions['id'] . '_mode'])),
		'disable_smiley_box' => !empty($editorOptions['disable_smiley_box']),
		'columns' => isset($editorOptions['columns']) ? $editorOptions['columns'] : 60,
		'rows' => isset($editorOptions['rows']) ? $editorOptions['rows'] : 12,
		'width' => isset($editorOptions['width']) ? $editorOptions['width'] : '70%',
		'height' => isset($editorOptions['height']) ? $editorOptions['height'] : '150px',
		'form' => isset($editorOptions['form']) ? $editorOptions['form'] : 'postmodify',
		'bbc_level' => !empty($editorOptions['bbc_level']) ? $editorOptions['bbc_level'] : 'full',
	);

	// Switch between default images and back... mostly in case you don't have an PersonalMessage template, but do have a Post template.
	if (isset($settings['use_default_images']) && $settings['use_default_images'] == 'defaults' && isset($settings['default_template']))
	{
		$temp1 = $settings['theme_url'];
		$settings['theme_url'] = $settings['default_theme_url'];

		$temp2 = $settings['images_url'];
		$settings['images_url'] = $settings['default_images_url'];

		$temp3 = $settings['theme_dir'];
		$settings['theme_dir'] = $settings['default_theme_dir'];
	}

	// Load the Post language file... for the moment at least.
	loadLanguage('Post');

	if (empty($context['bbc_tags']))
	{
		// The below array makes it dead easy to add images to this control. Add it to the array and everything else is done for you!
		$context['bbc_tags'] = array();
		$context['bbc_tags'][] = array(
			'bold' => array('code' => 'b', 'before' => '[b]', 'after' => '[/b]', 'description' => $txt['bold']),
			'italicize' => array('code' => 'i', 'before' => '[i]', 'after' => '[/i]', 'description' => $txt['italic']),
			'underline' => array('code' => 'u', 'before' => '[u]', 'after' => '[/u]', 'description' => $txt['underline']),
			'strike' => array('code' => 's', 'before' => '[s]', 'after' => '[/s]', 'description' => $txt['strike']),
			array(),
			'pre' => array('code' => 'pre', 'before' => '[pre]', 'after' => '[/pre]', 'description' => $txt['preformatted']),
			'left' => array('code' => 'left', 'before' => '[left]', 'after' => '[/left]', 'description' => $txt['left_align']),
			'center' => array('code' => 'center', 'before' => '[center]', 'after' => '[/center]', 'description' => $txt['center']),
			'right' => array('code' => 'right', 'before' => '[right]', 'after' => '[/right]', 'description' => $txt['right_align']),
		);
		$context['bbc_tags'][] = array(
			'flash' => array('code' => 'flash', 'before' => '[flash=200,200]', 'after' => '[/flash]', 'description' => $txt['flash']),
			'img' => array('code' => 'img', 'before' => '[img]', 'after' => '[/img]', 'description' => $txt['image']),
			'url' => array('code' => 'url', 'before' => '[url]', 'after' => '[/url]', 'description' => $txt['hyperlink']),
			'email' => array('code' => 'email', 'before' => '[email]', 'after' => '[/email]', 'description' => $txt['insert_email']),
			'ftp' => array('code' => 'ftp', 'before' => '[ftp]', 'after' => '[/ftp]', 'description' => $txt['ftp']),
			array(),
			'glow' => array('code' => 'glow', 'before' => '[glow=red,2,300]', 'after' => '[/glow]', 'description' => $txt['glow']),
			'shadow' => array('code' => 'shadow', 'before' => '[shadow=red,left]', 'after' => '[/shadow]', 'description' => $txt['shadow']),
			'move' => array('code' => 'move', 'before' => '[move]', 'after' => '[/move]', 'description' => $txt['marquee']),
			array(),
			'sup' => array('code' => 'sup', 'before' => '[sup]', 'after' => '[/sup]', 'description' => $txt['superscript']),
			'sub' => array('code' => 'sub', 'before' => '[sub]', 'after' => '[/sub]', 'description' => $txt['subscript']),
			'tele' => array('code' => 'tt', 'before' => '[tt]', 'after' => '[/tt]', 'description' => $txt['teletype']),
			array(),
			'table' => array('code' => 'table', 'before' => '[table]\n[tr]\n[td]', 'after' => '[/td]\n[/tr]\n[/table]', 'description' => $txt['table']),
			'code' => array('code' => 'code', 'before' => '[code]', 'after' => '[/code]', 'description' => $txt['bbc_code']),
			'quote' => array('code' => 'quote', 'before' => '[quote]', 'after' => '[/quote]', 'description' => $txt['bbc_quote']),
			array(),
			'list' => array('code' => 'list', 'before' => '[list]\n[li]', 'after' => '[/li]\n[li][/li]\n[/list]', 'description' => $txt['list']),
			'hr' => array('code' => 'hr', 'before' => '[hr]', 'description' => $txt['horizontal_rule']),
		);

		// Show the toggle?
		if (empty($modSettings['disable_wysiwyg']))
		{
			$context['bbc_tags'][1][] = array();
			$context['bbc_tags'][1]['toggle'] = array('code' => 'toggle', 'before' => '', 'description' => $txt['toggle_view']);
		}
	}

	// Initialize smiley array... if not loaded before.
	if (empty($context['smileys']) && empty($editorOptions['disable_smiley_box']))
	{
		$context['smileys'] = array(
			'postform' => array(),
			'popup' => array(),
		);
	
		// Load smileys - don't bother to run a query if we're not using the database's ones anyhow.
		if (empty($modSettings['smiley_enable']) && $user_info['smiley_set'] != 'none')
			$context['smileys']['postform'][] = array(
				'smileys' => array(
					array('code' => ':)', 'filename' => 'smiley.gif', 'description' => $txt['icon_smiley']),
					array('code' => ';)', 'filename' => 'wink.gif', 'description' => $txt['icon_wink']),
					array('code' => ':D', 'filename' => 'cheesy.gif', 'description' => $txt['icon_cheesy']),
					array('code' => ';D', 'filename' => 'grin.gif', 'description' => $txt['icon_grin']),
					array('code' => '>:(', 'filename' => 'angry.gif', 'description' => $txt['icon_angry']),
					array('code' => ':(', 'filename' => 'sad.gif', 'description' => $txt['icon_sad']),
					array('code' => ':o', 'filename' => 'shocked.gif', 'description' => $txt['icon_shocked']),
					array('code' => '8)', 'filename' => 'cool.gif', 'description' => $txt['icon_cool']),
					array('code' => '???', 'filename' => 'huh.gif', 'description' => $txt['icon_huh']),
					array('code' => '::)', 'filename' => 'rolleyes.gif', 'description' => $txt['icon_rolleyes']),
					array('code' => ':P', 'filename' => 'tongue.gif', 'description' => $txt['icon_tongue']),
					array('code' => ':-[', 'filename' => 'embarrassed.gif', 'description' => $txt['icon_embarrassed']),
					array('code' => ':-X', 'filename' => 'lipsrsealed.gif', 'description' => $txt['icon_lips']),
					array('code' => ':-\\', 'filename' => 'undecided.gif', 'description' => $txt['icon_undecided']),
					array('code' => ':-*', 'filename' => 'kiss.gif', 'description' => $txt['icon_kiss']),
					array('code' => ':\'(', 'filename' => 'cry.gif', 'description' => $txt['icon_cry'])
				),
				'last' => true,
			);
		elseif ($user_info['smiley_set'] != 'none')
		{
			if (($temp = cache_get_data('posting_smileys', 480)) == null)
			{
				$request = $smfFunc['db_query']('', "
					SELECT code, filename, description, smiley_row, hidden
					FROM {$db_prefix}smileys
					WHERE hidden IN (0, 2)
					ORDER BY smiley_row, smiley_order", __FILE__, __LINE__);
				while ($row = $smfFunc['db_fetch_assoc']($request))
				{
					$row['code'] = htmlspecialchars($row['code']);
					$row['filename'] = htmlspecialchars($row['filename']);
					$row['description'] = htmlspecialchars($row['description']);
	
					$context['smileys'][empty($row['hidden']) ? 'postform' : 'popup'][$row['smiley_row']]['smileys'][] = $row;
				}
				$smfFunc['db_free_result']($request);
	
				cache_put_data('posting_smileys', $context['smileys'], 480);
			}
			else
				$context['smileys'] = $temp;
		}
	
		// Clean house... add slashes to the code for javascript.
		foreach (array_keys($context['smileys']) as $location)
		{
			foreach ($context['smileys'][$location] as $j => $row)
			{
				$n = count($context['smileys'][$location][$j]['smileys']);
				for ($i = 0; $i < $n; $i++)
				{
					$context['smileys'][$location][$j]['smileys'][$i]['code'] = addslashes($context['smileys'][$location][$j]['smileys'][$i]['code']);
					$context['smileys'][$location][$j]['smileys'][$i]['js_description'] = addslashes($context['smileys'][$location][$j]['smileys'][$i]['description']);
				}
	
				$context['smileys'][$location][$j]['smileys'][$n - 1]['last'] = true;
			}
			if (!empty($context['smileys'][$location]))
				$context['smileys'][$location][count($context['smileys'][$location]) - 1]['last'] = true;
		}
	}

	// Set a flag so the sub template knows what to do...
	$context['show_bbc'] = !empty($modSettings['enableBBC']) && !empty($settings['show_bbc']);

	// Generate a list of buttons that shouldn't be shown - this should be the fastest way to do this.
	if (!empty($modSettings['disabledBBC']))
	{
		$disabled_tags = explode(',', $modSettings['disabledBBC']);
		foreach ($disabled_tags as $tag)
			$context['disabled_tags'][trim($tag)] = true;
	}

	// Switch the URLs back... now we're back to whatever the main sub template is.  (like folder in PersonalMessage.)
	if (isset($settings['use_default_images']) && $settings['use_default_images'] == 'defaults' && isset($settings['default_template']))
	{
		$settings['theme_url'] = $temp1;
		$settings['images_url'] = $temp2;
		$settings['theme_dir'] = $temp3;
	}
}

// Create an an autosuggest box?
function create_control_autosuggest(&$suggestOptions)
{
	global $txt, $modSettings, $db_prefix, $options, $smfFunc;
	global $context, $settings, $user_info, $sourcedir;

	// First autosuggest means we need to set up some bits...
	if (empty($context['controls']['autosuggest']))
	{
		// Will want the template.
		loadTemplate('GenericControls');

		// Javascript is cool... says Grudge
		$context['html_headers'] .= '
		<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/suggest.js"></script>';
	}

	// Need an ID and something to search for to suggest!
	assert(isset($suggestOptions['id']));
	assert(isset($suggestOptions['search_type']));
	// Check the search type is registered.
	assert(AutoSuggestHandler($suggestOptions['search_type']));

	// Log this into our collection.
	$context['controls']['autosuggest'][$suggestOptions['id']] = array(
		'id' => $suggestOptions['id'],
		'value' => !empty($suggestOptions['value']) ? $suggestOptions['value'] : '',
		'search_type' => $suggestOptions['search_type'],
		'size' => !empty($suggestOptions['size']) ? $suggestOptions['size'] : 40,
		'width' => !empty($suggestOptions['width']) ? $suggestOptions['width'] : '200px',
		'button' => !empty($suggestOptions['button']) ? $suggestOptions['button'] : false,
		'callbacks' => !empty($suggestOptions['callbacks']) ? $suggestOptions['callbacks'] : array(),
	);
}

// This keeps track of all registered handling functions for auto suggest functionality and passes execution to them.
function AutoSuggestHandler($checkRegistered = null)
{
	global $context;

	// These are all registered types.
	$searchTypes = array(
		'member' => 'Member',
	);

	// If we're just checking the callback function is registered return true or false.
	if ($checkRegistered != null)
		return isset($searchTypes[$checkRegistered]) && function_exists('AutoSuggest_Search_' . $checkRegistered);

	checkSession('get');
	loadTemplate('Xml');

	// Any parameters?
	$context['search_param'] = isset($_REQUEST['search_param']) ? unserialize(base64_decode($_REQUEST['search_param'])) : array();

	if (isset($_REQUEST['suggest_type'], $_REQUEST['search']) && isset($searchTypes[$_REQUEST['suggest_type']]))
	{
		$function = 'AutoSuggest_Search_' . $searchTypes[$_REQUEST['suggest_type']];
		$context['sub_template'] = 'generic_xml';
		$context['xml_data'] = $function();
	}
}

// Search for a member - by realName or memberName by default.
function AutoSuggest_Search_Member()
{
	global $user_info, $db_prefix, $txt, $smfFunc;

	$_REQUEST['search'] = $smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_REQUEST['search'])) . '*';
	$_REQUEST['search'] = $smfFunc['db_escape_string'](trim($smfFunc['strtolower']($_REQUEST['search'])));
	$_REQUEST['search'] = strtr($_REQUEST['search'], array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_', '&#038;' => '&amp;'));

	// Find the member.
	$request = $smfFunc['db_query']('', "
		SELECT id_member, real_name
		FROM {$db_prefix}members
		WHERE real_name LIKE '$_REQUEST[search]'" . (!empty($context['search_param']['buddies']) ? '
			AND id_member IN (' . implode(', ', $user_info['buddies']) . ')' : '') . "
			AND is_activated IN (1, 11)
		LIMIT " . (strlen($_REQUEST['search']) <= 2 ? '100' : '800'), __FILE__, __LINE__);
	$xml_data = array(
		'members' => array(
			'identifier' => 'member',
			'children' => array(),
		),
	);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (function_exists('iconv'))
		{
			$utf8 = iconv($txt['lang_character_set'], 'UTF-8', $row['real_name']);
			if ($utf8)
				$row['real_name'] = $utf8;
		}

		$row['real_name'] = strtr($row['real_name'], array('&amp;' => '&#038;', '&lt;' => '&#060;', '&gt;' => '&#062;', '&quot;' => '&#034;'));

		if (preg_match('~&#\d+;~', $row['real_name']) != 0)
		{
			$fixchar = create_function('$n', '
				if ($n < 128)
					return chr($n);
				elseif ($n < 2048)
					return chr(192 | $n >> 6) . chr(128 | $n & 63);
				elseif ($n < 65536)
					return chr(224 | $n >> 12) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);
				else
					return chr(240 | $n >> 18) . chr(128 | $n >> 12 & 63) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);');

			$row['real_name'] = preg_replace('~&#(\d+);~e', '$fixchar(\'$1\')', $row['real_name']);
		}

		$xml_data['members']['children'][] = array(
			'attributes' => array(
				'id' => $row['id_member'],
			),
			'value' => $row['real_name'],
		);
	}
	$smfFunc['db_free_result']($request);

	return $xml_data;
}

?>