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

$L              = new Prefix('shop_');
$Page           = Page::instance();
$Shipping_types = Shipping_types::instance();
$Page
	->title($L->cart)
	->config($Shipping_types->get($Shipping_types->get_all()), 'cs.shop.shipping_types')
	->config(
		Orders::instance()->get_payment_methods(),
		'cs.shop.payment_methods'
	);
$Page->content(
	h::cs_shop_cart()
);
