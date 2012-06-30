<?php
namespace cs\database;
abstract class _Abstract {
				/**
				 * Is connection established
				 *
				 * @var bool
				 */
	public		$connected	= false,
				/**
				 * Is DB type, may be used for constructing requests, accounting particular features of current DB (lowercase name)
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
				$connecting_time;
				/**
				 * Asynchronous request
				 *
				 * @var bool
				 */
	protected	$async		= false;

	/**
	 * Connecting to DB
	 *
	 * @param string      $database
	 * @param string      $user
	 * @param string      $password
	 * @param string      $host
	 * @param bool|string $charset
	 */
	abstract function __construct ($database, $user = '', $password = '', $host = 'localhost', $charset = 'utf8');
	/**
	 * SQL request into DB
	 *
	 * @abstract
	 *
	 * @param string|string[] $query
	 *
	 * @return bool|object|resource
	 */
	function q ($query) {
		if (is_array($query) && !empty($query)) {
			$return = true;
			foreach ($query as $q) {
				$return = $return && $this->q($q);
			}
			return $return;
		}
		if(!$query) {
			return false;
		}
		global $db;
		$this->query['time']		= microtime(true);
		$this->queries['text'][]	= $this->query['text']				= str_replace('[prefix]', $this->prefix, $query);
		$result						= $this->q_internal($this->query['text']);
		$this->queries['result'][]	= $result;
		$this->query['time']		= round(microtime(true) - $this->query['time'], 6);
		$this->time					+= $this->query['time'];
		$this->queries['time'][]	= $this->query['time'];
		$db->time					+= $this->query['time'];
		++$this->queries['num'];
		++$db->queries;
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
	 * Asynchronous SQL request into DB (if is not supported - ordinary request will me executed).
	 * Result of execution can't be obtained, so, use it, for example, for deleting some non-critical data
	 *
	 * @abstract
	 *
	 * @param string|string[] $query
	 *
	 * @return bool|object|resource
	 */
	function aq ($query) {
		$this->async	= true;
		$result			= $this->q($query);
		$this->async	= false;
		return $result;
	}
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
	 * @param string		$query
	 * @param bool|string   $one_column	This parameter may contain name of interested column,
	 * 									and function will return not array with one element, but directly its value
	 *
	 * @return array|bool
	 */
	function qf ($query = '', $one_column = false) {
		if (!$query) {
			return false;
		}
		return $this->f($this->q($query), $one_column, false);
	}
	/**
	 * Combination of ::q() and ::fa() methods
	 *
	 * @param string        $query
	 * @param bool|string   $one_column
	 *
	 * @return array|bool
	 */
	function qfa ($query = '', $one_column = false) {
		if (!$query) {
			return false;
		}
		return $this->f($this->q($query), $one_column, true);
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
	 * @param $string
	 *
	 * @return string
	 */
	abstract function s ($string);
	/**
	 * Get information about server
	 *
	 * @return string
	 */
	abstract function server ();
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