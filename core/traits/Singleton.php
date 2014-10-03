<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
/**
 * Singleton trait
 *
 * Provides Singleton pattern realization
 */
trait Singleton {
	final protected function __construct () {}
	protected function construct () {}
	/**
	 * Get instance of class
	 *
	 * @param bool			$check	If true - checks, if instance was already created, if not - instance of cs\False_class will be returned
	 *
	 * @return False_class|static
	 */
	static function instance ($check = false) {
		static $instance;
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
	final protected function __clone () {}
	final protected function __wakeup() {}
}
