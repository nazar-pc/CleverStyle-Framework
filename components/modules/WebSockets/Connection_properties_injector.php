<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\WebSockets;
use
	cs\_SERVER,
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
		$SERVER            = new _SERVER(
			[
				'REMOTE_ADDR'              => $conn->remoteAddress,
				'HTTP_X_FORWARDED_FOR'     => $request->getHeader('X-Forwarded-For'),
				'HTTP_CLIENT_IP'           => $request->getHeader('Client-IP'),
				'HTTP_X_FORWARDED'         => $request->getHeader('X-Forwarded'),
				'HTTP_X_CLUSTER_CLIENT_IP' => $request->getHeader('X-Cluster-Client-IP'),
				'HTTP_FORWARDED_FOR'       => $request->getHeader('Forwarded-For'),
				'HTTP_FORWARDED'           => $request->getHeader('Forwarded')
			]
		);
		$conn->user_agent  = $request->getHeader('User-Agent');
		$conn->session_id  = $request->getCookie('session');
		$conn->remote_addr = $SERVER->remote_addr;
		$conn->ip          = $SERVER->ip;
		$conn->language    = $L->url_language($request->getPath()) ?: $L->clanguage;
		$this->delegate->onOpen($conn, $request);
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
