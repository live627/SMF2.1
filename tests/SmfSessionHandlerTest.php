<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Sources/Session.php';

/**
 * Unit tests for SmfSessionHandler.
 *
 * The database layer ($smcFunc) is replaced with in-memory fakes so no real
 * database is needed.
 */
final class SmfSessionHandlerTest extends TestCase
{
	private const VALID_ID = 'abcdefghijklmnop1234'; // 20 chars, matches ~^[A-Za-z0-9,-]{16,64}$~

	/** @var array<int, array{sql: string, params: array}> */
	private array $queries = [];

	/** @var array<int, array> */
	private array $inserts = [];

	/** @var mixed Row returned by db_fetch_row (false = no row). */
	private $fetchRow = false;

	private int $affectedRows = 1;

	private array $savedGlobals = [];

	protected function setUp(): void
	{
		global $smcFunc, $modSettings, $scripturl, $context;

		$this->savedGlobals = [
			'smcFunc' => $smcFunc ?? null,
			'modSettings' => $modSettings ?? null,
			'scripturl' => $scripturl ?? null,
			'context' => $context ?? null,
			'cookie' => $_COOKIE,
			'request' => $_REQUEST,
		];

		$this->queries = [];
		$this->inserts = [];
		$this->fetchRow = false;
		$this->affectedRows = 1;

		$smcFunc = [
			'db_query' => function ($identifier, $sql, $params = []) {
				$this->queries[] = ['sql' => $sql, 'params' => $params];
				return 'result-resource';
			},
			'db_fetch_row' => fn ($result) => $this->fetchRow,
			'db_free_result' => fn ($result) => true,
			'db_insert' => function ($method, $table, $columns, $values, $keys) {
				$this->inserts[] = compact('method', 'table', 'columns', 'values', 'keys');
				return true;
			},
			'db_affected_rows' => fn () => $this->affectedRows,
		];

		$modSettings = ['allow_guest_access' => 1];
		$scripturl = 'http://example.com/index.php';
		$context = [];
		$_COOKIE = ['SMFCookie' => 'x'];
		$_REQUEST = ['action' => 'profile'];
	}

	protected function tearDown(): void
	{
		global $smcFunc, $modSettings, $scripturl, $context;

		$smcFunc = $this->savedGlobals['smcFunc'];
		$modSettings = $this->savedGlobals['modSettings'];
		$scripturl = $this->savedGlobals['scripturl'];
		$context = $this->savedGlobals['context'];
		$_COOKIE = $this->savedGlobals['cookie'];
		$_REQUEST = $this->savedGlobals['request'];
	}

	// ---------------------------------------------------------------- open/close

	public function testOpenAndCloseAlwaysReturnTrue(): void
	{
		$handler = new SmfSessionHandler();

		$this->assertTrue($handler->open('/tmp', 'PHPSESSID'));
		$this->assertTrue($handler->close());
	}

	// ---------------------------------------------------------------------- read

	public function testReadWithInvalidIdReturnsEmptyStringAndSkipsDatabase(): void
	{
		$handler = new SmfSessionHandler();

		$this->assertSame('', $handler->read('short'));
		$this->assertSame('', $handler->read('bad id with spaces!!'));
		$this->assertCount(0, $this->queries);
	}

	public function testReadReturnsSessionDataFromDatabase(): void
	{
		$this->fetchRow = ['some|s:3:"abc";'];
		$handler = new SmfSessionHandler();

		$this->assertSame('some|s:3:"abc";', $handler->read(self::VALID_ID));
		$this->assertCount(1, $this->queries);
		$this->assertSame(self::VALID_ID, $this->queries[0]['params']['session_id']);
		$this->assertStringContainsString('FROM {db_prefix}sessions', $this->queries[0]['sql']);
	}

	public function testReadReturnsEmptyStringWhenNoRowExists(): void
	{
		$this->fetchRow = false;
		$handler = new SmfSessionHandler();

		$this->assertSame('', $handler->read(self::VALID_ID));
	}

	public function testReadCachesDataAcrossCalls(): void
	{
		$this->fetchRow = ['payload'];
		$handler = new SmfSessionHandler();

		$handler->read(self::VALID_ID);
		$handler->read(self::VALID_ID);

		$this->assertCount(1, $this->queries, 'Second read should use the cached data.');
	}

	// --------------------------------------------------------------------- write

	public function testWriteSkipsWhenNoCookies(): void
	{
		$_COOKIE = [];
		$handler = new SmfSessionHandler();

		$this->assertTrue($handler->write(self::VALID_ID, 'data'));
		$this->assertCount(0, $this->inserts);
	}

	public function testWriteSkipsForGuestsJustBrowsing(): void
	{
		$_REQUEST = []; // no action
		$handler = new SmfSessionHandler();

		$this->assertTrue($handler->write(self::VALID_ID, 'data'));
		$this->assertCount(0, $this->inserts);
	}

	public function testWriteSkipsForNoWriteActions(): void
	{
		$_REQUEST = ['action' => 'dlattach'];
		$handler = new SmfSessionHandler();

		$this->assertTrue($handler->write(self::VALID_ID, 'data'));
		$this->assertCount(0, $this->inserts);
	}

	public function testWriteAlwaysWritesWhenVerificationRequired(): void
	{
		global $context;
		$_REQUEST = [];
		$context['require_verification'] = true;
		$handler = new SmfSessionHandler();

		$this->assertTrue($handler->write(self::VALID_ID, 'data'));
		$this->assertCount(1, $this->inserts);
	}

	public function testWriteWithInvalidIdReturnsFalse(): void
	{
		$handler = new SmfSessionHandler();

		$this->assertFalse($handler->write('bad', 'data'));
		$this->assertCount(0, $this->inserts);
	}

	public function testWriteReplacesRowAndReturnsTrueOnSuccess(): void
	{
		$before = time();
		$handler = new SmfSessionHandler();

		$this->assertTrue($handler->write(self::VALID_ID, 'payload'));

		$this->assertCount(1, $this->inserts);
		$insert = $this->inserts[0];
		$this->assertSame('replace', $insert['method']);
		$this->assertSame('{db_prefix}sessions', $insert['table']);
		$this->assertSame(['session_id'], $insert['keys']);
		$this->assertSame(self::VALID_ID, $insert['values'][0]);
		$this->assertSame('payload', $insert['values'][1]);
		$this->assertGreaterThanOrEqual($before, $insert['values'][2]);
		$this->assertLessThanOrEqual(time(), $insert['values'][2]);
	}

	public function testWriteReturnsFalseWhenNoRowsAffected(): void
	{
		$this->affectedRows = 0;
		$handler = new SmfSessionHandler();

		$this->assertFalse($handler->write(self::VALID_ID, 'payload'));
	}

	// ------------------------------------------------------------------- destroy

	public function testDestroyWithInvalidIdReturnsFalse(): void
	{
		$handler = new SmfSessionHandler();

		$this->assertFalse($handler->destroy('nope'));
		$this->assertCount(0, $this->queries);
	}

	public function testDestroyDeletesSessionRow(): void
	{
		$handler = new SmfSessionHandler();

		$this->assertTrue($handler->destroy(self::VALID_ID));
		$this->assertCount(1, $this->queries);
		$this->assertStringContainsString('DELETE FROM {db_prefix}sessions', $this->queries[0]['sql']);
		$this->assertSame(self::VALID_ID, $this->queries[0]['params']['session_id']);
	}

	// ------------------------------------------------------------------------ gc

	public function testGcDeletesExpiredSessionsUsingGivenLifetime(): void
	{
		global $modSettings;
		$modSettings['databaseSession_lifetime'] = 0; // not configured
		$this->affectedRows = 5;
		$handler = new SmfSessionHandler();

		$before = time();
		$result = $handler->gc(7200);

		$this->assertSame(5, $result);
		$this->assertStringContainsString('DELETE FROM {db_prefix}sessions', $this->queries[0]['sql']);
		$cutoff = $this->queries[0]['params']['last_update'];
		$this->assertGreaterThanOrEqual($before - 7200, $cutoff);
		$this->assertLessThanOrEqual(time() - 7200, $cutoff);
	}

	public function testGcUsesConfiguredLifetimeWhenPhpDefaultIsLow(): void
	{
		global $modSettings;
		$modSettings['databaseSession_lifetime'] = 3600;
		$handler = new SmfSessionHandler();

		$handler->gc(1440);

		$cutoff = $this->queries[0]['params']['last_update'];
		$this->assertEqualsWithDelta(time() - 3600, $cutoff, 2);
	}

	public function testGcEnforcesMinimumLifetimeOf60Seconds(): void
	{
		global $modSettings;
		$modSettings['databaseSession_lifetime'] = 10;
		$handler = new SmfSessionHandler();

		$handler->gc(1440);

		$cutoff = $this->queries[0]['params']['last_update'];
		$this->assertEqualsWithDelta(time() - 60, $cutoff, 2);
	}

	public function testGcIgnoresConfiguredLifetimeWhenPhpValueIsHigherAndSettingIsLower(): void
	{
		global $modSettings;
		$modSettings['databaseSession_lifetime'] = 3600;
		$handler = new SmfSessionHandler();

		$handler->gc(86400);

		$cutoff = $this->queries[0]['params']['last_update'];
		$this->assertEqualsWithDelta(time() - 86400, $cutoff, 2);
	}

	// ----------------------------------------------------------------- validateId

	public function testValidateIdRejectsInvalidFormat(): void
	{
		$handler = new SmfSessionHandler();

		$this->assertFalse($handler->validateId('bad'));
		$this->assertCount(0, $this->queries);
	}

	public function testValidateIdAcceptsExistingSession(): void
	{
		$this->fetchRow = ['data'];
		$handler = new SmfSessionHandler();

		$this->assertTrue($handler->validateId(self::VALID_ID));
	}

	/**
	 * NOTE: validateId() compares the cached data against '' with !==, but
	 * read() stores null when no row exists, so this currently returns true
	 * for unknown sessions. With session.use_strict_mode this defeats the
	 * purpose of the check. Fix: `return $this->data !== null && $this->data !== '';`
	 * This test describes the intended behaviour and fails until that's fixed.
	 */
	public function testValidateIdRejectsUnknownSession(): void
	{
		$this->fetchRow = false;
		$handler = new SmfSessionHandler();

		$this->assertFalse($handler->validateId(self::VALID_ID));
	}

	// ------------------------------------------------------------ updateTimestamp

	public function testUpdateTimestampWithInvalidIdReturnsFalse(): void
	{
		$handler = new SmfSessionHandler();

		$this->assertFalse($handler->updateTimestamp('bad', 'data'));
	}

	public function testUpdateTimestampSkipsWriteWhenDataUnchanged(): void
	{
		$this->fetchRow = ['same'];
		$handler = new SmfSessionHandler();
		$handler->read(self::VALID_ID);

		$this->assertTrue($handler->updateTimestamp(self::VALID_ID, 'same'));
		$this->assertCount(0, $this->inserts);
	}

	/**
	 * NOTE: updateTimestamp() calls `$this->write($id, data)` — `data` is
	 * missing its `$`, which is an undefined constant (fatal Error on PHP 8).
	 * This test fails until that is fixed to `$data`.
	 */
	public function testUpdateTimestampWritesWhenDataChanged(): void
	{
		$this->fetchRow = ['old'];
		$handler = new SmfSessionHandler();
		$handler->read(self::VALID_ID);

		$this->assertTrue($handler->updateTimestamp(self::VALID_ID, 'new'));
		$this->assertCount(1, $this->inserts);
		$this->assertSame('new', $this->inserts[0]['values'][1]);
	}
}
