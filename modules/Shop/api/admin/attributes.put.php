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
	$_POST['type'],
	$_POST['title'],
	$_POST['title_internal'],
	$_POST['value']
)
) {
	throw new ExitException(400);
}
$result = Attributes::instance()->set(
	$Request->route_ids[0],
	$_POST['type'],
	$_POST['title'],
	$_POST['title_internal'],
	$_POST['value']
);
if (!$result) {
	throw new ExitException(500);
}
