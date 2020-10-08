<?php

namespace PHPTDD;

class SMCTest extends BaseTestCase
{
	public function callbackProvider()
	{
		return array(
			array(
				'{empty}',
				array(),
				array(
					'MySQL' => '\'\'',
					'PostgreSQL' => '\'\'',
				)
			),
			array(
				'{literal:string}',
				array(),
				array(
					'MySQL' => '\'string\'',
					'PostgreSQL' => '\'string\'',
				)
			),
			array(
				'{int:int}',
				array('int' => 0),
				array(
					'MySQL' => 0,
					'PostgreSQL' => 0,
				)
			),
			array(
				'{array_int:int}',
				array('int' => [0, 1]),
				array(
					'MySQL' => '0, 1',
					'PostgreSQL' => '0, 1',
				)
			),
			array(
				'{literal:string}',
				array(),
				array(
					'MySQL' => '\'string\'',
					'PostgreSQL' => '\'string\'',
				)
			),
			array(
				'{string:string}',
				array('string' => 'string'),
				array(
					'MySQL' => '\'string\'',
					'PostgreSQL' => '\'string\'',
				)
			),
			array(
				'{array_string:string}',
				array('string' => array('string', 'another_string')),
				array(
					'MySQL' => '\'string\', \'another_string\'',
					'PostgreSQL' => '\'string\', \'another_string\'',
				)
			),
			array(
				'{date:var}',
				array('var' => '2010-11-30'),
				array(
					'MySQL' => '\'2010-11-30\'',
					'PostgreSQL' => '\'2010-11-30\'::date',
				)
			),
			array(
				'{time:var}',
				array('var' => '23:59:59'),
				array(
					'MySQL' => '\'23:59:59\'',
					'PostgreSQL' => '\'23:59:59\'::time',
				)
			),
			array(
				'{datetime:var}',
				array('var' => '2010-11-30 23:59:59'),
				array(
					'MySQL' => 'str_to_date(\'2010-11-30 23:59:59\',\'%Y-%m-%d %h:%i:%s\')',
					'PostgreSQL' => 'to_timestamp(\'2010-11-30 23:59:59\',\'YYYY-MM-DD HH24:MI:SS\')',
				)
			),
			array(
				'{float:var}',
				array('var' => 2.3),
				array(
					'MySQL' => '2.3',
					'PostgreSQL' => '2.3',
				)
			),
			array(
				'{inet:var}',
				array('var' => '127.0.0.1'),
				array(
					'MySQL' => sprintf('unhex(\'%1$s\')', bin2hex(inet_pton('127.0.0.1'))),
					'PostgreSQL' => '\'127.0.0.1\'::inet',
				)
			),
			array(
				'{array_inet:var}',
				array('var' => ['127.0.0.1', '127.0.0.1']),
				array(
					'MySQL' => sprintf('unhex(\'%1$s\'), unhex(\'%1$s\')', bin2hex(inet_pton('127.0.0.1'))),
					'PostgreSQL' => '\'127.0.0.1\'::inet, \'127.0.0.1\'::inet',
				)
			),
			array(
				'{identifier:a_string}',
				array('a_string' => 'a_string'),
				array(
					'MySQL' => '`a_string`',
					'PostgreSQL' => '"a_string"',
				)
			),
		);
	}

	/**
	 * @dataProvider callbackProvider
	 */
	public function testCallback($test, $params, $expected)
	{
		global $smcFunc;

		$db_string = $smcFunc['db_quote']($test, $params);
		$this->assertEquals($db_string, $expected[$smcFunc['db_title']]);
	}

	public function testListTables()
	{
		global $db_prefix, $smcFunc;

		db_extend('packages');
		$tables = $smcFunc['db_list_tables']();
		$this->assertContains(substr(strrchr($db_prefix, '.'), 1) . 'log_actions', $tables);
		$this->assertCount(73, $tables);
	}

	public function testListOneTable()
	{
		global $db_prefix, $smcFunc;

		db_extend('packages');
		$tables = $smcFunc['db_list_tables'](false, '%attach%');
		$this->assertContains(substr(strrchr($db_prefix, '.'), 1) . 'attachments', $tables);
		$this->assertCount(1, $tables);
	}

	public function test_entity_fix()
	{
		global $smcFunc;

		$this->assertEquals(
			'A&amp;AA&#x1CF;B',
			$smcFunc['entity_fix']('A&amp;A&#x1F;A&#x1CF;B')
		);
	}

	public function htmlspecialcharsProvider()
	{
		return array(
			array(
				'elvIs "the kIng" presLey who\'s online  αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός',
				'elvIs &quot;the kIng&quot; presLey who\'s online  αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός ',
			),
			array(
				'A \'quote\' is <b>bold</b>',
				'A \'quote\' is &lt;b&gt;bold&lt;/b&gt;',
			),
			array(
				'A&amp;A&#x1F;A&#x1CF;B',
				'A&amp;amp;A&amp;#x1F;A&amp;#x1CF;B ',
			),
			array(
				"\x8F!!!",
				'',
			),
		);
	}

	/**
	 * @dataProvider htmlspecialcharsProvider
	 */
	public function test_htmlspecialchars($test, $expected)
	{
		global $smcFunc;

		$this->assertEquals($expected, $smcFunc['htmlspecialchars']($test));
	}

	public function test_htmlspecialchars2()
	{
		global $smcFunc;

		$this->assertEquals(
			'A &#039;quote&#039; is &lt;b&gt;bold&lt;/b&gt;'
			$smcFunc['htmlspecialchars']('A \'quote\' is <b>bold</b>', ENT_QUOTES)
		);
	}

	public function test_strlen()
	{
		global $smcFunc;

		$this->assertEquals(7, strlen('A&amp;B'));
		$this->assertEquals(3, $smcFunc['strlen']('A&amp;B'));
	}

	public function test_strpos()
	{
		global $smcFunc;

		$this->assertEquals(6, strpos('A&amp;B', 'B'));
		$this->assertEquals(2, $smcFunc['strpos']('A&amp;B', 'B'));
	}

	public function test_strtolower()
	{
		global $smcFunc;

		$this->assertEquals('русские', $smcFunc['strtolower']('РУССКИЕ'));
	}

	public function test_strtoupper()
	{
		global $smcFunc;

		$this->assertEquals('РУССКИЕ', $smcFunc['strtoupper']('русские'));
	}

	public function test_substr()
	{
		global $smcFunc;

		$this->assertEquals('A&amp;B', substr('aA&amp;B', 1));
		$this->assertEquals('A&amp;B', $smcFunc['substr']('aA&amp;B', 1));
		$this->assertEquals('B', $smcFunc['substr']('aA&amp;B', -1));
	}

	public function test_ucwords()
	{
		global $smcFunc;

		$this->assertEquals(
			'ElvIs "the KIng" PresLey Who\'s Online  Αλώπηξ Βαφής Ψημένη Γη, Δρασκελίζει Υπέρ Νωθρού Κυνός ',
			$smcFunc['ucwords']('elvIs "the kIng" presLey who\'s online  αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός')
		);
	}

	public function test_ucwords2()
	{
		global $smcFunc;

		$this->assertEquals(
			'La Dernière Usine Française D\'Accordéons Reste À Tulle',
			$smcFunc['ucwords']('La dernière usine française d\'accordéons reste à Tulle')
		);
	}

	public function testReplaceValues()
	{
		global $db_prefix, $smcFunc;

		$smcFunc['db_insert']('replace',
			'{db_prefix}settings',
			array('variable' => 'string-255', 'value' => 'string-65534'),
			array(
				array('variable1', 'value1'),
				array('variable2', 'value2'),
				array('variable3', 'value3'),
			),
			array('variable')
		);
		$this->assertEquals(3, $smcFunc['db_affected_rows']());
		$request = $smcFunc['db_query']('', '
			SELECT value
			FROM {db_prefix}settings
			WHERE variable IN ({array_string:variables})
			ORDER BY variable',
			array(
				'variables' => array('variable1', 'variable2', 'variable3')
			)
		);
		list ($variable1) = $smcFunc['db_fetch_row']($request);
		list ($variable2) = $smcFunc['db_fetch_row']($request);
		list ($variable3) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$this->assertEquals('value1', $variable1);
		$this->assertEquals('value2', $variable2);
		$this->assertEquals('value3', $variable3);

		$smcFunc['db_insert']('replace',
			'{db_prefix}settings',
			array('variable' => 'string-255', 'value' => 'string-65534'),
			array(
				array('variable1', 'value11'),
				array('variable2', 'value22'),
				array('variable3', 'value33'),
			),
			array('variable')
		);
		$this->assertEquals(3, $smcFunc['db_affected_rows']());
		$request = $smcFunc['db_query']('', '
			SELECT value
			FROM {db_prefix}settings
			WHERE variable IN ({array_string:variables})
			ORDER BY variable',
			array(
				'variables' => array('variable1', 'variable2', 'variable3')
			)
		);
		list ($variable1) = $smcFunc['db_fetch_row']($request);
		list ($variable2) = $smcFunc['db_fetch_row']($request);
		list ($variable3) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$this->assertEquals('value11', $variable1);
		$this->assertEquals('value22', $variable2);
		$this->assertEquals('value33', $variable3);
	}
}