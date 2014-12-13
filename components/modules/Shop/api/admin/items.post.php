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
	cs\Config,
	cs\Page;

if (!isset(
	$_POST['category'],
	$_POST['price'],
	$_POST['in_stock'],
	$_POST['soon'],
	$_POST['listed'],
	$_POST['attributes'],
	$_POST['images'],
	$_POST['tags']
)) {
	error_code(400);
	return;
}
$id = Items::instance()->add(
	$_POST['category'],
	$_POST['price'],
	$_POST['in_stock'],
	$_POST['soon'],
	$_POST['listed'],
	$_POST['attributes'],
	_json_decode($_POST['images']),
	_trim(explode(',', $_POST['tags']))
);
if (!$id) {
	error_code(500);
	return;
}
code_header(201);
$Config = Config::instance();
Page::instance()->json(
	$Config->core_url().'/'.$Config->server['relative_address']."/$id"
);
