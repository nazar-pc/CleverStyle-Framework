<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			ArrayAccess,
			SimpleXMLElement;
/**
 * False_class is used for chained calling, when some method may return false.
 *
 * Usage of class is simple, just return his instance instead of real boolean <i>false</i>.
 * On every call of any method or getting of any property or getting any element of array instance of the this class will be returned.
 * Access to anything of this class instance will be casted to boolean <i>false</i>
 *
 * Inherits SimpleXMLElement in order to be casted from object to boolean as <i>false</i>
 *
 * @property string	$error
 */
class False_class extends SimpleXMLElement implements ArrayAccess {
	/**
	 * Use this method to obtain correct instance
	 *
	 * @return False_class
	 */
	static function instance () {
		static $instance;
		if (!isset($instance)) {
			$instance	= new self('<?xml version=\'1.0\'?><cs></cs>');
		}
		return $instance;
	}
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
