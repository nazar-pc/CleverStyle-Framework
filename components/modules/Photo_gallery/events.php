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
	cs\Language;

Event::instance()
	->on(
		'System/Route/routing_replace',
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
		'System/Index/construct',
		function () {
			if (Config::instance()->module('Photo_gallery')->installed()) {
				require __DIR__.'/events/installed.php';
			}
		}
	);
