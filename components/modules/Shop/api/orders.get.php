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
	cs\Page,
	cs\User;

$Index  = Index::instance();
$Page   = Page::instance();
$User   = User::instance();
$Orders = Orders::instance();
if ($User->guest()) {
	error_code(403);
	return;
}
/**
 * Get order items, not order itself
 */
if (isset($Index->route_ids[0], $Index->route_path[2]) && $Index->route_path[2] == 'items') {
	$Page->json(
		$Orders->get_items($Index->route_ids[0])
	);
} elseif (isset($Index->route_ids[0])) {
	$order = $Orders->get($Index->route_ids[0]);
	if (!$order) {
		error_code(404);
	} elseif ($order['user'] != $User->id) {
		error_code(403);
	} else {
		$Page->json($order);
	}
} else {
	$Page->json(
		$Orders->get(
			$Orders->search([
				'user' => $User->id
			])
		)
	);
}
