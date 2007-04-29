/* ATTENTION: You don't need to run or use this file!  The upgrade.php script does everything for you! */

/******************************************************************************/
--- Changing column names.
/******************************************************************************/

---# Renaming table columns.
---{
// The array holding all the changes.
$nameChanges = array(
	'admin_info_files' => array(
		'ID_FILE' => 'ID_FILE id_file tinyint(4) unsigned NOT NULL auto_increment',
	),
	'approval_queue' => array(
		'ID_MSG' => 'ID_MSG id_msg int(10) unsigned NOT NULL default \'0\'',
		'ID_ATTACH' => 'ID_ATTACH id_attach int(10) unsigned NOT NULL default \'0\'',
		'ID_EVENT' => 'ID_EVENT id_event smallint(5) unsigned NOT NULL default \'0\'',
		'attachmentType' => 'attachmentType attachment_type tinyint(3) unsigned NOT NULL default \'0\'',
	),
	'attachments' => array(
		'ID_ATTACH' => 'ID_ATTACH id_attach int(10) unsigned NOT NULL auto_increment',
		'ID_THUMB' => 'ID_THUMB id_thumb int(10) unsigned NOT NULL default \'0\'',
		'ID_MSG' => 'ID_MSG id_msg int(10) unsigned NOT NULL default \'0\'',
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'attachmentType' => 'attachmentType attachment_type tinyint(3) unsigned NOT NULL default \'0\'',
	),
	'ban_groups' => array(
		'ID_BAN_GROUP' => 'ID_BAN_GROUP id_ban_group mediumint(8) unsigned NOT NULL auto_increment',
	),
	'ban_items' => array(
		'ID_BAN' => 'ID_BAN id_ban mediumint(8) unsigned NOT NULL auto_increment',
		'ID_BAN_GROUP' => 'ID_BAN_GROUP id_ban_group smallint(5) unsigned NOT NULL default \'0\'',
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
	),
	'board_permissions' => array(
		'ID_GROUP' => 'ID_GROUP id_group smallint(5) NOT NULL default \'0\'',
		'ID_PROFILE' => 'ID_PROFILE id_profile smallint(5) NOT NULL default \'0\'',
		'addDeny' => 'addDeny add_deny tinyint(4) NOT NULL default \'1\'',
	),
	'boards' => array(
		'ID_BOARD' => 'ID_BOARD id_board smallint(5) unsigned NOT NULL auto_increment',
		'ID_CAT' => 'ID_CAT id_cat tinyint(4) unsigned NOT NULL default \'0\'',
		'childLevel' => 'childLevel child_level tinyint(4) unsigned NOT NULL default \'0\'',
		'ID_PARENT' => 'ID_PARENT id_parent smallint(5) unsigned NOT NULL default \'0\'',
		'boardOrder' => 'boardOrder board_order smallint(5) NOT NULL default \'0\'',
		'ID_LAST_MSG' => 'ID_LAST_MSG id_last_msg int(10) unsigned NOT NULL default \'0\'',
		'ID_MSG_UPDATED' => 'ID_MSG_UPDATED id_msg_updated int(10) unsigned NOT NULL default \'0\'',
		'memberGroups' => 'memberGroups member_groups varchar(255) NOT NULL default \'-1,0\'',
		'ID_PROFILE' => 'ID_PROFILE id_profile smallint(5) unsigned NOT NULL default \'1\'',
		'numTopics' => 'numTopics num_topics mediumint(8) unsigned NOT NULL default \'0\'',
		'numPosts' => 'numPosts num_posts mediumint(8) unsigned NOT NULL default \'0\'',
		'countPosts' => 'countPosts count_posts tinyint(4) NOT NULL default \'0\'',
		'ID_THEME' => 'ID_THEME id_theme tinyint(4) unsigned NOT NULL default \'0\'',
		'unapprovedPosts' => 'unapprovedPosts unapproved_posts smallint(5) NOT NULL default \'0\'',
		'unapprovedTopics' => 'unapprovedTopics unapproved_topics smallint(5) NOT NULL default \'0\'',
	),
	'calendar' => array(
		'ID_EVENT' => 'ID_EVENT id_event smallint(5) unsigned NOT NULL auto_increment',
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_BOARD' => 'ID_BOARD id_board smallint(5) unsigned NOT NULL default \'0\'',
		'ID_TOPIC' => 'ID_TOPIC id_topic mediumint(8) unsigned NOT NULL default \'0\'',
		'startDate' => 'startDate start_date date NOT NULL default \'0001-01-01\'',
		'endDate' => 'endDate end_date date NOT NULL default \'0001-01-01\'',
	),
	'calendar_holidays' => array(
		'ID_HOLIDAY' => 'ID_HOLIDAY id_holiday smallint(5) unsigned NOT NULL auto_increment',
		'eventDate' => 'eventDate event_date date NOT NULL default \'0001-01-01\'',
	),
	'categories' => array(
		'ID_CAT' => 'ID_CAT id_cat tinyint(4) unsigned NOT NULL auto_increment',
		'catOrder' => 'catOrder cat_order tinyint(4) NOT NULL default \'0\'',
		'canCollapse' => 'canCollapse can_collapse tinyint(1) NOT NULL default \'1\'',
	),
	'collapsed_categories' => array(
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_CAT' => 'ID_CAT id_cat tinyint(4) unsigned NOT NULL auto_increment',
	),
	'custom_fields' => array(
		'ID_FIELD' => 'ID_FIELD id_field smallint(5) NOT NULL auto_increment',
		'colName' => 'colName col_name varchar(12) NOT NULL default \'\'',
		'fieldName' => 'fieldName field_name varchar(40) NOT NULL default \'\'',
		'fieldDesc' => 'fieldDesc field_desc tinytext NOT NULL',
		'fieldType' => 'fieldType field_type varchar(8) NOT NULL default \'text\'',
		'fieldLength' => 'fieldLength field_length smallint(5) NOT NULL default \'255\'',
		'fieldOptions' => 'fieldOptions field_options tinytext NOT NULL',
		'showReg' => 'showReg show_reg tinyint(3) NOT NULL default \'0\'',
		'showDisplay' => 'showDisplay show_display tinyint(3) NOT NULL default \'0\'',
		'showProfile' => 'showProfile show_profile varchar(20) NOT NULL default \'forumProfile\'',
		'defaultValue' => 'defaultValue default_value varchar(8) NOT NULL default \'0\'',
	),
	'group_moderators' => array(
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_GROUP' => 'ID_GROUP id_group smallint(5) unsigned NOT NULL default \'0\'',
	),
	'log_actions' => array(
		'ID_ACTION' => 'ID_ACTION id_action int(10) unsigned NOT NULL auto_increment',
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'logTime' => 'logTime log_time int(10) unsigned NOT NULL default \'0\'',
		'ID_MSG' => 'ID_MSG id_msg int(10) unsigned NOT NULL default \'0\'',
		'ID_TOPIC' => 'ID_TOPIC id_topic mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_BOARD' => 'ID_BOARD id_board smallint(5) unsigned NOT NULL default \'0\'',
	),
	'log_activity' => array(
		'mostOn' => 'mostOn most_on smallint(5) unsigned NOT NULL default \'0\'',
	),
	'log_banned' => array(
		'ID_BAN_LOG' => 'ID_BAN_LOG id_ban_log mediumint(8) unsigned NOT NULL auto_increment',
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'logTime' => 'logTime log_time int(10) unsigned NOT NULL default \'0\'',
	),
	'log_boards' => array(
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_MSG' => 'ID_MSG id_msg int(10) unsigned NOT NULL default \'0\'',
		'ID_BOARD' => 'ID_BOARD id_board smallint(5) unsigned NOT NULL default \'0\'',
	),
	'log_digest' => array(
		'ID_TOPIC' => 'ID_TOPIC id_topic mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_MSG' => 'ID_MSG id_msg int(10) unsigned NOT NULL default \'0\'',
	),
	'log_errors' => array(
		'ID_ERROR' => 'ID_ERROR id_error mediumint(8) unsigned NOT NULL auto_increment',
		'logTime' => 'logTime log_time int(10) unsigned NOT NULL default \'0\'',
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'errorType' => 'errorType error_type char(15) NOT NULL default \'general\'',
	),
	'log_floodcontrol' => array(
		'logTime' => 'logTime log_time int(10) unsigned NOT NULL default \'0\'',
	),
	'log_group_requests' => array(
		'ID_REQUEST' => 'ID_REQUEST id_request mediumint(8) unsigned NOT NULL auto_increment',
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_GROUP' => 'ID_GROUP id_group smallint(5) unsigned NOT NULL default \'0\'',
	),
	'log_karma' => array(
		'ID_TARGET' => 'ID_TARGET id_target mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_EXECUTOR' => 'ID_EXECUTOR id_executor mediumint(8) unsigned NOT NULL default \'0\'',
		'logTime' => 'logTime log_time int(10) unsigned NOT NULL default \'0\'',
	),
	'log_mark_read' => array(
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_MSG' => 'ID_MSG id_msg int(10) unsigned NOT NULL default \'0\'',
		'ID_BOARD' => 'ID_BOARD id_board smallint(5) unsigned NOT NULL default \'0\'',
	),
	'log_notify' => array(
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_TOPIC' => 'ID_TOPIC id_topic mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_BOARD' => 'ID_BOARD id_board smallint(5) unsigned NOT NULL default \'0\'',
	),
	'log_packages' => array(
		'ID_INSTALL' => 'ID_INSTALL id_install int(10) NOT NULL auto_increment',
		'ID_MEMBER_INSTALLED' => 'ID_MEMBER_INSTALLED id_member_installed mediumint(8) NOT NULL',
		'ID_MEMBER_REMOVED' => 'ID_MEMBER_REMOVED id_member_removed mediumint(8) NOT NULL default \'0\'',
	),
	'log_polls' => array(
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_CHOICE' => 'ID_CHOICE id_choice tinyint(3) unsigned NOT NULL default \'0\'',
		'ID_POLL' => 'ID_POLL id_poll mediumint(8) unsigned NOT NULL default \'0\'',
	),
	'log_reported' => array(
		'ID_REPORT' => 'ID_REPORT id_report mediumint(8) unsigned NOT NULL auto_increment',
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_MSG' => 'ID_MSG id_msg int(10) unsigned NOT NULL default \'0\'',
		'ID_TOPIC' => 'ID_TOPIC id_topic mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_BOARD' => 'ID_BOARD id_board smallint(5) unsigned NOT NULL default \'0\'',
	),
	'log_reported_comments' => array(
		'ID_COMMENT' => 'ID_COMMENT id_comment mediumint(8) unsigned NOT NULL auto_increment',
		'ID_REPORT' => 'ID_REPORT id_report mediumint(8) NOT NULL',
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
	),
	'log_scheduled_tasks' => array(
		'ID_LOG' => 'ID_LOG id_log mediumint(8) NOT NULL auto_increment',
		'ID_TASK' => 'ID_TASK id_task smallint(5) NOT NULL',
		'timeRun' => 'timeRun time_run int(10) NOT NULL',
		'timeTaken' => 'timeTaken time_taken float NOT NULL default \'0\'',
	),
	'log_search_results' => array(
		'ID_TOPIC' => 'ID_TOPIC id_topic mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_MSG' => 'ID_MSG id_msg int(10) unsigned NOT NULL default \'0\'',
	),
	'log_search_messages' => array(
		'ID_SEARCH' => 'ID_SEARCH id_search tinyint(3) unsigned NOT NULL default \'0\'',
		'ID_MSG' => 'ID_MSG id_msg int(10) unsigned NOT NULL default \'0\'',
	),
	'log_search_results' => array(
		'ID_SEARCH' => 'ID_SEARCH id_search tinyint(3) unsigned NOT NULL default \'0\'',
	),
	'log_search_subjects' => array(
		'ID_TOPIC' => 'ID_TOPIC id_topic mediumint(8) unsigned NOT NULL default \'0\'',
	),
	'log_search_topics' => array(
		'ID_SEARCH' => 'ID_SEARCH id_search tinyint(3) unsigned NOT NULL default \'0\'',
		'ID_TOPIC' => 'ID_TOPIC id_topic mediumint(8) unsigned NOT NULL default \'0\'',
	),
	'log_topics' => array(
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_MSG' => 'ID_MSG id_msg int(10) unsigned NOT NULL default \'0\'',
		'ID_TOPIC' => 'ID_TOPIC id_topic mediumint(8) unsigned NOT NULL default \'0\'',
	),
	'mail_queue' => array(
		'ID_MAIL' => 'ID_MAIL id_mail int(10) unsigned NOT NULL auto_increment',
	),
	'members' => array(
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL auto_increment',
		'memberName' => 'memberName member_name varchar(80) NOT NULL default \'\'',
		'dateRegistered' => 'dateRegistered date_registered int(10) unsigned NOT NULL default \'0\'',
		'ID_GROUP' => 'ID_GROUP id_group smallint(5) unsigned NOT NULL default \'0\'',
		'lastLogin' => 'lastLogin last_login int(10) unsigned NOT NULL default \'0\'',
		'realName' => 'realName real_name tinytext NOT NULL',
		'instantMessages' => 'instantMessages instant_messages smallint(5) NOT NULL default \'0\'',
		'unreadMessages' => 'unreadMessages unread_messages smallint(5) NOT NULL default \'0\'',
		'messageLabels' => 'messageLabels message_labels text NOT NULL',
		'emailAddress' => 'emailAddress email_address tinytext NOT NULL',
		'personalText' => 'personalText personal_text tinytext NOT NULL',
		'websiteTitle' => 'websiteTitle website_title tinytext NOT NULL',
		'websiteUrl' => 'websiteUrl website_url tinytext NOT NULL',
		'ICQ' => 'ICQ icq tinytext NOT NULL',
		'AIM' => 'AIM aim varchar(16) NOT NULL default \'\'',
		'YIM' => 'YIM yim varchar(32) NOT NULL default \'\'',
		'MSN' => 'MSN msn tinytext NOT NULL',
		'hideEmail' => 'hideEmail hide_email tinyint(4) NOT NULL default \'0\'',
		'showOnline' => 'showOnline show_online tinyint(4) NOT NULL default \'1\'',
		'timeFormat' => 'timeFormat time_format varchar(80) NOT NULL default \'\'',
		'timeOffset' => 'timeOffset time_offset float NOT NULL default \'0\'',
		'karmaBad' => 'karmaBad karma_bad smallint(5) unsigned NOT NULL default \'0\'',
		'karmaGood' => 'karmaGood karma_good smallint(5) unsigned NOT NULL default \'0\'',
		'notifyAnnouncements' => 'notifyAnnouncements notify_announcements tinyint(4) NOT NULL default \'1\'',
		'notifyRegularity' => 'notifyRegularity notify_regularity tinyint(4) NOT NULL default \'1\'',
		'notifySendBody' => 'notifySendBody notify_send_body tinyint(4) NOT NULL default \'0\'',
		'notifyTypes' => 'notifyTypes notify_types tinyint(4) NOT NULL default \'2\'',
		'memberIP' => 'memberIP member_ip tinytext NOT NULL',
		'secretQuestion' => 'secretQuestion secret_question tinytext NOT NULL',
		'secretAnswer' => 'secretAnswer secret_answer varchar(64) NOT NULL default \'\'',
		'ID_THEME' => 'ID_THEME id_theme tinyint(4) unsigned NOT NULL default \'0\'',
		'ID_MSG_LAST_VISIT' => 'ID_MSG_LAST_VISIT id_msg_last_visit int(10) unsigned NOT NULL default \'0\'',
		'additionalGroups' => 'additionalGroups additional_groups tinytext NOT NULL',
		'smileySet' => 'smileySet smiley_set varchar(48) NOT NULL default \'\'',
		'ID_POST_GROUP' => 'ID_POST_GROUP id_post_group smallint(5) unsigned NOT NULL default \'0\'',
		'totalTimeLoggedIn' => 'totalTimeLoggedIn total_time_logged_in int(10) unsigned NOT NULL default \'0\'',
		'passwordSalt' => 'passwordSalt password_salt varchar(5) NOT NULL default \'\'',
		'ignoreBoards' => 'ignoreBoards ignore_boards tinytext NOT NULL',
		'memberIP2' => 'memberIP2 member_ip2 tinytext NOT NULL',
	),
	'messages' => array(
		'ID_MSG' => 'ID_MSG id_msg int(10) unsigned NOT NULL auto_increment',
		'ID_TOPIC' => 'ID_TOPIC id_topic mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_BOARD' => 'ID_BOARD id_board smallint(5) unsigned NOT NULL default \'0\'',
		'posterTime' => 'posterTime poster_time int(10) unsigned NOT NULL default \'0\'',
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_MSG_MODIFIED' => 'ID_MSG_MODIFIED id_msg_modified int(10) unsigned NOT NULL default \'0\'',
		'posterName' => 'posterName poster_name tinytext NOT NULL',
		'posterEmail' => 'posterEmail poster_email tinytext NOT NULL',
		'posterIP' => 'posterIP poster_ip tinytext NOT NULL',
		'smileysEnabled' => 'smileysEnabled smileys_enabled tinyint(4) NOT NULL default \'1\'',
		'modifiedTime' => 'modifiedTime modified_time int(10) unsigned NOT NULL default \'0\'',
		'modifiedName' => 'modifiedName modified_name tinytext NOT NULL',
	),
	'membergroups' => array(
		'ID_GROUP' => 'ID_GROUP id_group smallint(5) unsigned NOT NULL auto_increment',
		'ID_PARENT' => 'ID_PARENT id_parent smallint(5) NOT NULL default \'-2\'',
		'groupName' => 'groupName group_name varchar(80) NOT NULL default \'\'',
		'onlineColor' => 'onlineColor online_color varchar(20) NOT NULL default \'\'',
		'minPosts' => 'minPosts min_posts mediumint(9) NOT NULL default \'-1\'',
		'maxMessages' => 'maxMessages max_messages smallint(5) unsigned NOT NULL default \'0\'',
		'groupType' => 'groupType group_type tinyint(3) NOT NULL default \'0\'',
	),
	'message_icons' => array(
		'ID_ICON' => 'ID_ICON id_icon smallint(5) unsigned NOT NULL auto_increment',
		'iconOrder' => 'iconOrder icon_order smallint(5) unsigned NOT NULL default \'0\'',
		'ID_BOARD' => 'ID_BOARD id_board smallint(5) unsigned NOT NULL default \'0\'',
	),
	'moderators' => array(
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_BOARD' => 'ID_BOARD id_board smallint(5) unsigned NOT NULL default \'0\'',
	),
	'package_servers' => array(
		'ID_SERVER' => 'ID_SERVER id_server smallint(5) unsigned NOT NULL auto_increment',
	),
	'personal_messages' => array(
		'ID_PM' => 'ID_PM id_pm int(10) unsigned NOT NULL auto_increment',
		'ID_MEMBER_FROM' => 'ID_MEMBER_FROM id_member_from mediumint(8) unsigned NOT NULL default \'0\'',
		'deletedBySender' => 'deletedBySender deleted_by_sender tinyint(3) unsigned NOT NULL default \'0\'',
		'fromName' => 'fromName from_name tinytext NOT NULL',
	),
	'permission_profiles' => array(
		'ID_PROFILE' => 'ID_PROFILE id_profile smallint(5) NOT NULL auto_increment',
		'ID_PARENT' => 'ID_PARENT id_parent smallint(5) unsigned NOT NULL default \'0\'',
	),
	'permissions' => array(
		'ID_GROUP' => 'ID_GROUP id_group smallint(5) NOT NULL default \'0\'',
		'addDeny' => 'addDeny add_deny tinyint(4) NOT NULL default \'1\'',
	),
	'pm_recipients' => array(
		'ID_PM' => 'ID_PM id_pm int(10) unsigned NOT NULL default \'0\'',
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
	),
	'polls' => array(
		'ID_POLL' => 'ID_POLL id_poll mediumint(8) unsigned NOT NULL auto_increment',
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) unsigned NOT NULL default \'0\'',
		'votingLocked' => 'votingLocked voting_locked tinyint(1) NOT NULL default \'0\'',
		'maxVotes' => 'maxVotes max_votes tinyint(3) unsigned NOT NULL default \'1\'',
		'expireTime' => 'expireTime expire_time int(10) unsigned NOT NULL default \'0\'',
		'hideResults' => 'hideResults hide_results tinyint(3) unsigned NOT NULL default \'0\'',
		'changeVote' => 'changeVote change_vote tinyint(3) unsigned NOT NULL default \'0\'',
		'posterName' => 'posterName poster_name tinytext NOT NULL',
	),
	'poll_choices' => array(
		'ID_CHOICE' => 'ID_CHOICE id_choice tinyint(3) unsigned NOT NULL default \'0\'',
		'ID_POLL' => 'ID_POLL id_poll mediumint(8) unsigned NOT NULL default \'0\'',
	),
	'scheduled_tasks' => array(
		'ID_TASK' => 'ID_TASK id_task smallint(5) NOT NULL auto_increment',
		'nextTime' => 'nextTime next_time int(10) NOT NULL',
		'timeRegularity' => 'timeRegularity time_regularity smallint(5) NOT NULL',
		'timeOffset' => 'timeOffset time_offset int(10) NOT NULL',
		'timeUnit' => 'timeUnit time_unit varchar(1) NOT NULL default \'h\'',
	),
	'smileys' => array(
		'ID_SMILEY' => 'ID_SMILEY id_smiley smallint(5) unsigned NOT NULL auto_increment',
		'smileyRow' => 'smileyRow smiley_row tinyint(4) unsigned NOT NULL default \'0\'',
		'smileyOrder' => 'smileyOrder smiley_order smallint(5) unsigned NOT NULL default \'0\'',
	),
	'themes' => array(
		'ID_MEMBER' => 'ID_MEMBER id_member mediumint(8) NOT NULL default \'0\'',
		'ID_THEME' => 'ID_THEME id_theme tinyint(4) unsigned NOT NULL default \'1\'',
	),
	'topics' => array(
		'ID_TOPIC' => 'ID_TOPIC id_topic mediumint(8) unsigned NOT NULL auto_increment',
		'isSticky' => 'isSticky is_sticky tinyint(4) NOT NULL default \'0\'',
		'ID_BOARD' => 'ID_BOARD id_board smallint(5) unsigned NOT NULL default \'0\'',
		'ID_FIRST_MSG' => 'ID_FIRST_MSG id_first_msg int(10) unsigned NOT NULL default \'0\'',
		'ID_LAST_MSG' => 'ID_LAST_MSG id_last_msg int(10) unsigned NOT NULL default \'0\'',
		'ID_MEMBER_STARTED' => 'ID_MEMBER_STARTED id_member_started mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_MEMBER_UPDATED' => 'ID_MEMBER_UPDATED id_member_updated mediumint(8) unsigned NOT NULL default \'0\'',
		'ID_POLL' => 'ID_POLL id_poll mediumint(8) unsigned NOT NULL default \'0\'',
		'numReplies' => 'numReplies num_replies int(10) unsigned NOT NULL default \'0\'',
		'numViews' => 'numViews num_views int(10) unsigned NOT NULL default \'0\'',
		'unapprovedPosts' => 'unapprovedPosts unapproved_posts smallint(5) NOT NULL default \'0\'',
	),
);

$_GET['ren_col'] = isset($_GET['ren_col']) ? (int) $_GET['ren_col'] : 0;
$step_progress['name'] = 'Renaming columns';
$step_progress['current'] = $_GET['ren_col'];
$step_progress['total'] = count($nameChanges);

$count = 0;
// Now do every table...
foreach ($nameChanges as $table_name => $table)
{
	// Already done this?
	$count++;
	if ($_GET['ren_col'] > $count)
		continue;
	$_GET['ren_col'] = $count;

	// Check the table exists!
	$request = upgrade_query("
		SHOW TABLES
		LIKE '{$db_prefix}$table_name'");
	if (mysql_num_rows($request) == 0)
	{
		mysql_free_result($request);
		continue;
	}
	mysql_free_result($request);

	// Check each column!
	$actualChanges = array();
	foreach ($table as $colname => $coldef)
	{
		$change = array(
			'table' => $table_name,
			'name' => $colname,
			'type' => 'column',
			'method' => 'change_remove',
			'text' => 'CHANGE ' . $coldef,
		);
		if (protected_alter($change, $substep, true) == false)
			$actualChanges[] = ' CHANGE COLUMN ' . $coldef;
	}

	// Do the query - if it needs doing.
	if (!empty($actualChanges))
	{
		$change = array(
			'table' => $table_name,
			'name' => 'na',
			'type' => 'table',
			'method' => 'full_change',
			'text' => implode(', ', $actualChanges),
		);

		// Here we go - hold on!
		protected_alter($change, $substep);
	}
	
	// Update where we are!
	$step_progress['current'] = $_GET['ren_col'];
}

// All done!
unset($_GET['ren_col']);
---}
---#

---# Converting "log_online"...
DROP TABLE IF EXISTS {$db_prefix}log_online;
CREATE TABLE {$db_prefix}log_online (
	session varchar(32) NOT NULL default '',
	log_time int(10) NOT NULL default '0',
	id_member mediumint(8) unsigned NOT NULL default '0',
	ip int(10) unsigned NOT NULL default '0',
	url text NOT NULL,
	PRIMARY KEY (session),
	KEY log_time (log_time),
	KEY id_member (id_member)
) TYPE=MyISAM{$db_collation};
---#

/******************************************************************************/
--- Adding new forum settings.
/******************************************************************************/

---# Resetting settings_updated.
REPLACE INTO {$db_prefix}settings
	(variable, value)
VALUES
	('settings_updated', '0'),
	('last_mod_report_action', '0'),
	('next_task_time', UNIX_TIMESTAMP());
---#

---# Changing stats settings.
---{
$request = upgrade_query("
	SELECT value
	FROM {$db_prefix}themes
	WHERE variable = 'show_sp1_info'");
if (mysql_num_rows($request) != 0)
{
	upgrade_query("
		UPDATE {$db_prefix}themes
		SET variable = 'show_stats_index'
		WHERE variable = 'show_sp1_info'");
}
upgrade_query("
	DELETE FROM {$db_prefix}themes
	WHERE variable = 'show_sp1_info'");
---}
---#

---# Changing visual verification setting.
---{
$request = upgrade_query("
	SELECT value
	FROM {$db_prefix}settings
	WHERE variable = 'disable_visual_verification'");
if (mysql_num_rows($request) != 0)
{
	list ($oldValue) = mysql_fetch_row($request);
	if ($oldValue != 0)
	{
		// We have changed the medium setting from SMF 1.1.2.
		if ($oldValue == 4)
			$oldValue = 5;

		upgrade_query("
			UPDATE {$db_prefix}settings
			SET variable = 'visual_verification_type'
				AND value = $oldValue
			WHERE variable = 'disable_visual_verification'");
	}
}
upgrade_query("
	DELETE FROM {$db_prefix}settings
	WHERE variable = 'disable_visual_verification'");
---}
---#

---# Changing default personal text setting.
UPDATE {$db_prefix}settings
SET variable = 'default_personal_text'
WHERE variable = 'default_personalText';

DELETE FROM {$db_prefix}settings
WHERE variable = 'default_personalText';
---#

---# Removing allow hide email setting.
DELETE FROM {$db_prefix}settings
WHERE variable = 'allow_hideEmail'
	OR variable = 'allow_hide_email';
---#

---# Ensuring stats index setting present...
INSERT IGNORE INTO {$db_prefix}themes
	(id_theme, variable, value)
VALUES
	(1, 'show_stats_index', '0');
---#

---# Replacing old calendar settings...
---{
// Only try it if one of the "new" settings doesn't yet exist.
if (!isset($modSettings['cal_showholidays']) || !isset($modSettings['cal_showbdays']) || !isset($modSettings['cal_showevents']))
{
	// Default to just the calendar setting.
	$modSettings['cal_showholidays'] = empty($modSettings['cal_showholidaysoncalendar']) ? 0 : 1;
	$modSettings['cal_showbdays'] = empty($modSettings['cal_showbdaysoncalendar']) ? 0 : 1;
	$modSettings['cal_showevents'] = empty($modSettings['cal_showeventsoncalendar']) ? 0 : 1;

	// Then take into account board index.
	if (!empty($modSettings['cal_showholidaysonindex']))
		$modSettings['cal_showholidays'] = $modSettings['cal_showholidays'] === 1 ? 2 : 3;
	if (!empty($modSettings['cal_showbdaysonindex']))
		$modSettings['cal_showbdays'] = $modSettings['cal_showbdays'] === 1 ? 2 : 3;
	if (!empty($modSettings['cal_showeventsonindex']))
		$modSettings['cal_showevents'] = $modSettings['cal_showevents'] === 1 ? 2 : 3;

	// Actually save the settings.
	upgrade_query("
		INSERT IGNORE INTO {$db_prefix}settings
			(variable, value)
		VALUES
			('cal_showholidays', $modSettings[cal_showholidays]),
			('cal_showbdays', $modSettings[cal_showbdays]),
			('cal_showevents', $modSettings[cal_showevents])");
}

---}
---#

---# Deleting old calendar settings...
	DELETE FROM {$db_prefix}settings
	WHERE VARIABLE IN ('cal_showholidaysonindex', 'cal_showbdaysonindex', 'cal_showeventsonindex',
		'cal_showholidaysoncalendar', 'cal_showbdaysoncalendar', 'cal_showeventsoncalendar',
		'cal_holidaycolor', 'cal_bdaycolor', 'cal_eventcolor');
---#

---# Adding advanced signature settings...
---{
if (empty($modSettings['signature_settings']))
{
	if (isset($modSettings['max_signatureLength']))
		$modSettings['signature_settings'] = '1,' . $modSettings['max_signatureLength'] . ',0,0,0,0,0,0:';
	else
		$modSettings['signature_settings'] = '1,300,0,0,0,0,0,0:';

	upgrade_query("
		INSERT IGNORE INTO {$db_prefix}settings
			(variable, value)
		VALUES
			('signature_settings', '$modSettings[signature_settings]')");

	upgrade_query("
		DELETE FROM {$db_prefix}settings
		WHERE variable = 'max_signatureLength'");
}
---}
---#

---# Updating spam protection settings.
---{
if (empty($modSettings['pm_spam_settings']))
{
	if (isset($modSettings['max_pm_recipients']))
		$modSettings['pm_spam_settings'] = $modSettings['max_pm_recipients'] . ',5,20';
	else
		$modSettings['pm_spam_settings'] = '10,5,20';
}
elseif (substr_count($modSettings['pm_spam_settings'], ',') == 1)
{
	$modSettings['pm_spam_settings'] .= ',20';
}

upgrade_query("
	INSERT IGNORE INTO {$db_prefix}settings
		(variable, value)
	VALUES
		('pm_spam_settings', '$modSettings[pm_spam_settings]')");

upgrade_query("
	DELETE FROM {$db_prefix}settings
	WHERE variable = 'max_pm_recipients'");
---}
---#

---# Adjusting timezone settings...
---{
	if (!isset($modSettings['default_timezone']) && function_exists('date_default_timezone_set'))
	{
		$server_offset = mktime(0, 0, 0, 1, 1, 1970);
		$timezone_id = 'Etc/GMT' . ($server_offset > 0 ? '+' : '') . ($server_offset / 3600);
		if (date_default_timezone_set($timezone_id))
			upgrade_query("
				REPLACE INTO {$db_prefix}settings
					(variable, value)
				VALUES
					('default_timezone', '$timezone_id')");
	}
---}
---#

---# Adding index to log_notify table...
ALTER TABLE {$db_prefix}log_notify
ADD INDEX id_topic (id_topic, id_member);
---#

/******************************************************************************/
--- Adding custom profile fields.
/******************************************************************************/

---# Creating "custom_fields" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}custom_fields (
	id_field smallint(5) NOT NULL auto_increment,
	col_name varchar(12) NOT NULL default '',
	field_name varchar(40) NOT NULL default '',
	field_desc tinytext NOT NULL,
	field_type varchar(8) NOT NULL default 'text',
	field_length smallint(5) NOT NULL default '255',
	field_options tinytext NOT NULL,
	mask tinytext NOT NULL,
	show_reg tinyint(3) NOT NULL default '0',
	show_display tinyint(3) NOT NULL default '0',
	show_profile varchar(20) NOT NULL default 'forumProfile',
	private tinyint(3) NOT NULL default '0',
	active tinyint(3) NOT NULL default '1',
	bbc tinyint(3) NOT NULL default '0',
	default_value varchar(8) NOT NULL default '0',
	PRIMARY KEY (id_field),
	UNIQUE col_name (col_name)
) TYPE=MyISAM{$db_collation};
---#

/******************************************************************************/
--- Adding email digest functionality.
/******************************************************************************/

---# Creating "log_digest" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}log_digest (
	id_topic mediumint(8) unsigned NOT NULL default '0',
	id_msg int(10) unsigned NOT NULL default '0',
	note_type varchar(10) NOT NULL default 'post',
	daily smallint(3) unsigned NOT NULL default '0',
	exclude mediumint(8) unsigned NOT NULL default '0'
) TYPE=MyISAM{$db_collation};
---#

---# Adding digest option to "members" table...
ALTER TABLE {$db_prefix}members
CHANGE COLUMN notifyOnce notify_regularity tinyint(4) unsigned NOT NULL default '1';
---#
/******************************************************************************/
--- Making changes to the package manager.
/******************************************************************************/

---# Creating "log_packages" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}log_packages (
	id_install int(10) NOT NULL auto_increment,
	filename tinytext NOT NULL,
	package_id tinytext NOT NULL,
	name tinytext NOT NULL,
	version tinytext NOT NULL,
	id_member_installed mediumint(8) NOT NULL,
	member_installed tinytext NOT NULL,
	time_installed int(10) NOT NULL default '0',
	id_member_removed mediumint(8) NOT NULL default '0',
	member_removed tinytext NOT NULL,
	time_removed int(10) NOT NULL default '0',
	install_state tinyint(3) NOT NULL default '1',
	failed_steps text NOT NULL,
	themes_installed tinytext NOT NULL,
	db_changes text NOT NULL,
	PRIMARY KEY (id_install),
	KEY filename (filename(15))
) TYPE=MyISAM{$db_collation};
---#

---# Adding extra "log_packages" columns...
ALTER TABLE {$db_prefix}log_packages
ADD db_changes text NOT NULL AFTER themes_installed;
---#

/******************************************************************************/
--- Creating mail queue functionality.
/******************************************************************************/

---# Creating "mail_queue" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}mail_queue (
	id_mail int(10) unsigned NOT NULL auto_increment,
	time_sent int(10) NOT NULL default '0',
	recipient tinytext NOT NULL,
	body text NOT NULL,
	subject tinytext NOT NULL,
	headers text NOT NULL,
	send_html tinyint(3) NOT NULL default '0',
	priority tinyint(3) NOT NULL default '1',
	PRIMARY KEY (id_mail),
	KEY time_sent (time_sent),
	KEY priority (priority)
) TYPE=MyISAM{$db_collation};
---#

---# Adding new mail queue settings...
---{
if (!isset($modSettings['mail_next_send']))
{
	upgrade_query("
		INSERT INTO {$db_prefix}settings
			(variable, value)
		VALUES
			('mail_next_send', '0'),
			('mail_recent', '0000000000|0')");
}
---}
---#

/******************************************************************************/
--- Creating moderation center tables.
/******************************************************************************/

---# Creating "log_reported" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}log_reported (
	id_report mediumint(8) unsigned NOT NULL auto_increment,
	id_msg int(10) unsigned NOT NULL default '0',
	id_topic mediumint(8) unsigned NOT NULL default '0',
	id_board smallint(5) unsigned NOT NULL default '0',
	id_member mediumint(8) unsigned NOT NULL default '0',
	membername tinytext NOT NULL,
	subject tinytext NOT NULL,
	body text NOT NULL,
	time_started int(10) NOT NULL default '0',
	time_updated int(10) NOT NULL default '0',
	num_reports mediumint(6) NOT NULL default '0',
	closed tinyint(3) NOT NULL default '0',
	ignore_all tinyint(3) NOT NULL default '0',
	PRIMARY KEY (id_report),
	KEY id_member (id_member),
	KEY id_topic (id_topic),
	KEY closed (closed),
	KEY time_started (time_started),
	KEY id_msg (id_msg)
) TYPE=MyISAM{$db_collation};
---#

---# Creating "log_reported_comments" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}log_reported_comments (
	id_comment mediumint(8) unsigned NOT NULL auto_increment,
	id_report mediumint(8) NOT NULL,
	id_member mediumint(8) NOT NULL,
	membername tinytext NOT NULL,
	comment tinytext NOT NULL,
	time_sent int(10) NOT NULL,
	PRIMARY KEY (id_comment),
	KEY id_report (id_report),
	KEY id_member (id_member),
	KEY time_sent (time_sent)
) TYPE=MyISAM{$db_collation};
---#

---# Adding moderator center permissions...
---{
// Don't do this twice!
if (@$modSettings['smfVersion'] < '2.0')
{
	// Try find people who probably should see the moderation center.
	$request = upgrade_query("
		SELECT id_group, add_deny, permission
		FROM {$db_prefix}permissions
		WHERE permission = 'calendar_edit_any'");
	$inserts = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$inserts[] = "($row[id_group], 'access_mod_center', $row[add_deny])";
	}
	mysql_free_result($request);

	if (!empty($inserts))
		upgrade_query("
			INSERT IGNORE INTO {$db_prefix}permissions
				(id_group, permission, add_deny)
			VALUES
				" . implode(',', $inserts));
}
---}
---#

---# Adding moderation center preferences...
ALTER TABLE {$db_prefix}members
ADD mod_prefs varchar(20) NOT NULL default '';
---#

/******************************************************************************/
--- Adding user warnings.
/******************************************************************************/

---# Creating member notices table...
CREATE TABLE IF NOT EXISTS {$db_prefix}log_member_notices (
	id_notice mediumint(8) unsigned NOT NULL auto_increment,
	subject tinytext NOT NULL,
	body text NOT NULL,
	PRIMARY KEY (id_notice)
) TYPE=MyISAM{$db_collation};
---#

---# Creating comments table...
CREATE TABLE IF NOT EXISTS {$db_prefix}log_comments (
	id_comment mediumint(8) unsigned NOT NULL auto_increment,
	id_member mediumint(8) unsigned NOT NULL default '0',
	member_name varchar(80) NOT NULL default '',
	comment_type varchar(8) NOT NULL default 'warning',
	id_recipient mediumint(8) unsigned NOT NULL default '0',
	recipient_name tinytext NOT NULL,
	log_time int(10) NOT NULL default '0',
	id_notice mediumint(8) unsigned NOT NULL default '0',
	counter tinyint(3) NOT NULL default '0',
	body text NOT NULL,
	PRIMARY KEY (id_comment),
	KEY id_recipient (id_recipient),
	KEY log_time (log_time),
	KEY comment_type (comment_type(8))
) TYPE=MyISAM{$db_collation};
---#

---# Adding user warning column...
ALTER TABLE {$db_prefix}members
ADD warning tinyint(4) NOT NULL default '0';

ALTER TABLE {$db_prefix}members
ADD INDEX warning (warning);
---#

---# Ensuring warning settings are present...
INSERT IGNORE INTO {$db_prefix}settings
	(variable, value)
VALUES
	('warning_settings', '1,20,0'),
	('warning_watch', '10'),
	('warning_moderate', '35'),
	('warning_mute', '60');
---#

/******************************************************************************/
--- Enhancing membergroups.
/******************************************************************************/

---# Creating "log_group_requests" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}log_group_requests (
	id_request mediumint(8) unsigned NOT NULL auto_increment,
	id_member mediumint(8) unsigned NOT NULL default '0',
	id_group smallint(5) unsigned NOT NULL default '0',
	time_applied int(10) unsigned NOT NULL default '0',
	reason text NOT NULL,
	PRIMARY KEY (id_request),
	UNIQUE id_member (id_member, id_group) 
) TYPE=MyISAM{$db_collation};
---#

---# Adding new membergroup table columns...
ALTER TABLE {$db_prefix}membergroups
ADD description text NOT NULL AFTER group_name;

ALTER TABLE {$db_prefix}membergroups
ADD group_type tinyint(3) NOT NULL default '0';

ALTER TABLE {$db_prefix}membergroups
ADD hidden tinyint(3) NOT NULL default '0';
---#

---# Creating "group_moderators" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}group_moderators (
	id_group smallint(5) unsigned NOT NULL default '0',
	id_member mediumint(8) unsigned NOT NULL default '0',
	PRIMARY KEY (id_group, id_member) 
) TYPE=MyISAM{$db_collation};
---#

/******************************************************************************/
--- Updating attachment data...
/******************************************************************************/

---# Altering attachment table.
ALTER TABLE {$db_prefix}attachments
ADD COLUMN fileext varchar(8) NOT NULL default '',
ADD COLUMN mime_type varchar(20) NOT NULL default '';
---#

---# Populate the attachment extension.
UPDATE {$db_prefix}attachments
SET fileext = LOWER(SUBSTRING(filename, 1 - (INSTR(REVERSE(filename), '.'))))
WHERE fileext = ''
	AND INSTR(filename, '.')
	AND attachment_type != 3;
---#

---# Updating thumbnail attachments.
UPDATE {$db_prefix}attachments
SET fileext = 'jpg'
WHERE attachment_type = 3
	AND fileext = ''
	AND RIGHT(filename, 9) = 'JPG_thumb';

UPDATE {$db_prefix}attachments
SET fileext = 'png'
WHERE attachment_type = 3
	AND fileext = ''
	AND RIGHT(filename, 9) = 'PNG_thumb';
---#

---# Calculating attachment mime types.
---{
// Don't ever bother doing this twice.
//!!! 1==1 is temporary to allow alpha testers not to complain.
if (1 == 1 || @$modSettings['smfVersion'] < '2.0')
{
	$_GET['a'] = isset($_GET['a']) ? (int) $_GET['a'] : 0;

	if (!function_exists('getAttachmentFilename'))
	{
		function getAttachmentFilename($filename, $attachment_id)
		{
			global $modSettings;
		
			$clean_name = strtr($filename, '������������������������������������������������������������', 'SZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy');
			$clean_name = strtr($clean_name, array('�' => 'TH', '�' => 'th', '�' => 'DH', '�' => 'dh', '�' => 'ss', '�' => 'OE', '�' => 'oe', '�' => 'AE', '�' => 'ae', '�' => 'u'));
			$clean_name = preg_replace(array('/\s/', '/[^\w_\.\-]/'), array('_', ''), $clean_name);
			$enc_name = $attachment_id . '_' . strtr($clean_name, '.', '_') . md5($clean_name);
			$clean_name = preg_replace('~\.[\.]+~', '.', $clean_name);
		
			if ($attachment_id == false)
				return $clean_name;
		
			if (file_exists($modSettings['attachmentUploadDir'] . '/' . $enc_name))
				$filename = $modSettings['attachmentUploadDir'] . '/' . $enc_name;
			else
				$filename = $modSettings['attachmentUploadDir'] . '/' . $clean_name;
		
			return $filename;
		}
	}

	$ext_updates = array();
	
	// What headers are valid results for getimagesize?
	$validTypes = array(
		1 => 'gif',
		2 => 'jpeg',
		3 => 'png',
		5 => 'psd',
		6 => 'bmp',
		7 => 'tiff',
		8 => 'tiff',
		9 => 'jpeg',
		14 => 'iff',
	);
	
	$is_done = false;
	while (!$is_done)
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_attach, filename, fileext
			FROM {$db_prefix}attachments
			WHERE fileext != ''
				AND mime_type = ''
			LIMIT $_GET[a], 100", false, false);
		// Finished?
		if ($smfFunc['db_num_rows']($request) == 0)
			$is_done = true;
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			$filename = getAttachmentFilename($row['filename'], $row['id_attach']);
			if (!file_exists($filename))
				continue;
	
			// Is it an image?
			$size = @getimagesize($filename);
			// Nothing valid?
			if (empty($size) || empty($size[0]))
				continue;
			// Got the mime?
			elseif (!empty($size['mime']))
				$mime = $size['mime'];
			// Otherwise is it valid?
			elseif (!isset($validTypes[$size[2]]))
				continue;
			else
				$mime = 'image/' . $validTypes[$size[2]];
	
			// Let's try keep updates to a minimum.
			if (!isset($ext_updates[$row['fileext'] . $size['mime']]))
				$ext_updates[$row['fileext'] . $size['mime']] = array(
					'fileext' => $row['fileext'],
					'mime' => $mime,
					'files' => array(),
				);
			$ext_updates[$row['fileext'] . $size['mime']]['files'][] = $row['id_attach'];
		}
		$smfFunc['db_free_result']($request);
	
		// Do the updates?
		foreach ($ext_updates as $key => $update)
		{
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}attachments
				SET mime_type = '$update[mime]'
				WHERE id_attach IN (" . implode(',', $update[files]) . ")", false, false);

			// Remove it.
			unset($ext_updates[$key]);
		}
	
		$_GET['a'] += 100;
	}
	
	unset($_GET['a']);
}
---}
---#

/******************************************************************************/
--- Adding Post Moderation.
/******************************************************************************/

---# Creating "approval_queue" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}approval_queue (
	id_msg int(10) unsigned NOT NULL default '0',
	id_attach int(10) unsigned NOT NULL default '0',
	id_event smallint(5) unsigned NOT NULL default '0'
) TYPE=MyISAM{$db_collation};
---#

---# Adding approved column to attachments table...
ALTER TABLE {$db_prefix}attachments
ADD approved tinyint(3) NOT NULL default '1';
---#

---# Adding approved column to messages table...
ALTER TABLE {$db_prefix}messages
ADD approved tinyint(3) NOT NULL default '1';

ALTER TABLE {$db_prefix}messages
ADD INDEX approved (approved);
---#

---# Adding unapproved count column to topics table...
ALTER TABLE {$db_prefix}topics
ADD unapproved_posts smallint(5) NOT NULL default '0';
---#

---# Adding approved column to topics table...
ALTER TABLE {$db_prefix}topics
ADD approved tinyint(3) NOT NULL default '1',
ADD INDEX approved (approved);
---#

---# Adding approved columns to boards table...
ALTER TABLE {$db_prefix}boards
ADD unapproved_posts smallint(5) NOT NULL default '0',
ADD unapproved_topics smallint(5) NOT NULL default '0';
---#

---# Adding post moderation permissions...
---{
// We *cannot* do this twice!
if (@$modSettings['smfVersion'] < '2.0')
{
	// Anyone who can currently edit posts we assume can approve them...
	$request = upgrade_query("
		SELECT id_group, id_board, add_deny, permission
		FROM {$db_prefix}board_permissions
		WHERE permission = 'modify_any'");
	$inserts = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$inserts[] = "($row[id_group], $row[id_board], 'approve_posts', $row[add_deny])";
	}
	mysql_free_result($request);

	if (!empty($inserts))
		upgrade_query("
			INSERT IGNORE INTO {$db_prefix}board_permissions
				(id_group, id_board, permission, add_deny)
			VALUES
				" . implode(',', $inserts));
}
---}
---#

/******************************************************************************/
--- Upgrading the error log.
/******************************************************************************/

---# Adding columns to log_errors table...
ALTER TABLE {$db_prefix}log_errors
ADD error_type char(15) NOT NULL default 'general';
ALTER TABLE {$db_prefix}log_errors
ADD file tinytext NOT NULL,
ADD line mediumint(8) unsigned NOT NULL default '0';
---#

---{
$request = upgrade_query("
	SELECT COUNT(*)
	FROM {$db_prefix}log_errors");
list($totalActions) = mysql_fetch_row($request);
mysql_free_result($request);

$_GET['m'] = !empty($_GET['m']) ? (int) $_GET['m'] : '0';

while ($_GET['m'] < $totalActions)
{
	nextSubStep($substep);

	$request = upgrade_query("
		SELECT id_error, message, file, line
		FROM {$db_prefix}log_errors
		LIMIT $_GET[m], 500");
	while($row = mysql_fetch_assoc($request))
	{	
		preg_match('~<br />(%1\$s: )?([\w\. \\\\/\-_:]+)<br />(%2\$s: )?([\d]+)~', $row['message'], $matches);
		if (!empty($matches[2]) && !empty($matches[4]) && empty($row['file']) && empty($row['line']))
		{
			$row['file'] = addslashes(str_replace('\\', '/', $matches[2]));
			$row['line'] = (int) $matches[4];
			$row['message'] = addslashes(preg_replace('~<br />(%1\$s: )?([\w\. \\\\/\-_:]+)<br />(%2\$s: )?([\d]+)~', '', $row['message']));
		}
		else
			continue;

		upgrade_query("
			UPDATE {$db_prefix}log_errors
			SET file = SUBSTRING('$row[file]', 1, 255),
				line = $row[line],
				message = SUBSTRING('$row[message]', 1, 65535)
			WHERE id_error = $row[id_error]
			LIMIT 1");
	}

	$_GET['m'] += 500;
}
unset($_GET['m']);
---}

/******************************************************************************/
--- Adding Scheduled Tasks Data.
/******************************************************************************/

---# Creating Scheduled Task Table...
CREATE TABLE IF NOT EXISTS {$db_prefix}scheduled_tasks (
	id_task smallint(5) NOT NULL auto_increment,
	next_time int(10) NOT NULL,
	time_offset int(10) NOT NULL,
	time_regularity smallint(5) NOT NULL,
	time_unit varchar(1) NOT NULL default 'h',
	disabled tinyint(3) NOT NULL default '0',
	task varchar(24) NOT NULL default '',
	PRIMARY KEY (id_task),
	KEY next_time (next_time),
	KEY disabled (disabled),
	UNIQUE task (task)
) TYPE=MyISAM{$db_collation};
---#

---# Populating Scheduled Task Table...
INSERT IGNORE INTO {$db_prefix}scheduled_tasks
	(next_time, time_offset, time_regularity, time_unit, disabled, task)
VALUES
	(0, 0, 2, 'h', 0, 'approval_notification'),
	(0, 0, 7, 'd', 0, 'auto_optimize'),
	(0, 60, 1, 'd', 0, 'daily_maintenance'),
	(0, 0, 1, 'd', 0, 'daily_digest'),
	(0, 0, 1, 'w', 0, 'weekly_digest'),
	(0, 0, 1, 'd', 0, 'fetchSMfiles'),
	(0, -55800, 1, 'd', 1, 'birthdayemails');
---#

---# Populating Scheduled Task Table...
UPDATE {$db_prefix}scheduled_tasks
SET task = 'daily_maintenance', time_regularity = 1, time_unit = 'd', time_offset = 60
WHERE task = 'clean_cache';
---#

---# Moving auto optimise settings to scheduled task...
---{
if (!isset($modSettings['next_task_time']) && isset($modSettings['autoOptLastOpt']))
{
	// Try move over the regularity...
	if (isset($modSettings['autoOptDatabase']))
	{
		$disabled = empty($modSettings['autoOptDatabase']) ? 1 : 0;
		$regularity = $disabled ? 7 : $modSettings['autoOptDatabase'];
		$next_time = $modSettings['autoOptLastOpt'] + 3600 * 24 * $modSettings['autoOptDatabase'];

		// Update the task accordingly.
		upgrade_query("
			UPDATE {$db_prefix}scheduled_tasks
			SET disabled = $disabled, time_regularity = $regularity, next_time = $next_time
			WHERE task = 'auto_optimize'");
	}

	// Delete the old settings!
	upgrade_query("
		DELETE FROM {$db_prefix}settings
		WHERE VARIABLE IN ('autoOptLastOpt', 'autoOptDatabase')");
}
---}
---#

---# Creating Scheduled Task Log Table...
CREATE TABLE IF NOT EXISTS {$db_prefix}log_scheduled_tasks (
	id_log mediumint(8) NOT NULL auto_increment,
	id_task smallint(5) NOT NULL,
	time_run int(10) NOT NULL,
	time_taken float NOT NULL default '0',
	PRIMARY KEY (id_log)
) TYPE=MyISAM{$db_collation};
---#

---# Adding new scheduled task setting...
---{
if (!isset($modSettings['next_task_time']))
{
	upgrade_query("
		INSERT INTO {$db_prefix}settings
			(variable, value)
		VALUES
			('next_task_time', '0')");
}
---}
---#

/******************************************************************************/
--- Adding permission profiles for boards.
/******************************************************************************/

---# Creating "permission_profiles" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}permission_profiles (
	id_profile smallint(5) NOT NULL auto_increment,
	profile_name tinytext NOT NULL,
	id_parent smallint(5) unsigned NOT NULL default '0',
	PRIMARY KEY (id_profile)
) TYPE=MyISAM{$db_collation};
---#

---# Adding profile columns to boards table...
ALTER TABLE {$db_prefix}boards
ADD id_profile smallint(5) unsigned NOT NULL default '1' AFTER member_groups;
---#

---# Adding profile columns to board permission table...
ALTER TABLE {$db_prefix}board_permissions
ADD id_profile smallint(5) unsigned NOT NULL default '1' AFTER id_group;

ALTER TABLE {$db_prefix}board_permissions
DROP PRIMARY KEY,
ADD PRIMARY KEY (id_group, id_profile, permission);
---#

---# Migrating old board profiles to profile sysetem
---{

// Doing this twice would be awful!
$request = upgrade_query("
	SELECT COUNT(*)
	FROM {$db_prefix}permission_profiles");
list ($profileCount) = mysql_fetch_row($request);
mysql_free_result($request);

if ($profileCount == 0)
{
	// Insert a boat load of default profile permissions.
	upgrade_query("
		INSERT INTO {$db_prefix}permission_profiles
			(id_profile, profile_name)
		VALUES
			(1, 'default'),
			(2, 'no_polls'),
			(3, 'reply_only'),
			(4, 'read_only')");

	// Fetch the current "default" permissions.
	$request = upgrade_query("
		SELECT id_group, permission, add_deny
		FROM {$db_prefix}board_permissions
		WHERE id_board = 0");
	$cur_perms = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$cur_perms['default'][$row['id_group']][$row['permission']] = $row['add_deny'];
	}
	mysql_free_result($request);

	// Work out what the others would be based on this.
	$permission_mode = array(
		'read_only' => array(
			'post_new',
			'poll_post',
			'post_reply_own',
			'post_reply_any',
		),
		'reply_only' => array(
			'post_new',
			'poll_post',
		),
		'no_polls' => array(
			'poll_post',
		),
	);

	$perm_inserts = array();
	// Cycle through default...
	foreach ($cur_perms['default'] as $group => $permissions)
	{
		// Then permissions...
		foreach ($permissions as $name => $add_deny)
		{
			// Then the other types.
			foreach ($permission_mode as $type => $restrictions)
			{
				// If this isn't restricted or this group can moderate then pass it through.
				if (!in_array($name, $restrictions) || !empty($cur_perms['default'][$group]['moderate_board']))
				{
					$cur_perms[$type][$group][$name] = $add_deny;
					$numtype = $type == 'no_polls' ? 2 : ($type == 'reply_only' ? 3 : 4);
					$perm_inserts[] = "($numtype, $group, '$name', $add_deny)";
				}
			}
		}
	}

	// Update the default permissions, this is easy!
	upgrade_query("
		UPDATE {$db_prefix}board_permissions
		SET id_profile = 1
		WHERE id_board = 0");

	// Add the three non-default permissions.
	if (!empty($perm_inserts))
		upgrade_query("
			INSERT INTO {$db_prefix}board_permissions
				(id_profile, id_group, permission, add_deny)
			VALUES
				" . implode(',', $perm_inserts));

	// Load all the other permissions
	$request = upgrade_query("
		SELECT id_board, id_group, permission, add_deny
		FROM {$db_prefix}board_permissions
		WHERE id_profile = 0");
	$all_perms = array();
	while ($row = mysql_fetch_assoc($request))
		$all_perms[$row['id_board']][$row['id_group']][$row['permission']] = $row['add_deny'];
	mysql_free_result($request);

	// Now we have the profile profiles for this installation. We now need to go through each board and work out what the permission profile should be!
	$request = upgrade_query("
		SELECT id_board, permission_mode
		FROM {$db_prefix}boards");
	$board_updates = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Is it a truely local permission board? If so this is a new profile!
		if ($row['permission_mode'] != 0 && !empty($modSettings['permission_enable_by_board']))
		{
			// I know we could cache this, but I think we need to be practical - this is slow but guaranteed to work.
			upgrade_query("
				INSERT INTO {$db_prefix}permission_profiles
					(profile_name, id_parent)
				VALUES
					('', $row[id_board])");
			$board_updates[mysql_insert_id()][] = $row['id_board'];
		}
		// Otherwise, dear god, this is an old school "simple" permission...
		elseif ($row['permission_mode'] > 1 && $row['permission_mode'] < 5)
		{
			$board_updates[$row['permission_mode']][] = $row['id_board'];
		}
		// Otherwise this is easy. It becomes default.
		else
			$board_updates[1][] = $row['id_board'];
	}
	mysql_free_result($request);

	// Update the board tables.
	foreach ($board_updates as $profile => $boards)
	{
		if (empty($boards))
			continue;

		$boards = implode(',', $boards);

		upgrade_query("
			UPDATE {$db_prefix}boards
			SET id_profile = $profile
			WHERE id_board IN ($boards)");

		// If it's a custom profile then update this too.
		if ($profile > 4)
			upgrade_query("
				UPDATE {$db_prefix}board_permissions
				SET id_profile = $profile
				WHERE id_board IN ($boards)
					AND id_profile = 0");
	}
}
---}
---#

---# Adding inherited permissions...
ALTER TABLE {$db_prefix}membergroups
ADD id_parent smallint(5) NOT NULL default '-2';
---#

---# Deleting old permission settings...
DELETE FROM {$db_prefix}settings
WHERE VARIABLE IN ('permission_enable_by_board', 'autoOptDatabase');
---#

---# Removing old permission_mode column...
ALTER TABLE {$db_prefix}boards
DROP COLUMN permission_mode;
---#

---# Removing old board permissions column...
ALTER TABLE {$db_prefix}board_permissions
DROP COLUMN id_board;
---#

/******************************************************************************/
--- Adding Some Additional Functionality.
/******************************************************************************/

---# Adding column to hold the boards being ignored ...
ALTER TABLE {$db_prefix}members
ADD ignore_boards tinytext NOT NULL;
---#

---# Purge flood control ...
DELETE FROM {$db_prefix}log_floodcontrol;
---#

---# Adding advanced flood control ...
ALTER TABLE {$db_prefix}log_floodcontrol
ADD log_type varchar(8) NOT NULL default 'post';
---#

---# Sorting out flood control keys ...
ALTER TABLE {$db_prefix}log_floodcontrol
DROP PRIMARY KEY,
ADD PRIMARY KEY (ip(16), log_type(8));
---#

/******************************************************************************/
--- Adding some columns to moderation log
/******************************************************************************/
---# Add the columns and the keys to log_actions ...
ALTER TABLE {$db_prefix}log_actions
ADD id_board smallint(5) unsigned NOT NULL default '0',
ADD id_topic mediumint(8) unsigned NOT NULL default '0',
ADD id_msg int(10) unsigned NOT NULL default '0',
ADD KEY id_board (id_board),
ADD KEY id_msg (id_msg);
---#

---# Update the information already in log_actions
---{
$request = upgrade_query("
	SELECT COUNT(*)
	FROM {$db_prefix}log_actions");
list($totalActions) = mysql_fetch_row($request);
mysql_free_result($request);

$_GET['m'] = !empty($_GET['m']) ? (int) $_GET['m'] : '0';

while ($_GET['m'] < $totalActions)
{
	nextSubStep($substep);

	$mrequest = upgrade_query("
		SELECT id_action, extra, id_board, id_topic, id_msg
		FROM {$db_prefix}log_actions
		LIMIT $_GET[m], 500");

	while ($row = mysql_fetch_assoc($mrequest))
	{
		if (!empty($row['id_board']) || !empty($row['id_topic']) || !empty($row['id_msg']))
			continue;
		$row['extra'] = @unserialize($row['extra']);
		// Corrupt?
		$row['extra'] = is_array($row['extra']) ? $row['extra'] : array();
		if (!empty($row['extra']['board']))
		{
			$board_id = (int) $row['extra']['board'];
			unset($row['extra']['board']);
		}
		else
			$board_id = '0';
		if (!empty($row['extra']['board_to']) && empty($board_id))
		{
			$board_id = (int) $row['extra']['board_to'];
			unset($row['extra']['board_to']);
		}
		
		if (!empty($row['extra']['topic']))
		{
			$topic_id = (int) $row['extra']['topic'];
			unset($row['extra']['topic']);
			if (empty($board_id))
			{
				$trequest = upgrade_query("
					SELECT id_board
					FROM {$db_prefix}topics
					WHERE id_topic=$topic_id
					LIMIT 1");
				if (mysql_num_rows($trequest))
					list($board_id) = mysql_fetch_row($trequest);
				mysql_free_result($trequest);
			}
		}
		else
			$topic_id = '0';

		if(!empty($row['extra']['message']))
		{
			$msg_id = (int) $row['extra']['message'];
			unset($row['extra']['message']);
			if (empty($topic_id) || empty($board_id))
			{
				$trequest = upgrade_query("
					SELECT id_board, id_topic
					FROM {$db_prefix}messages
					WHERE id_msg=$msg_id
					LIMIT 1");
				if (mysql_num_rows($trequest))
					list($board_id, $topic_id) = mysql_fetch_row($trequest);
				mysql_free_result($trequest);
			}
		}
		else
			$msg_id = '0';
		$row['extra'] = addslashes(serialize($row['extra']));
		upgrade_query("UPDATE {$db_prefix}log_actions SET id_board=$board_id, id_topic=$topic_id, id_msg=$msg_id, extra='$row[extra]' WHERE id_action=$row[id_action]");
	}
	$_GET['m'] += 500;
}
unset($_GET['m']);
---}
---#

/******************************************************************************/
--- Create a repository for the javascript files from Simple Machines...
/******************************************************************************/

---# Creating repository table ...
CREATE TABLE IF NOT EXISTS {$db_prefix}admin_info_files (
  id_file tinyint(4) unsigned NOT NULL auto_increment,
  filename tinytext NOT NULL,
  path tinytext NOT NULL,
  parameters tinytext NOT NULL,
  data text NOT NULL,
  filetype tinytext NOT NULL,
  PRIMARY KEY (id_file),
  KEY filename (filename(30))
) TYPE=MyISAM{$db_collation};
---#

---# Add in the files to get from Simple Machines...
INSERT IGNORE INTO {$db_prefix}admin_info_files
	(id_file, filename, path, parameters)
VALUES
	(1, 'current-version.js', '/smf/', 'version=%3$s'),
	(2, 'detailed-version.js', '/smf/', 'language=%1$s'),
	(3, 'latest-news.js', '/smf/', 'language=%1$s&format=%2$s'),
	(4, 'latest-packages.js', '/smf/', 'language=%1$s'),
	(5, 'latest-smileys.js', '/smf/', 'language=%1$s'),
	(6, 'latest-support.js', '/smf/', 'language=%1$s'),
	(7, 'latest-themes.js', '/smf/', 'language=%1$s');
---#

---# Ensure that the table has the filetype column
ALTER TABLE {$db_prefix}admin_info_files
ADD filetype tinytext NOT NULL;
---#

---# Set the filetype for the files
UPDATE {$db_prefix}admin_info_files
SET filetype='text/javascript'
WHERE id_file IN (1,2,3,4,5,6,7);
---#

---# Ensure that the files from Simple Machines get updated
UPDATE {$db_prefix}scheduled_tasks
SET next_time = UNIX_TIMESTAMP()
WHERE id_task = 7
LIMIT 1;
---#

/******************************************************************************/
--- Adding new personal messaging functionality.
/******************************************************************************/

---# Adding personal message rules table...
CREATE TABLE IF NOT EXISTS {$db_prefix}pm_rules (
	id_rule int(10) unsigned NOT NULL auto_increment,
	id_member int(10) unsigned NOT NULL default '0',
	rule_name varchar(60) NOT NULL,
	criteria text NOT NULL,
	actions text NOT NULL,
	delete_pm tinyint(3) unsigned NOT NULL default '0',
	is_or tinyint(3) unsigned NOT NULL default '0',
	PRIMARY KEY (id_rule),
	KEY id_member (id_member),
	KEY delete_pm (delete_pm)
) TYPE=MyISAM{$db_collation};
---#

---# Adding new message status columns...
ALTER TABLE {$db_prefix}members
ADD COLUMN new_pm tinyint(3) NOT NULL default '0';

ALTER TABLE {$db_prefix}members
ADD COLUMN pm_prefs mediumint(8) NOT NULL default '0';

ALTER TABLE {$db_prefix}pm_recipients
ADD COLUMN is_new tinyint(3) NOT NULL default '0';
---#

---# Set the new status to be correct....
---{
// Don't do this twice!
if (@$modSettings['smfVersion'] < '2.0')
{
	// Set all unread messages as new.
	upgrade_query("
		UPDATE {$db_prefix}pm_recipients
		SET is_new = 1
		WHERE is_read = 0");

	// Also set members to have a new pm if they have any unread.
	upgrade_query("
		UPDATE {$db_prefix}members
		SET new_pm = 1
		WHERE unread_messages > 0");
}
---}
---#

---# Adding personal message tracking column...
ALTER TABLE {$db_prefix}personal_messages
ADD id_pm_head int(10) unsigned NOT NULL AFTER id_pm,
ADD INDEX id_pm_head (id_pm_head);
---#

---# Adding personal message tracking column...
UPDATE {$db_prefix}personal_messages
SET id_pm_head = id_pm
WHERE id_pm_head = 0;
---#

/******************************************************************************/
--- Final clean up...
/******************************************************************************/

---# Sorting the boards...
ALTER TABLE {$db_prefix}categories
ORDER BY cat_order;

ALTER TABLE {$db_prefix}boards
ORDER BY board_order;
---#
