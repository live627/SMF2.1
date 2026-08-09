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

class PostLookup extends BoardLookup
{
	public function getId(array $actions, int $member_id): ?int
	{
		if (isset($actions['action'], $actions['board']) && $actions['action'] === 'post' && $actions['action'] === 'post2' && empty($actions['topic'])) {
			return (int) $actions['board'];
		}

		return null;
	}

	public function getText(array $actions, int $member_id): string
	{
		return Lang::getTxt(isset($actions['poll']) ? 'who_poll' : 'who_post', file: 'Who');
	}
}