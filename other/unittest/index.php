<?php

	// Settings
	$testDir = './tests';
	$templateDir = './templates';
	$jsURL = './templates/scripts';

	require_once('../../SSI.php');

	$context['template_layers'] = array('html');
	$context['ut_js_url'] = $jsURL;
	$settings['template_dirs'] = array_merge($settings['template_dirs'], array($templateDir));
	loadTemplate('UnitTest');

	$subAction = isset($_GET['sa']) && function_exists('do_' . $_GET['sa']) ? 'do_' . $_GET['sa'] : 'do_index';

	$subAction();

	obExit(true);

	function do_index()
	{
		global $testDir, $context;

		require_once($testDir . '/Class-UnitTest.php');

		$tests = getAllTests();

		$context['tests'] = array();
		foreach ($tests as $testIndex => $test)
		{
			$context_subTests = array();
			foreach ($test['sub_tests'] as $subTestID => $subTest)
				$context_subTests[] = array(
					'id' => $subTestID,
					'name' => jsEscape($subTest['name'], 'string'),
					'description' => jsEscape($subTest['description'], 'string'),
				);
			$context_subTests[count($context_subTests) - 1]['is_last'] = true;

			$context['tests'][] = array(
				'id' => $test['id'],
				'isMultiThreadSafe' => $test['isMultiThreadSafe'],
				'sub_tests' => $context_subTests,
			);

		}
		$context['tests'][count($context['tests']) - 1]['isLast'] = true;

		$context['sub_template'] = 'ut_index';
	}

	function do_test()
	{
		global $context, $testDir;

		require_once($testDir . '/Class-UnitTest.php');

		$tests = array();

		// Determine which test(s).
		if (isset($_REQUEST['test']))
		{
			$className = 'UnitTest_' . $_REQUEST['test'];
			if (!file_exists($testDir . '/Class-' . $className . '.php'))
				trigger_error('Test not found', E_USER_ERROR);

			require_once($testDir . '/Class-' . $className . '.php');
			$classInstance = new $className();

			$availableSubTests = array_keys($classInstance->getTests());

			// Determine the sub tests.
			if (isset($_REQUEST['subtest']))
			{
				$subTest = $_REQUEST['subtest'];

				if (!in_array($subTest, $availableSubTests))
					trigger_error('Sub test not found', E_USER_ERROR);

				$subTests = array($subTest);
			}

			// With no sub test set, default to all sub tests.
			else
				$subTests = $availableSubTests;

			// Just do this one test.
			$tests[] = array(
				'id' => substr($className, 9),
				'sub_tests' => $subTests,
			);
		}

		// With no test set, default to all tests.
		else
		{
			$allTests = getAllTests();

			// We're only interested in the ID's.
			foreach ($allTests as $test)
				$tests[] = array(
					'id' => $test['id'],
					'sub_tests' => array_keys($test['sub_tests']),
				);
		}

		// Now let's do the tests shall we?
		$testResults = array();
		foreach ($tests as $test)
		{
			$className = 'UnitTest_' . $test['id'];
			$classInstance = new $className;
			$classInstance->initialize();

			foreach ($test['sub_tests'] as $subTestId)
			{
				$myResult = $classInstance->doTest($subTestId);
				$testResults[] = array(
					'test_id' => $test['id'],
					'sub_test_id' => $subTestId,
					'passed' => $myResult === true,
					'error_msg' => $myResult === true ? '' : htmlspecialchars($myResult),
				);
			}
		}

		$context['test_results'] = $testResults;

		$context['template_layers'] = array();
		$context['sub_template'] = 'ut_test_results';
	}

	function getAllTests()
	{
		global $testDir;

		require_once($testDir . '/Class-UnitTest.php');

		$tests = array();

		$testDirHandle = dir($testDir);
		while ($fileName = $testDirHandle->read())
		{
			if (substr($fileName, 0, 1) === '.' || $fileName === 'Class-UnitTest.php' || substr($fileName, -4) !== '.php')
				continue;

			require_once($testDir . '/' . $fileName);

			// Chop off 'Class-' and '.php'.
			$className = substr($fileName, 6, -4);

			// Create an instance of this class.
			$classInstance = new $className();

			// Add this test to the list, including its sub tests.
			$tests[] = array(
				'id' => substr($className, 9),
				'isMultiThreadSafe' => $classInstance->isMultiThreadSafe,
				'sub_tests' => $classInstance->getTests(),
			);
		}

		return $tests;
	}

	function jsEscape($string, $type = 'inline')
	{
		switch ($type)
		{
			case 'inline':
				return htmlspecialchars(strtr($string, array('\\' => '\\\\', "\r" => '', "\t" => '', "\n" => ' ')));
			break;

			case 'code_block':
				return strtr($string, array("\r" => '', '<script>' => '<\\\' + \\\'script>', '</' => '<\\\' + \\\'/'));
			break;

			case 'string':
				return strtr($string, array('\'' => '\\\'', '\\' => '\\\\', "\n" => '\\n', "\r" => '', '<script>' => '<\\\' + \\\'script>', '</' => '<\\\' + \\\'/'));
			break;
		}
	}

?>