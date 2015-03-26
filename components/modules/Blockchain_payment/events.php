<?php
/**
 * @package   Blockchain payment
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blockchain_payment;
use
	cs\Config,
	cs\Event;

Event::instance()->on(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['Blockchain_payment']['active']) {
			case -1:
				if (!admin_path()) {
					return;
				}
				require __DIR__.'/events/uninstalled.php';
				break;
			case 1:
				require __DIR__.'/events/enabled.php';
				if (admin_path()) {
					require __DIR__.'/events/enabled/admin.php';
				}
		}
	}
);
