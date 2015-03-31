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
	cs\Route;

$Route = Route::instance();
if (!isset(
	$Route->ids[0],
	$_POST['parent'],
	$_POST['title'],
	$_POST['description'],
	$_POST['title_attribute'],
	$_POST['description_attribute'],
	$_POST['image'],
	$_POST['visible'],
	$_POST['attributes']
)) {
	error_code(400);
	return;
}
$result = Categories::instance()->set(
	$Route->ids[0],
	$_POST['parent'],
	$_POST['title'],
	$_POST['description'],
	$_POST['title_attribute'],
	$_POST['description_attribute'],
	$_POST['image'],
	$_POST['visible'],
	$_POST['attributes']
);
if (!$result) {
	error_code(500);
	return;
}
