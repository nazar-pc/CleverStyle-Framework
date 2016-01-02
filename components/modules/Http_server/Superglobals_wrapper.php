<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Http_server;
use
	ArrayAccess,
	Iterator;

/**
 * Wrapper for `$_SERVER` `$_GET`, `$_POST`, `$_REQUEST` for http server
 */
class Superglobals_wrapper implements ArrayAccess, Iterator {
	/**
	 * @var array
	 */
	protected $requests = [];
	/**
	 * Whether key exists (from original superglobal)
	 *
	 * @param string $index
	 *
	 * @return bool
	 */
	function offsetExists ($index) {
		$request_id = get_request_id();
		return isset($this->requests[$request_id][$index]);
	}
	/**
	 * Get key (from original superglobal)
	 *
	 * @param string $index
	 *
	 * @return mixed
	 */
	function offsetGet ($index) {
		$request_id = get_request_id();
		return $this->requests[$request_id][$index];
	}
	/**
	 * Set key (from original superglobal)
	 *
	 * @param string $index
	 * @param mixed  $value
	 */
	function offsetSet ($index, $value) {
		$request_id = get_request_id();
		if ($index === $request_id) {
			$this->requests[$index] = $value;
			return;
		}
		$this->requests[$request_id][$index] = $value;
	}
	/**
	 * Unset key (from original superglobal)
	 *
	 * @param string $index
	 */
	function offsetUnset ($index) {
		$request_id = get_request_id();
		if ($index === $request_id) {
			unset($this->requests[$index]);
			return;
		}
		unset($this->requests[$request_id][$index]);
	}
	/**
	 * Get current (from original superglobal)
	 *
	 * @return mixed Can return any type.
	 */
	function current () {
		$request_id = get_request_id();
		/**
		 * Workaround with checking for object is for HHVM
		 */
		return is_object($this->requests[$request_id]) ? $this->requests[$request_id]->current() : current($this->requests[$request_id]);
	}
	/**
	 * Move forward to next element (from original superglobal)
	 */
	function next () {
		$request_id = get_request_id();
		/**
		 * Workaround with checking for object is for HHVM
		 */
		is_object($this->requests[$request_id]) ? $this->requests[$request_id]->next() : next($this->requests[$request_id]);
	}
	/**
	 * Return the key of the current element (from original superglobal)
	 *
	 * @return mixed scalar on success, or null on failure.
	 */
	function key () {
		$request_id = get_request_id();
		/**
		 * Workaround with checking for object is for HHVM
		 */
		return is_object($this->requests[$request_id]) ? $this->requests[$request_id]->key() : key($this->requests[$request_id]);
	}
	/**
	 * Checks if current position is valid (from original superglobal)
	 *
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	function valid () {
		return $this->key() !== null;
	}
	/**
	 * Rewind the Iterator to the first element (from original superglobal)
	 */
	function rewind () {
		$request_id = get_request_id();
		/**
		 * Workaround with checking for object is for HHVM
		 */
		return is_object($this->requests[$request_id]) ? $this->requests[$request_id]->rewind() : reset($this->requests[$request_id]);
	}
	function __get ($index) {
		$request_id = get_request_id();
		return $this->requests[$request_id]->$index;
	}
}
