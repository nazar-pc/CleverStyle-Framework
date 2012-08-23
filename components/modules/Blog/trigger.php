<?php
/**
 * @package        Blog
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
global $Core, $Config;
$Core->register_trigger(
	'admin/System/components/modules/uninstall/process',
	function ($data) {
		global $User, $Core, $Cache;
		$module			= basename(__DIR__);
		if ($data['name'] != $module || !$User->is('admin')) {
			return true;
		}
		time_limit_pause();
		include_once MODULES.'/'.$module.'/class.php';
		$Blog	= $Core->create('cs\\modules\\Blog\\Blog');
		$structure		= $Blog->get_sections_structure();
		while (!empty($structure['sections'])) {
			foreach ($structure['sections'] as $section) {
				$Blog->del_section($section['id']);
			}
			$structure	= $Blog->get_sections_structure();
		}
		unset($section);
		if (!empty($structure['pages'])) {
			foreach ($structure['pages'] as $page) {
				$Blog->del($page);
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