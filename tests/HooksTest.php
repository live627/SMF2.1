<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class HooksTest extends TestCase
{
	public function setUp(): void
	{
		global $sourcedir;

		require_once($sourcedir . '/ManageMaintenance.php');
	}

	public function integrationProvider(): array
	{
		return [
			[
				'integrate_test1',
				'testing_class::staticHook',
				true,
				'$boarddir/tests/IntegrationFixtures.php',
				false,
				'$boarddir/tests/IntegrationFixtures.php|testing_class::staticHook',
			],
			[
				'integrate_test2',
				'testing_class::instantiatedHook',
				true,
				'$boarddir/tests/IntegrationFixtures.php',
				true,
				'$boarddir/tests/IntegrationFixtures.php|testing_class::instantiatedHook#',
			],
			['integrate_test', 'testing_hook', true, '', false, 'testing_hook'],
			['integrate_test', 'testing_hook2', true, '', false, 'testing_hook,testing_hook2'],
		];
	}

	/**
	 * @dataProvider integrationProvider
	 */
	public function testAddHooks($hook, $function, $permanent, $file, $object, $expected): void
	{
		global $modSettings, $smcFunc;

		add_integration_function($hook, $function, $permanent, $file, $object);
		$this->assertEquals($expected, $modSettings[$hook]);

		$request = $smcFunc['db_query'](
			'',
			'
			SELECT value
			FROM {db_prefix}settings
			WHERE variable = {string:variable}',
			[
				'variable' => $hook,
			]
		);
		[$actual] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @depends testAddHooks
	 */
	public function testManageHooks(): void
	{
		global $context;

		loadLanguage('Admin');
		list_integration_hooks();
		$this->assertEquals(
			'integrate_test2',
			$context['list_integration_hooks']['rows'][4]['data']['hook_name']['value']
		);
		$this->assertEquals(
			'<span class="main_icons news" title="Function is a method and its class is instantiated"></span> Function: testing_class::instantiatedHook<br>Included file: $boarddir/tests/IntegrationFixtures.php',
			$context['list_integration_hooks']['rows'][4]['data']['function_name']['value']
		);
		$this->assertEquals(
			'./tests/IntegrationFixtures.php',
			$context['list_integration_hooks']['rows'][4]['data']['file_name']['value']
		);
		$this->assertStringContainsString(
			'Exists',
			$context['list_integration_hooks']['rows'][4]['data']['status']['value']
		);
		$this->assertStringContainsString(
			'Not found',
			$context['list_integration_hooks']['rows'][5]['data']['status']['value']
		);
	}

	/**
	 * @dataProvider integrationProvider
	 *
	 * @depends testAddHooks
	 */
	public function testCallHooks($hook): void
	{
		global $db_show_debug, $context;

		$actual = call_integration_hook($hook);

		$this->assertEquals(current($actual), $hook);
		$this->assertTrue($db_show_debug === true);
		$this->assertContains($hook, $context['debug']['hooks']);
	}

	/**
	 * @dataProvider integrationProvider
	 *
	 * @depends testAddHooks
	 */
	public function testRemoveHooks($hook, $function, $permanent, $file, $object): void
	{
		global $modSettings, $smcFunc;

		remove_integration_function($hook, $function, $permanent, $file, $object);

		if ($function == 'testing_hook')
			$this->assertEquals('testing_hook2', $modSettings[$hook]);
		else
			$this->assertEmpty($modSettings[$hook]);

		$request = $smcFunc['db_query'](
			'',
			'
			SELECT value
			FROM {db_prefix}settings
			WHERE variable = {string:variable}',
			[
				'variable' => $hook,
			]
		);
		[$actual] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if ($function == 'testing_hook')
			$this->assertEquals('testing_hook2', $actual);
		else
			$this->assertEmpty($actual);

		$this->assertEquals($modSettings[$hook], $actual);
	}
}
