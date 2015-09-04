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
	cs\ExitException,
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
		$this->__request_id = ASYNC_HTTP_SERVER ? md5(random_bytes(100)) : 1;
	}
	/**
	 * @param string $data
	 */
	function __invoke ($data) {
		$this->bootstrap();
		$request    = $this->request;
		$request_id = $this->__request_id;
		$this->fill_superglobals(
			$this->prepare_superglobals($request, $data, $request_id),
			$request_id
		);
		ob_start();
		try {
			try {
				Config::instance(true)->reinit();
				Language::instance();
				Index::instance();
			} catch (ExitException $e) {
			}
			try {
				Index::instance(true)->__finish();
				Page::instance()->__finish();
				User::instance(true)->__finish();
			} catch (ExitException $e) {
			}
		} catch (\Exception $e) {
		}
		$this->response->writeHead(_http_response_code(0, $request_id), _header(null));
		$this->response->end(ob_get_clean());
		$this->cleanup();
		$request->close();
	}
	/**
	 * @param \React\HTTP\Request $request
	 * @param string              $data
	 * @param int|string          $request_id
	 *
	 * @return array
	 */
	protected function prepare_superglobals ($request, $data, $request_id) {
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
				$POST[$request_id] = json_decode($data, true);
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
	 * @param array      $SUPERGLOBALS
	 * @param int|string $request_id
	 */
	protected function fill_superglobals ($SUPERGLOBALS, $request_id) {
		if (ASYNC_HTTP_SERVER) {
			$_SERVER[$request_id]  = new _SERVER($SUPERGLOBALS['SERVER']);
			$_COOKIE[$request_id]  = $SUPERGLOBALS['COOKIE'];
			$_GET[$request_id]     = $SUPERGLOBALS['GET'];
			$_POST[$request_id]    = $SUPERGLOBALS['POST'];
			$_REQUEST[$request_id] = $SUPERGLOBALS['POST'] + $SUPERGLOBALS['GET'];
		} else {
			$_SERVER = new _SERVER($SUPERGLOBALS['SERVER']);
			$_COOKIE  = $SUPERGLOBALS['COOKIE'];
			$_GET     = $SUPERGLOBALS['GET'];
			$_POST    = $SUPERGLOBALS['POST'];
			$_REQUEST = $SUPERGLOBALS['POST'] + $SUPERGLOBALS['GET'];
		}
	}
	/**
	 * Various preparations before processing of current request
	 */
	protected function bootstrap () {
		_header('Content-Type: text/html; charset=utf-8');
		_header('Vary: Accept-Language,User-Agent,Cookie');
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
