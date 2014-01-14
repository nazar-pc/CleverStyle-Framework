<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			cs\Cache,
			cs\User,
			cs\Trigger;
Trigger::instance()->register(
	'admin/System/components/modules/uninstall/process',
	function ($data) {
		if ($data['name'] != 'Static_pages' || !User::instance()->admin()) {
			return true;
		}
		time_limit_pause();
		$Static_pages	= Static_pages::instance();
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
			Cache::instance()->Static_pages
		);
		time_limit_pause(false);
		return true;
	}
);
