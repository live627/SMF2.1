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
				'password' => '',
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
				'check_reserved_name' => true,
				'check_password_strength' => true,
				'check_email_ban' => true,
				'send_welcome_email' => true,
				'require' => 'nothing',
				'birthdate' => '11-11-1111',
				'timezone' => 'time',
			),
			array(
				'interface' => 'admin',
				'username' => 'User 3',
				'email' => 'search@email.tld',
				'password' => 'password',
				'password_check' => 'password',
				'check_reserved_name' => true,
				'check_password_strength' => true,
				'check_email_ban' => true,
				'send_welcome_email' => true,
				'require' => 'activation',
				'birthdate' => '11-11-1111',
				'timezone' => 'time',
			),
			array(
				'interface' => 'admin',
				'username' => 'User 4',
				'email' => 'search4@email.tld',
				'password' => 'password',
				'password_check' => 'password',
				'check_reserved_name' => true,
				'check_password_strength' => true,
				'check_email_ban' => true,
				'send_welcome_email' => true,
				'require' => 'nothing',
				'birthdate' => '11-11-1111',
				'timezone' => 'time',
			),
		);
		global $members, $membersTest;

		$membersTest = array();
		foreach ($this->options as $options)
		{
			$memID = registerMember($options, true);
			$this->assertInternalType('int', $memID);
			$membersTest[] = $memID;
		}
	}

	public function testAddMembers()
	{

		global $members, $membersTest;
		$members = list_getMembers(0, 30, 'id_member', 'id_member IN({array_int:members})', ['members' => $membersTest]);
		$this->assertCount(count($this->options), $members);
		foreach ($members as $member)
			$this->assertContains($member['id_member'], $membersTest);
	}

	public function tearDown()
	{
		global $members, $membersTest;

		deleteMembers($membersTest);
		$members = list_getMembers(0, 30, 'id_member', '1');

		$this->assertCount(1, $members);
	}
}
