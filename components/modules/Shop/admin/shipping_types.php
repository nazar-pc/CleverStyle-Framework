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
	cs\Language\Prefix,
	cs\Page;

Index::instance()->buttons = false;
$L                         = new Prefix('shop_');
$Page                      = Page::instance();
$Page->title($L->shipping_types);
$Shipping_types     = Shipping_types::instance();
$all_shipping_types = $Shipping_types->get($Shipping_types->get_all());
usort($all_shipping_types, function ($attr1, $attr2) {
	return $attr1['title'] > $attr2['title'] ? 1 : -1;
});
$Page->content(
	h::{'h3.uk-lead.cs-center'}($L->shipping_types).
	h::{'cs-table[list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
			$L->title,
			$L->price,
			$L->phone_needed,
			$L->address_needed,
			$L->action
		).
		h::{'cs-table-row| cs-table-cell'}(array_map(
			function ($order_status) use ($L) {
				return [
					$order_status['title'],
					$order_status['price'],
					h::icon($order_status['phone_needed'] ? 'check' : 'minus'),
					h::icon($order_status['address_needed'] ? 'check' : 'minus'),
					h::{'button.uk-button.cs-shop-shipping-type-edit'}(
						$L->edit,
						[
							'data-id' => $order_status['id']
						]
					).
					h::{'button.uk-button.cs-shop-shipping-type-delete'}(
						$L->delete,
						[
							'data-id' => $order_status['id']
						]
					)
				];
			},
			$all_shipping_types
		) ?: false)
	).
	h::{'p button.uk-button.cs-shop-shipping-type-add'}($L->add)
);
