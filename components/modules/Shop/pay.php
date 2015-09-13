<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language\Prefix,
	cs\Route,
	cs\User;
$Config = Config::instance();
$L      = new Prefix('shop_');
$Orders = Orders::instance();
$order  = $Orders->get(@Route::instance()->ids[0]);
if (!$order || $order['user'] != User::instance()->id) {
	throw new ExitException(404);
}
interface_off();
if ($order['paid'] || $order['payment_method'] == Orders::PAYMENT_METHOD_CASH) {
	_header('Location: '.$Config->core_url().'/'.path($L->shop).'/'.path($L->orders));
	return;
}
Event::instance()->fire(
	'System/payment/execute',
	[
		'amount'         => $order['for_payment'],
		'currency'       => $Config->module('Shop')->currency,
		'user'           => $order['user'],
		'payment_method' => $order['payment_method'],
		'module'         => 'Shop',
		'purpose'        => "orders/$order[id]",
		'description'    => $L->payment_for_order($order['id'])
	]
);
