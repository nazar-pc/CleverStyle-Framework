<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Language\Prefix,
	cs\Page,
	cs\Trigger;

$L               = new Prefix('shop_');
$Page            = Page::instance();
$payment_methods = [
	'shop:cash' => [
		'title'       => $L->cash,
		'description' => ''
	]
];
Trigger::instance()->run('System/payment/methods', [
	'methods' => &$payment_methods
]);
$Page->json($payment_methods);
