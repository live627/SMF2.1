<?php

declare(strict_types=1);

namespace PHPTDD;

class SubsTest extends BaseTestCase
{
	public function testTimeformat(): void
	{
		global $context, $txt, $modSettings, $user_info;

		$this->assertEquals('%b %d, %Y, %I:%M %p', $user_info['time_format']);
		$this->assertEquals('%b %d, %Y, %I:%M %p', $modSettings['time_format']);
		$this->assertEquals('May 23, 1970, 09:21 PM', timeformat(12345678));
		$this->assertEquals('May 23, 1970, 09:21 PM', timeformat(12345678), '%F %H:%M');
		$this->assertEquals('May 23, 1970, 09:21 PM', timeformat(12345678, false));
		$this->assertEquals('1970-05-23', timeformat(12345678, '%F'));
		$this->assertStringContainsString('Today', timeformat(time()));
	}

	public function dateOrTimeProvider(): array
	{
		return [
			[
				'%c',
				'%x',
				'%X',
			],
			[
				'c',
				'%b %d, %Y',
				'%I:%M %p',
			],
			[
				' .  %c c . ',
				'%x',
				'%X',
			],
		];
	}

	/**
	 * @dataProvider dateOrTimeProvider
	 */
	public function testDateOrTime(string $test, string $expected_date, string $expected_time): void
	{
		$this->assertEquals($expected_date, get_date_or_time_format('date', $test));
		$this->assertEquals($expected_time, get_date_or_time_format('time', $test));
	}

	public function testBuildRegex(): void
	{
		$this->assertEquals(' \.  %c c \. ', build_regex(' .  %c c . '));
		$this->assertEquals(' \.  %c\, c \. ', build_regex(' .  %c, c . ', ','));
		$this->assertEquals('(?>a|b|c)', build_regex(['a', 'b', 'c']));
		$this->assertEquals(
			['(?>a\,|b|c)'],
			build_regex(['a,', 'b', 'c'], ',', true)
		);
	}

	public function sentenceListProvider(): array
	{
		return [
			[
				['a', 'b', 'c'],
				'a, b, and c',
			],
			[
				['a', 'b,b', 'c'],
				'a, b,b, and c',
			],
			[
				['a', 'b, b', 'c'],
				'a; b, b; and c',
			],
		];
	}

	/**
	 * @dataProvider sentenceListProvider
	 */
	public function testSentenceList(array $test, string $expected): void
	{
		$this->assertEquals($expected, sentence_list($test));
	}

	public function testSentenceList2(): void
	{
		global $txt;

		$txt['sentence_list_format'][4] = 'items {1} with {-1} ({series})';
		$this->assertEquals('items a with d (b, c)', sentence_list(['a', 'b', 'c', 'd']));
	}

	public function iriProvider(): array
	{
		return [
			[
				'http://üser:pässword@☃.net/påth',
				'http://%C3%BCser:p%C3%A4ssword@xn--n3h.net/p%C3%A5th',
			],
			[
				'http://☃.net/',
				'http://xn--n3h.net/',
			],
		];
	}

	/**
	 * @dataProvider iriProvider
	 */
	public function testIri(string $test, string $expected): void
	{
		$this->assertEquals($expected, iri_to_url($test));
		$this->assertEquals($test, url_to_iri($expected));
	}
}
