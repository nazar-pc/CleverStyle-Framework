<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\server;
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
		$this->__request_id = md5(openssl_random_pseudo_bytes(100));
	}
	/**
	 * @param string $data
	 */
	function __invoke ($data) {
		$this->bootstrap();
		$request = $this->request;
		$SERVER  = [];
		foreach ($request->getHeaders() as $key => $value) {
			if ($key == 'Content-Type') {
				$SERVER['CONTENT_TYPE'] = $value;
			} elseif ($key == 'Cookie') {
				$value                        = _trim(explode(';', $value));
				$value                        = array_map(function ($cookie) {
					return explode('=', $cookie);
				}, $value);
				$_COOKIE[$this->__request_id] = array_column($value, 1, 0);
			} else {
				$SERVER['HTTP_'.strtoupper(strtr($key, '-', '_'))] = $value;
			}
		}
		$SERVER['REQUEST_METHOD']  = $request->getMethod();
		$SERVER['REQUEST_URI']     = $request->getPath();
		$SERVER['QUERY_STRING']    = http_build_query($request->getQuery());
		$_GET[$this->__request_id] = $request->getQuery();
		$SERVER['SERVER_PROTOCOL'] = 'HTTP/'.$request->getHttpVersion();
		switch (explode(';', @$SERVER['CONTENT_TYPE'])[0]) {
			case 'application/json':
				$_POST[$this->__request_id] = json_decode($data, true);
				break;
			default:
				parse_str($data, $POST);
				$_POST[$this->__request_id] = $POST;
				unset($POST);
		}
		ob_start();
		$_SERVER[$this->__request_id] = new _SERVER($SERVER);
		try {
			Config::instance(true)->reinit();
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
		$response = $this->response;
		$response->writeHead(_http_response_code(), _header(null));
		$response->end(ob_get_clean());
		$this->cleanup();
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
		/**
		 * Clean objects pool
		 */
		objects_pool($this->__request_id, []);
		unset(
			$_COOKIE[$this->__request_id],
			$_SERVER[$this->__request_id],
			$_GET[$this->__request_id],
			$_POST[$this->__request_id],
			$_REQUEST[$this->__request_id]
		);
		admin_path(-1);
		api_path(-1);
		current_module(-1);
		home_page(-1);
	}
}
