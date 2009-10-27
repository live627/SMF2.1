<?php

	class UnitTest_html_to_bbc extends UnitTest
	{
		protected $_tests = array(
			'hr_tags_1' => array(
				'name' => 'hr tags (1)',
				'description' => "Make sure that hr tags are properly handled.\n\n.",
				'input' => '<hr></hr>a<div><br></div><div><hr></hr></div>',
				'output' => "[hr]\na\n\n[hr]\n",
			),
			'tables_1' => array(
				'name' => 'tables (1)',
				'description' => "Make sure that tables are properly handled.\n\nRight aligned table cell.",
				'input' => '<table><tr><td align="right">a</td></tr></table>',
				'output' => "[table][tr][td][right]a[/right][/td][/tr][/table]",
			),
			'tables_2' => array(
				'name' => 'tables (1)',
				'description' => "Make sure that tables are properly handled.\n\n.Right aligned table cell but with no closure.",
				'input' => '<table><tr><td align="right">a</tr></table>',
				'output' => "[table][tr]a[/tr][/table]",
			),
			'img_1' => array(
				'name' => 'images (1)',
				'description' => "Make sure that images that have no full path are rewriten to contain a full path.",
				'input' => '<img src="Smileys/default/tongue.gif" alt=":P" title="Tongue" class="smiley" border="0">',
				'output' => "[img alt=:P]{baseURL}/Smileys/default/tongue.gif[/img]",
			),
			'url_1' => array(
				'name' => 'URLs (1)',
				'description' => "Make sure that URLs that have no full path are rewriten to contain a full path.",
				'input' => '<a href="test.html">test</a>',
				'output' => "[url={baseURL}/test.html]test[/url]",
			),
			'list_1' => array(
				'name' => 'Lists (1)',
				'description' => "Make sure that lists that are not closed at all are still processed.",
				'input' => '<ul class="bbc_list"><li>test',
				'output' => "[list]\n[*]test\n[/list]",
			),


		);

		public function initialize()
		{
			global $sourcedir;

			require_once($sourcedir . '/Subs-Editor.php');
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
			global $scripturl;

			if (!isset($this->_tests[$testID]))
				return 'Invalid test ID given';

			$parsedurl = parse_url($scripturl);
			$baseurl = $parsedurl['scheme'] . '://' . $parsedurl['host'] . (empty($parsedurl['port']) ? '' : ':' . $parsedurl['port']) . preg_replace('~/(?:index\\.php)?$~', '', $parsedurl['path']);

			$input = $this->_tests[$testID]['input'];
			$expected_output = strtr($this->_tests[$testID]['output'], array(
				'{baseURL}' => $baseurl,
			));



			$output = html_to_bbc($input);
			if ($output === $expected_output)
				return true;

			else
				return sprintf("Unexpected output received from legalise_bbc().\nInput: %1\$s\nExpected output: %2\$s\nReal output: %3\$s", htmlspecialchars($this->_tests[$testID]['input']), htmlspecialchars($expected_output), htmlspecialchars($output));
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