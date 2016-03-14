<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Config,
	cs\ExitException,
	cs\Page,
	cs\Response,
	cs\Route,
	cs\Session;

if (
	!isset(
		$_POST['shipping_type'],
		$_POST['shipping_username'],
		$_POST['shipping_phone'],
		$_POST['shipping_address'],
		$_POST['payment_method'],
		$_POST['comment'],
		$_POST['items']
	) ||
	!is_array($_POST['items']) ||
	!$_POST['items'] ||
	!$_POST['shipping_username']
) {
	throw new ExitException(400);
}
$Config  = Config::instance();
$Session = Session::instance();
if (
	!$Config->module('Shop')->allow_guests_orders &&
	!$Session->user()
) {
	throw new ExitException(403);
}
$Orders       = Orders::instance();
$recalculated = $Orders->get_recalculated_cart_prices($_POST['items'], $_POST['shipping_type']);
if (!$recalculated) {
	throw new ExitException(400);
}
$id = $Orders->add(
	$Session->get_user(),
	$_POST['shipping_type'],
	$recalculated['shipping']['price'],
	$_POST['shipping_username'],
	$_POST['shipping_phone'],
	$_POST['shipping_address'],
	$_POST['payment_method'],
	0,
	$Config->module('Shop')->default_order_status,
	$_POST['comment']
);
if (!$id) {
	throw new ExitException(500);
}
if (!$Session->user()) {
	$orders   = $Session->get_data('shop_orders') ?: [];
	$orders[] = $id;
	$Session->set_data('shop_orders', $orders);
	unset($orders);
}
$Items = Items::instance();
foreach ($recalculated['items'] as $item) {
	$item_data = $Items->get($item['id']);
	$Orders->add_item($id, $item['id'], $item['units'], $item['price'], $item_data['price']);
}
Response::instance()->code = 201;
Page::instance()->json(
	$Config->core_url().'/'.Route::instance()->relative_address."/$id"
);
