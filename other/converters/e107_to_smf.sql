/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "e107"
/******************************************************************************/
---~ version: "SMF 2.0 Beta 2"
---~ settings: "/e107_config.php"
---~ from_prefix: "`$mySQLdefaultdb`.$mySQLprefix"
---~ table_test: "{$from_prefix}user"

/******************************************************************************/
--- Converting ranks...
/******************************************************************************/

DELETE FROM {$to_prefix}membergroups
WHERE group_name LIKE 'e107%';

---{
	$request = mysql_query("
		SELECT e107_value
		FROM {$from_prefix}core
		WHERE e107_name = 'pref'
		LIMIT 1");
	list($prefs) = mysql_fetch_row($request);
	mysql_free_result($request);

	$prefs = @unserialize(strtr($prefs, array("\n" => ' ', "\r" => ' ')));

	if (isset($prefs['forum_levels']) && isset($prefs['forum_thresholds']))
	{
		$inserts = '';
		$post_count = explode(',', $prefs['forum_thresholds']);
		foreach (explode(',', $prefs['forum_levels']) as $k => $groupname)
			if ($groupname !== '')
				$inserts .= "
					(SUBSTRING('e107 " . addslashes($groupname) . "', 1, 255), $prefs[forum_thresholds], '', '')";

		if (!empty($inserts))
			mysql_query("
				INSERT INTO {$to_prefix}membergroups
					(group_name, min_posts, online_color, stars)
				VALUES " . substr($inserts, 0, -1));
	}
---}

/******************************************************************************/
--- Converting groups...
/******************************************************************************/

---* {$to_prefix}membergroups
SELECT
	SUBSTRING(CONCAT('e107 ', userclass_name), 1, 255) AS group_name,
	-1 AS min_posts, '' AS online_color, '' AS stars
FROM {$from_prefix}userclass_classes;
---*

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	u.user_id AS id_member, SUBSTRING(u.user_name, 1, 80) AS member_name,
	u.user_join AS date_registered, u.user_forums AS posts,
	IF (u.user_admin = 1, 1, 0) AS id_group, u.user_lastvisit AS last_login,
	SUBSTRING(u.user_name, 1, 255) AS real_name,
	SUBSTRING(u.user_password, 1, 64) AS passwd,
	SUBSTRING(u.user_email, 1, 255) AS email_address, 0 AS gender,
	u.user_birthday AS birthdate,
	SUBSTRING(REPLACE(u.user_homepage, 'http://', ''), 1, 255) AS website_title,
	SUBSTRING(u.user_homepage, 1, 255) AS website_url,
	SUBSTRING(u.user_location, 1, 255) AS location,
	SUBSTRING(u.user_icq, 1, 255) AS icq, SUBSTRING(u.user_aim, 1, 16) AS aim,
	SUBSTRING(u.user_msn, 1, 255) AS msn, u.user_hideemail AS hide_email,
	SUBSTRING(u.user_signature, 1, 65534) AS signature,
	IF(SUBSTRING(u.user_timezone, 1, 1) = '+', SUBSTRING(u.user_timezone, 2), u.user_timezone) AS time_offset,
	SUBSTRING(u.user_image, 1, 255) AS avatar,
	SUBSTRING(u.user_customtitle, 1, 255) AS usertitle,
	SUBSTRING(u.user_ip, 1, 255) AS member_ip, '' AS lngfile, '' AS buddy_list,
	'' AS pm_ignore_list, '' AS message_labels, '' AS personal_text, '' AS yim,
	'' AS time_format, '' AS secret_question, '' AS secret_answer,
	'' AS validation_code, '' AS additional_groups, '' AS smiley_set,
	'' AS password_salt, SUBSTRING(u.user_ip, 1, 255) AS member_ip2
FROM {$from_prefix}user AS u
WHERE u.user_id > 0;
---*

/******************************************************************************/
--- Converting additional member groups...
/******************************************************************************/

---# Checking memberships...
---{
while (true)
{
	pastTime($substep);

	$result = mysql_query("
		SELECT u.user_id AS id_member, mg.id_group
		FROM ({$from_prefix}userclass_classes AS uc, {$from_prefix}user AS u, {$to_prefix}membergroups AS mg)
		WHERE FIND_IN_SET(uc.userclass_id, REPLACE(u.user_class, '.', ','))
			AND BINARY CONCAT('e107 ', uc.userclass_name) = mg.group_name
		ORDER BY id_member
		LIMIT $_REQUEST[start], 250");
	$additional_groups = '';
	$last_member = 0;
	while ($row = mysql_fetch_assoc($result))
	{
		if (empty($last_member))
			$last_member = $row['id_member'];

		if ($last_member != $row['id_member'])
		{
			$additional_groups = addslashes($additional_groups);

			mysql_query("
				UPDATE {$to_prefix}members
				SET additional_groups = '$additional_groups'
				WHERE id_member = $last_member
				LIMIT 1");
			$last_member = $row['id_member'];
			$additional_groups = $row['id_group'];
		}
		else
		{
			if ($additional_groups == '')
				$additional_groups = $row['id_group'];
			else
				$additional_groups .= ',' . $row['id_group'];
		}
	}

	$_REQUEST['start'] += 250;
	if (mysql_num_rows($result) < 250)
		break;

	mysql_free_result($result);
}
$_REQUEST['start'] = 0;

if ($last_member != 0)
{
	$additional_groups = addslashes($additional_groups);

	mysql_query("
		UPDATE {$to_prefix}members
		SET additional_groups = '$additional_groups'
		WHERE id_member = $last_member
		LIMIT 1");
}
---}
---#

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT
	forum_id AS id_cat, SUBSTRING(forum_name, 1, 255) AS name,
	forum_order AS cat_order
FROM {$from_prefix}forum
WHERE forum_parent = 0;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE id_board != 0;

---* {$to_prefix}boards
SELECT
	f.forum_id AS id_board, SUBSTRING(f.forum_name, 1, 255) AS name,
	SUBSTRING(f.forum_description, 1, 65534) AS description,
	f.forum_parent AS id_cat, f.forum_threads AS num_topics,
	f.forum_threads + f.forum_replies AS num_posts, f.forum_order AS board_order,
	CASE f.forum_class
		WHEN 252 THEN '-1'
		WHEN 255 THEN ''
		WHEN 253 THEN '0'
		WHEN 251 THEN '0'
		WHEN 254 THEN ''
		WHEN 0 THEN '-1,0'
		ELSE IFNULL(mg.id_group, '')
	END AS member_groups
FROM {$from_prefix}forum AS f
	LEFT JOIN {$from_prefix}userclass_classes AS uc ON (uc.userclass_id = f.forum_class)
	LEFT JOIN {$to_prefix}membergroups AS mg ON (BINARY mg.group_name = CONCAT('e107 ', uc.userclass_name))
WHERE f.forum_parent > 0;
---*

/******************************************************************************/
--- Converting topics...
/******************************************************************************/

TRUNCATE {$to_prefix}topics;
TRUNCATE {$to_prefix}log_topics;
TRUNCATE {$to_prefix}log_boards;
TRUNCATE {$to_prefix}log_mark_read;

---* {$to_prefix}topics
SELECT
	t.thread_id AS id_topic, t.thread_s AS is_sticky,
	t.thread_forum_id AS id_board, t.thread_id AS id_first_msg,
	IFNULL(tl.thread_id, t.thread_id) AS id_last_msg,
	IFNULL(us.user_id, 0) AS id_member_started,
	IFNULL(ul.user_id, IFNULL(us.user_id, 0)) AS id_member_updated,
	IFNULL(p.poll_id, 0) AS id_poll, COUNT(*) AS num_replies, t.thread_views AS num_views,
	IF(t.thread_active = 1, 0, 1) AS locked
FROM {$from_prefix}forum_t AS t
	LEFT JOIN {$from_prefix}user AS us ON (us.user_id = SUBSTRING_INDEX(t.thread_user, '.', 1))
	LEFT JOIN {$from_prefix}forum_t AS tl ON (tl.thread_parent = t.thread_id AND tl.thread_datestamp = t.thread_lastpost)
	LEFT JOIN {$from_prefix}user AS ul ON (ul.user_id = SUBSTRING_INDEX(tl.thread_user, '.', 1))
	LEFT JOIN {$from_prefix}forum_t AS m ON (tl.thread_parent = t.thread_id)
	LEFT JOIN {$from_prefix}poll AS p ON (p.poll_datestamp = t.thread_id)
WHERE t.thread_parent = 0
GROUP BY t.thread_id;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;

---* {$to_prefix}messages 200
---{
$row['body'] = preg_replace('~\[size=([789]|[012]\d)\]~is', '[size=$1px]', $row['body']);
---}
SELECT m.thread_id AS id_msg,
	IF(m.thread_parent = 0, m.thread_id, m.thread_parent) AS id_topic,
	m.thread_forum_id AS id_board, m.thread_datestamp AS poster_time,
	IFNULL(u.user_id, 0) AS id_member,
	SUBSTRING(m.thread_name, 1, 255) AS subject,
	SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(m.thread_user, '.', 2), '.', -1), 0x1, 1), 1, 255) AS poster_name,
	SUBSTRING(IFNULL(u.user_email, 'e107.imported@example.com'), 1, 255) AS poster_email,
	'0.0.0.0' AS poster_ip, 1 AS smileys_enabled, m.thread_thread AS body,
	'' AS modified_name, 'xx' AS icon
FROM {$from_prefix}forum_t AS m
	LEFT JOIN {$from_prefix}user AS u ON (u.user_id = SUBSTRING_INDEX(m.thread_user, '.', 1));
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	p.poll_id AS id_poll, SUBSTRING(p.poll_title, 1, 255) AS question,
	0 AS voting_locked, 1 AS max_votes, p.poll_end_datestamp AS expire_time,
	0 AS hide_results, 0 AS change_vote, p.poll_admin_id AS id_member,
	SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(t.thread_user, '.', 2), '.', -1), 0x1, 1), 1, 255) AS poster_name
FROM ({$from_prefix}poll AS p, {$from_prefix}forum_t AS t)
WHERE p.poll_datestamp = t.thread_id;
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---{
$request = mysql_query("
	SELECT
		poll_id, poll_option_1, poll_option_2, poll_option_3, poll_option_4,
		poll_option_5, poll_option_6, poll_option_7, poll_option_8,
		poll_option_9, poll_option_10, poll_votes_1, poll_votes_2, poll_votes_3,
		poll_votes_4, poll_votes_5, poll_votes_6, poll_votes_7, poll_votes_8,
		poll_votes_9, poll_votes_10
	FROM ({$from_prefix}poll AS p, {$from_prefix}forum_t AS t)
	WHERE p.poll_datestamp = t.thread_id");
$inserts = '';
while ($row = mysql_fetch_assoc($request))
{
	for ($i = 1; $i <= 10; $i++)
	{
		if (!empty($row['poll_option' . $i]))
			$inserts .= "
				($row[poll_id], $i, '" . addslashes(substr($row['poll_option_' . $i], 0, 255)) . "', " . $row['poll_votes_' . $i] . '),';
	}
}
mysql_free_result($request);

if ($inserts !== '')
	mysql_query("
		INSERT INTO {$to_prefix}poll_choices
			(id_poll, id_choice, label, votes)
		VALUES " . substr($inserts, 0, -1));
---}


/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
SELECT
	pm.pm_id AS id_pm, uf.user_id AS id_member_from, 0 AS deleted_by_sender,
	SUBSTRING(pm.pm_from_user, 1, 255) AS from_name,
	pm.pm_sent_datestamp AS msgtime,
	SUBSTRING(pm.pm_subject, 1, 255) AS subject,
	SUBSTRING(pm.pm_message, 1, 65534) AS body
FROM ({$from_prefix}pm_messages AS pm, {$from_prefix}user AS uf)
WHERE uf.user_name = pm.pm_from_user;
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT
	pm.pm_id AS id_pm, ut.user_id AS id_member, 0 AS bcc,
	IF (pm.pm_rcv_datestamp = 0, 0, 1) AS is_read, 0 AS deleted, '' AS labels
FROM ({$from_prefix}pm_messages AS pm, {$from_prefix}user AS ut)
WHERE ut.user_name = pm.pm_to_user;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT
	u.user_id AS id_member, t.thread_id AS id_topic, 0 AS sent
FROM ({$from_prefix}forum_t AS t, {$from_prefix}user AS u)
WHERE u.user_id = SUBSTRING_INDEX(t.thread_user, '.', 1)
	AND t.thread_active = 99
	AND t.thread_parent = 0;
---*


/******************************************************************************/
--- Converting board access...
/******************************************************************************/

---# Do all board permissions...
---{

$request = mysql_query("
	SELECT forum_id
	FROM {$from_prefix}forum
	WHERE forum_class = 251");
$readonlyBoards = array();
while ($row = mysql_fetch_assoc($request))
	$readonlyBoards[] = $row['forum_id'];
mysql_free_result($request);

if (!empty($readonlyBoards))
	mysql_query("
		UPDATE {$to_prefix}boards
		SET id_profile = 4
		WHERE id_board IN (" . implode(', ', $readonlyBoards) . ")
		LIMIT " . count($readonlyBoards));
---}
---#

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT f.forum_id AS id_board, u.user_id AS id_member
FROM ({$from_prefix}forum AS f, {$from_prefix}user AS u)
WHERE FIND_IN_SET(u.user_name, REPLACE(f.forum_moderators, ', ', ','));
---*

/******************************************************************************/
--- Converting banned users...
/******************************************************************************/

TRUNCATE {$to_prefix}ban_items;
TRUNCATE {$to_prefix}ban_groups;


---# Moving banned entries...
---{
while (true)
{
	pastTime($substep);

	$result = mysql_query("
		SELECT banlist_ip, banlist_reason
		FROM {$from_prefix}banlist
		LIMIT $_REQUEST[start], 250");
	$ban_time = time();
	$ban_num = 0;
	while ($row = mysql_fetch_assoc($result))
	{
		$ban_num++;
		mysql_query("
			INSERT INTO {$to_prefix}ban_groups
				(name, ban_time, expire_time, notes, reason, cannot_access)
			VALUES ('migrated_ban_$ban_num', $ban_time, NULL, '', '" . addslashes($row['banlist_reason']) . "', 1)");
		$ID_BAN_GROUP = mysql_insert_id();

		if (empty($ID_BAN_GROUP))
			continue;

		if (strpos($row['banlist_ip'], '@') !== false)
		{
			mysql_query("
				INSERT INTO {$to_prefix}ban_items
					(ID_BAN_GROUP, email_address, hostname)
				VALUES ($ID_BAN_GROUP, '" . addslashes($row['banlist_ip']) . "', '')");
			continue;
		}
		else
		{
			list ($octet1, $octet2, $octet3, $octet4) = explode('.', $row['banlist_ip']);

			$ip_high1 = $octet1;
			$ip_low1 = $octet1;

			$ip_high2 = $octet2;
			$ip_low2 = $octet2;

			$ip_high3 = $octet3;
			$ip_low3 = $octet3;

			$ip_high4 = $octet4;
			$ip_low4 =$octet4;

			mysql_query("
				INSERT INTO {$to_prefix}ban_items
					(ID_BAN_GROUP, ip_low1, ip_high1, ip_low2, ip_high2, ip_low3, ip_high3, ip_low4, ip_high4, email_address, hostname)
				VALUES ($ID_BAN_GROUP, $ip_low1, $ip_high1, $ip_low2, $ip_high2, $ip_low3, $ip_high3, $ip_low4, $ip_high4, '', '')");
			continue;
		}
	}

	$_REQUEST['start'] += 250;
	if (mysql_num_rows($result) < 250)
		break;

	mysql_free_result($result);
}
$_REQUEST['start'] = 0;
---}
---#

---# Moving banned user...
---{
$request = mysql_query("
	SELECT user_id
	FROM {$from_prefix}user
	WHERE user_ban = 1");
if (mysql_num_rows($request) > 0)
{
	mysql_query("
		INSERT INTO {to_prefix}ban_groups
			(name, ban_time, expire_time, reason, notes, cannot_access)
			VALUES ('migrated_ban_users', $ban_time, NULL, '', 'Imported from e107', 1)");
		$ID_BAN_GROUP = mysql_insert_id();

	if (empty($ID_BAN_GROUP))
		continue;

	$inserts = '';
	while ($row = mysql_fetch_assoc($request))
		$inserts .= "
			($ID_BAN_GROUP, $row[user_id], '', ''),";
	mysql_free_result($request);

	mysql_query("
		INSERT INTO {$to_prefix}ban_items
			(ID_BAN_GROUP, id_member, email_address, hostname)
		VALUES " . substr($inserts, 0, -1));
}
---}
---#
