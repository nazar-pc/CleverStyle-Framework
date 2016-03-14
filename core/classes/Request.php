<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	cs\Request\Cookie,
	cs\Request\Data,
	cs\Request\Files,
	cs\Request\Platform,
	cs\Request\Query,
	cs\Request\Server;

class Request {
	use
		Singleton,
		Cookie,
		Data,
		Files,
		Platform,
		Query,
		Server;

	/**
	 * Initialize request object with specified data
	 *
	 * @param string[]             $server      Typically `$_SERVER`
	 * @param array                $query       Typically `$_GET`
	 * @param array                $data        Typically `$_POST`
	 * @param null|resource|string $data_stream String, like `php://input` or resource, like `fopen('php://input', 'br')`
	 * @param string[]             $cookie      Typically `$_COOKIE`
	 * @param array                $files       Typically `$_FILES`; might be like native PHP array `$_FILES` or normalized; each file item MUST contain keys
	 *                                          `name`,
	 *                                          `type`, `size`, `error` and at least one of `tmp_name` or `stream`
	 */
	function init ($server, $query, $data, $data_stream, $cookie, $files) {
		$this->init_server($server);
		$this->init_query($query);
		$this->init_data($data, $data_stream);
		$this->init_cookie($cookie);
		$this->init_files($files);
		$this->init_platform();
	}
	/**
	 * Initialize request object from superglobals `$_SERVER`, `$_GET`, `$_POST`, `$_COOKIE` and `$_FILES` (including parsing `php://input` in case of custom
	 * request methods)
	 */
	function init_from_globals () {
		// Hack: we override out `$_SERVER`, so conversion from iterator to an array is needed
		$this->init_server(iterator_to_array($_SERVER));
		$this->init_query($_GET);
		$this->init_data($this->init_from_globals_get_data(), 'php://input');
		$this->init_cookie($_COOKIE);
		// TODO: parse input stream for handling files when using request methods other than POST (complete multipart messages support needed actually)
		$this->init_files($_FILES);
		$this->init_platform();
	}
	/**
	 * Parse data from input stream if necessary (JSON, custom request methods)
	 *
	 * `$this->init_server()` assumed to be called already
	 *
	 * @return array
	 */
	protected function init_from_globals_get_data () {
		/**
		 * Support for JSON requests and/or request methods different than POST
		 */
		if (preg_match('#^application/([^+\s]+\+)?json#', $this->content_type)) {
			return _json_decode(@file_get_contents('php://input')) ?: [];
		} elseif (
			$this->method !== 'POST' &&
			strpos($this->content_type, 'application/x-www-form-urlencoded') === 0
		) {
			@parse_str(file_get_contents('php://input'), $result);
			return $result;
		}
		return $_POST;
	}
}
