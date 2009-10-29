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
				'output' => "[list]\n\t[li]test[/li]\n[/list]",
			),
			'list_2' => array(
				'name' => 'Lists (2)',
				'description' => "A simple example of nested lists.",
				'input' => '<ul><li>a<ul><li>b</li></ul></li></ul>',
				'output' => "[list]\n\t[li]a[list]\n\t\t[li]b[/li]\n\t[/list][/li]\n[/list]",
			),
			'list_3' => array(
				'name' => 'Lists (3)',
				'description' => "A nested list with an item just after the list definition.",
				'input' => '<ul>a<li>b<ul><li>c</li></ul></li></ul>',
				'output' => "[list]\n\t[li]a[/li]\n\t[li]b[list]\n\t\t[li]c[/li]\n\t[/list][/li]\n[/list]",
			),
			'list_4' => array(
				'name' => 'Lists (4)',
				'description' => "A nested list that has the nested list AS a list item instead of INSIDE a list item.",
				'input' => '<ul><li>a</li><ul><li>b</li></ul></ul>',
				'output' => "[list]\n\t[li]a[/li]\n\t[li][list]\n\t\t[li]b[/li]\n\t[/list][/li]\n[/list]",
			),
			'list_5' => array(
				'name' => 'Lists (5)',
				'description' => "A list that has a closing list item tag as its first item.",
				'input' => '<ul></li><li>a</li></ul>',
				'output' => "[list]\n\t[li]a[/li]\n[/list]",
			),
			'list_6' => array(
				'name' => 'Lists (6)',
				'description' => "A list that has a closing list tag as its first item.",
				'input' => '</ul><ul><li>a</li></ul>',
				'output' => "[list]\n\t[li]a[/li]\n[/list]",
			),
			'list_7' => array(
				'name' => 'Lists (7)',
				'description' => "A list that has numeric items.",
				'input' => '<ol><li>a</li></ul>',
				'output' => "[list type=decimal]\n\t[li]a[/li]\n[/list]",
			),
			'list_8' => array(
				'name' => 'Lists (8)',
				'description' => "A list that uses non-typical bullet points.",
				'input' => '<ul type="square"><li>a</li></ul>',
				'output' => "[list type=square]\n\t[li]a[/li]\n[/list]",
			),
			'list_9' => array(
				'name' => 'Lists (9)',
				'description' => "A list that isn't properly closed at the end.",
				'input' => '<ul>a',
				'output' => "[list]\n\t[li]a[/li]\n[/list]",
			),
			'list_10' => array(
				'name' => 'Lists (10)',
				'description' => "A list that isn't properly closed at the end.",
				'input' => '<ul><li><ul>a',
				'output' => "[list]\n\t[li][list]\n\t\t[li]a[/li]\n\t[/list][/li]\n[/list]",
			),
			'script_1' => array(
				'name' => 'Scripts (1)',
				'description' => "A script and everything in it should simply be removed.",
				'input' => 'a<script>b</script>c',
				'output' => "ac",
			),
			'comment_1' => array(
				'name' => 'Comments (1)',
				'description' => "Comments within the html should be removed.",
				'input' => 'a<!-- b<ul>c -->d',
				'output' => "ad",
			),
			'comment_2' => array(
				'name' => 'Comments (2)',
				'description' => "CDATA blocks within the html should be removed.",
				'input' => 'a<![CDATA[b<ul>c]]>d',
				'output' => "ad",
			),
			'style_1' => array(
				'name' => 'Style (1)',
				'description' => "A style block and everything in it should simply be removed.",
				'input' => 'a<style>b</style>c',
				'output' => "ac",
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
				return sprintf("Unexpected output received from legalise_bbc().\nInput: %1\$s\nExpected output: <pre>%2\$s</pre>\nReal output: <pre>%3\$s</pre>", htmlspecialchars($this->_tests[$testID]['input']), htmlspecialchars($expected_output), htmlspecialchars($output));
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