<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

if (!isset($_POST['mode'])) {
	return;
}
global $Index, $OAuth2;
$draft	= false;
switch ($_POST['mode']) {
	case 'add':
		$Index->save($OAuth2->add_client($_POST['name'], $_POST['domain'], $_POST['active']));
	break;
	case 'edit':
		$Index->save($OAuth2->set_client($_POST['id'], $_POST['secret'], $_POST['name'], $_POST['domain'], $_POST['active']));
	break;
	case 'delete':
		$Index->save($OAuth2->del_client($_POST['id']));
}