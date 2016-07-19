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
	 * @var string[]
	 */
	public $forwarded;
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
		$this->parse_forwarded_header();
		$this->cli          = @$server['CLI'] === true;
		$this->method       = strtoupper($server['REQUEST_METHOD']);
		$this->host         = $this->host($server);
		$this->secure       = $this->secure($server);
		$this->scheme       = $this->secure ? 'https' : 'http';
		$this->protocol     = $server['SERVER_PROTOCOL'];
		$this->query_string = $server['QUERY_STRING'];
		$this->uri          = null_byte_filter(rawurldecode($server['REQUEST_URI'])) ?: '/';
		if (strpos($this->uri, '/index.php') === 0) {
			$this->uri = substr($this->uri, 10);
		}
		$this->path        = explode('?', $this->uri, 2)[0];
		$this->remote_addr = $server['REMOTE_ADDR'];
		$this->ip          = $this->ip();
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
	 * Parse `Forwarded` header into `$this->forwarded`
	 */
	protected function parse_forwarded_header () {
		$this->forwarded = [];
		if (preg_match_all('/(for|proto|by)=(.*)(?:;|$)/Ui', explode(',', $this->header('forwarded'))[0], $matches)) {
			$this->forwarded = array_combine(
				$matches[1],
				_trim($matches[2], " \t\n\r\0\x0B\"")
			);
		}
	}
	/**
	 * The best guessed IP of client (based on all known headers), `127.0.0.1` by default
	 *
	 * @return string
	 */
	protected function ip () {
		$ips = [
			@$this->forwarded['for'],
			$this->header('x-forwarded-for'),
			$this->header('client-ip'),
			$this->header('x-forwarded'),
			$this->header('x-cluster-client-ip'),
			$this->header('forwarded-for')
		];
		$ips = array_filter($ips, 'trim');
		foreach ($ips as $ip) {
			$ip = trim(explode(',', $ip)[0]);
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				return $ip;
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
		$host                  = @$server['SERVER_NAME'] ?: '';
		$port                  = '';
		$expected_port         = $this->secure ? 443 : 80;
		$forwarded_host_header = $this->header('x-forwarded-host');
		$host_header           = $this->header('host');
		if (!$host && $forwarded_host_header) {
			list($host, $port) = explode(':', $forwarded_host_header) + [1 => $this->header('x-forwarded-port')];
		} elseif ($host_header) {
			if (!$host || filter_var($host, FILTER_VALIDATE_IP)) {
				$host = $host_header;
			} elseif (strpos($host_header, ':') !== false) {
				$port = explode(':', $host_header)[1];
			}
		}
		if ($port == $expected_port) {
			$port = '';
		}
		if (preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host) !== '') {
			return '';
		}
		return $host.($port ? ':'.(int)$port : '');
	}
	/**
	 * Secure protocol detection
	 *
	 * @param array $server
	 *
	 * @return bool
	 */
	protected function secure ($server) {
		return @$server['HTTPS'] ? $server['HTTPS'] !== 'off' : in_array(
			'https',
			[
				@$server['REQUEST_SCHEME'],
				@$this->forwarded['proto'],
				$this->header('x-forwarded-proto')
			]
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
