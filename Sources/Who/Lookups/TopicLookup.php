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

class TopicLookup implements DataDrivenLookupInterface
{
	/**
	 * Unique name for this lookup type.
	 *
	 * Used as the key when grouping batched lookup requests.
	 */
	public const NAME = 'topics';

	public function getId(array $actions, int $member_id): ?int
	{
		if ((!isset($actions['action']) || $actions['action'] === 'display') && isset($actions['topic'])) {
			return (int) $actions['topic'];
		}

		return null;
	}

	public function getText(array $actions, int $member_id): string
	{
		return Lang::getTxt('who_topic', file: 'Who');
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
			'SELECT t.id_topic, m.subject
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE {query_see_topic_board}
				AND t.id_topic IN ({array_int:topic_list})' . (Config::$modSettings['postmod_active'] ? '
				AND t.approved = {int:is_approved}' : ''),
			[
				'topic_list' => $requested_ids,
				'is_approved' => 1,
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