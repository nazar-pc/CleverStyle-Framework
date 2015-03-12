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
	cs\Page,
	cs\Route;

$Route = Route::instance();
if (!isset($Route->route[0])) {
	error_code(400);
	return;
}
$content = Content::instance()->get($Route->route[0]);
if (!$content) {
	error_code(404);
	return;
}
Page::instance()->json($content);
