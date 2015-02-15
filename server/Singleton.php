<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2015, Nazar Mokrynskyi
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
			'cs\\Cache',
			'cs\\Config',
			'cs\\Core',
			'cs\\Encryption',
			'cs\\Event',
			'cs\\Group',
			'cs\\Index',
			'cs\\Key',
			'cs\\Language',
			'cs\\Menu',
			'cs\\Page',
			'cs\\Page\\Meta',
			'cs\\Permission',
			'cs\\User'
		])) {
			$request_id   = get_request_id();
			$objects_pool = &objects_pool();
			if (!isset($objects_pool[$request_id])) {
				$objects_pool[$request_id] = [];
			}
			$request_pool = &$objects_pool[$request_id];
			if (isset($request_pool[$class]) && $request_pool[$class]) {
				return $request_pool[$class];
			}
			$request_pool[$class] = self::instance_prototype($request_pool[$class], $check);
			if ($request_pool[$class]) {
				$request_pool[$class]->__request_id = $request_id;
			}
			return $request_pool[$class];
		}
		return self::instance_prototype($instance, $check);
	}
}
