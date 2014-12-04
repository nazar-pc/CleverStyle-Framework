<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Test
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
/**
 * Singleton trait (actually not at all, hackable thing for tests)
 */
trait Singleton {
	final protected function __construct () {}
	protected function construct () {}
	/**
	 * Get instance of class
	 *
	 * @param bool					$check	If true - checks, if instance was already created, if not - instance of cs\False_class will be returned
	 *
	 * @return False_class|static
	 */
	static function instance ($check = false) {
		return static::instance_internal($check);
	}
	/**
	 * @param bool					$check
	 * @param bool|object|null		$replace_with
	 *
	 * @return False_class|static
	 */
	static private function instance_internal ($check = false, $replace_with = false) {
		static $instance;
		if ($replace_with !== false) {
			if ($replace_with === null) {
				unset($instance);
				return False_class::instance();
			}
			$instance	= $replace_with;
			return $instance;
		}
		if ($check) {
			return isset($instance) ? $instance : False_class::instance();
		}
		if (isset($instance)) {
			return $instance;
		}
		$class	= ltrim(get_called_class(), '\\');
		if (substr($class, 0, 2) == 'cs' && class_exists('cs\\custom'.substr($class, 2), false)) {
			$instance	= 'cs\\custom'.substr($class, 2);
			$instance	= $instance::instance();
		} else {
			$instance	= new static;
		}
		$instance->construct();
		return $instance;
	}
	/**
	 * Stub instance with custom object that will contain properties and methods specified here
	 *
	 * @param mixed[]				$properties	Default properties of object
	 * @param callable[]|string[]	$methods	Methods of object - if callable - will be called, if not - will be used as return values
	 *
	 * @return False_class|static
	 */
	static function instance_stub ($properties = [], $methods = []) {
		return static::instance_internal(
			false,
			new Mock_object($properties, $methods)
		);
	}
	/**
	 * Reset instance, so that previously created instance or mocked custom object will be removed
	 */
	static function instance_reset () {
		static::instance_internal(false, null);
	}
	/**
	 * Replace instance with custom object
	 *
	 * @param	object	$object
	 *
	 * @return False_class|static
	 */
	static function instance_replace ($object) {
		return static::instance_internal(false, $object);
	}
	final protected function __clone () {}
	final protected function __wakeup() {}
}
