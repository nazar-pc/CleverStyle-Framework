<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;

/**
 * @method static Storage instance($check = false)
 */
class Storage {
	use	Singleton;
	protected	$connections			= [];
	protected	$successful_connections	= [];
	protected	$failed_connections		= [];
	/**
	 * Get list of connections of specified type
	 *
	 * @param	bool|null|string $status	<b>null</b>		- returns array of connections with objects<br>
	 * 										<b>true|1</b>	- returns array of names of successful connections<br>
	 * 										<b>false|0</b>	- returns array of names of failed connections<br>
	 * @return	array|null
	 */
	function get_connections_list ($status = null) {
		if ($status === null) {
			return $this->connections;
		} elseif ($status == 0) {
			return $this->failed_connections;
		} elseif ($status == 1) {
			return $this->successful_connections;
		}
		return null;
	}
	/**
	 * Processing of getting storage instance
	 *
	 * @param	int								$connection
	 * @return	Storage\_Abstract|False_class
	 */
	function storage ($connection) {
		if (!is_int($connection) && $connection != '0') {
			return False_class::instance();
		}
		return $this->connecting($connection);
	}
	/**
	 * Processing of getting storage instance
	 *
	 * @param	int								$connection
	 * @return	Storage\_Abstract|False_class
	 */
	function __get ($connection) {
		return $this->storage($connection);
	}
	/**
	 * Processing of all storage requests
	 *
	 * @param int								$connection
	 *
	 * @return Storage\_Abstract|False_class
	 */
	protected function connecting ($connection) {
		/**
		 * If connection found in list of failed connections - return instance of False_class
		 */
		if (isset($this->failed_connections[$connection])) {
			return False_class::instance();
		}
		/**
		 * If connection already exists - return reference on the instance of Storage engine object
		 */
		if (isset($this->connections[$connection])) {
			return $this->connections[$connection];
		}
		$Config							= Config::instance();
		/**
		 * If connection to the local storage
		 */
		if ($connection == 0) {
			$Core					= Core::instance();
			$storage['connection']	= $Core->storage_type;
			$storage['url']			= $Core->storage_url;
			$storage['host']		= $Core->storage_host;
			$storage['user']		= $Core->storage_user;
			$storage['password']	= $Core->storage_password;
		} elseif (isset($Config->storage[$connection])) {
			$storage = &$Config->storage[$connection];
		} else {
			return False_class::instance();
		}
		/**
		 * Create new Storage connection
		 */
		$engine_class					= "\\cs\\Storage\\$storage[connection]";
		$this->connections[$connection]	= new $engine_class($storage['url'], $storage['host'], $storage['user'], $storage['password']);
		/**
		 * If successfully - add connection to the list of success connections and return instance of DB engine object
		 */
		if (is_object($this->connections[$connection]) && $this->connections[$connection]->connected()) {
			$this->successful_connections[] = "$connection/$storage[host]/$storage[connection]";
			unset($storage);
			$this->$connection = $this->connections[$connection];
			return $this->connections[$connection];
		/**
		 * If failed - add connection to the list of failed connections and display connection error
		 */
		} else {
			unset($this->$connection);
			$this->failed_connections[$connection] = "$connection/$storage[host]/$storage[connection]";
			unset($storage);
			trigger_error(Language::instance()->error_storage.' '.$this->failed_connections[$connection], E_USER_WARNING);
			$return			= False_class::instance();
			$return->error	= 'Connection failed';
			return $return;
		}
	}
	/**
	 * Test connection to the Storage
	 *
	 * @param bool|int[]|string[] $data	Array or string in JSON format of connection parameters
	 *
	 * @return bool
	 */
	function test ($data = false) {
		if (empty($data)) {
			return false;
		} elseif (is_array_indexed($data)) {
			if (isset($data[0])) {
				$storage = Config::instance()->storage[$data[0]];
			} else {
				return false;
			}
		} else {
			$storage = $data;
		}
		unset($data);
		if (is_array($storage)) {
			$connection_class	= "\\cs\\Storage\\$storage[connection]";
			$test				= new $connection_class($storage['url'], $storage['host'], $storage['user'], $storage['password']);
			return $test->connected();
		} else {
			return false;
		}
	}
}
