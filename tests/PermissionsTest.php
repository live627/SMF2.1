<?php

namespace PHPTDD;

class PermissionsTest extends BaseTestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-Sound.php');
	}

	public function permissionsControllerProvider()
	{
		return array(
			array(0),
			array(2),
		);
	}

	/**
	 * @dataProvider permissionsControllerProvider
	 */
	public function testPermissionsController(int $group)
	{
		global $context;

		$_GET['group'] = $group;
		ModifyMembergroup();
		$this->assertEquals($group, $context['group']['id']);
		$this->assertEquals('membergroup', $context['permission_type']);
		$this->permissionsAssertions();
		foreach (array('view_stats', 'who_view') as $check)
			$this->assertEquals('on', $context['permissions']['membergroup']['columns'][0]['general']['permissions'][$check]['select']);
	}

	public function testGuestPermissionsController()
	{
		global $context;

		$_GET['group'] = -1;
		ModifyMembergroup();
		$this->assertEquals(-1, $context['group']['id']);
		$this->assertEquals('membergroup', $context['permission_type']);
		$this->permissionsAssertions();
		foreach (array('view_stats') as $check)
			$this->assertEquals('on', $context['permissions']['membergroup']['columns'][0]['general']['permissions'][$check]['select']);
		foreach (array('who_view') as $check)
			$this->assertEquals('off', $context['permissions']['membergroup']['columns'][0]['general']['permissions'][$check]['select']);
	}

	private function permissionsAssertions()
	{
		global $context;

		$this->assertIsArray($context['permissions']);
		$this->assertCount(2, $context['permissions']['membergroup']['columns']);
		$this->assertArrayHasKey('membergroup', $context['permissions']);
		$this->assertArrayHasKey('board', $context['permissions']);
		foreach (array('view_stats', 'who_view') as $check)
			$this->assertArrayHasKey($check, $context['permissions']['membergroup']['columns'][0]['general']['permissions']);
	}

	public function testPermissions()
	{
		loadAllPermissions();
		$this->permissionsAssertions();
	}

	public function testllegalPermissions()
	{
		global $context;

		loadIllegalPermissions();
		$this->assertIsArray($context['illegal_permissions']);
		$this->assertEmpty($context['illegal_permissions']);
	}

	public function testIllegalGuestPermissions()
	{
		global $context;

		loadIllegalGuestPermissions();
		$this->assertIsArray($context['non_guest_permissions']);
		foreach (array('admin_forum', 'edit_news') as $check)
			$this->assertContains($check, $context['non_guest_permissions']);
	}

	public function testIllegalBBCHtmlGroups()
	{
		global $context;

		loadIllegalBBCHtmlGroups();
		$this->assertIsArray($context['permissions_excluded']['bbc_html']);
		foreach (array(-1, 0, 2, 3, 4, 5, 6, 7, 8) as $check)
			$this->assertContains($check, $context['permissions_excluded']['bbc_html']);
		foreach (array(-2, 1, 9) as $check)
			$this->assertNotContains($check, $context['permissions_excluded']['bbc_html']);
	}
}