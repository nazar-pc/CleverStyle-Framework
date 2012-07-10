<?php
namespace cs;
/**
 * For providing loaders for some "heavy" or/and rarely used objects.
 * Such objects will be actually created only at first usage, otherwise we can skip their creation in order to save resources.
 */
class Loader {
	protected	$class,
				$variable;
	/**
	 * @param string $class
	 * @param string $variable
	 */
	function __construct ($class, $variable) {
		$this->class	= $class;
		$this->variable	= $variable;
	}
	function __get ($variable) {
		global ${$this->variable}, $Core;
		${$this->variable} = $Core->create($this->class);
		return ${$this->variable}->$variable;
	}
	function __set ($variable, $value) {
		global ${$this->variable}, $Core;
		${$this->variable} = $Core->create($this->class);
		return ${$this->variable}->$variable = $value;
	}
	function __call ($function, $arguments) {
		global ${$this->variable}, $Core;
		${$this->variable} = $Core->create($this->class);
		return call_user_func_array(
			[
				${$this->variable},
				$function
			],
			$arguments
		);
	}
}