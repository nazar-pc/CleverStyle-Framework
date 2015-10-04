<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Photo_gallery;
use
	cs\Config,
	cs\Event,
	cs\Storage;
Event::instance()->on(
	'admin/System/components/modules/uninstall/before',
	function ($data) {
		if ($data['name'] != 'Photo_gallery') {
			return;
		}
		$module_data	= Config::instance()->module('Photo_gallery');
		$storage		= Storage::instance()->{$module_data->storage('files')};
		$Photo_gallery	= Photo_gallery::instance();
		foreach ($Photo_gallery->get_galleries_list() as $gallery) {
			$Photo_gallery->del_gallery($gallery);
		}
		unset($gallery);
		if ($storage->is_dir('Photo_gallery')) {
			$storage->rmdir('Photo_gallery');
		}
	}
);
