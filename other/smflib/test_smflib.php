<?php
	echo "+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
	error_reporting(E_ALL);

	require_once('../../Settings.php');

	if (empty($db_persist))
		$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);
	else
		$db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);

	// Show an error if the connection couldn't be made.
	if (!$db_connection || !@mysql_select_db($db_name, $db_connection))
		die('DB connection error');

	$id_member = 5;

	$forum_version = "SMF 1.1 Beta 3";

	if (!function_exists('smflib_init') || !smflib_init())
		die('invalid version');

	echo $forum_version, "\n";

	$conditions['allowedTo'] = array(
		0 => array(
			'not_set' => array(
				'user_info',
				'modSettings',
			),
			'set' => array(),
		),
		1 => array(
			'not_set' => array(
				'modSettings',
			),
			'set' => array(
				'user_info' => "''",
			),
		),
		2 => array(
			'not_set' => array(
			),
			'set' => array(
				'user_info' => "array('is_admin' => 1, 'permissions' => array('my_perm'), 'groups' => array('0', '1'))",
				'modSettings' => "array('permission_enable_by_board' => '0')",
			),
		),
		3 => array(
			'not_set' => array(
				'modSettings',
			),
			'set' => array(
				'user_info' => "array('is_admin' => true)"
			),
		),
		4 => array(
			'not_set' => array(
			),
			'set' => array(
				'user_info' => "array('permissions' => array('my_perm', 'post_reply_any'))",
				'modSettings' => "array('permission_enable_by_board' => '0')",
			),
		),
	);

	$tests['allowedTo'] = array(
		"allowedTo('')"							=> array(false, false,  true,  true,  true),
		"allowedTo(array())"						=> array(false, false,  true,  true,  true),
		"allowedTo('any_perm')"					=> array(false, false, false,  true, false),
		"allowedTo(array('any_perm'))"			=> array(false, false, false,  true, false),
		"allowedTo('my_perm')"					=> array(false, false,  true,  true,  true),
		"allowedTo(array('a', 'my_perm'))"		=> array(false, false,  true,  true,  true),
		"allowedTo(1)"							=> array(false, false, false, false, false),
		"allowedTo(null)"						=> array(false, false, false, false, false),
//		"allowedTo()"							=> array( null,  null,  null,  null,  null),
		"allowedTo('any_perm', array(1))"		=> array(false, false, false,  true,  true),//!!! to be determined.
		"allowedTo('post_reply_any', array(1))"	=> array(false, false, false,  true,  true),//!!! to be determined.
	);

	// Run the tests.
	foreach ($conditions as $function => $conditionArray)
	{
		foreach ($conditionArray as $conditionNr => $variables)
		{
			// Set the conditions of the global variables.
			foreach ($variables['not_set'] as $var)
				if (isset($GLOBALS[$var]))
					unset($GLOBALS[$var]);
			foreach ($variables['set'] as $var => $value)
				eval('$GLOBALS[\'' . $var . '\'] = ' . $value . ';');

			foreach ($tests[$function] as $test => $expected_results)
			{
				$results = eval('return ' . $test . ';');
				if ($results !== $expected_results[$conditionNr])
				{
					//echo 'Assumption not correct: ', $test, ' === ', var_export($expected_results[$conditionNr]), ";<br />\nTurns out to be: ", $test, ' === ', var_export($results) . ";<br />\nCondition:\n", var_dump($conditions[$function][$conditionNr]), "<br />\n";

					// There's no use showing all errors, one is bad enough.
					//exit;
					//echo '-|-';
				}
			}
		}
	}


	//echo " <br />\nDone. <br />\n=== <br />\n";
	echo "===============================================================================\n";

	function db_query($param1, $param2, $param3)
	{
		echo "-->db_query\n";
		echo $param1, ' (FILE: ', $param2, ', LINE:', $param3, ')';
		echo "\n--------\n";
		return mysql_query($param1);

	}

?>