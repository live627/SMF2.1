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

	/**
	 * @group slowl,k
	 */
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
	public function testAddRule() : void
	{
		global $context, $membersTest;

		$_GET['add'] = 'send';
		ManageRules();
		$this->assertEmpty($context['rule']['id']);

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

		$_GET['apply'] = 'send';
		unset($_GET['save']);
		$_GET[$context['session_var']] = $context['session_id'];
		$_GET[$_SESSION['session_var']] = $_SESSION['session_value'];
		LoadRules(true);
		ManageRules();

		$_REQUEST['userspec'] = 'test';
		$_REQUEST['search'] = 'great';
		MessageSearch2();
		$this->assertTrue(empty($context['search_errors']));
	//	$this->assertIsNotArray($context['personal_messages']);
		$this->assertEmpty($context['personal_messages']);
	}

	/**
	 * @depends testAddMemberser
	 */
	public function testRemoveMembers() : void
	{
		global $membersTest;

		deleteMembers($membersTest);
		$members = list_getMembers(0, 30, 'id_member', '1');

		$this->assertCount(1, $members);
	}
}
