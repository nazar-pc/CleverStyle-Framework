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
	cs\Page,
	cs\Page,
	cs\Route;

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
	empty($_POST['items']) ||
	!is_array($_POST['items'])
) {
	error_code(400);
	return;
}
$Config = Config::instance();
$User   = User::instance();
if (
	!$Config->module('Shop')->allow_guests_orders &&
	!$User->user()
) {
	error_code(403);
	return;
}
$Orders       = Orders::instance();
$recalculated = $Orders->get_recalculated_cart_prices($_POST['items'], $_POST['shipping_type']);
if (!$recalculated) {
	error_code(400);
	return;
}
$id = $Orders->add(
	$User->id,
	$_POST['shipping_type'],
	$recalculated['shipping']['price'],
	@$_POST['shipping_username'] ?: $User->username(),
	$_POST['shipping_phone'],
	$_POST['shipping_address'],
	$_POST['payment_method'],
	0,
	$Config->module('Shop')->default_order_status,
	$_POST['comment']
);
if (!$id) {
	error_code(500);
	return;
}
if (!$User->user()) {
	$orders   = $User->get_session_data('shop_orders') ?: [];
	$orders[] = $id;
	$User->set_session_data('shop_orders', $orders);
	unset($orders);
}
$Items = Items::instance();
foreach ($recalculated['items'] as $item) {
	$item_data = $Items->get($item['id']);
	$Orders->add_item($id, $item, $item['units'], $item['price'], $item_data['price']);
}
code_header(201);
Page::instance()->json(
	$Config->core_url().'/'.Route::instance()->relative_address."/$id"
);
