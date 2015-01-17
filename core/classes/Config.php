<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;

/**
 * Provides next triggers:
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
 * @property mixed[] $core			Property with most general configuration properties
 * @property mixed[] $db			Property, that stores configuration of databases, except the main database, parameters of which are written in configuration file
 * @property mixed[] $storage		Property, that stores configuration of storages, except the main storage, parameters of which are written in configuration file
 * @property mixed[] $components	Internal structure of components parameters
 * @property mixed[] $replace		Property stores replacing rules, that are used to replace text on pages
 * @property mixed[] $routing		Property store routs replacing rules, they are applied to current rule on every pages once
 * @property array   $server		Array of some address data about mirrors and current address properties
 * @property bool    $can_be_admin	Allows to check ability to be admin user (can be limited by IP)
 *
 * @method static Config instance($check = false)
 */
class Config {
	use	Singleton;
	protected $core			= [];
	protected $db			= [];
	protected $storage		= [];
	protected $components	= [];
	protected $replace		= [];
	protected $routing		= [];
	protected $server		= [
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
	protected $can_be_admin	= true;
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
			$this->load();
		} else {
			foreach ($config as $part => $value) {
				$this->$part = $value;
			}
			unset($part, $value);
		}
		Trigger::instance()->run('System/Config/before_init');
		/**
		 * System initialization with current configuration
		 */
		$this->init();
		Trigger::instance()->run('System/Config/after_init');
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
		Language::instance()->change($this->core['language']);
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
		if (!is_array($ips) || empty($ips)) {
			return false;
		}
		$REMOTE_ADDR			= preg_replace('/[^a-f0-9\.:]/i', '', $_SERVER['REMOTE_ADDR']);
		$HTTP_X_REAL_IP			= @preg_replace('/[^a-f0-9\.:]/i', '', $_SERVER['HTTP_X_REAL_IP']) ?: false;
		$HTTP_X_FORWARDED_FOR	= @preg_replace('/[^a-f0-9\.:]/i', '', $_SERVER['HTTP_X_FORWARDED_FOR']) ?: false;
		$HTTP_CLIENT_IP			= @preg_replace('/[^a-f0-9\.:]/i', '', $_SERVER['HTTP_CLIENT_IP']) ?: false;
		foreach ($ips as $ip) {
			if ($ip) {
				$char = mb_substr($ip, 0, 1);
				if ($char != mb_substr($ip, -1)) {
					$ip = "/$ip/";
				}
				if (
					_preg_match($ip, $REMOTE_ADDR) ||
					@_preg_match($ip, $HTTP_X_REAL_IP) ||
					@_preg_match($ip, $HTTP_X_FORWARDED_FOR) ||
					@_preg_match($ip, $HTTP_CLIENT_IP)
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
		$server['raw_relative_address']	= urldecode(trim($_SERVER['REQUEST_URI'], '/'));
		$server['raw_relative_address']	= null_byte_filter($server['raw_relative_address']);
		if (Core::instance()->fixed_language) {
			$server['raw_relative_address']	= explode('/', $server['raw_relative_address'], 2);
			$server['raw_relative_address']	= isset($server['raw_relative_address'][1]) ? $server['raw_relative_address'][1] : '';
		}
		$server['host']					= $_SERVER['HTTP_HOST'];
		if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
			$server['protocol']	= $_SERVER['HTTP_X_FORWARDED_PROTO'];
		} else {
			$server['protocol'] = @$_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		}
		/**
		 * Search for url matching in all mirrors
		 */
		foreach ($this->core['url'] as $i => $address) {
			list($protocol, $urls)			= explode('://', $address, 2);
			$urls							= explode(';', $urls);
			$server['mirrors'][$protocol][]	= $urls[0];
			if ($protocol == $server['protocol'] && $server['mirror_index'] === -1) {
				foreach ($urls as $url) {
					if (mb_strpos("$_SERVER[HTTP_HOST]$server[raw_relative_address]", $url) === 0) {
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
			exit;
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
				header('Location: '.substr($server['raw_relative_address'], 9));
			} else {
				error_code(400);
				Page::instance()->error();
			}
			exit;
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
			header('Content-Type: application/json; charset=utf-8', true);
			interface_off();
		}
	}
	/**
	 * Check whether referer is local
	 *
	 * @return bool
	 */
	protected function is_referer_local () {
		if (@strpos($_SERVER['HTTP_REFERER'], '://') === false) {
			return false;
		}
		$referer	= [
			'url'		=> $_SERVER['HTTP_REFERER'],
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
		$rc	= trim($raw_relative_address, '/');
		/**
		 * Routing replacing
		 */
		Trigger::instance()->run('System/Config/pre_routing_replace', [
			'rc'	=> &$rc
		]);
		$rc	= explode('?', $rc, 2)[0];
		if (!empty($this->routing['in'])) {
			foreach ($this->routing['in'] as $i => $search) {
				$rc = _preg_replace($search, $this->routing['out'][$i], $rc) ?: str_replace($search, $this->routing['out'][$i], $rc);
			}
			unset($i, $search);
		}
		Trigger::instance()->run('System/Config/routing_replace', [
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
	 * Load and save clangs of all languages in cache for multilingual functionality.
	 * Used by system
	 *
	 * @return array	clangs
	 */
	function update_clangs () {
		$clangs		= [];
		foreach ($this->core['active_languages'] as $language) {
			$clangs[$language]	= file_get_json_nocomments(LANGUAGES."/$language.json")['clang'];
		}
		unset($language);
		file_put_json(CACHE.'/languages_clangs', $clangs);
		return $clangs;
	}
	/**
	 * Reloading of settings cache
	 *
	 * @return bool
	 */
	protected function load () {
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
		Cache::instance()->config	= [
			'core'			=> $this->core,
			'db'			=> $this->db,
			'storage'		=> $this->storage,
			'components'	=> $this->components,
			'replace'		=> $this->replace,
			'routing'		=> $this->routing
		];
		$L							= Language::instance();
		if (User::instance(true) && $this->core['multilingual']) {
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
		$this->load();
		$this->apply_internal(false);
	}
	/**
	 * Returns specified item with allowed access level (read only or read/write)
	 *
	 * @param string		$item
	 *
	 * @return array|bool
	 */
	function &__get ($item) {
		if (!isset($this->$item)) {
			$return	= false;
			return $return;
		}
		/**
		 * Modifications only for administrators
		 */
		if (admin_path() && User::instance(true)->admin()) {
			$return = &$this->$item;
		} else {
			$return = $this->$item;
		}
		return $return;
	}
	/**
	 * Sets value of specified item if it is allowed
	 *
	 * @param string		$item
	 * @param array|bool	$data
	 *
	 * @return array|bool
	 */
	function __set ($item, $data) {
		/**
		 * Allow modification only for administrators or requests from methods of Config class
		 */
		if (!isset($this->$item) || !User::instance(true)->admin()) {
			return false;
		}
		return $this->$item = $data;
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
		if (Core::instance()->fixed_language) {
			$base_url	.= '/'.Language::instance()->clang;
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
