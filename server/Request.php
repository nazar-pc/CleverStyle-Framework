<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\server;
class Request {
	/**
	 * @param string               $data
	 * @param \React\Http\Request  $request
	 * @param \React\Http\Response $response
	 */
	static function handle ($data, $request, $response) {
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
		// TODO: run request processing here
		$headers = [];
		array_map(function ($header) use (&$headers) {
			$header              = explode(':', $header, 2);
			$headers[$header[0]] = ltrim($header[1]);
		}, headers_list());
		header_remove();
		$response->writeHead(200, $headers);
		$response->end(ob_get_clean());
	}
}
