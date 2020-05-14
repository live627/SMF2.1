<?php

namespace PHPTDD;

class MiscTest extends BaseTestCase
{
	public function setUp() : void
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

	public function testIsFilteredRequest()
	{
		$this->assertFalse(is_filtered_request(array('m' => true), 'l'));
		$_REQUEST['l'] = 'm';
		$this->assertTrue(is_filtered_request(array('m' => true), 'l'));

		unset($_REQUEST['l']);
		$this->assertFalse(is_filtered_request(array('m' => array('s' => array('p'))), 'l'));
		$_REQUEST['s'] = 'a';
		$this->assertFalse(is_filtered_request(array('m' => array('s' => array('p'))), 'l'));

		$_REQUEST['l'] = 'm';
		$_REQUEST['s'] = 'p';
		$this->assertTrue(is_filtered_request(array('m' => array('s' => array('p'))), 'l'));

		unset($_REQUEST);
	}
}