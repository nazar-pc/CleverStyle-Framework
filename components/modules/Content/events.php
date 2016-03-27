<?php
/**
 * @package   Content
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

namespace	cs;

Event::instance()->on(
	'System/App/construct',
	function () {
		$module_data = Config::instance()->module('Content');
		switch (true) {
			case $module_data->enabled():
				require __DIR__.'/events/enabled.php';
			case $module_data->installed():
				require __DIR__.'/events/installed.php';
		}
	}
);
