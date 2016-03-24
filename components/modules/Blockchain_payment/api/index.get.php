<?php
/**
 * @package   Blockchain payment
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blockchain_payment;
use
	cs\ExitException,
	cs\Language,
	cs\Page,
	cs\Request,
	cs\User;

$Page = Page::instance();
array_pop($Page->Title);
$Page->title(
	Language::instance()->blockchain_payment_bitcoin
);
$Transactions = Transactions::instance();
$Request      = Request::instance();
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
