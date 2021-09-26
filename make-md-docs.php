<?php

require_once "./vendor/autoload.php";
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;

use PhpParser\{Node, NodeFinder};

// Stuff we will ignore.
$ignoreFiles = array(
	// Index files.
	'\./attachments/index\.php',
	'\./avatars/index\.php',
	'\./avatars/[A-Za-z0-9]+/index\.php',
	'\./cache/index\.php',
	'\./custom_avatar/index\.php',
	'\./Packages/index\.php',
	'\./Packages/backups/index\.php',
	'\./Smileys/[A-Za-z0-9]+/index\.php',
	'\./Smileys/index\.php',
	'\./Sources/index\.php',
	'\./Sources/tasks/index\.php',
	'\./Themes/default/css/index\.php',
	'\./Themes/default/fonts/index\.php',
	'\./Themes/default/fonts/sound/index\.php',
	'\./Themes/default/images/[A-Za-z0-9]+/index\.php',
	'\./Themes/default/images/index\.php',
	'\./Themes/default/index\.php',
	'\./Themes/default/languages/index\.php',
	'\./Themes/default/scripts/index\.php',
	'\./Themes/index\.php',
	// Language Files are ignored as they don't use the License format.
	'./Themes/default/languages/[A-Za-z0-9]+\.english\.php',
	// Cache and miscellaneous.
	'\./cache/',
	'\./other/',
	'\./tests/',
	'\./vendor/',
	// Minify Stuff.
	'\./Sources/minify/',
	// random_compat().
	'\./Sources/random_compat/',
	// ReCaptcha Stuff.
	'\./Sources/ReCaptcha/',
	// We will ignore Settings.php if this is a live dev site.
	'\./Settings\.php',
	'\./Settings_bak\.php',
	'\./db_last_error\.php',
);

$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
$traverser = new NodeTraverser();
$traverser->addVisitor(new class extends NodeVisitorAbstract
{
	public function enterNode(Node $node)
	{
		if ($node instanceof Function_)
		{
			// Clean out the function body
			$node->stmts = [];
		}
	}
});
$nodeFinder = new NodeFinder;
$prettyPrinter = new PrettyPrinter\Standard;
$dumper = new NodeDumper;
$factory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
foreach (new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator('.', FilesystemIterator::UNIX_PATHS)
) as $currentFile => $fileInfo)
{
	if ($fileInfo->getExtension() == 'php')
	{
		foreach ($ignoreFiles as $if)
			if (preg_match('~' . $if . '~i', $currentFile))
				continue 2;

		$ast = $parser->parse(file_get_contents($currentFile));
		$ast = $traverser->traverse($ast);

		$nodes = $nodeFinder->findInstanceOf($ast, Node\Stmt\Function_::class);
		$count = count($nodes);
		if ($count > 0)
			if (($file = fopen('./docs/' . hash("crc32b", $currentFile) . '.md', 'w')) !== false)
			{
				fwrite(
					$file,
					sprintf(
						"---\nlayout: default\nnavtitle: %s\ntitle: %s\ncount: %d\n---\n\n",
						basename($currentFile),
						$currentFile,
						$count
					)
				);
				foreach ($nodes as $node)
				{
					fwrite(
						$file,
						sprintf(
							"### %s\n",
							$node->name
						)
					);
					foreach (explode("\n", $prettyPrinter->prettyPrint([$node])) as $func)
					{
						if (strpos($func, 'function') === 0)
							fwrite(
								$file,
								sprintf(
									"\n```php\n%s\n```\n",
									$func
								)
							);
					}
					$docComment = $node->getDocComment();

					if ($docComment !== null)
					{
						$docblock = $factory->create($docComment->getText());
						fwrite(
							$file,
							sprintf(
								"%s\n\n%s\n\n",
								$docblock->getSummary(),
								$docblock->getDescription()
							)
						);
						write_params($file, $docblock);
					}
				}

				fclose($file);
			}
			else
				throw new Exception('Unable to open file ' . $currentFile . "\n");
	}
}

function write_params($file, $docblock)
{
	if ($docblock->hasTag('param'))
	{
		fwrite($file, "Type|Parameter|Description\n---|---|---\n");
		foreach ($docblock->getTags() as $tag)
			if ($tag->getName() === 'param')
			{
				$description = '';
				if ($tag->getDescription())
					$description = $tag->getDescription()->render();

				$variableName = '';
				if ($tag->getVariableName())
				{
					if ($tag->isReference())
						$variableName .= ' &';
					if ($tag->isVariadic())
						$variableName .= '...';

					$variableName .= sprintf('$%s', $tag->getVariableName());
				}

				fwrite(
					$file,
					sprintf(
						"`%s`|`%s`|%s\n",
						str_replace('|', '&#124;', $tag->getType()),
						$variableName,
						$description
					)
				);
			}
		fwrite($file, "\n");
	}
}