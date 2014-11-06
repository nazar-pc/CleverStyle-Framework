<?php
/**
 * @package        Photo gallery
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2013-2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Photo_gallery;

use
	cs\DB,
	cs\Language,
	cs\Page,
	cs\User;

$L    = Language::instance();
$Page = Page::instance();
if (isset($_POST['edit_images'])) {
	$User          = User::instance();
	$Photo_gallery = Photo_gallery::instance();
	foreach ($_POST['edit_images'] as $image => $data) {
		$image = $Photo_gallery->get($image);
		if ($image && $User->admin() || $image['user'] == $User->id) {
			if (isset($data['delete']) && $data['delete']) {
				$Photo_gallery->del($image['id']);
			} else {
				$Photo_gallery->set($image['id'], $data['title'], $data['description']);
			}
		}
	}
	$Page->success($L->changes_saved);
}
