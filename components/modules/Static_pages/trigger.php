<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Core;
$Core->register_trigger(
	'System/Config/pre_routing_replace',
	function () {
		global $Config;
		switch ($Config->components['modules']['Static_pages']['active']) {
			case 1:
				require __DIR__.'/trigger/enabled.php';
		}
	}
);
$Core->register_trigger(
	'System/Index/construct',
	function () {
		if (!ADMIN) {
			return;
		}
		global $Config;
		switch ($Config->components['modules']['Blogs']['active']) {
			case 0:
			case 1:
				require __DIR__.'/trigger/installed.php';
		}
	}
);