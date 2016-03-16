<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
/**
 * @deprecated Use `cs\Request` instead
 * @todo       Remove in 4.x
 */
class Route {
	use
		Singleton;
	/**
	 * Current mirror according to configuration
	 *
	 * @deprecated Use `cs\Request::$mirror_index` instead
	 *
	 * @var int
	 */
	public $mirror_index = -1;
	/**
	 * Relative address as it came from URL
	 *
	 * @deprecated Use `cs\Request::$uri` instead
	 *
	 * @var string
	 */
	public $raw_relative_address = '';
	/**
	 * Normalized processed representation of relative address, may differ from raw, should be used in most cases
	 *
	 * @deprecated Use `cs\Request::$path_normalized` instead
	 *
	 * @var string
	 */
	public $relative_address = '';
	/**
	 * Contains parsed route of current page url in form of array without module name and prefixes <i>admin</i>/<i>api</i>
	 *
	 * @deprecated Use `cs\Request::$route` instead
	 *
	 * @var array
	 */
	public $route = [];
	/**
	 * Like $route property, but excludes numerical items
	 *
	 * @deprecated Use `cs\Request::$route_path` instead
	 *
	 * @var string[]
	 */
	public $path = [];
	/**
	 * Like $route property, but only includes numerical items (opposite to route_path property)
	 *
	 * @deprecated Use `cs\Request::$route_ids` instead
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
		$Request                    = Request::instance();
		$this->mirror_index         = &$Request->mirror_index;
		$this->raw_relative_address = &$Request->uri;
		$this->relative_address     = &$Request->path_normalized;
		$this->route                = &$Request->route;
		$this->path                 = &$Request->route_path;
		$this->ids                  = &$Request->route_ids;
	}
	/**
	 * Process raw relative route.
	 *
	 * @deprecated Use `cs\Request::analyze_route_path()` instead
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
		$path   = explode('?', $raw_relative_address, 2)[0];
		$result = Request::instance()->analyze_route_path($path);
		return [
			'route'            => $result['route'],
			'relative_address' => $result['path_normalized'],
			'ADMIN'            => $result['admin_path'],
			'API'              => $result['api_path'],
			'MODULE'           => $result['current_module'],
			'HOME'             => $result['home_page']
		];
	}
}
