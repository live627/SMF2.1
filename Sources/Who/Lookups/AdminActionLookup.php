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

namespace SMF\Who\Lookups;

use SMF\{Config, Lang, User};
use SMF\Who\LookupInterface;

final class AdminActionLookup implements LookupInterface
{
	public function supports(array $actions): bool
	{
		return isset($actions['action'])
			&& User::$me->allowedTo('moderate_forum')
			&& Lang::txtExists(
				'whoadmin_' . $actions['action'],
				file: 'Who',
			);
	}

	public function getText(array $actions): string|array
	{
		return Lang::getTxt(
			'whoadmin_' . $actions['action'],
			['scripturl' => Config::$scripturl],
			file: 'Who',
		);
	}
}