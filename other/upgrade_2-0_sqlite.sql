/* ATTENTION: You don't need to run or use this file!  The upgrade.php script does everything for you! */

/******************************************************************************/
--- Updating custom fields.
/******************************************************************************/

---# Adding search ability to custom fields.
ALTER TABLE {$db_prefix}custom_fields
ADD COLUMN can_search smallint NOT NULL default '0' AFTER bbc;
---#

/******************************************************************************/
--- Adding search engine tracking.
/******************************************************************************/

---# Creating spider table.
CREATE TABLE {$db_prefix}spiders (
	id_spider smallint primary key,
	spider_name varchar(255) NOT NULL,
	user_agent varchar(255) NOT NULL,
	ip_info varchar(255) NOT NULL
);

INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (1, 'Google', 'googlebot', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (2, 'Yahoo!', 'slurp', '');
INSERT INTO {$db_prefix}spiders	(id_spider, spider_name, user_agent, ip_info) VALUES (3, 'MSN', 'msn', '');
---#

---# Creating spider hit tracking table.
CREATE TABLE {$db_prefix}log_spider_hits (
  id_spider smallint NOT NULL default '0',
  session varchar(32) NOT NULL default '',
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
  unique_visits smallint NOT NULL default '0',
  page_hits smallint NOT NULL default '0',
  last_seen int NOT NULL default '0',
  stat_date date NOT NULL default '0001-01-01',
  PRIMARY KEY (stat_date, id_spider)
);
---#

/******************************************************************************/
--- Adding error log pruning.
/******************************************************************************/

--# Adding scheduled task...
INSERT INTO {$db_prefix}scheduled_tasks (next_time, time_offset, time_regularity, time_unit, disabled, task) VALUES (9, 0, 0, 1, 'w', 0, 'pruneErrorLog');
INSERT INTO {$db_prefix}settings (variable, value) VALUES ('pruneErrorLog', '30');
--#
