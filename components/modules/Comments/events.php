<?php
/**
 * @package   Comments
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
Event::instance()->on(
	'System/Index/construct',
	function () {
		$module_data = Config::instance()->module('Comments');
		switch (true) {
			case $module_data->enabled():
				require __DIR__.'/events/enabled.php';
			case $module_data->installed():
				require __DIR__.'/events/installed.php';
		}
	}
);
