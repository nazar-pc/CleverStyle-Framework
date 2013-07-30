<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'System/Config/routing_replace',
	function ($data) {
		if (!Config::instance()->module('Photo_gallery')->active() && substr($data['rc'], 0, 5) != 'admin') {
			return;
		}
		$rc		= explode('/', $data['rc']);
		if ($rc[0] == path(Language::instance()->Photo_gallery) || $rc[0] == 'Photo_gallery') {
			$rc[0]		= 'Photo_gallery';
			$data['rc']	= implode('/', $rc);
		}
	}
)->register(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['Photo_gallery']['active']) {
			case 1:
				require __DIR__.'/trigger/enabled.php';
			case 0:
				if (!ADMIN) {
					return;
				}
				require __DIR__.'/trigger/installed.php';
		}
	}
);