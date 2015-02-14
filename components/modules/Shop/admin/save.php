<?php
/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
 */
namespace cs;
if (isset($_POST['save'])) {
	Config::instance()->module('Shop')->set([
		'price_formatting'                    => xap($_POST['price_formatting']),
		'items_per_page'                      => (int)$_POST['items_per_page'],
		'items_per_page_admin'                => (int)$_POST['items_per_page_admin'],
		'allow_guests_orders'                 => (int)$_POST['allow_guests_orders'],
		'automatically_reduce_in_stock_value' => (int)$_POST['automatically_reduce_in_stock_value'],
		'default_order_status'                => (int)$_POST['default_order_status'],
		'default_paid_order_status'           => (int)$_POST['default_paid_order_status']
	]);
	Index::instance()->save(true);
}
