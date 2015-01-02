<?php
/**
 * @package		Comments
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['Comments']['active']) {
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
