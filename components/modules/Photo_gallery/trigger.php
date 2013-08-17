<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['Photo_gallery']['active']) {
			case 1:
			case 0:
				if (!ADMIN) {
					return;
				}
				require __DIR__.'/trigger/installed.php';
		}
	}
);