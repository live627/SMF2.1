<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class SMCTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		db_extend('packages');
	}

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

	#[\PHPUnit\Framework\Attributes\DataProvider('callbackProvider')]
    public function testCallback($test, $params, $expected): void
	{
		global $smcFunc;

		$this->assertEquals($smcFunc['db_quote']($test, $params), $expected[$smcFunc['db_title']]);
	}

	public function testListTables(): void
	{
		global $smcFunc;

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

	#[\PHPUnit\Framework\Attributes\DataProvider('htmlspecialcharsProvider')]
    public function testHtmlspecialchars($test, $expected): void
	{
		global $smcFunc;

		$this->assertEquals($expected, $smcFunc['htmlspecialchars']($test));
	}

	public function fourByteProvider(): array
	{
		return [
			[
				'€😄A𐍈¢',
				'€&#128516;A&#66376;¢',
			],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('fourByteProvider')]
    public function testFourByte($test, $expected): void
	{
		global $smcFunc;

		$this->assertEquals($expected, $smcFunc['htmlspecialchars']($test));
		$this->assertEquals(
			$expected,
			preg_replace_callback(
				'|[\x{10000}-\x{10FFFF}]|u',
				function ($m)
				{
					$val = (ord($m[0][0]) & 0x07) << 18;
					$val += (ord($m[0][1]) & 0x3F) << 12;
					$val += (ord($m[0][2]) & 0x3F) << 6;
					$val += (ord($m[0][3]) & 0x3F);

					return '&#' . $val . ';';
				},
				$test
			)
		);
		$this->assertEquals($test, preg_replace_callback('~&#(\d{3,8});~', 'fixchar__callback', $expected));
	}

	public function testHtmlspecialchars2(): void
	{
		global $smcFunc;

		$this->assertEquals(
			'A &#39;quote&#39; is &lt;b&gt;bold&lt;/b&gt;',
			$smcFunc['htmlspecialchars']('A \'quote\' is <b>bold</b>', ENT_QUOTES)
		);
	}

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

	#[\PHPUnit\Framework\Attributes\DataProvider('htmltrimProvider')]
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
			'ElvIs "The KIng" PresLey Who\'s Online  Αλώπηξ Βαφής Ψημένη Γη, Δρασκελίζει Υπέρ Νωθρού Κυνός',
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
		global $smcFunc;

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

	public function nullProvider(): array
	{
		// Testing issue #7420.
		return [
			[
				[
					'name' => 'test_legacy_null',
					'type' => 'varchar',
					'size' => 1,
					'null' => true,
				],
				true,
			],
			[
				[
					'name' => 'test_legacy_not_null',
					'type' => 'varchar',
					'size' => 1,
					'null' => false,
				],
				false,
			],
			[
				[
					'name' => 'test_not_null',
					'type' => 'varchar',
					'size' => 1,
					'not_null' => false,
				],
				true,
			],
			[
				[
					'name' => 'test_null',
					'type' => 'varchar',
					'size' => 1,
					'not_null' => true,
				],
				false,
			],
			[
				[
					'name' => 'test_default_null',
					'type' => 'varchar',
					'size' => 1,
					'default' => null,
				],
				true,
			],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('nullProvider')]
    public function testNull(array $test, bool $expected): void
	{
		global $smcFunc;

		$smcFunc['db_add_column']('{db_prefix}log_packages', $test, []);
		$cols = $smcFunc['db_list_columns']('{db_prefix}log_packages', true);
		$smcFunc['db_remove_column']('{db_prefix}log_packages', $test['name']);

		$this->assertNotContains($test['name'], $smcFunc['db_list_columns']('{db_prefix}log_packages'));
		$this->assertArrayHasKey($test['name'], $cols);
		$col = $cols[$test['name']];
		$this->assertIsString($col['name']);
		$this->assertNotEmpty($col['name']);
		$this->assertSame(!$expected, $col['not_null']);
		$this->assertSame($expected, $col['null']);
		$this->assertNull($col['default']);
	}

	public function defaultProvider(): array
	{
		// Testing issue #7418.
		return [
			[
				[
					'name' => 'test_default_empty',
					'type' => 'varchar',
					'size' => 1,
					'default' => '',
				],
				'',
			],
			[
				[
					'name' => 'test_default_value',
					'type' => 'varchar',
					'size' => 1,
					'default' => 'a',
				],
				'a',
			],
			[
				[
					'name' => 'test_default_int',
					'type' => 'varchar',
					'size' => 1,
					'default' => 1,
				],
				'1',
			],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('defaultProvider')]
    public function testDefault(array $test, $expected): void
	{
		global $smcFunc;

		$smcFunc['db_add_column']('{db_prefix}log_packages', $test, []);
		$cols = $smcFunc['db_list_columns']('{db_prefix}log_packages', true);
		$smcFunc['db_remove_column']('{db_prefix}log_packages', $test['name']);

		$this->assertNotContains($test['name'], $smcFunc['db_list_columns']('{db_prefix}log_packages'));
		$this->assertArrayHasKey($test['name'], $cols);
		$col = $cols[$test['name']];
		$this->assertIsString($col['name']);
		$this->assertNotEmpty($col['name']);
		$this->assertFalse($col['not_null']);
		$this->assertTrue($col['null']);
		$this->assertSame($expected, $col['default']);
	}

	public function testCreateTable(): void
	{
		global $db_prefix, $smcFunc;

		$def = [
			'columns' => [
				[
					'name' => 'id_atest',
					'type' => 'INT',
					'not_null' => true,
				],
				[
					'name' => 'fails_in_pg',
					'type' => 'TEXT',
					'not_null' => true,
				],
				[
					'name' => 'fails_in_mysql_56',
					'type' => 'varchar',
					'size' => 512,
					'default' => '',
					'not_null' => true,
				],
			],
			'indexes' => [
				[
					'type' => 'primary',
					'columns' => ['id_atest'],
				],
				[
					'type' => 'index',
					'name' => 'ix_test_text',
					'columns' => ['fails_in_pg (64)'],
				],
				[
					'type' => 'index',
					'name' => 'ix_test_ix_length',
					'columns' => ['fails_in_mysql_56'],
				],
			],
		];

		// All write operations must be performed BEFORE any assertions
		// because any failures will break (interrupt) the script,
		// leaving the database in an unexpected state.
		$smcFunc['db_create_table']('{db_prefix}a_test_record', $def['columns'], $def['indexes']);
		$structure = $smcFunc['db_table_structure']('{db_prefix}a_test_record');
		$tables = $smcFunc['db_list_tables']();
		$smcFunc['db_drop_table']('{db_prefix}a_test_record');

		$this->assertCount(3, $def['columns']);
		$this->assertCount(3, $def['indexes']);
		$this->assertCount(3, $structure['columns']);
		$this->assertCount(3, $structure['indexes']);
		$this->assertEquals($db_prefix . 'a_test_record', $structure['name']);
		$this->assertContains('smf_a_test_record', $tables);
		$this->assertNotContains('smf_a_test_record', $smcFunc['db_list_tables']());
		foreach ($def['columns'] as $col)
		{
			$this->assertArrayHasKey($col['name'], $structure['columns']);
			$dbcol = $structure['columns'][$col['name']];
			$this->assertEquals($col['name'], $dbcol['name']);
			$this->assertFalse($dbcol['null']);
			$this->assertTrue($dbcol['not_null']);
			$this->assertFalse($dbcol['auto']);
		}
	}

	public function testCreateTableToUpdate(): array
	{
		global $db_prefix, $smcFunc;

		// Testing issue #7594.
		$def = [
			'columns' => [
				// pk
				[
					'name' => 'id_atest',
					'type' => 'int',
					'auto' => true,
				],
				// unspec
				[
					'name' => 'from_int_unspec_to_int_unspec',
					'type' => 'int',
				],
				[
					'name' => 'from_int_unspec_to_int_0',
					'type' => 'int',
				],
				[
					'name' => 'from_int_unspec_to_int_25',
					'type' => 'int',
				],
				[
					'name' => 'from_int_unspec_to_int_null',
					'type' => 'int',
				],
				[
					'name' => 'from_int_unspec_to_text_unspec',
					'type' => 'int',
				],
				[
					'name' => 'from_int_unspec_to_text_emptystr',
					'type' => 'int',
				],
				[
					'name' => 'from_int_unspec_to_text_george',
					'type' => 'int',
				],
				[
					'name' => 'from_int_unspec_to_text_null',
					'type' => 'int',
				],
				[
					'name' => 'from_int_unspec_drop_default',
					'type' => 'int',
				],
				// 0
				[
					'name' => 'from_int_0_to_int_unspec',
					'type' => 'int',
					'default' => 0,
				],
				[
					'name' => 'from_int_0_to_int_0',
					'type' => 'int',
					'default' => 0,
				],
				[
					'name' => 'from_int_0_to_int_25',
					'type' => 'int',
					'default' => 0,
				],
				[
					'name' => 'from_int_0_to_int_null',
					'type' => 'int',
					'default' => 0,
				],
				[
					'name' => 'from_int_0_to_text_unspec',
					'type' => 'int',
					'default' => 0,
				],
				[
					'name' => 'from_int_0_to_text_emptystr',
					'type' => 'int',
					'default' => 0,
				],
				[
					'name' => 'from_int_0_to_text_george',
					'type' => 'int',
					'default' => 0,
				],
				[
					'name' => 'from_int_0_to_text_null',
					'type' => 'int',
					'default' => 0,
				],
				[
					'name' => 'from_int_0_drop_default',
					'type' => 'int',
					'default' => 0,
				],
				// 25
				[
					'name' => 'from_int_25_to_int_unspec',
					'type' => 'int',
					'default' => 25,
				],
				[
					'name' => 'from_int_25_to_int_0',
					'type' => 'int',
					'default' => 25,
				],
				[
					'name' => 'from_int_25_to_int_25',
					'type' => 'int',
					'default' => 25,
				],
				[
					'name' => 'from_int_25_to_int_null',
					'type' => 'int',
					'default' => 25,
				],
				[
					'name' => 'from_int_25_to_text_unspec',
					'type' => 'int',
					'default' => 25,
				],
				[
					'name' => 'from_int_25_to_text_emptystr',
					'type' => 'int',
					'default' => 25,
				],
				[
					'name' => 'from_int_25_to_text_george',
					'type' => 'int',
					'default' => 25,
				],
				[
					'name' => 'from_int_25_to_text_null',
					'type' => 'int',
					'default' => 25,
				],
				[
					'name' => 'from_int_25_drop_default',
					'type' => 'int',
					'default' => 25,
				],
				// null
				[
					'name' => 'from_int_null_to_int_unspec',
					'type' => 'int',
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_int_null_to_int_0',
					'type' => 'int',
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_int_null_to_int_25',
					'type' => 'int',
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_int_null_to_int_null',
					'type' => 'int',
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_int_null_to_text_unspec',
					'type' => 'int',
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_int_null_to_text_emptystr',
					'type' => 'int',
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_int_null_to_text_george',
					'type' => 'int',
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_int_null_to_text_null',
					'type' => 'int',
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_int_null_drop_default',
					'type' => 'int',
					'default' => null,
					'null' => true,
				],
				// unspec
				[
					'name' => 'from_text_unspec_to_int_unspec',
					'type' => 'VARCHAR',
					'size' => 25,
				],
				[
					'name' => 'from_text_unspec_to_int_0',
					'type' => 'VARCHAR',
					'size' => 25,
				],
				[
					'name' => 'from_text_unspec_to_int_25',
					'type' => 'VARCHAR',
					'size' => 25,
				],
				[
					'name' => 'from_text_unspec_to_int_null',
					'type' => 'VARCHAR',
					'size' => 25,
				],
				[
					'name' => 'from_text_unspec_to_text_unspec',
					'type' => 'VARCHAR',
					'size' => 25,
				],
				[
					'name' => 'from_text_unspec_to_text_emptystr',
					'type' => 'VARCHAR',
					'size' => 25,
				],
				[
					'name' => 'from_text_unspec_to_text_george',
					'type' => 'VARCHAR',
					'size' => 25,
				],
				[
					'name' => 'from_text_unspec_to_text_null',
					'type' => 'VARCHAR',
					'size' => 25,
				],
				[
					'name' => 'from_text_unspec_drop_default',
					'type' => 'VARCHAR',
					'size' => 25,
				],
				// ''
				[
					'name' => 'from_text_emptystr_to_int_unspec',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => '',
				],
				[
					'name' => 'from_text_emptystr_to_int_0',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => '',
				],
				[
					'name' => 'from_text_emptystr_to_int_25',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => '',
				],
				[
					'name' => 'from_text_emptystr_to_int_null',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => '',
				],
				[
					'name' => 'from_text_emptystr_to_text_unspec',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => '',
				],
				[
					'name' => 'from_text_emptystr_to_text_emptystr',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => '',
				],
				[
					'name' => 'from_text_emptystr_to_text_george',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => '',
				],
				[
					'name' => 'from_text_emptystr_to_text_null',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => '',
				],
				[
					'name' => 'from_text_emptystr_drop_default',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => '',
				],
				// george
				[
					'name' => 'from_text_george_to_int_unspec',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => 'george',
				],
				[
					'name' => 'from_text_george_to_int_0',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => 'george',
				],
				[
					'name' => 'from_text_george_to_int_25',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => 'george',
				],
				[
					'name' => 'from_text_george_to_int_null',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => 'george',
				],
				[
					'name' => 'from_text_george_to_text_unspec',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => 'george',
				],
				[
					'name' => 'from_text_george_to_text_emptystr',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => 'george',
				],
				[
					'name' => 'from_text_george_to_text_george',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => 'george',
				],
				[
					'name' => 'from_text_george_to_text_null',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => 'george',
				],
				[
					'name' => 'from_text_george_drop_default',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => 'george',
				],
				// null
				[
					'name' => 'from_text_null_to_int_unspec',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_text_null_to_int_0',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_text_null_to_int_25',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_text_null_to_int_null',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_text_null_to_text_unspec',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_text_null_to_text_emptystr',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_text_null_to_text_george',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_text_null_to_text_null',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => null,
					'null' => true,
				],
				[
					'name' => 'from_text_null_drop_default',
					'type' => 'VARCHAR',
					'size' => 25,
					'default' => null,
					'null' => true,
				],
				// null tests
				[
					'name' => 'from_null_true_to_null_false',
					'type' => 'VARCHAR',
					'size' => 25,
					'null' => true,
				],
				[
					'name' => 'from_null_false_to_null_true',
					'type' => 'VARCHAR',
					'size' => 25,
					'null' => false,
				],
				// not_null tests
				[
					'name' => 'from_not_null_true_to_not_null_false',
					'type' => 'VARCHAR',
					'size' => 25,
					'not_null' => true,
				],
				[
					'name' => 'from_not_null_false_to_not_null_true',
					'type' => 'VARCHAR',
					'size' => 25,
					'not_null' => false,
				],
			],
			'indexes' => [
				[
					'type' => 'primary',
					'columns' => ['id_atest'],
				],
			],
		];

		// All write operations must be performed BEFORE any assertions
		// because any failures will break (interrupt) the script,
		// leaving the database in an unexpected state.
		$smcFunc['db_create_table']('{db_prefix}a_test_record', $def['columns'], $def['indexes']);
		$structure = $smcFunc['db_table_structure']('{db_prefix}a_test_record');
		$tables = $smcFunc['db_list_tables']();

		$this->assertCount(77, $def['columns']);
		$this->assertCount(1, $def['indexes']);
		$this->assertCount(77, $structure['columns']);
		$this->assertCount(1, $structure['indexes']);
		$this->assertEquals($db_prefix . 'a_test_record', $structure['name']);
		$this->assertContains('smf_a_test_record', $tables);

		return $structure['columns'];
	}

	public function columnProvider(): iterable
	{
		$file = json_decode(file_get_contents(__DIR__ . '/fixtures/table1.json'), true);

		foreach ($file['columns'] as $name => $test)
			yield $name => [$test];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('columnProvider')]
    #[Depends('testCreateTableToUpdate')]
    public function testCheckColumn(array $expected, array $columns): void
	{
		$this->assertArrayHasKey('name', $expected);
		$this->assertArrayHasKey($expected['name'], $columns);
		$expected_column = $columns[$expected['name']];
		$this->assertArrayHasKey('size', $expected);
		$this->assertArrayHasKey('null', $expected);
		$this->assertArrayHasKey('not_null', $expected);
		$this->assertArrayHasKey('default', $expected);
		$this->assertArrayHasKey('name', $expected_column);
		$this->assertArrayHasKey('size', $expected_column);
		$this->assertArrayHasKey('null', $expected_column);
		$this->assertArrayHasKey('not_null', $expected_column);
		$this->assertArrayHasKey('default', $expected_column);
		$this->assertIsString($expected_column['name']);
		$this->assertNotEmpty($expected_column['name']);
		$this->assertIsBool($expected_column['not_null']);
		$this->assertIsBool($expected_column['null']);
		$this->assertSame($expected['name'], $expected_column['name']);
		$this->assertSame($expected['null'], $expected_column['null']);
		$this->assertSame($expected['not_null'], $expected_column['not_null']);
		$this->assertSame($expected['default'], $expected_column['default']);
	}

	#[\PHPUnit\Framework\Attributes\Depends('testCreateTableToUpdate')]
    public function testUpdateTable(array $columns): array
	{
		global $db_prefix, $smcFunc;

		$def = [
			// unspec
			'from_int_unspec_to_int_unspec' => [
				'type' => 'int',
			],
			'from_int_unspec_to_int_0' => [
				'type' => 'int',
				'default' => 0,
			],
			'from_int_unspec_to_int_25' => [
				'type' => 'int',
				'default' => 25,
			],
			'from_int_unspec_to_int_null' => [
				'type' => 'int',
				'default' => null,
				'null' => true,
			],
			'from_int_unspec_to_text_unspec' => [
				'type' => 'VARCHAR',
				'size' => 50,
			],
			'from_int_unspec_to_text_emptystr' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => '',
			],
			'from_int_unspec_to_text_george' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => 'george',
			],
			'from_int_unspec_to_text_null' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => null,
				'null' => true,
			],
			'from_int_unspec_drop_default' => [
				'drop_default' => true,
			],

			// 0
			'from_int_0_to_int_unspec' => [
				'type' => 'int',
			],
			'from_int_0_to_int_0' => [
				'type' => 'int',
				'default' => 0,
			],
			'from_int_0_to_int_25' => [
				'type' => 'int',
				'default' => 25,
			],
			'from_int_0_to_int_null' => [
				'type' => 'int',
				'default' => null,
				'null' => true,
			],
			// Without a new default specified, this one SHOULD FAIL due to incompatible data types...
			// For purposes of letting the test complete, providing a default here...
			'from_int_0_to_text_unspec' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => 'would fail otherwise',
			],
			'from_int_0_to_text_emptystr' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => '',
			],
			'from_int_0_to_text_george' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => 'george',
			],
			'from_int_0_to_text_null' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => null,
				'null' => true,
			],
			'from_int_0_drop_default' => [
				'drop_default' => true,
			],

			// 25
			'from_int_25_to_int_unspec' => [
				'type' => 'int',
			],
			'from_int_25_to_int_0' => [
				'type' => 'int',
				'default' => 0,
			],
			'from_int_25_to_int_25' => [
				'type' => 'int',
				'default' => 25,
			],
			'from_int_25_to_int_null' => [
				'type' => 'int',
				'default' => null,
				'null' => true,
			],
			// Without a new default specified, this one SHOULD FAIL due to incompatible data types...
			// For purposes of letting the test complete, setting a default here...
			'from_int_25_to_text_unspec' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => 'would fail otherwise',
			],
			'from_int_25_to_text_emptystr' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => '',
			],
			'from_int_25_to_text_george' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => 'george',
			],
			'from_int_25_to_text_null' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => null,
				'null' => true,
			],
			'from_int_25_drop_default' => [
				'drop_default' => true,
			],

			// null
			'from_int_null_to_int_unspec' => [
				'type' => 'int',
			],
			'from_int_null_to_int_0' => [
				'type' => 'int',
				'default' => 0,
			],
			'from_int_null_to_int_25' => [
				'type' => 'int',
				'default' => 25,
			],
			'from_int_null_to_int_null' => [
				'type' => 'int',
				'default' => null,
				'null' => true,
			],
			'from_int_null_to_text_unspec' => [
				'type' => 'VARCHAR',
				'size' => 50,
			],
			'from_int_null_to_text_emptystr' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => '',
			],
			'from_int_null_to_text_george' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => 'george',
			],
			'from_int_null_to_text_null' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => null,
				'null' => true,
			],
			'from_int_null_drop_default' => [
				'null' => false,
				'drop_default' => true,
			],

			// unspec
			'from_text_unspec_to_int_unspec' => [
				'type' => 'int',
			],
			'from_text_unspec_to_int_0' => [
				'type' => 'int',
				'default' => 0,
			],
			'from_text_unspec_to_int_25' => [
				'type' => 'int',
				'default' => 25,
			],
			'from_text_unspec_to_int_null' => [
				'type' => 'int',
				'default' => null,
				'null' => true,
			],
			'from_text_unspec_to_text_unspec' => [
				'type' => 'VARCHAR',
				'size' => 50,
			],
			'from_text_unspec_to_text_emptystr' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => '',
			],
			'from_text_unspec_to_text_george' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => 'george',
			],
			'from_text_unspec_to_text_null' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => null,
				'null' => true,
			],
			'from_text_unspec_drop_default' => [
				'null' => false,
				'drop_default' => true,
			],

			// ''
			// Without a new default specified, this one SHOULD FAIL due to incompatible data types...
			// For purposes of letting the test complete, setting a default here...
			'from_text_emptystr_to_int_unspec' => [
				'type' => 'int',
				'default' => 9999999,
			],
			'from_text_emptystr_to_int_0' => [
				'type' => 'int',
				'default' => 0,
			],
			'from_text_emptystr_to_int_25' => [
				'type' => 'int',
				'default' => 25,
			],
			'from_text_emptystr_to_int_null' => [
				'type' => 'int',
				'default' => null,
				'null' => true,
			],
			'from_text_emptystr_to_text_unspec' => [
				'type' => 'VARCHAR',
				'size' => 50,
			],
			'from_text_emptystr_to_text_emptystr' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => '',
			],
			'from_text_emptystr_to_text_george' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => 'george',
			],
			'from_text_emptystr_to_text_null' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => null,
				'null' => true,
			],
			'from_text_emptystr_drop_default' => [
				'null' => false,
				'drop_default' => true,
			],

			// george
			// Without a new default specified, this one SHOULD FAIL due to incompatible data types...
			// For purposes of letting the test complete, setting a default here...
			'from_text_george_to_int_unspec' => [
				'type' => 'int',
				'default' => 9999999,
			],
			'from_text_george_to_int_0' => [
				'type' => 'int',
				'default' => 0,
			],
			'from_text_george_to_int_25' => [
				'type' => 'int',
				'default' => 25,
			],
			'from_text_george_to_int_null' => [
				'type' => 'int',
				'default' => null,
				'null' => true,
			],
			'from_text_george_to_text_unspec' => [
				'type' => 'VARCHAR',
				'size' => 50,
			],
			'from_text_george_to_text_emptystr' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => '',
			],
			'from_text_george_to_text_george' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => 'george',
			],
			'from_text_george_to_text_null' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => null,
				'null' => true,
			],
			'from_text_george_drop_default' => [
				'null' => false,
				'drop_default' => true,
			],

			// null
			'from_text_null_to_int_unspec' => [
				'type' => 'int',
			],
			'from_text_null_to_int_0' => [
				'type' => 'int',
				'default' => 0,
			],
			'from_text_null_to_int_25' => [
				'type' => 'int',
				'default' => 25,
			],
			'from_text_null_to_int_null' => [
				'type' => 'int',
				'default' => null,
				'null' => true,
			],
			'from_text_null_to_text_unspec' => [
				'type' => 'VARCHAR',
				'size' => 50,
			],
			'from_text_null_to_text_emptystr' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => '',
			],
			'from_text_null_to_text_george' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => 'george',
			],
			'from_text_null_to_text_null' => [
				'type' => 'VARCHAR',
				'size' => 50,
				'default' => null,
				'null' => true,
			],
			'from_text_null_drop_default' => [
				'null' => false,
				'drop_default' => true,
			],

			// null tests
			'from_null_true_to_null_false' => [
				'type' => 'VARCHAR',
				'size' => 25,
				'null' => false,
			],
			'from_null_false_to_null_true' => [
				'type' => 'VARCHAR',
				'size' => 25,
				'null' => true,
			],

			// not_null tests
			'from_not_null_true_to_not_null_false' => [
				'type' => 'VARCHAR',
				'size' => 25,
				'not_null' => false,
			],
			'from_not_null_false_to_not_null_true' => [
				'type' => 'VARCHAR',
				'size' => 25,
				'not_null' => true,
			],
		];

		foreach ($def as $col_name => $col_info)
		{
			$this->assertArrayHasKey($col_name, $columns);
			$smcFunc['db_change_column']('{db_prefix}a_test_record', $col_name, $col_info);
		}

		$structure = $smcFunc['db_table_structure']('{db_prefix}a_test_record');
		$tables = $smcFunc['db_list_tables']();

		$this->assertCount(77, $structure['columns']);
		$this->assertCount(1, $structure['indexes']);
		$this->assertEquals($db_prefix . 'a_test_record', $structure['name']);
		$this->assertContains('smf_a_test_record', $tables);

		return $structure['columns'];
	}

	public function updatedColumnProvider(): iterable
	{
		$file = json_decode(file_get_contents(__DIR__ . '/fixtures/table2.json'), true);

		foreach ($file['columns'] as $name => $test)
			yield $name => [$test];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('updatedColumnProvider')]
    #[Depends('testCreateTableToUpdate')]
    #[Depends('testUpdateTable')]
    public function testCheckUpdatedColumn(array $expected, array $columns, array $updated): void
	{
		$this->assertArrayHasKey('name', $expected);
		$this->assertArrayHasKey($expected['name'], $columns);
		$this->assertArrayHasKey($expected['name'], $updated);
		$expected_column = $updated[$expected['name']];
		$this->assertArrayHasKey('size', $expected);
		$this->assertArrayHasKey('null', $expected);
		$this->assertArrayHasKey('not_null', $expected);
		$this->assertArrayHasKey('default', $expected);
		$this->assertArrayHasKey('name', $expected_column);
		$this->assertArrayHasKey('size', $expected_column);
		$this->assertArrayHasKey('null', $expected_column);
		$this->assertArrayHasKey('not_null', $expected_column);
		$this->assertArrayHasKey('default', $expected_column);
		$this->assertIsString($expected_column['name']);
		$this->assertNotEmpty($expected_column['name']);
		$this->assertIsBool($expected_column['not_null']);
		$this->assertIsBool($expected_column['null']);
		$this->assertSame($expected['name'], $expected_column['name']);
		$this->assertSame($expected['null'], $expected_column['null']);
		$this->assertSame($expected['not_null'], $expected_column['not_null']);
		$this->assertSame($expected['default'], $expected_column['default']);
	}

	public function testDropTable(): void
	{
		global $smcFunc;

		// Always drop the table regardless of whether the two
		// tests above for creating and updating it have failed.
		// Keep the database in a known and expected state.
		$smcFunc['db_drop_table']('{db_prefix}a_test_record');
		$this->assertNotContains('smf_a_test_record', $smcFunc['db_list_tables']());
	}
}
