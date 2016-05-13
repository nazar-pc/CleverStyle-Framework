<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\App;
use
	cs\Event,
	cs\ExitException,
	cs\Page,
	cs\Response,
	cs\App\Router\CLI,
	cs\App\Router\Controller,
	cs\App\Router\Files;

trait Router {
	use
		CLI,
		Controller,
		Files;
	/**
	 * Path that will be used by controller to render page
	 *
	 * @var string[]
	 */
	protected $controller_path;
	/**
	 * @var string
	 */
	protected $working_directory;
	protected function init_router () {
		$this->controller_path   = ['index'];
		$this->working_directory = '';
	}
	/**
	 * Execute router
	 *
	 * Depending on module, files-based or controller-based router might be used
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	protected function execute_router ($Request) {
		$this->working_directory = $this->get_working_directory($Request);
		$this->check_and_normalize_route($Request);
		if (!Event::instance()->fire('System/App/execute_router/before')) {
			return;
		}
		/**
		 * If module consists of index.html only
		 */
		if (file_exists("$this->working_directory/index.html")) {
			ob_start();
			_include("$this->working_directory/index.html", false, false);
			Page::instance()->content(ob_get_clean());
			return;
		}
		if (file_exists("$this->working_directory/Controller.php")) {
			$this->controller_router($Request);
		} else {
			$this->files_router($Request);
		}
		Event::instance()->fire('System/App/execute_router/after');
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @return string
	 *
	 * @throws ExitException
	 */
	protected function get_working_directory ($Request) {
		$working_directory = MODULES."/$Request->current_module";
		if ($Request->cli_path) {
			$working_directory .= '/cli';
		} elseif ($Request->admin_path) {
			$working_directory .= '/admin';
		} elseif ($Request->api_path) {
			$working_directory .= '/api';
		}
		if (!is_dir($working_directory) && (!$Request->cli_path || $Request->method != 'CLI')) {
			throw new ExitException(404);
		}
		return $working_directory;
	}
	/**
	 * Normalize `cs\Request::$route_path` and fill `cs\App::$controller_path`
	 *
	 * @param \cs\Request $Request
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
			$this->check_and_normalize_route_internal($Request, $path, $structure, $Request->cli_path || $Request->api_path);
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
	 * @param \cs\Request $Request
	 * @param string      $path
	 * @param array       $structure
	 * @param bool        $cli_or_api_path
	 *
	 * @throws ExitException
	 */
	protected function check_and_normalize_route_internal ($Request, &$path, $structure, $cli_or_api_path) {
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
		if (!$this->check_permission($Request, $path)) {
			throw new ExitException(403);
		}
	}
	/**
	 * If HTTP method handler not found we generate either `501 Not Implemented` if other methods are supported or `404 Not Found` if handlers for others
	 * methods also doesn't exist
	 *
	 * @param string[]    $available_methods
	 * @param string      $request_method
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	protected function handler_not_found ($available_methods, $request_method, $Request) {
		if ($available_methods) {
			if ($Request->cli_path) {
				$this->print_cli_structure($Request->path);
				if ($request_method !== 'cli') {
					throw new ExitException(501);
				}
			} else {
				Response::instance()->header('Allow', implode(', ', $available_methods));
				if ($request_method !== 'options') {
					throw new ExitException(501);
				}
			}
		} else {
			throw new ExitException(404);
		}
	}
}
