<?php
/******************************************************************************
* Subs-Post.php                                                               *
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
if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file contains those functions pertaining to posting, and other such
	operations, including sending emails, ims, blocking spam, preparsing posts,
	spell checking, and the post box.  This is done with the following:

	void preparsecode(string &message, boolean previewing = false)
		- takes a message and parses it, returning nothing.
		- cleans up links (javascript, etc.) and code/quote sections.
		- won't convert \n's and a few other things if previewing is true.

	string un_preparsecode(string message)
		// !!!

	void fixTags(string &message)
		- used by preparsecode, fixes links in message and returns nothing.

	void fixTag(string &message, string myTag, string protocol,
			bool embeddedUrl = false, bool hasEqualSign = false,
			bool hasExtra = false)
		- used by fixTags, fixes a specific tag's links.
		- myTag is the tag, protocol is http of ftp, embeddedUrl is whether
		  it *can* be set to something, hasEqualSign is whether it *is*
		  set to something, and hasExtra is whether it can have extra
		  cruft after the begin tag.

	bool sendmail(array to, string subject, string message,
			string message_id = auto, string from = webmaster,
			bool send_html = false, int priority = 1, bool hotmail_fix = null)
		- sends an email to the specified recipient.
		- uses the mail_type setting and the webmaster_email global.
		- to is he email(s), string or array, to send to.
		- subject and message are those of the email - expected to have
		  slashes but not be parsed.
		- subject is expected to have entities, message is not.
		- from is a string which masks the address for use with replies.
		- if message_id is specified, uses that as the local-part of the
		  Message-ID header.
		- send_html indicates whether or not the message is HTML vs. plain
		  text, and does not add any HTML.
		- returns whether or not the email was sent properly.

	bool AddMailQueue(bool flush = true, array to_array = array(), string subject = '', string message = '',
		string headers = '', bool send_html = false, int priority = 1)
		//!!

	array sendpm(array recipients, string subject, string message,
			bool store_outbox = false, array from = current_member)
		- sends an personal message from the specified person to the
		  specified people. (from defaults to the user.)
		- recipients should be an array containing the arrays 'to' and 'bcc',
		  both containing ID_MEMBERs.
		- subject and message should have no slashes and no html entities.
		- from is an array, with the id, name, and username of the member.
		- returns an array with log entries telling how many recipients were
		  successful and which recipients it failed to send to.

	string mimespecialchars(string text, bool with_charset = true,
			hotmail_fix = false)
		- prepare text strings for sending as email.
		- in case there are higher ASCII characters in the given string, this
		  function will attempt the transport method 'quoted-printable'. 
		  Otherwise the transport method '7bit' is used.
		- with hotmail_fix set all higher ASCII characters are converted to
		  HTML entities to assure proper display of the mail.
		- returns an array containing the character set, the converted string
		  and the transport method.

	bool smtp_mail(array mail_to_array, string subject, string message,
			string headers)
		- sends mail, like mail() but over SMTP.  Used internally.
		- takes email addresses, a subject and message, and any headers.
		- expects no slashes or entities.
		- returns whether it sent or not.

	bool server_parse(string message, resource socket, string response)
		- sends the specified message to the server, and checks for the
		  expected response. (used internally.)
		- takes the message to send, socket to send on, and the expected
		  response code.
		- returns whether it responded as such.

	void calendarValidatePost()
		- checks if the calendar post was valid.

	void theme_postbox(string message)
		- outputs a postbox from a template.
		- takes a default message as a parameter.

	void SpellCheck()
		- spell checks the post for typos ;).
		- uses the pspell library, which MUST be installed.
		- has problems with internationalization.
		- is accessed via ?action=spellcheck.

	void sendNotifications(array topics, string type, array exclude)
		- sends a notification to members who have elected to receive emails
		  when things happen to a topic, such as replies are posted.
		- uses the Post langauge file.
		- topics represents the topics the action is happening to.
		- the type can be any of reply, sticky, lock, unlock, remove, move,
		  merge, and split.  An appropriate message will be sent for each.
		- automatically finds the subject and its board, and checks permissions
		  for each member who is "signed up" for notifications.
		- will not send 'reply' notifications more than once in a row.
		- members in the exclude array will not be processed for the topic with the same key.

	bool createPost(&array msgOptions, &array topicOptions, &array posterOptions)
		// !!!

	bool createAttachment(&array attachmentOptions)
		// !!!

	bool modifyPost(&array msgOptions, &array topicOptions, &array posterOptions)
		// !!!

	bool approvePosts(array msgs, bool approve)
		// !!!

	array approveTopics(array topics, bool approve)
		// !!!

	void sendApprovalNotifications(array topicData)
		// !!!

	void updateLastMessages(array ID_BOARDs, int ID_MSG)
		- takes an array of board IDs and updates their last messages.
		- if the board has a parent, that parent board is also automatically
		  updated.
		- columns updated are ID_LAST_MSG and lastUpdated.
		- note that ID_LAST_MSG should always be updated using this function,
		  and is not automatically updated upon other changes.

	void adminNotify(string type, int memberID, string memberName = null)
		- sends all admins an email to let them know a new member has joined.
		- types supported are 'approval', 'activation', and 'standard'.
		- called by registerMember() function in Subs-Members.php.
		- email is sent to all groups that have the moderate_forum permission.
		- uses the Login language file.
		- the language set by each member is being used (if available).

	Sending emails from SMF:
	---------------------------------------------------------------------------
		// !!!
*/

// Parses some bbc before sending into the database...
function preparsecode(&$message, $previewing = false)
{
	global $user_info, $modSettings, $smfFunc, $context;

	// This line makes all languages *theoretically* work even with the wrong charset ;).
	//$message = preg_replace('~&amp;#(\d{4,5}|[2-9]\d{2,4}|1[2-9]\d);~', '&#$1;', $message);

	// Clean up after nobbc ;).
	$message = preg_replace('~\[nobbc\](.+?)\[/nobbc\]~ie', '\'[nobbc]\' . strtr(\'$1\', array(\'[\' => \'&#91;\', \']\' => \'&#93;\', \':\' => \'&#58;\', \'@\' => \'&#64;\')) . \'[/nobbc]\'', $message);

	// Remove \r's... they're evil!
	$message = strtr($message, array("\r" => ''));

	// You won't believe this - but too many periods upsets apache it seems!
	$message = preg_replace('~\.{100,}~', '...', $message);

	// Trim off trailing quotes - these often happen by accident.
	while (substr($message, -7) == '[quote]')
		$message = substr($message, 0, -7);
	while (substr($message, 0, 8) == '[/quote]')
		$message = substr($message, 8);

	// Check if all code tags are closed.
	$codeopen = preg_match_all('~(\[code(?:=[^\]]+)?\])~is', $message, $dummy);
	$codeclose = preg_match_all('~(\[/code\])~is', $message, $dummy);

	// Close/open all code tags...
	if ($codeopen > $codeclose)
		$message .= str_repeat('[/code]', $codeopen - $codeclose);
	elseif ($codeclose > $codeopen)
		$message = str_repeat('[code]', $codeclose - $codeopen) . $message;

	// Now that we've fixed all the code tags, let's fix the img and url tags...
	$parts = preg_split('~(\[/code\]|\[code(?:=[^\]]+)?\])~i', $message, -1, PREG_SPLIT_DELIM_CAPTURE);

	// Only mess with stuff outside [code] tags.
	for ($i = 0, $n = count($parts); $i < $n; $i++)
	{
		// It goes 0 = outside, 1 = begin tag, 2 = inside, 3 = close tag, repeat.
		if ($i % 4 == 0)
		{
			fixTags($parts[$i]);

			// Replace /me.+?\n with [me=name]dsf[/me]\n.
			if (strpos($user_info['name'], '[') !== false || strpos($user_info['name'], ']') !== false || strpos($user_info['name'], '\'') !== false || strpos($user_info['name'], '"') !== false)
				$parts[$i] = preg_replace('~(?:\A|\n)/me(?: |&nbsp;)([^\n\z]*)~i', '[me=&quot;' . $user_info['name'] . '&quot;]$1[/me]', $parts[$i]);
			else
				$parts[$i] = preg_replace('~(?:\A|\n)/me(?: |&nbsp;)([^\n\z]*)~i', '[me=' . $user_info['name'] . ']$1[/me]', $parts[$i]);

			if (!$previewing)
			{
				if (allowedTo('admin_forum'))
					$parts[$i] = preg_replace('~\[html\](.+?)\[/html\]~ise', '\'[html]\' . strtr(un_htmlspecialchars(\'$1\'), array("\n" => \'&#13;\', \'  \' => \' &#32;\')) . \'[/html]\'', $parts[$i]);
				// We should edit them out, or else if an admin edits the message they will get shown...
				else
					$parts[$i] = preg_replace('~\[[/]?html\]~i', '', $parts[$i]);
			}

			// Let's look at the time tags...
			$parts[$i] = preg_replace('~\[time(=(absolute))*\](.+?)\[/time\]~ie', '\'[time]\' . (is_numeric(\'$3\') || @strtotime(\'$3\') == 0 ? \'$3\' : strtotime(\'$3\') - (\'$2\' == \'absolute\' ? 0 : (($modSettings[\'time_offset\'] + $user_info[\'time_offset\']) * 3600))) . \'[/time]\'', $parts[$i]);

			$list_open = substr_count($parts[$i], '[list]') + substr_count($parts[$i], '[list ');
			$list_close = substr_count($parts[$i], '[/list]');
			if ($list_close - $list_open > 0)
				$parts[$i] = str_repeat('[list]', $list_close - $list_open) . $parts[$i];
			if ($list_open - $list_close > 0)
				$parts[$i] = $parts[$i] . str_repeat('[/list]', $list_open - $list_close);

			$mistake_fixes = array(
				// Find [table]s not followed by [tr].
				'~\[table\](?![\s' . ($context['utf8'] ? '\x{C2A0}' : '\xA0') . ']*\[tr\])~is' . ($context['utf8'] ? 'u' : '') => '[table][tr]',
				// Find [tr]s not followed by [td].
				'~\[tr\](?![\s' . ($context['utf8'] ? '\x{C2A0}' : '\xA0') . ']*\[td\])~is' . ($context['utf8'] ? 'u' : '') => '[tr][td]',
				// Find [/td]s not followed by something valid.
				'~\[/td\](?![\s' . ($context['utf8'] ? '\x{C2A0}' : '\xA0') . ']*(?:\[td\]|\[/tr\]|\[/table\]))~is' . ($context['utf8'] ? 'u' : '') => '[/td][/tr]',
				// Find [/tr]s not followed by something valid.
				'~\[/tr\](?![\s' . ($context['utf8'] ? '\x{C2A0}' : '\xA0') . ']*(?:\[tr\]|\[/table\]))~is' . ($context['utf8'] ? 'u' : '') => '[/tr][/table]',
				// Find [/td]s incorrectly followed by [/table].
				'~\[/td\][\s' . ($context['utf8'] ? '\x{C2A0}' : '\xA0') . ']*\[/table\]~is' . ($context['utf8'] ? 'u' : '') => '[/td][/tr][/table]',
				// Find [table]s, [tr]s, and [/td]s (possibly correctly) followed by [td].
				'~\[(table|tr|/td)\]([\s' . ($context['utf8'] ? '\x{C2A0}' : '\xA0') . ']*)\[td\]~is' . ($context['utf8'] ? 'u' : '') => '[$1]$2[_td_]',
				// Now, any [td]s left should have a [tr] before them.
				'~\[td\]~is' => '[tr][td]',
				// Look for [tr]s which are correctly placed.
				'~\[(table|/tr)\]([\s' . ($context['utf8'] ? '\x{C2A0}' : '\xA0') . ']*)\[tr\]~is' . ($context['utf8'] ? 'u' : '') => '[$1]$2[_tr_]',
				// Any remaining [tr]s should have a [table] before them.
				'~\[tr\]~is' => '[table][tr]',
				// Look for [/td]s followed by [/tr].
				'~\[/td\]([\s' . ($context['utf8'] ? '\x{C2A0}' : '\xA0') . ']*)\[/tr\]~is' . ($context['utf8'] ? 'u' : '') => '[/td]$1[_/tr_]',
				// Any remaining [/tr]s should have a [/td].
				'~\[/tr\]~is' => '[/td][/tr]',
				// Look for properly opened [li]s which aren't closed.
				'~\[li\]([^\[\]]+?)\[li\]~is' => '[li]$1[_/li_][_li_]',
				'~\[li\]([^\[\]]+?)$~is' => '[li]$1[/li]',
				// Lists - find correctly closed items/lists.
				'~\[/li\]([\s' . ($context['utf8'] ? '\x{C2A0}' : '\xA0') . ']*)\[/list\]~is' . ($context['utf8'] ? 'u' : '') => '[_/li_]$1[/list]',
				// Find list items closed and then opened.
				'~\[/li\]([\s' . ($context['utf8'] ? '\x{C2A0}' : '\xA0') . ']*)\[li\]~is' . ($context['utf8'] ? 'u' : '') => '[_/li_]$1[_li_]',
				// Now, find any [list]s or [/li]s followed by [li].
				'~\[(list(?: [^\]]*?)?|/li)\]([\s' . ($context['utf8'] ? '\x{C2A0}' : '\xA0') . ']*)\[li\]~is' . ($context['utf8'] ? 'u' : '') => '[$1]$2[_li_]',
				// Any remaining [li]s weren't inside a [list].
				'~\[li\]~i' => '[list][li]',
				// Any remaining [/li]s weren't before a [/list].
				'~\[/li\]~i' => '[/li][/list]',
				// Put the correct ones back how we found them.
				'~\[_(li|/li|td|tr|/tr)_\]~i' => '[$1]',
			);

			// Fix up some use of tables without [tr]s, etc. (it has to be done more than once to catch it all.)
			for ($j = 0; $j < 3; $j++)
				$parts[$i] = preg_replace(array_keys($mistake_fixes), $mistake_fixes, $parts[$i]);
		}
	}

	// Put it back together!
	if (!$previewing)
		$message = strtr(implode('', $parts), array('  ' => '&nbsp; ', "\n" => '<br />', $context['utf8'] ? "\xC2\xA0" : "\xA0" => '&nbsp;'));
	else
		$message = strtr(implode('', $parts), array('  ' => '&nbsp; ', $context['utf8'] ? "\xC2\xA0" : "\xA0" => '&nbsp;'));

	// Now let's quickly clean up things that will slow our parser (which are common in posted code.)
	$message = strtr($message, array('[]' => '&#91;]', '[&#039;' => '&#91;&#039;'));
}

// This is very simple, and just removes things done by preparsecode.
function un_preparsecode($message)
{
	$parts = preg_split('~(\[/code\]|\[code(?:=[^\]]+)?\])~i', $message, -1, PREG_SPLIT_DELIM_CAPTURE);

	// We're going to unparse only the stuff outside [code]...
	for ($i = 0, $n = count($parts); $i < $n; $i++)
	{
		// If $i is a multiple of four (0, 4, 8, ...) then it's not a code section...
		if ($i % 4 == 0)
		{
			$parts[$i] = preg_replace('~\[html\](.+?)\[/html\]~ie', '\'[html]\' . strtr(htmlspecialchars(stripslashes(\'$1\'), ENT_QUOTES), array(\'&amp;#13;\' => \'<br />\', \'&amp;#32;\' => \' \')) . \'[/html]\'', $parts[$i]);

			// Attempt to un-parse the time to something less awful.
			$parts[$i] = preg_replace('~\[time\](\d{0,10})\[/time\]~ie', '\'[time]\' . strftime(\'%c\', \'$1\') . \'[/time]\'', $parts[$i]);
		}
	}

	// Change breaks back to \n's.
	return preg_replace('~<br( /)?' . '>~', "\n", implode('', $parts));
}

// Fix any URLs posted - ie. remove 'javascript:'.
function fixTags(&$message)
{
	global $modSettings;

	// WARNING: Editing the below can cause large security holes in your forum.
	// Edit only if you are sure you know what you are doing.

	$fixArray = array(
		// [img]http://...[/img] or [img width=1]http://...[/img]
		array(
			'tag' => 'img',
			'protocols' => array('http', 'https'),
			'embeddedUrl' => false,
			'hasEqualSign' => false,
			'hasExtra' => true,
		),
		// [url]http://...[/url]
		array(
			'tag' => 'url',
			'protocols' => array('http', 'https'),
			'embeddedUrl' => true,
			'hasEqualSign' => false,
		),
		// [url=http://...]name[/url]
		array(
			'tag' => 'url',
			'protocols' => array('http', 'https'),
			'embeddedUrl' => true,
			'hasEqualSign' => true,
		),
		// [iurl]http://...[/iurl]
		array(
			'tag' => 'iurl',
			'protocols' => array('http', 'https'),
			'embeddedUrl' => true,
			'hasEqualSign' => false,
		),
		// [iurl=http://...]name[/iurl]
		array(
			'tag' => 'iurl',
			'protocols' => array('http', 'https'),
			'embeddedUrl' => true,
			'hasEqualSign' => true,
		),
		// [ftp]ftp://...[/ftp]
		array(
			'tag' => 'ftp',
			'protocols' => array('ftp', 'ftps'),
			'embeddedUrl' => true,
			'hasEqualSign' => false,
		),
		// [ftp=ftp://...]name[/ftp]
		array(
			'tag' => 'ftp',
			'protocols' => array('ftp', 'ftps'),
			'embeddedUrl' => true,
			'hasEqualSign' => true,
		),
		// [flash]http://...[/flash]
		array(
			'tag' => 'flash',
			'protocols' => array('http', 'https'),
			'embeddedUrl' => false,
			'hasEqualSign' => false,
			'hasExtra' => true,
		),
	);

	// Fix each type of tag.
	foreach ($fixArray as $param)
		fixTag($message, $param['tag'], $param['protocols'], $param['embeddedUrl'], $param['hasEqualSign'], !empty($param['hasExtra']));

	// Now fix possible security problems with images loading links automatically...
	$message = preg_replace('~(\[img.*?\])(.+?)\[/img\]~eis', '\'$1\' . preg_replace(\'~action(=|%3d)(?!dlattach)~i\', \'action-\', \'$2\') . \'[/img]\'', $message);

	// Limit the size of images posted?
	if (!empty($modSettings['max_image_width']) || !empty($modSettings['max_image_height']))
	{
		// Find all the img tags - with or without width and height.
		preg_match_all('~\[img(\s+width=\d+)?(\s+height=\d+)?(\s+width=\d+)?\](.+?)\[/img\]~is', $message, $matches, PREG_PATTERN_ORDER);

		$replaces = array();
		foreach ($matches[0] as $match => $dummy)
		{
			// If the width was after the height, handle it.
			$matches[1][$match] = !empty($matches[3][$match]) ? $matches[3][$match] : $matches[1][$match];

			// Now figure out if they had a desired height or width...
			$desired_width = !empty($matches[1][$match]) ? (int) substr(trim($matches[1][$match]), 6) : 0;
			$desired_height = !empty($matches[2][$match]) ? (int) substr(trim($matches[2][$match]), 7) : 0;

			// One was omitted, or both.  We'll have to find its real size...
			if (empty($desired_width) || empty($desired_height))
			{
				list ($width, $height) = url_image_size(un_htmlspecialchars($matches[4][$match]));

				// They don't have any desired width or height!
				if (empty($desired_width) && empty($desired_height))
				{
					$desired_width = $width;
					$desired_height = $height;
				}
				// Scale it to the width...
				elseif (empty($desired_width) && !empty($height))
					$desired_width = (int) (($desired_height * $width) / $height);
				// Scale if to the height.
				elseif (!empty($width))
					$desired_height = (int) (($desired_width * $height) / $width);
			}

			// If the width and height are fine, just continue along...
			if ($desired_width <= $modSettings['max_image_width'] && $desired_height <= $modSettings['max_image_height'])
				continue;

			// Too bad, it's too wide.  Make it as wide as the maximum.
			if ($desired_width > $modSettings['max_image_width'] && !empty($modSettings['max_image_width']))
			{
				$desired_height = (int) (($modSettings['max_image_width'] * $desired_height) / $desired_width);
				$desired_width = $modSettings['max_image_width'];
			}

			// Now check the height, as well.  Might have to scale twice, even...
			if ($desired_height > $modSettings['max_image_height'] && !empty($modSettings['max_image_height']))
			{
				$desired_width = (int) (($modSettings['max_image_height'] * $desired_width) / $desired_height);
				$desired_height = $modSettings['max_image_height'];
			}

			$replaces[$matches[0][$match]] = '[img' . (!empty($desired_width) ? ' width=' . $desired_width : '') . (!empty($desired_height) ? ' height=' . $desired_height : '') . ']' . $matches[4][$match] . '[/img]';
		}

		// If any img tags were actually changed...
		if (!empty($replaces))
			$message = strtr($message, $replaces);
	}
}

// Fix a specific class of tag - ie. url with =.
function fixTag(&$message, $myTag, $protocols, $embeddedUrl = false, $hasEqualSign = false, $hasExtra = false)
{
	global $boardurl, $scripturl;

	if (preg_match('~^([^:]+://[^/]+)~', $boardurl, $match) != 0)
		$domain_url = $match[1];
	else
		$domain_url = $boardurl . '/';

	$replaces = array();

	if ($hasEqualSign)
		preg_match_all('~\[(' . $myTag . ')=([^\]]*?)\]~is', $message, $matches);
	else
		preg_match_all('~\[(' . $myTag . ($hasExtra ? '(?:[^\]]*?)' : '') . ')\](.+?)\[/(' . $myTag . ')\]~is', $message, $matches);

	foreach ($matches[0] as $k => $dummy)
	{
		// Remove all leading and trailing whitespace.
		$replace = trim($matches[2][$k]);
		$this_tag = $matches[1][$k];
		if (!$hasEqualSign)
			$this_close = $matches[3][$k];

		$found = false;
		foreach ($protocols as $protocol)
		{
			$found = strncasecmp($replace, $protocol . '://', strlen($protocol) + 3) === 0;
			if ($found)
				break;
		}

		if (!$found && $protocols[0] == 'http')
		{
			if (substr($replace, 0, 1) == '/')
				$replace = $domain_url . $replace;
			elseif (substr($replace, 0, 1) == '?')
				$replace = $scripturl . $replace;
			elseif (substr($replace, 0, 1) == '#' && $embeddedUrl)
			{
				$replace = '#' . preg_replace('~[^A-Za-z0-9_\-#]~', '', substr($replace, 1));
				$this_tag = 'iurl';
				$this_close = 'iurl';
			}
			else
				$replace = $protocols[0] . '://' . $replace;
		}
		elseif (!$found)
			$replace = $protocols[0] . '://' . $replace;

		if ($hasEqualSign)
			$replaces['[' . $matches[1][$k] . '=' . $matches[2][$k] . ']'] = '[' . $this_tag . '=' . $replace . ']';
		elseif ($embeddedUrl)
			$replaces['[' . $matches[1][$k] . ']' . $matches[2][$k] . '[/' . $matches[3][$k] . ']'] = '[' . $this_tag . '=' . $replace . ']' . $matches[2][$k] . '[/' . $this_close . ']';
		else
			$replaces['[' . $matches[1][$k] . ']' . $matches[2][$k] . '[/' . $matches[3][$k] . ']'] = '[' . $this_tag . ']' . $replace . '[/' . $this_close . ']';
	}

	foreach ($replaces as $k => $v)
	{
		if ($k == $v)
			unset($replaces[$k]);
	}

	if (!empty($replaces))
		$message = strtr($message, $replaces);
}

// Send off an email.
function sendmail($to, $subject, $message, $from = null, $message_id = null, $send_html = false, $priority = 1, $hotmail_fix = null)
{
	global $webmaster_email, $context, $modSettings, $txt, $scripturl;
	global $db_prefix;

	// So far so good.
	$mail_result = true;

	// If the recipient list isn't an array, make it one.
	$to_array = is_array($to) ? $to : array($to);

	// Sadly Hotmail doesn't support character sets properly.
	if ($hotmail_fix === null)
	{
		$hotmail_to = array();
		foreach ($to_array as $i => $to_address)
		{
			if (preg_match('~@hotmail.[a-zA-Z\.]{2,6}$~i', $to_address) === 1)
			{
				$hotmail_to[] = $to_address;
				$to_array = array_diff($to_array, array($to_address));
			}
		}

		// Call this function recursively for the hotmail addresses.
		if (!empty($hotmail_to))
			$mail_result = sendmail($hotmail_to, $subject, $message, $from, $message_id, $send_html, $priority, true);

		// The remaining addresses no longer need the fix.
		$hotmail_fix = false;

		// No other addresses left? Return instantly.
		if (empty($to_array))
			return $mail_result;
	}

	// Get rid of slashes and entities.
	$subject = un_htmlspecialchars(stripslashes($subject));
	// Make the message use \r\n's only.
	$message = str_replace(array("\r", "\n"), array('', "\r\n"), stripslashes($message));

	// Make sure hotmail mails are sent as HTML so that HTML entities work.
	if ($hotmail_fix && !$send_html)
	{
		$send_html = true;
		$message = strtr($message, array("\r\n" => "<br />\r\n"));
		$message = preg_replace('~(' . preg_quote($scripturl, '~') . '([?/][\w\-_%\.,\?&;=#]+)?)~', '<a href="$1">$1</a>', $message);
	}

	list (, $from_name) = mimespecialchars(addcslashes($from !== null ? $from : $context['forum_name'], '<>()\'\\"'), true, $hotmail_fix);
	list (, $subject) = mimespecialchars($subject, true, $hotmail_fix);

	// Construct the mail headers...
	$headers = 'From: "' . $from_name . '" <' . (empty($modSettings['mail_from']) ? $webmaster_email : $modSettings['mail_from']) . ">\r\n";
	$headers .= $from !== null ? 'Reply-To: <' . $from . ">\r\n" : '';
	$headers .= 'Return-Path: ' . (empty($modSettings['mail_from']) ? $webmaster_email: $modSettings['mail_from']) . "\r\n";
	$headers .= 'Date: ' . gmdate('D, d M Y H:i:s') . ' +0000' . "\r\n";

	if ($message_id !== null && empty($modSettings['mail_no_message_id']))
		$headers .= 'Message-ID: <' . md5($scripturl . microtime()) . '-' . $message_id . strstr(empty($modSettings['mail_from']) ? $webmaster_email : $modSettings['mail_from'], '@') . ">\r\n";
	$headers .= "X-Mailer: SMF\r\n";

	if (isset($modSettings['integrate_outgoing_email']) && function_exists($modSettings['integrate_outgoing_email']))
	{
		if ($modSettings['integrate_outgoing_email']($subject, $message, $headers) === false)
			return false;
	}	
	
	$charset = isset($context['character_set']) ? $context['character_set'] : $txt['lang_character_set'];

	list ($charset, $message, $encoding) = mimespecialchars($message, false, $hotmail_fix);

	// Sending HTML?  Let's plop in some basic stuff, then.
	if ($send_html)
	{
		// This should send a text message with MIME multipart/alternative stuff.
		$mime_boundary = 'SMF-' . md5($message . time());
		$headers .= 'Mime-Version: 1.0' . "\r\n";
		$headers .= 'Content-Type: multipart/alternative; boundary="' . $mime_boundary . '"' . "\r\n";
		$headers .= 'Content-Transfer-Encoding: ' . ($encoding == '' ? '7bit' : $encoding);

		// Save the original message...
		$orig_message = $message;

		// But, then, dump it and use a plain one for dinosaur clients.
		$message = un_htmlspecialchars(strip_tags(strtr($orig_message, array('</title>' => "\r\n")))) . "\r\n--" . $mime_boundary . "\r\n";

		// This is the plain text version.  Even if no one sees it, we need it for spam checkers.
		$message .= 'Content-Type: text/plain; charset=' . $charset . "\r\n";
		$message .= 'Content-Transfer-Encoding: ' . ($encoding == '' ? '7bit' : $encoding) . "\r\n\r\n";
		$message .= un_htmlspecialchars(strip_tags(strtr($orig_message, array('</title>' => "\r\n")))) . "\r\n--" . $mime_boundary . "\r\n";

		// This is the actual HTML message, prim and proper.  If we wanted images, they could be inlined here (with multipart/related, etc.)
		$message .= 'Content-Type: text/html; charset=' . $charset . "\r\n";
		$message .= 'Content-Transfer-Encoding: ' . ($encoding == '' ? '7bit' : $encoding) . "\r\n\r\n";
		$message .= $orig_message . "\r\n--" . $mime_boundary . '--';
	}
	// Text is good too.
	else
	{
		$headers .= 'Content-Type: text/plain; charset=' . $charset . "\r\n";
		if ($encoding != '')
			$headers .= 'Content-Transfer-Encoding: ' . $encoding;
	}

	// Are we using the mail queue, if so this is where we butt in...
	if (!empty($modSettings['mail_queue']) && $priority < 4)
		return AddMailQueue(false, $to_array, $subject, $message, $headers, $send_html, $priority);
	// If it's a priority mail, send it now - note though that this should NOT be used for sending many at once.
	elseif (!empty($modSettings['mail_queue']) && !empty($modSettings['mail_limit']))
	{
		list ($mt, $mn) = @explode('|', $modSettings['mail_recent']);
		if (empty($mn) || time() > $mt + 60)
			$mr = time() . '|' . 1;
		else
			$mr = $mt . '|' . ((int) $mn + 1);

		updateSettings(array('mail_recent' => $mr));
	}

	// SMTP or sendmail?
	if (empty($modSettings['mail_type']) || $modSettings['smtp_host'] == '')
	{
		$subject = strtr($subject, array("\r" => '', "\n" => ''));
		if (!empty($modSettings['mail_strip_carriage']))
		{
			$message = strtr($message, array("\r" => ''));
			$headers = strtr($headers, array("\r" => ''));
		}

		foreach ($to_array as $to)
		{
			if (!mail(strtr($to, array("\r" => '', "\n" => '')), $subject, $message, $headers))
			{
				log_error(sprintf($txt['mail_send_unable'], $to));
				$mail_result = false;
			}

			// Wait, wait, I'm still sending here!
			@set_time_limit(300);
			if (function_exists('apache_reset_timeout'))
				apache_reset_timeout();
		}
	}
	else
		$mail_result = $mail_result  && smtp_mail($to_array, $subject, $message, $send_html ? $headers : "Mime-Version: 1.0\r\n" . $headers);

	// Everything go smoothly?
	return $mail_result;
}

// Add an email to the mail queue.
function AddMailQueue($flush = false, $to_array = array(), $subject = '', $message = '', $headers = '', $send_html = false, $priority = 1)
{
	global $db_prefix, $context, $modSettings;

	static $cur_insert = array();
	static $cur_insert_len = 0;

	// If we're flushing, make the final inserts - also if we're near the MySQL length limit!
	if (($flush || $cur_insert_len > 800000)&& !empty($cur_insert))
	{
		// Explode out the query and run it.
		db_query("
			INSERT INTO {$db_prefix}mail_queue
				(time_sent, recipient, body, subject, headers, send_html, priority)
			VALUES
				" . implode(',', $cur_insert), __FILE__, __LINE__);

		$cur_insert = array();
		$cur_insert_len = 0;

		$context['flush_mail'] = false;
	}

	// If we're flushing we're done.
	if ($flush)
	{
		$nextSendTime = time() + 10;

		db_query("
			UPDATE {$db_prefix}settings
			SET value = '$nextSendTime'
			WHERE variable = 'mail_next_send'
				AND value = '0'", __FILE__, __LINE__);

		return true;
	}

	// Now prepare the data for inserting.
	$subject = addslashes($subject);
	$message = addslashes($message);
	$headers = addslashes($headers);

	// Ensure we tell obExit to flush.
	$context['flush_mail'] = true;

	foreach ($to_array as $to)
	{
		// Will this insert go over MySQL's limit?
		$this_insert_len = strlen($to) + strlen($message) + strlen($headers) + 700;

		// Insert limit of 1M (just under the safety) is reached?
		if ($this_insert_len + $cur_insert_len > 1000000)
		{
			// Flush out what we have so far.
			db_query("
				INSERT INTO {$db_prefix}mail_queue
					(time_sent, recipient, body, subject, headers, send_html, priority)
				VALUES
					" . implode(",\n", $cur_insert), __FILE__, __LINE__);

			// Clear this out.
			$cur_insert = array();
			$cur_insert_len = 0;
		}

		// Now add the current insert to the array...
		$cur_insert[] = "(" . time() . ", '$to', '$message', '$subject', '$headers', " . ($send_html ? 1 : 0) . ", $priority)";
		$cur_insert_len += $this_insert_len;
	}

	return true;
}

// Send off a personal message.
function sendpm($recipients, $subject, $message, $store_outbox = false, $from = null)
{
	global $db_prefix, $ID_MEMBER, $scripturl, $txt, $user_info, $language;
	global $modSettings, $smfFunc;

	// Initialize log array.
	$log = array(
		'failed' => array(),
		'sent' => array()
	);

	if ($from === null)
		$from = array(
			'id' => $ID_MEMBER,
			'name' => $user_info['name'],
			'username' => $user_info['username']
		);
	// Probably not needed.  /me something should be of the typer.
	else
		$user_info['name'] = $from['name'];

	// This is the one that will go in their inbox.
	$htmlmessage = $smfFunc['htmlspecialchars']($message, ENT_QUOTES);
	$htmlsubject = $smfFunc['htmlspecialchars']($subject);
	preparsecode($htmlmessage);

	// Integrated PMs
	if (isset($modSettings['integrate_personal_message']) && function_exists($modSettings['integrate_personal_message']))
		$modSettings['integrate_personal_message']($recipients, $from['username'], $subject, $message);
	
	// Get a list of usernames and convert them to IDs.
	$usernames = array();
	foreach ($recipients as $rec_type => $rec)
	{
		foreach ($rec as $id => $member)
		{
			if (!is_numeric($recipients[$rec_type][$id]))
			{
				$recipients[$rec_type][$id] = $smfFunc['strtolower'](trim(preg_replace('/[<>&"\'=\\\]/', '', $recipients[$rec_type][$id])));
				$usernames[$recipients[$rec_type][$id]] = 0;
			}
		}
	}
	if (!empty($usernames))
	{
		$request = db_query("
			SELECT ID_MEMBER, memberName
			FROM {$db_prefix}members
			WHERE memberName IN ('" . implode("', '", array_keys($usernames)) . "')", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			if (isset($usernames[$smfFunc['strtolower']($row['memberName'])]))
				$usernames[$smfFunc['strtolower']($row['memberName'])] = $row['ID_MEMBER'];
		mysql_free_result($request);

		// Replace the usernames with IDs. Drop usernames that couldn't be found.
		foreach ($recipients as $rec_type => $rec)
			foreach ($rec as $id => $member)
			{
				if (is_numeric($recipients[$rec_type][$id]))
					continue;

				if (!empty($usernames[$member]))
					$recipients[$rec_type][$id] = $usernames[$member];
				else
				{
					$log['failed'][] = sprintf($txt['pm_error_user_not_found'], $recipients[$rec_type][$id]);
					unset($recipients[$rec_type][$id]);
				}
			}
	}

	// Make sure there are no duplicate 'to' members.
	$recipients['to'] = array_unique($recipients['to']);

	// Only 'bcc' members that aren't already in 'to'.
	$recipients['bcc'] = array_diff(array_unique($recipients['bcc']), $recipients['to']);

	// Combine 'to' and 'bcc' recipients.
	$all_to = array_merge($recipients['to'], $recipients['bcc']);

	$request = db_query("
		SELECT
			mem.memberName, mem.realName, mem.ID_MEMBER, mem.emailAddress, mem.lngfile, mg.maxMessages,
			mem.pm_email_notify, mem.instantMessages," . (allowedTo('moderate_forum') ? ' 0' : "
			(mem.pm_ignore_list = '*' OR FIND_IN_SET($from[id], mem.pm_ignore_list))") . " AS ignored,
			FIND_IN_SET($from[id], mem.buddy_list) AS is_buddy, mem.is_activated,
			(mem.ID_GROUP = 1 OR FIND_IN_SET(1, mem.additionalGroups)) AS is_admin
		FROM {$db_prefix}members AS mem
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
		WHERE mem.ID_MEMBER IN (" . implode(", ", $all_to) . ")
		ORDER BY mem.lngfile
		LIMIT " . count($all_to), __FILE__, __LINE__);
	$notifications = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Has the receiver gone over their message limit, assuming that neither they nor the sender are important?!
		if (!empty($row['maxMessages']) && $row['maxMessages'] <= $row['instantMessages'] && !allowedTo('moderate_forum') && !$row['is_admin'])
		{
			$log['failed'][] = sprintf($txt['pm_error_data_limit_reached'], $row['realName']);
			unset($all_to[array_search($row['ID_MEMBER'], $all_to)]);
			continue;
		}

		if (!empty($row['ignored']))
		{
			$log['failed'][] = sprintf($txt['pm_error_ignored_by_user'], $row['realName']);
			unset($all_to[array_search($row['ID_MEMBER'], $all_to)]);
			continue;
		}

		// Send a notification, if enabled - taking into account buddy list!.
		if (!empty($row['emailAddress']) && ($row['pm_email_notify'] == 1 || ($row['pm_email_notify'] > 1 && ($row['is_buddy'] || !empty($modSettings['enable_buddylist'])))) && $row['is_activated'] == 1)
			$notifications[empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile']][] = $row['emailAddress'];

		$log['sent'][] = sprintf(isset($txt['pm_successfully_sent']) ? $txt['pm_successfully_sent'] : '', $row['realName']);
	}
	mysql_free_result($request);

	// Only 'send' the message if there are any recipients left.
	if (empty($all_to))
		return $log;

	// Insert the message itself and then grab the last insert id.
	db_query("
		INSERT INTO {$db_prefix}personal_messages
			(ID_MEMBER_FROM, deletedBySender, fromName, msgtime, subject, body)
		VALUES ($from[id], " . ($store_outbox ? '0' : '1') . ", SUBSTRING('$from[username]', 1, 255), " . time() . ", SUBSTRING('$htmlsubject', 1, 255), SUBSTRING('$htmlmessage', 1, 65534))", __FILE__, __LINE__);
	$ID_PM = db_insert_id();

	// Add the recipients.
	if (!empty($ID_PM))
	{
		// Some people think manually deleting personal_messages is fun... it's not. We protect against it though :)
		db_query("
			DELETE FROM {$db_prefix}pm_recipients
			WHERE ID_PM = $ID_PM", __FILE__, __LINE__);

		$insertRows = array();
		foreach ($all_to as $to)
			$insertRows[] = "($ID_PM, $to, " . (in_array($to, $recipients['bcc']) ? '1' : '0') . ')';

		db_query("
			INSERT INTO {$db_prefix}pm_recipients
				(ID_PM, ID_MEMBER, bcc)
			VALUES " . implode(',
				', $insertRows), __FILE__, __LINE__);
	}

	$message = stripslashes($message);
	censorText($message);
	censorText($subject);
	$message = trim(un_htmlspecialchars(strip_tags(strtr(parse_bbc(htmlspecialchars($message), false), array('<br />' => "\n", '</div>' => "\n", '</li>' => "\n", '&#91;' => '[', '&#93;' => ']')))));

	foreach ($notifications as $lang => $notification_list)
	{
		// Make sure to use the right language.
		if (loadLanguage('PersonalMessage', $lang, false) === false)
			loadLanguage('InstantMessage', $lang, false);

		// Replace the right things in the message strings.
		$mailsubject = str_replace(array('SUBJECT', 'SENDER'), array($subject, un_htmlspecialchars($from['name'])), $txt[561]);
		$mailmessage = str_replace(array('SUBJECT', 'MESSAGE', 'SENDER'), array($subject, $message, un_htmlspecialchars($from['name'])), $txt[562]);
		$mailmessage .= "\n\n" . $txt['instant_reply'] . ' ' . $scripturl . '?action=pm;sa=send;f=inbox;pmsg=' . $ID_PM . ';quote;u=' . $from['id'];

		// Off the notification email goes!
		sendmail($notification_list, $mailsubject, $mailmessage, null, 'p' . $ID_PM, false, 2);
	}

	// Back to what we were on before!
	if (loadLanguage('PersonalMessage') === false)
		loadLanguage('InstantMessage');

	// Add one to their unread and read message counts.
	updateMemberData($all_to, array('instantMessages' => '+', 'unreadMessages' => '+'));

	return $log;
}

// Prepare text strings for sending as email body or header.
function mimespecialchars($string, $with_charset = true, $hotmail_fix = false)
{
	global $context;

	$charset = $context['character_set'];

	// This is the fun part....
	if (preg_match_all('~&#(\d{3,8});~', $string, $matches) !== 0 && !$hotmail_fix)
	{
		// Let's, for now, assume there are only &#021;'ish characters.
		$simple = true;

		foreach ($matches[1] as $entity)
			if ($entity > 128)
				$simple = false;
		unset($matches);

		if ($simple)
			$string = preg_replace('~&#(\d{3,8});~e', 'chr(\'$1\')', $string);
		else
		{
			// Try to convert the string to UTF-8.
			if (!$context['utf8'] && function_exists('iconv'))
				$string = @iconv($context['character_set'], 'UTF-8', $string);

			$fixchar = create_function('$n', '
				if ($n < 128)
					return chr($n);
				elseif ($n < 2048)
					return chr(192 | $n >> 6) . chr(128 | $n & 63);
				elseif ($n < 65536)
					return chr(224 | $n >> 12) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);
				else
					return chr(240 | $n >> 18) . chr(128 | $n >> 12 & 63) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);');

			$string = preg_replace('~&#(\d{3,8});~e', '$fixchar(\'$1\')', $string);

			// Unicode, baby.
			$charset = 'UTF-8';
		}
	}

	// Convert all special characters to HTML entities...just for Hotmail :-\
	if ($hotmail_fix && ($context['utf8'] || function_exists('iconv') || $context['character_set'] === 'ISO-8859-1'))
	{
		if (!$context['utf8'] && function_exists('iconv'))
			$string = @iconv($context['character_set'], 'UTF-8', $string);

		$entityConvert = create_function('$c', '
			if (strlen($c) === 1 && ord($c{0}) <= 0x7F)
				return $c;
			elseif (strlen($c) === 2 && ord($c{0}) >= 0xC0 && ord($c{0}) <= 0xDF)
				return "&#" . (((ord($c{0}) ^ 0xC0) << 6) + (ord($c{1}) ^ 0x80)) . ";";
			elseif (strlen($c) === 3 && ord($c{0}) >= 0xE0 && ord($c{0}) <= 0xEF)
				return "&#" . (((ord($c{0}) ^ 0xE0) << 12) + ((ord($c{1}) ^ 0x80) << 6) + (ord($c{2}) ^ 0x80)) . ";";
			elseif (strlen($c) === 4 && ord($c{0}) >= 0xF0 && ord($c{0}) <= 0xF7)
				return "&#" . (((ord($c{0}) ^ 0xF0) << 18) + ((ord($c{1}) ^ 0x80) << 12) + ((ord($c{2}) ^ 0x80) << 6) + (ord($c{3}) ^ 0x80)) . ";";
			else
				return "";');

		// Convert all 'special' characters to HTML entities.
		preg_replace('~[\x80-\x{10FFFF}]~eu', '$entityConvert("\1")', $string);

		return array($charset, $string, '7bit');
	}

	// We don't need to mess with the subject line if no special characters were in it..
	elseif (!$hotmail_fix &preg_match('~([^\x09\x0A\x0D\x20-\x7F])~', $string) === 1)
	{
		// replace special characters...
		$string = preg_replace('~([^\x09\x0A\x0D\x20\x25-\x3C\x3E\x41-\x5A\x61-\x7A])~e', 'sprintf("=%02X", ord("\1"))', $string);

		$start_with = $with_charset ? '=?' . $charset . '?Q?' : '';
		$end_with = $with_charset ? '?=' : '';
		$num_chars = 76 - strlen($start_with) - strlen($end_with);


/*		// Add soft breaks after 76 characters.
		$lines = explode("\r\n", $string);
		foreach ($lines as $i => $dummy)
		{
			$result = array();
			foreach (explode("\r\n", $lines[$i]) as $line)
			{
				while (true)
				{
					if (strlen($line) <= $num_chars)
					{
						$result[] = $line;
						break;
					}
					$cut_at = $line{$num_chars - 2} === '=' ?  $num_chars - 2 : ($line{$num_chars - 3} === '=' ? $num_chars - 3 : $num_chars - 1);
					$result[] = substr($line, 0, $cut_at);// . '=';
					$line = substr($line, $cut_at);
				}
			}
			$lines[$i] = implode("$end_with\r\n$start_with", $result);
		}
		return array($charset, $start_with . implode("$end_with\r\n$start_with", $lines) . $end_with, 'quoted-printable');
*/		
		return array($charset, $start_with . $string . $end_with, 'quoted-printable');
	}

	else
		return array($charset, $string, '7bit');
}

// Send an email via SMTP.
function smtp_mail($mail_to_array, $subject, $message, $headers)
{
	global $modSettings, $webmaster_email, $txt;

	$modSettings['smtp_host'] = trim($modSettings['smtp_host']);

	// Try POP3 before SMTP?
	// !!! There's no interface for this yet.
	if ($modSettings['mail_type'] == 2 && $modSettings['smtp_username'] != '' && $modSettings['smtp_password'] != '')
	{
		$socket = fsockopen($modSettings['smtp_host'], 110, $errno, $errstr, 2);
		if (!$socket && (substr($modSettings['smtp_host'], 0, 5) == 'smtp.' || substr($modSettings['smtp_host'], 0, 11) == 'ssl://smtp.'))
			$socket = fsockopen(strtr($modSettings['smtp_host'], array('smtp.' => 'pop.')), 110, $errno, $errstr, 2);

		if ($socket)
		{
			fgets($socket, 256);
			fputs($socket, 'USER ' . $modSettings['smtp_username'] . "\r\n");
			fgets($socket, 256);
			fputs($socket, 'PASS ' . base64_decode($modSettings['smtp_password']) . "\r\n");
			fgets($socket, 256);
			fputs($socket, 'QUIT' . "\r\n");

			fclose($socket);
		}
	}

	// Try to connect to the SMTP server... if it doesn't exist, only wait three seconds.
	if (!$socket = fsockopen($modSettings['smtp_host'], empty($modSettings['smtp_port']) ? 25 : $modSettings['smtp_port'], $errno, $errstr, 3))
	{
		// Maybe we can still save this?  The port might be wrong.
		if (substr($modSettings['smtp_host'], 0, 4) == 'ssl:' && (empty($modSettings['smtp_port']) || $modSettings['smtp_port'] == 25))
		{
			if ($socket = fsockopen($modSettings['smtp_host'], 465, $errno, $errstr, 3))
				log_error($txt['smtp_port_ssl']);
		}

		// Unable to connect!  Don't show any error message, but just log one and try to continue anyway.
		if (!$socket)
		{
			log_error($txt['smtp_no_connect'] . ': ' . $errno . ' : ' . $errstr);
			return false;
		}
	}

	// Wait for a response of 220, without "-" continuer.
	if (!server_parse(null, $socket, '220'))
		return false;

	if ($modSettings['mail_type'] == 1 && $modSettings['smtp_username'] != '' && $modSettings['smtp_password'] != '')
	{
		// !!! These should send the CURRENT server's name, not the mail server's!

		// EHLO could be understood to mean encrypted hello...
		if (server_parse('EHLO ' . $modSettings['smtp_host'], $socket, null) == '250')
		{
			if (!server_parse('AUTH LOGIN', $socket, '334'))
				return false;
			// Send the username and password, encoded.
			if (!server_parse(base64_encode($modSettings['smtp_username']), $socket, '334'))
				return false;
			// The password is already encoded ;)
			if (!server_parse($modSettings['smtp_password'], $socket, '235'))
				return false;
		}
		elseif (!server_parse('HELO ' . $modSettings['smtp_host'], $socket, '250'))
			return false;
	}
	else
	{
		// Just say "helo".
		if (!server_parse('HELO ' . $modSettings['smtp_host'], $socket, '250'))
			return false;
	}

	// Fix the message for any lines beginning with a period! (the first is ignored, you see.)
	$message = strtr($message, array("\r\n." => "\r\n.."));

	// !! Theoretically, we should be able to just loop the RCPT TO.
	$mail_to_array = array_values($mail_to_array);
	foreach ($mail_to_array as $i => $mail_to)
	{
		// Reset the connection to send another email.
		if ($i != 0)
		{
			if (!server_parse('RSET', $socket, '250'))
				return false;
		}

		// From, to, and then start the data...
		if (!server_parse('MAIL FROM: <' . (empty($modSettings['mail_from']) ? $webmaster_email : $modSettings['mail_from']) . '>', $socket, '250'))
			return false;
		if (!server_parse('RCPT TO: <' . $mail_to . '>', $socket, '250'))
			return false;
		if (!server_parse('DATA', $socket, '354'))
			return false;
		fputs($socket, 'Subject: ' . $subject . "\r\n");
		if (strlen($mail_to) > 0)
			fputs($socket, 'To: <' . $mail_to . ">\r\n");
		fputs($socket, $headers . "\r\n\r\n");
		fputs($socket, $message . "\r\n");

		// Send a ., or in other words "end of data".
		if (!server_parse('.', $socket, '250'))
			return false;

		// Almost done, almost done... don't stop me just yet!
		@set_time_limit(300);
		if (function_exists('apache_reset_timeout'))
			apache_reset_timeout();
	}
	fputs($socket, "QUIT\r\n");
	fclose($socket);

	return true;
}

// Parse a message to the SMTP server.
function server_parse($message, $socket, $response)
{
	global $txt;

	if ($message !== null)
		fputs($socket, $message . "\r\n");

	// No response yet.
	$server_response = '';

	while (substr($server_response, 3, 1) != ' ')
		if (!($server_response = fgets($socket, 256)))
		{
			// !!! Change this message to reflect that it may mean bad user/password/server issues/etc.
			log_error($txt['smtp_bad_response']);
			return false;
		}

	if ($response === null)
		return substr($server_response, 0, 3);

	if (substr($server_response, 0, 3) != $response)
	{
		log_error($txt['smtp_error'] . $server_response);
		return false;
	}

	return true;
}

// Makes sure the calendar post is valid.
function calendarValidatePost()
{
	global $modSettings, $txt, $sourcedir, $smfFunc;

	if (!isset($_POST['deleteevent']))
	{
		// No month?  No year?
		if (!isset($_POST['month']))
			fatal_lang_error('calendar7', false);
		if (!isset($_POST['year']))
			fatal_lang_error('calendar8', false);

		// Check the month and year...
		if ($_POST['month'] < 1 || $_POST['month'] > 12)
			fatal_lang_error('calendar1', false);
		if ($_POST['year'] < $modSettings['cal_minyear'] || $_POST['year'] > $modSettings['cal_maxyear'])
			fatal_lang_error('calendar2', false);
	}

	// Make sure they're allowed to post...
	isAllowedTo('calendar_post');

	if (isset($_POST['span']))
	{
		// Make sure it's turned on and not some fool trying to trick it.
		if (empty($modSettings['cal_allowspan']))
			fatal_lang_error('calendar55', false);
		if ($_POST['span'] < 1 || $_POST['span'] > $modSettings['cal_maxspan'])
			fatal_lang_error('calendar56', false);
	}

	// There is no need to validate the following values if we are just deleting the event.
	if (!isset($_POST['deleteevent']))
	{
		// No day?
		if (!isset($_POST['day']))
			fatal_lang_error('calendar14', false);
		if (!isset($_POST['evtitle']) && !isset($_POST['subject']))
			fatal_lang_error('calendar15', false);
		elseif (!isset($_POST['evtitle']))
			$_POST['evtitle'] = $_POST['subject'];

		// Bad day?
		if (!checkdate($_POST['month'], $_POST['day'], $_POST['year']))
			fatal_lang_error('calendar16', false);

		// No title?
		if ($smfFunc['htmltrim']($_POST['evtitle']) === '')
			fatal_lang_error('calendar17', false);
		if ($smfFunc['strlen']($_POST['evtitle']) > 30)
			$_POST['evtitle'] = $smfFunc['substr']($_POST['evtitle'], 0, 30);
		$_POST['evtitle'] = str_replace(';', '', $_POST['evtitle']);
	}
}

// Prints a post box.  Used everywhere you post or send.
function theme_postbox($msg)
{
	global $txt, $modSettings, $db_prefix;
	global $context, $settings, $user_info;

	// Switch between default images and back... mostly in case you don't have an PersonalMessage template, but do ahve a Post template.
	if (isset($settings['use_default_images']) && $settings['use_default_images'] == 'defaults' && isset($settings['default_template']))
	{
		$temp1 = $settings['theme_url'];
		$settings['theme_url'] = $settings['default_theme_url'];

		$temp2 = $settings['images_url'];
		$settings['images_url'] = $settings['default_images_url'];

		$temp3 = $settings['theme_dir'];
		$settings['theme_dir'] = $settings['default_theme_dir'];
	}

	// Load the Post template and language file.
	loadLanguage('Post');
	loadTemplate('Post');

	// Initialize smiley array...
	$context['smileys'] = array(
		'postform' => array(),
		'popup' => array(),
	);

	// Load smileys - don't bother to run a query if we're not using the database's ones anyhow.
	if (empty($modSettings['smiley_enable']) && $user_info['smiley_set'] != 'none')
		$context['smileys']['postform'][] = array(
			'smileys' => array(
				array('code' => ':)', 'filename' => 'smiley.gif', 'description' => $txt[287]),
				array('code' => ';)', 'filename' => 'wink.gif', 'description' => $txt[292]),
				array('code' => ':D', 'filename' => 'cheesy.gif', 'description' => $txt[289]),
				array('code' => ';D', 'filename' => 'grin.gif', 'description' => $txt[293]),
				array('code' => '>:(', 'filename' => 'angry.gif', 'description' => $txt[288]),
				array('code' => ':(', 'filename' => 'sad.gif', 'description' => $txt[291]),
				array('code' => ':o', 'filename' => 'shocked.gif', 'description' => $txt[294]),
				array('code' => '8)', 'filename' => 'cool.gif', 'description' => $txt[295]),
				array('code' => '???', 'filename' => 'huh.gif', 'description' => $txt[296]),
				array('code' => '::)', 'filename' => 'rolleyes.gif', 'description' => $txt[450]),
				array('code' => ':P', 'filename' => 'tongue.gif', 'description' => $txt[451]),
				array('code' => ':-[', 'filename' => 'embarrassed.gif', 'description' => $txt[526]),
				array('code' => ':-X', 'filename' => 'lipsrsealed.gif', 'description' => $txt[527]),
				array('code' => ':-\\', 'filename' => 'undecided.gif', 'description' => $txt[528]),
				array('code' => ':-*', 'filename' => 'kiss.gif', 'description' => $txt[529]),
				array('code' => ':\'(', 'filename' => 'cry.gif', 'description' => $txt[530])
			),
			'last' => true,
		);
	elseif ($user_info['smiley_set'] != 'none')
	{
		if (($temp = cache_get_data('posting_smileys', 480)) == null)
		{
			$request = db_query("
				SELECT code, filename, description, smileyRow, hidden
				FROM {$db_prefix}smileys
				WHERE hidden IN (0, 2)
				ORDER BY smileyRow, smileyOrder", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
			{
				$row['code'] = htmlspecialchars($row['code']);
				$row['filename'] = htmlspecialchars($row['filename']);
				$row['description'] = htmlspecialchars($row['description']);

				$context['smileys'][empty($row['hidden']) ? 'postform' : 'popup'][$row['smileyRow']]['smileys'][] = $row;
			}
			mysql_free_result($request);

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
	$settings['smileys_url'] = $modSettings['smileys_url'] . '/' . $user_info['smiley_set'];

	// Allow for things to be overridden.
	if (!isset($context['post_box_columns']))
		$context['post_box_columns'] = 60;
	if (!isset($context['post_box_rows']))
		$context['post_box_rows'] = 12;
	if (!isset($context['post_box_name']))
		$context['post_box_name'] = 'message';
	if (!isset($context['post_form']))
		$context['post_form'] = 'postmodify';

	// Set a flag so the sub template knows what to do...
	$context['show_bbc'] = !empty($modSettings['enableBBC']) && !empty($settings['show_bbc']);

	// Generate a list of buttons that shouldn't be shown - this should be the fastest way to do this.
	if (!empty($modSettings['disabledBBC']))
	{
		$disabled_tags = explode(',', $modSettings['disabledBBC']);
		foreach ($disabled_tags as $tag)
			$context['disabled_tags'][trim($tag)] = true;
	}

	// Go!  Supa-sub-template-smash!
	template_postbox($msg);

	// Switch the URLs back... now we're back to whatever the main sub template is.  (like folder in PersonalMessage.)
	if (isset($settings['use_default_images']) && $settings['use_default_images'] == 'defaults' && isset($settings['default_template']))
	{
		$settings['theme_url'] = $temp1;
		$settings['images_url'] = $temp2;
		$settings['theme_dir'] = $temp3;
	}
}

function SpellCheck()
{
	global $txt, $context, $smfFunc;

	// A list of "words" we know about but pspell doesn't.
	$known_words = array('smf', 'php', 'mysql', 'www', 'gif', 'jpeg', 'png', 'http', 'smfisawesome', 'grandia', 'terranigma', 'rpgs');

	loadLanguage('Post');
	loadTemplate('Post');

	// Okay, this looks funny, but it actually fixes a weird bug.
	ob_start();
	$old = error_reporting(0);

	// See, first, some windows machines don't load pspell properly on the first try.  Dumb, but this is a workaround.
	pspell_new('en');

	// Next, the dictionary in question may not exist.  So, we try it... but...
	$pspell_link = pspell_new($txt['lang_dictionary'], $txt['lang_spelling'], '', strtr($txt['lang_character_set'], array('iso-' => 'iso', 'ISO-' => 'iso')), PSPELL_FAST | PSPELL_RUN_TOGETHER);
	error_reporting($old);
	ob_end_clean();

	// Most people don't have anything but english installed... so we use english as a last resort.
	if (!$pspell_link)
		$pspell_link = pspell_new('en', '', '', '', PSPELL_FAST | PSPELL_RUN_TOGETHER);

	if (!isset($_POST['spellstring']) || !$pspell_link)
		die;

	// Can't have any \n's or \r's in javascript strings.
	$mystr = trim(str_replace(array("\r", "\n"), array('', '_|_'), stripslashes($_POST['spellstring'])));

	$alphas = '������������������������������[:alpha:]\'';
	preg_match_all('~(?:<[^>]+>)|(?:\[[^ ][^\]]*\])|(?:&[^; ]+;)|(?<=^|[^' . $alphas . '])([' . $alphas . ']+)~si', $mystr, $alphas, PREG_PATTERN_ORDER);

	// Do this after because the js doesn't count '\"' as two, but PHP does.
	$context['spell_js'] = '
		var txt = {"done": "' . $txt['spellcheck_done'] . '"};
		var mispstr = "' . str_replace(array('\\', '"', '</script>'), array('\\\\', '\\"', '<" + "/script>'), $mystr) . '";
		var misps = Array(';

	// This is some sanity checking: they should be chronological.
	$last_occurance = 0;

	$found_words = false;
	$code_block = false;
	for ($i = 0, $n = count($alphas[0]); $i < $n; $i++)
	{
		// Check if we're inside a code block...
		if (preg_match('~\[/?code\]~i', $alphas[0][$i]))
			$code_block = !$code_block;

		// Code block?  No word?
		if (empty($alphas[1][$i]) || $code_block)
		{
			$last_occurance += strlen($alphas[0][$i]);
			continue;
		}

		$check_word = $alphas[1][$i];
		if (substr($check_word, 0, 1) == '\'')
			$check_word = substr($check_word, 1);
		if (substr($check_word, -1) == '\'')
			$check_word = substr($check_word, 0, -1);

		// If the word is a known word, or spelled right...
		// !!! Add an option for uppercase skipping?
		if (in_array($smfFunc['strtolower']($check_word), $known_words) || pspell_check($pspell_link, $check_word) || strtoupper($check_word) == $check_word)
		{
			// Add on this word's length, and continue.
			$last_occurance += strlen($alphas[0][$i]);
			continue;
		}

		// Find the word, and move up the "last occurance" to here.
		if ($last_occurance <= strlen($mystr))
			$last_occurance = strpos($mystr, $alphas[0][$i], $last_occurance + 1);
		$found_words = true;

		// Add on the javascript for this misspelling.
		$context['spell_js'] .= '
			new misp("' . $alphas[1][$i] . '", ' . (int) $last_occurance . ', ' . ($last_occurance + strlen($alphas[1][$i]) - 1) . ', [';

		// If there are suggestions, add them in...
		$suggestions = pspell_suggest($pspell_link, $check_word);
		if (!empty($suggestions))
		{
			for ($j = 0, $k = count($suggestions); $j < $k; $j++)
			{
				// Add back the quotes...
				if (substr($alphas[1][$i], 0, 1) == '\'')
					$suggestions[$j] = '\'' . $suggestions[$j];
				if (substr($alphas[1][$i], -1) == '\'')
					$suggestions[$j] = $suggestions[$j] . '\'';
			}

			$context['spell_js'] .= '"' . join('", "', $suggestions) . '"';
		}

		$context['spell_js'] .= ']),';
	}

	// If words were found, take off the last comma.
	if ($found_words)
		$context['spell_js'] = substr($context['spell_js'], 0, -1);

	$context['spell_js'] .= '
		);';

	// And instruct the template system to just show the spellcheck sub template.
	$context['template_layers'] = array();
	$context['sub_template'] = 'spellcheck';
}

// Notify members that something has happened to a topic  they marked!
function sendNotifications($topics, $type, $exclude = array())
{
	global $txt, $scripturl, $db_prefix, $language, $user_info;
	global $ID_MEMBER, $modSettings, $sourcedir;

	$notification_types = array(
		'reply' => array('subject' => 'notification_reply_subject', 'message' => 'notification_reply'),
		'sticky' => array('subject' => 'notification_sticky_subject', 'message' => 'notification_sticky'),
		'lock' => array('subject' => 'notification_lock_subject', 'message' => 'notification_lock'),
		'unlock' => array('subject' => 'notification_unlock_subject', 'message' => 'notification_unlock'),
		'remove' => array('subject' => 'notification_remove_subject', 'message' => 'notification_remove'),
		'move' => array('subject' => 'notification_move_subject', 'message' => 'notification_move'),
		'merge' => array('subject' => 'notification_merge_subject', 'message' => 'notification_merge'),
		'split' => array('subject' => 'notification_split_subject', 'message' => 'notification_split'),
	);
	$current_type = $notification_types[$type];

	// Can't do it if there's no topics.
	if (empty($topics))
		return;
	// It must be an array - it must!
	if (!is_array($topics))
		$topics = array($topics);

	// Get the subject and body...
	$result = db_query("
		SELECT mf.subject, ml.body, ml.ID_MEMBER, t.ID_LAST_MSG, t.ID_TOPIC,
			IFNULL(mem.realName, ml.posterName) AS posterName
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS mf, {$db_prefix}messages AS ml)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = ml.ID_MEMBER)
		WHERE t.ID_TOPIC IN (" . implode(',', $topics) . ")
			AND mf.ID_MSG = t.ID_FIRST_MSG
			AND ml.ID_MSG = t.ID_LAST_MSG
		LIMIT 1", __FILE__, __LINE__);
	$topicData = array();
	while ($row = mysql_fetch_assoc($result))
	{
		// Clean it up.
		censorText($row['subject']);
		censorText($row['body']);
		$row['subject'] = un_htmlspecialchars($row['subject']);
		$row['body'] = trim(un_htmlspecialchars(strip_tags(strtr(parse_bbc($row['body'], false, $row['ID_LAST_MSG']), array('<br />' => "\n", '</div>' => "\n", '</li>' => "\n", '&#91;' => '[', '&#93;' => ']')))));

		$topicData[$row['ID_TOPIC']] = array(
			'subject' => $row['subject'],
			'body' => $row['body'],
			'last_id' => $row['ID_LAST_MSG'],
			'topic' => $row['ID_TOPIC'],
			'name' => $user_info['name'],
			'exclude' => '',
		);
	}
	mysql_free_result($result);

	// Work out any exclusions...
	foreach ($topics as $key => $id)
		if (isset($topicData[$id]) && !empty($exclude[$key]))
			$topicData[$id]['exclude'] = (int) $exclude[$key];

	// Nada?
	if (empty($topicData))
		trigger_error('sendNotifications(): topics not found', E_USER_NOTICE);

	$topics = array_keys($topicData);

	// Insert all of these items into the digest log for those who want notifications later.
	$digest_insert = array();
	foreach ($topicData as $id => $data)
		$digest_insert[] = "($data[topic], $data[last_id], '$type', " . ((int) $data['exclude']) . ')';
	db_query("
		INSERT INTO {$db_prefix}log_digest
			(ID_TOPIC, ID_MSG, note_type, exclude)
		VALUES
			" . implode(', ', $digest_insert), __FILE__, __LINE__);

	// Find the members with notification on for this topic.
	$members = db_query("
		SELECT
			mem.ID_MEMBER, mem.emailAddress, mem.notifyRegularity, mem.notifyTypes, mem.notifySendBody, mem.lngfile,
			ln.sent, mem.ID_GROUP, mem.additionalGroups, b.memberGroups, mem.ID_POST_GROUP, t.ID_MEMBER_STARTED,
			ln.ID_TOPIC
		FROM ({$db_prefix}log_notify AS ln, {$db_prefix}members AS mem, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
		WHERE ln.ID_TOPIC IN (" . implode(',', $topics) . ")
			AND t.ID_TOPIC = ln.ID_TOPIC
			AND b.ID_BOARD = t.ID_BOARD
			AND ln.ID_MEMBER != $ID_MEMBER
			AND mem.is_activated = 1
			AND mem.notifyTypes < " . ($type == 'reply' ? '4' : '3') . "
			AND mem.notifyRegularity < 2
			AND ln.ID_MEMBER = mem.ID_MEMBER
		GROUP BY mem.ID_MEMBER, ln.ID_TOPIC
		ORDER BY mem.lngfile", __FILE__, __LINE__);
	$sent = 0;
	while ($row = mysql_fetch_assoc($members))
	{
		// Don't do the excluded...
		if ($topicData[$row['ID_TOPIC']]['exclude'] == $row['ID_MEMBER'])
			continue;

		// Easier to check this here... if they aren't the topic poster do they really want to know?
		if ($type != 'reply' && $row['notifyTypes'] == 2 && $row['ID_MEMBER'] != $row['ID_MEMBER_STARTED'])
			continue;

		if ($row['ID_GROUP'] != 1)
		{
			$allowed = explode(',', $row['memberGroups']);
			$row['additionalGroups'] = explode(',', $row['additionalGroups']);
			$row['additionalGroups'][] = $row['ID_GROUP'];
			$row['additionalGroups'][] = $row['ID_POST_GROUP'];

			if (count(array_intersect($allowed, $row['additionalGroups'])) == 0)
				continue;
		}

		$needed_language = empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile'];
		if (empty($current_language) || $current_language != $needed_language)
			$current_language = loadLanguage('Post', $needed_language, false);

		$message = sprintf($txt[$current_type['message']], un_htmlspecialchars($topicData[$row['ID_TOPIC']]['name']));
		if ($type != 'remove')
			$message .=
				$scripturl . '?topic=' . $row['ID_TOPIC'] . '.new;topicseen#new' . "\n\n" .
				$txt['notifyUnsubscribe'] . ': ' . $scripturl . '?action=notify;topic=' . $row['ID_TOPIC'] . '.0';
		// Do they want the body of the message sent too?
		if (!empty($row['notifySendBody']) && $type == 'reply' && empty($modSettings['disallow_sendBody']))
			$message .= "\n\n" . $txt['notification_reply_body'] . "\n\n" . $topicData[$row['ID_TOPIC']]['body'];
		if (!empty($row['notifyRegularity']) && $type == 'reply')
			$message .= "\n\n" . $txt['notifyXOnce2'];

		// Send only if once is off or it's on and it hasn't been sent.
		if ($type != 'reply' || empty($row['notifyRegularity']) || empty($row['sent']))
		{
			sendmail($row['emailAddress'], sprintf($txt[$current_type['subject']], $topicData[$row['ID_TOPIC']]['subject']),
				$message . "\n\n" .
				$txt[130], null, 'm' . $topicData[$row['ID_TOPIC']]['last_id']);
			$sent++;
		}
	}
	mysql_free_result($members);

	if (isset($current_language) && $current_language != $user_info['language'])
		loadLanguage('Post');

	// Sent!
	if ($type == 'reply' && !empty($sent))
		db_query("
			UPDATE {$db_prefix}log_notify
			SET sent = 1
			WHERE ID_TOPIC IN (" . implode(',', $topics) . ")
				AND ID_MEMBER != $ID_MEMBER", __FILE__, __LINE__);

	// For approvals we need to unsend the exclusions (This *is* the quickest way!)
	if (!empty($sent) && !empty($exclude))
	{
		foreach ($topicData as $id => $data)
			if ($data['exclude'])
				db_query("
					UPDATE {$db_prefix}log_notify
					SET sent = 0
					WHERE ID_TOPIC = $id
						AND ID_MEMBER = $data[exclude]", __FILE__, __LINE__);
	}
}

// Create a post, either as new topic (ID_TOPIC = 0) or in an existing one.
// The input parameters of this function assume:
// - Strings have been escaped.
// - Integers have been cast to integer.
// - Mandatory parameters are set.
function createPost(&$msgOptions, &$topicOptions, &$posterOptions)
{
	global $db_prefix, $user_info, $ID_MEMBER, $txt, $modSettings;

	// Set optional parameters to the default value.
	$msgOptions['icon'] = empty($msgOptions['icon']) ? 'xx' : $msgOptions['icon'];
	$msgOptions['smileys_enabled'] = !empty($msgOptions['smileys_enabled']);
	$msgOptions['attachments'] = empty($msgOptions['attachments']) ? array() : $msgOptions['attachments'];
	$msgOptions['approved'] = isset($msgOptions['approved']) ? (int) $msgOptions['approved'] : 1;
	$topicOptions['id'] = empty($topicOptions['id']) ? 0 : (int) $topicOptions['id'];
	$topicOptions['poll'] = isset($topicOptions['poll']) ? (int) $topicOptions['poll'] : null;
	$topicOptions['lock_mode'] = isset($topicOptions['lock_mode']) ?  $topicOptions['lock_mode'] : null;
	$topicOptions['sticky_mode'] = isset($topicOptions['sticky_mode']) ? $topicOptions['sticky_mode'] : null;
	$posterOptions['id'] = empty($posterOptions['id']) ? 0 : (int) $posterOptions['id'];
	$posterOptions['ip'] = empty($posterOptions['ip']) ? $user_info['ip'] : $posterOptions['ip'];

	// If nothing was filled in as name/e-mail address, try the member table.
	if (!isset($posterOptions['name']) || $posterOptions['name'] == '' || (empty($posterOptions['email']) && !empty($posterOptions['id'])))
	{
		if (empty($posterOptions['id']))
		{
			$posterOptions['id'] = 0;
			$posterOptions['name'] = $txt[28];
			$posterOptions['email'] = '';
		}
		elseif ($posterOptions['id'] != $ID_MEMBER)
		{
			$request = db_query("
				SELECT memberName, emailAddress
				FROM {$db_prefix}members
				WHERE ID_MEMBER = $posterOptions[id]
				LIMIT 1", __FILE__, __LINE__);
			// Couldn't find the current poster?
			if (mysql_num_rows($request) == 0)
			{
				trigger_error('createPost(): Invalid member id ' . $posterOptions['id'], E_USER_NOTICE);
				$posterOptions['id'] = 0;
				$posterOptions['name'] = $txt[28];
				$posterOptions['email'] = '';
			}
			else
				list ($posterOptions['name'], $posterOptions['email']) = mysql_fetch_row($request);
			mysql_free_result($request);
		}
		else
		{
			$posterOptions['name'] = $user_info['name'];
			$posterOptions['email'] = $user_info['email'];
		}

		$posterOptions['email'] = addslashes($posterOptions['email']);
	}

	// It's do or die time: forget any user aborts!
	$previous_ignore_user_abort = ignore_user_abort(true);

	$new_topic = empty($topicOptions['id']);

	// Insert the post.
	db_query("
		INSERT INTO {$db_prefix}messages
			(ID_BOARD, ID_TOPIC, ID_MEMBER, subject, body, posterName, posterEmail, posterTime,
			posterIP, smileysEnabled, modifiedName, icon, approved)
		VALUES ($topicOptions[board], $topicOptions[id], $posterOptions[id], SUBSTRING('$msgOptions[subject]', 1, 255), SUBSTRING('$msgOptions[body]', 1, 65534), SUBSTRING('$posterOptions[name]', 1, 255), SUBSTRING('$posterOptions[email]', 1, 255), " . time() . ",
			SUBSTRING('$posterOptions[ip]', 1, 255), " . ($msgOptions['smileys_enabled'] ? '1' : '0') . ", '', SUBSTRING('$msgOptions[icon]', 1, 16), $msgOptions[approved])", __FILE__, __LINE__);
	$msgOptions['id'] = db_insert_id();

	// Something went wrong creating the message...
	if (empty($msgOptions['id']))
		return false;

	// Fix the attachments.
	if (!empty($msgOptions['attachments']))
		db_query("
			UPDATE {$db_prefix}attachments
			SET ID_MSG = $msgOptions[id]
			WHERE ID_ATTACH IN (" . implode(', ', $msgOptions['attachments']) . ')', __FILE__, __LINE__);

	// Insert a new topic (if the topicID was left empty.
	if ($new_topic)
	{
		db_query("
			INSERT INTO {$db_prefix}topics
				(ID_BOARD, ID_MEMBER_STARTED, ID_MEMBER_UPDATED, ID_FIRST_MSG, ID_LAST_MSG, locked, isSticky, numViews, ID_POLL, unapprovedPosts, approved)
			VALUES ($topicOptions[board], $posterOptions[id], $posterOptions[id], $msgOptions[id], $msgOptions[id],
				" . ($topicOptions['lock_mode'] === null ? '0' : $topicOptions['lock_mode']) . ', ' .
				($topicOptions['sticky_mode'] === null ? '0' : $topicOptions['sticky_mode']) . ", 0,
				" . ($topicOptions['poll'] === null ? '0' : $topicOptions['poll']) . ', ' . ($msgOptions['approved'] ? 0 : 1) . ", $msgOptions[approved])", __FILE__, __LINE__);
		$topicOptions['id'] = db_insert_id();

		// The topic couldn't be created for some reason.
		if (empty($topicOptions['id']))
		{
			// We should delete the post that did work, though...
			db_query("
				DELETE FROM {$db_prefix}messages
				WHERE ID_MSG = $msgOptions[id]
				LIMIT 1", __FILE__, __LINE__);

			return false;
		}

		// Fix the message with the topic.
		db_query("
			UPDATE {$db_prefix}messages
			SET ID_TOPIC = $topicOptions[id]
			WHERE ID_MSG = $msgOptions[id]
			LIMIT 1", __FILE__, __LINE__);

		// There's been a new topic AND a new post today.
		trackStats(array('topics' => '+', 'posts' => '+'));

		updateStats('topic', true);
		updateStats('subject', $topicOptions['id'], $msgOptions['subject']);
	}
	// The topic already exists, it only needs a little updating.
	else
	{
		$countChange = $msgOptions['approved'] ? 'numReplies = numReplies + 1' : 'unapprovedPosts = unapprovedPosts + 1';

		// Update the number of replies and the lock/sticky status.
		db_query("
			UPDATE {$db_prefix}topics
			SET
				" . ($msgOptions['approved'] ? "ID_MEMBER_UPDATED = $posterOptions[id], ID_LAST_MSG = $msgOptions[id]," : '') . "
				$countChange" . ($topicOptions['lock_mode'] === null ? '' : ",
				locked = $topicOptions[lock_mode]") . ($topicOptions['sticky_mode'] === null ? '' : ",
				isSticky = $topicOptions[sticky_mode]") . "
			WHERE ID_TOPIC = $topicOptions[id]
			LIMIT 1", __FILE__, __LINE__);

		// One new post has been added today.
		trackStats(array('posts' => '+'));
	}

	// Creating is modifying...in a way.
	//!!! Why not set ID_MSG_MODIFIED on the insert?
	db_query("
		UPDATE {$db_prefix}messages
		SET ID_MSG_MODIFIED = $msgOptions[id]
		WHERE ID_MSG = $msgOptions[id]", __FILE__, __LINE__);

	// Increase the number of posts and topics on the board.
	if ($msgOptions['approved'])
		db_query("
			UPDATE {$db_prefix}boards
			SET numPosts = numPosts + 1" . ($new_topic ? ', numTopics = numTopics + 1' : '') . "
			WHERE ID_BOARD = $topicOptions[board]
			LIMIT 1", __FILE__, __LINE__);
	else
	{
		db_query("
			UPDATE {$db_prefix}boards
			SET unapprovedPosts = unapprovedPosts + 1" . ($new_topic ? ', unapprovedTopics = unapprovedTopics + 1' : '') . "
			WHERE ID_BOARD = $topicOptions[board]
			LIMIT 1", __FILE__, __LINE__);

		// Add to the approval queue too.
		db_query("
			INSERT IGNORE INTO {$db_prefix}approval_queue
				(ID_MSG)
			VALUES
				($msgOptions[id])", __FILE__, __LINE__);
	}

	// Mark inserted topic as read (only for the user calling this function).
	if (!empty($topicOptions['mark_as_read']) && !$user_info['is_guest'])
	{
		// Since it's likely they *read* it before replying, let's try an UPDATE first.
		if (!$new_topic)
		{
			db_query("
				UPDATE {$db_prefix}log_topics
				SET ID_MSG = $msgOptions[id] + 1
				WHERE ID_MEMBER = $ID_MEMBER
					AND ID_TOPIC = $topicOptions[id]
				LIMIT 1", __FILE__, __LINE__);

			$flag = db_affected_rows() != 0;
		}

		if (empty($flag))
			db_query("
				REPLACE INTO {$db_prefix}log_topics
					(ID_TOPIC, ID_MEMBER, ID_MSG)
				VALUES ($topicOptions[id], $ID_MEMBER, $msgOptions[id] + 1)", __FILE__, __LINE__);
	}

	// If there's a custom search index, it needs updating...
	if (!empty($modSettings['search_custom_index_config']))
	{
		//$index_settings = unserialize($modSettings['search_custom_index_config']);

		$inserts = '';
		foreach (text2words(stripslashes($msgOptions['body']), 4, true) as $word)
			$inserts .= "($word, $msgOptions[id]),\n";

		if (!empty($inserts))
			db_query("
				INSERT IGNORE INTO {$db_prefix}log_search_words
					(ID_WORD, ID_MSG)
				VALUES
					" . substr($inserts, 0, -2), __FILE__, __LINE__);
	}

	// Increase the post counter for the user that created the post.
	if (!empty($posterOptions['update_post_count']) && !empty($posterOptions['id']))
	{
		// Are you the one that happened to create this post?
		if ($ID_MEMBER == $posterOptions['id'])
			$user_info['posts']++;
		updateMemberData($posterOptions['id'], array('posts' => '+'));
	}

	// They've posted, so they can make the view count go up one if they really want. (this is to keep views >= replies...)
	$_SESSION['last_read_topic'] = 0;

	// Better safe than sorry.
	if (isset($_SESSION['topicseen_cache'][$topicOptions['board']]))
		$_SESSION['topicseen_cache'][$topicOptions['board']]--;

	// Update all the stats so everyone knows about this new topic and message.
	updateStats('message', true, $msgOptions['id']);
	if ($msgOptions['approved'])
		updateLastMessages($topicOptions['board'], $msgOptions['id']);

	// Alright, done now... we can abort now, I guess... at least this much is done.
	ignore_user_abort($previous_ignore_user_abort);

	// Success.
	return true;
}

// !!!
function createAttachment(&$attachmentOptions)
{
	global $db_prefix, $modSettings, $sourcedir;

	$attachmentOptions['errors'] = array();
	if (!isset($attachmentOptions['post']))
		$attachmentOptions['post'] = 0;
	if (!isset($attachmentOptions['approved']))
		$attachmentOptions['approved'] = 1;

	$already_uploaded = preg_match('~^post_tmp_' . $attachmentOptions['poster'] . '_\d+$~', $attachmentOptions['tmp_name']) != 0;
	$file_restricted = @ini_get('open_basedir') != '' && !$already_uploaded;

	if ($already_uploaded)
		$attachmentOptions['tmp_name'] = $modSettings['attachmentUploadDir'] . '/' . $attachmentOptions['tmp_name'];

	// Make sure the file actually exists... sometimes it doesn't.
	if ((!$file_restricted && !file_exists($attachmentOptions['tmp_name'])) || (!$already_uploaded && !is_uploaded_file($attachmentOptions['tmp_name'])))
	{
		$attachmentOptions['errors'] = array('could_not_upload');
		return false;
	}

	if (!$file_restricted || $already_uploaded)
		list ($attachmentOptions['width'], $attachmentOptions['height']) = @getimagesize($attachmentOptions['tmp_name']);

	// Remove special foreign characters from the filename.
	if (empty($modSettings['attachmentEncryptFilenames']))
		$attachmentOptions['name'] = getAttachmentFilename($attachmentOptions['name'], false, true);

	// Is the file too big?
	if (!empty($modSettings['attachmentSizeLimit']) && $attachmentOptions['size'] > $modSettings['attachmentSizeLimit'] * 1024)
		$attachmentOptions['errors'][] = 'too_large';

	if (!empty($modSettings['attachmentCheckExtensions']))
	{
		$allowed = explode(',', strtolower($modSettings['attachmentExtensions']));
		foreach ($allowed as $k => $dummy)
			$allowed[$k] = trim($dummy);

		if (!in_array(strtolower(substr(strrchr($attachmentOptions['name'], '.'), 1)), $allowed))
			$attachmentOptions['errors'][] = 'bad_extension';
	}

	if (!empty($modSettings['attachmentDirSizeLimit']))
	{
		// Make sure the directory isn't full.
		$dirSize = 0;
		$dir = @opendir($modSettings['attachmentUploadDir']) or fatal_lang_error('smf115b', 'critical');
		while ($file = readdir($dir))
		{
			if (substr($file, 0, -1) == '.')
				continue;

			if (preg_match('~^post_tmp_\d+_\d+$~', $file) != 0)
			{
				// Temp file is more than 5 hours old!
				if (filemtime($modSettings['attachmentUploadDir'] . '/' . $file) < time() - 18000)
					@unlink($modSettings['attachmentUploadDir'] . '/' . $file);
				continue;
			}

			$dirSize += filesize($modSettings['attachmentUploadDir'] . '/' . $file);
		}
		closedir($dir);

		// Too big!  Maybe you could zip it or something...
		if ($attachmentOptions['size'] + $dirSize > $modSettings['attachmentDirSizeLimit'] * 1024)
			$attachmentOptions['errors'][] = 'directory_full';
	}

	// Check if the file already exists.... (for those who do not encrypt their filenames...)
	if (empty($modSettings['attachmentEncryptFilenames']))
	{
		// Make sure they aren't trying to upload a nasty file.
		$disabledFiles = array('con', 'com1', 'com2', 'com3', 'com4', 'prn', 'aux', 'lpt1', '.htaccess', 'index.php');
		if (in_array(strtolower(basename($attachmentOptions['name'])), $disabledFiles))
			$attachmentOptions['errors'][] = 'bad_filename';

		// Check if there's another file with that name...
		$request = db_query("
			SELECT ID_ATTACH
			FROM {$db_prefix}attachments
			WHERE filename = '" . strtolower($attachmentOptions['name']) . "'
			LIMIT 1", __FILE__, __LINE__);
		if (mysql_num_rows($request) > 0)
			$attachmentOptions['errors'][] = 'taken_filename';
		mysql_free_result($request);
	}

	if (!empty($attachmentOptions['errors']))
		return false;

	if (!is_writable($modSettings['attachmentUploadDir']))
		fatal_lang_error('attachments_no_write', 'critical');

	db_query("
		INSERT INTO {$db_prefix}attachments
			(ID_MSG, filename, size, width, height, approved)
		VALUES (" . (int) $attachmentOptions['post'] . ", SUBSTRING('" . $attachmentOptions['name'] . "', 1, 255), " . (int) $attachmentOptions['size'] . ', ' . (empty($attachmentOptions['width']) ? '0' : (int) $attachmentOptions['width']) . ', ' . (empty($attachmentOptions['height']) ? '0' : (int) $attachmentOptions['height']) . ', ' . (int) $attachmentOptions['approved'] . ')', __FILE__, __LINE__);
	$attachmentOptions['id'] = db_insert_id();

	if (empty($attachmentOptions['id']))
		return false;

	// If it's not approved add to the approval queue.
	if (!$attachmentOptions['approved'])
		db_query("
			INSERT IGNORE INTO {$db_prefix}approval_queue
				(ID_ATTACH, ID_MSG)
			VALUES
				($attachmentOptions[id], " . (int) $attachmentOptions['post'] . ")", __FILE__, __LINE__);

	$attachmentOptions['destination'] = $modSettings['attachmentUploadDir'] . '/' . getAttachmentFilename(basename($attachmentOptions['name']), $attachmentOptions['id'], true);

	if ($already_uploaded)
		rename($attachmentOptions['tmp_name'], $attachmentOptions['destination']);
	elseif (!move_uploaded_file($attachmentOptions['tmp_name'], $attachmentOptions['destination']))
		fatal_lang_error('smf124', 'critical');
	// We couldn't access the file before...
	elseif ($file_restricted)
	{
		list ($attachmentOptions['width'], $attachmentOptions['height']) = @getimagesize($attachmentOptions['destination']);

		if (!empty($attachmentOptions['width']) && !empty($attachmentOptions['height']))
			db_query("
				UPDATE {$db_prefix}attachments
				SET
					width = " . (int) $attachmentOptions['width'] . ",
					height = " . (int) $attachmentOptions['height'] . "
				WHERE ID_ATTACH = $attachmentOptions[id]
				LIMIT 1", __FILE__, __LINE__);
	}

	// Attempt to chmod it.
	@chmod($attachmentOptions['destination'], 0644);

	if (!empty($attachmentOptions['skip_thumbnail']) || (empty($attachmentOptions['width']) && empty($attachmentOptions['height'])))
		return true;

	// Like thumbnails, do we?
	if (!empty($modSettings['attachmentThumbnails']) && !empty($modSettings['attachmentThumbWidth']) && !empty($modSettings['attachmentThumbHeight']) && ($attachmentOptions['width'] > $modSettings['attachmentThumbWidth'] || $attachmentOptions['height'] > $modSettings['attachmentThumbHeight']))
	{
		require_once($sourcedir . '/Subs-Graphics.php');
		if (createThumbnail($attachmentOptions['destination'], $modSettings['attachmentThumbWidth'], $modSettings['attachmentThumbHeight']))
		{
			// Figure out how big we actually made it.
			list ($thumb_width, $thumb_height) = @getimagesize($attachmentOptions['destination'] . '_thumb');
			$thumb_filename = addslashes($attachmentOptions['name'] . '_thumb');
			$thumb_size = filesize($attachmentOptions['destination'] . '_thumb');

			// To the database we go!
			db_query("
				INSERT INTO {$db_prefix}attachments
					(ID_MSG, attachmentType, filename, size, width, height, approved)
				VALUES (" . (int) $attachmentOptions['post'] . ", 3, SUBSTRING('$thumb_filename', 1, 255), " . (int) $thumb_size . ", " . (int) $thumb_width . ", " . (int) $thumb_height . ', ' . (int) $attachmentOptions['approved'] . ')', __FILE__, __LINE__);
			$attachmentOptions['thumb'] = db_insert_id();

			if (!empty($attachmentOptions['thumb']))
			{
				db_query("
					UPDATE {$db_prefix}attachments
					SET ID_THUMB = $attachmentOptions[thumb]
					WHERE ID_ATTACH = $attachmentOptions[id]
					LIMIT 1", __FILE__, __LINE__);

				rename($attachmentOptions['destination'] . '_thumb', $modSettings['attachmentUploadDir'] . '/' . getAttachmentFilename($thumb_filename, $attachmentOptions['thumb'], true));
			}
		}
	}

	return true;
}

// !!!
function modifyPost(&$msgOptions, &$topicOptions, &$posterOptions)
{
	global $db_prefix, $user_info, $ID_MEMBER, $modSettings;

	$topicOptions['poll'] = isset($topicOptions['poll']) ? (int) $topicOptions['poll'] : null;
	$topicOptions['lock_mode'] = isset($topicOptions['lock_mode']) ? $topicOptions['lock_mode'] : null;
	$topicOptions['sticky_mode'] = isset($topicOptions['sticky_mode']) ? $topicOptions['sticky_mode'] : null;

	// This is longer than it has to be, but makes it so we only set/change what we have to.
	$messages_columns = array();
	if (isset($posterOptions['name']))
		$messages_columns[] = "posterName = '$posterOptions[name]'";
	if (isset($posterOptions['email']))
		$messages_columns[] = "posterEmail = '$posterOptions[email]'";
	if (isset($msgOptions['icon']))
		$messages_columns[] = "icon = '$msgOptions[icon]'";
	if (isset($msgOptions['subject']))
		$messages_columns[] = "subject = '$msgOptions[subject]'";
	if (isset($msgOptions['body']))
	{
		$messages_columns[] = "body = '$msgOptions[body]'";
		
		if (!empty($modSettings['search_custom_index_config']))
		{
			$request = db_query("
				SELECT body
				FROM {$db_prefix}messages
				WHERE ID_MSG = $msgOptions[id]", __FILE__, __LINE__);
			list ($old_body) = mysql_fetch_row($request);
			mysql_free_result($request);
		}
	}
	if (!empty($msgOptions['modify_time']))
	{
		$messages_columns[] = "modifiedTime = $msgOptions[modify_time]";
		$messages_columns[] = "modifiedName = '$msgOptions[modify_name]'";
		$messages_columns[] = "ID_MSG_MODIFIED = $modSettings[maxMsgID]";
	}
	if (isset($msgOptions['smileys_enabled']))
		$messages_columns[] = "smileysEnabled = " . (empty($msgOptions['smileys_enabled']) ? '0' : '1');

	// Change the post.
	db_query("
		UPDATE {$db_prefix}messages
		SET " . implode(', ', $messages_columns) . "
		WHERE ID_MSG = $msgOptions[id]
		LIMIT 1", __FILE__, __LINE__);

	// Lock and or sticky the post.
	if ($topicOptions['sticky_mode'] !== null || $topicOptions['lock_mode'] !== null || $topicOptions['poll'] !== null)
	{
		db_query("
			UPDATE {$db_prefix}topics
			SET
				isSticky = " . ($topicOptions['sticky_mode'] === null ? 'isSticky' : $topicOptions['sticky_mode']) . ",
				locked = " . ($topicOptions['lock_mode'] === null ? 'locked' : $topicOptions['lock_mode']) . ",
				ID_POLL = " . ($topicOptions['poll'] === null ? 'ID_POLL' : $topicOptions['poll']) . "
			WHERE ID_TOPIC = $topicOptions[id]
			LIMIT 1", __FILE__, __LINE__);
	}

	// Mark inserted topic as read.
	if (!empty($topicOptions['mark_as_read']) && !$user_info['is_guest'])
		db_query("
			REPLACE INTO {$db_prefix}log_topics
				(ID_TOPIC, ID_MEMBER, ID_MSG)
			VALUES ($topicOptions[id], $ID_MEMBER, $modSettings[maxMsgID])", __FILE__, __LINE__);

	// If there's a custom search index, it needs to be modified...
	if (isset($msgOptions['body']) && !empty($modSettings['search_custom_index_config']))
	{
		$stopwords = empty($modSettings['search_stopwords']) ?  array() : explode(',', addslashes($modSettings['search_stopwords']));
		$old_index = text2words($old_body, 4, true);
		$new_index = text2words(stripslashes($msgOptions['body']), 4, true);

		// Calculate the words to remove from the index.
		$removed_words = array_diff(array_diff($old_index, $new_index), $stopwords);
		if (!empty($removed_words))
			db_query("
				DELETE FROM {$db_prefix}log_search_words
				WHERE ID_MSG = $msgOptions[id]
					AND ID_WORD IN (" . implode(", ", $removed_words) . ")
				LIMIT " . count($removed_words), __FILE__, __LINE__);

		// Calculate the new words to be indexed.
		$inserted_words = array_diff(array_diff($new_index, $old_index), $stopwords);
		if (!empty($inserted_words))
			db_query("
				INSERT IGNORE INTO {$db_prefix}log_search_words
					(ID_WORD, ID_MSG)
				VALUES
					('" . implode("', $msgOptions[id]),
					('", $inserted_words) . "', $msgOptions[id])", __FILE__, __LINE__);
	}

	if (isset($msgOptions['subject']))
	{
		// Only update the subject if this was the first message in the topic.
		$request = db_query("
			SELECT ID_TOPIC
			FROM {$db_prefix}topics
			WHERE ID_FIRST_MSG = $msgOptions[id]
			LIMIT 1", __FILE__, __LINE__);
		if (mysql_num_rows($request) == 1)
			updateStats('subject', $topicOptions['id'], $msgOptions['subject']);
		mysql_free_result($request);
	}

	// Finally, if we are setting the approved state we need to do much more work :(
	if (isset($msgOptions['approved']))
		approvePosts($msgOptions['id'], $msgOptions['approved']);

	return true;
}

// Approve (or not) some posts... without permission checks...
function approvePosts($msgs, $approve = true)
{
	global $db_prefix, $sourcedir;

	if (!is_array($msgs))
		$msgs = array($msgs);

	if (empty($msgs))
		return false;

	// May as well start at the beginning, working out *what* we need to change.
	$request = db_query("
		SELECT m.ID_MSG, m.approved, m.ID_TOPIC, m.ID_BOARD, t.ID_FIRST_MSG, t.ID_LAST_MSG,
			m.body, m.subject, IFNULL(mem.realName, m.posterName) AS posterName, m.ID_MEMBER,
			t.approved AS topic_approved
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE m.ID_MSG IN (" . implode(',', $msgs) . ")
			AND m.approved = " . ($approve ? 0 : 1) . "
			AND t.ID_TOPIC = m.ID_TOPIC", __FILE__, __LINE__);
	$msgs = array();
	$topics = array();
	$topic_changes = array();
	$board_changes = array();
	$notification_topics = array();
	$notification_posts = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Easy...
		$msgs[] = $row['ID_MSG'];
		$topics[] = $row['ID_TOPIC'];

		// Ensure our change array exists already.
		if (!isset($topic_changes[$row['ID_TOPIC']]))
			$topic_changes[$row['ID_TOPIC']] = array(
				'ID_LAST_MSG' => $row['ID_LAST_MSG'],
				'approved' => $row['topic_approved'],
				'replies' => 0,
				'unapproved_posts' => 0,
			);
		if (!isset($board_changes[$row['ID_BOARD']]))
			$board_changes[$row['ID_BOARD']] = array(
				'posts' => 0,
				'topics' => 0,
				'unapproved_posts' => 0,
				'unapproved_topics' => 0,
			);

		// If it's the first message then the topic state changes!
		if ($row['ID_MSG'] == $row['ID_FIRST_MSG'])
		{
			$topic_changes[$row['ID_TOPIC']]['approved'] = $approve ? 1 : 0;

			$board_changes[$row['ID_BOARD']]['unapproved_topics'] += $approve ? -1 : 1;
			$board_changes[$row['ID_BOARD']]['topics'] += $approve ? 1 : -1;

			// Note we need to ensure we annouce this topic!
			$notification_topics[] = array(
				'body' => $row['body'],
				'subject' => $row['subject'],
				'name' => $row['posterName'],
				'board' => $row['ID_BOARD'],
				'topic' => $row['ID_TOPIC'],
				'msg' => $row['ID_FIRST_MSG'],
				'poster' => $row['ID_MEMBER'],
			);
		}
		else
		{
			$topic_changes[$row['ID_TOPIC']]['replies'] += $approve ? 1 : -1;

			// This will be a post... but don't notify unless it's not followed by approved ones.
			if ($row['ID_MSG'] > $row['ID_LAST_MSG'])
				$notification_posts[$row['ID_TOPIC']][] = array(
					'id' => $row['ID_MSG'],
					'body' => $row['message'],
					'subject' => $row['subject'],
					'name' => $row['posterName'],
					'topic' => $row['ID_TOPIC'],
				);
		}

		// If this is being approved and ID_MSG is higher than the current ID_LAST_MSG then it changes.
		if ($approve && $row['ID_MSG'] > $topic_changes[$row['ID_TOPIC']]['ID_LAST_MSG'])
			$topic_changes[$row['ID_TOPIC']]['ID_LAST_MSG'] = $row['ID_MSG'];
		// If this is being unapproved, and it's equal to the ID_LAST_MSG we need to find a new one!
		elseif (!$approve)
			// Default to the first message and then we'll override in a bit ;)
			$topic_changes[$row['ID_TOPIC']]['ID_LAST_MSG'] = $row['ID_FIRST_MSG'];

		$topic_changes[$row['ID_TOPIC']]['unapproved_posts'] += $approve ? -1 : 1;
		$board_changes[$row['ID_BOARD']]['unapproved_posts'] += $approve ? -1 : 1;
		$board_changes[$row['ID_BOARD']]['posts'] += $approve ? 1 : -1;
	}
	mysql_free_result($request);

	if (empty($msgs))
		return;

	// Now we have the differences make the changes, first the easy one.
	db_query("
		UPDATE {$db_prefix}messages
		SET approved = " . ($approve ? 1 : 0) . "
		WHERE ID_MSG IN (" . implode(',', $msgs) . ")", __FILE__, __LINE__);

	// If we were unapproving find the last msg in the topics...
	if (!$approve)
	{
		$request = db_query("
			SELECT ID_TOPIC, MAX(ID_MSG) AS ID_LAST_MSG
			FROM {$db_prefix}messages
			WHERE ID_TOPIC IN (" . implode(',', $topics) . ")
				AND approved = 1
			GROUP BY ID_TOPIC", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$topic_changes[$row['ID_TOPIC']]['ID_LAST_MSG'] = $row['ID_LAST_MSG'];
		mysql_free_result($request);
	}

	// ... next the topics...
	foreach ($topic_changes as $id => $changes)
		db_query("
			UPDATE {$db_prefix}topics
			SET approved = $changes[approved], unapprovedPosts = unapprovedPosts + $changes[unapproved_posts],
				numReplies = numReplies + $changes[replies], ID_LAST_MSG = $changes[ID_LAST_MSG]
			WHERE ID_TOPIC = $id", __FILE__, __LINE__);

	// ... finally the boards...
	foreach ($board_changes as $id => $changes)
		db_query("
			UPDATE {$db_prefix}boards
			SET numPosts = numPosts + $changes[posts], unapprovedPosts = unapprovedPosts + $changes[unapproved_posts],
				numTopics = numTopics + $changes[topics], unapprovedTopics = unapprovedTopics + $changes[unapproved_topics]
			WHERE ID_BOARD = $id", __FILE__, __LINE__);

	// Finally, least importantly, notifications!
	if ($approve)
	{
		if (!empty($notification_topics))
		{
			require_once($sourcedir . '/Post.php');
			notifyMembersBoard($notification_topics);
		}
		if (!empty($notification_posts))
			sendApprovalNotifications($notification_posts);

		db_query("
			DELETE FROM {$db_prefix}approval_queue
			WHERE ID_MSG IN (" . implode(',', $msgs) . ")
				AND ID_ATTACH = 0", __FILE__, __LINE__);
	}
	// If unapproving add to the approval queue!
	else
	{
		db_query("
			INSERT IGNORE INTO {$db_prefix}approval_queue
				(ID_MSG)
			VALUES
				(" . implode('), (', $msgs) . ")", __FILE__, __LINE__);
	}

	// Update the last messages on the boards...
	updateLastMessages(array_keys($board_changes));

	return true;
}

// Approve topics?
function approveTopics($topics, $approve = true)
{
	global $db_prefix;

	if (!is_array($topics))
		$topics = array($topics);

	if (empty($topics))
		return false;

	$approve_type = $approve ? 0 : 1;

	// Just get the messages to be approved and pass through...
	$request = db_query("
		SELECT ID_MSG
		FROM {$db_prefix}messages
		WHERE ID_TOPIC IN (" . implode(',', $topics) . ")
			AND approved = $approve_type", __FILE__, __LINE__);
	$msgs = array();
	while ($row = mysql_fetch_assoc($request))
		$msgs[] = $row['ID_MSG'];
	mysql_free_result($request);

	return approvePosts($msgs, $approve);
}

// A special function for handling the hell which is sending approval notifications.
function sendApprovalNotifications(&$topicData)
{
	global $txt, $scripturl, $db_prefix, $language, $user_info;
	global $modSettings, $sourcedir;

	// Clean up the data...
	if (!is_array($topicData) || empty($topicData))
		return;

	$topics = array();
	$digest_insert = array();
	foreach ($topicData as $topic => $msgs)
		foreach ($msgs as $msgKey => $msg)
	{
		censorText($topicData[$topic][$msgKey]['subject']);
		censorText($topicData[$topic][$msgKey]['body']);
		$topicData[$topic][$msgKey]['subject'] = un_htmlspecialchars($topicData[$topic][$msgKey]['subject']);
		$topicData[$topic][$msgKey]['body'] = trim(un_htmlspecialchars(strip_tags(strtr(parse_bbc($topicData[$topic][$msgKey]['body'], false), array('<br />' => "\n", '</div>' => "\n", '</li>' => "\n", '&#91;' => '[', '&#93;' => ']')))));

		$topics[] = $msg['id'];
		$digest_insert[] = "($msg[topic], $msg[id], 'reply', $user_info[id])";
	}

	// These need to go into the digest too...
	db_query("
		INSERT INTO {$db_prefix}log_digest
			(ID_TOPIC, ID_MSG, note_type, exclude)
		VALUES
			" . implode(', ', $digest_insert), __FILE__, __LINE__);

	// Find everyone who needs to know about this.
	$members = db_query("
		SELECT
			mem.ID_MEMBER, mem.emailAddress, mem.notifyRegularity, mem.notifyTypes, mem.notifySendBody, mem.lngfile,
			ln.sent, mem.ID_GROUP, mem.additionalGroups, b.memberGroups, mem.ID_POST_GROUP, t.ID_MEMBER_STARTED,
			ln.ID_TOPIC
		FROM ({$db_prefix}log_notify AS ln, {$db_prefix}members AS mem, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
		WHERE ln.ID_TOPIC IN (" . implode(',', $topics) . ")
			AND t.ID_TOPIC = ln.ID_TOPIC
			AND b.ID_BOARD = t.ID_BOARD
			AND mem.ID_MEMBER != $user_info[id]
			AND mem.is_activated = 1
			AND mem.notifyTypes < 4
			AND mem.notifyRegularity < 2
			AND ln.ID_MEMBER = mem.ID_MEMBER
		GROUP BY mem.ID_MEMBER, ln.ID_TOPIC
		ORDER BY mem.lngfile", __FILE__, __LINE__);
	$sent = 0;
	while ($row = mysql_fetch_assoc($members))
	{
		if ($row['ID_GROUP'] != 1)
		{
			$allowed = explode(',', $row['memberGroups']);
			$row['additionalGroups'] = explode(',', $row['additionalGroups']);
			$row['additionalGroups'][] = $row['ID_GROUP'];
			$row['additionalGroups'][] = $row['ID_POST_GROUP'];

			if (count(array_intersect($allowed, $row['additionalGroups'])) == 0)
				continue;
		}

		$needed_language = empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile'];
		if (empty($current_language) || $current_language != $needed_language)
			$current_language = loadLanguage('Post', $needed_language, false);

		$sent_this_time = false;
		// Now loop through all the messages to send.
		foreach ($topicData[$row['ID_TOPIC']] as $msg)
		{
			$message = sprintf($txt['notification_reply'], un_htmlspecialchars($msg['name']));
			$message .=
				$scripturl . '?topic=' . $row['ID_TOPIC'] . '.new;topicseen#new' . "\n\n" .
				$txt['notifyUnsubscribe'] . ': ' . $scripturl . '?action=notify;topic=' . $row['ID_TOPIC'] . '.0';

			// Do they want the body of the message sent too?
			if (!empty($row['notifySendBody']) && empty($modSettings['disallow_sendBody']))
				$message .= "\n\n" . $txt['notification_reply_body'] . "\n\n" . $msg['body'];
			if (!empty($row['notifyRegularity']))
				$message .= "\n\n" . $txt['notifyXOnce2'];
	
			// Send only if once is off or it's on and it hasn't been sent.
			if (empty($row['notifyRegularity']) || (empty($row['sent']) && !$sent_this_time))
			{
				sendmail($row['emailAddress'], sprintf($txt['notification_reply_subject'], $msg['subject']),
					$message . "\n\n" .
					$txt[130], null, 'm' . $msg['id']);
				$sent++;
			}
			$sent_this_time = true;
		}
	}
	mysql_free_result($members);

	if (isset($current_language) && $current_language != $user_info['language'])
		loadLanguage('Post');

	// Sent!
	if (!empty($sent))
		db_query("
			UPDATE {$db_prefix}log_notify
			SET sent = 1
			WHERE ID_TOPIC IN (" . implode(',', $topics) . ")
				AND ID_MEMBER != $user_info[id]", __FILE__, __LINE__);
}

// Update the last message in a board, and its parents.
function updateLastMessages($setboards, $ID_MSG = 0)
{
	global $db_prefix, $board_info, $board, $modSettings;

	if (!is_array($setboards))
		$setboards = array($setboards);

	// If we don't know the ID_MSG we need to find it.
	if (!$ID_MSG)
	{
		// Find the latest message on this board (highest ID_MSG.)
		$request = db_query("
			SELECT ID_BOARD, MAX(ID_LAST_MSG) AS ID_MSG
			FROM {$db_prefix}topics
			WHERE ID_BOARD IN (" . implode(', ', $setboards) . ")
				AND approved = 1
			GROUP BY ID_BOARD", __FILE__, __LINE__);
		$lastMsg = array();
		while ($row = mysql_fetch_assoc($request))
			$lastMsg[$row['ID_BOARD']] = $row['ID_MSG'];
		mysql_free_result($request);
	}
	else
	{
		foreach ($setboards as $ID_BOARD)
			$lastMsg[$ID_BOARD] = $ID_MSG;
	}

	$parent_boards = array();
	// Get all the child boards for the parents, if they have some...
	foreach ($setboards as $ID_BOARD)
	{
		if (!isset($lastMsg[$ID_BOARD]))
			$lastMsg[$ID_BOARD] = 0;

		if (!empty($board) && $ID_BOARD == $board)
			$parents = $board_info['parent_boards'];
		else
			$parents = getBoardParents($ID_BOARD);

		// Ignore any parents on the top child level.
		//!!! Why?
		foreach ($parents as $id => $parent)
		{
			if ($parent['level'] != 0)
			{
				// If we're already doing this one as a board, is this a higher last modified?
				if (isset($lastMsg[$id]) && $lastMsg[$ID_BOARD] > $lastMsg[$id])
					$lastMsg[$id] = $lastMsg[$ID_BOARD];
				elseif (!isset($lastMsg[$id]) && (!isset($parent_boards[$id]) || $parent_boards[$id] < $lastMsg[$ID_BOARD]))
					$parent_boards[$id] = $lastMsg[$ID_BOARD];
			}
		}
	}

	// Note to help understand what is happening here. For parents we update the timestamp of the last message for determining
	// whether there are child boards which have not been read. For the boards themselves we update both this and ID_LAST_MSG.

	$board_updates = array();
	$parent_updates = array();
	// Finally, to save on queries make the changes...
	foreach ($parent_boards as $id => $msg)
	{
		if (!isset($parent_updates[$msg]))
			$parent_updates[$msg] = array($id);
		else
			$parent_updates[$msg][] = $id;
	}

	foreach ($lastMsg as $id => $msg)
	{
		if (!isset($board_updates[$msg]))
			$board_updates[$msg] = array($id);
		else
			$board_updates[$msg][] = $id;
	}

	// Now commit the changes!
	foreach ($parent_updates as $ID_MSG => $boards)
	{
		db_query("
			UPDATE {$db_prefix}boards
			SET ID_MSG_UPDATED = $ID_MSG
			WHERE ID_BOARD IN (" . implode(',', $boards) . ")
				AND ID_MSG_UPDATED < $ID_MSG
			LIMIT " . count($boards), __FILE__, __LINE__);
	}
	foreach ($board_updates as $ID_MSG => $boards)
	{
		db_query("
			UPDATE {$db_prefix}boards
			SET ID_LAST_MSG = $ID_MSG, ID_MSG_UPDATED = $ID_MSG
			WHERE ID_BOARD IN (" . implode(',', $boards) . ")
			LIMIT " . count($boards), __FILE__, __LINE__);
	}
}

// This simple function gets a list of all administrators and sends them an email to let them know a new member has joined.
function adminNotify($type, $memberID, $memberName = null)
{
	global $txt, $db_prefix, $modSettings, $language, $scripturl, $user_info;

	// If the setting isn't enabled then just exit.
	if (empty($modSettings['notify_new_registration']))
		return;

	if ($memberName == null)
	{
		// Get the new user's name....
		$request = db_query("
			SELECT realName
			FROM {$db_prefix}members
			WHERE ID_MEMBER = $memberID
			LIMIT 1", __FILE__, __LINE__);
		list ($memberName) = mysql_fetch_row($request);
		mysql_free_result($request);
	}

	$toNotify = array();
	$groups = array();

	// All membergroups who can approve members.
	$request = db_query("
		SELECT ID_GROUP
		FROM {$db_prefix}permissions
		WHERE permission = 'moderate_forum'
			AND addDeny = 1
			AND ID_GROUP != 0", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$groups[] = $row['ID_GROUP'];
	mysql_free_result($request);

	// Add administrators too...
	$groups[] = 1;
	$groups = array_unique($groups);

	// Get a list of all members who have ability to approve accounts - these are the people who we inform.
	$request = db_query("
		SELECT ID_MEMBER, lngfile, emailAddress
		FROM {$db_prefix}members
		WHERE (ID_GROUP IN (" . implode(', ', $groups) . ") OR FIND_IN_SET(" . implode(', additionalGroups) OR FIND_IN_SET(', $groups) . ", additionalGroups))
			AND notifyTypes != 4
		ORDER BY lngfile", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		// Post it in this members language.
		$needed_language = empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile'];
		if (empty($current_language) || $current_language != $needed_language)
			$current_language = loadLanguage('Login', $needed_language, false);

		// Construct the message based on what they are being told.
		$message = sprintf($txt['admin_notify_profile'], $memberName) . "\n\n" .
			"$scripturl?action=profile;u=$memberID\n\n";

		// If they need to be approved add more info...
		if ($type == 'approval')
			$message .= $txt['admin_notify_approval'] . "\n\n" .
				"$scripturl?action=admin;area=viewmembers;sa=browse;type=approve\n\n";

		// And do the actual sending...
		sendmail($row['emailAddress'], $txt['admin_notify_subject'], $message . $txt[130], null, null, false, 0);
	}
	mysql_free_result($request);

	if (isset($current_language) && $current_language != $user_info['language'])
		loadLanguage('Login');
}

?>