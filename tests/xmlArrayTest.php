<?php

namespace PHPTDD;

use xmlArray;

class TestxmlArray extends BaseTestCase
{
	protected function setUp()
	{
		global $sourcedir;

	require_once($sourcedir . '/Class-Package.php');
	}

	public function testxmlArray()
	{
		global $context;

		$xml = '<?xml version="1.0"?>
		<characters>
			<character>
				<name>Luke Skywalker</name>
				<weapon>Lightsaber</weapon>
			</character>
			<character>
				<name>Sauron</name>
				<weapon>Evil Eye</weapon>
			</character>
		</characters>';
		$array =
		[
			'character' => [
				'name' => 'Luke Skywalker',
				'weapon' => 'Lightsaber',
			],
			'character' => [
				'name' => 'Sauron',
				'weapon' => 'Evil Eye',
			],
		];

		$result = new xmlArray($xml);
		$this->assertTrue($result->exists('characters[0]'));
		$this->assertTrue($result->exists('characters[0]/character'));
		$this->assertTrue($result->exists('characters[0]/character'));
		$this->assertSame($array, $result->to_array('characters[0]'));
		$this->assertEquals(1, $result->count('characters'));
		$this->assertCount(2, $result->set('characters[0]/character'));

		// create_xml() encases all values in CDATA tags... but it's also naive, encoding unwanted things (emptiness).
		$cdatalessResult = preg_replace('#<!\[CDATA\[(.+?)\]\]>#s', '$1', $result->create_xml());

		// Remove whitespace since they no longer match.
		$this->assertEquals(preg_replace('/\s\s+/', '', $xml),preg_replace('/\s\s+/', '', $cdatalessResult));
    }
}