<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Needs the fixture server (node server.js) reachable at http://11.0.0.1 on port 80.
 * SMF's is_fetch_safe() refuses loopback and private addresses, and fetch_web_data()
 * only talks to ports 80/443, so 127.0.0.1:8125 can never work. Instead:
 *
 *     sudo ip addr add 11.0.0.1/32 dev lo      (macOS: sudo ifconfig lo0 alias 11.0.0.1)
 *     sudo node server.js 80
 *
 * Override the address with the TEST_SERVER_URL environment variable.
 */
class FetchWebdataTest extends TestCase
{
	private static function baseUrl(): string
	{
		return rtrim(getenv('TEST_SERVER_URL') ?: 'http://11.0.0.1', '/');
	}

	/**
	 * @return string[][]
	 */
	public function postProvider(): array
	{
		return [
			[
				'/html',
				'q=smf+forum site:simplemachines.org&ia=about',
				'simplemachines.org',
			],
		];
	}

	/**
	 * @return string[][]
	 */
	public function getProvider(): array
	{
		return [
			[
				'/bing.html',
				'Bing',
			],
		];
	}

	/**
	 * @dataProvider getProvider
	 */
	public function testGet(string $path, string $responseBody): void
	{
		$data = fetch_web_data(self::baseUrl() . $path);

		if ($data === false)
			$this->markTestSkipped('Could not fetch ' . $path . '; is 11.0.0.1 set up and "node server.js 80" running? (see the class comment)');

		$this->assertStringContainsString($responseBody, $data);
	}

	/**
	 * @dataProvider postProvider
	 */
	public function testPost(string $path, string $postData, string $responseBody): void
	{
		$data = fetch_web_data(self::baseUrl() . $path, $postData);

		if ($data === false)
			$this->markTestSkipped('Could not post to ' . $path . '; is 11.0.0.1 set up and "node server.js 80" running? (see the class comment)');

		$this->assertStringContainsString($responseBody, $data);
	}
}