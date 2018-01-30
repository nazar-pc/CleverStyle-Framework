<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
namespace cs\Cache;
abstract class _Abstract {
	/**
	 * Get item from cache
	 *
	 * @abstract
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return false|mixed Returns item on success of <b>false</b> on failure
	 */
	abstract public function get ($item);
	/**
	 * Put or change data of cache item
	 *
	 * @abstract
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 * @param mixed  $data
	 *
	 * @return bool
	 */
	abstract public function set ($item, $data);
	/**
	 * Delete item from cache
	 *
	 * @abstract
	 *
	 * @param string $item May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool
	 */
	abstract public function del ($item);
	/**
	 * Clean cache by deleting all items
	 *
	 * @abstract
	 *
	 * @return bool
	 */
	abstract public function clean ();
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	final protected function __clone () {
	}
}
