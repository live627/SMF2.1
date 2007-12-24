<?php
/**********************************************************************************
* ManagePaid.php                                                                  *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 1                                      *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file contains all the screens that control settings for topics and
	posts.

	void ManagePaidSubscriptions()
		- the main entrance point for the 'Paid Subscription' screen.
		- accessed from ?action=admin;area=paidsubscribe.
		- calls the right function based on the given sub-action.
		- defaults to sub-action 'view'.
		- requires admin_forum permission for admin based actions.

	void ModifySubscriptionSettings()
		- set any setting related to paid subscriptions.
		- requires the moderate_forum permission
		- accessed from ?action=admin;area=paidsubscribe;sa=settings.

	void ViewSubscriptions()
		- view a list of all the current subscriptions
		- requires the admin_forum permission
		- accessed from ?action=admin;area=paidsubscribe;sa=view.

	void ModifySubscription()
		- edit a subscription.
		- accessed from ?action=admin;area=paidsubscribe;sa=modify.

	void ViewSubscribedUsers()
		- view a list of all the users who currently have a subscription.
		- requires the admin_forum permission.
		- subscription ID is required, in the form of $_GET['sid'].
		- accessed from ?action=admin;area=paidsubscribe;sa=viewsub.

	int list_getSubscribedUserCount()
		// !!

	array list_getSubscribedUsers()
		// !!

	void ModifyUserSubscription()
		- edit a users subscription.
		- accessed from ?action=admin;area=paidsubscribe;sa=modifyuser.

	void reapplySubscriptions(array users)
		- reapplies all subscription rules for each of the users.

	void addSubscription(int id_subscribe, int id_member)
		- add/extend a subscription for a member.

	void removeSubscription(int id_subscribe, int id_member)
		- remove a subscription from a user.

	array loadPaymentGateways()
		- checks the Sources directory for any files fitting the format of a payment gateway.
		- loads each file to check it's valid.
		- includes each file and returns the function name and whether it should work with this version of SMF.
*/

function ManagePaidSubscriptions()
{
	global $context, $txt, $scripturl, $sourcedir, $smfFunc;

	// Load the required language and template.
	loadLanguage('ManagePaid');
	loadTemplate('ManagePaid');

	$subActions = array(
		'modify' => array('ModifySubscription', 'admin_forum'),
		'modifyuser' => array('ModifyUserSubscription', 'admin_forum'),
		'settings' => array('ModifySubscriptionSettings', 'admin_forum'),
		'view' => array('ViewSubscriptions', 'admin_forum'),
		'viewsub' => array('ViewSubscribedUsers', 'admin_forum'),
	);

	// Default the sub-action to 'view subscriptions'.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'view';

	// Make sure you can do this.
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	$context['page_title'] = $txt['paid_subscriptions'];

	// Tabs for browsing the different subscription functions.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['paid_subscriptions'],
		'help' => '',
		'description' => $txt['paid_subscriptions_desc'],
		'tabs' => array(
			'view' => array(
				'description' => $txt['paid_subs_view_desc'],
			),
			'settings' => array(
				'description' => $txt['paid_subs_settings_desc'],
			),
		),
	);

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']][0]();
}

// Modify which payment methods are to be used.
function ModifySubscriptionSettings($return_config = false)
{
	global $context, $txt, $db_prefix, $modSettings, $sourcedir, $smfFunc, $scripturl;

	// If the currency is set to something different then we need to set it to other for this to work and set it back shortly.
	$modSettings['paid_currency'] = !empty($modSettings['paid_currency_code']) ? $modSettings['paid_currency_code'] : '';
	if (!empty($modSettings['paid_currency_code']) && !in_array($modSettings['paid_currency_code'], array('usd', 'eur', 'gbp')))
		$modSettings['paid_currency'] = 'other';
		
	// These are all the default settings.
	$config_vars = array(
			array('select', 'paid_email', array(0 => $txt['paid_email_no'], 1 => $txt['paid_email_error'], 2 => $txt['paid_email_all']), 'subtext' => $txt['paid_email_desc']),
			array('text', 'paid_email_to', 'subtext' => $txt['paid_email_to_desc'], 'size' => 60),
		'',
			'dummy_currency' => array('select', 'paid_currency', array('usd' => $txt['usd'], 'eur' => $txt['eur'], 'gbp' => $txt['gbp'], 'other' => $txt['other']), 'javascript' => 'onchange="toggleOther();"'),
			array('text', 'paid_currency_code', 'subtext' => $txt['paid_currency_code_desc'], 'size' => 5, 'force_div_id' => 'custom_currency_code_div'),
			array('text', 'paid_currency_symbol', 'subtext' => $txt['paid_currency_symbol_desc'], 'size' => 8, 'force_div_id' => 'custom_currency_symbol_div'),
	);

	// Now load all the other gateway settings.
	$gateways = loadPaymentGateways();
	foreach ($gateways as $gateway)
	{
		$gatewayClass = new $gateway['display_class']();
		$setting_data = $gatewayClass->getGatewaySettings();
		if (!empty($setting_data))
		{
			$config_vars[] = array('title', $gatewayClass->title, 'text_label' => (isset($txt['paidsubs_gateway_title_' . $gatewayClass->title]) ? $txt['paidsubs_gateway_title_' . $gatewayClass->title] : $gatewayClass->title));
			$config_vars = array_merge($config_vars, $setting_data);
		}
	}

	// Just searching?
	if ($return_config)
		return $config_vars;

	// Get the settings template fired up.
	require_once($sourcedir .'/ManageServer.php');

	// Some important context stuff
	$context['page_title'] = $txt['settings'];
	$context['sub_template'] = 'show_settings';
	$context['settings_message'] = '<span class="smalltext">' . $txt['paid_note'] . '</span>';

	// Get the final touches in place.
	$context['post_url'] = $scripturl . '?action=admin;area=paidsubscribe;save;sa=settings';
	$context['settings_title'] = $txt['settings'];

	// We want javascript for our currency options.
	$context['settings_insert_below'] = '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function toggleOther()
			{
				var otherOn = document.getElementById("paid_currency").value == \'other\';

				if (otherOn)
				{
					document.getElementById("custom_currency_code_div").style.display = "";
					document.getElementById("custom_currency_symbol_div").style.display = "";
				}
				else
				{
					document.getElementById("custom_currency_code_div").style.display = "none";
					document.getElementById("custom_currency_symbol_div").style.display = "none";
					
				}
			}
			toggleOther();
		// ]]></script>';

	// Saving the settings?
	if (isset($_GET['save']))
	{
		checkSession();

		// Sort out the currency stuff.
		if ($_POST['paid_currency'] != 'other')
		{
			$_POST['paid_currency_code'] = $_POST['paid_currency'];
			$_POST['paid_currency_symbol'] = $txt[$_POST['paid_currency'] . '_symbol'];
		}
		unset($config_vars['dummy_currency']);

		saveDBSettings($config_vars);

		redirectexit('action=admin;area=paidsubscribe;sa=settings');
	}

	// Prepare the settings...
	prepareDBSettingContext($config_vars);
}

// Are we looking at viewing the subscriptions?
function ViewSubscriptions()
{
	global $context, $txt, $db_prefix, $modSettings, $smfFunc, $sourcedir, $scripturl;

	// Not made the settings yet?
	if (empty($modSettings['paid_currency_symbol']))
		fatal_lang_error('paid_not_set_currency', false);

	// Some basic stuff.
	$context['page_title'] = $txt['paid_subs_view'];
	loadSubscriptions();

	$listOptions = array(
		'id' => 'subscription_list',
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=paidsubscribe;sa=view',
		'get_items' => array(
			'function' => create_function('', '
				global $context;
				return $context[\'subscriptions\'];
			'),
		),
		'get_count' => array(
			'function' => create_function('', '
				global $context;
				return count($context[\'subscriptions\']);
			'),
		),
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['paid_name'],
					'style' => 'text-align: left; width: 35%;',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $scripturl;

						return sprintf(\'<a href="%1$s?action=admin;area=paidsubscribe;sa=viewsub;sid=%2$s">%3$s</a>\', $scripturl, $rowData[\'id\'], $rowData[\'name\']);
					'),
				),
			),
			'cost' => array(
				'header' => array(
					'value' => $txt['paid_cost'],
					'style' => 'text-align: left;',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $context, $txt;

						return $rowData[\'flexible\'] ? \'<i>\' . $txt[\'flexible\'] . \'</i>\' : $rowData[\'cost\'] . \' / \' . $rowData[\'length\'];
					'),
				),
			),
			'pending' => array(
				'header' => array(
					'value' => $txt['paid_pending'],
				),
				'data' => array(
					'db_htmlsafe' => 'pending',
					'style' => 'text-align: center;',
				),
			),
			'finished' => array(
				'header' => array(
					'value' => $txt['paid_finished'],
				),
				'data' => array(
					'db_htmlsafe' => 'finished',
					'style' => 'text-align: center;',
				),
			),
			'total' => array(
				'header' => array(
					'value' => $txt['paid_active'],
				),
				'data' => array(
					'db_htmlsafe' => 'total',
					'style' => 'text-align: center;',
				),
			),
			'is_active' => array(
				'header' => array(
					'value' => $txt['paid_is_active'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $context, $txt;

						return \'<span style="color: \' . ($rowData[\'active\'] ? \'green\' : \'red\') . \'">\' . ($rowData[\'active\'] ? $txt[\'yes\'] : $txt[\'no\']) . \'</span>\';
					'),
					'style' => 'text-align: center;',
				),
			),
			'modify' => array(
				'data' => array(
					'function' => create_function('$rowData', '
						global $context, $txt, $scripturl;

						return \'<a href="\' . $scripturl . \'?action=admin;area=paidsubscribe;sa=modify;sid=\' . $rowData[\'id\'] . \'">\' . $txt[\'modify\'] . \'</a>\';
					'),
					'style' => 'text-align: center;',
				),
			),
			'delete' => array(
				'data' => array(
					'function' => create_function('$rowData', '
						global $context, $txt, $scripturl;

						return \'<a href="\' . $scripturl . \'?action=admin;area=paidsubscribe;sa=modify;delete;sid=\' . $rowData[\'id\'] . \'">\' . $txt[\'delete\'] . \'</a>\';
					'),
					'style' => 'text-align: center;',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=paidsubscribe;sa=modify',
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
					<input type="submit" name="add" value="' . $txt['paid_add_subscription'] . '" />
				',
				'class' => 'titlebg',
				'style' => 'text-align: right;',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'subscription_list';
}

// Adding, editing and deleting subscriptions.
function ModifySubscription()
{
	global $context, $txt, $db_prefix, $modSettings, $smfFunc;

	$context['sub_id'] = isset($_REQUEST['sid']) ? (int) $_REQUEST['sid'] : 0;
	$context['action_type'] = $context['sub_id'] ? (isset($_REQUEST['delete']) ? 'delete' : 'edit') : 'add';

	// Setup the template.
	$context['sub_template'] = $context['action_type'] == 'delete' ? 'delete_subscription' : 'modify_subscription';
	$context['page_title'] = $txt['paid_' . $context['action_type'] . '_subscription'];

	// Delete it?
	if (isset($_POST['delete_confirm']) && isset($_REQUEST['delete']))
	{
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}subscriptions
			WHERE id_subscribe = $context[sub_id]
			LIMIT 1", __FILE__, __LINE__);

		redirectexit('action=admin;area=paidsubscribe;view');
	}

	// Saving?
	if (isset($_POST['save']))
	{
		// Some cleaning...
		$isActive = isset($_POST['active']) ? 1 : 0;
		$isRepeatable = isset($_POST['repeatable']) ? 1 : 0;
		$allowpartial = isset($_POST['allow_partial']) ? 1 : 0;
		$reminder = isset($_POST['reminder']) ? (int) $_POST['reminder'] : 0;
		$emailComplete = strlen($_POST['emailcomplete']) > 10 ? trim($_POST['emailcomplete']) : '';

		// Is this a fixed one?
		if ($_POST['duration_type'] == 'fixed')
		{
			// Clean the span.
			$span = $_POST['span_value'] . $_POST['span_unit'];

			// Sort out the cost.
			$cost = array('fixed' => $_POST['cost']);
		}
		// Flexible is harder but more fun ;)
		else
		{
			$span = 'F';

			$cost = array(
				'day' => $_POST['cost_day'],
				'week' => $_POST['cost_week'],
				'month' => $_POST['cost_month'],
				'year' => $_POST['cost_year'],
			);
		}
		$cost = serialize($cost);

		// Yep, time to do additional groups.
		$addgroups = array();
		if (!empty($_POST['addgroup']))
			foreach ($_POST['addgroup'] as $id => $dummy)
				$addgroups[] = (int) $id;
		$addgroups = implode(',', $addgroups);

		// Is it new?!
		if ($context['action_type'] == 'add')
		{
			$smfFunc['db_query']('', "
				INSERT INTO {$db_prefix}subscriptions
					(name, description, active, length, cost, id_group, add_groups, repeatable, allow_partial, email_complete, reminder)
				VALUES
					('$_POST[name]', '$_POST[desc]', $isActive, '$span', '$cost', $_POST[prim_group], '$addgroups', $isRepeatable, $allowpartial, '$emailComplete', $reminder)", __FILE__, __LINE__);
		}
		// Otherwise must be editing.
		else
		{
			// Don't do groups if there are active members
			$request = $smfFunc['db_query']('', "
				SELECT COUNT(*)
				FROM {$db_prefix}log_subscribed
				WHERE id_subscribe = $context[sub_id]
					AND status = 1", __FILE__, __LINE__);
			list ($disableGroups) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}subscriptions
					SET name = '$_POST[name]', description = '$_POST[desc]', active = $isActive, length = '$span',
					cost = '$cost'" . ($disableGroups ? '' : ", id_group = $_POST[prim_group], add_groups = '$addgroups'") . ",
					repeatable = $isRepeatable, allow_partial = $allowpartial, email_complete = '$emailComplete', reminder = $reminder
				WHERE id_subscribe = $context[sub_id]", __FILE__, __LINE__);
		}

		redirectexit('action=admin;area=paidsubscribe;view');
	}

	// Defaults.
	if ($context['action_type'] == 'add')
	{
		$context['sub'] = array(
			'name' => '',
			'desc' => '',
			'cost' => array(
				'fixed' => 0,
			),
			'span' => array(
				'value' => '',
				'unit' => 'D',
			),
			'prim_group' => 0,
			'add_groups' => array(),
			'active' => 1,
			'repeatable' => 1,
			'allow_partial' => 0,
			'duration' => 'fixed',
			'email_complete' => '',
			'reminder' => 0,
		);
	}
	// Otherwise load up all the details.
	else
	{
		$request = $smfFunc['db_query']('', "
			SELECT name, description, cost, length, id_group, add_groups, active, repeatable, allow_partial, email_complete, reminder
			FROM {$db_prefix}subscriptions
			WHERE id_subscribe = $context[sub_id]
			LIMIT 1", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			// Sort the date.
			preg_match('~(\d*)(\w)~', $row['length'], $match);
			if (isset($match[2]))
			{
				$span_value = $match[1];
				$span_unit = $match[2];
			}
			else
			{
				$span_value = 0;
				$span_unit = 'D';
			}

			// Is this a flexible one?
			if ($row['length'] == 'F')
				$isFlexible = true;
			else
				$isFlexible = false;

			$context['sub'] = array(
				'name' => $row['name'],
				'desc' => $row['description'],
				'cost' => @unserialize($row['cost']),
				'span' => array(
					'value' => $span_value,
					'unit' => $span_unit,
				),
				'prim_group' => $row['id_group'],
				'add_groups' => explode(',', $row['add_groups']),
				'active' => $row['active'],
				'repeatable' => $row['repeatable'],
				'allow_partial' => $row['allow_partial'],
				'duration' => $isFlexible ? 'flexible' : 'fixed',
				'email_complete' => htmlspecialchars($row['email_complete']),
				'reminder' => $row['reminder'],
			);
		}
		$smfFunc['db_free_result']($request);

		// Does this have members who are active?
		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}log_subscribed
			WHERE id_subscribe = $context[sub_id]
				AND status = 1", __FILE__, __LINE__);
		list ($context['disable_groups']) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
	}

	// Load up all the groups.
	$request = $smfFunc['db_query']('', "
		SELECT id_group, group_name
		FROM {$db_prefix}membergroups
		WHERE id_group != 3
			AND min_posts = -1", __FILE__, __LINE__);
	$context['groups'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['groups'][$row['id_group']] = $row['group_name'];
	$smfFunc['db_free_result']($request);
}

// View all the users subscribed to a particular subscription!
function ViewSubscribedUsers()
{
	global $context, $txt, $db_prefix, $modSettings, $scripturl, $options, $smfFunc, $sourcedir;

	// Setup the template.
	$context['page_title'] = $txt['viewing_users_subscribed'];

	// ID of the subscription.
	$context['sub_id'] = (int) $_REQUEST['sid'];

	// Load the subscription information.
	$request = $smfFunc['db_query']('', "
		SELECT id_subscribe, name, description, cost, length, id_group, add_groups, active
		FROM {$db_prefix}subscriptions
		WHERE id_subscribe = $context[sub_id]", __FILE__, __LINE__);
	// Something wrong?
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_access');
	// Do the subscription context.
	$row = $smfFunc['db_fetch_assoc']($request);
	$context['subscription'] = array(
		'id' => $row['id_subscribe'],
		'name' => $row['name'],
		'desc' => $row['description'],
		'active' => $row['active'],
	);
	$smfFunc['db_free_result']($request);

	// Are we searching for people?
	$search_string = isset($_POST['ssearch']) && !empty($_POST['sub_search']) ? ' AND IFNULL(mem.real_name, \'$txt[guest]\') LIKE \'%' . $smfFunc['db_escape_string']($_POST['sub_search']) . '%\'' : '';

	$listOptions = array(
		'id' => 'subscribed_users_list',
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=paidsubscribe;sa=viewsub;sid=' . $context['sub_id'],
		'default_sort_col' => 'name',
		'get_items' => array(
			'function' => 'list_getSubscribedUsers',
			'params' => array(
				$context['sub_id'],
				$search_string,
			),
		),
		'get_count' => array(
			'function' => 'list_getSubscribedUserCount',
			'params' => array(
				$context['sub_id'],
				$search_string,
			),
		),
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['who_member'],
					'style' => 'text-align: left; width: 20%;',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $context, $txt, $scripturl;

						return $rowData[\'id_member\'] == 0 ? $txt[\'guest\'] : \'<a href="\' . $scripturl . \'?action=profile;u=\' . $rowData[\'id_member\'] . \'">\' . $rowData[\'name\'] . \'</a>\';
					'),
				),
				'sort' => array(
					'default' => 'name',
					'reverse' => 'name DESC',
				),
			),
			'status' => array(
				'header' => array(
					'value' => $txt['paid_status'],
					'style' => 'text-align: left; width: 10%;',
				),
				'data' => array(
					'db_htmlsafe' => 'status_text',
				),
				'sort' => array(
					'default' => 'status',
					'reverse' => 'status DESC',
				),
			),
			'payments_pending' => array(
				'header' => array(
					'value' => $txt['paid_payments_pending'],
					'style' => 'text-align: left; width: 10%;',
				),
				'data' => array(
					'db_htmlsafe' => 'pending',
				),
				'sort' => array(
					'default' => 'payments_pending',
					'reverse' => 'payments_pending DESC',
				),
			),
			'start_time' => array(
				'header' => array(
					'value' => $txt['start_date'],
					'style' => 'text-align: left; width: 20%;',
				),
				'data' => array(
					'db_htmlsafe' => 'start_date',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'start_time',
					'reverse' => 'start_time DESC',
				),
			),
			'end_time' => array(
				'header' => array(
					'value' => $txt['end_date'],
					'style' => 'text-align: left; width: 20%;',
				),
				'data' => array(
					'db_htmlsafe' => 'end_date',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'end_time',
					'reverse' => 'end_time DESC',
				),
			),
			'modify' => array(
				'header' => array(
					'style' => 'width: 10%;',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $context, $txt, $scripturl;

						return \'<a href="\' . $scripturl . \'?action=admin;area=paidsubscribe;sa=modifyuser;lid=\' . $rowData[\'id\'] . \'">\' . $txt[\'modify\'] . \'</a>\';
					'),
					'style' => 'text-align: center;',
				),
			),
			'delete' => array(
				'header' => array(
					'style' => 'width: 4%;',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $context, $txt, $scripturl;

						return \'<input type="checkbox" name="delsub[\' . $rowData[\'id\'] . \']" class="check" />\';
					'),
					'style' => 'text-align: center;',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=paidsubscribe;sa=modifyuser;sid=' . $context['sub_id'],
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
					<div style="float: left;">
						<input type="submit" name="add" value="' . $txt['paid_add_subscription'] . '" />
					</div>
					<div style="float: right;">
						<input type="submit" name="finished" value="' . $txt['complete_selected'] . '" onclick="return confirm(\'' . $txt['complete_are_sure'] . '\');"/>
						<input type="submit" name="delete" value="' . $txt['delete_selected'] . '" onclick="return confirm(\'' . $txt['delete_are_sure'] . '\');"/>
					</div>
				',
				'class' => 'titlebg',
			),
			array(
				'position' => 'top_of_list',
				'value' => '
					<div style="float: left;">
						' . sprintf($txt['view_users_subscribed'], $row['name']) . '
					</div>
					<div style="float: right;">
						<input type="text" name="sub_search" value="" />
						<input type="submit" name="ssearch" value="' . $txt['search_sub'] . '" />
					</div>
				',
				'class' => 'titlebg',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'subscribed_users_list';
}

// Returns how many people are subscribed to a paid subscription.
function list_getSubscribedUserCount($id_sub, $search_string)
{
	global $smfFunc, $db_prefix;

	// Get the total amount of users.
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*) AS total_subs
		FROM {$db_prefix}log_subscribed AS ls
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = ls.id_member)
		WHERE id_subscribe = $id_sub $search_string
			AND (ls.end_time != 0 OR ls.payments_pending != 0)", __FILE__, __LINE__);
	list ($memberCount) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	return $memberCount;
}

function list_getSubscribedUsers($start, $items_per_page, $sort, $id_sub, $search_string)
{
	global $smfFunc, $db_prefix, $txt;

	$request = $smfFunc['db_query']('', "
		SELECT ls.id_sublog, IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, '$txt[guest]') AS name, ls.start_time, ls.end_time,
			ls.status, ls.payments_pending
		FROM {$db_prefix}log_subscribed AS ls
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = ls.id_member)
		WHERE ls.id_subscribe = $id_sub $search_string
			AND (ls.end_time != 0 OR ls.payments_pending != 0)
		ORDER BY $sort
		LIMIT $start, $items_per_page", __FILE__, __LINE__);
	$subscribers = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$subscribers[] = array(
			'id' => $row['id_sublog'],
			'id_member' => $row['id_member'],
			'name' => $row['name'],
			'start_date' => timeformat($row['start_time'], false),
			'end_date' => $row['end_time'] == 0 ? 'N/A' : timeformat($row['end_time'], false),
			'pending' => $row['payments_pending'],
			'status' => $row['status'],
			'status_text' => $row['status'] == 0 ? ($row['payments_pending'] == 0 ? $txt['paid_finished'] : $txt['paid_pending']) : $txt['paid_active'],
		);
	$smfFunc['db_free_result']($request);

	return $subscribers;
}

// Edit or add a user subscription.
function ModifyUserSubscription()
{
	global $context, $txt, $db_prefix, $modSettings, $smfFunc;

	loadSubscriptions();

	$context['log_id'] = isset($_REQUEST['lid']) ? (int) $_REQUEST['lid'] : 0;
	$context['sub_id'] = isset($_REQUEST['sid']) ? (int) $_REQUEST['sid'] : 0;
	$context['action_type'] = $context['log_id'] ? 'edit' : 'add';

	// Setup the template.
	$context['sub_template'] = 'modify_user_subscription';
	$context['page_title'] = $txt[$context['action_type'] . '_subscriber'];

	// If we haven't been passed the subscription ID get it.
	if ($context['log_id'] && !$context['sub_id'])
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_subscribe
			FROM {$db_prefix}log_subscribed
			WHERE id_sublog = $context[log_id]", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('no_access');
		list ($context['sub_id']) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
	}

	if (!isset($context['subscriptions'][$context['sub_id']]))
		fatal_lang_error('no_access');
	$context['current_subscription'] = $context['subscriptions'][$context['sub_id']];

	// Searching?
	if (isset($_POST['ssearch']))
	{
		return ViewSubscribedUsers();
	}
	// Saving?
	elseif (isset($_REQUEST['save_sub']))
	{
		// Work out the dates...
		$starttime = mktime($_POST['hour'], $_POST['minute'], 0, $_POST['month'], $_POST['day'], $_POST['year']);
		$endtime = mktime($_POST['hourend'], $_POST['minuteend'], 0, $_POST['monthend'], $_POST['dayend'], $_POST['yearend']);

		// Status.
		$status = $_POST['status'];

		// New one?
		if (empty($context['log_id']))
		{
			// Find the user...
			$request = $smfFunc['db_query']('', "
				SELECT id_member, id_group
				FROM {$db_prefix}members
				WHERE real_name = '$_POST[name]'
				LIMIT 1", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($request) == 0)
				fatal_lang_error('error_member_not_found');
			
			list ($id_member, $id_group) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			// Ensure the member doesn't already have a subscription!
			$request = $smfFunc['db_query']('', "
				SELECT id_subscribe
				FROM {$db_prefix}log_subscribed
				WHERE id_subscribe = $context[sub_id]
					AND id_member = $id_member", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($request) != 0)
				fatal_lang_error('member_already_subscribed');
			$smfFunc['db_free_result']($request);

			// Actually put the subscription in place.
			if ($status == 1)
				addSubscription($context['sub_id'], $id_member, 0, $starttime, $endtime);
			else
			{
				$smfFunc['db_query']('', "
					INSERT INTO {$db_prefix}log_subscribed
						(id_subscribe, id_member, old_id_group, start_time, end_time, status)
					VALUES
						($context[sub_id], $id_member, $id_group, $starttime, $endtime, $status)", __FILE__, __LINE__);
			}
		}
		// Updating.
		else
		{
			$request = $smfFunc['db_query']('', "
				SELECT id_member, status
				FROM {$db_prefix}log_subscribed
				WHERE id_sublog = $context[log_id]", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($request) == 0)
				fatal_lang_error('no_access');

			list ($id_member, $old_status) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			// Pick the right permission stuff depending on what the status is changing from/to.
			if ($old_status == 1 && $status != 1)
				removeSubscription($context['sub_id'], $id_member);
			elseif ($status == 1 && $old_status != 1)
			{
				addSubscription($context['sub_id'], $id_member, 0, $starttime, $endtime);
			}
			else
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}log_subscribed
					SET start_time = $starttime, end_time = $endtime, status = $status
					WHERE id_sublog = $context[log_id]", __FILE__, __LINE__);
			}
		}

		// Done - redirect...
		redirectexit('action=admin;area=paidsubscribe;sa=viewsub;sid=' . $context['sub_id']);
	}
	// Deleting?
	elseif (isset($_REQUEST['delete']) || isset($_REQUEST['finished']))
	{
		// Do the actual deletes!
		if (!empty($_REQUEST['delsub']))
		{
			$toDelete = array();
			foreach ($_REQUEST['delsub'] as $id => $dummy)
				$toDelete[] = (int) $id;

			$request = $smfFunc['db_query']('', "
				SELECT id_subscribe, id_member
				FROM {$db_prefix}log_subscribed
				WHERE id_sublog IN (" . implode(',', $toDelete) . ")", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				removeSubscription($row['id_subscribe'], $row['id_member'], isset($_REQUEST['delete']));
			$smfFunc['db_free_result']($request);
		}
		redirectexit('action=admin;area=paidsubscribe;sa=viewsub;sid=' . $context['sub_id']);
	}

	// Default attributes.
	if ($context['action_type'] == 'add')
	{
		$context['sub'] = array(
			'id' => 0,
			'start' => array(
				'year' => (int) strftime('%Y', time()),
				'month' => (int) strftime('%m', time()),
				'day' => (int) strftime('%d', time()),
				'hour' => (int) strftime('%H', time()),
				'min' => (int) strftime('%M', time()) < 10 ? '0' . (int) strftime('%M', time()) : (int) strftime('%M', time()),
				'last_day' => 0,
			),
			'end' => array(
				'year' => (int) strftime('%Y', time()),
				'month' => (int) strftime('%m', time()),
				'day' => (int) strftime('%d', time()),
				'hour' => (int) strftime('%H', time()),
				'min' => (int) strftime('%M', time()) < 10 ? '0' . (int) strftime('%M', time()) : (int) strftime('%M', time()),
				'last_day' => 0,
			),
			'status' => 1,
		);
		$context['sub']['start']['last_day'] = (int) strftime('%d', mktime(0, 0, 0, $context['sub']['start']['month'] == 12 ? 1 : $context['sub']['start']['month'] + 1, 0, $context['sub']['start']['month'] == 12 ? $context['sub']['start']['year'] + 1 : $context['sub']['start']['year']));
		$context['sub']['end']['last_day'] = (int) strftime('%d', mktime(0, 0, 0, $context['sub']['end']['month'] == 12 ? 1 : $context['sub']['end']['month'] + 1, 0, $context['sub']['end']['month'] == 12 ? $context['sub']['end']['year'] + 1 : $context['sub']['end']['year']));

		if (isset($_GET['uid']))
		{
			$request = $smfFunc['db_query']('', "
				SELECT real_name
				FROM {$db_prefix}members
				WHERE id_member = " . (int) $_GET['uid'], __FILE__, __LINE__);
			list ($context['sub']['username']) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);
		}
		else
			$context['sub']['username'] = '';
	}
	// Otherwise load the existing info.
	else
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_sublog, id_subscribe, ls.id_member, start_time, end_time, status, payments_pending, pending_details,
				IFNULL(mem.real_name, '') AS username
			FROM {$db_prefix}log_subscribed AS ls
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = ls.id_member)
			WHERE id_sublog = $context[log_id]
			LIMIT 1", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error(1);
		$row = $smfFunc['db_fetch_assoc']($request);
		$smfFunc['db_free_result']($request);

		// Any pending payments?
		$context['pending_payments'] = array();
		if (!empty($row['pending_details']))
		{
			$pending_details = @unserialize($row['pending_details']);
			foreach ($pending_details as $id => $pending)
			{
				// Only this type need be displayed.
				if ($pending[3] == 'payback')
				{
					// Work out what the options were.
					$costs = @unserialize($context['current_subscription']['real_cost']);

					if ($context['current_subscription']['real_length'] == 'F')
					{
						foreach ($costs as $duration => $cost)
						{
							if ($cost != 0 && $cost == $pending[1] && $duration == $pending[2])
								$context['pending_payments'][$id] = array(
									'desc' => sprintf($modSettings['paid_currency_symbol'], $cost . '/' . $txt[$duration]),
								);
						}
					}
					elseif ($costs['fixed'] == $pending[1])
					{
						$context['pending_payments'][$id] = array(
							'desc' => sprintf($modSettings['paid_currency_symbol'], $costs['fixed']),
						);
					}
				}
			}

			// Check if we are adding/removing any.
			if (isset($_GET['pending']))
			{
				foreach ($pending_details as $id => $pending)
				{
					// Found the one to action?
					if ($_GET['pending'] == $id && $pending[3] == 'payback' && isset($context['pending_payments'][$id]))
					{
						// Flexible?
						if (isset($_GET['accept']))
							addSubscription($context['current_subscription']['id'], $row['id_member'], $context['current_subscription']['real_length'] == 'F' ? strtoupper(substr($pending[2], 0, 1)) : 0);
						unset($pending_details[$id]);

						$new_details = $smfFunc['db_escape_string'](serialize($pending_details));

						// Update the entry.
						$smfFunc['db_query']('', "
							UPDATE {$db_prefix}log_subscribed
							SET payments_pending = payments_pending - 1, pending_details = '$new_details'
							WHERE id_sublog = $context[log_id]", __FILE__, __LINE__);

						// Reload
						redirectexit('action=admin;area=paidsubscribe;sa=modifyuser;lid=' . $context['log_id']);
					}
				}
			}
		}

		$context['sub_id'] = $row['id_subscribe'];
		$context['sub'] = array(
			'id' => 0,
			'start' => array(
				'year' => (int) strftime('%Y', $row['start_time']),
				'month' => (int) strftime('%m', $row['start_time']),
				'day' => (int) strftime('%d', $row['start_time']),
				'hour' => (int) strftime('%H', $row['start_time']),
				'min' => (int) strftime('%M', $row['start_time']) < 10 ? '0' . (int) strftime('%M', $row['start_time']) : (int) strftime('%M', $row['start_time']),
				'last_day' => 0,
			),
			'end' => array(
				'year' => (int) strftime('%Y', $row['end_time']),
				'month' => (int) strftime('%m', $row['end_time']),
				'day' => (int) strftime('%d', $row['end_time']),
				'hour' => (int) strftime('%H', $row['end_time']),
				'min' => (int) strftime('%M', $row['end_time']) < 10 ? '0' . (int) strftime('%M', $row['end_time']) : (int) strftime('%M', $row['end_time']),
				'last_day' => 0,
			),
			'status' => $row['status'],
			'username' => $row['username'],
		);
		$context['sub']['start']['last_day'] = (int) strftime('%d', mktime(0, 0, 0, $context['sub']['start']['month'] == 12 ? 1 : $context['sub']['start']['month'] + 1, 0, $context['sub']['start']['month'] == 12 ? $context['sub']['start']['year'] + 1 : $context['sub']['start']['year']));
		$context['sub']['end']['last_day'] = (int) strftime('%d', mktime(0, 0, 0, $context['sub']['end']['month'] == 12 ? 1 : $context['sub']['end']['month'] + 1, 0, $context['sub']['end']['month'] == 12 ? $context['sub']['end']['year'] + 1 : $context['sub']['end']['year']));
	}
}

// Re-apply subscription rules.
function reapplySubscriptions($users)
{
	global $db_prefix, $smfFunc;

	// Make it an array.
	if (!is_array($users))
		$users = array($users);

	// Get all the members current groups.
	$groups = array();
	$request = $smfFunc['db_query']('', "
		SELECT id_member, id_group, additional_groups
		FROM {$db_prefix}members
		WHERE id_member IN (" . implode(',', $users) . ")", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$groups[$row['id_member']] = array(
			'primary' => $row['id_group'],
			'additional' => explode(',', $row['additional_groups']),
		);
	}
	$smfFunc['db_free_result']($request);

	$request = $smfFunc['db_query']('', "
		SELECT ls.id_member, ls.old_id_group, s.id_group, s.add_groups
		FROM {$db_prefix}log_subscribed AS ls
			INNER JOIN {$db_prefix}subscriptions AS s ON (s.id_subscribe = ls.id_subscribe)
		WHERE ls.id_member IN (" . implode(',', $users) . ")
			AND ls.end_time > " . time(), __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Specific primary group?
		if ($row['id_group'] != 0)
		{
			// If this is changing - add the old one to the additional groups so it's not lost.
			if ($row['id_group'] != $groups[$row['id_member']]['primary'])
				$groups[$row['id_member']]['additional'][] = $groups[$row['id_member']]['primary'];
			$groups[$row['id_member']]['primary'] = $row['id_group'];
		}

		// Additional groups.
		if (!empty($row['add_groups']))
			$groups[$row['id_member']]['additional'] = array_merge($groups[$row['id_member']]['additional'], explode(',', $row['add_groups']));
	}
	$smfFunc['db_free_result']($request);

	// Update all the members.
	foreach ($groups as $id => $group)
	{
		$group['additional'] = array_unique($group['additional']);
		foreach ($group['additional'] as $key => $value)
			if (empty($value))
				unset($group['additional'][$key]);
		$addgroups = implode(',', $group['additional']);

		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}members
			SET id_group = $group[primary], additional_groups = '$addgroups'
			WHERE id_member = $id
			LIMIT 1", __FILE__, __LINE__);
	}
}

// Add or extend a subscription of a user.
function addSubscription($id_subscribe, $id_member, $renewel = 0, $forceStartTime = 0, $forceEndTime = 0)
{
	global $db_prefix, $context, $smfFunc;

	// Take the easy way out...
	loadSubscriptions();

	// Exists, yes?
	if (!isset($context['subscriptions'][$id_subscribe]))
		return;

	$curSub = $context['subscriptions'][$id_subscribe];

	// Grab the duration.
	$duration = $curSub['num_length'];

	// If this is a renewel change the duration to be correct.
	if (!empty($renewel))
	{
		switch ($renewel)
		{
			case 'D':
				$duration = 86400;
				break;
			case 'W':
				$duration = 604800;
				break;
			case 'M':
				$duration = 2629743;
				break;
			case 'Y':
				$duration = 31556926;
				break;
			default:
				break;
		}
	}

	// Firstly, see whether it exists, and is active. If so then this is meerly an extension.
	$request = $smfFunc['db_query']('', "
		SELECT id_sublog, end_time, start_time
		FROM {$db_prefix}log_subscribed
		WHERE id_subscribe = $id_subscribe
			AND id_member = $id_member
			AND status = 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) != 0)
	{
		list ($id_sublog, $endtime, $starttime) = $smfFunc['db_fetch_row']($request);

		// If this has already expired but is active, extension means the period from now.
		if ($endtime < time())
			$endtime = time();
		if ($starttime == 0)
			$starttime = time();

		// Work out the new expiry date.
		$endtime += $duration;

		if ($forceEndTime != 0)
			$endtime = $forceEndTime;

		// As everything else should be good, just update!
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}log_subscribed
			SET end_time = $endtime, start_time = $starttime
			WHERE id_sublog = $id_sublog", __FILE__, __LINE__);

		return;
	}
	$smfFunc['db_free_result']($request);

	// If we're here, that means we don't have an active subscription - that means we need to do some work!
	$request = $smfFunc['db_query']('', "
		SELECT m.id_group, m.additional_groups
		FROM {$db_prefix}members AS m
		WHERE m.id_member = $id_member", __FILE__, __LINE__);
	// Just in case the member doesn't exist.
	if ($smfFunc['db_num_rows']($request) == 0)
		return;

	list ($old_id_group, $additional_groups) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Prepare additional groups.
	$newAddGroups = explode(',', $curSub['add_groups']);
	$curAddGroups = explode(',', $additional_groups);

	$newAddGroups = array_merge($newAddGroups, $curAddGroups);

	// Simple, simple, simple - hopefully... id_group first.
	if ($curSub['prim_group'] != 0)
	{
		$id_group = $curSub['prim_group'];

		// Ensure their old priviledges are maintained.
		if ($old_id_group != 0)
			$newAddGroups[] = $old_id_group;
	}
	else
		$id_group = $old_id_group;

	// Yep, make sure it's unique, and no empties.
	foreach ($newAddGroups as $k => $v)
		if (empty($v))
			unset($newAddGroups[$k]);
	$newAddGroups = array_unique($newAddGroups);
	$newAddGroups = implode(',', $newAddGroups);

	// Store the new settings.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}members
		SET id_group = $id_group, additional_groups = '$newAddGroups'
		WHERE id_member = $id_member", __FILE__, __LINE__);

	// Now log the subscription - maybe we have a dorment subscription we can restore?
	$request = $smfFunc['db_query']('', "
		SELECT id_sublog, end_time, start_time
		FROM {$db_prefix}log_subscribed
		WHERE id_subscribe = $id_subscribe
			AND id_member = $id_member", __FILE__, __LINE__);
	//!!! Don't really need to do this twice...
	if ($smfFunc['db_num_rows']($request) != 0)
	{
		list ($id_sublog, $endtime, $starttime) = $smfFunc['db_fetch_row']($request);

		// If this has already expired but is active, extension means the period from now.
		if ($endtime < time())
			$endtime = time();
		if ($starttime == 0)
			$starttime = time();

		// Work out the new expiry date.
		$endtime += $duration;

		if ($forceEndTime != 0)
			$endtime = $forceEndTime;

		// As everything else should be good, just update!
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}log_subscribed
			SET start_time = $starttime, end_time = $endtime, old_id_group = $old_id_group, status = 1,
				reminder_sent = 0
			WHERE id_sublog = $id_sublog", __FILE__, __LINE__);

		return;
	}
	$smfFunc['db_free_result']($request);

	// Otherwise a very simple insert.
	$endtime = time() + $duration;
	if ($forceEndTime != 0)
		$endtime = $forceEndTime;

	if ($forceStartTime == 0)
		$starttime = time();
	else
		$starttime = $forceStartTime;

	$smfFunc['db_query']('', "
		INSERT INTO {$db_prefix}log_subscribed
			(id_subscribe, id_member, old_id_group, start_time, end_time, status)
		VALUES
			($id_subscribe, $id_member, $old_id_group, $starttime, $endtime, 1)", __FILE__, __LINE__);
}

// Removes a subscription from a user, as in removes the groups.
function removeSubscription($id_subscribe, $id_member, $delete = false)
{
	global $db_prefix, $context, $smfFunc;

	loadSubscriptions();

	// Load the user core bits.
	$request = $smfFunc['db_query']('', "
		SELECT m.id_group, m.additional_groups
		FROM {$db_prefix}members AS m
		WHERE m.id_member = $id_member", __FILE__, __LINE__);

	// Just in case of errors.
	if ($smfFunc['db_num_rows']($request) == 0)
	{
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_subscribed
			WHERE id_member = $id_member", __FILE__, __LINE__);
		return;
	}
	list ($id_group, $additional_groups) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Get all of the subscriptions for this user - it will be necessary!
	$request = $smfFunc['db_query']('', "
		SELECT ls.id_subscribe, ls.old_id_group
		FROM {$db_prefix}log_subscribed AS ls
		WHERE ls.id_member = $id_member
			AND status = 1", __FILE__, __LINE__);

	// What if like, there isn't any?
	if ($smfFunc['db_num_rows']($request) == 0)
		return;

	// These variables will be handy, honest ;)
	$removals = array();
	$allowed = array();
	$old_id_group = 0;
	$new_id_group = -1;
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (!isset($context['subscriptions'][$row['id_subscribe']]))
			continue;

		// The one we're removing?
		if ($row['id_subscribe'] == $id_subscribe)
		{
			$removals = explode(',', $context['subscriptions'][$row['id_subscribe']]['add_groups']);
			if ($context['subscriptions'][$row['id_subscribe']]['prim_group'] != 0)
				$removals[] = $context['subscriptions'][$row['id_subscribe']]['prim_group'];
			$old_id_group = $row['old_id_group'];
		}
		// Otherwise things we allow.
		else
		{
			$allowed = array_merge($allowed, explode(',', $context['subscriptions'][$row['id_subscribe']]['add_groups']));
			if ($context['subscriptions'][$row['id_subscribe']]['prim_group'] != 0)
			{
				$allowed[] = $context['subscriptions'][$row['id_subscribe']]['prim_group'];
				$new_id_group = $context['subscriptions'][$row['id_subscribe']]['prim_group'];
			}
		}
	}
	$smfFunc['db_free_result']($request);

	// Now, for everything we are removing check they defintely are not allowed it.
	$existingGroups = explode(',', $additional_groups);
	foreach ($existingGroups as $key => $group)
		if (empty($group) || (in_array($group, $removals) && !in_array($group, $allowed)))
			unset($existingGroups[$key]);

	// Finally, do something with the current primary group.
	if (in_array($id_group, $removals))
	{
		// If this primary group is actually allowed keep it.
		if (in_array($id_group, $allowed))
			$existingGroups[] = $id_group;

		// Either way, change the id_group back.
		if ($new_id_group < 1)
		{
			// If we revert to the old id-group we need to ensure it wasn't from a subscription.
			foreach ($context['subscriptions'] as $id => $group)
				// It was? Make them a regular member then!
				if ($group['prim_group'] == $old_id_group)
					$old_id_group = 0;

			$id_group = $old_id_group;
		}
		else
			$id_group = $new_id_group;
	}

	// Crazy stuff, we seem to have our groups fixed, just make them unique
	$existingGroups = array_unique($existingGroups);
	$existingGroups = implode(',', $existingGroups);

	// Update the member
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}members
		SET id_group = $id_group, additional_groups = '$existingGroups'
		WHERE id_member = $id_member", __FILE__, __LINE__);

	// Disable the subscription.
	if (!$delete)
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}log_subscribed
			SET status = 0
			WHERE id_member = $id_member
				AND id_subscribe = $id_subscribe", __FILE__, __LINE__);
	// Otherwise delete it!
	else
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_subscribed
			WHERE id_member = $id_member
				AND id_subscribe = $id_subscribe", __FILE__, __LINE__);
}

// This just kind of caches all the subscription data.
function loadSubscriptions()
{
	global $context, $db_prefix, $txt, $modSettings, $smfFunc;

	if (!empty($context['subscriptions']))
		return;

	// Make sure this is loaded, just in case.
	loadLanguage('ManagePaid');

	$request = $smfFunc['db_query']('', "
		SELECT id_subscribe, name, description, cost, length, id_group, add_groups, active, repeatable
		FROM {$db_prefix}subscriptions", __FILE__, __LINE__);
	$context['subscriptions'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Pick a cost.
		$costs = @unserialize($row['cost']);

		if ($row['length'] != 'F' && !empty($modSettings['paid_currency_symbol']) && !empty($costs['fixed']))
			$cost = sprintf($modSettings['paid_currency_symbol'], $costs['fixed']);
		else
			$cost = '???';

		// Do the span.
		preg_match('~(\d*)(\w)~', $row['length'], $match);
		if (isset($match[2]))
		{
			$num_length = $match[1];
			$length = $match[1] . ' ';
			switch ($match[2])
			{
				case 'D':
					$length .= $txt['paid_mod_span_days'];
					$num_length *= 86400;
					break;
				case 'W':
					$length .= $txt['paid_mod_span_weeks'];
					$num_length *= 604800;
					break;
				case 'M':
					$length .= $txt['paid_mod_span_months'];
					$num_length *= 2629743;
					break;
				case 'Y':
					$length .= $txt['paid_mod_span_years'];
					$num_length *= 31556926;
					break;
			}
		}
		else
			$length = '??';

		$context['subscriptions'][$row['id_subscribe']] = array(
			'id' => $row['id_subscribe'],
			'name' => $row['name'],
			'desc' => $row['description'],
			'cost' => $cost,
			'real_cost' => $row['cost'],
			'length' => $length,
			'num_length' => $num_length,
			'real_length' => $row['length'],
			'pending' => 0,
			'finished' => 0,
			'total' => 0,
			'active' => $row['active'],
			'prim_group' => $row['id_group'],
			'add_groups' => $row['add_groups'],
			'flexible' => $row['length'] == 'F' ? true : false,
			'repeatable' => $row['repeatable'],
		);
	}
	$smfFunc['db_free_result']($request);

	// Do the counts.
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(id_sublog) AS member_count, id_subscribe, status
		FROM {$db_prefix}log_subscribed
		GROUP BY id_subscribe, status", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$ind = $row['status'] == 0 ? 'finished' : 'total';

		if (isset($context['subscriptions'][$row['id_subscribe']]))
			$context['subscriptions'][$row['id_subscribe']][$ind] = $row['member_count'];
	}
	$smfFunc['db_free_result']($request);

	// How many payments are we waiting on?
	$request = $smfFunc['db_query']('', "
		SELECT SUM(payments_pending) AS total_pending, id_subscribe
		FROM {$db_prefix}log_subscribed
		GROUP BY id_subscribe", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (isset($context['subscriptions'][$row['id_subscribe']]))
			$context['subscriptions'][$row['id_subscribe']]['pending'] = $row['total_pending'];
	}
	$smfFunc['db_free_result']($request);
}

// Load all the payment gateways.
function loadPaymentGateways()
{
	global $sourcedir;

	$gateways = array();
	if ($dh = opendir($sourcedir))
	{
		while (($file = readdir($dh)) !== false)
		{
			if (!is_dir($file) && preg_match('~Subscriptions-([A-Za-z\d]+)\.php~', $file, $matches))
			{
				// Check this is definitely a valid gateway!
				$fp = fopen($sourcedir . '/' . $file, 'rb');
				$header = fread($fp, 4096);
				fclose($fp);

				if (strpos($header, '// SMF Payment Gateway: ' . strtolower($matches[1])) !== false)
				{
					loadClassFile($file);

					$gateways[] = array(
						'filename' => $file,
						'code' => strtolower($matches[1]),
						// Don't need anything snazier than this yet.
						'valid_version' => class_exists(strtolower($matches[1]) . '_payment') && class_exists(strtolower($matches[1]) . '_display'),
						'payment_class' => strtolower($matches[1]) . '_payment',
						'display_class' => strtolower($matches[1]) . '_display',
					);
				}
			}
		}
	}
	closedir($dh);

	return $gateways;
}

?>