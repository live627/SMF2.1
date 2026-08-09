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

use SMF\{Config, Lang, Utils};
use SMF\Who\LookupInterface;

final class BoardIndexLookup implements LookupInterface
{
	public function supports(array $actions): bool
	{
		return (
			(!isset($actions['action']) || $actions['action'] === 'boardindex')
			&& !isset($actions['topic'])
			&& !isset($actions['board'])
		);
	}

	public function getText(array $actions): string|array
	{
		return Lang::getTxt(
			'who_index',
			[
				'scripturl' => Config::$scripturl,
				'forum_name' => Utils::$context['forum_name_html_safe'],
			],
			file: 'Who',
		);
	}
}