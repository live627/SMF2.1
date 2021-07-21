<?php
namespace PHPTDD;

class CensoringTest extends BaseTestCase
{
	protected $tests = [
		'this' => ['this' => 'not_this'],
		'This' => ['This' => 'not_case_this'],
		'ex' => ['ex' => 'not_ex'],
	];

	/**
	 * @return string[][]
	 */
	public function keepCaseWholeWordProvider(): array
	{
		return [
			['This is some jumbled text', 'not_case_this is some jumbled text'],
			['Example of random text', 'Example of random text'],
			['foobar', 'foobar'],
		];
	}

	/**
	 * @dataProvider keepCaseWholeWordProvider
	 *
	 * @return void
	 */
	public function testWholeWordsCaseSensitive($inputText, $expected): void
	{
		global $modSettings;

		$modSettings['censorWholeWord'] = true;
		$modSettings['censorIgnoreCase'] = false;

		$actual = censorText($inputText);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @return string[][]
	 */
	public function wholeWordsCaseInsensitiveProvider(): array
	{
		return [
			['This is some jumbled text', 'not_this is some jumbled text'],
			['Example of random text', 'Example of random text'],
			['foobar', 'foobar'],
		];
	}

	/**
	 * @dataProvider wholeWordsCaseInsensitiveProvider
	 *
	 * @return void
	 */
	public function testWholeWordsCaseInsensitive($inputText, $expected): void
	{
		global $modSettings;

		$modSettings['censorWholeWord'] = true;
		$modSettings['censorIgnoreCase'] = true;

		$actual = censorText($inputText);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @return string[][]
	 */
	public function notWholeWordsCaseSensitiveProvider(): array
	{
		return [
			['This is some jumbled text', 'not_case_this is some jumbled tnot_ext'],
			[
				'All hell breaks loose in this here set of example texts',
				'All hell breaks loose in not_this here set of not_example tnot_exts',
			],
			['Example of random text', 'Example of random tnot_ext'],
			['foobar', 'foobar'],
		];
	}

	/**
	 * @dataProvider notWholeWordsCaseSensitiveProvider
	 *
	 * @return void
	 */
	public function testNotWholeWordsCaseSensitive($inputText, $expected): void
	{
		global $modSettings;

		$modSettings['censorWholeWord'] = false;
		$modSettings['censorIgnoreCase'] = false;

		$actual = censorText($inputText);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @return string[][]
	 */
	public function notWholeWordsCaseInsensitiveProvider(): array
	{
		return [
			['This is some jumbled text', 'not_not_case_this is some jumbled tnot_ext'],
			[
				'All hell breaks loose in this here set of example texts',
				'All hell breaks loose in not_not_case_this here set of not_example tnot_exts',
			],
			['Example of random text', 'not_example of random tnot_ext'],
			['foobar', 'foobar'],
		];
	}

	/**
	 * @dataProvider notWholeWordsCaseInsensitiveProvider
	 *
	 * @return void
	 */
	public function testNotWholeWordsCaseInsensitive($inputText, $expected): void
	{
		global $modSettings;

		$modSettings['censorWholeWord'] = false;
		$modSettings['censorIgnoreCase'] = true;

		$actual = censorText($inputText);

		$this->assertEquals($expected, $actual);
	}

	public function setUp() : void
	{
		global $modSettings;

		$vulgar = [];
		$proper = [];

		foreach (array_values($this->tests) as $key => $val)
		{
			$vulgar[] = key($val);
			$proper[] = current($val);
		}

		$modSettings['censor_vulgar'] = implode("\n", $vulgar);
		$modSettings['censor_proper'] = implode("\n", $proper);
	}

	protected function setCensors($pairs): void
	{
		global $modSettings;

		$vulgar = [];
		$proper = [];

		foreach ($pairs as $key => $val)
		{
			$vulgar[] = $key;
			$proper[] = $val;
		}

		$modSettings['censor_vulgar'] = implode("\n", $vulgar);
		$modSettings['censor_proper'] = implode("\n", $proper);
	}
}
