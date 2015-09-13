<?php
/**
 * @package   Photo gallery
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Photo_gallery;
use
	cs\ExitException,
	cs\Page,
	cs\Route,
	cs\User;
$Route = Route::instance();
$User  = User::instance();
if (!$User->user()) {
	throw new ExitException(403);
}
if (!isset($Route->route[1])) {
	throw new ExitException(400);
}
$Photo_gallery = Photo_gallery::instance();
$image         = $Photo_gallery->get($Route->route[1]);
if (!$image) {
	throw new ExitException(404);
}
if ($User->admin() || $image['user'] == $User->id) {
	$Photo_gallery->del($image['id']);
	Page::instance()->json('ok');
} else {
	throw new ExitException(403);
}
