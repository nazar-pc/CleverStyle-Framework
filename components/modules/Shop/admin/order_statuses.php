<?php
/**
 * @package    Shop
 * @order_status  modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	h,
	cs\Config,
	cs\Index,
	cs\Language\Prefix,
	cs\Page;

Index::instance()->buttons = false;
$L                         = new Prefix('shop_');
$Page                      = Page::instance();
$Page->title($L->order_statuses);
$Order_statuses  = Order_statuses::instance();
$all_order_statuses  = $Order_statuses->get($Order_statuses->get_all());
$order_status_types = $Order_statuses->get_type_to_name_array();
usort($all_order_statuses, function ($order_status1, $order_status2) {
	return $order_status1['title'] > $order_status2['title'] ? 1 : -1;
});
$Page->content(
	h::{'h3.uk-lead.cs-center'}($L->order_statuses).
	h::{'cs-table[list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
			'id',
			"$L->title ".h::icon('caret-down'),
			$L->order_status_type,
			$L->send_update_status_email,
			$L->action
		).
		h::{'cs-table-row'}(array_map(
			function ($order_status) use ($L, $order_status_types) {
				return h::cs_table_cell(
					[
						$order_status['id'],
						$order_status['title'],
						$order_status_types[$order_status['type']],
						h::icon($order_status['send_update_status_email'] ? 'check' : 'minus'),
						h::{'button.uk-button.cs-shop-order-status-edit'}(
							$L->edit,
							[
								'data-id' => $order_status['id']
							]
						).
						h::{'button.uk-button.cs-shop-order-status-delete'}(
							$L->delete,
							[
								'data-id' => $order_status['id']
							]
						)
					],
					[
						'style' => $order_status['color'] ? "background: $order_status[color]" : ''
					]
				);
			},
			$all_order_statuses
		) ?: false)
	).
	h::{'p button.uk-button.cs-shop-order-status-add'}($L->add)
);
