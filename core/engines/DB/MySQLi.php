<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs\DB;
class MySQLi extends _Abstract {
	/**
	 * @var \MySQLi Instance of DB connection
	 */
	protected	$instance;

	/**
	 * Connecting to the DB
	 *
	 * @param string	$database
	 * @param string	$user
	 * @param string	$password
	 * @param string	$host
	 * @param string	$charset
	 * @param string	$prefix
	 *
	 * @return bool|MySQLi
	 */
	function __construct ($database, $user = '', $password = '', $host = 'localhost', $charset = 'utf8', $prefix = '') {
		$this->connecting_time	= microtime(true);
		/**
		 * Parsing of $host variable, detecting port and persistent connection
		 */
		$host					= explode(':', $host);
		$port					= ini_get("mysqli.default_port");
		if (count($host) == 1) {
			$host	= $host[0];
		} elseif (count($host) == 2) {
			if ($host[0] == 'p') {
				$host	= $host[0].':'.$host[1];
			} else {
				$port	= $host[1];
				$host	= $host[0];
			}
		} elseif (count($host) == 3) {
			$port	= $host[2];
			$host	= $host[0].':'.$host[1];
		}
		$this->instance = new \MySQLi($host, $user, $password, $database, $port);
		if(is_object($this->instance) && !$this->instance->connect_errno) {
			$this->database = $database;
			/**
			 * Changing DB charset
			 */
			if ($charset && $charset != $this->instance->get_charset()->charset) {
				$this->instance->set_charset($charset);
			}
			$this->connected = true;
		} else {
			return false;
		}
		$this->connecting_time	= microtime(true) - $this->connecting_time;
		global $db;
		if (is_object($db)) {
			$db->time				+= $this->connecting_time;
		}
		$this->db_type			= 'mysql';
		$this->prefix			= $prefix;
		return $this;
	}

	/**
	 * SQL request into DB
	 *
	 * @abstract
	 *
	 * @param string|string[] $query
	 *
	 * @return bool|object|resource
	 */
	protected function q_internal ($query) {
		if ($this->async && defined('MYSQLI_ASYNC')) {
			return @$this->instance->query($query, MYSQLI_ASYNC);
		} else {
			return @$this->instance->query($query);
		}
	}
	/**
	 * Number
	 *
	 * Getting number of selected rows
	 *
	 * @param object 	$query_result
	 *
	 * @return bool|int
	 */
	function n ($query_result) {
		if(is_object($query_result)) {
			return $query_result->num_rows;
		} else {
			return false;
		}
	}
	/**
	 * Fetch
	 *
	 * Fetch a result row as an associative array
	 *
	 * @param object				$query_result
	 * @param bool					$single_column
	 * @param bool					$array
	 * @param bool					$indexed
	 *
	 * @return array|bool|string
	 */
	function f ($query_result, $single_column = false, $array = false, $indexed = false) {
		if ($single_column) {
			$result_type	= MYSQLI_NUM;
		} else {
			$result_type	= $indexed ? MYSQLI_NUM : MYSQLI_ASSOC;
		}
		if (is_object($query_result)) {
			if ($array) {
				$result = [];
				if ($single_column === false) {
					while ($current = $query_result->fetch_array($result_type)) {
						$result[] = $current;
					}
				} else {
					while ($current = $query_result->fetch_array($result_type)) {
						$result[] = $current[0];
					}
				}
				$this->free($query_result);
				return $result;
			} else {
				$result	= $query_result->fetch_array($result_type);
				if ($single_column && is_array($result)) {
					return $result[0];
				}
				return $result;
			}
		} else {
			return false;
		}
	}
	/**
	 * Id
	 *
	 * Get id of last inserted row
	 *
	 * @return int
	 */
	function id () {
		return $this->instance->insert_id;
	}
	/**
	 * Affected
	 *
	 * Get number of affected rows during last query
	 *
	 * @return int
	 */
	function affected () {
		return $this->instance->affected_rows;
	}
	/**
	 * Free result memory
	 *
	 * @param object	$query_result
	 *
	 * @return bool
	 */
	function free ($query_result) {
		if(is_object($query_result)) {
			return $query_result->free();
		} else {
			return false;
		}
	}
	/**
	 * Preparing string for using in SQL query
	 * SQL Injection Protection
	 *
	 * @param string	$string
	 * @param bool		$single_quotes_around
	 *
	 * @return string
	 */
	protected function s_internal ($string, $single_quotes_around) {
		$return	= $this->instance->real_escape_string($string);
		return $single_quotes_around ? "'$return'" : $return;
	}
	/**
	 * Get information about server
	 *
	 * @return string
	 */
	function server () {
		return $this->instance->server_info;
	}
	/**
	 * Disconnecting from DB
	 */
	function __destruct () {
		if($this->connected && is_object($this->instance)) {
			$this->instance->close();
			$this->connected = false;
		}
	}
}