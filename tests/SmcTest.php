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
}