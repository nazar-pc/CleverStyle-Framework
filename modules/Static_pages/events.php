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
			$structure      = Pages::instance()->get_map();
			$route_imploded = implode('/', $route);
			if (isset($structure[$route_imploded])) {
				$data['current_module'] = 'Static_pages';
				$route                  = [$structure[$route_imploded]];
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
			foreach ($Categories->get_all() as $category) {
				foreach ($Pages->get_for_category($category['id']) as $page) {
					$Pages->del($page);
				}
				if ($category['id']) {
					$Categories->del($category['id']);
				}
			}
			unset(Cache::instance()->Static_pages);
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
