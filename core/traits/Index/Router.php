<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Index;
/**
 * @property bool     $module          Name of current module
 * @property bool     $in_api          Whether current page is api
 * @property bool     $in_admin        Whether current page is administration and user is admin
 * @property string   $request_method
 * @property string   $working_directory
 * @property string[] $path            Reference to Route::instance()->path
 * @property string[] $ids             Reference to Route::instance()->ids
 * @property string[] $controller_path Path that will be used by controller to render page
 */
trait Router {
	/**
	 * Execute router
	 *
	 * Depending on module, files-based or controller-based router might be used
	 */
	protected function execute_router () {
		$this->check_and_normalize_route();
		if (!error_code()) {
			$router = file_exists("$this->working_directory/Controller.php") ? 'controller_router' : 'files_router';
			$this->$router();
		}
	}
	/**
	 * Normalize route path and fill `cs\Index::$route_path` and `cs\Index::$route_ids` properties
	 */
	protected function check_and_normalize_route () {
		if (!file_exists("$this->working_directory/index.json")) {
			return;
		}
		$structure = file_get_json("$this->working_directory/index.json");
		if (!$structure) {
			return;
		}
		for ($nesting_level = 0; $structure; ++$nesting_level) {
			/**
			 * Next level of routing path
			 */
			$path = @$this->path[$nesting_level];
			/**
			 * If path not specified - take first from structure
			 */
			$code = $this->check_and_normalize_route_internal($path, $structure);
			if ($code !== 200) {
				error_code($code);
				return;
			}
			$this->path[$nesting_level] = $path;
			/**
			 * Fill paths array intended for controller's usage
			 */
			$this->controller_path[] = $path;
			/**
			 * If nested structure is not available - we'll not go into next iteration of this cycle
			 */
			$structure = @$structure[$path];
		}
	}
	/**
	 * @param string $path
	 * @param array  $structure
	 *
	 * @return int HTTP status code
	 */
	protected function check_and_normalize_route_internal (&$path, $structure) {
		/**
		 * If path not specified - take first from structure
		 */
		if (!$path) {
			$path = isset($structure[0]) ? $structure[0] : array_keys($structure)[0];
			/**
			 * We need exact paths for API request (or `_` ending if available) and less strict mode for other cases that allows go deeper automatically
			 */
			if ($path !== '_' && api_path()) {
				return 404;
			}
		} elseif (!isset($structure[$path]) && !in_array($path, $structure)) {
			return 404;
		}
		if (!$this->check_permission($path)) {
			return 403;
		}
		return 200;
	}
	/**
	 * Include files necessary for module page rendering
	 */
	protected function files_router () {
		foreach ($this->controller_path as $index => $path) {
			/**
			 * Starting from index 2 we need to maintain slash-separated string that includes all paths from index 1 and till current
			 */
			if ($index > 1) {
				$path = implode('/', array_slice($this->controller_path, 1, $index));
			}
			$next_exists = isset($this->controller_path[$index + 1]);
			if (!$this->files_router_handler($this->working_directory, $path, !$next_exists)) {
				return;
			}
		}
	}
	/**
	 * Include files that corresponds for specific paths in URL
	 *
	 * @param string $dir
	 * @param string $basename
	 * @param bool   $required
	 *
	 * @return bool
	 */
	protected function files_router_handler ($dir, $basename, $required = true) {
		$this->files_router_handler_internal($dir, $basename, $required);
		return !error_code();
	}
	protected function files_router_handler_internal ($dir, $basename, $required) {
		$included = _include("$dir/$basename.php", false, false) !== false;
		if (!api_path()) {
			return;
		}
		$included = _include("$dir/$basename.$this->request_method.php", false, false) !== false || $included;
		if ($included || !$required) {
			return;
		}
		$methods = get_files_list($dir, "/^$basename\\.[a-z]+\\.php$/");
		$methods = _strtoupper(_substr($methods, strlen($basename) + 1, -4));
		$this->handler_not_found($methods);
	}
	/**
	 * If HTTP method handler not found we generate either `501 Not Implemented` if other methods are supported or `404 Not Found` if handlers for others
	 * methods also doesn't exist
	 *
	 * @param string[] $available_methods
	 */
	protected function handler_not_found ($available_methods) {
		if ($available_methods) {
			$available_methods = implode(', ', $available_methods);
			_header("Allow: $available_methods");
			if ($this->request_method !== 'options') {
				error_code(501);
			}
		} else {
			error_code(404);
		}
	}
	/**
	 * Call methods necessary for module page rendering
	 */
	protected function controller_router () {
		$suffix = '';
		if ($this->in_admin) {
			$suffix = '\\admin';
		} elseif ($this->in_api) {
			$suffix = '\\api';
		}
		$controller_class = "cs\\modules\\$this->module$suffix\\Controller";
		foreach ($this->controller_path as $index => $path) {
			/**
			 * Starting from index 2 we need to maintain underscore-separated string that includes all paths from index 1 and till current
			 */
			if ($index > 1) {
				$path = implode('_', array_slice($this->controller_path, 1, $index));
			}
			$next_exists = isset($this->controller_path[$index + 1]);
			if (!$this->controller_router_handler($controller_class, $path, !$next_exists)) {
				return;
			}
		}
	}
	/**
	 * Call methods that corresponds for specific paths in URL
	 *
	 * @param string $controller_class
	 * @param string $method_name
	 * @param bool   $required
	 *
	 * @return bool
	 */
	protected function controller_router_handler ($controller_class, $method_name, $required = true) {
		$method_name = str_replace('.', '_', $method_name);
		$this->controller_router_handler_internal($controller_class, $method_name, $required);
		return !error_code();
	}
	/**
	 * @param string $controller_class
	 * @param string $method_name
	 * @param bool   $required
	 */
	protected function controller_router_handler_internal ($controller_class, $method_name, $required) {
		$included =
			method_exists($controller_class, $method_name) &&
			$controller_class::$method_name($this->ids, $this->path) !== false;
		if (!api_path()) {
			return;
		}
		$included =
			method_exists($controller_class, $method_name.'_'.$this->request_method) &&
			$controller_class::{$method_name.'_'.$this->request_method}($this->ids, $this->path) !== false ||
			$included;
		if ($included || !$required) {
			return;
		}
		$methods = array_filter(
			get_class_methods($controller_class),
			function ($method) use ($method_name) {
				return preg_match("/^{$method_name}_[a-z]+$/", $method);
			}
		);
		$methods = _strtoupper(_substr($methods, strlen($method_name) + 1));
		$this->handler_not_found($methods);
	}
}
