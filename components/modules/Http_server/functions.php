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

	require DIR.'/core/functions_global.php';
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
