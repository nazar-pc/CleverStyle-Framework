<?php
/**
 * @package    Psr7
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
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
	static function init_from_psr7 ($Psr7_request) {
		++System_request::$id;
		$System_request = System_request::instance();
		self::from_psr7_server($System_request, $Psr7_request);
		self::from_psr7_query($System_request, $Psr7_request);
		self::from_psr7_data_and_files($System_request, $Psr7_request);
		$System_request->init_route();
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
		$System_request->remote_addr = @$Psr7_request->getServerParams()['REMOTE_ADDR'] ?: '127.0.0.1';
		$System_request->ip          = $System_request->ip(
			[
				'HTTP_X_FORWARDED_FOR'     => $Psr7_request->getHeaderLine('x-forwarded-for'),
				'HTTP_CLIENT_IP'           => $Psr7_request->getHeaderLine('client-ip'),
				'HTTP_X_FORWARDED'         => $Psr7_request->getHeaderLine('x-forwarded'),
				'HTTP_X_CLUSTER_CLIENT_IP' => $Psr7_request->getHeaderLine('x-cluster-client-ip'),
				'HTTP_FORWARDED_FOR'       => $Psr7_request->getHeaderLine('forwarded-for'),
				'HTTP_FORWARDED'           => $Psr7_request->getHeaderLine('forwarded')
			]
		);
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
