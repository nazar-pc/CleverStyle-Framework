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
global $Index, $User, $Cache;
switch ($_POST['mode']) {
	case 'add':
		$Index->save(
			(bool)$User->add_permission($_POST['permission']['group'], $_POST['permission']['label'])
		);
	break;
	case 'edit':
		$Index->save(
			$User->set_permission($_POST['permission']['id'], $_POST['permission']['group'], $_POST['permission']['label'])
		);
	break;
	case 'delete':
		$Index->save(
			$User->del_permission($_POST['id'])
		);
	break;
}