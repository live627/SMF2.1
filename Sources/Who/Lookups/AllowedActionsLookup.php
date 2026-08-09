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

final class AllowedActionsLookup implements LookupInterface
{
	/**
	 * Maps actions to the permissions required to view them in
	 * the Who's Online list.
	 *
	 * The array keys are action names and the values are lists of
	 * permissions that grant access to the action.
	 *
	 * @var array<string, string[]>
	 */
	public array $allowed_actions = [
		'admin' => ['moderate_forum', 'manage_membergroups', 'manage_bans', 'admin_forum', 'manage_permissions', 'send_mail', 'manage_attachments', 'manage_smileys', 'manage_boards', 'edit_news'],
		'ban' => ['manage_bans'],
		'boardrecount' => ['admin_forum'],
		'calendar' => ['calendar_view'],
		'corefeatures' => ['admin_forum'],
		'editnews' => ['edit_news'],
		'featuresettings' => ['admin_forum'],
		'languages' => ['admin_forum'],
		'logs' => ['admin_forum'],
		'mailing' => ['send_mail'],
		'mailqueue' => ['admin_forum'],
		'maintain' => ['admin_forum'],
		'manageattachments' => ['manage_attachments'],
		'manageboards' => ['manage_boards'],
		'managecalendar' => ['admin_forum'],
		'managesearch' => ['admin_forum'],
		'managesmileys' => ['manage_smileys'],
		'membergroups' => ['manage_membergroups'],
		'mlist' => ['view_mlist'],
		'moderate' => ['access_mod_center', 'moderate_forum', 'manage_membergroups'],
		'modsettings' => ['admin_forum'],
		'news' => ['edit_news', 'send_mail', 'admin_forum'],
		'optimizetables' => ['admin_forum'],
		'packages' => ['admin_forum'],
		'paidsubscribe' => ['admin_forum'],
		'permissions' => ['manage_permissions'],
		'postsettings' => ['admin_forum'],
		'regcenter' => ['admin_forum', 'moderate_forum'],
		'repairboards' => ['admin_forum'],
		'reports' => ['admin_forum'],
		'scheduledtasks' => ['admin_forum'],
		'search' => ['search_posts'],
		'search2' => ['search_posts'],
		'securitysettings' => ['admin_forum'],
		'sengines' => ['admin_forum'],
		'serversettings' => ['admin_forum'],
		'setcensor' => ['moderate_forum'],
		'setreserve' => ['moderate_forum'],
		'stats' => ['view_stats'],
		'theme' => ['admin_forum'],
		'viewerrorlog' => ['admin_forum'],
		'viewmembers' => ['moderate_forum'],
	];

	/****************
	 * Public methods
	 ****************/

	/**
	 * Registers an action and its associated permissions.
	 *
	 * Any user possessing at least one of the specified permissions
	 * will be able to view the action.
	 *
	 * @param string $action Action name.
	 * @param string[] $permissions Permissions that grant access.
	 */
	public function addAllowedAction(string $action, array $permissions): void
	{
		$this->allowed_actions[$action] = $permissions;
	}

	/**
	 * Adds a permission to an existing action.
	 *
	 * @param string $action Action name.
	 * @param string $permission Permission to add.
	 */
	public function addPermissionToAction(string $action, string $permission): void
	{
		$this->allowed_actions[$action][] = $permission;
	}

	/**
	 * Gets the permissions associated with an action.
	 *
	 * @param string $action Action name.
	 *
	 * @return string[] Permissions associated with the action, or an
	 *    empty array if the action is not registered.
	 */
	public function getAllowedAction(string $action): array
	{
		return $this->allowed_actions[$action] ?? [];
	}

	/**
	 * Determines whether an action has been registered.
	 *
	 * @param string $action Action name.
	 *
	 * @return bool Whether the action exists.
	 */
	public function hasAllowedAction(string $action): bool
	{
		return isset($this->allowed_actions[$action]);
	}

	/**
	 * Determines whether this lookup can handle the supplied action.
	 *
	 * An action is supported when it has been registered and a
	 * corresponding language string exists.
	 *
	 * @param array $actions Parsed request parameters.
	 *
	 * @return bool Whether the action is supported.
	 */
	public function supports(array $actions): bool
	{
		return isset($this->allowed_actions[$actions['action']])
			&& Lang::txtExists('whoallow_' . $actions['action'], file: 'Who');
	}

	/**
	 * Gets the Who's Online description for an action.
	 *
	 * Returns a localized description if the current user has the
	 * required permissions. Otherwise returns an appropriate fallback
	 * description or a hidden placeholder.
	 *
	 * @param array $actions Parsed request parameters.
	 *
	 * @return string|array Localized action text or hidden placeholder.
	 */
	public function getText(array $actions): string|array
	{
		$permission_names = $this->allowed_actions[$actions['action']];

		if (User::$me->allowedTo($permission_names)) {
			return Lang::getTxt('whoallow_' . $actions['action'], ['scripturl' => Config::$scripturl], file: 'Who');
		} elseif (\in_array('moderate_forum', $permission_names)) {
			return Lang::getTxt('who_moderate', file: 'Who');
		} elseif (\in_array('admin_forum', $permission_names)) {
			return Lang::getTxt('who_admin', file: 'Who');
		}

		return ['label' => 'who_hidden', 'class' => 'em'];
	}
}