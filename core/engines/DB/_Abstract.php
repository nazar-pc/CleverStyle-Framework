<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\DB;
abstract class _Abstract {
	/**
	 * Is connection established
	 *
	 * @var bool
	 */
	protected $connected = false;
	/**
	 * DB type, may be used for constructing requests, accounting particular features of current DB (lowercase name)
	 *
	 * @var string
	 */
	protected $db_type = '';
	/**
	 * Current DB
	 *
	 * @var string
	 */
	protected $database;
	/**
	 * Current prefix
	 *
	 * @var string
	 */
	protected $prefix;
	/**
	 * Total time of requests execution
	 *
	 * @var int
	 */
	protected $time;
	/**
	 * Array for storing of data of the last executed request
	 *
	 * @var array
	 */
	protected $query = [
		'time' => '',
		'text' => ''
	];
	/**
	 * Array for storing data of all executed requests
	 *
	 * @var array
	 */
	protected $queries = [
		'num'  => '',
		'time' => [],
		'text' => []
	];
	/**
	 * Connection time
	 *
	 * @var float
	 */
	protected $connecting_time;
	/**
	 * Asynchronous request
	 *
	 * @var bool
	 */
	protected $async = false;
	/**
	 * Connecting to the DB
	 *
	 * @param string $database
	 * @param string $user
	 * @param string $password
	 * @param string $host
	 * @param string $charset
	 * @param string $prefix
	 */
	abstract function __construct ($database, $user = '', $password = '', $host = 'localhost', $charset = 'utf8', $prefix = '');
	/**
	 * Query
	 *
	 * SQL request into DB
	 *
	 * @abstract
	 *
	 * @param string|string[] $query  SQL query string or array, may be a format string in accordance with the first parameter of sprintf() function
	 * @param string|string[] $params May be array of arguments for formatting of <b>$query</b><br>
	 *                                or string - in this case it will be first argument for formatting of <b>$query</b>
	 * @param string          $_      if <b>$params</s> is string - this parameter will be second argument for formatting of <b>$query</b>.
	 *                                If you need more arguments - add them after this one, function will accept them.
	 *
	 * @return false|object|resource
	 */
	function q ($query, $params = [], $_ = null) {
		$normalized = $this->prepare_and_normalize_arguments($query, func_get_args());
		if (!$normalized) {
			return false;
		}
		list($query, $params) = $normalized;
		/**
		 * Executing multiple queries
		 */
		if (is_array($query)) {
			return $this->execute_multiple($query, $params);
		}
		return $this->execute_single($query, $params);
	}
	/**
	 * @param string|string[] $query
	 * @param array           $arguments
	 *
	 * @return array|false
	 */
	protected function prepare_and_normalize_arguments ($query, $arguments) {
		if (!$query || !$arguments) {
			return false;
		}
		$query = str_replace('[prefix]', $this->prefix, $query);
		switch (count($arguments)) {
			default:
				$params = array_slice($arguments, 1);
				break;
			case 1:
				$params = [];
				break;
			case 2:
				$params = (array)$arguments[1];
				break;
		}
		foreach ($params as &$param) {
			$param = $this->s($param, false);
		}
		return [
			$query,
			$params
		];
	}
	/**
	 * @param string[] $queries
	 * @param string[] $params
	 *
	 * @return false|object|resource
	 */
	protected function execute_multiple ($queries, $params) {
		$time_from = microtime(true);
		foreach ($queries as &$q) {
			$q = $params ? vsprintf($q, $params) : $q;
		}
		unset($q);
		$this->queries['num'] += count($queries);
		$result = $this->q_multi_internal($queries);
		$this->time += round(microtime(true) - $time_from, 6);
		return $result;
	}
	/**
	 * @param string   $query
	 * @param string[] $params
	 *
	 * @return false|object|resource
	 */
	protected function execute_single ($query, $params) {
		$time_from           = microtime(true);
		$this->query['text'] = empty($params) ? $query : vsprintf($query, $params);
		if (DEBUG) {
			$this->queries['text'][] = $this->query['text'];
		}
		$result              = $this->q_internal($this->query['text']);
		$this->query['time'] = round(microtime(true) - $time_from, 6);
		$this->time += $this->query['time'];
		if (DEBUG) {
			$this->queries['time'][] = $this->query['time'];
		}
		++$this->queries['num'];
		return $result;
	}
	/**
	 * Asynchronous, Query
	 *
	 * Asynchronous SQL request into DB (if is not supported - ordinary request will me executed).
	 * Result of execution can't be obtained, so, use it, for example, for deleting some non-critical data
	 *
	 * @abstract
	 *
	 * @param string|string[] $query          SQL query string, may be a format string in accordance with the first parameter of sprintf() function
	 * @param string|string[] $params         May be array of arguments for formatting of <b>$query</b><br>
	 *                                        or string - in this case it will be first argument for formatting of <b>$query</b>
	 * @param string          $param          if <b>$params</s> is string - this parameter will be second argument for formatting of <b>$query</b>.
	 *                                        If you need more arguments - add them after this one, function will accept them.
	 *
	 * @return false|object|resource
	 */
	function aq ($query, $params = [], $param = null) {
		$this->async = true;
		$result      = call_user_func_array([$this, 'q'], func_get_args());
		$this->async = false;
		return $result;
	}
	/**
	 * SQL request to DB
	 *
	 * @abstract
	 *
	 * @param string|string[] $query
	 *
	 * @return false|object|resource
	 */
	abstract protected function q_internal ($query);
	/**
	 * Multiple SQL request to DB
	 *
	 * @abstract
	 *
	 * @param string[] $query
	 *
	 * @return false|object|resource
	 */
	abstract protected function q_multi_internal ($query);
	/**
	 * Number
	 *
	 * Getting number of selected rows
	 *
	 * @abstract
	 *
	 * @param object|resource $query_result
	 *
	 * @return false|int
	 */
	abstract function n ($query_result);
	/**
	 * Fetch
	 *
	 * Fetch a result row as an associative array
	 *
	 * @abstract
	 *
	 * @param false|object|resource $query_result
	 * @param bool                  $single_column If <b>true</b> function will return not array with one element, but directly its value
	 * @param bool                  $array         If <b>true</b> returns array of associative arrays of all fetched rows
	 * @param bool                  $indexed       If <b>false</b> - associative array will be returned
	 *
	 * @return array[]|false|int|int[]|string|string[]
	 */
	abstract function f ($query_result, $single_column = false, $array = false, $indexed = false);
	/**
	 * Fetch, Array
	 *
	 * Similar to ::f() method, with parameter <b>$array</b> = true
	 *
	 * @param false|object|resource $query_result
	 * @param bool                  $single_column If <b>true</b> function will return not array with one element, but directly its value
	 * @param bool                  $indexed       If <b>false</b> - associative array will be returned
	 *
	 * @return array[]|false
	 */
	function fa ($query_result, $single_column = false, $indexed = false) {
		return $this->f($query_result, $single_column, true, $indexed);
	}
	/**
	 * Fetch, Single
	 *
	 * Similar to ::f() method, with parameter <b>$single_column</b> = true
	 *
	 * @param false|object|resource $query_result
	 * @param bool                  $array If <b>true</b> returns array of associative arrays of all fetched rows
	 *
	 * @return false|int|int[]|string|string[]
	 */
	function fs ($query_result, $array = false) {
		return $this->f($query_result, true, $array);
	}
	/**
	 * Fetch, Array, Single
	 *
	 * Combination of ::fa() and ::fs() methods
	 *
	 * @param false|object|resource $query_result
	 *
	 * @return false|int[]|string[]
	 */
	function fas ($query_result) {
		return $this->fa($query_result, true);
	}
	/**
	 * Query, Fetch
	 *
	 * Combination of ::q() and ::f() methods
	 *
	 * @param array|string $query         SQL query string, or you can put all parameters, that ::q() function can accept in form of array
	 * @param bool         $single_column If <b>true</b> function will return not array with one element, but directly its value
	 * @param bool         $array         If <b>true</b> returns array of associative arrays of all fetched rows
	 * @param bool         $indexed       If <b>false</b> - associative array will be returned
	 *
	 * @return array[]|false|int|int[]|string|string[]
	 */
	function qf ($query, $single_column = false, $array = false, $indexed = false) {
		list($query, $params) = $this->q_prepare($query);
		if (!$query) {
			return false;
		}
		return $this->f($this->q($query, $params), $single_column, $array, $indexed);
	}
	/**
	 * Query, Fetch, Array
	 *
	 * Combination of ::q() and ::fa() methods
	 *
	 * @param array|string $query         SQL query string, or you can put all parameters, that ::q() function can accept in form of array
	 * @param bool         $single_column If <b>true</b> function will return not array with one element, but directly its value
	 * @param bool         $indexed       If <b>false</b> - associative array will be returned
	 *
	 * @return array[]|false
	 */
	function qfa ($query, $single_column = false, $indexed = false) {
		list($query, $params) = $this->q_prepare($query);
		if (!$query) {
			return false;
		}
		return $this->fa($this->q($query, $params), $single_column, $indexed);
	}
	/**
	 * Query, Fetch, Single
	 *
	 * Combination of ::q() and ::fs() methods
	 *
	 * @param array|string $query SQL query string, or you can put all parameters, that ::q() function can accept in form of array
	 * @param bool         $array If <b>true</b> returns array of associative arrays of all fetched rows
	 *
	 * @return false|int|int[]|string|string[]
	 */
	function qfs ($query, $array = false) {
		list($query, $params) = $this->q_prepare($query);
		if (!$query) {
			return false;
		}
		return $this->fs($this->q($query, $params), $array);
	}
	/**
	 * Query, Fetch, Array, Single
	 *
	 * Combination of ::q() and ::fas() methods
	 *
	 * @param array|string $query SQL query string, or you can put all parameters, that ::q() function can accept in form of array
	 *
	 * @return false|int[]|string[]
	 */
	function qfas ($query) {
		list($query, $params) = $this->q_prepare($query);
		if (!$query) {
			return false;
		}
		return $this->fas($this->q($query, $params));
	}
	/**
	 * Query preparing for ::q*() methods
	 *
	 * @param array|string|string[] $query
	 *
	 * @return array|false [<b>$query</b>, <b>$params</b>]
	 */
	protected function q_prepare ($query) {
		if (!$query) {
			return false;
		}
		$params = [];
		if (is_array($query)) {
			if (count($query) == 2) {
				$params = $query[1];
			} elseif (count($query) > 2) {
				$params = array_slice($query, 1);
			}
			$query = $query[0];
		}
		return [
			$query,
			$params
		];
	}
	/**
	 * Method for simplified inserting of several rows
	 *
	 * @param string        $query
	 * @param array|array[] $params   Array of array of parameters for inserting
	 * @param bool          $join     If true - inserting of several rows will be combined in one query. For this, be sure, that your query has keyword
	 *                                <i>VALUES</i> in uppercase. Part of query after this keyword will be multiplied with coma separator.
	 *
	 * @return bool
	 */
	function insert ($query, $params, $join = true) {
		if (!$query || !$params) {
			return false;
		}
		if ($join) {
			$query    = explode('VALUES', $query, 2);
			$query[1] = explode(')', $query[1], 2);
			$query    = [
				$query[0],
				$query[1][0].')',
				$query[1][1]
			];
			if (!isset($query[1]) || !$query[1]) {
				return false;
			}
			$query[1] .= str_repeat(",$query[1]", count($params) - 1);
			$query = $query[0].'VALUES'.$query[1].$query[2];
			return (bool)$this->q(
				$query,
				call_user_func_array(
					'array_merge',
					array_map(
						'array_values',
						_array($params)
					)
				)
			);
		} else {
			$result = true;
			foreach ($params as $p) {
				$result = $result && (bool)$this->q($query, $p);
			}
			return $result;
		}
	}
	/**
	 * Id
	 *
	 * Get id of last inserted row
	 *
	 * @abstract
	 *
	 * @return int
	 */
	abstract function id ();
	/**
	 * Affected
	 *
	 * Get number of affected rows during last query
	 *
	 * @abstract
	 *
	 * @return int
	 */
	abstract function affected ();
	/**
	 * Free result memory
	 *
	 * @abstract
	 *
	 * @param object|resource $query_result
	 */
	abstract function free ($query_result);
	/**
	 * Get columns list of table
	 *
	 * @param string       $table
	 * @param false|string $like
	 *
	 * @return string[]
	 */
	function columns ($table, $like = false) {
		if (!$table) {
			return false;
		}
		if ($like) {
			$like    = $this->s($like);
			$columns = $this->qfa("SHOW COLUMNS FROM `$table` LIKE $like") ?: [];
		} else {
			$columns = $this->qfa("SHOW COLUMNS FROM `$table`") ?: [];
		}
		foreach ($columns as &$column) {
			$column = $column['Field'];
		}
		return $columns;
	}
	/**
	 * Get tables list
	 *
	 * @param false|string $like
	 *
	 * @return string[]
	 */
	function tables ($like = false) {
		if ($like) {
			$like = $this->s($like);
			return $this->qfas("SHOW TABLES FROM `$this->database` LIKE $like") ?: [];
		} else {
			return $this->qfas("SHOW TABLES FROM `$this->database`") ?: [];
		}
	}
	/**
	 * Safe
	 *
	 * Preparing string for using in SQL query
	 * SQL Injection Protection
	 *
	 * @param string|string[] $string
	 * @param bool            $single_quotes_around
	 *
	 * @return string|string[]
	 */
	function s ($string, $single_quotes_around = true) {
		if (is_array($string)) {
			foreach ($string as &$s) {
				$s = $this->s_internal($s, $single_quotes_around);
			}
			return $string;
		}
		return $this->s_internal($string, $single_quotes_around);
	}
	/**
	 * Preparing string for using in SQL query
	 * SQL Injection Protection
	 *
	 * @param string $string
	 * @param bool   $single_quotes_around
	 *
	 * @return string
	 */
	abstract protected function s_internal ($string, $single_quotes_around);
	/**
	 * Get information about server
	 *
	 * @return string
	 */
	abstract function server ();
	/**
	 * Connection state
	 *
	 * @return bool
	 */
	function connected () {
		return $this->connected;
	}
	/**
	 * Database type (lowercase, for example <i>mysql</i>)
	 *
	 * @return string
	 */
	function db_type () {
		return $this->db_type;
	}
	/**
	 * Database name
	 *
	 * @return string
	 */
	function database () {
		return $this->database;
	}
	/**
	 * Queries array, has 3 properties:<ul>
	 * <li>num - total number of performed queries
	 * <li>time - array with time of each query execution
	 * <li>text - array with text text of each query
	 *
	 * @return array
	 */
	function queries () {
		return $this->queries;
	}
	/**
	 * Last query information, has 2 properties:<ul>
	 * <li>time - execution time
	 * <li>text - query text
	 *
	 * @return array
	 */
	function query () {
		return $this->query;
	}
	/**
	 * Total working time (including connection, queries execution and other delays)
	 *
	 * @return int
	 */
	function time () {
		return $this->time;
	}
	/**
	 * Connecting time
	 *
	 * @return float
	 */
	function connecting_time () {
		return $this->connecting_time;
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	final function __clone () {
	}
	/**
	 * Disconnecting from DB
	 */
	abstract function __destruct ();
}
