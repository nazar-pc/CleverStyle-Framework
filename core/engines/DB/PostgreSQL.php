<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\DB;
class PostgreSQL extends _Abstract {
	/**
	 * @var resource DB connection handler
	 */
	protected $handler;
	/**
	 * @var resource
	 */
	protected $query_result;
	/**
	 * @inheritdoc
	 */
	function __construct ($database, $user = '', $password = '', $host = 'localhost', $prefix = '') {
		$start = microtime(true);
		/**
		 * Parsing of $host variable, detecting port and persistent connection
		 */
		list($host, $port, $persistent) = $this->get_host_port_and_persistent($host);
		$connection_string = "host=$host port=$port dbname=$database user=$user password=$password options='--client_encoding=UTF8'";
		$this->handler     = $persistent ? pg_connect($connection_string) : pg_pconnect($connection_string);
		if (!is_resource($this->handler)) {
			return;
		}
		$this->database        = $database;
		$this->connected       = true;
		$this->connecting_time = microtime(true) - $start;
		$this->db_type         = 'postgresql';
		$this->prefix          = $prefix;
	}
	/**
	 * Parse host string into host, port and persistent separately
	 *
	 * Understands `p:` prefix for persistent connections
	 *
	 * @param string $host_string
	 *
	 * @return array
	 */
	protected function get_host_port_and_persistent ($host_string) {
		$host       = explode(':', $host_string);
		$port       = 5432;
		$persistent = false;
		switch (count($host)) {
			case 1:
				$host = $host[0];
				break;
			case 2:
				if ($host[0] == 'p') {
					$persistent = true;
					$host       = $host[1];
				} else {
					list($host, $port) = $host;
				}
				break;
			case 3:
				$persistent = true;
				list(, $host, $port) = $host;
		}
		return [$host, $port, $persistent];
	}
	/**
	 * @inheritdoc
	 */
	function q ($query, $params = [], ...$param) {
		// Hack to convert small subset of MySQL queries into PostgreSQL-compatible syntax
		$query = str_replace('`', '"', $query);
		$query = preg_replace_callback(
			'/(INSERT IGNORE INTO|REPLACE INTO)(.+)(;|$)/Uis',
			function ($matches) {
				// Only support simplest cases
				if (stripos($matches[2], 'on duplicate')) {
					return $matches[0];
				}
				switch (strtoupper($matches[1])) {
					case 'INSERT IGNORE INTO':
						return "INSERT INTO $matches[2] ON CONFLICT DO NOTHING$matches[3]";
					case 'REPLACE INTO':
						$table_name = substr(
							$matches[2],
							strpos($matches[2], '"') + 1
						);
						$table_name = substr(
							$table_name,
							0,
							strpos($table_name, '"')
						);
						$update     = preg_replace_callback(
							'/"([^"]+)"/',
							function ($matches) {
								return "\"$matches[1]\" = EXCLUDED.\"$matches[1]\"";
							},
							substr(
								strstr($matches[2], ')', true),
								strpos($matches[2], '(') + 1
							)
						);
						// Only support constraint named as table with `_primary` prefix
						return "INSERT INTO $matches[2] ON CONFLICT ON CONSTRAINT \"{$table_name}_primary\" DO UPDATE SET $update$matches[3]";
				}
			},
			$query
		);
		return parent::q(...([$query] + func_get_args()));
	}
	/**
	 * @inheritdoc
	 *
	 * @return false|resource
	 */
	protected function q_internal ($query) {
		if (!$query) {
			return false;
		}
		return $this->query_result = pg_query($this->handler, $query);
	}
	/**
	 * @inheritdoc
	 */
	protected function q_multi_internal ($query) {
		return (bool)$this->q_internal(implode(';', $query));
	}
	/**
	 * @inheritdoc
	 *
	 * @param false|resource $query_result
	 */
	function f ($query_result, $single_column = false, $array = false, $indexed = false) {
		if (!is_resource($query_result)) {
			return false;
		}
		$result_type = $single_column || $indexed ? PGSQL_NUM : PGSQL_ASSOC;
		if ($array) {
			$result = [];
			while ($current = pg_fetch_array($query_result, null, $result_type)) {
				$result[] = $single_column ? $current[0] : $current;
			}
			$this->free($query_result);
			return $result;
		}
		$result = pg_fetch_array($query_result, null, $result_type);
		return $single_column && $result ? $result[0] : $result;
	}
	/**
	 * @inheritdoc
	 */
	function id () {
		return (int)$this->qfs('SELECT lastval()');
	}
	/**
	 * @inheritdoc
	 */
	function affected () {
		return is_resource($this->query_result) ? pg_affected_rows($this->query_result) : 0;
	}
	/**
	 * @inheritdoc
	 *
	 * @param false|resource $query_result
	 */
	function free ($query_result) {
		if (is_resource($query_result)) {
			return pg_free_result($query_result);
		}
		return true;
	}
	/**
	 * @inheritdoc
	 */
	function columns ($table, $like = false) {
		if (!$table) {
			return false;
		}
		if ($like) {
			$like    = $this->s($like);
			$columns = $this->qfas(
				"SELECT `column_name` 
				FROM `information_schema`.`columns`
				WHERE
					`table_name` = '$table' AND
					`column_name` LIKE $like"
			) ?: [];
		} else {
			$columns = $this->qfas(
				"SELECT `column_name`
				FROM `information_schema`.`columns`
				WHERE `table_name` = '$table'"
			) ?: [];
		}
		return $columns;
	}
	/**
	 * @inheritdoc
	 */
	function tables ($like = false) {
		if ($like) {
			$like = $this->s($like);
			return $this->qfas(
				"SELECT `table_name`
				FROM `information_schema`.`tables`
				WHERE
					`table_schema` = 'public' AND
					`table_name` LIKE $like
				ORDER BY `table_name` ASC"
			) ?: [];
		} else {
			return $this->qfas(
				"SELECT `table_name`
				FROM `information_schema`.`tables`
				WHERE `table_schema` = 'public'
				ORDER BY `table_name` ASC"
			) ?: [];
		}
	}
	/**
	 * @inheritdoc
	 */
	protected function s_internal ($string, $single_quotes_around) {
		return $single_quotes_around ? pg_escape_literal($this->handler, $string) : pg_escape_string($this->handler, $string);
	}
	/**
	 * @inheritdoc
	 */
	function server () {
		return pg_version($this->handler)['server'];
	}
	/**
	 * @inheritdoc
	 */
	function __destruct () {
		if ($this->connected && is_resource($this->handler)) {
			pg_close($this->handler);
			$this->connected = false;
		}
	}
}
