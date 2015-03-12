<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Page,
	cs\Route;

$Route          = Route::instance();
$Page           = Page::instance();
$Shipping_types = Shipping_types::instance();
if (isset($_GET['ids'])) {
	$shipping_types = $Shipping_types->get(explode(',', $Route->ids[0]));
	if (!$shipping_types) {
		error_code(404);
	} else {
		$Page->json($shipping_types);
	}
} elseif (isset($Route->ids[0])) {
	$shipping_type = $Shipping_types->get($Route->ids[0]);
	if (!$shipping_type) {
		error_code(404);
	} else {
		$Page->json($shipping_type);
	}
} else {
	$Page->json(
		$Shipping_types->get(
			$Shipping_types->get_all()
		)
	);
}
