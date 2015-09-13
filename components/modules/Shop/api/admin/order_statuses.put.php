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
	cs\ExitException,
	cs\Route;

$Route = Route::instance();
if (!isset(
	$Route->ids[0],
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
	$Route->ids[0],
	$_POST['title'],
	$_POST['type'],
	$_POST['color'],
	$_POST['send_update_status_email'],
	$_POST['comment']
);
if (!$result) {
	throw new ExitException(500);
}
