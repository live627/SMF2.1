<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class GroupsTest extends TestCase
{
	public function setUp(): void
	{
		require_once __DIR__ . '/../Sources/Groups.php';
	}

	public function tearDown(): void
	{
		unset($_REQUEST);
	}

	public function test()
	{
		global $context;

		Groups();
		$this->assertEquals('show_list', $context['sub_template']);
		$this->assertEquals(4, $context['group_lists']['num_columns']);
	}

	public function testShowMembers()
	{
		global $context;

		$_REQUEST['group'] = '1';
		$_REQUEST['sa'] = 'members';
		Groups(); 
		$this->assertEquals(1, $context['members'][0]['id']);
		$this->assertStringContainsString('test', $context['members'][0]['name']);
		$this->assertEquals(1, $context['total_members']);
	}
}
