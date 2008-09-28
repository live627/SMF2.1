/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "vBulletin 3.7"
/******************************************************************************/
---~ version: "SMF 1.1"
---~ settings: "/admin/config.php", "/includes/config.php"
---~ from_prefix: "`" . $config['Database']['dbname'] . "`." . $config['Database']['tableprefix'] . ""
---~ table_test: "{$from_prefix}user"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
---{
$ignore = true;
$row['signature'] = preg_replace(
	array(
		'~\[(quote)=([^\]]+)\]~i',
		'~\[(.+?)=&quot;(.+?)&quot;\]~is',
	),
	array(
		'[$1=&quot;$2&quot;]',
		'[$1=$2]',
	), strtr($row['signature'], array('"' => '&quot;')));
$row['signature'] = substr($row['signature'], 0, 65534);
---}
SELECT
	u.userid AS id_member, SUBSTRING(u.username, 1, 80) AS member_name,
	SUBSTRING(u.username, 1, 255) AS real_name,
	SUBSTRING(u.password, 1, 64) AS passwd,
	SUBSTRING(u.email, 1, 255) AS email_address,
	SUBSTRING(u.homepage, 1, 255) AS website_title,
	SUBSTRING(u.homepage, 1, 255) AS website_url,
	SUBSTRING(u.icq, 1, 255) AS icq, SUBSTRING(u.aim, 1, 16) AS aim,
	SUBSTRING(u.yahoo, 1, 32) AS yim, SUBSTRING(u.msn, 1, 255) AS msn,
	SUBSTRING(IF(u.customtitle, u.usertitle, ''), 1, 255) AS usertitle,
	u.lastvisit AS last_login, u.joindate AS date_registered, u.posts,
	u.reputation AS karma_good, u.birthday_search AS birthdate,
	SUBSTRING(u.ipaddress, 1, 255) AS member_ip,
	SUBSTRING(u.ipaddress, 1, 255) AS member_ip2,
	CASE
		WHEN u.usergroupid = 6 THEN 1
		WHEN u.usergroupid = 5 THEN 2
		WHEN u.usergroupid = 7 THEN 2
		ELSE 0
	END AS id_group,
	CASE WHEN u.usergroupid IN (3, 4) THEN 0 ELSE 1 END AS is_activated,
	SUBSTRING(u.salt, 1, 5) AS password_salt,
	SUBSTRING(ut.signature, 1, 65534) AS signature, '' AS lngfile,
	'' AS buddy_list, '' AS pm_ignore_list, '' AS message_labels,
	'' AS personal_text, '' AS time_format, '' AS avatar, '' AS secret_question,
	'' AS secret_answer, '' AS validation_code, '' AS additional_groups,
	'' AS smiley_set
FROM {$from_prefix}user AS u
	LEFT JOIN {$from_prefix}usertextfield AS ut ON (ut.userid = u.userid)
WHERE u.userid != 0;
---*

/******************************************************************************/
--- Converting administrators...
/******************************************************************************/

---{
$request = convert_query("
	SELECT userid AS id_member
	FROM {$from_prefix}administrator");
$admins = array();
while ($row = mysql_fetch_assoc($request))
	$admins[] = $row['id_member'];
convert_free_result($request);

convert_query("
	UPDATE {$to_prefix}members
	SET id_group = 1
	WHERE id_member IN (" . implode(',', $admins) .  ")");
---}

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

ALTER TABLE {$to_prefix}categories
CHANGE COLUMN id_cat id_cat SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN cat_order cat_order SMALLINT(5) NOT NULL;

---* {$to_prefix}categories
SELECT
	forumid AS id_cat, SUBSTRING(title, 1, 255) AS name,
	displayorder AS cat_order, '' AS can_collapse
FROM {$from_prefix}forum
WHERE parentid = -1
ORDER BY cat_order;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE id_board != 0;

ALTER TABLE {$to_prefix}boards
CHANGE COLUMN id_board id_board SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN id_cat id_cat SMALLINT(5) NOT NULL;

/* The converter will set id_cat for us based on id_parent being wrong. */
---* {$to_prefix}boards
SELECT
	forumid AS id_board, SUBSTRING(title, 1, 255) AS name,
	SUBSTRING(description, 1, 65534) AS description,
	displayorder AS board_order, replycount AS num_posts,
	threadcount AS num_topics, parentid AS id_parent, '-1,0' AS member_groups
FROM {$from_prefix}forum
WHERE parentid != -1;
---*

/******************************************************************************/
--- Assigning boards to categories...
/******************************************************************************/

---{
$request = convert_query("
	SELECT forumid AS id_cat
	FROM {$from_prefix}forum
	WHERE parentid = '-1'");

$cats = array();
while ($row = mysql_fetch_assoc($request))
	$cats[$row['id_cat']] = $row['id_cat'];
convert_free_result($request);

// Get the boards now
$request = convert_query("
	SELECT forumid AS id_board, parentid AS id_cat
	FROM {$from_prefix}forum
	WHERE parentid != '-1'");

while ($row = mysql_fetch_assoc($request))
{
	foreach ($cats as $key => $value)
	{
		if ($key == $row['id_cat'])
		{
			convert_query("
				UPDATE {$to_prefix}boards
				SET id_cat = '$key'
				WHERE id_board = '$row[id_board]'");
		}
	}
}
convert_free_result($request);

// id_parent is 0 when the id_cat and id_parent are equal.
convert_query("
	UPDATE {$to_prefix}boards
	SET id_parent = 0
	WHERE id_parent = id_cat");
---}

/******************************************************************************/
--- Converting topics...
/******************************************************************************/

TRUNCATE {$to_prefix}topics;
TRUNCATE {$to_prefix}log_topics;
TRUNCATE {$to_prefix}log_boards;
TRUNCATE {$to_prefix}log_mark_read;

---* {$to_prefix}topics
---{
$ignore = true;
---}
SELECT
	t.threadid AS id_topic, t.forumid AS id_board, t.sticky AS is_sticky,
	t.pollid AS id_poll, t.views AS num_views, t.postuserid AS id_member_started,
	ul.userid AS id_member_updated, t.replycount AS num_replies,
	IF(t.open, 0, 1) AS locked, MIN(p.postid) AS id_first_msg,
	MAX(p.postid) AS id_last_msg
FROM ({$from_prefix}thread AS t, {$from_prefix}post AS p)
	LEFT JOIN {$from_prefix}user AS ul ON (ul.username = t.lastposter)
WHERE p.threadid = t.threadid
GROUP BY t.threadid
HAVING id_first_msg != 0
	AND id_last_msg != 0;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
---{
$ignore = true;
$row['body'] = preg_replace(
	array(
		'~\[(quote)=([^\]]+)\]~i',
		'~\[(.+?)=&quot;(.+?)&quot;\]~is',
		'~\[INDENT\]~is',
		'~\[/INDENT\]~is',
		'~\[LIST=1\]~is',
	),
	array(
		'[$1=&quot;$2&quot;]',
		'[$1=$2]',
		'	',
		'',
		'[list type=decimal]',
	), strtr($row['body'], array('"' => '&quot;')));

// Code tags are mean
$replace = array();
preg_match('~\[code\](.+?)\[/code\]~is', $row['body'], $matches);
foreach ($matches as $temp)
	$replace[$temp] = htmlspecialchars($temp);
$row['body'] = substr(strtr($row['body'], $replace), 0, 65534);
---}
SELECT
	p.postid AS id_msg, p.threadid AS id_topic, p.dateline AS poster_time,
	p.userid AS id_member,
	SUBSTRING(IF(p.title = '', t.title, p.title), 1, 255) AS subject,
	SUBSTRING(p.username, 1, 255) AS poster_name,
	SUBSTRING(p.ipaddress, 1, 255) AS poster_ip, t.forumid AS id_board,
	p.allowsmilie AS smileys_enabled,
	REPLACE(p.pagetext, '<br>', '<br />') AS body, '' AS poster_email,
	'' AS modified_name, 'xx' AS icon
FROM ({$from_prefix}post AS p, {$from_prefix}thread AS t)
WHERE t.threadid = p.threadid;
---*

---* {$to_prefix}messages (update id_msg)
SELECT postid AS id_msg, username AS modified_name, dateline AS modified_time
FROM {$from_prefix}editlog
ORDER BY dateline;
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
---{
$ignore = true;
---}
SELECT
	p.pollid AS id_poll, SUBSTRING(p.question, 1, 255) AS question,
	IF(p.active = 0, 1, 0) AS voting_locked, p.multiple AS max_votes,
	SUBSTRING(IFNULL(t.postusername, 'Guest'), 1, 255) AS poster_name,
	IF(p.timeout = 0, 0, p.dateline + p.timeout * 86400) AS expire_time,
	t.postuserid AS id_member
FROM {$from_prefix}poll AS p
	LEFT JOIN {$from_prefix}thread AS t ON (t.pollid = p.pollid);
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
$ignore = true;
$no_add = true;
$keys = array('id_poll', 'id_choice', 'label', 'votes');

$options = explode('|||', $row['options']);
$votes = explode('|||', $row['votes']);
for ($i = 0, $n = count($options); $i < $n; $i++)
	$rows[] = $row['id_poll'] . ', ' . ($i + 1) . ", '" . addslashes($options[$i]) . "', '" . @$votes[$i] . "'";
---}
SELECT pollid AS id_poll, options, votes
FROM {$from_prefix}poll;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT pollid AS id_poll, userid AS id_member, voteoption AS id_choice
FROM {$from_prefix}pollvote;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
---{
$ignore = true;
$row['body'] = preg_replace(
	array(
		'~\[(quote)=([^\]]+)\]~i',
		'~\[(.+?)=&quot;(.+?)&quot;\]~is',
		'~\[INDENT\]~is',
		'~\[/INDENT\]~is',
		'~\[LIST=1\]~is',
	),
	array(
		'[$1=&quot;$2&quot;]',
		'[$1=$2]',
		'	',
		'',
		'[list=decimal]',
	), strtr($row['body'], array('"' => '&quot;')));

// Code tags are mean
$replace = array();
preg_match('~\[code\](.+?)\[/code\]~is', $row['body'], $matches);
foreach ($matches as $temp)
	$replace[$temp] = htmlspecialchars($temp);
$row['body'] = substr(strtr($row['body'], $replace), 0, 65534);
---}
SELECT
	pm.pmid AS id_pm, pmt.fromuserid AS id_member_from, pmt.dateline AS msgtime,
	SUBSTRING(pmt.fromusername, 1, 255) AS from_name,
	SUBSTRING(pmt.title, 1, 255) AS subject,
	SUBSTRING(REPLACE(pmt.message, '<br>', '<br />'), 1, 65534) AS body
FROM ({$from_prefix}pm AS pm, {$from_prefix}pmtext AS pmt)
WHERE pmt.pmtextid = pm.pmtextid
	AND pm.folderid != -1;
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT pmid AS id_pm, userid AS id_member, messageread != 0 AS is_read, '-1' AS labels
FROM {$from_prefix}pm;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT userid AS id_member, threadid AS id_topic
FROM {$from_prefix}subscribethread;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
SELECT userid AS id_member, forumid AS id_board
FROM {$from_prefix}subscribeforum;
---*

/******************************************************************************/
--- Converting smileys...
/******************************************************************************/

UPDATE {$to_prefix}smileys
SET hidden = 1;

---{
$specificSmileys = array(
	':cool:' => 'cool',
	':(' => 'sad',
	':confused:' => 'huh',
	':mad:' => 'angry',
	':rolleyes:' => 'rolleyes',
	':eek:' => 'shocked',
	':p' => 'tongue',
	':o' => 'embarrassed',
	';)' => 'wink',
	':D' => 'grin',
	':)' => 'smiley',
);

$request = convert_query("
	SELECT MAX(smiley_order)
	FROM {$to_prefix}smileys");
list ($count) = convert_fetch_row($request);
convert_free_result($request);

$request = convert_query("
	SELECT code
	FROM {$to_prefix}smileys");
$currentCodes = array();
while ($row = mysql_fetch_assoc($request))
	$currentCodes[] = $row['code'];
convert_free_result($request);

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

/******************************************************************************/
--- Converting attachments...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('id_attach', 'size', 'filename', 'id_msg', 'downloads', 'width', 'height');

if (!isset($vb_settings))
{
	$result = convert_query("
		SELECT varname, value
		FROM {$from_prefix}setting
		WHERE varname IN ('attachfile', 'attachpath', 'usefileavatar', 'avatarpath')
		LIMIT 4");
	$vb_settings = array();
	while ($row2 = mysql_fetch_assoc($result))
	{
		if (substr($row2['value'], 0, 2) == './')
			$row2['value'] = $_POST['path_from'] . substr($row2['value'], 1);
		$vb_settings[$row2['varname']] = $row2['value'];
	}
	convert_free_result($result);
}

// Is this an image???
$attachmentExtension = strtolower(substr(strrchr($row['filename'], '.'), 1));
if (!in_array($attachmentExtension, array('jpg', 'jpeg', 'gif', 'png')))
	$attachmentExtention = '';

// Set the default empty values.
$width = '0';
$height = '0';

$newfilename = getAttachmentFilename($row['filename'], $id_attach);
if (empty($vb_settings['attachfile']))
{
	$fp = @fopen($attachmentUploadDir . '/' . $newfilename, 'wb');
	if (!$fp)
		return;

	fwrite($fp, $row['filedata']);
	fclose($fp);
}
elseif ($vb_settings['attachfile'] == 1)
{
	if (!copy($vb_settings['attachpath'] . '/' . $row['userid'] . '/' . $row['attachmentid'] . '.attach', $attachmentUploadDir . '/' . $newfilename))
		return;
}
elseif ($vb_settings['attachfile'] == 2)
{
	if (!copy($vb_settings['attachpath'] . '/' . chunk_split($row['userid'], 1, '/') . $row['attachmentid'] . '.attach', $attachmentUploadDir . '/' . $newfilename))
		return;
}

// Is an an image?
if (!empty($attachmentExtension))
	list ($width, $height) = getimagesize($attachmentUploadDir . '/' . $newfilename);

$rows[] = "$id_attach, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[id_msg], $row[downloads], '$width', '$height'";
$id_attach++;
---}
SELECT
	postid AS id_msg, counter AS downloads, filename, filedata, userid,
	attachmentid
FROM {$from_prefix}attachment;
---*

/******************************************************************************/
--- Converting avatars...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('id_attach', 'size', 'filename', 'id_member');

if (!isset($vb_settings))
{
	$result = convert_query("
		SELECT varname, value
		FROM {$from_prefix}setting
		WHERE varname IN ('attachfile', 'attachpath', 'usefileavatar', 'avatarpath')
		LIMIT 4");
	$vb_settings = array();
	while ($row2 = mysql_fetch_assoc($result))
	{
		if (substr($row2['value'], 0, 2) == './')
			$row2['value'] = $_POST['path_from'] . substr($row2['value'], 1);
		$vb_settings[$row2['varname']] = $row2['value'];
	}
	convert_free_result($result);
}


$newfilename = getAttachmentFilename($row['filename'], $id_attach);
if (strlen($newfilename) > 255)
	return;
elseif (empty($vb_settings['usefileavatar']))
{
	$fp = @fopen($attachmentUploadDir . '/' . $newfilename, 'wb');
	if (!$fp)
		return;

	fwrite($fp, $row['filedata']);
	fclose($fp);
}
elseif (!copy($vb_settings['avatarpath'] . '/avatar' . $row['id_member'] . '_' . $row['avatarrevision'] . '.gif', $attachmentUploadDir . '/' . $newfilename))
	return;

$rows[] = "$id_attach, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[id_member]";
$id_attach++;
---}
SELECT ca.userid AS id_member, ca.filedata, ca.filename, u.avatarrevision
FROM ({$from_prefix}customavatar AS ca, {$from_prefix}user AS u)
WHERE u.userid = ca.userid;
---*