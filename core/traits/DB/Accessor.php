<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
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
	private	$db			= false,	//Link to db object
			$db_prime	= false;	//Link to primary db object
	/**
	 * Returns link to the object of db for reading (can be mirror of main DB)
	 *
	 * @return \cs\DB\_Abstract
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
		$this->db = DB::instance()->{$this->cdb()}();
		return $this->db;
	}
	/**
	 * Returns link to the object of db for writing (always main DB)
	 *
	 * @return \cs\DB\_Abstract
	 */
	function db_prime () {
		if (is_object($this->db_prime)) {
			return $this->db_prime;
		}
		/**
		 * Save reference for faster access
		 */
		$this->db_prime = DB::instance()->{$this->cdb()}();
		return $this->db_prime;
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	abstract protected function cdb ();
}