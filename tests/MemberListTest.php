<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class TestMemberList extends TestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Memberlist.php');
	}

	public function testActionIndexMembers(): void
	{
		global $context;

		Memberlist();
		$this->assertCount(6, $context['columns']);
		$this->assertEquals(11, $context['num_members']);
		$this->assertEquals('t', $context['members'][1]['sort_letter']);
	}

	public function testActionMlSearch(): void
	{
		global $context;

		$_GET['sa'] = 'search';
		$_GET['search'] = 'admin';
		$_GET['fields'] = 'name, email';
		Memberlist();
		unset($_GET['search']);
		$this->assertStringContainsString('test', $context['members'][1]['name']);
	}
}
