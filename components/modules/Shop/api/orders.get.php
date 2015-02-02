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
if (isset($Index->route_ids[0], $Index->route_path[1])) {
	$order = $Orders->get($Index->route_ids[0]);
	if (!$order) {
		error_code(404);
	} elseif (
		$order['user'] != $User->id &&
		!in_array($order['id'], $User->get_session_data('shop_orders'))
	) {
		error_code(403);
	}
	switch ($Index->route_path[1]) {
		/**
		 * Get order items, not order itself
		 */
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
	} elseif (
		$order['user'] != $User->id &&
		!in_array($order['id'], $User->get_session_data('shop_orders'))
	) {
		error_code(403);
	} else {
		$Page->json($order);
	}
} elseif ($User->user()) {
	$Page->json(
		$Orders->get(
			$Orders->search([
				'user' => $User->id
			])
		)
	);
} else {
	$Page->json(
		$Orders->get(
			$User->get_session_data('shop_orders') ?: []
		)
	);
}
