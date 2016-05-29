<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;

trait Server {
	/**
	 * Uppercase method, GET by default
	 *
	 * @var string
	 */
	public $method;
	/**
	 * The best guessed host
	 *
	 * @var string
	 */
	public $host;
	/**
	 * Schema `http` or `https`
	 *
	 * @var string
	 */
	public $scheme;
	/**
	 * Is requested with HTTPS
	 *
	 * @var bool
	 */
	public $secure;
	/**
	 * Protocol, for instance: `HTTP/1.0`, `HTTP/1.1` (default), HTTP/2.0
	 *
	 * @var string
	 */
	public $protocol;
	/**
	 * Path
	 *
	 * @var string
	 */
	public $path;
	/**
	 * URI, basically `$path?$query_string` (without `?` is query string is empty), `/` by default
	 *
	 * @var string
	 */
	public $uri;
	/**
	 * Query string
	 *
	 * @var string
	 */
	public $query_string;
	/**
	 * Where request came from, not necessary real IP of client, `127.0.0.1` by default
	 *
	 * @var string
	 */
	public $remote_addr;
	/**
	 * The best guessed IP of client (based on all known headers), `$this->remote_addr` by default
	 *
	 * @var string
	 */
	public $ip;
	/**
	 * Headers are normalized to lowercase keys with hyphen as separator, for instance: `connection`, `referer`, `content-type`, `accept-language`
	 *
	 * @var string[]
	 */
	public $headers;
	/**
	 * @var bool
	 */
	protected $cli;
	/**
	 * @param string[] $server Typically `$_SERVER`
	 */
	function init_server ($server = []) {
		$this->fill_headers($server);
		/**
		 * Add some defaults to avoid isset() hell afterwards
		 */
		$server += [
			'QUERY_STRING'    => '',
			'REMOTE_ADDR'     => '127.0.0.1',
			'REQUEST_URI'     => '/',
			'REQUEST_METHOD'  => 'GET',
			'SERVER_PROTOCOL' => 'HTTP/1.1'
		];
		$this->fill_server_properties($server);
	}
	/**
	 * @param string[] $server
	 */
	protected function fill_server_properties ($server) {
		$this->cli          = @$server['CLI'] === true;
		$this->method       = strtoupper($server['REQUEST_METHOD']);
		$this->host         = $this->host($server);
		$this->secure       = $this->secure($server);
		$this->scheme       = $this->secure ? 'https' : 'http';
		$this->protocol     = $server['SERVER_PROTOCOL'];
		$this->query_string = $server['QUERY_STRING'];
		$this->uri          = null_byte_filter(urldecode($server['REQUEST_URI'])) ?: '/';
		$this->path         = explode('?', $this->uri, 2)[0];
		$this->remote_addr  = $server['REMOTE_ADDR'];
		$this->ip           = $this->ip($server);
	}
	/**
	 * @param string[] $server
	 */
	protected function fill_headers ($server) {
		$headers = [];
		foreach ($server as $header => $header_content) {
			if (strpos($header, 'HTTP_') === 0) {
				$header = substr($header, 5);
			} elseif (strpos($header, 'CONTENT_') !== 0) {
				continue;
			}
			$header           = strtolower(str_replace('_', '-', $header));
			$headers[$header] = $header_content;
		}
		$this->headers = $headers;
	}
	/**
	 * The best guessed IP of client (based on all known headers), `127.0.0.1` by default
	 *
	 * @param string[] $server
	 *
	 * @return string
	 */
	protected function ip ($server) {
		$all_possible_keys = [
			'HTTP_X_FORWARDED_FOR',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED'
		];
		foreach ($all_possible_keys as $key) {
			if (isset($server[$key])) {
				$ip = trim(explode(',', $server[$key])[0]);
				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
					return $ip;
				}
			}
		}
		return $this->remote_addr;
	}
	/**
	 * The best guessed host
	 *
	 * @param string[] $server
	 *
	 * @return string
	 */
	protected function host ($server) {
		$host          = @$server['SERVER_NAME'] ?: '';
		$port          = '';
		$expected_port = $this->secure ? 443 : 80;
		if (!$host && isset($server['HTTP_X_FORWARDED_HOST'])) {
			$host = $server['HTTP_X_FORWARDED_HOST'];
			if (
				isset($server['HTTP_X_FORWARDED_PORT']) &&
				$server['HTTP_X_FORWARDED_PORT'] != $expected_port
			) {
				$port = (int)$server['HTTP_X_FORWARDED_PORT'];
			}
		} elseif (isset($server['HTTP_HOST'])) {
			/** @noinspection NotOptimalIfConditionsInspection */
			if (!$host || filter_var($host, FILTER_VALIDATE_IP)) {
				$host = $server['HTTP_HOST'];
			} elseif (strpos($server['HTTP_HOST'], ':') !== false) {
				$port = (int)explode(':', $server['HTTP_HOST'])[1];
				if ($port == $expected_port) {
					$port = '';
				}
			}
		}
		if (preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host) !== '') {
			return '';
		}
		return $host.($port ? ":$port" : '');
	}
	/**
	 * Secure protocol detection
	 *
	 * @param array $server
	 *
	 * @return bool
	 */
	protected function secure ($server) {
		return @$server['HTTPS'] ? $server['HTTPS'] !== 'off' : (
			@$server['REQUEST_SCHEME'] === 'https' ||
			@$server['HTTP_X_FORWARDED_PROTO'] === 'https'
		);
	}
	/**
	 * Get header by name
	 *
	 * @param string $name Case-insensitive
	 *
	 * @return string Header content if exists or empty string otherwise
	 */
	function header ($name) {
		$name = strtolower($name);
		return isset($this->headers[$name]) ? $this->headers[$name] : '';
	}
}
