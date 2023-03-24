<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

use ErrorException;
use xmlArray;

class TestxmlArray extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Class-Package.php';
	}

	public function data(): array
	{
		return [
			[
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
			],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('data')]
    public function test(string $xml, array $array): void
	{
		$result = new xmlArray($xml);
		$this->assertTrue($result->exists('characters[0]'));
		$this->assertTrue($result->exists('characters[0]/character'));
		$this->assertTrue($result->exists('characters[0]/character[1]/@film'));
		$this->assertSame($array, $result->to_array('characters[0]'));
		$this->assertSame(['characters' => $array], $result->to_array());

		// no work
		//$this->assertEquals('characters', $result->name());
		$this->assertEquals('', $result->name());

		$this->assertEquals('LOTR', $result->fetch('characters[0]/character[1]/@film'));
		$this->assertInstanceOf(xmlArray::class, $result->path('characters[0]/character[1]/'));
		$this->assertEquals(1, $result->count('characters'));
		$this->assertCount(2, $result->set('characters[0]/character'));

		// create_xml() encases all values in CDATA tags... but it's also naive, encoding unwanted things (emptiness).
		$cdatalessResult = preg_replace('#<!\[CDATA\[(.+?)\]\]>#s', '$1', $result->create_xml());

		// Remove whitespace since they no longer match.
		$this->assertEquals(preg_replace('/\s\s+/', '', $xml), preg_replace('/\s\s+/', '', $cdatalessResult));
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('data')]
    public function testNoisyFailure(string $xml): void
	{
		set_error_handler(
			function ($errno, $errstr, $errfile, $errline, $errcontext): void
			{
				throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
			}
		);

		$this->expectException(\Throwable::class);
		$result = new xmlArray($xml);
		$result->to_array('error');

		restore_error_handler();
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('data')]
    public function testSilentFailure(string $xml): void
	{
		$result = new xmlArray($xml, true, 0);
		$this->assertFalse($result->to_array('error'));
		$this->assertFalse($result->path('error'));
		$this->assertFalse($result->create_xml('error'));
	}
}
