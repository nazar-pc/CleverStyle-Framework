<?php
/**
 * @package        CleverStyle CMS
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;
use
	ArrayIterator;

/**
 * Generic wrapper for `$_SERVER` to make its usage easier and more secure
 *
 * @property string $accept_language Language accepted by client, `''` by default
 * @property string $accept_version  Version accepted by client, will match `/^[0-9\.]+$/`, `1` by default
 * @property string $content_type    Content type, `''` by default
 * @property bool   $dnt             Do not track
 * @property string $host            The best guessed host
 * @property string $ip              The best guessed IP of client (based on all known headers), `$this->remote_addr` by default
 * @property string $query_string    Query string
 * @property string $referer         HTTP referer, `''` by default
 * @property string $remote_addr     Where request came from, not necessary real IP of client
 * @property string $request_uri     Request uri
 * @property string $request_method  Request method
 * @property bool   $secure          Is requested with HTTPS
 * @property string $user_agent      User agent
 */
class _SERVER extends ArrayIterator {
	public $accept_language = '';
	public $accept_version  = '';
	public $content_type    = '';
	public $dnt             = false;
	public $host            = '';
	public $ip              = '';
	public $query_string    = '';
	public $referer         = '';
	public $remote_addr     = '';
	public $request_method  = '';
	public $request_uri     = '';
	public $secure          = false;
	public $user_agent      = '';
	public $_SERVER         = [];

	function __construct ($SERVER) {
		parent::__construct($SERVER);
		$this->_SERVER         = $SERVER;
		$this->accept_language = isset($SERVER['HTTP_ACCEPT_LANGUAGE']) ? $SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
		$this->accept_version  =
			isset($SERVER['HTTP_ACCEPT_VERSION']) && preg_match('/^[0-9\.]+$/', $SERVER['HTTP_ACCEPT_VERSION']) ? $SERVER['HTTP_ACCEPT_VERSION'] : 1;
		$this->content_type    = isset($SERVER['CONTENT_TYPE']) ? $SERVER['CONTENT_TYPE'] : '';
		$this->dnt             = isset($SERVER['HTTP_DNT']) && $SERVER['HTTP_DNT'] == 1;
		$this->host            = isset($SERVER['SERVER_NAME']) ? $SERVER['SERVER_NAME'] : '';
		if (isset($SERVER['HTTP_HOST'])) {
			if (filter_var($this->host, FILTER_VALIDATE_IP)) {
				$this->host = $SERVER['HTTP_HOST'];
			} elseif (strpos($SERVER['HTTP_HOST'], ':') !== false) {
				$this->host .= ':'.(int)explode(':', $SERVER['HTTP_HOST'], 2)[1];
			}
		}
		$this->ip             = $this->ip($_SERVER);
		$this->query_string   = isset($SERVER['QUERY_STRING']) ? $SERVER['QUERY_STRING'] : '';
		$this->referer        = isset($SERVER['HTTP_REFERER']) && filter_var($SERVER['HTTP_REFERER'], FILTER_VALIDATE_URL) ? $SERVER['HTTP_REFERER'] : '';
		$this->remote_addr    = isset($SERVER['REMOTE_ADDR']) ? $SERVER['REMOTE_ADDR'] : '127.0.0.1';
		$this->request_uri    = isset($SERVER['REQUEST_URI']) ? $SERVER['REQUEST_URI'] : '';
		$this->request_method = isset($SERVER['REQUEST_METHOD']) ? $SERVER['REQUEST_METHOD'] : '';
		$this->secure         = isset($SERVER['HTTPS']) ? $SERVER['HTTPS'] == 'on' : (
			isset($SERVER['HTTP_X_FORWARDED_PROTO']) && $SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
		);
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
	 * Whether key exists (from original `$_SERVER` super global)
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	function offsetExists ($key) {
		return isset($this->_SERVER[$key]);
	}
	/**
	 * Get key (from original `$_SERVER` super global)
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	function offsetGet ($key) {
		return $this->_SERVER[$key];
	}
	/**
	 * Set key (from original `$_SERVER` super global)
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	function offsetSet ($key, $value) {
		$this->_SERVER[$key] = $value;
	}
	/**
	 * Unset key (from original `$_SERVER` super global)
	 *
	 * @param string $key
	 */
	public function offsetUnset ($key) {
		unset($this->_SERVER[$key]);
	}
}
