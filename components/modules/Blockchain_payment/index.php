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
	h,
	cs\Config,
	cs\Event,
	cs\Language,
	cs\Page,
	cs\Route,
	cs\User;

$Page = Page::instance();
array_pop($Page->Title);
$Page->title(
	Language::instance()->blockchain_payment_bitcoin
);
$Transactions = Transactions::instance();
if (isset($_GET['secret'])) {
	interface_off();
	if ($_GET['test']) {
		return;
	}
	$id = $Transactions->search(
		[
			'secret' => $_GET['secret']
		]
	);
	if (!$id) {
		error_code(400);
		return;
	}
	$transaction = $Transactions->get($id[0]);
	if (!$transaction) {
		error_code(404);
		return;
	}
	if (
		$transaction['input_address'] != $_GET['input_address'] ||
		$transaction['destination_address'] != $_GET['destination_address'] ||
		$transaction['amount'] > $_GET['value'] / 100000000
	) {
		error_code(400);
		return;
	}
	$Transactions->set_as_paid(
		$transaction['id'],
		$_GET['transaction_hash'],
		$_GET['input_transaction_hash']
	);
	if ($_GET['confirmations'] >= Config::instance()->module('Blockchain_payment')->confirmations_required) {
		$Transactions->set_as_confirmed($transaction['id']);
		Event::instance()->fire(
			'System/payment/success',
			[
				'module'  => $transaction['module'],
				'purpose' => $transaction['purpose']
			]
		);
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
	if (!$transaction) {
		error_code(404);
		return;
	}
	if ($transaction['user'] != User::instance()->id) {
		error_code(403);
		return;
	}
	if ($transaction['confirmed']) {
		$callback = '/';
		Event::instance()->fire(
			'System/payment/success',
			[
				'module'   => $transaction['module'],
				'purpose'  => $transaction['purpose'],
				'callback' => &$callback
			]
		);
		interface_off();
		_header("Location: $callback");
		return;
	}
	$Page->content(
		h::cs_blockchain_payment_pay(
			[
				'data-id'     => $transaction['id'],
				'address'     => $transaction['input_address'],
				'amount'      => $transaction['amount_btc'],
				'label'       => urlencode("$transaction[module]/$transaction[purpose]"),
				'description' => h::prepare_attr_value(_json_encode($transaction['description']))
			]
		)
	);
}
