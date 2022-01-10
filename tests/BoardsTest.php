<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class BoardsTest extends TestCase
{
	private $options = [];

	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/BoardIndex.php';
		require_once __DIR__ . '/../Sources/Subs-BoardIndex.php';
		require_once __DIR__ . '/../Sources/Subs-Boards.php';
	}

	public function setUp(): void
	{
		$this->options = [
			[
				'board_name' => 'Search 1',
				'move_to' => 'top',
				'target_category' => 1,
				'access_groups' => [1],
			],
			[
				'board_name' => 'Search 2',
				'move_to' => 'child',
				'target_board' => 1,
				'target_category' => 1,
				'access_groups' => [1],
			],
			[
				'board_name' => 'Search 3',
				'redirect' => 'test',
				'move_to' => 'bottom',
				'target_category' => 1,
				'access_groups' => [1],
				'deny_groups' => [0],
			],
			[
				'board_name' => 'Search 4',
				'moderator_string' => 'test',
				'moderator_group_string' => 'Moderator',
				'move_to' => 'before',
				'target_board' => 1,
				'target_category' => 1,
				'access_groups' => [1],
				'num_posts' => 0,
			],
		];
	}

	public function testAddBoards(): void
	{
		global $boards, $boardsTest;

		getBoardTree();
		$countBefore = count($boards);

		$boardsTest = [];

		foreach ($this->options as $options)
			$boardsTest[] = (int) createBoard($options);

		// createBoard() returns 0 when the insert fails.
		foreach ($boardsTest as $id)
			$this->assertGreaterThan(0, $id, 'createBoard() returned 0.');

		$this->assertCount(4, array_unique($boardsTest), 'Each board needs its own id.');

		getBoardTree();

		// Relative to the fixture rather than a hardcoded total.
		$this->assertCount($countBefore + 4, $boards);

		// Every board should have the basics from its options.
		foreach ($boardsTest as $index => $id)
		{
			$this->assertArrayHasKey($id, $boards);
			$this->assertSame($id, (int) $boards[$id]['id']);
			$this->assertSame(1, (int) $boards[$id]['category']);
			$this->assertSame($this->options[$index]['board_name'], $boards[$id]['name']);
			$this->assertSame(['1'], $boards[$id]['member_groups']);
		}

		[$top, $child, $redirect, $before] = $boardsTest;

		// 'top': a root board that sorts ahead of the existing root board.
		$this->assertSame(0, (int) $boards[$top]['parent']);
		$this->assertSame(0, (int) $boards[$top]['level']);
		$this->assertLessThan($boards[1]['order'], $boards[$top]['order']);

		// 'child': hangs off board 1, one level deeper.
		$this->assertSame(1, (int) $boards[$child]['parent']);
		$this->assertSame((int) $boards[1]['level'] + 1, (int) $boards[$child]['level']);
		$this->assertArrayHasKey($child, $boards[1]['tree']['children']);

		// 'bottom': a root board that sorts after every other root board.
		$this->assertSame('test', $boards[$redirect]['redirect']);
		$this->assertSame(['0'], $boards[$redirect]['deny_groups']);
		$this->assertSame(0, (int) $boards[$redirect]['parent']);
		$this->assertSame(0, (int) $boards[$redirect]['level']);

		foreach ($boards as $other_id => $other)
			if ($other_id !== $redirect && (int) $other['category'] === 1 && (int) $other['level'] === 0)
				$this->assertLessThan($boards[$redirect]['order'], $other['order'], 'Board ' . $other_id . ' should sort before the bottom board.');

		// 'before': same parent and level as board 1, directly ahead of it.
		$this->assertSame((int) $boards[1]['parent'], (int) $boards[$before]['parent']);
		$this->assertSame((int) $boards[1]['level'], (int) $boards[$before]['level']);
		$this->assertLessThan($boards[1]['order'], $boards[$before]['order']);
		$this->assertSame(0, (int) $boards[$before]['posts']);

		// reorderBoards() should leave every board with its own position.
		$orders = array_map('intval', array_column($boards, 'order'));
		$this->assertCount(count($boards), array_unique($orders), 'board_order values must be unique.');
	}

	/**
	 * @dataProvider invalidCreateOptionsProvider
	 */
	public function testCreateBoardInvalidOptions(array $options, string $expectedMessage): void
	{
		global $boards;

		getBoardTree();
		$countBefore = count($boards);

		$thrown = null;

		try
		{
			createBoard($options);
		}
		catch (\Exception $e)
		{
			$thrown = $e;
		}

		$this->assertNotNull($thrown, 'createBoard() should have thrown.');
		$this->assertSame($expectedMessage, $thrown->getMessage());

		// Nothing should have been inserted.
		getBoardTree();
		$this->assertCount($countBefore, $boards);
	}

	public static function invalidCreateOptionsProvider(): array
	{
		return [
			'no name' => [
				['move_to' => 'top', 'target_category' => 1],
				'create_board_missing_options',
			],
			'blank name' => [
				['board_name' => '   ', 'move_to' => 'top', 'target_category' => 1],
				'create_board_missing_options',
			],
			'no move_to' => [
				['board_name' => 'Invalid', 'target_category' => 1],
				'create_board_missing_options',
			],
			'no target category' => [
				['board_name' => 'Invalid', 'move_to' => 'top'],
				'create_board_missing_options',
			],
			'child without target board' => [
				['board_name' => 'Invalid', 'move_to' => 'child', 'target_category' => 1],
				'move_board_no_target',
			],
			'before without target board' => [
				['board_name' => 'Invalid', 'move_to' => 'before', 'target_category' => 1],
				'move_board_no_target',
			],
			'after without target board' => [
				['board_name' => 'Invalid', 'move_to' => 'after', 'target_category' => 1],
				'move_board_no_target',
			],
		];
	}

	public function testBoardIndexController(): void
	{
		global $boardsTest, $db_show_debug, $context, $modSettings, $settings;

		$settings['number_recent_posts'] = 2;
		$modSettings['cal_enabled'] = true;
		$settings['show_group_key'] = true;
		$settings['show_newsfader'] = true;

		BoardIndex();
		$this->assertCount(1, $context['categories']);

		foreach ($context['categories'] as $category)
		{
			$this->assertCount(14, $category['boards']);

			foreach ($category['boards'] as $board)
			{
				$this->assertIsArray($board['children']);
				$this->assertIsArray($board['link_children']);
				$this->assertIsArray($board['moderators']);
				$this->assertIsArray($board['link_moderators']);
				$this->assertIsArray($board['link_moderator_groups']);
			}
		}
		$this->assertCount(1, $context['categories'][1]['boards'][1]['children']);
		$this->assertCount(1, $context['categories'][1]['boards'][1]['link_children']);
		$this->assertCount(1, $context['categories'][1]['boards'][$boardsTest[3]]['moderators']);
		$this->assertCount(1, $context['categories'][1]['boards'][$boardsTest[3]]['link_moderators']);
		//$this->assertCount(1, $context['categories'][1]['boards'][$boardsTest[3]]['link_moderator_groups']);
		$this->assertEquals('test', $context['categories'][1]['boards'][$boardsTest[3]]['moderators'][0]['name']);

		$this->assertIsArray($context['latest_posts']);
		$this->assertCount(2, $context['latest_posts']);
		$this->assertStringContainsString('Automated Test', $context['latest_posts'][0]['subject']);
		$this->assertStringContainsString('Automated Test', $context['latest_post']['subject']);
		$this->assertContains(['tpl' => 'recent', 'txt' => 'recent_posts'], $context['info_center']);

		$this->assertContains(['tpl' => 'stats', 'txt' => 'forum_stats'], $context['info_center']);

		$this->assertFalse($context['show_buddies']);
		$this->assertTrue($context['show_who']);
		$this->assertContains(['tpl' => 'online', 'txt' => 'online_users'], $context['info_center']);

		$this->assertEquals('My Community - Index', $context['page_title']);
		$this->assertArrayHasKey('markread', $context['mark_read_button']);

		$this->assertTrue($db_show_debug);
		$this->assertContains('integrate_mark_read_button', $context['debug']['hooks']);
	}

	public function testBoardIndex(): void
	{
		global $boardsTest;

		getBoardTree();
		$boardIndexOptions = [
			'include_categories' => true,
			'base_level' => 0,
			'parent_id' => 0,
			'set_latest_post' => true,
			'countChildPosts' => true,
		];
		$categories = getBoardIndex($boardIndexOptions);
		$this->assertCount(1, $categories);

		foreach ($categories as $category)
		{
			$this->assertCount(14, $category['boards']);

			foreach ($category['boards'] as $board)
			{
				$this->assertIsArray($board['children']);
				$this->assertIsArray($board['link_children']);
				$this->assertIsArray($board['moderators']);
				$this->assertIsArray($board['link_moderators']);
				$this->assertIsArray($board['link_moderator_groups']);
			}
		}
		$this->assertCount(1, $categories[1]['boards'][1]['children']);
		$this->assertCount(1, $categories[1]['boards'][1]['link_children']);
		$this->assertCount(1, $categories[1]['boards'][$boardsTest[3]]['moderators']);
		$this->assertCount(1, $categories[1]['boards'][$boardsTest[3]]['link_moderators']);
		//$this->assertCount(1, $categories[1]['boards'][$boardsTest[3]]['link_moderator_groups']);
		$this->assertEquals('test', $categories[1]['boards'][$boardsTest[3]]['moderators'][0]['name']);
	}

	public function testMoveChildError(): void
	{
		global $boards, $txt;

		getBoardTree();
		$keys = array_flip(['parent', 'level', 'order', 'category', 'name']);
		$snapshot = array_intersect_key($boards[1], $keys);

		$options = [
			'move_to' => 'test',
		];

		$thrown = null;

		try
		{
			modifyBoard(1, $options);
		}
		catch (\Exception $e)
		{
			$thrown = $e;
		}

		$this->assertNotNull($thrown, 'modifyBoard() should have thrown for an unknown move_to.');

		// An empty message means the language string was missing, so it must not be blank.
		$this->assertNotSame('', $thrown->getMessage());
		$this->assertStringContainsString('test', $thrown->getMessage());
		$this->assertSame(sprintf($txt['modify_board_incorrect_move_to'], 'test'), $thrown->getMessage());

		// The failed move must not have changed the board.
		getBoardTree();
		$this->assertSame($snapshot, array_intersect_key($boards[1], $keys));
	}

	public function testMarkBoardsRead(): void
	{
		global $boardsTest, $smcFunc;

		markBoardsRead($boardsTest);
		$result = $smcFunc['db_query'](
			'',
			'
			SELECT COUNT(id_board)
			FROM {db_prefix}log_boards
				WHERE id_board IN ({array_int:board_list})
					AND id_member = 1',
			[
				'board_list' => $boardsTest,
			]
		);
		[$actual] = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);
		$this->assertEquals(4, $actual);

		markBoardsRead($boardsTest, true);
		$result = $smcFunc['db_query'](
			'',
			'
			SELECT COUNT(id_board)
			FROM {db_prefix}log_boards
				WHERE id_board IN ({array_int:board_list})
					AND id_member = 1',
			[
				'board_list' => $boardsTest,
			]
		);
		[$actual] = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);
		$this->assertEquals(0, $actual);
	}

	public function testRemoveBoards(): void
	{
		global $boards, $boardsTest;

		deleteBoards($boardsTest);
		getBoardTree();

		foreach ($boardsTest as $board)
			$this->assertArrayNotHasKey($board, $boards);

		$this->assertCount(11, $boards);
	}
}