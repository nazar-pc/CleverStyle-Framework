<?php
/**
 * @package   Composer assets
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\plugins\Composer_assets;
use
	cs\Event;
Event::instance()
	->on(
		'admin/System/components/plugins/disable/process',
		function ($data) {
			if ($data['name'] === 'Composer_assets') {
				rmdir_recursive(PUBLIC_STORAGE.'/Composer_assets');
			}
		}
	);
