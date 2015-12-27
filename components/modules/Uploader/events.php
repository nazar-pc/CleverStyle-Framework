<?php
/**
 * @package   Uploader
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
Event::instance()
	->on(
		'System/Config/init/after',
		function () {
			if (Config::instance()->module('Uploader')->enabled()) {
				require __DIR__.'/events/enabled.php';
			}
		}
	)
	->on(
		'System/Index/construct',
		function () {
			$module_data = Config::instance()->module('Uploader');
			switch (true) {
				case $module_data->uninstalled():
					require __DIR__.'/events/uninstalled.php';
					break;
				case $module_data->installed():
					require __DIR__.'/events/installed.php';
			}
		}
	);
