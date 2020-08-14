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

	public function testLoadMissingCSSFile()
	{
		global $context;

		unset($context['css_files']);
		unset($context['css_files_order']);
		loadCSSFile('foo.css');
		$this->assertFalse(isset($context['css_files']));
		$this->assertIsArray($context['css_files_order']);
		$this->assertEmpty($context['css_files_order']);
	}


	public function cssdata()
	{
		return array(
			array(
				'responsive.css', array('minimize' => true, 'order_pos' => 77), 'smf_responsive',
			),
			array(
				'file:///external.css', array('external' => true, 'order_pos' => 7), '',
			),
			array(
				'index.css', array('minimize' => true, 'order_pos' => 1), 'smf_index',
			),
			array(
				'calendar.css', array('force_current' => false, 'rtl' => 'calendar.rtl.css'), 'smf_calendar',
			),
		);
	}

	/**
	 * @dataProvider cssdata
	 */
	public function testLoadCSSFile($name, $params, $id)
	{
		global $context;

		unset($context['css_files']);
		loadCSSFile($name, $params, $id);
		$id = empty($id) ? strtr(basename($name, '.css'), '?', '_') : $id;
		$params['order_pos'] = $params['order_pos'] ?? 3000;
		$this->assertCount(1, $context['css_files']);
		$this->assertArrayHasKey($id, $context['css_files']);
		$this->assertStringContainsString($name, $context['css_files'][$id]['filePath']);
		$this->assertStringContainsString($name, $context['css_files'][$id]['fileName']);
		$this->assertStringContainsString($name, $context['css_files'][$id]['fileUrl']);
		$this->assertContains($id, $context['css_files_order']);
		$this->assertArrayHasKey($params['order_pos'], $context['css_files_order']);
		$this->assertEquals($id, $context['css_files_order'][$params['order_pos']]);
	}

	public function testLoadMissingjavascriptFile()
	{
		global $context;

		unset($context['javascript_files']);
		loadJavaScriptFile('foo.javascript');
		$this->assertFalse(isset($context['javascript_files']));
	}


	public function javascriptdata()
	{
		return array(
			array(
				'theme.js', array('minimize' => true), 'smf_responsive',
			),
			array(
				'file:///external.js', array('external' => true), '',
			),
			array(
				'script.js?test', array(), '',
			),
		);
	}

	/**
	 * @dataProvider javascriptdata
	 */
	public function testLoadjavascriptFile($name, $params, $id)
	{
		global $context;

		unset($context['javascript_files']);
		loadJavaScriptFile($name, $params, $id);
		$id = empty($id) ? basename(strtr($name, array('.js' => '', '?' => '_'))) : $id;
		$name = strtok($name, '?');
		$this->assertCount(1, $context['javascript_files']);
		$this->assertArrayHasKey($id, $context['javascript_files']);
		$this->assertEquals($name, $context['javascript_files'][$id]['fileName']);
		$this->assertStringContainsString($name, $context['javascript_files'][$id]['fileUrl']);
		$this->assertStringContainsString($name, $context['javascript_files'][$id]['filePath']);
	}
}