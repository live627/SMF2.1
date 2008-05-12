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
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (3, 'MSN', 'msn', '');
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

/******************************************************************************/
--- Adding restore topic from recycle.
/******************************************************************************/

---# Adding restore topic form recycle feature...
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
--- Adding general table indexes.
/******************************************************************************/

---# Adding index for topics table...
CREATE INDEX {$db_prefix}topics_member_started ON {$db_prefix}topics (id_member_started, id_board);
---#

