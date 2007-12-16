<?php
/**********************************************************************************
* subscriptions.php                                                               *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 1.1                                    *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
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

/*
	This file is the file which all subscription gateways should call when a payment has been
	received - it sorts out the user status.

	void generateSubscriptionError()
		//!!!
*/

// Start things rolling by getting SMF alive...
if (!file_exists(dirname(__FILE__) . '/SSI.php'))
	die('Cannot find SSI.php');

require_once(dirname(__FILE__) . '/SSI.php');
require_once($sourcedir . '/ManagePaid.php');
loadLanguage('ManagePaid');

// If there's literally nothing coming in, let's take flight!
if (empty($_POST))
	die($txt['paid_no_data']);

// I assume we're even active?
if (empty($modSettings['paid_enabled']))
	exit;

// We need to see whether we can find the correct payment gateway, we'll going to go through all our gateway scripts and find out if they are happy with what we have.
$txnType = '';
$gatewayHandles = loadPaymentGateways();
foreach ($gatewayHandles as $gateway)
{
	$gatewayClass = new $gateway['payment_class']();
	if ($gatewayClass->isValid())
	{
		$txnType = $gateway['code'];
		break;
	}
}

if (empty($txnType))
	generateSubscriptionError($txt['paid_unknown_transaction_type']);

// Get the ID_SUB and ID_MEMBER amoungst others...
@list ($ID_SUB, $ID_MEMBER) = $gatewayClass->precheck();

// This would be bad...
if (empty($ID_MEMBER))
	generateSubscriptionError($txt['paid_empty_member']);

// Integer these just incase.
$ID_SUB = (int) $ID_SUB;
$ID_MEMBER = (int) $ID_MEMBER;

// Verify the member.
$request = $smfFunc['db_query']('', "
	SELECT id_member, member_name, real_name, email_address
	FROM {$db_prefix}members
	WHERE id_member = $ID_MEMBER", __FILE__, __LINE__);
// Didn't find them?
if ($smfFunc['db_num_rows']($request) == 0)
	generateSubscriptionError(sprintf($txt['paid_could_not_find_member'], $ID_MEMBER));
list ($ID_MEMBER, $username, $name, $email) = $smfFunc['db_fetch_row']($request);
$smfFunc['db_free_result']($request);

// Get the subscription details.
$request = $smfFunc['db_query']('', "
	SELECT cost, active, length, name, email_complete
	FROM {$db_prefix}subscriptions
	WHERE id_subscribe = $ID_SUB", __FILE__, __LINE__);

// Didn't find it?
if ($smfFunc['db_num_rows']($request) == 0)
	generateSubscriptionError(sprintf($txt['paid_count_not_find_subscription'], $ID_MEMBER, $ID_SUB));

list ($cost, $active, $length, $subname, $emaildata) = $smfFunc['db_fetch_row']($request);
$smfFunc['db_free_result']($request);

// We wish to check the pending payments to make sure we are expecting this.
$request = $smfFunc['db_query']('', "
	SELECT id_sublog, payments_pending, pending_details
	FROM {$db_prefix}log_subscribed
	WHERE id_subscribe = $ID_SUB
		AND id_member = $ID_MEMBER
	LIMIT 1", __FILE__, __LINE__);
if ($smfFunc['db_num_rows']($request) == 0)
	generateSubscriptionError(sprintf($txt['paid_count_not_find_subscription_log'], $ID_MEMBER, $ID_SUB));
list ($id_sublog, $payments_pending, $pending_details) = $smfFunc['db_fetch_row']($request);
$smfFunc['db_free_result']($request);

// Is this a refund etc?
if ($gatewayClass->isRefund())
{
	// Delete user subscription.
	removeSubscription($ID_SUB, $ID_MEMBER);

	// Mark it as complete so we have a record.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}log_subscribed
		SET end_time = " . time() . "
		WHERE id_subscribe = $ID_SUB
			AND id_member = $ID_MEMBER
			AND status = 0", __FILE__, __LINE__);

	// Receipt?
	if (!empty($modSettings['paid_email']) && $modSettings['paid_email'] == 2)
	{
		$emailbody = $txt['paid_delete_sub_body'];
		$emailbody .= "\n\n\t";
		$emailbody .= $txt['paid_new_sub_body_sub'] . ' ' . $subname;
		$emailbody .= "\n\t" . $txt['paid_new_sub_body_name'] . ' ' . $name . ' (' . $username . ')';
		$emailbody .= "\n\t" . $txt['paid_new_sub_body_date'] . ' ' . timeformat(time(), false);

		paidAdminEmail($txt['paid_delete_sub_subject'], $emailbody);
	}

}
// Otherwise is it what we want, a purchase?
elseif ($gatewayClass->isPayment() || $gatewayClass->isSubscription())
{
	$cost = unserialize($cost);
	$totalCost = $gatewayClass->getCost();
	$notify = false;

	// For one off's we want to only capture them once!
	if (!$gatewayClass->isSubscription())
	{
		$real_details = @unserialize($pending_details);
		if (empty($real_details))
			generateSubscriptionError(sprintf($txt['paid_count_not_find_outstanding_payment'], $ID_MEMBER, $ID_SUB));
		// Now we just try to find anything pending. We don't really care which it is as security happens later.
		foreach ($real_details as $id => $detail)
		{
			unset($real_details[$id]);
			if ($detail[3] == 'payback' && $payments_pending)
				$payments_pending--;
			break;
		}
		$pending_details = empty($real_details) ? '' : $smfFunc['db_escape_string'](serialize($real_details));

		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}log_subscribed
			SET payments_pending = $payments_pending, pending_details = '$pending_details'
			WHERE id_sublog = $id_sublog", __FILE__, __LINE__);
	}

	// Is this flexible?
	if ($length == 'F')
	{
		$foundDuration = 0;
		// This is a little harder, can we find the right duration?
		foreach ($cost as $duration => $value)
		{
			if ($duration == 'fixed')
				continue;
			elseif ((float) $value == (float) $totalCost)
				$foundDuration = strtoupper(substr($duration, 0, 1));
		}

		// If we have the duration then we're done.
		if ($foundDuration !== 0)
		{
			$notify = true;
			addSubscription($ID_SUB, $ID_MEMBER, $foundDuration);
		}
	}
	else
	{
		$actual_cost = $cost['fixed'];
		// It must be at least the right amount.
		if ($totalCost != 0 && $totalCost >= $actual_cost)
		{
			// Add the subscription.
			$notify = true;
			addSubscription($ID_SUB, $ID_MEMBER);
		}
	}

	// Send a receipt?
	if (!empty($modSettings['paid_email']) && $modSettings['paid_email'] == 2 && $notify)
	{
		$emailbody = $txt['paid_new_sub_body'];
		$emailbody .= "\n\n\t";
		$emailbody .= $txt['paid_new_sub_body_sub'] . ' ' . $subname;
		$emailbody .= "\n\t" . $txt['paid_new_sub_body_price'] . ' ' . sprintf($modSettings['paid_currency_symbol'], $totalCost);
		$emailbody .= "\n\t" . $txt['paid_new_sub_body_name'] . ' ' . $name . ' (' . $username . ')';
		$emailbody .= "\n\t" . $txt['paid_new_sub_body_email'] . ' ' . $email;
		$emailbody .= "\n\t" . $txt['paid_new_sub_body_date'] . ' ' . timeformat(time(), false);
		$emailbody .= "\n" . $txt['paid_new_sub_body_link'] . ":\n\t" . $scripturl . '?action=profile;u=' . $ID_MEMBER;

		paidAdminEmail($txt['paid_new_sub_subject'], $emailbody);
	}

	// Email the user?
	if (strlen($emaildata > 10) && strpos($emaildata, "\n") !== false)
	{
		$subject = substr($emaildata, 0, strpos($emaildata, "\n") - 1);
		$body = substr($emaildata, strpos($emaildata, "\n") + 1);

		$search = array('{NAME}', '{FORUM}');
		$replace = array($name, $mbname);

		$subject = str_replace($search, $replace, $subject);
		$body = str_replace($search, $replace, $body);

		require_once($sourcedir . '/Subs-Post.php');
		sendmail($email, $subject, $body);
	}
}

// Incase we have anything specific to do.
$gatewayClass->close();

// Log an error then die.
function generateSubscriptionError($text, $notify = true)
{
	global $modSettings, $sourcedir, $db_prefix, $txt;

	// Send an email?
	if (!empty($modSettings['paid_email']))
		paidAdminEmail($txt['paid_error_subject'], $txt['paid_error_body'] . "\n" . 	"---------------------------------------------\n" . $text);

	// Otherwise log and die.
	log_error($text);
	exit;
}

// Send an email to admins.
function paidAdminEmail($subject, $body)
{
	global $sourcedir, $db_prefix, $mbname, $modSettings, $smfFunc;

	require_once($sourcedir . '/Subs-Post.php');
	$request = $smfFunc['db_query']('', "
		SELECT email_address, real_name
		FROM {$db_prefix}members
		WHERE id_group = 1", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		sendmail($row['email_address'], $subject, $row['real_name'] . "\n\n" . $body . "\n\n" . $mbname);
	$smfFunc['db_free_result']($request);

	if (!empty($modSettings['paid_email_to']))
	{
		foreach (explode(',', $modSettings['paid_email_to']) as $email)
			sendmail(trim($email), $subject, $body . "\n\n" . $mbname);
	}
}

?>