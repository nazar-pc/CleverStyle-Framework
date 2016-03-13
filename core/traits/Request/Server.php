<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;
use
	cs\ExitException;

trait Server {
	/**
	 * Language accepted by client, `''` by default
	 *
	 * @var string
	 */
	public $language;
	/**
	 * Version accepted by client, will match `/^[0-9\.]+$/`, useful for API, `1` by default
	 *
	 * @var string
	 */
	public $version;
	/**
	 * Content type, `''` by default
	 *
	 * @var string
	 */
	public $content_type;
	/**
	 * Do not track
	 *
	 * @var bool
	 */
	public $dnt;
	/**
	 * The best guessed host
	 *
	 * @var string
	 */
	public $host;
	/**
	 * The best guessed IP of client (based on all known headers), `$this->remote_addr` by default
	 *
	 * @var string
	 */
	public $ip;
	/**
	 * Schema `http` or `https`
	 *
	 * @var string
	 */
	public $schema;
	/**
	 * Protocol, for instance: `http/1.0`, `http/1.1` (default)
	 *
	 * @var string
	 */
	public $protocol;
	/**
	 * Query string
	 *
	 * @var string
	 */
	public $query_string;
	/**
	 * HTTP referer, `''` by default
	 *
	 * @var string
	 */
	public $referer;
	/**
	 * Where request came from, not necessary real IP of client
	 *
	 * @var string
	 */
	public $remote_addr;
	/**
	 * Path
	 *
	 * @var string
	 */
	public $path;
	/**
	 * Uppercase method, GET by default
	 *
	 * @var string
	 */
	public $method;
	/**
	 * Is requested with HTTPS
	 *
	 * @var bool
	 */
	public $secure;
	/**
	 * User agent, `127.0.0.1` by default
	 *
	 * @var string
	 */
	public $user_agent;
	/**
	 * Headers are normalized to lowercase keys with hyphen as separator, for instance: `connection`, `referer`, `content-type`, `accept-language`
	 *
	 * @var string[]
	 */
	public $headers;
	/**
	 * @param string[] $server Typically `$_SERVER`
	 *
	 * @throws ExitException
	 */
	function init_server ($server = []) {
		$this->fill_headers($server);
		/**
		 * Add some defaults to avoid isset() hell afterwards
		 */
		$server += [
			'HTTP_ACCEPT_LANGUAGE' => '',
			'HTTP_ACCEPT_VERSION'  => '1',
			'CONTENT_TYPE'         => '',
			'HTTP_DNT'             => '0',
			'QUERY_STRING'         => '',
			'HTTP_REFERER'         => '',
			'REMOTE_ADDR'          => '127.0.0.1',
			'REQUEST_URI'          => '',
			'REQUEST_METHOD'       => 'GET',
			'HTTP_USER_AGENT'      => '',
			'SERVER_PROTOCOL'      => 'http/1.1'
		];
		$this->fill_server_properties($server);
	}
	/**
	 * @param string[] $server
	 *
	 * @throws ExitException
	 */
	protected function fill_server_properties ($server) {
		$this->language     = $server['HTTP_ACCEPT_LANGUAGE'];
		$this->version      = preg_match('/^[0-9\.]+$/', $server['HTTP_ACCEPT_VERSION']) ? $server['HTTP_ACCEPT_VERSION'] : 1;
		$this->content_type = $server['CONTENT_TYPE'];
		$this->dnt          = $server['HTTP_DNT'] == 1;
		$this->secure       = $this->secure($server);
		$this->schema       = $this->secure ? 'https' : 'http';
		$this->protocol     = strtolower($server['SERVER_PROTOCOL']);
		$this->host         = $this->host($server);
		$this->ip           = $this->ip($_SERVER);
		$this->query_string = $server['QUERY_STRING'];
		$this->referer      = filter_var($server['HTTP_REFERER'], FILTER_VALIDATE_URL) ? $server['HTTP_REFERER'] : '';
		$this->remote_addr  = $server['REMOTE_ADDR'];
		$this->path         = explode('?', $server['REQUEST_URI'], 2)[0];
		$this->method       = strtoupper($server['REQUEST_METHOD']);
		$this->user_agent   = $server['HTTP_USER_AGENT'];
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
	 * The best guessed IP of client (based on all known headers), `$this->remote_addr` by default
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
		return isset($server['REMOTE_ADDR']) ? '127.0.0.1' : '';
	}
	/**
	 * The best guessed host
	 *
	 * @param string[] $server
	 *
	 * @throws ExitException
	 *
	 * @return string
	 */
	protected function host ($server) {
		$host          = isset($server['SERVER_NAME']) ? $server['SERVER_NAME'] : '';
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
			trigger_error("Invalid host", E_USER_WARNING);
			throw new ExitException(400);
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
		return isset($server['HTTPS']) && $server['HTTPS'] ? $server['HTTPS'] !== 'off' : (
			isset($server['HTTP_X_FORWARDED_PROTO']) && $server['HTTP_X_FORWARDED_PROTO'] === 'https'
		);
	}
}
