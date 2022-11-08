<?php

declare(strict_types=1);

namespace PHPTDD;

class AgreementTest extends BaseTestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Agreement.php');
		require_once($sourcedir . '/Modlog.php');
		require_once($sourcedir . '/ManageRegistration.php');
		require_once($sourcedir . '/Subs-Members.php');
		require_once($sourcedir . '/Profile-View.php');
	}

	public function testAgreement(): void
	{
		global $context;

		$this->assertArrayHasKey('requireAgreement', $GLOBALS['modSettings']);
		$this->assertEquals('1', $GLOBALS['modSettings']['requireAgreement']);
		Agreement();
		$this->assertStringContainsString('agreement', $context['agreement']);
		$this->assertFalse($context['can_accept_agreement']);
		$this->assertFalse($context['can_accept_privacy_policy']);
		$this->assertFalse($context['accept_doc']);
	}

	public function testEditPrivacyPolicy(): void
	{
		global $context;

		updateSettings(
			[
				'requireAgreement' => '1',
				'requirePolicyAgreement' => '0',
			]
		);

		loadLanguage('Admin+Login');
		EditPrivacyPolicy();
		$this->assertStringContainsString('', $context['privacy_policy']);

		$_POST['policy'] = 'test policy 123';
		$_POST[$context['session_var']] = $context['session_id'];
		$_POST[$context['admin-regp_token_var']] = $context['admin-regp_token'];
		EditPrivacyPolicy();
		$this->assertStringContainsString('policy', $context['privacy_policy']);
		loadLanguage('Modlog');
		$this->assertContains(
			'policy_updated',
			array_column(
				list_getModLogEntries(
					0,
					10,
					'log_time',
					'action IN ({array_string:actions})',
					['actions' => ['agreement_updated', 'policy_updated']],
					3
				),
				'action'
			)
		);
	}

	/**
	 * @depends testEditPrivacyPolicy
	 *
	 */
	public function testAgreement2(): void
	{
		global $context;

		$mem = list_getMembers(0, 1, 'id_member', 'id_member != 1', [], true)[0]['id_member'];
		FeignLogin($mem);
		$this->assertEquals($mem, $GLOBALS['user_info']['id']);
		$this->testAgreement();
		$this->assertStringContainsString('policy', $context['privacy_policy']);
		FeignLogin();
		$this->assertEquals(1, $GLOBALS['user_info']['id']);
	}

	public function testModifyRegistrationSettings(): void
	{
		global $context;

		loadLanguage('Admin+Login');
		$_GET = [
			'save' => '1',
		];
		$_POST = [
			'requireAgreement' => '1',
			'requirePolicyAgreement' => '1',
		];
		$token_check = createToken('admin-dbsc');
		$_POST[$token_check['admin-dbsc_token_var']] = $token_check['admin-dbsc_token'];
		$_POST[$context['session_var']] = $context['session_id'];
		ModifyRegistrationSettings();
		$this->assertTrue($context['saved_successful']);
		unset($context['saved_successful']);
		$this->assertArrayHasKey('requirePolicyAgreement', $context['config_vars']);
		$this->assertContains(
			'policy_accepted',
			array_column(
				list_getModLogEntries(
					0,
					10,
					'log_time',
					'action IN ({array_string:actions})',
					['actions' => ['agreement_accepted', 'policy_accepted']],
					2
				),
				'action'
			)
		);
		reloadSettings();
		$this->assertArrayHasKey('requirePolicyAgreement', $GLOBALS['modSettings']);
		$this->assertEquals('1', $GLOBALS['modSettings']['requirePolicyAgreement']);
	}

	/**
	 * @depends testEditPrivacyPolicy
	 * @depends testModifyRegistrationSettings
	 *
	 */
	public function testAgreement3(): void
	{
		global $context;

		$mem = list_getMembers(0, 1, 'id_member', 'id_member != 1', [], true)[0]['id_member'];
		FeignLogin($mem);
		$this->assertEquals($mem, $GLOBALS['user_info']['id']);
		$this->assertArrayHasKey('requirePolicyAgreement', $GLOBALS['modSettings']);
		$this->assertEquals('1', $GLOBALS['modSettings']['requirePolicyAgreement']);
		Agreement();
		$this->assertStringContainsString('agreement', $context['agreement']);
		$this->assertFalse($context['can_accept_agreement']);
		$this->assertTrue($context['can_accept_privacy_policy']);
		$this->assertTrue($context['accept_doc']);
		$this->assertStringContainsString('policy', $context['privacy_policy']);
		FeignLogin();
		$this->assertEquals(1, $GLOBALS['user_info']['id']);
	}

	/**
	 * @depends testModifyRegistrationSettings
	 *
	 */
	public function testAcceptAgreement(): void
	{
		global $context;

		$_POST[$context['session_var']] = $context['session_id'];
		$mem = list_getMembers(0, 1, 'id_member', 'id_member != 1', [], true)[0]['id_member'];
		FeignLogin($mem);
		$this->assertEquals($mem, $GLOBALS['user_info']['id']);
		AcceptAgreement();
		$this->assertStringContainsString('policy', $context['privacy_policy']);
		loadLanguage('Modlog');
		$this->assertContains(
			'policy_accepted',
			array_column(
				list_getModLogEntries(
					0,
					10,
					'log_time',
					'action IN ({array_string:actions})',
					['actions' => ['agreement_accepted', 'policy_accepted']],
					2
				),
				'action'
			)
		);
		FeignLogin();
		$this->assertEquals(1, $GLOBALS['user_info']['id']);
	}

	/**
	 * @depends testAcceptAgreement
	 *
	 */
	public function testAgreement4(): void
	{
		global $context;

		$mem = list_getMembers(0, 1, 'id_member', 'id_member != 1', [], true)[0]['id_member'];
		FeignLogin($mem);
		$this->assertEquals($mem, $GLOBALS['user_info']['id']);
		$this->testAgreement();
		$this->assertStringContainsString('policy', $context['privacy_policy']);
		FeignLogin();
		$this->assertEquals(1, $GLOBALS['user_info']['id']);
	}

	/**
	 * @depends testAcceptAgreement
	 *
	 */
	public function testTracking(): void
	{
		global $context;

		loadLanguage('Profile');
		$mem = list_getMembers(0, 1, 'id_member', 'id_member != 1', [], true)[0]['id_member'];
		trackEdits($mem);
		$this->assertCount(1, $context['edit_list']['rows']);
		trackEdits(1);
		$this->assertCount(1, $context['edit_list']['rows']);
	}
}