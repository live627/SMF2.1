<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Error\Error as PHPUnitError;

final class MembersTest extends TestCase
{
	private $options = [];

	protected function setUp(): void
	{
		global $modSettings, $sourcedir;

		require_once __DIR__ . '/../Sources/Subs-Membergroups.php';
		require_once __DIR__ . '/../Sources/Subs-Members.php';
		require_once __DIR__ . '/../Sources/Subs-Boards.php';

		// Hash password is slow with the default 10 on the hash cost, reducing this helps.
		$modSettings['bcrypt_hash_cost'] = 4;

		$this->options = [
			[
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
			],
			[
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
				'timezone' => array_rand(smf_list_timezones()),
			],
			[
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
				'timezone' => array_rand(smf_list_timezones()),
			],
			[
				'interface' => 'admin',
				'username' => 'User 4',
				'email' => 'search4@email.tld',
				'password' => 'password',
				'password_check' => 'password',
				'extra_register_vars' => [
					'member_ip' => long2ip(random_int(0, 2147483647)),
					'member_ip2' => long2ip(random_int(0, 2147483647)),
				],
				'check_reserved_name' => true,
				'check_password_strength' => true,
				'check_email_ban' => true,
				'send_welcome_email' => true,
				'require' => 'nothing',
				'birthdate' => '1111-11-11',
				'timezone' => array_rand(smf_list_timezones()),
				'theme_vars' => [
					'cust_loca' => 'testville',
					'cust_gender' => 'None',
				],
			],
		];
	}

	#[\PHPUnit\Framework\Attributes\Group('slow')]
    public function testAddMembers(): void
	{
		global $membersTest;

		$membersTest = [];

		foreach ($this->options as $options)
		{
			$memID = registerMember($options, true);
			$this->assertIsNumeric($memID);
			$membersTest[] = $memID;
		}
		$members =
			list_getMembers(0, 30, 'id_member', 'id_member IN({array_int:members})', ['members' => $membersTest]);
		$this->assertCount(4, $members);

		foreach ($members as $member)
			$this->assertContains($member['id_member'], $membersTest);
	}

	#[\PHPUnit\Framework\Attributes\Depends('testAddMembers')]
    public function testDuplicateMembers(): void
	{
		global $membersTest;

		$members = list_getMembers(
			0,
			30,
			'id_member',
			'id_member IN ({array_int:members})',
			['members' => $membersTest],
			true
		);
		$this->assertCount(2, $members[0]['duplicate_members']);
		$this->assertCount(2, $members[1]['duplicate_members']);
		$this->assertCount(2, $members[2]['duplicate_members']);
		$this->assertCount(0, $members[3]['duplicate_members']);
	}

	#[\PHPUnit\Framework\Attributes\Depends('testAddMembers')]
    public function testReattributePosts(): void
	{
		global $membersTest;

		reattributePosts($membersTest[0], 'info@simplemachines.org', 'Simple Machines', true);
		$this->assertEquals($membersTest[0], getMsgMemberID(1));

		reattributePosts(0, 'info@simplemachines.org', 'Simple Machines', true);
		$this->assertEquals(0, getMsgMemberID(1));
	}

	public function testReservedName(): void
	{
		global $modSettings;

		$this->assertFalse(isReservedName('test', 1));
		$this->assertFalse(isReservedName('t%', 1));

		$this->assertTrue(isReservedName('test'));
		$this->assertTrue(isReservedName('te*st', 0, true, false));
		$this->assertTrue(isReservedName('te*st', 1, true, false));
		$this->assertTrue(isReservedName('t%'));

		$this->assertCount(4, explode("\n", $modSettings['reserveNames']));
	}

	public function reservedNameProvider(): array
	{
		return [
			['Admin', 'admin'],
			['Webmaster', 'webmaster'],
			['Guest', 'guest'],
			['root', 'ROOT'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('reservedNameProvider')]
    public function testReservedNameCaseSensitive(string $uname, string $uname2) : void
	{
		global $modSettings;

		$modSettings['reserveCase'] = 1;
		$this->assertTrue(isReservedName($uname, 1, true, false));
		$this->assertFalse(isReservedName($uname2, 1, true, false));
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('reservedNameProvider')]
    public function testReservedNameCaseInsensitive(string $uname, string $uname2) : void
	{
		global $modSettings;

		$modSettings['reserveCase'] = 0;
		$this->assertTrue(isReservedName($uname, 1, true, false));
		$this->assertTrue(isReservedName($uname2, 1, true, false));
	}

	#[\PHPUnit\Framework\Attributes\Depends('testAddMembers')]
    public function testMembersAllowedTo(): void
	{
		global $membersTest;

		$members = membersAllowedTo('moderate_forum');
		$this->assertCount(2, $members);

		$members = membersAllowedTo('delete_any', 1);
		$this->assertCount(2, $members);
	}

	public function testNoMinUserInfo(): void
	{
		$this->assertCount(0, loadMinUserInfo([]));
		$this->assertCount(0, loadMinUserInfo([0]));
	}

	public function testMyMinUserInfo(): void
	{
		$data = loadMinUserInfo(1);
		$this->assertArrayHasKey(1, $data);
		$this->assertEquals(1, $data[1]['id']);
		$this->assertEquals('test', $data[1]['username']);
		$this->assertEquals('test', $data[1]['name']);
	}

	public function testMinUserInfoUnexpectedBehavior(): void
	{
		$data = loadMinUserInfo(4);
		$this->assertArrayHasKey(4, $data);
		$this->assertEquals(4, $data[4]['id']);
		$this->assertArrayHasKey(1, $data);
		$this->assertEquals(1, $data[1]['id']);
	}

	public function testUnknbownDataSet(): void
	{
		$this->expectException(PHPUnitError::class);
		loadMemberData('test', true, 'random');
	}

	public function testMyMemberData(): void
	{
		global $context, $memberContext, $membersTest, $user_profile;

		$members = loadMemberData('test', true, 'minimal');
		$this->assertCount(1, $members);
		$this->assertEquals('minimal', $context['loadMemberContext_set']);
		$this->assertContains(1, $members);
		$this->assertArrayHasKey(1, $user_profile);
		$this->assertEquals(1, $user_profile[1]['id_member']);
		$this->assertEquals('test', $user_profile[1]['member_name']);
		$this->assertEquals('test', $user_profile[1]['real_name']);
		$this->assertEquals(1, $user_profile[1]['id_group']);
		$data = loadMemberContext(1);
		$this->assertIsArray($data);
		$this->assertEquals(1, $data['id']);
		$this->assertEquals('test', $data['username']);
		$this->assertEquals('test', $data['name']);
		$this->assertArrayHasKey(1, $memberContext);
		$this->assertEquals(1, $memberContext[1]['id']);
		$this->assertEquals('test', $memberContext[1]['username']);
		$this->assertEquals('test', $memberContext[1]['name']);
	}

	public function testNoMemberData(): void
	{
		$this->assertFalse(loadMemberContext(0));
		$this->assertCount(0, loadMemberData([]));
		$this->assertCount(0, loadMemberData([0]));
	}

	public function testBakedMemberData(): void
	{
		$this->expectException(PHPUnitError::class);
		loadMemberContext(420);
	}

	#[\PHPUnit\Framework\Attributes\Depends('testAddMembers')]
    public function testMembersData(): void
	{
		global $context, $memberContext, $membersTest, $user_profile;

		$members = loadMemberData($membersTest);
		$this->assertCount(4, $members);
		$this->assertEquals('normal', $context['loadMemberContext_set']);

		foreach ($membersTest as $member)
		{
			$this->assertContains($member, $members);
			$this->assertArrayHasKey($member, $user_profile);
			$data = loadMemberContext($member, true);
			$this->assertIsArray($data);
			$this->assertEquals($member, $data['id']);
			$this->assertArrayHasKey($member, $memberContext);
			$this->assertEquals($member, $memberContext[$member]['id']);
		}
		$this->assertCount(2, $memberContext[$membersTest[3]]['custom_fields']);
		$this->assertEquals(
			'cust_loca',
			$memberContext[$membersTest[3]]['custom_fields'][0]['col_name']
		);
		$this->assertEquals(
			'testville',
			$memberContext[$membersTest[3]]['custom_fields'][0]['value']
		);
		$this->assertEquals(
			'cust_gender',
			$memberContext[$membersTest[3]]['custom_fields'][1]['col_name']
		);
		$this->assertEquals(
			'<span class=" main_icons gender_0" title="None"></span>',
			$memberContext[$membersTest[3]]['custom_fields'][1]['value']
		);
	}

	#[\PHPUnit\Framework\Attributes\Depends('testAddMembers')]
    public function testRemoveMembers(): void
	{
		global $membersTest;

		deleteMembers($membersTest);
		$members = list_getMembers(0, 30, 'id_member', '1');

		$this->assertCount(11, $members);
	}
}
