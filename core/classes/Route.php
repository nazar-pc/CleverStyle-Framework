<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
/**
 * Provides next events:
 *  System/Route/pre_routing_replace
 *  ['rc'    => &$rc] //Reference to string with current route, this string can be changed
 *
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
	 */
	protected function construct () {
		$Config = Config::instance();
		$L      = Language::instance();
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
			code_header(400);
			trigger_error($L->mirror_not_allowed, E_USER_ERROR);
			throw new \ExitException;
		}
		/**
		 * Remove trailing slashes
		 */
		$this->raw_relative_address = trim($this->raw_relative_address, ' /\\');
		/**
		 * Redirection processing
		 */
		if (mb_strpos($this->raw_relative_address, 'redirect/') === 0) {
			if ($this->is_referer_local($Config)) {
				_header('Location: '.substr($this->raw_relative_address, 9));
			} else {
				error_code(400);
				Page::instance()->error();
			}
			throw new \ExitException;
		}
		$processed_route = $this->process_route($this->raw_relative_address);
		if (!$processed_route) {
			error_code(403);
			Page::instance()->error();
			return;
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
		unset($item);
		$this->relative_address = $processed_route['relative_address'];
		admin_path($processed_route['ADMIN']);
		api_path($processed_route['API']);
		current_module($processed_route['MODULE']);
		home_page($processed_route['HOME']);
		// TODO Remove in future versions
		$this->keep_backward_compatibility($Config);
	}
	/**
	 * Keep backward compatibility with `cs\Config`, its properties `route` and `server` are filled from here
	 *
	 * @todo Remove in future versions
	 *
	 * @param Config $Config
	 */
	protected function keep_backward_compatibility ($Config) {
		/**
		 * @var _SERVER $_SERVER
		 */
		$Config->server['raw_relative_address'] = &$this->raw_relative_address;
		$Config->server['host']                 = $_SERVER->host;
		$Config->server['relative_address']     = &$this->relative_address;
		$Config->server['protocol']             = $_SERVER->protocol;
		$Config->server['mirror_index']         = &$this->mirror_index;
		$Config->route                          = &$this->route;
		$Index                                  = Index::instance();
		$Index->route_ids                       = &$this->ids;
		$Index->route_path                      = &$this->path;
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
	 * @return bool|string[]                            Relative address or <i>false</i> if access denied (occurs when admin access is limited by IP)
	 *                                                    Array contains next elements: route, relative_address, ADMIN, API, MODULE, HOME
	 */
	function process_route ($raw_relative_address) {
		$Config = Config::instance();
		$rc     = explode('?', $raw_relative_address, 2)[0];
		$rc     = trim($rc, '/');
		if (Language::instance()->url_language($rc)) {
			$rc = explode('/', $rc, 2);
			$rc = isset($rc[1]) ? $rc[1] : '';
		}
		/**
		 * Routing replacing
		 */
		Event::instance()->fire(
			'System/Route/pre_routing_replace',
			[
				'rc' => &$rc
			]
		);
		// TODO Remove in future versions
		Event::instance()->fire(
			'System/Config/pre_routing_replace',
			[
				'rc' => &$rc
			]
		);
		if (!empty($Config->routing['in'])) {
			foreach ($Config->routing['in'] as $i => $search) {
				$rc = _preg_replace($search, $Config->routing['out'][$i], $rc) ?: str_replace($search, $Config->routing['out'][$i], $rc);
			}
			unset($i, $search);
		}
		Event::instance()->fire(
			'System/Route/routing_replace',
			[
				'rc' => &$rc
			]
		);
		// TODO Remove in future versions
		Event::instance()->fire(
			'System/Config/routing_replace',
			[
				'rc' => &$rc
			]
		);
		/**
		 * Obtaining page path in form of array
		 */
		$rc = $rc ? explode('/', $rc) : [];
		/**
		 * If url looks like admin page
		 */
		if (@mb_strtolower($rc[0]) == 'admin') {
			if (!$Config->can_be_admin()) {
				return false;
			}
			$ADMIN = true;
			array_shift($rc);
			/**
			 * If url looks like API page
			 */
		} elseif (@mb_strtolower($rc[0]) == 'api') {
			$API = true;
			array_shift($rc);
		}
		if (!isset($ADMIN)) {
			$ADMIN = false;
		}
		if (!isset($API)) {
			$API = false;
		}
		/**
		 * Module detection
		 */
		$modules = array_keys(
			array_filter(
				$Config->components['modules'],
				function ($module_data) use ($ADMIN) {
					return $ADMIN || $module_data['active'] == 1;
				}
			)
		);
		$L       = Language::instance();
		$modules = array_combine(
			array_map(
				function ($module) use ($L) {
					return path($L->get($module));
				},
				$modules
			),
			$modules
		);
		if (@in_array($rc[0], array_values($modules))) {
			$MODULE = array_shift($rc);
		} elseif (@isset($modules[$rc[0]])) {
			$MODULE = $modules[array_shift($rc)];
		} else {
			$MODULE = $ADMIN || $API || isset($rc[0]) ? 'System' : $Config->core['default_module'];
			if (!$ADMIN && !$API && !isset($rc[1])) {
				$HOME = true;
			}
		}
		if (!isset($HOME)) {
			$HOME = false;
		}
		return [
			'route'            => $rc,
			'relative_address' => trim(
				($ADMIN ? 'admin/' : '').($API ? 'api/' : '').$MODULE.'/'.implode('/', $rc),
				'/'
			),
			'ADMIN'            => $ADMIN,
			'API'              => $API,
			'MODULE'           => $MODULE,
			'HOME'             => $HOME
		];
	}
}
