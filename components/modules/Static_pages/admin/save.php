<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			cs\Index;
if (!isset($_POST['mode'])) {
	return;
}
$Index			= Index::instance();
$Static_pages	= Static_pages::instance();
switch ($_POST['mode']) {
	case 'add_category':
		$Index->save($Static_pages->add_category($_POST['parent'], $_POST['title'], $_POST['path']));
	break;
	case 'edit_category':
		$Index->save($Static_pages->set_category($_POST['id'], $_POST['parent'], $_POST['title'], $_POST['path']));
	break;
	case 'delete_category':
		$Index->save($Static_pages->del_category($_POST['id']));
	break;
	case 'add_page':
		$Index->save($Static_pages->add($_POST['category'], $_POST['title'], $_POST['path'], $_POST['content'], $_POST['interface']));
	break;
	case 'edit_page':
		$Index->save($Static_pages->set($_POST['id'], $_POST['category'], $_POST['title'], $_POST['path'], $_POST['content'], $_POST['interface']));
	break;
	case 'delete_page':
		$Index->save($Static_pages->del($_POST['id']));
	break;
}
