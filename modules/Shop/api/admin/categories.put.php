<?php
/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Shop;
use
	cs\ExitException,
	cs\Request;

$Request = Request::instance();
if (!isset(
	$Request->route_ids[0],
	$_POST['parent'],
	$_POST['title'],
	$_POST['description'],
	$_POST['title_attribute'],
	$_POST['description_attribute'],
	$_POST['image'],
	$_POST['visible'],
	$_POST['attributes']
)
) {
	throw new ExitException(400);
}
$result = Categories::instance()->set(
	$Request->route_ids[0],
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
	throw new ExitException(500);
}
