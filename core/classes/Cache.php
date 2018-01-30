<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
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
	 * Name of cache driver
	 * @var string
	 */
	protected $driver;
	/**
	 * Instance of cache driver object
	 *
	 * @var Cache\_Abstract
	 */
	protected $driver_instance;
	/**
	 * Initialization, creating cache driver instance
	 */
	protected function construct () {
		if (!$this->init && $this->state) {
			$this->driver          = Core::instance()->cache_driver;
			$driver_class          = "cs\\Cache\\$this->driver";
			$this->driver_instance = new $driver_class();
		}
	}
	/**
	 * Returns instance for simplified work with cache, when using common prefix
	 *
	 * @param string $prefix
	 *
	 * @return Prefix
	 */
	public static function prefix ($prefix) {
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
	public function get ($item, $callback = null) {
		if (!$this->state) {
			return is_callable($callback) ? $callback() : false;
		}
		$item = trim($item, '/');
		$data = $this->driver_instance->get($item);
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
	public function set ($item, $data) {
		$this->driver_instance->del($item);
		if (!$this->state) {
			return true;
		}
		$item = trim($item, '/');
		return $this->driver_instance->set($item, $data);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool
	 */
	public function del ($item) {
		if (empty($item)) {
			return false;
		}
		/**
		 * Cache cleaning instead of removing when root specified
		 */
		if ($item == '/') {
			return $this->clean();
		}
		$item = trim($item, '/');
		return $this->driver_instance->del($item);
	}
	/**
	 * Clean cache by deleting all items
	 *
	 * @return bool
	 */
	public function clean () {
		return $this->driver_instance->clean();
	}
	/**
	 * Cache state enabled/disabled
	 *
	 * @return bool
	 */
	public function cache_state () {
		return $this->state;
	}
	/**
	 * Disable cache
	 */
	public function disable () {
		$this->state = false;
	}
	/**
	 * Get item from cache
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return false|mixed            Returns item on success of <b>false</b> on failure
	 */
	public function __get ($item) {
		return $this->get($item);
	}
	/**
	 * Put or change data of cache item
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 * @param mixed  $data
	 */
	public function __set ($item, $data) {
		$this->set($item, $data);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 */
	public function __unset ($item) {
		$this->del($item);
	}
}
