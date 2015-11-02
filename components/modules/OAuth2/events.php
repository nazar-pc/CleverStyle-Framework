<?php
/**
 * @package        OAuth2
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;

Event::instance()
	->on(
		'System/Route/routing_replace',
		function ($data) {
			if (Config::instance()->module('OAuth2')->enabled() && substr($data['rc'], 0, 5) != 'admin') {
				$rc = explode('/', $data['rc'], 2);
				if (isset($rc[0]) && $rc[0] == 'OAuth2') {
					if (isset($rc[1])) {
						$rc[1] = explode('?', $rc[1], 2)[0];
					}
					$data['rc'] = implode('/', $rc);
					_header('Cache-Control: no-store');
					_header('Pragma: no-cache');
				}
				$POST = (array)$_POST;
				Event::instance()->on(
					'System/User/construct/after',
					function () use ($POST) {
						foreach ($POST as $i => $v) {
							$_POST[$i] = $v;
						}
					}
				);
			}
		}
	)
	->on(
		'System/Index/construct',
		function () {
			$module_data = Config::instance()->module('OAuth2');
			switch (true) {
				case $module_data->uninstalled():
					require __DIR__.'/events/uninstalled.php';
					break;
				case $module_data->enabled():
					require __DIR__.'/events/enabled.php';
			}
		}
	);
