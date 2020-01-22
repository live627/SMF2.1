<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2020 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1 RC2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * PostgreSQL Cache API class
 *
 * @package cacheAPI
 */
class postgres_cache extends cache_api
{
	/** @var false|resource of the pg_prepare from get_data. */
	private $pg_get_data_prep;

	/** @var false|resource of the pg_prepare from put_data. */
	private $pg_put_data_prep;

	/** @var resource result of pg_connect. */
	private $db_connection;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	public function connect()
	{
		global $db_prefix;

		pg_prepare($this->db_connection, '', 'SELECT 1
			FROM   pg_tables
			WHERE  schemaname = $1
			AND    tablename = $2');

		$result = pg_execute($this->db_connection, '', array('public', $db_prefix . 'cache'));

		if (pg_affected_rows($result) === 0)
			pg_query($this->db_connection, 'CREATE UNLOGGED TABLE ' . $db_prefix . 'cache (key text, value text, ttl bigint, PRIMARY KEY (key))');
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSupported($test = false)
	{
		global $pg_cache_name, $pg_cache_user, $pg_cache_passwd, $pg_cache_options;

		$fn = !empty($pg_cache_options['persist']) ? 'pg_pconnect' : 'pg_connect';
		$connection = @$fn(
			sprintf(
				'host=%s port=%d user=%s password=%s',
				$pg_cache_server,
				$pg_cache_options['port'],
				$pg_cache_user,
				$pg_cache_passwd
			)
		);

		if (!$this->db_connection)
			return false;

		$this->db_connection = $connection;
		$result = pg_query($this->db_connection, 'SHOW server_version_num');
		$res = pg_fetch_assoc($result);

		if ($res['server_version_num'] < 90500)
			return false;

		return $test ? true : parent::isSupported();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData($key, $ttl = null)
	{
		global $db_prefix;

		if (empty($this->pg_get_data_prep))
			$this->pg_get_data_prep = pg_prepare($this->db_connection, 'smf_cache_get_data', 'SELECT value FROM ' . $db_prefix . 'cache WHERE key = $1 AND ttl >= $2 LIMIT 1');

		$result = pg_execute($this->db_connection, 'smf_cache_get_data', array($key, time()));

		if (pg_affected_rows($result) === 0)
			return null;

		$res = pg_fetch_assoc($result);

		return $res['value'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function putData($key, $value, $ttl = null)
	{
		global $db_prefix;

		if (!isset($value))
			$value = '';

		$ttl = time() + $ttl;

		if (empty($this->pg_put_data_prep))
			$this->pg_put_data_prep = pg_prepare($this->db_connection, 'smf_cache_put_data',
				'INSERT INTO ' . $db_prefix . 'cache(key,value,ttl) VALUES($1,$2,$3)
				ON CONFLICT(key) DO UPDATE SET value = excluded.value, ttl = excluded.ttl'
			);

		$result = pg_execute($this->db_connection, 'smf_cache_put_data', array($key, $value, $ttl));

		return pg_affected_rows($result) > 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function cleanCache($type = '')
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			TRUNCATE TABLE {db_prefix}cache',
			array()
		);

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVersion()
	{
		global $smcFunc;

		return $smcFunc['db_server_info']();
	}

	/**
	 * {@inheritDoc}
	 */
	public function housekeeping()
	{
		$this->createTempTable();
		$this->cleanCache();
		$this->retrieveData();
		$this->deleteTempTable();
	}

	/**
	 * Create the temp table of valid data.
	 *
	 * @return void
	 */
	private function createTempTable()
	{
		global $db_prefix;

		pg_query($this->db_connection, 'CREATE LOCAL TEMP TABLE IF NOT EXISTS ' . $db_prefix . 'cache_tmp AS SELECT * FROM ' . $db_prefix . 'cache WHERE ttl >= ' . time());
	}

	/**
	 * Delete the temp table.
	 *
	 * @return void
	 */
	private function deleteTempTable()
	{
		global $db_prefix;

		pg_query($this->db_connection, 'DROP TABLE IF EXISTS ' . $db_prefix . 'cache_tmp');
	}

	/**
	 * Retrieve the valid data from temp table.
	 *
	 * @return void
	 */
	private function retrieveData()
	{
		global $db_prefix;

		pg_query($this->db_connection, 'INSERT INTO ' . $db_prefix . 'cache SELECT * FROM ' . $db_prefix . 'cache_tmp ON CONFLICT DO NOTHING');
	}
}

?>