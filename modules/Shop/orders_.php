<?php
/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Shop;
use
	h,
	cs\Config,
	cs\Language\Prefix,
	cs\Page,
	cs\Session;

$L              = new Prefix('shop_');
$Page           = Page::instance();
$Session        = Session::instance();
$Categories     = Categories::instance();
$Items          = Items::instance();
$Orders         = Orders::instance();
$Order_statuses = Order_statuses::instance();
$Shipping_types = Shipping_types::instance();
$page           = $_GET['page'] ?? 1;
$count          = $_GET['count'] ?? Config::instance()->module('Shop')->items_per_page;
if ($Session->user()) {
	$orders       = $Orders->get(
		$Orders->search(
			[
				'user' => $Session->get_user()
			] + (array)$_GET,
			$page,
			$count,
			$_GET['order_by'] ?? 'date',
			@$_GET['asc']
		) ?: []
	);
	$orders_total = $Orders->search(
		[
			'user'        => $Session->get_user(),
			'total_count' => 1
		] + (array)$_GET,
		$page,
		$count,
		$_GET['order_by'] ?? 'date',
		@$_GET['asc']
	);
} else {
	$orders       = $Orders->get(
		$Session->get_data('shop_orders') ?: []
	);
	$orders_total = count($orders);
}
$module_path = path($L->shop);
$items_path  = path($L->items);
$orders_path = path($L->orders);
$Page->title($L->orders);
$Page->content(
	h::cs_shop_order_paid_notification().
	h::cs_shop_orders(
		h::h1($L->your_orders).
		h::{'#orders cs-shop-order'}(
			array_map(
				function ($order) use ($L, $Categories, $Items, $Order_statuses, $Orders, $Shipping_types, $module_path, $items_path) {
					$order_status  = $Order_statuses->get($order['status']);
					$shipping_type = $Shipping_types->get($order['shipping_type']);
					if (time() - $order['date'] < 24 * 3600) {
						$date = $L->to_locale(
							date($L->_time, $order['date'])
						);
					} else {
						$date = $L->to_locale(
							date($L->_datetime_long, $order['date'])
						);
					}
					return [
						[
							h::{'#items cs-shop-order-item'}(
								array_map(
									function ($item) use ($Categories, $Items, $module_path, $items_path) {
										$item_data = $Items->get_for_user($item['item']);
										return [
											h::{'img#img'}(
												[
													'src' => $item_data['images'][0] ?? Items::DEFAULT_IMAGE
												]
											).
											h::{'a#link'}(
												$item_data['title'] ?: '_',
												[
													'href'   => $item_data ?
														"$module_path/$items_path/".
														path($Categories->get_for_user($item_data['category'])['title']).
														'/'.
														path($item_data['title']).
														":$item_data[id]" : false,
													'target' => '_blank'
												]
											).
											h::{'#description'}(truncate($item_data['description'], 100) ?: false),
											[
												'item_id'    => $item_data['id'],
												'price'      => $item['price'],
												'unit_price' => $item['unit_price'],
												'units'      => $item['units']
											]
										];
									},
									$Orders->get_items($order['id'])
								)
							).
							h::{'#shipping_type'}(
								$shipping_type['title'] ?? '_',
								[
									'data-id'    => $order['shipping_type'],
									'data-price' => $shipping_type['price'] ?? '?'
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
							h::{'#phone'}($order['shipping_phone'] ?: false).
							h::{'#address'}($order['shipping_address'] ?: false).//Payment method add here
							h::{'#comment'}($order['comment'] ?: false)
						],
						[
							'order_id'       => $order['id'],
							'date'           => $order['date'],
							'date_formatted' => $date,
							'shipping_cost'  => $order['shipping_cost'],
							'for_payment'    => $order['for_payment'],
							'payment_method' => $order['payment_method'],
							'paid'           => $order['paid']
						]
					];
				},
				$orders
			) ?: false
		).
		pages(
			$page,
			ceil($orders_total / $count),
			function ($page) use ($Session, $module_path, $orders_path) {
				$base_url = "$module_path/$orders_path/?";
				return $base_url.http_build_query(
					array_merge(
						[
							'page' => $page,
							'user' => $Session->get_user()
						] + (array)$_GET
					)
				);
			},
			true
		)
	)
);
