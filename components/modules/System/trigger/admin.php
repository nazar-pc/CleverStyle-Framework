<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;
Trigger::instance()->register(
	'admin/System/Menu',
	function () {
		$Config		= Config::instance();
		$L			= Language::instance();
		$Menu		= Menu::instance();
		$structure	= $Config->core['simple_admin_mode'] ? file_get_json(__DIR__.'/../admin/index_simple.json') :  file_get_json(__DIR__.'/../admin/index.json');
		$route		= Index::instance()->route_path;
		foreach ($structure as $section => $items) {
			$Menu->add_section_item(
				'System',
				$L->$section,
				"admin/System/$section",
				[
					'class'	=> $route[0] == $section ? 'uk-active' : false
				]
			);
			foreach ($items as $item) {
				$Menu->add_item(
					'System',
					$L->$item,
					"admin/System/$section/$item",
					[
						'class'	=> $route[0] == $section && $route[1] == $item ? 'uk-active' : false
					]
				);
			}
		}
	}
);
