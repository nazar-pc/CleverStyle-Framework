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
	case 'add_section':
		$Index->save((bool)$Blog->add_section($_POST['parent'], $_POST['title'], $_POST['path']));
	break;
	case 'edit_section':
		$Index->save((bool)$Blog->set_section($_POST['id'], $_POST['parent'], $_POST['title'], $_POST['path']));
	break;
	case 'delete_section':
		$Index->save((bool)$Blog->del_section($_POST['id']));
	break;
}