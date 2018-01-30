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
if (!isset($Request->route_ids[0])) {
	throw new ExitException(400);
}
if (!Orders::instance()->del($Request->route_ids[0])) {
	throw new ExitException(500);
}
