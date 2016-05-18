<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\DB;
class SQLite extends _Abstract {
	/**
	 * @var \SQLite3 Instance of DB connection
	 */
	protected $instance;
	/**
	 * @param string $database Ignored for SQLite
	 * @param string $user     Ignored for SQLite
	 * @param string $password Ignored for SQLite
	 * @param string $host     Path to database file, relatively to website root or absolute
	 * @param string $charset  Ignored for SQLite
	 * @param string $prefix
	 */
	function __construct ($database, $user = '', $password = '', $host = '', $charset = '', $prefix = '') {
		$start = microtime(true);
		try {
			$this->instance        = @new \SQLite3($host);
			$this->database        = $database;
			$this->connected       = true;
			$this->connecting_time = microtime(true) - $start;
			$this->db_type         = 'sqlite';
			$this->prefix          = $prefix;
		} catch (\Exception $e) {
		}
	}
	/**
	 * @inheritdoc
	 */
	function q ($query, $params = [], ...$param) {
		// Hack to convert small subset of MySQL queries into SQLite-compatible syntax
		$query = str_replace('INSERT IGNORE', 'INSERT OR IGNORE', $query);
		return parent::q(...([$query] + func_get_args()));
	}
	/**
	 * @inheritdoc
	 *
	 * @return bool|\SQLite3Result
	 */
	protected function q_internal ($query) {
		if (!$query) {
			return false;
		}
		return $this->instance->query($query);
	}
	/**
	 * @inheritdoc
	 */
	protected function q_multi_internal ($query) {
		$result = true;
		foreach ($query as $q) {
			$result = $result && $this->q_internal($q);
		}
		return $result;
	}
	/**
	 * @deprecated
	 * @todo remove after 4.x release
	 *
	 * @inheritdoc
	 */
	function n ($query_result) {
		if (is_object($query_result)) {
			/**
			 * @var \SQLite3Result $query_result
			 */
			$n = 0;
			$query_result->reset();
			while ($query_result->fetchArray()) {
				++$n;
			}
			$query_result->reset();
			return $n;
		} else {
			return false;
		}
	}
	/**
	 * @inheritdoc
	 *
	 * @param false|\SQLite3Result $query_result
	 */
	function f ($query_result, $single_column = false, $array = false, $indexed = false) {
		if (!is_object($query_result)) {
			return false;
		}
		$result_type = $single_column || $indexed ? SQLITE3_NUM : SQLITE3_ASSOC;
		if ($array) {
			$result = [];
			while ($current = $query_result->fetchArray($result_type)) {
				$result[] = $single_column ? $current[0] : $current;
			}
			$this->free($query_result);
			return $result;
		}
		$result = $query_result->fetchArray($result_type);
		return $single_column && $result ? $result[0] : $result;
	}
	/**
	 * @inheritdoc
	 */
	function id () {
		return $this->instance->lastInsertRowID();
	}
	/**
	 * @inheritdoc
	 */
	function affected () {
		return $this->instance->changes();
	}
	/**
	 * @inheritdoc
	 *
	 * @param false|\SQLite3Result $query_result
	 */
	function free ($query_result) {
		if (is_object($query_result)) {
			return $query_result->finalize();
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
		$columns = $this->qfa("PRAGMA table_info(`$table`)") ?: [];
		foreach ($columns as &$column) {
			$column = $column['name'];
		}
		/**
		 * Only support the most common cases
		 */
		if ($like) {
			if (substr($like, -1) == '%') {
				$like = substr($like, 0, -1);
				return array_values(
					array_filter(
						$columns,
						function ($column) use ($like) {
							return strpos($column, $like) === 0;
						}
					)
				);
			} elseif (strpos($like, '%') === false) {
				return in_array($like, $columns) ? [$like] : [];
			} else {
				trigger_error("Can't get columns like $like, SQLite engine doesn't support such conditions", E_USER_WARNING);
				return [];
			}
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
				"SELECT `name`
				FROM `sqlite_master`
				WHERE
					`type` = 'table' AND
					`name` != 'sqlite_sequence' AND
					`name` LIKE $like
				ORDER BY `name` ASC"
			) ?: [];
		} else {
			return $this->qfas(
				"SELECT `name`
				FROM `sqlite_master`
				WHERE
					`type` = 'table' AND
					`name` != 'sqlite_sequence'
				ORDER BY `name` ASC"
			) ?: [];
		}
	}
	/**
	 * @inheritdoc
	 */
	protected function s_internal ($string, $single_quotes_around) {
		$return = \SQLite3::escapeString($string);
		return $single_quotes_around ? "'$return'" : $return;
	}
	/**
	 * @inheritdoc
	 */
	function server () {
		return \SQLite3::version()['versionString'];
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
