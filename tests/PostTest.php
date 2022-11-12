<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Post.php';
		require_once __DIR__ . '/../Sources/Display.php';
		require_once __DIR__ . '/../Sources/RemoveTopic.php';
	}

	public function testMakeReplyPost()
	{
		global $board, $board_info, $context, $smcFunc, $topic;

		unset($context['post_error']);
		$board = 1;
		$topic = 1;
		loadBoard();
		$_REQUEST['start'] = '0';
		$_POST['subject'] = 'Welcome';
		$_POST['message'] = 'Thanks';
		$_POST[$context['session_var']] = $context['session_id'];

		Post2();
		Display();

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}topics
			SET num_views = 0
			WHERE id_topic = 1');
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_topics
			WHERE id_topic = 1');

		$this->assertNotEquals(1, $context['topic_last_message']);
		$this->assertFalse(isset($context['post_error']));
		removeMessage($context['topic_last_message']);

		$this->assertEquals(1, $context['num_replies']);
		$this->assertEquals(1, $context['real_num_replies']);
		$this->assertEquals(1, $context['num_views']);
		$this->assertEquals(1, $context['topic_first_message']);
		$this->assertFalse($context['user']['started']);
		$this->assertEquals(0, $context['topic_starter_id']);
		$this->assertEquals('Simple Machines', $context['topic_poster_name']);
		$this->assertEquals('Welcome to SMF!', $context['subject']);

		$message = $context['get_message']();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertEquals(1, $message['id']);
		$this->assertEquals('Welcome to Simple Machines Forum!<br><br>We hope you enjoy using your forum.&nbsp; If you have any problems, please feel free to <a href="https://www.simplemachines.org/community/index.php" class="bbc_link" target="_blank" rel="noopener">ask us for assistance</a>.<br><br>Thanks!<br>Simple Machines', $message['body']);
		$this->assertEquals('Simple Machines', $message['member']['name']);

		$message = $context['get_message']();
		$this->assertNotFalse($message);
		$this->assertIsArray($message);
		$this->assertEquals('Thanks', $message['body']);
		$this->assertEquals('test', $message['member']['name']);

		$message = $context['get_message']();
		$this->assertFalse($message);
		unset($GLOBALS['board'], $GLOBALS['topic'], $_POST);
	}

	/**
	 * Test making a new topic
	 */
	public function tesjtMakeNewTopic()
	{
		global $board, $board_info;

		$board = 1;
		loadBoard();
		$check = $board_info['num_topics'];
		$_POST['subject'] = 'Welcome to CI';
		$_POST['message'] = 'So you want to test on CI, fine, sure.';
		$_POST['email'] = 'a@a.com';
		$_POST['icon'] = 'thumbup';
		$_POST['additonal_items'] = 0;
		Post2();

		loadBoard();
		$this->assertEquals($check + 1, $board_info['num_topics']);
	}

	/**
	 * Test making a post
	 */
	public function tesstModifyPost()
	{
		global $context, $board, $topic, $modSettings;

		// Set up for modifying a post
		$board = 1;
		$topic = 2;
		loadBoard();
		$topic_info = getTopicInfo($topic, 'message');

		$_REQUEST['msg'] = $topic_info['id_last_msg'];
		$_POST['subject'] = $topic_info['subject'];
		$_POST['message'] = $topic_info['body'];
		$_POST['email'] = 'a@a.com';
		$_POST['icon'] = 'xx';
		$_POST['lock'] = 1;
		$_POST['additonal_items'] = 0;

		Post2();

		// Check
		$topic_info = getTopicInfo($topic);
		$this->assertEquals(1, $topic_info['locked']);
	}
}
