<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\App;
use
	cs\ExitException,
	cs\Page,
	cs\Request,
	cs\Response;

/**
 * @property string[] $controller_path Path that will be used by controller to render page
 */
trait Router {
	/**
	 * Path that will be used by controller to render page
	 *
	 * @var string[]
	 */
	protected $controller_path;
	/**
	 * Execute router
	 *
	 * Depending on module, files-based or controller-based router might be used
	 *
	 * @throws ExitException
	 */
	protected function execute_router () {
		$Request = Request::instance();
		$this->check_and_normalize_route($Request);
		if (file_exists("$this->working_directory/Controller.php")) {
			$this->controller_router($Request);
		} else {
			$this->files_router($Request);
		}
	}
	/**
	 * Normalize `cs\Request::$route_path` and fill `cs\App::$controller_path`
	 *
	 * @param Request $Request
	 *
	 * @throws ExitException
	 */
	protected function check_and_normalize_route ($Request) {
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
			$path = @$Request->route_path[$nesting_level];
			/**
			 * If path not specified - take first from structure
			 */
			$this->check_and_normalize_route_internal($path, $structure, $Request->cli_path || $Request->api_path);
			$Request->route_path[$nesting_level] = $path;
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
	 * @param bool   $cli_or_api_path
	 *
	 * @throws ExitException
	 */
	protected function check_and_normalize_route_internal (&$path, $structure, $cli_or_api_path) {
		/**
		 * If path not specified - take first from structure
		 */
		if (!$path) {
			$path = isset($structure[0]) ? $structure[0] : array_keys($structure)[0];
			/**
			 * We need exact paths for CLI and API request (or `_` ending if available) and less strict mode for other cases that allows go deeper automatically
			 */
			if ($path !== '_' && $cli_or_api_path) {
				throw new ExitException(404);
			}
		} elseif (!isset($structure[$path]) && !in_array($path, $structure)) {
			throw new ExitException(404);
		}
		/** @noinspection PhpUndefinedMethodInspection */
		if (!$this->check_permission($path)) {
			throw new ExitException(403);
		}
	}
	/**
	 * Include files necessary for module page rendering
	 *
	 * @param Request $Request
	 *
	 * @throws ExitException
	 */
	protected function files_router ($Request) {
		foreach ($this->controller_path as $index => $path) {
			/**
			 * Starting from index 2 we need to maintain slash-separated string that includes all paths from index 1 and till current
			 */
			if ($index > 1) {
				$path = implode('/', array_slice($this->controller_path, 1, $index));
			}
			$next_exists = isset($this->controller_path[$index + 1]);
			$this->files_router_handler($Request, $this->working_directory, $path, !$next_exists);
		}
	}
	/**
	 * Include files that corresponds for specific paths in URL
	 *
	 * @param Request $Request
	 * @param string  $dir
	 * @param string  $basename
	 * @param bool    $required
	 *
	 * @throws ExitException
	 */
	protected function files_router_handler ($Request, $dir, $basename, $required = true) {
		$this->files_router_handler_internal($Request, $dir, $basename, $required);
	}
	/**
	 * @param Request $Request
	 * @param string  $dir
	 * @param string  $basename
	 * @param bool    $required
	 *
	 * @throws ExitException
	 */
	protected function files_router_handler_internal ($Request, $dir, $basename, $required) {
		$included = _include("$dir/$basename.php", false, false) !== false;
		if (!$Request->cli_path && !$Request->api_path) {
			return;
		}
		$request_method = strtolower($Request->method);
		$included       = _include("$dir/$basename.$request_method.php", false, false) !== false || $included;
		if ($included || !$required) {
			return;
		}
		$this->handler_not_found(
			$this->files_router_available_methods($dir, $basename),
			$request_method
		);
	}
	/**
	 * @param string $dir
	 * @param string $basename
	 *
	 * @return string[]
	 */
	protected function files_router_available_methods ($dir, $basename) {
		$methods = get_files_list($dir, "/^$basename\\.[a-z]+\\.php$/");
		$methods = _strtoupper(_substr($methods, strlen($basename) + 1, -4));
		natcasesort($methods);
		return array_values($methods);
	}
	/**
	 * If HTTP method handler not found we generate either `501 Not Implemented` if other methods are supported or `404 Not Found` if handlers for others
	 * methods also doesn't exist
	 *
	 * @param string[] $available_methods
	 * @param string   $request_method
	 *
	 * @throws ExitException
	 */
	protected function handler_not_found ($available_methods, $request_method) {
		if ($available_methods) {
			Response::instance()->header('Allow', implode(', ', $available_methods));
			if ($request_method !== 'options') {
				throw new ExitException(501);
			}
		} else {
			throw new ExitException(404);
		}
	}
	/**
	 * Call methods necessary for module page rendering
	 *
	 * @param Request $Request
	 *
	 * @throws ExitException
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
		$controller_class = "cs\\modules\\$Request->current_module$suffix\\Controller";
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
	 * @param Request $Request
	 * @param string  $controller_class
	 * @param string  $method_name
	 * @param bool    $required
	 *
	 * @throws ExitException
	 */
	protected function controller_router_handler ($Request, $controller_class, $method_name, $required = true) {
		$method_name = str_replace('.', '_', $method_name);
		$this->controller_router_handler_internal($Request, $controller_class, $method_name, $required);
	}
	/**
	 * @param Request $Request
	 * @param string  $controller_class
	 * @param string  $method_name
	 * @param bool    $required
	 *
	 * @throws ExitException
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
			$request_method
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
			get_class_methods($controller_class),
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
		$flat_structure = [];
		foreach ($structure as $path => $nested_structure) {
			if (is_array($nested_structure)) {
				$flat_structure[] = $prefix.$path;
				/** @noinspection SlowArrayOperationsInLoopInspection */
				$flat_structure = array_merge(
					$flat_structure,
					$this->controller_router_available_methods_to_flat_structure($nested_structure, $prefix.$path.'_')
				);
			} else {
				$flat_structure[] = $prefix.$nested_structure;
			}
		}
		return $flat_structure;
	}
	/**
	 * @param string   $controller_class
	 * @param string   $method_name
	 * @param Request  $Request
	 * @param Response $Response
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
