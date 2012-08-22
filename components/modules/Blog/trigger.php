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
		$structure		= $Blog->get_structure();
		while (!empty($structure['categories'])) {
			foreach ($structure['categories'] as $category) {
				$Blog->del_category($category['id']);
			}
			$structure	= $Blog->get_structure();
		}
		unset($category);
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
if (!(
	isset($Config->components['modules'][basename(__DIR__)]) &&
	$Config->components['modules'][basename(__DIR__)]['active'] == 1
)) {
	return;
}
$Core->register_trigger(
	'System/Config/routing_replace',
	function ($data) {
		if (empty($data['rc'])) {
			return;
		}
		$rc						= explode('/', $data['rc']);
		$module					= basename(__DIR__);
		switch ($rc[0]) {
			case 'admin':
			case 'api':
				return;
			case $module:
				$rc = ['index'];
		}
		global $Core, $Blog;
		include_once MODULES.'/'.$module.'/class.php';
		$Core->create('cs\\modules\\Blog\\Blog');
		$structure				= $Blog->get_structure();
		$categories				= array_slice($rc, 0, -1);
		$Blog->title	= [];
		if (!empty($categories)) {
			foreach ($categories as $category) {
				if (isset($structure['categories'][$category])) {
					$structure				= $structure['categories'][$category];
					$path[]					= $structure['path'];
					$Blog->title[]	= $structure['title'];
				}
			}
			unset($category);
		}
		unset($categories);
		if (isset($structure['pages'][array_slice($rc, -1)[0]])) {
			$data['rc']	= $module.'/'.$structure['pages'][array_slice($rc, -1)[0]];
		}
	}
);