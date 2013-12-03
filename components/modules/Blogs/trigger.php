<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['Blogs']['active']) {
			case -1:
				if (!ADMIN) {
					return;
				}
				require __DIR__.'/trigger/uninstalled.php';
			break;
			case 1:
				require __DIR__.'/trigger/enabled.php';
			default:
				if (!ADMIN) {
					return;
				}
				require __DIR__.'/trigger/installed.php';
		}
	}
);