<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
use
	cs\Language\Prefix;

Event::instance()->on(
	'admin/System/Menu',
	function () {
		$Config    = Config::instance();
		$L         = new Prefix('system_admin_');
		$Menu      = Menu::instance();
		$structure = $Config->core['simple_admin_mode'] ? file_get_json(__DIR__.'/../admin/index_simple.json') : file_get_json(__DIR__.'/../admin/index.json');
		$route     = Request::instance()->route_path;
		foreach ($structure as $section => $items) {
			$Menu->add_section_item(
				'System',
				$L->$section,
				[
					'href'    => "admin/System/$section",
					'primary' => $route[0] == $section
				]
			);
			foreach ($items as $item) {
				$Menu->add_item(
					'System',
					$L->$item,
					[
						'href'    => "admin/System/$section/$item",
						'primary' => $route[0] == $section && $route[1] == $item
					]
				);
			}
		}
	}
);
