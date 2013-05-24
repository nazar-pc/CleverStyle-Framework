<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Core;
$Core->register_trigger(
	'admin/System/components/modules/uninstall/process',
	function ($data) use ($Core) {
		global $User, $Cache, $Static_pages;
		$module			= basename(__DIR__);
		if ($data['name'] != $module || !$User->admin()) {
			return true;
		}
		time_limit_pause();
		$structure		= $Static_pages->get_structure();
		while (!empty($structure['categories'])) {
			foreach ($structure['categories'] as $category) {
				$Static_pages->del_category($category['id']);
			}
			$structure	= $Static_pages->get_structure();
		}
		unset($category);
		if (!empty($structure['pages'])) {
			foreach ($structure['pages'] as $page) {
				$Static_pages->del($page);
			}
			unset($page);
		}
		unset(
			$structure,
			$Cache->$module
		);
		time_limit_pause(false);
		return true;
	}
);
$Core->register_trigger(
	'System/Config/routing_replace',
	function ($data) use ($Core) {
		global $Config;
		$module					= basename(__DIR__);
		if (!$Config->module($module)->active() && substr($data['rc'], 0, 5) != 'admin') {
			return;
		}
		$rc						= explode('/', $data['rc']);
		global $Static_pages;
		$Core->create('cs\\modules\\Static_pages\\Static_pages');
		switch ($rc[0]) {
			case 'admin':
			case 'api':
				return;
			case $module:
				$rc = ['index'];
		}
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