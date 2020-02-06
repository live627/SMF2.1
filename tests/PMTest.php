<?php

namespace PHPTDD;

class PMTest extends BaseTestCase
{
	private $options = array();

	public function setUp() : void
	{
		global $modSettings, $sourcedir;

		require_once($sourcedir . '/Subs-Members.php');
		require_once($sourcedir . '/PersonalMessage.php');

		// Hash password is slow with the default 10 on the hash cost, reducing this helps.
		$modSettings['bcrypt_hash_cost'] = 4;

		$this->options = array(
			array(
				'interface' => 'admin',
				'username' => 'user',
				'email' => 'user@email.tld',
				'password' => '',
				'extra_register_vars' => array(
					'member_ip' => long2ip(rand(0, 2147483647)),
					'member_ip2' => long2ip(rand(0, 2147483647)),
				),
				'check_reserved_name' => true,
				'check_password_strength' => true,
				'check_email_ban' => true,
				'send_welcome_email' => true,
				'require' => 'nothing',
				'birthdate' => '1111-11-11',
				'timezone' => array_rand(smf_list_timezones()),
				'theme_vars' => array(
					'cust_loca' => 'testville',
					'cust_gender' => 'None',
				),
			),
		);
	}

	public function testAddMembers()
	{
		global $membersTest;

		$membersTest = array();
		foreach ($this->options as $options)
		{
			$memID = registerMember($options, true);
			$this->assertIsNumeric($memID);
			$membersTest[] = $memID;
		}
	}

	/**
	 * @depends testAddMembers
	 */
	public function testSendPM() : void
	{
		global $context, $membersTest;

		$_REQUEST['sa'] = 'send';
		MessageMain();
		$this->assertEmpty($context['to_value']);
		$this->assertFalse($context['quoted_message']);
		$this->assertEmpty($context['subject']);

		$_REQUEST['subject'] = 'Yo';
		$_REQUEST['message'] = 'This is for you, ok, have a great day';
		$_POST['to'] = 'test';
		$_POST['u'] = $membersTest[0];
		$_POST[$context['session_var']] = $context['session_id'];
		$_POST[$_SESSION['session_var']] = $_SESSION['session_value'];
		MessagePost2();
		$this->assertEmpty($context['post_error']);
		$this->assertStringContainsString(';done=sent', $context['current_label_redirect']);
		$this->assertCount(2, $context['send_log']['sent']);
		$this->assertEquals("PM successfully sent to 'test'.", $context['send_log']['sent'][1]);
		$this->assertEquals("PM successfully sent to 'user'.", $context['send_log']['sent'][$membersTest[0]]);

		$_REQUEST['userspec'] = 'test';
		$_REQUEST['search'] = 'great';
		MessageSearch2();
		$this->assertTrue(empty($context['search_errors']));
		$this->assertNotEmpty($context['personal_messages']);
	}

	/**
	 * @depends testSendPM
	 */
	public function testAddLabel() : void
	{
		global $context;

		$_POST['add'] = 'send';
		$_POST['label'] = 'test';
		$_POST[$context['session_var']] = $context['session_id'];
		$_POST[$_SESSION['session_var']] = $_SESSION['session_value'];
		LoadRules(true);
		ManageLabels();
		unset($_POST['add']);

		MessageMain();
		$this->assertContains('test', array_column($context['labels'], 'name', 'id'));
	}

	/**
	 * @depends testAddLabel
	 */
	public function testAddRule() : void
	{
		global $context;

		$_GET['add'] = 'send';
		ManageRules();
		$this->assertEmpty($context['rule']['id']);

		unset($_GET['add']);
		$_GET['save'] = 'send';
		$_POST['rule_logic'] = 'and';
		$_POST['rule_name'] = 'test';
		$_POST['ruletype'] = ['mid'];
		$_POST['ruledef'] = ['test'];
		$_POST['acttype'] = ['lab'];
		$_POST['labdef'] = [array_keys($context['labels'])[1]];
		$_POST[$context['session_var']] = $context['session_id'];
		$_POST[$_SESSION['session_var']] = $_SESSION['session_value'];
		ManageRules();
		LoadRules(true);
		$this->assertContains('test', array_column($context['rules'], 'name'));
	}

	/**
	 * @depends testAddRule
	 */
	public function testApplyRules() : void
	{
		global $context;

		$_GET['apply'] = 'send';
		unset($_GET['save']);
		$_GET[$context['session_var']] = $context['session_id'];
		$_GET[$_SESSION['session_var']] = $_SESSION['session_value'];
		ManageRules();

		MessageFolder();
		$labels = prepareMessageContext()['labels'];
		var_dump('test', array_column($labels, 'name'));
		$this->assertContains('test', array_column($labels, 'name'));
	}

	/**
	 * @depends testAddRule
	 */
	public function testModifyRule() : void
	{
		global $context;

		$_GET['add'] = 'send';
		$_GET['rid'] = key($context['rules']);
		LoadRules(true);
		ManageRules();
		$this->assertEquals($_GET['rid'], $context['rule']['id']);

		unset($_GET['add']);
		$_GET['save'] = 'send';
		$_POST['rule_logic'] = 'and';
		$_POST['rule_name'] = 'test';
		$_POST['ruletype'] = ['mid'];
		$_POST['ruledef'] = ['test'];
		$_POST['acttype'] = ['del'];
		$_POST[$context['session_var']] = $context['session_id'];
		$_POST[$_SESSION['session_var']] = $_SESSION['session_value'];
		ManageRules();
		LoadRules(true);
		$this->assertContains('test', array_column($context['rules'], 'name', 'id'));
	}

	/**
	 * @depends testModifyRule
	 */
	public function testApplyModifiedRules() : void
	{
		global $context;

		$_GET['apply'] = 'send';
		unset($_GET['save']);
		$_GET[$context['session_var']] = $context['session_id'];
		$_GET[$_SESSION['session_var']] = $_SESSION['session_value'];
		ManageRules();

		$_REQUEST['userspec'] = 'test';
		$_REQUEST['search'] = 'great';
		MessageSearch2();
		$this->assertTrue(empty($context['search_errors']));
		$this->assertEmpty($context['personal_messages']);
	}

	/**
	 * @depends testAddRule
	 */
	public function testDeleteRule() : void
	{
		global $context;

		$_GET['add'] = 'send';
		$_GET['rid'] = key(array_column($context['rules'], 'name', 'id'));
		LoadRules(true);
		ManageRules();
		$this->assertEquals($_GET['rid'], $context['rule']['id']);

		unset($_GET['add']);
		$_POST['delselected'] = 'send';
		$_POST['delrule'] = [$_GET['rid'] => 'on'];
		$_POST[$context['session_var']] = $context['session_id'];
		$_POST[$_SESSION['session_var']] = $_SESSION['session_value'];
		ManageRules();
		unset($_POST['delselected']);
		LoadRules(true);
		$this->assertNotContains('test', array_column($context['rules'], 'name'));
	}

	/**
	 * @depends testDeleteRule
	 */
	public function testRefreshRule() : void
	{
		$this->testSendPM();
		$this->testAddRule();
		$this->testApplyRules();
	}

	/**
	 * @depends testAddLabel
	 */
	public function testSearch() : void
	{
		global $context;

		MessageSearch();
		$this->assertCount(2, $context['search_labels']);
		$this->assertContains('test', array_column($context['search_labels'], 'name'));

		$_REQUEST['userspec'] = 'test';
		$_REQUEST['search'] = 'great';
		MessageSearch2();
		$this->assertTrue(empty($context['search_errors']));
		$this->assertCount(1, $context['personal_messages']);
		$this->assertStringContainsString('great', $context['personal_messages'][0]['body']);
	}

	/**
	 * @depends testAddLabel
	 */
	public function testDeleteLabel() : void
	{
		global $context;

		LoadRules(true);
		$this->assertContains('test', array_column($context['rules'], 'name'));
		$this->assertContains('test', array_column($context['labels'], 'name'));

		$_POST['delete'] = 'send';
		$_POST['delete_label'] = [array_keys($context['labels'])[1] => 'on'];
		$_POST[$context['session_var']] = $context['session_id'];
		$_POST[$_SESSION['session_var']] = $_SESSION['session_value'];
		ManageLabels();
		unset($_POST['delete']);
		LoadRules(true);
		MessageMain();
		$this->assertNotContains('test', array_column($context['rules'], 'name'));
		$this->assertNotContains('test', array_column($context['labels'], 'name'));
	}

	/**
	 * @depends testAddMembers
	 */
	public function testRemoveMembers() : void
	{
		global $membersTest;

		deleteMembers($membersTest);
		$members = list_getMembers(0, 30, 'id_member', '1');

		$this->assertCount(1, $members);
	}
}
