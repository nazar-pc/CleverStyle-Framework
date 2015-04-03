<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace {
	use
		cs\Config,
		cs\Route;
	/**
	 * Return request id from Request object
	 *
	 * @return string
	 */
	if (!ASYNC_HTTP_SERVER) {
		function get_request_id () {
			return 1;
		}
	} else {
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
				$null = null;
				return $null;
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
	 * @param string   $string             There are two special-case header calls. The first is a header that starts with the string "HTTP/" (case is not
	 *                                     significant), which will be used to figure out the HTTP status code to send. For example, if you have configured
	 *                                     Apache to use a PHP script to handle requests for missing files (using the ErrorDocument directive), you may want to
	 *                                     make sure that your script generates the proper status code.
	 * @param bool     $replace            The optional replace parameter indicates whether the header should replace a previous similar header,
	 *                                     or add a second header of the same type. By default it will replace
	 * @param int|null $http_response_code Forces the HTTP response code to the specified value
	 *
	 * @return mixed
	 */
	function _header ($string, $replace = true, $http_response_code = null) {
		static $headers = [];
		$request_id = get_request_id();
		if ($string === null) {
			if (isset($headers[$request_id])) {
				$return = $headers[$request_id];
				unset($headers[$request_id]);
				return $return;
			}
			return [];
		}
		if (strcasecmp(substr($string, 0, 4), 'http') === 0) {
			_http_response_code(explode(' ', $string)[1], $request_id);
			/** @noinspection PhpInconsistentReturnPointsInspection */
			return;
		}
		if (!isset($headers[$request_id])) {
			$headers[$request_id] = [];
		}
		$string = _trim(explode(':', $string, 2));
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
		$request_id = $request_id ?: get_request_id();
		if ($response_code == 0) {
			if (isset($codes[$request_id])) {
				$code = $codes[$request_id];
				unset($codes[$request_id]);
				return $code;
			}
			return 200;
		}
		return $codes[$request_id] = $response_code;
	}

	/**
	 * Function for setting cookies on all mirrors and taking into account cookies prefix. Parameters like in system function, but $path, $domain and $secure
	 * are skipped, they are detected automatically, and $api parameter added in the end.
	 *
	 * @param string $name
	 * @param string $value
	 * @param int    $expire
	 * @param bool   $httponly
	 *
	 * @return bool
	 */
	function _setcookie ($name, $value, $expire = 0, $httponly = false) {
		static $path, $domain, $prefix, $secure;
		$request_id = get_request_id();
		if (ASYNC_HTTP_SERVER) {
			if (!isset($_COOKIE[$request_id])) {
				$_COOKIE[$request_id] = [];
			}
			$request_cookie = &$_COOKIE[$request_id];
		} else {
			$request_cookie = &$_COOKIE;
		}
		if (!isset($prefix)) {
			$Config = Config::instance(true);
			/**
			 * @var \cs\_SERVER $_SERVER
			 */
			$prefix = '';
			$secure = $_SERVER->secure;
			$domain = $_SERVER->host;
			$path   = '/';
			if ($Config) {
				$Route  = Route::instance();
				$prefix = $Config->core['cookie_prefix'];
				$domain = $Config->core['cookie_domain'][$Route->mirror_index];
				$path   = $Config->core['cookie_path'][$Route->mirror_index];
			}
		}
		if ($value === '') {
			unset($request_cookie[$prefix.$name]);
		} else {
			$request_cookie[$prefix.$name] = $value;
		}
		_header(
			'Set-Cookie: '.
			rawurlencode($prefix.$name).'='.rawurlencode($value).
			($expire || !$value ? '; expires='.gmdate('D, d-M-Y H:i:s', $expire).' GMT' : '').
			($path ? "; path=$path" : '').
			($domain ? "; domain=$domain" : '').
			($secure ? '; secure' : '').
			($httponly ? '; HttpOnly' : ''),
			false
		);
		return true;
	}

	/**
	 * Function for getting of cookies, taking into account cookies prefix
	 *
	 * @param $name
	 *
	 * @return bool
	 */
	function _getcookie ($name) {
		static $prefix;
		$request_id = get_request_id();
		if (ASYNC_HTTP_SERVER) {
			if (!isset($_COOKIE[$request_id])) {
				return false;
			}
			$request_cookie = &$_COOKIE[$request_id];
		} else {
			$request_cookie = &$_COOKIE;
		}
		if (!isset($prefix)) {
			$prefix = Config::instance(true)->core['cookie_prefix'] ?: '';
		}
		return isset($request_cookie[$prefix.$name]) ? $request_cookie[$prefix.$name] : false;
	}

	/**
	 * Function that is used to define errors by specifying error code, and system will account this in its operation
	 *
	 * @param int|null $code
	 *
	 * @return int                <b>0</b> if no errors, error code otherwise
	 */
	function error_code ($code = null) {
		static $stored_code = [];
		$request_id = get_request_id();
		if ($code === -1) {
			unset($stored_code[$request_id]);
			return;
		}
		if (!isset($stored_code[$request_id])) {
			$stored_code[$request_id] = 0;
		}
		if (
			$code !== null &&
			(
				!$stored_code[$request_id] || $code == 0 //Allows to reset error code, but not allows to redefine by other code directly
			)
		) {
			$stored_code[$request_id] = $code;
		}
		return $stored_code[$request_id];
	}

	/**
	 * Is current path from administration area?
	 *
	 * @param bool|null $admin_path
	 *
	 * @return bool
	 */
	function admin_path ($admin_path = null) {
		static $stored_admin_path = [];
		$request_id = get_request_id();
		if ($admin_path === -1) {
			unset($stored_admin_path[$request_id]);
			return true;
		}
		if (!isset($stored_admin_path[$request_id])) {
			$stored_admin_path[$request_id] = false;
		}
		if ($admin_path !== null) {
			$stored_admin_path[$request_id] = $admin_path;
		}
		return $stored_admin_path[$request_id];
	}

	/**
	 * Is current path from api area?
	 *
	 * @param bool|null $api_path
	 *
	 * @return bool
	 */
	function api_path ($api_path = null) {
		static $stored_api_path = [];
		$request_id = get_request_id();
		if ($api_path === -1) {
			unset($stored_api_path[$request_id]);
			return true;
		}
		if (!isset($stored_api_path[$request_id])) {
			$stored_api_path[$request_id] = false;
		}
		if ($api_path !== null) {
			$stored_api_path[$request_id] = $api_path;
		}
		return $stored_api_path[$request_id];
	}

	/**
	 * Name of currently used module (for generation of current page)
	 *
	 * @param null|string $current_module
	 *
	 * @return bool
	 */
	function current_module ($current_module = null) {
		static $stored_current_module = [];
		$request_id = get_request_id();
		if ($current_module === -1) {
			unset($stored_current_module[$request_id]);
			return true;
		}
		if (!isset($stored_current_module[$request_id])) {
			$stored_current_module[$request_id] = '';
		}
		if ($current_module !== null) {
			$stored_current_module[$request_id] = $current_module;
		}
		return $stored_current_module[$request_id];
	}

	/**
	 * Is current page a home page?
	 *
	 * @param bool|null $home_page
	 *
	 * @return bool
	 */
	function home_page ($home_page = null) {
		static $stored_home_page = [];
		$request_id = get_request_id();
		if ($home_page === -1) {
			unset($stored_home_page[$request_id]);
			return true;
		}
		if (!isset($stored_home_page[$request_id])) {
			$stored_home_page[$request_id] = false;
		}
		if ($home_page !== null) {
			$stored_home_page[$request_id] = $home_page;
		}
		return $stored_home_page[$request_id];
	}

	/**
	 * Sends header with string representation of http status code, for example "404 Not Found" for corresponding server protocol
	 *
	 * @param int $code Status code number
	 *
	 * @return null|string String representation of status code code
	 */
	function code_header ($code) {
		$string_code = null;
		switch ($code) {
			case 201:
				$string_code = '201 Created';
				break;
			case 202:
				$string_code = '202 Accepted';
				break;
			case 301:
				$string_code = '301 Moved Permanently';
				break;
			case 302:
				$string_code = '302 Found';
				break;
			case 303:
				$string_code = '303 See Other';
				break;
			case 307:
				$string_code = '307 Temporary Redirect';
				break;
			case 400:
				$string_code = '400 Bad Request';
				break;
			case 403:
				$string_code = '403 Forbidden';
				break;
			case 404:
				$string_code = '404 Not Found';
				break;
			case 405:
				$string_code = '405 Method Not Allowed';
				break;
			case 409:
				$string_code = '409 Conflict';
				break;
			case 429:
				$string_code = '429 Too Many Requests';
				break;
			case 500:
				$string_code = '500 Internal Server Error';
				break;
			case 501:
				$string_code = '501 Not Implemented';
				break;
			case 503:
				$string_code = '503 Service Unavailable';
				break;
		}
		if ($string_code) {
			_http_response_code($code);
		}
		return $string_code;
	}
}

namespace cs\Singleton {
	/**
	 * Remove original system autoloader, new autoloader will store cache in different place
	 */
	spl_autoload_unregister(
		spl_autoload_functions()[0]
	);
	/**
	 * Auto Loading of classes
	 */
	spl_autoload_register(
		function ($class) {
			static $cache;
			if (!isset($cache)) {
				$cache = file_exists(CACHE.'/Http_server/classes/autoload') ? file_get_json(CACHE.'/Http_server/classes/autoload') : [];
			}
			if (isset($cache[$class])) {
				return require_once $cache[$class];
			}
			$prepared_class_name = ltrim($class, '\\');
			if (substr($prepared_class_name, 0, 3) == 'cs\\') {
				$prepared_class_name = substr($prepared_class_name, 3);
			}
			$prepared_class_name = explode('\\', $prepared_class_name);
			$namespace           = count($prepared_class_name) > 1 ? implode('/', array_slice($prepared_class_name, 0, -1)) : '';
			$class_name          = array_pop($prepared_class_name);
			/**
			 * Try to load classes from different places. If not found in one place - try in another.
			 */
			if (
				_require_once($file = DIR."/core/classes/$namespace/$class_name.php", false) ||    //Core classes
				_require_once($file = DIR."/core/thirdparty/$namespace/$class_name.php", false) || //Third party classes
				_require_once($file = DIR."/core/traits/$namespace/$class_name.php", false) ||     //Core traits
				_require_once($file = ENGINES."/$namespace/$class_name.php", false) ||             //Core engines
				_require_once($file = MODULES."/../$namespace/$class_name.php", false)             //Classes in modules and plugins
			) {
				$cache[$class] = realpath($file);
				if (!is_dir(CACHE.'/Http_server/classes')) {
					@mkdir(CACHE.'/Http_server/classes', 0770, true);
				}
				file_put_json(CACHE.'/Http_server/classes/autoload', $cache);
				return true;
			}
			return false;
		},
		true,
		true
	);
	/**
	 * Clean cache of classes autoload and customization
	 */
	function clean_classes_cache () {
		if (file_exists(CACHE.'/Http_server/classes/autoload')) {
			unlink(CACHE.'/Http_server/classes/autoload');
		}
		if (file_exists(CACHE.'/Http_server/classes/modified')) {
			unlink(CACHE.'/Http_server/classes/modified');
		}
	}

	/**
	 * Get or set modified classes (used in Singleton trait)
	 *
	 * @param array|null $updated_modified_classes
	 *
	 * @return array
	 */
	function modified_classes ($updated_modified_classes = null) {
		static $modified_classes;
		if (!isset($modified_classes)) {
			$modified_classes = file_exists(CACHE.'/Http_server/classes/modified') ? file_get_json(CACHE.'/Http_server/classes/modified') : [];
		}
		if ($updated_modified_classes) {
			$modified_classes = $updated_modified_classes;
			file_put_json(CACHE.'/Http_server/classes/modified', $modified_classes);
		}
		return $modified_classes;
	}
}
