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

$Index = Index::instance();
if (!isset($Index->route_ids[0])) {
	error_code(400);
	return;
}
$Orders		= Orders::instance();
$order_id	= $Index->route_ids[0];
if (isset($Index->route_path[2]) && $Index->route_path[2] == 'items') {
	if (!isset($_POST['items']) || empty($_POST['items'])) {
		error_code(400);
		return;
	}
	$items	= $Orders->get_items($order_id);
	foreach (array_column($items, 'item') as $item) {
		$Orders->del($order_id, $item);
	}
	unset($items, $item);
	$result = true;
	$items	= array_map(null, $_POST['items']['item'], $_POST['items']['units'], $_POST['items']['price'], $_POST['items']['unit_price']);
	foreach ($items as $item) {
		$result	= $Orders->add_item($order_id, $item[0], $item[1], $item[2], $item[3]) && $result;
	}
} else {
	if (!isset(
		$_POST['user'],
		$_POST['shipping_type'],
		$_POST['shipping_phone'],
		$_POST['shipping_address'],
		$_POST['status'],
		$_POST['comment']
	)) {
		error_code(400);
		return;
	}
	$result = $Orders->set(
		$order_id,
		$_POST['user'],
		$_POST['shipping_type'],
		$_POST['shipping_phone'],
		$_POST['shipping_address'],
		$_POST['status'],
		$_POST['comment']
	);
}
if (!$result) {
	error_code(500);
	return;
}
