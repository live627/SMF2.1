/* ATTENTION: You don't need to run or use this file!  The upgrade.php script does everything for you! */

/******************************************************************************/
--- Updating custom fields.
/******************************************************************************/

---# Adding search ability to custom fields.
ALTER TABLE {$db_prefix}custom_fields
ADD COLUMN can_search smallint NOT NULL default '0' AFTER bbc;
---#