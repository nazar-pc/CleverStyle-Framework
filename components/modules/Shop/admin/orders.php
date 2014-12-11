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
	cs\Index,
	cs\Language,
	cs\Language\Prefix,
	cs\Page,
	cs\User;

Index::instance()->buttons = false;
$L                         = new Prefix('shop_');
$Language                  = Language::instance();
$Page                      = Page::instance();
$Page->title($L->orders);
$Items          = Items::instance();
$Orders         = Orders::instance();
$Order_statuses = Order_statuses::instance();
$Shipping_types = Shipping_types::instance();
$all_orders     = $Orders->get($Orders->get_all());
$Page->content(
	h::{'h3.uk-lead.cs-center'}($L->orders).
	h::{'cs-table[list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
			'id',
			$L->datetime,
			$L->user,
			$L->order_items,
			$L->shipping_type,
			$L->status,
			$L->comment,
			$L->action
		).
		h::{'cs-table-row'}(array_map(
			function ($order) use ($L, $Language, $Items, $Order_statuses, $Orders, $Shipping_types) {
				$order_status = $Order_statuses->get($order['status']);
				$date         = $L->to_locale(
					date($Language->{TIME - $order['date'] < 24 * 3600 ? '_time' : '_datetime_long'}, $order['date'])
				);
				$username     = User::instance()->username($order['user']);
				return h::cs_table_cell(
					[
						$order['id'],
						$date,
						$username.h::br().$order['shipping_phone'], // TODO links to all orders of this user
						implode(
							h::br(),
							array_map(
								function ($item) use ($Items) {
									return $Items->get($item['item'])['title']; // TODO links to items
								},
								$Orders->get_items($order['id'])
							)
						),
						$Shipping_types->get($order['shipping_type'])['title'], // TODO links to all orders with this shipping type
						$order_status['title'], // TODO links to all orders with this order status
						$order['comment'],
						h::{'button.uk-button.cs-shop-order-details'}( // TODO details page/modal
							$L->details,
							[
								'data-id' => $order['id']
							]
						).
						h::{'button.uk-button.cs-shop-order-edit'}(
							$L->edit,
							[
								'data-id'       => $order['id'],
								'data-username' => $username,
								'data-date'     => $date
							]
						).
						h::{'button.uk-button.cs-shop-order-delete'}(
							$L->delete,
							[
								'data-id' => $order['id']
							]
						)
					],
					[
						'style' => $order_status['color'] ? "background: $order_status[color]" : ''
					]
				);
			},
			$all_orders
		) ?: false)
	).
	h::{'p button.uk-button.cs-shop-order-add'}($L->add)
);
