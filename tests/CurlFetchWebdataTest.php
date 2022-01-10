<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

use curl_fetch_web_data;

class CurlFetchWebdataTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Class-CurlFetchWeb.php';
	}

	/**
	 * @return (int|string|string[])[][]
	 */
	public function postProvider(): array
	{
		return [
			[
				'https://www.google.com',
				['gs_taif0' => 'smf'],
				405,
				'all we know',
			],
			[
				'https://duckduckgo.com/html',
				['q' => 'smf+forum site:simplemachines.org', 'ia' => 'about'],
				200,
				'Simple Machines Forum - Free &amp; open source community software',
			],
		];
	}

	/**
	 * @return       (int|string)[][]
	 *
	 * @psalm-return array{0: array{0: string, 1: int, 2: string}, 1: array{0: string, 1: int, 2: string}}
	 */
	public function getProvider(): array
	{
		return [
			[
				'https://www.google.com',
				200,
				'Search the world\'s information',
			],
			[
				'http://www.google.com/smf',
				404,
				'all we know',
			],
		];
	}

	/**
	 * @dataProvider getProvider
	 *
	 * @group        slow
	 *
	 */
	public function testGet(string $url, int $responseCode, string $responseBody): void
	{
		$curl = new curl_fetch_web_data([CURLOPT_RETURNTRANSFER => 1], 1);
		$curl->get_url_data($url);
		$this->assertEquals($responseCode, $curl->result('code'));
		$this->assertStringContainsString($responseBody, $curl->result('body'));
	}

	/**
	 * @dataProvider postProvider
	 *
	 * @group        slow
	 *
	 */
	public function testPost(string $url, array $postData, int $responseCode, string $responseBody): void
	{
		$curl = new curl_fetch_web_data();
		$curl->get_url_data($url, $postData);
		$this->assertEquals($responseCode, $curl->result('code'));
		$this->assertStringContainsString($responseBody, $curl->result('body'));
	}
}