<?php
function parse_diff(string $string): array
{
	$files = preg_split('/--- ([^\s+]+)[^\n]*\n\+\+\+ ([^\s]+)[^\n]*/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
	$num_files = count($files);
	$diffs = [];

	for ($i = 1; $i < $num_files; $i += 3)
	{
		$pieces = preg_split(
			'/@@\s+-(\d+)(?:,\s*(\d+))?\s+\+(\d+)(?:,\s*(\d+))?\s+@@[^\n]/',
			$files[$i + 2],
			-1,
			PREG_SPLIT_DELIM_CAPTURE
		);
		$num_pieces = count($pieces);
		$hunks = [];

		for ($j = 1; $j < $num_pieces; $j += 5)
		{
			$lines = [];
			foreach (preg_split('/\n/', $pieces[$j + 4]) as $line)
				if (strlen($line) && preg_match('/[ \+-]/', $line[0]))
					$lines[] = [
						match($line[0])
						{
							'+' => 'added',
							'-' => 'removed',
							' ' => 'unchanged'
						},
						substr($line, 1)
					];

				$hunks[] = [
					(int) $pieces[0],
					isset($pieces[$j + 1]) ? max(1, (int) $pieces[$j + 1]) : 1,
					(int) $pieces[$j + 2],
					isset($pieces[$j + 3]) ? max(1, (int) $pieces[$j + 3]) : 1,
					$lines,
				];
		}

		$diffs[] = [$files[$i], $files[$i + 1], $hunks];
	}

	return $diffs;
}

function byte_format(int $bytes): string
{
	for ($i = 0; $bytes > 1024 && $i < 3; $i++)
		$bytes /= 1024;

	return number_format($bytes,2) . ' ' . ['B', 'KB', 'MB'][$i];
}

enum ArgType
{
	case None;
	case Required;
	case Optional;
}

class Option
{
	/**
	 * Creates a new option.
	 *
	 * @param ?string  $short The option's short name (one of [a-zA-Z0-9?!§$%#]) or null for long-only options
	 * @param string   $long  The option's long name (a string of 1+ letter|digit|_|- characters, starting with a letter
	 *                        or digit) or null for short-only options
	 * @param string   $description Option description for the usage message
	 * @param ArgType  $argType  Whether the option can/must have an argument (optional, defaults to no argument)
	 */
	public function __construct(
		public ?string $short,
		public string $long,
		public string $description = '',
		public ArgType $argType = ArgType::None,
		public string $argName = '',
	)
	{
	}
}

function get_opts(array $options): array
{
	$args = [];
	$bopts = [];
	$vopts = [];
	$oopts = [];
	$lbopts = [];
	$lvopts = [];
	$loopts = [];
	$opts = ['args' => []];
	foreach ($options as $option)
	{
		if ($option->argType == ArgType::None)
		{
			$bopts[$option->short] = true;
			$lbopts[$option->long] = true;
		}
		elseif ($option->argType == ArgType::Required)
		{
			$vopts[$option->short] = true;
			$lvopts[$option->long] = true;
		}
		elseif ($option->argType == ArgType::Optional)
		{
			$oopts[$option->short] = true;
			$loopts[$option->long] = true;
		}
	}
	for ($i = 1; $i < $_SERVER['argc']; $i++)
	{
		$val = $_SERVER['argv'][$i];
		$len = strlen($val);
		$len1 = $len - 1;
		if ($len > 1 && $val[0] == '-' && $val[1] != '-')
		{
			for ($j = 1; $j < $len; $j++)
				if (isset($bopts[$val[$j]]))
					$opts['options'][$val[$j]] = ($opts['options'][$val[$j]] ?? 0) + 1;
				elseif (isset($vopts[$val[$j]]) || (isset($oopts[$val[$j]], $val[$j + 1]) && !isset($bopts[$val[$j + 1]])) || ($j == $len1 && isset($oopts[$val[$j]])))
				{
					if ($j < $len1)
						$opts['options'][$val[$j]] = substr($val, $j + 1);
					elseif ($j == $len1 && isset($_SERVER['argv'][$i + 1])/* && $_SERVER['argv'][++$i][0] != '-'*/)
						$opts['options'][$val[$j]] = $_SERVER['argv'][++$i];
					break;
				}
				elseif (isset($oopts[$val[$j]]))
					$opts['options'][$val[$j]] = ($opts['options'][$val[$j]] ?? 0) + 1;
				else
					$opts['options']['?'][] = $val[$j];
		}
		elseif ($len > 3 && $val[0] == '-' && $val[1] == '-' && preg_match('/--([A-Za-z-]+)(?:=?(.+))?+/'   , $val, $m))
		{
			if (isset($lbopts[$m[1]]))
				$opts['options'][$m[1]] = ($opts['options'][$m[1]] ?? 0) + 1;
			elseif (isset($lvopts[$m[1]]) || (isset($loopts[$m[1]], $m[2])) || (isset($loopts[$m[1]], $_SERVER['argv'][$i + 1]) && $_SERVER['argv'][$i + 1][0] != '-'))
			{
				if (isset($m[2]))
					$opts['options'][$m[1]] = $m[2];
				elseif (isset($_SERVER['argv'][$i + 1]) && $_SERVER['argv'][$i + 1][0] != '-')
					$opts['options'][$m[1]] = $_SERVER['argv'][++$i];
			}
			elseif (isset($loopts[$m[1]]))
				$opts['options'][$m[1]] = ($opts['options'][$m[1]] ?? 0) + 1;
			else
				$opts['options']['?'][] = $m[1];
		}
		else
			$opts['args'][] = $_SERVER['argv'][$i];
	}
	return $opts;
}

function show_help(array $arguments, array $options): string
{
	$args = array();
	$chars = 0;
	foreach ($arguments as $argument => $description)
		$chars = max($chars, strlen($argument) + 2);
	foreach ($options as $option)
	{
		$short = $option->short;
		$long = $option->long;
		$arg = ($short !== null ? '-' . $short . ', ' : '    ') . '--' . $long . ($option->argType == ArgType::Required ? ' <' . $option->argName . '>' : '');
		$chars = max($chars, strlen($arg));
		$args[] = [$arg, $option->description];
	}

	$output = "Arguments:\n";
	foreach ($arguments as $argument => $description)
		$output .= sprintf(
			'    %-' . $chars . "s %s\n",
			'<' . $argument . '>' ,
			wordwrap($description, 90 - $chars, "\n" . str_repeat(' ', $chars + 5))
		);

	$output .= "\nOptions:\n";
	foreach ($args as [$arg, $description])
		$output .= sprintf(
			'    %-' . $chars . "s %s\n",
			$arg,
			wordwrap($description, 90 - $chars, "\n" . str_repeat(' ', $chars + 5))
		);

	return $output;
}

$options = [
	new Option(null, 'tag', 'The new version to tag the files with. This must be in the format of MAJOR.MINOR.PATCH.', ArgType::Required, 'version'),
	new Option(null, 'ignore-pattern', 'Regular exprssion for ignoring files/diredtories. You must supply the delimiter!', ArgType::Required, 'regex'),
	new Option('v', 'verbose', 'Specify the verbosity of the output (-v to show the final list of files; -vv to show the individual operations; -vvv to show whether the operations are unique to the file and therefore have unchanged lines trimmed). Lower verbosity levels cascade onto higher ones.'),
	new Option('h', 'help', 'Show this help message and exit.'),
];

/*
 * The code inside this IF block will only be executed when this file is run
 * directly from the command line. If this file is included by another file,
 * the code in this block will be ignored.
 */
if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0]))
{
	$error = false;
	$verbosity = 0;
	$tag = '1.0.0';
	$ignore = '';
	$help = false;

	$opts = get_opts($options);
	if (isset($opts['options']))
		foreach ($opts['options'] as $optkey => $optval)
		{
			switch ($optkey)
			{
				case 'tag':
					$tag = $optval;
					break;
				case 'ignore-pattern':
					$ignore = $optval;
					break;
				case 'h':
				case 'help':
					$help = true;
					break;
				case 'v':
				case 'verbose':
					$verbosity = $optval;
					fprintf(STDOUT, "Verbosity is %d\n", $verbosity);
					break;
				case '?':
					foreach ($optval as $key)
						fprintf(STDERR, "Unknown option: %s\n", $key);
					$error = true;
					break;
				default:
					$error = true;
					break;
			}
		}

	$num_args = count($opts['args']);
	if ($num_args < 1 || $num_args > 2)
	{
		fprintf(STDERR, "Expected either 1 or 2 arguments; %d given\n", $num_args);
		$error = true;
	}
	if (!file_exists($opts['args'][0]))
	{
		fprintf(STDERR, "Source file does not exist: %s\n", $opts['args'][0]);
		$error = true;
	}
	if (preg_match('/^0|[1-9]\d*\.0|[1-9]\d*\.0|[1-9]\d*$/', $tag) !== 1)
	{
		fprintf(STDERR, "Invalid semver format for tag: %s\n", $tag);
		$error = true;
	}

	set_error_handler(
		function($errno, $errstr, $errfile, $errline)
		{
			throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
		}
	);
	try
	{
		if ($ignore != '' && preg_match($ignore, '') !== 1 && preg_last_error() !== PREG_NO_ERROR)
		{
			fprintf(STDERR, "Invalid regex %s (%s)\n", $ignore, preg_last_error_msg());
			$error = true;
		}
	}
	catch (Throwable $e)
	{
		fprintf(STDERR, "Invalid regex %s (%s)\n", $ignore, $e->getMessage());
		$error = true;
	}

	if ($help || $error)
	{
		if ($error)
			fwrite(STDERR, "\n");
		fprintf(
			$error ? STDERR : STDOUT,
			"Usage: php %s <source> [<target>] [OPTIONS]\n    %s\n\n%s",
			basename(__FILE__),
			'Convert a unified diff to a SMF modification.',
			show_help(
				[
					'source' => 'Unified diff to read.',
					'target' => 'XML modification to write. Will use standard output if omitted.'
				],
				$options
			)
		);

		exit($error ? 1 : 0);
	}

	$eta = -hrtime(true);
	$mem = memory_get_usage();

	$changeset = parse_diff(file_get_contents($opts['args'][0]));

	$list = [];
	$version_headers = [];
	$version_constants = [];
	foreach ($changeset as [, $filename, $hunks])
	{
		$filename = './' . substr($filename, 2);
		if ($ignore != '' && preg_match($ignore, $filename))
		{
			if ($verbosity > 1)
				fprintf(STDOUT, "Ignoring file: %s\n", $filename);
			continue;
		}

		if (($fp = fopen($filename, 'r')) !== false)
		{
			$contents = stream_get_contents($fp);
			fclose($fp);
			foreach ($hunks as [,,,, $lines])
			{
				$search = [];
				$add = [];
				$removes = [];
				$adds = [];
				$unchanged_lines = [];
				$num_unchanged_lines = 0;
				$where = 'replace';
				foreach ($lines as [$op, $c])
				{
					if ($op != 'added')
						$search[] = $c;
					if ($op != 'removed')
						$add[] = $c;
					if ($op == 'added')
						$adds[] = $c;
					if ($op == 'removed')
						$removes[] = $c;
					if ($op == 'unchanged')
					{
						$num_unchanged_lines++;
						if ($num_unchanged_lines < 4)
							$unchanged_lines[] = $c;
					}
				}
				$search = implode("\n", $search);
				$add = implode("\n", $add);
				$removes = implode("\n", $removes);
				$adds = implode("\n", $adds);

				// Take out unchanged lines if both added and removed are unique.
				// ... but only if this hunk includes one search/replace operation.
				if ($num_unchanged_lines == 6 && $removes != '' && substr_count($contents, $removes) < 2 && $adds != '' && substr_count($contents, $adds) < 2)
				{
					$search = $removes;
					$add = $adds;
				}
				// Take unchanged lines out from below added.
				// ... but only if this hunk includes one search/add after operation.
				if ($num_unchanged_lines == 6 && $removes == '' && $adds != '')
				{
					// Reduce search to one line if unique.
					$search = $unchanged_lines[2] != '' && substr_count($contents, $unchanged_lines[2]) < 2
						? $unchanged_lines[2]
						:  implode("\n", $unchanged_lines);
					$add = "\n" . $adds;
					$where = 'before';
				}
				// Remove bottom 3 unchanged lines if this hunk includes one search/remove operation.
				if ($num_unchanged_lines == 6 && $removes != '' && $adds == '')
				{
					// Reduce search to one line if unique.
					$add = $unchanged_lines[2] != '' && substr_count($contents, $unchanged_lines[2]) < 2
						? $unchanged_lines[2]
						: implode("\n", $unchanged_lines);
					$search = $add . "\n" . $removes;
				}
				$list[$filename][] = [$where, $search, $add];

				// Time to increment the all-important version number.
				if (str_contains($removes, '* @version'))
					$version_headers[$filename] = false;
				if (str_contains($removes, 'define(\'SMF_VERSION'))
					$version_constants[$filename] = false;
			}

			// Try to find them, but only if they aren't already in the diff.
			if (!isset($version_headers[$filename]) && ($pos = strpos($contents, '* @version')) !== false)
				$version_headers[$filename] = substr($contents, $pos, strpos($contents, "\n", $pos) - $pos);
			if (!isset($version_constants[$filename]) && ($pos = strpos($contents, 'define(\'SMF_VERSION')) !== false)
				$version_constants[$filename] = substr($contents, $pos, strpos($contents, "\n", $pos) - $pos);
		}
	}
	foreach (['./index.php', './SSI.php', './proxy.php', './cron.php'] as $filename)
		if (isset($list[$filename]) && !empty($version_constants[$filename]))
			array_unshift(
				$list[$filename],
				[
					'replace',
					$version_constants[$filename],
					'define(\'SMF_VERSION\', \'' . $tag . '\');'
				]
			);
		else
		{
			if (($fp = fopen($filename, 'r')) !== false)
			{
				$contents = stream_get_contents($fp);
				fclose($fp);

				if (!isset($version_headers[$filename]) && ($pos = strpos($contents, '* @version')) !== false)
					$version_headers[$filename] = substr($contents, $pos, strpos($contents, "\n", $pos) - $pos);
				if (!isset($version_constants[$filename]) && ($pos = strpos($contents, 'define(\'SMF_VERSION')) !== false)
					$version_constants[$filename] = substr($contents, $pos, strpos($contents, "\n", $pos) - $pos);

				$list[$filename][] = [
					'replace',
					$version_constants[$filename],
					'define(\'SMF_VERSION\', \'' . $tag . '\');'
				];
			}
		}

	foreach ($list as $filename => $ops)
		if (!empty($version_headers[$filename]))
			array_unshift(
				$list[$filename],
				['replace', $version_headers[$filename], '* @version ' . $tag]
			);

	// Sort the files alphabetically, high directory levels first.
	array_multisort(
		array_map(
			fn($filename) => substr_count($filename, '/') . $filename,
			array_keys($list)
		),
		SORT_NATURAL | SORT_FLAG_CASE,
		$list
	);

	if ($verbosity > 0)
		array_walk(
			$list,
			function ($operations, $filename)
			{
				printf(
					"Processed %s (Operations: %d)\n",
					$filename,
					count($operations)
				);
			}
		);

	$fp = isset($opts['args'][1]) ? fopen($opts['args'][1], 'w') : STDOUT;
	fprintf(
		$fp,
		"<?xml version=\"1.0\"?>\n<!DOCTYPE modification SYSTEM \"http://www.simplemachines.org/xml/modification\">\n<modification xmlns=\"http://www.simplemachines.org/xml/modification\" xmlns:smf=\"http://www.simplemachines.org/\">\n\t<id>smf:smf-%s</id>\n\t<version>1.0</version>",
		$tag
	);

	foreach ($list as $filename => $operations)
	{
		fprintf(
			$fp,
			"\n\n\t<file name=\"%s\">",
			strtr(
				$filename,
				[
					'./' => '$boarddir/',
					'./Sources' => '$sourcedir',
					'./avatars' => '$avatars_dir',
					'./Themes/default' => '$themedir',
					'./Themes/default/images' => '$imagesdir',
					'./Themes' => '$themes_dir',
					'./Themes/default/languages' => '$languagedir',
					'./Smileys' => '$smileysdir',
				]
			)
		);
		foreach ($operations as [$where, $search, $add])
			fprintf(
				$fp,
				"\n\t\t<operation>\n\t\t\t<search position=\"%s\">%s</search>\n\t\t\t<add>%s</add>\n\t\t</operation>",
				$where,
				str_contains($search, '<![CDATA[') || str_contains($search, ']]>')
					? htmlspecialchars($search)
					: '<![CDATA[' . $search . ']]>',
				str_contains($add, '<![CDATA[') || str_contains($add, ']]>')
					? htmlspecialchars($add)
					: '<![CDATA[' . $add . ']]>'
			);

		fwrite($fp, "\n\t</file>");
	}
	fwrite($fp, "\n\n</modification>");
	if (isset($opts['args'][1]))
		fclose($fp);

	$eta += hrtime(true);
	printf(
		"\n\nTime elapsed: %.2f seconds\nMemory usage: %s (%s peak)\n",
		$eta / 1e9,
		byte_format(memory_get_usage() - $mem),
		byte_format(memory_get_peak_usage())
	);
}
