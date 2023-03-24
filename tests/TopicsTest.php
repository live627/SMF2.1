<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

final class TopicsTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Subs-Post.php';
	}

	public function testApprove(): void
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT id_topic, id_board
			FROM {db_prefix}topics
			ORDER BY id_topic DESC'
		);
		[$id_topic, $id_board] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		approveTopics($id_topic);
		$request = $smcFunc['db_query']('', '
			SELECT num_topics, unapproved_topics, num_posts, unapproved_posts
			FROM {db_prefix}boards
			WHERE id_board = {int:id_board}',
			[
				'id_board' => $id_board
			]
		);
		[$num_topics, $unapproved_topics, $num_posts, $unapproved_posts] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$this->assertEquals(1, $num_topics);
		$this->assertEquals(0, $unapproved_topics);
		$this->assertEquals(2, $num_posts);
		$this->assertEquals(0, $unapproved_posts);

		approveTopics($id_topic, false);
		$request = $smcFunc['db_query']('', '
			SELECT num_topics, unapproved_topics, num_posts, unapproved_posts
			FROM {db_prefix}boards
			WHERE id_board = {int:id_board}',
			[
				'id_board' => $id_board
			]
		);
		[$num_topics, $unapproved_topics, $num_posts, $unapproved_posts] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$this->assertEquals(0, $num_topics);
		$this->assertEquals(1, $unapproved_topics);
		$this->assertEquals(1, $num_posts);
		$this->assertEquals(1, $unapproved_posts);

		approveTopics($id_topic);
		$request = $smcFunc['db_query']('', '
			SELECT num_topics, unapproved_topics, num_posts, unapproved_posts
			FROM {db_prefix}boards
			WHERE id_board = {int:id_board}',
			[
				'id_board' => $id_board
			]
		);
		[$num_topics, $unapproved_topics, $num_posts, $unapproved_posts] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$this->assertEquals(1, $num_topics);
		$this->assertEquals(0, $unapproved_topics);
		$this->assertEquals(2, $num_posts);
		$this->assertEquals(0, $unapproved_posts);
	}
}
