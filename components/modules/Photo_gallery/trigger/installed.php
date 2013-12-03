<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2013
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Photo_gallery;
use			cs\Config,
			cs\Storage,
			cs\Trigger;
Trigger::instance()->register(
	'admin/System/components/modules/uninstall/process',
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