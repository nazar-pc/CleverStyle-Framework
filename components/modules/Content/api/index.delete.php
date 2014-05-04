<?php
/**
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */

namespace cs\modules\Content;

use
	cs\Index,
	cs\User;

if (!User::instance()->admin()) {
	error_code(403);
	return;
}

$Index = Index::instance();

if (!isset($Index->route_path[0])) {
	error_code(400);
	return;
}

$result = Content::instance()->del($Index->route_path[0]);

if (!$result) {
	error_code(500);
	return;
}
