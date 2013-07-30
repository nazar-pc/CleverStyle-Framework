<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Photo_gallery;
use			h,
			cs\Config,
			cs\Page,
			cs\User;
$Config	= Config::instance();
$User	= User::instance();
/**
 * If AJAX request from local referer, user is not guest - allow
 */
if (!(
	$Config->server['referer']['local'] &&
	$Config->server['ajax'] &&
	$User->user()
)) {
	sleep(1);
	define('ERROR_CODE', 403);
	return;
}
if (!isset($_POST['image'])) {
	define('ERROR_CODE', 400);
	return;
}
$Photo_gallery	= Photo_gallery::instance();
$image			= $Photo_gallery->get($_POST['image']);
if (!$image) {
	define('ERROR_CODE', 404);
	return;
}
if ($User->admin() || $image['user'] == $User->id) {
	$Photo_gallery->del($image['id']);
	Page::instance()->json('ok');
} else {
	define('ERROR_CODE', 403);
}