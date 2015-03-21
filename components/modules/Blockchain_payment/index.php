<?php
/**
 * @package   Blockchain payment
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blockchain_payment;
use
	cs\Config,
	cs\Event,
	cs\Language\Prefix,
	cs\Page,
	cs\Route,
	cs\User;

$Page         = Page::instance();
$Transactions = Transactions::instance();
if (isset($_GET['secret'])) {
	$id = $Transactions->search(
		[
			'secret' => $_GET['secret']
		]
	);
	if (!$id) {
		error_code(404);
		return;
	}
	$transaction = $Transactions->get($id[0]);
	if (
		$transaction['input_address'] != $_GET['input_address'] ||
		$transaction['destination_address'] != $_GET['destination_address']
	) {
		error_code(400);
		return;
	}
	$Transactions->set_as_paid(
		$transaction['id'],
		$_GET['transaction_hash'],
		$_GET['input_transaction_hash']
	);
	interface_off();
	if ($_GET['confirmations'] >= Config::instance()->module('Blockchain_payment')->confirmations_required) {
		$Transactions->set_as_confirmed($transaction['id']);
		$Page->content('*ok*');
	} else {
		$Page->content('More confirmations needed');
	}
} else {
	$Route = Route::instance();
	if (!isset($Route->ids[0])) {
		error_code(400);
		return;
	}
	$transaction = $Transactions->get($Route->ids[0]);
	if ($transaction['user'] != User::instance()->id) {
		error_code(403);
		return;
	}
	// TODO page with QR-code
	$Page->content("$transaction[amount_btc] BTC for $transaction[input_address]");
}
