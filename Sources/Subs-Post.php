<?php
/**********************************************************************************
* Subs-Post.php                                                                   *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 1.1                                    *
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
			bool store_outbox = false, array from = current_member, int pm_head = 0)
		- sends an personal message from the specified person to the
		  specified people. (from defaults to the user.)
		- recipients should be an array containing the arrays 'to' and 'bcc',
		  both containing ID_MEMBERs.
		- subject and message should have no slashes and no html entities.
		- pm_head is the ID of the chain being replied to - if any.
		- from is an array, with the id, name, and username of the member.
		- returns an array with log entries telling how many recipients were
		  successful and which recipients it failed to send to.

	string mimespecialchars(string text, bool with_charset = true,
			hotmail_fix = false, string custom_charset = null)
		- prepare text strings for sending as email.
		- in case there are higher ASCII characters in the given string, this
		  function will attempt the transport method 'quoted-printable'.
		  Otherwise the transport method '7bit' is used.
		- with hotmail_fix set all higher ASCII characters are converted to
		  HTML entities to assure proper display of the mail.
		- uses character set custom_charset if set.
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

	void updateLastMessages(array ID_BOARDs, int id_msg)
		- takes an array of board IDs and updates their last messages.
		- if the board has a parent, that parent board is also automatically
		  updated.
		- columns updated are id_last_msg and lastUpdated.
		- note that id_last_msg should always be updated using this function,
		  and is not automatically updated upon other changes.

	void adminNotify(string type, int memberID, string member_name = null)
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

	// The regular expression non breaking space has many versions.
	$non_breaking_space = $context['utf8'] ? ($context['server']['complex_preg_chars'] ? '\x{A0}' : pack('C*', 0xC2, 0xA0)) : '\xA0';

	// Only mess with stuff outside [code] tags.
	for ($i = 0, $n = count($parts); $i < $n; $i++)
	{
		// It goes 0 = outside, 1 = begin tag, 2 = inside, 3 = close tag, repeat.
		if ($i % 4 == 0)
		{
			fixTags($parts[$i]);

			// Replace /me.+?\n with [me=name]dsf[/me]\n.
			if (strpos($user_info['name'], '[') !== false || strpos($user_info['name'], ']') !== false || strpos($user_info['name'], '\'') !== false || strpos($user_info['name'], '"') !== false)
				$parts[$i] = preg_replace('~(?:\A|\n)/me(?: |&nbsp;)([^\n]*)(?:\z)?~i', '[me=&quot;' . $user_info['name'] . '&quot;]$1[/me]', $parts[$i]);
			else
				$parts[$i] = preg_replace('~(?:\A|\n)/me(?: |&nbsp;)([^\n]*)(?:\z)?~i', '[me=' . $user_info['name'] . ']$1[/me]', $parts[$i]);

			if (!$previewing)
			{
				if (allowedTo('admin_forum'))
					$parts[$i] = preg_replace('~\[html\](.+?)\[/html\]~ise', '\'[html]\' . $smfFunc[\'db_escape_string\'](strtr(un_htmlspecialchars(\'$1\'), array("\n" => \'&#13;\', \'  \' => \' &#32;\'))) . \'[/html]\'', $parts[$i]);
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

			// Make sure all tags are lowercase.
			$parts[$i] = preg_replace('~\[([/]?)(list|li|table|tr|td)([^\]]*)\]~ie', '\'[$1\' . strtolower(\'$2\') . \'$3]\'', $parts[$i]);

			$mistake_fixes = array(
				// Find [table]s not followed by [tr].
				'~\[table\](?![\s' . $non_breaking_space . ']*\[tr\])~s' . ($context['utf8'] ? 'u' : '') => '[table][tr]',
				// Find [tr]s not followed by [td].
				'~\[tr\](?![\s' . $non_breaking_space . ']*\[td\])~s' . ($context['utf8'] ? 'u' : '') => '[tr][td]',
				// Find [/td]s not followed by something valid.
				'~\[/td\](?![\s' . $non_breaking_space . ']*(?:\[td\]|\[/tr\]|\[/table\]))~s' . ($context['utf8'] ? 'u' : '') => '[/td][/tr]',
				// Find [/tr]s not followed by something valid.
				'~\[/tr\](?![\s' . $non_breaking_space . ']*(?:\[tr\]|\[/table\]))~s' . ($context['utf8'] ? 'u' : '') => '[/tr][/table]',
				// Find [/td]s incorrectly followed by [/table].
				'~\[/td\][\s' . $non_breaking_space . ']*\[/table\]~s' . ($context['utf8'] ? 'u' : '') => '[/td][/tr][/table]',
				// Find [table]s, [tr]s, and [/td]s (possibly correctly) followed by [td].
				'~\[(table|tr|/td)\]([\s' . $non_breaking_space . ']*)\[td\]~s' . ($context['utf8'] ? 'u' : '') => '[$1]$2[_td_]',
				// Now, any [td]s left should have a [tr] before them.
				'~\[td\]~s' => '[tr][td]',
				// Look for [tr]s which are correctly placed.
				'~\[(table|/tr)\]([\s' . $non_breaking_space . ']*)\[tr\]~s' . ($context['utf8'] ? 'u' : '') => '[$1]$2[_tr_]',
				// Any remaining [tr]s should have a [table] before them.
				'~\[tr\]~s' => '[table][tr]',
				// Look for [/td]s followed by [/tr].
				'~\[/td\]([\s' . $non_breaking_space . ']*)\[/tr\]~s' . ($context['utf8'] ? 'u' : '') => '[/td]$1[_/tr_]',
				// Any remaining [/tr]s should have a [/td].
				'~\[/tr\]~s' => '[/td][/tr]',
				// Look for properly opened [li]s which aren't closed.
				'~\[li\]([^\[\]]+?)\[li\]~s' => '[li]$1[_/li_][_li_]',
				'~\[li\]([^\[\]]+?)$~s' => '[li]$1[/li]',
				// Lists - find correctly closed items/lists.
				'~\[/li\]([\s' . $non_breaking_space . ']*)\[/list\]~s' . ($context['utf8'] ? 'u' : '') => '[_/li_]$1[/list]',
				// Find list items closed and then opened.
				'~\[/li\]([\s' . $non_breaking_space . ']*)\[li\]~s' . ($context['utf8'] ? 'u' : '') => '[_/li_]$1[_li_]',
				// Now, find any [list]s or [/li]s followed by [li].
				'~\[(list(?: [^\]]*?)?|/li)\]([\s' . $non_breaking_space . ']*)\[li\]~s' . ($context['utf8'] ? 'u' : '') => '[$1]$2[_li_]',
				// Any remaining [li]s weren't inside a [list].
				'~\[li\]~' => '[list][li]',
				// Any remaining [/li]s weren't before a [/list].
				'~\[/li\]~' => '[/li][/list]',
				// Put the correct ones back how we found them.
				'~\[_(li|/li|td|tr|/tr)_\]~' => '[$1]',
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
	global $smfFunc;

	$parts = preg_split('~(\[/code\]|\[code(?:=[^\]]+)?\])~i', $message, -1, PREG_SPLIT_DELIM_CAPTURE);

	// We're going to unparse only the stuff outside [code]...
	for ($i = 0, $n = count($parts); $i < $n; $i++)
	{
		// If $i is a multiple of four (0, 4, 8, ...) then it's not a code section...
		if ($i % 4 == 0)
		{
			$parts[$i] = preg_replace('~\[html\](.+?)\[/html\]~ie', '\'[html]\' . strtr(htmlspecialchars($smfFunc[\'db_unescape_string\'](\'$1\'), ENT_QUOTES), array(\'&amp;#13;\' => \'<br />\', \'&amp;#32;\' => \' \')) . \'[/html]\'', $parts[$i]);

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
		preg_match_all('~\[(' . $myTag . ')=([^\]]*?)\](.+?)\[/(' . $myTag . ')\]~is', $message, $matches);
	else
		preg_match_all('~\[(' . $myTag . ($hasExtra ? '(?:[^\]]*?)' : '') . ')\](.+?)\[/(' . $myTag . ')\]~is', $message, $matches);

	foreach ($matches[0] as $k => $dummy)
	{
		// Remove all leading and trailing whitespace.
		$replace = trim($matches[2][$k]);
		$this_tag = $matches[1][$k];
		if (!$hasEqualSign)
			$this_close = $matches[3][$k];
		else
			$this_close = $matches[4][$k];

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

		if ($hasEqualSign && $embeddedUrl)
			$replaces['[' . $matches[1][$k] . '=' . $matches[2][$k] . ']' . $matches[3][$k] . '[/' . $matches[4][$k] . ']'] = '[' . $this_tag . '=' . $replace . ']' . $matches[3][$k] . '[/' . $this_close . ']';
		elseif ($hasEqualSign)
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
	global $db_prefix, $smfFunc;

	// Use sendmail if it's set or if no SMTP server is set.
	$use_sendmail = empty($modSettings['mail_type']) || $modSettings['smtp_host'] == '';

	// Line breaks need to be \r\n only in windows or for SMTP.
	$line_break = $context['server']['is_windows'] || !$use_sendmail ? "\r\n" : "\n";

	// So far so good.
	$mail_result = true;

	// If the recipient list isn't an array, make it one.
	$to_array = is_array($to) ? $to : array($to);

	// Sadly Hotmail & Yahoo mail don't support character sets properly.
	if ($hotmail_fix === null)
	{
		$hotmail_to = array();
		foreach ($to_array as $i => $to_address)
		{
			if (preg_match('~@(yahoo|hotmail)\.[a-zA-Z\.]{2,6}$~i', $to_address) === 1)
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
	$subject = un_htmlspecialchars($smfFunc['db_unescape_string']($subject));
	// Make the message use the proper line breaks.
	$message = str_replace(array("\r", "\n"), array('', $line_break), $smfFunc['db_unescape_string']($message));

	// Make sure hotmail mails are sent as HTML so that HTML entities work.
	if ($hotmail_fix && !$send_html)
	{
		$send_html = true;
		$message = strtr($message, array($line_break => '<br />' . $line_break));
		$message = preg_replace('~(' . preg_quote($scripturl, '~') . '([?/][\w\-_%\.,\?&;=#]+)?)~', '<a href="$1">$1</a>', $message);
	}

	list (, $from_name) = mimespecialchars(addcslashes($from !== null ? $from : $context['forum_name'], '<>()\'\\"'), true, $hotmail_fix, $line_break);
	list (, $subject) = mimespecialchars($subject, true, $hotmail_fix, $line_break);

	// Construct the mail headers...
	$headers = 'From: "' . $from_name . '" <' . (empty($modSettings['mail_from']) ? $webmaster_email : $modSettings['mail_from']) . '>' . $line_break;
	$headers .= $from !== null ? 'Reply-To: <' . $from . '>' . $line_break : '';
	$headers .= 'Return-Path: ' . (empty($modSettings['mail_from']) ? $webmaster_email: $modSettings['mail_from']) . $line_break;
	$headers .= 'Date: ' . gmdate('D, d M Y H:i:s') . ' -0000' . $line_break;

	if ($message_id !== null && empty($modSettings['mail_no_message_id']))
		$headers .= 'Message-ID: <' . md5($scripturl . microtime()) . '-' . $message_id . strstr(empty($modSettings['mail_from']) ? $webmaster_email : $modSettings['mail_from'], '@') . '>' . $line_break;
	$headers .= 'X-Mailer: SMF' . $line_break;

	// pass this to the integration before we start modifying the output -- it'll make it easier later
	if (isset($modSettings['integrate_outgoing_email']) && function_exists($modSettings['integrate_outgoing_email']))
	{
		if ($modSettings['integrate_outgoing_email']($subject, $message, $headers) === false)
			return false;
	}

	// Save the original message...
	$orig_message = $message;

	// The mime boundary separates the different alternative versions.
	$mime_boundary = 'SMF-' . md5($message . time());

	// Using mime, as it allows to send a plain unencoded alternative.
	$headers .= 'Mime-Version: 1.0' . $line_break;
	$headers .= 'Content-Type: multipart/alternative; boundary="' . $mime_boundary . '"' . $line_break;
	$headers .= 'Content-Transfer-Encoding: 7bit' . $line_break;

	// Sending HTML?  Let's plop in some basic stuff, then.
	if ($send_html)
	{
		$no_html_message = un_htmlspecialchars(strip_tags(strtr($orig_message, array('</title>' => $line_break))));

		// But, then, dump it and use a plain one for dinosaur clients.
		list(, $plain_message) = mimespecialchars($no_html_message, false, true, $line_break);
		$message = $plain_message . $line_break . '--' . $mime_boundary . $line_break;

		// This is the plain text version.  Even if no one sees it, we need it for spam checkers.
		list($charset, $plain_charset_message, $encoding) = mimespecialchars($no_html_message, false, false, $line_break);
		$message .= 'Content-Type: text/plain; charset=' . $charset . $line_break;
		$message .= 'Content-Transfer-Encoding: ' . $encoding . $line_break . $line_break;
		$message .= $plain_charset_message . $line_break . '--' . $mime_boundary . $line_break;

		// This is the actual HTML message, prim and proper.  If we wanted images, they could be inlined here (with multipart/related, etc.)
		list($charset, $html_message, $encoding) = mimespecialchars($orig_message, false, $hotmail_fix, $line_break);
		$message .= 'Content-Type: text/html; charset=' . $charset . $line_break;
		$message .= 'Content-Transfer-Encoding: ' . ($encoding == '' ? '7bit' : $encoding) . $line_break . $line_break;
		$message .= $html_message . $line_break . '--' . $mime_boundary . '--';
	}
	// Text is good too.
	else
	{
		// Send a plain message first, for the older web clients.
		list(, $plain_message) = mimespecialchars($orig_message, false, true, $line_break);
		$message = $plain_message . $line_break . '--' . $mime_boundary . $line_break;

		// Now add an encoded message using the forum's character set.
		list ($charset, $encoded_message, $encoding) = mimespecialchars($orig_message, false, false, $line_break);
		$message .= 'Content-Type: text/plain; charset=' . $charset . $line_break;
		$message .= 'Content-Transfer-Encoding: ' . $encoding . $line_break . $line_break;
		$message .= $encoded_message . $line_break . '--' . $mime_boundary . '--';
	}

	// Are we using the mail queue, if so this is where we butt in...
	if (!empty($modSettings['mail_queue']) && $priority < 4)
		return AddMailQueue(false, $to_array, $subject, $message, $headers, $send_html, $priority);

	// If it's a priority mail, send it now - note though that this should NOT be used for sending many at once.
	elseif (!empty($modSettings['mail_queue']) && !empty($modSettings['mail_limit']))
	{
		list ($last_mail_time, $mails_this_minute) = @explode('|', $modSettings['mail_recent']);
		if (empty($mails_this_minute) || time() > $last_mail_time + 60)
			$new_queue_stat = time() . '|' . 1;
		else
			$new_queue_stat = $last_mail_time . '|' . ((int) $mails_this_minute + 1);

		updateSettings(array('mail_recent' => $new_queue_stat));
	}

	// SMTP or sendmail?
	if ($use_sendmail)
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
		$mail_result = $mail_result  && smtp_mail($to_array, $subject, $message, $headers);

	// Everything go smoothly?
	return $mail_result;
}

// Add an email to the mail queue.
function AddMailQueue($flush = false, $to_array = array(), $subject = '', $message = '', $headers = '', $send_html = false, $priority = 1)
{
	global $db_prefix, $context, $modSettings, $smfFunc;

	static $cur_insert = array();
	static $cur_insert_len = 0;

	// If we're flushing, make the final inserts - also if we're near the MySQL length limit!
	if (($flush || $cur_insert_len > 800000)&& !empty($cur_insert))
	{
		// Explode out the query and run it.
		$smfFunc['db_query']('', "
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

		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}settings
			SET value = '$nextSendTime'
			WHERE variable = 'mail_next_send'
				AND value = '0'", __FILE__, __LINE__);

		return true;
	}

	// Now prepare the data for inserting.
	$subject = $smfFunc['db_escape_string']($subject);
	$message = $smfFunc['db_escape_string']($message);
	$headers = $smfFunc['db_escape_string']($headers);

	// Ensure we tell obExit to flush.
	$context['flush_mail'] = true;

	foreach ($to_array as $to)
	{
		$to = $smfFunc['db_escape_string']($to);

		// Will this insert go over MySQL's limit?
		$this_insert_len = strlen($to) + strlen($message) + strlen($headers) + 700;

		// Insert limit of 1M (just under the safety) is reached?
		if ($this_insert_len + $cur_insert_len > 1000000)
		{
			// Flush out what we have so far.
			$smfFunc['db_query']('', "
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

	// If they are using SSI there is a good chance obExit will never be called.  So lets be nice and flush it for them.
	if (SMF === 'SSI')
		return AddMailQueue(true);

	return true;
}

// Send off a personal message.
function sendpm($recipients, $subject, $message, $store_outbox = false, $from = null, $pm_head = 0)
{
	global $db_prefix, $scripturl, $txt, $user_info, $language;
	global $modSettings, $smfFunc;

	$onBehalf = $from !== null;

	// Initialize log array.
	$log = array(
		'failed' => array(),
		'sent' => array()
	);

	if ($from === null)
		$from = array(
			'id' => $user_info['id'],
			'name' => $user_info['name'],
			'username' => $user_info['username']
		);
	// Probably not needed.  /me something should be of the typer.
	else
		$user_info['name'] = $from['name'];

	// This is the one that will go in their inbox.
	$htmlmessage = $smfFunc['db_escape_string']($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($message), ENT_QUOTES));
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
		$request = $smfFunc['db_query']('pm_find_username', "
			SELECT id_member, member_name
			FROM {$db_prefix}members
			WHERE " . ($smfFunc['db_case_sensitive'] ? 'LOWER(member_name)' : 'member_name') . " IN ('" . implode("', '", array_keys($usernames)) . "')", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			if (isset($usernames[$smfFunc['strtolower']($row['member_name'])]))
				$usernames[$smfFunc['strtolower']($row['member_name'])] = $row['id_member'];
		$smfFunc['db_free_result']($request);

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

	// Check no-one will want it deleted right away!
	$request = $smfFunc['db_query']('', "
		SELECT
			id_member, criteria, is_or
		FROM {$db_prefix}pm_rules
		WHERE id_member IN (" . implode(", ", $all_to) . ")
			AND delete_pm = 1", __FILE__, __LINE__);
	$deletes = array();
	// Check whether we have to apply anything...
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$criteria = unserialize($row['criteria']);
		// Note we don't check the buddy status cause deletion from buddy = madness!
		$delete = false;
		foreach ($criteria as $c)
		{
			$match = false;
			if (($c['t'] == 'mid' && $c['v'] == $from['id']) || ($c['t'] == 'gid' && in_array($c['v'], $user_info['groups'])) || ($c['t'] == 'sub' && strpos($subject, $c['v']) !== false) || ($c['t'] == 'msg' && strpos($message, $c['v']) !== false))
				$delete = true;
			// If we're adding and one criteria don't match then we stop!
			elseif (!$row['is_or'])
			{
				$delete = false;
				break;
			}
		}
		if ($delete)
			$deletes[$row['id_member']] = 1;
 	}
 	$smfFunc['db_free_result']($request);

	// Load the membergrounp message limits.
	//!!! Consider caching this?
	static $message_limit_cache = array();
	if (!allowedTo('moderate_forum') && empty($message_limit_cache))
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_group, max_messages
			FROM {$db_prefix}membergroups", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$message_limit_cache[$row['id_group']] = $row['max_messages'];
		$smfFunc['db_free_result']($request);
	}

	// Load the groups that are allowed to read PMs.
	$allowed_groups = array();
	$disallowed_groups = array();
	$request = $smfFunc['db_query']('', "
		SELECT id_group, add_deny
		FROM {$db_prefix}permissions
		WHERE permission='pm_read'", __FILE__, __LINE__);

	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (empty($row['add_deny']))
			$disallowed_groups[] = $row['id_group'];
		else
			$allowed_groups[] = $row['id_group'];
	}

	$smfFunc['db_free_result']($request);

	if (empty($modSettings['permission_enable_deny']))
		$disallowed_groups = array();

	$request = $smfFunc['db_query']('', "
		SELECT
			member_name, real_name, id_member, email_address, lngfile,
			pm_email_notify, instant_messages," . (allowedTo('moderate_forum') ? ' 0' : "
			(pm_ignore_list = '*' OR FIND_IN_SET($from[id], pm_ignore_list))") . " AS ignored,
			FIND_IN_SET($from[id], buddy_list) AS is_buddy, is_activated,
			additional_groups, id_group, id_post_group
		FROM {$db_prefix}members AS mem
		WHERE id_member IN (" . implode(", ", $all_to) . ")
		ORDER BY lngfile
		LIMIT " . count($all_to), __FILE__, __LINE__);
	$notifications = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Don't do anything for members to be deleted!
		if (isset($deletes[$row['id_member']]))
			continue;

		// We need to know this members groups.
		$groups = explode(',', $row['additional_groups']);
		$groups[] = $row['id_group'];
		$groups[] = $row['id_post_group'];

		$message_limit = -1;
		// For each group see whether they've gone over their limit - assuming they're not an admin.
		if (!in_array(1, $groups))
		{
			foreach ($groups as $id)
			{
				if (isset($message_limit_cache[$id]) && $message_limit != 0 && $message_limit < $message_limit_cache[$id])
					$message_limit = $message_limit_cache[$id];
			}

			if ($message_limit > 0 && $message_limit <= $row['instant_messages'])
			{
				$log['failed'][] = sprintf($txt['pm_error_data_limit_reached'], $row['real_name']);
				unset($all_to[array_search($row['id_member'], $all_to)]);
				continue;
			}

			// Do they have any of the allowed groups?
			if (count(array_intersect($allowed_groups, $groups)) == 0 || count(array_intersect($disallowed_groups, $groups)) != 0)
			{
				$log['failed'][] = sprintf($txt['pm_error_user_cannot_read'], $row['real_name']);
				unset($all_to[array_search($row['id_member'], $all_to)]);
				continue;
			}
		}

		if (!empty($row['ignored']))
		{
			$log['failed'][] = sprintf($txt['pm_error_ignored_by_user'], $row['real_name']);
			unset($all_to[array_search($row['id_member'], $all_to)]);
			continue;
		}

		// Does the recipient even have permission to read this message?


		// Send a notification, if enabled - taking into account buddy list!.
		if (!empty($row['email_address']) && ($row['pm_email_notify'] == 1 || ($row['pm_email_notify'] > 1 && ($row['is_buddy'] || !empty($modSettings['enable_buddylist'])))) && $row['is_activated'] == 1)
			$notifications[empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile']][] = $row['email_address'];

		$log['sent'][] = sprintf(isset($txt['pm_successfully_sent']) ? $txt['pm_successfully_sent'] : '', $row['real_name']);
	}
	$smfFunc['db_free_result']($request);

	// Only 'send' the message if there are any recipients left.
	if (empty($all_to))
		return $log;

	// Insert the message itself and then grab the last insert id.
	$smfFunc['db_query']('', "
		INSERT INTO {$db_prefix}personal_messages
			(id_pm_head, id_member_from, deleted_by_sender, from_name, msgtime, subject, body)
		VALUES ($pm_head, $from[id], " . ($store_outbox ? '0' : '1') . ", SUBSTRING('$from[username]', 1, 255), " . time() . ", SUBSTRING('$htmlsubject', 1, 255), SUBSTRING('$htmlmessage', 1, 65534))", __FILE__, __LINE__);
	$id_pm = $smfFunc['db_insert_id']("{$db_prefix}personal_messages", 'id_pm');

	// Add the recipients.
	if (!empty($id_pm))
	{
		// If this is new we need to set it part of it's own conversation.
		if (empty($pm_head))
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}personal_messages
				SET id_pm_head = $id_pm
				WHERE id_pm = $id_pm", __FILE__, __LINE__);

		// Some people think manually deleting personal_messages is fun... it's not. We protect against it though :)
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}pm_recipients
			WHERE id_pm = $id_pm", __FILE__, __LINE__);

		$insertRows = array();
		foreach ($all_to as $to)
		{
			$insertRows[] = array($id_pm, $to, in_array($to, $recipients['bcc']) ? 1 : 0, isset($deletes[$to]) ? 1 : 0, 1);
		}

		$smfFunc['db_insert']('insert',
			"{$db_prefix}pm_recipients",
			array('id_pm', 'id_member', 'bcc', 'deleted', 'is_new'),
			$insertRows,
			array('id_pm', 'id_member'), __FILE__, __LINE__
		);
	}

	$message = $smfFunc['db_unescape_string']($message);
	censorText($message);
	censorText($subject);
	$message = trim(un_htmlspecialchars(strip_tags(strtr(parse_bbc(htmlspecialchars($message), false), array('<br />' => "\n", '</div>' => "\n", '</li>' => "\n", '&#91;' => '[', '&#93;' => ']')))));

	foreach ($notifications as $lang => $notification_list)
	{
		// Make sure to use the right language.
		if (loadLanguage('PersonalMessage', $lang, false) === false)
			loadLanguage('InstantMessage', $lang, false);

		// Replace the right things in the message strings.
		$mailsubject = str_replace(array('SUBJECT', 'SENDER'), array($subject, un_htmlspecialchars($from['name'])), $txt['new_pm_subject']);
		$mailmessage = str_replace(array('SUBJECT', 'MESSAGE', 'SENDER'), array($subject, $message, un_htmlspecialchars($from['name'])), $txt['pm_email']);
		$mailmessage .= "\n\n" . $txt['instant_reply'] . ' ' . $scripturl . '?action=pm;sa=send;f=inbox;pmsg=' . $id_pm . ';quote;u=' . $from['id'];

		// Off the notification email goes!
		sendmail($notification_list, $mailsubject, $mailmessage, null, 'p' . $id_pm, false, 2);
	}

	// Back to what we were on before!
	if (loadLanguage('PersonalMessage') === false)
		loadLanguage('InstantMessage');

	// Add one to their unread and read message counts.
	foreach ($all_to as $k => $id)
		if (isset($deletes[$id]))
			unset($all_to[$k]);
	if (!empty($all_to))
		updateMemberData($all_to, array('instant_messages' => '+', 'unread_messages' => '+', 'new_pm' => '1'));

	return $log;
}

// Prepare text strings for sending as email body or header.
function mimespecialchars($string, $with_charset = true, $hotmail_fix = false, $line_break = "\r\n", $custom_charset = null)
{
	global $context;

	$charset = $custom_charset !== null ? $custom_charset : $context['character_set'];

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
		return array($charset, preg_replace('~([\x80-' . ($context['server']['complex_preg_chars'] ? '\x{10FFFF}' : pack('C*', 0xF7, 0xBF, 0xBF, 0xBF)) . '])~eu', '$entityConvert(\'1\')', $string), '7bit');
	}

	// We don't need to mess with the subject line if no special characters were in it..
	elseif (!$hotmail_fix && preg_match('~([^\x09\x0A\x0D\x20-\x7F])~', $string) === 1)
	{
		// Base64 encode.
		$string = base64_encode($string);

		// Show the characterset and the transfer-encoding for header strings.
		if ($with_charset)
			$string = '=?' . $charset . '?B?' . $string . '?=';

		// Break it up in lines (mail body).
		else
			$string = chunk_split($string, 76, $line_break);

		return array($charset, $string, 'base64');
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
	$pspell_link = pspell_new($txt['lang_dictionary'], $txt['lang_spelling'], '', strtr($context['character_set'], array('iso-' => 'iso', 'ISO-' => 'iso')), PSPELL_FAST | PSPELL_RUN_TOGETHER);
	error_reporting($old);
	ob_end_clean();

	// Most people don't have anything but english installed... so we use english as a last resort.
	if (!$pspell_link)
		$pspell_link = pspell_new('en', '', '', '', PSPELL_FAST | PSPELL_RUN_TOGETHER);

	if (!isset($_POST['spellstring']) || !$pspell_link)
		die;

	// Construct a bit of Javascript code.
	$context['spell_js'] = '
		var txt = {"done": "' . $txt['spellcheck_done'] . '"};
		var mispstr = window.opener.document.forms[spell_formname][spell_fieldname].value;
		var misps = Array(';

	// Get all the words (Javascript already separated them).
	$alphas = explode("\n", $smfFunc['db_unescape_string'](strtr($_POST['spellstring'], array("\r" => ''))));

	$found_words = false;
	for ($i = 0, $n = count($alphas); $i < $n; $i++)
	{
		// Words are sent like 'word|offset_begin|offset_end'.
		$check_word = explode('|', $alphas[$i]);

		// If the word is a known word, or spelled right...
		if (in_array($smfFunc['strtolower']($check_word[0]), $known_words) || pspell_check($pspell_link, $check_word[0]) || !isset($check_word[2]))
			continue;

		// Find the word, and move up the "last occurance" to here.
		$found_words = true;

		// Add on the javascript for this misspelling.
		$context['spell_js'] .= '
			new misp("' . strtr($check_word[0], array('\\' => '\\\\', '"' => '\\"', '<' => '', '&gt;' => '')) . '", ' . (int) $check_word[1] . ', ' . (int) $check_word[2] . ', [';

		// If there are suggestions, add them in...
		$suggestions = pspell_suggest($pspell_link, $check_word[0]);
		if (!empty($suggestions))
			$context['spell_js'] .= '"' . join('", "', $suggestions) . '"';

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
	global $modSettings, $sourcedir, $context, $smfFunc;

	// Can't do it if there's no topics.
	if (empty($topics))
		return;
	// It must be an array - it must!
	if (!is_array($topics))
		$topics = array($topics);

	// Get the subject and body...
	$result = $smfFunc['db_query']('', "
		SELECT mf.subject, ml.body, ml.id_member, t.id_last_msg, t.id_topic,
			IFNULL(mem.real_name, ml.poster_name) AS poster_name
		FROM {$db_prefix}topics AS t
			INNER JOIN {$db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
			INNER JOIN {$db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = ml.id_member)
		WHERE t.id_topic IN (" . implode(',', $topics) . ")
		LIMIT 1", __FILE__, __LINE__);
	$topicData = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		// Clean it up.
		censorText($row['subject']);
		censorText($row['body']);
		$row['subject'] = un_htmlspecialchars($row['subject']);
		$row['body'] = trim(un_htmlspecialchars(strip_tags(strtr(parse_bbc($row['body'], false, $row['id_last_msg']), array('<br />' => "\n", '</div>' => "\n", '</li>' => "\n", '&#91;' => '[', '&#93;' => ']')))));

		$topicData[$row['id_topic']] = array(
			'subject' => $row['subject'],
			'body' => $row['body'],
			'last_id' => $row['id_last_msg'],
			'topic' => $row['id_topic'],
			'name' => $user_info['name'],
			'exclude' => '',
		);
	}
	$smfFunc['db_free_result']($result);

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
	$smfFunc['db_query']('', "
		INSERT INTO {$db_prefix}log_digest
			(id_topic, id_msg, note_type, exclude)
		VALUES
			" . implode(', ', $digest_insert), __FILE__, __LINE__);

	// Find the members with notification on for this topic.
	$members = $smfFunc['db_query']('', "
		SELECT
			mem.id_member, mem.email_address, mem.notify_regularity, mem.notify_types, mem.notify_send_body, mem.lngfile,
			ln.sent, mem.id_group, mem.additional_groups, b.member_groups, mem.id_post_group, t.id_member_started,
			ln.id_topic
		FROM {$db_prefix}log_notify AS ln
			INNER JOIN {$db_prefix}members AS mem ON (mem.id_member = ln.id_member)
			INNER JOIN {$db_prefix}topics AS t ON (t.id_topic = ln.id_topic)
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)
		WHERE ln.id_topic IN (" . implode(',', $topics) . ")
			AND mem.notify_types < " . ($type == 'reply' ? '4' : '3') . "
			AND mem.notify_regularity < 2
			AND mem.is_activated = 1
			AND ln.id_member != $user_info[id]
		ORDER BY mem.lngfile", __FILE__, __LINE__);
	$sent = 0;
	while ($row = $smfFunc['db_fetch_assoc']($members))
	{
		// Don't do the excluded...
		if ($topicData[$row['id_topic']]['exclude'] == $row['id_member'])
			continue;

		// Easier to check this here... if they aren't the topic poster do they really want to know?
		if ($type != 'reply' && $row['notify_types'] == 2 && $row['id_member'] != $row['id_member_started'])
			continue;

		if ($row['id_group'] != 1)
		{
			$allowed = explode(',', $row['member_groups']);
			$row['additional_groups'] = explode(',', $row['additional_groups']);
			$row['additional_groups'][] = $row['id_group'];
			$row['additional_groups'][] = $row['id_post_group'];

			if (count(array_intersect($allowed, $row['additional_groups'])) == 0)
				continue;
		}

		$needed_language = empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile'];
		if (empty($current_language) || $current_language != $needed_language)
			$current_language = loadLanguage('Post', $needed_language, false);

		$message_type = 'notification_' . $type;
		$replacements = array(
			'TOPICSUBJECT' => $topicData[$row['id_topic']]['subject'],
			'POSTERNAME' => un_htmlspecialchars($topicData[$row['id_topic']]['name']),
			'TOPICLINK' => $scripturl . '?topic=' . $row['id_topic'] . '.new;topicseen#new',
			'UNSUBSCRIBELINK' => $scripturl . '?action=notify;topic=' . $row['id_topic'] . '.0',
		);

		if ($type == 'remove')
		{
			unset($replacements['TOPICLINK']);
			unset($replacements['UNSUBSCRIBELINK']);
		}
		// Do they want the body of the message sent too?
		if (!empty($row['notify_send_body']) && $type == 'reply' && empty($modSettings['disallow_sendBody']))
		{
			$message_type .= '_body';
			$replacements['MESSAGE'] = $topicData[$row['id_topic']]['body'];
		}
		if (!empty($row['notify_regularity']) && $type == 'reply')
			$message_type .= '_once';

		// Send only if once is off or it's on and it hasn't been sent.
		if ($type != 'reply' || empty($row['notify_regularity']) || empty($row['sent']))
		{
			$emaildata = loadEmailTemplate($message_type, $replacements);
			sendmail($row['email_address'], $emaildata['subject'], $emaildata['body'], null, 'm' . $topicData[$row['id_topic']]['last_id']);
			$sent++;
		}
	}
	$smfFunc['db_free_result']($members);

	if (isset($current_language) && $current_language != $user_info['language'])
		loadLanguage('Post');

	// Sent!
	if ($type == 'reply' && !empty($sent))
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}log_notify
			SET sent = 1
			WHERE id_topic IN (" . implode(',', $topics) . ")
				AND id_member != $user_info[id]", __FILE__, __LINE__);

	// For approvals we need to unsend the exclusions (This *is* the quickest way!)
	if (!empty($sent) && !empty($exclude))
	{
		foreach ($topicData as $id => $data)
			if ($data['exclude'])
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}log_notify
					SET sent = 0
					WHERE id_topic = $id
						AND id_member = $data[exclude]", __FILE__, __LINE__);
	}
}

// Create a post, either as new topic (id_topic = 0) or in an existing one.
// The input parameters of this function assume:
// - Strings have been escaped.
// - Integers have been cast to integer.
// - Mandatory parameters are set.
function createPost(&$msgOptions, &$topicOptions, &$posterOptions)
{
	global $db_prefix, $user_info, $txt, $modSettings, $smfFunc;

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

	// We need to know if the topic is approved. If we're told that's great - if not find out.
	if (!empty($topicOptions['id']) && !isset($topicOptions['is_approved']))
	{
		$request = $smfFunc['db_query']('', "
			SELECT approved
			FROM {$db_prefix}topics
			WHERE id_topic = $topicOptions[id]
			LIMIT 1", __FILE__, __LINE__);
		list ($topicOptions['is_approved']) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
	}

	// If nothing was filled in as name/e-mail address, try the member table.
	if (!isset($posterOptions['name']) || $posterOptions['name'] == '' || (empty($posterOptions['email']) && !empty($posterOptions['id'])))
	{
		if (empty($posterOptions['id']))
		{
			$posterOptions['id'] = 0;
			$posterOptions['name'] = $txt['guest_title'];
			$posterOptions['email'] = '';
		}
		elseif ($posterOptions['id'] != $user_info['id'])
		{
			$request = $smfFunc['db_query']('', "
				SELECT member_name, email_address
				FROM {$db_prefix}members
				WHERE id_member = $posterOptions[id]
				LIMIT 1", __FILE__, __LINE__);
			// Couldn't find the current poster?
			if ($smfFunc['db_num_rows']($request) == 0)
			{
				trigger_error('createPost(): Invalid member id ' . $posterOptions['id'], E_USER_NOTICE);
				$posterOptions['id'] = 0;
				$posterOptions['name'] = $txt['guest_title'];
				$posterOptions['email'] = '';
			}
			else
				list ($posterOptions['name'], $posterOptions['email']) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);
		}
		else
		{
			$posterOptions['name'] = $user_info['name'];
			$posterOptions['email'] = $user_info['email'];
		}

		$posterOptions['email'] = $smfFunc['db_escape_string']($posterOptions['email']);
	}

	// It's do or die time: forget any user aborts!
	$previous_ignore_user_abort = ignore_user_abort(true);

	$new_topic = empty($topicOptions['id']);

	// Insert the post.
	$smfFunc['db_query']('', "
		INSERT INTO {$db_prefix}messages
			(id_board, id_topic, id_member, subject, body, poster_name, poster_email, poster_time,
			poster_ip, smileys_enabled, modified_name, icon, approved)
		VALUES ($topicOptions[board], $topicOptions[id], $posterOptions[id], SUBSTRING('$msgOptions[subject]', 1, 255), SUBSTRING('$msgOptions[body]', 1, 65534), SUBSTRING('$posterOptions[name]', 1, 255), SUBSTRING('$posterOptions[email]', 1, 255), " . time() . ",
			SUBSTRING('$posterOptions[ip]', 1, 255), " . ($msgOptions['smileys_enabled'] ? '1' : '0') . ", '', SUBSTRING('$msgOptions[icon]', 1, 16), $msgOptions[approved])", __FILE__, __LINE__);
	$msgOptions['id'] = $smfFunc['db_insert_id']("{$db_prefix}messages", 'id_msg');

	// Something went wrong creating the message...
	if (empty($msgOptions['id']))
		return false;

	// Fix the attachments.
	if (!empty($msgOptions['attachments']))
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}attachments
			SET id_msg = $msgOptions[id]
			WHERE id_attach IN (" . implode(', ', $msgOptions['attachments']) . ')', __FILE__, __LINE__);

	// Insert a new topic (if the topicID was left empty.
	if ($new_topic)
	{
		$smfFunc['db_query']('', "
			INSERT INTO {$db_prefix}topics
				(id_board, id_member_started, id_member_updated, id_first_msg, id_last_msg, locked, is_sticky, num_views, id_poll, unapproved_posts, approved)
			VALUES ($topicOptions[board], $posterOptions[id], $posterOptions[id], $msgOptions[id], $msgOptions[id],
				" . ($topicOptions['lock_mode'] === null ? '0' : $topicOptions['lock_mode']) . ', ' .
				($topicOptions['sticky_mode'] === null ? '0' : $topicOptions['sticky_mode']) . ", 0,
				" . ($topicOptions['poll'] === null ? '0' : $topicOptions['poll']) . ', ' . ($msgOptions['approved'] ? 0 : 1) . ", $msgOptions[approved])", __FILE__, __LINE__);
		$topicOptions['id'] = $smfFunc['db_insert_id']("{$db_prefix}topics", 'id_topic');

		// The topic couldn't be created for some reason.
		if (empty($topicOptions['id']))
		{
			// We should delete the post that did work, though...
			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}messages
				WHERE id_msg = $msgOptions[id]", __FILE__, __LINE__);

			return false;
		}

		// Fix the message with the topic.
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}messages
			SET id_topic = $topicOptions[id]
			WHERE id_msg = $msgOptions[id]", __FILE__, __LINE__);

		// There's been a new topic AND a new post today.
		trackStats(array('topics' => '+', 'posts' => '+'));

		updateStats('topic', true);
		updateStats('subject', $topicOptions['id'], $msgOptions['subject']);
		//What if we want to export new topics out to a CMS?
		if (isset($modSettings['integrate_create_topic']) && function_exists($modSettings['integrate_create_topic']))
			$modSettings['integrate_create_topic']($msgOptions, $topicOptions, $posterOptions);
	}
	// The topic already exists, it only needs a little updating.
	else
	{
		$countChange = $msgOptions['approved'] ? 'num_replies = num_replies + 1' : 'unapproved_posts = unapproved_posts + 1';

		// Update the number of replies and the lock/sticky status.
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}topics
			SET
				" . ($msgOptions['approved'] ? "id_member_updated = $posterOptions[id], id_last_msg = $msgOptions[id]," : '') . "
				$countChange" . ($topicOptions['lock_mode'] === null ? '' : ",
				locked = $topicOptions[lock_mode]") . ($topicOptions['sticky_mode'] === null ? '' : ",
				is_sticky = $topicOptions[sticky_mode]") . "
			WHERE id_topic = $topicOptions[id]", __FILE__, __LINE__);

		// One new post has been added today.
		trackStats(array('posts' => '+'));
	}

	// Creating is modifying...in a way.
	//!!! Why not set id_msg_modified on the insert?
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}messages
		SET id_msg_modified = $msgOptions[id]
		WHERE id_msg = $msgOptions[id]", __FILE__, __LINE__);

	// Increase the number of posts and topics on the board.
	if ($msgOptions['approved'])
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}boards
			SET num_posts = num_posts + 1" . ($new_topic ? ', num_topics = num_topics + 1' : '') . "
			WHERE id_board = $topicOptions[board]", __FILE__, __LINE__);
	else
	{
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}boards
			SET unapproved_posts = unapproved_posts + 1" . ($new_topic ? ', unapproved_topics = unapproved_topics + 1' : '') . "
			WHERE id_board = $topicOptions[board]", __FILE__, __LINE__);

		// Add to the approval queue too.
		$smfFunc['db_query']('', "
			INSERT INTO {$db_prefix}approval_queue
				(id_msg)
			VALUES
				($msgOptions[id])", __FILE__, __LINE__);
	}

	// Mark inserted topic as read (only for the user calling this function).
	if (!empty($topicOptions['mark_as_read']) && !$user_info['is_guest'])
	{
		// Since it's likely they *read* it before replying, let's try an UPDATE first.
		if (!$new_topic)
		{
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}log_topics
				SET id_msg = $msgOptions[id] + 1
				WHERE id_member = $user_info[id]
					AND id_topic = $topicOptions[id]", __FILE__, __LINE__);

			$flag = $smfFunc['db_affected_rows']() != 0;
		}

		if (empty($flag))
		{
			$smfFunc['db_insert']('replace',
				"{$db_prefix}log_topics",
				array('id_topic', 'id_member', 'id_msg'),
				array($topicOptions['id'], $user_info['id'], $msgOptions['id'] + 1),
				array('id_topic', 'id_member'), __FILE__, __LINE__
			);
		}
	}

	// If there's a custom search index, it needs updating...
	if (!empty($modSettings['search_custom_index_config']))
	{
		//$index_settings = unserialize($modSettings['search_custom_index_config']);

		$inserts = array();
		foreach (text2words($smfFunc['db_unescape_string']($msgOptions['body']), 4, true) as $word)
			$inserts[] = array($word, $msgOptions['id']);

		if (!empty($inserts))
			$smfFunc['db_insert']('ignore',
				"{$db_prefix}log_search_words",
				array('id_word', 'id_msg'),
				$inserts,
				array('id_word', 'id_msg'), __FILE__, __LINE__
			);
	}

	// Increase the post counter for the user that created the post.
	if (!empty($posterOptions['update_post_count']) && !empty($posterOptions['id']))
	{
		// Are you the one that happened to create this post?
		if ($user_info['id'] == $posterOptions['id'])
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

	// Update the last message on the board assuming it's approved AND the topic is.
	if ($msgOptions['approved'])
		updateLastMessages($topicOptions['board'], $new_topic || !empty($topicOptions['is_approved']) ? $msgOptions['id'] : 0);

	// Alright, done now... we can abort now, I guess... at least this much is done.
	ignore_user_abort($previous_ignore_user_abort);

	// Success.
	return true;
}

// !!!
function createAttachment(&$attachmentOptions)
{
	global $db_prefix, $modSettings, $sourcedir, $smfFunc;

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

	// These are the only valid image types for SMF.
	$validImageTypes = array(1 => 'gif', 2 => 'jpeg', 3 => 'png', 5 => 'psd', 6 => 'bmp', 7 => 'tiff', 8 => 'tiff', 9 => 'jpeg', 14 => 'iff');

	if (!$file_restricted || $already_uploaded)
	{
		$size = @getimagesize($attachmentOptions['tmp_name']);
		list ($attachmentOptions['width'], $attachmentOptions['height']) = $size;

		// If it's an image get the mime type right.
		if (empty($attachmentOptions['mime_type']) && $attachmentOptions['width'])
		{
			// Got a proper mime type?
			if (!empty($size['mime']))
				$attachmentOptions['mime_type'] = $size['mime'];
			// Otherwise a valid one?
			elseif (isset($validImageTypes[$size[2]]))
				$attachmentOptions['mime_type'] = 'image/' . $validImageTypes[$size[2]];
		}
 	}

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
		$dir = @opendir($modSettings['attachmentUploadDir']) or fatal_lang_error('cant_access_upload_path', 'critical');
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
		$request = $smfFunc['db_query']('', "
			SELECT id_attach
			FROM {$db_prefix}attachments
			WHERE filename = '" . strtolower($attachmentOptions['name']) . "'
			LIMIT 1", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) > 0)
			$attachmentOptions['errors'][] = 'taken_filename';
		$smfFunc['db_free_result']($request);
	}

	if (!empty($attachmentOptions['errors']))
		return false;

	if (!is_writable($modSettings['attachmentUploadDir']))
		fatal_lang_error('attachments_no_write', 'critical');

	// Assuming no-one set the extension let's take a look at it.
	if (empty($attachmentOptions['fileext']))
	{
		$attachmentOptions['fileext'] = strtolower(strrpos($attachmentOptions['name'], '.') !== false ? substr($attachmentOptions['name'], strrpos($attachmentOptions['name'], '.') + 1) : '');
		if (strlen($attachmentOptions['fileext']) > 8 || '.' . $attachmentOptions['fileext'] == $attachmentOptions['name'])
			$attachmentOptions['fileext'] = '';
	}

	$smfFunc['db_query']('', "
		INSERT INTO {$db_prefix}attachments
			(id_msg, filename, fileext, size, width, height, mime_type, approved)
		VALUES (" . (int) $attachmentOptions['post'] . ", SUBSTRING('" . $attachmentOptions['name'] . "', 1, 255), SUBSTRING('" . $attachmentOptions['fileext'] . "', 1, 8), " . (int) $attachmentOptions['size'] . ', ' . (empty($attachmentOptions['width']) ? '0' : (int) $attachmentOptions['width']) . ', ' . (empty($attachmentOptions['height']) ? '0' : (int) $attachmentOptions['height']) . ', ' . (!empty($attachmentOptions['mime_type']) ? "SUBSTRING('$attachmentOptions[mime_type]', 1, 20)" : "''") . ', ' . (int) $attachmentOptions['approved'] . ')', __FILE__, __LINE__);
	$attachmentOptions['id'] = $smfFunc['db_insert_id']("{$db_prefix}attachments", 'id_attach');

	if (empty($attachmentOptions['id']))
		return false;

	// If it's not approved add to the approval queue.
	if (!$attachmentOptions['approved'])
		$smfFunc['db_query']('', "
			INSERT INTO {$db_prefix}approval_queue
				(id_attach, id_msg)
			VALUES
				($attachmentOptions[id], " . (int) $attachmentOptions['post'] . ")", __FILE__, __LINE__);

	$attachmentOptions['destination'] = $modSettings['attachmentUploadDir'] . '/' . getAttachmentFilename(basename($attachmentOptions['name']), $attachmentOptions['id'], true);

	if ($already_uploaded)
		rename($attachmentOptions['tmp_name'], $attachmentOptions['destination']);
	elseif (!move_uploaded_file($attachmentOptions['tmp_name'], $attachmentOptions['destination']))
		fatal_lang_error('attach_timeout', 'critical');
	// We couldn't access the file before...
	elseif ($file_restricted)
	{
		$size = @getimagesize($attachmentOptions['destination']);
		list ($attachmentOptions['width'], $attachmentOptions['height']) = $size;

		// Have a go at getting the right mime type.
		if (empty($attachmentOptions['mime_type']) && $attachmentOptions['width'])
		{
			if (!empty($size['mime']))
				$attachmentOptions['mime_type'] = $size['mime'];
			elseif (isset($validImageTypes[$size[2]]))
				$attachmentOptions['mime_type'] = 'image/' . $validImageTypes[$size[2]];
		}

		if (!empty($attachmentOptions['width']) && !empty($attachmentOptions['height']))
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}attachments
				SET
					width = " . (int) $attachmentOptions['width'] . ",
					height = " . (int) $attachmentOptions['height'] . ",
					mime_type = '" . (empty($attachmentOptions['mime_type']) ? '' : $attachmentOptions['mime_type']) . "'
				WHERE id_attach = $attachmentOptions[id]", __FILE__, __LINE__);
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
			$size = @getimagesize($attachmentOptions['destination'] . '_thumb');
			list ($thumb_width, $thumb_height) = $size;

			if (!empty($size['mime']))
				$thumb_mime = $size['mime'];
			elseif (isset($validImageTypes[$size[2]]))
				$thumb_mime = 'image/' . $validImageTypes[$size[2]];
			// Lord only knows how this happened...
			else
				$thumb_mime = '';

			$thumb_filename = $smfFunc['db_escape_string']($attachmentOptions['name'] . '_thumb');
			$thumb_size = filesize($attachmentOptions['destination'] . '_thumb');

			// To the database we go!
			$smfFunc['db_query']('', "
				INSERT INTO {$db_prefix}attachments
					(id_msg, attachment_type, filename, fileext, size, width, height, mime_type, approved)
				VALUES (" . (int) $attachmentOptions['post'] . ", 3, SUBSTRING('$thumb_filename', 1, 255), SUBSTRING('" . $attachmentOptions['fileext'] . "', 1, 8), " . (int) $thumb_size . ", " . (int) $thumb_width . ", " . (int) $thumb_height . ", SUBSTRING('$thumb_mime', 1, 20), " . (int) $attachmentOptions['approved'] . ')', __FILE__, __LINE__);
			$attachmentOptions['thumb'] = $smfFunc['db_insert_id']("{$db_prefix}attachments", 'id_attach');

			if (!empty($attachmentOptions['thumb']))
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}attachments
					SET id_thumb = $attachmentOptions[thumb]
					WHERE id_attach = $attachmentOptions[id]", __FILE__, __LINE__);

				rename($attachmentOptions['destination'] . '_thumb', $modSettings['attachmentUploadDir'] . '/' . getAttachmentFilename($thumb_filename, $attachmentOptions['thumb'], true));
			}
		}
	}

	return true;
}

// !!!
function modifyPost(&$msgOptions, &$topicOptions, &$posterOptions)
{
	global $db_prefix, $user_info, $modSettings, $smfFunc;

	$topicOptions['poll'] = isset($topicOptions['poll']) ? (int) $topicOptions['poll'] : null;
	$topicOptions['lock_mode'] = isset($topicOptions['lock_mode']) ? $topicOptions['lock_mode'] : null;
	$topicOptions['sticky_mode'] = isset($topicOptions['sticky_mode']) ? $topicOptions['sticky_mode'] : null;

	// This is longer than it has to be, but makes it so we only set/change what we have to.
	$messages_columns = array();
	if (isset($posterOptions['name']))
		$messages_columns[] = "poster_name = '$posterOptions[name]'";
	if (isset($posterOptions['email']))
		$messages_columns[] = "poster_email = '$posterOptions[email]'";
	if (isset($msgOptions['icon']))
		$messages_columns[] = "icon = '$msgOptions[icon]'";
	if (isset($msgOptions['subject']))
		$messages_columns[] = "subject = '$msgOptions[subject]'";
	if (isset($msgOptions['body']))
	{
		$messages_columns[] = "body = '$msgOptions[body]'";

		if (!empty($modSettings['search_custom_index_config']))
		{
			$request = $smfFunc['db_query']('', "
				SELECT body
				FROM {$db_prefix}messages
				WHERE id_msg = $msgOptions[id]", __FILE__, __LINE__);
			list ($old_body) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);
		}
	}
	if (!empty($msgOptions['modify_time']))
	{
		$messages_columns[] = "modified_time = $msgOptions[modify_time]";
		$messages_columns[] = "modified_name = '$msgOptions[modify_name]'";
		$messages_columns[] = "id_msg_modified = $modSettings[maxMsgID]";
	}
	if (isset($msgOptions['smileys_enabled']))
		$messages_columns[] = "smileys_enabled = " . (empty($msgOptions['smileys_enabled']) ? '0' : '1');

	// Change the post.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}messages
		SET " . implode(', ', $messages_columns) . "
		WHERE id_msg = $msgOptions[id]", __FILE__, __LINE__);

	// Lock and or sticky the post.
	if ($topicOptions['sticky_mode'] !== null || $topicOptions['lock_mode'] !== null || $topicOptions['poll'] !== null)
	{
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}topics
			SET
				is_sticky = " . ($topicOptions['sticky_mode'] === null ? 'is_sticky' : $topicOptions['sticky_mode']) . ",
				locked = " . ($topicOptions['lock_mode'] === null ? 'locked' : $topicOptions['lock_mode']) . ",
				id_poll = " . ($topicOptions['poll'] === null ? 'id_poll' : $topicOptions['poll']) . "
			WHERE id_topic = $topicOptions[id]", __FILE__, __LINE__);
	}

	// Mark inserted topic as read.
	if (!empty($topicOptions['mark_as_read']) && !$user_info['is_guest'])
		$smfFunc['db_insert']('replace',
			"{$db_prefix}log_topics",
			array('id_topic', 'id_member', 'id_msg'),
			array($topicOptions['id'], $user_info['id'], $modSettings['maxMsgID']),
			array('id_topic', 'id_member'), __FILE__, __LINE__
		);

	// If there's a custom search index, it needs to be modified...
	if (isset($msgOptions['body']) && !empty($modSettings['search_custom_index_config']))
	{
		$stopwords = empty($modSettings['search_stopwords']) ?  array() : explode(',', $smfFunc['db_escape_string']($modSettings['search_stopwords']));
		$old_index = text2words($old_body, 4, true);
		$new_index = text2words($smfFunc['db_unescape_string']($msgOptions['body']), 4, true);

		// Calculate the words to be added and removed from the index.
		$removed_words = array_diff(array_diff($old_index, $new_index), $stopwords);
		$inserted_words = array_diff(array_diff($new_index, $old_index), $stopwords);
		// Delete the removed words AND the added ones to avoid key constraints.
		if (!empty($removed_words))
		{
			$removed_words = array_merge($removed_words, $inserted_words);
			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}log_search_words
				WHERE id_msg = $msgOptions[id]
					AND id_word IN (" . implode(", ", $removed_words) . ")", __FILE__, __LINE__);
		}

		// Add the new words to be indexed.
		if (!empty($inserted_words))
		{
			$inserts = array();
			foreach ($inserted_words as $word)
				$inserts[] = array("'$word'", $msgOptions['id']);
			$smfFunc['db_insert']('insert',
				"{$db_prefix}log_search_words",
				array('id_word', 'id_msg'),
				$inserts,
				array('id_word', 'id_msg'), __FILE__, __LINE__
			);
		}
	}

	if (isset($msgOptions['subject']))
	{
		// Only update the subject if this was the first message in the topic.
		$request = $smfFunc['db_query']('', "
			SELECT id_topic
			FROM {$db_prefix}topics
			WHERE id_first_msg = $msgOptions[id]
			LIMIT 1", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) == 1)
			updateStats('subject', $topicOptions['id'], $msgOptions['subject']);
		$smfFunc['db_free_result']($request);
	}

	// Finally, if we are setting the approved state we need to do much more work :(
	if (isset($msgOptions['approved']))
		approvePosts($msgOptions['id'], $msgOptions['approved']);

	return true;
}

// Approve (or not) some posts... without permission checks...
function approvePosts($msgs, $approve = true)
{
	global $db_prefix, $sourcedir, $smfFunc;

	if (!is_array($msgs))
		$msgs = array($msgs);

	if (empty($msgs))
		return false;

	// May as well start at the beginning, working out *what* we need to change.
	$request = $smfFunc['db_query']('', "
		SELECT m.id_msg, m.approved, m.id_topic, m.id_board, t.id_first_msg, t.id_last_msg,
			m.body, m.subject, IFNULL(mem.real_name, m.poster_name) AS poster_name, m.id_member,
			t.approved AS topic_approved
		FROM {$db_prefix}messages AS m
			INNER JOIN {$db_prefix}topics AS t ON (t.id_topic = m.id_topic)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
		WHERE m.id_msg IN (" . implode(',', $msgs) . ")
			AND m.approved = " . ($approve ? 0 : 1), __FILE__, __LINE__);
	$msgs = array();
	$topics = array();
	$topic_changes = array();
	$board_changes = array();
	$notification_topics = array();
	$notification_posts = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Easy...
		$msgs[] = $row['id_msg'];
		$topics[] = $row['id_topic'];

		// Ensure our change array exists already.
		if (!isset($topic_changes[$row['id_topic']]))
			$topic_changes[$row['id_topic']] = array(
				'id_last_msg' => $row['id_last_msg'],
				'approved' => $row['topic_approved'],
				'replies' => 0,
				'unapproved_posts' => 0,
			);
		if (!isset($board_changes[$row['id_board']]))
			$board_changes[$row['id_board']] = array(
				'posts' => 0,
				'topics' => 0,
				'unapproved_posts' => 0,
				'unapproved_topics' => 0,
			);

		// If it's the first message then the topic state changes!
		if ($row['id_msg'] == $row['id_first_msg'])
		{
			$topic_changes[$row['id_topic']]['approved'] = $approve ? 1 : 0;

			$board_changes[$row['id_board']]['unapproved_topics'] += $approve ? -1 : 1;
			$board_changes[$row['id_board']]['topics'] += $approve ? 1 : -1;

			// Note we need to ensure we annouce this topic!
			$notification_topics[] = array(
				'body' => $row['body'],
				'subject' => $row['subject'],
				'name' => $row['poster_name'],
				'board' => $row['id_board'],
				'topic' => $row['id_topic'],
				'msg' => $row['id_first_msg'],
				'poster' => $row['id_member'],
			);
		}
		else
		{
			$topic_changes[$row['id_topic']]['replies'] += $approve ? 1 : -1;

			// This will be a post... but don't notify unless it's not followed by approved ones.
			if ($row['id_msg'] > $row['id_last_msg'])
				$notification_posts[$row['id_topic']][] = array(
					'id' => $row['id_msg'],
					'body' => $row['message'],
					'subject' => $row['subject'],
					'name' => $row['poster_name'],
					'topic' => $row['id_topic'],
				);
		}

		// If this is being approved and id_msg is higher than the current id_last_msg then it changes.
		if ($approve && $row['id_msg'] > $topic_changes[$row['id_topic']]['id_last_msg'])
			$topic_changes[$row['id_topic']]['id_last_msg'] = $row['id_msg'];
		// If this is being unapproved, and it's equal to the id_last_msg we need to find a new one!
		elseif (!$approve)
			// Default to the first message and then we'll override in a bit ;)
			$topic_changes[$row['id_topic']]['id_last_msg'] = $row['id_first_msg'];

		$topic_changes[$row['id_topic']]['unapproved_posts'] += $approve ? -1 : 1;
		$board_changes[$row['id_board']]['unapproved_posts'] += $approve ? -1 : 1;
		$board_changes[$row['id_board']]['posts'] += $approve ? 1 : -1;
	}
	$smfFunc['db_free_result']($request);

	if (empty($msgs))
		return;

	// Now we have the differences make the changes, first the easy one.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}messages
		SET approved = " . ($approve ? 1 : 0) . "
		WHERE id_msg IN (" . implode(',', $msgs) . ")", __FILE__, __LINE__);

	// If we were unapproving find the last msg in the topics...
	if (!$approve)
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_topic, MAX(id_msg) AS id_last_msg
			FROM {$db_prefix}messages
			WHERE id_topic IN (" . implode(',', $topics) . ")
				AND approved = 1
			GROUP BY id_topic", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$topic_changes[$row['id_topic']]['id_last_msg'] = $row['id_last_msg'];
		$smfFunc['db_free_result']($request);
	}

	// ... next the topics...
	foreach ($topic_changes as $id => $changes)
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}topics
			SET approved = $changes[approved], unapproved_posts = unapproved_posts + $changes[unapproved_posts],
				num_replies = num_replies + $changes[replies], id_last_msg = $changes[id_last_msg]
			WHERE id_topic = $id", __FILE__, __LINE__);

	// ... finally the boards...
	foreach ($board_changes as $id => $changes)
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}boards
			SET num_posts = num_posts + $changes[posts], unapproved_posts = unapproved_posts + $changes[unapproved_posts],
				num_topics = num_topics + $changes[topics], unapproved_topics = unapproved_topics + $changes[unapproved_topics]
			WHERE id_board = $id", __FILE__, __LINE__);

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

		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}approval_queue
			WHERE id_msg IN (" . implode(',', $msgs) . ")
				AND id_attach = 0", __FILE__, __LINE__);
	}
	// If unapproving add to the approval queue!
	else
	{
		$msgInserts = array();
		foreach ($msgs as $msg)
			$msgInserts[] = array($msg);

		$smfFunc['db_insert']('ignore',
			"{$db_prefix}approval_queue",
			array('id_msg'),
			$msgInserts,
			array('id_msg'), __FILE__, __LINE__
		);
	}

	// Update the last messages on the boards...
	updateLastMessages(array_keys($board_changes));

	return true;
}

// Approve topics?
function approveTopics($topics, $approve = true)
{
	global $db_prefix, $smfFunc;

	if (!is_array($topics))
		$topics = array($topics);

	if (empty($topics))
		return false;

	$approve_type = $approve ? 0 : 1;

	// Just get the messages to be approved and pass through...
	$request = $smfFunc['db_query']('', "
		SELECT id_msg
		FROM {$db_prefix}messages
		WHERE id_topic IN (" . implode(',', $topics) . ")
			AND approved = $approve_type", __FILE__, __LINE__);
	$msgs = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$msgs[] = $row['id_msg'];
	$smfFunc['db_free_result']($request);

	return approvePosts($msgs, $approve);
}

// A special function for handling the hell which is sending approval notifications.
function sendApprovalNotifications(&$topicData)
{
	global $txt, $scripturl, $db_prefix, $language, $user_info;
	global $modSettings, $sourcedir, $context, $smfFunc;

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
	$smfFunc['db_query']('', "
		INSERT INTO {$db_prefix}log_digest
			(id_topic, id_msg, note_type, exclude)
		VALUES
			" . implode(', ', $digest_insert), __FILE__, __LINE__);

	// Find everyone who needs to know about this.
	$members = $smfFunc['db_query']('', "
		SELECT
			mem.id_member, mem.email_address, mem.notify_regularity, mem.notify_types, mem.notify_send_body, mem.lngfile,
			ln.sent, mem.id_group, mem.additional_groups, b.member_groups, mem.id_post_group, t.id_member_started,
			ln.id_topic
		FROM {$db_prefix}log_notify AS ln
			INNER JOIN {$db_prefix}members AS mem ON (mem.id_member = ln.id_member)
			INNER JOIN {$db_prefix}topics AS t ON (t.id_topic = ln.id_topic)
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)
		WHERE ln.id_topic IN (" . implode(',', $topics) . ")
			AND mem.is_activated = 1
			AND mem.notify_types < 4
			AND mem.notify_regularity < 2
		GROUP BY mem.id_member, ln.id_topic
		ORDER BY mem.lngfile", __FILE__, __LINE__);
	$sent = 0;
	while ($row = $smfFunc['db_fetch_assoc']($members))
	{
		if ($row['id_group'] != 1)
		{
			$allowed = explode(',', $row['member_groups']);
			$row['additional_groups'] = explode(',', $row['additional_groups']);
			$row['additional_groups'][] = $row['id_group'];
			$row['additional_groups'][] = $row['id_post_group'];

			if (count(array_intersect($allowed, $row['additional_groups'])) == 0)
				continue;
		}

		$needed_language = empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile'];
		if (empty($current_language) || $current_language != $needed_language)
			$current_language = loadLanguage('Post', $needed_language, false);

		$sent_this_time = false;
		// Now loop through all the messages to send.
		foreach ($topicData[$row['id_topic']] as $msg)
		{
			$replacements = array(
				'TOPICSUBJECT' => $topicData[$row['id_topic']]['subject'],
				'POSTERNAME' => un_htmlspecialchars($topicData[$row['id_topic']]['name']),
				'TOPICLINK' => $scripturl . '?topic=' . $row['id_topic'] . '.new;topicseen#new',
				'UNSUBSCRIBELINK' => $scripturl . '?action=notify;topic=' . $row['id_topic'] . '.0',
			);

			$message_type = 'notification_reply';
			// Do they want the body of the message sent too?
			if (!empty($row['notify_send_body']) && empty($modSettings['disallow_sendBody']))
			{
				$message_type .= '_body';
				$replacements['BODY'] = $topicData[$row['id_topic']]['body'];
			}
			if (!empty($row['notify_regularity']))
				$message_type .= '_once';

			// Send only if once is off or it's on and it hasn't been sent.
			if (empty($row['notify_regularity']) || (empty($row['sent']) && !$sent_this_time))
			{
				$emaildata = loadEmailTemplate($message_type, $replacements);
				sendmail($row['email_address'], $emaildata['subject'], $emaildata['body'], null, 'm' . $topicData[$row['id_topic']]['last_id']);
				$sent++;
			}

			$sent_this_time = true;
		}
	}
	$smfFunc['db_free_result']($members);

	if (isset($current_language) && $current_language != $user_info['language'])
		loadLanguage('Post');

	// Sent!
	if (!empty($sent))
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}log_notify
			SET sent = 1
			WHERE id_topic IN (" . implode(',', $topics) . ")
				AND id_member != $user_info[id]", __FILE__, __LINE__);
}

// Update the last message in a board, and its parents.
function updateLastMessages($setboards, $id_msg = 0)
{
	global $db_prefix, $board_info, $board, $modSettings, $smfFunc;

	// Please - let's be sane.
	if (empty($setboards))
		return false;

	if (!is_array($setboards))
		$setboards = array($setboards);

	// If we don't know the id_msg we need to find it.
	if (!$id_msg)
	{
		// Find the latest message on this board (highest id_msg.)
		$request = $smfFunc['db_query']('', "
			SELECT id_board, MAX(id_last_msg) AS id_msg
			FROM {$db_prefix}topics
			WHERE id_board IN (" . implode(', ', $setboards) . ")
				AND approved = 1
			GROUP BY id_board", __FILE__, __LINE__);
		$lastMsg = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$lastMsg[$row['id_board']] = $row['id_msg'];
		$smfFunc['db_free_result']($request);
	}
	else
	{
		foreach ($setboards as $id_board)
			$lastMsg[$id_board] = $id_msg;
	}

	$parent_boards = array();
	// Keep track of last modified dates.
	$lastModified = $lastMsg;
	// Get all the child boards for the parents, if they have some...
	foreach ($setboards as $id_board)
	{
		if (!isset($lastMsg[$id_board]))
		{
			$lastMsg[$id_board] = 0;
			$lastModified[$id_board] = 0;
		}

		if (!empty($board) && $id_board == $board)
			$parents = $board_info['parent_boards'];
		else
			$parents = getBoardParents($id_board);

		// Ignore any parents on the top child level.
		//!!! Why?
		foreach ($parents as $id => $parent)
		{
			if ($parent['level'] != 0)
			{
				// If we're already doing this one as a board, is this a higher last modified?
				if (isset($lastModified[$id]) && $lastModified[$id_board] > $lastModified[$id])
					$lastModified[$id] = $lastModified[$id_board];
				elseif (!isset($lastModified[$id]) && (!isset($parent_boards[$id]) || $parent_boards[$id] < $lastModified[$id_board]))
					$parent_boards[$id] = $lastModified[$id_board];
			}
		}
	}

	// Note to help understand what is happening here. For parents we update the timestamp of the last message for determining
	// whether there are child boards which have not been read. For the boards themselves we update both this and id_last_msg.

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
		if (!isset($board_updates[$msg . '-' . $lastModified[$id]]))
			$board_updates[$msg . '-' . $lastModified[$id]] = array(
				'id' => $msg,
				'updated' => $lastModified[$id],
				'boards' => array($id)
			);

		else
			$board_updates[$msg . '-' . $lastModified[$id]]['boards'][] = $id;
	}

	// Now commit the changes!
	foreach ($parent_updates as $id_msg => $boards)
	{
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}boards
			SET id_msg_updated = $id_msg
			WHERE id_board IN (" . implode(',', $boards) . ")
				AND id_msg_updated < $id_msg", __FILE__, __LINE__);
	}
	foreach ($board_updates as $board_data)
	{
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}boards
			SET id_last_msg = $board_data[id], id_msg_updated = $board_data[updated]
			WHERE id_board IN (" . implode(',', $board_data['boards']) . ")", __FILE__, __LINE__);
	}
}

// This simple function gets a list of all administrators and sends them an email to let them know a new member has joined.
function adminNotify($type, $memberID, $member_name = null)
{
	global $txt, $db_prefix, $modSettings, $language, $scripturl, $user_info, $context, $smfFunc;

	// If the setting isn't enabled then just exit.
	if (empty($modSettings['notify_new_registration']))
		return;

	if ($member_name == null)
	{
		// Get the new user's name....
		$request = $smfFunc['db_query']('', "
			SELECT real_name
			FROM {$db_prefix}members
			WHERE id_member = $memberID
			LIMIT 1", __FILE__, __LINE__);
		list ($member_name) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
	}

	$toNotify = array();
	$groups = array();

	// All membergroups who can approve members.
	$request = $smfFunc['db_query']('', "
		SELECT id_group
		FROM {$db_prefix}permissions
		WHERE permission = 'moderate_forum'
			AND add_deny = 1
			AND id_group != 0", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$groups[] = $row['id_group'];
	$smfFunc['db_free_result']($request);

	// Add administrators too...
	$groups[] = 1;
	$groups = array_unique($groups);

	// Get a list of all members who have ability to approve accounts - these are the people who we inform.
	$request = $smfFunc['db_query']('', "
		SELECT id_member, lngfile, email_address
		FROM {$db_prefix}members
		WHERE (id_group IN (" . implode(', ', $groups) . ") OR FIND_IN_SET(" . implode(', additional_groups) OR FIND_IN_SET(', $groups) . ", additional_groups))
			AND notify_types != 4
		ORDER BY lngfile", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$replacements = array(
			'USERNAME' => $member_name,
			'PROFILELINK' => $scripturl . '?action=profile;u=' . $memberID
		);
		$emailtype = 'admin_notify';

		// If they need to be approved add more info...
		if ($type == 'approval')
		{
			$replacements['APPROVALLINK'] = $scripturl . '?action=admin;area=viewmembers;sa=browse;type=approve';
			$emailtype .= '_approval';
		}

		$emaildata = loadEmailTemplate($emailtype, $replacements, empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile']);

		// And do the actual sending...
		sendmail($row['email_address'], $emaildata['subject'], $emaildata['body'], null, null, false, 0);
	}
	$smfFunc['db_free_result']($request);

	if (isset($current_language) && $current_language != $user_info['language'])
		loadLanguage('Login');
}

function loadEmailTemplate($template, $replacements = array(), $lang = '', $loadLang = true)
{
	global $txt, $mbname, $scripturl, $settings, $user_info;

	// First things first, load up the email templates language file, if we need to.
	if ($loadLang)
		loadLanguage('EmailTemplates', $lang);

	if (!isset($txt['emails'][$template]))
		fatal_lang_error('email_no_template', 'template', array($template));

	$ret = array(
		'subject' => $txt['emails'][$template]['subject'],
		'body' => $txt['emails'][$template]['body'],
	);


	// Add in the default replacements.
	$replacements += array(
		'FORUMNAME' => $mbname,
		'SCRIPTURL' => $scripturl,
		'THEMEURL' => $settings['theme_url'],
		'IMAGESURL' => $settings['images_url'],
		'DEFAULT_THEMEURL' => $settings['default_theme_url'],
		'REGARDS' => $txt['regards_team'],
	);

	// Split the replacements up into two arrays, for use with str_replace
	$find = array();
	$replace = array();

	foreach ($replacements as $f => $r)
	{
		$find[] = '{' . $f . '}';
		$replace[] = $r;
	}

	// Do the variable replacements.
	$ret['subject'] = str_replace($find, $replace, $ret['subject']);
	$ret['body'] = str_replace($find, $replace, $ret['body']);

	// Now deal with the {USER.variable} items.
	$ret['subject'] = preg_replace_callback('~{USER.([^}]+)}~', 'user_info_callback', $ret['subject']);
	$ret['body'] = preg_replace_callback('~{USER.([^}]+)}~', 'user_info_callback', $ret['body']);

	// Finally return the email to the caller so they can send it out.
	return $ret;
}

function user_info_callback($matches)
{
	global $user_info;
	if (empty($matches[1]))
		return '';

	$use_ref = true;
	$ref = &$user_info;

	foreach (explode('.', $matches[1]) as $index)
	{
		if ($use_ref && isset($ref[$index]))
			$ref = &$ref[$index];
		else
		{
			$use_ref = false;
			break;
		}
	}

	return $use_ref ? $ref : $matches[0];
}
?>