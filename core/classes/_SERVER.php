<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	ArrayAccess,
	Iterator;

/**
 * Generic wrapper for `$_SERVER` to make its usage easier and more secure
 *
 * @deprecated Use `cs\Request` instead, it provides very similar, but more powerful interface
 * @todo       Remove in 4.x
 *
 * @property string $language       Language accepted by client, `''` by default
 * @property string $version        Version accepted by client, will match `/^[0-9\.]+$/`, useful for API, `1` by default
 * @property string $content_type   Content type, `''` by default
 * @property bool   $dnt            Do not track
 * @property string $host           The best guessed host
 * @property string $ip             The best guessed IP of client (based on all known headers), `$this->remote_addr` by default
 * @property string $protocol       Protocol `http` or `https`
 * @property string $query_string   Query string
 * @property string $referer        HTTP referer, `''` by default
 * @property string $remote_addr    Where request came from, not necessary real IP of client
 * @property string $request_uri    Request uri
 * @property string $request_method Request method
 * @property bool   $secure         Is requested with HTTPS
 * @property string $user_agent     User agent
 */
class _SERVER implements ArrayAccess, Iterator {
	protected $_SERVER;

	function __construct (array $SERVER) {
		$this->_SERVER = $SERVER;
	}
	function __get ($key) {
		$Request = Request::instance();
		switch ($key) {
			case 'secure':
			case 'host':
			case 'ip':
			case 'query_string':
			case 'remote_addr':
				return $Request->$key;
			case 'content_type':
			case 'dnt':
			case 'referer':
			case 'user_agent':
				return $Request->header($key);
			case 'language':
			case 'version':
				return $Request->header("accept-$key");
			case 'request_method':
				return $Request->method;
			case 'request_uri':
				return $this->_SERVER['REQUEST_URI'];
			case 'protocol':
				return $Request->scheme;
			default:
				return false;
		}
	}
	/**
	 * Whether key exists (from original `$_SERVER` superglobal)
	 *
	 * @param string $index
	 *
	 * @return bool
	 */
	function offsetExists ($index) {
		return isset($this->_SERVER[$index]);
	}
	/**
	 * Get key (from original `$_SERVER` superglobal)
	 *
	 * @param string $index
	 *
	 * @return mixed
	 */
	function offsetGet ($index) {
		return $this->_SERVER[$index];
	}
	/**
	 * Set key (from original `$_SERVER` superglobal)
	 *
	 * @param string $index
	 * @param mixed  $value
	 */
	function offsetSet ($index, $value) {
		$this->_SERVER[$index] = $value;
	}
	/**
	 * Unset key (from original `$_SERVER` superglobal)
	 *
	 * @param string $index
	 */
	public function offsetUnset ($index) {
		unset($this->_SERVER[$index]);
	}
	/**
	 * Get current (from original `$_SERVER` superglobal)
	 *
	 * @return mixed Can return any type.
	 */
	public function current () {
		return current($this->_SERVER);
	}
	/**
	 * Move forward to next element (from original `$_SERVER` superglobal)
	 */
	public function next () {
		next($this->_SERVER);
	}
	/**
	 * Return the key of the current element (from original `$_SERVER` superglobal)
	 *
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key () {
		return key($this->_SERVER);
	}
	/**
	 * Checks if current position is valid (from original `$_SERVER` superglobal)
	 *
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid () {
		return $this->key() !== null;
	}
	/**
	 * Rewind the Iterator to the first element (from original `$_SERVER` superglobal)
	 */
	public function rewind () {
		reset($this->_SERVER);
	}
}
