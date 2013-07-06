<?php
/**
 * @package		Plupload
 * @category	modules
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU GPL v2, see license.txt
 */
global $Core;
$Core->register_trigger(
	'System/Config/pre_routing_replace',
	function () {
		global $Config;
		switch ($Config->components['modules']['Plupload']['active']) {
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
		switch ($Config->components['modules']['Plupload']['active']) {
			case -1:
				require __DIR__.'/trigger/uninstalled.php';
			break;
			case 0:
			case 1:
				require __DIR__.'/trigger/installed.php';
		}
	}
);