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
	Ratchet\Client\Factory as Client_factory,
	Ratchet\Client\WebSocket as Client_websocket,
	React\EventLoop\Factory as Loop_factory,
	cs\Config;
/**
 * @return bool
 */
function is_server_running () {
	$connected = false;
	$servers   = Pool::instance()->get_all();
	if ($servers) {
		shuffle($servers);
		$loop      = Loop_factory::create();
		$connector = new Client_factory($loop);
		$connector($servers[0])->then(
			function (Client_websocket $connection) use ($loop, &$connected) {
				$connected = true;
				$connection->close();
				$loop->stop();
			},
			function () use ($loop) {
				$loop->stop();
			}
		);
		$loop->run();
	}
	return $connected;
}

/**
 * Just check whether is is possible to call `exec()`
 *
 * @return bool
 */
function is_exec_available () {
	return
		function_exists('exec') &&
		!in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))));
}

/**
 * Running WebSockets server in background on any platform
 */
function cross_platform_server_in_background () {
	$supervisor = 'php '.__DIR__.'/supervisor.php';
	$cmd        = 'php '.__DIR__.'/start_cli.php '.Config::instance()->core_url();
	if (substr(PHP_OS, 0, 3) != 'WIN') {
		exec("$supervisor '$cmd' > /dev/null &");
	} else {
		pclose(popen("start /B $supervisor '$cmd'", 'r'));
	}
}
