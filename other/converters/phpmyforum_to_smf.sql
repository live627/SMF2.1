/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "phpMyForum 4.1.x"
/******************************************************************************/
---~ version: "SMF 1.1"
---~ defines: PMF_INCLUDE
---~ settings: "/config.inc.php"
---~ from_prefix: "`" . $_cfg['DB_NAME']. "`." . $_cfg['DB_PREFIX']. ""
---~ table_test: "{$from_prefix}user"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	u.id AS id_member, SUBSTRING(u.name, 1, 80) AS member_name,
	SUBSTRING(u.name, 1, 255) AS real_name,
	SUBSTRING(u.pass, 1, 64) AS passwd,
	u.gender AS gender,
	SUBSTRING(u.email, 1, 255) AS email_address,
	SUBSTRING(u.icq, 1, 255) AS icq, 
	u.last_login AS last_login, u.reg AS date_registered, u.posts,
	u.geb AS birthdate,
	IF (u.group_id = 1, 1, 0) AS id_group,
	u.posts AS posts, u.post_email AS hide_email,
	IF (u.avatar LIKE 'upload:%','', u.avatar)  AS avatar
FROM {$from_prefix}user AS u;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT 
	id AS id_cat, name AS name, rang AS cat_order
FROM {$from_prefix}board
WHERE is_board = '0';
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

---* {$to_prefix}boards
---{
$ignore_slashes = true;
---}
SELECT
	id AS id_board, SUBSTRING(name, 1, 255) AS name, '-1,0,1,2' AS member_groups, 
	SUBSTRING(info, 1, 65534) AS description, rang AS board_order,
	posts AS num_posts, topics AS num_topics, parent_id AS id_cat, parent_id AS id_parent
FROM {$from_prefix}board
WHERE is_board != '0';
---*

/******************************************************************************/
--- Converting topics...
/******************************************************************************/

TRUNCATE {$to_prefix}topics;
TRUNCATE {$to_prefix}log_topics;
TRUNCATE {$to_prefix}log_boards;
TRUNCATE {$to_prefix}log_mark_read;
TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}topics
SELECT
	t.id AS id_topic, t.board_id AS id_board, 
	t.top AS is_sticky, t.views AS num_views, 
	t.user_id AS id_member_started, 
	t.user_id AS id_member_updated, 
	MIN(p.id) AS id_first_msg, MAX(p.id) AS id_last_msg, 
	t.closed AS locked
FROM {$from_prefix}topic AS t
	INNER JOIN {$from_prefix}post AS p ON (p.topic_id = t.id)
GROUP BY t.id
HAVING id_first_msg != 0
	AND id_last_msg != 0;
---*


/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;

---* {$to_prefix}messages 200
---{
$ignore_slashes = true;
$row['body'] = preg_replace(
array(
		'~\[email=\"(.+?)\"\](.+?)\[\/email\]~is',
		'~\[url=\"(.+?)\"\](.+?)\[\/url\]~is',
		'~\[color=\"(.+?)\"\](.+?)\[\/color\]~is',
		'~\[font=\"(.+?)\"\](.+?)\[\/font\]~is',
		'~\[align=\"(.+?)\"\](.+?)\[\/align\]~is',
		'~\[size=\"(.+?)0\"\](.+?)\[\/size\]~is',
		'~\[field=\"(.+?)\"\](.+?)\[\/field\]~is',
		'~\[list=a\]~is',
		'~\[list=1\]~is',
		'~\[\/list=(.+?)\]~is',
		'~\[hidden\](.+?)\[\/hidden\]~is',
	),
	array(
		'[email=$1]$2[/email]',
		'[url=$1]$2[/url]',
		'[color=$1]$2[/color]',
		'[font=$1]$2[/font]',
		'[$1]$2[/$1]',
		'[size=$1pt]$2[/size]',
		'<fieldset><legend>$1</legend>$2</fieldset>',
		'[list type=lower-alpha]',
		'[list type=decimal]',
		'[/list]',
		'[spoiler]$1[/spoiler]',
	),
	trim($row['body'])
);

---}
SELECT
	p.id AS id_msg, p.topic_id AS id_topic,
	t.board_id  AS id_board, p.post_date AS poster_time, 
	p.user_id AS id_member,	p.edit_time AS id_msg_MODIFIED,
	t.name AS subject,
	u.name AS poster_name, 
	u.email AS poster_email,	p.ip AS poster_ip, 
	'1' AS smileys_enabled, p.edit_time AS modified_time, 
	IF (p.edit_time > 0 , u.name, '') AS modified_name, p.text AS body, 'xx' AS icon
FROM {$from_prefix}post AS p
	INNER JOIN {$from_prefix}topic AS t ON (t.id = p.topic_id)
	LEFT JOIN {$from_prefix}user AS u ON (u.id = p.user_id)
GROUP BY p.id;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/
TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT 
	user_id AS id_member, topic_id AS id_topic
FROM {$from_prefix}topic_abo;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/
TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT 
	user_id AS id_member, board_id AS id_board
FROM {$from_prefix}board_abo;
---*

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT 
	user_id AS id_member, board_ID AS id_board
FROM {$from_prefix}board_mod;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
---{
$ignore_slashes = true;
$row['body'] = preg_replace(
array(
		'~\[email=\"(.+?)\"\](.+?)\[\/email\]~is',
		'~\[url=\"(.+?)\"\](.+?)\[\/url\]~is',
		'~\[color=\"(.+?)\"\](.+?)\[\/color\]~is',
		'~\[font=\"(.+?)\"\](.+?)\[\/font\]~is',
		'~\[align=\"(.+?)\"\](.+?)\[\/align\]~is',
		'~\[size=\"(.+?)0\"\](.+?)\[\/size\]~is',
		'~\[field=\"(.+?)\"\](.+?)\[\/field\]~is',
		'~\[list=a\]~is',
		'~\[list=1\]~is',
		'~\[\/list=(.+?)\]~is',
		'~\[hidden\](.+?)\[\/hidden\]~is',
	),
	array(
		'[email=$1]$2[/email]',
		'[url=$1]$2[/url]',
		'[color=$1]$2[/color]',
		'[font=$1]$2[/font]',
		'[$1]$2[/$1]',
		'[size=$1pt]$2[/size]',
		'<fieldset><legend>$1</legend>$2</fieldset>',
		'[list type=lower-alpha]',
		'[list type=decimal]',
		'[/list]',
		'[spoiler]$1[/spoiler]',
	),
	trim($row['body'])
);

---}
SELECT
	pm.id AS id_pm, pm.from_id AS id_member_from, pm.send AS msgtime,
	IF(u.name IS NULL, 'Guest', SUBSTRING(u.name, 1, 255)) AS from_name,
	SUBSTRING(pm.name, 1, 255) AS subject,
	SUBSTRING(pm.text, 1, 65534) AS body
FROM {$from_prefix}private AS pm
	LEFT JOIN {$from_prefix}user AS u ON (u.id = pm.from_id);
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT 
	id AS id_pm, to_id AS id_member, view AS is_read,
	del AS deleted, '-1' AS labels
FROM {$from_prefix}private;
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/
TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
---{

$request = convert_query("
	SELECT topic_id
	FROM {$from_prefix}poll
	WHERE id = $row[id_poll]
	LIMIT 1");
list ($id_topic) = convert_fetch_row($request);

convert_free_result($request);

if(isset($id_topic))
	convert_query("
		UPDATE {$to_prefix}topics
		SET id_poll = $row[id_poll]
		WHERE id_topic = $id_topic
		LIMIT 1");
---}
SELECT
	p.id AS id_poll, SUBSTRING(p.name , 1, 255) AS question,
	t.user_id AS id_member, p.days  AS expire_time,
	SUBSTRING(IFNULL(u.name, ''), 1, 255) AS poster_name,
	'1' AS max_votes
FROM {$from_prefix}poll AS p 
	LEFT JOIN {$from_prefix}topic AS t ON (p.topic_id = t.id)
	LEFT JOIN {$from_prefix}user AS u ON (u.id = t.user_id);
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
SELECT 
	poll_id AS id_poll, rang AS id_choice, SUBSTRING(text, 1, 255) AS label, 
	votes AS votes
FROM {$from_prefix}poll_option;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
---{
$ignore = true;
---}
SELECT 
	o.poll_id AS id_poll, v.user_id AS id_member, '1' AS id_choice
FROM {$from_prefix}poll_vote AS v
	INNER JOIN {$from_prefix}poll_option AS o ON (v.option_id = o.id);
---*

/******************************************************************************/
--- Converting smileys...
/******************************************************************************/
TRUNCATE  {$to_prefix}smileys;
---* {$to_prefix}smileys

---{
$no_add = true;
$keys = array('code', 'filename', 'description', 'smpath', 'hidden');

if (!isset($smf_smileys_directory))
{
	/* Find the path for SMF smileys. */
	$request = convert_query("
	SELECT value
	FROM {$to_prefix}settings
	WHERE variable = 'smileys_dir'
	LIMIT 1");

	list ($smf_smileys_directory) = convert_fetch_row($request);
	convert_free_result($request);
}
	
/* enable custom smileys */
$request = convert_query("
	SELECT value
	FROM {$to_prefix}settings
	WHERE variable = 'smiley_enable'
	LIMIT 1");

list ($smiley_enable) = convert_fetch_row($request);
convert_free_result($request);	
	
if (isset($smiley_enable))
	convert_query("
		UPDATE {$to_prefix}settings
		SET value = '1' 
		WHERE variable='smiley_enable'");
	
else
	convert_query("
		REPLACE INTO {$to_prefix}settings
			(variable, value)
		VALUES ('smiley_enable','1')");

$row['filename'] = substr(strrchr($row['filename'], '/'),1);		
$row['description'] = htmlspecialchars($row['description'],ENT_QUOTES);			

if (is_file($_POST['path_from'] . '/images/default/smilies/'. $row['filename'])) 
{
 	copy($_POST['path_from'] . '/images/default/smilies/'. $row['filename'] , $smf_smileys_directory . '/default/'.$row['filename']);
	
	$request2 = convert_query("
		INSERT IGNORE INTO {$to_prefix}smileys
			(code, filename, description, hidden)
		VALUES ('$row[code]','$row[filename]', '$row[description]','0')");
}
---}
SELECT 
	filename AS filename, code AS code, name AS description
FROM {$from_prefix}smilie;
---*

/******************************************************************************/
--- Converting attachments...
/******************************************************************************/
TRUNCATE  {$to_prefix}attachments;

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('id_attach', 'size', 'filename', 'id_msg', 'downloads');

$newfilename = getLegacyAttachmentFilename($row['filename'], $id_attach);

if (copy($_POST['path_from'] . '/attachments/' . $row['id_attach'] , $attachmentUploadDir . '/' . $newfilename))
	$rows[] = "$row[id_attach], " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[id_msg], $row[downloads]";
	
---}
SELECT
	id AS id_attach, post_id AS id_msg, views AS downloads, filename AS filename
FROM {$from_prefix}attachment;
---*

/******************************************************************************/
--- Converting avatars...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('id_attach', 'size', 'filename', 'id_member');

// Find the path for SMF avatars.
	$request = convert_query("
		SELECT MAX(id_attach)
		FROM {$to_prefix}attachments
		LIMIT 1");

	list ($id_attach) = convert_fetch_row($request);
	convert_free_result($request);
	$id_attach++;

$row['filename'] = substr(strrchr($row['avatar'], ':'),1);
$newfilename = 'avatar_' . $row['id_member'] . strrchr($row['filename'], '.');

if (strlen($newfilename) <= 255 && copy($_POST['path_from'] . '/images/avatars/' . $row['filename'] , $attachmentUploadDir . '/' . $newfilename)) 
	$rows[] = "$id_attach, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($newfilename) . "', $row[id_member]";
	
---}
SELECT 
	id AS id_member, avatar
FROM {$from_prefix}user
WHERE avatar LIKE 'upload:%';
---*