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
$Shipping_types = Shipping_types::instance();
if (isset($Index->route_ids[0])) {
	$order_status = $Shipping_types->get($Index->route_ids[0]);
	if (!$order_status) {
		error_code(404);
	} else {
		$Page->json($order_status);
	}
	return;
} else {
	$Page->json(
		$Shipping_types->get(
			$Shipping_types->get_all()
		)
	);
}
