<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
if (!isset($_POST['mode'])) {
	return;
}
global $Index, $Blog;
switch ($_POST['mode']) {
	case 'add_category':
		$Index->save((bool)$Blog->add_category($_POST['parent'], $_POST['title'], $_POST['path']));
	break;
	case 'edit_category':
		$Index->save((bool)$Blog->set_category($_POST['id'], $_POST['parent'], $_POST['title'], $_POST['path']));
	break;
	case 'delete_category':
		$Index->save((bool)$Blog->del_category($_POST['id']));
	break;
	case 'add_page':
		$Index->save((bool)$Blog->add($_POST['category'], $_POST['title'], $_POST['path'], $_POST['content'], $_POST['interface']));
	break;
	case 'edit_page':
		$Index->save((bool)$Blog->set($_POST['id'], $_POST['category'], $_POST['title'], $_POST['path'], $_POST['content'], $_POST['interface']));
	break;
	case 'delete_page':
		$Index->save((bool)$Blog->del($_POST['id']));
	break;
}