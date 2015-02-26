<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\DB;
class MySQLi extends _Abstract {
	/**
	 * @var \MySQLi Instance of DB connection
	 */
	protected $instance;
	/**
	 * @inheritdoc
	 */
	function __construct ($database, $user = '', $password = '', $host = 'localhost', $charset = 'utf8', $prefix = '') {
		$this->connecting_time = microtime(true);
		/**
		 * Parsing of $host variable, detecting port and persistent connection
		 */
		$host = explode(':', $host);
		$port = ini_get('mysqli.default_port') ?: 3306;
		if (count($host) == 1) {
			$host = $host[0];
		} elseif (count($host) == 2) {
			if ($host[0] == 'p') {
				$host = "$host[0]:$host[1]";
			} else {
				$port = $host[1];
				$host = $host[0];
			}
		} elseif (count($host) == 3) {
			$port = $host[2];
			$host = "$host[0]:$host[1]";
		}
		$this->instance = @new \MySQLi($host, $user, $password, $database, $port);
		if (is_object($this->instance) && !$this->instance->connect_errno) {
			$this->database = $database;
			/**
			 * Changing DB charset
			 */
			if ($charset && $charset != $this->instance->character_set_name()) {
				$this->instance->set_charset($charset);
			}
			$this->connected = true;
		} else {
			return;
		}
		$this->connecting_time = microtime(true) - $this->connecting_time;
		$this->db_type         = 'mysql';
		$this->prefix          = $prefix;
	}
	/**
	 * @inheritdoc
	 */
	protected function q_internal ($query) {
		if ($this->async && defined('MYSQLI_ASYNC')) {
			$result = @$this->instance->query($query, MYSQLI_ASYNC);
			// In case of MySQL Client error - try to fix everything, but only once
			if (
				!$result &&
				$this->instance->errno >= 2000 &&
				$this->instance->ping()
			) {
				$result = @$this->instance->query($query, MYSQLI_ASYNC);
			}
			return $result;
		} else {
			$result = @$this->instance->query($query);
			// In case of MySQL Client error - try to fix everything, but only once
			if (
				!$result &&
				$this->instance->errno >= 2000 &&
				$this->instance->ping()
			) {
				$result = @$this->instance->query($query);
			}
			return $result;
		}
	}
	/**
	 * @inheritdoc
	 */
	protected function q_multi_internal ($query) {
		$query  = implode(';', $query);
		$return = @$this->instance->multi_query($query);
		while ($this->instance->more_results() && $this->instance->next_result()) {
			//Nothing, just finish multi_query
		}
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function n ($query_result) {
		if (is_object($query_result)) {
			return $query_result->num_rows;
		} else {
			return false;
		}
	}
	/**
	 * @inheritdoc
	 */
	function f ($query_result, $single_column = false, $array = false, $indexed = false) {
		if ($single_column) {
			$result_type = MYSQLI_NUM;
		} else {
			$result_type = $indexed ? MYSQLI_NUM : MYSQLI_ASSOC;
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
				$result = $query_result->fetch_array($result_type);
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
	 * @inheritdoc
	 */
	function id () {
		return $this->instance->insert_id;
	}
	/**
	 * @inheritdoc
	 */
	function affected () {
		return $this->instance->affected_rows;
	}
	/**
	 * @inheritdoc
	 */
	function free ($query_result) {
		if (is_object($query_result)) {
			return $query_result->free();
		} else {
			return false;
		}
	}
	/**
	 * @inheritdoc
	 */
	protected function s_internal ($string, $single_quotes_around) {
		$return = $this->instance->real_escape_string($string);
		return $single_quotes_around ? "'$return'" : $return;
	}
	/**
	 * @inheritdoc
	 */
	function server () {
		return $this->instance->server_info;
	}
	/**
	 * @inheritdoc
	 */
	function __destruct () {
		if ($this->connected && is_object($this->instance)) {
			$this->instance->close();
			$this->connected = false;
		}
	}
}
