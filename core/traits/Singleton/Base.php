<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Singleton;
use
	cs\False_class;
/**
 * Singleton trait
 *
 * Provides Singleton pattern implementation
 */
trait Base {
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
	 * @param object $instance
	 * @param bool   $check If true - checks, if instance was already created, if not - instance of cs\False_class will be returned
	 *
	 * @return False_class|static
	 */
	protected static function instance_prototype (&$instance, $check = false) {
		if ($check) {
			return $instance ?: False_class::instance();
		}
		if ($instance) {
			return $instance;
		}
		$class = get_called_class();
		if (substr($class, 0, 2) != 'cs') {
			return False_class::instance();
		}
		$custom_class_base = 'cs\\custom'.substr($class, 2);
		$next_alias        = $class;
		if (class_exists($custom_class_base, false)) {
			$next_alias = $custom_class_base;
		}
		$modified_classes = modified_classes();
		if (!isset($modified_classes[$class])) {
			$aliases                  = [];
			$modified_classes[$class] = [
				'aliases'     => &$aliases,
				'final_class' => &$next_alias
			];
			$classes                  = glob(CUSTOM.'/classes/'.substr($class, 2).'_*.php');
			foreach ($classes as $custom_class) {
				// Path to file with customized class
				$custom_class = str_replace(CUSTOM.'/classes/', '', substr($custom_class, 0, -4));
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
			if (!is_dir(CACHE.'/classes')) {
				@mkdir(CACHE.'/classes', 0770, true);
			}
			modified_classes($modified_classes);
		}
		foreach ($modified_classes[$class]['aliases'] as $alias) {
			/**
			 * If for whatever reason base class does or file that should be included does not exists
			 */
			if (
				!class_exists($alias['original'], false) ||
				!file_exists(CUSTOM."/classes/$alias[path].php")
			) {
				clean_classes_cache();
				$instance = new $class;
				$instance->construct();
				return $instance;
			}
			class_alias($alias['original'], $alias['alias']);
			require_once CUSTOM."/classes/$alias[path].php";
		}
		$instance = new $modified_classes[$class]['final_class'];
		$instance->construct();
		return $instance;
	}
	final protected function __clone () {
	}
	final protected function __wakeup () {
	}
}
