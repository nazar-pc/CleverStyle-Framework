<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			ArrayAccess;
/**
 * False_class is used for chained calling, when some method may return false.

 * Usage of class is simple, just return his instance instead of real boolean <i>false</i>.
 * On every call of any method or getting of any property instance of the same class will be returned.
 * Also object may be converted to string '0' because of __toString() method,
 * and may be used as array - every item will not exist, but in case of access trial - again instance of this class will be returned.
 */
class False_class implements ArrayAccess {
	/**
	 * Getting any property
	 *
	 * @param string		$item
	 *
	 * @return False_class
	 */
	function __get ($item) {
		return $this;
	}
	/**
	 * Calling of any method
	 *
	 * @param string	$method
	 * @param mixed[]	$params
	 *
	 * @return False_class
	 */
	function __call ($method, $params) {
		return $this;
	}
	/**
	 * @return string
	 */
	function __toString () {
		return '0';
	}
	/**
	 * If item exists
	 */
	function offsetExists ($offset) {
		return false;
	}
	/**
	 * Get item
	 */
	function offsetGet ($offset) {
		return $this;
	}
	/**
	 * Set item
	 */
	public function offsetSet ($offset, $value) {}
	/**
	 * Delete item
	 */
	public function offsetUnset ($offset) {}
}