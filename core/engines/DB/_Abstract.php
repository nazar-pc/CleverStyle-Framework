<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs\DB;
abstract class _Abstract {
				/**
				 * Is connection established
				 *
				 * @var bool
				 */
	protected	$connected	= false,
				/**
				 * DB type, may be used for constructing requests, accounting particular features of current DB (lowercase name)
				 *
				 * @var bool
				 */
				$db_type	= false,
				/**
				 * Current DB
				 *
				 * @var string
				 */
				$database,
				/**
				 * Current prefix
				 *
				 * @var string
				 */
				$prefix,
				/**
				 * Total time of requests execution
				 *
				 * @var int
				 */
				$time,
				/**
				 * Array for storing of data of the last executed request
				 *
				 * @var array
				 */
				$query		= [
					'start'		=> '',
					'end'		=> '',
					'time'		=> '',
					'text'		=> '',
					'id'		=> ''
				],
				/**
				 * Array for storing data of all executed requests
				 *
				 * @var array
				 */
				$queries	= [
					'num'		=> '',
					'time'		=> [],
					'text'		=> [],
					'result'	=> []
				],
				/**
				 * Connection time
				 *
				 * @var int
				 */
				$connecting_time,
				/**
				 * Asynchronous request
				 *
				 * @var bool
				 */
				$async		= false;
	/**
	 * Connecting to the DB
	 *
	 * @param string	$database
	 * @param string	$user
	 * @param string	$password
	 * @param string	$host
	 * @param string	$charset
	 * @param string	$prefix
	 */
	abstract function __construct ($database, $user = '', $password = '', $host = 'localhost', $charset = 'utf8', $prefix = '');
	/**
	 * SQL request into DB
	 *
	 * @abstract
	 *
	 * @param string|string[]		$query	SQL query string or array, may be a format string in accordance with the first parameter of sprintf() function
	 * @param string|string[]		$params	May be array of arguments for formatting of <b>$query</b><br>
	 * 										or string - in this case it will be first argument for formatting of <b>$query</b>
	 * @param string				$param	if <b>$params</s> is string - this parameter will be second argument for formatting of <b>$query</b>.
	 * 										If you need more arguments - add them after this one, function will accept them.
	 *
	 * @return bool|object|resource
	 */
	function q ($query, $params = [], $param = null) {
		$query	= str_replace('[prefix]', $this->prefix, $query);
		switch (func_num_args()) {
			default:
				$params	= array_slice(func_get_args(), 1);
			break;
			case 0:
				return false;
			case 1:
			case 2:
				if (!is_array($params)) {
					$params	= [$params];
				}
			break;
		}
		if (!empty($params)) {
			unset($param);
			foreach ($params as &$param) {
				$param	= $this->s($param, false);
			}
			unset($param);
		}
		if (is_array($query) && !empty($query)) {
			$return = true;
			foreach ($query as &$q) {
				if (is_array($q)) {
					if (count($q) > 1) {
						$params	= array_slice($q, 1);
					}
					$q		= $q[0];
				}
				$return = $return && $this->q(empty($params) ? $q : vsprintf($q, $params));
			}
			return $return;
		}
		if(!$query) {
			return true;
		}
		global $db;
		$this->query['time']		= microtime(true);
		$this->queries['text'][]	= $this->query['text']				= empty($params) ? $query : vsprintf($query, $params);
		$result						= $this->q_internal($this->query['text']);
		$this->queries['result'][]	= $result;
		$this->query['time']		= round(microtime(true) - $this->query['time'], 6);
		$this->time					+= $this->query['time'];
		$this->queries['time'][]	= $this->query['time'];
		++$this->queries['num'];
		if (is_object($db)) {
			$db->time		+= $this->query['time'];
			++$db->queries;
		}
		return $result;
	}
	/**
	 * Asynchronous SQL request into DB (if is not supported - ordinary request will me executed).
	 * Result of execution can't be obtained, so, use it, for example, for deleting some non-critical data
	 *
	 * @abstract
	 *
	 * @param string|string[]		$query	SQL query string, may be a format string in accordance with the first parameter of sprintf() function
	 * @param string|string[]		$params	May be array of arguments for formatting of <b>$query</b><br>
	 * 										or string - in this case it will be first argument for formatting of <b>$query</b>
	 * @param string				$param	if <b>$params</s> is string - this parameter will be second argument for formatting of <b>$query</b>.
	 * 										If you need more arguments - add them after this one, function will accept them.
	 *
	 * @return bool|object|resource
	 */
	function aq ($query, $params = [], $param = null) {
		$this->async	= true;
		$result			= call_user_func_array([$this, 'q'], func_get_args());
		$this->async	= false;
		return $result;
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
	abstract protected function q_internal ($query);
	/**
	 * Getting number of selected rows
	 *
	 * @abstract
	 *
	 * @param bool|object|resource $query_result
	 *
	 * @return int|bool
	 */
	abstract function n ($query_result = false);
	/**
	 * Fetch a result row as an associative array
	 *
	 * @abstract
	 *
	 * @param bool|object|resource	$query_result
	 * @param bool					$array			If <b>true</b> returns array of associative arrays of all fetched rows
	 * @param bool|string			$one_column		This parameter may contain name of interested column,
	 * 												and function will return not array with one element, but directly its value
	 *
	 * @return array|bool
	 */
	abstract function f ($query_result = false, $one_column = false, $array = false);
	/**
	 * Similar to ::f() method, with parameter <b>$array</b> = true
	 *
	 * @param bool|object|resource $query_result
	 * @param bool|string   $one_column
	 *
	 * @return array|bool
	 */
	function fa ($query_result = false, $one_column = false) {
		return $this->f($query_result, $one_column, true);
	}
	/**
	 * Combination of ::q() and ::f() methods
	 *
	 * @param array|string	$query		SQL query string, or you can put all parameters, that ::q() function can accept in form of array
	 * @param bool|string   $one_column	This parameter may contain name of interested column,
	 * 									and function will return not array with one element, but directly its value
	 *
	 * @return array|bool
	 */
	function qf ($query = '', $one_column = false) {
		$params	= [];
		if (is_array($query) && !empty($query)) {
			if (count($query) == 2) {
				$params	= $query[1];
			} elseif (count($query) > 2) {
				$params	= array_slice($query, 1);
			}
			$query	= $query[0];
		}
		if (!$query) {
			return false;
		}
		return $this->f($this->q($query, $params), $one_column, false);
	}
	/**
	 * Combination of ::q() and ::fa() methods
	 *
	 * @param array|string	$query		SQL query string, or you can put all parameters, that ::q() function can accept in form of array
	 * @param bool|string   $one_column	This parameter may contain name of interested column,
	 * 									and function will return not array with one element, but directly its value
	 *
	 * @return array|bool
	 */
	function qfa ($query = '', $one_column = false) {
		$params	= [];
		if (is_array($query) && !empty($query)) {
			if (count($query) == 2) {
				$params	= $query[1];
			} elseif (count($query) > 2) {
				$params	= array_slice($query, 1);
			}
			$query	= $query[0];
		}
		if (!$query) {
			return false;
		}
		return $this->f($this->q($query, $params), $one_column, true);
	}
	/**
	 * Get id of last inserted row
	 *
	 * @abstract
	 *
	 * @return int
	 */
	abstract function id ();
	/**
	 * Free result memory
	 *
	 * @abstract
	 *
	 * @param bool|object|resource $query_result
	 *
	 * @return bool
	 */
	abstract function free ($query_result = false);
	/**
	 * Get columns list of table
	 *
	 * @param string $table
	 * @param bool|string $like
	 *
	 * @return array|bool
	 */
	function columns ($table, $like = false) {
		if(!$table) {
			return false;
		}
		if ($like) {
			$columns = $this->qfa('SHOW COLUMNS FROM `'.$table.'` LIKE \''.$like.'\'');
		} else {
			$columns = $this->qfa('SHOW COLUMNS FROM `'.$table.'`');
		}
		foreach ($columns as &$column) {
			$column = $column['Field'];
		}
		return $columns;
	}
	function tables ($like = false) {
		if ($like) {
			return $this->qfa('SHOW TABLES FROM `'.$this->database.'` LIKE \''.$like.'\'');
		} else {
			return $this->qfa('SHOW TEBLES FROM `'.$this->database.'`');
		}
	}
	/**
	 * Preparing string for using in SQL query
	 * SQL Injection Protection
	 *
	 * @param string|string[]	$string
	 * @param bool				$single_quotes_around
	 *
	 * @return string|string[]
	 */
	function s ($string, $single_quotes_around = true) {
		if (is_array($string)) {
			foreach ($string as &$s) {
				$s	= $this->s_internal($s, $single_quotes_around);
			}
			return $string;
		}
		return $this->s_internal($string, $single_quotes_around);
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
	 * Queries array
	 *
	 * @return array
	 */
	function queries () {
		return $this->queries;
	}
	/**
	 * Queries array
	 *
	 * @return array
	 */
	function query () {
		return $this->query;
	}
	/**
	 * Connecting time
	 *
	 * @return int
	 */
	function time () {
		return $this->time;
	}
	/**
	 * Working time
	 *
	 * @return int
	 */
	function connecting_time () {
		return $this->connecting_time;
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	final function __clone () {}
	/**
	 * Disconnecting from DB
	 */
	abstract function __destruct ();
}