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
if (!isset($Route->ids[0])) {
	throw new ExitException(400);
}
if (!Orders::instance()->del($Route->ids[0])) {
	throw new ExitException(500);
}
