<?php

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
	'Post' => array(
		130 => 'regards_team',
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

?>