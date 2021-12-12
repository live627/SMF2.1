<?php

declare(strict_types=1);

namespace PHPTDD;

class SMCTest extends BaseTestCase
{
	/**
	 */
	public function callbackProvider(): array
	{
		return [
			[
				'{empty}',
				[],
				[
					'MySQL' => '\'\'',
					'PostgreSQL' => '\'\'',
				],
			],
			[
				'{literal:string}',
				[],
				[
					'MySQL' => '\'string\'',
					'PostgreSQL' => '\'string\'',
				],
			],
			[
				'{int:int}',
				['int' => 0],
				[
					'MySQL' => 0,
					'PostgreSQL' => 0,
				],
			],
			[
				'{array_int:int}',
				['int' => [0, 1]],
				[
					'MySQL' => '0, 1',
					'PostgreSQL' => '0, 1',
				],
			],
			[
				'{literal:string}',
				[],
				[
					'MySQL' => '\'string\'',
					'PostgreSQL' => '\'string\'',
				],
			],
			[
				'{string:string}',
				['string' => 'string'],
				[
					'MySQL' => '\'string\'',
					'PostgreSQL' => '\'string\'',
				],
			],
			[
				'{array_string:string}',
				['string' => ['string', 'another_string']],
				[
					'MySQL' => '\'string\', \'another_string\'',
					'PostgreSQL' => '\'string\', \'another_string\'',
				],
			],
			[
				'{date:var}',
				['var' => '2010-11-30'],
				[
					'MySQL' => '\'2010-11-30\'',
					'PostgreSQL' => '\'2010-11-30\'::date',
				],
			],
			[
				'{time:var}',
				['var' => '23:59:59'],
				[
					'MySQL' => '\'23:59:59\'',
					'PostgreSQL' => '\'23:59:59\'::time',
				],
			],
			[
				'{datetime:var}',
				['var' => '2010-11-30 23:59:59'],
				[
					'MySQL' => 'str_to_date(\'2010-11-30 23:59:59\',\'%Y-%m-%d %h:%i:%s\')',
					'PostgreSQL' => 'to_timestamp(\'2010-11-30 23:59:59\',\'YYYY-MM-DD HH24:MI:SS\')',
				],
			],
			[
				'{float:var}',
				['var' => 2.3],
				[
					'MySQL' => '2.3',
					'PostgreSQL' => '2.3',
				],
			],
			[
				'{inet:var}',
				['var' => '127.0.0.1'],
				[
					'MySQL' => sprintf('unhex(\'%1$s\')', bin2hex(inet_pton('127.0.0.1'))),
					'PostgreSQL' => '\'127.0.0.1\'::inet',
				],
			],
			[
				'{array_inet:var}',
				['var' => ['127.0.0.1', '127.0.0.1']],
				[
					'MySQL' => sprintf('unhex(\'%1$s\'), unhex(\'%1$s\')', bin2hex(inet_pton('127.0.0.1'))),
					'PostgreSQL' => '\'127.0.0.1\'::inet, \'127.0.0.1\'::inet',
				],
			],
			[
				'{identifier:a_string}',
				['a_string' => 'a_string'],
				[
					'MySQL' => '`a_string`',
					'PostgreSQL' => '"a_string"',
				],
			],
		];
	}

	/**
	 * @dataProvider callbackProvider
	 *
	 */
	public function testCallback($test, $params, $expected): void
	{
		global $smcFunc;

		$db_string = $smcFunc['db_quote']($test, $params);
		$this->assertEquals($db_string, $expected[$smcFunc['db_title']]);
	}

	public function testListTables(): void
	{
		global $smcFunc;

		db_extend('packages');
		$tables = $smcFunc['db_list_tables']();
		$this->assertContains('smf_log_actions', $tables);
		$this->assertCount(73, $tables);
	}

	public function testListOneTable(): void
	{
		global $smcFunc;

		$tables = $smcFunc['db_list_tables'](false, '%attach%');
		$this->assertStringContainsString('attachments', $tables[0]);
		$this->assertCount(1, $tables);
	}

	public function testEntityFix(): void
	{
		global $smcFunc;

		$this->assertEquals('', $smcFunc['entity_fix']('x1F'));
		$this->assertEquals('&#32;', $smcFunc['entity_fix']('x20'));
		$this->assertEquals('', $smcFunc['entity_fix']('31'));
		$this->assertEquals('&#32;', $smcFunc['entity_fix']('32'));
	}

	/**
	 * @return string[][]
	 */
	public function htmlspecialcharsProvider(): array
	{
		return [
			[
				'elvIs "the kIng" presLey who\'s online  αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός',
				'elvIs &quot;the kIng&quot; presLey who\'s online  αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός',
			],
			[
				'A \'quote\' is <b>bold</b>',
				'A \'quote\' is &lt;b&gt;bold&lt;/b&gt;',
			],
			[
				'A&amp;A&#x1F;A&#x1CF;B',
				'A&amp;amp;A&amp;#x1F;A&amp;#x1CF;B',
			],
			[
				"\x8F!!!",
				'',
			],
		];
	}

	/**
	 * @dataProvider htmlspecialcharsProvider
	 *
	 */
	public function testHtmlspecialchars($test, $expected): void
	{
		global $smcFunc;

		$this->assertEquals($expected, $smcFunc['htmlspecialchars']($test));
	}

	public function testHtmlspecialchars2(): void
	{
		global $smcFunc;

		$this->assertEquals(
			'A &#39;quote&#39; is &lt;b&gt;bold&lt;/b&gt;',
			$smcFunc['htmlspecialchars']('A \'quote\' is <b>bold</b>', ENT_QUOTES)
		);
	}

	/**
	 * @return string[][]
	 */
	public function htmltrimProvider(): array
	{
		return [
			[
				"\0\t\n\v\f\r\ejjk\u{a0}\u{ad}\u{2000}\u{2001}\u{2002}\u{2003}\u{2004}\u{2005}\u{2006}\u{2007}\u{2008}\u{2009}\u{200a}\u{200b}\u{200c}\u{200d}\u{200e}\u{200f}\u{2028}\u{2029}\u{202a}\u{202b}\u{202c}\u{202d}\u{202e}\u{202f}\u{205f}\u{2060}\u{2061}\u{2062}\u{2063}\u{2064}\u{2065}\u{2066}\u{2067}\u{2068}\u{2069}\u{206a}\u{206b}\u{206c}\u{206d}\u{206e}\u{206f}\u{3000}\u{feff}",
				'jjk',
			],
			[
				"\0\n\n\1\n\r\n\13\27a ab\r\n\t\t\r\r\ncà\1êß€\0\0abcbc   d\n\n\t\r\n\5   e\2\n\3\n\4\n",
				"a ab\r\n\t\t\r\r\ncà\1êß€\0\0abcbc   d\n\n\t\r\n\5   e",
			],
		];
	}

	/**
	 * @dataProvider htmltrimProvider
	 *
	 */
	public function testHtmltrim($test, $expected): void
	{
		global $smcFunc;

		$this->assertEquals($expected, $smcFunc['htmltrim']($test));
	}

	public function testStrlen(): void
	{
		global $smcFunc;

		$this->assertEquals(7, strlen('A&amp;B'));
		$this->assertEquals(3, $smcFunc['strlen']('A&amp;B'));
	}

	public function testStrpos(): void
	{
		global $smcFunc;

		$this->assertEquals(6, strpos('A&amp;B', 'B'));
		$this->assertEquals(2, $smcFunc['strpos']('A&amp;B', 'B'));
	}

	public function testStrtolower(): void
	{
		global $smcFunc;

		$this->assertEquals('русские', $smcFunc['strtolower']('РУССКИЕ'));
	}

	public function testStrtoupper(): void
	{
		global $smcFunc;

		$this->assertEquals('РУССКИЕ', $smcFunc['strtoupper']('русские'));
	}

	public function testSubstr(): void
	{
		global $smcFunc;

		$this->assertEquals('A&amp;B', substr('aA&amp;B', 1));
		$this->assertEquals('A&amp;B', $smcFunc['substr']('aA&amp;B', 1));
		$this->assertEquals('B', $smcFunc['substr']('aA&amp;B', -1));
	}

	public function testUcwords(): void
	{
		global $smcFunc;

		$this->assertEquals(
			'ElvIs "the KIng" PresLey Who\'s Online  Αλώπηξ Βαφής Ψημένη Γη, Δρασκελίζει Υπέρ Νωθρού Κυνός',
			$smcFunc['ucwords'](
				'elvIs "the kIng" presLey who\'s online  αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός'
			)
		);
	}

	public function testUcwords2(): void
	{
		global $smcFunc;

		$this->assertEquals(
			'La Dernière Usine Française D\'accordéons Reste À Tulle',
			$smcFunc['ucwords']('La dernière usine française d\'accordéons reste à Tulle')
		);
	}

	public function testReplaceValues(): void
	{
		global $db_prefix, $smcFunc;

		$smcFunc['db_insert'](
			'replace',
			'{db_prefix}settings',
			['variable' => 'string-255', 'value' => 'string-65534'],
			[
				['variable1', 'value1'],
				['variable2', 'value2'],
				['variable3', 'value3'],
			],
			['variable']
		);
		$this->assertEquals(3, $smcFunc['db_affected_rows']());
		$request = $smcFunc['db_query'](
			'',
			'
			SELECT value
			FROM {db_prefix}settings
			WHERE variable IN ({array_string:variables})
			ORDER BY variable',
			[
				'variables' => ['variable1', 'variable2', 'variable3'],
			]
		);
		[$variable1] = $smcFunc['db_fetch_row']($request);
		[$variable2] = $smcFunc['db_fetch_row']($request);
		[$variable3] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$this->assertEquals('value1', $variable1);
		$this->assertEquals('value2', $variable2);
		$this->assertEquals('value3', $variable3);

		$smcFunc['db_insert'](
			'replace',
			'{db_prefix}settings',
			['variable' => 'string-255', 'value' => 'string-65534'],
			[
				['variable1', 'value11'],
				['variable2', 'value22'],
				['variable3', 'value33'],
			],
			['variable']
		);
		$this->assertEquals(
			$smcFunc['db_title'] == MYSQL_TITLE ? 6 : 3,
			$smcFunc['db_affected_rows']()
		);
		$request = $smcFunc['db_query'](
			'',
			'
			SELECT value
			FROM {db_prefix}settings
			WHERE variable IN ({array_string:variables})
			ORDER BY variable',
			[
				'variables' => ['variable1', 'variable2', 'variable3'],
			]
		);
		[$variable1] = $smcFunc['db_fetch_row']($request);
		[$variable2] = $smcFunc['db_fetch_row']($request);
		[$variable3] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$this->assertEquals('value11', $variable1);
		$this->assertEquals('value22', $variable2);
		$this->assertEquals('value33', $variable3);
	}
}
