<?php
/**
 * @package   Photo gallery
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Photo_gallery;

use
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Storage;

Event::instance()
	->on(
		'System/Request/routing_replace/after',
		function ($data) {
			if (!Config::instance()->module('Photo_gallery')->enabled()) {
				return;
			}
			if ($data['current_module'] != 'Photo_gallery' || !$data['route'] || !$data['regular_path']) {
				return;
			}
			$route = &$data['route'];
			if (
				isset($route[0]) &&
				!isset($route[1]) &&
				!in_array(@$route[0], ['gallery', 'edit_image'])
			) {
				$route[1]      = $route[0];
				$route[0]      = 'gallery';
				$Photo_gallery = Photo_gallery::instance();
				$galleries     = $Photo_gallery->get_galleries_list();
				if (!isset($galleries[$route[1]])) {
					throw new ExitException(404);
				}
				$route[1] = $galleries[$route[1]];
			}
		}
	)
	->on(
		'admin/System/modules/uninstall/before',
		function ($data) {
			if ($data['name'] != 'Photo_gallery') {
				return;
			}
			$module_data   = Config::instance()->module('Photo_gallery');
			$storage       = Storage::instance()->storage($module_data->storage('files'));
			$Photo_gallery = Photo_gallery::instance();
			foreach ($Photo_gallery->get_galleries_list() ?: [] as $gallery) {
				$Photo_gallery->del_gallery($gallery);
			}
			unset($gallery);
			if ($storage->is_dir('Photo_gallery')) {
				$storage->rmdir('Photo_gallery');
			}
		}
	);
