<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Core, $Config;
$Core->register_trigger(
	'admin/System/components/modules/uninstall/process',
	function ($data) use ($Core) {
		global $User, $Cache;
		$module		= basename(__DIR__);
		if ($data['name'] != $module || !$User->is('admin')) {
			return true;
		}
		time_limit_pause();
		include_once MODULES.'/'.$module.'/class.php';
		$Blogs		= $Core->create('cs\\modules\\Blogs\\Blogs');
		$structure	= $Blogs->get_sections_structure();
		while (!empty($structure['sections'])) {
			foreach ($structure['sections'] as $section) {
				$Blogs->del_section($section['id']);
			}
			$structure	= $Blogs->get_sections_structure();
		}
		unset($section);
		if (!empty($structure['pages'])) {
			foreach ($structure['pages'] as $page) {
				$Blogs->del($page);
			}
		}
		unset(
			$page,
			$structure,
			$Cache->$module
		);
		time_limit_pause(false);
		return true;
	}
);
$Core->register_trigger(
	'System/Index/mainmenu',
	function ($data) {
		global $L;
		$module	= basename(__DIR__);
		if ($data['module'] == $module) {
			$data['module']	= path($L->$module);
		}
	}
);
$Core->register_trigger(
	'System/Config/routing_replace',
	function ($data) {
		global $L;
		$module	= basename(__DIR__);
		$rc		= explode('/', $data['rc']);
		if ($rc[0] == $L->$module) {
			$rc[0]		= $module;
			$data['rc']	= implode('/', $rc);
		}
	}
);