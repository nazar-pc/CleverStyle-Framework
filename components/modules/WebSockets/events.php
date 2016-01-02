<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
Event::instance()->on(
	'System/Index/construct',
	function () {
		$module_data = Config::instance()->module('WebSockets');
		switch (true) {
			case $module_data->enabled():
				require_once __DIR__.'/functions.php';
				require __DIR__.'/events/enabled.php';
				return;
			case $module_data->uninstalled():
				require __DIR__.'/events/uninstalled.php';
		}
	}
);
