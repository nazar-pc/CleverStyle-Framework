<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Core;
$Core->register_trigger(
	'System/Config/routing_replace',
	function ($data) {
		global $Config;
		if (!$Config->module('OAuth2')->active() && substr($data['rc'], 0, 5) != 'admin') {
			return;
		}
		$rc		= explode('/', $data['rc']);
		if (isset($rc[0]) && $rc[0] == 'OAuth2') {
			if (isset($rc[1])) {
				$rc[1]	= explode('?', $rc[1], 2)[0];
			}
			$data['rc']	= implode('/', $rc);
			header('Cache-Control: no-store');
    		header('Pragma: no-cache');
		}
	}
);
$Core->register_trigger(
	'System/Index/construct',
	function () {
		global $Config;
		switch ($Config->components['modules']['OAuth2']['active']) {
			case 1:
				require __DIR__.'/trigger/enabled.php';
		}
	}
);