/* ATTENTION: You don't need to run or use this file!  The upgrade.php script does everything for you! */

/******************************************************************************/
--- Changing column names.
/******************************************************************************/

---# Changing all column names.
ALTER TABLE {$db_prefix}log_activity
RENAME COLUMN mostOn TO most_on;
---#
