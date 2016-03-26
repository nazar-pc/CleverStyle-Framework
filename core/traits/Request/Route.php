<?php
/**
 * @package   CleverStyle CMS
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
	cs\Response;

/**
 * Provides next events:
 *  System/Request/routing_replace
 *  ['rc'    => &$rc] //Reference to string with current route, this string can be changed
 *
 * @property string $scheme
 * @property string $host
 * @property string $path
 */
trait Route {
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
	 * Like `$route` property, but only includes numerical items (opposite to route_path property)
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
	 * Request to api section
	 *
	 * @var bool
	 */
	public $api_path;
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
	function init_route () {
		$this->mirror_index    = -1;
		$this->path_normalized = '';
		$this->route           = [];
		$this->route_path      = [];
		$this->route_ids       = [];
		$this->admin_path      = false;
		$this->api_path        = false;
		$this->current_module  = '';
		$this->home_page       = false;
		$Config                = Config::instance();
		/**
		 * Search for url matching in all mirrors
		 */
		foreach ($Config->core['url'] as $i => $address) {
			list($scheme, $urls) = explode('://', $address, 2);
			if (
				$this->mirror_index === -1 &&
				$scheme == $this->scheme
			) {
				foreach (explode(';', $urls) as $url) {
					if (mb_strpos("$this->host/$this->path", "$url/") === 0) {
						$this->mirror_index = $i;
						break 2;
					}
				}
			}
		}
		unset($address, $i, $urls, $url, $scheme);
		/**
		 * If match was not found - mirror is not allowed!
		 */
		if ($this->mirror_index === -1) {
			throw new ExitException("Mirror $this->host not allowed", 400);
		}
		$results = $this->analyze_route_path($this->path);
		$this->handle_redirect($Config, $results['path_normalized']);
		$this->route = $results['route'];
		/**
		 * Separate numeric and other parts of route
		 */
		foreach ($this->route as $item) {
			if (is_numeric($item)) {
				$this->route_ids[] = $item;
			} else {
				$this->route_path[] = $item;
			}
		}
		$this->path_normalized = $results['path_normalized'];
		$this->admin_path      = $results['admin_path'];
		$this->api_path        = $results['api_path'];
		$this->current_module  = $results['current_module'];
		$this->home_page       = $results['home_page'];
	}
	/**
	 * Process raw relative route.
	 *
	 * As result returns current route in system in form of array, normalized path, detects module path points to, whether this is API call, administration
	 * page, or home page whether this is API call, admin page, or home page
	 *
	 * @param string $path
	 *
	 * @return array Array contains next elements: `route`, `path_normalized`, `admin_path`, `api_path`, `current_module`, `home_page`
	 */
	function analyze_route_path ($path) {
		$rc = trim($path, '/');
		if (Language::instance()->url_language($rc)) {
			$rc = explode('/', $rc, 2);
			$rc = isset($rc[1]) ? $rc[1] : '';
		}
		Event::instance()->fire(
			'System/Route/routing_replace',
			[
				'rc' => &$rc
			]
		);
		Event::instance()->fire(
			'System/Request/routing_replace',
			[
				'rc' => &$rc
			]
		);
		/**
		 * Obtaining page path in form of array
		 */
		$rc         = $rc ? explode('/', $rc) : [];
		$admin_path = '';
		$api_path   = '';
		$home_page  = false;
		/**
		 * If url is admin or API page - set corresponding variables to corresponding path prefix
		 */
		if (@mb_strtolower($rc[0]) == 'admin') {
			$admin_path = 'admin/';
			array_shift($rc);
		} elseif (@mb_strtolower($rc[0]) == 'api') {
			$api_path = 'api/';
			array_shift($rc);
		}
		/**
		 * Module detection
		 */
		$current_module = $this->determine_page_module($rc, $home_page, $admin_path, $api_path);
		return [
			'route'           => $rc,
			'path_normalized' => trim(
				$admin_path.$api_path.$current_module.'/'.implode('/', $rc),
				'/'
			),
			'admin_path'      => (bool)$admin_path,
			'api_path'        => (bool)$api_path,
			'current_module'  => $current_module,
			'home_page'       => $home_page
		];
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
					if (mb_strpos($referer_host, $url) === 0) {
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
	 * @param array  $rc
	 * @param bool   $home_page
	 * @param string $admin_path
	 * @param string $api_path
	 *
	 * @return mixed|string
	 */
	protected function determine_page_module (&$rc, &$home_page, $admin_path, $api_path) {
		$Config  = Config::instance();
		$modules = $this->get_modules($Config, (bool)$admin_path);
		if (@in_array($rc[0], array_values($modules))) {
			return array_shift($rc);
		}
		if (@$modules[$rc[0]]) {
			return $modules[array_shift($rc)];
		}
		$current_module =
			$admin_path || $api_path || isset($rc[0])
				? 'System'
				: $Config->core['default_module'];
		if (!$admin_path && !$api_path && !isset($rc[1])) {
			$home_page = true;
		}
		return $current_module;
	}
	/**
	 * Get array of modules
	 *
	 * @param Config $Config
	 * @param bool   $admin_path
	 *
	 * @return array Array of form [localized_module_name => module_name]
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
		$L       = Language::instance();
		foreach ($modules as $module => &$localized_name) {
			$localized_name = path($L->$module);
		}
		return array_flip($modules);
	}
}
