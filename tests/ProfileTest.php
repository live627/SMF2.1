<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

final class ProfileTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Profile.php';
	}

	protected function tearDown(): void
	{
		unset($_REQUEST);
	}

	public function testSummary(): void
	{
		global $context;

		ModifyProfile();
		$this->assertTrue($context['user']['is_owner']);
		$this->assertTrue($context['can_see_ip']);

		$_REQUEST['u'] = '2';
		ModifyProfile();
		$this->assertFalse($context['user']['is_owner']);
		$this->assertTrue($context['can_see_ip']);
		$this->assertEquals('user0', $context['member']['name']);
	}
}
