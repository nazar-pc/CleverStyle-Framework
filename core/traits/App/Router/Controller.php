<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\App\Router;
use
	cs\Page,
	cs\Response;

/**
 * @property string[] $controller_path Path that will be used by controller to render page
 */
trait Controller {
	/**
	 * Call methods necessary for module page rendering
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
	 */
	protected function controller_router ($Request) {
		$suffix = '';
		if ($Request->cli_path) {
			$suffix = '\\cli';
		} elseif ($Request->admin_path) {
			$suffix = '\\admin';
		} elseif ($Request->api_path) {
			$suffix = '\\api';
		}
		$controller_class = class_exists("cs\\custom\\modules\\$Request->current_module$suffix\\Controller")
			? "cs\\custom\\modules\\$Request->current_module$suffix\\Controller"
			: "cs\\modules\\$Request->current_module$suffix\\Controller";
		foreach ($this->controller_path as $index => $path) {
			/**
			 * Starting from index 2 we need to maintain underscore-separated string that includes all paths from index 1 and till current
			 */
			if ($index > 1) {
				$path = implode('_', array_slice($this->controller_path, 1, $index));
			}
			$next_exists = isset($this->controller_path[$index + 1]);
			$this->controller_router_handler($Request, $controller_class, $path, !$next_exists);
		}
	}
	/**
	 * Call methods that corresponds for specific paths in URL
	 *
	 * @param \cs\Request $Request
	 * @param string      $controller_class
	 * @param string      $method_name
	 * @param bool        $required
	 *
	 * @throws \cs\ExitException
	 */
	protected function controller_router_handler ($Request, $controller_class, $method_name, $required = true) {
		$method_name = str_replace('.', '_', $method_name);
		$this->controller_router_handler_internal($Request, $controller_class, $method_name, $required);
	}
	/**
	 * @param \cs\Request $Request
	 * @param string      $controller_class
	 * @param string      $method_name
	 * @param bool        $required
	 *
	 * @throws \cs\ExitException
	 */
	protected function controller_router_handler_internal ($Request, $controller_class, $method_name, $required) {
		$Response = Response::instance();
		$found    = $this->controller_router_handler_internal_execute($controller_class, $method_name, $Request, $Response);
		if (!$Request->cli_path && !$Request->api_path) {
			return;
		}
		$request_method = strtolower($Request->method);
		$found          = $this->controller_router_handler_internal_execute($controller_class, $method_name.'_'.$request_method, $Request, $Response) || $found;
		if ($found || !$required) {
			return;
		}
		$this->handler_not_found(
			$this->controller_router_available_methods($this->working_directory, $controller_class, $method_name),
			$request_method,
			$Request
		);
	}
	/**
	 * @param string $working_directory
	 * @param string $controller_class
	 * @param string $method_name
	 *
	 * @return string[]
	 */
	protected function controller_router_available_methods ($working_directory, $controller_class, $method_name) {
		$structure = file_exists("$working_directory/index.json") ? file_get_json("$working_directory/index.json") : ['index'];
		$structure = $this->controller_router_available_methods_to_flat_structure($structure);
		$methods   = array_filter(
			get_class_methods($controller_class) ?: [],
			function ($found_method) use ($method_name, $structure) {
				if (!preg_match("/^{$method_name}_[a-z_]+$/", $found_method)) {
					return false;
				}
				foreach ($structure as $structure_method) {
					if (strpos($found_method, $structure_method) === 0 && strpos($method_name, $structure_method) !== 0) {
						return false;
					}
				}
				return true;
			}
		);
		if (method_exists($controller_class, $method_name)) {
			$methods[] = $method_name;
		}
		$methods = _strtoupper(_substr($methods, strlen($method_name) + 1));
		natcasesort($methods);
		return array_values($methods);
	}
	/**
	 * @param array  $structure
	 * @param string $prefix
	 *
	 * @return string[]
	 */
	protected function controller_router_available_methods_to_flat_structure ($structure, $prefix = '') {
		/**
		 * Hack: first key in order to avoid warning when `$flat_structure` is empty at `return`
		 */
		$flat_structure = [[]];
		foreach ($structure as $path => $nested_structure) {
			if (!is_array($nested_structure)) {
				$path             = $nested_structure;
				$nested_structure = [];
			}
			$flat_structure[] = [$prefix.$path];
			$flat_structure[] = $this->controller_router_available_methods_to_flat_structure($nested_structure, $prefix.$path.'_');
		}
		return array_merge(...$flat_structure);
	}
	/**
	 * @param string      $controller_class
	 * @param string      $method_name
	 * @param \cs\Request $Request
	 * @param Response    $Response
	 *
	 * @return bool
	 */
	protected function controller_router_handler_internal_execute ($controller_class, $method_name, $Request, $Response) {
		if (!method_exists($controller_class, $method_name)) {
			return false;
		}
		$result = $controller_class::$method_name($Request, $Response);
		if ($result !== null) {
			Page::instance()->{$Request->api_path ? 'json' : 'content'}($result);
		}
		return true;
	}
}
