<?php

namespace PHPTDD;

use curl_fetch_web_data;

class CurlFetchWebdataTest extends BaseTestCase
{
	protected function setUp()
	{
		global $sourcedir;

		require_once($sourcedir . '/Class-CurlFetchWeb.php');
	}

	public function postProvider()
	{
		return array(
			array(
				'https://www.google.com',
				array('gs_taif0' => 'smf'),
				405,
				'all we know',
			),
			array(
				'https://duckduckgo.com/html',
				array('q' => 'smf+forum site:simplemachines.org', 'ia' => 'about'),
				200,
				'Simple Machines Forum - Free &amp; open source community software',
			),
		);
	}

	public function getProvider()
	{
		return array(
			array(
				'https://www.google.com',
				200,
				'Search the world\'s information',
			),
			array(
				'http://www.google.com/smf',
				404,
				'all we know',
			),
		);
	}

	/**
	 * @dataProvider getProvider
     * @group slow
	 */
	public function testGet(string $url, int $responseCode, string $responseBody)
	{
		$curl = new curl_fetch_web_data(array(CURLOPT_RETURNTRANSFER => 1), 1);
		$curl->get_url_data($url);
		$this->assertEquals($responseCode, $curl->result('code'));
		$this->assertContains($responseBody, $curl->result('body'));
	}

	/**
	 * @dataProvider postProvider
     * @group slow
	 */
	public function testPost(string $url, array $postData, int $responseCode, string $responseBody)
	{
		$curl = new curl_fetch_web_data();
		$curl->get_url_data($url, $postData);
		$this->assertEquals($responseCode, $curl->result('code'));
		$this->assertContains($responseBody, $curl->result('body'));
	}
}