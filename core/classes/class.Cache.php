<?php
namespace cs;
class Cache {
	protected	$cache,					//Cache state
				$init		= false,	//Initialization state
				$instance;
	function __construct () {
		$this->init();
		if (!$this->init && $this->cache) {
			global $Core;
			$engine_class	= '\\cs\\cache\\'.$Core->config('cache_engine');
			$this->instance	= new $engine_class();
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
		if (is_object($this->instance)){
			$this->instance->del($item);
		}
		if (!$this->cache) {
			return true;
		}
		return $this->instance->set($item, $data);
	}
	function del ($item) {
		if (is_object($this->instance)){
			return $this->instance->del($item);
		} else {
			return false;
		}
	}
	function clean () {
		if (is_object($this->instance)){
			return $this->instance->clean();
		} else {
			return false;
		}
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
	 *
	 * @final
	 */
	function __clone () {}
}