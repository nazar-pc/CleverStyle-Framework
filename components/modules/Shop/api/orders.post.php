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
	cs\User;

if (
	!isset(
		$_POST['shipping_type'],
		$_POST['shipping_username'],
		$_POST['shipping_phone'],
		$_POST['shipping_address'],
		$_POST['comment'],
		$_POST['items']
	) ||
	empty($_POST['items']) ||
	!is_array($_POST['items'])
) {
	error_code(400);
	return;
}
$User = User::instance();
if ($User->guest()) { // TODO make configurable and allow guests to make orders (store order id in session to allow getting order data until session died in GET request)
	error_code(403);
	return;
}
$Config        = Config::instance();
$Orders        = Orders::instance();
$shipping_type = Shipping_types::instance()->get($_POST['shipping_type']);
$id            = $Orders->add(
	$User->id,
	$_POST['shipping_type'],
	$shipping_type['price'], // TODO discount feature
	@$_POST['shipping_username'] ?: $User->username(),
	$_POST['shipping_phone'],
	$_POST['shipping_address'],
	$Config->module('Shop')->default_order_status,
	$_POST['comment']
);
if (!$id) {
	error_code(500);
	return;
}
$Items = Items::instance();
foreach ($_POST['items'] as $item => $units) {
	$item_data = $Items->get($item);
	$Orders->add_item($id, $item, $units, $item_data['price'] * $units, $item_data['price']); // TODO discount feature
}
code_header(201);
Page::instance()->json(
	$Config->core_url().'/'.$Config->server['relative_address']."/$id"
);
