<?php

namespace PHPTDD;

class MembersTest extends BaseTestCase
{
	private $options = array();

	public function setUp() : void
	{
		global $modSettings, $sourcedir;

		require_once($sourcedir . '/Subs-Membergroups.php');
		require_once($sourcedir . '/Subs-Members.php');
		require_once($sourcedir . '/Subs-Boards.php');

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
				'birthdate' => '1111-11-11',
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
				'birthdate' => '1111-11-11',
				'timezone' => 'time',
			),
			array(
				'interface' => 'admin',
				'username' => 'User 4',
				'email' => 'search4@email.tld',
				'password' => 'password',
				'password_check' => 'password',
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
				'timezone' => 'time',
			),
		);
	}

	/**
	 * @group slowcv
	 */
	public function testAddMembers()
	{
		global $membersTest;

		$membersTest = array();
		foreach ($this->options as $options)
		{
			$memID = registerMember($options, true);
			$this->assertIsInt($memID);
			$membersTest[] = $memID;
		}
		$members = list_getMembers(0, 30, 'id_member', 'id_member IN({array_int:members})', ['members' => $membersTest]);
		$this->assertCount(4, $members);
		foreach ($members as $member)
			$this->assertContains($member['id_member'], $membersTest);
	}

	/**
	 * @depends testAddMembers
	 */
	public function testDuplicateMembers() : void
	{
		global $membersTest;

		$members = list_getMembers(0, 30, 'id_member', 'id_member IN({array_int:members})', ['members' => $membersTest], true);
		$this->assertCount(2, $members[0]['duplicate_members']);
		$this->assertCount(2, $members[1]['duplicate_members']);
		$this->assertCount(2, $members[2]['duplicate_members']);
		$this->assertCount(0, $members[3]['duplicate_members']);
	}

	/**
	 * @depends testAddMembers
	 */
	public function testReattributePosts() : void
	{
		global $membersTest;

		reattributePosts($membersTest[0], 'info@simplemachines.org', 'Simple Machines', true);
		$this->assertEquals($membersTest[0], getMsgMemberID(1));

		reattributePosts(0, 'info@simplemachines.org', 'Simple Machines', true);
		$this->assertEquals(0, getMsgMemberID(1));
	}

	/**
	 * @depends testAddMembers
	 */
	public function testMembersAllowedTo() : void
	{
		global $membersTest;

		$members = membersAllowedTo('moderate_forum');
		$this->assertCount(2, $members);

		$members = membersAllowedTo('delete_any', 1);
		$this->assertCount(2, $members);
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
