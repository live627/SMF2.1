<?php

	class UnitTest_parsesmileys extends UnitTest
	{
		protected $_tests = array(
			'standard_1' => array(
				'name' => 'Standard smiley parsing (1)',
				'description' => "Make sure that a default smiley is parsed. Smiley in between text.",
				'input' => 'abc :) def',
				'output' => 'abc <img src="{smiley_url}/smiley.gif" alt="&#58;&#41;" title="Smiley" border="0" class="smiley" /> def',
			),
			'standard_2' => array(
				'name' => 'Standard smiley parsing (2)',
				'description' => "Make sure that a default smiley is parsed. Smiley at the end of text.",
				'input' => 'abc :)',
				'output' => 'abc <img src="{smiley_url}/smiley.gif" alt="&#58;&#41;" title="Smiley" border="0" class="smiley" />',
			),
			'standard_3' => array(
				'name' => 'Standard smiley parsing (3)',
				'description' => "Make sure that a default smiley is parsed. Smiley at the begin of text.",
				'input' => ':) def',
				'output' => '<img src="{smiley_url}/smiley.gif" alt="&#58;&#41;" title="Smiley" border="0" class="smiley" /> def',
			),
			'standard_4' => array(
				'name' => 'Standard smiley parsing (4)',
				'description' => "Make sure that a default smiley is parsed. Smiley without proper spacing.",
				'input' => 'abc:)def',
				'output' => 'abc:)def',
			),

			'collision_1' => array(
				'name' => 'Colliding smiley parsing (1)',
				'description' => "Make sure that a default smiley is parsed. Bigger smiley that can be part of the smaller smiley code.",
				'input' => 'test test <3 test',
				'output' => '<img src="{smiley_url}/laugh.gif" alt="test test &lt;3 test" title="&#091;UnitTest] 2" border="0" class="smiley" />',
			),
			'collision_2' => array(
				'name' => 'Colliding smiley parsing (2)',
				'description' => "Make sure that a default smiley is parsed. BBC may not be touched...usually.",
				'input' => '[url=mailto:David@bla.com]',
				'output' => '[url=mailto:David@bla.com]',
			),
		);

		public function initialize()
		{
			global $sourcedir, $smcFunc;

			require_once($sourcedir . '/Subs.php');

			$smileys = array(
				1 => array(
					'code' => 'test <3',
					'file' => 'angel.gif',
				),
				2 => array(
					'code' => 'test test <3 test',
					'file' => 'laugh.gif',
				),
			);

			foreach ($smileys as $index => $smileyInfo)
			{
				$request = $smcFunc['db_query']('', '
					SELECT id_smiley
					FROM {db_prefix}smileys
					WHERE description = {text:unit_test}',
					array(
						'unit_test' => '[UnitTest] ' . $index,
					)
				);

				if ($smcFunc['db_num_rows']($request) === 0)
				{
					$smcFunc['db_insert']('',
						'{db_prefix}smileys',
						array(
							'code' => 'string',
							'filename' => 'string',
							'description' => 'string',
							'smiley_row' => 'int',
							'smiley_order' => 'int',
							'hidden' => 'int',
						),
						array(
							$smileyInfo['code'],
							$smileyInfo['file'],
							'[UnitTest] ' . $index,
							0,
							0,
							1,
						),
						array('id_smiley')
					);
				}

				$smcFunc['db_free_result']($request);
			}
		}

		public function getTests()
		{
			$tests = array();
			foreach ($this->_tests as $testID => $testInfo)
				$tests[$testID] = array(
					'name' => $testInfo['name'],
					'description' => $testInfo['description'],
				);

			return $tests;
		}

		public function doTest($testID)
		{
			global $modSettings, $user_info;

			if (!isset($this->_tests[$testID]))
				return 'Invalid test ID given';

			$this->_tests[$testID]['output'] = strtr($this->_tests[$testID]['output'], array('{smiley_url}' => $modSettings['smileys_url'] . '/' . $user_info['smiley_set']));
			$message = $this->_tests[$testID]['input'];
			parsesmileys($message);
			if ($message === $this->_tests[$testID]['output'])
				return true;

			else
				return sprintf("Unexpected output received from parsesmileys().\n\nInput:\n%1\$s\n\nExpected output:\n%2\$s\n\nReal output:\n%3\$s", htmlspecialchars($this->_tests[$testID]['input']), htmlspecialchars($this->_tests[$testID]['output']), htmlspecialchars($message));
		}

		public function getTestDescription($testID)
		{
			if (isset($this->_tests[$testID]['description']))
				return $this->_tests[$testID]['description'];
			elseif (isset($this->_tests[$testID]))
				return 'No description available';
			else
				return 'Invalid test ID given';

		}
	}
?>