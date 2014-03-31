<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
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
 */
class Config {
	use	Singleton;

	protected	$data			= [
					'core'			=> [],
					'db'			=> [],
					'storage'		=> [],
					'components'	=> [],
					'replace'		=> [],
					'routing'		=> [],
					'admin_parts'	=> [						//Columns in DB table of engine configuration
						'core',
						'db',
						'storage',
						'components',
						'replace',
						'routing'
					],
					'server'		=> [						//Array of some address data about mirrors and current address properties
						'raw_relative_address'		=> '',		//Raw page url (in browser's address bar)
						'host'						=> '',		//Current domain
						'relative_address'			=> '',		//Corrected page address (recommended for usage)
						'protocol'					=> '',		//Page protocol (http/https)
						'base_url'					=> '',		//Address of the main page of current mirror, including prefix (http/https)
						'mirrors'					=> [		//Array of all domains, which allowed to access the site
							'count'		=> 0,					//Total count
							'http'		=> [],					//Insecure (http) domains
							'https'		=> []					//Secure (https) domains
						],
						'referer'					=> [
							'url'		=> '',
							'host'		=> '',
							'protocol'	=> '',
							'local'		=> false
						],
						'ajax'						=> false,	//Is this page request via AJAX
						'mirror_index'				=> 0		//Index of current domain in mirrors list ('0' - main domain)
					],
					'can_be_admin'		=> true					//Allows to check ability to be admin user (can be limited by IP)
				],
				$init	= false;
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
		if (is_array($config)) {
			$query = false;
			foreach ($this->admin_parts as $part) {
				if (isset($config[$part])) {
					$this->$part = $config[$part];
				} else {
					$query = true;
					break;
				}
			}
			unset($part);
		} else {
			$query = true;
		}
		/**
		 * Cache rebuilding, if necessary
		 */
		if ($query == true) {
			$this->load();
		}
		/**
		 * System initialization with current configuration
		 */
		$this->init();
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
	protected function init() {
		Language::instance()->change($this->core['language']);
		$Page	= Page::instance();
		$Page->init(
			get_core_ml_text('name'),
			$this->core['theme'],
			$this->core['color_scheme']
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
		$HTTP_X_FORWARDED_FOR	= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? preg_replace('/[^a-f0-9\.:]/i', '', $_SERVER['HTTP_X_FORWARDED_FOR']) : false;
		$HTTP_CLIENT_IP			= isset($_SERVER['HTTP_CLIENT_IP']) ? preg_replace('/[^a-f0-9\.:]/i', '', $_SERVER['HTTP_CLIENT_IP']) : false;
		foreach ($ips as $ip) {
			if (!empty($ip)) {
				$char = mb_substr($ip, 0, 1);
				if ($char != mb_substr($ip, -1)) {
					$ip = "/$ip/";
				}
				if (
					_preg_match($ip, $REMOTE_ADDR) ||
					($HTTP_X_FORWARDED_FOR && _preg_match($ip, $HTTP_X_FORWARDED_FOR)) ||
					($HTTP_CLIENT_IP && _preg_match($ip, $HTTP_CLIENT_IP))
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
		$server['raw_relative_address']	= urldecode($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$server['raw_relative_address']	= null_byte_filter($server['raw_relative_address']);
		$server['host']					= $_SERVER['HTTP_HOST'];
		$server['protocol']				= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		/**
		 * If it  is not the main domain - try to find match in mirrors
		 */
		foreach ((array)$this->core['url'] as $i => $address) {
			list($protocol, $urls)			= explode('://', $address, 2);
			$urls							= explode(';', $urls);
			$server['mirrors'][$protocol]	= array_merge(
				isset($server['mirrors'][$protocol]) ? $server['mirrors'][$protocol] : [],
				[$urls[0]]
			);
			/**
			 * $url = [0 => protocol, 1 => [list of domain and IP addresses]]
			 */
			if ($protocol == $server['protocol']) {
				foreach ($urls as $url) {
					if (mb_strpos($server['raw_relative_address'], $url) === 0) {
						$server['base_url']			= "$server[protocol]://$url";
						$current_mirror_base_url	= $url;
						$server['mirror_index']		= $i;
						break 2;
					}
				}
				unset($url);
			}
		}
		unset($address, $i, $urls, $protocol);
		$server['mirrors']['count'] = count($server['mirrors']['http']) + count($server['mirrors']['https']);
		/**
		 * If match was not found - mirror is not allowed!
		 */
		if (!isset($current_mirror_base_url)) {
			$server['base_url'] = '';
			code_header(400);
			trigger_error($L->mirror_not_allowed, E_USER_ERROR);
			exit;
		}
		/**
		 * Referer detection
		 */
		if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '://') !== false) {
			$ref				= &$server['referer'];
			$referer			= explode('://', $ref['url'] = $_SERVER['HTTP_REFERER']);
			$referer[1]			= explode('/', $referer[1]);
			$referer[1]			= $referer[1][0];
			$ref['protocol']	= $referer[0];
			$ref['host']		= $referer[1];
			unset($referer);
			$ref['local']		= in_array($ref['host'], $server['mirrors'][$ref['protocol']]);
			unset($ref);
		}
		/**
		 * Preparing page url without basic path
		 */
		$server['raw_relative_address']	= trim(mb_substr($server['raw_relative_address'], mb_strlen($current_mirror_base_url)), ' /\\');
		unset($current_mirror_base_url);
		$this->route					= trim($server['raw_relative_address'], '/');
		/**
		 * Redirection processing
		 */
		if (mb_strpos($this->route, 'redirect/') === 0) {
			if ($server['referer']['local']) {
				header('Location: '.substr($this->route, 9));
			} else {
				error_code(400);
				Page::instance()->error();
			}
			define('STOP', true);
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
		!defined('ADMIN')	&& define('ADMIN', $processed_route['ADMIN']);
		!defined('API')		&& define('API', $processed_route['API']);
		!defined('MODULE')	&& define('MODULE', $processed_route['MODULE']);
		!defined('HOME')	&& define('HOME', $processed_route['HOME']);
		if (API) {
			header('Content-Type: application/json', true);
			interface_off();
		}
		$server['ajax']				= isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
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
		if ($rc && strpos($rc, 'api/') === 0) {
			$rc	= explode('?', $rc, 2)[0];
		}
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
		if (isset($rc[0]) && mb_strtolower($rc[0]) == 'admin') {
			if ($this->core['ip_admin_list_only'] && !$this->check_ip($this->core['ip_admin_list'])) {
				return false;
			}
			$ADMIN	= true;
			array_shift($rc);
		/**
		 * If url looks like API page
		 */
		} elseif (isset($rc[0]) && mb_strtolower($rc[0]) == 'api') {
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
		if (isset($rc[0]) && in_array($rc[0], array_values($modules))) {
			$MODULE	= array_shift($rc);
		} elseif (isset($rc[0]) && isset($modules[$rc[0]])) {
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
		$themes							= $this->core['themes'];
		$this->core['themes']			= get_files_list(THEMES, false, 'd');
		asort($this->core['themes']);
		$color_schemes					= $this->core['color_schemes'];
		$this->core['color_schemes']	= [];
		foreach ($this->core['themes'] as $theme) {
			$this->core['color_schemes'][$theme]	= [];
			$this->core['color_schemes'][$theme]	= get_files_list(THEMES."/$theme/schemes", false, 'd') ?: ['Default'];
			asort($this->core['color_schemes'][$theme]);
		}
		if ($themes != $this->core['themes'] || $color_schemes != $this->core['color_schemes']) {
			$this->save();
		}
	}
	/**
	 * Updating information about set of available languages
	 */
	function reload_languages () {
		$languages	= $this->core['languages'];
		$this->core['languages'] = array_unique(
			array_merge(
				_mb_substr(get_files_list(LANGUAGES, '/^.*?\.php$/i', 'f'), 0, -4) ?: [],
				_mb_substr(get_files_list(LANGUAGES, '/^.*?\.json$/i', 'f'), 0, -5) ?: []
			)
		);
		asort($this->core['languages']);
		if ($languages != $this->core['languages']) {
			$this->save();
		}
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
		Cache::instance()->{"languages/clangs"}	= $clangs;
		foreach ($this->core['url'] as &$url) {
			$url_aliases		= explode('//', $url);
			$url_aliases[1]		= explode(';', $url_aliases[1]);
			$last_url			= $url_aliases[1][count($url_aliases[1]) - 1];
			foreach ($clangs as $clang) {
				if (!in_array("$last_url/$clang", $url_aliases[1])) {
					array_unshift($url_aliases[1], "$last_url/$clang");
					$url	= "$url_aliases[0]//".implode(';', $url_aliases[1]);
					$this->save();
				}
			}
		}
		return $clangs;
	}
	/**
	 * Reloading of settings cache
	 *
	 * @return bool
	 */
	protected function load () {
		$query = [];
		foreach ($this->admin_parts as $part) {
			$query[] = '`'.$part.'`';
		}
		unset($part);
		$query	= implode(', ', $query);
		$result = DB::instance()->qf([
			"SELECT $query FROM `[prefix]config` WHERE `domain` = '%s' LIMIT 1",
			DOMAIN
		]);
		if (is_array($result)) {
			foreach ($this->admin_parts as $part) {
				$this->$part = _json_decode($result[$part]);
			}
			unset($part);
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
		/**
		 * If errors - cache updating must be stopped
		 */
		if (class_exists('\\cs\\Error', false) && Error::instance(true)->num()) {
			return false;
		}
		$config						= [];
		foreach ($this->admin_parts as $part) {
			$config[$part] = $this->$part;
		}
		unset($part);
		if ($cache_not_saved_mark) {
			$config['core']['cache_not_saved'] = $this->core['cache_not_saved'] = true;
		} else {
			unset($config['core']['cache_not_saved'], $this->core['cache_not_saved']);
		}
		Cache::instance()->config	= $config;
		$L							= Language::instance();
		if (User::instance(true) && $this->core['multilingual']) {
			$L->change(User::instance()->language);
		} else {
			$L->change($this->core['language']);
		}
		if ($this->core['multilingual']) {
			$this->update_clangs();
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
		$cdb		= DB::instance()->db_prime(0);
		$query	= '';
		if (isset($this->data['core']['cache_not_saved'])) {
			unset($this->data['core']['cache_not_saved']);
		}
		foreach ($this->admin_parts as $part) {
			if (isset($this->data[$part])) {
				$query[] = "`$part` = ".$cdb->s(_json_encode($this->$part));
			}
		}
		unset($part, $temp);
		$query	= implode(', ', $query);
		if ($cdb->q("UPDATE `[prefix]config` SET $query WHERE `domain` = '%s' LIMIT 1", DOMAIN)) {
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
		if (isset($this->data[$item])) {
			$debug_backtrace = debug_backtrace()[1];
			$debug_backtrace = [
				'class'		=> isset($debug_backtrace['class']) ? $debug_backtrace['class'] : '',
				'function'	=> isset($debug_backtrace['function']) ? $debug_backtrace['function'] : ''
			];
			/**
			 * Modifications only for administrators or requests from methods of Config class
			 */
			if (
				User::instance(true)->admin() ||
				$debug_backtrace['class'] == __CLASS__
			) {
				$return = &$this->data[$item];
			} else {
				$return = $this->data[$item];
			}
			return $return;
		}
		$return	= false;
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
		$debug_backtrace = debug_backtrace()[1];
		$debug_backtrace = [
			'class'		=> $debug_backtrace['class'],
			'function'	=> $debug_backtrace['function']
		];
		/**
		 * Allow modification only for administrators or requests from methods of Config class
		 */
		if (
			!isset($this->data[$item]) ||
			(
				User::instance(true)->admin() &&
				$debug_backtrace['class'] != __CLASS__
			)
		) {
			return false;
		}
		return $this->data[$item] = $data;
	}
	/**
	 * Get base url of current mirror
	 *
	 * @return string
	 */
	function base_url () {
		return $this->server['base_url'];
	}
	/**
	 * Get base url of main domain
	 *
	 * @return string
	 */
	function core_url () {
		return explode(';', $this->core['url'], 2)[0];
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
		return (new Config\Module_Properties($this->components['modules'][$module_name], $module_name));
	}
}
