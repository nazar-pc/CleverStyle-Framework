<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2013
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Photo_gallery;
use			h,
			cs\Config,
			cs\Page,
			cs\User;
$Config	= Config::instance();
/**
 * If AJAX request from local referer, user is not guest - allow
 */
if (!(
	$Config->server['referer']['local'] &&
	$Config->server['ajax'] &&
	User::instance()->user()
)) {
	sleep(1);
	error_code(403);
	return;
}
if (!isset($_POST['files'], $_POST['gallery']) || empty($_POST['files'])) {
	error_code(400);
	return;
}
$Photo_gallery	= Photo_gallery::instance();
$files			= $_POST['files'];
foreach ($files as $i => &$file) {
	$file	= $Photo_gallery->add($file, $_POST['gallery']);
	if (!$file) {
		unset($files[$i]);
	}
}
unset($i, $file);
Page::instance()->json($files);