<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
namespace cs\DB;
use
	mysqli_result,
	mysqli_stmt;

class MySQLi extends _Abstract {
	/**
	 * @var \MySQLi Instance of DB connection
	 */
	protected $instance;
	/**
	 * @inheritdoc
	 */
	public function __construct ($database, $user = '', $password = '', $host = 'localhost', $prefix = '') {
		$start = microtime(true);
		/**
		 * Parsing of $host variable, detecting port and persistent connection
		 */
		list($host, $port) = $this->get_host_and_port($host);
		$this->instance = new \MySQLi($host, $user, $password, $database, $port);
		if (!is_object($this->instance) || $this->instance->connect_errno) {
			return;
		}
		$this->database = $database;
		/**
		 * Changing DB charset if necessary
		 */
		if ($this->instance->character_set_name() != 'utf8mb4') {
			$this->instance->set_charset('utf8mb4');
		}
		/**
		 * Force strict mode
		 */
		$this->instance->query(
			"SET SESSION sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'"
		);
		$this->connected       = true;
		$this->connecting_time = microtime(true) - $start;
		$this->db_type         = 'mysql';
		$this->prefix          = $prefix;
	}
	/**
	 * Parse host string into host and port separately
	 *
	 * Understands `p:` prefix for persistent connections
	 *
	 * @param string $host_string
	 *
	 * @return array
	 */
	protected function get_host_and_port ($host_string) {
		$host = explode(':', $host_string);
		$port = ini_get('mysqli.default_port') ?: 3306;
		switch (count($host)) {
			case 1:
				$host = $host[0];
				break;
			case 2:
				if ($host[0] == 'p') {
					$host = "$host[0]:$host[1]";
				} else {
					list($host, $port) = $host;
				}
				break;
			case 3:
				$port = $host[2];
				$host = "$host[0]:$host[1]";
		}
		return [$host, $port];
	}
	/**
	 * @inheritdoc
	 *
	 * @return bool|mysqli_result|mysqli_stmt
	 */
	protected function q_internal ($query, $parameters = []) {
		if (!$query) {
			return false;
		}
		$stmt = $this->q_internal_once($query, $parameters);
		/*
		 * In case of MySQL Client error try once again
		 */
		if (
			!$stmt &&
			$this->instance->errno >= 2000 &&
			$this->instance->ping()
		) {
			$stmt = $this->q_internal_once($query, $parameters);
		}
		return $stmt;
	}
	/**
	 * @param string $query
	 * @param array  $parameters
	 *
	 * @return bool|mysqli_result|mysqli_stmt
	 */
	protected function q_internal_once ($query, $parameters) {
		if (!$parameters) {
			return $this->instance->query($query);
		}
		$stmt = $this->instance->prepare($query);
		if (!$stmt) {
			return false;
		}
		// Allows to provide more parameters for prepared statements than needed
		$local_parameters = array_slice($parameters, 0, substr_count($query, '?'));
		if (!$this->q_internal_once_bind_param($stmt, $local_parameters)) {
			return false;
		}
		$result = $stmt->execute();
		/**
		 * Return result only for SELECT queries, boolean otherwise
		 */
		return $result && strpos($query, 'SELECT') === 0 ? $stmt : $result;
	}
	/**
	 * @param mysqli_stmt $stmt
	 * @param array       $local_parameters
	 *
	 * @return bool
	 */
	protected function q_internal_once_bind_param ($stmt, $local_parameters) {
		return $stmt->bind_param(
			str_repeat('s', count($local_parameters)),
			...$local_parameters
		);
	}
	/**
	 * @inheritdoc
	 *
	 * @param false|mysqli_result|mysqli_stmt $query_result_stmt
	 */
	public function f ($query_result_stmt, $single_column = false, $array = false, $indexed = false) {
		if ($query_result_stmt instanceof mysqli_result) {
			return $this->f_result($query_result_stmt, $single_column, $array, $indexed);
		}
		if ($query_result_stmt instanceof mysqli_stmt) {
			return $this->f_stmt($query_result_stmt, $single_column, $array, $indexed);
		}
		return false;
	}
	/**
	 * @param mysqli_result $query_result
	 * @param bool          $single_column
	 * @param bool          $array
	 * @param bool          $indexed
	 *
	 * @return array|bool|mixed
	 */
	protected function f_result ($query_result, $single_column, $array, $indexed) {
		$result_type = $single_column || $indexed ? MYSQLI_NUM : MYSQLI_ASSOC;
		if ($array) {
			$result = [];
			while ($current = $query_result->fetch_array($result_type)) {
				$result[] = $single_column ? $current[0] : $current;
			}
			$this->free($query_result);
			return $result;
		}
		$result = $query_result->fetch_array($result_type);
		if ($result === null) {
			$result = false;
		}
		return $single_column && $result ? $result[0] : $result;
	}
	/**
	 * @param mysqli_stmt $stmt
	 * @param bool        $single_column
	 * @param bool        $array
	 * @param bool        $indexed
	 *
	 * @return array|bool|mixed
	 */
	protected function f_stmt ($stmt, $single_column, $array, $indexed) {
		$meta    = $stmt->result_metadata();
		$result  = [];
		$columns = [];
		while ($field = $meta->fetch_field()) {
			$result[]  = null;
			$columns[] = $field->name;
		}
		if (!$stmt->store_result() || !$this->f_stmt_bind_result($stmt, $result)) {
			return false;
		}
		if ($array) {
			$return_result = [];
			while ($current_result = $this->f_stmt_internal($stmt, $result, $single_column, $indexed, $columns)) {
				$return_result[] = $current_result;
			}
			$this->free($stmt);
			return $return_result;
		}
		return $this->f_stmt_internal($stmt, $result, $single_column, $indexed, $columns);
	}
	/**
	 * @param mysqli_stmt $stmt
	 * @param array       $result
	 *
	 * @return bool
	 */
	protected function f_stmt_bind_result ($stmt, &$result) {
		return $stmt->bind_result(...$result);
	}
	/**
	 * @param mysqli_stmt $stmt
	 * @param array       $result
	 * @param bool        $single_column
	 * @param bool        $indexed
	 * @param string[]    $columns
	 *
	 * @return array|bool
	 */
	protected function f_stmt_internal ($stmt, $result, $single_column, $indexed, $columns) {
		if (!$stmt->fetch()) {
			return false;
		}
		if ($single_column && $result) {
			return $result[0];
		}
		// Hack: `$result`'s values are all references, we need to dereference them into plain values
		$new_result = [];
		foreach ($result as $i => $v) {
			$new_result[$i] = $v;
		}
		$result = $new_result;
		if ($indexed) {
			return $result;
		}
		return array_combine($columns, $result);
	}
	/**
	 * @inheritdoc
	 */
	public function id () {
		return $this->instance->insert_id;
	}
	/**
	 * @inheritdoc
	 */
	public function affected () {
		return $this->instance->affected_rows;
	}
	/**
	 * @inheritdoc
	 *
	 * @param false|mysqli_result|mysqli_stmt $query_result_stmt
	 */
	public function free ($query_result_stmt) {
		if ($query_result_stmt instanceof mysqli_result) {
			$query_result_stmt->free();
		}
		if ($query_result_stmt instanceof mysqli_stmt) {
			$query_result_stmt->free_result();
		}
		return true;
	}
	/**
	 * @inheritdoc
	 */
	public function columns ($table, $like = false) {
		if (!$table) {
			return false;
		}
		if ($like) {
			$like    = $this->s($like);
			$columns = $this->qfas("SHOW COLUMNS FROM `$table` LIKE $like") ?: [];
		} else {
			$columns = $this->qfas("SHOW COLUMNS FROM `$table`") ?: [];
		}
		return $columns;
	}
	/**
	 * @inheritdoc
	 */
	public function tables ($like = false) {
		if ($like) {
			$like = $this->s($like);
			return $this->qfas("SHOW TABLES FROM `$this->database` LIKE $like") ?: [];
		} else {
			return $this->qfas("SHOW TABLES FROM `$this->database`") ?: [];
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
	public function server () {
		return $this->instance->server_info;
	}
	/**
	 * @inheritdoc
	 */
	public function __destruct () {
		if ($this->connected && is_object($this->instance)) {
			$this->instance->close();
			$this->connected = false;
		}
	}
}
