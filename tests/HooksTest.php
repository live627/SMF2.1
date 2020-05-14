<?php

namespace PHPTDD;

class HooksTest extends BaseTestCase
{
	public function integrationProvider()
	{
		return array(
			array('integrate_test1', 'testing_class::staticHook', true, '$boarddir/tests/IntegrationFixtures.php', false, '$boarddir/tests/IntegrationFixtures.php|testing_class::staticHook'),
			array('integrate_test2', 'testing_class::instantiatedHook', true, '$boarddir/tests/IntegrationFixtures.php', true, '$boarddir/tests/IntegrationFixtures.php|testing_class::instantiatedHook#'),
			array('integrate_test', 'testing_hook', true, '', false, 'testing_hook'),
			array('integrate_test', 'testing_hook2', true, '', false, 'testing_hook,testing_hook2'),
		);
	}

	/**
	 * @dataProvider integrationProvider
	 */
	public function testAddHooks($hook, $function, $permanent, $file, $object, $expected)
	{
		global $modSettings, $smcFunc;

		add_integration_function($hook, $function, $permanent, $file, $object);
		$this->assertEquals($expected, $modSettings[$hook]);

		$request = $smcFunc['db_query']('', '
			SELECT value
			FROM {db_prefix}settings
			WHERE variable = {string:variable}',
			array(
				'variable' => $hook,
			)
		);
		list ($actual) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @dataProvider integrationProvider
	 * @depends testAddHooks
	 */
	public function testCallHooks($hook)
	{
		global $db_show_debug, $context;

		$actual = call_integration_hook($hook);

		$this->assertEquals(current($actual), $hook);
		$this->assertTrue($db_show_debug === true);
		$this->assertContains($hook, $context['debug']['hooks']);
	}

	/**
	 * @dataProvider integrationProvider
	 * @depends testAddHooks
	 */
	public function testRemoveHooks($hook, $function, $permanent, $file, $object)
	{
		global $modSettings, $smcFunc;

		remove_integration_function($hook, $function, $permanent, $file, $object);

		if ($function == 'testing_hook')
			$this->assertEquals('testing_hook2', $modSettings[$hook]);
		else
			$this->assertEmpty($modSettings[$hook]);

		$request = $smcFunc['db_query']('', '
			SELECT value
			FROM {db_prefix}settings
			WHERE variable = {string:variable}',
			array(
				'variable' => $hook,
			)
		);
		list ($actual) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		if ($function == 'testing_hook')
			$this->assertEquals('testing_hook2', $actual);
		else
			$this->assertEmpty($actual);

		$this->assertEquals($modSettings[$hook], $actual);
	}
}
