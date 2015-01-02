<?php
/**
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */

namespace cs\modules\Content;

use
	cs\Config,
	cs\User;

if (!User::instance()->admin()) {
	error_code(403);
	return;
}

$Config = Config::instance();

if (!isset($Config->route[0])) {
	error_code(400);
	return;
}

$result = Content::instance()->del($Config->route[0]);

if (!$result) {
	error_code(500);
	return;
}
