<?php

namespace PHPTDD;

class TestSettingsForm extends BaseTestCase
{
	protected $configVars = array();
	protected $permissionResults = array();

	public function setUp()
	{
	global $sourcedir, $user_info;

	require_once($sourcedir . '/ManageServer.php');
		loadLanguage('Admin', 'english', true, true);

		// Elevate the user.
		$user_info['permissions'][] = 'manage_permissions';

		$this->configVars = array(
			array('text', 'name1'),
			array('int', 'name2'),
			array('float', 'name3'),
			array('large_text', 'name4'),
			array('check', 'name5'),
			array('select', 'name6', array('value' => 'display')),
			array('select', 'name6m', array('value1' => 'display1', 'value2' => 'display2'), 'multiple' => true),
			array('password', 'name7'),
			array('permissions', 'name8'),
			array('bbc', 'name9'),
		'',
			array(
				'select', 'test', 'label' => 'Test Option',
				array('opt1' => 'Option 1', 'opt2' => 'Option 2', 'opt3' => 'Option 4', 'opt4' => 'Option 4', 'opt5' => 'Option 5'),
				'multiple' => true,
			),
			array('select', 'tetst2', array('value1' => 'display1', 'value2' => 'display2'), 'multiple' => true, 'size' => 7),
		);
		$this->permissionResults = array(
			-1 => array(
				'id' => -1,
				'name' => 'Guests',
				'is_postgroup' => false,
				'status' => 'off',
			),
			0 => array(
				'id' => 0,
				'name' => 'Regular Members',
				'is_postgroup' => false,
				'status' => 'off',
			),
			2 => array(
				'id' => '2',
				'name' => 'Global Moderator',
				'is_postgroup' => false,
				'status' => 'off',
			),
		);
		$_POST = array(
			'name1' => 'value1',
			'name2' => '5',
			'name3' => '4.6',
			'name4' => 'value4',
			'name5' => '1',
			'name6' => 'value',
			'name6m' => array('value1', 'value2'),
			'name7' => array('value', 'value'),
			'name8' => array(0 => 'on'),
			'name9' => array('b', 'i')
		);
	}

	public function testPrepareDBSettingContext()
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
		$this->assertContains(array('tag' => 'b', 'show_help' => false), $context['bbc_sections'][$this->configVars[9][1]]['columns'][0]);
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

	public function testSaveDBSettings()
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
	}

	public function assertisSaved()
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
		$this->assertSame(array('b', 'i'), $context['bbc_sections'][$this->configVars[9][1]]['disabled']);
	}

	public function tearDown()
	{
	global $smcFunc;
				$request = $smcFunc['db_query']('', '
			DELETE FROM {db_prefix}settings
			WHERE variable IN ({array_string:setting_name})',
			array(
				'setting_name' => array_column($this->configVars, 1),
			)
		);
				$request = $smcFunc['db_query']('', '
			DELETE FROM {db_prefix}permissions
			WHERE permission IN ({array_string:setting_name})',
			array(
				'setting_name' => array_column($this->configVars, 1),
			)
		);
	}

	public function testPrepareFile()
	{
		global $context;

		$this->configVars = array(
			array('mtitle', 'maintenance_subject', 'file', 'text', 36),
			array('enableCompressedOutput', 'enableCompressedOutput', 'db', 'check', null, 'enableCompressedOutput'),
		);
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

	public function telstSaveFile()
	{
		global $context;

		$this->configVars = array(
			array('mtitle', 'maintenance_subject', 'file', 'text', 36),
			array('enableCompressedOutput', 'enableCompressedOutput', 'db', 'check', null, 'enableCompressedOutput'),
		);
		$_POST = array(
			'mtitle' => 'value',
			'enableCompressedOutput' => '1'
		);
		$token_check = createToken('admin-ssc');
		$_POST[$token_check['admin-ssc_token_var']] = $token_check['admin-ssc_token'];
		$token_check = createToken('admin-dbsc');
		$_POST[$token_check['admin-dbsc_token_var']] = $token_check['admin-dbsc_token'];
		saveSettings($this->configVars);

		// Reload
		global $boarddir, $mtitle;
		require($boarddir . '/Settings.php');
		prepareServerSettingsContext($this->configVars);
		$this->assertSame('value', $mtitle);
		$this->assertSame('value', $context['config_vars'][$this->configVars[0][0]]['value']);
		$this->assertEquals(1, $context['config_vars'][$this->configVars[1][0]]['value']);

		// Restore
		$_POST = array(
			'mtitle' => 'Maintenance Mode'
		);
		$token_check = createToken('admin-ssc');
		$_POST[$token_check['admin-ssc_token_var']] = $token_check['admin-ssc_token'];
		$token_check = createToken('admin-dbsc');
		$_POST[$token_check['admin-dbsc_token_var']] = $token_check['admin-dbsc_token'];
		saveSettings($this->configVars);
	}
}
