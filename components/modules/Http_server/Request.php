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
	cs\App,
	cs\ExitException,
	cs\Language,
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
	 * @var float
	 */
	protected $request_started;
	/**
	 * @param \React\Http\Request  $request
	 * @param \React\Http\Response $response
	 * @param float                $request_started
	 */
	function __construct ($request, $response, $request_started) {
		$this->request         = $request;
		$this->response        = $response;
		$this->request_started = $request_started;
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
					Page::instance()->error($e->getMessage() ?: null, $e->getJson());
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
		$request->close();
		User::instance()->disable_memory_cache();
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
	}
	/**
	 * @throws ExitException
	 */
	protected function execute_request () {
		$Request = System_request::instance();
		$Request->init_from_globals();
		$Request->started = $this->request_started;
		System_response::instance()->init_with_typical_default_settings();
		$L            = Language::instance(true);
		$url_language = $L->url_language();
		if ($url_language) {
			$L->change($url_language);
		}
		App::instance()->execute();
	}
}
