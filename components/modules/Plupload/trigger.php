<?php
/**
 * @package		Plupload
 * @category	modules
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU GPL v2, see license.txt
 */
namespace	cs;
Trigger::instance()
	->register(
		'System/Config/pre_routing_replace',
		function () {
			switch (Config::instance()->components['modules']['Plupload']['active']) {
				case 1:
					require __DIR__.'/trigger/enabled.php';
			}
		}
	)
	->register(
		'System/Index/construct',
		function () {
			if (!ADMIN) {
				return;
			}
			switch (Config::instance()->components['modules']['Plupload']['active']) {
				case -1:
					require __DIR__.'/trigger/uninstalled.php';
				break;
				case 0:
				case 1:
					require __DIR__.'/trigger/installed.php';
			}
		}
	);
