<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Cache;
use
	cs\Cache;

/**
 * Class for simplified work with cache, when using common prefix
 */
class Prefix {
	protected $prefix;
	/**
	 * Initialization with some prefix
	 *
	 * @param string $prefix
	 */
	function __construct ($prefix) {
		$this->prefix = $prefix;
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
		return Cache::instance()->get("$this->prefix/$item", $callback);
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
		return Cache::instance()->set("$this->prefix/$item", $data);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool
	 */
	function del ($item) {
		return Cache::instance()->del("$this->prefix/$item");
	}
	/**
	 * Get item from cache
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return false|mixed Returns item on success of <b>false</b> on failure
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
