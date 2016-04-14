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
	cs\Event;

Event::instance()
	->on(
		'System/Request/routing_replace',
		function ($data) {
			if (
				strpos($data['rc'], 'admin') !== 0 &&
				!Config::instance()->module('Static_pages')->enabled()
			) {
				return;
			}
			$rc = explode('/', $data['rc']);
			switch ($rc[0]) {
				case 'admin':
				case 'api':
					return;
				case 'Static_pages':
					if (!isset($rc[1])) {
						$rc = ['index'];
					}
			}
			$structure  = Pages::instance()->get_structure();
			$categories = array_slice($rc, 0, -1);
			if (!empty($categories)) {
				foreach ($categories as $category) {
					if (isset($structure['categories'][$category])) {
						$structure = $structure['categories'][$category];
					}
				}
			}
			unset($categories);
			if (isset($structure['pages'][array_slice($rc, -1)[0]])) {
				$data['rc'] = 'Static_pages/'.$structure['pages'][array_slice($rc, -1)[0]];
			}
		}
	)
	->on(
		'admin/System/components/modules/uninstall/before',
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
	);
