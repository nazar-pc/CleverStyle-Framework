<?php
/**
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */

namespace	cs;

Event::instance()->on(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['Content']['active']) {
			case 1:
				require __DIR__.'/events/enabled.php';
			default:
				if (!admin_path()) {
					return;
				}
				require __DIR__.'/events/installed.php';
		}
	}
);
