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
global $Index, $Static_pages;
switch ($_POST['mode']) {
	case 'add_category':
		$Index->save($Static_pages->add_category($_POST['parent'], $_POST['title'], $_POST['path']));
	break;
}