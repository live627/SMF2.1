<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class TestSettingsForm extends TestCase
{
	protected $configVars = [];

	protected $permissionResults = [];

	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/ManageServer.php';
		loadLanguage('Admin', 'english', true, true);
	}

	protected function setUp(): void
	{
		$this->configVars = [
			['text', 'name1'],
			['int', 'name2'],
			['float', 'name3'],
			['large_text', 'name4'],
			['check', 'name5'],
			['select', 'name6', ['value' => 'display']],
			['select', 'name6m', ['value1' => 'display1', 'value2' => 'display2'], 'multiple' => true],
			['password', 'name7'],
			['permissions', 'name8'],
			['bbc', 'name9'],
			'',
			[
				'select',
				'test',
				'label' => 'Test Option',
				[
					'opt1' => 'Option 1',
					'opt2' => 'Option 2',
					'opt3' => 'Option 4',
					'opt4' => 'Option 4',
					'opt5' => 'Option 5',
				],
				'multiple' => true,
			],
			['select', 'tetst2', ['value1' => 'display1', 'value2' => 'display2'], 'multiple' => true, 'size' => 7],
		];
		$this->permissionResults = [
			-1 => [
				'id' => -1,
				'name' => 'Guests',
				'is_postgroup' => false,
				'status' => 'off',
			],
			0 => [
				'id' => 0,
				'name' => 'Regular Members',
				'is_postgroup' => false,
				'status' => 'off',
			],
			2 => [
				'id' => '2',
				'name' => 'Global Moderator',
				'is_postgroup' => false,
				'status' => 'off',
			],
		];
		$_POST = [
			'name1' => 'value1',
			'name2' => '5',
			'name3' => '4.6',
			'name4' => 'value4',
			'name5' => '1',
			'name6' => 'value',
			'name6m' => ['value1', 'value2'],
			'name7' => ['value', 'value'],
			'name8' => [0 => 'on'],
			'name9' => ['b', 'i'],
		];
	}

	protected function tearDown(): void
	{
		unset($_REQUEST);
	}

	public function testPrepareDBSettingContext(): void
	{
		global $context;

		prepareDBSettingContext($this->configVars);
		$this->assertCount(1, $context['config_vars'][$this->configVars[5][1]]['data']);
		$this->assertContains('value', $context['config_vars'][$this->configVars[5][1]]['data'][0]);
		$this->assertCount(2, $context['config_vars'][$this->configVars[6][1]]['data']);
		$this->assertContains('value1', $context['config_vars'][$this->configVars[6][1]]['data'][0]);
		$this->assertEquals(4, $context['config_vars'][$this->configVars[6][1]]['size']);
		$this->assertEquals(5, $context['config_vars'][$this->configVars[11][1]]['size']);
		$this->assertEquals(7, $context['config_vars'][$this->configVars[12][1]]['size']);
		$this->assertEquals(0, $context['config_vars'][$this->configVars[5][1]]['size']);
		$this->assertContains(
			['tag' => 'b', 'show_help' => false],
			$context['bbc_sections'][$this->configVars[9][1]]['columns'][0]
		);

		foreach ($this->configVars as $configVar)
		{
			if (is_array($configVar))
			{
				$this->assertTrue(isset($context['config_vars'][$configVar[1]]));
				$this->assertSame($configVar[0], $context['config_vars'][$configVar[1]]['type']);

				if ($configVar[0] == 'select' && !empty($configVar['multiple']))
					$this->assertSame($configVar[1] . '[]', $context['config_vars'][$configVar[1]]['name']);
				else
					$this->assertSame($configVar[1], $context['config_vars'][$configVar[1]]['name']);
			}
		}
		$context[$this->configVars[8][1]] = array_slice($context[$this->configVars[8][1]], 0, 3, true);
		$this->assertEquals($this->permissionResults, $context[$this->configVars[8][1]]);
	}

	public function testSaveDBSettings(): void
	{
		global $context, $modSettings;
		$token_check = createToken('admin-dbsc');
		$_POST[$token_check['admin-dbsc_token_var']] = $token_check['admin-dbsc_token'];
		$_POST[$context['session_var']] = $context['session_id'];
		$_POST[$_SESSION['session_var']] = $_SESSION['session_value'];

		// Create the token for the separate inline permission verification.
		$token_check = createToken('admin-mp');
		$_POST[$token_check['admin-mp_token_var']] = $token_check['admin-mp_token'];
		saveDBSettings($this->configVars);
		$modSettings['bbc_disabled_' . $this->configVars[9][1]] = $_POST['name9'];
		prepareDBSettingContext($this->configVars);
		$this->assertisSaved();
		$this->permissionResults[0]['status'] = 'on';
		$context[$this->configVars[8][1]] = array_slice($context[$this->configVars[8][1]], 0, 3, true);
		$this->assertEquals($this->permissionResults, $context[$this->configVars[8][1]]);
		$this->cleanup();
	}

	public function assertisSaved(): void
	{
		global $context;

		foreach (array_intersect_key($_POST, array_column($this->configVars, 1)) as $varName => $configValue)
		{
			if (!is_array($configValue))
			{
				$this->assertTrue(isset($context['config_vars'][$varName]));
				$this->assertSame($configValue, $context['config_vars'][$varName]['value']);
			}
		}
		$this->assertSame('value', $context['config_vars'][$this->configVars[5][1]]['value']);
		$this->assertContains('value1', $context['config_vars'][$this->configVars[6][1]]['value']);
		$this->assertSame(['b', 'i'], $context['bbc_sections'][$this->configVars[9][1]]['disabled']);
	}

	public function cleanup(): void
	{
		global $smcFunc;

		$smcFunc['db_query'](
			'',
			'
			DELETE FROM {db_prefix}settings
			WHERE variable IN ({array_string:setting_name})',
			[
				'setting_name' => array_column($this->configVars, 1),
			]
		);
		$smcFunc['db_query'](
			'',
			'
			DELETE FROM {db_prefix}permissions
			WHERE permission IN ({array_string:setting_name})',
			[
				'setting_name' => array_column($this->configVars, 1),
			]
		);
	}

	public function testPrepareFile(): void
	{
		global $context;

		$this->configVars = [
			['mtitle', 'maintenance_subject', 'file', 'text', 36],
			['enableCompressedOutput', 'enableCompressedOutput', 'db', 'check', null, 'enableCompressedOutput'],
		];
		prepareServerSettingsContext($this->configVars);

		foreach ($this->configVars as $configVar)
		{
			$this->assertTrue(isset($context['config_vars'][$configVar[0]]));
			$this->assertSame($configVar[3], $context['config_vars'][$configVar[0]]['type']);
			$this->assertSame($configVar[0], $context['config_vars'][$configVar[0]]['name']);
		}
		global $mtitle;
		$this->assertSame('Maintenance Mode', $mtitle);
		$this->assertSame('Maintenance Mode', $context['config_vars'][$this->configVars[0][0]]['value']);
		$this->assertEquals(0, $context['config_vars'][$this->configVars[1][0]]['value']);
	}

	public function testSaveFile(): void
	{
		global $context;

		$this->configVars = [
			['mtitle', 'maintenance_subject', 'file', 'text', 36],
			['enableCompressedOutput', 'enableCompressedOutput', 'db', 'check', null, 'enableCompressedOutput'],
		];
		$_POST = [
			'mtitle' => 'value',
			'enableCompressedOutput' => '1',
		];
		$token_check = createToken('admin-ssc');
		$_POST[$token_check['admin-ssc_token_var']] = $token_check['admin-ssc_token'];
		$token_check = createToken('admin-dbsc');
		$_POST[$token_check['admin-dbsc_token_var']] = $token_check['admin-dbsc_token'];
		saveSettings($this->configVars);

		// Reload
		global $boarddir, $mtitle;
		require $boarddir . '/Settings.php';
		prepareServerSettingsContext($this->configVars);
		$this->assertSame('value', $mtitle);
		$this->assertSame('value', $context['config_vars'][$this->configVars[0][0]]['value']);
		$this->assertEquals(1, $context['config_vars'][$this->configVars[1][0]]['value']);

		// Restore
		$_POST = [
			'mtitle' => 'Maintenance Mode',
			'enableCompressedOutput' => '0',
		];
		$token_check = createToken('admin-ssc');
		$_POST[$token_check['admin-ssc_token_var']] = $token_check['admin-ssc_token'];
		$token_check = createToken('admin-dbsc');
		$_POST[$token_check['admin-dbsc_token_var']] = $token_check['admin-dbsc_token'];
		saveSettings($this->configVars);
		prepareServerSettingsContext($this->configVars);
		$this->assertEquals(0, $context['config_vars'][$this->configVars[1][0]]['value']);
	}
}
