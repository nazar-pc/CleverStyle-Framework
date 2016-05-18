<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Http_server;
use
	React;

require_once __DIR__.'/../../../core/bootstrap.php';

for ($i = 1; isset($argv[$i]); ++$i) {
	switch ($argv[$i]) {
		case '-p':
			$port = $argv[++$i];
			break;
	}
}

$loop   = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http   = new React\Http\Server($socket);

$http->on(
	'request',
	function (React\Http\Request $request, React\Http\Response $response) {
		$request->on(
			'data',
			function ($data) use ($request, $response) {
				Request::process($request, $response, microtime(true), $data);
			}
		);
	}
);
if (!isset($port)) {
	echo 'Http server for CleverStyle Framework
Usage: php components/modules/Http_server/run_server.php -p <port>
  -p - Is used to specify on which port server should listen for incoming connections
';
	return;
}
$socket->listen($port);
$loop->run();
