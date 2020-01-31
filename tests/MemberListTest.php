<?php

namespace PHPTDD;

class TestMemberList extends BaseTestCase
{
	protected function setUp()
	{
		global $sourcedir, $user_info;

		require_once($sourcedir . '/Memberlist.php');
		$user_info['permissions'][] = 'view_mlist';
	}

	public function testActionIndexMembers()
	{
		global $context;

		Memberlist();
		$this->assertCount(6, $context['columns']);
		$this->assertEquals(1, $context['num_members']);
		$this->assertEquals('t', $context['members'][1]['sort_letter']);
	}

	public function testActionMlSearch()
	{
		global $context;

		$_GET['sa'] = 'search';
		$_GET['search'] = 'admin';
		$_GET['fields'] = 'name, email';
		Memberlist();
		$this->assertContains('test', $context['members'][1]['name']);
	}
}