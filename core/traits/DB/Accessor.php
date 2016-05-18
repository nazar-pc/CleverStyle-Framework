<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\DB;
use
	cs\DB;

/**
 * Accessor trait
 *
 * Provides db() and db_prime() methods for simplified for with DB
 */
trait Accessor {
	/**
	 * Link to db object
	 * @var false|_Abstract
	 */
	private $_db = false;
	/**
	 * Link to primary db object
	 * @var false|_Abstract
	 */
	private $_db_prime = false;
	/**
	 * Returns link to the object of db for reading (can be mirror of main DB)
	 *
	 * @return _Abstract
	 */
	function db () {
		if (is_object($this->_db)) {
			return $this->_db;
		}
		if (is_object($this->_db_prime)) {
			return $this->_db = $this->_db_prime;
		}
		/**
		 * Save reference for faster access
		 */
		$this->_db = DB::instance()->{(string)$this->cdb()}();
		return $this->_db;
	}
	/**
	 * Returns link to the object of db for writing (always main DB)
	 *
	 * @return _Abstract
	 */
	function db_prime () {
		if (is_object($this->_db_prime)) {
			return $this->_db_prime;
		}
		/**
		 * Save reference for faster access
		 */
		$this->_db_prime = DB::instance()->{(string)$this->cdb()}();
		return $this->_db_prime;
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	abstract protected function cdb ();
}
