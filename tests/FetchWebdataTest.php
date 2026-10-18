<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Needs the fixture server running: node server.js
 * Override the address with the TEST_SERVER_URL environment variable.
 */
class FetchWebdataTest extends TestCase
{
	private static function baseUrl(): string
	{
		return rtrim(getenv('TEST_SERVER_URL') ?: 'http://localhost:8125', '/');
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
			$this->markTestSkipped('Could not fetch ' . $path . '; is "node server.js" running?');

		$this->assertStringContainsString($responseBody, $data);
	}

	/**
	 * @dataProvider postProvider
	 */
	public function testPost(string $path, string $postData, string $responseBody): void
	{
		$data = fetch_web_data(self::baseUrl() . $path, $postData);

		if ($data === false)
			$this->markTestSkipped('Could not post to ' . $path . '; is "node server.js" running?');

		$this->assertStringContainsString($responseBody, $data);
	}
}