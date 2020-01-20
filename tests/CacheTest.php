<?php

namespace PHPTDD;

use cache_api_interface;
use cache_api;
use smf_cache;
use sqlite_cache;
use apcu_cache;
use apc_cache;
use memcached_cache;
use memcache_cache;
use postgres_cache;

class CacheTest extends BaseTestCase
{
	private $_cache_obj;

	public function setUp()
	{
		global $cache_accelerator, $cache_enable;

		$cache_accelerator = '';
		$cache_enable = 1;
	}

	public function tearDown()
	{
		global $cache_accelerator, $cache_enable, $cacheAPI;

		$cache_accelerator = '';
		$cache_enable = 0;
		$cacheAPI = false;
		$this->assertFalse($this->_cache_obj->isSupported());
	}

	public function testDefault()
	{
		global $cache_accelerator;

		$cache_accelerator = 'sqlite';
		$this->_cache_obj = loadCacheAccelerator();
		$this->assertInstanceOf(sqlite_cache::class, $this->_cache_obj);
	}

	public function testFallback()
	{
		global $cache_accelerator;

		$cache_accelerator = 'zend';
		$this->_cache_obj = loadCacheAccelerator();
		$this->assertInstanceOf(smf_cache::class, $this->_cache_obj);
	}

	public function data()
	{
		return array(
			array(
				'smf',
				smf_cache::class,
			),
			array(
				'sqlite',
				sqlite_cache::class,
			),
			array(
				'apcu',
				apcu_cache::class,
			),
			array(
				'apc',
				apc_cache::class,
			),
			array(
				'memcached',
				memcached_cache::class,
			),
			array(
				'memcache',
				memcache_cache::class,
			),
			array(
				'postgres',
				postgres_cache::class,
			),
		);
	}

	/**
	 * @dataProvider data
	 */
	public function test(string $api, string $fqcn)
	{
		$this->_cache_obj = loadCacheAccelerator($api, false);
		$this->assertInstanceOf(cache_api_interface::class, $this->_cache_obj);
		$this->assertInstanceOf(cache_api::class, $this->_cache_obj);
		$this->assertInstanceOf($fqcn, $this->_cache_obj);
		$this->assertTrue($this->_cache_obj->isSupported());

		$this->_cache_obj->putData('test2', 'val', -10);
		$this->assertNull($this->_cache_obj->getData('test2'));
		$this->_cache_obj->putData('test2', null, -10);

		$this->assertTrue($this->_cache_obj->setDefaultTTL(-10));
		$this->assertEquals(-10, $this->_cache_obj->getDefaultTTL());

		$this->_cache_obj->putData('test2', 'val');
		$this->assertNull($this->_cache_obj->getData('test2'));
		$this->_cache_obj->putData('test2', null);

		$this->assertTrue($this->_cache_obj->setDefaultTTL());
		$this->assertEquals(120, $this->_cache_obj->getDefaultTTL());

		$this->_cache_obj->putData('test', null);
		$this->assertNull($this->_cache_obj->getData('test'));

		$this->_cache_obj->putData('test', 'val');
		$this->assertSame('val', $this->_cache_obj->getData('test'));

		$this->assertTrue($this->_cache_obj->cleanCache());
		$this->assertNull($this->_cache_obj->getData('test'));

		$this->assertNull($this->_cache_obj->getData('test_undef'));
	}
}