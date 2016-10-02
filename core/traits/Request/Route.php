<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;
use
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language,
	cs\Request\Route\Static_files,
	cs\Response;

/**
 * @property bool   $cli
 * @property string $scheme
 * @property string $host
 * @property string $path
 *
 * @method string header(string $name)
 */
trait Route {
	use
		Static_files;
	/**
	 * Current mirror according to configuration
	 *
	 * @var int
	 */
	public $mirror_index;
	/**
	 * Normalized processed representation of relative address, may differ from raw, should be used in most cases
	 *
	 * @var string
	 */
	public $path_normalized;
	/**
	 * Contains parsed route of current page url in form of array without module name and prefixes `admin|api`
	 *
	 * @var array
	 */
	public $route;
	/**
	 * Like `$route` property, but excludes numerical items
	 *
	 * @var string[]
	 */
	public $route_path;
	/**
	 * Like `$route` property, but only contains numerical items (opposite to route_path property)
	 *
	 * @var int[]
	 */
	public $route_ids;
	/**
	 * Request to administration section
	 *
	 * @var bool
	 */
	public $admin_path;
	/**
	 * Request to CLI interface
	 *
	 * @var bool
	 */
	public $cli_path;
	/**
	 * Request to api section
	 *
	 * @var bool
	 */
	public $api_path;
	/**
	 * Request to regular page (not administration section, not API and not CLI)
	 *
	 * @var bool
	 */
	public $regular_path;
	/**
	 * Current module
	 *
	 * @var string
	 */
	public $current_module;
	/**
	 * Home page
	 *
	 * @var bool
	 */
	public $home_page;
	/**
	 * Initialize route based on system configuration, requires `::init_server()` being called first since uses its data
	 *
	 * @throws ExitException
	 */
	public function init_route () {
		/**
		 * Serve static files here for early exit
		 */
		$this->serve_static_files();
		$this->mirror_index    = -1;
		$this->path_normalized = '';
		$this->route           = [];
		$this->route_path      = [];
		$this->route_ids       = [];
		$this->cli_path        = false;
		$this->admin_path      = false;
		$this->api_path        = false;
		$this->regular_path    = true;
		$this->current_module  = '';
		$this->home_page       = false;
		if ($this->cli) {
			$results = $this->analyze_route_path($this->path);
		} else {
			$Config             = Config::instance();
			$this->mirror_index = $this->determine_current_mirror_index($Config);
			/**
			 * If match was not found - mirror is not allowed!
			 */
			if ($this->mirror_index === -1) {
				throw new ExitException("Mirror $this->host not allowed", 400);
			}
			$results = $this->analyze_route_path($this->path);
			$this->handle_redirect($Config, $results['path_normalized']);
		}
		$this->route           = $results['route'];
		$this->route_path      = $results['route_path'];
		$this->route_ids       = $results['route_ids'];
		$this->path_normalized = $results['path_normalized'];
		$this->cli_path        = $results['cli_path'];
		$this->admin_path      = $results['admin_path'];
		$this->api_path        = $results['api_path'];
		$this->regular_path    = $results['regular_path'];
		$this->current_module  = $results['current_module'];
		$this->home_page       = $results['home_page'];
	}
	/**
	 * @param Config $Config
	 *
	 * @return int
	 */
	protected function determine_current_mirror_index ($Config) {
		/**
		 * Search for url matching in all mirrors
		 */
		foreach ($Config->core['url'] as $i => $address) {
			list($scheme, $urls) = explode('://', $address, 2);
			if ($scheme == $this->scheme) {
				foreach (explode(';', $urls) as $url) {
					if (mb_strpos("$this->host/$this->path", "$url/") === 0) {
						return $i;
					}
				}
			}
		}
		return -1;
	}
	/**
	 * Process raw relative route.
	 *
	 * As result returns current route in system in form of array, normalized path, detects module path points to, whether this is API call, administration
	 * page, or home page whether this is API call, admin page, or home page
	 *
	 * @param string $path
	 *
	 * @return array Array contains next elements: `route`, `path_normalized`, `cli_path`, `admin_path`, `api_path`, `current_module`, `home_page`
	 */
	public function analyze_route_path ($path) {
		$route = trim($path, '/');
		Event::instance()->fire(
			'System/Request/routing_replace/before',
			[
				'rc' => &$route
			]
		);
		$url_language = Language::instance()->url_language($route);
		/**
		 * Obtaining page path in form of array
		 */
		$route = explode('/', $route);
		if ($url_language) {
			array_shift($route);
		}
		if (@$route[0] === '') {
			array_shift($route);
		}
		$cli_path     = $this->cli && @strtolower($route[0]) == 'cli';
		$admin_path   = @strtolower($route[0]) == 'admin';
		$api_path     = @strtolower($route[0]) == 'api';
		$regular_path = !($cli_path || $admin_path || $api_path);
		$path_prefix  = '';
		$home_page    = false;
		/**
		 * If url is cli, admin or API page - set corresponding variables to corresponding path prefix
		 */
		if (!$regular_path) {
			$path_prefix = array_shift($route).'/';
		}
		/**
		 * Module detection
		 */
		$current_module = $this->determine_page_module($route, $home_page, $admin_path, $regular_path);
		list($route_path, $route_ids) = $this->split_route($route);
		$old_route      = $route;
		$old_route_path = $route_path;
		$old_route_ids  = $route_ids;
		Event::instance()->fire(
			'System/Request/routing_replace/after',
			[
				'route'          => &$route,
				'route_path'     => &$route_path,
				'route_ids'      => &$route_ids,
				'cli_path'       => &$cli_path,
				'admin_path'     => &$admin_path,
				'api_path'       => &$api_path,
				'regular_path'   => &$regular_path,
				'current_module' => &$current_module,
				'home_page'      => &$home_page
			]
		);
		if ($route != $old_route && $route_path == $old_route_path && $route_ids == $old_route_ids) {
			list($route_path, $route_ids) = $this->split_route($route);
		}
		return [
			'route'           => $route,
			'route_path'      => $route_path,
			'route_ids'       => $route_ids,
			'path_normalized' => trim(
				"$path_prefix$current_module/".implode('/', $route),
				'/'
			),
			'cli_path'        => $cli_path,
			'admin_path'      => $admin_path,
			'api_path'        => $api_path,
			'regular_path'    => $regular_path,
			'current_module'  => $current_module,
			'home_page'       => $home_page
		];
	}
	/**
	 * @param array $route
	 *
	 * @return array[] Key `0` contains array of paths, key `1` contains array of identifiers
	 */
	protected function split_route ($route) {
		$route_path = [];
		$route_ids  = [];
		/**
		 * Separate numeric and other parts of route
		 */
		foreach ($route as $item) {
			if (is_numeric($item)) {
				$route_ids[] = $item;
			} else {
				$route_path[] = $item;
			}
		}
		return [$route_path, $route_ids];
	}
	/**
	 * @param Config $Config
	 * @param string $path_normalized
	 *
	 * @throws ExitException
	 */
	protected function handle_redirect ($Config, $path_normalized) {
		/**
		 * Redirection processing
		 */
		if (strpos($path_normalized, 'System/redirect/') === 0) {
			if ($this->is_referer_local($Config)) {
				Response::instance()->redirect(
					substr($path_normalized, 16),
					301
				);
				throw new ExitException;
			} else {
				throw new ExitException(400);
			}
		}
	}
	/**
	 * Check whether referer is local
	 *
	 * @param Config $Config
	 *
	 * @return bool
	 */
	protected function is_referer_local ($Config) {
		$referer = $this->header('referer');
		if (!$referer) {
			return false;
		}
		list($referer_protocol, $referer_host) = explode('://', $referer);
		$referer_host = explode('/', $referer_host)[0];
		foreach ($Config->core['url'] as $address) {
			list($protocol, $urls) = explode('://', $address, 2);
			if ($protocol === $referer_protocol) {
				foreach (explode(';', $urls) as $url) {
					if (mb_strpos("$referer_host/", "$url/") === 0) {
						return true;
					}
				}
			}
		}
		return false;
	}
	/**
	 * Determine module of current page based on page path and system configuration
	 *
	 * @param array $rc
	 * @param bool  $home_page
	 * @param bool  $admin_path
	 * @param bool  $regular_path
	 *
	 * @return string
	 */
	protected function determine_page_module (&$rc, &$home_page, $admin_path, $regular_path) {
		$Config           = Config::instance();
		$modules          = $this->get_modules($Config, (bool)$admin_path);
		$module_specified = @$rc[0];
		if ($module_specified) {
			if (in_array($module_specified, $modules)) {
				return array_shift($rc);
			}
			$L = Language::instance();
			foreach ($modules as $module) {
				if ($module_specified == path($L->$module)) {
					array_shift($rc);
					return $module;
				}
			}
		}
		if (!$regular_path || $module_specified) {
			$current_module = 'System';
		} else {
			$current_module = $Config->core['default_module'];
			$home_page      = true;
		}
		return $current_module;
	}
	/**
	 * Get array of modules
	 *
	 * @param Config $Config
	 * @param bool   $admin_path
	 *
	 * @return string[]
	 */
	protected function get_modules ($Config, $admin_path) {
		$modules = array_filter(
			$Config->components['modules'],
			function ($module_data) use ($admin_path) {
				/**
				 * Skip uninstalled modules and modules that are disabled (on all pages except admin pages)
				 */
				return
					(
						$admin_path &&
						$module_data['active'] == Config\Module_Properties::DISABLED
					) ||
					$module_data['active'] == Config\Module_Properties::ENABLED;
			}
		);
		return array_keys($modules);
	}
	/**
	 * Get route part by index
	 *
	 * @param int $index
	 *
	 * @return int|null|string
	 */
	public function route ($index) {
		return @$this->route[$index];
	}
	/**
	 * Get route path part by index
	 *
	 * @param int $index
	 *
	 * @return null|string
	 */
	public function route_path ($index) {
		return @$this->route_path[$index];
	}
	/**
	 * Get route ids part by index
	 *
	 * @param int $index
	 *
	 * @return int|null
	 */
	public function route_ids ($index) {
		return @$this->route_ids[$index];
	}
}
