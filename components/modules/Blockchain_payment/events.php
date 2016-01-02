<?php
/**
 * @package   Blockchain payment
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blockchain_payment;
use
	cs\Config,
	cs\Event;

Event::instance()->on(
	'System/Index/construct',
	function () {
		$module_data = Config::instance()->module('Blockchain_payment');
		switch (true) {
			case $module_data->uninstalled():
				require __DIR__.'/events/uninstalled.php';
				break;
			case $module_data->enabled():
				require __DIR__.'/events/enabled.php';
				require __DIR__.'/events/enabled/admin.php';
		}
	}
);
