<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;
/**
 * False_class is used for chained calling, when some method may return false.
 *
 * Usage of class is simple, just return his instance instead of real boolean <i>false</i>.
 * On every call of any method or getting of any property instance of the same class will be returned.
 * Also object may be converted to string '0' because of __toString() method.
 */
class False_class {
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
}