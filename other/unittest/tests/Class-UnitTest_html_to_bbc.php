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
			if (!isset($this->_tests[$testID]))
				return 'Invalid test ID given';
				
			$output = html_to_bbc($this->_tests[$testID]['input']);
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