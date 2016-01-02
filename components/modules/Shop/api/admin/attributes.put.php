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
	cs\ExitException,
	cs\Route;

$Route = Route::instance();
if (!isset(
	$Route->ids[0],
	$_POST['type'],
	$_POST['title'],
	$_POST['title_internal'],
	$_POST['value']
)
) {
	throw new ExitException(400);
}
$result = Attributes::instance()->set(
	$Route->ids[0],
	$_POST['type'],
	$_POST['title'],
	$_POST['title_internal'],
	$_POST['value']
);
if (!$result) {
	throw new ExitException(500);
}
