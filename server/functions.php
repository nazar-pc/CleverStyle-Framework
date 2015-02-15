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
	foreach (debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS) as $item) {
		if (isset($item['object']->__request_id)) {
			return $item['object']->__request_id;
		}
	}
	return '';
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
	if ($update_objects_pool) {
		$objects_pool = $update_objects_pool;
	}
	return $objects_pool;
}
