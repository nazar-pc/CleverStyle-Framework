<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
use
	cs\modules\Http_server\Request;

/**
 * Time of start of execution, is used as current time
 */
define('MICROTIME', microtime(true));         //Time in seconds (float)
define('TIME', floor(MICROTIME));             //Time in seconds (integer)
define('DIR', realpath(__DIR__.'/../../..')); //Root directory
chdir(DIR);
for ($i = 1; isset($argv[$i]); ++$i) {
	switch ($argv[$i]) {
		case '-p':
			$port = $argv[++$i];
			break;
	}
}
require_once __DIR__.'/custom_loader.php';
$loop   = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http   = new React\Http\Server($socket);

$http->on(
	'request',
	function (\React\Http\Request $request, \React\Http\Response $response) {
		$request->on(
			'data',
			function ($data) use ($request, $response) {
				Request::process($request, $response, microtime(true), $data);
			}
		);
	}
);
if (!isset($port)) {
	echo 'Http server for CleverStyle CMS
Usage: php components/modules/Http_server/run_server.php -p <port>
  -p - Is used to specify on which port server should listen for incoming connections
';
	return;
}
$socket->listen($port);
$loop->run();
