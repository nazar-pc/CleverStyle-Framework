<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
Event::instance()->on(
	'System/Index/construct',
	function () {
		$Config = Config::instance();
		if (!isset($Config->components['modules']['WebSockets'])) {
			return;
		}
		switch ($Config->components['modules']['WebSockets']['active']) {
			case 1:
				require_once __DIR__.'/functions.php';
				require __DIR__.'/events/enabled.php';
				return;
			case -1:
				if (!admin_path()) {
					return;
				}
				require __DIR__.'/events/uninstalled.php';
		}
	}
);
