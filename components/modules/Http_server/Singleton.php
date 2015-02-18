<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	cs\Singleton\Base;
/**
 * @inheritdoc
 */
trait Singleton {
	use Base;
	/**
	 * @inheritdoc
	 */
	static function instance ($check = false) {
		static $instance;
		$class = get_called_class();
		if (in_array($class, [
			'cs\\Config',
			'cs\\Event',
			'cs\\Index',
			'cs\\Menu',
			'cs\\Page',
			'cs\\Page\\Meta',
			'cs\\User'
		])) {
			$request_id   = get_request_id();
			$objects_pool = &objects_pool($request_id);
			if (isset($objects_pool[$class]) && $objects_pool[$class]) {
				return $objects_pool[$class];
			}
			return self::instance_prototype($objects_pool[$class], $check);
		}
		return self::instance_prototype($instance, $check);
	}
}
