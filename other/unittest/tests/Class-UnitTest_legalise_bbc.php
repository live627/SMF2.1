<?php

	class UnitTest_legalise_bbc extends UnitTest
	{
		protected $_tests = array(
			'alignment_1' => array(
				'name' => 'Alignment exclusivity (1)',
				'description' => "Make sure that there\'s only one alignment tag open at a time.\n\nTwo properly nested tags that need to be reduced to one.",
				'input' => '[left][right]x[/right][/left]',
				'output' => '[left]x[/left]',
			),
			'alignment_2' => array(
				'name' => 'Alignment exclusivity (2)',
				'description' => "Make sure that there's only one alignment tag open at a time.\n\nTesting incorrectly nested tags.",
				'input' => '[left][right]x[/left][/right]',
				'output' => '[left]x[/left]',
			),
			'alignment_3' => array(
				'name' => 'Alignment exclusivity (3)',
				'description' => "Make sure that there's only one alignment tag open at a time.\n\nTesting tags that haven't be closed.",
				'input' => '[left][right]x',
				'output' => '[left]x[/left]',
			),
			'alignment_4' => array(
				'name' => 'Alignment exclusivity (4)',
				'description' => "Make sure that there's only one alignment tag open at a time.\n\nUnexpected closing tag.",
				'input' => 'x[/left]',
				'output' => 'x',
			),
			'alignment_5' => array(
				'name' => 'Alignment exclusivity (5)',
				'description' => "Make sure that there's only one alignment tag open at a time.\n\nLike 'Alignment exclusivity (1)', but including text outside the both the inner and outer tags.",
				'input' => 'a[left]b[right]c[/right]d[/left]e',
				'output' => 'a[left]bcd[/left]e',
			),
			'alignment_6' => array(
				'name' => 'Alignment exclusivity (6)',
				'description' => "Make sure that there's only one alignment tag open at a time.\n\nRemove empty aligning tags.",
				'input' => 'a[left]b[/left]c[right][/right]d',
				'output' => 'a[left]b[/left]cd',
			),
			
			'block_vs_inline_1' => array(
				'name' => 'Block vs. inline (1)',
				'description' => "Make sure that block tags don't intersect inline tags.\n\nClose inline tags before opening block tags, reopen them inside the block and reopen them after the block.",
				'input' => 'a[b]b[quote]c[/quote]d[/b]e',
				'output' => 'a[b]b[/b][quote][b]c[/b][/quote][b]d[/b]e',
			),
			'block_vs_inline_2' => array(
				'name' => 'Block vs. inline (2)',
				'description' => "Make sure that block tags don't intersect inline tags.\n\nTest not closing the block tag.",
				'input' => 'a[b]b[quote]c[/b]d',
				'output' => 'a[b]b[/b][quote][b]c[/b]d[/quote]',
			),
			'block_vs_inline_3' => array(
				'name' => 'Block vs. inline (3)',
				'description' => "Make sure that block tags don't intersect inline tags.\n\nTest not closing an inline tag.",
				'input' => 'a[b]b[quote]c[/quote]d',
				'output' => 'a[b]b[/b][quote][b]c[/b][/quote][b]d[/b]',
			),
			'block_vs_inline_4' => array(
				'name' => 'Block vs. inline (4)',
				'description' => "Make sure that block tags don't intersect inline tags.\n\nMultiple block tags that need to be closed in the proper order.",
				'input' => 'a[b][i]b[quote]c[/quote]d[/i][/b]e',
				'output' => 'a[b][i]b[/i][/b][quote][b][i]c[/i][/b][/quote][b][i]d[/i][/b]e',
			),
			'block_vs_inline_5' => array(
				'name' => 'Block vs. inline (5)',
				'description' => "Make sure that block tags don't intersect inline tags.\n\nMultiple layer inline/block tags.",
				'input' => 'a[b]b[quote]c[i]d[quote]e[/quote]f[/i]g[/quote]h[/b]i',
				'output' => 'a[b]b[/b][quote][b]c[i]d[/i][/b][quote][b][i]e[/i][/b][/quote][b][i]f[/i]g[/b][/quote][b]h[/b]i',
			),
			'nobbc_and_code_blocks_1' => array(
				'name' => 'Nobbc and code blocks (1)',
				'description' => "Make sure that code and nobbc blocks are respected.\n\Tags inbetween nobbc tags should be ignored.",
				'input' => 'a[quote]b[nobbc]c[/quote]d[/nobbc]e[/quote]f',
				'output' => 'a[quote]b[nobbc]c[/quote]d[/nobbc]e[/quote]f',
			),
			'nobbc_and_code_blocks_2' => array(
				'name' => 'Nobbc and code blocks (2)',
				'description' => "Make sure that code and nobbc blocks are respected.\n\nTags inbetween code tags should be ignored.",
				'input' => 'a[quote]b[code]c[/quote]d[/code]e[/quote]f',
				'output' => 'a[quote]b[code]c[/quote]d[/code]e[/quote]f',
			),
			'nobbc_and_code_blocks_3' => array(
				'name' => 'Nobbc and code blocks (3)',
				'description' => "Make sure that code and nobbc blocks are respected.\n\nIntertwining code and nobbc tags.",
				'input' => 'a[nobbc]b[code]c[/code]d[/code]e[code]f[/nobbc]g a[code]b[nobbc]c[/nobbc]d[/nobbc]e[nobbc]f[/code]g',
				'output' => 'a[nobbc]b[code]c[/code]d[/code]e[code]f[/nobbc]g a[code]b[nobbc]c[/nobbc]d[/nobbc]e[nobbc]f[/code]g',
			),
			'nobbc_and_code_blocks_4' => array(
				'name' => 'Nobbc and code blocks (4)',
				'description' => "Make sure that code and nobbc blocks are respected.\n\nNot closing a code block.",
				'input' => 'a[code]b[quote]c',
				'output' => 'a[code]b[quote]c[/code]',
			),
			'nobbc_and_code_blocks_5' => array(
				'name' => 'Nobbc and code blocks (5)',
				'description' => "Make sure that code and nobbc blocks are respected.\n\nNot closing a nobbc block as well as a few inline tags.",
				'input' => 'a[b]b[i]c[nobbc]d[quote]e',
				'output' => 'a[b]b[i]c[/i][/b][nobbc]d[quote]e[/nobbc]',
			),
			'nobbc_and_code_blocks_6' => array(
				'name' => 'Nobbc and code blocks (6)',
				'description' => "Make sure that code and nobbc blocks are respected.\n\nClosing a code block but leaving inline tags opened.",
				'input' => 'a[b]b[i]c[code]d[quote]e[/code]f',
				'output' => 'a[b]b[i]c[/i][/b][code]d[quote]e[/code][b][i]f[/i][/b]',
			),
			'large_chunks_1' => array(
				'name' => 'Large chunks of data (1)',
				'description' => "Make sure that large chunks of text don't crash Apache.\n\nJust some random data [bug 2181].",
				'input' => '(randomly generated)',
				'output' => '',
			),
			'incomplete_tags_1' => array(
				'name' => 'Incomplete tags (1)',
				'description' => "Make sure that crippled tags are ignored.\n\nIncomplete close tag.",
				'input' => '[quote]a[/quote',
				'output' => '[quote]a[/quote[/quote]',
			),
			'tags_with_content_1' => array(
				'name' => 'Tags with properties (1)',
				'description' => "Make sure that tags with content are properly handled.\n\nStandard quote tag.",
				'input' => 'a[i]b[quote author=Compuart link=topic=245544.msg1586074#msg1586074 date=1214018832]c[/quote]d[/i]e',
				'output' => 'a[i]b[/i][quote author=Compuart link=topic=245544.msg1586074#msg1586074 date=1214018832][i]c[/i][/quote][i]d[/i]e',
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
				
			// Special tests.
			switch ($testID)
			{
				case 'large_chunks_1':
					
					$this->_tests[$testID]['input'] = '';
					
					// Generate 500,000 random characters.
					for ($i = 0; $i < 50000; $i++)
						$this->_tests[$testID]['input'] .= pack('C*', rand(32, 126), rand(32, 126), rand(32, 126), rand(32, 126), rand(32, 126), rand(32, 126), rand(32, 126), rand(32, 126), rand(32, 126), rand(32, 126));
						
					// Remove closing square brackets.
					$this->_tests[$testID]['input'] = strtr($this->_tests[$testID]['input'], array(']' => ''));
						
					$this->_tests[$testID]['output'] = $this->_tests[$testID]['input'];
				break;	
			}
				
			$output = legalise_bbc($this->_tests[$testID]['input']);
			if ($output === $this->_tests[$testID]['output'])
				return true;
				
			else
				return sprintf("Unexpected output received from legalise_bbc().\nInput: %1\$s\nExpected output: %2\$s\nReal output: %3\$s", $this->_tests[$testID]['input'], $this->_tests[$testID]['output'], $output);
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