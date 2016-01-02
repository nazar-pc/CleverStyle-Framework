<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	cs\Event;
Event::instance()->on(
	'admin/System/components/modules/uninstall/after',
	function ($data) {
		if ($data['name'] == 'Composer') {
			$dir = DIR.'/storage/Composer';
			if (!rmdir_recursive($dir)) {
				trigger_error("Composer's directory $dir was not removed completely", E_USER_WARNING);
			}
		}
	}
);
