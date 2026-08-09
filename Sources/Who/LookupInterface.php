<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2026 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 3.0 Alpha 4
 */

declare(strict_types=1);

namespace SMF\Who;

interface LookupInterface
{
	/**
	 * Determines whether this lookup handles the supplied action.
	 *
	 * @param array $actions Parsed request parameters.
	 *
	 * @return bool Whether this lookup can handle the action.
	 */
	public function supports(array $actions): bool;

	/**
	 * Returns the language string for the action.
	 *
	 * @param array $actions Parsed request parameters.
	 *
	 * @return string|array
	 */
	public function getText(array $actions): string|array;
}