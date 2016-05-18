<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Singleton;
use
	cs\False_class,
	cs\Request;

/**
 * Singleton trait
 *
 * Provides Singleton-like implementation with some advanced capabilities
 */
trait Base {
	private $__request_id;
	final protected function __construct () {
	}
	protected function construct () {
	}
	/**
	 * Get instance of class
	 *
	 * @param bool $check If true - checks, if instance was already created, if not - instance of cs\False_class will be returned
	 *
	 * @return False_class|static
	 */
	static function instance ($check = false) {
		static $instance;
		return self::instance_prototype($instance, $check);
	}
	/**
	 * Get instance of class
	 *
	 * @param static $instance
	 * @param bool   $check If true - checks, if instance was already created, if not - instance of cs\False_class will be returned
	 *
	 * @return False_class|static
	 */
	protected static function instance_prototype (&$instance, $check = false) {
		static::instance_prototype_state_init($instance);
		if ($instance) {
			return $instance;
		}
		if ($check) {
			return False_class::instance();
		}
		$class = get_called_class();
		if (strpos($class, 'cs') !== 0) {
			return False_class::instance();
		}
		list($aliases, $final_class) = static::instance_prototype_get_aliases_final_class($class);
		foreach ($aliases as $alias) {
			/**
			 * If for whatever reason base class does not exists or file that should be included does not exists
			 */
			if (
				!class_exists($alias['original'], false) ||
				!file_exists($alias['path'])
			) {
				clean_classes_cache();
				$instance = new $class;
				static::instance_prototype_state_init($instance);
				$instance->construct();
				return $instance;
			}
			class_alias($alias['original'], $alias['alias']);
			require_once $alias['path'];
		}
		$instance = new $final_class;
		static::instance_prototype_state_init($instance);
		$instance->construct();
		return $instance;
	}
	/**
	 * @param string $class
	 *
	 * @return array
	 */
	protected static function instance_prototype_get_aliases_final_class ($class) {
		$modified_classes = modified_classes();
		if (!isset($modified_classes[$class])) {
			$custom_class_base = 'cs\\custom'.substr($class, 2);
			$next_alias        = $class;
			$aliases           = [];
			if (class_exists($custom_class_base, false)) {
				$next_alias = $custom_class_base;
			}
			$custom_classes = defined('CUSTOM') ? glob(CUSTOM.'/classes/'.substr($class, 2).'_*.php') : [];
			foreach ($custom_classes as $custom_class) {
				// Same path with prefixed class name
				$_custom_class   = explode('/', $custom_class);
				$_custom_class[] = '_'.array_pop($_custom_class);
				$_custom_class   = implode('/', $_custom_class);
				$aliases[]       = [
					'original' => $next_alias,
					'alias'    => "cs\\custom\\$_custom_class",
					'path'     => $custom_class
				];
				$next_alias      = "cs\\custom\\$custom_class";
			}
			$modified_classes[$class] = [
				'aliases'     => $aliases,
				'final_class' => $next_alias
			];
			modified_classes($modified_classes);
		}
		return [
			$modified_classes[$class]['aliases'],
			$modified_classes[$class]['final_class']
		];
	}
	/**
	 * @param static $instance
	 */
	protected static function instance_prototype_state_init (&$instance) {
		if ($instance && $instance->__request_id !== Request::$id) {
			$instance->__request_id = Request::$id;
			if (defined('static::INIT_STATE_METHOD')) {
				$instance->{constant('static::INIT_STATE_METHOD')}();
			}
		}
	}
	final protected function __clone () {
	}
	final protected function __wakeup () {
	}
}
