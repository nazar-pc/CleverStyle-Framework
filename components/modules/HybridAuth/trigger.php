<?php
/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
global $Core;
$Core->register_trigger(
	'System/Config/pre_routing_replace',
	function () {
		global $Config;
		switch ($Config->components['modules']['HybridAuth']['active']) {
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
		switch ($Config->components['modules']['HybridAuth']['active']) {
			case -1:
				require __DIR__.'/trigger/uninstalled.php';
			break;
			case 0:
			case 1:
				require __DIR__.'/trigger/installed.php';
		}
	}
);