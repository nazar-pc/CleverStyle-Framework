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
	cs\_SERVER,
	cs\Config,
	cs\ExitException,
	cs\Language,
	cs\Index,
	cs\Page,
	cs\User;

class Request {
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
		$this->request  = $request;
		$this->response = $response;
	}
	/**
	 * @param string $data
	 */
	function __invoke ($data) {
		$this->bootstrap();
		$request = $this->request;
		$this->fill_superglobals(
			$this->prepare_superglobals($request, $data)
		);
		ob_start();
		try {
			try {
				$this->execute_request();
			} catch (ExitException $e) {
				if ($e->getCode() >= 400) {
					Page::instance()->error($e->getMessage() ?: null, $e->getJson(), $e->getCode());
				}
			}
		} catch (\Exception $e) {
			// Handle generic exceptions to avoid server from stopping
		}
		$this->response->writeHead(_http_response_code(0), _header(null));
		$this->response->end(ob_get_clean());
		$this->cleanup();
		$request->close();
	}
	/**
	 * Various preparations before processing of current request
	 */
	protected function bootstrap () {
		_header('Content-Type: text/html; charset=utf-8');
		_header('Vary: Accept-Language,User-Agent,Cookie');
	}
	/**
	 * @param \React\HTTP\Request $request
	 * @param string              $data
	 *
	 * @return array
	 */
	protected function prepare_superglobals ($request, $data) {
		$SERVER = [
			'SERVER_SOFTWARE' => 'ReactPHP'
		];
		$COOKIE = [];
		$POST   = [];
		foreach ($request->getHeaders() as $key => $value) {
			if ($key == 'Content-Type') {
				$SERVER['CONTENT_TYPE'] = $value;
			} elseif ($key == 'Cookie') {
				$value = _trim(explode(';', $value));
				foreach ($value as $c) {
					$c             = explode('=', $c);
					$COOKIE[$c[0]] = $c[1];
				}
				unset($c);
			} else {
				$key                 = strtoupper(str_replace('-', '_', $key));
				$SERVER["HTTP_$key"] = $value;
			}
		}
		$SERVER['REQUEST_METHOD']  = $request->getMethod();
		$SERVER['REQUEST_URI']     = $request->getPath();
		$SERVER['QUERY_STRING']    = http_build_query($request->getQuery());
		$SERVER['REMOTE_ADDR']     = $request->remoteAddress;
		$GET                       = $request->getQuery();
		$SERVER['SERVER_PROTOCOL'] = 'HTTP/'.$request->getHttpVersion();
		if (isset($SERVER['CONTENT_TYPE'])) {
			if (strpos($SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') === 0) {
				parse_str($data, $POST);
			} elseif (preg_match('#^application/([^+\s]+\+)?json#', $SERVER['CONTENT_TYPE'])) {
				$POST = json_decode($data, true);
			}
		}
		return [
			'SERVER' => $SERVER,
			'COOKIE' => $COOKIE,
			'GET'    => $GET,
			'POST'   => $POST
		];
	}
	/**
	 * @param array $SUPERGLOBALS
	 */
	protected function fill_superglobals ($SUPERGLOBALS) {
		$_SERVER  = new _SERVER($SUPERGLOBALS['SERVER']);
		$_COOKIE  = $SUPERGLOBALS['COOKIE'];
		$_GET     = $SUPERGLOBALS['GET'];
		$_POST    = $SUPERGLOBALS['POST'];
		$_REQUEST = $SUPERGLOBALS['POST'] + $SUPERGLOBALS['GET'];
	}
	/**
	 * @throws ExitException
	 */
	protected function execute_request () {
		try {
			/**
			 * @var \cs\custom\Config $Config
			 */
			$Config = Config::instance(true);
			$Config->reinit();
			Language::instance();
			Index::instance();
		} catch (ExitException $e) {
			if ($e->getCode()) {
				throw $e;
			}
		}
		try {
			Index::instance(true)->__finish();
			Page::instance()->__finish();
			User::instance(true)->__finish();
		} catch (ExitException $e) {
			if ($e->getCode()) {
				throw $e;
			}
		}
	}
	/**
	 * Various cleanups after processing of current request to free used memory
	 */
	function cleanup () {
		/**
		 * Clean objects pool
		 */
		objects_pool([]);
		admin_path(false);
		api_path(false);
		current_module('');
		home_page(false);
	}
}
