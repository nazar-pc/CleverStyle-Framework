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
	cs\Page,
	cs\Route;

$Route = Route::instance();
$Page  = Page::instance();
$Items = Items::instance();
if (isset($_GET['ids'])) {
	$items = $Items->get_for_user(explode(',', $_GET['ids']));
	if (!$items) {
		throw new ExitException(404);
	} else {
		$Page->json($items);
	}
} elseif (isset($Route->ids[0])) {
	$item = $Items->get_for_user($Route->ids[0]);
	if (!$item) {
		throw new ExitException(404);
	} else {
		$Page->json($item);
	}
} else {
	throw new ExitException(400);
}
