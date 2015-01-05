<?php
/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
 */
namespace cs\modules\Shop;
use cs\Trigger;
Trigger::instance()->register(
	'admin/System/components/modules/uninstall/process',
	function ($data) {
		if ($data['name'] != 'Shop') {
			return;
		}
		$Categories = Categories::instance();
		foreach ($Categories->get_all() as $category) {
			$Categories->del($category);
		}
		unset($category);
		$Items = Items::instance();
		foreach ($Items->get_all() as $item) {
			$Items->del($item);
		}
		unset($item);
		$Attributes = Attributes::instance();
		foreach ($Attributes->get_all() as $attribute) {
			$Attributes->del($attribute);
		}
		unset($attribute);
		$Order_statuses = Order_statuses::instance();
		foreach ($Order_statuses->get_all() as $order_status) {
			$Order_statuses->del($order_status);
		}
		unset($order_status);
		$Shipping_types = Shipping_types::instance();
		foreach ($Shipping_types->get_all() as $shipping_type) {
			$Shipping_types->del($shipping_type);
		}
		unset($shipping_type);
	}
);
