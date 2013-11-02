<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
if (!isset($_POST['mode'])) {
	return;
}
$Index	= Index::instance();
$Group	= Group::instance();
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'add':
			$Index->save(
				(bool)$Group->add($_POST['group']['title'], $_POST['group']['description'])
			);
		break;
		case 'edit':
			$Index->save(
				$Group->set($_POST['group'], $_POST['group']['id'])
			);
		break;
		case 'delete':
			$id = (int)$_POST['id'];
			if ($id != User::ADMIN_GROUP_ID && $id != User::USER_GROUP_ID && $id != User::BOT_GROUP_ID) {	//Three primary groups should not be deleted
				$Index->save(
					$Group->del($_POST['id'])
				);
			}
		break;
		case 'permissions':
			$Index->save(
				  $Group->set_permissions($_POST['permission'], $_POST['id'])
			);
		break;
	}
}