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
	cs\Response,
	cs\Route;

if (!isset(
	$_POST['price'],
	$_POST['phone_needed'],
	$_POST['address_needed'],
	$_POST['title'],
	$_POST['description']
)
) {
	throw new ExitException(400);
}
$id = Shipping_types::instance()->add(
	$_POST['price'],
	$_POST['phone_needed'],
	$_POST['address_needed'],
	$_POST['title'],
	$_POST['description']
);
if (!$id) {
	throw new ExitException(500);
}
Response::instance()->code = 201;
Page::instance()->json(
	Config::instance()->core_url().'/'.Route::instance()->relative_address."/$id"
);
