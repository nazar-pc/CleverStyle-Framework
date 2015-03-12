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
$Order_statuses = Order_statuses::instance();
if (isset($Route->ids[0])) {
	$order_status = $Order_statuses->get($Route->ids[0]);
	if (!$order_status) {
		error_code(404);
	} else {
		$Page->json($order_status);
	}
} elseif (isset($Route->path[2]) && $Route->path[2] == 'types') {
	$Page->json(
		$Order_statuses->get_type_to_name_array()
	);
} else {
	$Page->json(
		$Order_statuses->get(
			$Order_statuses->get_all()
		)
	);
}
