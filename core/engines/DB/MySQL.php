<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs\DB;
class MySQL extends _Abstract {
	/**
	 * @var resource MySQL link identifier
	 */
	protected	$id;

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
	 * @return bool|MySQL
	 */
	function __construct ($database, $user = '', $password = '', $host = 'localhost', $charset = 'utf8', $prefix = '') {
		$this->connecting_time = microtime(true);
		$this->id = mysql_connect($host, $user, $password);
		if(is_resource($this->id)) {
			if(!mysql_select_db($database, $this->id)) {
				return false;
			}
			$this->database = $database;
			/**
			 * Changing DB charset
			 */
			if ($charset && $charset != mysql_client_encoding($this->id)) {
				mysql_set_charset($charset, $this->id);
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
		return @mysql_query($query, $this->id);
	}
	/**
	 * Getting number of selected rows
	 *
	 * @param bool|resource $query_result
	 *
	 * @return bool|int
	 */
	function n ($query_result = false) {
		if($query_result === false) {
			$query_result = $this->queries['result'][count($this->queries['result'])-1];
		}
		if(is_resource($query_result)) {
			return mysql_num_rows($query_result);
		} else {
			return (bool)$query_result;
		}
	}
	/**
	 * Fetch a result row as an associative array
	 *
	 * @param bool|resource $query_result
	 * @param bool|string   $one_column
	 * @param bool $array
	 *
	 * @return array|bool
	 */
	function f ($query_result = false, $one_column = false, $array = false) {
		if ($query_result === false) {
			$query_result = $this->queries['result'][count($this->queries['result'])-1];
		}
		if (is_resource($query_result)) {
			if ($array) {
				$result = [];
				if ($one_column === false) {
					while ($current = mysql_fetch_assoc($query_result)) {
						$result[] = $current;
					}
				} else {
					$one_column = (string)$one_column;
					while ($current = mysql_fetch_assoc($query_result)) {
						$result[] = $current[$one_column];
					}
				}
				$this->free($query_result);
				return $result;
			} else {
				$result	= mysql_fetch_assoc($query_result);
				if ($one_column && is_array($result)) {
					return $result[$one_column];
				}
				return $result;
			}
		} else {
			return (bool)$query_result;
		}
	}
	/**
	 * Get id of last inserted row
	 *
	 * @return int
	 */
	function id () {
		return mysql_insert_id($this->id);
	}
	/**
	 * Free result memory
	 *
	 * @param bool|resource $query_result
	 *
	 * @return bool
	 */
	function free ($query_result = false) {
		if($query_result === false) {
			$query_result = $this->queries['result'][count($this->queries['result'])-1];
		}
		if(is_resource($query_result)) {
			return mysql_free_result($query_result);
		} else {
			return (bool)$query_result;
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
		$return	= mysql_real_escape_string($string, $this->id);
		return $single_quotes_around ? "'$return'" : $return;
		//return 'unhex(\''.bin2hex((string)$string).'\')';
	}
	/**
	 * Get information about server
	 *
	 * @return string
	 */
	function server () {
		return mysql_get_server_info($this->id);
	}
	/**
	 * Disconnecting from DB
	 */
	function __destruct () {
		if($this->connected && is_resource($this->id)) {
			if (is_array($this->queries['result'])) {
				foreach ($this->queries['result'] as &$resource) {
					if (is_resource($resource)) {
						mysql_free_result($resource);
						$resource = false;
					}
				}
			}
			mysql_close($this->id);
			$this->connected = false;
		}
	}
}