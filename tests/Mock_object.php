<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
/**
 * Mock object that accepts properties and methods in constructor and is used to simulate behavior of other class to some needed extent
 */
class Mock_object {
	/**
	 * Properties of object will be actually stored here (both passed in constructor, and created later)
	 * @var mixed[]
	 */
	protected $properties = [];
	/**
	 * Methods of object will be actually stored here
	 * @var callable[]|string[]
	 */
	protected $methods = [];
	/**
	 * @param mixed[]             $properties
	 * @param callable[]|string[] $methods
	 */
	function __construct ($properties, $methods) {
		$this->properties = $properties;
		$this->methods    = $methods;
	}
	function &__get ($property) {
		if (!isset($this->properties[$property])) {
			$null = null;
			return $null;
		}
		return $this->properties[$property];
	}
	function __set ($property, $value) {
		$this->properties[$property] = $value;
	}
	function __isset ($property) {
		return isset($this->properties[$property]);
	}
	function __unset ($property) {
		unset($this->properties[$property]);
	}
	function __call ($method, $arguments) {
		if (!isset($this->methods[$method])) {
			return null;
		}
		$method = $this->methods[$method];
		if (is_callable($method)) {
			return call_user_func_array($method, $arguments);
		}
		return $method;
	}
}
