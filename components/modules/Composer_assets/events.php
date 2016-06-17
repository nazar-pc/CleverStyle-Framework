<?php
/**
 * @package   Composer assets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer_assets;
use
	cs\Event;

Event::instance()->on(
	'admin/System/components/modules/uninstall/after',
	function ($data) {
		if ($data['name'] === 'Composer_assets') {
			rmdir_recursive(PUBLIC_STORAGE.'/Composer_assets');
		}
	}
);
