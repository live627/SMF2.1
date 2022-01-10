<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class SubsTest extends TestCase
{
	public function testTimeformat(): void
	{
		global $modSettings, $user_info;

		$this->assertEquals('%b %d, %Y, %I:%M %p', $user_info['time_format']);
		$this->assertEquals('%b %d, %Y, %I:%M %p', $modSettings['time_format']);
		$this->assertEquals('May 23, 1970, 09:21 PM', timeformat(12345678));
		$this->assertEquals('1970-05-23 21:21', timeformat(12345678, '%F %H:%M'));
		$this->assertEquals('May 23, 1970, 09:21 PM', timeformat(12345678, false));
		$this->assertEquals('1970-05-23', timeformat(12345678, '%F'));
		$this->assertStringContainsString('Today', timeformat(time()));
	}

	public function testTimeformatTz(): void
	{
		$this->assertEquals('1970-05-23 14:21', smf_strftime('%F %H:%M', 12345678, 'America/Phoenix'));
		$this->assertEquals('1970-05-23 14:21', timeformat(12345678, '%F %H:%M', 'America/Phoenix'));
		$this->assertRegExp('/[A-Z][a-z]{2} [0-9]{1,2}, [0-9]{4}, [0-9]{1,2}:[0-9]{2} [AP]M/', timeformat(strtotime('yesterday 00:00'), true, 'America/Phoenix'));
		$this->assertRegExp('/[A-Z][a-z]{2} [0-9]{1,2}, [0-9]{4}, [0-9]{1,2}:[0-9]{2} [AP]M/', timeformat(strtotime('yesterday 03:00'), true, 'America/Phoenix'));
		$this->assertStringContainsString('Today', timeformat(strtotime('yesterday 13:00'), true, 'America/Phoenix'));
		$this->assertStringContainsString('Today', timeformat(strtotime('yesterday 23:00'), true, 'America/Phoenix'));
		$this->assertStringContainsString('Today', timeformat(strtotime('today 00:00'), true, 'America/Phoenix'));
		$this->assertStringContainsString('Today', timeformat(strtotime('today 03:00'), true, 'America/Phoenix'));
		$this->assertRegExp('/[A-Z][a-z]{2} [0-9]{1,2}, [0-9]{4}, [0-9]{1,2}:[0-9]{2} [AP]M/', timeformat(strtotime('today 13:00'), true, 'America/Phoenix'));
		$this->assertRegExp('/[A-Z][a-z]{2} [0-9]{1,2}, [0-9]{4}, [0-9]{1,2}:[0-9]{2} [AP]M/', timeformat(strtotime('today 23:00'), true, 'America/Phoenix'));
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

	public function pageIndexProvider(): array
	{
		return array_merge(
			// Test that it doesn't underflow.
			array_map(fn($x) => [$x, 15, 5, 0, 0], range(-2, 4)),

			// Operate with normal parameters.
			array_map(fn($x) => [$x, 15, 5, 1, 5], range(5, 9)),

			// Test that it doesn't overflow.
			array_map(fn($x) => [$x, 15, 5, 2, 10], range(10, 21)),

			// Test that it outputs digits, not decimals.
			[[3000001, 4205, 42, 100, 4200]],

			// Ensure no rounding errors when normalising max.
			array_map(fn($x) => [6, $x, 5, 1, 5], range(16, 19)),
		);
	}

	/**
	 * @dataProvider pageIndexProvider
	 */
	public function testPageIndex(int $start, int $max_value, int $num_per_page, int $this_page, int $this_value): void
	{
		global $context;

		unset($context['current_page']);
		$page_index = constructPageIndex('querystring', $start, $max_value, $num_per_page);
		$pages = explode(' ', strip_tags($page_index));

		foreach ($pages as $page)
			if ($page !== 'Pages' && $page !== '...' && $page !== '')
				$this->assertStringMatchesFormat('%d', strtr($page, ['Pages1' => '1']));

		$this->assertIsInt($start);
		$this->assertIsInt($context['current_page']);
		$this->assertEquals($this_value, $start);
		$this->assertEquals($this_page, $context['current_page']);

		if ($start > 0)
			$this->assertStringContainsString(
				sprintf(
					'<span class="current_page">%d</span>',
					$this_page + 1
				),
				$page_index
			);

		if ($this_page > 0)
			$this->assertStringContainsString(
				sprintf(
					'<a class="nav_page" href="querystring;start=%d"><span class="main_icons previous_page"></span></a>',
					$this_value - $num_per_page
				),
				$page_index
			);
		else
			$this->assertStringNotContainsString('previous_page', $page_index);
	}

	/**
	 * @dataProvider pageIndexProvider
	 */
	public function testPageIndexAll(int $start, int $max_value, int $num_per_page, int $this_page, int $this_value): void
	{
		global $context, $modSettings;

		unset($context['current_page']);
		$modSettings['compactTopicPagesEnable'] = 0;
		$start_invalid = $start < 0;
		$page_index = constructPageIndex('querystring', $start, $max_value, $num_per_page);
		$modSettings['compactTopicPagesEnable'] = 1;

		for ($i = 0, $j = 1; $i < $max_value; $i += $num_per_page, $j++)
		{
			if ($start_invalid || $i != $start)
				$this->assertStringContainsString(
					sprintf('<a class="nav_page" href="querystring;start=%d">%d</a>', $i, $j),
					$page_index
				);
			else
				$this->assertStringContainsString(
					sprintf('<span class="current_page">%d</span>', $j),
					$page_index
				);
		}

		$this->assertIsInt($start);
		$this->assertIsInt($context['current_page']);
		$this->assertEquals($this_value, $start);
		$this->assertEquals($this_page, $context['current_page']);
	}
}
