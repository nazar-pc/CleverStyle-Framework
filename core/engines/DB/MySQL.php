<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\DB;
use			cs\DB;
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
		$db						= DB::instance();
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
	 * Number
	 *
	 * Getting number of selected rows
	 *
	 * @param resource $query_result
	 *
	 * @return bool|int
	 */
	function n ($query_result) {
		if(is_resource($query_result)) {
			return mysql_num_rows($query_result);
		} else {
			return false;
		}
	}
	/**
	 * Fetch
	 *
	 * Fetch a result row as an associative array
	 *
	 * @param resource				$query_result
	 * @param bool					$single_column
	 * @param bool $array
	 * @param bool					$indexed
	 *
	 * @return array|bool|string
	 */
	function f ($query_result, $single_column = false, $array = false, $indexed = false) {
		if ($single_column) {
			$result_type	= MYSQL_NUM;
		} else {
			$result_type	= $indexed ? MYSQL_NUM : MYSQL_ASSOC;
		}
		if (is_resource($query_result)) {
			if ($array) {
				$result = [];
				if ($single_column === false) {
					while ($current = mysql_fetch_array($query_result, $result_type)) {
						$result[] = $current;
					}
				} else {
					while ($current = mysql_fetch_array($query_result, $result_type)) {
						$result[] = $current[0];
					}
				}
				$this->free($query_result);
				return $result;
			} else {
				$result	= mysql_fetch_array($query_result, $result_type);
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
		return mysql_insert_id($this->id);
	}
	/**
	 * Affected
	 *
	 * Get number of affected rows during last query
	 *
	 * @return int
	 */
	function affected () {
		return mysql_affected_rows($this->id);
	}
	/**
	 * Free result memory
	 *
	 * @param resource $query_result
	 *
	 * @return bool
	 */
	function free ($query_result) {
		if(is_resource($query_result)) {
			return mysql_free_result($query_result);
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
		$return	= mysql_real_escape_string($string, $this->id);
		return $single_quotes_around ? "'$return'" : $return;
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
			mysql_close($this->id);
			$this->connected = false;
		}
	}
}
