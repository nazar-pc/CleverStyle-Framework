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
	cs\Config,
	cs\ExitException,
	cs\Page,
	cs\Request,
	cs\Response;

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
)
) {
	throw new ExitException(400);
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
	throw new ExitException(500);
}
Response::instance()->code = 201;
Page::instance()->json(
	Config::instance()->core_url().'/'.Request::instance()->path_normalized."/$id"
);
