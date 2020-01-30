<?php

namespace PHPTDD;

class BoardsTest extends BaseTestCase
{
	private $options = array();

	protected function setUp()
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-BoardIndex.php');
		require_once($sourcedir . '/Subs-Boards.php');

		$this->options = array(
			array(
				'board_name' => 'Search 1',
				'move_to' => 'top',
				'target_category' => 1,
				'access_groups' => [-1],
			),
			array(
				'board_name' => 'Search 2',
				'move_to' => 'child',
				'target_board' => 1,
				'target_category' => 1,
				'access_groups' => [-1],
			),
			array(
				'board_name' => 'Search 3',
				'redirect' => 'test',
				'move_to' => 'bottom',
				'target_category' => 1,
				'access_groups' => [-1],
				'deny_groups' => [0],
			),
			array(
				'board_name' => 'Search 4',
				'moderator_string' => 'test',
				'moderator_group_string' => 'Moderator',
				'move_to' => 'before',
				'target_board' => 1,
				'target_category' => 1,
				'access_groups' => [-1],
				'num_posts' => 0,
			),
		);
	}

	public function testAddBoards()
	{
		global $boards, $boardsTest;

		$boardsTest = array();
		foreach ($this->options as $options)
			$boardsTest[] = createBoard($options);

		getBoardTree();
		foreach ($boardsTest as $board)
		{
			$this->assertArrayHasKey($board, $boards);
			$this->assertEquals($board, $boards[$board]['id']);
			$this->assertEquals(1, $boards[$board]['category']);
			$this->assertInternalType('array', $boards[$board]['member_groups']);
		}

		$this->assertCount(5, $boards);
	}

	public function testBoardIndex()
	{
		global $boardsTest;

		getBoardTree();
		$boardIndexOptions = array(
			'include_categories' => true,
			'base_level' => 0,
			'parent_id' => 0,
			'set_latest_post' => true,
			'countChildPosts' => true
		);
		$categories = getBoardIndex($boardIndexOptions);
		$this->assertCount(1, $categories);
		foreach ($categories as $category)
		{
			$this->assertCount(4, $category['boards']);

			foreach ($category['boards'] as $board)
			{
				$this->assertInternalType('array', $board['children']);
				$this->assertInternalType('array', $board['link_children']);
				$this->assertInternalType('array', $board['moderators']);
				$this->assertInternalType('array', $board['link_moderators']);
				$this->assertInternalType('array', $board['link_moderator_groups']);
			}
		}
		$this->assertCount(1, $categories[1]['boards'][1]['children']);
		$this->assertCount(1, $categories[1]['boards'][1]['link_children']);
		$this->assertCount(1, $categories[1]['boards'][$boardsTest[3]]['moderators']);
		$this->assertCount(1, $categories[1]['boards'][$boardsTest[3]]['link_moderators']);
		//$this->assertCount(1, $categories[1]['boards'][$boardsTest[3]]['link_moderator_groups']);
		$this->assertEquals('test', $categories[1]['boards'][$boardsTest[3]]['moderators'][0]['name']);
	}

	/**
	 * @expectedException PHPUnit\Framework\Error\Error
	 */
	public function testMoveChildError()
	{
		$options = array(
			'move_to' => 'test',
		);
		modifyBoard(1, $options);
	}

	public function testMarkBoardsRead()
	{
		global $boardsTest, $smcFunc, $user_info;

		$user_info['id'] = 1;
		markBoardsRead($boardsTest);
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(id_board)
			FROM {db_prefix}log_boards
				WHERE id_board IN ({array_int:board_list})
					AND id_member = 1',
				array(
					'board_list' => $boardsTest,
			)
		);
		list ($actual) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);
		$this->assertEquals(4, $actual);

		markBoardsRead($boardsTest, true);
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(id_board)
			FROM {db_prefix}log_boards
				WHERE id_board IN ({array_int:board_list})
					AND id_member = 1',
				array(
					'board_list' => $boardsTest,
			)
		);
		list ($actual) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);
		$this->assertEquals(0, $actual);

		unset($user_info);
	}

	public function testRemoveBoards()
	{
		global $boards, $boardsTest;

		deleteBoards($boardsTest);
		getBoardTree();

		foreach ($boardsTest as $board)
			$this->assertArrayNotHasKey($board, $boards);

		$this->assertCount(1, $boards);
	}
}
