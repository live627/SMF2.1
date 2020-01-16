<?php

namespace PHPTDD;

class FetchWebdataTest extends BaseTestCase
{
	public function postProvider()
	{
		return array(
			array(
				'https://duckduckgo.com/html',
				'q=smf+forum site:simplemachines.org&ia=about',
				'Simple Machines <b>Forum</b> - Free &amp; open source community software',
			),
		);
	}

	public function getProvider()
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
	 */
	public function testGet(string $url, string $responseBody)
	{
		$data =  fetch_web_data($url);
		$this->assertContains($responseBody,	$data);
	}

	/**
	 * @dataProvider postProvider
	 */
	public function testPost(string $url, string $postData, string $responseBody)
	{
		$data = fetch_web_data($url, $postData);
		$this->assertContains($responseBody, $data);
	}
}