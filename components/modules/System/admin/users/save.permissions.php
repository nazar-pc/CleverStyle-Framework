<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
if (!isset($_POST['mode'])) {
	return;
}
$Index		= Index::instance();
$Permission	= Permission::instance();
switch ($_POST['mode']) {
	case 'add':
		$Index->save(
			(bool)$Permission->add($_POST['permission']['group'], $_POST['permission']['label'])
		);
	break;
	case 'edit':
		$Index->save(
			$Permission->set($_POST['permission']['id'], $_POST['permission']['group'], $_POST['permission']['label'])
		);
	break;
	case 'delete':
		$Index->save(
			$Permission->del($_POST['id'])
		);
	break;
}
