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
	cs\Route,
	cs\Session;

$Page    = Page::instance();
$Route   = Route::instance();
$Session = Session::instance();
$Orders  = Orders::instance();
if (isset($Route->ids[0], $Route->path[1])) {
	$order = $Orders->get($Route->ids[0]);
	if (!$order) {
		error_code(404);
	} elseif (
		$order['user'] != $Session->get_user() &&
		!in_array($order['id'], $Session->get_data('shop_orders'))
	) {
		error_code(403);
	}
	switch ($Route->path[1]) {
		/**
		 * Get order items, not order itself
		 */
		case 'items':
			$Page->json(
				$Orders->get_items($Route->ids[0])
			);
			break;
		case 'statuses':
			$Page->json(
				$Orders->get_statuses($Route->ids[0])
			);
			break;
	}
} elseif (isset($Route->ids[0])) {
	$order = $Orders->get($Route->ids[0]);
	if (!$order) {
		error_code(404);
	} elseif (
		$order['user'] != $Session->get_user() &&
		!in_array($order['id'], $Session->get_data('shop_orders'))
	) {
		error_code(403);
	} else {
		$Page->json($order);
	}
} elseif ($Session->user()) {
	$Page->json(
		$Orders->get(
			$Orders->search(
				[
					'user' => $Session->get_user()
				]
			)
		)
	);
} else {
	$Page->json(
		$Orders->get(
			$Session->get_data('shop_orders') ?: []
		)
	);
}
