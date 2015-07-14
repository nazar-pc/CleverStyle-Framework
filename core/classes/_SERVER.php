<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	ArrayAccess,
	Iterator;

/**
 * Generic wrapper for `$_SERVER` to make its usage easier and more secure
 *
 * @property string $language        Language accepted by client, `''` by default
 * @property string $version         Version accepted by client, will match `/^[0-9\.]+$/`, useful for API, `1` by default
 * @property string $content_type    Content type, `''` by default
 * @property bool   $dnt             Do not track
 * @property string $host            The best guessed host
 * @property string $ip              The best guessed IP of client (based on all known headers), `$this->remote_addr` by default
 * @property string $protocol        Protocol `http` or `https`
 * @property string $query_string    Query string
 * @property string $referer         HTTP referer, `''` by default
 * @property string $remote_addr     Where request came from, not necessary real IP of client
 * @property string $request_uri     Request uri
 * @property string $request_method  Request method
 * @property bool   $secure          Is requested with HTTPS
 * @property string $user_agent      User agent
 */
class _SERVER implements ArrayAccess, Iterator {
	public    $language       = '';
	public    $version        = '';
	public    $content_type   = '';
	public    $dnt            = false;
	public    $host           = '';
	public    $ip             = '';
	public    $protocol       = '';
	public    $query_string   = '';
	public    $referer        = '';
	public    $remote_addr    = '';
	public    $request_method = '';
	public    $request_uri    = '';
	public    $secure         = false;
	public    $user_agent     = '';
	protected $_SERVER        = [];

	function __construct ($SERVER) {
		$this->_SERVER = $SERVER;
		$this->update($SERVER);
	}
	protected function update ($SERVER) {
		$this->language       = isset($SERVER['HTTP_ACCEPT_LANGUAGE']) ? $SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
		$this->version        =
			isset($SERVER['HTTP_ACCEPT_VERSION']) && preg_match('/^[0-9\.]+$/', $SERVER['HTTP_ACCEPT_VERSION']) ? $SERVER['HTTP_ACCEPT_VERSION'] : 1;
		$this->content_type   = isset($SERVER['CONTENT_TYPE']) ? $SERVER['CONTENT_TYPE'] : '';
		$this->dnt            = isset($SERVER['HTTP_DNT']) && $SERVER['HTTP_DNT'] == 1;
		$this->secure         = $this->secure($SERVER);
		$this->protocol       = $this->secure ? 'https' : 'http';
		$this->host           = $this->host($SERVER);
		$this->ip             = $this->ip($_SERVER);
		$this->query_string   = isset($SERVER['QUERY_STRING']) ? $SERVER['QUERY_STRING'] : '';
		$this->referer        = isset($SERVER['HTTP_REFERER']) && filter_var($SERVER['HTTP_REFERER'], FILTER_VALIDATE_URL) ? $SERVER['HTTP_REFERER'] : '';
		$this->remote_addr    = isset($SERVER['REMOTE_ADDR']) ? $SERVER['REMOTE_ADDR'] : '127.0.0.1';
		$this->request_uri    = isset($SERVER['REQUEST_URI']) ? $SERVER['REQUEST_URI'] : '';
		$this->request_method = isset($SERVER['REQUEST_METHOD']) ? $SERVER['REQUEST_METHOD'] : '';
		$this->user_agent     = isset($SERVER['HTTP_USER_AGENT']) ? $SERVER['HTTP_USER_AGENT'] : '';
	}
	/**
	 * The best guessed IP of client (based on all known headers), `$this->remote_addr` by default
	 *
	 * @param array $SERVER
	 *
	 * @return string
	 */
	protected function ip ($SERVER) {
		$all_possible_keys = [
			'HTTP_X_FORWARDED_FOR',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED'
		];
		foreach ($all_possible_keys as $key) {
			if (isset($SERVER[$key])) {
				$ip = trim(explode(',', $SERVER[$key])[0]);
				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
					return $ip;
				}
			}
		}
		return isset($SERVER['REMOTE_ADDR']) ? '127.0.0.1' : '';
	}
	/**
	 * The best guessed host
	 *
	 * @throws \ExitException
	 *
	 * @param array $SERVER
	 *
	 * @return string
	 */
	protected function host ($SERVER) {
		$host          = isset($SERVER['SERVER_NAME']) ? $SERVER['SERVER_NAME'] : '';
		$port          = '';
		$expected_port = $this->secure ? 443 : 80;
		if (!$host && isset($SERVER['HTTP_X_FORWARDED_HOST'])) {
			$host = $SERVER['HTTP_X_FORWARDED_HOST'];
			if (
				isset($SERVER['HTTP_X_FORWARDED_PORT']) &&
				$SERVER['HTTP_X_FORWARDED_PORT'] != $expected_port
			) {
				$port = (int)$SERVER['HTTP_X_FORWARDED_PORT'];
			}
		} elseif (isset($SERVER['HTTP_HOST'])) {
			/** @noinspection NotOptimalIfConditionsInspection */
			if (!$host || filter_var($host, FILTER_VALIDATE_IP)) {
				$host = $SERVER['HTTP_HOST'];
			} elseif (strpos($SERVER['HTTP_HOST'], ':') !== false) {
				$port = (int)explode(':', $SERVER['HTTP_HOST'])[1];
				if ($port == $expected_port) {
					$port = '';
				}
			}
		}
		if (preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host) !== '') {
			status_code(400);
			trigger_error("Invalid host", E_USER_ERROR);
			throw new \ExitException;
		}
		return $host.($port ? ":$port" : '');
	}
	/**
	 * Secure protocol detection
	 *
	 * @param array $SERVER
	 *
	 * @return bool
	 */
	protected function secure ($SERVER) {
		return isset($SERVER['HTTPS']) && $SERVER['HTTPS'] ? $SERVER['HTTPS'] !== 'off' : (
			isset($SERVER['HTTP_X_FORWARDED_PROTO']) && $SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
		);
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
		$this->update($this->_SERVER);
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
