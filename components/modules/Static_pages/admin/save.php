<?php
/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Static_pages;
use            cs\Index;
if (!isset($_POST['mode'])) {
	return;
}
$Index      = Index::instance();
$Pages      = Pages::instance();
$Categories = Categories::instance();
switch ($_POST['mode']) {
	case 'add_category':
		$Index->save($Categories->add($_POST['parent'], $_POST['title'], $_POST['path']));
		break;
	case 'edit_category':
		$Index->save($Categories->set($_POST['id'], $_POST['parent'], $_POST['title'], $_POST['path']));
		break;
	case 'delete_category':
		$Index->save($Categories->del($_POST['id']));
		break;
	case 'add_page':
		$Index->save($Pages->add($_POST['category'], $_POST['title'], $_POST['path'], $_POST['content'], $_POST['interface']));
		break;
	case 'edit_page':
		$Index->save($Pages->set($_POST['id'], $_POST['category'], $_POST['title'], $_POST['path'], $_POST['content'], $_POST['interface']));
		break;
	case 'delete_page':
		$Index->save($Pages->del($_POST['id']));
		break;
}
