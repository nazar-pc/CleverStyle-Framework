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
	cs\Config,
	cs\Page,
	cs\Route;

if (!isset(
	$_POST['user'],
	$_POST['shipping_type'],
	$_POST['shipping_cost'],
	$_POST['shipping_username'],
	$_POST['shipping_phone'],
	$_POST['shipping_address'],
	$_POST['payment_method'],
	$_POST['paid'],
	$_POST['status'],
	$_POST['comment']
)) {
	error_code(400);
	return;
}
$id = Orders::instance()->add(
	$_POST['user'],
	$_POST['shipping_type'],
	$_POST['shipping_cost'],
	$_POST['shipping_username'],
	$_POST['shipping_phone'],
	$_POST['shipping_address'],
	$_POST['payment_method'],
	$_POST['paid'],
	$_POST['status'],
	$_POST['comment']
);
if (!$id) {
	error_code(500);
	return;
}
status_code(201);
$Config = Config::instance();
Page::instance()->json(
	$Config->core_url().'/'.Route::instance()->relative_address."/$id"
);
