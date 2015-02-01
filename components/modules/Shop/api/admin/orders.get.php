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

$Index  = Index::instance();
$Page   = Page::instance();
$Orders = Orders::instance();
/**
 * Get order items, not order itself
 */
if (isset($Index->route_ids[0], $Index->route_path[2])) {
	switch ($Index->route_path[2]) {
		case 'items':
			$Page->json(
				$Orders->get_items($Index->route_ids[0])
			);
			break;
		case 'statuses':
			$Page->json(
				$Orders->get_statuses($Index->route_ids[0])
			);
			break;
	}
} elseif (isset($Index->route_ids[0])) {
	$order = $Orders->get($Index->route_ids[0]);
	if (!$order) {
		error_code(404);
	} else {
		$Page->json($order);
	}
} else {
	$Page->json(
		$Orders->get(
			$Orders->get_all()
		)
	);
}
