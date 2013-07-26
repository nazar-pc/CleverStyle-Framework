<?php
/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'System/Config/pre_routing_replace',
	function () {
		switch (Config::instance()->components['modules']['HybridAuth']['active']) {
			case 1:
				require __DIR__.'/trigger/enabled.php';
		}
	}
);
Trigger::instance()->register(
	'System/Index/construct',
	function () {
		if (!ADMIN) {
			return;
		}
		switch (Config::instance()->components['modules']['HybridAuth']['active']) {
			case -1:
				require __DIR__.'/trigger/uninstalled.php';
			break;
		}
	}
);