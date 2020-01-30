<?php

namespace PHPTDD;

class BoardsTest extends BaseTestCase
{
	private $options = array();
	private $boards = array();

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
		);
	}

	public function testAddBoards()
	{
		global $cat_tree, $boards, $boardList, $smcFunc;

		foreach ($this->options as $options)
			$this->boards[] = createBoard($options);

		getBoardTree();

		foreach ($this->boards as $board)
		{
			$this->assertArrayHasKey($board, $boards);
			$this->assertEquals($board, $boards[$board]['id']);
			$this->assertEquals(1, $boards[$board]['category']);
			$this->assertInternalType('array', $boards[$board]['member_groups']);
		}

		$this->assertCount(4, $boards);
	}

	public function testBoardIndex()
	{
		global $boards;

		getBoardTree();
		$this->boards = array_diff(array_keys($boards), array(1));
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
			$this->assertCount(3, $category['boards']);

			foreach ($category['boards'] as $board)
			{
				$this->assertInternalType('array', $board['children']);
				$this->assertInternalType('array', $board['link_children']);
				$this->assertInternalType('array', $board['moderators']);
				$this->assertInternalType('array', $board['link_moderators']);
				$this->assertInternalType('array', $board['link_moderator_groups']);
			}
		}

	}

	public function testRemoveBoards()
	{
		global $boards;

		getBoardTree();
		$this->boards = array_diff(array_keys($boards), array(1));
		deleteBoards($this->boards);
		getBoardTree();

		foreach ($this->boards as $board)
			$this->assertArrayNotHasKey($board, $boards);

		$this->assertCount(1, $boards);
	}
}
