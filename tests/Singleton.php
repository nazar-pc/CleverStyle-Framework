<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
use
	cs\Singleton\Base;
/**
 * Singleton trait (actually not at all, hackable thing for tests)
 */
trait Singleton {
	use Base;
	/**
	 * @inheritdoc
	 */
	static function instance ($check = false) {
		return static::instance_internal($check);
	}
	/**
	 * @param bool             $check
	 * @param bool|object|null $replace_with
	 *
	 * @return False_class|static
	 */
	static private function instance_internal ($check = false, $replace_with = false) {
		static $instance;
		if ($replace_with !== false) {
			$instance = $replace_with;
			return $instance;
		}
		return self::instance_prototype($instance, $check);
	}
	/**
	 * Stub instance with custom object that will contain properties and methods specified here
	 *
	 * @param mixed[]             $properties Default properties of object
	 * @param callable[]|string[] $methods    Methods of object - if callable - will be called, if not - will be used as return values
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
	 * @param    object $object
	 *
	 * @return False_class|static
	 */
	static function instance_replace ($object) {
		return static::instance_internal(false, $object);
	}
}
