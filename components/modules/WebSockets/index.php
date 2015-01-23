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
require __DIR__.'/Ratchet/vendor/autoload.php';
if (PHP_SAPI == 'cli') {
	Server::instance()->run();
} else {
	interface_off();
	// TODO: security check here
	$Config = Config::instance();
	// Try to connect to socket if exists
	$socket = @fsockopen(
		explode('/', $Config->base_url())[2],
		$Config->module('WebSockets')->{$_SERVER->secure ? 'external_port_secure' : 'external_port'},
		$error,
		$error,
		2
	);
	if ($socket) {
		fclose($socket);
		echo 'Server already running';
		return;
	}
	/**
	 * Check whether `exec()` is available (to run server from CLI completely in background)
	 */
	if (
		function_exists('exec') &&
		strtolower(ini_get('safe_mode')) != 'on' &&
		!in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))))
	) {
		$cmd = 'php '.__DIR__.'/prepare_cli.php '.$Config->base_url().'/WebSockets';
		if (substr(PHP_OS, 0, 3) != 'WIN') {
			exec("$cmd > /dev/null &");
		} else {
			pclose(popen("start /B $cmd", 'r'));
		}
		echo 'Server started';
	} else {
		ignore_user_abort(1);
		Server::instance()->run();
	}
}
