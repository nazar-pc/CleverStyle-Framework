<?php
/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	h,
	cs\Config,
	cs\Language,
	cs\Language\Prefix,
	cs\Page,
	cs\User;

$L              = new Prefix('shop_');
$Language       = Language::instance();
$Page           = Page::instance();
$User           = User::instance();
$Categories     = Categories::instance();
$Items          = Items::instance();
$Orders         = Orders::instance();
$Order_statuses = Order_statuses::instance();
$Shipping_types = Shipping_types::instance();
$page           = @$_GET['page'] ?: 1;
$count          = @$_GET['count'] ?: Config::instance()->module('Shop')->items_per_page;
$orders         = $Orders->get($Orders->search(
	[
		'user' => $User->id
	] + $_GET,
	$page,
	$count,
	@$_GET['order_by'] ?: 'date',
	@$_GET['asc']
));
$orders_total   = $Orders->search(
	[
		'user'        => $User->id,
		'total_count' => 1
	] + $_GET,
	$page,
	$count,
	@$_GET['order_by'] ?: 'date',
	@$_GET['asc']
);
$module_path    = path($L->shop);
$items_path     = path($L->items);
$orders_path    = path($L->orders);
$Page->title($L->orders);
$Page->content(
	h::cs_shop_orders(
		h::h1($L->your_orders).
		h::cs_shop_order(array_map(
			function ($order) use ($L, $Language, $Categories, $Items, $Order_statuses, $Orders, $Shipping_types, $module_path, $items_path) {
				$order_status = $Order_statuses->get($order['status']);
				$date         = $L->to_locale(
					date($Language->{TIME - $order['date'] < 24 * 3600 ? '_time' : '_datetime_long'}, $order['date'])
				);
				return [
					[
						h::{'#items cs-shop-order-item'}(array_map(
							function ($item) use ($Categories, $Items, $module_path, $items_path) {
								$item_data = $Items->get($item['item']);
								return [
									h::img([
										'src' => @$item['images'][0] ?: 'components/modules/Shop/includes/img/no-image.svg'
									]).
									h::a(
										$item_data['title'],
										[
											'href'   => "$module_path/$items_path/".path($Categories->get($item_data['category'])['title']).'/'.path($item_data['title']).":$item_data[id]",
											'target' => '_blank'
										]
									),
									[
										'data-id'         => $item_data['id'],
										'data-price'      => $item['price'],
										'data-unit-price' => $item['unit_price'],
										'data-units'      => $item['units']
									]
								];
							},
							$Orders->get_items($order['id'])
						)).
						h::{'#shipping_type'}(
							$Shipping_types->get($order['shipping_type'])['title'],
							[
								'data-id' => $order['shipping_type']
							]
						).
						h::{'#order_status'}(
							$order_status['title'],
							[
								'data-id'    => $order['status'],
								'data-color' => $order_status['color'],
								'data-type'  => $order_status['type']
							]
						).
						$order['comment']
					],
					[
						'data-id'             => $order['id'],
						'data-date'           => $order['date'],
						'data-date-formatted' => h::prepare_attr_value($date)
					]
				];
			},
			$orders
		) ?: false).
		pages($page, ceil($orders_total / $count), function ($page) use ($User, $module_path, $orders_path) {
			$base_url = "$module_path/$orders_path/?";
			return $base_url.http_build_query(array_merge(
				[
					'page' => $page,
					'user' => $User->id
				] + $_GET
			));
		}, true)
	)
);
