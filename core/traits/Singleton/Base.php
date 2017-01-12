<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2017, Nazar Mokrynskyi
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
	public static function instance ($check = false) {
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
		if (is_object($instance)) {
			static::instance_reinit($instance);
			return $instance;
		}
		$called_class = get_called_class();
		if ($check || strpos($called_class, 'cs') !== 0) {
			return False_class::instance();
		}
		list($aliases, $final_class) = static::instance_prototype_get_aliases_final_class($called_class);
		foreach ($aliases as $alias) {
			/**
			 * If for whatever reason base class does not exists or file that should be included does not exists
			 */
			if (
				!class_exists($alias['original'], false) ||
				!file_exists($alias['path'])
			) {
				__classes_clean_cache();
				return static::instance_create($instance, $called_class, $called_class);
			}
			class_alias($alias['original'], $alias['alias']);
			require_once $alias['path'];
		}
		return static::instance_create($instance, $called_class, $final_class);
	}
	/**
	 * @param static $instance
	 */
	protected static function instance_reinit (&$instance) {
		if ($instance && $instance->__request_id !== Request::$id) {
			$instance->__request_id = Request::$id;
			if (defined('static::INIT_STATE_METHOD')) {
				$instance->{constant('static::INIT_STATE_METHOD')}();
			}
		}
	}
	/**
	 * @param static $instance
	 * @param string $called_class
	 * @param string $final_class
	 *
	 * @return static
	 */
	protected static function instance_create (&$instance, $called_class, $final_class) {
		if ($final_class != $called_class) {
			/**
			 * We can't access protected methods of class if it doesn't extend current class, so let's call its `::instance()` method instead
			 */
			return $final_class::instance();
		}
		$instance               = new $called_class;
		$instance->__request_id = Request::$id;
		$instance->construct();
		if (defined('static::INIT_STATE_METHOD')) {
			$instance->{constant('static::INIT_STATE_METHOD')}();
		}
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
			$custom_classes_paths = defined('CUSTOM') ? glob(CUSTOM.'/classes/'.str_replace('\\', '/', substr($class, 3)).'_*.php') : [];
			$custom_length        = defined('CUSTOM') ? strlen(CUSTOM.'/classes/') : 0;
			foreach ($custom_classes_paths as $custom_class_path) {
				$custom_class = substr($custom_class_path, $custom_length, -4);
				$custom_class = 'cs\\custom\\'.str_replace('/', '\\', $custom_class);
				// Same path with prefixed class name
				$_custom_class   = explode('\\', $custom_class);
				$_custom_class[] = '_'.array_pop($_custom_class);
				$_custom_class   = implode('\\', $_custom_class);
				$aliases[]       = [
					'original' => $next_alias,
					'alias'    => $_custom_class,
					'path'     => $custom_class_path
				];
				$next_alias      = $custom_class;
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
	final protected function __clone () {
	}
	final protected function __wakeup () {
	}
}
