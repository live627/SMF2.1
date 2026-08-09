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

class MsgLookup implements DataDrivenLookupInterface
{
	/**
	 * Unique name for this lookup type.
	 *
	 * Used as the key when grouping batched lookup requests.
	 */
	public const NAME = 'msg';

	public function getId(array $actions, int $member_id): ?int
	{
		if (isset($actions['action']) && Lang::txtExists('whopost_' . $actions['action'], file: 'Who')) {
			return (int) ($actions['msg'] ?? ($actions['quote'] ?? 0));
		}

		return null;
	}

	public function getText(array $actions, int $member_id): string
	{
		return Lang::getTxt('whopost_' . $actions['action'], file: 'Who');
	}

	public function getFormat(array $row): array
	{
		return [
			'id_topic' => $row['id_topic'],
			'subject' => Lang::censorText($row['subject']),
			'scripturl' => Config::$scripturl
		];
	}

	public function fetch(array $requested_ids): array
	{
		$result = Db::$db->query(
			'SELECT m.id_topic, m.subject
			FROM {db_prefix}messages AS m
				' . (Config::$modSettings['postmod_active'] ? 'INNER JOIN {db_prefix}topics AS t ON (t.id_topic = m.id_topic AND t.approved = {int:is_approved})' : '') . '
			WHERE m.id_msg IN ({array_int:message_list})
				AND {query_see_message_board}' . (Config::$modSettings['postmod_active'] ? '
				AND m.approved = {int:is_approved}' : '') . '
			LIMIT 1',
			[
				'is_approved' => 1,
				'message_list' => $requested_ids,
			],
		);

		$topics = [];

		while ($row = Db::$db->fetch_assoc($result)) {
			$topics[$row['id_topic']] = $row;
		}
		Db::$db->free_result($result);

		return $topics;
	}
}