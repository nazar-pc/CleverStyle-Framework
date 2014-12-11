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
$Orders = Orders::instance();
if (isset($Index->route_ids[0])) {
	$orders = $Orders->get($Index->route_ids[0]);
	if (!$orders) {
		error_code(404);
	} else {
		$Page->json($orders);
	}
	return;
} else {
	$Page->json(
		$Orders->get(
			$Orders->get_all()
		)
	);
}
