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
	Ratchet\Client\Connector as Client_connector,
	Ratchet\Client\WebSocket as Client_websocket,
	Ratchet\ConnectionInterface,
	Ratchet\Http\HttpServer,
	Ratchet\MessageComponentInterface,
	Ratchet\Server\IoServer,
	Ratchet\WebSocket\WsServer,
	React\Dns\Resolver\Factory as Dns_factory,
	React\EventLoop\Factory as Loop_factory,
	cs\Config,
	cs\Event,
	cs\Request,
	cs\Session,
	cs\Singleton,
	cs\User,
	Exception,
	SplObjectStorage;

class Server implements MessageComponentInterface {
	use
		Singleton;
	/**
	 * Message will be delivered to everyone
	 */
	const SEND_TO_ALL = 1;
	/**
	 * Message will be delivered to registered users only
	 */
	const SEND_TO_REGISTERED_USERS = 2;
	/**
	 * Message will be delivered to users, specified in target (might be array of users)
	 */
	const SEND_TO_SPECIFIC_USERS = 3;
	/**
	 * Message will be delivered to users from group, specified in target (might be array of groups)
	 */
	const SEND_TO_USERS_GROUP = 4;
	/**
	 * Message will be delivered to users whose connection objects have property with certain value, target should be an array with format [property, value]
	 */
	const SEND_TO_FILTER = 5;
	/**
	 * Each object additionally will have properties `user_id`, `session_id`, `session_expire` and `user_groups` with user id and ids of user groups
	 * correspondingly
	 *
	 * @var ConnectionInterface[]|SplObjectStorage
	 */
	protected $clients;
	/**
	 * @var ConnectionInterface[]|SplObjectStorage
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
	 * Address to WebSockets server in format wss://server/WebSockets or ws://server/WebSockets, so that one WebSockets server can reach another (in case of
	 * several servers)
	 *
	 * @var string
	 */
	protected $address;
	/**
	 * @var IoServer
	 */
	protected $io_server;
	/**
	 * @var \React\EventLoop\LoopInterface
	 */
	protected $loop;
	/**
	 * @var Client_connector
	 */
	protected $client_connector;
	/**
	 * @var int
	 */
	protected $listen_port;
	/**
	 * @var string
	 */
	protected $listen_locally;
	/**
	 * @var string
	 */
	protected $dns_server;
	/**
	 * @var string
	 */
	protected $security_key;
	/**
	 * @var bool
	 */
	protected $remember_session_ip;
	protected function construct () {
		$Config                    = Config::instance();
		$Request                   = Request::instance();
		$module_data               = $Config->module('WebSockets');
		$this->listen_port         = $module_data->listen_port;
		$this->listen_locally      = $module_data->listen_locally ? '127.0.0.1' : '0.0.0.0';
		$this->dns_server          = $module_data->dns_server ?: '127.0.0.1';
		$this->dns_server          = $module_data->security_key;
		$this->remember_session_ip = $Config->core['remember_user_ip'];
		$this->pool                = Pool::instance();
		$this->clients             = new SplObjectStorage;
		$this->servers             = new SplObjectStorage;
		$this->address             = ($Request->secure ? 'wss' : 'ws')."://$Request->host/WebSockets";
	}
	/**
	 * Run WebSockets server
	 *
	 * @param null|string $address
	 */
	function run ($address = null) {
		$this->address = $address ?: $this->address;
		@ini_set('error_log', LOGS.'/WebSockets-server.log');
		$ws_server = new WsServer($this);
		// No encoding check - better performance, browsers do this anyway
		$ws_server->setEncodingChecks(false);
		// Disable all versions except RFC6455, which is supported by all modern browsers
		$ws_server->disableVersion(0);
		$ws_server->disableVersion(6);
		$this->io_server        = IoServer::factory(
			new HttpServer(
				new Connection_properties_injector($ws_server)
			),
			$this->listen_port,
			$this->listen_locally
		);
		$this->loop             = $this->io_server->loop;
		$this->client_connector = new Client_connector(
			$this->loop,
			(new Dns_factory)->create(
				$this->dns_server,
				$this->loop
			)
		);
		$this->connect_to_master();
		// Since we may work with a lot of different users - disable this cache in order to not run out of memory
		User::instance()->disable_memory_cache();
		$this->loop->run();
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
		if (!$this->parse_message($message, $action, $details, $send_to, $target)) {
			if (!$from_master) {
				$connection->close();
			}
			return;
		}
		switch ($action) {
			/**
			 * Connection to master server as server (by default all connections considered as clients)
			 */
			case "Server/connect:$this->security_key":
				/**
				 * Under certain circumstances it may happen so that one server become available through multiple addresses,
				 * in this case we need to remove one of them from list of pools
				 */
				if ($details['from_slave'] === $this->address) {
					$this->pool->del($details['to_master']);
					$connection->close();
				} else {
					$this->clients->detach($connection);
					$this->servers->attach($connection);
				}
				return;
			/**
			 * Internal connection from application
			 */
			case "Application/Internal:$this->security_key":
				/** @noinspection PhpUndefinedFieldInspection */
				if ($this->parse_message($details, $action_, $details_, $send_to_, $target_)) {
					$connection->close();
					$this->send_to_clients($action_, $details_, $send_to_, $target_);
				}
				return;
			case 'Client/authentication':
				$Session = Session::instance();
				/** @noinspection PhpUndefinedFieldInspection */
				$session = $Session->get($connection->session_id);
				/** @noinspection PhpUndefinedFieldInspection */
				if (
					!$session ||
					!$Session->is_session_owner($session['id'], $connection->user_agent, $connection->remote_addr, $connection->ip)
				) {
					$connection->send(
						_json_encode(['Client/authentication:error', $this->compose_error(403)])
					);
					$connection->close();
					return;
				}
				$connection->user_id        = $session['user'];
				$connection->session_id     = $session['id'];
				$connection->session_expire = $session['expire'];
				$connection->groups         = User::instance()->get_groups($session['user']);
				$connection->send(
					_json_encode(['Client/authentication', 'ok'])
				);
		}
		if ($from_master) {
			$this->send_to_clients_internal($action, $details, $send_to, $target);
		} elseif ($this->servers->contains($connection)) {
			$this->broadcast_message_to_servers($message, $connection);
			if (!$send_to) {
				return;
			}
			$this->send_to_clients_internal($action, $details, $send_to, $target);
		} elseif (isset($connection->user_id)) {
			/** @noinspection PhpUndefinedFieldInspection */
			Event::instance()->fire(
				"WebSockets/message",
				[
					'action'     => $action,
					'details'    => $details,
					'language'   => $connection->language,
					'user'       => $connection->user_id,
					'session'    => $connection->session_id,
					'connection' => $connection
				]
			);
		}
	}
	/**
	 * @param string    $message
	 * @param string    $action
	 * @param mixed     $details
	 * @param int|int[] $send_to
	 * @param int       $target
	 *
	 * @return bool
	 */
	protected function parse_message ($message, &$action, &$details, &$send_to, &$target) {
		$decoded_message = _json_decode($message);
		if (
			!is_array($decoded_message) ||
			!array_key_exists(0, $decoded_message) ||
			!array_key_exists(1, $decoded_message)
		) {
			return false;
		}
		list($action, $details) = $decoded_message;
		$send_to = isset($decoded_message[2]) ? $decoded_message[2] : 0;
		$target  = isset($decoded_message[3]) ? $decoded_message[3] : false;
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
	 * Compose error
	 *
	 * @param int         $error_code    HTTP status code
	 * @param null|string $error_message String representation of status code
	 *
	 * @return array Array to be passed as details to `::send_to_clients()`
	 */
	function compose_error ($error_code, $error_message = null) {
		$error_message = $error_message ?: status_code_string($error_code);
		return [$error_code, $error_message];
	}
	/**
	 * Send request to client
	 *
	 * @param string          $action
	 * @param mixed           $details
	 * @param int             $send_to Constants `self::SEND_TO*` should be used here
	 * @param false|int|int[] $target  Id or array of ids in case of response to one or several users or groups
	 */
	function send_to_clients ($action, $details, $send_to, $target = false) {
		$message = _json_encode([$action, $details, $send_to, $target]);
		/**
		 * If server running in current process
		 */
		if ($this->io_server) {
			if ($this->connection_to_master) {
				$this->connection_to_master->send($message);
			} else {
				$this->broadcast_message_to_servers($message);
			}
			$this->send_to_clients_internal($action, $details, $send_to, $target);
			return;
		}
		$servers = $this->pool->get_all();
		if ($servers) {
			shuffle($servers);
			$loop      = Loop_factory::create();
			$connector = new Client_connector($loop);
			$connector($servers[0])->then(
				function (Client_websocket $connection) use ($message) {
					$connection->send(
						_json_encode(["Application/Internal:$this->security_key", $message])
					);
					// Connection will be closed by server itself, no need to stop loop here
				},
				function () use ($loop) {
					$loop->stop();
				}
			);
			$loop->run();
		}
	}
	/**
	 * Send request to client
	 *
	 * @param string                  $action
	 * @param mixed                   $details
	 * @param int                     $send_to Constants `self::SEND_TO_*` should be used here
	 * @param false|int|int[]|mixed[] $target  Id or array of ids in case of response to one or several users or groups, [property, value] for filter
	 */
	protected function send_to_clients_internal ($action, $details, $send_to, $target = false) {
		$message = _json_encode([$action, $details]);
		/**
		 * Special system actions
		 */
		switch ($action) {
			case 'Server/close_by_session':
				foreach ($this->clients as $client) {
					if ($client->session_id == $details) {
						$client->send(_json_encode('Server/close'));
						$client->close();
					}
				}
				return;
			case 'Server/close_by_user':
				foreach ($this->clients as $client) {
					if ($client->user_id == $details) {
						$client->send(_json_encode('Server/close'));
						$client->close();
					}
				}
				return;
		}
		switch ($send_to) {
			case self::SEND_TO_ALL:
				foreach ($this->clients as $client) {
					$client->send($message);
				}
				break;
			case self::SEND_TO_REGISTERED_USERS:
				foreach ($this->clients as $client) {
					if (isset($client->user_id)) {
						$this->send_to_client_if_not_expire($client, $message);
					}
				}
				break;
			case self::SEND_TO_SPECIFIC_USERS:
				$target = (array)$target;
				foreach ($this->clients as $client) {
					if (isset($client->user_id) && in_array($client->user_id, $target)) {
						$this->send_to_client_if_not_expire($client, $message);
					}
				}
				break;
			case self::SEND_TO_USERS_GROUP:
				$target = (array)$target;
				foreach ($this->clients as $client) {
					if (isset($client->user_groups) && array_intersect($client->user_groups, $target)) {
						$this->send_to_client_if_not_expire($client, $message);
					}
				}
				break;
			case self::SEND_TO_FILTER:
				list($property, $value) = $target;
				foreach ($this->clients as $client) {
					if (isset($client->$property) && $client->$property === $value) {
						$this->send_to_client_if_not_expire($client, $message);
					}
				}
				break;
		}
	}
	/**
	 * If session not expire - will send message, otherwise will disconnect
	 *
	 * @param ConnectionInterface $client
	 * @param string              $message
	 */
	protected function send_to_client_if_not_expire ($client, $message) {
		/** @noinspection PhpUndefinedFieldInspection */
		if ($client->session_expire >= time()) {
			$client->send($message);
		} else {
			$client->close();
		}
	}
	/**
	 * Close all client connections by specified session id
	 *
	 * @param string $session_id
	 */
	function close_by_session ($session_id) {
		$this->send_to_clients('Server/close_by_session', $session_id, 0);
	}
	/**
	 * Close all client connections by specified user id
	 *
	 * @param string $user_id
	 */
	function close_by_user ($user_id) {
		$this->send_to_clients('Server/close_by_user', $user_id, 0);
	}
	/**
	 * Connect to master server
	 *
	 * Two trials, if server do not respond twice - it will be removed from servers pool, and next server will become master
	 */
	protected function connect_to_master () {
		static $last_trial = '';
		// Add server to connections pool and connect to master if any
		$this->pool->add($this->address);
		$master = $this->pool->get_master();
		if ($master && $master != $this->address) {
			call_user_func($this->client_connector, $master)->then(
				function (Client_websocket $connection) use (&$last_trial, $master) {
					$last_trial                 = '';
					$this->connection_to_master = $connection;
					$connection->on(
						'message',
						function ($message) use ($connection) {
							$this->onMessage($connection, $message);
						}
					);
					$connection->on(
						'error',
						function () use ($connection) {
							$connection->close();
						}
					);
					$connection->on(
						'close',
						function () {
							$this->connection_to_master = null;
							$this->loop->addTimer(
								1,
								function () {
									$this->connect_to_master();
								}
							);
						}
					);
					/**
					 * Tell master that we are server also, not regular client
					 */
					$connection->send(
						_json_encode(
							[
								"Server/connect:$this->security_key",
								[
									'to_master'  => $master,
									'from_slave' => $this->address
								]
							]
						)
					);
				},
				function () use (&$last_trial, $master) {
					if ($last_trial == $master) {
						$this->pool->del($master);
					} else {
						$last_trial = $master;
					}
					$this->loop->addTimer(
						1,
						function () {
							$this->connect_to_master();
						}
					);
					$this->connect_to_master();
				}
			);
		} else {
			$last_trial = '';
			/**
			 * Sometimes other servers may loose connection with master server, so new master will be selected and we need to handle this nicely
			 */
			$this->loop->addTimer(
				30,
				function () {
					$this->connect_to_master();
				}
			);
		}
	}
	/**
	 * Get event loop instance
	 *
	 * @return \React\EventLoop\LoopInterface
	 */
	function get_loop () {
		return $this->loop;
	}
	/**
	 * @param ConnectionInterface $connection
	 */
	function onClose (ConnectionInterface $connection) {
		/**
		 * Generate pseudo-event when client is disconnected
		 */
		if (isset($connection->user_id) && $this->clients->contains($connection)) {
			/** @noinspection PhpUndefinedFieldInspection */
			Event::instance()->fire(
				"WebSockets/message",
				[
					'action'     => 'Client/disconnection',
					'details'    => null,
					'language'   => $connection->language,
					'user'       => $connection->user_id,
					'session'    => $connection->session_id,
					'connection' => $connection
				]
			);
		}
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
		$this->pool->del($this->address);
	}
}
