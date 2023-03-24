<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

final class PermissionsTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Subs-Sound.php';
	}

	/**
	 * @return int[][]
	 */
	public function permissionsControllerProvider(): array
	{
		return [
			[0],
			[2],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('permissionsControllerProvider')]
    public function testPermissionsController(int $group): void
	{
		global $context;

		$_GET['group'] = $group;
		ModifyMembergroup();
		$this->assertEquals($group, $context['group']['id']);
		$this->assertEquals('membergroup', $context['permission_type']);
		$this->permissionsAssertions();

		foreach (['view_stats', 'who_view'] as $check)
			$this->assertEquals(
				'on',
				$context['permissions']['membergroup']['columns'][0]['general']['permissions'][$check]['select']
			);
	}

	public function testGuestPermissionsController(): void
	{
		global $context;

		$_GET['group'] = -1;
		ModifyMembergroup();
		$this->assertEquals(-1, $context['group']['id']);
		$this->assertEquals('membergroup', $context['permission_type']);
		$this->permissionsAssertions();

		foreach (['view_stats'] as $check)
			$this->assertEquals(
				'on',
				$context['permissions']['membergroup']['columns'][0]['general']['permissions'][$check]['select']
			);

		foreach (['who_view'] as $check)
			$this->assertEquals(
				'off',
				$context['permissions']['membergroup']['columns'][0]['general']['permissions'][$check]['select']
			);
	}

	private function permissionsAssertions(): void
	{
		global $context;

		$this->assertIsArray($context['permissions']);
		$this->assertCount(2, $context['permissions']['membergroup']['columns']);
		$this->assertArrayHasKey('membergroup', $context['permissions']);
		$this->assertArrayHasKey('board', $context['permissions']);

		foreach (['view_stats', 'who_view'] as $check)
			$this->assertArrayHasKey(
				$check,
				$context['permissions']['membergroup']['columns'][0]['general']['permissions']
			);
	}

	public function testPermissions(): void
	{
		loadAllPermissions();
		$this->permissionsAssertions();
	}

	public function testllegalPermissions(): void
	{
		global $context;

		loadIllegalPermissions();
		$this->assertIsArray($context['illegal_permissions']);
		$this->assertEmpty($context['illegal_permissions']);
	}

	public function testIllegalGuestPermissions(): void
	{
		global $context;

		loadIllegalGuestPermissions();
		$this->assertIsArray($context['non_guest_permissions']);

		foreach (['admin_forum', 'edit_news'] as $check)
			$this->assertContains($check, $context['non_guest_permissions']);
	}

	public function testIllegalBBCHtmlGroups(): void
	{
		global $context;

		loadIllegalBBCHtmlGroups();
		$this->assertIsArray($context['permissions_excluded']['bbc_html']);

		foreach ([-1, 0, 2, 3, 4, 5, 6, 7, 8] as $check)
			$this->assertContains($check, $context['permissions_excluded']['bbc_html']);

		foreach ([-2, 1, 9] as $check)
			$this->assertNotContains($check, $context['permissions_excluded']['bbc_html']);
	}
}
