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
 * @param string        $request_id
 * @param null|object[] $update_objects_pool
 *
 * @return object[]
 */
function &objects_pool ($request_id, $update_objects_pool = null) {
	static $objects_pool = [];
	if (!isset($objects_pool[$request_id])) {
		$objects_pool[$request_id] = [];
	}
	if (is_array($update_objects_pool)) {
		if (empty($update_objects_pool)) {
			unset($objects_pool[$request_id]);
		} else {
			$objects_pool[$request_id] = $update_objects_pool;
		}
	}
	return $objects_pool[$request_id];
}

/** @noinspection PhpInconsistentReturnPointsInspection */
/**
 * Send a raw HTTP header (similar to built-in function)
 *
 * @param string $string             There are two special-case header calls. The first is a header that starts with the string "HTTP/" (case is not significant),
 *                                   which will be used to figure out the HTTP status code to send. For example, if you have configured Apache to use a PHP script
 *                                   to handle requests for missing files (using the ErrorDocument directive),
 *                                   you may want to make sure that your script generates the proper status code.
 * @param bool   $replace            The optional replace parameter indicates whether the header should replace a previous similar header,
 *                                   or add a second header of the same type. By default it will replace
 * @param null   $http_response_code Forces the HTTP response code to the specified value
 *
 * @return mixed
 */
function _header ($string, $replace = true, $http_response_code = null) {
	static $headers = [];
	$request_id = get_request_id();
	if (strcasecmp(substr($string, 0, 4), 'http') === 0) {
		_http_response_code(explode(' ', $string)[1], $request_id);
		/** @noinspection PhpInconsistentReturnPointsInspection */
		return;
	}
	if ($string === null) {
		if (isset($headers[$request_id])) {
			$return = $headers[$request_id];
			unset($headers[$request_id]);
			return $return;
		}
		return [];
	}
	if (!isset($headers[$request_id])) {
		$headers[$request_id] = [];
	}
	$string = _trim(explode(':', $string));
	if ($replace) {
		$headers[$request_id][$string[0]] = [$string[1]];
	} else {
		$headers[$request_id][$string[0]][] = $string[1];
	}
	if ($http_response_code) {
		_http_response_code($http_response_code, $request_id);
	}
}

/**
 * Get or Set the HTTP response code (similar to built-in function)
 *
 * @param int  $response_code The optional response_code will set the response code.
 * @param null $request_id    Used internally
 *
 * @return int The current response code. By default the return value is int(200).
 */
function _http_response_code ($response_code = 0, $request_id = null) {
	static $codes = [];
	$request_id    = $request_id ?: get_request_id();
	$response_code = $response_code ?: 200;
	if ($response_code < 1) {
		if (isset($codes[$request_id])) {
			$code = $codes[$request_id];
			unset($codes[$request_id]);
			return $code;
		}
		return 200;
	}
	return $codes[$request_id] = $response_code;
}
