<?php

declare(strict_types=1);
/**
 * SMF Coding Standard
 *
 * @package    smf-coding-standard
 * @author     Simple Machines https://www.simplemachines.org
 * @copyright  2021 Simple Machines and individual contributors
 * @license    https://www.simplemachines.org/about/smf/license.php BSD
 */
namespace SMF\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that new classes are instantiated without brackets if they do not have any parameters.
 *
 * @since     1.0
 */
class InstantiateNewClassesSniff implements Sniff
{
	/**
	 * Registers the token types that this sniff wishes to listen to.
	 *
	 * @return  array
	 */
	public function register()
	{
		return [T_NEW];
	}

	/**
	 * Process the tokens that this sniff is listening for.
	 *
	 * @param   PHP_CodeSniffer\Files\File $phpcsFile The file where the token was found.
	 * @param   integer                    $stackPtr  The position in the stack where the token was found.
	 *
	 */
	public function process(File $phpcsFile, $stackPtr): void
	{
		$tokens = $phpcsFile->getTokens();

		$running = true;
		$valid = false;
		$started = false;

		$cnt = $stackPtr + 1;

		do
		{
			if (!isset($tokens[$cnt]))
				$running = false;
			else
			{
				switch ($tokens[$cnt]['code'])
				{
					case T_SEMICOLON :
					case T_COMMA :
						$valid = true;
						$running = false;
						break;

					case T_OPEN_PARENTHESIS :
						$started = true;
						break;

					case T_VARIABLE :
					case T_STRING :
					case T_LNUMBER :
					case T_CONSTANT_ENCAPSED_STRING :
					case T_DOUBLE_QUOTED_STRING :
					case T_ARRAY :
					case T_TRUE :
					case T_FALSE :
					case T_NULL :
						if ($started === true)
						{
							$valid = true;
							$running = false;
						}

						break;

					case T_CLOSE_PARENTHESIS :
						if ($started === false)
						{
							$valid = true;
						}

						$running = false;
						break;

					case T_WHITESPACE :
						break;

					case T_OPEN_SHORT_ARRAY :
						if ($started === true)
						{
							$valid = true;
							$running = false;
						}
						break;
				}

				$cnt++;
			}
		} while ($running === true);

		if ($valid === false)
		{
			$error = 'Instanciating new class without parameters does not require brackets.';
			$fix = $phpcsFile->addFixableError($error, $stackPtr, 'NewClass');

			if ($fix)
			{
				$classNameEnd = $phpcsFile->findNext(
					[
						T_VARIABLE,
						T_WHITESPACE,
						T_NS_SEPARATOR,
						T_STRING,
						T_SELF,
					],
					($stackPtr + 1),
					null,
					true,
					null,
					true
				);

				$phpcsFile->fixer->beginChangeset();

				if ($tokens[($stackPtr + 3)]['code'] === T_WHITESPACE)
				{
					$phpcsFile->fixer->replaceToken(($stackPtr + 3), '');
				}

				for ($i = $classNameEnd; $i < $cnt; $i++)
				{
					$phpcsFile->fixer->replaceToken($i, '');
				}

				$phpcsFile->fixer->endChangeset();
			}
		}
	}
}
