<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Error\Error as PHPUnitError;

final class BoardsTest extends TestCase
{
	private $options = [];

	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/BoardIndex.php';
		require_once __DIR__ . '/../Sources/Subs-BoardIndex.php';
		require_once __DIR__ . '/../Sources/Subs-Boards.php';
	}

	protected function setUp(): void
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

		$boardsTest = [];

		foreach ($this->options as $options)
			$boardsTest[] = createBoard($options);

		getBoardTree();

		foreach ($boardsTest as $board)
		{
			$this->assertArrayHasKey($board, $boards);
			$this->assertEquals($board, $boards[$board]['id']);
			$this->assertEquals(1, $boards[$board]['category']);
			$this->assertIsArray($boards[$board]['member_groups']);
		}

		$this->assertCount(15, $boards);
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

		$this->assertTrue($db_show_debug === true);
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
		$options = [
			'move_to' => 'test',
		];
		$this->expectException(PHPUnitError::class);
		modifyBoard(1, $options);
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
