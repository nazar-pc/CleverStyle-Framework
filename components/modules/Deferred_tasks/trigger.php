<?php
/**
 * @package		Deferred tasks
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2013
 * @license		MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['Deferred_tasks']['active']) {
			case -1:
				if (!ADMIN) {
					return;
				}
				require __DIR__.'/trigger/uninstalled.php';
		}
	}
);