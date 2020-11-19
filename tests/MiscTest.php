<?php

namespace PHPTDD;

class MiscTest extends BaseTestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-Admin.php');
	}

	/**
	 * @return string[][]
	 */
	public function data(): array
	{
		return array(
			//~ array(
				//~ 'imagemagick',
			//~ ),
			array(
				'db_server',
			),
			array(
				'apcu',
			),
			//~ array(
				//~ 'memcacheimplementation',
			//~ ),
			//~ array(
				//~ 'memcachedimplementation',
			//~ ),
			array(
				'php',
			),
		);
	}

	/**
	 * @dataProvider data
	 *
	 * @return void
	 */
	public function testServerVersions(string $checkFor): void
	{
		$versions = getServerVersions([$checkFor]);
		if (empty($versions))
			$this->markTestSkipped();
		$this->assertTrue(version_compare($versions[$checkFor]['version'], '0.0.1', '>='));
	}

	public function testIsFilteredRequest(): void
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

	public function testLoadMissingCSSFile(): void
	{
		global $context;

		unset($context['css_files']);
		unset($context['css_files_order']);
		loadCSSFile('foo.css');
		$this->assertFalse(isset($context['css_files']));
		$this->assertIsArray($context['css_files_order']);
		$this->assertEmpty($context['css_files_order']);
	}

	/**
	 * @return ((bool|int|string)[]|string)[][]
	 *
	 * @psalm-return array{0: array{0: string, 1: array{seed: string, order_pos: int}, 2: string}, 1: array{0: string, 1: array{seed: true}, 2: string}, 2: array{0: string, 1: array{seed: false}, 2: string}, 3: array{0: string, 1: array{external: true, order_pos: int}, 2: string}, 4: array{0: string, 1: array{minimize: true, order_pos: int}, 2: string}, 5: array{0: string, 1: array{force_current: false, rtl: string}, 2: string}}
	 */
	public function cssdata(): array
	{
		return array(
			array(
				'responsive.css', array('seed' => 'lol', 'order_pos' => 77), 'smf_responsive',
			),
			array(
				'responsive.css', array('seed' => true), 'smf_responsive',
			),
			array(
				'responsive.css', array('seed' => false), 'smf_responsive',
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
	 *
	 * @return void
	 */
	public function testLoadCSSFile($name, $params, $id): void
	{
		global $context;

		unset($context['css_files']);
		loadCSSFile($name, $params, $id);
		$id = (empty($id) ? strtr(basename($name, '.css'), '?', '_') : $id) . '_css';
		$params['order_pos'] = $params['order_pos'] ?? 3000;
		$this->assertCount(1, $context['css_files']);
		$this->assertArrayHasKey($id, $context['css_files']);
		$this->assertStringContainsString($name, $context['css_files'][$id]['filePath']);
		$this->assertStringContainsString($name, $context['css_files'][$id]['fileName']);
		$this->assertStringContainsString($name, $context['css_files'][$id]['fileUrl']);
		$this->assertStringContainsString(
			!isset($params['seed']) || $params['seed'] === true ? $context['browser_cache'] : $params['seed'],
			$context['css_files'][$id]['options']['seed']
		);
		$this->assertContains($id, $context['css_files_order']);
		$this->assertArrayHasKey($params['order_pos'], $context['css_files_order']);
		$this->assertEquals($id, $context['css_files_order'][$context['css_files'][$id]['options']['order_pos']]);
	}

	public function testLoadMissingjavascriptFile(): void
	{
		global $context;

		unset($context['javascript_files']);
		loadJavaScriptFile('foo.javascript');
		$this->assertFalse(isset($context['javascript_files']));
	}

	/**
	 * @return (string|true[])[][]
	 *
	 * @psalm-return array{0: array{0: string, 1: array{minimize: true}, 2: string}, 1: array{0: string, 1: array{external: true}, 2: string}, 2: array{0: string, 1: array<empty, empty>, 2: string}}
	 */
	public function javascriptdata(): array
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
	 *
	 * @return void
	 */
	public function testLoadjavascriptFile($name, $params, $id): void
	{
		global $context;

		unset($context['javascript_files']);
		loadJavaScriptFile($name, $params, $id);
		$id = (empty($id) ? basename(strtr($name, array('.js' => '', '?' => '_'))) : $id) . '_js';
		$name = strtok($name, '?');
		$this->assertCount(1, $context['javascript_files']);
		$this->assertArrayHasKey($id, $context['javascript_files']);
		$this->assertEquals($name, $context['javascript_files'][$id]['fileName']);
		$this->assertStringContainsString($name, $context['javascript_files'][$id]['fileUrl']);
		$this->assertStringContainsString($name, $context['javascript_files'][$id]['filePath']);
	}
}