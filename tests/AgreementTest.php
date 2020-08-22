<?php

namespace PHPTDD;

class AgreementTest extends BaseTestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Agreement.php');
		require_once($sourcedir . '/Modlog.php');
		require_once($sourcedir . '/Subs-Members.php');
	}

	public function testAgreement()
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

	public function testEditPrivacyPolicy()
	{
		global $context;

		EditPrivacyPolicy();
		$this->assertStringContainsString('', $context['privacy_policy']);

		$_POST['policy'] = 'test policy 123';
		$_POST[$context['session_var']] = $context['session_id'];
		$_POST[$context['admin-regp_token_var']] = $context['admin-regp_token'];
		EditPrivacyPolicy();
		$this->assertStringContainsString('policy', $context['privacy_policy']);
		loadLanguage('Modlog');
		$this->assertContains('policy_updated', array_column(list_getModLogEntries(0, 10, 'log_time', 'action IN ({array_string:actions})', ['actions' => array('agreement_updated', 'policy_updated')], 3), 'action'));
	}

	public function testModifyRegistrationSettings()
	{
		global $context;

		$_GET = array(
			'save' => '1',
		);
		$_POST = array(
			'requireAgreement' => '1',
			'requirePolicyAgreement' => '1',
		);
		$token_check = createToken('admin-dbsc');
		$_POST[$token_check['admin-dbsc_token_var']] = $token_check['admin-dbsc_token'];
		$_POST[$context['session_var']] = $context['session_id'];
		ModifyRegistrationSettings(); 
		$this->assertTrue($context['saved_successful']);
		unset($context['saved_successful']);
		$this->assertArrayHasKey('requirePolicyAgreement', $context['config_vars']);
		$this->assertContains('policy_accepted', array_column(list_getModLogEntries(0, 10, 'log_time', 'action IN ({array_string:actions})', ['actions' => array('agreement_accepted', 'policy_accepted')], 2), 'action'));
	} 
}