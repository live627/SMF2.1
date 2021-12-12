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
namespace SMF\Sniffs\Operators;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Check to ensure that the logical operators 'and' and 'or' are not used.
 * Use the && and || operators instead.
 *
 * @since     1.0
 */
class ValidLogicalOperatorsSniff implements Sniff
{
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register()
	{
		return [
			T_LOGICAL_AND,
			T_LOGICAL_OR,
		];
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param   PHP_CodeSniffer\Files\File $phpcsFile The current file being scanned.
	 * @param   int                        $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 */
	public function process(File $phpcsFile, $stackPtr): void
	{
		$tokens = $phpcsFile->getTokens();
		$operators = [
			'and' => '&&',
			'or' => '||',
		];
		$operator = strtolower($tokens[$stackPtr]['content']);

		if (isset($operators[$operator]) === false)
		{
			// We have correct logical operators in use so return
			return;
		}

		$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);

		if ($tokens[$nextToken]['code'] === T_EXIT)
		{
			// This enforces an exception for things like `or die;` and `or exit;`
			return;
		}

		// Special SMF! cases.
		if ($tokens[$nextToken]['content'] === 'jexit'
			|| $tokens[$nextToken]['content'] === 'JSession'
			|| $tokens[$nextToken]['content'] === 'define'
			|| $tokens[($nextToken + 2)]['content'] === 'sendResponse'
			|| $tokens[($nextToken + 2)]['content'] === 'sendJsonResponse'
		)
		{
			// Exceptions for things like `or jexit()`, `or JSession`, `or define`, `or sendResponse`, `or sendJsonResponse`
			return;
		}

		$error = 'Logical operator "%s" not allowed; use "%s" instead';
		$data = [
			$operator,
			$operators[$operator],
		];
		$fix = $phpcsFile->addFixableError($error, $stackPtr, 'NotAllowed', $data);

		if ($fix === true)
		{
			$phpcsFile->fixer->replaceToken($stackPtr, $operators[$operator]);
		}
	}
}
