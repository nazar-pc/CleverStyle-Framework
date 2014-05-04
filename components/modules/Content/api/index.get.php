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
	cs\Page;

$Index = Index::instance();

if (!isset($Index->route_path[0])) {
	error_code(400);
	return;
}

$content = Content::instance()->get($Index->route_path[0]);

if (!$content) {
	error_code(404);
	return;
}

Page::instance()->json($content);
