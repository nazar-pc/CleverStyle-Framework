<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;
class Cache {
	use Singleton;

	protected	$cache,					//Cache state
				$init		= false,	//Initialization state
				$engine,
				/**
				 * Instance of cache engine object
				 *
				 * @var Cache\_Abstract
				 */
				$engine_instance;
	/**
	 * Initialization, creating cache engine instance
	 */
	protected function construct () {
		$this->cache = !DEBUG;
		if (!$this->init && $this->cache) {
			$engine_class	= '\\cs\\Cache\\'.($this->engine = Core::instance()->cache_engine);
			$this->engine_instance	= new $engine_class();
		}
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
		return $this->engine_instance->get($item);
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
		if ($this->engine != 'BlackHole' && is_object($this->engine_instance)){
			$this->engine_instance->del($item);
		}
		if (!$this->cache) {
			return true;
		}
		return $this->engine_instance->set($item, $data);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string	$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool
	 */
	function del ($item) {
		if (empty($item) || $item == '/') {
			return false;
		}
		if (is_object($this->engine_instance)){
			return $this->engine_instance->del($item);
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
		if (is_object($this->engine_instance)){
			return $this->engine_instance->clean();
		} else {
			return false;
		}
	}
	/**
	 * Cache state enabled/disabled
	 *
	 * @return bool
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
}