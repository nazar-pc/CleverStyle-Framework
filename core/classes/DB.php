<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;

/**
 * @method static DB instance($check = false)
 */
class DB {
	use	Singleton;
	/**
	 * Total number of executed queries
	 * @var int
	 */
	public		$queries				= 0;
	/**
	 * Total time spent on all queries and connections
	 * @var int
	 */
	public		$time					= 0;
	/**
	 * @var array
	 */
	protected	$connections			= [];
	/**
	 * @var array
	 */
	protected	$successful_connections	= [];
	/**
	 * @var array
	 */
	protected	$failed_connections		= [];
	/**
	 * @var array
	 */
	protected	$mirrors				= [];
	/**
	 * Get list of connections of specified type
	 *
	 * @param	bool|null|string	$status	<b>null</b>		- returns array of successful connections with corresponding objects as values of array<br>
	 * 										<b>true|1</b>	- returns array of names of successful connections<br>
	 * 										<b>false|0</b>	- returns array of names of failed connections<br>
	 * 										<b>mirror</b>	- returns array of names of mirror connections
	 * @return	array|null
	 */
	function get_connections_list ($status = null) {
		if ($status === null) {
			return $this->connections;
		} elseif ($status == 0) {
			return $this->failed_connections;
		} elseif ($status == 1) {
			return $this->successful_connections;
		} elseif ($status == 'mirror') {
			return $this->mirrors;
		}
		return null;
	}
	/**
	 * Processing of requests for getting data from DB. Balancing of DB may be used with corresponding settings.
	 *
	 * @param	int								$connection
	 *
	 * @return	DB\_Abstract|False_class					Returns instance of False_class on failure
	 */
	function db ($connection) {
		if (!is_int($connection) && $connection != '0') {
			return False_class::instance();
		}
		$Config	= Config::instance(true);
		/**
		 * Try to find existing mirror connection
		 */
		if (isset($this->mirrors[$connection])) {
			return $this->mirrors[$connection];
		/**
		 * Try to find existing connection
		 */
		} elseif (isset($this->connections[$connection])) {
			return $this->connections[$connection];
		/**
		 * If DB balancing enabled - try to connect to the mirror
		 */
		} elseif ($Config && !empty($Config->core) && $Config->core['db_balance'] && $mirrors = count($Config->db[$connection]['mirrors'])) {
			$select = mt_rand(0, $Config->core['maindb_for_write'] ? $mirrors - 1 : $mirrors);
			if ($select < $mirrors) {
				$mirror = $Config->db[$connection]['mirrors'][--$select];
				$mirror_connection = $this->connecting($mirror['name'], $mirror);
				if (is_object($mirror_connection) && $mirror_connection->connected()) {
					$this->mirrors[$connection] = $mirror_connection;
					return $this->mirrors[$connection];
				} else {
					unset($mirror_connection);
					return $this->__call($connection, [true]);
				}
			} else {
				return $this->connecting($connection);
			}
		/**
		 * Connecting to the DB
		 */
		} else {
			return $this->connecting($connection);
		}
	}
	/**
	 * Processing of requests for getting data from DB. Balancing of DB may be used with corresponding settings.
	 *
	 * @param	int								$connection
	 *
	 * @return	DB\_Abstract|False_class					Returns instance of False_class on failure
	 */
	function __get ($connection) {
		return $this->db($connection);
	}
	/**
	 * Processing of requests for changing data in DB.
	 *
	 * @param	int								$connection
	 *
	 * @return	DB\_Abstract|False_class					Returns instance of False_class on failure
	 */
	function db_prime ($connection) {
		if (!is_int($connection) && $connection != '0') {
			return False_class::instance();
		}
		return $this->connecting($connection, false);
	}
	/**
	 * Processing of requests for changing data in DB, and direct queries to database.
	 *
	 * @param	int								$connection
	 * @param	array							$mode
	 *
	 * @return	DB\_Abstract|False_class					Returns instance of False_class on failure
	 */
	function __call ($connection, $mode) {
		if (is_int($connection) || $connection == '0') {
			return $this->connecting($connection, isset($mode[0]) ? (bool)$mode[0] : false);
		} elseif (method_exists('\\cs\\DB\\_Abstract', $connection)) {
			return call_user_func_array([$this->db(0), $connection], $mode);
		} else {
			return False_class::instance();
		}
	}
	/**
	 * Processing of all DB request
	 *
	 * @param int								$connection	Database id
	 * @param array|bool						$mirror
	 *
	 * @return DB\_Abstract|False_class
	 */
	protected function connecting ($connection, $mirror = true) {
		/**
		 * If connection found in list of failed connections - return instance of False_class
		 */
		if (isset($this->failed_connections[$connection])) {
			return False_class::instance();
		}
		/**
		 * If we want to get data and connection with DB mirror already exists - return reference on the instance of DB engine object
		 */
		if ($mirror === true && isset($this->mirrors[$connection])) {
			return $this->mirrors[$connection];
		}
		/**
		 * If connection already exists - return reference on the instance of DB engine object
		 */
		if (isset($this->connections[$connection])) {
			return $this->connections[$connection];
		}
		$Config							= Config::instance();
		$Core							= Core::instance();
		$L								= Language::instance();
		/**
		 * If connection to the core DB and it is not connection to the mirror
		 */
		if ($connection == 0 && !is_array($mirror)) {
			$db['type']		= $Core->db_type;
			$db['name']		= $Core->db_name;
			$db['user']		= $Core->db_user;
			$db['password']	= $Core->db_password;
			$db['host']		= $Core->db_host;
			$db['charset']	= $Core->db_charset;
			$db['prefix']	= $Core->db_prefix;
		} else {
			/**
			 * If it is connection to the DB mirror
			 */
			if (is_array($mirror)) {
				$db = &$mirror;
			} else {
				if (!isset($Config->db[$connection]) || !is_array($Config->db[$connection])) {
					return False_class::instance();
				}
				$db = &$Config->db[$connection];
			}
		}
		/**
		 * Create new DB connection
		 */
		$engine_class					= '\\cs\\DB\\'.$db['type'];
		$this->connections[$connection]	= new $engine_class($db['name'], $db['user'], $db['password'], $db['host'], $db['charset'], $db['prefix']);
		unset($engine_class);
		/**
		 * If successfully - add connection to the list of success connections and return instance of DB engine object
		 */
		if (is_object($this->connections[$connection]) && $this->connections[$connection]->connected()) {
			$this->successful_connections[] = ($connection == 0 ? $L->core_db.'('.$Core->db_type.')' : $connection).'/'.$db['host'].'/'.$db['type'];
			unset($db);
			$this->$connection = $this->connections[$connection];
			return $this->connections[$connection];
		/**
		 * If failed - add connection to the list of failed connections and try to connect to the DB mirror if it is allowed
		 */
		} else {
			unset($this->$connection);
			$this->failed_connections[$connection] = ($connection == 0 ? $L->core_db.'('.$Core->db_type.')' : $connection).'/'.$db['host'].'/'.$db['type'];
			unset($db);
			if (
				$mirror === true &&
				(
					($connection == 0 && isset($Config->db[0]['mirrors']) && is_array($Config->db[0]['mirrors']) && count($Config->db[0]['mirrors'])) ||
					(isset($Config->db[$connection]['mirrors']) && is_array($Config->db[$connection]['mirrors']) && count($Config->db[$connection]['mirrors']))
				)
			) {
				$dbx = ($connection == 0 ? $Config->db[0]['mirrors'] : $Config->db[$connection]['mirrors']);
				foreach ($dbx as $i => &$mirror_data) {
					$mirror_connection = $this->connecting($connection.' ('.$mirror_data['name'].')', $mirror_data);
					if (is_object($mirror_connection) && $mirror_connection->connected()) {
						$this->mirrors[$connection] = $mirror_connection;
						$this->$connection = $this->connections[$connection];
						return $this->mirrors[$connection];
					}
				}
				unset($dbx, $i, $mirror_data, $mirror_connection);
			}
			/**
			 * If mirror connection is not allowed - display connection error
			 */
			$return	= False_class::instance();
			if (!is_array($mirror)) {
				code_header(500);
				if ($connection == 0) {
					trigger_error($L->error_core_db, E_USER_ERROR);
				} else {
					trigger_error($L->error_db.' '.$this->failed_connections[$connection], E_USER_ERROR);
				}
				$return->error	= 'Connection failed';
			}
			return $return;
		}
	}
	/**
	 * Test connection to the DB
	 *
	 * @param int[]|string[] $data	Array or string in JSON format of connection parameters
	 *
	 * @return bool
	 */
	function test ($data) {
		$Core	= Core::instance();
		if (empty($data)) {
			return false;
		} elseif (is_array_indexed($data)) {
			$Config	= Config::instance();
			if (isset($data[1])) {
				$db = $Config->db[$data[0]]['mirrors'][$data[1]];
			} elseif (isset($data[0])) {
				if ($data[0] == 0) {
					$db = [
						'type'		=> $Core->db_type,
						'host'		=> $Core->db_host,
						'name'		=> $Core->db_name,
						'user'		=> $Core->db_user,
						'password'	=> $Core->db_password,
						'charset'	=> $Core->db_charset
					];
				} else {
					$db = $Config->db[$data[0]];
				}
			} else {
				return false;
			}
		} else {
			$db = $data;
		}
		unset($data);
		if (is_array($db)) {
			errors_off();
			$engine_class	= '\\cs\\DB\\'.$db['type'];
			$test			= new $engine_class(
				$db['name'],
				$db['user'],
				$db['password'],
				$db['host'] ?: $Core->db_host,
				$db['charset'] ?: $Core->db_charset
			);
			errors_on();
			return $test->connected();
		} else {
			return false;
		}
	}
}
