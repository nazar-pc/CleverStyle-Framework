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
	$_POST['type'],
	$_POST['title'],
	$_POST['title_internal'],
	$_POST['value']
)) {
	error_code(400);
	return;
}
$id = Attributes::instance()->add(
	$_POST['type'],
	$_POST['title'],
	$_POST['title_internal'],
	$_POST['value']
);
if (!$id) {
	error_code(500);
	return;
}
code_header(201);
$Config = Config::instance();
Page::instance()->json(
	$Config->core_url().'/'.Route::instance()->relative_address."/$id"
);
