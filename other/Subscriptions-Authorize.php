<?php
/**********************************************************************************
* Subscriptions-twoCheckOut.php                                                   *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 RC4                                         *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2010 by:     Simple Machines LLC (http://www.simplemachines.org) *
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

// This won't be dedicated without this - this must exist in each gateway!
// SMF Payment Gateway: authorize

class authorize_display
{
	public $title = 'Authorize.net | Credit Card';

	// Basic settings that we need.
	public function getGatewaySettings()
	{
		global $txt;

		$setting_data = array(
			array('text', 'authorize_id', 'subtext' => $txt['authorize_id_desc']),
			array('text', 'authorize_transid'),
		);

		return $setting_data;
	}

	// Is it enabled?
	public function gatewayEnabled()
	{
		global $modSettings;

		return !empty($modSettings['authorize_id']) && !empty($modSettings['authorize_transid']);
	}

	// Lets set up the fields needed for the transaction.
	public function fetchGatewayFields($unique_id, $sub_data, $value, $period, $return_url)
	{
		global $modSettings, $txt, $boardurl, $context;

		$return_data = array(
			'form' => 'https://' . (empty($modSettings['paidsubs_test']) ? 'secure' : 'test') . '.authorize.net/gateway/transact.dll',
			'id' => 'authorize',
			'hidden' => array(),
			'title' => $txt['authorize'],
			'desc' => $txt['paid_confirm_authorize'],
			'submit' => $txt['paid_authorize_order'],
			'javascript' => '',
		);

		$timestamp = time();
		$sequence = substr(time(), -5);
		$hash = $this->_md5_hmac($modSettings['authorize_transid'], $modSettings['authorize_id'] . '^' . $sequence . '^' . $timestamp . '^' . $value . '^' . strtoupper($modSettings['paid_currency_code']));

		$return_data['hidden']['x_login'] = $modSettings['authorize_id'];
		$return_data['hidden']['x_amount'] = $value;
		$return_data['hidden']['x_currency_code'] = strtoupper($modSettings['paid_currency_code']);
		$return_data['hidden']['x_show_form'] = 'PAYMENT_FORM';
		$return_data['hidden']['x_test_request'] = empty($modSettings['paidsubs_test']) ? 'FALSE' : 'TRUE';
		$return_data['hidden']['x_fp_sequence'] = $sequence;
		$return_data['hidden']['x_fp_timestamp'] = $timestamp;
		$return_data['hidden']['x_fp_hash'] = $hash;
		$return_data['hidden']['x_invoice_num'] = $unique_id;
		$return_data['hidden']['x_email'] = $context['user']['email'];
		$return_data['hidden']['x_type'] = 'AUTH_CAPTURE';
		$return_data['hidden']['x_cust_id'] = $context['user']['name'];
		$return_data['hidden']['x_relay_url'] = $boardurl . '/subscriptions.php';

		return $return_data;
	}

	// A private function to generate the hash.
	private function _md5_hmac($key, $data)
	{
		$key = str_pad(strlen($key) <= 64 ? $key : pack('H*', md5($key)), 64, chr(0x00));
		return md5(($key ^ str_repeat(chr(0x5c), 64)) . pack('H*', md5(($key ^ str_repeat(chr(0x36), 64)) . $data)));
	}
}

class authorize_payment
{
	private $return_data;

	public function isValid()
	{
		global $modSettings;

		// Is it even on?
		if (empty($modSettings['authorize_id']) || empty($modSettings['authorize_transid']))
			return false;
		// We got a hash?
		if (empty($_POST['x_MD5_Hash']))
			return false;
		// Do we have an invoice number?
		if (empty($_POST['x_invoice_num']))
			return false;
		if (empty($_POST['x_response_code']))
			return false;
log_error(print_r($_POST, true));
		return true;
	}

	// Validate this is valid for this transaction type.
	public function precheck()
	{
		global $modSettings;

		// Is this the right hash?
		if ($_POST['x_MD5_Hash'] != strtoupper(md5($modSettings['authorize_id'] . $_POST['x_trans_id'] . $_POST['x_amount'])))
			exit;

		// Can't exist if it doesn't contain anything.
		if (empty($_POST['x_invoice_num']))
			exit;

		// Verify the currency
		$currency = $_POST['x_currency_code'];

		// Verify the currency!
		if (strtolower($currency) != $modSettings['currency_code'])
			exit;

		// Return the ID_SUB/ID_MEMBER
		return explode('+', $_POST['x_invoice_num']);
	}

	// Is this a refund?
	public function isRefund()
	{
		return false;
	}

	// Is this a subscription?
	public function isSubscription()
	{
		return false;
	}

	// Is this a normal payment?
	public function isPayment()
	{
		if ($_POST['x_response_code'] == 1)
			return true;
		else
			return false;
	}

	// How much was paid?
	public function getCost()
	{
		return $_POST['x_amount'];
	}

	// Redirect the user away.
	public function close()
	{
		exit();
	}
}

?>