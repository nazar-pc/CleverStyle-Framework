<?php
/**
 * @package    CleverStyle Framework
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
	protected $properties;
	/**
	 * Methods of object will be actually stored here
	 * @var callable[]|mixed[]
	 */
	protected $methods;
	/**
	 * Multi methods of object will be actually stored here
	 * @var callable[][]|mixed[][]
	 */
	protected $methods_multi;
	/**
	 * @var int[]
	 */
	protected $methods_multi_index;
	/**
	 * @param mixed[]                $properties
	 * @param callable[]|mixed[]     $methods
	 * @param callable[][]|mixed[][] $methods_multi
	 */
	function __construct ($properties, $methods, $methods_multi) {
		$this->properties          = $properties;
		$this->methods             = $methods;
		$this->methods_multi       = $methods_multi;
		$this->methods_multi_index = array_combine(
			array_keys($methods_multi),
			array_fill(0, count($methods_multi), 0)
		);
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
	/**
	 * @param string $method
	 * @param array  $arguments
	 *
	 * @return callable|mixed|null
	 */
	function __call ($method, $arguments) {
		if (isset($this->methods[$method])) {
			$method = $this->methods[$method];
			if (is_callable($method)) {
				return $method(...$arguments);
			}
			return $method;
		} elseif (isset($this->methods_multi[$method])) {
			$method = $this->methods_multi[$method][$this->methods_multi_index[$method]++];
			if (is_callable($method)) {
				return $method(...$arguments);
			}
			return $method;
		} else {
			return null;
		}
	}
}
