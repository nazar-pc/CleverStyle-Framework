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
require_once __DIR__.'/http/vendor/autoload.php';
$loop   = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http   = new React\Http\Server($socket);
// TODO: only first request handled currently, need more work in core
$http->on('request', function (\React\Http\Request $request, \React\Http\Response $response) {
	$request->on('data', function ($data) use ($request, $response) {
		$_SERVER = [];
		// TODO: Parse cookie header
		foreach ($request->getHeaders() as $key => $value) {
			if ($key == 'Content-Type') {
				$_SERVER['CONTENT_TYPE'] = $value;
			} else {
				$_SERVER['HTTP_'.strtoupper(strtr($key, '-', '_'))] = $value;
			}
		}
		$_SERVER['REQUEST_METHOD']  = $request->getMethod();
		$_SERVER['REQUEST_URI']     = $request->getPath();
		$_SERVER['QUERY_STRING']    = http_build_query($request->getQuery());
		$_GET                       = $request->getQuery();
		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/'.$request->getHttpVersion();
		switch (explode(';', @$_SERVER['HTTP_'])) {
			case 'application/json':
				$_POST = json_decode($data, true);
				break;
			default:
				parse_str($data, $_POST);
		}
		ob_start();
		require_once __DIR__.'/index.php';
		shutdown_function();
		$headers = [];
		array_map(function ($header) use (&$headers) {
			$header              = explode(':', $header, 2);
			$headers[$header[0]] = ltrim($header[1]);
		}, headers_list());
		header_remove();
		$response->writeHead(200, $headers);
		$response->end(ob_get_clean());
	});
});

$socket->listen(9998);
$loop->run();
