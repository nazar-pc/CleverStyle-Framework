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
$Route        = Route::instance();
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
$Page->json(
	[
		'id'            => $transaction['id'],
		'input_address' => $transaction['input_address'],
		'amount'        => $transaction['amount'],
		'currency'      => $transaction['currency'],
		'amount_btc'    => $transaction['amount_btc'],
		'module'        => $transaction['module'],
		'purpose'       => $transaction['purpose'],
		'description'   => $transaction['description'],
		'paid'          => $transaction['paid'],
		'confirmed'     => $transaction['confirmed']
	]
);
