<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
if (!isset($_POST['mode'])) {
	return;
}
global $Config, $Page, $Index, $User, $L;
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'add':
			$Index->save(
				(bool)$User->add_group($_POST['group']['title'], $_POST['group']['description'])
			);
		break;
		case 'edit':
			$Index->save(
				$User->set_group($_POST['group'], $_POST['group']['id'])
			);
		break;
		case 'delete':
			$id = (int)$_POST['id'];
			if ($id != 1 && $id != 2 && $id != 3) {
				$Index->save(
					$User->del_group($_POST['id'])
				);
			}
		break;
		case 'permissions':
			$Index->save(
				$User->set_group_permissions($_POST['permission'], $_POST['id'])
			);
		break;
	}
}