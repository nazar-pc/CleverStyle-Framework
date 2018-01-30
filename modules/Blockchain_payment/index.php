<?php
/**
 * @package  Blockchain payment
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Blockchain_payment;
use
	h,
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language,
	cs\Page,
	cs\Request,
	cs\Response,
	cs\User;

$Page = Page::instance();
array_pop($Page->Title);
$Page->title(
	Language::instance()->blockchain_payment_bitcoin
);
$Transactions = Transactions::instance();
if (isset($_GET['secret'])) {
	$Page->interface = false;
	if ($_GET['test']) {
		return;
	}
	$id = $Transactions->search(
		[
			'secret' => $_GET['secret']
		]
	);
	if (!$id) {
		throw new ExitException(400);
	}
	$transaction = $Transactions->get($id[0]);
	if (!$transaction) {
		throw new ExitException(404);
	}
	if (
		$transaction['input_address'] != $_GET['input_address'] ||
		$transaction['destination_address'] != $_GET['destination_address'] ||
		$transaction['amount'] > $_GET['value'] / 100000000
	) {
		throw new ExitException(400);
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
	$Request = Request::instance();
	if (!isset($Request->route_ids[0])) {
		throw new ExitException(400);
	}
	$transaction = $Transactions->get($Request->route_ids[0]);
	if (!$transaction) {
		throw new ExitException(404);
	}
	if ($transaction['user'] != User::instance()->id) {
		throw new ExitException(403);
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
		$Page->interface = false;
		Response::instance()->redirect($callback);
		return;
	}
	$Page->content(
		h::cs_blockchain_payment_pay(
			[
				'data-id'     => $transaction['id'],
				'address'     => $transaction['input_address'],
				'amount'      => $transaction['amount_btc'],
				'description' => _json_encode($transaction['description'])
			]
		)
	);
}
