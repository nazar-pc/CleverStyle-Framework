<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\WebSockets;
use
	cs\Config,
	cs\Route;
if (PHP_SAPI == 'cli') {
	Server::instance()->run(isset($_GET['address']) ? $_GET['address'] : null);
} else {
	interface_off();
	$Config      = Config::instance();
	$module_data = $Config->module('WebSockets');
	$rc          = Route::instance()->route;
	if ($module_data->security_key !== @$rc[0]) {
		error_code(400);
		return;
	}
	if (is_server_running()) {
		echo 'Server already running';
		return;
	}
	if (is_exec_available()) {
		cross_platform_server_in_background();
		echo 'Server started';
	} else {
		set_time_limit(0);
		ignore_user_abort(1);
		if (!isset($rc[1])) {
			// Supervising for the case if server will go down
			while (true) {
				if (is_server_running()) {
					sleep(10);
					continue;
				}
				file_get_contents(
					$Config->base_url().'/WebSockets/'.$Config->module('WebSockets')->security_key.'/supervised',
					null,
					stream_context_create([
						'http' => [
							'timeout' => 0
						]
					])
				);
			}
		} else {
			Server::instance()->run();
		}
	}
}
