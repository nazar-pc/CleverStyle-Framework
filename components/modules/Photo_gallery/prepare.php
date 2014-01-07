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
			cs\DB,
			cs\Index,
			cs\Language,
			cs\Page,
			cs\User;
Index::instance()->title_auto	= false;
$Config	= Config::instance();
$L		= Language::instance();
$Page	= Page::instance();
$Page->title($L->Photo_gallery);
$rc		= &$Config->route;
if (isset($rc[0])) {
	if (!isset($rc[1])) {
		$rc	= ['gallery', $rc[0]];
	} else {
		$rc	= ['edit_images', $rc[1]];
	}
}
if (isset($_POST['edit_images'])) {
	$User	= User::instance();
	$Photo_gallery	= Photo_gallery::instance();
	foreach ($_POST['edit_images'] as $image => $data) {
		$image	= $Photo_gallery->get($image);
		if ($image && $User->admin() || $image['user'] == $User->id) {
			if (isset($data['delete'])) {
				$Photo_gallery->del($image['id']);
			} else {
				$Photo_gallery->set($image['id'], $data['title'], $data['description']);
			}
		}
	}
	$Page->success($L->changes_saved);
}
