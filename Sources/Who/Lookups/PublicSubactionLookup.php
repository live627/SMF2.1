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

use SMF\{Config, Lang};
use SMF\Who\LookupInterface;

final class PublicSubactionLookup implements LookupInterface
{
	public function __construct(
		private readonly string|false $preferred_prefix = false,
	) {
	}

	public function supports(array $actions): bool
	{
		return isset($actions['action'], $actions['sa'])
			&& Lang::txtExists(
				'whoall_' . $actions['action'] . '_' . $actions['sa'],
				file: 'Who',
			);
	}

	public function getText(array $actions): string|array
	{
		$prefix = 'whoall_';

		if (
			$this->preferred_prefix
			&& Lang::txtExists(
				$this->preferred_prefix . $actions['action'] . '_' . $actions['sa'],
				file: 'Who',
			)
		) {
			$prefix = $this->preferred_prefix;
		}

		return Lang::getTxt(
			$prefix . $actions['action'] . '_' . $actions['sa'],
			['scripturl' => Config::$scripturl],
			file: 'Who',
		);
	}
}