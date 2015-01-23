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
/**
 * @return bool
 */
function is_server_running () {
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
		return true;
	}
	return false;
}

/**
 * @return bool
 */
function is_exec_available () {
	return
		function_exists('exec') &&
		strtolower(ini_get('safe_mode')) != 'on' &&
		!in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))));
}

function cross_platform_server_in_background () {
	$cmd = 'php '.__DIR__.'/prepare_cli.php '.Config::instance()->base_url().'/WebSockets';
	if (substr(PHP_OS, 0, 3) != 'WIN') {
		exec("$cmd > /dev/null &");
	} else {
		pclose(popen("start /B $cmd", 'r'));
	}
}
