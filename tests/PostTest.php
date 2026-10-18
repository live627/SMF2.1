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
	 * Posts a reply to topic 1 through Post2(), renders the topic with
	 * Display(), removes the new message again and returns its rendered body.
	 */
	private function postReplyAndGetBody(string $message): string
	{
		global $board, $context, $smcFunc, $topic;

		unset($context['post_error']);
		$board = 1;
		$topic = 1;
		loadBoard();
		$_REQUEST['start'] = '0';
		$_POST['subject'] = 'Integration test';
		$_POST['message'] = $message;
		$_POST[$context['session_var']] = $context['session_id'];

		// If Post2() finds an error it falls back to Post(), which dies when
		// there are no cookies. PHPUnit has none, so fake one to let the real
		// error reach the assertion below instead of a cookie error.
		$fake_cookie = empty($_COOKIE);
		if ($fake_cookie)
			$_COOKIE['smf_phpunit'] = '1';

		// Several posts in a row would otherwise trip flood protection.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_floodcontrol');

		Post2();
		Display();

		// Leave the topic the way testMakeReplyPost expects to find it.
		unset($_SESSION['last_read_topic']);
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}topics
			SET num_views = 0
			WHERE id_topic = 1');
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_topics
			WHERE id_topic = 1');

		if ($fake_cookie)
			unset($_COOKIE['smf_phpunit']);

		// Report why the post was rejected, if it was.
		$this->assertFalse(
			isset($context['post_error']),
			'Post2() rejected the post: ' . print_r($context['post_error'] ?? [], true)
		);
		$this->assertNotEquals(1, $context['topic_last_message']);
		removeMessage($context['topic_last_message']);

		// The new post is the last one displayed.
		$body = null;
		while (($displayed = $context['get_message']()) !== false)
			$body = $displayed['body'];

		unset($GLOBALS['board'], $GLOBALS['topic'], $_POST);

		$this->assertNotNull($body);

		return $body;
	}

	/**
	 * @return string[][]
	 */
	public function imgAltEncodingProvider(): array
	{
		return [
			'ampersand' => [
				'[img alt=Tom & Jerry]http://example.com/image.png[/img]',
				'alt="Tom &amp; Jerry"',
			],
			'double quotes' => [
				'[img alt=say "hi"]http://example.com/image.png[/img]',
				'alt="say &quot;hi&quot;"',
			],
		];
	}

	/**
	 * Text is escaped once when the post is saved. Rendering it into an
	 * attribute must not escape it a second time (no "&amp;amp;").
	 *
	 * @dataProvider imgAltEncodingProvider
	 */
	public function testPostedImgAltIsEncodedOnce(string $message, string $expected_attribute): void
	{
		$body = $this->postReplyAndGetBody($message);

		$this->assertStringContainsString($expected_attribute, $body);
		$this->assertStringNotContainsString('&amp;amp;', $body);
		$this->assertStringNotContainsString('&amp;quot;', $body);
	}

	/**
	 * Smart quotes pasted from Microsoft Word survive htmlspecialchars() when
	 * saving and are only converted while rendering. They must come out as
	 * entities, and must not be able to break out of an attribute.
	 */
	public function testPostedMsWordQuotesAreSafe(): void
	{
		$ldquo = "\xE2\x80\x9C";
		$rdquo = "\xE2\x80\x9D";
		$rsquo = "\xE2\x80\x99";

		$body = $this->postReplyAndGetBody(
			"Word said {$ldquo}hello{$rdquo} and it{$rsquo}s fine "
			. "[img alt={$ldquo} onerror={$ldquo}alert(1)]http://example.com/image.png[/img]"
		);

		// Plain text quotes become entities, not raw quote characters.
		$this->assertStringContainsString('Word said &quot;hello&quot; and it&#039;s fine', $body);

		// No generated tag may contain an injected event handler.
		$this->assertSame(
			0,
			preg_match('~<[^<>]*\son\w+\s*=\s*["\']~i', $body),
			'Attribute breakout found in: ' . $body
		);
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
