<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Base class for tests that work on the real database.
 *
 * A test case that wants its database changes undone opts in in one of two ways:
 *
 *  - Set the property for every test in the class:
 *
 *        protected bool $use_transaction = true;
 *
 *  - Or call $this->startTransaction() in the one test (or in setUp()) that needs it.
 *
 * A subclass that overrides setUp() or tearDown() must call parent::setUp() first
 * and parent::tearDown() last. Calling it last means the test's own cleanup runs
 * inside the transaction, and skipping it leaves the transaction open.
 * Tests that do not opt in run as before and keep what they write.
 *
 * What a rollback does not undo:
 *  - Tables that do not support transactions (MyISAM on MySQL).
 *  - Statements that commit implicitly (CREATE, ALTER and DROP TABLE).
 *  - Auto-increment counters and sequences.
 *  - SMF's cache and the in-memory $modSettings and $context.
 */
abstract class IntegrationTestCase extends TestCase
{
	/** Set to true in a subclass to run every test in a transaction. */
	protected bool $use_transaction = false;

	private bool $in_transaction = false;

	protected function setUp(): void
	{
		parent::setUp();

		if ($this->use_transaction)
			$this->startTransaction();
	}

	protected function tearDown(): void
	{
		global $smcFunc;

		parent::tearDown();

		if (!$this->in_transaction)
			return;

		$this->in_transaction = false;

		if (!$smcFunc['db_transaction']('rollback'))
			$this->fail('Could not roll back the database transaction.');
	}

	/**
	 * Starts a transaction that is rolled back when the test ends.
	 * Does nothing if one is already open.
	 */
	protected function startTransaction(): void
	{
		global $smcFunc;

		if ($this->in_transaction)
			return;

		if (!$smcFunc['db_transaction']('begin'))
			$this->fail('Could not start a database transaction.');

		$this->in_transaction = true;
	}

	/**
	 * Whether this test is running inside a transaction that will be rolled back.
	 */
	protected function inTransaction(): bool
	{
		return $this->in_transaction;
	}
}