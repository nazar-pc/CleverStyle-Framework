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
	$_POST['price'],
	$_POST['phone_needed'],
	$_POST['address_needed'],
	$_POST['title'],
	$_POST['description']
)
) {
	throw new ExitException(400);
}
$result = Shipping_types::instance()->set(
	$Request->route_ids[0],
	$_POST['price'],
	$_POST['phone_needed'],
	$_POST['address_needed'],
	$_POST['title'],
	$_POST['description']
);
if (!$result) {
	throw new ExitException(500);
}
