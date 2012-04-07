<?php
/**
 * For providing loaders for some "heavy" objects
 * Such objects will be initialized only if they realy needed, otherwise we can skip their initialization in order
 * to save resources
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
		global $$this->variable, $Objects;
		${$this->variable} = $Objects->load($this->class);
		return ${$this->variable}->$variable;
	}
	function __set ($variable, $value) {
		global $$this->variable, $Objects;
		${$this->variable} = $Objects->load($this->class);
		return ${$this->variable}->$variable = $value;
	}
	function __call ($function, $arguments) {
		global $$this->variable, $Objects;
		${$this->variable} = $Objects->load($this->class);
		return call_user_func_array(array(${$this->variable}, $function), $arguments);
	}
}