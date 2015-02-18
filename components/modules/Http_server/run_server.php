<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
use
	cs\modules\Http_server\Request;
/**
 * This is custom loader that includes basic files and defines constants,
 * but do not call any class to leave that all for test cases, and unregisters shutdown function
 */
if (version_compare(PHP_VERSION, '5.4', '<')) {
	exit('CleverStyle CMS require PHP 5.4 or higher');
}
/**
 * Time of start of execution, is used as current time
 */
define('MICROTIME', microtime(true));         //Time in seconds (float)
define('TIME', floor(MICROTIME));             //Time in seconds (integer)
define('DIR', realpath(__DIR__.'/../../..')); //Root directory
chdir(DIR);
$async = false;
for ($i = 1; isset($argv[$i]); ++$i) {
	switch ($argv[$i]) {
		case '-p':
			$port = $argv[++$i];
			break;
		case '-a':
			$async = true;
	}
}
/**
 * Whether server should be ready for asynchronous processing (is not - more optimizations might be applied)
 */
define('ASYNC_HTTP_SERVER', $async);
unset($async);
require_once __DIR__.'/custom_loader.php';
require_once __DIR__.'/vendor/autoload.php';
$loop   = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http   = new React\Http\Server($socket);

$http->on('request', function (\React\Http\Request $request, \React\Http\Response $response) {
	$request->on(
		'data',
		new Request($request, $response)
	);
});
if (!isset($port)) {
	exit(
'Http server for CleverStyle CMS
Usage: php components/modules/Http_server/run_server.php -p <port> [-a]
  -p - Is used to specify on which port server should listen for incoming connections
  -a - Prepare server for asynchronous processing (decrease system optimizations, but might
       be useful if other code will benefit from this), using asynchronous code without this
       option will result in unpredictable behavior
'
	);
}
$socket->listen($port);
$loop->run();
