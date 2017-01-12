<?php
/**
 * @package   Content
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Content;
use
	cs\ExitException,
	cs\Request,
	cs\User;

if (!User::instance()->admin()) {
	throw new ExitException(403);
}
$Request = Request::instance();
if (!isset($Request->route[0])) {
	throw new ExitException(400);
}
$result = Content::instance()->del($Request->route[0]);
if (!$result) {
	throw new ExitException(500);
}
