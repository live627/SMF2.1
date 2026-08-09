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

namespace SMF\Actions;

use SMF\ActionInterface;
use SMF\ActionRouter;
use SMF\ActionTrait;
use SMF\Config;
use SMF\Db\DatabaseApi as Db;
use SMF\ErrorHandler;
use SMF\IntegrationHook;
use SMF\IP;
use SMF\Lang;
use SMF\PageIndex;
use SMF\Routable;
use SMF\Theme;
use SMF\Time;
use SMF\User;
use SMF\Utils;

/**
 * Who's online, and what are they doing?
 * This class prepares the who's online data for the Who template.
 * It requires the who_view permission.
 * It is enabled with the who_enabled setting.
 * It is accessed via ?action=who.
 *
 * Uses Who template, main sub-template
 * Uses Who language file.
 */
class Who implements ActionInterface, Routable
{
	use ActionRouter;
	use ActionTrait;

	/**
	 * Dispatcher to whichever sub-action method is necessary.
	 */
	public function execute(): void
	{
		// Load the 'Who' template.
		Theme::loadTemplate('Who');

		// Permissions, permissions, permissions.
		User::$me->isAllowedTo('who_view');

		// You can't do anything if this is off.
		if (empty(Config::$modSettings['who_enabled'])) {
			ErrorHandler::fatalLang('who_off', false);
		}

		// Discourage robots from indexing this page.
		Utils::$context['robot_no_index'] = true;

		// Sort out... the column sorting.
		$sort_methods = [
			'user' => 'mem.real_name',
			'time' => 'lo.log_time',
		];

		$show_methods = [
			'members' => '(lo.id_member != 0)',
			'guests' => '(lo.id_member = 0)',
			'all' => '1=1',
		];

		// Store the sort methods and the show types for use in the template.
		Utils::$context['sort_methods'] = [
			'user' => Lang::getTxt('who_user', file: 'Who'),
			'time' => Lang::getTxt('who_time', file: 'Who'),
		];

		Utils::$context['show_methods'] = [
			'all' => Lang::getTxt('who_show_all', file: 'Who'),
			'members' => Lang::getTxt('who_show_members_only', file: 'Who'),
			'guests' => Lang::getTxt('who_show_guests_only', file: 'Who'),
		];

		// Can they see spiders too?
		if (
			!empty(Config::$modSettings['show_spider_online'])
			&& (
				Config::$modSettings['show_spider_online'] == 2
				|| User::$me->allowedTo('admin_forum')
			)
			&& !empty(Config::$modSettings['spider_name_cache'])
		) {
			$show_methods['spiders'] = '(lo.id_member = 0 AND lo.id_spider > 0)';
			$show_methods['guests'] = '(lo.id_member = 0 AND lo.id_spider = 0)';
			Utils::$context['show_methods']['spiders'] = Lang::getTxt('who_show_spiders_only', file: 'Who');
		} elseif (
			empty(Config::$modSettings['show_spider_online'])
			&& isset($_SESSION['who_online_filter'])
			&& $_SESSION['who_online_filter'] == 'spiders'
		) {
			unset($_SESSION['who_online_filter']);
		}

		// Does the user prefer a different sort direction?
		if (isset($_REQUEST['sort'], $sort_methods[$_REQUEST['sort']])) {
			Utils::$context['sort_by'] = $_SESSION['who_online_sort_by'] = $_REQUEST['sort'];
			$sort_method = $sort_methods[$_REQUEST['sort']];
		}
		// Did we set a preferred sort order earlier in the session?
		elseif (isset($_SESSION['who_online_sort_by'])) {
			Utils::$context['sort_by'] = $_SESSION['who_online_sort_by'];
			$sort_method = $sort_methods[$_SESSION['who_online_sort_by']];
		}
		// Default to last time online.
		else {
			Utils::$context['sort_by'] = $_SESSION['who_online_sort_by'] = 'time';
			$sort_method = 'lo.log_time';
		}

		Utils::$context['sort_direction'] = isset($_REQUEST['asc']) || (isset($_REQUEST['sort_dir']) && $_REQUEST['sort_dir'] == 'asc') ? 'up' : 'down';

		$conditions = [];

		if (!User::$me->allowedTo('moderate_forum')) {
			$conditions[] = '(COALESCE(mem.show_online, 1) = 1)';
		}

		// Fallback to top filter?
		if (isset($_REQUEST['submit_top'], $_REQUEST['show_top'])) {
			$_REQUEST['show'] = $_REQUEST['show_top'];
		}

		// Does the user wish to apply a filter?
		if (isset($_REQUEST['show'], $show_methods[$_REQUEST['show']])) {
			Utils::$context['show_by'] = $_SESSION['who_online_filter'] = $_REQUEST['show'];
		}
		// Perhaps we saved a filter earlier in the session?
		elseif (isset($_SESSION['who_online_filter'])) {
			Utils::$context['show_by'] = $_SESSION['who_online_filter'];
		} else {
			Utils::$context['show_by'] = 'members';
		}

		$conditions[] = $show_methods[Utils::$context['show_by']];

		// Get the total amount of members online.
		$request = Db::$db->query(
			'SELECT COUNT(*)
			FROM {db_prefix}log_online AS lo
				LEFT JOIN {db_prefix}members AS mem ON (lo.id_member = mem.id_member)' . (!empty($conditions) ? '
			WHERE ' . implode(' AND ', $conditions) : ''),
			[
			],
		);
		list($totalMembers) = Db::$db->fetch_row($request);
		$totalMembers = (int) $totalMembers;
		Db::$db->free_result($request);

		// Prepare some page index variables.
		Utils::$context['start'] = (int) $_REQUEST['start'];
		Utils::$context['page_index'] = new PageIndex(Config::$scripturl . '?action=who;sort=' . Utils::$context['sort_by'] . (Utils::$context['sort_direction'] == 'up' ? ';asc' : '') . ';show=' . Utils::$context['show_by'], Utils::$context['start'], $totalMembers, (int) Config::$modSettings['defaultMaxMembers']);

		// If the supplied start value was invalid, redirect to the correct one.
		if ($_REQUEST['start'] != Utils::$context['start']) {
			Utils::redirectexit(Utils::$context['page_index']->base_url . ';start=' . Utils::$context['start']);
		}

		// Look for people online, provided they don't mind if you see they are.
		Utils::$context['members'] = [];
		$member_ids = [];
		$url_data = [];

		$request = Db::$db->query(
			'SELECT
				lo.log_time, lo.id_member, lo.url, lo.ip AS ip, mem.real_name,
				lo.session, mg.online_color, COALESCE(mem.show_online, 1) AS show_online,
				lo.id_spider
			FROM {db_prefix}log_online AS lo
				LEFT JOIN {db_prefix}members AS mem ON (lo.id_member = mem.id_member)
				LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = {int:regular_member} THEN mem.id_post_group ELSE mem.id_group END)' . (!empty($conditions) ? '
			WHERE ' . implode(' AND ', $conditions) : '') . '
			ORDER BY {raw:sort_method} {raw:sort_direction}
			LIMIT {int:offset}, {int:limit}',
			[
				'regular_member' => 0,
				'sort_method' => $sort_method,
				'sort_direction' => Utils::$context['sort_direction'] == 'up' ? 'ASC' : 'DESC',
				'offset' => Utils::$context['start'],
				'limit' => Config::$modSettings['defaultMaxMembers'],
			],
		);

		while ($row = Db::$db->fetch_assoc($request)) {
			$actions = Utils::jsonDecode($row['url'], true);

			if ($actions === []) {
				continue;
			}

			// Send the information to the template.
			Utils::$context['members'][$row['session']] = [
				'id' => $row['id_member'],
				'ip' => User::$me->allowedTo('moderate_forum') ? new IP($row['ip']) : '',
				// It is *going* to be today or yesterday, so why keep that information in there?
				'time' => strtr(Time::create('@' . $row['log_time'])->format(), [Lang::getTxt('today', file: 'General') => '', Lang::getTxt('yesterday', file: 'General') => '']),
				'timestamp' => $row['log_time'],
				'query' => $actions,
				'is_hidden' => $row['show_online'] == 0,
				'id_spider' => $row['id_spider'],
				'color' => empty($row['online_color']) ? '' : $row['online_color'],
			];

			$url_data[$row['session']] = [$row['url'], (int) 
			$row['id_member']];
			$member_ids[] = $row['id_member'];
		}
		Db::$db->free_result($request);

		// Load the user data for these members.
		User::load($member_ids);

		// Are we showing spiders?
		$spiderFormatted = [];

		if (
			!empty(Config::$modSettings['show_spider_online'])
			&& !empty(Config::$modSettings['spider_name_cache'])
			&& (
				Config::$modSettings['show_spider_online'] == 2
				|| User::$me->allowedTo('admin_forum')
			)
		) {
			foreach (Utils::jsonDecode(Config::$modSettings['spider_name_cache'], true) as $id => $name) {
				$spiderFormatted[$id] = [
					'name' => $name,
					'group' => Lang::getTxt('spiders', file: 'General'),
					'link' => $name,
					'email' => $name,
				];
			}
		}

		$url_data = self::determineActions($url_data);

		// Setup the linktree and page title (do it down here because the language files are now loaded..)
		Utils::$context['page_title'] = Lang::getTxt('who_title', file: 'General');
		Utils::$context['linktree'][] = [
			'url' => Config::$scripturl . '?action=who',
			'name' => Lang::getTxt('who_title', file: 'General'),
		];

		// Put it in the context variables.
		foreach (Utils::$context['members'] as $i => $member) {
			$member['id'] = isset(User::$loaded[$member['id']]) ? $member['id'] : 0;

			$formatted = User::$loaded[$member['id']]->format();

			// Keep the IP that came from the database.
			$formatted['ip'] = $member['ip'];

			if ($member['id'] == 0) {
				if (isset($spiderFormatted[$member['id_spider']])) {
					$formatted = array_merge($formatted, $spiderFormatted[$member['id_spider']]);
				} else {
					$formatted = array_merge($formatted, [
						'link' => Lang::getTxt('guest_title', file: 'General'),
						'email' => Lang::getTxt('guest_title', file: 'General'),
					]);
				}
			}

			Utils::$context['members'][$i] = array_merge(Utils::$context['members'][$i], $formatted);

			Utils::$context['members'][$i]['action'] = $url_data[$i] ?? ['label' => 'who_hidden', 'class' => 'em'];
		}

		// Some people can't send personal messages...
		Utils::$context['can_send_pm'] = User::$me->allowedTo('pm_send');
		Utils::$context['can_send_email'] = User::$me->allowedTo('moderate_forum');

		// any profile fields disabled?
		Utils::$context['disabled_fields'] = isset(Config::$modSettings['disabled_profile_fields']) ? array_flip(explode(',', Config::$modSettings['disabled_profile_fields'])) : [];
	}

	/***********************
	 * Public static methods
	 ***********************/

	/**
	 * This method determines the actions of the members passed in URLs.
	 *
	 * Adding actions to the Who's Online list:
	 * Adding actions to this list is actually relatively easy...
	 *  - for actions anyone should be able to see, just add a string named whoall_ACTION.
	 *    (where ACTION is the action used in index.php.)
	 *  - for actions that have a subaction which should be represented differently, use whoall_ACTION_SUBACTION.
	 *  - for actions that include a topic, and should be restricted, use whotopic_ACTION.
	 *  - for actions that use a message, by msg or quote, use whopost_ACTION.
	 *  - for administrator-only actions, use whoadmin_ACTION.
	 *  - for actions that should be viewable only with certain permissions,
	 *    use whoallow_ACTION and add a list of possible permissions to the
	 *    self::$allowed_actions array, using ACTION as the key.
	 *
	 * @param mixed $urls a single url (string) or an array of arrays, each inner array being (JSON-encoded request data, id_member)
	 * @param string|bool $preferred_prefix = false
	 * @return array|string an array of descriptions if you passed an array, otherwise the string describing their current location.
	 */
	public static function determineActions(mixed $urls, string|bool $preferred_prefix = false): array|string
	{
		if (!User::$me->allowedTo('who_view')) {
			return [];
		}

		$lookups = [
			new \SMF\Who\Lookups\AllowedActionsLookup,
			new \SMF\Who\Lookups\BoardIndexLookup,
			new \SMF\Who\Lookups\EmptyActionLookup,
			new \SMF\Who\Lookups\PublicActionLookup($preferred_prefix),
			new \SMF\Who\Lookups\PublicSubactionLookup($preferred_prefix),
			new \SMF\Who\Lookups\AdminActionLookup,
		];
		$batched_lookups = [
			new \SMF\Who\Lookups\BoardLookup,
			new \SMF\Who\Lookups\MemberProfileLookup,
			new \SMF\Who\Lookups\MsgLookup,
			new \SMF\Who\Lookups\PostLookup,
			new \SMF\Who\Lookups\TopicLookup,
			new \SMF\Who\Lookups\TopicTxtLookup,
		];

		IntegrationHook::call('integrate_who_lookups', [&$lookups, &$batched_lookups]);
		IntegrationHook::call('integrate_who_allowed', [&$lookups[0]->allowed_actions]);

		// This hook is depreated because it is missing the corerct prefix.
		IntegrationHook::call('who_allowed', [&$lookups[0]->allowed_actions]);

		if (!\is_array($urls)) {
			$url_list = [[$urls, User::$me->id]];
		} else {
			$url_list = $urls;
		}

		// These are done to later query these in large chunks. (instead of one by one.)
		$requested_data = [
			'topics' => [],
			'profiles' => [],
			'boards' => [],
		];

		$data = [];

		foreach ($url_list as $k => $url) {
			// Get the request parameters..
			$actions = Utils::jsonDecode($url[0], true);

			if ($actions === []) {
				continue;
			}

			// If it's the admin or moderation center, and there is an area set, use that instead.
			if (isset($actions['action']) && ($actions['action'] === 'admin' || $actions['action'] === 'moderate') && isset($actions['area'])) {
				$actions['action'] = $actions['area'];
			}

			foreach ($lookups as $lookup)
			{
				if ($lookup->supports($actions))
				{
					$data[$k] = $lookup->getText($actions);
					break;
				}
			}

			if (!isset($data[$k])) {
				if (!empty($actions['action'])) {
					$data[$k] = Lang::getTxt('who_generic', $actions, file: 'Who');
				} else {
					$data[$k] = ['label' => 'who_unknown', 'class' => 'em'];
				}
			}

			if (isset($actions['error'])) {
				$error_message = Lang::getTxt(
					$actions['error'] == 'guest_login' ? 'who_guest_login' : $actions['error'],
					(array) ($actions['error_params'] ?? []),
					file: 'Who+Errors',
				);

				$error_message = str_replace('"', '&quot;', $error_message);

				if (!empty($error_message)) {
					$error_message = ' <span class="main_icons error" title="' . $error_message . '"></span>';

					if (\is_array($data[$k])) {
						$data[$k]['error_message'] = $error_message;
					} else {
						$data[$k] .= $error_message;
					}
				}
			}

			foreach ($batched_lookups as $lookup)
			{
				$name = $lookup::NAME;
				$id = $lookup->getId($actions, $url[1]);

				if ($id !== null) {
					// Queue it up for later.
					$requested_data[$name][$id][$k] = $lookup->getText($actions, $url[1]);

					// Assume they can't view it right now.
					$data[$k] = ['label' => 'who_hidden', 'class' => 'em'];
				}
			}

			// Maybe the action is integrated into another system?
			if (\count($integrate_actions = IntegrationHook::call('integrate_whos_online', [$actions])) > 0) {
				foreach ($integrate_actions as $integrate_action) {
					if (!empty($integrate_action)) {
						$data[$k] = $integrate_action;

						if (isset($actions['topic'], $requested_data['topics'][(int) $actions['topic']][$k])) {
							$requested_data['topics'][(int) $actions['topic']][$k] = $integrate_action;
						}

						if (isset($actions['board'], $requested_data['boards'][(int) $actions['board']][$k])) {
							$requested_data['boards'][(int) $actions['board']][$k] = $integrate_action;
						}

						if (isset($actions['u'], $requested_data['profiles'][(int) $actions['u']][$k])) {
							$requested_data['profiles'][(int) $actions['u']][$k] = $integrate_action;
						}
						break;
					}
				}
			}
		}

		foreach ($batched_lookups as $lookup)
		{
			$name = $lookup::NAME;

			if (empty($requested_data[$name])) {
				continue;
			}

			$records = $lookup->fetch(array_keys($requested_data[$name]));

			// Can we now replace the hidden sessions with something they can see?
			foreach ($requested_data[$name] as $id => $session_data) {
				if (isset($records[$id])) {
					foreach ($session_data as $k => $session_text) {
						$data[$k] = Lang::formatText($session_text, $lookup->getFormat($records[$id]));
					}
				}
			}
		}

		IntegrationHook::call('in0tegrate_whos_online_after', [&$url_list, &$data]);

		// This hook is depreated because it is missing the corerct prefix.
		//~ IntegrationHook::call('whos_online_after', [&$urls, &$data]);

		if (!\is_array($urls)) {
			return $data[0] ?? false;
		}

		return $data;
	}
}
