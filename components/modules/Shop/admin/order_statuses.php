<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	h,
	cs\Index,
	cs\Language\Prefix,
	cs\Page;

$Index = Index::instance();
$L     = new Prefix('shop_');
$Page  = Page::instance();
$Page->title($L->order_statuses);
$Order_statuses     = Order_statuses::instance();
$all_order_statuses = $Order_statuses->get($Order_statuses->get_all());
$order_status_types = $Order_statuses->get_type_to_name_array();
usort(
	$all_order_statuses,
	function ($order_status1, $order_status2) {
		return $order_status1['title'] > $order_status2['title'] ? 1 : -1;
	}
);
$Index->buttons = false;
$Index->content(
	h::h2($L->order_statuses).
	h::{'table.cs-table[list]'}(
		h::{'tr th'}(
			'id',
			"$L->title ".h::icon('caret-down'),
			$L->order_status_type,
			$L->send_update_status_email,
			$L->action
		).
		h::{'tr| td'}(
			array_map(
				function ($order_status) use ($L, $order_status_types) {
					return [
						[
							$order_status['id'],
							$order_status['title'],
							$order_status_types[$order_status['type']],
							h::icon($order_status['send_update_status_email'] ? 'check' : 'minus'),
							h::{'button.cs-shop-order-status-edit[is=cs-button]'}(
								$L->edit,
								[
									'data-id' => $order_status['id']
								]
							).
							h::{'button.cs-shop-order-status-delete[is=cs-button]'}(
								$L->delete,
								[
									'data-id' => $order_status['id']
								]
							)
						],
						[
							'style' => $order_status['color'] ? "background: $order_status[color]" : ''
						]
					];
				},
				$all_order_statuses
			) ?: false
		)
	).
	h::{'button.cs-shop-order-status-add[is=cs-button]'}($L->add)
);
