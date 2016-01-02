<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
/**
 * Provides next events:
 *  System/Route/routing_replace
 *  ['rc'    => &$rc] //Reference to string with current route, this string can be changed
 *
 * @method static Route instance($check = false)
 */
class Route {
	use
		Singleton;
	/**
	 * Current mirror according to configuration
	 *
	 * @var int
	 */
	public $mirror_index = -1;
	/**
	 * Relative address as it came from URL
	 *
	 * @var string
	 */
	public $raw_relative_address = '';
	/**
	 * Normalized processed representation of relative address, may differ from raw, should be used in most cases
	 *
	 * @var string
	 */
	public $relative_address = '';
	/**
	 * Contains parsed route of current page url in form of array without module name and prefixes <i>admin</i>/<i>api</i>
	 *
	 * @var array
	 */
	public $route = [];
	/**
	 * Like $route property, but excludes numerical items
	 *
	 * @var string[]
	 */
	public $path = [];
	/**
	 * Like $route property, but only includes numerical items (opposite to route_path property)
	 *
	 * @var int[]
	 */
	public $ids = [];
	/**
	 * Loading of configuration, initialization of $Config, $Cache, $L and Page objects, Routing processing
	 *
	 * @throws ExitException
	 */
	protected function construct () {
		$Config = Config::instance();
		/**
		 * @var _SERVER $_SERVER
		 */
		$this->raw_relative_address = urldecode(trim($_SERVER->request_uri, '/'));
		$this->raw_relative_address = null_byte_filter($this->raw_relative_address);
		/**
		 * Search for url matching in all mirrors
		 */
		foreach ($Config->core['url'] as $i => $address) {
			list($protocol, $urls) = explode('://', $address, 2);
			if (
				$this->mirror_index === -1 &&
				$protocol == $_SERVER->protocol
			) {
				foreach (explode(';', $urls) as $url) {
					if (mb_strpos("$_SERVER->host$this->raw_relative_address", $url) === 0) {
						$this->mirror_index = $i;
						break 2;
					}
				}
			}
		}
		unset($address, $i, $urls, $url, $protocol);
		/**
		 * If match was not found - mirror is not allowed!
		 */
		if ($this->mirror_index === -1) {
			trigger_error("Mirror $_SERVER->host not allowed", E_USER_ERROR);
			throw new ExitException(400);
		}
		/**
		 * Remove trailing slashes
		 */
		$this->raw_relative_address = trim($this->raw_relative_address, ' /\\');
		$processed_route            = $this->process_route($this->raw_relative_address);
		/**
		 * Redirection processing
		 */
		if (mb_strpos($processed_route['relative_address'], 'System/redirect/') === 0) {
			if ($this->is_referer_local($Config)) {
				_header('Location: '.substr($processed_route['relative_address'], 16), true, 301);
				throw new ExitException(301);
			} else {
				throw new ExitException(400);
			}
		}
		if (!$processed_route) {
			throw new ExitException(403);
		}
		$this->route = $processed_route['route'];
		/**
		 * Separate numeric and other parts of route
		 */
		foreach ($this->route as $item) {
			if (is_numeric($item)) {
				$this->ids[] = $item;
			} else {
				$this->path[] = $item;
			}
		}
		$this->relative_address = $processed_route['relative_address'];
		admin_path($processed_route['ADMIN']);
		api_path($processed_route['API']);
		current_module($processed_route['MODULE']);
		home_page($processed_route['HOME']);
	}
	/**
	 * Check whether referer is local
	 *
	 * @param Config $Config
	 *
	 * @return bool
	 */
	protected function is_referer_local ($Config) {
		/**
		 * @var _SERVER $_SERVER
		 */
		if (!$_SERVER->referer) {
			return false;
		}
		list($referer_protocol, $referer_host) = explode('://', $_SERVER->referer);
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
	 * Process raw relative route.
	 *
	 * As result returns current route in system in form of array, corrected page address, detects MODULE, that responsible for processing this url,
	 * whether this is API call, ADMIN page, or HOME page
	 *
	 * @param string $raw_relative_address
	 *
	 * @return false|string[] Relative address or <i>false</i> if access denied (occurs when admin access is limited by IP). Array contains next elements:
	 *                        route, relative_address, ADMIN, API, MODULE, HOME
	 */
	function process_route ($raw_relative_address) {
		$rc = explode('?', $raw_relative_address, 2)[0];
		$rc = trim($rc, '/');
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
		/**
		 * Obtaining page path in form of array
		 */
		$rc    = $rc ? explode('/', $rc) : [];
		$ADMIN = '';
		$API   = '';
		$HOME  = false;
		/**
		 * If url is admin or API page - set corresponding variables to corresponding path prefix
		 */
		if (@mb_strtolower($rc[0]) == 'admin') {
			$ADMIN = 'admin/';
			array_shift($rc);
		} elseif (@mb_strtolower($rc[0]) == 'api') {
			$API = 'api/';
			array_shift($rc);
		}
		/**
		 * Module detection
		 */
		$MODULE = $this->determine_page_module($rc, $HOME, $ADMIN, $API);
		return [
			'route'            => $rc,
			'relative_address' => trim(
				$ADMIN.$API.$MODULE.'/'.implode('/', $rc),
				'/'
			),
			'ADMIN'            => (bool)$ADMIN,
			'API'              => (bool)$API,
			'MODULE'           => $MODULE,
			'HOME'             => $HOME
		];
	}
	/**
	 * Determine module of current page based on page path and system configuration
	 *
	 * @param array  $rc
	 * @param bool   $HOME
	 * @param string $ADMIN
	 * @param string $API
	 *
	 * @return mixed|string
	 */
	protected function determine_page_module (&$rc, &$HOME, $ADMIN, $API) {
		$Config  = Config::instance();
		$modules = $this->get_modules($Config, $ADMIN);
		if (@in_array($rc[0], array_values($modules))) {
			return array_shift($rc);
		}
		if (@$modules[$rc[0]]) {
			return $modules[array_shift($rc)];
		}
		$MODULE =
			$ADMIN || $API || isset($rc[0])
				? 'System'
				: $Config->core['default_module'];
		if (!$ADMIN && !$API && !isset($rc[1])) {
			$HOME = true;
		}
		return $MODULE;
	}
	/**
	 * Get array of modules
	 *
	 * @param Config $Config
	 * @param bool   $ADMIN
	 *
	 * @return array Array of form [localized_module_name => module_name]
	 */
	protected function get_modules ($Config, $ADMIN) {
		$modules = array_filter(
			$Config->components['modules'],
			function ($module_data) use ($ADMIN) {
				/**
				 * Skip uninstalled modules and modules that are disabled (on all pages except admin pages)
				 */
				return
					(
						$ADMIN &&
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
