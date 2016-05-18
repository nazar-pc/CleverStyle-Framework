<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	cs\Cache\Prefix;

/**
 * @method static $this instance($check = false)
 */
class Cache {
	use Singleton;
	/**
	 * Cache state
	 *
	 * @var bool
	 */
	protected $state = true;
	/**
	 * Initialization state
	 * @var bool
	 */
	protected $init = false;
	/**
	 * Name of cache engine
	 * @var string
	 */
	protected $engine;
	/**
	 * Instance of cache engine object
	 *
	 * @var Cache\_Abstract
	 */
	protected $engine_instance;
	/**
	 * Initialization, creating cache engine instance
	 */
	protected function construct () {
		if (!$this->init && $this->state) {
			$this->engine          = Core::instance()->cache_engine;
			$engine_class          = "cs\\Cache\\$this->engine";
			$this->engine_instance = new $engine_class();
		}
	}
	/**
	 * Returns instance for simplified work with cache, when using common prefix
	 *
	 * @param string $prefix
	 *
	 * @return Prefix
	 */
	static function prefix ($prefix) {
		return new Prefix($prefix);
	}
	/**
	 * Get item from cache
	 *
	 * If item not found and $callback parameter specified - closure must return value for item. This value will be set for current item, and returned.
	 *
	 * @param string        $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 * @param callable|null $callback
	 *
	 * @return false|mixed Returns item on success of <b>false</b> on failure
	 */
	function get ($item, $callback = null) {
		if (!$this->state) {
			return is_callable($callback) ? $callback() : false;
		}
		$item = trim($item, '/');
		$data = $this->engine_instance->get($item);
		if ($data === false && is_callable($callback)) {
			$data = $callback();
			if ($data !== false) {
				$this->set($item, $data);
			}
		}
		return $data;
	}
	/**
	 * Put or change data of cache item
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 * @param mixed  $data
	 *
	 * @return bool
	 */
	function set ($item, $data) {
		if (is_object($this->engine_instance)) {
			$this->engine_instance->del($item);
		}
		if (!$this->state) {
			return true;
		}
		$item = trim($item, '/');
		return $this->engine_instance->set($item, $data);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool
	 */
	function del ($item) {
		if (empty($item)) {
			return false;
		}
		/**
		 * Cache cleaning instead of removing when root specified
		 */
		if ($item == '/') {
			return $this->clean();
		}
		if (is_object($this->engine_instance)) {
			$item = trim($item, '/');
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
		if (is_object($this->engine_instance)) {
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
	function cache_state () {
		return $this->state;
	}
	/**
	 * Disable cache
	 */
	function disable () {
		$this->state = false;
	}
	/**
	 * Get item from cache
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return false|mixed            Returns item on success of <b>false</b> on failure
	 */
	function __get ($item) {
		return $this->get($item);
	}
	/**
	 * Put or change data of cache item
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 * @param mixed  $data
	 */
	function __set ($item, $data) {
		$this->set($item, $data);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 */
	function __unset ($item) {
		$this->del($item);
	}
}
