/* ATTENTION: You don't need to run or use this file!  The upgrade.php script does everything for you! */

/******************************************************************************/
--- Adding Open ID support.
/******************************************************************************/

---# Adding Open ID Assocation table...
CREATE TABLE {$db_prefix}openid_assoc (
	server_url text NOT NULL,
	handle varchar(255) NOT NULL,
	secret text NOT NULL,
	issued int NOT NULL,
	expires int NOT NULL,
	assoc_type varchar(64) NOT NULL,
	PRIMARY KEY (server_url, handle)
);
---#

/******************************************************************************/
--- Updating custom fields.
/******************************************************************************/

---# Adding search ability to custom fields.
---{
if ($smcFunc['db_server_info'] < 8.0)
{
	upgrade_query("
		ALTER TABLE {$db_prefix}custom_fields
		ADD COLUMN can_search smallint");

	upgrade_query("
		UPDATE {$db_prefix}custom_fields
		SET can_search = 0");

	upgrade_query("
		ALTER TABLE {$db_prefix}custom_fields
		ALTER COLUMN can_search SET NOT NULL");

	upgrade_query("
		ALTER TABLE {$db_prefix}custom_fields
		ALTER COLUMN can_search SET default '0'");
}
else
{
	upgrade_query("
		ALTER TABLE {$db_prefix}custom_fields
		ADD COLUMN can_search smallint NOT NULL default '0'");
}
---}
---#

---# Enhancing privacy settings for custom fields.
---{
if (isset($modSettings['smfVersion']) && $modSettings['smfVersion'] <= '2.0 Beta 1')
{
upgrade_query("
	UPDATE {$db_prefix}custom_fields
	SET private = 2
	WHERE private = 1");
}
if (isset($modSettings['smfVersion']) && $modSettings['smfVersion'] < '2.0 Beta 4')
{
upgrade_query("
	UPDATE {$db_prefix}custom_fields
	SET private = 3
	WHERE private = 2");
}
---}
---#

---# Changing default_values column to a larger field type...
ALTER TABLE {$db_prefix}custom_fields
ALTER COLUMN default_value TYPE varchar(255);
---#

---# Adding new custom fields columns.
ALTER TABLE {$db_prefix}custom_fields
ADD enclose text NOT NULL;

ALTER TABLE {$db_prefix}custom_fields
ADD placement smallint NOT NULL default '0';
---#

/******************************************************************************/
--- Adding new board specific features.
/******************************************************************************/

---# Implementing board redirects.
---{
if ($db_type == 'postgresql' && $smcFunc['db_server_info'] < 8.0)
{
	upgrade_query("
		ALTER TABLE {$db_prefix}boards
		ADD COLUMN redirect varchar(255)");

	upgrade_query("
		UPDATE {$db_prefix}boards
		SET redirect = ''");

	upgrade_query("
		ALTER TABLE {$db_prefix}boards
		ALTER COLUMN redirect SET NOT NULL");

	upgrade_query("
		ALTER TABLE {$db_prefix}boards
		ALTER COLUMN redirect SET default ''");
}
else
{
	upgrade_query("
		ALTER TABLE {$db_prefix}boards
		ADD COLUMN redirect varchar(255) NOT NULL DEFAULT ''");
}
---}
---#

/******************************************************************************/
--- Adding search engine tracking.
/******************************************************************************/

---# Creating spider sequence.
CREATE SEQUENCE {$db_prefix}spiders_seq;
---#

---# Creating spider table.
CREATE TABLE {$db_prefix}spiders (
	id_spider smallint NOT NULL default nextval('{$db_prefix}spiders_seq'),
	spider_name varchar(255) NOT NULL,
	user_agent varchar(255) NOT NULL,
	ip_info varchar(255) NOT NULL,
	PRIMARY KEY (id_spider)
);

INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (1, 'Google', 'googlebot', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (2, 'Yahoo!', 'slurp', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (3, 'MSN', 'msnbot', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (4, 'Google (Mobile)', 'Googlebot-Mobile', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (5, 'Google (Image)', 'Googlebot-Image', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (6, 'Google (AdSense)', 'Mediapartners-Google', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (7, 'Google (Adwords)', 'AdsBot-Google', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (8, 'Yahoo! (Mobile)', 'YahooSeeker/M1A1-R2D2', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (9, 'Yahoo! (Image)', 'Yahoo-MMCrawler', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (10, 'MSN (Mobile)', 'MSNBOT_Mobile', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (11, 'MSN (Media)', 'msnbot-media', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (12, 'Cuil', 'twiceler', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (13, 'Ask', 'Teoma', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (14, 'Baidu', 'Baiduspider', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (15, 'Gigablast', 'Gigabot', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (16, 'InternetArchive', 'ia_archiver-web.archive.org', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (17, 'Alexa', 'ia_archiver', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (18, 'Omgili', 'omgilibot', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (19, 'EntireWeb', 'Speedy Spider', '');
---#

---# Removing a spider.
---{
	upgrade_query("
		DELETE FROM {$db_prefix}spiders
		WHERE user_agent = 'yahoo' 
			AND spider_name = 'Yahoo! (Publisher)'
	");
---}
---#

---# Sequence for table log_spider_hits.
CREATE SEQUENCE {$db_prefix}log_spider_hits_seq;
---#

---# Creating spider hit tracking table.
CREATE TABLE {$db_prefix}log_spider_hits (
	id_hit int default nextval('{$db_prefix}log_spider_hits_seq'),
	id_spider smallint NOT NULL default '0',
	log_time int NOT NULL,
	url varchar(255) NOT NULL,
	processed smallint NOT NULL default '0'
);

CREATE INDEX {$db_prefix}log_spider_hits_id_spider ON {$db_prefix}log_spider_hits (id_spider);
CREATE INDEX {$db_prefix}log_spider_hits_log_time ON {$db_prefix}log_spider_hits (log_time);
CREATE INDEX {$db_prefix}log_spider_hits_processed ON {$db_prefix}log_spider_hits (processed);
---#

---# Creating spider statistic table.
CREATE TABLE {$db_prefix}log_spider_stats (
  id_spider smallint NOT NULL default '0',
  page_hits smallint NOT NULL default '0',
  last_seen int NOT NULL default '0',
  stat_date date NOT NULL default '0001-01-01',
  PRIMARY KEY (stat_date, id_spider)
);
---#


/******************************************************************************/
--- Adding new forum settings.
/******************************************************************************/

---# Enable cache if upgrading from 1.1 and lower.
---{
if (isset($modSettings['smfVersion']) && $modSettings['smfVersion'] <= '2.0 Beta 1')
{
	$request = upgrade_query("
		SELECT value
		FROM {$db_prefix}settings
		WHERE variable = 'cache_enable'");
	list ($cache_enable) = $smcFunc['db_fetch_row']($request);

	// No cache before 1.1.
	if ($smcFunc['db_num_rows']($request) == 0)
		upgrade_query("
			INSERT INTO {$db_prefix}settings
				(variable, value)
			VALUES ('cache_enable', '1')");
	elseif (empty($cache_enable))
		upgrade_query("
			UPDATE {$db_prefix}settings
			SET value = '1'
			WHERE variable = 'cache_enable'");
}
---}
---#

/******************************************************************************/
--- Adding misc functionality.
/******************************************************************************/

---# Converting "log_online".
ALTER TABLE {$db_prefix}log_online DROP CONSTRAINT {$db_prefix}log_online_log_time;
ALTER TABLE {$db_prefix}log_online DROP CONSTRAINT {$db_prefix}log_online_id_member;
DROP TABLE {$db_prefix}log_online;
CREATE TABLE {$db_prefix}log_online (
  session varchar(32) NOT NULL default '',
  log_time int NOT NULL default '0',
  id_member int NOT NULL default '0',
  id_spider smallint NOT NULL default '0',
  ip int NOT NULL default '0',
  url text NOT NULL,
  PRIMARY KEY (session)
);
CREATE INDEX {$db_prefix}log_online_log_time ON {$db_prefix}log_online (log_time);
CREATE INDEX {$db_prefix}log_online_id_member ON {$db_prefix}log_online (id_member);
---#

---# Adding guest voting - part 1...
---{
if ($smcFunc['db_server_info'] < 8.0)
{
	upgrade_query("
		ALTER TABLE {$db_prefix}polls
		ADD COLUMN guest_vote smallint");

	upgrade_query("
		UPDATE {$db_prefix}polls
		SET guest_vote = 0");

	upgrade_query("
		ALTER TABLE {$db_prefix}polls
		ALTER COLUMN guest_vote SET NOT NULL");

	upgrade_query("
		ALTER TABLE {$db_prefix}polls
		ALTER COLUMN guest_vote SET default '0'");
}
else
{
	upgrade_query("
		ALTER TABLE {$db_prefix}polls
		ADD COLUMN guest_vote smallint NOT NULL default '0'");
}
---}
---#

---# Adding guest voting - part 2...
DELETE FROM {$db_prefix}log_polls
WHERE id_member < 0;

ALTER TABLE {$db_prefix}log_polls DROP CONSTRAINT {$db_prefix}log_polls_pkey;

CREATE INDEX {$db_prefix}log_polls_id_poll ON {$db_prefix}log_polls (id_poll, id_member, id_choice);
---#

---# Adding admin log...
---{
if ($db_type == 'postgresql' && $smcFunc['db_server_info'] < 8.0)
{
	upgrade_query("
		ALTER TABLE {$db_prefix}log_actions
		ADD COLUMN id_log smallint");

	upgrade_query("
		UPDATE {$db_prefix}log_actions
		SET id_log = 1");

	upgrade_query("
		ALTER TABLE {$db_prefix}log_actions
		ALTER COLUMN id_log SET NOT NULL");

	upgrade_query("
		ALTER TABLE {$db_prefix}log_actions
		ALTER COLUMN id_log SET default '1'");
}
else
{
	upgrade_query("
		ALTER TABLE {$db_prefix}log_actions
		ADD COLUMN id_log smallint NOT NULL default '1'");
}
---}
---#

---# Adding search ability to custom fields.
---{
if ($smcFunc['db_server_info'] < 8.0)
{
	upgrade_query("
		ALTER TABLE {$db_prefix}members
		ADD COLUMN passwd_flood varchar(12)");

	upgrade_query("
		UPDATE {$db_prefix}members
		SET passwd_flood = ''");

	upgrade_query("
		ALTER TABLE {$db_prefix}members
		ALTER COLUMN passwd_flood SET NOT NULL");

	upgrade_query("
		ALTER TABLE {$db_prefix}members
		ALTER COLUMN passwd_flood SET default ''");
}
else
{
	upgrade_query("
		ALTER TABLE {$db_prefix}members
		ADD COLUMN passwd_flood varchar(12) NOT NULL default ''");
}
---}
---#

/******************************************************************************/
--- Adding weekly maintenance task.
/******************************************************************************/

---# Adding scheduled task...
INSERT INTO {$db_prefix}scheduled_tasks (next_time, time_offset, time_regularity, time_unit, disabled, task) VALUES (0, 0, 1, 'w', 0, 'weekly_maintenance');
---#

/******************************************************************************/
--- Adding log pruning.
/******************************************************************************/

---# Adding pruning option...
INSERT INTO {$db_prefix}settings (variable, value) VALUES ('pruningOptions', '30,180,180,180,30');
---#

/******************************************************************************/
--- Updating mail queue functionality.
/******************************************************************************/

---# Adding private to mail queue...
---{
if ($smcFunc['db_server_info'] < 8.0)
{
	upgrade_query("
		ALTER TABLE {$db_prefix}mail_queue
		ADD COLUMN private smallint");

	upgrade_query("
		UPDATE {$db_prefix}mail_queue
		SET private = 0");

	upgrade_query("
		ALTER TABLE {$db_prefix}mail_queue
		ALTER COLUMN private SET NOT NULL");

	upgrade_query("
		ALTER TABLE {$db_prefix}mail_queue
		ALTER COLUMN private SET default '0'");
}
else
{
	upgrade_query("
		ALTER TABLE {$db_prefix}mail_queue
		ADD COLUMN private smallint NOT NULL default '0'");
}
---}
---#

/******************************************************************************/
--- Updating attachments.
/******************************************************************************/

---# Adding multiple attachment path functionality.
---{
if ($smcFunc['db_server_info'] < 8.0)
{
	upgrade_query("
		ALTER TABLE {$db_prefix}attachments
		ADD COLUMN id_folder smallint");

	upgrade_query("
		UPDATE {$db_prefix}attachments
		SET id_folder = 1");

	upgrade_query("
		ALTER TABLE {$db_prefix}attachments
		ALTER COLUMN id_folder SET NOT NULL");

	upgrade_query("
		ALTER TABLE {$db_prefix}attachments
		ALTER COLUMN id_folder SET default '1'");
}
else
{
	upgrade_query("
		ALTER TABLE {$db_prefix}attachments
		ADD COLUMN id_folder smallint NOT NULL default '1'");
}
---}
---#

---# Adding file hash.
---{
	upgrade_query("
		ALTER TABLE {$db_prefix}attachments
		ADD COLUMN file_hash varchar(40) NOT NULL default ''");
---}
---#

/******************************************************************************/
--- Adding restore topic from recycle.
/******************************************************************************/

---# Adding restore topic from recycle feature...
---{
if ($db_type == 'postgresql' && $smcFunc['db_server_info'] < 8.0)
{
	upgrade_query("
		ALTER TABLE {$db_prefix}topics
		ADD COLUMN id_previous_board smallint");
	upgrade_query("
		ALTER TABLE {$db_prefix}topics
		ADD COLUMN id_previous_topic int");

	upgrade_query("
		UPDATE {$db_prefix}topics
		SET
			id_previous_board = 0,
			id_previous_topic = 0");

	upgrade_query("
		ALTER TABLE {$db_prefix}topics
		ALTER COLUMN id_previous_board SET NOT NULL");
	upgrade_query("
		ALTER TABLE {$db_prefix}topics
		ALTER COLUMN id_previous_topic SET NOT NULL");

	upgrade_query("
		ALTER TABLE {$db_prefix}topics
		ALTER COLUMN id_previous_board SET default '0'");
	upgrade_query("
		ALTER TABLE {$db_prefix}topics
		ALTER COLUMN id_previous_topic SET default '0'");
}
else
{
	upgrade_query("
		ALTER TABLE {$db_prefix}topics
		ADD COLUMN id_previous_board smallint NOT NULL default '0'");
	upgrade_query("
		ALTER TABLE {$db_prefix}topics
		ADD COLUMN id_previous_topic int NOT NULL default '0'");
}
---}
---#

/******************************************************************************/
--- Making changes to the package manager.
/******************************************************************************/

---# Changing URL to SMF package server...
UPDATE {$db_prefix}package_servers
SET url = 'http://custom.simplemachines.org/packages/mods'
WHERE url = 'http://mods.simplemachines.org';
---#

/******************************************************************************/
--- Adding general table indexes.
/******************************************************************************/

---# Adding index for topics table...
CREATE INDEX {$db_prefix}topics_member_started ON {$db_prefix}topics (id_member_started, id_board);
---#

/******************************************************************************/
--- Adding indexes to optimize stats.
/******************************************************************************/

---# Adding index on total_time_logged_in...
CREATE INDEX {$db_prefix}members_total_time_logged_in ON {$db_prefix}members (total_time_logged_in);
---#

/******************************************************************************/
--- Adding indexes on real_name.
/******************************************************************************/

---# Adding index on real_name...
CREATE INDEX {$db_prefix}members_real_name ON {$db_prefix}members (real_name);
---#

/******************************************************************************/
--- Adding index on member id and message id.
/******************************************************************************/

---# Adding index on member id and message id...
CREATE INDEX {$db_prefix}messages_id_member_msg ON {$db_prefix}messages (id_member, approved, id_msg);
---#

/******************************************************************************/
--- Adding index on id_topic, id_msg, id_member, approved.
/******************************************************************************/

---# Adding index on id_topic, id_msg, id_member, approved...
CREATE INDEX {$db_prefix}messages_current_topic ON {$db_prefix}messages (id_topic, id_msg, id_member, approved);
---#

/******************************************************************************/
--- Providing more room for ignoring boards.
/******************************************************************************/

---# Changing ignore_boards column to a larger field type...
ALTER TABLE {$db_prefix}members
ALTER COLUMN ignore_boards TYPE text;
---#

/******************************************************************************/
--- Adding default values to a couple of columns in log_subscribed
/******************************************************************************/

---# Adding default value for pending_details column
ALTER TABLE {$db_prefix}log_subscribed
ALTER COLUMN pending_details
SET DEFAULT '';
---#

---# Adding default value for vendor_ref column
ALTER TABLE {$db_prefix}log_subscribed
ALTER COLUMN vendor_ref
SET DEFAULT '';
---#

/*****************************************************************************/
--- Fixing aim on members for longer nicks.
/*****************************************************************************/

---# Changing 'aim' to varchar to allow using email...
ALTER TABLE {$db_prefix}members
ALTER COLUMN aim TYPE varchar(255);

ALTER TABLE {$db_prefix}members
ALTER COLUMN aim SET DEFAULT '';
---#

/*****************************************************************************/
--- Fixing column types in log_errors
/*****************************************************************************/

---# Changing 'ip' from char to varchar
ALTER TABLE {$db_prefix}log_errors
ALTER COLUMN ip TYPE varchar(16);

ALTER TABLE {$db_prefix}log_errors
ALTER COLUMN ip SET DEFAULT '';
---#

---# Changing 'error_type' from char to varchar
ALTER TABLE {$db_prefix}log_errors
ALTER COLUMN error_type TYPE varchar(15);
---#

/******************************************************************************/
--- Allow for longer calendar event/holiday titles.
/******************************************************************************/

---# Changing event title column to a larger field type...
ALTER TABLE {$db_prefix}calendar
ALTER COLUMN title TYPE varchar(60);
---#

---# Changing holiday title column to a larger field type...
ALTER TABLE {$db_prefix}calendar_holidays
ALTER COLUMN title TYPE varchar(60);
---#

/******************************************************************************/
--- Providing more room for apf options.
/******************************************************************************/

---# Changing field_options column to a larger field type...
ALTER TABLE {$db_prefix}custom_fields
ALTER COLUMN field_options TYPE text;
---#

/******************************************************************************/
--- Adding extra columns to polls.
/******************************************************************************/

---# Adding reset poll timestamp and guest voters counter.
---{
if ($smcFunc['db_server_info'] < 8.0)
{
	upgrade_query("
		ALTER TABLE {$db_prefix}polls
		ADD COLUMN reset_poll int");

	upgrade_query("
		UPDATE {$db_prefix}polls
		SET reset_poll = '0'
		WHERE reset_poll < 1");

	upgrade_query("
		ALTER TABLE {$db_prefix}polls
		ALTER COLUMN reset_poll SET NOT NULL");

	upgrade_query("
		ALTER TABLE {$db_prefix}polls
		ALTER COLUMN reset_poll SET default '0'");

	upgrade_query("
		ALTER TABLE {$db_prefix}polls
		ADD COLUMN num_guest_voters int");

	upgrade_query("
		UPDATE {$db_prefix}polls
		SET num_guest_voters = '0'
		WHERE num_guest_voters < 1");

	upgrade_query("
		ALTER TABLE {$db_prefix}polls
		ALTER COLUMN num_guest_voters SET NOT NULL");

	upgrade_query("
		ALTER TABLE {$db_prefix}polls
		ALTER COLUMN num_guest_voters SET default '0'");
}
else
{
	upgrade_query("
		ALTER TABLE {$db_prefix}polls
		ADD COLUMN reset_poll int NOT NULL default '0'");
	upgrade_query("
		ALTER TABLE {$db_prefix}polls
		ADD COLUMN num_guest_voters int NOT NULL default '0'");
}
---}
---#

---# Fixing guest voter tallys on existing polls...
---{
$request = upgrade_query("
	SELECT p.id_poll, count(lp.id_member) as guest_voters
	FROM {$db_prefix}polls AS p
		LEFT JOIN {$db_prefix}log_polls AS lp ON (lp.id_poll = p.id_poll AND lp.id_member = 0)
	WHERE lp.id_member = 0
		AND p.num_guest_voters = 0
	GROUP BY p.id_poll");

while ($request && $row = $smcFunc['db_fetch_assoc']($request))
	upgrade_query("
		UPDATE {$db_prefix}polls
		SET num_guest_voters = ". $row['guest_voters']. "
		WHERE id_poll = " . $row['id_poll'] . "
			AND num_guest_voters = 0");
---}
---#

/*****************************************************************************/
--- Fixing a bug with the inet_aton() function.
/*****************************************************************************/

---# Changing inet_aton function to use bigint instead of int...
CREATE OR REPLACE FUNCTION INET_ATON(text) RETURNS bigint AS
  'SELECT
    split_part($1, ''.'', 1)::int8 * (256 * 256 * 256) +
    split_part($1, ''.'', 2)::int8 * (256 * 256) +
    split_part($1, ''.'', 3)::int8 * 256 +
    split_part($1, ''.'', 4)::int8 AS result'
LANGUAGE 'sql';
---#

/*****************************************************************************/
--- Making additional changes to handle results from fixed inet_aton().
/*****************************************************************************/

---# Adding an IFNULL to handle 8-bit integers returned by inet_aton
CREATE OR REPLACE FUNCTION IFNULL(int8, int8) RETURNS int8 AS
  'SELECT COALESCE($1, $2) AS result'
LANGUAGE 'sql';
---#

---# Changing ip column in log_online to int8
ALTER TABLE {$db_prefix}log_online
ALTER COLUMN ip TYPE int8;
---#

/*****************************************************************************/
--- Adding additional functions
/*****************************************************************************/

---# Adding instr()
CREATE OR REPLACE FUNCTION INSTR(text, text) RETURNS integer AS
  'SELECT POSITION($2 IN $1) AS result'
LANGUAGE 'sql';
---#

---# Adding daty()
CREATE OR REPLACE FUNCTION day(date) RETURNS integer AS
  'SELECT EXTRACT(DAY FROM DATE($1))::integer AS result'
LANGUAGE 'sql';
---#

---# Adding IFNULL(varying, varying)
CREATE OR REPLACE FUNCTION IFNULL (character varying, character varying) RETURNS character varying AS
  'SELECT COALESCE($1, $2) AS result'
LANGUAGE 'sql';
---#

---# Adding IFNULL(varying, bool)
CREATE OR REPLACE FUNCTION IFNULL(character varying, boolean) RETURNS character varying AS
  'SELECT COALESCE($1, CAST(CAST($2 AS int) AS varchar)) AS result'
LANGUAGE 'sql';
---#

---# Adding IFNULL(int, bool)
CREATE OR REPLACE FUNCTION IFNULL(int, boolean) RETURNS int AS
  'SELECT COALESCE($1, CAST($2 AS int)) AS result'
LANGUAGE 'sql';
---#

---# Adding bool_not_eq_int()
CREATE OR REPLACE FUNCTION bool_not_eq_int (boolean, integer) RETURNS boolean AS
  'SELECT CAST($1 AS integer) != $2 AS result'
LANGUAGE 'sql';
---#

---# Creating operator bool_not_eq_int()
---{
$result = upgrade_query("SELECT oprname FROM pg_operator WHERE oprcode='bool_not_eq_int'");
if($smcFunc['db_num_rows']($result) == 0)
{
	upgrade_query("
		CREATE OPERATOR != (PROCEDURE = bool_not_eq_int, LEFTARG = boolean, RIGHTARG = integer)");
}
---}
---#