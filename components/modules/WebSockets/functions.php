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
		'127.0.0.1',
		$Config->module('WebSockets')->listen_port,
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
 * Just check whether is is possible to call `exec()`
 *
 * @return bool
 */
function is_exec_available () {
	return
		function_exists('exec') &&
		strtolower(ini_get('safe_mode')) != 'on' &&
		!in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))));
}
/**
 * Running WebSockets server in background on any platform
 */
function cross_platform_server_in_background () {
	$supervisor = 'php '.__DIR__.'/supervisor.php';
	$cmd        = 'php '.__DIR__.'/start_cli.php '.Config::instance()->base_url().'/WebSockets';
	if (substr(PHP_OS, 0, 3) != 'WIN') {
		exec("$supervisor '$cmd' > /dev/null &");
	} else {
		pclose(popen("start /B $supervisor '$cmd'", 'r'));
	}
}
