<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
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
 * @method static \cs\Config instance($check = false)
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
						'relative_address'			=> '',		//Corrected full page address (recommended for usage)
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
						'mirror_index'				=> -1		//Index of current domain in mirrors list ('-1' - main domain, not mirror)
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
			get_core_ml_text('keywords'),
			get_core_ml_text('description'),
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
					$ip = '/'.$ip.'/';
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
		$L										= Language::instance();
		$this->server['raw_relative_address']	= urldecode($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$this->server['raw_relative_address']	= null_byte_filter($this->server['raw_relative_address']);
		$this->server['host']					= $_SERVER['HTTP_HOST'];
		$this->server['protocol']				= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		$core_url								= explode('://', $this->core['url'], 2);
		$core_url[1]							= explode(';', $core_url[1]);
		/**
		 * $core_url = array(0 => protocol, 1 => array(list of domain and IP addresses))
		 * Check, whether it is main domain
		 */
		$current_mirror = false;
		if ($core_url[0] == $this->server['protocol']) {
			foreach ($core_url[1] as $url) {
				if (mb_strpos($this->server['raw_relative_address'], $url) === 0) {
					$this->server['base_url']	= $this->server['protocol']."://$url";
					$current_mirror				= $url;
					break;
				}
			}
		}
		$this->server['mirrors'][$core_url[0]] = array_merge($this->server['mirrors'][$core_url[0]], $core_url[1]);
		unset($core_url, $url);
		/**
		 * If it  is not the main domain - try to find match in mirrors
		 */
		if ($current_mirror === false && !empty($this->core['mirrors_url'])) {
			$mirrors_url = $this->core['mirrors_url'];
			foreach ($mirrors_url as $i => $mirror_url) {
				$mirror_url		= explode('://', $mirror_url, 2);
				$mirror_url[1]	= explode(';', $mirror_url[1]);
				/**
				 * $mirror_url = array(0 => protocol, 1 => array(list of domain and IP addresses))
				 */
				if ($mirror_url[0] == $this->server['protocol']) {
					foreach ($mirror_url[1] as $url) {
						if (mb_strpos($this->server['raw_relative_address'], $url) === 0) {
							$this->server['base_url']		= $this->server['protocol']."://$url";
							$current_mirror					= $url;
							$this->server['mirror_index']	= $i;
							break 2;
						}
					}
				}
			}
			unset($mirrors_url, $mirror_url, $url, $i);
			/**
			 * If match in mirrors was not found - mirror is not allowed!
			 */
			if ($this->server['mirror_index'] == -1) {
				$this->server['base_url'] = '';
				code_header(400);
				trigger_error($L->mirror_not_allowed, E_USER_ERROR);
			}
		/**
		 * If match was not found - mirror is not allowed!
		 */
		} elseif ($current_mirror === false) {
			$this->server['base_url'] = '';
			code_header(400);
			trigger_error($L->mirror_not_allowed, E_USER_ERROR);
		}
		if (!empty($this->core['mirrors_url'])) {
			$mirrors_url = $this->core['mirrors_url'];
			foreach ($mirrors_url as $mirror_url) {
				$mirror_url									= explode('://', $mirror_url, 2);
				$this->server['mirrors'][$mirror_url[0]]	= array_merge(
					isset($this->server['mirrors'][$mirror_url[0]]) ? $this->server['mirrors'][$mirror_url[0]] : [],
					isset($mirror_url[1]) ? explode(';', $mirror_url[1]) : []
				);
			}
			$this->server['mirrors']['count'] = count($this->server['mirrors']['http'])+count($this->server['mirrors']['https']);
			unset($mirrors_url, $mirror_url);
		}
		/**
		 * Referer detection
		 */
		if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '://') !== false) {
			$ref				= &$this->server['referer'];
			$referer			= explode('://', $ref['url'] = $_SERVER['HTTP_REFERER']);
			$referer[1]			= explode('/', $referer[1]);
			$referer[1]			= $referer[1][0];
			$ref['protocol']	= $referer[0];
			$ref['host']		= $referer[1];
			unset($referer);
			$ref['local']		= in_array($ref['host'], $this->server['mirrors'][$ref['protocol']]);
			unset($ref);
		}
		/**
		 * Preparing page url without basic path
		 */
		$this->server['raw_relative_address']	= trim(mb_substr($this->server['raw_relative_address'], mb_strlen($current_mirror)), ' /\\');
		unset($current_mirror);
		$r										= &$this->routing;
		$rc										= &$this->route;
		$rc										= trim($this->server['raw_relative_address'], '/');
		/**
		 * Redirection processing
		 */
		if (mb_strpos($rc, 'redirect/') === 0) {
			if ($this->server['referer']['local']) {
				header('Location: '.substr($rc, 9));
			} else {
				error_code(404);
				Page::instance()->error();
			}
			exit;
		}
		/**
		 * Routing replacing
		 */
		Trigger::instance()->run(
			'System/Config/pre_routing_replace',
			[
				'rc'	=> &$rc
			]
		);
		if ($rc && strpos($rc, 'api/') === 0) {
			$rc	= explode('?', $rc, 2)[0];
		}
		if (!empty($r['in'])) {
			errors_off();
			foreach ($r['in'] as $i => $search) {
				$rc = _preg_replace($search, $r['out'][$i], $rc) ?: str_replace($search, $r['out'][$i], $rc);
			}
			errors_on();
			unset($i, $search);
		}
		unset($r);
		Trigger::instance()->run(
			'System/Config/routing_replace',
			[
				'rc'	=> &$rc
			]
		);
		/**
		 * Obtaining page path in form of array
		 */
		$rc										= $rc ? explode('/', $rc) : [];
		/**
		 * If url looks like admin page
		 */
		if (isset($rc[0]) && mb_strtolower($rc[0]) == 'admin') {
			if ($this->core['ip_admin_list_only'] && !$this->check_ip($this->core['ip_admin_list'])) {
				error_code(403);
				Page::instance()->error();
				return;
			}
			if (!defined('ADMIN')) {
				define('ADMIN', true);
			}
			array_shift($rc);
		/**
		 * If url looks like API page
		 */
		} elseif (isset($rc[0]) && mb_strtolower($rc[0]) == 'api') {
			if (!defined('API')) {
				define('API', true);
			}
			array_shift($rc);
			header('Content-Type: application/json', true);
			interface_off();
		}
		if ($this->core['ip_admin_list_only'] && !$this->check_ip($this->core['ip_admin_list'])) {
			$this->can_be_admin = false;
		}
		!defined('ADMIN')	&& define('ADMIN', false);
		!defined('API')		&& define('API', false);
		/**
		 * Module detection
		 */
		$modules								= array_keys(array_filter(
			$this->components['modules'],
			function ($module_data) {
			   return ADMIN || $module_data['active'] == 1;
			}
		));
		$modules								= array_combine(
			array_map(
				function ($module) use ($L) {
					return path($L->get($module));
				},
				$modules
			),
			$modules
		);
		if (!defined('MODULE')) {
			if (isset($rc[0]) && in_array($rc[0], array_values($modules))) {
				define('MODULE', array_shift($rc));
			} elseif (isset($rc[0]) && isset($modules[$rc[0]])) {
				define('MODULE', $modules[array_shift($rc)]);
			} else {
				define('MODULE', ADMIN || API || isset($rc[0]) ? 'System' : $this->core['default_module']);
				if (!ADMIN && !API && !isset($rc[1])) {
					define('HOME', true);
				}
			}
		}
		!defined('HOME')	&& define('HOME', false);
		/**
		 * Corrected full page address (recommended for usage)
		 */
		$this->server['relative_address']		= trim(
			(ADMIN ? 'admin/' : '').MODULE.(API ? 'api/' : '').'/'.implode('/', $rc),
			'/'
		);
		$this->server['ajax']					= isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
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
			$this->core['color_schemes'][$theme]	= get_files_list(THEMES."/$theme/schemes", false, 'd') ?: [];
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
		$this->init();
		return true;
	}
	/**
	 * Saving settings
	 *
	 * @return bool
	 */
	function save () {
		$db		= DB::instance();
		$query	= '';
		if (isset($this->data['core']['cache_not_saved'])) {
			unset($this->data['core']['cache_not_saved']);
		}
		foreach ($this->admin_parts as $part) {
			if (isset($this->data[$part])) {
				$query[] = '`'.$part.'` = '.$db->{0}->s(_json_encode($this->$part));
			}
		}
		unset($part, $temp);
		$query	= implode(', ', $query);
		if ($db->{0}->q("UPDATE `[prefix]config` SET $query WHERE `domain` = '%s' LIMIT 1", DOMAIN)) {
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