<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	h,
	cs\Language\Prefix,
	cs\Page;

$L                  = new Prefix('shop_');
$Shipping_types     = Shipping_types::instance();
$all_shipping_types = $Shipping_types->get($Shipping_types->get_all());
usort(
	$all_shipping_types,
	function ($shipping_type1, $shipping_type2) {
		return $shipping_type1['title'] <=> $shipping_type2['title'];
	}
);
Page::instance()
	->title($L->shipping_types)
	->content(
		h::{'h2.cs-text-center'}($L->shipping_types).
		h::{'table.cs-table[list]'}(
			h::{'tr th'}(
				'id',
				"$L->title ".h::icon('caret-down'),
				$L->price,
				$L->phone_needed,
				$L->address_needed,
				$L->action
			).
			h::{'tr| td'}(
				array_map(
					function ($shipping_type) use ($L) {
						return [
							$shipping_type['id'],
							$shipping_type['title'],
							$shipping_type['price'],
							h::icon($shipping_type['phone_needed'] ? 'check' : 'minus'),
							h::icon($shipping_type['address_needed'] ? 'check' : 'minus'),
							h::{'button.cs-shop-shipping-type-edit[is=cs-button]'}(
								$L->edit,
								[
									'data-id' => $shipping_type['id']
								]
							).
							h::{'button.cs-shop-shipping-type-delete[is=cs-button]'}(
								$L->delete,
								[
									'data-id' => $shipping_type['id']
								]
							)
						];
					},
					$all_shipping_types
				) ?: false
			)
		).
		h::{'p button.cs-shop-shipping-type-add[is=cs-button]'}($L->add)
	);
