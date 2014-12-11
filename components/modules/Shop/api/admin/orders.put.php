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
if (!isset(
	$Index->route_ids[0],
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
$result = Orders::instance()->set(
	$Index->route_ids[0],
	$_POST['user'],
	$_POST['shipping_type'],
	$_POST['shipping_phone'],
	$_POST['shipping_address'],
	$_POST['status'],
	$_POST['comment']
);
if (!$result) {
	error_code(500);
	return;
}
