<?php

declare(strict_types=1);

namespace SMF\Tests;

require_once __DIR__ . '/IntegrationTestCase.php';

/**
 * Checks that IntegrationTestCase rolls back only when asked to.
 */
class IntegrationTestCaseTest extends IntegrationTestCase
{
	/** A member and topic that no fixture uses. */
	private const MEMBER = 9999999;
	private const TOPIC = 9999999;

	public function testNoTransactionByDefault(): void
	{
		$this->assertFalse($this->inTransaction());
	}

	public function testWritesAreVisibleInsideTheTransaction(): void
	{
		$this->startTransaction();

		$this->assertTrue($this->inTransaction());
		$this->assertFalse($this->rowExists());

		$this->insertRow();

		$this->assertTrue($this->rowExists());
	}

	/**
	 * @depends testWritesAreVisibleInsideTheTransaction
	 */
	public function testWritesWereRolledBack(): void
	{
		$this->assertFalse($this->inTransaction());
		$this->assertFalse($this->rowExists(), 'The row from the previous test should have been rolled back');
	}

	private function insertRow(): void
	{
		global $smcFunc;

		$smcFunc['db_insert']('insert',
			'{db_prefix}log_topics',
			['id_member' => 'int', 'id_topic' => 'int', 'id_msg' => 'int', 'unwatched' => 'int'],
			[self::MEMBER, self::TOPIC, 0, 0],
			['id_member', 'id_topic']
		);
	}

	private function rowExists(): bool
	{
		global $smcFunc;

		$result = $smcFunc['db_query']('', '
			SELECT id_topic
			FROM {db_prefix}log_topics
			WHERE id_member = {int:member}
				AND id_topic = {int:topic}',
			['member' => self::MEMBER, 'topic' => self::TOPIC]
		);
		$exists = $smcFunc['db_num_rows']($result) > 0;
		$smcFunc['db_free_result']($result);

		return $exists;
	}

	private function deleteRow(): void
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_topics
			WHERE id_member = {int:member}
				AND id_topic = {int:topic}',
			['member' => self::MEMBER, 'topic' => self::TOPIC]
		);
	}
}