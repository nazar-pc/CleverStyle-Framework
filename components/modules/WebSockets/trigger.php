<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['WebSockets']['active']) {
			case 1:
				require __DIR__.'/Pawl/vendor/autoload.php';
				require __DIR__.'/functions.php';
				return;
			case -1:
				if (!admin_path()) {
					return;
				}
				require __DIR__.'/trigger/uninstalled.php';
		}
	}
);
