<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

/**
 * @method static $this instance($check = false)
 */
class Storage {
	use
		Singleton;
	const CONNECTIONS_ACTIVE     = 0;
	const CONNECTIONS_SUCCESSFUL = 1;
	const CONNECTIONS_FAILED     = 2;
	/**
	 * @var array[]
	 */
	protected $connections = [
		self::CONNECTIONS_ACTIVE     => [],
		self::CONNECTIONS_SUCCESSFUL => [],
		self::CONNECTIONS_FAILED     => []
	];
	/**
	 * Get list of connections of specified type
	 *
	 * @param int $type One of constants `self::CONNECTIONS_*`
	 *
	 * @return array For `self::CONNECTIONS_ACTIVE` array of successful connections with corresponding objects as values of array, otherwise array where keys
	 *               are storage ids and values are strings with information about storage
	 */
	public function get_connections_list ($type) {
		return $this->connections[$type];
	}
	/**
	 * Processing of getting storage instance
	 *
	 * @param int $storage_id
	 *
	 * @return Storage\_Abstract|False_class
	 */
	public function storage ($storage_id) {
		/**
		 * If connection found in list of failed connections - return instance of False_class
		 */
		if (isset($this->connections[self::CONNECTIONS_FAILED][$storage_id])) {
			return False_class::instance();
		}
		/**
		 * If connection already exists - return reference on the instance of Storage driver object
		 */
		if (isset($this->connections[self::CONNECTIONS_ACTIVE][$storage_id])) {
			return $this->connections[self::CONNECTIONS_ACTIVE][$storage_id];
		}
		/**
		 * If connection to the local storage
		 */
		if ($storage_id == 0) {
			$Core    = Core::instance();
			$storage = [
				'connection' => $Core->storage_type,
				'url'        => $Core->storage_url,
				'host'       => $Core->storage_host,
				'user'       => $Core->storage_user,
				'password'   => $Core->storage_password
			];
		} else {
			$storage = Config::instance()->storage[$storage_id];
		}
		/**
		 * Establish new connection
		 *
		 * @var Storage\_Abstract $connection
		 */
		$driver_class    = "\\cs\\Storage\\$storage[connection]";
		$connection      = new $driver_class(
			$storage['url'],
			$storage['host'],
			$storage['user'],
			$storage['password']
		);
		$connection_name = "$storage_id/$storage[host]/$storage[connection]";
		/**
		 * If successfully - add connection to the list of success connections and return instance of Storage driver object
		 */
		if (is_object($connection) && $connection->connected()) {
			$this->connections[self::CONNECTIONS_ACTIVE][$storage_id] = $connection;
			$this->connections[self::CONNECTIONS_SUCCESSFUL][]        = $connection_name;
			return $connection;
		}
		/**
		 * If failed - add connection to the list of failed connections and log error
		 */
		$this->connections[self::CONNECTIONS_FAILED][$storage_id] = $connection_name;
		trigger_error("Error connecting to storage $connection_name", E_USER_WARNING);
		return False_class::instance();
	}
}
