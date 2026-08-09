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

use SMF\Lang;

class TopicTxtLookup extends TopicLookup
{
	public function getId(array $actions, int $member_id): ?int
	{
		if (isset($actions['action']) && Lang::txtExists('whotopic_' . $actions['action'], file: 'Who')) {
			return (int) ($actions['topic'] ?? ($actions['from'] ?? 0));
		}

		return null;
	}

	public function getText(array $actions, int $member_id): string
	{
		return Lang::getTxt('whotopic_' . $actions['action'], file: 'Who');
	}
}