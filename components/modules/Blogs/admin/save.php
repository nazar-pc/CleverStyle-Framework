<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

if (!isset($_POST['mode'])) {
	return;
}
global $Index, $Blogs;
switch ($_POST['mode']) {
	case 'add_section':
		$Index->save((bool)$Blogs->add_section($_POST['parent'], $_POST['title'], $_POST['path']));
	break;
	case 'edit_section':
		$Index->save((bool)$Blogs->set_section($_POST['id'], $_POST['parent'], $_POST['title'], $_POST['path']));
	break;
	case 'delete_section':
		$Index->save((bool)$Blogs->del_section($_POST['id']));
	break;
}