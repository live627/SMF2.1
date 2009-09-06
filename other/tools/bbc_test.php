<?php
/**********************************************************************************
* bbc_test.php                                                                    *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 RC2                                         *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/


initialize_inputs();
define_testcases();

show_header();

if (function_exists('action_' . $_GET['a']))
	call_user_func('action_' . $_GET['a']);
else
	call_user_func('action_splash');

show_footer();

function initialize_inputs()
{
	// Turn off magic quotes runtime and enable error reporting.
	if (function_exists('set_magic_quotes_runtime'))
		@set_magic_quotes_runtime(0);
	error_reporting(E_ALL);

	// Add slashes, as long as they aren't already being added.
	if (function_exists('get_magic_quotes_gpc') && @get_magic_quotes_gpc() == 0)
		foreach ($_POST as $k => $v)
			$_POST[$k] = addslashes($v);

	$_GET['a'] = (string) @$_GET['a'];
	$GLOBALS['this_url'] = 'http://' . (empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST']) . $_SERVER['PHP_SELF'];

	$possible = array(
		dirname(__FILE__),
		dirname(dirname(__FILE__)),
		dirname(dirname(dirname(__FILE__))),
		dirname(__FILE__) . '/forum',
		dirname(__FILE__) . '/forums',
		dirname(__FILE__) . '/community',
		dirname(dirname(__FILE__)) . '/forum',
		dirname(dirname(__FILE__)) . '/forums',
		dirname(dirname(__FILE__)) . '/community',
	);

	foreach ($possible as $dir)
		if (file_exists($dir . '/SSI.php'))
			break;

	require_once($dir . '/SSI.php');
}

function define_testcases()
{
	global $testcases;

	$testcases = array(
		'link01' => array(
			'desc' => 'autolinks inside of links',
			'text' => '[url=http://www.google.com/]test www.simplemachines.org test[/url]',
			'preparsed_check' => '~' . preg_quote('[url=http://www.google.com/]test www.simplemachines.org test[/url]', '~') . '~',
			'parsed_check' => '~<a href="http://www.google.com/"[^>]*>test www.simplemachines.org test</a>~',
		),
		'link02' => array(
			'desc' => 'links inside links',
			'text' => '[url=http://www.google.com/]this url has [email=unknown@simplemachines.org]an email[/email] and [url=http://www.yahoo.com]another URL[/url] in it![/url]',
			'preparsed_check' => '~' . preg_quote('[url=http://www.google.com/]this url has [email=unknown@simplemachines.org]an email[/email] and [url=http://www.yahoo.com]another URL[/url] in it![/url]', '~') . '~',
			'parsed_check' => '~<a href="http://www.google.com/"[^>]*>this url has [^ <>"]*an email[^ <>"]* and [^ <>"]*another URL</a> in it!~',
		),
		'link03' => array(
			'desc' => 'just a link',
			'text' => 'http://www.google.com/',
			'preparsed_check' => '~' . preg_quote('http://www.google.com/', '~') . '~',
			'parsed_check' => '~<a href="http://www.google.com/"[^>]*>http://www.google.com/</a>~',
		),

		'list01' => array(
			'desc' => 'lists inside of lists',
			'text' => '[list]
	[li]item[/li]
	[li][list]
		[li]sub item[/li]
	[/list][/li]
	[li]item[/li]
[/list]',
			'preparsed_check' => '~' . preg_quote('[list]
	[li]item[/li]
	[li][list]
		[li]sub item[/li]
	[/list][/li]
	[li]item[/li]
[/list]', '~') . '~',
			'parsed_check' => '~<ul[^>]*><li[^>]*>item</li><li[^>]*><ul[^>]*><li[^>]*>sub item</li></ul></li><li[^>]*>item</li></ul>~',
		),
		'list02' => array(
			'desc' => 'lazy [li] lists',
			'text' => '[li]test[li]test',
			'preparsed_check' => '~' . preg_quote('[list][li]test[/li][li]test[/li][/list]', '~') . '~',
			'parsed_check' => '~<ul[^>]*><li[^>]*>test</li><li[^>]*>test</li></ul>~',
		),
		'list03' => array(
			'desc' => 'lazy [*] lists',
			'text' => '[*]test[*]test[*]test',
			'preparsed_check' => '~' . preg_quote('[*]test[*]test[*]test', '~') . '~',
			'parsed_check' => '~<ul[^>]*><li[^>]*>test</li><li[^>]*>test</li><li[^>]*>test</li></ul>~',
		),

		'table01' => array(
			'desc' => 'tables inside of tables',
			'text' => '[table][tr][td][table][tr][td]test[/td][/tr][/table][/td][/tr][/table]',
			'preparsed_check' => '~' . preg_quote('[table][tr][td][table][tr][td]test[/td][/tr][/table][/td][/tr][/table]', '~') . '~',
			'parsed_check' => '~<table[^>]*><tr[^>]*><td[^>]*><table[^>]*><tr[^>]*><td[^>]*>test</td></tr></table></td></tr></table>~',
		),
		'table02' => array(
			'desc' => 'lazy [td] tables',
			'text' => '[td]test[/td]',
			'preparsed_check' => '~' . preg_quote('[table][tr][td]test[/td][/tr][/table]', '~') . '~',
			'parsed_check' => '~<table[^>]*><tr[^>]*><td[^>]*>test</td></tr></table>~',
		),
		'table03' => array(
			'desc' => 'no [tr] tables',
			'text' => '[table][td]test[/td][td]test2[/td][/table]',
			'preparsed_check' => '~' . preg_quote('[table][tr][td]test[/td][td]test2[/td][/tr][/table]', '~') . '~',
			'parsed_check' => '~<table[^>]*><tr[^>]*><td[^>]*>test</td><td[^>]*>test2</td></tr></table>~',
		),

		'quote01' => array(
			'desc' => 'YaBB SE quotes',
			'text' => '[quote author=name link=board=123;threadid=1234;start=30#12345 date=1019005435]test[/quote]',
			'preparsed_check' => '~' . preg_quote('[quote author=name link=board=123;threadid=1234;start=30#12345 date=1019005435]test[/quote]', '~') . '~',
			'parsed_check' => '~<div class="quoteheader"><a href=".+?/index.php\?threadid=1234;start=30#12345">.+?name.+?</a></div><div class="quote">test</div>~',
		),

		'smileys01' => array(
			'desc' => 'Embarrassed smiley skip',
			'text' => ':-[ :)',
			'preparsed_check' => '~' . preg_quote(':-[ :)', '~') . '~',
			'parsed_check' => '~<img [^>]+> <img [^>]+>~',
		),
	);
}

function run_test($test)
{
	global $sourcedir, $modSettings;

	require_once($sourcedir . '/Subs-Post.php');

	$modSettings['cache_enable'] = false;
	$test['text'] = strtr($test['text'], array("\r" => ''));

	$results = array();

	if (isset($test['preparsed_check']))
	{
		$results['preparsed'] = $test['text'];
		preparsecode($results['preparsed']);
		$results['preparsed'] = strtr($results['preparsed'], array('<br />' => "\n"));

		$results['preparsed_good'] = preg_match(strtr($test['preparsed_check'], array("\r" => '')), $results['preparsed']) != 0;
	}
	else
		$results['preparsed_good'] = null;

	if (isset($test['parsed_check']))
	{
		$preparsed = $test['text'];
		preparsecode($preparsed);

		$results['parsed'] = parse_bbc($preparsed, empty($test['no_smileys']));
		$results['parsed_good'] = preg_match(strtr($test['parsed_check'], array("\r" => '')), $results['parsed']) != 0;
	}
	else
		$results['parsed_good'] = null;

	$results['times'] = 200;

	$temp = $test['text'];

	$st = microtime();
	for ($i = 0; $i < $results['times']; $i++)
		preparsecode($temp);
	$results['preparse_time'] = array_sum(explode(' ', microtime())) - array_sum(explode(' ', $st));

	$st = microtime();
	for ($i = 0; $i < $results['times']; $i++)
		parse_bbc($temp, empty($test['no_smileys']));
	$results['parse_time'] = array_sum(explode(' ', microtime())) - array_sum(explode(' ', $st));

	return $results;
}

function action_splash()
{
	global $testcases;

	echo '
		<div class="panel">
			<h2>Testcases</h2>

			The following testcases are currently available:

			<ul>';

	foreach ($testcases as $id => $test)
		echo '
				<li><a href="', $_SERVER['PHP_SELF'], '?a=test&amp;t=', $id, '">', $id, ': ', $test['desc'], '</a></li>';

	echo '
			</ul>
		</div>';
}

function action_test()
{
	global $testcases;

	if (!isset($testcases[$_GET['t']]))
		return false;

	$results = run_test($testcases[$_GET['t']]);

	echo '
		<div class="panel">
			<h2>Testcase Results</h2>

			The following code:

			<div class="code"><pre style="margin: 0;">', htmlspecialchars($testcases[$_GET['t']]['text']), '</pre></div>

			<div style="margin-bottom: 2ex;">Took <strong>', round($results['parse_time'], 5), '</strong> seconds to parse ', $results['times'], ' times, and <strong>', round($results['preparse_time'], 5), '</strong> seconds to preparse ', $results['times'], ' times.</div>';

	if ($results['preparsed_good'] === true)
	{
		echo '
			<span style="color: #060;">It was preparsed to the following, as expected:</span>

			<div class="code" style="border: 2px dashed #2b2; margin-bottom: 2ex;"><pre style="margin: 0;">', htmlspecialchars($results['preparsed']), '</pre></div>';
	}
	elseif ($results['preparsed_good'] === false)
	{
		echo '
			<span style="color: #c00;">It was unexpectedly preparsed as the following:</span>

			<div class="code" style="border: 2px dashed #c22; margin-bottom: 2ex;"><pre style="margin: 0;">', htmlspecialchars($results['preparsed']), '</pre></div>';
	}

	if ($results['parsed_good'] === true)
	{
		echo '
			<span style="color: #060;">And, it parsed into the following, as expected:</span>

			<div class="code" style="border: 2px dashed #2b2; margin-bottom: 2ex;">', $results['parsed'], '</div>';
	}
	elseif ($results['parsed_good'] === false)
	{
		echo '
			<span style="color: #c00;">And, it parsed, unexpectedly, into the following:</span>

			<div class="code" style="border: 2px dashed #c22; margin-bottom: 2ex;">', $results['parsed'], '</div>';
	}

	echo '

			Return to <a href="', $_SERVER['PHP_SELF'], '">the index of tests</a>.
		</div>';
}

function show_header()
{
	global $start_time, $settings;
	$start_time = time();

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>BBC Testcases</title>
		<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js"></script>
		<style type="text/css">
			body
			{
				font-family: Verdana, sans-serif;
				background-color: #D4D4D4;
				margin: 0;
			}
			body, td
			{
				font-size: 10pt;
			}
			div#header
			{
				background-color: white;
				padding: 22px 4% 12px 4%;
				font-family: Georgia, serif;
				font-size: xx-large;
				border-bottom: 1px solid black;
				height: 40px;
			}
			div#content
			{
				padding: 20px 30px;
			}
			div.error_message
			{
				border: 2px dashed red;
				background-color: #E1E1E1;
				margin: 1ex 4ex;
				padding: 1.5ex;
			}
			div.panel
			{
				border: 1px solid gray;
				background-color: #F0F0F0;
				margin: 1ex 0;
				padding: 1.2ex;
			}
			div.panel h2
			{
				margin: 0;
				margin-bottom: 0.5ex;
				padding-bottom: 3px;
				border-bottom: 1px dashed black;
				font-size: 14pt;
				font-weight: normal;
			}
			div.panel h3
			{
				margin: 0;
				margin-bottom: 2ex;
				font-size: 10pt;
				font-weight: normal;
			}
			form
			{
				margin: 0;
			}
			td.textbox
			{
				padding-top: 2px;
				font-weight: bold;
				white-space: nowrap;
				padding-right: 2ex;
			}

			div.code
			{
				margin: 1ex 3ex 2ex 3ex;
				padding: 3px;
				background-color: #FAFAFA;
				font-family: monospace;
				overflow: auto;
			}
			div.code span.comment
			{
				font-style: italic;
				color: #000066;
			}
		</style>
	</head>
	<body>
		<div id="header">
			<a href="http://www.simplemachines.org/" target="_blank"><img src="', $settings['default_images_url'], '/smflogo.gif" style="width: 250px; float: right;" alt="Simple Machines" border="0" /></a>
			<div title="Grr, arg.">BBC Testcases</div>
		</div>
		<div id="content">';
}

function show_footer()
{
	echo '
		</div>
	</body>
</html>';
}

?>