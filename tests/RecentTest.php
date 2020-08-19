<?php

namespace PHPTDD;

class RecentTest extends BaseTestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Recent.php');
	}

	public function testGetLastPost()
	{
		$single = getLastPost();
		$this->assertStringContainsString('Automated Topic', $single['subject']);
	}

	public function testRecentPosts()
	{
		global $context;

		$_REQUEST['start'] = '0';
		RecentPosts();
		unset($_REQUEST['start']);
		$this->assertCount(10, $context['posts']);
		foreach ($context['posts'] as $post)
		{
			preg_match(
				'/Board ([0-9]+) - Topic ([0-9]+) - Messsage ([0-9]+)/',
				$post['message'],
				$matches
			);
			$this->assertEquals($matches[3], $post['id']);
			$this->assertEquals($matches[2], $post['topic']);
			$this->assertEquals($matches[1], $post['board']['id']);
			$this->assertEquals(1, $post['category']['id']);
			$this->assertStringContainsString('Category', $post['category']['name']);
			$this->assertStringContainsString('Test', $post['subject']);
			$this->assertStringContainsString('Test', $post['shorten_subject']);
		}
	}

	public function testUnreadTopics()
	{
		global $context, $modSettings;

		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		$modSettings['preview_characters'] = 1;
		UnreadTopics();
		$this->assertStringContainsString('SMF', $context['topics'][1]['last_post']['subject']);
		unset($_REQUEST['action'], $_REQUEST['start'], $context['topics'][1]);
		$this->assertCount(11, $context['topics']);
		foreach ($context['topics'] as $topic)
		{
			preg_match(
				'/Board ([0-9]+) - Topic ([0-9]+) - Messsage ([0-9]+)/',
				$topic['last_post']['preview'],
				$matches
			);
			$this->assertEquals($matches[3], $topic['last_post']['id']);
			$this->assertStringContainsString('?topic=' . $matches[2], $topic['href']);
			$this->assertStringContainsString('?topic=' . $matches[2], $topic['last_post']['href']);
			$this->assertStringContainsString('?board=' . $matches[1], $topic['board']['href']);
			$this->assertEquals($matches[1], $topic['board']['id']);
			$this->assertEquals(1, $topic['replies']);
			$this->assertEquals(0, $topic['views']);
			$this->assertStringContainsString('Test', $topic['last_post']['subject']);
		}
	}
}