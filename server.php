<?php
/**
 * @package    CleverSyle CMS
 * @subpackage CleverStyle CMS Server
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
/**
 * Before usage run:
 * $ git clone git@github.com:reactphp/http.git
 * $ cd http
 * $ composer install
 *
 * Start server with:
 * $ php-cgi server.php
 */
use
	cs\server\Request;
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
define('MICROTIME', microtime(true)); //Time in seconds (float)
define('TIME', floor(MICROTIME));     //Time in seconds (integer)
define('DIR', __DIR__);               //Root directory
chdir(DIR);

require_once __DIR__.'/server/custom_loader.php';
require_once __DIR__.'/http/vendor/autoload.php';
$loop   = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http   = new React\Http\Server($socket);
// TODO: only first request handled currently, need more work in core
$http->on('request', function (\React\Http\Request $request, \React\Http\Response $response) {
	$request->on('data', function ($data) use ($request, $response) {
		Request::handle($data, $request, $response);
	});
});

$socket->listen(9998);
$loop->run();
