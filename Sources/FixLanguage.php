<?php
/******************************************************************************
* FixLanguage.php                                                             *
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

//!!! No longer the case!
/* This file is used during the development of SMF 2.0 to keep track of text key changes. It will be deleted
   before distribution and it's only purpose is to ensure people using a non-default language are not left
   with errors. Eventually these key changes will form part of the translator for 2.0.

   Note this file is included from loadLanguage, and will take some processing power I'm afraid. */

// old_key => new_key
$txtChanges = array(
	'Admin' => array(
		4 => 'admin_boards',
		6 => 'admin_newsletters',
		7 => 'admin_news',
		8 => 'admin_groups',
		9 => 'admin_members',
		135 => 'admin_censored_words',
		207 => 'admin_reserved_names',
		'attachment_mode' => 'attachmentEnable',
		'attachment_mode_deactivate' => 'attachmentEnable_deactivate',
		'attachment_mode_enable_all' => 'attachmentEnable_enable_all',
		'attachment_mode_disable_new' => 'attachmentEnable_disable_new',
	),
	'Help' => array(
		'attachmentEnable' => 'attachment_manager_settings',
		'avatar_allow_server_stored' => 'avatar_server_stored',
		'avatar_allow_external_url' => 'avatar_external',
		'avatar_allow_upload' => 'avatar_upload',
		'default_personalText' => 'default_personal_text',
		'allow_hideEmail' => 'allow_hide_email',
	),
	'index' => array(
		2 => 'admin',
		10 => 'save',
		17 => 'modify',
		18 => 'forum_index',
		19 => 'members',
		20 => 'board_name',
		21 => 'posts',
		22 => 'last_post',
		24 => 'no_subject',
		26 => 'posts',
		27 => 'view_profile',
		28 => 'guest_title',
		29 => 'author',
		30 => 'on',
		31 => 'remove',
		33 => 'start_new_topic',
		34 => 'login',
		35 => 'username',
		36 => 'password',
		40 => 'username_no_exist',
		62 => 'board_moderator',
		63 => 'remove_topic',
		64 => 'topics',
		66 => 'modify_msg',
		68 => 'name',
		69 => 'email',
		70 => 'subject',
		72 => 'message',
		79 => 'profile',
		81 => 'choose_pass',
		82 => 'verify_pass',
		87 => 'position',
		92 => 'profile_of',
		94 => 'total',
		95 => 'posts_made',
		96 => 'website',
		97 => 'register',
		101 => 'message_index',
		102 => 'news',
		103 => 'home',
		104 => 'lock_unlock',
		105 => 'post',
		106 => 'error_occured',
		107 => 'at',
		108 => 'logout',
		109 => 'started_by',
		110 => 'replies',
		111 => 'last_post',
		114 => 'admin_login',
		118 => 'topic',
		119 => 'help',
		121 => 'remove_message',
		125 => 'notify',
		126 => 'notify_request',
		130 => 'regards_team',
		131 => 'notify_replies',
		132 => 'move_topic',
		133 => 'move_to',
		139 => 'pages',
		140 => 'users_active',
		144 => 'personal_messages',
		145 => 'reply_quote',
		146 => 'reply',
		151 => 'msg_alert_none',
		152 => 'msg_alert_you_have',
		153 => 'msg_alert_messages',
		154 => 'remove_message',
		158 => 'online_users',
		159 => 'personal_message',
		160 => 'jump_to',
		161 => 'go',
		162 => 'are_sure_remove_topic',
		163 => 'yes',
		164 => 'no',
		166 => 'search_results',
		167 => 'search_end_results',
		170 => 'search_no_results',
		176 => 'search_on',
		182 => 'search',
		190 => 'all',
		193 => 'back',
		194 => 'password_reminder',
		195 => 'topic_started',
		196 => 'title',
		197 => 'post_by',
		200 => 'memberlist_searchable',
		201 => 'welcome_member',
		208 => 'admin_center',
		211 => 'last_edit',
		212 => 'notify_deactivate',
		214 => 'recent_posts',
		227 => 'location',
		231 => 'gender',
		233 => 'date_registered',
		234 => 'recent_view',
		235 => 'recent_updated',
		238 => 'male',
		239 => 'female',
		240 => 'error_invalid_characters_username',
		247 => 'welmsg_hey',
		248 => 'welmsg_welcome',
		249 => 'welmsg_please',
		250 => 'welmsg_back',
		251 => 'select_destination',
		279 => 'posted_by',
		287 => 'icon_smiley',
		288 => 'icon_angry',
		289 => 'icon_cheesy',
		290 => 'icon_laugh',
		291 => 'icon_sad',
		292 => 'icon_wink',
		293 => 'icon_grin',
		294 => 'icon_shocked',
		295 => 'icon_cool',
		296 => 'icon_huh',
		298 => 'moderator',
		299 => 'moderators',
		300 => 'mark_board_read',
		301 => 'views',
		302 => 'new',
		303 => 'view_all_members',
		305 => 'view',
		// This removes this entry.
		307 => 'email',
		315 => 'forgot_your_password',
		450 => 'icon_rolleyes',
		451 => 'icon_tongue',
		454 => 'hot_topics',
		455 => 'very_hot_topics',
		526 => 'icon_embarrassed',
		527 => 'icon_lips',
		528 => 'icon_undecided',
		529 => 'icon_kiss',
		530 => 'icon_cry',
		685 => 'info_center_title',
		'calendar23' => 'calendar_post_event',
		'smf240' => 'quote',
		'smf251' => 'split',
		'smf252' => 'merge',
		'MSN' => 'msn',
	),
	'Login' => array(
		635 => 'login_below',
		636 => 'login_or_register',
		637 => 'login_with_forum',
	),
	'ManageSmileys' => array(
		'smiley_sets_enable' => 'setting_smiley_sets_enable',
		'smiley_sets_base_url' => 'setting_smileys_url',
		'smiley_sets_base_dir' => 'setting_smileys_dir',
		'smileys_enable' => 'setting_smiley_enable',
		'icons_enable_customized' => 'setting_messageIcons_enable',
		'icons_enable_customized_note' => 'setting_messageIcons_enable_note',
	),
	'ModSettings' => array(
		'default_personalText' => 'default_personal_text',
		'allow_hideEmail' => 'allow_hide_email',
	),
	'Post' => array(
		130 => 'regards_team',
	),
	'Reports' => array(
		'member_group_minPosts' => 'member_group_min_posts',
		'member_group_maxMessages' => 'member_group_max_messages',
	),
);

function applyTxtFixes()
{
	global $txtChanges, $txt, $helptxt;

	foreach ($txtChanges as $key => $file)
		foreach ($file as $old => $new)
		{
			if ($key == 'Help' && isset($helptxt[$old]))
				$helptxt[$new] = $helptxt[$old];
			elseif (isset($txt[$old]))
				$txt[$new] = $txt[$old];
			elseif (isset($txt[$new]) && !isset($txt[$old]))
				$txt[$old] = $txt[$new];
		}
}

// Fix the formatting of a legacy file
function fixLanguageFile($filename, $type, $lang, $test = false)
{
	global $txtChanges;

	if (!file_exists($filename))
		return -1;

	$edit_count = -1;

	// Load the file.
	$fileContents = implode('', file($filename));

	// The warning for editing files direct?
	if ($type != 'index' && $type != 'Install' && preg_match('~//\sVersion:[\s\d\w\.]*;\s*' . $type . '\s*//\s[\w\d\s!\.&;]*index\.' . $lang . '\.php\.~', $fileContents, $matches) == false)
	{
		$fileContents = preg_replace('~(//\sVersion:[\s\d\w\.]*;\s*' . $type . '\s*)~', "$1// Important! Before editing these language files please read the text at the topic of index.$lang.php.\n\n", $fileContents);
		$edit_count = 0;
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

		$edit_count = 0;
	}
	// Fix up the help file with existing indexes.
	if ($type == 'Help' && preg_match('~\\t\{\$~', $fileContents))
	{
		$fileContents = preg_replace('~\\t\{\$~', "\t{" . '\\\\' . "$", $fileContents);
		$edit_count = 0;
	}
	// Remove double quotes where easy.
	if ($type != 'Install' && preg_match('~"\\\\n"~', $fileContents, $matches))
	{
		$fileContents = preg_replace('~"\\\\n"~', '\'\\\\\\\\n\'', $fileContents);
		// Fix for the comment.
		$fileContents = strtr($fileContents, array('i.e. \'\\\\n\'' => 'i.e. "\\n"'));
		$edit_count = 0;
	}
	// More double quotes
	if ($type != 'Install' && preg_match('~"\\\\n\\\\n"~', $fileContents, $matches))
	{
		$fileContents = preg_replace('~"\\\\n\\\\n"~', '\'\\\\\\\\n\\\\\\\\n\'', $fileContents);
		// Fix for the comment.
		$fileContents = strtr($fileContents, array('i.e. \'\\\\n\\\\n\'' => 'i.e. "\\n\\n"'));
		$edit_count = 0;
	}
	// More silly amounts of joins.
	if ($type != 'Install' && preg_match('~\' \. \'~', $fileContents, $matches))
	{
		$fileContents = preg_replace('~\' \. \'~', '', $fileContents);
		$edit_count = 0;
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
		$edit_count = 0;
	}
	// And any double arrays.
	if ($type != 'Install' && preg_match('~\' \. \$(\w*)\[\'?([\d\w]*)\'?\] \. \'~', $fileContents))
	{
		$fileContents = preg_replace('~\' \. \$(\w*)\[\'?([\d\w]*)\'?\] \. \'~', "%s", $fileContents);
		$edit_count = 0;
	}
	// Do the same for ones which are only half opened.
	if ($type != 'Install' && preg_match('~\$(\w*) \. \'~', $fileContents))
	{
		$fileContents = preg_replace('~\$(\w*) \. \'~', "'%s", $fileContents);
		$edit_count = 0;
	}
	// And any double arrays.
	if ($type != 'Install' && preg_match('~\$(\w*)\[\'?([\d\w]*)\'?\] \. \'~', $fileContents))
	{
		$fileContents = preg_replace('~\$(\w*)\[\'?([\d\w]*)\'?\] \. \'~', "'%s", $fileContents);
		$edit_count = 0;
	}
	// Put back in any variables.
	if ($type != 'Install' && $type != 'Help' && preg_match('~#(context|settings|txt|boardurl|scripturl)~', $fileContents, $match))
	{
		$fileContents = preg_replace('~#(context|settings|txt|boardurl|scripturl)~', "$$1", $fileContents);
	}

	if (isset($txtChanges[$type]))
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
		}
 	}

	if (!empty($findArray))
	{
		if ($edit_count == -1)
			$edit_count = 0;
		$edit_count += count($findArray);

		$fileContents = str_replace($findArray, $replaceArray, $fileContents);
	}

	// Need no edits at all?
	if ($edit_count == -1)
		return -1;

	// Making some changes?
	if (!$test)
	{
		$fp = fopen($filename, 'w');
		fwrite($fp, $fileContents);
		fclose($fp);
	}

	return $edit_count;
}

// Fix a legacy template.
function fixTemplateFile($filename, $test = false)
{
	global $txtChanges;

	if (!file_exists($filename))
		return -1;

	$edit_count = -1;

	// Load the file.
	$fileContents = implode('', file($filename));
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

	// Finally, some potential sprintf changes....
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
	// Make sure we note we've changed something...
	if (strlen($fileContents) != $before)
		$findArray[] = 1;

	if (!empty($findArray))
	{
		if ($edit_count == -1)
			$edit_count = 0;
		$edit_count += count($findArray);

		$fileContents = str_replace($findArray, $replaceArray, $fileContents);
	}

	// Making those changes?
	if (!$test)
	{
		$fp = fopen($filename, 'w');
		fwrite($fp, $fileContents);
		fclose($fp);
	}

	return $edit_count;
}

?>
