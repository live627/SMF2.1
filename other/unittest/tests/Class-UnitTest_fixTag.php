<?php

	class UnitTest_fixTag extends UnitTest
	{
		protected $_tests = array(
			'url_tags_1' => array(
				'name' => 'url tags (1)',
				'description' => "Make sure that url tags don't allow foreign protocols like JavaScript.\n\n.",
				'input' => "[url=javascript:bla]a[/url]",
				'param' => 'url',
				'protocols' => array('http', 'https'),
				'embeddedURL' => true,
				'hasEqualSign' => true,
				'hasExtra' => false,
				'output' => "[url=http://javascript:bla]a[/url]",
			),
			'url_tags_2' => array(
				'name' => 'url tags (2)',
				'description' => "Make sure that url tags that aren't closed don't work.\n\n.",
				'input' => "[url=javascript:bla]a",
				'param' => 'url',
				'protocols' => array('http', 'https'),
				'embeddedURL' => true,
				'hasEqualSign' => true,
				'hasExtra' => false,
				'output' => "[url=http://javascript:bla]a",
			),
			'url_tags_3' => array(
				'name' => 'url tags (3)',
				'description' => "Make sure that url tags that aren't closed don't work.\n\n.",
				'input' => "[url=javascript:bla]",
				'param' => 'url',
				'protocols' => array('http', 'https'),
				'embeddedURL' => true,
				'hasEqualSign' => true,
				'hasExtra' => false,
				'output' => "[url=http://javascript:bla]",
			),
			'url_tags_4' => array(
				'name' => 'url tags (4)',
				'description' => "Make sure that empty url tags are properly parsed.\n\n.",
				'input' => "[url=javascript:bla][/url]",
				'param' => 'url',
				'protocols' => array('http', 'https'),
				'embeddedURL' => true,
				'hasEqualSign' => true,
				'hasExtra' => false,
				'output' => "[url=http://javascript:bla][/url]",
			),
			'url_tags_5' => array(
				'name' => 'url tags (5)',
				'description' => "Make sure that double url tags are properly parsed.\n\n.",
				'input' => "[url=javascript:bla][/url][/url]",
				'param' => 'url',
				'protocols' => array('http', 'https'),
				'embeddedURL' => true,
				'hasEqualSign' => true,
				'hasExtra' => false,
				'output' => "[url=http://javascript:bla][/url][/url]",
			),
			'url_tags_6' => array(
				'name' => 'url tags (6)',
				'description' => "Make sure that double url tags are properly parsed.\n\n.",
				'input' => "[url]javascript:bla[/url][/url]",
				'param' => 'url',
				'protocols' => array('http', 'https'),
				'embeddedURL' => true,
				'hasEqualSign' => false,
				'hasExtra' => false,
				'output' => "[url=http://javascript:bla]javascript:bla[/url][/url]",
			),
			'url_tags_7' => array(
				'name' => 'url tags (7)',
				'description' => "Make sure that URL's that aren't properly closed are still properly parsed.\n\n.",
				'input' => "[url]javascript:bla",
				'param' => 'url',
				'protocols' => array('http', 'https'),
				'embeddedURL' => true,
				'hasEqualSign' => false,
				'hasExtra' => false,
				'output' => "[url]javascript:bla",
			),
			'url_tags_8' => array(
				'name' => 'url tags (8)',
				'description' => "Just a regular URL.\n\n.",
				'input' => "[url]javascript:bla[/url]",
				'param' => 'url',
				'protocols' => array('http', 'https'),
				'embeddedURL' => true,
				'hasEqualSign' => false,
				'hasExtra' => false,
				'output' => "[url=http://javascript:bla]javascript:bla[/url]",
			),
			'img_tags_1' => array(
				'name' => 'img tags (1)',
				'description' => "Just a regular IMG.\n\n.",
				'input' => "[img]javascript:bla[/img]",
				'param' => 'img',
				'protocols' => array('http', 'https'),
				'embeddedURL' => false,
				'hasEqualSign' => false,
				'hasExtra' => true,
				'output' => "[img]http://javascript:bla[/img]",
			),
			'img_tags_2' => array(
				'name' => 'img tags (2)',
				'description' => "Just a regular IMG but now with attributes.\n\n.",
				'input' => "[img width=1]javascript:bla[/img]",
				'param' => 'img',
				'protocols' => array('http', 'https'),
				'embeddedURL' => false,
				'hasEqualSign' => false,
				'hasExtra' => true,
				'output' => "[img width=1]http://javascript:bla[/img]",
			),
			'img_tags_3' => array(
				'name' => 'img tags (3)',
				'description' => "Images with embedded tags.\n\n.",
				'input' => "[img width=1][url]javascript:bla[/url][/img]",
				'param' => 'img',
				'protocols' => array('http', 'https'),
				'embeddedURL' => false,
				'hasEqualSign' => false,
				'hasExtra' => true,
				'output' => "[img width=1]http://[url]javascript:bla[/url][/img]",
			),
		);
		
		public function initialize()
		{
			global $sourcedir;
			
			require_once($sourcedir . '/Subs-Post.php');
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
			if (!isset($this->_tests[$testID]))
				return 'Invalid test ID given';
				
			$output = $this->_tests[$testID]['input'];
			fixTag($output, $this->_tests[$testID]['param'], $this->_tests[$testID]['protocols'], $this->_tests[$testID]['embeddedURL'], $this->_tests[$testID]['hasEqualSign'], $this->_tests[$testID]['hasExtra']);
			if ($output === $this->_tests[$testID]['output'])
				return true;
				
			else
				return sprintf("Unexpected output received from legalise_bbc().\nInput: %1\$s\nExpected output: %2\$s\nReal output: %3\$s", htmlspecialchars($this->_tests[$testID]['input']), htmlspecialchars($this->_tests[$testID]['output']), htmlspecialchars($output));
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