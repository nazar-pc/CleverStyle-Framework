<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\WebSockets;
use
	cs\Language,
	Ratchet\ConnectionInterface,
	Ratchet\Http\HttpServerInterface,
	Guzzle\Http\Message\RequestInterface;

class Connection_properties_injector implements HttpServerInterface {
	private $delegate;
	/**
	 * @inheritdoc
	 */
	public function __construct (HttpServerInterface $delegate) {
		$this->delegate = $delegate;
	}
	/**
	 * @inheritdoc
	 */
	public function onOpen (ConnectionInterface $conn, RequestInterface $request = null) {
		$L = Language::instance();
		/** @noinspection PhpUndefinedFieldInspection */
		$ip               = $this->ip(
			[
				$conn->remoteAddress,
				$request->getHeader('X-Forwarded-For'),
				$request->getHeader('Client-IP'),
				$request->getHeader('X-Forwarded'),
				$request->getHeader('X-Cluster-Client-IP'),
				$request->getHeader('Forwarded-For'),
				$request->getHeader('Forwarded')
			]
		);
		$conn->user_agent = $request->getHeader('User-Agent');
		$conn->session_id = $request->getCookie('session');
		/** @noinspection PhpUndefinedFieldInspection */
		$conn->remote_addr = $conn->remoteAddress;
		$conn->ip          = $ip;
		$conn->language    = $L->url_language($request->getPath()) ?: $L->clanguage;
		$this->delegate->onOpen($conn, $request);
	}
	/**
	 * The best guessed IP of client (based on all known headers), `127.0.0.1` by default
	 *
	 * @param string[] $source_ips
	 *
	 * @return string
	 */
	protected function ip ($source_ips) {
		foreach ($source_ips as $ip) {
			$ip = trim(explode(',', $ip)[0]);
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				return $ip;
			}
		}
		return '127.0.0.1';
	}
	/**
	 * {@inheritdoc}
	 */
	public function onMessage (ConnectionInterface $from, $msg) {
		$this->delegate->onMessage($from, $msg);
	}
	/**
	 * {@inheritdoc}
	 */
	public function onClose (ConnectionInterface $conn) {
		$this->delegate->onClose($conn);
	}
	/**
	 * {@inheritdoc}
	 */
	public function onError (ConnectionInterface $conn, \Exception $e) {
		$this->delegate->onError($conn, $e);
	}
}
