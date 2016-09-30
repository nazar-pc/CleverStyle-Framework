<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\ExitException,
	cs\Page,
	cs\Request,
	cs\Session;

$Page    = Page::instance();
$Request = Request::instance();
$Session = Session::instance();
$Orders  = Orders::instance();
if (isset($Request->route_ids[0], $Request->route_path[1])) {
	$order = $Orders->get($Request->route_ids[0]);
	if (!$order) {
		throw new ExitException(404);
	} elseif (
		$order['user'] != $Session->get_user() &&
		!in_array($order['id'], $Session->get_data('shop_orders'))
	) {
		throw new ExitException(403);
	}
	switch ($Request->route_path[1]) {
		/**
		 * Get order items, not order itself
		 */
		case 'items':
			$Page->json(
				$Orders->get_items($Request->route_ids[0])
			);
			break;
		case 'statuses':
			$Page->json(
				$Orders->get_statuses($Request->route_ids[0])
			);
			break;
	}
} elseif (isset($Request->route_ids[0])) {
	$order = $Orders->get($Request->route_ids[0]);
	if (!$order) {
		throw new ExitException(404);
	} elseif (
		$order['user'] != $Session->get_user() &&
		!in_array($order['id'], $Session->get_data('shop_orders'))
	) {
		throw new ExitException(403);
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
			) ?: []
		)
	);
} else {
	$Page->json(
		$Orders->get(
			$Session->get_data('shop_orders') ?: []
		)
	);
}
