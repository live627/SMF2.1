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
		return [
			//~ array(
			//~ 'imagemagick',
			//~ ),
			[
				'db_server',
			],
			[
				'apcu',
			],
			//~ array(
			//~ 'memcacheimplementation',
			//~ ),
			//~ array(
			//~ 'memcachedimplementation',
			//~ ),
			[
				'php',
			],
		];
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
		$this->assertFalse(is_filtered_request(['m' => true], 'l'));
		$_REQUEST['l'] = 'm';
		$this->assertTrue(is_filtered_request(['m' => true], 'l'));

		unset($_REQUEST['l']);
		$this->assertFalse(is_filtered_request(['m' => ['s' => ['p']]], 'l'));
		$_REQUEST['s'] = 'a';
		$this->assertFalse(is_filtered_request(['m' => ['s' => ['p']]], 'l'));

		$_REQUEST['l'] = 'm';
		$_REQUEST['s'] = 'p';
		$this->assertTrue(is_filtered_request(['m' => ['s' => ['p']]], 'l'));

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
	 * @return array
	 */
	public function cssdata(): array
	{
		return [
			[
				'responsive.css',
				['seed' => 'lol', 'order_pos' => 77],
				'smf_responsive',
			],
			[
				'responsive.css',
				['seed' => true],
				'smf_responsive',
			],
			[
				'responsive.css',
				['seed' => false],
				'smf_responsive',
			],
			[
				'file:///external.css',
				['external' => true, 'order_pos' => 7],
				'',
			],
			[
				'index.css',
				['minimize' => true, 'order_pos' => 1],
				'smf_index',
			],
			[
				'calendar.css',
				['force_current' => false, 'rtl' => 'calendar.rtl.css'],
				'smf_calendar',
			],
		];
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
	 * @return array
	 */
	public function javascriptdata(): array
	{
		return [
			[
				'theme.js',
				['minimize' => true],
				'smf_responsive',
			],
			[
				'file:///external.js',
				['external' => true],
				'',
			],
			[
				'script.js?test',
				[],
				'',
			],
		];
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
		$id = (empty($id) ? basename(strtr($name, ['.js' => '', '?' => '_'])) : $id) . '_js';
		$name = strtok($name, '?');
		$this->assertCount(1, $context['javascript_files']);
		$this->assertArrayHasKey($id, $context['javascript_files']);
		$this->assertEquals($name, $context['javascript_files'][$id]['fileName']);
		$this->assertStringContainsString($name, $context['javascript_files'][$id]['fileUrl']);
		$this->assertStringContainsString($name, $context['javascript_files'][$id]['filePath']);
	}
}