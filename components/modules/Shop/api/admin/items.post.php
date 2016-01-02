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
	cs\Route;

if (!isset(
	$_POST['category'],
	$_POST['price'],
	$_POST['in_stock'],
	$_POST['soon'],
	$_POST['listed'],
	$_POST['attributes'],
	$_POST['images'],
	$_POST['tags']
)
) {
	throw new ExitException(400);
}
$id = Items::instance()->add(
	$_POST['category'],
	$_POST['price'],
	$_POST['in_stock'],
	$_POST['soon'],
	$_POST['listed'],
	$_POST['attributes'],
	_json_decode($_POST['images']) ?: [],
	@$_POST['videos'] ?: [],
	_trim(explode(',', $_POST['tags']))
);
if (!$id) {
	throw new ExitException(500);
}
status_code(201);
$Config = Config::instance();
Page::instance()->json(
	$Config->core_url().'/'.Route::instance()->relative_address."/$id"
);
