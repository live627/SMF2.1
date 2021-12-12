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
namespace SMF\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Verifies that control statements preceed as single space.
 *
 * @since   1.0
 */
class ControlSignatureSniff implements Sniff
{
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return int[]
	 */
	public function register()
	{
		return [
			T_TRY,
			T_CATCH,
			T_FINALLY,
			T_DO,
			T_WHILE,
			T_FOR,
			T_FOREACH,
			T_IF,
			T_ELSE,
			T_ELSEIF,
			T_SWITCH,
		];
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param   PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
	 * @param   int                        $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 */
	public function process(File $phpcsFile, $stackPtr): void
	{
		$tokens = $phpcsFile->getTokens();

		if (isset($tokens[($stackPtr + 1)]) === false)
		{
			return;
		}

		// Single space after the keyword.
		$found = 1;

		if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE)
		{
			$found = 0;
		}
		elseif ($tokens[($stackPtr + 1)]['content'] !== ' ')
		{
			if (strpos($tokens[($stackPtr + 1)]['content'], $phpcsFile->eolChar) !== false)
			{
				$found = 'newline';
			}
			else
			{
				$found = strlen($tokens[($stackPtr + 1)]['content']);
			}
		}

		if ($found !== 1
			&& $tokens[($stackPtr)]['code'] !== T_ELSE
			&& $tokens[($stackPtr)]['code'] !== T_TRY
			&& $tokens[($stackPtr)]['code'] !== T_DO
			&& $tokens[($stackPtr)]['code'] !== T_FINALLY
		)
		{
			$error = 'Expected 1 space after %s keyword; %s found';
			$data = [
				strtoupper($tokens[$stackPtr]['content']),
				$found,
			];
			$fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterKeyword', $data);

			if ($fix)
			{
				if ($found === 0)
				{
					$phpcsFile->fixer->addContent($stackPtr, ' ');
				}
				else
				{
					$phpcsFile->fixer->replaceToken(($stackPtr + 1), ' ');
				}
			}
		}

		if ($tokens[$stackPtr]['code'] === T_WHILE && !isset($tokens[$stackPtr]['scope_opener']) === true)
		{
			// Zero spaces after parenthesis closer.
			$closer = $tokens[$stackPtr]['parenthesis_closer'];
			$found = 0;

			if ($tokens[($closer + 1)]['code'] === T_WHITESPACE)
			{
				if (strpos($tokens[($closer + 1)]['content'], $phpcsFile->eolChar) !== false)
				{
					$found = 'newline';
				}
				else
				{
					$found = strlen($tokens[($closer + 1)]['content']);
				}
			}

			if ($found !== 0)
			{
				$error = 'Expected 0 spaces before semicolon; %s found';
				$data = [$found];
				$fix = $phpcsFile->addFixableError($error, $closer, 'SpaceBeforeSemicolon', $data);

				if ($fix)
				{
					$phpcsFile->fixer->replaceToken(($closer + 1), '');
				}
			}
		}//end if

		// Only want to check multi-keyword structures from here on.
		if ($tokens[$stackPtr]['code'] === T_DO)
		{
			if (isset($tokens[$stackPtr]['scope_closer']) === false)
			{
				return;
			}

			$closer = $tokens[$stackPtr]['scope_closer'];
		}
		elseif ($tokens[$stackPtr]['code'] === T_ELSE
			|| $tokens[$stackPtr]['code'] === T_ELSEIF
			|| $tokens[$stackPtr]['code'] === T_CATCH
		)
		{
			$closer = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);

			if ($closer === false || $tokens[$closer]['code'] !== T_CLOSE_CURLY_BRACKET)
			{
				return;
			}
		}
		else
		{
			return;
		}

		// Own line for do, else, elseif, catch and no white space after closing brace
		$found = 0;

		if ($tokens[($closer + 1)]['code'] === T_WHITESPACE
			&& $tokens[($closer + 1)]['content'] !== $phpcsFile->eolChar
		)
		{
			$found = strlen($tokens[($closer + 1)]['content']);
		}

		if ($found !== 0)
		{
			$error = 'Expected 0 space after closing brace; %s found';
			$data = [$found];
			$fix = $phpcsFile->addFixableError($error, $closer, 'SpaceAfterCloseBrace', $data);

			if ($fix === true)
			{
				$phpcsFile->fixer->replaceToken(($closer + 1), '' . $phpcsFile->eolChar);
			}
		}

		if ($tokens[($closer + 1)]['content'] !== $phpcsFile->eolChar && $found === 0)
		{
			$error = 'Definition of do,else,elseif,catch must be on their own line.';
			$fix = $phpcsFile->addFixableError($error, $closer, 'NewLineAfterCloseBrace');
			$blanks = substr(
				$tokens[($closer - 1)]['content'],
				strpos($tokens[($closer - 1)]['content'], $phpcsFile->eolChar)
			);
			$spaces = str_repeat("\t", strlen($blanks));

			if ($fix === true)
			{
				$phpcsFile->fixer->beginChangeset();
				$phpcsFile->fixer->addContent($closer, $phpcsFile->eolChar);
				$phpcsFile->fixer->addContentBefore(($closer + 1), $spaces);
				$phpcsFile->fixer->endChangeset();
			}
		}
	}
}
