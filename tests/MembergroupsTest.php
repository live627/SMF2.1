<?php

namespace PHPTDD;

class TestMembergroups extends BaseTestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-Members.php');
		require_once($sourcedir . '/Subs-Membergroups.php');
	}

	public function testGroupsAllowedTo()
	{
		$member_groups = groupsAllowedTo('who_view');
		$this->assertCount(3, $member_groups['allowed']);
		$this->assertContains(0, $member_groups['allowed']);
		$this->assertContains(1, $member_groups['allowed']);
		$this->assertContains(2, $member_groups['allowed']);
		$this->assertCount(0, $member_groups['denied']);
	}

	public function testGroupsAllowedToByBoard()
	{
		$member_groups = groupsAllowedTo('post_reply_any', 1);
		$this->assertCount(4, $member_groups['allowed']);
		$this->assertContains(0, $member_groups['allowed']);
		$this->assertContains(1, $member_groups['allowed']);
		$this->assertContains(2, $member_groups['allowed']);
		$this->assertContains(3, $member_groups['allowed']);
		$this->assertCount(0, $member_groups['denied']);
	}

	public function testGroupsWithPermissions()
	{
		$group_permissions = array('moderate_forum', 'who_view');
		$board_permissions = array('moderate_board');
		$member_groups = getGroupsWithPermissions($group_permissions, $board_permissions);

		$this->assertCount(3, $member_groups);
		$this->assertCount(1, $member_groups['moderate_forum']['allowed']);
		$this->assertContains(1, $member_groups['moderate_forum']['allowed']);
		$this->assertCount(0, $member_groups['moderate_forum']['denied']);

		$this->assertCount(3, $member_groups['who_view']['allowed']);
		$this->assertContains(0, $member_groups['who_view']['allowed']);
		$this->assertContains(1, $member_groups['who_view']['allowed']);
		$this->assertContains(2, $member_groups['who_view']['allowed']);
		$this->assertCount(0, $member_groups['who_view']['denied']);

		$this->assertCount(3, $member_groups['moderate_board']['allowed']);
		$this->assertContains(1, $member_groups['moderate_board']['allowed']);
		$this->assertContains(2, $member_groups['moderate_board']['allowed']);
		$this->assertContains(3, $member_groups['moderate_board']['allowed']);
		$this->assertCount(0, $member_groups['moderate_board']['denied']);
	}

	public function testGroupsWithPermissionsByProfile()
	{
		$member_groups = getGroupsWithPermissions(array(), array('post_reply_any'), 3);
		$this->assertCount(4, $member_groups['post_reply_any']['allowed']);
		$this->assertContains(0, $member_groups['post_reply_any']['allowed']);
		$this->assertContains(1, $member_groups['post_reply_any']['allowed']);
		$this->assertContains(2, $member_groups['post_reply_any']['allowed']);
		$this->assertContains(3, $member_groups['post_reply_any']['allowed']);
		$this->assertCount(0, $member_groups['post_reply_any']['denied']);

		$member_groups = getGroupsWithPermissions(array(), array('post_reply_any'), 4);
		$this->assertCount(3, $member_groups['post_reply_any']['allowed']);
		$this->assertContains(1, $member_groups['post_reply_any']['allowed']);
		$this->assertContains(2, $member_groups['post_reply_any']['allowed']);
		$this->assertContains(3, $member_groups['post_reply_any']['allowed']);
		$this->assertCount(0, $member_groups['post_reply_any']['denied']);
	}

	public function testAddNoMembersToGroup()
	{
		$this->assertFalse(addMembersToGroup(array(), 2));
	}

	public function testAddMembersToGroup()
	{
		$this->assertTrue(addMembersToGroup(1, 2));
		$members = array();
		$this->assertFalse(listMembergroupMembers_Href($members, 2));
		$this->assertCount(1, $members);
		$this->assertArrayHasKey(1, $members);
	}

	public function testRemoveNoMembersFromGroups()
	{
		$this->assertFalse(removeMembersFromGroups(array(), array(2)));
	}

	public function testRemoveMembersFromGroups()
	{
		$this->assertTrue(removeMembersFromGroups(1, 2));
		$members = array();
		$this->assertFalse(listMembergroupMembers_Href($members, 2));
		$this->assertCount(0, $members);
	}
}