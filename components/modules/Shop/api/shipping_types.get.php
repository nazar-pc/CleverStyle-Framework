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
	cs\Index,
	cs\Page;

$Index          = Index::instance();
$Page           = Page::instance();
$Shipping_types = Shipping_types::instance();
if (isset($_GET['ids'])) {
	$shipping_types = $Shipping_types->get_for_user(explode(',', $Index->route_ids[0]));
	if (!$shipping_types) {
		error_code(404);
	} else {
		$Page->json($shipping_types);
	}
} elseif (isset($Index->route_ids[0])) {
	$shipping_type = $Shipping_types->get_for_user($Index->route_ids[0]);
	if (!$shipping_type) {
		error_code(404);
	} else {
		$Page->json($shipping_type);
	}
} else {
	$Page->json(
		$Shipping_types->get_for_user(
			$Shipping_types->get_all()
		)
	);
}
