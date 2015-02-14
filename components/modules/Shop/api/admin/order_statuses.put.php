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

$Index = Index::instance();
if (!isset(
	$Index->route_ids[0],
	$_POST['title'],
	$_POST['type'],
	$_POST['color'],
	$_POST['send_update_status_email'],
	$_POST['comment']
)) {
	error_code(400);
	return;
}
$result = Order_statuses::instance()->set(
	$Index->route_ids[0],
	$_POST['title'],
	$_POST['type'],
	$_POST['color'],
	$_POST['send_update_status_email'],
	$_POST['comment']
);
if (!$result) {
	error_code(500);
	return;
}
