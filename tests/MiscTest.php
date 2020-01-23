<?php

namespace PHPTDD;

class MiscTest extends BaseTestCase
{
	protected function setUp()
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-Admin.php');
	}

	public function data()
	{
		return array(
			array(
				'imagemagick',
			),
			array(
				'db_server',
			),
			array(
				'apc',
			),
			array(
				'apcu',
			),
			array(
				'memcache',
			),
			array(
				'memcached',
			),
			array(
				'xcache',
			),
			array(
				'php',
			),
		);
	}

	/**
	 * @dataProvider data
	 */
	public function testServerVersions(string $checkFor)
	{
		$versions = getServerVersions([$checkFor]);
		if (empty($versions))
			$this->markTestSkipped();
		$this->assertTrue(version_compare($versions[$checkFor]['version'], '0.0.1', '>='));
	}
}