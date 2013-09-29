<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\Cache;
use			cs\Cache;
/**
 * Class for simplified work with cache, when using common prefix
 */
class Prefix {

	protected	$prefix;
	/**
	 * Initialization with some prefix
	 */
	function __construct ($prefix) {
		$this->prefix	= $prefix;
	}
	/**
	 * Get item from cache
	 *
	 * @param string		$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool|mixed			Returns item on success of <b>false</b> on failure
	 */
	function get ($item) {
		return Cache::instance()->get("$this->prefix/$item");
	}
	/**
	 * Tries to get item, and if item not found - calls closure, set returned value for specified item, and returns it as if it was found
	 *
	 * @param string	$item
	 * @param \Closure	$closure
	 *
	 * @return bool|mixed
	 */
	function get_wrapper ($item, $closure) {
		if (($data = $this->get($item)) === false) {
			$data	= $closure();
			if ($data !== false) {
				$this->set($item, $data);
			}
		}
		return $data;
	}
	/**
	 * Put or change data of cache item
	 *
	 * @param string	$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 * @param mixed		$data
	 *
	 * @return bool
	 */
	function set ($item, $data) {
		return Cache::instance()->set("$this->prefix/$item", $data);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string	$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool
	 */
	function del ($item) {
		return Cache::instance()->del("$this->prefix/$item");
	}
	/**
	 * Get item from cache
	 *
	 * @param string		$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool|mixed			Returns item on success of <b>false</b> on failure
	 */
	function __get ($item) {
		return $this->get("$this->prefix/$item");
	}
	/**
	 * Put or change data of cache item
	 *
	 * @param string	$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 * @param mixed		$data
	 *
	 * @return bool
	 */
	function __set ($item, $data) {
		return $this->set("$this->prefix/$item", $data);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string	$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool
	 */
	function __unset ($item) {
		return $this->del("$this->prefix/$item");
	}
}