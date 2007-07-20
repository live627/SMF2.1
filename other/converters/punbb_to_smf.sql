/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "PunBB 1.2.5"
/******************************************************************************/
---~ version: "SMF 2.0 Beta 1"
---~ settings: "/config.php"
---~ from_prefix: "`$db_name`.$db_prefix"
---~ table_test: "{$from_prefix}users"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}members
SELECT
	id AS id_member, SUBSTRING(username, 1, 80) AS member_name,
	registered AS date_registered, num_posts AS posts,
	IF(group_id = 1, 1, IF(group_id = 2, 2, 0)) AS id_group,
	SUBSTRING(title, 1, 255) AS usertitle, last_visit AS last_login,
	SUBSTRING(password, 1, 64) AS passwd,
	SUBSTRING(IFNULL(real_name, username), 1, 255) AS real_name,
	SUBSTRING(location, 1, 255) AS location,
	SUBSTRING(email, 1, 255) AS email_address,
	SUBSTRING(url, 1, 255) AS website_title,
	SUBSTRING(url, 1, 255) AS website_url, SUBSTRING(aim, 1, 16) AS aim,
	SUBSTRING(icq, 1, 255) AS icq, SUBSTRING(signature, 1, 65534) AS signature,
	SUBSTRING(yahoo, 1, 32) AS yim, SUBSTRING(msn, 1, 255) AS msn,
	IF(email_setting = 0, 0, 1) AS hide_email,
	timezone AS time_offset, SUBSTRING(registration_ip, 1, 255) AS member_ip,
	SUBSTRING(registration_ip, 1, 255) AS member_ip2,
	'' AS lngfile, '' AS buddy_list, '' AS pm_ignore_list, '' AS message_labels,
	'' AS personal_text, '' AS time_format, '' AS avatar, '' AS usertitle,
	'' AS secret_question, '' AS secret_answer, '' AS validation_code,
	'' AS additional_groups, '' AS smiley_set, '' AS password_salt
FROM {$from_prefix}users
WHERE id != 1;
---*

/******************************************************************************/
--- Converting avatars...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('id_attach', 'size', 'filename', 'id_member');

if (!isset($allowedExt))
{
	$allowedExt = array('.gif', '.jpg', '.png');

	$result = convert_query("
		SELECT conf_value
		FROM {$from_prefix}config
		WHERE conf_name = 'o_avatars_dir'
		LIMIT 1");
	list ($oldAttachmentDir) = mysql_fetch_row($result);
	mysql_free_result($result);

	if (!file_exists($oldAttachmentDir))
		$oldAttachmentDir = $_POST['path_from'] . '/' . $oldAttachmentDir;
}

$row['filename'] = false;
foreach ($allowedExt as $ext)
{
	// Found it?
	if (file_exists($oldAttachmentDir . '/' . $row['id_member'] . $ext))
	{
		$row['filename'] = $row['id_member'] . $ext;
		break;
	}
}

if (!empty($row['filename']))
{
	$newfilename = getAttachmentFilename($row['filename'], $id_attach);
	if (copy($oldAttachmentDir . '/' . $row['filename'], $attachmentUploadDir . '/' . $newfilename))
	{
		$rows[] = "$id_attach, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[id_member]";
		$id_attach++;
	}
}
---}
SELECT id AS id_member
FROM {$from_prefix}users
WHERE use_avatar = 1;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
---{
$row['name'] = substr(stripslashes($row['name']), 0, 255);
---}
SELECT id AS id_cat, cat_name AS name, disp_position AS cat_order
FROM {$from_prefix}categories;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE id_board != 0;

---* {$to_prefix}boards
---{
$row['name'] = substr(stripslashes($row['name']), 0, 255);
---}
SELECT
	f.id AS id_board, f.cat_id AS id_cat, f.disp_position AS board_order,
	f.forum_name AS name,
	SUBSTRING(IFNULL(f.forum_desc, ''), 1, 65534) AS description,
	f.num_topics AS num_topics, f.num_posts AS num_posts, '-1,0' AS member_groups
FROM {$from_prefix}forums AS f;
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
	t.id AS id_topic, t.sticky AS is_sticky, t.forum_id AS id_board,
	t.num_replies AS num_replies, t.num_views AS num_views, t.closed AS locked,
	MIN(p.id) AS id_first_msg, t.last_post_id AS id_last_msg
FROM ({$from_prefix}topics AS t, {$from_prefix}posts AS p)
WHERE p.topic_id = t.id
GROUP BY t.id
HAVING id_first_msg != 0
	AND id_last_msg != 0;
---*

---* {$to_prefix}topics (update id_topic)
SELECT t.id_topic, p.poster_id AS id_member_updated
FROM ({$to_prefix}topics AS t, {$from_prefix}posts AS p)
WHERE p.id = t.id_last_msg;
---*

---* {$to_prefix}topics (update id_topic)
SELECT t.id_topic, p.poster_id AS id_member_started
FROM ({$to_prefix}topics AS t, {$from_prefix}posts AS p)
WHERE p.id = t.id_first_msg;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;

---* {$to_prefix}messages 200
SELECT
	p.id AS id_msg, p.topic_id AS id_topic, t.forum_id AS id_board,
	p.posted AS poster_time, p.poster_id AS id_member,
	SUBSTRING(t.subject, 1, 255) AS subject,
	SUBSTRING(IFNULL(u.username, p.poster), 1, 255) AS poster_name,
	SUBSTRING(p.poster_ip, 1, 255) AS poster_ip,
	SUBSTRING(IFNULL(u.email, poster_email), 1, 255) AS poster_email,
	IF(p.hide_smilies = 0, 1, 0) AS smileys_enabled,
	SUBSTRING(REPLACE(p.message, '<br>', '<br />'), 1, 65534) AS body
FROM ({$from_prefix}posts AS p, {$from_prefix}topics AS t)
	LEFT JOIN {$from_prefix}users AS u ON (u.id = p.poster_id)
WHERE t.id = p.topic_id;
---*

/******************************************************************************/
--- Clearing unused tables...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;
TRUNCATE {$to_prefix}pm_recipients;
TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT user_id AS id_member, topic_id AS id_topic
FROM {$from_prefix}subscriptions;
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

	$result = convert_query("
		SELECT
			b.id, IFNULL(b.username, '') AS username, IFNULL(b.ip, '') AS ip,
			IFNULL(b.email, '') AS email, IFNULL(b.message, 'Imported from PunBB') AS message,
			IFNULL(b.expire, 0) AS expire, u.id AS user_id
		FROM {$from_prefix}bans AS b
			LEFT JOIN {$from_prefix}users AS u ON (u.username = b.username)
		LIMIT $_REQUEST[start], 50");
	$ban_time = time();
	while ($row = mysql_fetch_assoc($result))
	{
		convert_query("
			INSERT INTO {$to_prefix}ban_groups
				(name, ban_time, expire_time, reason, notes, cannot_access)
			VALUES ('migrated_ban_$row[id]', $ban_time, $row[expire], '', '$row[message]', 1)");
		$ID_BAN_GROUP = mysql_insert_id();

		if (empty($ID_BAN_GROUP))
			continue;

		if (!empty($row['email']))
		{
			convert_query("
				INSERT INTO {$to_prefix}ban_items
					(ID_BAN_GROUP, email_address, hostname)
				VALUES
					($ID_BAN_GROUP, '$row[email]', '')");
		}
		if (!empty($row['ip']))
		{
			$ips = explode(' ', $row['ip']);
			foreach ($ips as $ip)
			{
				$ip = trim($ip);
				$sections = explode('.', $ip);

				if (empty($sections[0]))
					continue;

				$ip_low1 = $sections[0];
				$ip_high1 = $sections[0];

				$ip_low2 = isset($sections[1]) ? $sections[1] : 0;
				$ip_high2 = isset($sections[1]) ? $sections[1] : 255;

				$ip_low3 = isset($sections[2]) ? $sections[2] : 0;
				$ip_high3 = isset($sections[2]) ? $sections[2] : 255;

				$ip_low4 = isset($sections[3]) ? $sections[3] : 0;
				$ip_high4 = isset($sections[3]) ? $sections[3] : 255;

				convert_query("
					INSERT INTO {$to_prefix}ban_items
						(ID_BAN_GROUP, ip_low1, ip_high1, ip_low2, ip_high2, ip_low3, ip_high3, ip_low4, ip_high4, email_address, hostname)
					VALUES ($ID_BAN_GROUP, $ip_low1, $ip_high1, $ip_low2, $ip_high2, $ip_low3, $ip_high3, $ip_low4, $ip_high4, '', '')");
			}
		}
		if (!empty($row['username']) && !empty($row['user_id']))
		{
			convert_query("
				INSERT INTO {$to_prefix}ban_items
					(ID_BAN_GROUP, id_member, email_address, hostname)
				VALUES
					($ID_BAN_GROUP, $row[user_id], '', '')");
		}
	}

	$_REQUEST['start'] += 50;
	if (mysql_num_rows($result) < 50)
		break;

	mysql_free_result($result);
}

$_REQUEST['start'] = 0;
---}
---#

/******************************************************************************/
--- Converting smileys...
/******************************************************************************/

UPDATE {$to_prefix}smileys
SET hidden = 1;

---{
$specificSmileys = array(
	':)' => 'smiley',
	'=)' => 'smiley',
	':|' => 'undecided',
	'=|' => 'undecided',
	':(' => 'sad',
	'=(' => 'sad',
	':D' => 'grin',
	'=D' => 'grin',
	':o' => 'shocked',
	':O' => 'shocked',
	';)' => 'wink',
	';/' => 'rolleyes',
	':P' => 'tongue',
	':lol:' => 'cheesy',
	':rolleyes:' => 'rolleyes',
	':cool:' => 'cool',
	':mad:' => 'angry',
);

$request = convert_query("
	SELECT MAX(smiley_order)
	FROM {$to_prefix}smileys");
list ($count) = mysql_fetch_row($request);
mysql_free_result($request);

$request = convert_query("
	SELECT code
	FROM {$to_prefix}smileys");
$currentCodes = array();
while ($row = mysql_fetch_assoc($request))
	$currentCodes[] = $row['code'];
mysql_free_result($request);

$rows = array();
foreach ($specificSmileys as $code => $name)
{
	if (in_array($code, $currentCodes))
		continue;

	$count++;
	$rows[] = "'$code', '{$name}.gif', '$name', $count";
}

if (!empty($rows))
	convert_query("
		REPLACE INTO {$to_prefix}smileys
			(code, filename, description, smiley_order)
		VALUES (" . implode("),
			(", $rows) . ")");
---}

ALTER TABLE {$to_prefix}smileys
ORDER BY LENGTH(code) DESC;