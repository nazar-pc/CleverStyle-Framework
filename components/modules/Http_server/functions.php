<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace {
	use
		cs\Config,
		cs\Request,
		cs\Response;

	/**
	 * Objects pool for usage in Singleton, optimized for WebServer
	 *
	 * @param null|object[] $update_objects_pool
	 *
	 * @return object[]
	 */
	function &objects_pool ($update_objects_pool = null) {
		static $objects_pool = [];
		if (is_array($update_objects_pool)) {
			$objects_pool = $update_objects_pool;
		}
		return $objects_pool;
	}

	/**
	 * Send a raw HTTP header (similar to built-in function)
	 *
	 * @deprecated Use `cs\Response` instead
	 * @todo       Remove in 4.x
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
		$Response = Response::instance();
		list($field, $value) = explode(';', $string, 2);
		$Response->header(trim($field), trim($value), $replace);
		if ($http_response_code !== null) {
			$Response->code = $http_response_code;
		}
	}

	/**
	 * Get or Set the HTTP response code (similar to built-in function)
	 *
	 * @deprecated Use `cs\Request::$code` instead
	 * @todo       Remove in 4.x
	 *
	 * @param int $response_code The optional response_code will set the response code.
	 *
	 * @return int The current response code. By default the return value is int(200).
	 */
	function _http_response_code ($response_code) {
		Response::instance()->code = $response_code;
	}

	/**
	 * Function for setting cookies on all mirrors and taking into account cookies prefix. Parameters like in system function, but $path, $domain and $secure
	 * are skipped, they are detected automatically, and $api parameter added in the end.
	 *
	 * @deprecated Use `cs\Response::cookie()` instead
	 * @todo       Remove in 4.x
	 *
	 * @param string $name
	 * @param string $value
	 * @param int    $expire
	 * @param bool   $httponly
	 *
	 * @return bool
	 */
	function _setcookie ($name, $value, $expire = 0, $httponly = false) {
		Response::instance()->cookie($name, $value, $expire, $httponly);
		return true;
	}

	/**
	 * Function for getting of cookies, taking into account cookies prefix
	 *
	 * @deprecated Use `cs\Request::$cookie` instead
	 * @todo       Remove in 4.x
	 *
	 * @param $name
	 *
	 * @return false|string
	 */
	function _getcookie ($name) {
		$Request = Request::instance();
		return isset($Request->cookie[$name]) ? $Request->cookie[$name] : false;
	}

	/**
	 * Is current path from administration area?
	 *
	 * @param bool|null $admin_path
	 *
	 * @return bool
	 */
	function admin_path ($admin_path = null) {
		static $stored_admin_path = false;
		if ($admin_path !== null) {
			$stored_admin_path = (bool)$admin_path;
		}
		return $stored_admin_path;
	}

	/**
	 * Is current path from api area?
	 *
	 * @param bool|null $api_path
	 *
	 * @return bool
	 */
	function api_path ($api_path = null) {
		static $stored_api_path = false;
		if ($api_path !== null) {
			$stored_api_path = (bool)$api_path;
		}
		return $stored_api_path;
	}

	/**
	 * Name of currently used module (for generation of current page)
	 *
	 * @param null|string $current_module
	 *
	 * @return string
	 */
	function current_module ($current_module = null) {
		static $stored_current_module = '';
		if ($current_module !== null) {
			$stored_current_module = $current_module;
		}
		return $stored_current_module;
	}

	/**
	 * Is current page a home page?
	 *
	 * @param bool|null $home_page
	 *
	 * @return bool
	 */
	function home_page ($home_page = null) {
		static $stored_home_page = false;
		if ($home_page !== null) {
			$stored_home_page = (bool)$home_page;
		}
		return $stored_home_page;
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
			if (strpos($prepared_class_name, 'cs\\') === 0) {
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
				/** @noinspection MkdirRaceConditionInspection */
				@mkdir(CACHE.'/Http_server/classes', 0770, true);
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
