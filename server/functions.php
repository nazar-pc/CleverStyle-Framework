<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * Return request id based on backtrace (maximum to Request object)
 *
 * @return string
 */
function get_request_id () {
	static $request_index;
	$backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS);
	if (!isset($request_index)) {
		foreach (array_reverse($backtrace) as $i => $b) {
			if (isset($b['object']->__request_id)) {
				$request_index = -$i - 1;
				break;
			}
		}
	}
	return array_slice($backtrace, $request_index, 1)[0]['object']->__request_id;
}

/**
 * Objects pool for usage in Singleton, optimized for WebServer
 *
 * @param null|object[] $update_objects_pool
 *
 * @return object[]
 */
function &objects_pool ($update_objects_pool = null) {
	/**
	 * @var object[] $objects_pool
	 */
	static $objects_pool = [];
	if (is_array($update_objects_pool)) {
		$objects_pool = $update_objects_pool;
	}
	return $objects_pool;
}
