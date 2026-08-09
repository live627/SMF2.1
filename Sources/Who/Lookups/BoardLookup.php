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

use SMF\Config;
use SMF\Db\DatabaseApi as Db;
use SMF\Lang;
use SMF\Who\DataDrivenLookupInterface;

class BoardLookup implements DataDrivenLookupInterface
{
	/**
	 * Unique name for this lookup type.
	 *
	 * Used as the key when grouping batched lookup requests.
	 */
	public const NAME = 'boards';

	public function getId(array $actions, int $member_id): ?int
	{
		if ((!isset($actions['action']) || $actions['action'] === 'messageindex') && isset($actions['board'])) {
			return (int) $actions['board'];
		}

		return null;
	}

	public function getText(array $actions, int $member_id): string
	{
		return Lang::getTxt('who_board', file: 'Who');
	}

	public function getFormat(array $row): array
	{
		return [
			'id_board' => $row['id_board'],
			'name' => $row['name'],
			'scripturl' => Config::$scripturl
		];
	}

	public function fetch(array $requested_ids): array
	{
		$result = Db::$db->query(
			'SELECT b.id_board, b.name
			FROM {db_prefix}boards AS b
			WHERE {query_see_board}
				AND b.id_board IN ({array_int:board_list})',
			[
				'board_list' => $requested_ids,
			],
		);

		$boards = [];

		while ($row = Db::$db->fetch_assoc($result)) {
			$boards[$row['id_board']] = $row;
		}
		Db::$db->free_result($result);

		return $boards;
	}
}