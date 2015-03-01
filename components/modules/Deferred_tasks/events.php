<?php
/**
 * @package		Deferred tasks
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
Event::instance()->on(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['Deferred_tasks']['active']) {
			case -1:
				if (!admin_path()) {
					return;
				}
				require __DIR__.'/events/uninstalled.php';
		}
	}
);
