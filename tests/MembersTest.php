<?php

namespace PHPTDD;

class MembersTest extends BaseTestCase
{
	private $options = array();

	protected function setUp()
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-Membergroups.php');
		require_once($sourcedir . '/Subs-Members.php');

		// Hash password is slow with the default 10 on the hash cost, reducing this helps.
		$modSettings['bcrypt_hash_cost'] = 4;

		$this->options = array(
			array(
				'interface' => 'admin',
				'username' => 'User 1',
				'email' => 'search@email1.tld',
				'password' => 'password',
				'password_check' => 'password',
				'check_reserved_name' => false,
				'check_password_strength' => false,
				'check_email_ban' => false,
				'send_welcome_email' => false,
				'require' => 'nothing',
				'memberGroup' => 1,
			),
			array(
				'interface' => 'admin',
				'username' => 'User 2',
				'email' => 'search@email2.tld',
				'password' => 'password',
				'password_check' => 'password',
				'check_reserved_name' => false,
				'check_password_strength' => false,
				'check_email_ban' => false,
				'send_welcome_email' => false,
				'require' => 'nothing',
				'memberGroup' => 0,
			),
		);
		global $members, $membersTest;

		$membersTest = array();
		foreach ($this->options as $options)
			$membersTest[] = registerMember($options);
	}

	public function testAddMembers()
	{

		global $members, $membersTest;
		$members = list_getMembers(0, 30, 'id_member', '1');
		foreach ($membersTest as $member)
		{
			$this->assertEquals($member, $members['id_member']);
			$this->assertEquals('User ' . $member + 1, $members['real_name']);
		}

		$this->assertCount(3, $members);
	}

	public function tearDown()
	{
		global $members, $membersTest;

		deleteMembers($membersTest);
		$members = list_getMembers(0, 30, 'id_member', '1');

		$this->assertCount(1, $members);
	}
}
