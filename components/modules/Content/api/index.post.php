<?php
/**
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */

namespace cs\modules\Content;

use cs\User;

if (!User::instance()->admin()) {
	error_code(403);
	return;
}

if (!isset($_POST['key'], $_POST['title'], $_POST['content'], $_POST['type'])) {
	error_code(400);
	return;
}

$result = Content::instance()->add($_POST['key'], $_POST['title'], $_POST['content'], $_POST['type']);

if (!$result) {
	error_code(500);
	return;
}
