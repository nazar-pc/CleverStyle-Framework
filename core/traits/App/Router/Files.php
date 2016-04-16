<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\App\Router;

/**
 * @property string[] $controller_path Path that will be used by controller to render page
 */
trait Files {
	/**
	 * Include files necessary for module page rendering
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
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
	 * @param \cs\Request $Request
	 * @param string      $dir
	 * @param string      $basename
	 * @param bool        $required
	 *
	 * @throws \cs\ExitException
	 */
	protected function files_router_handler ($Request, $dir, $basename, $required = true) {
		$this->files_router_handler_internal($Request, $dir, $basename, $required);
	}
	/**
	 * @param \cs\Request $Request
	 * @param string      $dir
	 * @param string      $basename
	 * @param bool        $required
	 *
	 * @throws \cs\ExitException
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
			$request_method,
			$Request
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
}
