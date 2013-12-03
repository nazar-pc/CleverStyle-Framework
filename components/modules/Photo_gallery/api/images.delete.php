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
$Config			= Config::instance();
$User			= User::instance();
/**
 * If AJAX request from local referer, user is not guest - allow
 */
if (!(
	$Config->server['referer']['local'] &&
	$Config->server['ajax'] &&
	$User->user()
)) {
	sleep(1);
	error_code(403);
	return;
}
if (!isset($Config->route[1])) {
	error_code(400);
	return;
}
$Photo_gallery	= Photo_gallery::instance();
$image			= $Photo_gallery->get($Config->route[1]);
if (!$image) {
	error_code(404);
	return;
}
if ($User->admin() || $image['user'] == $User->id) {
	$Photo_gallery->del($image['id']);
	Page::instance()->json('ok');
} else {
	error_code(403);
}