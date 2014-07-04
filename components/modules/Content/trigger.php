<?php
/**
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */

namespace	cs;

Trigger::instance()->register(
	'System/Index/construct',
	function () {
		switch (Config::instance()->module('Content')->active()) {
			case 1:
				require __DIR__.'/trigger/enabled.php';
			default:
				if (!admin_path()) {
					return;
				}
				require __DIR__.'/trigger/installed.php';
		}
	}
);
