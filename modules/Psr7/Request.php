<?php
/**
 * @package    Psr7
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\Psr7;
use
	cs\Request as System_request;

class Request {
	/**
	 * Initialize request from PSR-7 request object
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $Psr7_request
	 *
	 * @throws \cs\ExitException
	 */
	public static function init_from_psr7 ($Psr7_request) {
		++System_request::$id;
		$System_request = System_request::instance();
		self::from_psr7_server($System_request, $Psr7_request);
		self::from_psr7_query($System_request, $Psr7_request);
		self::from_psr7_data_and_files($System_request, $Psr7_request);
		$System_request->init_route();
		$System_request->started = microtime(true);
	}
	/**
	 * @param System_request                           $System_request
	 * @param \Psr\Http\Message\ServerRequestInterface $Psr7_request
	 */
	protected static function from_psr7_server ($System_request, $Psr7_request) {
		$uri                    = $Psr7_request->getUri();
		$System_request->method = $Psr7_request->getMethod();
		$System_request->host   = $uri->getHost();
		$System_request->scheme = $uri->getScheme();
		$System_request->secure = $System_request->scheme == 'https';
		if (
			(!$System_request->secure && $uri->getPort() != 80) ||
			($System_request->secure && $uri->getPort() != 443)
		) {
			$System_request->host .= ':'.$uri->getPort();
		}
		$System_request->protocol     = 'HTTP/'.$Psr7_request->getProtocolVersion();
		$System_request->path         = $uri->getPath();
		$System_request->query_string = $uri->getQuery();
		/** @noinspection NestedTernaryOperatorInspection */
		$System_request->uri         = $System_request->path.($System_request->query_string ? "?$System_request->query_string" : '') ?: '/';
		$System_request->remote_addr = $Psr7_request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
		$System_request->ip          = self::ip($System_request, $Psr7_request);
	}
	/**
	 * The best guessed IP of client (based on all known headers), `127.0.0.1` by default
	 *
	 * @param System_request                           $System_request
	 * @param \Psr\Http\Message\ServerRequestInterface $Psr7_request
	 *
	 * @return string
	 */
	protected static function ip ($System_request, $Psr7_request) {
		$potential_addresses = [
			$Psr7_request->getHeaderLine('x-forwarded-for'),
			$Psr7_request->getHeaderLine('client-ip'),
			$Psr7_request->getHeaderLine('x-forwarded'),
			$Psr7_request->getHeaderLine('x-cluster-client-ip'),
			$Psr7_request->getHeaderLine('forwarded-for'),
			$Psr7_request->getHeaderLine('forwarded')
		];
		foreach ($potential_addresses as $ip) {
			$ip = trim(explode(',', $ip)[0]);
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				return $ip;
			}
		}
		return $System_request->remote_addr;
	}
	/**
	 * @param System_request                           $System_request
	 * @param \Psr\Http\Message\ServerRequestInterface $Psr7_request
	 */
	protected static function from_psr7_query ($System_request, $Psr7_request) {
		$System_request->query = $Psr7_request->getQueryParams();
	}
	/**
	 * @param System_request                           $System_request
	 * @param \Psr\Http\Message\ServerRequestInterface $Psr7_request
	 *
	 * @throws \cs\ExitException
	 */
	protected static function from_psr7_data_and_files ($System_request, $Psr7_request) {
		Psr7_data_stream::$stream = $Psr7_request->getBody();
		$System_request->init_data_and_files([], [], fopen('request-psr7-data://', 'r'));
	}
}
