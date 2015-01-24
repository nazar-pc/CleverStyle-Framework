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
	cs\Config;
if (PHP_SAPI == 'cli') {
	Server::instance()->run();
} else {
	interface_off();
	$Config      = Config::instance();
	$module_data = $Config->module('WebSockets');
	$rc          = $Config->route;
	if ($module_data->security_key !== $rc[0]) {
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
		ignore_user_abort(1);
		Server::instance()->run();
	}
}
