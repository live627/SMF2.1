<?php

declare(strict_types=1);

namespace SMF\Tests;

require_once __DIR__ . '/IntegrationTestCase.php';

/**
 * Tests for posting through Sources/Post.php: replying to a topic with Post2()
 * and checking what Display() then shows, including how the post's text is
 * escaped when it is rendered.
 *
 * Needs the populated test install: topic 1 ("Welcome to SMF!", started by
 * Simple Machines in board 1) and member 1, named "test", as the current user.
 *
 * Every test runs in a transaction (see IntegrationTestCase), so the posts,
 * view counts and flood-control rows it writes are rolled back. Each reply is
 * also removed with removeMessage(), which puts SMF's in-memory statistics back;
 * a rollback cannot do that.
 */
class PostTest extends IntegrationTestCase
{
	/** The database changes each test makes are rolled back. */
	protected bool $use_transaction = true;

	private array $backup = [];

	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Post.php';
		require_once __DIR__ . '/../Sources/Display.php';
		require_once __DIR__ . '/../Sources/RemoveTopic.php';
	}

	public function setUp(): void
	{
		global $context, $smcFunc;

		parent::setUp();

		$this->backup = [
			'request' => $_REQUEST ?? [],
			'post' => $_POST ?? [],
			'last_read_topic' => $_SESSION['last_read_topic'] ?? null,
		];
		$_REQUEST = $this->backup['request'];
		$_POST = $this->backup['post'];
		unset($context['post_error'], $_SESSION['last_read_topic']);

		// Start from a topic nobody has viewed and from no flood-control history,
		// so a test can post and count views. The transaction undoes this.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}topics
			SET num_views = 0
			WHERE id_topic = 1');
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_topics
			WHERE id_topic = 1');
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_floodcontrol');
	}

	public function tearDown(): void
	{
		global $context;

		$_REQUEST = $this->backup['request'];
		$_POST = $this->backup['post'];

		if ($this->backup['last_read_topic'] === null)
			unset($_SESSION['last_read_topic']);
		else
			$_SESSION['last_read_topic'] = $this->backup['last_read_topic'];

		unset($GLOBALS['board'], $GLOBALS['topic'], $context['post_error'], $context['get_message']);

		// Last, so the transaction is rolled back after everything above.
		parent::tearDown();
	}

	/**
	 * Posts a reply to topic 1 through Post2(), renders the topic with Display()
	 * and removes the new message again. The rendered topic stays in $context.
	 */
	private function postReply(string $subject, string $message): void
	{
		global $board, $context, $topic;

		$board = 1;
		$topic = 1;
		loadBoard();
		$_REQUEST['start'] = '0';
		$_POST['subject'] = $subject;
		$_POST['message'] = $message;
		$_POST[$context['session_var']] = $context['session_id'];

		Post2();
		Display();

		// Report why the post was rejected, if it was.
		$this->assertFalse(
			isset($context['post_error']),
			'Post2() rejected the post: ' . print_r($context['post_error'] ?? [], true)
		);
		$this->assertNotEquals(1, $context['topic_last_message']);

		removeMessage($context['topic_last_message']);
	}

	/**
	 * @return string The rendered body of the last message Display() showed
	 */
	private function lastDisplayedBody(): string
	{
		global $context;

		$body = null;
		while (($displayed = $context['get_message']()) !== false)
			$body = $displayed['body'];

		$this->assertNotNull($body);

		return $body;
	}

	public function testMakeReplyPost(): void
	{
		global $context;

		$this->postReply('Welcome', 'Thanks');

		$this->assertEquals(1, $context['num_replies']);
		$this->assertEquals(1, $context['real_num_replies']);
		$this->assertEquals(1, $context['num_views']);
		$this->assertEquals(1, $context['topic_first_message']);
		$this->assertFalse($context['user']['started']);
		$this->assertEquals(0, $context['topic_starter_id']);
		$this->assertEquals('Simple Machines', $context['topic_poster_name']);
		$this->assertEquals('Welcome to SMF!', $context['subject']);

		$message = $context['get_message']();
		$this->assertIsArray($message);
		$this->assertEquals(1, $message['id']);
		$this->assertEquals('Welcome to Simple Machines Forum!<br><br>We hope you enjoy using your forum.&nbsp; If you have any problems, please feel free to <a href="https://www.simplemachines.org/community/index.php" class="bbc_link" target="_blank" rel="noopener">ask us for assistance</a>.<br><br>Thanks!<br>Simple Machines', $message['body']);
		$this->assertEquals('Simple Machines', $message['member']['name']);

		$message = $context['get_message']();
		$this->assertIsArray($message);
		$this->assertEquals('Thanks', $message['body']);
		$this->assertEquals('test', $message['member']['name']);

		$this->assertFalse($context['get_message']());
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
		$this->postReply('Integration test', $message);
		$body = $this->lastDisplayedBody();

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

		$this->postReply(
			'Integration test',
			"Word said {$ldquo}hello{$rdquo} and it{$rsquo}s fine "
			. "[img alt={$ldquo} onerror={$ldquo}alert(1)]http://example.com/image.png[/img]"
		);
		$body = $this->lastDisplayedBody();

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