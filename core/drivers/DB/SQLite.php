<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
namespace cs\DB;
use
	SQLite3Result;

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
	 * @param string $prefix
	 */
	public function __construct ($database, $user = '', $password = '', $host = '', $prefix = '') {
		if (!$host) {
			return;
		}
		$start = microtime(true);
		try {
			$this->instance        = new \SQLite3($host);
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
	public function q ($query, ...$params) {
		return parent::q(
			$this->convert_sql($query),
			...$params
		);
	}
	/**
	 * Convert small subset of MySQL queries into SQLite-compatible syntax
	 *
	 * @param string $query
	 *
	 * @return string
	 */
	protected function convert_sql ($query) {
		return str_replace('INSERT IGNORE', 'INSERT OR IGNORE', $query);
	}
	/**
	 * @inheritdoc
	 *
	 * @return false|SQLite3Result
	 */
	protected function q_internal ($query, $parameters = []) {
		if (!$query) {
			return false;
		}
		if ($parameters) {
			$stmt = $this->instance->prepare($query);
			if (!$stmt) {
				return false;
			}
			// Allows to provide more parameters for prepared statements than needed
			$local_parameters = array_slice($parameters, 0, substr_count($query, '?'));
			foreach ($local_parameters as $index => $parameter) {
				$stmt->bindValue($index + 1, $parameter);
			}
			return $stmt->execute();
		}
		return $this->instance->query($query);
	}
	/**
	 * @inheritdoc
	 *
	 * @param false|SQLite3Result $query_result
	 */
	public function f ($query_result, $single_column = false, $array = false, $indexed = false) {
		if (!($query_result instanceof SQLite3Result)) {
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
	public function id () {
		return $this->instance->lastInsertRowID();
	}
	/**
	 * @inheritdoc
	 */
	public function affected () {
		return $this->instance->changes();
	}
	/**
	 * @inheritdoc
	 *
	 * @param false|SQLite3Result $query_result
	 */
	public function free ($query_result) {
		if ($query_result instanceof SQLite3Result) {
			return $query_result->finalize();
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
				trigger_error("Can't get columns like $like, SQLite driver doesn't support such conditions", E_USER_WARNING);
				return [];
			}
		}
		return $columns;
	}
	/**
	 * @inheritdoc
	 */
	public function tables ($like = false) {
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
	public function server () {
		return \SQLite3::version()['versionString'];
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
