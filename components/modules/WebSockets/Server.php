<?php
/**
 * @package    WebSockets
 * @subpackage CleverStyle CMS We
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\WebSockets;
use
	Ratchet\ConnectionInterface,
	Ratchet\Http\HttpServer,
	Ratchet\MessageComponentInterface,
	Ratchet\Server\IoServer,
	Ratchet\WebSocket\WsServer,
	cs\Core,
	cs\Encryption,
	cs\Singleton,
	cs\Trigger,
	cs\User,
	SplObjectStorage;

class Server implements MessageComponentInterface {
	use
		Singleton;
	const RESPONSE_TO_ALL              = 1;
	const RESPONSE_TO_REGISTERED_USERS = 2;
	const RESPONSE_TO_SPECIFIC_USERS   = 3;
	const RESPONSE_TO_USERS_GROUP      = 4;
	/**
	 * Each object additionally will have properties `user_id` and `user_groups` with user id and ids of user groups correspondingly
	 *
	 * @var ConnectionInterface[]
	 */
	protected $clients;
	/**
	 * @var ConnectionInterface[]
	 */
	protected $servers;
	protected $is_master = false;
	/**
	 * Connection to master server
	 *
	 * @var ConnectionInterface
	 */
	protected $connection_to_master;
	/**
	 * @var IoServer
	 */
	protected $io_server;
	/**
	 * Run WebSockets server
	 */
	function construct () {
		$this->clients   = new SplObjectStorage;
		$this->servers   = new SplObjectStorage;
		$this->io_server = IoServer::factory(
			new HttpServer(
				new WsServer($this)
			),
			7777
		);
		$this->io_server->run();
		Trigger::instance()->run('WebSockets/register_actions');
	}
	/**
	 * @param ConnectionInterface $connection
	 */
	function onOpen (ConnectionInterface $connection) {
		echo "Connected\n";
		$this->clients->attach($connection);
		$connection->user_id     = User::GUEST_ID;
		$connection->user_groups = [];
	}
	/**
	 * @param ConnectionInterface $from
	 * @param string              $message
	 */
	function onMessage (ConnectionInterface $from, $message) {
		$decoded_message = _json_decode($message);
		if (
			!isset($decoded_message[0], $decoded_message[1]) ||
			!is_array($decoded_message)
		) {
			$from->close();
			return;
		}
		list($action, $details) = $decoded_message;
		$response_to = isset($decoded_message[2]) ? $decoded_message[2] : 0;
		$target      = isset($decoded_message[3]) ? $decoded_message[4] : false;
		unset($decoded_message);
		switch ($action) {
			/**
			 * Connection to master server as server (by default all connections considered as clients)
			 */
			case 'Server/connect':
				if (Encryption::instance()->decrypt($details) == Core::instance()->public_key) {
					$this->clients->detach($from);
					$this->servers->attach($from);
					return;
				}
				$from->close();
				return;
			case 'Client/authentication':
				// TODO: client authentication, assign user id and groups as properties of connection
		}
		if ($this->servers->contains($from)) {
			foreach ($this->servers as $server) {
				if ($server === $from) {
					continue;
				}
				$server->send($message);
			}
			if (!$response_to || !$target) {
				return;
			}
			$this->send_to_clients_internal($action, $details, $response_to, $target);
		} else {
			Trigger::instance()->run("WebSockets/$action", $details);
		}
	}
	/**
	 * Send request to client
	 *
	 * @param string         $action
	 * @param mixed          $details
	 * @param int            $response_to Constants `self::RESPONSE_TO*` should be used here
	 * @param bool|int|int[] $target      Id or array of ids in case of response to one or several users or groups
	 */
	function send_to_clients ($action, $details, $response_to, $target, $target = false) {
		if (!$this->is_master) {
			$this->send_to_master($action, $details, $response_to, $target);
		}
		if (!$this->io_server) {
			// TODO: if no local io server - there is a need to connect to local WebSockets server
		}
		$this->send_to_clients_internal($action, $details, $response_to, $target);
	}
	/**
	 * Send request to client
	 *
	 * @param string         $action
	 * @param mixed          $details
	 * @param int            $response_to Constants `self::RESPONSE_TO*` should be used here
	 * @param bool|int|int[] $target      Id or array of ids in case of response to one or several users or groups
	 */
	protected function send_to_clients_internal ($action, $details, $response_to, $target = false) {
		$message = _json_decode($action, $details);
		switch ($response_to) {
			case self::RESPONSE_TO_ALL:
				foreach ($this->clients as $client) {
					$client->send($message);
				}
				break;
			case self::RESPONSE_TO_REGISTERED_USERS:
				foreach ($this->clients as $client) {
					if ($client->user_id != User::GUEST_ID) {
						$client->send($message);
					}
				}
				break;
			case self::RESPONSE_TO_SPECIFIC_USERS:
				$target = (array)$target;
				foreach ($this->clients as $client) {
					if (in_array($client->user_id, $target)) {
						$client->send($message);
					}
				}
				break;
			case self::RESPONSE_TO_USERS_GROUP:
				$target = (array)$target;
				foreach ($this->clients as $client) {
					if (array_intersect($client->user_groups, $target)) {
						$client->send($message);
					}
				}
				break;
		}
	}
	/**
	 * Send request to master server in order to propagate request to all other servers
	 *
	 * @param string         $action
	 * @param mixed          $details
	 * @param int            $response_to Constants `self::RESPONSE_TO*` should be used here
	 * @param bool|int|int[] $target      Id or array of ids in case of response to one or several users or groups
	 */
	protected function send_to_master ($action, $details, $response_to = 0, $target = false) {
		if (!$this->connection_to_master) {
			return;
		}
		$this->connection_to_master->send(
			_json_decode($action, $details, $response_to, $target)
		);
	}
	/**
	 * @param ConnectionInterface $connection
	 */
	function onClose (ConnectionInterface $connection) {
		echo "Disconnected\n";
		// The connection is closed, remove it, as we can no longer send it messages
		$this->clients->detach($connection);
		$this->servers->detach($connection);
	}
	/**
	 * @param ConnectionInterface $connection
	 * @param \Exception          $e
	 */
	function onError (ConnectionInterface $connection, \Exception $e) {
		echo "An error has occurred: {$e->getMessage()}\n";
		$connection->close();
	}
}
