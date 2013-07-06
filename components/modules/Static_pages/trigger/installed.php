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
		if ($data['name'] != 'Static_pages' || !$User->admin()) {
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
			$Cache->Static_pages
		);
		time_limit_pause(false);
		return true;
	}
);