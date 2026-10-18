<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

use InvalidArgumentException;
use TOTP\Auth;

class TOTPTest extends TestCase
{
	/**
	 * Known good vector: key "ABC" produced code 424535 at this moment.
	 */
	private const KEY = 'ABC';
	private const CODE = '424535';
	private const TIMESTAMP = 1421707340;

	/**
	 * The base32 alphabet the class is expected to expose, mapped to values.
	 */
	private const BASE32_VALUES = [
		'A' => 0,
		'B' => 1,
		'C' => 2,
		'D' => 3,
		'E' => 4,
		'F' => 5,
		'G' => 6,
		'H' => 7,
		'I' => 8,
		'J' => 9,
		'K' => 10,
		'L' => 11,
		'M' => 12,
		'N' => 13,
		'O' => 14,
		'P' => 15,
		'Q' => 16,
		'R' => 17,
		'S' => 18,
		'T' => 19,
		'U' => 20,
		'V' => 21,
		'W' => 22,
		'X' => 23,
		'Y' => 24,
		'Z' => 25,
		2 => 26,
		3 => 27,
		4 => 28,
		5 => 29,
		6 => 30,
		7 => 31,
	];

	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Class-TOTP.php';
	}

	/**
	 * The alphabet's digit keys are ints in PHP arrays ('2' becomes 2), but
	 * generated codes are strings. assertContains() compares strictly, so
	 * normalise everything to strings before comparing.
	 *
	 * @return string[]
	 */
	private static function alphabet(): array
	{
		return array_map('strval', array_keys(self::BASE32_VALUES));
	}

	public function testInstantiate(): void
	{
		$auth = new Auth();
		$this->assertInstanceOf(Auth::class, $auth);
	}

	public function testInvalidInitKeyFails(): void
	{
		$this->expectException(InvalidArgumentException::class);
		new Auth('~');
	}

	public function testValidInitKeyGetsSet(): void
	{
		$auth = new Auth(self::KEY);
		$this->assertEquals(self::KEY, $auth->getInitKey(), 'Valid key can be got');
	}

	public function testBase32LookupIsCorrect(): void
	{
		$auth = new Auth();
		$this->assertEquals(self::BASE32_VALUES, $auth->getLookup());
	}

	public function testGetAndSetRange(): void
	{
		$range = 54;
		$auth = new Auth();
		$auth->setRange($range);
		$this->assertEquals($range, $auth->getRange(), 'Range is set correctly');
	}

	public function testInvalidRangeFails(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$auth = new Auth();
		$auth->setRange('cat');
	}

	public function testSetAndGetRefresh(): void
	{
		$refresh = 43;
		$auth = new Auth();
		$auth->setRefresh($refresh);
		$this->assertEquals($refresh, $auth->getRefresh(), 'Refresh is set and got OK');
	}

	public function testInvalidRefreshFails(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$auth = new Auth();
		$auth->setRefresh('litter');
	}

	public function testSetAndGetCodeLength(): void
	{
		$length = 123;
		$auth = new Auth();
		$auth->setCodeLength($length);
		$this->assertEquals($length, $auth->getCodeLength());
	}

	public function testGenerateAInitCode(): void
	{
		$auth = new Auth();
		$code = $auth->generateCode();
		$this->assertSame(16, strlen($code), 'Default code is 16 chars');

		foreach (str_split($code) as $letter)
			$this->assertContains($letter, self::alphabet(), 'Key consists of base 32 values');
	}

	public function testGenerateACodeOfLength(): void
	{
		$auth = new Auth();
		$code = $auth->generateCode(4);
		$this->assertSame(4, strlen($code), 'Requested length is honoured');

		foreach (str_split($code) as $letter)
			$this->assertContains($letter, self::alphabet(), 'Key consists of base 32 values');
	}

	public function testValidateAValidCode(): void
	{
		$auth = new Auth(self::KEY);

		$result = $auth->validateCode(self::CODE, null, self::TIMESTAMP);
		$this->assertTrue($result, 'This is a known good code');
	}

	public function testValidateAValidCodeFalseWhenOutOfRange(): void
	{
		$auth = new Auth(self::KEY);
		$auth->setRange(1);

		$result = $auth->validateCode(self::CODE, null, self::TIMESTAMP);
		$this->assertFalse($result, 'This was a known good code 2 seconds ago');
	}

	public function testValidateACodeOfWrongLengthFails(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$auth = new Auth();
		$auth->setCodeLength(10);
		$auth->validateCode('lt10');
	}

	public function testValidateWithDifferentInitKeyIsFalse(): void
	{
		$auth = new Auth('DEF');

		$result = $auth->validateCode(self::CODE, null, self::TIMESTAMP);
		$this->assertFalse($result, 'This is a known good code for key ABC, not DEF');
	}

	public function testValidateAfterTimeHasPassedIsFalse(): void
	{
		$auth = new Auth(self::KEY);

		// No timestamp given, so "now" is used: the 2015 code has long expired.
		$result = $auth->validateCode(self::CODE);
		$this->assertFalse($result, 'This was a known good code, but that was then');
	}
}