<?php
class Cache {
	protected	$cache,					//Cache state
				$init		= false,	//Initialization state
				$instance;
	function __construct () {
		$this->init();
		if (!$this->init && $this->cache) {
			global $CACHE_ENGINE;
			$this->instance = new $CACHE_ENGINE();
		}
	}
	function init () {
		$this->cache = !(defined('DEBUG') && DEBUG);
	}
	function get ($item) {
		if (!$this->cache) {
			return false;
		}
		return $this->instance->get($item);
	}
	function set ($item, $data) {
		if (!$this->cache) {
			return true;
		}
		return $this->instance->set($item, $data);
	}
	function del ($item) {
		if (!$this->cache) {
			return true;
		}
		return $this->instance->del($item);
	}
	function cache_state() {
		return $this->cache;
	}
	function disable () {
		$this->cache = false;
	}
	function __get ($item) {
		return $this->get($item);
	}
	function __set ($item, $data) {
		return $this->set($item, $data);
	}
	function __unset ($item) {
		return $this->del($item);
	}
	/**
	 * Cloning restriction
	 */
	function __clone () {}
}