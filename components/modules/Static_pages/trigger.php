<?php
/**
 * @package        Static Pages
 * @category       modules
 * @version        0.001
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
global $Core, $Config;
$Core->register_trigger(
	'admin/System/components/modules/uninstall/process',
	function ($data) {
		time_limit_pause();
		if ($data['name'] == basename(__DIR__)) {
			global $Cache;
			unset($Cache->{basename(__DIR__)});
		}
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
		global $Core, $Static_pages;
		include_once MODULES.'/'.$module.'/class.php';
		$Core->create('cs\\modules\\Static_pages\\Static_pages');
		$structure				= $Static_pages->get_structure();
		$categories				= array_slice($rc, 0, -1);
		$Static_pages->title	= [];
		if (!empty($categories)) {
			foreach ($categories as $category) {
				if (isset($structure['categories'][$category])) {
					$structure				= $structure['categories'][$category];
					$path[]					= $structure['path'];
					$Static_pages->title[]	= $structure['title'];
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