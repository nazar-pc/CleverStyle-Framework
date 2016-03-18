<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Response;
use
	cs\Request;

/**
 * Trait is here only to provide compatibility when response object is passed as second argument into controller
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
		return isset(Request::instance()->route_path[$index]);
	}
	/**
	 * @param string $index
	 *
	 * @return mixed
	 */
	function &offsetGet ($index) {
		return Request::instance()->route_path[$index];
	}
	/**
	 * @param string $index
	 * @param mixed  $value
	 */
	function offsetSet ($index, $value) {
		Request::instance()->route_path[$index] = $value;
	}
	/**
	 * @param string $index
	 */
	public function offsetUnset ($index) {
		unset(Request::instance()->route_path[$index]);
	}
	/**
	 * @return mixed Can return any type.
	 */
	public function current () {
		return current(Request::instance()->route_path);
	}
	public function next () {
		next(Request::instance()->route_path);
	}
	/**
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key () {
		return key(Request::instance()->route_path);
	}
	/**
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid () {
		return $this->key() !== null;
	}
	public function rewind () {
		reset(Request::instance()->route_path);
	}
}
