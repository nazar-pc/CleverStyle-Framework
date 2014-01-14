<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			cs\Config,
			cs\Index,
			cs\Language,
			cs\Page;
if (!isset($_POST['mode'])) {
	return;
}
$Index	= Index::instance();
$Blogs	= Blogs::instance();
$draft	= false;
switch ($_POST['mode']) {
	case 'add_section':
		$Index->save($Blogs->add_section($_POST['parent'], $_POST['title'], isset($_POST['path']) ? $_POST['path'] : null));
	break;
	case 'edit_section':
		$Index->save($Blogs->set_section($_POST['id'], $_POST['parent'], $_POST['title'], isset($_POST['path']) ? $_POST['path'] : null));
	break;
	case 'delete_section':
		$Index->save($Blogs->del_section($_POST['id']));
	break;
	case 'edit_post_draft':
		$draft	= true;
	case 'edit_post':
		$L		= Language::instance();
		$Page	= Page::instance();
		$save	= true;
		if (empty($_POST['title'])) {
			$Page->warning($L->post_title_empty);
			$save	= false;
		}
		if (empty($_POST['sections']) && $_POST['sections'] !== '0') {
			$Page->warning($L->no_post_sections_specified);
			$save	= false;
		}
		if (empty($_POST['content'])) {
			$Page->warning($L->post_content_empty);
			$save	= false;
		}
		if (empty($_POST['tags'])) {
			$Page->warning($L->no_post_tags_specified);
			$save	= false;
		}
		if ($save) {
			$Index->save(
				$Blogs->set($_POST['id'], $_POST['title'], null, $_POST['content'], $_POST['sections'], _trim(explode(',', $_POST['tags'])), $draft)
			);
		}
	break;
	case 'delete_post':
		$Index->save($Blogs->del($_POST['id']));
	break;
	case 'general':
		$Index->save(Config::instance()->module('Blogs')->set($_POST['general']));
	break;
}
