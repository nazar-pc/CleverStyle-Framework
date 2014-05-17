<?php
/**
 * @package        OAuth2
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;

Trigger::instance()->register(
	'System/Config/routing_replace',
	function ($data) {
		$Config = Config::instance();
		if ($Config->module('OAuth2')->active() && substr($data['rc'], 0, 5) != 'admin') {
			$rc = explode('/', $data['rc']);
			if (isset($rc[0]) && $rc[0] == 'OAuth2') {
				if (isset($rc[1])) {
					$rc[1] = explode('?', $rc[1], 2)[0];
				}
				$data['rc'] = implode('/', $rc);
				header('Cache-Control: no-store');
				header('Pragma: no-cache');
			}
		}
		switch ($Config->components['modules']['OAuth2']['active']) {
			case -1:
				if (!ADMIN) {
					return;
				}
				require __DIR__.'/trigger/uninstalled.php';
				break;
			case 1:
				require __DIR__.'/trigger/enabled.php';
		}
	}
);
