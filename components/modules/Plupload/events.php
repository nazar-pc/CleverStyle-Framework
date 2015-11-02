<?php
/**
 * @package   Plupload
 * @category  modules
 * @author    Moxiecode Systems AB
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright Moxiecode Systems AB
 * @license   GNU GPL v2, see license.txt
 */
namespace cs;
Event::instance()
	->on(
		'System/Config/init/after',
		function () {
			if (Config::instance()->module('Plupload')->enabled()) {
				require __DIR__.'/events/enabled.php';
			}
		}
	)
	->on(
		'System/Index/construct',
		function () {
			$module_data = Config::instance()->module('Plupload');
			switch (true) {
				case $module_data->uninstalled():
					require __DIR__.'/events/uninstalled.php';
					break;
				case $module_data->installed():
					require __DIR__.'/events/installed.php';
			}
		}
	);
