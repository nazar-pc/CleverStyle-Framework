<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;

/**
 * Provides next events:
 *  System/Config/pre_routing_replace
 *  ['rc'	=> <i>&$rc</i>]		//Reference to string with current route, this string can be changed
 *
 *  System/Config/routing_replace
 *  ['rc'	=> <i>&$rc</i>]		//Reference to string with current route, this string can be changed
 *
 *  System/Config/before_init
 *
 *  System/Config/after_init
 *
 * @method static Config instance($check = false)
 */
class Config {
	use	Singleton;
	/**
	 * Most general configuration properties
	 *
	 * @var mixed[]
	 */
	public $core		= [];
	/**
	 * Configuration of databases, except the main database, parameters of which are stored in configuration file
	 *
	 * @var mixed[]
	 */
	public $db			= [];
	/**
	 * Configuration of storages, except the main storage, parameters of which are stored in configuration file
	 *
	 * @var mixed[]
	 */
	public $storage		= [];
	/**
	 * Internal structure of components parameters
	 *
	 * @var mixed[]
	 */
	public $components	= [];
	/**
	 * Replacing rules, that are used to replace text on pages
	 *
	 * @var mixed[]
	 */
	public $replace		= [];
	/**
	 * Replacing rules, they are applied to current route, every rule is applied only once
	 *
	 * @var mixed[]
	 */
	public $routing		= [];
	/**
	 * Array of some address data about mirrors and current address properties
	 *
	 * @var mixed[]
	 */
	public $server		= [
		'raw_relative_address'	=> '',		//Raw page url (in browser's address bar)
		'host'					=> '',		//Current domain
		'relative_address'		=> '',		//Corrected page address (recommended for usage)
		'protocol'				=> '',		//Page protocol (http/https)
		'mirrors'				=> [		//Array of all domains, which allowed to access the site
			'count'		=> 0,				//Total count
			'http'		=> [],				//Insecure (http) domains
			'https'		=> []				//Secure (https) domains
		],
		'mirror_index'			=> -1		//Index of current domain in mirrors list ('0' - main domain)
	];
	/**
	 * Allows to check ability to be admin user (can be limited by IP)
	 * @todo Refactor to method
	 *
	 * @var bool
	 */
	public $can_be_admin	= true;
	/**
	 * Initialization state
	 *
	 * @var bool
	 */
	protected	$init	= false;
	/**
	 * Contains parsed route of current page url in form of array without module name and prefixes <i>admin</i>/<i>api</i>
	 *
	 * @var array
	 */
	public		$route	= [];
	/**
	 * Loading of configuration, initialization of $Config, $Cache, $L and Page objects, Routing processing
	 */
	function construct () {
		/**
		 * Reading settings from cache and defining missing data
		 */
		$config = Cache::instance()->config;
		/**
		 * Cache reloading, if necessary
		 */
		if (!is_array($config)) {
			$this->load_config_from_db();
		} else {
			foreach ($config as $part => $value) {
				$this->$part = $value;
			}
			unset($part, $value);
		}
		Event::instance()->fire('System/Config/before_init');
		/**
		 * System initialization with current configuration
		 */
		$this->init();
		Event::instance()->fire('System/Config/after_init');
		if (!file_exists(MODULES.'/'.$this->core['default_module'])) {
			$this->core['default_module']	= 'System';
			$this->save();
		}
		/**
		 * Address routing
		 */
		$this->routing();
	}
	/**
	 * Engine initialization (or reinitialization if necessary)
	 */
	protected function init () {
		Language::instance()->init();
		$Page	= Page::instance();
		$Page->init(
			get_core_ml_text('name'),
			$this->core['theme']
		);
		if (!$this->init) {
			$Page->replace($this->replace['in'], $this->replace['out']);
			$this->init = true;
			if ($this->check_ip($this->core['ip_black_list'])) {
				error_code(403);
				$Page->error();
				return;
			}
		}
		/**
		 * Setting system timezone
		 */
		date_default_timezone_set($this->core['timezone']);
	}
	/**
	 * Check user's IP address matches with elements of given list
	 *
	 * @param string[]	$ips
	 *
	 * @return bool
	 */
	protected function check_ip ($ips) {
		if (!$ips || !is_array($ips)) {
			return false;
		}
		/**
		 * @var _SERVER $_SERVER
		 */
		foreach ($ips as $ip) {
			if ($ip) {
				$char = mb_substr($ip, 0, 1);
				if ($char != mb_substr($ip, -1)) {
					$ip = "/$ip/";
				}
				if (
					_preg_match($ip, $_SERVER->remote_addr) ||
					(
						$_SERVER->ip &&
						_preg_match($ip, $_SERVER->ip)
					)
				) {
					return true;
				}
			}
		}
		return false;
	}
	/**
	 * Analyzing and processing of current page address
	 */
	protected function routing () {
		$L								= Language::instance();
		$server							= &$this->server;
		/**
		 * @var _SERVER $_SERVER
		 */
		$server['raw_relative_address']	= urldecode(trim($_SERVER->request_uri, '/'));
		$server['raw_relative_address']	= null_byte_filter($server['raw_relative_address']);
		$server['host']		= $_SERVER->host;
		$server['protocol']	= $_SERVER->secure ? 'https' : 'http';
		/**
		 * Search for url matching in all mirrors
		 */
		foreach ($this->core['url'] as $i => $address) {
			list($protocol, $urls)			= explode('://', $address, 2);
			$urls							= explode(';', $urls);
			$server['mirrors'][$protocol][]	= $urls[0];
			if ($protocol == $server['protocol'] && $server['mirror_index'] === -1) {
				foreach ($urls as $url) {
					if (mb_strpos("$server[host]$server[raw_relative_address]", $url) === 0) {
						$server['mirror_index']	= $i;
						break;
					}
				}
			}
		}
		unset($address, $i, $urls, $url, $protocol);
		$server['mirrors']['count'] = count($server['mirrors']['http']) + count($server['mirrors']['https']);
		/**
		 * If match was not found - mirror is not allowed!
		 */
		if ($server['mirror_index'] === -1) {
			code_header(400);
			trigger_error($L->mirror_not_allowed, E_USER_ERROR);
			throw new \ExitException;
		}
		/**
		 * Remove trailing slashes
		 */
		$server['raw_relative_address']	= trim($server['raw_relative_address'], ' /\\');
		/**
		 * Redirection processing
		 */
		if (mb_strpos($server['raw_relative_address'], 'redirect/') === 0) {
			if ($this->is_referer_local()) {
				_header('Location: '.substr($server['raw_relative_address'], 9));
			} else {
				error_code(400);
				Page::instance()->error();
			}
			throw new \ExitException;
		}
		$processed_route	= $this->process_route($server['raw_relative_address']);
		if (!$processed_route) {
			error_code(403);
			Page::instance()->error();
			return;
		}
		$this->route				= $processed_route['route'];
		$server['relative_address']	= $processed_route['relative_address'];
		admin_path($processed_route['ADMIN']);
		api_path($processed_route['API']);
		current_module($processed_route['MODULE']);
		home_page($processed_route['HOME']);
		if ($processed_route['API']) {
			_header('Content-Type: application/json; charset=utf-8', true);
			interface_off();
		}
	}
	/**
	 * Check whether referer is local
	 *
	 * @return bool
	 */
	protected function is_referer_local () {
		if (!$_SERVER->referer) {
			return false;
		}
		$referer	= [
			'url'		=> $_SERVER->referer,
			'host'		=> '',
			'protocol'	=> '',
			'local'		=> false
		];
		list($referer['protocol'], $referer['host'])	= explode('://', $referer['url']);
		$referer['host']								= explode('/', $referer['host'])[0];
		foreach ((array)$this->core['url'] as $address) {
			list($protocol, $urls)	= explode('://', $address, 2);
			$urls					= explode(';', $urls);
			if ($protocol === $referer['protocol']) {
				foreach ($urls as $url) {
					if (mb_strpos($referer['host'], $url) === 0) {
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
	 * @param string			$raw_relative_address
	 *
	 * @return bool|string[]							Relative address or <i>false</i> if access denied (occurs when admin access is limited by IP)
	 *                    								Array contains next elements: route, relative_address, ADMIN, API, MODULE, HOME
	 */
	function process_route ($raw_relative_address) {
		$rc = explode('?', $raw_relative_address, 2)[0];
		$rc = trim($rc, '/');
		if (Language::instance()->url_language($rc)) {
			$rc = explode('/', $rc, 2);
			$rc = isset($rc[1]) ? $rc[1] : '';
		}
		/**
		 * Routing replacing
		 */
		Event::instance()->fire('System/Config/pre_routing_replace', [
			'rc'	=> &$rc
		]);
		if (!empty($this->routing['in'])) {
			foreach ($this->routing['in'] as $i => $search) {
				$rc = _preg_replace($search, $this->routing['out'][$i], $rc) ?: str_replace($search, $this->routing['out'][$i], $rc);
			}
			unset($i, $search);
		}
		Event::instance()->fire('System/Config/routing_replace', [
			'rc'	=> &$rc
		]);
		/**
		 * Obtaining page path in form of array
		 */
		$rc	= $rc ? explode('/', $rc) : [];
		/**
		 * If url looks like admin page
		 */
		if (@mb_strtolower($rc[0]) == 'admin') {
			if ($this->core['ip_admin_list_only'] && !$this->check_ip($this->core['ip_admin_list'])) {
				return false;
			}
			$ADMIN	= true;
			array_shift($rc);
		/**
		 * If url looks like API page
		 */
		} elseif (@mb_strtolower($rc[0]) == 'api') {
			$API	= true;
			array_shift($rc);
		}
		if ($this->core['ip_admin_list_only'] && !$this->check_ip($this->core['ip_admin_list'])) {
			$this->can_be_admin = false;
		}
		if (!isset($ADMIN)) {
			$ADMIN	= false;
		}
		if (!isset($API)) {
			$API	= false;
		}
		/**
		 * Module detection
		 */
		$modules	= array_keys(array_filter(
			$this->components['modules'],
			function ($module_data) use ($ADMIN) {
			   return $ADMIN || $module_data['active'] == 1;
			}
		));
		$L			= Language::instance();
		$modules	= array_combine(
			array_map(
				function ($module) use ($L) {
					return path($L->get($module));
				},
				$modules
			),
			$modules
		);
		if (@in_array($rc[0], array_values($modules))) {
			$MODULE	= array_shift($rc);
		} elseif (@isset($modules[$rc[0]])) {
			$MODULE	= $modules[array_shift($rc)];
		} else {
			$MODULE	= $ADMIN || $API || isset($rc[0]) ? 'System' : $this->core['default_module'];
			if (!$ADMIN && !$API && !isset($rc[1])) {
				$HOME	= true;
			}
		}
		if (!isset($HOME)) {
			$HOME	= false;
		}
		return [
			'route'				=> $rc,
			'relative_address'	=> trim(
				($ADMIN ? 'admin/' : '').($API ? 'api/' : '').$MODULE.'/'.implode('/', $rc),
				'/'
			),
			'ADMIN'				=> $ADMIN,
			'API'				=> $API,
			'MODULE'			=> $MODULE,
			'HOME'				=> $HOME
		];
	}
	/**
	 * Updating information about set of available themes
	 */
	function reload_themes () {
		$this->core['themes']	= get_files_list(THEMES, false, 'd');
		asort($this->core['themes']);
	}
	/**
	 * Updating information about set of available languages
	 */
	function reload_languages () {
		$this->core['languages'] = array_unique(
			array_merge(
				_mb_substr(get_files_list(LANGUAGES, '/^.*?\.php$/i', 'f'), 0, -4) ?: [],
				_mb_substr(get_files_list(LANGUAGES, '/^.*?\.json$/i', 'f'), 0, -5) ?: []
			)
		);
		asort($this->core['languages']);
	}
	/**
	 * Reloading of settings cache
	 *
	 * @return bool
	 */
	protected function load_config_from_db () {
		$result = DB::instance()->qf([
			"SELECT
				`core`,
				`db`,
				`storage`,
				`components`,
				`replace`,
				`routing`
			FROM `[prefix]config`
			WHERE `domain` = '%s'
			LIMIT 1",
			DOMAIN
		]);
		if (is_array($result)) {
			foreach ($result as $part => $value) {
				$this->$part = _json_decode($value);
			}
			unset($part, $value);
		} else {
			return false;
		}
		$this->reload_themes();
		$this->reload_languages();
		$this->apply_internal(false);
		return true;
	}
	/**
	 * Applying settings without saving changes into db
	 *
	 * @return bool
	 */
	function apply () {
		return $this->apply_internal();
	}
	/**
	 * Applying settings without saving changes into db
	 *
	 * @param bool $cache_not_saved_mark
	 *
	 * @return bool
	 */
	protected function apply_internal ($cache_not_saved_mark = true) {
		if ($cache_not_saved_mark) {
			$this->core['cache_not_saved'] = true;
		} else {
			unset($this->core['cache_not_saved']);
		}
		$Cache         = Cache::instance();
		$Cache->config = [
			'core'       => $this->core,
			'db'         => $this->db,
			'storage'    => $this->storage,
			'components' => $this->components,
			'replace'    => $this->replace,
			'routing'    => $this->routing
		];
		unset($Cache->{'languages'});
		$L = Language::instance();
		if ($this->core['multilingual'] && User::instance(true)) {
			$L->change(User::instance()->language);
		} else {
			$L->change($this->core['language']);
		}
		$this->init();
		return true;
	}
	/**
	 * Saving settings
	 *
	 * @return bool
	 */
	function save () {
		if (isset($this->core['cache_not_saved'])) {
			unset($this->core['cache_not_saved']);
		}
		$cdb	= DB::instance()->db_prime(0);
		if ($cdb->q(
			"UPDATE `[prefix]config`
			SET
				`core`			= '%s',
				`db`			= '%s',
				`storage`		= '%s',
				`components`	= '%s',
				`replace`		= '%s',
				`routing`		= '%s'
			WHERE `domain` = '%s'
			LIMIT 1",
			_json_encode($this->core),
			_json_encode($this->db),
			_json_encode($this->storage),
			_json_encode($this->components),
			_json_encode($this->replace),
			_json_encode($this->routing),
			DOMAIN
		)) {
			$this->apply_internal(false);
			return true;
		}
		return false;
	}
	/**
	 * Canceling of applied settings
	 */
	function cancel () {
		unset(Cache::instance()->config);
		$this->load_config_from_db();
		$this->apply_internal(false);
	}
	/**
	 * Get base url of current mirror including language suffix
	 *
	 * @return string
	 */
	function base_url () {
		if ($this->server['mirror_index'] === -1) {
			return '';
		}
		$base_url	= $this->server['protocol'].'://'.$this->server['host'];
		$L			= Language::instance();
		if ($L->url_language()) {
			$base_url	.= '/'.$L->clang;
		}
		return $base_url;
	}
	/**
	 * Get base url of main domain
	 *
	 * @return string
	 */
	function core_url () {
		return $this->server['protocol'].'://'.$this->server['host'];
	}
	/**
	 * Get object for getting db and storage configuration of module
	 *
	 * @param string $module_name
	 *
	 * @return Config\Module_Properties
	 */
	function module ($module_name) {
		if (!isset($this->components['modules'][$module_name])) {
			return False_class::instance();
		}
		return new Config\Module_Properties($this->components['modules'][$module_name], $module_name);
	}
}
