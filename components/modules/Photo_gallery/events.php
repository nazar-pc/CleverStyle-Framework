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
	cs\Language,
	cs\Storage;

Event::instance()
	->on(
		'System/Request/routing_replace',
		function ($data) {
			$rc = explode('/', $data['rc']);
			$L  = Language::instance();
			if ($rc[0] != 'Photo_gallery' && $rc[0] != path($L->Photo_gallery)) {
				return;
			}
			$rc[0] = 'Photo_gallery';
			if (
				isset($rc[1]) &&
				!isset($rc[2]) &&
				!in_array($rc[1], ['gallery', 'edit_image'])
			) {
				$rc[2]         = $rc[1];
				$rc[1]         = 'gallery';
				$Photo_gallery = Photo_gallery::instance();
				$galleries     = $Photo_gallery->get_galleries_list();
				if (!isset($galleries[$rc[2]])) {
					throw new ExitException(404);
				}
				$rc[2] = $galleries[$rc[2]];
			}
			$data['rc'] = implode('/', $rc);
		}
	)
	->on(
		'admin/System/components/modules/uninstall/before',
		function ($data) {
			if ($data['name'] != 'Photo_gallery') {
				return;
			}
			$module_data   = Config::instance()->module('Photo_gallery');
			$storage       = Storage::instance()->{$module_data->storage('files')};
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
