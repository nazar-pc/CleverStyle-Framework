<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\DB;
use			cs\DB;
/**
 * Accessor trait
 *
 * Provides db() and db_prime() methods for simplified for with DB
 */
trait Accessor {
	/**
	 * Link to db object
	 * @var bool|_Abstract
	 */
	private	$db			= false;
	/**
	 * Link to primary db object
	 * @var bool|_Abstract
	 */
	private	$db_prime	= false;
	/**
	 * Returns link to the object of db for reading (can be mirror of main DB)
	 *
	 * @return _Abstract
	 */
	function db () {
		if (is_object($this->db)) {
			return $this->db;
		}
		if (is_object($this->db_prime)) {
			return $this->db = $this->db_prime;
		}
		/**
		 * Save reference for faster access
		 */
		$this->db = DB::instance()->{(string)$this->cdb()}();
		return $this->db;
	}
	/**
	 * Returns link to the object of db for writing (always main DB)
	 *
	 * @return _Abstract
	 */
	function db_prime () {
		if (is_object($this->db_prime)) {
			return $this->db_prime;
		}
		/**
		 * Save reference for faster access
		 */
		$this->db_prime = DB::instance()->{(string)$this->cdb()}();
		return $this->db_prime;
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	abstract protected function cdb ();
}
