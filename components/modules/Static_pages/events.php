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
	cs\Config,
	cs\Event;
Event::instance()
	->on(
		'System/Route/routing_replace',
		function ($data) {
			if (
				substr($data['rc'], 0, 5) != 'admin' &&
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
		'System/Index/construct',
		function () {
			if (Config::instance()->module('Static_pages')->installed()) {
				require __DIR__.'/events/installed.php';
			}
		}
	);
