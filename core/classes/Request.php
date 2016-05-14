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
	cs\Request\Data_and_files,
	cs\Request\Query,
	cs\Request\Route,
	cs\Request\Psr7,
	cs\Request\Server;

/**
 * @method static $this instance($check = false)
 */
class Request {
	use
		Singleton,
		Cookie,
		Data_and_files,
		Query,
		Psr7,
		Route,
		Server;
	/**
	 * Global request id, used by system
	 *
	 * @var int
	 */
	public static $id = 0;
	/**
	 * Unix timestamp when request processing started
	 *
	 * @var float
	 */
	public $started;
	/**
	 * Initialize request object with specified data
	 *
	 * @param string[]             $server          Typically `$_SERVER`
	 * @param array                $query           Typically `$_GET`
	 * @param array                $data            Typically `$_POST`
	 * @param array[]              $files           Typically `$_FILES`; might be like native PHP array `$_FILES` or normalized; each file item MUST contain
	 *                                              keys `name`, `type`, `size`, `error` and at least one of `tmp_name` or `stream`
	 * @param null|resource|string $data_stream     String, like `php://input` or resource, like `fopen('php://input', 'rb')`
	 * @param string[]             $cookie          Typically `$_COOKIE`
	 * @param float                $request_started Unix timestamp when request processing started
	 *
	 * @throws ExitException
	 */
	function init ($server, $query, $data, $files, $data_stream, $cookie, $request_started) {
		++static::$id;
		$this->init_server($server);
		$this->init_query($query);
		$this->init_data_and_files($data, $files, $data_stream);
		$this->init_cookie($cookie);
		$this->init_route();
		$this->started = $request_started;
	}
	/**
	 * Initialize request object from superglobals `$_SERVER`, `$_GET`, `$_POST`, `$_COOKIE` and `$_FILES` (including parsing `php://input` when necessary)
	 *
	 * @throws ExitException
	 */
	function init_from_globals () {
		$this->init($_SERVER, $_GET, $_POST, $_FILES, 'php://input', $_COOKIE, MICROTIME);
	}
}
