<?php

namespace PHPTDD;

use SMF\Cache\CacheApi;
use SMF\Cache\CacheApiInterface;
use SMF\Cache\FileBased;
use SMF\Cache\Sqlite;
use SMF\Cache\Apcu;
use SMF\Cache\Apc;
use SMF\Cache\Memcached;
use SMF\Cache\Memcache;
use SMF\Cache\Postgres;

class CacheTest extends BaseTestCase
{
	private $cacheObj;

	public function setUp() : void
	{
		global $cache_accelerator, $cache_enable;

		$cache_accelerator = '';
		$cache_enable = 1;
	}

	public function tearDown() : void
	{
		global $cache_accelerator, $cache_enable, $cacheAPI;

		$cache_accelerator = '';
		$cache_enable = 0;
		$cacheAPI = false;

		if ($this->cacheObj instanceof Postgres)
			smf_db_query('', 'DROP TABLE IF EXISTS {db_prefix}cache');
		if ($this->cacheObj instanceof CacheApiInterface)
			$this->assertFalse($this->cacheObj->isSupported());
	}

	public function testDefault(): void
	{
		global $cache_accelerator;

		$cache_accelerator = 'sqlite';
		$this->cacheObj = loadCacheAccelerator();
		$this->assertInstanceOf(Sqlite::class, $this->cacheObj);
	}

	public function testFallback(): void
	{
		global $cache_accelerator;

		$cache_accelerator = 'zend';
		$this->cacheObj = loadCacheAccelerator();
		$this->assertInstanceOf(FileBased::class, $this->cacheObj);
	}

	public function testNoFallback(): void
	{
		global $cache_accelerator;

		$cache_accelerator = 'zend';
		$this->cacheObj = loadCacheAccelerator(null, false);
		$this->assertFalse($this->cacheObj);
	}

	public function data(): array
	{
		return array(
			array(
				'filebased',
				FileBased::class,
			),
			array(
				'sqlite',
				Sqlite::class,
			),
			array(
				'apcu',
				Apcu::class,
			),
			array(
				'memcached',
				Memcached::class,
			),
			array(
				'memcache',
				Memcache::class,
			),
			array(
				'postgres',
				Postgres::class,
			),
		);
	}

	/**
	 * @dataProvider data
	 */
	public function test(string $api, string $fqcn)
	{
		$this->cacheObj = loadCacheAccelerator($api, false);
		if (!$this->cacheObj)
			$this->markTestSkipped();

		$this->assertInstanceOf(CacheApiInterface::class, $this->cacheObj);
		$this->assertInstanceOf(CacheApi::class, $this->cacheObj);
		$this->assertInstanceOf($fqcn, $this->cacheObj);
		$this->assertTrue($this->cacheObj->isSupported());

		$this->cacheObj->putData('test2', 'val', -10);
		$this->assertNull($this->cacheObj->getData('test2'));
		$this->cacheObj->putData('test2', null, -10);

		$this->assertTrue($this->cacheObj->setDefaultTTL(-10));
		$this->assertEquals(-10, $this->cacheObj->getDefaultTTL());

		$this->cacheObj->putData('test2', 'val');
		$this->assertNull($this->cacheObj->getData('test2'));
		$this->cacheObj->putData('test2', null);

		$this->assertTrue($this->cacheObj->setDefaultTTL());
		$this->assertEquals(120, $this->cacheObj->getDefaultTTL());

		$this->cacheObj->putData('test', null);
		$this->assertNull($this->cacheObj->getData('test'));

		$this->cacheObj->putData('test', 'val');
		$this->assertSame('val', $this->cacheObj->getData('test'));

		$this->cacheObj->putData('test', 'val1');
		$this->assertSame('val1', $this->cacheObj->getData('test'));

		$this->assertTrue($this->cacheObj->cleanCache());
		$this->assertNull($this->cacheObj->getData('test'));

		$this->assertNull($this->cacheObj->getData('test'));
		$this->assertNull($this->cacheObj->getData('test_undef'));

		$this->assertTrue(version_compare($this->cacheObj->getCompatibleVersion(), '0.0.1', '>='));
		$this->assertTrue(version_compare($this->cacheObj->getMinimumVersion(), '0.0.1', '>='));
		$this->assertTrue(version_compare($this->cacheObj->getVersion(), '0.0.1', '>='));
	}
}