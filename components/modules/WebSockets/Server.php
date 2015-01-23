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
	React\EventLoop\Factory as Loop_factory,
	Ratchet\Client\Factory as Client_factory,
	Ratchet\ConnectionInterface,
	Ratchet\Http\HttpServer,
	Ratchet\MessageComponentInterface,
	Ratchet\Server\IoServer,
	Ratchet\Client\WebSocket as Client_websocket,
	Ratchet\WebSocket\WsServer,
	cs\Config,
	cs\Core,
	cs\Encryption,
	cs\Singleton,
	cs\Trigger,
	cs\User,
	SplObjectStorage;
class Server implements MessageComponentInterface {
	use
		Singleton;
	const SEND_TO_ALL              = 1;
	const SEND_TO_REGISTERED_USERS = 2;
	const SEND_TO_SPECIFIC_USERS   = 3;
	const SEND_TO_USERS_GROUP      = 4;
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
	/**
	 * Is current server instance master server
	 *
	 * @var bool
	 */
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
	function run () {
		$this->clients = new SplObjectStorage;
		$this->servers = new SplObjectStorage;
		$ws_server     = new WsServer($this);
		// No encoding check - better performance, browsers do this anyway
		$ws_server->setEncodingChecks(false);
		// Disable all versions except RFC6455, which is supported by all
		$ws_server->disableVersion(0);
		$ws_server->disableVersion(6);
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		$this->io_server = IoServer::factory(
			new HttpServer($ws_server),
			Config::instance()->module('WebSockets')->{$_SERVER->secure ? 'external_port_secure' : 'external_port'}
		);
		$this->io_server->run();
		// Since we may work with a lot of different users - disable this cache in order to not run out of memory
		User::instance()->disable_memory_cache();
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
		$res = $this->parse_message($message, $action, $details, $response_to, $target);
		if (!$res) {
			$from->close();
			return;
		}
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
			/**
			 * Internal connection from application
			 */
			case 'Internal':
				if (
					$this->parse_message($details, $action_, $details_, $response_to_, $target_) &&
					in_array($from->remoteAddress, ['127.0.0.1', '::1'])
				) {
					$from->close();
					$this->send_to_master($details);
					$this->send_to_clients_internal($action_, $details_, $response_to_, $target_);
				}
				return;
			case 'Client/authentication':
				// TODO: client authentication, assign user id and groups as properties of connection
		}
		if ($this->servers->contains($from)) {
			$this->broadcast_message_to_servers($message, $from);
			if (!$response_to) {
				return;
			}
			$this->send_to_clients_internal($action, $details, $response_to, $target);
		} else {
			Trigger::instance()->run("WebSockets/$action", $details);
		}
	}
	/**
	 * @param string    $message
	 * @param string    $action
	 * @param mixed     $details
	 * @param int|int[] $response_to
	 * @param int       $target
	 *
	 * @return bool
	 */
	protected function parse_message ($message, &$action, &$details, &$response_to, &$target) {
		$decoded_message = _json_decode($message);
		if (
			!isset($decoded_message[0], $decoded_message[1]) ||
			!is_array($decoded_message)
		) {
			return false;
		}
		list($action, $details) = $decoded_message;
		$response_to = isset($decoded_message[2]) ? $decoded_message[2] : 0;
		$target      = isset($decoded_message[3]) ? $decoded_message[4] : false;
		return true;
	}
	/**
	 * @param string                   $message
	 * @param ConnectionInterface|null $skip_server
	 */
	protected function broadcast_message_to_servers ($message, $skip_server = null) {
		foreach ($this->servers as $server) {
			if ($server === $skip_server) {
				continue;
			}
			$server->send($message);
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
	function send_to_clients ($action, $details, $response_to, $target = false) {
		$message = _json_encode([$action, $details, $response_to, $target]);
		/**
		 * If server running in current process
		 */
		if ($this->io_server) {
			if ($this->is_master) {
				$this->broadcast_message_to_servers($message);
			} else {
				$this->send_to_master($message);
			}
			$this->send_to_clients_internal($action, $details, $response_to, $target);
			$this->broadcast_message_to_servers($message);
			return;
		}
		/**
		 * Is server not running at all - run it
		 */
		if (!is_server_running()) {
			if (is_exec_available()) {
				cross_platform_server_in_background();
			} else {
				file_get_contents(
					Config::instance()->base_url().'/WebSockets',
					null,
					stream_context_create([
						'http' => [
							'timeout' => 2
						]
					])
				);
			}
			// Wait while server will start
			sleep(2);
		}
		$protocol  = $_SERVER->secure ? 'wss' : 'ws';
		$port      = Config::instance()->module('WebSockets')->{$_SERVER->secure ? 'external_port_secure' : 'external_port'};
		$loop      = Loop_factory::create();
		$connector = new Client_factory($loop);
		$connector("$protocol://127.0.0.1:$port")->then(
			function (Client_websocket $conn) use ($loop, $message) {
				$conn->send(
					_json_encode(['Internal', $message])
				);
			},
			function () use ($loop) {
				$loop->stop();
			}
		);
		$loop->run();
	}
	/**
	 * Send request to client
	 *
	 * @param string         $action
	 * @param mixed          $details
	 * @param int            $response_to Constants `self::SEND_TO_*` should be used here
	 * @param bool|int|int[] $target      Id or array of ids in case of response to one or several users or groups
	 */
	protected function send_to_clients_internal ($action, $details, $response_to, $target = false) {
		$message = _json_encode([$action, $details]);
		switch ($response_to) {
			case self::SEND_TO_ALL:
				foreach ($this->clients as $client) {
					$client->send($message);
				}
				break;
			case self::SEND_TO_REGISTERED_USERS:
				foreach ($this->clients as $client) {
					if ($client->user_id != User::GUEST_ID) {
						$client->send($message);
					}
				}
				break;
			case self::SEND_TO_SPECIFIC_USERS:
				$target = (array)$target;
				foreach ($this->clients as $client) {
					if (in_array($client->user_id, $target)) {
						$client->send($message);
					}
				}
				break;
			case self::SEND_TO_USERS_GROUP:
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
	 * @param string $message
	 */
	protected function send_to_master ($message) {
		if (!$this->connection_to_master) {
			return;
		}
		$this->connection_to_master->send($message);
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
