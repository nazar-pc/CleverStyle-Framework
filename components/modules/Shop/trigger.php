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
	cs\Trigger,
	cs\Language\Prefix;

Trigger::instance()
	->register(
		'System/Config/routing_replace',
		function ($data) {
			$rc = explode('/', $data['rc']);
			$L  = new Prefix('shop_');
			if ($rc[0] != 'Shop' && $rc[0] != path($L->shop)) {
				return;
			}
			$rc[0] = 'Shop';
			if (!isset($rc[1])) {
				$rc[1] = 'categories_';
			}
			switch ($rc[1]) {
				case path($L->categories):
					$rc[1] = 'categories_';
					break;
			}
			$data['rc'] = implode('/', $rc);
		}
	)
	->register(
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
