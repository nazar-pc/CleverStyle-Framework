<?php
/**
 * @package    Shop
 * @attribute  modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Config,
	cs\Language\Prefix,
	cs\Trigger;

Trigger::instance()
	->register(
		'System/payment/success',
		function ($data) {
			if ($data['module'] != 'Shop') {
				return;
			}
			$purpose = explode('/', $data['purpose']);
			if ($purpose[0] != 'orders') {
				return;
			}
			$Config  = Config::instance();
			Orders::instance()->set_status(
				$purpose[1],
				$Config->module('Shop')->default_paid_order_status,
				'' // TODO: possibly some comment message here
			);
			$L       = new Prefix('shop_');
			interface_off();
			header('Location: '.$Config->core_url().'/'.path($L->shop).'/'.path($L->orders).'/?paid='.(int)$purpose[1]); // TODO: handle successful payment in interface
		}
	)
	->register(
		'System/payment/error',
		function ($data) {
			if ($data['module'] != 'Shop') {
				return;
			}
			$purpose = explode('/', $data['purpose']);
			if ($purpose[0] != 'orders') {
				return;
			}
			$Config  = Config::instance();
			$L       = new Prefix('shop_');
			interface_off();
			header('Location: '.$Config->core_url().'/'.path($L->shop).'/'.path($L->orders).'/?error='.(int)$purpose[1]); // TODO: handle failed payment in interface
		}
	);
