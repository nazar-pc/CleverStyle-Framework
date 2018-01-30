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
	$_POST['title'],
	$_POST['type'],
	$_POST['color'],
	$_POST['send_update_status_email'],
	$_POST['comment']
)
) {
	throw new ExitException(400);
}
$result = Order_statuses::instance()->set(
	$Request->route_ids[0],
	$_POST['title'],
	$_POST['type'],
	$_POST['color'],
	$_POST['send_update_status_email'],
	$_POST['comment']
);
if (!$result) {
	throw new ExitException(500);
}
