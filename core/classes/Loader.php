<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;
/**
 * For providing loaders for some "heavy" or/and rarely used objects.
 * Such objects will be actually created only at first usage, otherwise we can skip their creation in order to save resources.
 */
class Loader {
	protected	$class,
				$variable;
	/**
	 * Saving information about class name, and name of object variable in global scope
	 *
	 * @param string	$class
	 * @param string	$variable
	 */
	function __construct ($class, $variable) {
		$this->class	= $class;
		$this->variable	= $variable;
	}
	/**
	 * Creating real object on property getting
	 *
	 * @param string	$property
	 *
	 * @return mixed
	 */
	function __get ($property) {
		global ${$this->variable}, $Core;
		${$this->variable} = $Core->create($this->class);
		return ${$this->variable}->$property;
	}
	/**
	 * Creating real object on property setting
	 *
	 * @param string	$property
	 * @param mixed		$value
	 *
	 * @return mixed
	 */
	function __set ($property, $value) {
		global ${$this->variable}, $Core;
		${$this->variable} = $Core->create($this->class);
		return ${$this->variable}->$property = $value;
	}
	/**
	 * Creating real object on method calling
	 *
	 * @param string	$method
	 * @param mixed[]	$arguments
	 *
	 * @return mixed
	 */
	function __call ($method, $arguments) {
		global ${$this->variable}, $Core;
		${$this->variable} = $Core->create($this->class);
		return call_user_func_array(
			[
				${$this->variable},
				$method
			],
			$arguments
		);
	}
}