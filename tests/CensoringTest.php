<?php
namespace PHPTDD;

class CensoringTest extends BaseTestCase
{
	protected $tests = array(
			'this' => array('this' => 'not_this'),
			'This' => array('This' => 'not_case_this'),
			'ex' => array('ex' => 'not_ex'),
		);
		
	function keepCaseWholeWordProvider()
	{
		return[
			['This is some jumbled text','not_case_this is some jumbled text'],
			['Example of random text','Example of random text'],
			['foobar','foobar'],
		];
	}
	
	/**
	 * @dataProvider keepCaseWholeWordProvider
	 */
	function testWholeWordsCaseSensitive($inputText, $expected)
	{
		global $modSettings;

		$modSettings['censorWholeWord'] = true;
		$modSettings['censorIgnoreCase'] = false;

		$actual = censorText($inputText);

		$this->assertEquals($expected, $actual);
	}

	function wholeWordsCaseInsensitiveProvider()
	{
		return[
			['This is some jumbled text','not_this is some jumbled text'],
			['Example of random text','Example of random text'],
			['foobar','foobar'],
		];
	}

	/**
	 * @dataProvider wholeWordsCaseInsensitiveProvider
	 */
	function testWholeWordsCaseInsensitive($inputText, $expected)
	{
		global $modSettings;

		$modSettings['censorWholeWord'] = true;
		$modSettings['censorIgnoreCase'] = true;

		$actual = censorText($inputText);

		$this->assertEquals($expected, $actual);
	}

	function notWholeWordsCaseSensitiveProvider()
	{
		return[
			['This is some jumbled text','not_case_this is some jumbled tnot_ext'],
			['All hell breaks loose in this here set of example texts','All hell breaks loose in not_this here set of not_example tnot_exts'],
			['Example of random text','Example of random tnot_ext'],
			['foobar','foobar'],
		];
	}

	/**
	 * @dataProvider notWholeWordsCaseSensitiveProvider
	 */
	function testNotWholeWordsCaseSensitive($inputText, $expected)
	{
		global $modSettings;

		$modSettings['censorWholeWord'] = false;
		$modSettings['censorIgnoreCase'] = false;

		$actual = censorText($inputText);

		$this->assertEquals($expected, $actual);
	}

	function notWholeWordsCaseInsensitiveProvider()
	{
		return[
			['This is some jumbled text','not_not_case_this is some jumbled tnot_ext'],
			['All hell breaks loose in this here set of example texts','All hell breaks loose in not_not_case_this here set of not_example tnot_exts'],
			['Example of random text','not_example of random tnot_ext'],
			['foobar','foobar'],
		];
	}

	/**
	 * @dataProvider notWholeWordsCaseInsensitiveProvider
	 */
	function testNotWholeWordsCaseInsensitive($inputText, $expected)
	{
		global $modSettings;

		$modSettings['censorWholeWord'] = false;
		$modSettings['censorIgnoreCase'] = true;
		
		$actual = censorText($inputText);

		$this->assertEquals($expected, $actual);
	}

	function setUp()
	{
		global $modSettings;

		$vulgar = array();
		$proper = array();

		foreach (array_values($this->tests) as $key => $val)
		{
			$vulgar[] = key($val);
			$proper[] = current($val);
		}

		$modSettings['censor_vulgar'] = implode("\n", $vulgar);
		$modSettings['censor_proper'] = implode("\n", $proper);
	}

	protected function setCensors($pairs)
	{
		global $modSettings;

		$vulgar = array();
		$proper = array();

		foreach ($pairs as $key => $val)
		{
			$vulgar[] = $key;
			$proper[] = $val;
		}

		$modSettings['censor_vulgar'] = implode("\n", $vulgar);
		$modSettings['censor_proper'] = implode("\n", $proper);
	}
}
