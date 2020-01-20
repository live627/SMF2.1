<?php

namespace PHPTDD;

use xmlArray;
use ErrorException;

class TestxmlArray extends BaseTestCase
{
	protected function setUp()
	{
		global $sourcedir;

		require_once($sourcedir . '/Class-Package.php');
	}

	public function data()
	{
		return array(
			array(
				'<?xml version="1.0"?>
				<characters>
					<character film="Star Wars">
						<name>Luke Skywalker</name>
						<weapon>Lightsaber</weapon>
					</character>
					<character film="LOTR">
						<name>Sauron</name>
						<weapon>Evil Eye</weapon>
					</character>
				</characters>',
				[
					'character' => [
						'name' => 'Luke Skywalker',
						'weapon' => 'Lightsaber',
					],
					'character' => [
						'name' => 'Sauron',
						'weapon' => 'Evil Eye',
					],
				],
			),
		);
	}

	/**
	 * @dataProvider data
	 */
	public function test(string $xml, array $array)
	{
		$result = new xmlArray($xml);
		$this->assertTrue($result->exists('characters[0]'));
		$this->assertTrue($result->exists('characters[0]/character'));
		$this->assertTrue($result->exists('characters[0]/character[1]/@film'));
		$this->assertSame($array, $result->to_array('characters[0]'));
		$this->assertSame(['characters' => $array], $result->to_array());

		// no work
		//$this->assertEquals('characters', $result->name());

		$this->assertEquals('LOTR', $result->fetch('characters[0]/character[1]/@film'));
		$this->assertInstanceOf(xmlArray::class, $result->path('characters[0]/character[1]/'));
		$this->assertEquals(1, $result->count('characters'));
		$this->assertCount(2, $result->set('characters[0]/character'));

		// create_xml() encases all values in CDATA tags... but it's also naive, encoding unwanted things (emptiness).
		$cdatalessResult = preg_replace('#<!\[CDATA\[(.+?)\]\]>#s', '$1', $result->create_xml());

		// Remove whitespace since they no longer match.
		$this->assertEquals(preg_replace('/\s\s+/', '', $xml), preg_replace('/\s\s+/', '', $cdatalessResult));
	}

	/**
	 * @dataProvider data
	 */
	public function testNoisyFailure(string $xml)
	{
		set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext)
		{
			throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
		});

		$this->expectException(ErrorException::class);
		$result = new xmlArray($xml);
		$result->to_array('error');

		restore_error_handler();
	}

	/**
	 * @dataProvider data
	 */
	public function testSilentFailure(string $xml)
	{
		$result = new xmlArray($xml, true, 0);
		$this->assertFalse($result->to_array('error'));
	}
}