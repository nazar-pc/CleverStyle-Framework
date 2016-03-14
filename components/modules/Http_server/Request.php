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
	cs\ExitException,
	cs\Language,
	cs\Index,
	cs\Page,
	cs\Request as System_request,
	cs\Response as System_response,
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
	 *
	 * @throws ExitException
	 */
	function __invoke ($data) {
		$request = $this->request;
		$this->fill_superglobals(
			$this->prepare_superglobals($request, $data)
		);
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
		$Response = System_response::instance();
		$this->response->writeHead($Response->code, $Response->headers);
		if (is_resource($Response->body_stream)) {
			$position = ftell($Response->body_stream);
			rewind($Response->body_stream);
			while (!feof($Response->body_stream)) {
				$this->response->write(fread($Response->body_stream, 1024));
			}
			fseek($Response->body_stream, $position);
		} else {
			$this->response->end($Response->body);
		}
		$this->cleanup();
		$request->close();
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
	 *
	 * @throws ExitException
	 */
	protected function fill_superglobals ($SUPERGLOBALS) {
		// Hack: Filling $_SERVER is primarily needed for HybridAuth (many hard dependencies on `$_SERVER`)
		$_SERVER  = new _SERVER($SUPERGLOBALS['SERVER']);
		$_COOKIE  = $SUPERGLOBALS['COOKIE'];
		$_GET     = $SUPERGLOBALS['GET'];
		$_POST    = $SUPERGLOBALS['POST'];
		$_REQUEST = $SUPERGLOBALS['POST'] + $SUPERGLOBALS['GET'];
		// TODO: Move this initialization separately
		System_request::instance()->init_from_globals();
		System_response::instance()->init(
			'',
			null,
			[
				'Content-Type' => 'text/html; charset=utf-8',
				'Vary'         => 'Accept-Language,User-Agent,Cookie'
			],
			200,
			$_SERVER['SERVER_PROTOCOL']
		);
	}
	/**
	 * @throws ExitException
	 */
	protected function execute_request () {
		try {
			$L            = Language::instance(true);
			$url_language = $L->url_language();
			if ($url_language) {
				$L->change($url_language);
			}
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
	}
}
