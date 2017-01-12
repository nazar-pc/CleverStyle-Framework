<?php
/**
 * @package   Photo gallery
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Photo_gallery;
use
	cs\Language\Prefix,
	cs\Page;

$L             = new Prefix('photo_gallery_');
$Page          = Page::instance();
$Photo_gallery = Photo_gallery::instance();
if (isset($_POST['add'])) {
	$add = $_POST['add'];
	if ($Photo_gallery->add_gallery($add['title'], isset($add['path']) ? $add['path'] : null, $add['description'], $add['active'], $add['preview_image'])) {
		$Page->success($L->changes_saved);
	} else {
		$Page->warning($L->changes_save_error);
	}
} elseif (isset($_POST['edit'])) {
	$edit = $_POST['edit'];
	if ($Photo_gallery->set_gallery(
		$edit['id'],
		$edit['title'],
		isset($edit['path']) ? $edit['path'] : null,
		$edit['description'],
		$edit['active'],
		$edit['preview_image']
	)
	) {
		$Page->success($L->changes_saved);
	} else {
		$Page->warning($L->changes_save_error);
	}
} elseif (isset($_POST['delete'])) {
	if ($Photo_gallery->del_gallery($_POST['delete'])) {
		$Page->success($L->changes_saved);
	} else {
		$Page->warning($L->changes_save_error);
	}
}
