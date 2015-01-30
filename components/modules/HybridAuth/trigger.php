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
Event::instance()->on(
	'System/Config/pre_routing_replace',
	function () {
		switch (Config::instance()->components['modules']['HybridAuth']['active']) {
			case 1:
				require __DIR__.'/events/enabled.php';
		}
	}
);
Event::instance()->on(
	'System/Index/construct',
	function () {
		if (!admin_path()) {
			return;
		}
		switch (Config::instance()->components['modules']['HybridAuth']['active']) {
			case -1:
				require __DIR__.'/events/uninstalled.php';
			break;
		}
	}
);
