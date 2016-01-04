<?php
/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Static_pages;
use
	cs\Language,
	cs\Page;

if (!isset($_POST['mode'])) {
	return;
}
$L          = Language::instance();
$Page       = Page::instance();
$Pages      = Pages::instance();
$Categories = Categories::instance();
switch ($_POST['mode']) {
	case 'add_category':
		if ($Categories->add($_POST['parent'], $_POST['title'], $_POST['path'])) {
			$Page->success($L->changes_saved);
		} else {
			$Page->warning($L->changes_save_error);
		}
		break;
	case 'edit_category':
		if ($Categories->set($_POST['id'], $_POST['parent'], $_POST['title'], $_POST['path'])) {
			$Page->success($L->changes_saved);
		} else {
			$Page->warning($L->changes_save_error);
		}
		break;
	case 'delete_category':
		if ($Categories->del($_POST['id'])) {
			$Page->success($L->changes_saved);
		} else {
			$Page->warning($L->changes_save_error);
		}
		break;
	case 'add_page':
		if ($Pages->add($_POST['category'], $_POST['title'], $_POST['path'], $_POST['content'], $_POST['interface'])) {
			$Page->success($L->changes_saved);
		} else {
			$Page->warning($L->changes_save_error);
		}
		break;
	case 'edit_page':
		if ($Pages->set($_POST['id'], $_POST['category'], $_POST['title'], $_POST['path'], $_POST['content'], $_POST['interface'])) {
			$Page->success($L->changes_saved);
		} else {
			$Page->warning($L->changes_save_error);
		}
		break;
	case 'delete_page':
		if ($Pages->del($_POST['id'])) {
			$Page->success($L->changes_saved);
		} else {
			$Page->warning($L->changes_save_error);
		}
		break;
}
