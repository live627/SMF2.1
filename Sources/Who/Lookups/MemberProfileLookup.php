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

class MemberProfileLookup implements DataDrivenLookupInterface
{
	/**
	 * Unique name for this lookup type.
	 *
	 * Used as the key when grouping batched lookup requests.
	 */
	public const NAME = 'profiles';

	public function getId(array $actions, int $member_id): ?int
	{
		if (isset($actions['action']) && $actions['action'] === 'profile') {
			return (int) ($actions['u'] ?? $member_id);
		}

		return null;
	}

	public function getText(array $actions, int $member_id): string
	{
		return Lang::getTxt($actions['u'] == $member_id ? 'who_viewownprofile' :  'who_viewprofile', file: 'Who');
	}

	public function getFormat(array $row): array
	{
		return [
			'id_member' => $row['id_member'],
			'name' => $row['real_name'],
			'scripturl' => Config::$scripturl
		];
	}

	public function fetch(array $requested_ids): array
	{
		$allow_view_own = User::$me->allowedTo('is_not_guest');
		$allow_view_any = User::$me->allowedTo('profile_view');
		$member_data = [];

		if ($allow_view_any || $allow_view_own) {
			$result = Db::$db->query(
				'SELECT id_member, real_name
				FROM {db_prefix}members
				WHERE id_member IN ({array_int:member_list})',
				[
					'member_list' => $requested_ids,
				],
			);

			while ($row = Db::$db->fetch_assoc($result)) {
				if ($allow_view_any || User::$me->id == $row['id_member']) {
					$member_data[$row['id_member']] = $row;
				}
			}
			Db::$db->free_result($result);
		}

		return $member_data;
	}
}