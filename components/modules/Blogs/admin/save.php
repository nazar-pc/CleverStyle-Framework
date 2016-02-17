<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	cs\Config,
	cs\Language\Prefix,
	cs\Page;

if (!isset($_POST['mode'])) {
	return;
}
$L        = new Prefix('blogs_');
$Page     = Page::instance();
$Posts    = Posts::instance();
$Sections = Sections::instance();
switch ($_POST['mode']) {
	case 'add_section':
		if ($Sections->add($_POST['parent'], $_POST['title'], isset($_POST['path']) ? $_POST['path'] : null)) {
			$Page->success($L->changes_saved);
		} else {
			$Page->warning($L->changes_save_error);
		}
		break;
	case 'edit_section':
		if ($Sections->set($_POST['id'], $_POST['parent'], $_POST['title'], isset($_POST['path']) ? $_POST['path'] : null)) {
			$Page->success($L->changes_saved);
		} else {
			$Page->warning($L->changes_save_error);
		}
		break;
	case 'delete_section':
		if ($Sections->del($_POST['id'])) {
			$Page->success($L->changes_saved);
		} else {
			$Page->warning($L->changes_save_error);
		}
		break;
	case 'delete_post':
		if ($Posts->del($_POST['id'])) {
			$Page->success($L->changes_saved);
		} else {
			$Page->warning($L->changes_save_error);
		}
		break;
	case 'general':
		if (Config::instance()->module('Blogs')->set($_POST['general'])) {
			$Page->success($L->changes_saved);
		} else {
			$Page->warning($L->changes_save_error);
		}
		break;
}
