<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Index,
	cs\Page;

$Index          = Index::instance();
$Page           = Page::instance();
$Order_statuses = Order_statuses::instance();
if (isset($Index->route_ids[0])) {
	$order_status = $Order_statuses->get($Index->route_ids[0]);
	if (!$order_status) {
		error_code(404);
	} else {
		$Page->json($order_status);
	}
} elseif (isset($Index->route_path[2]) && $Index->route_path[2] == 'types') {
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
