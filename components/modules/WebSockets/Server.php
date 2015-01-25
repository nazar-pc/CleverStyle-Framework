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
	Exception,
	SplObjectStorage;
class Server implements MessageComponentInterface {
	use
		Singleton;
	const SEND_TO_ALL              = 1;
	const SEND_TO_REGISTERED_USERS = 2;
	const SEND_TO_SPECIFIC_USERS   = 3;
	const SEND_TO_USERS_GROUP      = 4;
	/**
	 * Each object additionally will have properties `user_id`, `session_id` and `user_groups` with user id and ids of user groups correspondingly
	 *
	 * @var ConnectionInterface[]
	 */
	protected $clients;
	/**
	 * @var ConnectionInterface[]
	 */
	protected $servers;
	/**
	 * Connection to master server
	 *
	 * @var ConnectionInterface
	 */
	protected $connection_to_master;
	/**
	 * @var Pool
	 */
	protected $pool;
	/**
	 * Public address to WebSockets server in format wss://server/WebSockets or ws://server/WebSockets
	 *
	 * @var string
	 */
	protected $public_address;
	/**
	 * @var IoServer
	 */
	protected $io_server;
	/**
	 * @var int
	 */
	protected $listen_port;
	/**
	 * @var bool
	 */
	protected $listen_locally;
	/**
	 * @var bool
	 */
	protected $remember_session_ip;
	protected function construct () {
		$Config                    = Config::instance();
		$this->listen_port         = $Config->module('WebSockets')->listen_port;
		$this->listen_locally      = $Config->module('WebSockets')->listen_locally;
		$this->remember_session_ip = $Config->core['remember_user_ip'];
		$this->pool                = Pool::instance();
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		$this->public_address = ($_SERVER->secure ? 'wss' : 'ws')."://$_SERVER->host/WebSockets";
	}
	/**
	 * Run WebSockets server
	 */
	function run () {
		@ini_set('error_log', LOGS.'/WebSockets-server.log');
		$this->clients = new SplObjectStorage;
		$this->servers = new SplObjectStorage;
		$ws_server     = new WsServer($this);
		// No encoding check - better performance, browsers do this anyway
		$ws_server->setEncodingChecks(false);
		// Disable all versions except RFC6455, which is supported by all modern browsers
		$ws_server->disableVersion(0);
		$ws_server->disableVersion(6);
		$this->io_server = IoServer::factory(
			new HttpServer($ws_server),
			$this->listen_port,
			$this->listen_locally ? '127.0.0.1' : '0.0.0.0'
		);
		$this->connect_to_master();
		// Since we may work with a lot of different users - disable this cache in order to not run out of memory
		User::instance()->disable_memory_cache();
		Trigger::instance()->run('WebSockets/register_actions');
		$this->io_server->run();
	}
	/**
	 * @param ConnectionInterface $connection
	 */
	function onOpen (ConnectionInterface $connection) {
		$this->clients->attach($connection);
	}
	/**
	 * @param ConnectionInterface $connection
	 * @param string              $message
	 */
	function onMessage (ConnectionInterface $connection, $message) {
		$from_master = $connection === $this->connection_to_master;
		if (!$this->parse_message($message, $action, $details, $response_to, $target)) {
			if (!$from_master) {
				$connection->close();
			}
			return;
		}
		switch ($action) {
			/**
			 * Connection to master server as server (by default all connections considered as clients)
			 */
			case 'Server/connect':
				if (Encryption::instance()->decrypt($details) == Core::instance()->public_key) {
					$this->clients->detach($connection);
					$this->servers->attach($connection);
					return;
				}
				$connection->close();
				return;
			/**
			 * Internal connection from application
			 */
			case 'Application/Internal':
				/** @noinspection PhpUndefinedFieldInspection */
				if (
					$connection->remoteAddress == '127.0.0.1' &&
					$this->parse_message($details, $action_, $details_, $response_to_, $target_)
				) {
					$connection->close();
					$this->send_to_clients($action_, $details_, $response_to_, $target_);
				}
				return;
			case 'Client/authentication':
				if (!isset($details['session'], $details['user_agent'], $details['language'])) {
					$connection->send(_json_encode([
						'Client/authentication:error',
						$this->compose_error(400)
					]));
					return;
				}
				$User    = User::instance();
				$session = $User->get_session($details['session']);
				/** @noinspection PhpUndefinedFieldInspection */
				if (
					$session['user_agent'] != $details['user_agent'] ||
					(
						$this->remember_session_ip &&
						$session['ip'] != ip2hex($connection->remoteAddress)
					)
				) {
					$connection->send(_json_encode([
						'Client/authentication:error',
						$this->compose_error(403)
					]));
					$connection->close();
					return;
				}
				$connection->language   = $details['language'];
				$connection->user_id    = $session['user'];
				$connection->session_id = $session['id'];
				$connection->groups     = $User->get_groups($session['user']);
				$connection->send(_json_encode([
					'Client/authentication',
					'ok'
				]));
		}
		if ($from_master) {
			$this->send_to_clients_internal($action, $details, $response_to, $target);
		} elseif ($this->servers->contains($connection)) {
			$this->broadcast_message_to_servers($message, $connection);
			if (!$response_to) {
				return;
			}
			$this->send_to_clients_internal($action, $details, $response_to, $target);
		} elseif (isset($connection->user_id)) {
			/** @noinspection PhpUndefinedFieldInspection */
			Trigger::instance()->run("WebSockets/$action", [
				'details'  => $details,
				'language' => $connection->language,
				'user'     => $connection->user_id,
				'session'  => $connection->session_id
			]);
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
	 * Compose error, arguments similar to `code_header()`
	 *
	 * @param int         $error_code
	 * @param null|string $error_message String representation of status code code
	 *
	 * @return array Array to be passed as details to `::send_to_clients()`
	 */
	function compose_error ($error_code, $error_message = null) {
		$error_message = $error_message ?: code_header($error_code);
		return [
			$error_code,
			$error_message
		];
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
			if ($this->connection_to_master) {
				$this->connection_to_master->send($message);
			} else {
				$this->broadcast_message_to_servers($message);
			}
			$this->send_to_clients_internal($action, $details, $response_to, $target);
			return;
		}
		/**
		 * Is server not running at all - run it
		 */
		if (!is_server_running()) {
			if (is_exec_available()) {
				cross_platform_server_in_background();
				// Wait while server will start
				sleep(1);
			} else {
				$Config = Config::instance();
				file_get_contents(
					$Config->base_url().'/WebSockets/'.$Config->module('WebSockets')->security_key,
					null,
					stream_context_create([
						'http' => [
							'timeout' => 1
						]
					])
				);
			}
		}
		$loop      = Loop_factory::create();
		$connector = new Client_factory($loop);
		$connector("ws://127.0.0.1:$this->listen_port")->then(
			function (Client_websocket $connection) use ($message) {
				$connection->send(
					_json_encode(['Application/Internal', $message])
				);
				// Connection will be closed by server itself, no need to stop loop here
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
					if (isset($client->user_id)) {
						$client->send($message);
					}
				}
				break;
			case self::SEND_TO_SPECIFIC_USERS:
				$target = (array)$target;
				foreach ($this->clients as $client) {
					if (isset($client->user_id) && in_array($client->user_id, $target)) {
						$client->send($message);
					}
				}
				break;
			case self::SEND_TO_USERS_GROUP:
				$target = (array)$target;
				foreach ($this->clients as $client) {
					if (isset($client->user_groups) && array_intersect($client->user_groups, $target)) {
						$client->send($message);
					}
				}
				break;
		}
	}
	/**
	 * Connect to master server
	 *
	 * Two trials, if server do not respond twice - it will be removed from servers pool, and next server will become master
	 */
	protected function connect_to_master () {
		static $last_trial = '';
		// Add server to connections pool and connect to master if any
		$this->pool->add($this->public_address);
		$master = $this->pool->get_master();
		if ($master && $master != $this->public_address) {
			$connector = new Client_factory($this->io_server->loop);
			$connector($master)->then(
				function (Client_websocket $connection) use (&$last_trial) {
					$last_trial                 = '';
					$this->connection_to_master = $connection;
					$connection->on('message', function ($message) use ($connection) {
						$this->onMessage($connection, $message);
					});
					$connection->on('error', function () use ($connection) {
						$connection->close();
					});
					$connection->on('close', function () {
						$this->connection_to_master = null;
						sleep(1);
						$this->connect_to_master();
					});
				},
				function () use ($master, &$last_trial) {
					if ($last_trial == $master) {
						$this->pool->del($master);
					} else {
						$last_trial = $master;
					}
					sleep(1);
					$this->connect_to_master();
				}
			);
		} else {
			$last_trial = '';
		}
	}
	/**
	 * @param ConnectionInterface $connection
	 */
	function onClose (ConnectionInterface $connection) {
		// The connection is closed, remove it, as we can no longer send it messages
		$this->clients->detach($connection);
		$this->servers->detach($connection);
	}
	/**
	 * @param ConnectionInterface $connection
	 * @param Exception           $e
	 */
	function onError (ConnectionInterface $connection, Exception $e) {
		$connection->close();
	}
	function __destruct () {
		$this->pool->del($this->public_address);
	}
}
