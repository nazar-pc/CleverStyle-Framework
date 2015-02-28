<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	cs\Event;
Event::instance()
	->on('admin/System/components/modules/install/prepare', function () {
		//
	})
	->on('admin/System/components/modules/uninstall/process', function () {
		//
	})
	->on('admin/System/components/plugins/enable/prepare', function () {
		//
	})
	->on('admin/System/components/plugins/disable/prepare', function () {
		//
	});
