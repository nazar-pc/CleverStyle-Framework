<?php
/**
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */

namespace cs\modules\Content;

use cs\User;

if (!User::instance()->admin()) {
	error_code(403);
	return;
}

if (!isset($_POST['key'])) {
	error_code(400);
	return;
}

$result = Content::instance()->del($_POST['key']);

if (!$result) {
	error_code(500);
	return;
}
