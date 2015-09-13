<?php
/**
 * @package   Content
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Content;
use
	cs\ExitException,
	cs\User,
	cs\Route;

if (!User::instance()->admin()) {
	throw new ExitException(403);
}
$Route = Route::instance();
if (!isset($Route->route[0])) {
	throw new ExitException(400);
}
$result = Content::instance()->del($Route->route[0]);
if (!$result) {
	throw new ExitException(500);
}
