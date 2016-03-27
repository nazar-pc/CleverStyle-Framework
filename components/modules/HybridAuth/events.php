<?php
/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
Event::instance()->on(
	'System/User/construct/after',
	function () {
		if (Config::instance()->module('HybridAuth')->enabled()) {
			require __DIR__.'/events/enabled.php';
			require_once __DIR__.'/events/enabled/functions.php';
		}
	}
);
Event::instance()->on(
	'System/App/construct',
	function () {
		if (Config::instance()->module('HybridAuth')->uninstalled()) {
			require __DIR__.'/events/uninstalled.php';
		}
	}
);
