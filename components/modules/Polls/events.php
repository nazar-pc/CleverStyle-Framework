<?php
/**
 * @package        Polls
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;

Event::instance()->on(
	'System/Index/construct',
	function () {
		$Config = Config::instance();
		if (!isset($Config->components['modules']['Polls'])) {
			return;
		}
		switch ($Config->components['modules']['Polls']['active']) {
			case 0:
			case 1:
				if (!admin_path()) {
					return;
				}
				require __DIR__.'/events/installed.php';
		}
	}
);
