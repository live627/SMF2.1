<?php

declare(strict_types=1);

namespace PHPTDD;

use SMF\Cache\APIs\Apcu;
use SMF\Cache\APIs\FileBased;
use SMF\Cache\APIs\MemcachedImplementation;
use SMF\Cache\APIs\MemcacheImplementation;
use SMF\Cache\APIs\Postgres;
use SMF\Cache\APIs\Sqlite;
use SMF\Cache\CacheApi;
use SMF\Cache\CacheApiInterface;

class CacheTest extends BaseTestCase
{
	private $cacheObj;

	public function setUp() : void
	{
		global $cache_enable;

		$this->setCacheAccelerator('');
		$cache_enable = 1;
	}

	public function tearDown() : void
	{
		global $cache_enable, $cacheAPI;

		$this->setCacheAccelerator('');
		$cache_enable = 0;
		$cacheAPI = false;

		if ($this->cacheObj instanceof Postgres)
			smf_db_query('', 'DROP TABLE IF EXISTS {db_prefix}cache');

		if ($this->cacheObj instanceof CacheApiInterface)
			$this->assertFalse($this->cacheObj->isSupported());
	}

	public function setCacheAccelerator(string $accelerator): void
	{
		global $cache_accelerator;

		$cache_accelerator = $accelerator;
	}

	public function testDefault(): void
	{
		$this->setCacheAccelerator('Sqlite');
		$this->cacheObj = loadCacheAccelerator();
		$this->assertInstanceOf(Sqlite::class, $this->cacheObj);
	}

	public function testFallback(): void
	{
		$this->setCacheAccelerator('Zend');
		$this->cacheObj = loadCacheAccelerator();
		$this->assertInstanceOf(FileBased::class, $this->cacheObj);
	}

	public function testNoFallback(): void
	{
		$this->setCacheAccelerator('Zend');
		$this->cacheObj = loadCacheAccelerator(null, false);
		$this->assertFalse($this->cacheObj);
	}

	public function testNotFound(): void
	{
		$this->setCacheAccelerator('NotFound');
		$this->cacheObj = loadCacheAccelerator();
		$this->assertFalse($this->cacheObj);
	}

	public function data(): array
	{
		return [
			[
				FileBased::class,
			],
			[
				Sqlite::class,
			],
			[
				Apcu::class,
			],
			[
				MemcachedImplementation::class,
			],
			[
				MemcacheImplementation::class,
			],
			[
				Postgres::class,
			],
		];
	}

	/**
	 * @dataProvider data
	 */
	public function test(string $fqcn): void
	{
		$this->cacheObj = loadCacheAccelerator($fqcn, false);

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