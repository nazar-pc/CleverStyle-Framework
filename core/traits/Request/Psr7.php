<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;

trait Psr7 {
	/**
	 * Initialize request from PSR-7 request object
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 *
	 * @throws \cs\ExitException
	 */
	function init_from_psr7 ($request) {
		$this->from_psr7_server($request);
		$this->from_psr7_query($request);
		$this->from_psr7_data_and_files($request);
		$this->init_route();
	}
	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 */
	protected function from_psr7_server ($request) {
		$uri          = $request->getUri();
		$this->method = $request->getMethod();
		$this->host   = $uri->getHost();
		$this->scheme = $uri->getScheme();
		$this->secure = $this->scheme == 'https';
		if (
			(!$this->secure && $uri->getPort() != 80) ||
			($this->secure && $uri->getPort() != 443)
		) {
			$this->host .= ':'.$uri->getPort();
		}
		$this->protocol     = 'HTTP/'.$request->getProtocolVersion();
		$this->path         = $uri->getPath();
		$this->query_string = $uri->getQuery();
		/** @noinspection NestedTernaryOperatorInspection */
		$this->uri         = $this->path.($this->query_string ? "?$this->query_string" : '') ?: '/';
		$this->remote_addr = @$request->getServerParams()['REMOTE_ADDR'] ?: '127.0.0.1';
		$this->ip          = $this->ip(
			[
				'HTTP_X_FORWARDED_FOR'     => $request->getHeaderLine('x-forwarded-for'),
				'HTTP_CLIENT_IP'           => $request->getHeaderLine('client-ip'),
				'HTTP_X_FORWARDED'         => $request->getHeaderLine('x-forwarded'),
				'HTTP_X_CLUSTER_CLIENT_IP' => $request->getHeaderLine('x-cluster-client-ip'),
				'HTTP_FORWARDED_FOR'       => $request->getHeaderLine('forwarded-for'),
				'HTTP_FORWARDED'           => $request->getHeaderLine('forwarded')
			]
		);
	}
	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 */
	protected function from_psr7_query ($request) {
		$this->query = $request->getQueryParams();
	}
	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 *
	 * @throws \cs\ExitException
	 */
	protected function from_psr7_data_and_files ($request) {
		Psr7_data_stream::$stream = $request->getBody();
		$this->init_data_and_files([], [], fopen('request-psr7-data://', 'r'));
	}
}
