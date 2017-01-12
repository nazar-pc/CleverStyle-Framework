<?php
/**
 * @package   Shop
 * @attribute modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Config,
	cs\Event,
	cs\Language\Prefix,
	cs\Menu,
	cs\Request;

Event::instance()
	->on(
		'System/Request/routing_replace/after',
		function ($data) {
			if (!Config::instance()->module('Shop')->enabled()) {
				return;
			}
			if ($data['current_module'] != 'Shop' || !$data['regular_path']) {
				return;
			}
			$route = &$data['route'];
			if (!isset($route[0])) {
				$route[0] = 'categories_';
			}
			$L = new Prefix('shop_');
			switch ($route[0]) {
				case path($L->categories):
					$route[0] = 'categories_';
					break;
				case path($L->items):
					$route[0] = 'items_';
					break;
				case path($L->orders):
					$route[0] = 'orders_';
					break;
				case path($L->cart):
					$route[0] = 'cart';
					break;
			}
		}
	)
	->on(
		'System/payment/success',
		function ($data) {
			if ($data['module'] != 'Shop') {
				return;
			}
			$purpose = explode('/', $data['purpose']);
			if ($purpose[0] != 'orders') {
				return;
			}
			$Config = Config::instance();
			$Orders = Orders::instance();
			$order  = $Orders->get($purpose[1]);
			if (!$order['paid']) {
				$Orders->set(
					$order['id'],
					$order['user'],
					$order['shipping_type'],
					$order['shipping_cost'],
					$order['shipping_username'],
					$order['shipping_phone'],
					$order['shipping_address'],
					$order['payment_method'],
					1,
					$Config->module('Shop')->default_paid_order_status,
					$order['comment']
				);
				$Orders->set_status(
					$purpose[1],
					$Config->module('Shop')->default_paid_order_status,
					'' // TODO: possibly some comment message here
				);
			}
			$L                = new Prefix('shop_');
			$data['callback'] = $Config->core_url().'/'.path($L->shop).'/'.path($L->orders).'/?paid_success='.(int)$purpose[1];
		}
	)
	->on(
		'System/payment/error',
		function ($data) {
			if ($data['module'] != 'Shop') {
				return;
			}
			$purpose = explode('/', $data['purpose']);
			if ($purpose[0] != 'orders') {
				return;
			}
			$Config           = Config::instance();
			$L                = new Prefix('shop_');
			$data['callback'] = $Config->core_url().'/'.path($L->shop).'/'.path($L->orders).'/?paid_error='.(int)$purpose[1];
		}
	)
	->on(
		'admin/System/Menu',
		function () {
			$L       = new Prefix('shop_');
			$Menu    = Menu::instance();
			$Request = Request::instance();
			foreach (['general', 'orders', 'categories', 'items', 'attributes', 'order_statuses', 'shipping_types'] as $section) {
				$Menu->add_item(
					'Shop',
					$L->$section,
					[
						'href'    => "admin/Shop/$section",
						'primary' => $Request->route_path(0) == $section
					]
				);
			}
		}
	)
	->on(
		'admin/System/modules/uninstall/before',
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
	)
	->on(
		'admin/System/modules/install/after',
		function ($data) {
			if ($data['name'] != 'Shop') {
				return;
			}
			Config::instance()->module('Shop')->set(
				[
					'currency'                            => 'USD',
					'price_formatting'                    => '$%s USD',
					'items_per_page'                      => 20,
					'items_per_page_admin'                => 50,
					'allow_guests_orders'                 => 1,
					'automatically_reduce_in_stock_value' => 0,
					'default_order_status'                => 1,
					'default_paid_order_status'           => 1
				]
			);
		}
	);
