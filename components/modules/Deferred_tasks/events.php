<?php
/**
 * @package   Deferred tasks
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
Event::instance()->on(
	'System/Index/construct',
	function () {
		if (Config::instance()->module('Deferred_tasks')->uninstalled()) {
			require __DIR__.'/events/uninstalled.php';
		}
	}
);
