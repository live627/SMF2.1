<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Makes several real HTTP requests to a running SMF and checks that every
 * visit ends up in the log_online table.
 *
 * Needs SMF served over HTTP, e.g. from the project root:
 *
 *     php -S 127.0.0.1:8080 -t .
 *
 * Override the address with the TEST_SMF_URL environment variable.
 *
 * How SMF logs guests (see writeLog() in Sources/Logging.php): a guest's row
 * is keyed by "ip" + their IP address, so two guest requests from the same IP
 * share one row. To get one row per visit, every request below is sent from
 * its own loopback address (127.0.0.2, 127.0.0.3, ...). Linux treats the
 * whole 127.0.0.0/8 block as local; macOS needs the aliases adding first,
 * e.g. "sudo ifconfig lo0 alias 127.0.0.2".
 */
class OnlineLogTest extends TestCase
{
	public function setUp(): void
	{
		global $modSettings;

		// who_enabled fills the url column; no_guest_logging stops guest rows.
		if (empty($modSettings['who_enabled']) || !empty($modSettings['no_guest_logging']))
			$this->markTestSkipped('Needs who_enabled on and no_guest_logging off.');

		$this->deleteRows();
	}

	public function tearDown(): void
	{
		$this->deleteRows();
	}

	/**
	 * @dataProvider visitProvider
	 */
	public function testEachVisitIsLogged(array $query, string $ip): void
	{
		$session = 'ip' . $ip;
		$label = ($query === [] ? 'board index' : http_build_query($query)) . ' from ' . $ip;
		$userAgent = 'SMFOnlineLogTest/' . $ip;

		$this->assertNull($this->fetchRow($session), $label . ': row should not exist before the visit');

		$before = time();
		$status = $this->visit($ip, $query, $userAgent);
		$this->assertSame(200, $status, $label . ': SMF should answer 200 (a redirect means boardurl does not match ' . self::baseUrl() . ')');

		$row = $this->fetchRow($session);
		$this->assertNotNull($row, $label . ' should be in log_online');
		$this->assertEquals(0, $row['id_member'], $label . ': guests have no member id');
		$this->assertEquals(0, $row['id_spider'], $label . ': should not be mistaken for a spider');
		$this->assertGreaterThanOrEqual($before - 1, (int) $row['log_time'], $label . ': log_time is stale');
		$this->assertLessThanOrEqual(time() + 1, (int) $row['log_time'], $label . ': log_time is in the future');

		// The url column holds the request's query string plus the user agent as JSON.
		// SMF may add parameters it worked out itself (topic=1 also logs board=1),
		// so check what this visit sent rather than demanding an exact match.
		$logged = json_decode($row['url'], true);
		$this->assertIsArray($logged, $label . ': url column should hold JSON');
		foreach ($query + ['USER_AGENT' => $userAgent] as $key => $value)
		{
			$this->assertArrayHasKey($key, $logged, $label . ': url column is missing "' . $key . '"');
			$this->assertEquals($value, $logged[$key], $label . ': url column has the wrong "' . $key . '"');
		}
	}

	/**
	 * Each visit comes from its own client address, so each gets its own row.
	 *
	 * @return array<string, array{0: array, 1: string}>  [query, client ip]
	 */
	public static function visitProvider(): array
	{
		return [
			'board index' => [[], '127.0.0.2'],
			'message index' => [['board' => '1.0'], '127.0.0.3'],
			'topic display' => [['topic' => '1.0'], '127.0.0.4'],
			'help action' => [['action' => 'help'], '127.0.0.5'],
			'search action' => [['action' => 'search'], '127.0.0.6'],
			'login action' => [['action' => 'login'], '127.0.0.7'],
			'recent action' => [['action' => 'recent'], '127.0.0.8'],
		];
	}

	private static function baseUrl(): string
	{
		return rtrim(getenv('TEST_SMF_URL') ?: 'http://127.0.0.1:8080', '/');
	}

	private function deleteRows(): void
	{
		global $smcFunc;

		$smcFunc['db_query'](
			'',
			'TRUNCATE TABLE {db_prefix}log_online',
			[]
		);
	}

	/**
	 * Requests index.php as a guest from the given client address.
	 *
	 * @return int HTTP status code
	 */
	private function visit(string $clientIp, array $query, string $userAgent): int
	{
		$url = self::baseUrl() . '/index.php' . ($query === [] ? '' : '?' . http_build_query($query));

		$context = stream_context_create([
			'socket' => ['bindto' => $clientIp . ':0'],
			'http' => [
				'method' => 'GET',
				'user_agent' => $userAgent,
				'follow_location' => 0,
				'ignore_errors' => true,
				'timeout' => 15,
			],
		]);

		// No cookies are sent, so each request starts a brand new guest session.
		$body = @file_get_contents($url, false, $context);

		if ($body === false)
			$this->markTestSkipped(
				'Could not reach ' . $url . ' from ' . $clientIp . '; is "php -S 127.0.0.1:8080 -t ." running?'
			);

		return preg_match('~^HTTP/\S+\s+(\d{3})~', $http_response_header[0] ?? '', $matches) ? (int) $matches[1] : 0;
	}

	/**
	 * @return array|null The log_online row, or null if there is none.
	 */
	private function fetchRow(string $session): ?array
	{
		global $smcFunc;

		$request = $smcFunc['db_query'](
			'',
			'
			SELECT session, id_member, id_spider, log_time, url
			FROM {db_prefix}log_online
			WHERE session = {string:session}',
			['session' => $session]
		);
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		return $row ?: null;
	}
}