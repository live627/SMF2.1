<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class TestMemberList extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Memberlist.php';
	}

	public function test(): void
	{
		global $context;

		$_REQUEST['start'] = '0';
		Memberlist();
		$this->assertCount(6, $context['columns']);
		$this->assertEquals(11, $context['num_members']);
		$this->assertEquals('t', $context['members'][1]['sort_letter']);
	}

	public function testSearch(): void
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
