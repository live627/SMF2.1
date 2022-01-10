<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class RecentTest extends TestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Recent.php');
		require_once($sourcedir . '/Subs-Boards.php');
		require_once($sourcedir . '/Notify.php');
		require_once($sourcedir . '/Subs-Post.php');
		require_once($sourcedir . '/Display.php');
		require_once($sourcedir . '/RemoveTopic.php');
	}

	public function testGetLastPost(): void
	{
		$single = getLastPost();
		$this->assertStringContainsString('Automated Test', $single['subject']);
	}

	public function testRecentPostsInBoard(): void
	{
		global $board, $context;

		$_REQUEST['start'] = '0';
		$board = 1;
		RecentPosts();
		$this->assertCount(1, $context['posts']);
		unset($GLOBALS['board'], $context['posts']);
	}

	public function testRecentPostsInCat(): void
	{
		global $context;

		$_REQUEST['start'] = '0';
		$_REQUEST['c'] = 1;
		RecentPosts();
		$this->assertCount(10, $context['posts']);
		unset($_REQUEST['c'], $context['posts']);
	}

	public function testRecentPosts(): void
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

	public function testUnreadTopicsInCat(): void
	{
		global $context;

		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		$_REQUEST['c'] = '1';
		$modSettings['preview_characters'] = 1;
		UnreadTopics();
		$this->assertCount(11, $context['topics']);
		$this->assertStringContainsString('SMF', $context['topics'][1]['last_post']['subject']);
		unset($_REQUEST['c'], $context['topics']);
	}

	public function testUnreadTopicsInBoard(): void
	{
		global $board, $context, $modSettings;

		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		$board = 1;
		$modSettings['preview_characters'] = 1;
		UnreadTopics();
		$this->assertCount(1, $context['topics']);
		$this->assertStringContainsString('SMF', $context['topics'][1]['last_post']['subject']);
		unset($GLOBALS['board'], $context['topics']);
	}

	public function testMarkBoardRead(): void
	{
		global $board, $context, $modSettings, $smcFunc;

		markBoardsRead([1]);
		$request = $smcFunc['db_query']('', '
			SELECT id_msg
			FROM {db_prefix}log_mark_read
			WHERE id_board = 1
				AND id_member = 1');
		[$actual] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		$board = 1;
		UnreadTopics();
		$topics = $context['topics'];
		unset($GLOBALS['board'], $context['topics']);

		markBoardsRead([1], true);
		$request = $smcFunc['db_query']('', '
			SELECT id_msg
			FROM {db_prefix}log_mark_read
			WHERE id_board = 1
				AND id_member = 1');
		[$actual2] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		$_REQUEST['boards'] = 1;
		UnreadTopics();
		$unread_topics = $context['topics'];
		unset($_REQUEST['boards'], $context['topics']);

		$this->assertEquals($modSettings['maxMsgID'], $actual);
		$this->assertNull($actual2);
		$this->assertIsArray($topics);
		$this->assertEmpty($topics);
		$this->assertCount(1, $unread_topics);
		$this->assertStringContainsString('SMF', $unread_topics[1]['last_post']['subject']);
	}

	public function testMarkRead(): void
	{
		global $board, $context, $smcFunc, $topic;

		$_REQUEST['sa'] = 'unreadreplies';
		$_REQUEST['topics'] = '1';
		MarkRead();
		unset($_REQUEST['sa'], $_REQUEST['topics']);

		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		$board = 1;
		UnreadTopics();
		$topics = $context['topics'];
		unset($GLOBALS['board'], $context['topics']);

		$_REQUEST['sa'] = 'topic';
		$_REQUEST['t'] = '0';
		$topic = 1;
		MarkRead();
		unset($GLOBALS['topic'], $_REQUEST['sa'], $_REQUEST['t']);

		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		$_REQUEST['boards'] = 1;
		UnreadTopics();
		$unread_topics = $context['topics'];
		unset($_REQUEST['boards'], $context['topics']);

		$this->assertIsArray($topics);
		$this->assertEmpty($topics);
		$this->assertCount(1, $unread_topics);
		$this->assertStringContainsString('SMF', $unread_topics[1]['last_post']['subject']);
	}

	public function testNewPostMarkedRead(): void
	{
		global $board, $context, $smcFunc, $topic;

		$msgOptions = [
			'body' => 'Mark read test',
			'id' => 0,
			'subject' => 'test',
		];
		$topicOptions = [
			'id' => 1,
			'board' => 1,
			'mark_as_read' => true,
		];
		$posterOptions = [
			'id' => 1,
		];
		createPost($msgOptions, $topicOptions, $posterOptions);

		$request = $smcFunc['db_query']('', '
			SELECT id_msg
			FROM {db_prefix}log_topics
			WHERE id_topic = 1
				AND id_member = 1');
		[$actual] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		removeMessage($msgOptions['id']);
		$this->assertEquals($msgOptions['id'], $actual);

		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		$_REQUEST['boards'] = 1;
		UnreadTopics();
		$topics = $context['topics'];
		unset($_REQUEST['boards'], $context['topics']);

		$this->assertIsArray($topics);
		$this->assertEmpty($topics);
	}

	public function testNewPostNotMarkedRead(): void
	{
		global $board, $context, $smcFunc, $topic;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_topics
			WHERE id_topic = 1');
		$msgOptions = [
			'body' => 'Mark read test',
			'id' => 0,
			'subject' => 'test',
		];
		$topicOptions = [
			'id' => 1,
			'board' => 1,
			'mark_as_read' => false,
		];
		$posterOptions = [
			'id' => 1,
		];
		createPost($msgOptions, $topicOptions, $posterOptions);

		$request = $smcFunc['db_query']('', '
			SELECT id_msg
			FROM {db_prefix}log_topics
			WHERE id_topic = 1
				AND id_member = 1');
		[$actual] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		removeMessage($msgOptions['id']);
		$this->assertEquals(0, $actual);

		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		$_REQUEST['boards'] = 1;
		UnreadTopics();
		$topics = $context['topics'];
		unset($_REQUEST['boards'], $context['topics']);

		$this->assertCount(1, $topics);
		$this->assertStringContainsString('SMF', $topics[1]['last_post']['subject']);
	}

	public function testPageMarkedRead(): void
	{
		global $board, $context, $modSettings, $smcFunc, $topic;

		for ($i = 0; $i < 2; $i++)
		{
			$msgOptions = [
				'body' => 'Mark page read test',
				'id' => 0,
				'subject' => 'test',
			];
			$topicOptions = [
				'id' => 1,
				'board' => 1,
				'mark_as_read' => false,
			];
			$posterOptions = [
				'id' => 1,
			];
			createPost($msgOptions, $topicOptions, $posterOptions);

			$msgs[$i] = $msgOptions['id'];
		}

		$topic = 1;
		loadBoard();

		$modSettings['defaultMaxMessages'] = 2;
		Display();
		unset($GLOBALS['topic']);

		$request = $smcFunc['db_query']('', '
			SELECT id_msg
			FROM {db_prefix}log_topics
			WHERE id_topic = 1
				AND id_member = 1');
		[$actual] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		$_REQUEST['boards'] = 1;
		UnreadTopics();
		$topics = $context['topics'];
		unset($_REQUEST['boards'], $context['topics']);

		foreach ($msgs as $msg)
			removeMessage($msg);
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_topics
			WHERE id_topic = 1');
		$this->assertEquals($msgs[0], $actual);

		$this->assertCount(1, $topics);
		$this->assertEquals('test', $topics[1]['last_post']['subject']);
	}

	public function testTopicNotify()
	{
		global $board, $context, $smcFunc, $topic;

		$_GET['mode'] = '0';
		$_GET['xml'] = '1';
		$topic = 1;
		TopicNotify();
		unset($GLOBALS['topic'], $_GET['xml'], $_GET['mode']);

		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		$board = 1;
		UnreadTopics();
		$this->assertIsArray($context['topics']);
		$this->assertEmpty($context['topics']);
		unset($GLOBALS['board'], $context['topics']);

		$_GET['mode'] = '1';
		$_GET['xml'] = '1';
		$GLOBALS['topic'] = 1;
		TopicNotify();
		unset($GLOBALS['topic'], $_GET['xml'], $_GET['mode']);

		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		$_REQUEST['boards'] = 1;
		UnreadTopics();
		$this->assertCount(1, $context['topics']);
		$this->assertStringContainsString('SMF', $context['topics'][1]['last_post']['subject']);
		unset($_REQUEST['boards'], $context['topics']);
	}

	public function testUnreadTopics(): void
	{
		global $context;

		$_REQUEST['action'] = 'unread';
		$_REQUEST['start'] = '0';
		unset($_REQUEST['c']);
		UnreadTopics();
		$this->assertCount(11, $context['topics']);
		$this->assertStringContainsString('SMF', $context['topics'][1]['last_post']['subject']);
		unset($_REQUEST['action'], $_REQUEST['start'], $context['topics'][1]);
		$this->assertCount(10, $context['topics']);

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
		unset($context['topics']);
	}
}