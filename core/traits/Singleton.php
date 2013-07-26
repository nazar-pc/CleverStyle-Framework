<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
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
		if (defined('STOP')) {
			return False_class::instance();
		}
		if ($check) {
			return isset($instance) ? $instance : False_class::instance();
		}
		if (isset($instance)) {
			return $instance;
		}
		if (substr(__CLASS__, 0, 2) == 'cs' && class_exists('cs\\custom'.substr(__CLASS__, 2), false)) {
			$instance	= 'cs\\custom'.substr(__CLASS__, 2);
			$instance	= new $instance;
		} else {
			$instance	= new static;
		}
		$instance->construct();
		return $instance;
	}
	final protected function __clone () {}
	final protected function __wakeup() {}
}