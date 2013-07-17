<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
/**
 * Trait Singleton
 */
trait Singleton {
	protected static	$instance	= null;
	final protected function __construct () {}
	protected function construct () {}
	/**
	 * Get instance of class
	 *
	 * @param bool			$check	If true - checks, if instance was already created
	 *
	 * @return bool|$this
	 */
	static function instance ($check = false) {
		if (defined('STOP')) {
			return new False_class;
		}
		if ($check) {
			return (bool)self::$instance;
		}
		/**
		 * Transition compatibility fix
		 */
		if (!self::$instance) {
			$class	= explode('\\', __CLASS__);
			$class	= array_pop($class);
			switch ($class) {
				case 'Language':
					global $L;
					$L = self::$instance	= new self;
					break;
				case 'DB':
					global $db;
					$db = self::$instance	= new self;
					break;
				default:
					global $$class;
					$$class = self::$instance	= new self;
			}
			self::$instance->construct();
		}
		if (self::$instance) {
			return self::$instance;
		}
		if (substr(__CLASS__, 0, 2) == 'cs' && class_exists('cs\\custom'.substr(__CLASS__, 2), false)) {
			self::$instance	= 'cs\\custom'.substr(__CLASS__, 2);
			self::$instance	= new self::$instance;
		} else {
			self::$instance	= new self;
		}
		self::$instance->construct();
		return self::$instance;
	}
	final protected function __clone () {}
	final protected function __wakeup() {}
}