/* ATTENTION: You don't need to run or use this file!  The upgrade.php script does everything for you! */

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

---# Ensuring stats index setting present...
INSERT IGNORE INTO {$db_prefix}themes
	(ID_THEME, variable, value)
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

---# Adding PM spam protection settings.
---{
if (empty($modSettings['pm_spam_settings']))
{
	if (isset($modSettings['max_pm_recipients']))
		$modSettings['pm_spam_settings'] = (int) $modSettings['max_pm_recipients'] . ',5';
	else
		$modSettings['pm_spam_settings'] = '10,5';

	upgrade_query("
		INSERT IGNORE INTO {$db_prefix}settings
			(variable, value)
		VALUES
			('pm_spam_settings', '$modSettings[pm_spam_settings]')");
}
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

/******************************************************************************/
--- Adding email digest functionality.
/******************************************************************************/

---# Creating "log_digest" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}log_digest (
	ID_TOPIC mediumint(8) unsigned NOT NULL,
	ID_MSG mediumint(8) unsigned NOT NULL,
	note_type varchar(10) NOT NULL default 'post',
	daily smallint(3) unsigned NOT NULL default '0',
	exclude mediumint(8) unsigned NOT NULL default '0'
) TYPE=MyISAM;
---#

---# Adding digest option to "members" table...
ALTER TABLE {$db_prefix}members
CHANGE COLUMN notifyOnce notifyRegularity tinyint(4) unsigned NOT NULL default '1';
---#
/******************************************************************************/
--- Making changes to the package manager.
/******************************************************************************/

---# Creating "log_packages" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}log_packages (
	ID_INSTALL int(10) NOT NULL auto_increment,
	filename tinytext NOT NULL,
	package_id tinytext NOT NULL,
	name tinytext NOT NULL,
	version tinytext NOT NULL,
	ID_MEMBER_INSTALLED mediumint(8) NOT NULL,
	member_installed tinytext NOT NULL,
	time_installed int(10) NOT NULL default '0',
	ID_MEMBER_REMOVED mediumint(8) NOT NULL default '0',
	member_removed tinytext NOT NULL,
	time_removed int(10) NOT NULL default '0',
	install_state tinyint(3) NOT NULL default '1',
	failed_steps text NOT NULL,
	themes_installed tinytext NOT NULL,
	PRIMARY KEY (ID_INSTALL),
	KEY filename (filename(15))
) TYPE=MyISAM;
---#

/******************************************************************************/
--- Creating mail queue functionality.
/******************************************************************************/

---# Creating "mail_queue" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}mail_queue (
	ID_MAIL int(10) unsigned NOT NULL auto_increment,
	time_sent int(10) NOT NULL default '0',
	recipient tinytext NOT NULL,
	body text NOT NULL,
	subject tinytext NOT NULL,
	headers text NOT NULL,
	send_html tinyint(3) NOT NULL default '0',
	priority tinyint(3) NOT NULL default '1',
	PRIMARY KEY (ID_MAIL),
	KEY time_sent (time_sent),
	KEY priority (priority)
) TYPE=MyISAM;
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
	ID_REPORT mediumint(8) unsigned NOT NULL auto_increment,
	ID_MSG int(10) NOT NULL,
	ID_TOPIC mediumint(10) NOT NULL,
	ID_BOARD int(10) NOT NULL,
	ID_MEMBER mediumint(8) NOT NULL,
	membername tinytext NOT NULL,
	subject tinytext NOT NULL,
	body text NOT NULL,
	time_started int(10) NOT NULL default '0',
	time_updated int(10) NOT NULL default '0',
	num_reports mediumint(6) NOT NULL default '0',
	closed tinyint(3) NOT NULL default '0',
	ignore_all tinyint(3) NOT NULL default '0',
	PRIMARY KEY (ID_REPORT),
	KEY ID_MEMBER (ID_MEMBER),
	KEY ID_TOPIC (ID_TOPIC),
	KEY closed (closed),
	KEY time_started (time_started),
	KEY ID_MSG (ID_MSG)
) TYPE=MyISAM;
---#

---# Creating "log_reported_comments" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}log_reported_comments (
	ID_COMMENT mediumint(8) unsigned NOT NULL auto_increment,
	ID_REPORT mediumint(8) NOT NULL,
	ID_MEMBER mediumint(8) NOT NULL,
	membername tinytext NOT NULL,
	comment tinytext NOT NULL,
	time_sent int(10) NOT NULL,
	PRIMARY KEY (ID_COMMENT),
	KEY ID_REPORT (ID_REPORT),
	KEY ID_MEMBER (ID_MEMBER),
	KEY time_sent (time_sent)
) TYPE=MyISAM;
---#

---# Adding moderator center permissions...
---{
// Don't do this twice!
if (@$modSettings['smfVersion'] < '2.0')
{
	// Try find people who probably should see the moderation center.
	$request = upgrade_query("
		SELECT ID_GROUP, addDeny, permission
		FROM {$db_prefix}permissions
		WHERE permission = 'calendar_edit_any'");
	$inserts = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$inserts[] = "($row[ID_GROUP], 'access_mod_center', $row[addDeny])";
	}
	mysql_free_result($request);

	if (!empty($inserts))
		upgrade_query("
			INSERT IGNORE INTO {$db_prefix}permissions
				(ID_GROUP, permission, addDeny)
			VALUES
				" . implode(',', $inserts));
}
---}
---#

/******************************************************************************/
--- Enhancing membergroups.
/******************************************************************************/

---# Creating "log_group_requests" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}log_group_requests (
	ID_REQUEST mediumint(8) unsigned NOT NULL auto_increment,
	ID_MEMBER mediumint(8) NOT NULL,
	ID_GROUP smallint(5) NOT NULL,
	time_applied int(10) NOT NULL default '0',
	reason text NOT NULL,
	PRIMARY KEY (ID_REQUEST),
	UNIQUE ID_MEMBER (ID_MEMBER, ID_GROUP) 
) TYPE=MYISAM;
---#

---# Adding new membergroup table columns...
ALTER TABLE {$db_prefix}membergroups
ADD description text NOT NULL AFTER groupName;

ALTER TABLE {$db_prefix}membergroups
ADD groupType tinyint(3) NOT NULL default '0';

ALTER TABLE {$db_prefix}membergroups
ADD hidden tinyint(3) NOT NULL default '0';
---#

---# Creating "group_moderators" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}group_moderators (
	ID_GROUP smallint(5) unsigned NOT NULL default '0',
	ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
	PRIMARY KEY (ID_GROUP, ID_MEMBER) 
) TYPE=MyISAM;
---#

/******************************************************************************/
--- Adding Post Moderation.
/******************************************************************************/

---# Creating "approval_queue" table...
CREATE TABLE IF NOT EXISTS {$db_prefix}approval_queue (
	ID_MSG int(10) unsigned NOT NULL default '0',
	ID_ATTACH int(10) unsigned NOT NULL default '0',
	ID_EVENT smallint(5) unsigned NOT NULL default '0'
) TYPE=MYISAM;
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
ADD unapprovedPosts smallint(5) NOT NULL default '0';
---#

---# Adding approved column to topics table...
ALTER TABLE {$db_prefix}topics
ADD approved tinyint(3) NOT NULL default '1',
ADD INDEX approved (approved);
---#

---# Adding approved columns to boards table...
ALTER TABLE {$db_prefix}boards
ADD unapprovedPosts smallint(5) NOT NULL default '0',
ADD unapprovedTopics smallint(5) NOT NULL default '0';
---#

---# Adding post moderation permissions...
---{
// We *cannot* do this twice!
if (@$modSettings['smfVersion'] < '2.0')
{
	// Anyone who can currently edit posts we assume can approve them...
	$request = upgrade_query("
		SELECT ID_GROUP, ID_BOARD, addDeny, permission
		FROM {$db_prefix}board_permissions
		WHERE permission = 'modify_any'");
	$inserts = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$inserts[] = "($row[ID_GROUP], $row[ID_BOARD], 'approve_posts', $row[addDeny])";
	}
	mysql_free_result($request);

	if (!empty($inserts))
		upgrade_query("
			INSERT IGNORE INTO {$db_prefix}board_permissions
				(ID_GROUP, ID_BOARD, permission, addDeny)
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
ADD errorType char(15) NOT NULL default 'general';
ALTER TABLE {$db_prefix}log_errors
ADD file tinytext NOT NULL default '',
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
		SELECT ID_ERROR, message, file, line
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
			WHERE ID_ERROR = $row[ID_ERROR]
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
	ID_TASK smallint(5) NOT NULL auto_increment,
	nextTime int(10) NOT NULL,
	timeOffset int(10) NOT NULL,
	timeRegularity smallint(5) NOT NULL,
	timeUnit varchar(1) NOT NULL default 'h',
	disabled tinyint(3) NOT NULL default '0',
	task varchar(24) NOT NULL default '',
	PRIMARY KEY (ID_TASK),
	KEY nextTime (nextTime),
	KEY disabled (disabled),
	UNIQUE task (task)
) TYPE=MyISAM;
---#

---# Populating Scheduled Task Table...
INSERT IGNORE INTO {$db_prefix}scheduled_tasks
	(ID_TASK, nextTime, timeOffset, timeRegularity, timeUnit, disabled, task)
VALUES
	(1, 0, 0, 2, 'h', 0, 'approval_notification'),
	(2, 0, 0, 7, 'd', 0, 'auto_optimize'),
	(3, 0, 0, 12, 'h', 0, 'clean_cache'),
	(5, 0, 0, 1, 'd', 0, 'daily_digest'),
	(6, 0, 0, 1, 'w', 0, 'weekly_digest'),
	(7, 0, 0, 1, 'd', 0, 'fetchSMfiles');
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
		$nextTime = $modSettings['autoOptLastOpt'] + 3600 * 24 * $modSettings['autoOptDatabase'];

		// Update the task accordingly.
		upgrade_query("
			UPDATE {$db_prefix}scheduled_tasks
			SET disabled = $disabled, timeRegularity = $regularity, nextTime = $nextTime
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
	ID_LOG mediumint(8) NOT NULL auto_increment,
	ID_TASK smallint(5) NOT NULL,
	timeRun int(10) NOT NULL,
	timeTaken float NOT NULL default '0',
	PRIMARY KEY (ID_LOG)
) TYPE=MyISAM;
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
	ID_PROFILE smallint(5) NOT NULL auto_increment,
	profile_name tinytext NOT NULL,
	ID_PARENT smallint(5) unsigned NOT NULL default '0',
	PRIMARY KEY (ID_PROFILE)
) TYPE=MyISAM;
---#

---# Adding profile columns...
ALTER TABLE {$db_prefix}boards
ADD ID_PROFILE smallint(5) unsigned NOT NULL default '0' AFTER memberGroups;

ALTER TABLE {$db_prefix}board_permissions
ADD ID_PROFILE smallint(5) unsigned NOT NULL default '0' AFTER ID_GROUP;

ALTER TABLE {$db_prefix}board_permissions
DROP PRIMARY KEY,
ADD PRIMARY KEY (ID_GROUP, ID_PROFILE, permission);
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
			(ID_PROFILE, profile_name)
		VALUES
			(1, 'default'),
			(2, 'no_polls'),
			(3, 'reply_only'),
			(4, 'read_only')");

	// Fetch the current "default" permissions.
	$request = upgrade_query("
		SELECT ID_GROUP, permission, addDeny
		FROM {$db_prefix}board_permissions
		WHERE ID_BOARD = 0");
	$cur_perms = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$cur_perms['default'][$row['ID_GROUP']][$row['permission']] = $row['addDeny'];
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
		foreach ($permissions as $name => $addDeny)
		{
			// Then the other types.
			foreach ($permission_mode as $type => $restrictions)
			{
				// If this isn't restricted or this group can moderate then pass it through.
				if (!in_array($name, $restrictions) || !empty($cur_perms['default'][$group]['moderate_board']))
				{
					$cur_perms[$type][$group][$name] = $addDeny;
					$numtype = $type == 'no_polls' ? 2 : ($type == 'reply_only' ? 3 : 4);
					$perm_inserts[] = "($numtype, $group, '$name', $addDeny)";
				}
			}
		}
	}

	// Update the default permissions, this is easy!
	upgrade_query("
		UPDATE {$db_prefix}board_permissions
		SET ID_PROFILE = 1
		WHERE ID_BOARD = 0");

	// Add the three non-default permissions.
	if (!empty($perm_inserts))
		upgrade_query("
			INSERT INTO {$db_prefix}board_permissions
				(ID_PROFILE, ID_GROUP, permission, addDeny)
			VALUES
				" . implode(',', $perm_inserts));

	// Load all the other permissions
	$request = upgrade_query("
		SELECT ID_BOARD, ID_GROUP, permission, addDeny
		FROM {$db_prefix}board_permissions
		WHERE ID_PROFILE = 0");
	$all_perms = array();
	while ($row = mysql_fetch_assoc($request))
		$all_perms[$row['ID_BOARD']][$row['ID_GROUP']][$row['permission']] = $row['addDeny'];
	mysql_free_result($request);

	// Now we have the profile profiles for this installation. We now need to go through each board and work out what the permission profile should be!
	$request = upgrade_query("
		SELECT ID_BOARD, permission_mode
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
					(profile_name, ID_PARENT)
				VALUES
					('', $row[ID_BOARD])");
			$board_updates[mysql_insert_id()][] = $row['ID_BOARD'];
		}
		// Otherwise, dear god, this is an old school "simple" permission...
		elseif ($row['permission_mode'] > 1 && $row['permission_mode'] < 5)
		{
			$board_updates[$row['permission_mode']][] = $row['ID_BOARD'];
		}
		// Otherwise this is easy. It becomes default.
		else
			$board_updates[1][] = $row['ID_BOARD'];
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
			SET ID_PROFILE = $profile
			WHERE ID_BOARD IN ($boards)");

		// If it's a custom profile then update this too.
		if ($profile > 4)
			upgrade_query("
				UPDATE {$db_prefix}board_permissions
				SET ID_PROFILE = $profile
				WHERE ID_BOARD IN ($boards)
					AND ID_PROFILE = 0");
	}
}
---}
---#

---# Adding inherited permissions...
ALTER TABLE {$db_prefix}membergroups
ADD ID_PARENT smallint(5) NOT NULL default '-2';
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
DROP COLUMN ID_BOARD;
---#

/******************************************************************************/
--- Adding Ignore Board Option.
/******************************************************************************/

---# Adding column to hold the boards being ignored ...
ALTER TABLE {$db_prefix}members
ADD ignoreBoards tinytext NOT NULL default '';
---#

/******************************************************************************/
--- Adding some columns to moderation log
/******************************************************************************/
---# Add the columns and the keys to log_actions ...
ALTER TABLE {$db_prefix}log_actions
ADD ID_BOARD smallint(5) unsigned NOT NULL default '0',
ADD ID_TOPIC mediumint(8) unsigned NOT NULL default '0',
ADD ID_MSG int(10) unsigned NOT NULL default '0',
ADD KEY ID_BOARD (ID_BOARD),
ADD KEY ID_MSG (ID_MSG);
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
		SELECT ID_ACTION, extra, ID_BOARD, ID_TOPIC, ID_MSG
		FROM {$db_prefix}log_actions
		LIMIT $_GET[m], 500");

	while ($row = mysql_fetch_assoc($mrequest))
	{
		if (!empty($row['ID_BOARD']) || !empty($row['ID_TOPIC']) || !empty($row['ID_MSG']))
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
					SELECT ID_BOARD
					FROM {$db_prefix}topics
					WHERE ID_TOPIC=$topic_id
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
					SELECT ID_BOARD, ID_TOPIC
					FROM {$db_prefix}messages
					WHERE ID_MSG=$msg_id
					LIMIT 1");
				if (mysql_num_rows($trequest))
					list($board_id, $topic_id) = mysql_fetch_row($trequest);
				mysql_free_result($trequest);
			}
		}
		else
			$msg_id = '0';
		$row['extra'] = addslashes(serialize($row['extra']));
		upgrade_query("UPDATE {$db_prefix}log_actions SET ID_BOARD=$board_id, ID_TOPIC=$topic_id, ID_MSG=$msg_id, extra='$row[extra]' WHERE ID_ACTION=$row[ID_ACTION]");
	}
	$_GET['m'] += 500;
}
unset($_GET['m']);
---}
---#

/******************************************************************************/
--- Create a repository for the javascript files from Simple Machines...
/******************************************************************************/

---# Creating the table ...
CREATE TABLE IF NOT EXISTS {$db_prefix}admin_info_files (
  ID_FILE tinyint(4) unsigned NOT NULL auto_increment,
  filename tinytext NOT NULL default '',
  path tinytext NOT NULL default '',
  parameters tinytext NOT NULL default '',
  data text NOT NULL default '',
  filetype tinytext NOT NULL default '',
  PRIMARY KEY (ID_FILE),
  KEY filename (filename(30))
) TYPE=MYISAM;
---#

---# Add in the files to get from Simple Machiens...
INSERT IGNORE INTO {$db_prefix}admin_info_files
	(ID_FILE, filename, path, parameters)
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
ADD filetype tinytext NOT NULL default '';
---#

---# Set the filetype for the files
UPDATE {$db_prefix}admin_info_files
SET filetype='text/javascript'
WHERE ID_FILE IN (1,2,3,4,5,6,7);
---#

---# Ensure that the files from Simple Machines get updated
UPDATE {$db_prefix}scheduled_tasks
SET nextTime = UNIX_TIMESTAMP()
WHERE ID_TASK = 7
LIMIT 1;
---#


/******************************************************************************/
--- Final clean up...
/******************************************************************************/

---# Sorting the boards...
ALTER TABLE {$db_prefix}categories
ORDER BY catOrder;

ALTER TABLE {$db_prefix}boards
ORDER BY boardOrder;
---#
