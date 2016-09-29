<?php
/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Static_pages;
use
	cs\Cache,
	cs\Config,
	cs\Event,
	cs\Language\Prefix,
	cs\Menu,
	cs\Request;

Event::instance()
	->on(
		'System/Request/routing_replace/after',
		function ($data) {
			if (!Config::instance()->module('Static_pages')->enabled()) {
				return;
			}
			if (!$data['regular_path']) {
				return;
			}
			$route = &$data['route'];
			if ($data['current_module'] == 'Static_pages' && !isset($route[0])) {
				$route = ['index'];
			}
			if (!$route) {
				return;
			}
			$structure  = Pages::instance()->get_structure();
			$categories = array_slice($route, 0, -1);
			if (!$categories) {
				foreach ($categories as $category) {
					if (isset($structure['categories'][$category])) {
						$structure = $structure['categories'][$category];
					}
				}
			}
			$page = array_slice($route, -1)[0];
			if (isset($structure['pages'][$page])) {
				$data['current_module'] = 'Static_pages';
				$route                  = [$structure['pages'][$page]];
			}
		}
	)
	->on(
		'admin/System/modules/uninstall/before',
		function ($data) {
			if ($data['name'] != 'Static_pages') {
				return true;
			}
			time_limit_pause();
			$Pages      = Pages::instance();
			$Categories = Categories::instance();
			$structure  = $Pages->get_structure();
			while (!empty($structure['categories'])) {
				foreach ($structure['categories'] as $category) {
					$Categories->del($category['id']);
				}
				$structure = $Pages->get_structure();
			}
			unset($category);
			if (!empty($structure['pages'])) {
				foreach ($structure['pages'] as $page) {
					$Pages->del($page);
				}
			}
			unset(
				$structure,
				Cache::instance()->Static_pages
			);
			time_limit_pause(false);
			return true;
		}
	)
	->on(
		'admin/System/Menu',
		function () {
			$L = new Prefix('static_pages_');
			Menu::instance()->add_item(
				'Static_pages',
				$L->browse_categories,
				[
					'href'    => 'admin/Static_pages/browse_categories',
					'primary' => Request::instance()->route_path(0) == 'browse_categories'
				]
			);
		}
	);
