<?php
/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014-2015, Nazar Mokrynskyi
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
$Page           = Page::instance();
$Categories     = Categories::instance();
$Items          = Items::instance();
$Shipping_types = Shipping_types::instance();
$items          = @_json_decode($_COOKIE['shop_cart_items']);
$Page->title($L->cart);
$Page->config($Shipping_types->get($Shipping_types->get_all()), 'cs.shop.shipping_types');
if (!$items || !is_array($items)) {
	$Page->content(
		h::cs_shop_empty_cart($L->cart_empty)
	);
	return;
}
$module_path = path($L->shop);
$items_path  = path($L->items);
$Page->content(
	h::cs_shop_cart(
		h::h1($L->your_cart).
		h::{'#items cs-shop-cart-item'}(array_map(
			function ($item, $units) use ($Categories, $Items, $module_path, $items_path) {
				$item = $Items->get($item);
				return [
					h::{'img#img'}([
						'src' => @$item['images'][0] ?: 'components/modules/Shop/includes/img/no-image.svg'
					]).
					h::{'a#link'}(
						$item['title'],
						[
							'href'   => "$module_path/$items_path/".path($Categories->get($item['category'])['title']).'/'.path($item['title']).":$item[id]",
							'target' => '_blank'
						]
					).
					h::{'#description'}(truncate($item['description'], 200) ?: false),
					[
						'data-id'         => $item['id'],
						'data-price'      => $item['price'] * $units,
						'data-unit-price' => $item['price'], // TODO discount feature
						'data-units'      => $units
					]
				];
			},
			array_keys($items),
			_int(array_values($items))
		)),
		[
			'username'	=> h::prepare_attr_value(User::instance()->username())
		]
	)
);
