<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
	public function setUp(): void
	{
		global $sourcedir;

		require_once($sourcedir . '/Profile.php');
	}

	public function tearDown(): void
	{
		unset($_REQUEST);
	}

	public function testSummary()
	{
		global $context;

		ModifyProfile();
		$this->assertTrue($context['user']['is_owner']);
		$this->assertTrue($context['can_see_ip']);

		$_REQUEST['u'] = '2';
		ModifyProfile();
		$this->assertFalse($context['user']['is_owner']);
		$this->assertTrue($context['can_see_ip']);
		$this->assertEquals('<strong>Test successful</strong>', $context['member']['name']);
	}
}
