<?php
/**
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */

namespace cs\modules\Content;

use cs\Page;

if (!isset($_GET['key'])) {
	error_code(400);
	return;
}

$content = Content::instance()->get($_GET['key']);

if (!$content) {
	error_code(404);
	return;
}

Page::instance()->json($content);
