<?php
/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
 */
namespace cs;
Trigger::instance()->register(
	'admin/System/components/modules/install/process', //TODO add trigger for uninstallation to remove all files and other stuff
	function ($data) {
		if ($data['name'] != 'Shop') {
			return;
		}
		Config::instance()->module('Shop')->set([
			'price_formatting'                    => '$%s USD',
			'items_per_page'                      => 20,
			'items_per_page_admin'                => 50,
			'automatically_reduce_in_stock_value' => 0,
			'default_order_status'                => 1
		]);
	}
);
