<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;
class Cache {
	protected	$cache,					//Cache state
				$init		= false,	//Initialization state
				$engine,
				/**
				 * Instance of cache engine object
				 *
				 * @var Cache\_Abstract
				 */
				$instance;
	/**
	 * Initialization, creating cache engine instance
	 */
	function __construct () {
		$this->init();
		if (!$this->init && $this->cache) {
			global $Core;
			$engine_class	= '\\cs\\Cache\\'.($this->engine = $Core->cache_engine);
			$this->instance	= new $engine_class();
		}
	}
	/**
	 * Cache (re)initialization
	 */
	function init () {
		$this->cache = !(defined('DEBUG') && DEBUG);
	}
	/**
	 * Get item from cache
	 *
	 * @param string		$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool|mixed			Returns item on success of <b>false</b> on failure
	 */
	function get ($item) {
		if (!$this->cache) {
			return false;
		}
		return $this->instance->get($item);
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
		if ($this->engine != 'BlackHole' && is_object($this->instance)){
			$this->instance->del($item);
		}
		if (!$this->cache) {
			return true;
		}
		return $this->instance->set($item, $data);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string	$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool
	 */
	function del ($item) {
		$this->del_internal($item);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string	$item				May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 * @param bool		$process_mirrors
	 *
	 * @return bool
	 */
	protected function del_internal ($item, $process_mirrors = true) {
		if (empty($item) || $item == '/') {
			return false;
		}
		global $User, $Config;
		if ($process_mirrors && isset($Config->core['cache_sync']) && $Config->core['cache_sync'] && is_object($User) && !$User->system()) {
			global $Core;
			$Core->api_request('System/admin/cache/del', ['item' => $item]);
		}
		if (is_object($this->instance)){
			return $this->instance->del($item);
		} else {
			return false;
		}
	}
	/**
	 * Clean cache by deleting all items
	 *
	 * @return bool
	 */
	function clean () {
		if (is_object($this->instance)){
			return $this->instance->clean();
		} else {
			return false;
		}
	}
	/**
	 * Cache state enabled/disabled
	 *
	 * @return mixed
	 */
	function cache_state() {
		return $this->cache;
	}
	/**
	 * Disable cache
	 */
	function disable () {
		$this->cache = false;
	}
	/**
	 * Get item from cache
	 *
	 * @param string		$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool|mixed			Returns item on success of <b>false</b> on failure
	 */
	function __get ($item) {
		return $this->get($item);
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
		return $this->set($item, $data);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string	$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool
	 */
	function __unset ($item) {
		return $this->del($item);
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	function __clone () {}
}
/**
 * For IDE
 */
if (false) {
	global $Cache;
	$Cache = new Cache;
}