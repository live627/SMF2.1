<?php

declare(strict_types=1);

namespace SMF\Tests;

require_once __DIR__ . '/IntegrationTestCase.php';

/**
 * Tests for Sources/Recent.php: the recent posts and unread topics pages (and
 * the board filtering behind them), and the ways a topic or board gets marked
 * as read (marking by hand, replying, viewing a page, and turning notifications
 * on and off).
 *
 * Needs the populated test install: SMF's Welcome topic (the one unread topic
 * with "SMF" in its subject) plus ten generated topics in category 1, every one
 * of them written as "Board n - Topic n - Messsage n", and member 1 as the
 * current user.
 *
 * Every test runs in a transaction (see IntegrationTestCase), so what it writes to
 * the database is rolled back. The request, board, topic and settings globals,
 * which a rollback does not touch, are put back by setUp() and tearDown(), and
 * tests that create posts remove them again so SMF's in-memory statistics go back
 * too. Tests can therefore run in any order.
 */
class RecentTest extends IntegrationTestCase
{
	/** The database changes each test makes are rolled back. */
	protected bool $use_transaction = true;

	/** How the populate step words each post (note the spelling of "Messsage"). */
	private const FIXTURE_MESSAGE = '/Board ([0-9]+) - Topic ([0-9]+) - Messsage ([0-9]+)/';

	/** Settings that tests change. */
	private const SETTINGS = ['preview_characters', 'defaultMaxMessages'];

	private array $backup = [];

	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Recent.php';
		require_once __DIR__ . '/../Sources/Subs-Boards.php';
		require_once __DIR__ . '/../Sources/Notify.php';
		require_once __DIR__ . '/../Sources/Subs-Post.php';
		require_once __DIR__ . '/../Sources/Display.php';
		require_once __DIR__ . '/../Sources/RemoveTopic.php';
	}

	public function setUp(): void
	{
		global $modSettings;

		parent::setUp();

		// The bootstrap may not have created these, and reading an undefined
		// superglobal is an error under PHPUnit, so default them to empty arrays.
		$this->backup = [
			'request' => $_REQUEST ?? [],
			'get' => $_GET ?? [],
			'settings' => array_intersect_key($modSettings, array_flip(self::SETTINGS)),
		];
		$_REQUEST = $this->backup['request'];
		$_GET = $this->backup['get'];

		// Start from "nothing has been read", whatever other tests left behind.
		$this->resetReadState();
	}

	public function tearDown(): void
	{
		global $context, $modSettings;

		$_REQUEST = $this->backup['request'];
		$_GET = $this->backup['get'];
		$modSettings = $this->backup['settings'] + array_diff_key($modSettings, array_flip(self::SETTINGS));

		unset($GLOBALS['board'], $GLOBALS['topic'], $context['topics'], $context['posts']);

		// Last, so the transaction is rolled back after everything above.
		parent::tearDown();
	}

	// ------------------------------------------------------------------
	// Helpers
	// ------------------------------------------------------------------

	/**
	 * @return int[] [board, topic, message] ids from a fixture post's text
	 */
	private function parseFixture(string $text): array
	{
		$this->assertSame(1, preg_match(self::FIXTURE_MESSAGE, $text, $found), 'Not a fixture post: ' . $text);

		return [(int) $found[1], (int) $found[2], (int) $found[3]];
	}

	private function recentPosts(array $request = [], ?int $board = null): array
	{
		global $context;

		$_REQUEST = array_merge($_REQUEST, ['start' => '0'], $request);
		if ($board !== null)
			$GLOBALS['board'] = $board;

		RecentPosts();

		return $context['posts'];
	}

	private function unreadTopics(array $request = [], ?int $board = null): array
	{
		global $context;

		$_REQUEST = array_merge($_REQUEST, ['action' => 'unread', 'start' => '0'], $request);
		if ($board !== null)
			$GLOBALS['board'] = $board;

		UnreadTopics();

		$topics = $context['topics'];
		unset($context['topics'], $GLOBALS['board']);

		return $topics;
	}

	private function markRead(array $request, ?int $topic = null): void
	{
		$_REQUEST = array_merge($_REQUEST, ['start' => '0'], $request);
		if ($topic !== null)
			$GLOBALS['topic'] = $topic;

		MarkRead();

		unset($GLOBALS['topic']);
	}

	private function topicNotify(string $mode): void
	{
		global $context;

		$_GET = array_merge($_GET, ['mode' => $mode, 'xml' => '1']);

		// Only needed if TopicNotify() checks the session (it does not for logged-in members here).
		if (!empty($context['session_var']))
			$_GET[$context['session_var']] = $context['session_id'];

		$GLOBALS['topic'] = 1;

		TopicNotify();

		unset($GLOBALS['topic']);
	}

	/**
	 * Posts a reply to topic 1 as member 1 and returns its id.
	 */
	private function createReply(string $body, bool $mark_as_read): int
	{
		$msg_options = ['body' => $body, 'id' => 0, 'subject' => 'test'];
		$topic_options = ['id' => 1, 'board' => 1, 'mark_as_read' => $mark_as_read];
		$poster_options = ['id' => 1];

		createPost($msg_options, $topic_options, $poster_options);

		return (int) $msg_options['id'];
	}

	/**
	 * The id_msg member 1 has read up to, or null if there is no row.
	 *
	 * @param string $table log_topics (topic 1) or log_mark_read (board 1)
	 */
	private function readMarker(string $table): ?string
	{
		global $smcFunc;

		$column = $table === 'log_topics' ? 'id_topic' : 'id_board';
		$result = $smcFunc['db_query']('', '
			SELECT id_msg
			FROM {db_prefix}' . $table . '
			WHERE ' . $column . ' = 1
				AND id_member = 1');
		$row = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		return $row ? $row[0] : null;
	}

	/**
	 * @return string|null The unwatched flag member 1 has on topic 1, null if no row
	 */
	private function topicUnwatched(): ?string
	{
		global $smcFunc;

		$result = $smcFunc['db_query']('', '
			SELECT unwatched
			FROM {db_prefix}log_topics
			WHERE id_topic = 1
				AND id_member = 1');
		$row = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		return $row ? $row[0] : null;
	}

	/**
	 * Forget what member 1 has read, so every test starts with all topics unread.
	 * Rolled back with the rest of the test, so other tests' read state is untouched.
	 */
	private function resetReadState(): void
	{
		global $smcFunc;

		foreach (['log_topics', 'log_mark_read'] as $table)
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}' . $table . '
				WHERE id_member = 1');
	}

	/**
	 * Runs getBoardParams() and returns the board ids it found.
	 *
	 * @return int[]
	 */
	private function boardParams(array $request = [], ?int $board = null): array
	{
		$_REQUEST = array_merge($_REQUEST, $request);
		if ($board !== null)
			$GLOBALS['board'] = $board;

		return array_map('intval', getBoardParams());
	}

	private function assertOnlyWelcomeUnread(array $topics): void
	{
		$this->assertCount(1, $topics);
		$this->assertStringContainsString('SMF', $topics[1]['last_post']['subject']);
	}

	// ------------------------------------------------------------------
	// Recent posts
	// ------------------------------------------------------------------

	public function testGetLastPost(): void
	{
		$this->assertStringContainsString('Automated Test', getLastPost()['subject']);
	}

	public function testRecentPostsInBoard(): void
	{
		$this->assertCount(1, $this->recentPosts([], 1));
	}

	public function testRecentPostsInCat(): void
	{
		$this->assertCount(10, $this->recentPosts(['c' => 1]));
	}

	public function testRecentPosts(): void
	{
		$posts = $this->recentPosts();
		$this->assertCount(10, $posts);

		foreach ($posts as $post)
		{
			[$board, $topic, $msg] = $this->parseFixture($post['message']);

			$this->assertEquals($msg, $post['id']);
			$this->assertEquals($topic, $post['topic']);
			$this->assertEquals($board, $post['board']['id']);
			$this->assertEquals(1, $post['category']['id']);
			$this->assertStringContainsString('Category', $post['category']['name']);
			$this->assertStringContainsString('Test', $post['subject']);
			$this->assertStringContainsString('Test', $post['shorten_subject']);
		}
	}

	// ------------------------------------------------------------------
	// Unread topics
	// ------------------------------------------------------------------

	public function testUnreadTopicsInCat(): void
	{
		global $modSettings;

		$modSettings['preview_characters'] = 1;
		$topics = $this->unreadTopics(['c' => '1']);

		$this->assertCount(11, $topics);
		$this->assertStringContainsString('SMF', $topics[1]['last_post']['subject']);
	}

	public function testUnreadTopicsInBoard(): void
	{
		global $modSettings;

		$modSettings['preview_characters'] = 1;

		$this->assertOnlyWelcomeUnread($this->unreadTopics([], 1));
	}

	public function testUnreadTopics(): void
	{
		$topics = $this->unreadTopics();

		$this->assertCount(11, $topics);
		$this->assertStringContainsString('SMF', $topics[1]['last_post']['subject']);

		// The rest are the generated fixture topics.
		unset($topics[1]);
		$this->assertCount(10, $topics);

		foreach ($topics as $topic)
		{
			[$board, $topic_id, $msg] = $this->parseFixture($topic['last_post']['preview']);

			$this->assertEquals($msg, $topic['last_post']['id']);
			$this->assertStringContainsString('?topic=' . $topic_id, $topic['href']);
			$this->assertStringContainsString('?topic=' . $topic_id, $topic['last_post']['href']);
			$this->assertStringContainsString('?board=' . $board, $topic['board']['href']);
			$this->assertEquals($board, $topic['board']['id']);
			$this->assertEquals(1, $topic['replies']);
			$this->assertEquals(0, $topic['views']);
			$this->assertStringContainsString('Test', $topic['last_post']['subject']);
		}
	}

	// ------------------------------------------------------------------
	// Which boards a page covers
	// ------------------------------------------------------------------

	public function testBoardParamsWithoutLimits(): void
	{
		global $context;

		$boards = $this->boardParams();

		$this->assertTrue($context['no_board_limits']);
		$this->assertContains(1, $boards);
	}

	public function testBoardParamsInBoard(): void
	{
		global $context;

		$this->assertSame([1], $this->boardParams([], 1));
		$this->assertFalse($context['no_board_limits']);
	}

	public function testBoardParamsInBoardList(): void
	{
		$this->assertSame([1], $this->boardParams(['boards' => '1']));
	}

	public function testBoardParamsInCat(): void
	{
		$all = $this->boardParams();
		$in_cat = $this->boardParams(['c' => '1']);

		$this->assertContains(1, $in_cat);
		$this->assertSame([], array_diff($in_cat, $all), 'A category cannot have boards the user cannot see');
	}

	public function testBoardParamsWithChildren(): void
	{
		$this->assertContains(1, $this->boardParams(['children' => '1'], 1));
	}

	// ------------------------------------------------------------------
	// Marking things read
	// ------------------------------------------------------------------

	public function testMarkBoardRead(): void
	{
		global $modSettings;

		markBoardsRead([1]);
		$marked_up_to = $this->readMarker('log_mark_read');
		$unread = $this->unreadTopics([], 1);

		// Resetting the board makes everything in it unread again.
		markBoardsRead([1], true);
		$marked_after_reset = $this->readMarker('log_mark_read');
		$unread_after_reset = $this->unreadTopics(['boards' => 1]);

		$this->assertEquals($modSettings['maxMsgID'], $marked_up_to);
		$this->assertSame([], $unread);
		$this->assertNull($marked_after_reset);
		$this->assertOnlyWelcomeUnread($unread_after_reset);
	}

	public function testMarkRead(): void
	{
		$this->markRead(['sa' => 'unreadreplies', 'topics' => '1']);
		$unread = $this->unreadTopics([], 1);

		$this->markRead(['sa' => 'topic'], 1);
		$unread_after_topic = $this->unreadTopics(['boards' => 1]);

		$this->assertSame([], $unread);
		$this->assertOnlyWelcomeUnread($unread_after_topic);
	}

	/**
	 * Fails until MarkRead() is fixed: its unreadreplies branch looks the topic
	 * up with $logged_topics[$topic] (the global) instead of $logged_topics[$id_topic],
	 * so the unwatched flag is always reset to 0.
	 */
	public function testMarkReadKeepsTopicUnwatched(): void
	{
		$this->topicNotify('0');
		$this->assertEquals(1, $this->topicUnwatched(), 'Turning notifications off should unwatch the topic');

		$this->markRead(['sa' => 'unreadreplies', 'topics' => '1']);

		$this->assertEquals(1, $this->topicUnwatched(), 'Marking the topic read should not make it watched again');
	}

	public function testNewPostMarkedRead(): void
	{
		$msg = $this->createReply('Mark read test', true);

		try
		{
			$marker = $this->readMarker('log_topics');
		}
		finally
		{
			removeMessage($msg);
		}

		$this->assertEquals($msg, $marker);
		$this->assertSame([], $this->unreadTopics(['boards' => 1]));
	}

	public function testNewPostNotMarkedRead(): void
	{
		$msg = $this->createReply('Mark read test', false);

		try
		{
			$marker = $this->readMarker('log_topics');
		}
		finally
		{
			removeMessage($msg);
		}

		$this->assertEquals(0, $marker);
		$this->assertOnlyWelcomeUnread($this->unreadTopics(['boards' => 1]));
	}

	public function testPageMarkedRead(): void
	{
		global $modSettings;

		$msgs = [];

		$msgs[] = $this->createReply('Mark page read test', false);
		$msgs[] = $this->createReply('Mark page read test', false);

		// Viewing the first page of two messages marks only that page read.
		$GLOBALS['topic'] = 1;
		loadBoard();
		$modSettings['defaultMaxMessages'] = 2;
		Display();
		unset($GLOBALS['topic']);

		$marker = $this->readMarker('log_topics');
		$unread = $this->unreadTopics(['boards' => 1]);

		$this->assertEquals($msgs[0], $marker);
		$this->assertCount(1, $unread);
		$this->assertEquals('test', $unread[1]['last_post']['subject']);
	}

	public function testTopicNotify(): void
	{
		$this->topicNotify('0');
		$this->assertSame([], $this->unreadTopics([], 1));

		$this->topicNotify('1');
		$this->assertOnlyWelcomeUnread($this->unreadTopics(['boards' => 1]));
	}
}