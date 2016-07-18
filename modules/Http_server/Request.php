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
	cs\App,
	cs\ExitException,
	cs\Language,
	cs\Page,
	cs\Request as System_request,
	cs\Response as System_response,
	cs\User;

class Request {
	/**
	 * @param \React\Http\Request  $request
	 * @param \React\Http\Response $response
	 * @param float                $request_started
	 * @param string               $data
	 */
	static function process ($request, $response, $request_started, $data) {
		static::fill_superglobals(
			static::prepare_superglobals($request, $data)
		);
		static::execute($request_started);
		$Response = System_response::instance();
		/**
		 * When error happens in \cs\Request initialization, there might be no headers yet since \cs\Response was not initialized
		 */
		$response->writeHead($Response->code, $Response->headers ?: []);
		if ($Response->code >= 300 && $Response->code < 400) {
			$response->end();
		} elseif (is_resource($Response->body_stream)) {
			$position = ftell($Response->body_stream);
			rewind($Response->body_stream);
			while (!feof($Response->body_stream)) {
				$response->write(fread($Response->body_stream, 1024));
			}
			fseek($Response->body_stream, $position);
		} else {
			$response->end($Response->body);
		}
		$request->close();
		User::instance()->disable_memory_cache();
	}
	/**
	 * @param float $request_started
	 */
	protected static function execute ($request_started) {
		try {
			try {
				static::execute_request($request_started);
			} catch (ExitException $e) {
				if ($e->getCode() >= 400) {
					Page::instance()->error($e->getMessage() ?: null, $e->getJson());
				}
			}
		} catch (\Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}
	}
	/**
	 * @param \React\HTTP\Request $request
	 * @param string              $data
	 *
	 * @return array
	 */
	protected static function prepare_superglobals ($request, $data) {
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
	protected static function fill_superglobals ($SUPERGLOBALS) {
		// Hack: Filling $_SERVER is primarily needed for HybridAuth (many hard dependencies on `$_SERVER`)
		$_SERVER  = $SUPERGLOBALS['SERVER'];
		$_COOKIE  = $SUPERGLOBALS['COOKIE'];
		$_GET     = $SUPERGLOBALS['GET'];
		$_POST    = is_array($SUPERGLOBALS['POST']) ? $SUPERGLOBALS['POST'] : [];
		$_REQUEST = $_POST + $_GET;
	}
	/**
	 * @param float $request_started
	 *
	 * @throws ExitException
	 */
	protected static function execute_request ($request_started) {
		$Request = System_request::instance();
		$Request->init_from_globals();
		$Request->started = $request_started;
		System_response::instance()->init_with_typical_default_settings();
		$L            = Language::instance(true);
		$url_language = $L->url_language();
		if ($url_language) {
			$L->change($url_language);
		}
		App::instance()->execute();
	}
}
