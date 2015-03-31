<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	cs\Event;
Event::instance()
	->on('admin/System/components/modules/uninstall/process', function ($data) {
		if ($data['name'] == 'Composer') {
			$dir = DIR.'/storage/Composer';
			if (!is_dir($dir)) {
				return;
			}
			get_files_list(
				$dir,
				false,
				'fd',
				true,
				true,
				false,
				false,
				true,
				function ($item) {
					if (is_dir($item)) {
						@rmdir($item);
					} else {
						@unlink($item);
					}
				}
			);
			@rmdir($dir);
		}
	});
