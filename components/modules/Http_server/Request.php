<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Http_server;
use
	cs\_SERVER,
	cs\Config,
	cs\Language,
	cs\Index,
	cs\Page,
	cs\User;
class Request {
	public $__request_id;
	/**
	 * @var \React\Http\Request
	 */
	protected $request;
	/**
	 * @var \React\Http\Response
	 */
	protected $response;
	/**
	 * @param \React\Http\Request  $request
	 * @param \React\Http\Response $response
	 */
	function __construct ($request, $response) {
		$this->request      = $request;
		$this->response     = $response;
		$this->__request_id = ASYNC_HTTP_SERVER ? md5(openssl_random_pseudo_bytes(100)) : 1;
	}
	/**
	 * @param string $data
	 */
	function __invoke ($data) {
		$this->bootstrap();
		$request    = $this->request;
		$request_id = $this->__request_id;
		$SERVER     = [
			'SERVER_SOFTWARE' => 'ReactPHP'
		];
		foreach ($request->getHeaders() as $key => $value) {
			if ($key == 'Content-Type') {
				$SERVER['CONTENT_TYPE'] = $value;
			} elseif ($key == 'Cookie') {
				$value  = _trim(explode(';', $value));
				$value  = array_map(function ($cookie) {
					return explode('=', $cookie);
				}, $value);
				$COOKIE = array_column($value, 1, 0);
			} else {
				$SERVER['HTTP_'.strtoupper(strtr($key, '-', '_'))] = $value;
			}
		}
		$SERVER['REQUEST_METHOD']  = $request->getMethod();
		$SERVER['REQUEST_URI']     = $request->getPath();
		$SERVER['QUERY_STRING']    = http_build_query($request->getQuery());
		$SERVER['REMOTE_ADDR']     = http_build_query($request->remoteAddress);
		$GET[$request_id]          = $request->getQuery();
		$SERVER['SERVER_PROTOCOL'] = 'HTTP/'.$request->getHttpVersion();
		if (isset($SERVER['CONTENT_TYPE'])) {
			if (strpos($SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') === 0) {
				parse_str($data, $POST);
			} elseif (preg_match('#^application/([^+\s]+\+)?json#', $SERVER['CONTENT_TYPE'])) {
				$POST[$request_id] = json_decode($data, true);
			}
		}
		ob_start();
		$COOKIE = isset($COOKIE) ? $COOKIE : [];
		$POST   = isset($POST) ? $POST : [];
		if (ASYNC_HTTP_SERVER) {
			$_SERVER[$request_id]  = new _SERVER($SERVER);
			$_COOKIE[$request_id]  = $COOKIE;
			$_GET[$request_id]     = $GET;
			$_POST[$request_id]    = $POST;
			$_REQUEST[$request_id] = $POST + $GET;
		} else {
			$_SERVER  = new _SERVER($SERVER);
			$_COOKIE  = $COOKIE;
			$_GET     = $GET;
			$_POST    = $POST;
			$_REQUEST = $POST + $GET;
		}
		try {
			try {
				if (!ASYNC_HTTP_SERVER) {
					Config::instance(true)->reinit();
				}
				Language::instance();
				Index::instance();
			} catch (\ExitException $e) {
			}
			try {
				Index::instance(true)->__finish();
				Page::instance()->__finish();
				User::instance(true)->__finish();
			} catch (\ExitException $e) {
			}
		} catch (\Exception $e) {
		}
		$response = $this->response;
		$response->writeHead(_http_response_code(0, $request_id), _header(null));
		$response->end(ob_get_clean());
		$this->cleanup();
		$request->close();
	}
	/**
	 * Various preparations before processing of current request
	 */
	protected function bootstrap () {
		_header('Content-Type: text/html; charset=utf-8');
		_header('Vary: Content-Language,User-Agent,Cookie');
		_header('Connection: close');
	}
	/**
	 * Various cleanups after processing of current request to free used memory
	 */
	function cleanup () {
		$request_id = $this->__request_id;
		/**
		 * Clean objects pool
		 */
		objects_pool($request_id, []);
		if (ASYNC_HTTP_SERVER) {
			unset(
				$_COOKIE[$request_id],
				$_SERVER[$request_id],
				$_GET[$request_id],
				$_POST[$request_id],
				$_REQUEST[$request_id]
			);
		}
		error_code(-1);
		admin_path(-1);
		api_path(-1);
		current_module(-1);
		home_page(-1);
	}
}
