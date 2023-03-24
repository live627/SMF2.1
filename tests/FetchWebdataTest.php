<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class FetchWebdataTest extends TestCase
{
	/**
	 * @return string[][]
	 */
	public function postProvider(): array
	{
		return [
			[
				'https://duckduckgo.com/html',
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
				'https://www.bing.com',
				'Bing',
			],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('getProvider')]
    #[Group('slow')]
    public function testGet(string $url, string $responseBody): void
	{
		if (($data = fetch_web_data($url)) !== false)
			$this->assertStringContainsString($responseBody, $data);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('postProvider')]
    #[Group('slow')]
    public function testPost(string $url, string $postData, string $responseBody): void
	{
		if (($data = fetch_web_data($url, $postData)) !== false)
			$this->assertStringContainsString($responseBody, $data);
	}
}