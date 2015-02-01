<?php
/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
 */
namespace cs;
Event::instance()->on(
	'admin/System/components/modules/install/process',
	function ($data) {
		if ($data['name'] != 'Shop') {
			return;
		}
		Config::instance()->module('Shop')->set([
			'price_formatting'                    => '$%s USD',
			'items_per_page'                      => 20,
			'items_per_page_admin'                => 50,
			'allow_guests_orders'                 => 1,
			'automatically_reduce_in_stock_value' => 0,
			'default_order_status'                => 1,
			'default_paid_order_status'           => 1
		]);
	}
);
