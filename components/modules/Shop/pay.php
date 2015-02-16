<?php
/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2015, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Config,
	cs\Event,
	cs\Index,
	cs\Language\Prefix,
	cs\Page,
	cs\User;
$Config = Config::instance();
$Index  = Index::instance();
$L      = new Prefix('shop_');
$Orders = Orders::instance();
$order  = $Orders->get($Index->route_ids[0]);
if (!$order || $order['user'] != User::instance()->id) {
	error_code(404);
	return;
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
		'user'           => $order['user'],
		'payment_method' => $order['payment_method'],
		'module'         => 'Shop',
		'purpose'        => "orders/$order[id]",
		'description'    => $L->payment_for_order($order['id'])
	]
);
