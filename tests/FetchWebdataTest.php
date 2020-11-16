<?php

namespace PHPTDD;

class FetchWebdataTest extends BaseTestCase
{
	/**
	 * @return string[][]
	 *
	 * @psalm-return array{0: array{0: string, 1: string, 2: string}}
	 */
	public function postProvider(): array
	{
		return array(
			array(
				'https://duckduckgo.com/html',
				'q=smf+forum site:simplemachines.org&ia=about',
				'Simple Machines <b>Forum</b> - Free &amp; open source community software',
			),
		);
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{0: array{0: string, 1: string}}
	 */
	public function getProvider(): array
	{
		return array(
			array(
				'https://www.bing.com',
				'Bing',
			),
		);
	}

	/**
	 * @dataProvider getProvider
	 *
	 * @group slow
	 *
	 * @return void
	 */
	public function testGet(string $url, string $responseBody): void
	{
		if (($data = fetch_web_data($url)) !== false)
			$this->assertStringContainsString($responseBody, $data);
	}

	/**
	 * @dataProvider postProvider
	 *
	 * @group slow
	 *
	 * @return void
	 */
	public function testPost(string $url, string $postData, string $responseBody): void
	{
		if (($data = fetch_web_data($url, $postData)) !== false)
			$this->assertStringContainsString($responseBody, $data);
	}
}