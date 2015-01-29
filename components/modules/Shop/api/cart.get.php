<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * Provides next triggers:
 *  Shop/Cart/calculate
 *  [
 *   'items'    => &$items,   // Array of array elements [id => item_id, units => units, price => total_price]
 *   'shipping' => &$shipping // Array in form [type => shipping_type_id, price => shipping_type_price]
 *  ]
 */

namespace cs\modules\Shop;
use
	cs\Page;

$items         = @$_GET['items'];
$shipping_type = @$_GET['shipping_type'];
if (!$items || !$shipping_type) {
	error_code(400);
	return;
}
$shipping_type = Shipping_types::instance()->get_for_user($shipping_type);
if (!$shipping_type) {
	error_code(404);
}
Page::instance()->json(
	Orders::instance()->get_recalculated_cart_prices($items, $shipping_type['id'])
);
