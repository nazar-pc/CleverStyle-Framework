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
$result = Items::instance()->set(
	$Index->route_ids[0],
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
if (!$result) {
	error_code(500);
	return;
}
