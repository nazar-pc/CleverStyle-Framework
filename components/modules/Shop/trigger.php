<?php
/**
 * @package    Shop
 * @attribute  modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Config,
	cs\Trigger;

Trigger::instance()->register(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['Shop']['active']) {
			case 1:
				if (admin_path()) {
					require __DIR__.'/trigger/enabled/admin.php';
				}
		}
	}
);
