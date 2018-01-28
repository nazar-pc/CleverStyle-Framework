<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\WebSockets;
use
	cs\Config,
	cs\Language,
	Ratchet\ConnectionInterface,
	Ratchet\Http\HttpServerInterface,
	Psr\Http\Message\RequestInterface;

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
		/** @noinspection NullPointerExceptionInspection */
		$ip = $this->ip(
			[
				$conn->remoteAddress,
				$request->getHeaderLine('X-Forwarded-For'),
				$request->getHeaderLine('Client-IP'),
				$request->getHeaderLine('X-Forwarded'),
				$request->getHeaderLine('X-Cluster-Client-IP'),
				$request->getHeaderLine('Forwarded-For'),
				$request->getHeaderLine('Forwarded')
			]
		);
		/** @noinspection NullPointerExceptionInspection */
		$conn->user_agent = $request->getHeaderLine('User-Agent');
		$cookie_name      = Config::instance()->core['cookie_prefix'].'session';
		/** @noinspection NullPointerExceptionInspection */
		foreach ($request->getHeader('Cookie') as $cookie) {
			$cookie = explode('=', $cookie);
			if ($cookie[0] === $cookie_name) {
				$conn->session_id = $cookie[1];
				break;
			}
		}
		/** @noinspection PhpUndefinedFieldInspection */
		$conn->remote_addr = $conn->remoteAddress;
		$conn->ip          = $ip;
		/** @noinspection NullPointerExceptionInspection */
		$conn->language = $L->url_language($request->getUri()->getPath()) ?: $L->clanguage;
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
