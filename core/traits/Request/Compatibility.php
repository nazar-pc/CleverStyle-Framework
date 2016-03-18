<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;

/**
 * Trait is here only to provide compatibility when request object is passed as first argument into controller
 *
 * @todo Remove in 4.x
 */
trait Compatibility {
	/**
	 * @param string $index
	 *
	 * @return bool
	 */
	function offsetExists ($index) {
		return isset($this->route_ids[$index]);
	}
	/**
	 * @param string $index
	 *
	 * @return mixed
	 */
	function &offsetGet ($index) {
		return $this->route_ids[$index];
	}
	/**
	 * @param string $index
	 * @param mixed  $value
	 */
	function offsetSet ($index, $value) {
		$this->route_ids[$index] = $value;
	}
	/**
	 * @param string $index
	 */
	public function offsetUnset ($index) {
		unset($this->route_ids[$index]);
	}
	/**
	 * @return mixed Can return any type.
	 */
	public function current () {
		return current($this->route_ids);
	}
	public function next () {
		next($this->route_ids);
	}
	/**
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key () {
		return key($this->route_ids);
	}
	/**
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid () {
		return $this->key() !== null;
	}
	public function rewind () {
		reset($this->route_ids);
	}
}
